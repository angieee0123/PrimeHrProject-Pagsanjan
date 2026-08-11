<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use App\Services\SiteContentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Website Content — the editor for the public welcome page.
 *
 * This is the only write path in the system whose output is read by
 * unauthenticated visitors, so the properties pinned here are the ones that
 * would matter if they broke: who may write, what a field may contain, and
 * that installing the feature changed nothing until somebody edits something.
 *
 * Tables are built by hand because RefreshDatabase does not work in this
 * project — see CLAUDE.md.
 */
class WebsiteContentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->text('roles')->nullable();
            // EnsureAccountActive 403s anyone whose status is not 'Active',
            // so a test user without this column can never reach a controller.
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // Touched while rendering a public page: the theme block, and the two
        // hero counts SiteContentService reads from the HR tables.
        Schema::create('system_ai_settings', function (Blueprint $t) {
            $t->id();
            $t->string('provider')->nullable(); $t->text('api_key')->nullable(); $t->string('model')->nullable();
            $t->string('theme')->default('default');
            $t->string('custom_theme_primary', 7)->nullable(); $t->string('theme_secondary', 7)->nullable();
            $t->string('theme_accent', 7)->nullable(); $t->string('theme_muted', 7)->nullable();
            $t->string('sidebar_style')->nullable(); $t->string('topbar_style')->nullable();
            $t->timestamps();
        });
        Schema::create('user_theme_settings', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id');
            $t->string('theme')->default('default');
            $t->string('custom_theme_primary', 7)->nullable(); $t->string('theme_secondary', 7)->nullable();
            $t->string('theme_accent', 7)->nullable(); $t->string('theme_muted', 7)->nullable();
            $t->string('sidebar_style')->nullable(); $t->string('topbar_style')->nullable();
            $t->timestamps();
        });
        Schema::create('employees', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable();
            $t->string('employee_id')->nullable(); $t->string('first_name')->nullable();
            $t->string('last_name')->nullable(); $t->string('photo')->nullable();
        });
        Schema::create('departments', function (Blueprint $t) {
            $t->id(); $t->string('name')->nullable();
        });

        SiteContentService::flushCache();
    }

    protected function tearDown(): void
    {
        SiteContentService::flushCache();
        parent::tearDown();
    }

    /** User has no $fillable, so mass assignment would drop `roles` silently. */
    private function user(string $username, array $roles): User
    {
        $u = new User();
        $u->email = $username . '@x.test';
        $u->username = $username;
        $u->roles = $roles;
        $u->status = 'Active';
        $u->save();

        return $u;
    }

    private function admin(): User
    {
        return $this->user('admin', ['admin']);
    }

    private function employee(): User
    {
        return $this->user('emp', ['employee']);
    }

    // ── Defaults ────────────────────────────────────────────────────

    #[Test]
    public function an_unedited_section_returns_the_shipped_wording(): void
    {
        $this->assertSame(
            SiteContentService::defaults()['contact']['email'],
            SiteContentService::section('contact')['email'],
        );
    }

    #[Test]
    public function a_saved_section_keeps_defaults_for_fields_it_did_not_set(): void
    {
        SiteContentService::put('contact', ['email' => 'new@pagsanjan.gov.ph']);
        SiteContentService::flushCache();

        $contact = SiteContentService::section('contact');

        $this->assertSame('new@pagsanjan.gov.ph', $contact['email']);
        // Untouched by that save — must not come back blank.
        $this->assertSame(
            SiteContentService::defaults()['contact']['phone'],
            $contact['phone'],
        );
    }

    #[Test]
    public function deleting_a_list_item_does_not_resurrect_the_default_underneath(): void
    {
        $one = [['date' => '2026-01-01', 'tag' => 'Notice', 'title' => 'Only one', 'excerpt' => '']];
        SiteContentService::put('announcements', ['items' => $one]);
        SiteContentService::flushCache();

        $items = SiteContentService::section('announcements')['items'];

        $this->assertCount(1, $items, 'a list is replaced wholesale, never merged item by item');
        $this->assertSame('Only one', $items[0]['title']);
    }

    // ── The rail's two groups ───────────────────────────────────────

    #[Test]
    public function every_grouped_section_is_a_real_section_with_defaults(): void
    {
        $defaults = SiteContentService::defaults();

        foreach (SiteContentService::SECTION_GROUPS as $group => $sections) {
            foreach ($sections as $key => $label) {
                $this->assertArrayHasKey(
                    $key,
                    $defaults,
                    "'$key' is listed under '$group' but has no defaults, so its panel would render empty",
                );
                $this->assertFileExists(
                    resource_path("views/admin/website/sections/$key.blade.php"),
                    "'$key' is listed under '$group' but has no form partial",
                );
            }
        }
    }

    #[Test]
    public function every_section_with_defaults_appears_in_exactly_one_group(): void
    {
        $grouped = array_keys(SiteContentService::sections());

        $this->assertSame(
            count($grouped),
            count(array_unique($grouped)),
            'a section is listed in both groups, so the rail would show it twice',
        );

        // The logo is stored content but has no text form, so it is the one
        // key that is deliberately absent from the rail.
        $expected = array_diff(array_keys(SiteContentService::defaults()), [SiteContentService::LOGO_KEY]);

        $this->assertEqualsCanonicalizing(
            array_values($expected),
            $grouped,
            'a section has defaults but no rail entry, so nobody can edit it',
        );
    }

    #[Test]
    public function every_section_has_a_plain_english_blurb(): void
    {
        // The overview shows one card per section, and the card is useless
        // without the sentence saying which part of the page it is.
        foreach (array_keys(SiteContentService::sections()) as $key) {
            $this->assertArrayHasKey(
                $key,
                SiteContentService::SECTION_BLURBS,
                "'$key' has no blurb, so its overview card would be a bare title",
            );
            $this->assertNotSame('', trim(SiteContentService::SECTION_BLURBS[$key]));
        }

        $this->assertEqualsCanonicalizing(
            array_keys(SiteContentService::sections()),
            array_keys(SiteContentService::SECTION_BLURBS),
            'a blurb exists for a section that is not in the rail',
        );
    }

    #[Test]
    public function announcements_leads_the_rail(): void
    {
        // The only content that goes stale on its own; it opens by default.
        $this->assertSame('announcements', array_key_first(SiteContentService::sections()));
    }

    // ── Authorisation ───────────────────────────────────────────────

    #[Test]
    public function an_employee_cannot_open_the_editor(): void
    {
        $this->actingAs($this->employee())->get('/admin/website')->assertForbidden();
    }

    #[Test]
    public function an_employee_cannot_write_content(): void
    {
        $this->actingAs($this->employee())
            ->postJson('/admin/website/govbar', ['left' => 'hacked'])
            ->assertForbidden();

        $this->assertDatabaseCount('site_contents', 0);
    }

    #[Test]
    public function a_guest_cannot_write_content(): void
    {
        $this->postJson('/admin/website/govbar', ['left' => 'hacked'])->assertUnauthorized();
        $this->assertDatabaseCount('site_contents', 0);
    }

    #[Test]
    public function an_employee_cannot_reset_a_section(): void
    {
        SiteContentService::put('govbar', ['left' => 'kept']);

        $this->actingAs($this->employee())
            ->deleteJson('/admin/website/govbar')
            ->assertForbidden();

        $this->assertDatabaseCount('site_contents', 1);
    }

    #[Test]
    public function an_admin_can_save_and_reset_a_section(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/admin/website/govbar', ['left' => 'Left copy', 'right' => 'Right copy'])
            ->assertOk();

        SiteContentService::flushCache();
        $this->assertSame('Left copy', SiteContentService::section('govbar')['left']);
        $this->assertSame($admin->id, SiteContent::where('key', 'govbar')->first()->updated_by);

        $this->actingAs($admin)->deleteJson('/admin/website/govbar')->assertOk();

        SiteContentService::flushCache();
        $this->assertDatabaseCount('site_contents', 0);
        $this->assertSame(
            SiteContentService::defaults()['govbar']['left'],
            SiteContentService::section('govbar')['left'],
        );
    }

    #[Test]
    public function an_unknown_section_is_refused(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/website/passwords', ['left' => 'x'])
            ->assertNotFound();
    }

    // ── What a field may contain ────────────────────────────────────

    #[Test]
    public function a_link_may_not_carry_a_javascript_url(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/website/footer', [
                'links' => [['label' => 'Click', 'anchor' => 'javascript:alert(1)']],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('links.0.anchor');
    }

    #[Test]
    public function a_link_may_not_carry_a_data_url(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/website/footer', [
                'links' => [['label' => 'Click', 'anchor' => 'data:text/html;base64,PHNjcmlwdD4=']],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function an_icon_outside_the_vocabulary_is_refused(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/website/services', [
                'categories' => [[
                    'label' => 'X',
                    'icon'  => '"><script>alert(1)</script>',
                    'items' => [],
                ]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('categories.0.icon');
    }

    #[Test]
    public function an_announcement_tag_outside_the_vocabulary_is_refused(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/website/announcements', [
                'items' => [['date' => '2026-01-01', 'tag' => 'Whatever', 'title' => 'T', 'excerpt' => '']],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.tag');
    }

    #[Test]
    public function markup_saved_into_a_copy_field_is_escaped_on_the_public_page(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/website/hero', ['badge' => '<script>alert(1)</script>'])
            ->assertOk();

        SiteContentService::flushCache();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    #[Test]
    public function a_list_cannot_be_grown_past_its_cap(): void
    {
        $tags = array_fill(0, 20, 'tag');

        $this->actingAs($this->admin())
            ->postJson('/admin/website/hero', ['tags' => $tags])
            ->assertStatus(422)
            ->assertJsonValidationErrors('tags');
    }

    // ── The public page ─────────────────────────────────────────────

    #[Test]
    public function the_welcome_page_shows_what_an_admin_saved(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/website/contact', ['email' => 'mayor@pagsanjan.gov.ph'])
            ->assertOk();

        SiteContentService::flushCache();

        $this->get('/')->assertOk()->assertSee('mayor@pagsanjan.gov.ph');
    }

    #[Test]
    public function the_welcome_page_still_renders_when_nothing_has_been_edited(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(SiteContentService::defaults()['hero']['title_highlight']);
    }

    // ── Shared chrome across every public page ──────────────────────

    #[Test]
    public function the_shared_header_and_footer_follow_an_edit_on_every_public_page(): void
    {
        // The gov bar, the brand block and the footer were hard-coded four
        // times over. Editing them changed the welcome page only, so the
        // sign-in screens kept the old municipality name and "© 2025".
        SiteContentService::put('govbar', ['left' => 'GOVBAR-LEFT-X', 'right' => 'GOVBAR-RIGHT-X']);
        SiteContentService::put('brand', ['name' => 'BRAND-NAME-X', 'sub' => 'BRAND-SUB-X']);
        SiteContentService::put('footer', [
            'name' => 'FOOTER-NAME-X', 'sub' => 'FOOTER-SUB-X', 'copyright' => 'COPYRIGHT-X',
        ]);
        SiteContentService::flushCache();

        $probes = [
            'GOVBAR-LEFT-X', 'GOVBAR-RIGHT-X',
            'BRAND-NAME-X', 'BRAND-SUB-X',
            'FOOTER-NAME-X', 'FOOTER-SUB-X', 'COPYRIGHT-X',
        ];

        $pages = [
            'welcome'         => $this->get('/')->assertOk()->getContent(),
            'login'           => $this->get('/login')->assertOk()->getContent(),
            'forgot-password' => $this->get('/password/forgot')->assertOk()->getContent(),
            // Reached only mid-login, so a plain GET redirects; the view is
            // rendered directly to exercise the same components.
            'select-role'     => view('user.select-role', [
                'options' => [['role' => 'admin'], ['role' => 'hr']],
            ])->render(),
        ];

        foreach ($pages as $name => $html) {
            foreach ($probes as $probe) {
                $this->assertStringContainsString($probe, $html, "$name did not pick up $probe");
            }
            // The year is rendered, never stored — all four said "© 2025".
            $this->assertStringContainsString('&copy; ' . date('Y'), $html, "$name is not rendering the current year");
        }
    }

    // ── Logo ────────────────────────────────────────────────────────

    #[Test]
    public function the_shipped_logo_is_used_until_one_is_uploaded(): void
    {
        $this->assertNull(SiteContentService::logoPath());
        $this->assertStringContainsString(SiteContentService::DEFAULT_LOGO, SiteContentService::logoUrl());
    }

    #[Test]
    public function an_admin_can_upload_a_logo_and_every_page_picks_it_up(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/website/logo', [
                'logo' => UploadedFile::fake()->image('seal.png', 300, 300),
            ])
            ->assertOk();

        SiteContentService::flushCache();

        $path = SiteContentService::logoPath();
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        // The public page and the sign-in screen are rendered by different
        // Blade files; both must move together, which is the whole point.
        $url = SiteContentService::logoUrl();
        $this->get('/')->assertOk()->assertSee($url, false);
        $this->get('/login')->assertOk()->assertSee($url, false);
    }

    #[Test]
    public function replacing_the_logo_deletes_the_file_it_replaced(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/website/logo', ['logo' => UploadedFile::fake()->image('one.png', 200, 200)])
            ->assertOk();
        SiteContentService::flushCache();
        $first = SiteContentService::logoPath();

        $this->actingAs($admin)
            ->post('/admin/website/logo', ['logo' => UploadedFile::fake()->image('two.png', 200, 200)])
            ->assertOk();
        SiteContentService::flushCache();
        $second = SiteContentService::logoPath();

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    #[Test]
    public function resetting_the_logo_removes_the_upload_and_restores_the_shipped_one(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/website/logo', ['logo' => UploadedFile::fake()->image('seal.png', 200, 200)])
            ->assertOk();
        SiteContentService::flushCache();
        $path = SiteContentService::logoPath();

        $this->actingAs($admin)->deleteJson('/admin/website/logo')->assertOk();
        SiteContentService::flushCache();

        Storage::disk('public')->assertMissing($path);
        $this->assertNull(SiteContentService::logoPath());
        $this->assertStringContainsString(SiteContentService::DEFAULT_LOGO, SiteContentService::logoUrl());
    }

    #[Test]
    public function an_employee_cannot_upload_a_logo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->employee())
            ->post('/admin/website/logo', ['logo' => UploadedFile::fake()->image('x.png', 200, 200)])
            ->assertForbidden();

        $this->assertNull(SiteContentService::logoPath());
    }

    #[Test]
    public function an_svg_is_refused_because_it_can_carry_script(): void
    {
        Storage::fake('public');

        $svg = UploadedFile::fake()->createWithContent(
            'evil.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
        );

        $this->actingAs($this->admin())
            ->post('/admin/website/logo', ['logo' => $svg], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('logo');

        $this->assertNull(SiteContentService::logoPath());
    }

    #[Test]
    public function a_non_image_is_refused_even_with_an_image_extension(): void
    {
        Storage::fake('public');

        $php = UploadedFile::fake()->createWithContent('shell.png', '<?php echo "pwned"; ?>');

        $this->actingAs($this->admin())
            ->post('/admin/website/logo', ['logo' => $php], ['Accept' => 'application/json'])
            ->assertStatus(422);

        $this->assertNull(SiteContentService::logoPath());
    }

    #[Test]
    public function the_stored_filename_is_generated_not_taken_from_the_upload(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/website/logo', [
                'logo' => UploadedFile::fake()->image('../../evil name.png', 200, 200),
            ])
            ->assertOk();

        SiteContentService::flushCache();
        $path = SiteContentService::logoPath();

        $this->assertMatchesRegularExpression('#^site/logo-[0-9a-f]{16}\.png$#', $path);
    }

    #[Test]
    public function a_missing_logo_file_falls_back_instead_of_serving_a_broken_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/website/logo', ['logo' => UploadedFile::fake()->image('seal.png', 200, 200)])
            ->assertOk();
        SiteContentService::flushCache();

        // Someone clears storage without touching the database.
        Storage::disk('public')->delete(SiteContentService::logoPath());
        SiteContentService::flushCache();

        $this->assertNull(SiteContentService::logoPath());
        $this->assertStringContainsString(SiteContentService::DEFAULT_LOGO, SiteContentService::logoUrl());
    }
}
