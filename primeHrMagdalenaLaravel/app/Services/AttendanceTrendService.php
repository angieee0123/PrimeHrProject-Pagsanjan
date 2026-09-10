<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Attendance rate over a *chosen* period, for the admin dashboard's trend card.
 *
 * The chart used to be hard-wired to "now": week meant the last seven days,
 * month the last thirty, year the last twelve. That answers "how are we doing
 * lately" and nothing else — an administrator asking "what did September 2024
 * look like" had no way to ask. This service builds the same three series for
 * any anchored period, so Week / Month / Year became a *bucket size* rather
 * than a fixed window.
 *
 * Everything the chart draws is defined in exactly one place here, and the
 * definitions are the ones the rest of the system already uses:
 *
 * - **Working days only.** Saturday and Sunday are not charted. They are not
 *   days anyone is expected in, and a 0% point every seventh and first day
 *   turned the line into a sawtooth that said more about the calendar than
 *   about attendance. The dashboard's own Attendance Performance card already
 *   counts working days this way.
 * - **Lateness is measured against each employee's own schedule** plus
 *   {@see AttendanceComputationService::GRACE_MINUTES}, not against a flat
 *   08:05 for everyone — the same rule, and the same fallback, as
 *   {@see \App\Http\Controllers\MayorDashboardController::countLate()}. A flat
 *   cut-off hands the 07:00 shift 65 minutes of grace.
 * - **The denominator is the roster actually expected that day**: headcount
 *   less anyone on approved leave or an approved travel order. Those people are
 *   accounted for, and counting them as absent made the rate fall every time
 *   leave was approved. Same definition as the mayor's dashboard, and the same
 *   headcount the stat cards above the chart divide by — a chart that disagreed
 *   with the "Attendance Rate" card on the same page would be worse than either
 *   figure alone.
 *
 *   Headcount is *today's*, because nothing in this schema records when a
 *   roster changed: `employees.created_at` is the row's insert date,
 *   `employment_details.appointment_date` is present but not maintained, and
 *   there is no separation date at all. A period when fewer people were
 *   employed therefore reads low, which is a limit of the data rather than of
 *   this arithmetic.
 * - **All three series are a percentage of that roster**, so they sit on one
 *   0-100 scale and the year view is the average of the daily rates rather than
 *   the old present ÷ (headcount × calendar days), which counts every weekend
 *   as a full absence and pinned the series to the bottom of the axis.
 *
 * Two kinds of day are `null`, never 0, and Chart.js draws them as a gap:
 *
 * - **a day that has not happened yet** — the table can hold future-dated rows,
 *   and a day that has not arrived is not a day with no attendance;
 * - **a working day the attendance table has no row for at all** — nothing has
 *   been encoded for it, which reads as "everyone was absent" if it is drawn as
 *   zero. That is the cliff at the right-hand end of a current week whose last
 *   day or two is not in yet, and the whole of a month like August 2026.
 *
 * The distinction survives because a day that *was* encoded keeps its rows even
 * when nobody clocked in — a leave day is an `attendance` row with a null
 * `am_in` — and that day is charted, at 0% present, correctly.
 *
 * Five queries per request, whatever the range — the per-day pattern this
 * replaced ran two queries for *every* day it drew.
 */
class AttendanceTrendService
{
    /** Bucket sizes the card offers. Anything else falls back to 'month'. */
    public const PERIODS = ['week', 'month', 'year'];

    /**
     * Shift start used when an employee has no `schedules` row covering the
     * day. Read from the service that owns the default rather than retyped, so
     * a change to the standard working hours reaches this chart too.
     */
    private const DEFAULT_SHIFT_START_MINUTES = AttendanceComputationService::DEFAULT_AM_START;

    /**
     * The chart payload for one period.
     *
     * @param  string  $period  'week' | 'month' | 'year'
     * @param  mixed   $anchor  any date inside the wanted period; defaults to today
     */
    public function series(string $period, $anchor = null): array
    {
        $period = in_array($period, self::PERIODS, true) ? $period : 'month';
        $today  = Carbon::today();
        $anchor = $this->parseDate($anchor) ?? $today->copy();

        [$start, $end] = $this->rangeFor($period, $anchor);

        $context = $this->loadRange($start, $end);

        // One metrics row per working day in the range, keyed 'Y-m-d'. A null
        // value is a day with nothing to draw: one that has not happened yet,
        // or one the attendance table holds no row for at all. Both keep their
        // slot on the axis and leave a gap in the line, which is the truthful
        // shape — a day nobody encoded is not a day everybody missed.
        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if ($day->isWeekend()) {
                continue;
            }

            $key = $day->toDateString();

            $days[$key] = ($day->gt($today) || ! isset($context['rows'][$key]))
                ? null
                : $this->dayMetrics($day, $context);
        }

