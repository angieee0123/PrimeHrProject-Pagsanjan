<?php

namespace App\Console\Commands;

use App\Models\AccreditedHoursLog;
use App\Models\Attendance;
use App\Models\DailySalaryComputation;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\TravelOrder;
use App\Services\AttendanceComputationService;
use App\Services\CscTimeConversionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Fill realistic weekday punches for a month, one row per employee per day.
 *
 * Gap-filler for dev/demo databases: it never overwrites a touched row and
 * never paves over days owned by another workflow (approved leave, travel
 * orders, holidays). Punches go through AttendanceComputationService, the
 * same accreditation the correction form and the scanner use, so seeded days
 * agree with the DTR, reports, and the AI Assistant.
 *
 * Deliberately stops at the accredited-hours columns: AccreditedHoursLog and
 * DailySalaryComputation belong to the payroll chain, which recomputes them
 * from these same punches when it runs.
 *
 *   php artisan attendance:seed-month 2026-09
 *   php artisan attendance:seed-month 2026-09 --to=2026-09-08 --late-pct=15 --absent-pct=5
 */
class SeedMonthlyAttendance extends Command
{
    protected $signature = 'attendance:seed-month
                            {month? : Month to seed as YYYY-MM (default current month)}
                            {--to= : Last date to seed as YYYY-MM-DD (default yesterday)}
                            {--late-pct=10 : Percent of days with a late arrival}
                            {--absent-pct=3 : Percent of days marked absent}
                            {--repair-mislabeled : Rewrite punch-less rows inside approved leave/travel ranges to the LEAVE/TRAVEL_ORDER shape the observers write}';

    protected $description = 'Seed realistic weekday attendance punches for a month without touching existing rows';

    /** Days owned by another workflow: read, never written, by this command. */
    private const PROTECTED_TYPES = ['LEAVE', 'TRAVEL_ORDER', 'HOLIDAY'];

    public function handle(AttendanceComputationService $computation): int
    {
        $month = (string) ($this->argument('month') ?: now()->format('Y-m'));

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('Month must look like YYYY-MM, e.g. 2026-09.');

            return self::FAILURE;
        }

        $to = $this->option('to') ? Carbon::parse($this->option('to'))->startOfDay() : now()->subDay()->startOfDay();
        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $endOfMonth = $start->copy()->endOfMonth()->startOfDay();
        $to = $to->lt($endOfMonth) ? $to : $endOfMonth;

        if ($to->lt($start)) {
            $this->error('Cutoff is before the month starts — nothing to seed.');

            return self::FAILURE;
        }

        $latePct = max(0, min(100, (int) $this->option('late-pct')));
        $absentPct = max(0, min(100, (int) $this->option('absent-pct')));

        $days = [];
        for ($day = $start->copy(); $day->lte($to); $day->addDay()) {
            if (!$day->isWeekend()) {
                $days[] = $day->toDateString();
            }
        }

        $employees = Employee::query()->orderBy('id')->get();

        if ($employees->isEmpty() || empty($days)) {
            $this->warn('No employees or no weekdays in range — nothing to seed.');

            return self::SUCCESS;
        }

        $covered = $this->coveredDates($employees->pluck('id')->all(), $days[0], end($days));

        $tally = ['regular' => 0, 'late' => 0, 'absent' => 0, 'skipped_touched' => 0, 'skipped_protected' => 0];

