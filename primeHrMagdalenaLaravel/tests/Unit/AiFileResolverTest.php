<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\Employee;
use App\Models\GovernmentId;
use App\Models\User;
use App\Services\AiAccessPolicy;
use App\Services\AiFileResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * AiFileResolver is what turns a card in the chat into actual bytes. Two
 * properties have to hold, and these cover both:
 *
 *  1. a reference can only ever name a row in the database — never a path, so
 *     there is nothing to traverse out of;
 *  2. the caller's permission is checked against the row's owner every time,
 *     not just when the card was first drawn.
 */
class AiFileResolverTest extends TestCase
{
    private AiFileResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->createSchema();

        $this->resolver = new AiFileResolver(new AiAccessPolicy());
    }

    /**
     * Only the three tables these assertions touch, built by hand on the
     * in-memory SQLite connection. The project's own migrations cannot run
     * here — 2026_04_15_182306_add_timestamps_to_tables emits MySQL-only
     * `ON UPDATE CURRENT_TIMESTAMP` — so RefreshDatabase is not an option.
     */
    private function createSchema(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('document_type')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('government_ids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('gsis_no')->nullable();
            $table->string('gsis_file_path')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('philhealth_file_path')->nullable();
        });
    }

    private function user(array $roles, ?int $employeeId = null): User
    {
        $user = new User();
        $user->roles = $roles;
        $user->status = 'Active';
        $user->employee_id = $employeeId;

        return $user;
    }

    private function employee(string $last = 'Cruz'): Employee
    {
        return Employee::create([
            'employee_id' => 'EMP-' . fake()->unique()->numberBetween(1000, 9999),
            'first_name' => 'Juan',
            'last_name' => $last,
        ]);
    }

    private function documentFor(Employee $employee): Document
    {
        $path = UploadedFile::fake()
            ->create('contract.pdf', 12)
            ->store('documents', 'public');

        return Document::create([
            'employee_id' => $employee->id,
            'document_type' => 'Contract',
            'file_path' => $path,
            'uploaded_at' => now(),
            'status' => 'approved',
        ]);
    }

    #[Test]
    public function an_admin_can_open_any_employees_document(): void
    {
        $document = $this->documentFor($this->employee());

        $result = $this->resolver->resolve($this->user(['admin']), 'documents', (string) $document->id);

        $this->assertTrue($result['success']);
        $this->assertSame(basename($document->file_path), $result['file_name']);
        $this->assertStringEndsWith('.pdf', $result['file_name']);
        $this->assertTrue($result['inline'], 'PDFs should open in place.');
    }

    #[Test]
    public function an_employee_cannot_open_someone_elses_document(): void
    {
        $owner = $this->employee('Santos');
        $other = $this->employee('Reyes');
        $document = $this->documentFor($owner);

        $result = $this->resolver->resolve(
            $this->user(['employee'], $other->id),
            'documents',
            (string) $document->id
        );

        $this->assertFalse($result['success']);
        $this->assertSame(403, $result['status']);
    }

    #[Test]
    public function an_employee_can_open_their_own_document(): void
    {
        $owner = $this->employee();
        $document = $this->documentFor($owner);

        $result = $this->resolver->resolve(
            $this->user(['employee'], $owner->id),
            'documents',
            (string) $document->id
        );

        $this->assertTrue($result['success']);
    }

    #[Test]
    public function a_user_with_no_linked_employee_record_is_refused(): void
    {
        $document = $this->documentFor($this->employee());

        $result = $this->resolver->resolve($this->user(['employee']), 'documents', (string) $document->id);

        $this->assertFalse($result['success']);
        $this->assertSame(403, $result['status']);
    }

    #[Test]
    public function an_unknown_source_resolves_to_nothing(): void
    {
        $result = $this->resolver->resolve($this->user(['admin']), 'users', '1');

        $this->assertFalse($result['success']);
        $this->assertSame(404, $result['status']);
    }

    /**
     * The reference is an id, so there is no path for a caller to tamper with.
     * These are the shapes an attacker would try anyway.
     */
    #[Test]
    public function references_that_are_not_row_ids_resolve_to_nothing(): void
    {
        $admin = $this->user(['admin']);

        foreach (['../../.env', 'storage/app/.env', '1;2', 'abc', ''] as $ref) {
            $result = $this->resolver->resolve($admin, 'documents', $ref);
            $this->assertFalse($result['success'], "Reference '{$ref}' should not resolve.");
        }
    }

    #[Test]
    public function a_government_id_scan_column_must_be_one_we_publish(): void
    {
        $employee = $this->employee();
        $path = UploadedFile::fake()->image('gsis.png')->store('employees/government_ids', 'public');

        $govId = GovernmentId::create([
            'employee_id' => $employee->id,
            'gsis_no' => '123',
            'gsis_file_path' => '/storage/' . $path,
        ]);

        $admin = $this->user(['admin']);

        $allowed = $this->resolver->resolve($admin, 'government_ids', $govId->id . '-gsis_file_path');
        $this->assertTrue($allowed['success']);
        $this->assertTrue($allowed['inline'], 'Images should render in place.');

        // Not in GOV_ID_COLUMNS — a real column on the table, but not a scan.
        $denied = $this->resolver->resolve($admin, 'government_ids', $govId->id . '-gsis_no');
        $this->assertFalse($denied['success']);
    }

    /**
     * Photos and ID scans were saved as "/storage/…" URLs while documents were
     * saved as bare disk paths. Both have to resolve.
     */
    #[Test]
    public function stored_storage_urls_and_bare_paths_both_resolve(): void
    {
        $path = UploadedFile::fake()->image('face.jpg')->store('employees/photos', 'public');

        $this->assertSame($path, $this->resolver->toDiskPath('/storage/' . $path));
        $this->assertSame($path, $this->resolver->toDiskPath($path));
        $this->assertSame($path, $this->resolver->toDiskPath('http://localhost/storage/' . $path));
        $this->assertNull($this->resolver->toDiskPath('../../.env'));
    }

    #[Test]
    public function a_row_whose_file_is_missing_from_storage_is_not_served(): void
    {
        $employee = $this->employee();

        $document = Document::create([
            'employee_id' => $employee->id,
            'document_type' => 'Contract',
            'file_path' => 'documents/never-uploaded.pdf',
            'uploaded_at' => now(),
            'status' => 'approved',
        ]);

        $result = $this->resolver->resolve($this->user(['admin']), 'documents', (string) $document->id);

        $this->assertFalse($result['success']);
        $this->assertSame(404, $result['status']);
        $this->assertFalse($this->resolver->existsOnDisk('documents/never-uploaded.pdf'));
    }

    #[Test]
    public function images_are_recognised_and_svg_is_never_inline(): void
    {
        $this->assertTrue($this->resolver->isImage('photo.PNG'));
        $this->assertTrue($this->resolver->isImage('scan.jpeg'));
        $this->assertFalse($this->resolver->isImage('contract.pdf'));
        $this->assertFalse($this->resolver->isImage('logo.svg'));
    }
}
