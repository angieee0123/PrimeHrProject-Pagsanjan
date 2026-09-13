<?php

namespace Database\Seeders;

use App\Models\AttendanceExemption;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Attendance exemptions for the roster's genuinely field-based work: two
 * employee rows with different configurations, and one department row.
 *
 * `AttendanceExemption::getActiveExemption()` resolves an employee first, then
 * their department, then their designation, so an employee row always shadows a
 * department row for the same person. The department row is therefore not a
 * stand-in for either employee row: it covers the rest of General Services, and
 * it carries flags identical to the employee row for the same office on purpose
 * — if the two ever disagreed, which configuration applied to that office would
 * depend on which branch of the lookup happened to answer.
 *
 * An exemption changes only how a day is *read*. The `attendance` table keeps
 * the punches that were actually scanned; the not-required flags and the
 * schedule-based auto-fill are applied on the way out, which is what the
 * attendance screen's abandoned and incomplete warnings and the DTR display
 * consume. Nothing here writes to `attendance` — the attendance seeders own that
 * table.
 *
 * The two employee configurations differ because one shape cannot describe both
 * kinds of field work:
 *
 * - Supply and property errands (General Services). The morning arrival and the
 *   afternoon departure are scanned at the office; the noon break is spent on the
 *   road, so AM OUT and PM IN are the two punches that go missing. Both are
 *   marked not required and both auto-fill switches are left on, so the DTR
 *   reconstructs a whole day from the scans that exist.
 * - Disaster response (MDRRM). The shift starts at a site rather than at a desk,
 *   so the morning pair is not required and is reconstructed from the schedule;
 *   the afternoon is worked at the operations centre, where PM IN and PM OUT are
 *   really scanned. `auto_fill_pm_in` is off here so that captured afternoon scan
 *   is what the DTR shows — left on, the schedule's default would replace it and
 *   quietly hide a late return from the field.
 *
 * The disaster-response pick is a Job Order, and the row says so: the roster's
 * field offices hold no plantilla staff, and a daily-rate employee is paid for
 * the days worked, so the exemption is a statement about where the day is worked
 * rather than about the hours that can be scanned.
 */
class PagsanjanAttendanceExemptionSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /**
     * Effectivity: the 2026 calendar year, the same window the roster's work
     * schedules cover. The dates are written rather than left null because a
     * null/null row is active forever and leaves nothing to review — an
     * employee reassigned out of field work would keep the exemption silently.
     */
    private const EFFECTIVE_FROM = '2026-01-01';

    private const EFFECTIVE_TO = '2026-12-31';

    /** The office whose errands put staff on the road across the noon break. */
    private const ERRAND_OFFICE = 'GSO';

    /** Offices whose work begins at a site instead of at a desk. */
    private const RESPONSE_OFFICES = ['MDRRM', 'AGRI'];

    public function run(): void
    {
        $this->seedRandomness();

        $roster = $this->rosterEmployees();

        $errandDepartmentId = $this->departmentIdForOffice(self::ERRAND_OFFICE);
        $errandEmployee = $this->firstRosterEmployee($roster, [$errandDepartmentId], 'Permanent');

        $responseDepartmentIds = array_map(
            fn (string $office) => $this->departmentIdForOffice($office),
            self::RESPONSE_OFFICES
        );
        $responseEmployee = $this->firstRosterEmployee($roster, $responseDepartmentIds);

        if (! $errandEmployee || ! $responseEmployee) {
            throw new \RuntimeException(
                'The Pagsanjan roster is missing the field personnel these exemptions describe — run PagsanjanEmployeeSeeder first.'
            );
        }

        $createdBy = User::whereJsonContains('roles', 'admin')->value('id');

        if (! $createdBy) {
            $this->command->warn('No admin user found; the exemptions are written with created_by = null.');
        }

        $rows = [
            [
                'exemption_type' => 'employee',
                'reference_id' => $errandEmployee->id,
                'reference_name' => $this->screensName($errandEmployee),
                'exempt_from_abandoned' => true,
                'exempt_from_incomplete' => true,
                'reason' => 'Runs supply and property deliveries for the municipal offices and barangays; the noon '
                    . 'break is spent on the road, so the AM OUT and PM IN scans are not captured.',
                'start_date' => self::EFFECTIVE_FROM,
                'end_date' => self::EFFECTIVE_TO,
                'am_in_not_required' => false,
                'am_out_not_required' => true,
                'pm_in_not_required' => true,
                'pm_out_not_required' => false,
                'auto_fill_am_out' => true,
                'auto_fill_pm_in' => true,
            ],
            [
                'exemption_type' => 'employee',
                'reference_id' => $responseEmployee->id,
                'reference_name' => $this->screensName($responseEmployee),
                'exempt_from_abandoned' => true,
                'exempt_from_incomplete' => true,
                'reason' => 'Disaster-response duty takes this post to flood-prone barangays at the start of the shift '
                    . 'and back to the MDRRM operations centre in the afternoon; it is a Job Order paid the PHP '
                    . number_format(self::JOB_ORDER_DAILY_RATE, 2)
                    . ' daily rate for days worked, not for hours scanned.',
                'start_date' => self::EFFECTIVE_FROM,
                'end_date' => self::EFFECTIVE_TO,
                'am_in_not_required' => true,
                'am_out_not_required' => true,
                'pm_in_not_required' => false,
                'pm_out_not_required' => false,
                'auto_fill_am_out' => true,
                'auto_fill_pm_in' => false,
            ],
            [
                'exemption_type' => 'department',
                'reference_id' => $errandDepartmentId,
                'reference_name' => (string) Department::where('id', $errandDepartmentId)->value('name'),
                'exempt_from_abandoned' => true,
                'exempt_from_incomplete' => true,
                'reason' => 'General Services errands — supply delivery, motor pool and building maintenance — run '
                    . 'through the noon break, so the office is away from the scanner for the two middle punches.',
                'start_date' => self::EFFECTIVE_FROM,
                'end_date' => self::EFFECTIVE_TO,
                'am_in_not_required' => false,
                'am_out_not_required' => true,
                'pm_in_not_required' => true,
                'pm_out_not_required' => false,
                'auto_fill_am_out' => true,
                'auto_fill_pm_in' => true,
            ],
        ];

        DB::transaction(function () use ($rows, $createdBy) {
            $this->deleteOwnedRows($rows);

            foreach ($rows as $row) {
                AttendanceExemption::create(array_merge($row, ['created_by' => $createdBy]));
            }
        });

        $this->command->info(sprintf(
            'Attendance exemptions: %s (employee, AM OUT + PM IN not required, both flags cleared, auto-fill on), '
            . '%s (employee, AM IN + AM OUT not required, PM IN auto-fill off), %s (department, same errand flags).',
            $rows[0]['reference_name'],
            $rows[1]['reference_name'],
            $rows[2]['reference_name']
        ));
    }

    /**
     * The first roster employee filed under one of the given departments,
     * optionally narrowed to a single employment status.
     *
     * Roster order is the tiebreak — never a random draw and never the order the
     * database hands rows back in. A department filter on its own puts every
     * General Services employee in the running, and re-running this seeder has to
     * exempt the same person.
     *
     * @param  array<int, Employee>  $roster  keyed by employees.id, in roster order
     * @param  array<int, int>  $departmentIds
     */
    private function firstRosterEmployee(array $roster, array $departmentIds, ?string $status = null): ?Employee
    {
        foreach ($roster as $employee) {
            $detail = $this->employmentDetailFor($employee->id);

            if (! $detail || ! in_array((int) $detail->department_id, $departmentIds, true)) {
                continue;
            }

            if ($status !== null && $detail->employment_status !== $status) {
                continue;
            }

            return $employee;
        }

        return null;
    }

    /** The name the screens print: `First M. Last`, with the suffix when there is one. */
    private function screensName(Employee $employee): string
    {
        $middle = $employee->middle_name ? ' ' . mb_substr($employee->middle_name, 0, 1) . '.' : '';

        return trim(
            $employee->first_name . $middle . ' ' . $employee->last_name
            . ($employee->suffix ? ' ' . $employee->suffix : '')
        );
    }

    /**
     * Remove this seeder's own rows before rewriting them.
     *
     * Scoped to the (exemption_type, reference_id) pairs it is about to write, so
     * an exemption a human configured for anybody or anything else survives a
     * re-seed. Unscoped, the delete would make this seeder the owner of the whole
     * table.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function deleteOwnedRows(array $rows): void
    {
        AttendanceExemption::where(function ($query) use ($rows) {
            foreach ($rows as $row) {
                $query->orWhere(function ($scoped) use ($row) {
                    $scoped->where('exemption_type', $row['exemption_type'])
                        ->where('reference_id', $row['reference_id']);
                });
            }
        })->delete();
    }
}
