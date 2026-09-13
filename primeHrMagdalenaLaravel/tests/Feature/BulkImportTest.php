<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Bulk import no longer needs an `employee_id` column.
 *
 * The download template used to carry one with `EMP-2024-001` as the sample, so
 * every import was an invitation to invent employee numbers — the number the
 * QR badge, the DTR and the payslip all print. It is now assigned on create as
 * EMP-<year>-<sequence> (Employee::booted()), and the file may leave it out.
 *
 * Two properties keep that from being a one-way door, and both are pinned here:
 * a file that *does* carry the column keeps the numbers it supplies — the
 * docs/bulk_import_parts/ migration files carry real municipal numbers — and a
 * repeat upload is still refused rather than duplicated, now matched on the
 * email when there is no number to match on.
 *
 * No RefreshDatabase: the project's migrations cannot run on the test
 * connection (see CLAUDE.md), so the tables this path touches are built by
 * hand. They are the ones one imported row writes: the employee, the account,
 * the department/designation it was filed under, and the address, contact and
 * government-ID rows beside them.
 */
class BulkImportTest extends TestCase
{
    private int $year;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = (int) now()->year;

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        foreach ([
            'government_ids', 'contacts', 'addresses', 'employment_details',
            'designations', 'departments', 'users', 'employees',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    private function createSchema(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable()->unique();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('photo')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('sex')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('username')->nullable()->unique();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->text('roles')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('head')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('monthly_rate')->nullable();
            $table->string('employment_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('employment_status')->nullable();
            $table->date('appointment_date')->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('step_increment')->nullable();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('type')->nullable();
            $table->string('house_no')->nullable();
            $table->string('street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('type')->nullable();
            $table->string('number')->nullable();
            $table->string('contact_person')->nullable();
        });

        Schema::create('government_ids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();

            foreach (['gsis', 'philhealth', 'pagibig', 'tin', 'license'] as $id) {
                $table->string("{$id}_no")->nullable();
                $table->string("{$id}_file_path")->nullable();
            }
        });
    }

    /** The admin running the import. Nothing here depends on their roles — the route is `auth` only. */
    private function admin(): User
    {
        $user = User::create([
            'name' => 'HR Admin',
            'email' => 'admin@example.test',
            'username' => 'hradmin',
            'password' => 'x',
            'roles' => ['admin'],
            'status' => 'Active',
        ]);

        // Every /admin path sits behind the app-wide EnsureUserIsActive →
        // EnsureRoleForArea → EnsureEmailIsVerifiedForArea chain (bootstrap/app.php).
        // `email_verified_at` is not fillable, so an unverified admin is
        // redirected to the verification notice instead of reaching the import.
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function import(string $csv): TestResponse
    {
        return $this->actingAs($this->admin())->post('/admin/personnel/bulk-import', [
            'csv_file' => UploadedFile::fake()->createWithContent('employees.csv', $csv),
        ]);
    }

    #[Test]
    public function a_template_without_an_employee_id_column_imports_and_the_numbers_are_generated(): void
    {
        // Exactly the columns downloadTemplate() now writes.
        $response = $this->import(
            "first_name,middle_name,last_name,department,designation,email\n"
            . "Juan,,Dela Cruz,Administration,Administrative Officer II,juan@example.test\n"
            . "Maria,,Santos,Administration,Administrative Officer II,maria@example.test\n"
        );

        $response->assertOk()->assertJson(['success' => true, 'imported' => 2, 'skipped' => 0]);

        $this->assertSame(2, Employee::count());
        $this->assertSame(
            "EMP-{$this->year}-0001",
            Employee::where('first_name', 'Juan')->value('employee_id'),
        );
        $this->assertSame(
            "EMP-{$this->year}-0002",
            Employee::where('first_name', 'Maria')->value('employee_id'),
        );
    }

    /**
     * The column is optional, not forbidden: an old template (or a spreadsheet
     * with a blank cell) must still import, with the blank filled in.
     */
    #[Test]
    public function a_blank_cell_is_filled_in_even_when_the_column_is_present(): void
    {
        $response = $this->import(
            "employee_id,first_name,last_name,department,designation,email\n"
            . ",Pedro,Reyes,Administration,Clerk,pedro@example.test\n"
            . "EMP-PGS-0009,Ana,Reyes,Administration,Clerk,ana@example.test\n"
        );

        $response->assertOk()->assertJson(['imported' => 2, 'skipped' => 0]);

        $this->assertSame(
            "EMP-{$this->year}-0001",
            Employee::where('first_name', 'Pedro')->value('employee_id'),
        );
        // The number the file supplied is a real one and is kept as it stands —
        // it is what that employee's badge and payslip already print.
        $this->assertSame(
            'EMP-PGS-0009',
            Employee::where('first_name', 'Ana')->value('employee_id'),
        );
    }

    /**
     * The regression this replaces: a row whose ID cell was missing rather than
     * blank used to fall through to `'' . '@lgu.gov.ph'` for its account
     * address, so `users.email` being UNIQUE failed every row after the first.
     */
    #[Test]
    public function a_row_with_no_email_still_gets_its_own_account_address(): void
    {
        $response = $this->import(
            "first_name,last_name,department,designation\n"
            . "Pedro,Reyes,Administration,Clerk\n"
            . "Ana,Reyes,Administration,Clerk\n"
        );

        $response->assertOk()->assertJson(['imported' => 2, 'skipped' => 0]);

        $this->assertSame(
            [
                "EMP-{$this->year}-0001@lgu.gov.ph",
                "EMP-{$this->year}-0002@lgu.gov.ph",
            ],
            User::whereIn('username', ['reyespedro', 'reyesana'])
                ->orderBy('id')
                ->pluck('email')
                ->all(),
        );
    }

    #[Test]
    public function a_row_that_is_already_on_record_is_reported_rather_than_imported(): void
    {
        // Already on record, with no employee number to match on in the file —
        // which is the case a template without the column creates.
        Employee::create([
            'employee_id' => "EMP-{$this->year}-0001",
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.test',
        ]);

        $response = $this->import(
            "first_name,last_name,department,designation,email\n"
            . "Juan,Dela Cruz,Administration,Clerk,juan@example.test\n"
        );

        $response->assertOk()->assertJson(['success' => true, 'imported' => 0, 'skipped' => 1]);

        $duplicate = $response->json('duplicates.0');
        $this->assertSame('juan@example.test', $duplicate['email']);
        $this->assertSame('', $duplicate['employee_id']);
        $this->assertSame('Juan Dela Cruz', $duplicate['name']);

        // Reported is not imported: the row was not written a second time.
        $this->assertSame(1, Employee::count());
    }

    #[Test]
    public function a_duplicate_number_is_reported_by_that_number(): void
    {
        Employee::create([
            'employee_id' => 'EMP-PGS-0001',
            'first_name' => 'Pedro',
            'last_name' => 'Reyes',
        ]);

        $response = $this->import(
            "employee_id,first_name,last_name,department,designation,email\n"
            . "EMP-PGS-0001,Pedro,Reyes,Administration,Clerk,pedro@example.test\n"
        );

        $response->assertOk()->assertJson(['imported' => 0, 'skipped' => 1]);
        $this->assertSame('EMP-PGS-0001', $response->json('duplicates.0.employee_id'));
        $this->assertSame('', $response->json('duplicates.0.email'));
    }

    /**
     * Dropping one required column must not drop the others with it: a file
     * without a first name is still refused rather than stored blank.
     */
    #[Test]
    public function the_other_required_fields_are_still_required(): void
    {
        $response = $this->import(
            "first_name,last_name,department,designation\n"
            . ",Reyes,Administration,Clerk\n"
        );

        $response->assertOk()->assertJson(['success' => true, 'imported' => 0, 'skipped' => 1]);
        $this->assertStringContainsString(
            'Missing required field(s): first_name',
            $response->json('errors.0'),
        );
        $this->assertSame(0, Employee::count());
    }

    /**
     * A header row that omits the column entirely is not a column-count
     * mismatch: every row still matches the header it was read against.
     */
    #[Test]
    public function a_file_that_omits_the_column_entirely_is_not_a_column_count_mismatch(): void
    {
        $response = $this->import(
            "first_name,last_name,department,designation,email\n"
            . "Juan,Dela Cruz,Administration,Clerk,juan@example.test\n"
        );

        $response->assertOk()->assertJson(['imported' => 1, 'skipped' => 0]);
        $this->assertSame([], $response->json('errors'));
    }

    /**
     * The success modal used to claim, for any import that created an employee,
     * that each one "was emailed a verification link and, separately, their
     * username and password" — with no idea whether that was true. The response
     * now carries the addresses, so the claim can be checked against reality.
     */
    #[Test]
    public function the_response_names_the_addresses_the_credentials_email_went_to(): void
    {
        // The test connection sends through the array transport (phpunit.xml),
        // so the mailables are really rendered and handed over, just not
        // delivered anywhere.
        $response = $this->import(
            "first_name,last_name,department,designation,email\n"
            . "Juan,Dela Cruz,Administration,Clerk,juan@example.test\n"
            . "Maria,Santos,Administration,Clerk,maria@example.test\n"
        );

        $response->assertOk()->assertJson(['imported' => 2]);

        $this->assertSame(
            ['juan@example.test', 'maria@example.test'],
            $response->json('emails.sent'),
        );
        $this->assertSame([], $response->json('emails.failed'));
        $this->assertSame([], $response->json('emails.not_attempted'));
    }

    /**
     * A mail failure must not read as a success, and must not be lost when the
     * modal closes: the response says which address, and the log keeps why.
     *
     * The mailer is pointed at a closed port rather than mocked, so the failure
     * travels the real path — SMTP transport, TransportException, handler.
     */
    #[Test]
    public function a_refused_send_is_reported_and_logged(): void
    {
        Log::spy();

        config([
            'mail.default' => 'unreachable',
            // Port 1 on the loopback interface refuses immediately, so this
            // fails as a refusal rather than waiting out the socket timeout.
            'mail.mailers.unreachable' => [
                'transport' => 'smtp',
                'host' => '127.0.0.1',
                'port' => 1,
                'timeout' => 2,
            ],
        ]);

        $response = $this->import(
            "first_name,last_name,department,designation,email\n"
            . "Juan,Dela Cruz,Administration,Clerk,juan@example.test\n"
        );

        // The import itself still succeeded: the rows are durable and the
        // admin's next action depends on knowing that.
        $response->assertOk()->assertJson(['success' => true, 'imported' => 1]);
        $this->assertSame(1, Employee::count());

        $this->assertSame([], $response->json('emails.sent'));

        // Both messages for that account are reported, not just the first.
        $failures = $response->json('emails.failed');
        $this->assertCount(2, $failures);
        $this->assertSame(['juan@example.test', 'juan@example.test'], array_column($failures, 'email'));
        $this->assertSame(['verification link', 'username and password'], array_column($failures, 'kind'));
        $this->assertNotEmpty($failures[1]['reason']);

        // And the reason outlives the modal: this is what an admin comes back
        // asking about days later.
        Log::shouldHaveReceived('error')->withArgs(function ($message, $context = []) {
            return $message === 'Bulk import: employee email failed'
                && ($context['email'] ?? null) === 'juan@example.test'
                && ($context['email_kind'] ?? null) === 'username and password';
        });
    }

    /**
     * A relay that is down fails every send, and each attempt costs the socket
     * timeout. Three accounts is enough to conclude it is unreachable; the rest
     * are reported as not attempted instead of silently burning the request's
     * whole time budget.
     */
    #[Test]
    public function a_dead_mail_server_does_not_burn_through_every_account(): void
    {
        config([
            'mail.default' => 'unreachable',
            'mail.mailers.unreachable' => [
                'transport' => 'smtp',
                'host' => '127.0.0.1',
                'port' => 1,
                'timeout' => 2,
            ],
        ]);

        $rows = '';
        foreach (['a', 'b', 'c', 'd', 'e'] as $initial) {
            $rows .= "{$initial},Reyes,Administration,Clerk,{$initial}@example.test\n";
        }

        $response = $this->import(
            "first_name,last_name,department,designation,email\n" . $rows
        );

        $response->assertOk()->assertJson(['imported' => 5]);

        // Two messages for each of the first three accounts, then it stops.
        $this->assertCount(6, $response->json('emails.failed'));

        $this->assertSame(
            ['d@example.test', 'e@example.test'],
            $response->json('emails.not_attempted'),
        );
    }
}
