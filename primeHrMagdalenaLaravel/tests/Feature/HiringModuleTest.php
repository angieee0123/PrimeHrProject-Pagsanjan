<?php

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\AuditLog;
use App\Models\ScreeningResult;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsRecruitmentSchema;
use Tests\TestCase;

/**
 * The Recruitment module on the real HTTP stack, against the real database.
 *
 * Two questions, and they are the two a page-by-page review cannot answer:
 *
 *  1. **Can somebody who should not act, act?** `/admin/recruitment` is closed
 *     to employees by `EnsureRoleForArea`, and writes are closed to HR staff by
 *     the controllers' own `requireManager`. A hidden button is not a
 *     permission, so each is exercised as an actual request.
 *  2. **Does an upload reach the score?** The test follows one resume from the
 *     HTTP upload through the queued job (sync in tests) to a ranking row, so
 *     the module cannot silently store an application it never screens.
 */
class HiringModuleTest extends TestCase
{
    use BuildsRecruitmentSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildRecruitmentSchema([
            'vacancies',
            'candidates',
            'applicant_profiles',
            'screening_results',
            'audit_log',
        ]);

        $this->buildChromeTables();

        Storage::fake((string) config('hiring.disk', 'local'));
    }

    /**
     * The tables the admin chrome reads on every page. The project's own
     * migrations cannot run on SQLite, so the handful the layout touches are
     * built by hand.
     */
    private function buildChromeTables(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('audience')->default('admin');
            $table->string('category')->nullable();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->string('dedupe_key')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->string('theme')->nullable();
            $table->string('custom_theme')->nullable();
            $table->json('theme_overrides')->nullable();
            $table->string('sidebar_surface')->nullable();
            $table->string('topbar_surface')->nullable();
            $table->timestamps();
        });
    }

    private function user(string $role): User
    {
        $user = new User([
            'name' => 'Test '.ucfirst($role),
            'username' => $role,
            'email' => $role.'@pagsanjan.gov.ph',
            'roles' => [$role],
            'status' => 'Active',
        ]);

        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function vacancy(array $overrides = []): Vacancy
    {
        return Vacancy::create(array_merge([
            'position_title' => 'Administrative Officer I',
            'place_of_assignment' => "Mayor's Office",
            'salary_grade' => 11,
            'monthly_salary' => 30000,
            'qs_education' => "Bachelor's degree relevant to the job",
            'qs_training_hours' => 40,
            'qs_experience_years' => 5,
            'qs_eligibility' => 'Career Service Professional',
            'required_skills' => ['records management'],
            'weights' => [
                'education' => 0.2,
                'training' => 0.2,
                'experience' => 0.3,
                'eligibility' => 0.1,
                'skills' => 0.2,
            ],
            'slots' => 1,
            'posting_date' => now()->toDateString(),
            'closing_date' => now()->addMonth()->toDateString(),
            'status' => Vacancy::STATUS_OPEN,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function storePayload(array $overrides = []): array
    {
        return array_merge([
            'position_title' => 'Records Officer I',
            'place_of_assignment' => 'Human Resource Management Office',
            'plantilla_item_no' => 'HRO-001',
            'salary_grade' => 11,
            'monthly_salary' => 32000,
            'qs_education' => "Bachelor's degree relevant to the job",
            'qs_training_hours' => 16,
            'qs_experience_years' => 2,
            'qs_eligibility' => 'Career Service Professional',
            'competency' => 'Records management',
            'required_skills' => 'records management, communication',
            'weight_education' => 0.3,
            'weight_training' => 0.2,
            'weight_experience' => 0.2,
            'weight_eligibility' => 0.1,
            'weight_skills' => 0.2,
            'instructions' => 'Submit through the HRMO.',
            'slots' => 2,
            'posting_date' => now()->toDateString(),
            'closing_date' => now()->addMonth()->toDateString(),
            'status' => 'OPEN',
        ], $overrides);
    }

    private function resumeText(): string
    {
        return implode("\n", [
            'JUAN DELA CRUZ',
            'juan.delacruz@example.com',
            'Bachelor of Science in Environmental Science',
            'Civil Service Professional',
            '40 hours of training',
            '5 years of experience',
            'Skills: records management',
        ]);
    }

    #[Test]
    public function an_administrator_sees_the_overview(): void
    {
        $this->vacancy();

        $response = $this->actingAs($this->user('admin'))->get(route('admin.recruitment'));

        $response->assertOk();
        $response->assertSee('Open Vacancies');
        $response->assertSee('Applicants by position');
    }

    #[Test]
    public function an_employee_cannot_reach_the_module(): void
    {
        $this->actingAs($this->user('employee'))
            ->get(route('admin.recruitment'))
            ->assertForbidden();
    }

    #[Test]
    public function creating_a_vacancy_stores_it_and_audits_the_officer(): void
    {
        $admin = $this->user('admin');

        $response = $this->actingAs($admin)->post(route('admin.recruitment.store'), $this->storePayload());

        $vacancy = Vacancy::where('position_title', 'Records Officer I')->first();

        $this->assertNotNull($vacancy);
        $response->assertRedirect(route('admin.recruitment.show', $vacancy->id));

        $this->assertSame('OPEN', $vacancy->status);
        $this->assertSame(['records management', 'communication'], $vacancy->required_skills);
        $this->assertSame($admin->id, $vacancy->created_by);
        $this->assertSame(2, $vacancy->slots);

        $this->assertDatabaseHas('audit_log', [
            'action' => 'CREATE_VACANCY',
            'entity' => 'vacancy',
            'entity_id' => $vacancy->id,
            'user_name' => $admin->name,
        ]);
    }

    #[Test]
    public function the_weights_must_add_up_to_one(): void
    {
        $this->actingAs($this->user('admin'))
            ->post(route('admin.recruitment.store'), $this->storePayload(['weight_skills' => 0.9]))
            ->assertSessionHasErrors('weights');

        $this->assertSame(0, Vacancy::count());
    }

    #[Test]
    public function hr_staff_may_read_but_not_manage(): void
    {
        $hr = $this->user('hr');

        $this->actingAs($hr)->get(route('admin.recruitment.vacancies'))->assertOk();
        $this->actingAs($hr)->get(route('admin.recruitment.create'))->assertForbidden();
        $this->actingAs($hr)->post(route('admin.recruitment.store'), $this->storePayload())->assertForbidden();
        $this->actingAs($hr)->get(route('admin.recruitment.audit'))->assertForbidden();
    }

    #[Test]
    public function a_draft_is_published_held_and_resumed(): void
    {
        $admin = $this->user('admin');
        $vacancy = $this->vacancy([
            'status' => Vacancy::STATUS_DRAFT,
            'posting_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.recruitment.publish', $vacancy->id))
            ->assertRedirect(route('admin.recruitment.show', $vacancy->id));

        $this->assertSame('OPEN', $vacancy->fresh()->status);
        $this->assertNotNull($vacancy->fresh()->published_at);

        $this->actingAs($admin)
            ->post(route('admin.recruitment.hold', $vacancy->id), ['note' => 'Budget review'])
            ->assertRedirect(route('admin.recruitment.show', $vacancy->id));

        $this->assertSame('ON_HOLD', $vacancy->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.recruitment.resume', $vacancy->id), [
                'closing_date' => now()->addMonths(2)->toDateString(),
            ])
            ->assertRedirect(route('admin.recruitment.show', $vacancy->id));

        $resumed = $vacancy->fresh();
        $this->assertSame('OPEN', $resumed->status);
        $this->assertSame(0, $resumed->reopen_count, 'A resumed hold is not a reopen.');
    }

    #[Test]
    public function a_terminal_position_refuses_a_reopen_and_says_why(): void
    {
        $vacancy = $this->vacancy(['status' => Vacancy::STATUS_CLOSED]);

        $response = $this->actingAs($this->user('admin'))
            ->post(route('admin.recruitment.reopen', $vacancy->id), [
                'closing_date' => now()->addMonth()->toDateString(),
            ]);

        $response->assertRedirect(route('admin.recruitment.show', $vacancy->id));
        $response->assertSessionHas('error');

        $this->assertSame('CLOSED', $vacancy->fresh()->status);

        $this->assertDatabaseMissing('audit_log', ['action' => 'REOPEN_VACANCY']);
    }

    #[Test]
    public function an_uploaded_resume_is_screened_scored_and_ranked(): void
    {
        $admin = $this->user('admin');
        $vacancy = $this->vacancy();

        $file = UploadedFile::fake()->createWithContent('juan-dela-cruz.txt', $this->resumeText());

        $response = $this->actingAs($admin)->post(
            route('admin.recruitment.resumes.upload', $vacancy->id),
            ['files' => [$file]],
        );

        $response->assertRedirect(route('admin.recruitment.show', $vacancy->id));
        $response->assertSessionHas('success');

        $profile = ApplicantProfile::where('vacancy_id', $vacancy->id)->first();

        $this->assertNotNull($profile);
        $this->assertSame(ApplicantProfile::PARSE_DONE, $profile->parse_status);
        $this->assertNotNull($profile->screeningResult);
        $this->assertSame(1, $profile->screeningResult->rank_position);
        $this->assertStringContainsString('Education:', $profile->screeningResult->explanation);

        $this->assertDatabaseHas('audit_log', [
            'action' => 'UPLOAD_RESUME',
            'entity' => 'vacancy',
            'entity_id' => $vacancy->id,
        ]);
    }

    #[Test]
    public function hr_staff_may_screen_even_though_they_cannot_manage(): void
    {
        $hr = $this->user('hr');
        $vacancy = $this->vacancy();

        $file = UploadedFile::fake()->createWithContent('juan.txt', $this->resumeText());

        $this->actingAs($hr)->post(
            route('admin.recruitment.resumes.upload', $vacancy->id),
            ['files' => [$file]],
        )->assertRedirect(route('admin.recruitment.show', $vacancy->id));

        $this->assertSame(1, ApplicantProfile::where('vacancy_id', $vacancy->id)->count());
    }

    #[Test]
    public function a_resume_download_is_private_and_not_sniffable(): void
    {
        $admin = $this->user('admin');
        $vacancy = $this->vacancy();

        $file = UploadedFile::fake()->createWithContent('juan.txt', $this->resumeText());

        $this->actingAs($admin)->post(
            route('admin.recruitment.resumes.upload', $vacancy->id),
            ['files' => [$file]],
        );

        $profile = ApplicantProfile::where('vacancy_id', $vacancy->id)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.recruitment.resume-file', $profile->id));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('JUAN DELA CRUZ', (string) $response->getContent());

        $this->assertTrue(
            AuditLog::where('action', 'VIEW_RESUME')->where('entity_id', $profile->id)->exists(),
            'Opening somebody else\'s personal document is recorded.'
        );
    }

    #[Test]
    public function the_audit_page_is_readable_by_an_administrator(): void
    {
        $admin = $this->user('admin');
        AuditLog::create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'action' => 'CREATE_VACANCY',
            'entity' => 'vacancy',
            'entity_id' => '00000000-0000-0000-0000-000000000000',
            'at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.recruitment.audit'));

        $response->assertOk();
        $response->assertSee('CREATE VACANCY');
    }

    #[Test]
    public function the_page_renders_inside_the_layout_content_area(): void
    {
        $html = (string) $this->actingAs($this->user('admin'))
            ->get(route('admin.recruitment'))
            ->getContent();

        // A child view that forgets @section('content') is echoed before the
        // layout, which puts the module behind the fixed sidebar. Pin the
        // structure so that cannot come back.
        $this->assertTrue(str_starts_with(ltrim($html), '<!DOCTYPE html>'));

        $main = strpos($html, '<main class="main-content">');

        $this->assertNotFalse($main, 'The layout main element should be present.');
        $this->assertGreaterThan($main, strpos($html, 'class="glass-shell"'));
        $this->assertGreaterThan($main, strpos($html, 'Open Vacancies'));
    }

    #[Test]
    public function every_recruitment_page_renders_with_real_rows(): void
    {
        $admin = $this->user('admin');
        $vacancy = $this->vacancy();

        $file = UploadedFile::fake()->createWithContent('juan.txt', $this->resumeText());

        $this->actingAs($admin)->post(
            route('admin.recruitment.resumes.upload', $vacancy->id),
            ['files' => [$file]],
        );

        $this->actingAs($admin)->get(route('admin.recruitment.vacancies'))->assertOk()->assertSee('Administrative Officer I');
        $this->actingAs($admin)->get(route('admin.recruitment.show', $vacancy->id))->assertOk()->assertSee('JUAN DELA CRUZ');
        $this->actingAs($admin)->get(route('admin.recruitment.rankings'))->assertOk()->assertSee('JUAN DELA CRUZ');
        $this->actingAs($admin)->get(route('admin.recruitment.approvals'))->assertOk();
        $this->actingAs($admin)->get(route('admin.recruitment.archive'))->assertOk();
        $this->actingAs($admin)->get(route('admin.recruitment.create'))->assertOk()->assertSee('Fit-score weights');
    }

    #[Test]
    public function a_deleted_vacancy_is_a_404_not_a_broken_page(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('admin.recruitment.show', '00000000-0000-0000-0000-000000000000'))
            ->assertNotFound();
    }
}
