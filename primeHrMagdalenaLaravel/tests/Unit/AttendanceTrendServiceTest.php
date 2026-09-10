<?php

namespace Tests\Unit;

use App\Services\AttendanceComputationService;
use App\Services\AttendanceTrendService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsAttendanceSchema;
use Tests\TestCase;

/**
 * The Attendance Trend card can be pointed at any week, month or year, so the
 * arithmetic behind it has to hold for a period that is not "now" — which is
 * exactly what nothing tested while the chart was hard-wired to the last seven,
 * thirty and three hundred and sixty-five days.
 *
 * The definitions pinned here are the ones the rest of the system already uses
 * (see the class docblock); changing one should have to change a case below.
 */
class AttendanceTrendServiceTest extends TestCase
{
    use BuildsAttendanceSchema;

    private AttendanceTrendService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttendanceSchema();
        $this->createLeaveAndTravelSchema();

        // A Thursday. Freezing "today" is what makes the future-day rules —
        // which are half of what this service decides — assertable at all.
        Carbon::setTestNow(Carbon::parse('2026-09-10 09:00:00'));

        $this->service = new AttendanceTrendService();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** The two approval tables the expected roster is read from. */
    private function createLeaveAndTravelSchema(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('pending');
        });

        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('travel_date');
            $table->date('return_date');
            $table->string('status')->default('pending');
        });
    }

    private function employees(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            DB::table('employees')->insert(['id' => $i, 'first_name' => 'Employee', 'last_name' => (string) $i]);
        }
    }

    /** A punch row. `$amIn` null is an encoded day nobody clocked in on. */
    private function punch(int $employeeId, string $date, ?string $amIn): void
    {
        DB::table('attendance')->insert([
            'employee_id' => $employeeId,
            'date'        => $date,
            'am_in'       => $amIn,
        ]);
    }

    private function dayOf(array $series, string $label): int|string
    {
        return array_search($label, $series['labels'], true);
    }

    #[Test]
    public function the_three_rates_are_percentages_of_the_expected_roster(): void
    {
        $this->employees(4);

        // Tue 1 Sep 2026: three clocked in (two of them late), one did not.
        $this->punch(1, '2026-09-01', '07:50');
        $this->punch(2, '2026-09-01', '08:30');
        $this->punch(3, '2026-09-01', '09:00');
        $this->punch(4, '2026-09-01', null);

        $series = $this->service->series('month', '2026-09-01');
        $index  = $this->dayOf($series, '1');

        $this->assertSame(75.0, $series['data'][$index]);        // 3 of 4
        $this->assertSame(50.0, $series['lateData'][$index]);    // 2 of 4
        $this->assertSame(25.0, $series['absentData'][$index]);  // 1 of 4
    }

    #[Test]
    public function lateness_is_measured_against_the_employees_own_schedule(): void
    {
        $this->employees(2);

        // Employee 1 works 07:00-16:00, so 07:20 is fifteen minutes late. The
        // flat 08:05 cut-off this replaced would have called it early.
        DB::table('schedules')->insert([
            'employee_id' => 1,
            'start_date'  => '2026-01-01',
            'end_date'    => '2026-12-31',
            'am_in'       => '07:00:00',
        ]);

        $this->punch(1, '2026-09-01', '07:20');
        $this->punch(2, '2026-09-01', '07:20'); // no schedule: 08:00 + grace

        $series = $this->service->series('month', '2026-09-01');
        $index  = $this->dayOf($series, '1');

        $this->assertSame(50.0, $series['lateData'][$index]);
        $this->assertSame(100.0, $series['data'][$index]); // both clocked in
    }

    #[Test]
    public function the_grace_period_is_the_one_the_computation_service_owns(): void
    {
        $this->employees(1);
        $this->punch(1, '2026-09-01', '08:00');

        // On the boundary: 08:00 + GRACE. Late only past it.
        $atGrace = Carbon::parse('08:00')->addMinutes(AttendanceComputationService::GRACE_MINUTES)->format('H:i');
        $this->punch(1, '2026-09-02', $atGrace);

        $series = $this->service->series('month', '2026-09-01');

        $this->assertSame(0.0, $series['lateData'][$this->dayOf($series, '1')]);
        $this->assertSame(0.0, $series['lateData'][$this->dayOf($series, '2')]);
    }

    #[Test]
    public function approved_leave_and_travel_are_taken_out_of_the_roster_but_pending_ones_are_not(): void
    {
        $this->employees(4);

        // Everyone is in, but two of the four are accounted for elsewhere.
        foreach ([1, 2, 3, 4] as $id) {
            $this->punch($id, '2026-09-01', '07:50');
        }

        DB::table('leave_applications')->insert([
            'employee_id' => 3, 'start_date' => '2026-09-01', 'end_date' => '2026-09-02', 'status' => 'approved',
        ]);
        DB::table('travel_orders')->insert([
            'employee_id' => 4, 'travel_date' => '2026-09-01', 'return_date' => '2026-09-01', 'status' => 'approved',
        ]);
        DB::table('leave_applications')->insert([
            'employee_id' => 2, 'start_date' => '2026-09-01', 'end_date' => '2026-09-01', 'status' => 'pending',
        ]);

        $series = $this->service->series('month', '2026-09-01');
        $index  = $this->dayOf($series, '1');

        // Four present, two expected away: the rate is 100, not 50.
        $this->assertSame(100.0, $series['data'][$index]);
        $this->assertSame(0.0, $series['absentData'][$index]);
    }

    #[Test]
    public function weekends_are_not_charted(): void
    {
        $this->employees(1);
        $this->punch(1, '2026-09-05', '07:50'); // Saturday

        $series = $this->service->series('week', '2026-09-10'); // Mon 7 - Sun 13

        $this->assertSame(['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], $series['labels']);
    }

    #[Test]
    public function a_working_day_with_no_attendance_rows_is_a_gap_not_a_zero(): void
    {
        $this->employees(2);
        $this->punch(1, '2026-09-01', '07:50');

        $series = $this->service->series('month', '2026-09-01');

        $this->assertSame(50.0, $series['data'][$this->dayOf($series, '1')]); // one of two
        // Nothing was encoded for 2 September: a gap, not "everybody absent".
        $this->assertNull($series['data'][$this->dayOf($series, '2')]);
        $this->assertNull($series['absentData'][$this->dayOf($series, '2')]);
    }

    #[Test]
    public function days_after_today_are_not_charted_even_when_rows_exist(): void
    {
        $this->employees(2);
        $this->punch(1, '2026-09-11', '07:50'); // tomorrow, already in the table

        $series = $this->service->series('week', '2026-09-10');
        $index  = $this->dayOf($series, 'Fri');

        $this->assertNull($series['data'][$index]);
        $this->assertNull($series['lateData'][$index]);
    }

    #[Test]
    public function a_period_with_nothing_in_it_says_so(): void
    {
        $this->employees(2);

        $series = $this->service->series('month', '2024-09-15');

        $this->assertFalse($series['has_data']);
        $this->assertFalse($series['in_future']);
        $this->assertSame('September 2024', $series['label']);
        $this->assertSame([null], array_values(array_unique($series['data'])));
    }

    #[Test]
    public function a_period_that_has_not_happened_yet_is_flagged_and_cannot_be_stepped_into(): void
    {
        $this->employees(2);

        $series = $this->service->series('month', '2030-01-01');

        $this->assertFalse($series['has_data']);
        $this->assertTrue($series['in_future']);
        $this->assertFalse($series['can_step_next']);
    }

    #[Test]
    public function the_current_period_cannot_be_stepped_past(): void
    {
        $this->employees(2);

        $this->assertFalse($this->service->series('month', '2026-09-10')['can_step_next']);
        $this->assertFalse($this->service->series('week', '2026-09-10')['can_step_next']);
        $this->assertTrue($this->service->series('month', '2026-08-10')['can_step_next']);
    }

    #[Test]
    public function the_year_view_averages_the_months_working_days(): void
    {
        $this->employees(2);
        $this->punch(1, '2026-01-05', '07:50');
        $this->punch(2, '2026-01-05', '07:50');
        $this->punch(1, '2026-01-06', '07:50'); // one of two: 50%

        $series = $this->service->series('year', '2026-06-01');

        // (100 + 50) / 2 — an average of daily rates, on the same 0-100 scale
        // as the week and month views.
        $this->assertSame(75.0, $series['data'][0]);
        // February was never encoded, and October has not happened.
        $this->assertNull($series['data'][1]);
        $this->assertNull($series['data'][9]);
    }

    #[Test]
    public function an_unknown_period_falls_back_to_the_month_and_an_unreadable_date_to_today(): void
    {
        $this->employees(1);

        $series = $this->service->series('fortnightly', 'not-a-date');

        $this->assertSame('month', $series['period']);
        $this->assertSame('2026-09-01', $series['start']);
        $this->assertSame('2026-09-30', $series['end']);
        $this->assertSame('2026-09-10', $series['anchor']);
    }

    #[Test]
    public function the_range_is_the_whole_bucket_the_anchor_sits_in(): void
    {
        $week = $this->service->series('week', '2026-09-10');   // Thu
        $this->assertSame('2026-09-07', $week['start']);        // Monday
        $this->assertSame('2026-09-13', $week['end']);          // Sunday

        $month = $this->service->series('month', '2026-02-14');
        $this->assertSame('2026-02-01', $month['start']);
        $this->assertSame('2026-02-28', $month['end']);

        $year = $this->service->series('year', '2026-06-01');
        $this->assertSame('2026-01-01', $year['start']);
        $this->assertSame('2026-12-31', $year['end']);
    }
}
