<?php

namespace Tests\Unit;

use App\Models\Employee;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The employee number is assigned by the model, not typed into the wizard.
 *
 * The wizard used to ask for it — free text, with a PGS-0001 example — so two
 * admins could pick the same number, a letter could land in the middle of it,
 * and the number never said when the employee joined. It is now minted on
 * create as EMP-<year>-<sequence>.
 *
 * These assertions pin the four properties that makes it worth having: the
 * format, that the sequence continues from what is already in the table rather
 * than from a counter, that only this year's numbers count, and that an
 * explicitly supplied number — bulk import, the seeder, existing rows — is
 * never overwritten. The last one matters most: `employees.employee_id` is what
 * the DTR, the payslip and the QR badge identify a person by, so moving one
 * would invalidate a printed badge.
 *
 * No database: the project's migrations cannot run on the test connection (see
 * CLAUDE.md), so only the table these assertions touch is built by hand.
 */
class EmployeeIdGenerationTest extends TestCase
{
    private int $year;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = (int) now()->year;

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable()->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('employees');

        parent::tearDown();
    }

    private function seedNumber(string $employeeId): void
    {
        Employee::create([
            'employee_id' => $employeeId,
            'first_name' => 'Existing',
            'last_name' => 'Employee',
        ]);
    }

    private function makeEmployee(array $attributes = []): Employee
    {
        return Employee::create($attributes + [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);
    }

    #[Test]
    public function a_new_employee_is_assigned_the_first_number_of_the_year(): void
    {
        $employee = $this->makeEmployee();

        $this->assertSame("EMP-{$this->year}-0001", $employee->employee_id);
    }

    /**
     * The format the wizard promises in place of the old input is the format
     * the generator mints. If these two ever drift the screen is lying.
     */
    #[Test]
    public function the_generated_number_matches_the_format_the_wizard_shows(): void
    {
        $this->assertSame("EMP-{$this->year}-xxxx", Employee::employeeIdPreview());

        $employee = $this->makeEmployee();

        $this->assertMatchesRegularExpression('/^EMP-\d{4}-\d{4}$/', $employee->employee_id);
        $this->assertStringStartsWith(
            Employee::employeeIdPrefix(),
            $employee->employee_id,
        );
    }

    #[Test]
    public function each_new_employee_gets_the_next_number_of_the_year(): void
    {
        $this->assertSame("EMP-{$this->year}-0001", $this->makeEmployee()->employee_id);
        $this->assertSame("EMP-{$this->year}-0002", $this->makeEmployee()->employee_id);
        $this->assertSame("EMP-{$this->year}-0003", $this->makeEmployee()->employee_id);
    }

    /**
     * The sequence is read back from the table, so a deleted row leaves a gap
     * the next employee does not re-use a number for — and a number typed in by
     * hand further up the range still counts as issued.
     */
    #[Test]
    public function the_sequence_continues_from_the_highest_number_already_issued(): void
    {
        $this->seedNumber("EMP-{$this->year}-0007");

        $this->assertSame("EMP-{$this->year}-0008", $this->makeEmployee()->employee_id);
    }

    #[Test]
    public function a_retired_number_is_not_handed_out_again(): void
    {
        $first = $this->makeEmployee();
        $this->makeEmployee();

        $first->delete();

        $this->assertSame("EMP-{$this->year}-0003", $this->makeEmployee()->employee_id);
    }

    #[Test]
    public function another_years_numbers_do_not_advance_this_years_sequence(): void
    {
        $this->seedNumber('EMP-' . ($this->year - 1) . '-0042');
        $this->seedNumber('EMP-' . ($this->year + 1) . '-0009');

        $this->assertSame("EMP-{$this->year}-0001", $this->makeEmployee()->employee_id);
    }

    /**
     * A legacy number that merely starts with this year's prefix is not part of
     * the sequence, and reading `0001-A` as 1 would hand the next employee a
     * number they cannot be told apart from it.
     */
    #[Test]
    public function a_non_numeric_suffix_is_not_read_as_a_sequence_position(): void
    {
        $this->seedNumber("EMP-{$this->year}-0001-A");
        $this->seedNumber("EMP-{$this->year}-temp");

        $this->assertSame("EMP-{$this->year}-0001", $this->makeEmployee()->employee_id);
    }

    /**
     * Bulk import and the seeder name their own numbers, and they must keep
     * them — `employee_id` is not the model's to rewrite once it has a value.
     */
    #[Test]
    public function a_supplied_number_is_never_overwritten(): void
    {
        $this->assertSame('PGS-0001', $this->makeEmployee(['employee_id' => 'PGS-0001'])->employee_id);

        // A number from outside this year's range is not part of the sequence,
        // so it does not consume a position: the next employee gets 0001.
        $this->assertSame("EMP-{$this->year}-0001", $this->makeEmployee()->employee_id);

        // One inside the range is a number that has been issued, so it does
        // consume its position — handing the next employee 0042 as well would
        // leave two people with numbers nothing can tell apart.
        $this->assertSame(
            "EMP-{$this->year}-0042",
            $this->makeEmployee(['employee_id' => "EMP-{$this->year}-0042"])->employee_id,
        );
        $this->assertSame("EMP-{$this->year}-0043", $this->makeEmployee()->employee_id);
    }

    #[Test]
    public function saving_an_employee_that_already_has_a_number_leaves_it_alone(): void
    {
        $employee = $this->makeEmployee();

        $employee->update(['first_name' => 'Renamed']);

        $this->assertSame("EMP-{$this->year}-0001", $employee->fresh()->employee_id);
    }
}
