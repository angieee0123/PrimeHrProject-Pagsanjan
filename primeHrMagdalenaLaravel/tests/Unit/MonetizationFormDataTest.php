<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\MonetizationRequest;
use App\Models\User;
use App\Services\MonetizationFormDataService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The printed Monetization sheet is a document the municipality signs and
 * files, so what it states about somebody's pay has to be what the system
 * decided — not a second computation done at print time.
 *
 * These pin the properties that make that true: the amount comes off the
 * stored row, the credits are the ones the request was filed against, the
 * preparer is the account that pressed the button rather than the employee
 * being paid, and one employee's URL cannot fetch another's sheet.
 */
class MonetizationFormDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    /**
     * Only the tables these assertions touch, built by hand on the in-memory
     * SQLite connection. The project's own migrations cannot run here —
     * 2026_04_15_182306_add_timestamps_to_tables emits MySQL-only
     * `ON UPDATE CURRENT_TIMESTAMP` — so RefreshDatabase is not an option.
     */
    private function createSchema(): void
    {
        Schema::create('monetization_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->decimal('vl_days', 10, 3)->default(0);
            $table->decimal('sl_days', 10, 3)->default(0);
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->decimal('vl_balance', 10, 3)->nullable();
            $table->decimal('sl_balance', 10, 3)->nullable();
            $table->decimal('computed_amount', 14, 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('approver_remarks')->nullable();
            $table->unsignedBigInteger('filed_by')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->decimal('monthly_rate', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->text('roles')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * The office's own worked example: Driver II, 12,087.00 a month, 75 days
     * monetized against 111.969 VL and 129.00 SL credits.
     */
    private function seedOfficeExample(): MonetizationRequest
    {
        $driver = Employee::create([
            'employee_id' => 'PGS-0115',
            'first_name' => 'Jay',
            'last_name' => 'Almontero',
        ]);

        $designation = \App\Models\Designation::create([
            'title' => 'Driver II',
            'monthly_rate' => 12087.00,
        ]);

        $department = \App\Models\Department::create(['name' => 'General Services']);

        \App\Models\EmploymentDetail::create([
            'employee_id' => $driver->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
        ]);

        $request = new MonetizationRequest([
            'employee_id' => $driver->id,
            'vl_days' => 75,
            'sl_days' => 0,
            'monthly_salary' => 12087.00,
            'vl_balance' => 111.969,
            'sl_balance' => 129.000,
            'status' => 'approved',
        ]);
        $request->computed_amount = $request->computeAmount();
        $request->save();

        return $request;
    }

    #[Test]
    public function prints_the_stored_amount_rather_than_recomputing_it(): void
    {
        $request = $this->seedOfficeExample();

        // The sheet must follow the row, not the formula: overwrite the stored
        // amount and the printed total moves with it. A form that recomputed
        // would keep printing 43,687.89 and disagree with every screen in the
        // system that reads this column.
        $request->forceFill(['computed_amount' => 40000.00])->save();

        $data = (new MonetizationFormDataService)->build($request->id);

        $this->assertSame('40,000.00', $data['amount']);
    }

    #[Test]
    public function traces_the_office_worked_example(): void
    {
        $request = $this->seedOfficeExample();

        $data = (new MonetizationFormDataService)->build($request->id);

        $this->assertSame('JAY ALMONTERO', $data['employeeName']);
        $this->assertSame('Driver II', $data['position']);
        $this->assertSame('12,087.00', $data['salary']);

        // Credits print at the precision they are stored with — 111.969 days
        // is not 112 — and the trailing zero of a three-decimal whole is
        // dropped, the way the office's sheet writes "129.00".
        $this->assertSame('111.969', $data['vacationCredits']);
        $this->assertSame('129.00', $data['sickCredits']);
        $this->assertSame('240.969', $data['totalCredits']);

        // D is the days being monetized, which is what the amount was
        // computed from — not the total credits on the line above it.
        $this->assertSame('75', $data['monetizedDays']);
        $this->assertSame('0.0481927', $data['constantFactor']);
        $this->assertSame('906,525.00', $data['salaryTimesDays']);
        $this->assertSame('43,687.89', $data['amount']);
    }

    #[Test]
    public function credits_are_the_balances_at_filing_not_todays(): void
    {
        $request = $this->seedOfficeExample();

        // The approval has since taken the monetized days out of the live
        // balance. The sheet says "as of <filed date>", so it must keep
        // printing the figures the computation was made against.
        $data = (new MonetizationFormDataService)->build($request->id);

        $this->assertSame('111.969', $data['vacationCredits']);
        $this->assertSame(
            $request->created_at->format('F j, Y'),
            $data['creditsAsOf'],
            'The "as of" date is when the balances were read, never today.'
        );
    }

    #[Test]
    public function prepared_by_is_the_generator_not_the_employee(): void
    {
        $request = $this->seedOfficeExample();

        $hrEmployee = Employee::create([
            'employee_id' => 'PGS-0002',
            'first_name' => 'Kevin',
            'middle_name' => 'Mar',
            'last_name' => 'Moreno',
        ]);

        $designation = \App\Models\Designation::create(['title' => 'Admin Aide III']);

        \App\Models\EmploymentDetail::create([
            'employee_id' => $hrEmployee->id,
            'designation_id' => $designation->id,
        ]);

        $hrUser = User::create([
            'name' => 'kmoreno',
            'username' => 'kmoreno',
            'email' => 'kmoreno@example.test',
            'password' => 'x',
            'employee_id' => $hrEmployee->id,
        ]);

        $data = (new MonetizationFormDataService)->build($request->id, $hrUser);

        $this->assertSame('KEVIN M. MORENO', $data['preparedBy']['name']);
        $this->assertSame('Admin Aide III', $data['preparedBy']['title']);

        // The recipient is untouched by who printed the sheet.
        $this->assertSame('JAY ALMONTERO', $data['employeeName']);
    }

    #[Test]
    public function a_generator_with_no_real_name_leaves_the_line_blank(): void
    {
        $request = $this->seedOfficeExample();

        // "admin" is an account, not a person who prepared anything, and a
        // form is read as prepared the moment a name appears over that line.
        $anonymous = User::create([
            'name' => 'admin',
            'username' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'x',
        ]);

        $data = (new MonetizationFormDataService)->build($request->id, $anonymous);

        $this->assertSame('', $data['preparedBy']['name']);
        $this->assertSame('', $data['preparedBy']['title']);
    }

    #[Test]
    public function an_employee_cannot_build_another_employees_sheet(): void
    {
        $request = $this->seedOfficeExample();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        (new MonetizationFormDataService)->build(
            $request->id,
            null,
            $request->employee_id + 1
        );
    }
}