        $hasData = array_filter($days) !== [];

        $series = $period === 'year'
            ? $this->bucketByMonth($days, (int) $start->year)
            : $this->bucketByDay($days, $period);

        $nextStart = $this->rangeFor($period, $this->step($period, $anchor, 1))[0];

        return array_merge($series, [
            'period'         => $period,
            'anchor'         => $anchor->toDateString(),
            'start'          => $start->toDateString(),
            'end'            => $end->toDateString(),
            'label'          => $this->rangeLabel($period, $start, $end),
            'sublabel'       => $this->subLabel($period, $start, $end),
            'has_data'       => $hasData,
            'in_future'      => $start->gt($today),
            'can_step_next'  => $nextStart->lte($today),
        ]);
    }

    /**
     * First and last day of the period containing $anchor.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function rangeFor(string $period, Carbon $anchor): array
    {
        if ($period === 'week') {
            // Monday to Sunday, so a week is the whole week it names.
            $monday = $anchor->copy()->startOfWeek(Carbon::MONDAY);

            return [$monday, $monday->copy()->addDays(6)];
        }

        return match ($period) {
            'year'  => [$anchor->copy()->startOfYear(), $anchor->copy()->endOfYear()],
            default => [$anchor->copy()->startOfMonth(), $anchor->copy()->endOfMonth()],
        };
    }

    /** The anchor moved by one bucket, in the direction the stepper arrows go. */
    private function step(string $period, Carbon $anchor, int $direction): Carbon
    {
        return match ($period) {
            'week'  => $anchor->copy()->addWeeks($direction),
            'year'  => $anchor->copy()->addYears($direction),
            default => $anchor->copy()->addMonthsNoOverflow($direction),
        };
    }

    private function parseDate($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Everything the range needs, in five queries.
     *
     * @return array{headcount: int, rows: array, present: array, leave: array, travel: array, schedules: array}
     */
    private function loadRange(Carbon $start, Carbon $end): array
    {
        $from = $start->toDateString();
        $to   = $end->toDateString();

        $rows    = [];
        $present = [];
        $records = DB::table('attendance')
            ->select('employee_id', 'date', 'am_in')
            ->whereBetween('date', [$from, $to])
            ->get();

        foreach ($records as $record) {
            $key = Carbon::parse($record->date)->toDateString();
            $rows[$key] = ($rows[$key] ?? 0) + 1; // the day was encoded at all

            if ($record->am_in !== null) {
                $present[$key][(int) $record->employee_id] = $record->am_in;
            }
        }

        // Schedules overlapping the range, grouped per employee. Chosen per day
        // in PHP so one query covers a whole year.
        $schedules = [];
        $scheduleRows = DB::table('schedules')
            ->select('employee_id', 'start_date', 'end_date', 'am_in')
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->get();

        foreach ($scheduleRows as $row) {
            $schedules[(int) $row->employee_id][] = [
                'start' => Carbon::parse($row->start_date),
                'end'   => Carbon::parse($row->end_date),
                'am_in' => $row->am_in,
            ];
        }

        return [
            'headcount' => Employee::count(),
            'rows'      => $rows,
            'present'   => $present,
            'leave'     => $this->expandRangesToDays(
                DB::table('leave_applications')
                    ->select('employee_id', 'start_date', 'end_date')
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $to)
                    ->whereDate('end_date', '>=', $from)
                    ->get(),
                'start_date',
                'end_date',
                $start,
                $end
            ),
            'travel' => $this->expandRangesToDays(
                DB::table('travel_orders')
                    ->select('employee_id', 'travel_date', 'return_date')
                    ->where('status', 'approved')
                    ->whereDate('travel_date', '<=', $to)
                    ->whereDate('return_date', '>=', $from)
                    ->get(),
                'travel_date',
                'return_date',
                $start,
                $end
            ),
            'schedules' => $schedules,
        ];
    }

    /**
     * Expand approved leave / travel rows — each a date *range* — into
     * `['Y-m-d' => [employee_id => true]]`, clipped to the range being charted.
     */
    private function expandRangesToDays($rows, string $fromColumn, string $toColumn, Carbon $start, Carbon $end): array
    {
        $byDate = [];

        foreach ($rows as $row) {
            $from = Carbon::parse($row->{$fromColumn});
            $to   = Carbon::parse($row->{$toColumn});

            if ($from->lt($start)) {
                $from = $start->copy();
            }
            if ($to->gt($end)) {
                $to = $end->copy();
            }

            for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
                $byDate[$day->toDateString()][(int) $row->employee_id] = true;
            }
        }

        return $byDate;
    }

    /**
     * The three rates for one working day, as percentages of the roster
     * expected in that day.
     */
    private function dayMetrics(Carbon $day, array $context): array
    {
        $key     = $day->toDateString();
        $present = $context['present'][$key] ?? [];

        $late = 0;
        foreach ($present as $employeeId => $amIn) {
            $scheduled = $this->scheduledStartMinutes($context['schedules'][$employeeId] ?? [], $day);

            if ($this->minutesOfDay($amIn) > $scheduled + AttendanceComputationService::GRACE_MINUTES) {
                $late++;
            }
        }

        $expected = max(
            $context['headcount']
                - count($context['leave'][$key] ?? [])
                - count($context['travel'][$key] ?? []),
            0
        );

        $presentCount = count($present);
        $absent       = max($expected - $presentCount, 0);

        return [
            'date'        => $key,
            'present'     => $presentCount,
            'late'        => $late,
            'expected'    => $expected,
            'rate'        => $expected > 0 ? min(round($presentCount / $expected * 100, 1), 100.0) : 0.0,
            'late_rate'   => $expected > 0 ? min(round($late / $expected * 100, 1), 100.0) : 0.0,
            'absent_rate' => $expected > 0 ? round($absent / $expected * 100, 1) : 0.0,
        ];
    }

    /**
     * The shift start in force for one employee on one day, in minutes past
     * midnight.
     *
     * Mirrors {@see \App\Models\Employee::getScheduleForDate()} exactly — a
     * schedule covers the day when `start_date <= day <= end_date`. The copies
     * are read here in bulk rather than one `schedules` query per employee per
     * day, which is the same answer at a fraction of the cost.
     *
     * @param  array<int, array{start: Carbon, end: Carbon, am_in: ?string}>  $schedules
     */
    private function scheduledStartMinutes(array $schedules, Carbon $day): int
    {
        foreach ($schedules as $schedule) {
            if ($schedule['start']->lte($day) && $schedule['end']->gte($day)) {
                return $schedule['am_in'] ? $this->minutesOfDay($schedule['am_in']) : self::DEFAULT_SHIFT_START_MINUTES;
            }
        }

        return self::DEFAULT_SHIFT_START_MINUTES;
    }

    /** 'HH:MM' or 'HH:MM:SS' as minutes past midnight. */
    private function minutesOfDay(?string $time): int
    {
        if (! $time) {
            return 0;
        }

        $parts = explode(':', $time);

        return ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
    }

    /**
     * One point per working day.
     *
     * @param  array<string, ?array>  $days
     */
    private function bucketByDay(array $days, string $period): array
    {
        $format  = $period === 'week' ? 'D' : 'j';
        $payload = ['labels' => [], 'data' => [], 'lateData' => [], 'absentData' => []];

        foreach ($days as $key => $row) {
            $payload['labels'][]     = Carbon::parse($key)->format($format);
            $payload['data'][]       = $row ? $row['rate'] : null;
            $payload['lateData'][]   = $row ? $row['late_rate'] : null;
            $payload['absentData'][] = $row ? $row['absent_rate'] : null;
        }

        return $payload;
    }

    /**
     * One point per month: the average of that month's working-day rates, on
     * the same 0-100 scale as the other two views. A month with no charted day
     * (all of it still ahead) is a null point.
     *
     * @param  array<string, ?array>  $days
     */
    private function bucketByMonth(array $days, int $year): array
    {
        $payload = ['labels' => [], 'data' => [], 'lateData' => [], 'absentData' => []];

        for ($month = 1; $month <= 12; $month++) {
            $prefix = sprintf('%04d-%02d', $year, $month);
            $rows   = array_filter($days, fn ($row) => $row !== null && str_starts_with($row['date'], $prefix));

            $payload['labels'][]     = Carbon::create($year, $month, 1)->format('M');
            $payload['data'][]       = $this->average($rows, 'rate');
            $payload['lateData'][]   = $this->average($rows, 'late_rate');
            $payload['absentData'][] = $this->average($rows, 'absent_rate');
        }

        return $payload;
    }

    /** @param array<int, array> $rows */
    private function average(array $rows, string $key): ?float
    {
        if ($rows === []) {
            return null;
        }

        return round(array_sum(array_column($rows, $key)) / count($rows), 1);
    }

    /**
     * The period in words, short enough for the empty-state sentence.
     * Built here because the server owns the range; the browser only prints it.
     */
    private function rangeLabel(string $period, Carbon $start, Carbon $end): string
    {
        return match ($period) {
            'week'  => $start->format('M j') . ' – ' . $end->format('M j, Y'),
            'year'  => $start->format('Y'),
            default => $start->format('F Y'),
        };
    }

    private function subLabel(string $period, Carbon $start, Carbon $end): string
    {
        return ($period === 'year' ? 'Monthly average' : 'Working days')
            . ' · ' . $this->rangeLabel($period, $start, $end);
    }
}
