<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Importing the Citizen's Charter from admin/settings.
 *
 * The failure this pins: PHP enforces upload_max_filesize (2M on this
 * project's server) before Laravel runs, so a real multi-megabyte charter
 * arrives as a failed upload and the `required` rule answered 422 with "the
 * charter field is required" — true about the request, useless about the
 * cause. The endpoint must name the size limit and its fix instead.
 */
class CharterUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->default('Active');
            $table->text('roles')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('citizen_charters', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('file_type', 12)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->longText('content')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->string('status', 20)->default('extracted');
            $table->string('extractor', 40)->nullable();
            $table->text('error')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();
        });

        // Empty means "no provider configured", so chatbot narration falls
        // back to quoting the charter's own words.
        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();
        });
    }

    private function admin(): User
    {
        // email_verified_at is not mass-assignable, so it is set directly —
        // the area gate turns away unverified accounts before they reach the
        // endpoint under test.
        return tap(User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'status' => 'Active',
            'roles' => ['admin'],
        ]), fn (User $user) => $user->forceFill(['email_verified_at' => now()])->save());
    }

    /** A file PHP itself refused, the way an over-limit charter arrives. */
    private function discardedUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'charter') . '.pdf';
        file_put_contents($path, '%PDF-1.4 fake');

        return new UploadedFile($path, 'Citizens Charter ARTA 2026.pdf', 'application/pdf', UPLOAD_ERR_INI_SIZE, true);
    }

    #[Test]
    public function an_over_limit_file_names_the_server_ceiling_not_a_missing_field(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/admin/settings/charter', [
            'charter' => $this->discardedUpload(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('upload_max_filesize', (string) $response->json('message'));
        $this->assertStringNotContainsString('required', strtolower((string) $response->json('message')));
    }

    #[Test]
    public function no_file_at_all_says_to_choose_one(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/admin/settings/charter', []);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Choose a PDF or DOCX file', (string) $response->json('message'));
    }

    #[Test]
    public function a_non_admin_is_refused(): void
    {
        $employee = tap(User::create([
            'name' => 'Regular Employee',
            'email' => 'employee@example.com',
            'password' => 'password123',
            'status' => 'Active',
            'roles' => ['employee'],
        ]), fn (User $user) => $user->forceFill(['email_verified_at' => now()])->save());

        $response = $this->actingAs($employee)->postJson('/admin/settings/charter', [
            'charter' => UploadedFile::fake()->create('charter.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function a_wrong_format_is_refused(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/admin/settings/charter', [
            'charter' => UploadedFile::fake()->create('charter.txt', 100, 'text/plain'),
        ]);

        $response->assertStatus(422);
        // Stock validation failures carry an errors bag rather than the
        // endpoint's own success envelope.
        $response->assertJsonValidationErrors('charter');
    }

    /**
     * The server-side path for charters too large to upload: a real Word file
     * written with PhpWord, imported from disk, extracted, activated, and
     * answered from — the whole pipeline the upload button feeds.
     */
    private function writeCharterDocx(): string
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText('CITIZEN\'S CHARTER 2026 — BUSINESS PERMIT (New Application)');
        $section->addText('Requirements: duly accomplished application form, barangay clearance, DTI registration.');
        $section->addText('Fees: PHP 500 for micro enterprises. Processing time: 3 working days.');

        $path = tempnam(sys_get_temp_dir(), 'charter-cmd') . '.docx';
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    #[Test]
    public function the_command_imports_a_charter_from_disk_without_an_upload_ceiling(): void
    {
        $path = $this->writeCharterDocx();

        $this->artisan('charter:import', ['path' => $path])
            ->assertSuccessful();

        $current = \App\Models\CitizenCharter::current();

        $this->assertNotNull($current);
        $this->assertSame('extracted', $current->status);
        $this->assertStringContainsString('PHP 500', (string) $current->content);

        $answer = (new \App\Services\CitizenCharterService())->answer(null, 'What are the requirements for a business permit?');

        $this->assertNotNull($answer);
        $this->assertStringContainsString('barangay clearance', strtolower($answer['answer']));
    }

    #[Test]
    public function the_command_refuses_a_missing_file_and_a_wrong_format(): void
    {
        $this->artisan('charter:import', ['path' => sys_get_temp_dir() . '/no-such-charter.pdf'])
            ->assertFailed();

        $txt = tempnam(sys_get_temp_dir(), 'charter-cmd') . '.txt';
        file_put_contents($txt, 'not a charter');

        $this->artisan('charter:import', ['path' => $txt])
            ->assertFailed();

        $this->assertNull(\App\Models\CitizenCharter::current());
    }

    /**
     * Charter requirements and fees live in tables. The streaming extractor
     * must read them without PhpWord's object graph — the path that exhausted
     * 128M on the real ARTA file — and keep neighbouring cells apart so they
     * retrieve as one chunk.
     */
    #[Test]
    public function charter_tables_extract_without_loading_the_document(): void
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText('SEDULA REQUIREMENTS');
        $table = $section->addTable();
        $row = $table->addRow();
        $row->addCell()->addText('Valid ID');
        $row->addCell()->addText('PHP 55');
        $row = $table->addRow();
        $row->addCell()->addText('Proof of residency');
        $row->addCell()->addText('30 minutes');

        $path = tempnam(sys_get_temp_dir(), 'charter-cmd') . '.docx';
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        $this->artisan('charter:import', ['path' => $path])
            ->assertSuccessful();

        $current = \App\Models\CitizenCharter::current();

        $this->assertNotNull($current);
        // The low-memory streaming reader did the work, not PhpWord's loader.
        $this->assertSame('docx-xml', $current->extractor);
        $this->assertStringContainsString('Valid ID', (string) $current->content);
        $this->assertStringContainsString('PHP 55', (string) $current->content);
        $this->assertStringContainsString('Proof of residency', (string) $current->content);
    }
}