        foreach ($employees as $employee) {
            foreach ($days as $date) {
                if (isset($covered[$employee->id][$date])) {
                    $tally['skipped_protected']++;
                    continue;
                }

                $existing = Attendance::where('employee_id', $employee->id)->whereDate('date', $date)->first();

                if ($existing
                    && (in_array($existing->attendance_type, self::PROTECTED_TYPES, true)
                        || $existing->am_in || $existing->pm_in)) {
                    $tally[in_array($existing->attendance_type, self::PROTECTED_TYPES, true)
                        ? 'skipped_protected' : 'skipped_touched']++;
                    continue;
                }

                // Deterministic per employee/day: reruns paint the same day.
                mt_srand(crc32($employee->id . '|' . $date));
                $roll = mt_rand(1, 100);
                $isAbsent = $roll <= $absentPct;

                if ($isAbsent) {
                    // Found-or-created by calendar day (whereDate, not plain
                    // equality: a DATETIME column stores 'Y-m-d H:i:s', which
                    // never equals the 'Y-m-d' string, and the unique
                    // (employee_id, date) pair would duplicate on rerun).
                    // An untouched empty row found above is converted rather
                    // than left behind.
                    $row = Attendance::where('employee_id', $employee->id)->whereDate('date', $date)->first()
                        ?? Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $date,
                            'attendance_type' => 'ABSENT',
                        ]);
                    $row->update([
                        'am_in' => null, 'am_out' => null, 'pm_in' => null, 'pm_out' => null,
                        'ot_in' => null, 'ot_out' => null,
                        'attendance_type' => 'ABSENT',
                        'accredited_hours' => null, 'total_hours' => null,
                    ]);
                    $tally['absent']++;
                    continue;
                }

                $isLate = $roll <= $absentPct + $latePct;
                $punches = $this->punchesFor($employee, $date, $isLate);

