<?php

namespace Database\Seeders;

use App\Models\AccreditedHoursLog;
use App\Models\Attendance;
use App\Models\DailySalaryComputation;
use App\Models\LeaveApplication;
use App\Models\Schedule;
use App\Models\TravelOrder;
use App\Services\AttendanceComputationService;
use App\Services\CscTimeConversionService;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The DTR for the thirty Pagsanjan personnel, January to September 2026.
 *
 * Three tables are written together and must agree: `attendance` (the register
 * the DTR and the dashboards read), `accredited_hours_log` (the minutes, which
 * is what pay is actually computed from) and `daily_salary_computations` (the
 * peso figure). `JulyAttendanceSeeder` established that shape; this seeder
 * follows it, but reads its subjects through the roster trait so the register
 * cannot disagree with the roster about who is in the dataset.
 *
 * The pay figure is never re-derived here. `DailySalaryComputation::
 * computeFromAccreditedLog()` is the one implementation of "minutes => pesos",
 * and the payslips seeded by `PagsanjanPayrollSeeder` aggregate from what it
 * writes, so a second copy of the formula would be a second answer to the same
 * question.
 *
 * Two approval workflows decide what some of those days are, and the register
 * has to agree with both. An approved leave application and an approved travel
 * order each own their dates, so they are read here and written in the shape
 * their own approval produces: `AttendancePunchService` refuses to punch a day
 * already marked LEAVE or TRAVEL_ORDER, because a punch would erase the
 * approval's trace from the DTR - and the dashboard, the reports and the AI
 * Assistant all count leave and absence from `attendance.attendance_type`, so a
 * travel day left as REGULAR is reported as a day at the desk. Neither
 * workflow's dates are invented here; they are read from the tables that own
 * them.
 */
class PagsanjanAttendanceSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /**
     * The seeded register window, both ends inclusive.
     *
     * It runs from the first working day of the seeded year to the last working
     * day before the dataset's "today" (2026-09-14), so a DTR, an attendance
     * trend or a payslip period asked for any month of 2026 finds a register
     * behind it rather than an empty month.
     *
     * Deliberately a string pair rather than `now()`: a seeder that follows the
     * wall clock writes a different register every day it is run, and the whole
     * point of seeding fixed randomness is that two people comparing the same
     * record are looking at the same record.
     */
    private const WINDOW_START = '2026-01-01';

    private const WINDOW_END = '2026-09-11';

    /**
     * Grace period before an arrival counts as late.
     *
     * Read from the service that owns the rule rather than typed again: if the
     * organisation's grace changes, the seeded DTR must move with it or the
     * dashboard's lateness count and the register it counts would disagree.
     */
    private const GRACE_MINUTES = AttendanceComputationService::GRACE_MINUTES;

    /**
     * Cumulative cut-offs of the daily roll: 3% absent, a further 4% late, the
     * rest present. Leave and travel days are decided by the tables that own
     * those approvals, not by this roll.
     *
     * The rates are per working day, which is why they are lower than a
     * month's-worth figure would suggest. Over the full January-September
     * window an employee has roughly 180 working days, so 3% absent is about
     * five unpaid days a year and 4% late about seven late arrivals - an
     * ordinary year for a plantilla employee. The 5%/7% this started at was
     * sized for a single six-week window; spread across nine months it read as
     * chronic absenteeism.
     *
     * Read as a ceiling rather than independent percentages so every employee is
     * classified exactly once per working day.
     */
    private const ABSENT_THROUGH = 3;

    private const LATE_THROUGH = 7;

    /** Half of a working day, both in the schedule and in a leave day's credit. */
    private const HALF_DAY_MINUTES = CscTimeConversionService::MINUTES_PER_HALF_DAY;

    /** Free-text reasons for an unplanned absence; the register has no absence code to hold. */
    private const ABSENCE_REASONS = [
        'Absent without prior notice',
        'Sick - no medical certificate filed',
        'Personal errand',
        'Family emergency',
    ];

    public function run(): void
    {
        $this->seedRandomness();

        $employees = $this->rosterEmployees();

        if ($employees === []) {
            $this->command->warn('No seeded Pagsanjan employees found - run PagsanjanEmployeeSeeder first.');

            return;
        }

        $employeeIds = array_keys($employees);
        $workingDays = $this->windowWorkingDays();

        // Read the schedules once. Over 182 working days the register asks for
        // 5,400 of them, and a query per cell is the difference between a seed
        // that finishes and one that does not.
        $schedules = [];
        foreach ($employeeIds as $employeeId) {
            $schedules[$employeeId] = $this->scheduleFor($employeeId);
        }

        // Another workflow (an approved leave or travel order) owns the days it
        // has already written: `attendance` is UNIQUE(employee_id, date), so
        // rewriting one would either fail or erase the approval's trace. The
        // claims are read before this seeder's own rows are cleared, which is
        // what keeps those rows out of the clear.
        $claimed = $this->claimedCells($employeeIds);
        $leaves = $this->approvedLeaveIndex($employeeIds);
        $travels = $this->approvedTravelIndex($employeeIds);

        $deleted = $this->clearWindow($employeeIds);

        // `LATE` is a report bucket, not a stored type: a late day is still a
        // REGULAR day, with the lateness carried in the log's `late_minutes`
        // and the register's remarks. Only the cells this run writes are rolled
        // here - the claimed ones are counted from the rows themselves below,
        // because a second run writes none of them and a summary that said
        // "0 on approved leave" over a register holding 44 of them would send
        // whoever read it looking for a bug that is not there.
        $written = 0;
        $counts = ['REGULAR' => 0, 'LATE' => 0, 'ABSENT' => 0, 'LEAVE' => 0, 'TRAVEL_ORDER' => 0];
        $register = ['REGULAR' => 0, 'ABSENT' => 0, 'LEAVE' => 0, 'TRAVEL_ORDER' => 0];

        // One transaction around the whole register. Each cell writes three
        // rows through the ORM - the register row, its accredited-hours log and
        // the daily peso computation, the last two through model observers - and
        // at 5,400 cells that is a long string of individually committed
        // statements. Committing the window once is most of the difference
        // between a seed that takes a minute and one that takes ten.
        DB::transaction(function () use ($workingDays, $employees, $schedules, $claimed, $leaves, $travels, &$written, &$counts) {
            foreach ($workingDays as $date) {
                $dateKey = $date->toDateString();

                foreach (array_keys($employees) as $employeeId) {
                    if (isset($claimed[$employeeId . '|' . $dateKey])) {
                        continue;
                    }

                    $schedule = $schedules[$employeeId];

                    // Every cell draws the same five values whatever it turns out
                    // to be, so a change in the leave table - which changes which
                    // branch a cell takes - cannot shift the sequence for the cells
                    // after it. Without that, the "reproducible" dataset would only
                    // be reproducible for a database in exactly one state.
                    $roll = $this->draw(1, 100);
                    $earlyMinutes = $this->draw(0, $this->earlyWindowMinutes($schedule['am_in']));
                    $birthSecond = $this->draw(0, 59);
                    $lateMinutes = $this->draw(6, 55);
                    $overtimeMinutes = $this->draw(0, 20);
                    $reasonIndex = $this->draw(0, count(self::ABSENCE_REASONS) - 1);

                    $leave = $leaves[$employeeId][$dateKey] ?? null;
                    $travel = $travels[$employeeId][$dateKey] ?? null;

                    // Leave is tested first, so a day covered by both an approved
                    // leave application and an approved travel order is written as
                    // LEAVE. That is the correct answer rather than an arbitrary
                    // tie-break: the leave is a debit against the employee's own
                    // credits, and recording travel instead would leave the leave
                    // ledger spent on a day the DTR says was spent elsewhere. The
                    // branch order makes the choice the code's, not the order the
                    // two approval tables happened to return their rows in.
                    if ($leave) {
                        $this->writeLeaveDay($employeeId, $dateKey, $schedule, $leave);
                        $counts['LEAVE']++;
                    } elseif ($travel) {
                        $this->writeTravelDay($employeeId, $dateKey, $schedule, $travel);
                        $counts['TRAVEL_ORDER']++;
                    } elseif ($roll <= self::ABSENT_THROUGH) {
                        $this->writeAbsentDay($employeeId, $dateKey, self::ABSENCE_REASONS[$reasonIndex]);
                        $counts['ABSENT']++;
                    } elseif ($roll <= self::LATE_THROUGH) {
                        $this->writeWorkedDay($employeeId, $dateKey, $schedule, $lateMinutes, $birthSecond, $overtimeMinutes);
                        $counts['LATE']++;
                    } else {
                        // On time: anywhere from the schedule's start back to the
                        // earlier of "half an hour early" and "an hour and a half
                        // before the start", so a 07:00 shift cannot arrive at 06:00
                        // and read as a five-hour lead on an eight-hour day.
                        $this->writeWorkedDay($employeeId, $dateKey, $schedule, -$earlyMinutes, $birthSecond, $overtimeMinutes);
                        $counts['REGULAR']++;
                    }

                    $written++;
                }
            }
        });

        // The register's actual composition, read back rather than inferred:
        // the claimed cells were written by the leave and travel workflows, so
        // their types are only knowable from the rows.
        foreach (Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [self::WINDOW_START, self::WINDOW_END])
            ->selectRaw('attendance_type, count(*) as total')
            ->groupBy('attendance_type')
            ->pluck('total', 'attendance_type') as $type => $total) {
            $register[$type] = (int) $total;
        }

        $this->command->info(sprintf(
            'Pagsanjan attendance: %d rows written over %d working days (%s to %s), %d cell(s) left to another workflow and %d stale row(s) cleared. Register now holds %d days: %d worked (%d of them late), %d absent, %d on approved leave, %d on approved travel order.',
            $written,
            count($workingDays),
            self::WINDOW_START,
            self::WINDOW_END,
            count($claimed),
            $deleted,
            array_sum($register),
            $register['REGULAR'],
            $counts['LATE'],
            $register['ABSENT'],
            $register['LEAVE'],
            $register['TRAVEL_ORDER'],
        ));
    }

    /**
     * The (employee, date) cells some other workflow already wrote.
     *
     * Scoped to the roster and to the window, so a demo register for an
     * unseeded employee (employee 1, the admin's own record) is left alone.
     * LEAVE and TRAVEL_ORDER rows are the ones that must survive; everything
     * else in the window is this seeder's own output from a previous run and is
     * replaced rather than skipped. That last half is what lets a register
     * seeded before a travel order was approved be corrected: a REGULAR or
     * ABSENT cell covered by an approved order is not claimed here, so the purge
     * clears it and the travel branch rewrites it. Skipping it instead would
     * leave the wrong day standing on every re-run, with no way to repair it.
     *
     * @param  array<int, int>  $employeeIds
     * @return array<string, true> keyed "<employee_id>|<Y-m-d>"
     */
    private function claimedCells(array $employeeIds): array
    {
        return Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [self::WINDOW_START, self::WINDOW_END])
            ->whereIn('attendance_type', ['LEAVE', 'TRAVEL_ORDER'])
            ->get(['employee_id', 'date'])
            ->mapWithKeys(fn (Attendance $row) => [
                $row->employee_id . '|' . $row->date->toDateString() => true,
            ])
            ->all();
    }

    /**
     * Approved leave overlapping the window, as employee => date => application.
     *
     * A leave day is not a decision this seeder makes: the employee asked for
     * the day, HR approved it, and the register has to show LEAVE with the
     * application's number on it. Building the map first also means the
     * per-cell lookup is an array hit rather than a query per row.
     *
     * @param  array<int, int>  $employeeIds
     * @return array<int, array<string, LeaveApplication>>
     */
    private function approvedLeaveIndex(array $employeeIds): array
    {
        $applications = LeaveApplication::with('leaveType')
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where('start_date', '<=', self::WINDOW_END)
            ->where('end_date', '>=', self::WINDOW_START)
            ->get();

        $index = [];

        foreach ($applications as $application) {
            $day = $application->start_date->copy()->startOfDay();
            $last = $application->end_date->copy()->startOfDay();

            while ($day->lte($last)) {
                if ($day->isWeekend()) {
                    $day->addDay();

                    continue;
                }

                $index[$application->employee_id][$day->toDateString()] = $application;
                $day->addDay();
            }
        }

        return $index;
    }

    /**
     * Approved travel orders overlapping the window, as employee => date => order.
     *
     * Travel is the second workflow that owns days on this register, and it is
     * read exactly the way leave is: `travel_date`..`return_date` inclusive,
     * with the weekend days dropped because the register holds working days
     * only. A travel day is not this seeder's decision either - the employee
     * filed the trip and HR approved it, and the DTR has to show TRAVEL_ORDER
     * with the order's number on it. Building the map first also keeps the
     * per-cell lookup an array hit rather than a query per row.
     *
     * @param  array<int, int>  $employeeIds
     * @return array<int, array<string, TravelOrder>>
     */
    private function approvedTravelIndex(array $employeeIds): array
    {
        $orders = TravelOrder::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where('travel_date', '<=', self::WINDOW_END)
            ->where('return_date', '>=', self::WINDOW_START)
            ->get();

        $index = [];

        foreach ($orders as $order) {
            $day = $order->travel_date->copy()->startOfDay();
            $last = $order->return_date->copy()->startOfDay();

            while ($day->lte($last)) {
                if ($day->isWeekend()) {
                    $day->addDay();

                    continue;
                }

                $index[$order->employee_id][$day->toDateString()] = $order;
                $day->addDay();
            }
        }

        return $index;
    }

    /**
     * Remove this seeder's own previous output before rewriting it.
     *
     * Scoped three ways: to the roster, to the window, and to the attendance
     * types this seeder writes. A LEAVE or TRAVEL_ORDER row in the window was
     * put there by an approval, not by this seeder, and deleting it would erase
     * the approval's trace from the DTR on the way to re-seeding.
     *
     * `accredited_hours_log` has no date column, so its rows are scoped through
     * the attendance rows being cleared. `salary_computations` is deliberately
     * untouched: it is owned by the payslip seeder and holds the figures the
     * approved payroll was built from.
     *
     * @param  array<int, int>  $employeeIds
     */
    private function clearWindow(array $employeeIds): int
    {
        $attendanceIds = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [self::WINDOW_START, self::WINDOW_END])
            ->whereNotIn('attendance_type', ['LEAVE', 'TRAVEL_ORDER'])
            ->pluck('id');

        if ($attendanceIds->isEmpty()) {
            return 0;
        }

        DB::table('daily_salary_computations')->whereIn('accredited_hours_log_id', function ($query) use ($attendanceIds) {
            $query->select('id')->from('accredited_hours_log')->whereIn('attendance_id', $attendanceIds);
        })->delete();

        DB::table('accredited_hours_log')->whereIn('attendance_id', $attendanceIds)->delete();

        return Attendance::whereIn('id', $attendanceIds)->delete();
    }

    /**
     * The window's Mondays to Fridays, oldest first.
     *
     * @return array<int, Carbon>
     */
    private function windowWorkingDays(): array
    {
        $days = [];
        $date = Carbon::parse(self::WINDOW_START)->startOfDay();
        $end = Carbon::parse(self::WINDOW_END)->startOfDay();

        while ($date->lte($end)) {
            if (! $date->isWeekend()) {
                $days[] = $date->copy();
            }

            $date->addDay();
        }

        return $days;
    }

    /**
     * The schedule in force for an employee, as minute-of-day values.
     *
     * The roster's normal hours are only a fallback: GSO, GSO-SL and MDRRM run
     * 07:00-16:00, and hardcoding 08:00-17:00 for them would put every arrival
     * on a 07:00 shift an hour inside the grace period.
     *
     * @return array{am_in: int, am_out: int, pm_in: int, pm_out: int, id: ?int}
     */
    private function scheduleFor(int $employeeId): array
    {
        $schedule = Schedule::where('employee_id', $employeeId)->orderByDesc('start_date')->first();

        return [
            'am_in' => $this->timeToMinutes($schedule?->am_in ?: '08:00:00'),
            'am_out' => $this->timeToMinutes($schedule?->am_out ?: '12:00:00'),
            'pm_in' => $this->timeToMinutes($schedule?->pm_in ?: '13:00:00'),
            'pm_out' => $this->timeToMinutes($schedule?->pm_out ?: '17:00:00'),
            'id' => $schedule?->id,
        ];
    }

    /** How early an on-time arrival may be, capped so it cannot precede a sensible start. */
    private function earlyWindowMinutes(int $scheduledStart): int
    {
        $earliest = max(0, $scheduledStart - 90);

        return min(30, max(0, $scheduledStart - $earliest));
    }

    /**
     * A day the employee worked: the register row, its minutes, and its pay.
     *
     * `$morningOffset` is minutes past the scheduled start (0 or a negative
     * number of early minutes), and `$afternoonOffset` the same for the
     * departure - both are offsets rather than clock times because only the
     * schedule moves between offices.
     */
    private function writeWorkedDay(
        int $employeeId,
        string $date,
        array $schedule,
        int $morningOffset,
        int $birthSecond,
        int $afternoonOffset,
    ): void {
        $arrival = $this->minutesToTime($schedule['am_in'] + $morningOffset);
        $graceEnd = $schedule['am_in'] + self::GRACE_MINUTES;
        $arrivalMinutes = $this->timeToMinutes($arrival);
        $lateMinutes = $arrivalMinutes > $graceEnd ? $arrivalMinutes - $graceEnd : 0;

        $amCredited = max(0, min($schedule['am_out'] - max($arrivalMinutes, $schedule['am_in']), self::HALF_DAY_MINUTES));
        $pmCredited = max(0, min($schedule['pm_out'] - $schedule['pm_in'], self::HALF_DAY_MINUTES));
        $accredited = $amCredited + $pmCredited;

        $attendance = Attendance::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'am_in' => $arrival,
            'am_out' => $this->minutesToTime($schedule['am_out'], $birthSecond),
            'pm_in' => $this->minutesToTime($schedule['pm_in'], $birthSecond),
            'pm_out' => $this->minutesToTime($schedule['pm_out'] + $afternoonOffset, $birthSecond),
            'ot_in' => null,
            'ot_out' => null,
            // The column's unit is minutes (480 is a full day), despite its name.
            'accredited_hours' => $accredited,
            'total_hours' => $accredited,
            'attendance_type' => 'REGULAR',
            'remarks' => $lateMinutes > 0
                ? sprintf('Late arrival - %d minute(s) beyond the %d-minute grace period.', $lateMinutes, self::GRACE_MINUTES)
                : null,
        ]);

        $note = $lateMinutes > 0
            ? sprintf('Late %d min past grace; %d minute(s) not accredited.', $lateMinutes, $lateMinutes)
            : sprintf('On time; %d of %d minutes accredited.', $accredited, self::HALF_DAY_MINUTES * 2);

        $this->writeLog($attendance, $schedule, $amCredited, $pmCredited, $lateMinutes, $note, $lateMinutes === 0 && $arrivalMinutes > $schedule['am_in']);
    }

    /**
     * A day the employee did not report for: nothing worked, nothing paid.
     *
     * The three tables still all get a row. A missing log would make the day
     * invisible to the accredited-hours views, and a missing daily computation
     * would leave the payslip aggregating a fortnight that is short a day.
     */
    private function writeAbsentDay(int $employeeId, string $date, string $reason): void
    {
        $attendance = Attendance::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'am_in' => null,
            'am_out' => null,
            'pm_in' => null,
            'pm_out' => null,
            'ot_in' => null,
            'ot_out' => null,
            'accredited_hours' => 0,
            'total_hours' => 0,
            'attendance_type' => 'ABSENT',
            'remarks' => $reason,
        ]);

        $this->writeLog($attendance, null, 0, 0, 0, 'Absent - no time recorded for the day.');
    }

    /**
     * An approved leave day: the employee is out, but the day is paid.
     *
     * The minutes are credited at four hours a session so the day reads as a
     * full 480 on the DTR, which is how the leave balance was debited. The
     * application's own number goes in `remarks` because "on leave" alone
     * cannot be reconciled against the approval it came from.
     */
    private function writeLeaveDay(
        int $employeeId,
        string $date,
        array $schedule,
        LeaveApplication $application,
    ): void {
        $leaveName = $application->leaveType?->leave_name
            ?? $application->leave_code
            ?? 'Leave';
        $total = self::HALF_DAY_MINUTES * 2;

        $attendance = Attendance::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'am_in' => null,
            'am_out' => null,
            'pm_in' => null,
            'pm_out' => null,
            'ot_in' => null,
            'ot_out' => null,
            'accredited_hours' => $total,
            'total_hours' => $total,
            'attendance_type' => 'LEAVE',
            'remarks' => sprintf('Leave: %s - %s', $leaveName, $application->application_number),
        ]);

        $this->writeLog(
            $attendance,
            $schedule,
            self::HALF_DAY_MINUTES,
            self::HALF_DAY_MINUTES,
            0,
            sprintf('Approved leave (%s), application %s - the day is credited in full.', $leaveName, $application->application_number),
        );
    }

    /**
     * An approved travel order day: the employee is out of the office on the
     * municipality's business, and the day is paid in full.
     *
     * The shape is the leave day's, and it is the shape `TravelOrderObserver`
     * writes when an order is approved at run time: all four punches empty, the
     * minutes credited at four hours a session so the day reads as a full 480,
     * and the order's own number in `remarks` because "on travel" alone cannot
     * be reconciled against the approval it came from.
     */
    private function writeTravelDay(
        int $employeeId,
        string $date,
        array $schedule,
        TravelOrder $order,
    ): void {
        $total = self::HALF_DAY_MINUTES * 2;

        $attendance = Attendance::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'am_in' => null,
            'am_out' => null,
            'pm_in' => null,
            'pm_out' => null,
            'ot_in' => null,
            'ot_out' => null,
            'accredited_hours' => $total,
            'total_hours' => $total,
            'attendance_type' => 'TRAVEL_ORDER',
            'remarks' => sprintf('Travel Order: %s - %s', $order->destination, $order->order_number),
        ]);

        $this->writeLog(
            $attendance,
            $schedule,
            self::HALF_DAY_MINUTES,
            self::HALF_DAY_MINUTES,
            0,
            sprintf('Approved travel order (%s), order %s - the day is credited in full.', $order->destination, $order->order_number),
        );
    }

    /**
     * The minutes row for an attendance row, and the peso row for that.
     *
     * `computeFromAccreditedLog()` owns the pesos, so the only thing decided
     * here is the minutes.
     */
    private function writeLog(
        Attendance $attendance,
        ?array $schedule,
        int $amCredited,
        int $pmCredited,
        int $lateMinutes,
        string $note,
        bool $graceApplied = false,
    ): void {
        $log = AccreditedHoursLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'schedule_id' => $schedule['id'] ?? null,
            'am_accredited_minutes' => $amCredited,
            'pm_accredited_minutes' => $pmCredited,
            'ot_minutes' => 0,
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => 0,
            'total_accredited_minutes' => $amCredited + $pmCredited,
            'total_actual_minutes' => $amCredited + $pmCredited,
            'am_grace_applied' => $graceApplied,
            'pm_grace_applied' => false,
            'computation_notes' => $note,
            'late_deducted_from_leave' => false,
            'late_deduction_leave_type' => null,
            'lwop_minutes' => 0,
            'requires_salary_deduction' => false,
            'undertime_deducted_from_leave' => false,
            'undertime_deduction_leave_type' => null,
        ]);

        DailySalaryComputation::computeFromAccreditedLog($log);
    }

    /** Minutes since midnight for a schedule time or clock reading. */
    private function timeToMinutes(?string $time): int
    {
        if (! $time) {
            return 0;
        }

        [$hours, $minutes] = array_pad(explode(':', $time), 2, '0');

        return ((int) $hours * 60) + (int) $minutes;
    }

    /**
     * A clock reading for a minute of the day, clamped inside the day.
     *
     * `$birthSecond` varies the seconds field so a register does not read as
     * thirty people arriving on the same second of the same minute.
     */
    private function minutesToTime(int $minuteOfDay, int $birthSecond = 0): string
    {
        $minuteOfDay = max(0, min($minuteOfDay, (23 * 60) + 59));

        return sprintf('%02d:%02d:%02d', intdiv($minuteOfDay, 60), $minuteOfDay % 60, $birthSecond);
    }
}
