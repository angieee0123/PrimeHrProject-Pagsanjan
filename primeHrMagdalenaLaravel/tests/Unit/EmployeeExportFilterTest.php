<?php

namespace Tests\Unit;

use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\EmployeePayslipController;
use App\Http\Controllers\EmployeeTrainingController;
use App\Models\SalaryComputation;
use App\Models\Training;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * An employee's export carries what their page is showing.
 *
 * All three Export buttons on the employee side used to ignore the toolbar
 * above them. Attendance serialised `detailedRecords` — the array as it was
 * *fetched*, before the View dropdown or the search box touched it. Training
 * linked to an endpoint that took no parameters and always returned every
 * verified record. Payslip had no handler at all.
 *
 * What makes the fix hold is not that each endpoint filters, but that it
 * filters *the same way the page does*. The page decides in JavaScript and the
 * file decides in PHP, so the two rules are written twice and can drift apart
 * — a drift nobody notices, because both halves keep working and only their
 * agreement breaks. These cases pin the agreement.
 *
 * The methods under test are pure — a record in, a decision out — so they are
 * exercised directly rather than through a request, which is what lets this
 * run without the migration set (`RefreshDatabase` cannot run in this
 * project; see CLAUDE.md).
 */
class EmployeeExportFilterTest extends TestCase
{
    private function invokePrivate(object $controller, string $method, ...$args)
    {
        $reflection = new ReflectionMethod($controller, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($controller, ...$args);
    }

    // ── Attendance ────────────────────────────────────────────────────────

    /**
     * One day's record, in the shape `fetchDetailedRecords()` returns.
     */
    private function day(array $overrides = []): array
    {
        return $overrides + [
            'date' => 'Aug 26, 2026',
            'date_key' => '2026-08-26',
            'day' => 'Wednesday',
            'am_in' => '08:00',
            'am_out' => '12:00',
            'pm_in' => '13:00',
            'pm_out' => '17:00',
            'ot_in' => null,
            'ot_out' => null,
            'late_minutes' => 0,
            'undertime' => 0,
            'total_hours' => '8.0 hrs',
            'accredited_minutes' => 480,
            'leave_deduction' => '-',
            'is_on_leave' => false,
            'is_on_travel_order' => false,
        ];
    }

    private function state(array $record): string
    {
        return $this->invokePrivate(new EmployeeAttendanceController(), 'recordState', $record);
    }

    #[Test]
    public function a_day_is_classified_the_way_the_timeline_dot_classifies_it(): void
    {
        $this->assertSame('present', $this->state($this->day()));

        $this->assertSame('late', $this->state($this->day(['late_minutes' => 21])));

        // Some punches but not all four. Incomplete outranks late, the same
        // order renderDetailedDTR() resolves the row's data-state in.
        $this->assertSame('incomplete', $this->state($this->day([
            'pm_out' => null,
            'late_minutes' => 21,
        ])));

        $this->assertSame('absent', $this->state($this->day([
            'am_in' => null, 'am_out' => null, 'pm_in' => null, 'pm_out' => null,
        ])));

        $this->assertSame('weekend', $this->state($this->day([
            'day' => 'Saturday',
            'am_in' => null, 'am_out' => null, 'pm_in' => null, 'pm_out' => null,
        ])));
    }

    #[Test]
    public function an_approved_absence_is_never_read_as_an_absence(): void
    {
        // A leave or travel row carries sentinel strings in place of times. A
        // naive "no punches" test reads those as an unexplained absence, which
        // is the single worst thing this file could say about somebody.
        $leave = $this->day([
            'am_in' => 'ON LEAVE', 'am_out' => 'ON LEAVE',
            'pm_in' => 'ON LEAVE', 'pm_out' => 'ON LEAVE',
            'is_on_leave' => true,
            'leave_info' => ['leave_code' => 'VL', 'leave_type' => 'Vacation Leave', 'days' => 1],
        ]);

        $this->assertSame('leave', $this->state($leave));

        $travel = $this->day([
            'am_in' => 'ON TRAVEL', 'am_out' => 'ON TRAVEL',
            'pm_in' => 'ON TRAVEL', 'pm_out' => 'ON TRAVEL',
            'is_on_travel_order' => true,
            'travel_order_info' => ['order_number' => 'TO-2026-14', 'destination' => 'Santa Cruz', 'duration' => 1],
        ]);

        $this->assertSame('leave', $this->state($travel));
    }

    #[Test]
    public function a_sentinel_is_never_written_into_a_time_column(): void
    {
        $controller = new EmployeeAttendanceController();

        $leave = $this->day([
            'am_in' => 'ON LEAVE', 'am_out' => 'ON LEAVE',
            'pm_in' => 'ON LEAVE', 'pm_out' => 'ON LEAVE',
            'is_on_leave' => true,
        ]);
        $leave['state'] = $this->state($leave);

        $this->assertSame('—', $this->invokePrivate($controller, 'timeCell', $leave, 'am_in'));

        // A working day with no punch on that slot says so, rather than
        // reading as a blank somebody might take for "nothing owed".
        $missing = $this->day(['pm_out' => null]);
        $missing['state'] = $this->state($missing);

        $this->assertSame('Log Missing', $this->invokePrivate($controller, 'timeCell', $missing, 'pm_out'));

        // Overtime is not scheduled, so an empty OT slot is not a missing log.
        $this->assertSame('—', $this->invokePrivate($controller, 'timeCell', $missing, 'ot_in'));
    }

    #[Test]
    public function the_view_dropdown_narrows_the_file_the_way_it_narrows_the_table(): void
    {
        $controller = new EmployeeAttendanceController();

        $monday = $this->day(['day' => 'Monday', 'late_minutes' => 15]);
        $monday['state'] = $this->state($monday);

        $sunday = $this->day([
            'day' => 'Sunday',
            'am_in' => null, 'am_out' => null, 'pm_in' => null, 'pm_out' => null,
        ]);
        $sunday['state'] = $this->state($sunday);

        $matches = fn (array $record, string $view) => $this->invokePrivate($controller, 'matchesView', $record, $view);

        $this->assertTrue($matches($monday, 'all'));
        $this->assertTrue($matches($sunday, 'all'));

        $this->assertTrue($matches($monday, 'mon'));
        $this->assertFalse($matches($sunday, 'mon'));

        $this->assertTrue($matches($monday, 'weekdays'));
        $this->assertFalse($matches($monday, 'weekend'));
        $this->assertTrue($matches($sunday, 'weekend'));

        $this->assertTrue($matches($monday, 'late'));
        $this->assertFalse($matches($monday, 'present'));

        // An unrecognised chip narrows nothing rather than matching nothing —
        // an empty file reads as "you were never at work".
        $this->assertTrue($matches($monday, 'all'));
    }

    #[Test]
    public function overtime_is_counted_the_way_the_kpi_card_counts_it(): void
    {
        $controller = new EmployeeAttendanceController();

        $this->assertSame(90, $this->invokePrivate($controller, 'overtimeMinutes', $this->day([
            'ot_in' => '17:30', 'ot_out' => '19:00',
        ])));

        // One punch alone is not an overtime span.
        $this->assertSame(0, $this->invokePrivate($controller, 'overtimeMinutes', $this->day([
            'ot_in' => '17:30', 'ot_out' => null,
        ])));
    }

    // ── Payslip ───────────────────────────────────────────────────────────

    #[Test]
    public function a_draft_computation_is_pending_on_the_page_and_in_the_file(): void
    {
        $controller = new EmployeePayslipController();

        // `salary_computations.status` defaults to 'draft', and draft means
        // what pending means: payroll has not settled it. The badge used to
        // test for 'pending' alone and label a draft "Processed", which is a
        // false statement about somebody's pay.
        foreach (['draft', 'pending'] as $stored) {
            $this->assertSame(
                'Pending',
                $this->invokePrivate($controller, 'statusLabel', new SalaryComputation(['status' => $stored])),
                "A '{$stored}' computation must read as Pending.",
            );
        }

        foreach (['approved', 'paid'] as $stored) {
            $this->assertSame(
                'Processed',
                $this->invokePrivate($controller, 'statusLabel', new SalaryComputation(['status' => $stored])),
            );
        }
    }

    #[Test]
    public function a_payslip_search_matches_the_period_the_way_the_table_prints_it(): void
    {
        $controller = new EmployeePayslipController();

        $payslip = new SalaryComputation([
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-15',
            'status' => 'paid',
        ]);

        $matches = fn (string $term) => $this->invokePrivate($controller, 'matchesSearch', $payslip, $term);

        $this->assertTrue($matches(''), 'An empty search narrows nothing.');
        $this->assertTrue($matches('Aug 01'), 'The period as the table spells it.');
        $this->assertTrue($matches('processed'), 'The status word the badge shows.');
        $this->assertFalse($matches('pending'));
        $this->assertFalse($matches('December'));
    }

    // ── Training ──────────────────────────────────────────────────────────

    private function training(array $attributes = []): Training
    {
        return new Training($attributes + [
            'title' => 'Records Management Seminar',
            'conducted_by' => 'Civil Service Commission',
            'date_from' => '2026-07-30',
            'date_to' => '2026-08-01',
            'hours' => 24,
            'position_type' => 'Technical',
            'ref_doc_no' => 'CSC-2026-114',
            'status' => 'verified',
        ]);
    }

    #[Test]
    public function a_training_that_straddles_the_range_boundary_stays_in_the_file(): void
    {
        $controller = new EmployeeTrainingController();
        $training = $this->training(); // 30 July – 1 August

        $inRange = fn (string $from, string $to) => $this->invokePrivate($controller, 'inDateRange', $training, $from, $to);

        // The whole point of overlap: a seminar running into August belongs in
        // an August filter, and testing only its start date would drop it
        // under a parameter block that says August is covered.
        $this->assertTrue($inRange('2026-08-01', '2026-08-31'));
        $this->assertTrue($inRange('2026-07-01', '2026-07-31'));
        $this->assertTrue($inRange('', ''), 'No range narrows nothing.');

        $this->assertFalse($inRange('2026-09-01', '2026-09-30'));
        $this->assertFalse($inRange('2026-06-01', '2026-06-30'));
    }

    #[Test]
    public function the_status_chips_and_position_filter_reach_the_file(): void
    {
        $controller = new EmployeeTrainingController();

        $base = ['status' => '', 'position_type' => '', 'date_from' => '', 'date_to' => '', 'search' => ''];
        $matches = fn (Training $t, array $filters) => $this->invokePrivate($controller, 'matchesFilters', $t, $filters + $base);

        $rejected = $this->training(['status' => 'rejected']);

        $this->assertTrue($matches($rejected, []), 'No chip selected exports everything.');
        $this->assertTrue($matches($rejected, ['status' => 'rejected']));
        $this->assertFalse($matches($rejected, ['status' => 'verified']));

        $this->assertTrue($matches($rejected, ['position_type' => 'Technical']));
        $this->assertFalse($matches($rejected, ['position_type' => 'Managerial']));

        $this->assertTrue($matches($rejected, ['search' => 'records management']));
        $this->assertTrue($matches($rejected, ['search' => 'CSC-2026-114']), 'The reference the row prints.');
        $this->assertFalse($matches($rejected, ['search' => 'leadership']));
    }

    #[Test]
    public function only_a_verified_submission_credits_hours_to_the_pds(): void
    {
        $controller = new EmployeeTrainingController();

        // Hours Claimed and Hours Credited are separate columns precisely
        // because these two rows declare the same 24 hours.
        $this->assertSame(24, $this->invokePrivate($controller, 'creditedHours', $this->training()));
        $this->assertSame(0, $this->invokePrivate($controller, 'creditedHours', $this->training(['status' => 'rejected'])));
        $this->assertSame(0, $this->invokePrivate($controller, 'creditedHours', $this->training(['status' => 'pending'])));
    }

    #[Test]
    public function a_day_count_includes_both_ends(): void
    {
        $controller = new EmployeeTrainingController();

        $this->assertSame('3', $this->invokePrivate($controller, 'dayCount', $this->training()));
        $this->assertSame('1', $this->invokePrivate($controller, 'dayCount', $this->training([
            'date_from' => '2026-08-26', 'date_to' => '2026-08-26',
        ])));
    }
}