                $row = Attendance::where('employee_id', $employee->id)->whereDate('date', $date)->first()
                    ?? Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $date,
                        'attendance_type' => 'REGULAR',
                    ]);

                $accredited = $computation->computeAccreditedHours(
                    $employee->id, $date,
                    $punches['am_in'], $punches['am_out'], $punches['pm_in'], $punches['pm_out']
                );

                $row->update([
                    'am_in' => $punches['am_in'],
                    'am_out' => $punches['am_out'],
                    'pm_in' => $punches['pm_in'],
                    'pm_out' => $punches['pm_out'],
                    'attendance_type' => 'REGULAR',
                    'accredited_hours' => $accredited['accredited_minutes'],
                    'total_hours' => $computation->computeTotalHours(
                        $punches['am_in'], $punches['am_out'], $punches['pm_in'], $punches['pm_out'], null, null
                    ),
                ]);

                $tally[$isLate ? 'late' : 'regular']++;
            }
        }

        $this->table(
            ['Regular', 'Late', 'Absent', 'Skipped (touched)', 'Skipped (leave/travel/holiday)'],
            [[$tally['regular'], $tally['late'], $tally['absent'], $tally['skipped_touched'], $tally['skipped_protected']]]
        );

        if ($this->option('repair-mislabeled')) {
            $repaired = $this->repairMislabeled($start->toDateString(), $to->toDateString());
            $this->table(
                ['Repaired to LEAVE', 'Repaired to TRAVEL_ORDER', 'Repair failed'],
                [[$repaired['leave'], $repaired['travel'], $repaired['failed']]]
            );
        }

        return self::SUCCESS;
    }

    /**
     * Rewrite rows the approval observers would have written correctly if
     * they had found an empty day: an approved leave/travel range whose
     * attendance row exists but is neither the right type (pre-fix writes
     * stored REGULAR with the type silently discarded) nor touched with real
     * punches — plus days with no row at all, which the observers backfill
     * on approval.
     *
     * Only punch-less rows are converted — a day with real punches inside an
     * approved range is a genuine conflict for HR, not for a script. The
     * replacement row, log, and salary computation replicate
     * LeaveApplicationObserver and TravelOrderObserver field-for-field.
     *
     * @return array{leave: int, travel: int, failed: int}
     */
    private function repairMislabeled(string $from, string $to): array
    {
        $tally = ['leave' => 0, 'travel' => 0, 'failed' => 0];

        $leaves = LeaveApplication::with('leaveType')
            ->where('status', 'approved')
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->get();

        foreach ($leaves as $leave) {
            foreach ($this->weekdaysBetween(
                max($this->ymd($leave->start_date), $from),
                min($this->ymd($leave->end_date), $to)
            ) as $date) {
                $row = Attendance::where('employee_id', $leave->employee_id)->whereDate('date', $date)->first();

                if ($row && ($row->attendance_type === 'LEAVE' || $row->am_in || $row->pm_in)) {
                    continue;
                }

                try {
                    $schedule = $leave->employee?->getScheduleForDate($date);
                    $fields = [
                        'am_in' => null, 'am_out' => null, 'pm_in' => null, 'pm_out' => null,
                        'ot_in' => null, 'ot_out' => null,
                        'accredited_hours' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                        'total_hours' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                        'attendance_type' => 'LEAVE',
                        'remarks' => "Leave: {$leave->leaveType->leave_name} - {$leave->application_number}",
                    ];

                    // A missing day is backfilled exactly as the observer
                    // would have written it on approval; a mislabeled
                    // punch-less row is rewritten to the same shape.
                    $row = $row
                        ? tap($row)->update($fields)
                        : Attendance::create([
                            'employee_id' => $leave->employee_id,
                            'date' => $date,
                        ] + $fields);

                    $log = AccreditedHoursLog::updateOrCreate(
                        ['attendance_id' => $row->id],
                        [
                            'employee_id' => $leave->employee_id,
                            'schedule_id' => $schedule ? $schedule->id : null,
                            'am_accredited_minutes' => CscTimeConversionService::MINUTES_PER_HALF_DAY,
                            'pm_accredited_minutes' => CscTimeConversionService::MINUTES_PER_HALF_DAY,
                            'ot_minutes' => 0,
                            'late_minutes' => 0,
                            'undertime_minutes' => 0,
                            'total_accredited_minutes' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                            'total_actual_minutes' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                            'am_grace_applied' => false,
                            'pm_grace_applied' => false,
                            'computation_notes' => sprintf(
                                'On approved leave: %s - %s (%s)',
                                $leave->leaveType->leave_name ?? 'Leave',
                                $leave->application_number,
                                $leave->leaveType->leave_code ?? 'N/A'
                            ),
                        ]
                    );

                    DailySalaryComputation::computeFromAccreditedLog($log);
                    $tally['leave']++;
                } catch (\Throwable $e) {
                    $tally['failed']++;
                    $this->warn("Could not repair employee {$leave->employee_id} {$date}: {$e->getMessage()}");
                }
            }
        }

        $trips = TravelOrder::where('status', 'approved')
            ->where('travel_date', '<=', $to)
            ->where('return_date', '>=', $from)
            ->get();

        foreach ($trips as $trip) {
            foreach ($this->weekdaysBetween(
                max($this->ymd($trip->travel_date), $from),
                min($this->ymd($trip->return_date), $to)
            ) as $date) {
                $row = Attendance::where('employee_id', $trip->employee_id)->whereDate('date', $date)->first();

                if ($row && ($row->attendance_type === 'TRAVEL_ORDER' || $row->am_in || $row->pm_in)) {
                    continue;
                }

                try {
                    $schedule = $trip->employee?->getScheduleForDate($date);
                    $fields = [
                        'am_in' => null, 'am_out' => null, 'pm_in' => null, 'pm_out' => null,
                        'ot_in' => null, 'ot_out' => null,
                        'accredited_hours' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                        'total_hours' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                        'attendance_type' => 'TRAVEL_ORDER',
                        'remarks' => "Travel Order: {$trip->destination} - {$trip->order_number}",
                    ];

                    $row = $row
                        ? tap($row)->update($fields)
                        : Attendance::create([
                            'employee_id' => $trip->employee_id,
                            'date' => $date,
                        ] + $fields);

                    $log = AccreditedHoursLog::updateOrCreate(
                        ['attendance_id' => $row->id],
                        [
                            'employee_id' => $trip->employee_id,
                            'schedule_id' => $schedule ? $schedule->id : null,
                            'am_accredited_minutes' => CscTimeConversionService::MINUTES_PER_HALF_DAY,
                            'pm_accredited_minutes' => CscTimeConversionService::MINUTES_PER_HALF_DAY,
                            'ot_minutes' => 0,
                            'late_minutes' => 0,
                            'undertime_minutes' => 0,
                            'total_accredited_minutes' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                            'total_actual_minutes' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                            'am_grace_applied' => false,
                            'pm_grace_applied' => false,
                            'computation_notes' => sprintf(
                                'On approved travel order: %s - %s (%s)',
                                $trip->destination,
                                $trip->order_number,
                                $trip->purpose
                            ),
                        ]
                    );

                    DailySalaryComputation::computeFromAccreditedLog($log);
                    $tally['travel']++;
                } catch (\Throwable $e) {
                    $tally['failed']++;
                    $this->warn("Could not repair employee {$trip->employee_id} {$date}: {$e->getMessage()}");
                }
            }
        }

        return $tally;
    }

    /**
     * Carbon, string, or null → Y-m-d. Model casts differ per table, and
     * max()/min() over mixed types compare objects by identity rather than by
     * date, so everything is normalized before bounding a range.
     */
    private function ymd(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10);
    }

    /**
     * @return array<int, string> Weekday Y-m-d dates between two Y-m-d bounds.
     */
    private function weekdaysBetween(string $from, string $to): array
    {
        $days = [];
        $cursor = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        while ($cursor->lte($end)) {
            if (!CscTimeConversionService::isWeekend($cursor)) {
                $days[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return $days;
    }

    /**
     * Dates already spoken for: existing protected-type rows plus approved
     * leave and travel ranges (whose observers own those days).
     *
     * @param array<int> $employeeIds
     * @return array<int, array<string, true>>
     */
    private function coveredDates(array $employeeIds, string $from, string $to): array
    {
        $covered = [];

        $mark = function (int $employeeId, string $date) use (&$covered): void {
            $covered[$employeeId][$date] = true;
        };

        foreach (Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$from, $to])
            ->whereIn('attendance_type', self::PROTECTED_TYPES)
            ->get(['employee_id', 'date']) as $row) {
            $mark((int) $row->employee_id, Carbon::parse($row->date)->toDateString());
        }

        foreach (LeaveApplication::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->get(['employee_id', 'start_date', 'end_date']) as $leave) {
            $cursor = Carbon::parse($leave->start_date)->max(Carbon::parse($from));
            $end = Carbon::parse($leave->end_date)->min(Carbon::parse($to));

            while ($cursor->lte($end)) {
                $mark((int) $leave->employee_id, $cursor->toDateString());
                $cursor->addDay();
            }
        }

        foreach (TravelOrder::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where('travel_date', '<=', $to)
            ->where('return_date', '>=', $from)
            ->get(['employee_id', 'travel_date', 'return_date']) as $trip) {
            $cursor = Carbon::parse($trip->travel_date)->max(Carbon::parse($from));
            $end = Carbon::parse($trip->return_date)->min(Carbon::parse($to));

            while ($cursor->lte($end)) {
                $mark((int) $trip->employee_id, $cursor->toDateString());
                $cursor->addDay();
            }
        }

        return $covered;
    }

    /**
     * A plausible day around the employee's own schedule: on-time arrivals
     * inside the grace window, late ones after it, outs hugging the session
     * ends. Times are H:i, the shape correctAttendance validates.
     *
     * @return array{am_in: string, am_out: string, pm_in: string, pm_out: string}
     */
    private function punchesFor(Employee $employee, string $date, bool $late): array
    {
        $schedule = $employee->getScheduleForDate($date);

        $toMin = fn (?string $t, int $fallback) => $t
            ? ((int) explode(':', $t)[0]) * 60 + ((int) explode(':', $t)[1])
            : $fallback;

        $amStart = $toMin($schedule?->am_in, AttendanceComputationService::DEFAULT_AM_START);
        $amEnd = $toMin($schedule?->am_out, AttendanceComputationService::DEFAULT_AM_END);
        $pmStart = $toMin($schedule?->pm_in, AttendanceComputationService::DEFAULT_PM_START);
        $pmEnd = $toMin($schedule?->pm_out, AttendanceComputationService::DEFAULT_PM_END);

        $fmt = fn (int $minutes) => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);

        return [
            // On-time: mostly early, sometimes inside the grace window; late:
            // past the grace, up to ~35 minutes after it.
            'am_in' => $late
                ? $fmt($amStart + AttendanceComputationService::GRACE_MINUTES + mt_rand(1, 35))
                : (mt_rand(1, 100) <= 70
                    ? $fmt($amStart - mt_rand(0, 22))
                    : $fmt($amStart + mt_rand(0, AttendanceComputationService::GRACE_MINUTES - 1))),
            'am_out' => $fmt($amEnd + mt_rand(-5, 5)),
            'pm_in' => $fmt($pmStart - mt_rand(0, 5) + mt_rand(0, 5)),
            'pm_out' => $fmt($pmEnd + mt_rand(-5, 10)),
        ];
    }
}
