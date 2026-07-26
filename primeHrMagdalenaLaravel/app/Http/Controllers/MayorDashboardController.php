<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MayorDashboardController extends Controller
{
    /** Hire dates, read once per request and reused by every growth range. */
    private ?\Illuminate\Support\Collection $hireDates = null;

    public function index()
    {
        $today = Carbon::today();

        $totalEmployees = Employee::count();
        $newThisMonth = Employee::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Attendance data may lag behind the real calendar date (e.g. still catching
        // up on data entry). Anchor "today's" attendance to the most recent date
        // that actually has records, so the dashboard doesn't show a false zero.
        // Never anchor past today: the table can hold future-dated rows (test or
        // mis-keyed entries), and taking a plain MAX() would pin the dashboard to
        // a date that has not happened yet.
        $latestAttendanceDate = Attendance::whereNotNull('am_in')
            ->whereDate('date', '<=', $today->toDateString())
            ->max('date');
        $attendanceAnchor = $latestAttendanceDate ? Carbon::parse($latestAttendanceDate) : $today;
        $attendanceIsLive = $attendanceAnchor->isSameDay($today);
        $attendanceLabel = $attendanceIsLive ? 'Today' : $attendanceAnchor->format('M d');

        $presentToday = Attendance::whereDate('date', $attendanceAnchor)
            ->whereNotNull('am_in')
            ->distinct('employee_id')
            ->count();

        // Lateness is measured against each employee's own scheduled start, read
        // from the schedules table. It used to be a flat '08:05:00' for everyone,
        // which handed the 07:00 shift 65 minutes of grace instead of 5.
        $lateToday = $this->countLate($attendanceAnchor);

        // Absent = expected but unaccounted for. Anyone on approved leave or an
        // approved travel order is accounted for, so they are excluded from the
        // expected roster rather than being counted as absent, which is what
        // plain (headcount - present) did.
        $onLeaveAnchor  = $this->employeesOnLeave($attendanceAnchor);
        $onTravelAnchor = $this->employeesOnTravel($attendanceAnchor);
        $expectedToday  = max($totalEmployees - $onLeaveAnchor - $onTravelAnchor, 0);
        $absentToday    = max($expectedToday - $presentToday, 0);
        $attendanceRate = $expectedToday > 0 ? round(($presentToday / $expectedToday) * 100, 1) : 0;

        $onLeaveToday = $this->employeesOnLeave($today);
        $pendingLeave = LeaveApplication::where('status', 'pending')->count();

        // Payroll computations may also lag behind the real calendar month, so
        // anchor to the most recent month that has them — but never to a future
        // one. A plain MAX(work_date) landed on 2026-09 (3 stray rows, ₱16.5K)
        // and reported that as "Monthly Payroll", hiding the real current month
        // (93 rows, ₱110.9K). Ignoring rows dated after today fixes the stat
        // card, the trend chart and the designation breakdown together.
        $latestPayrollDate = DB::table('daily_salary_computations')
            ->whereDate('work_date', '<=', $today->toDateString())
            ->max('work_date');
        $payrollAnchor = $latestPayrollDate ? Carbon::parse($latestPayrollDate) : now();
        $payrollIsLive = $payrollAnchor->isSameMonth(now());
        $payrollLabel = $payrollAnchor->format('M Y');

        $monthlyPayroll = DB::table('daily_salary_computations')
            ->whereMonth('work_date', $payrollAnchor->month)
            ->whereYear('work_date', $payrollAnchor->year)
            ->sum(DB::raw('daily_basic_pay + ot_pay'));

        $stats = [
            'total_employees'    => $totalEmployees,
            'new_this_month'     => $newThisMonth,
            'present_today'      => $presentToday,
            'attendance_rate'    => $attendanceRate,
            'attendance_label'   => $attendanceLabel,
            'attendance_is_live' => $attendanceIsLive,
            'expected_today'     => $expectedToday,
            'on_leave_anchor'    => $onLeaveAnchor,
            'on_travel_anchor'   => $onTravelAnchor,
            'on_leave'           => $onLeaveToday,
            'pending_leave'      => $pendingLeave,
            'monthly_payroll'    => $monthlyPayroll,
            'payroll_label'      => $payrollLabel,
            'payroll_is_live'    => $payrollIsLive,
        ];

        // Workforce by department — a categorical palette, so it has to survive
        // colour-vision deficiency. The previous set failed three checks: #1e40af
        // sat outside the lightness band, #065f46 fell under the chroma floor and
        // read grey, and #6d28d9 vs #1e40af scored ΔE 13.2 for *normal* vision —
        // under the 15 floor, i.e. hard to tell apart even with full colour
        // vision. These six are a validated ordering (worst adjacent CVD ΔE 9.1,
        // normal-vision 19.6) and are assigned in fixed order, never cycled.
        $deptColors = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300'];

        // Every staffed department, not just the first six — the card scrolls
        // past the sixth rather than dropping the rest. Departments with nobody
        // assigned are left out: 16 of the 26 active ones are empty, and listing
        // them would bury the actual distribution under zero-length bars.
        $departments = Department::where('status', 'Active')
            ->withCount(['employmentDetails as employee_count'])
            ->having('employee_count', '>', 0)
            ->orderByDesc('employee_count')
            ->orderBy('name')
            ->get()
            ->values()
            ->map(function ($dept, $index) use ($deptColors) {
                return [
                    'name'  => $dept->name,
                    'count' => $dept->employee_count,
                    // Hues are assigned in fixed order and never cycled. Past the
                    // palette the rows take a neutral tone rather than repeating a
                    // colour that already identifies a department above.
                    'color' => $deptColors[$index] ?? '#9aa1b5',
                ];
            });
        $totalInDepts = $departments->sum('count');
        $departments = $departments->map(function ($dept) use ($totalInDepts) {
            $dept['percentage'] = $totalInDepts > 0 ? round(($dept['count'] / $totalInDepts) * 100) : 0;
            return $dept;
        });

        // Compact doughnut: today's attendance status (mutually exclusive segments)
        $attendanceToday = [
            'on_time' => max($presentToday - $lateToday, 0),
            'late'    => $lateToday,
            'absent'  => $absentToday,
            'rate'    => $attendanceRate,
        ];

        // Payroll trend, selectable by period. Anchored to the latest month that
        // actually has computations, not the calendar month, so an empty current
        // month doesn't render the chart as a flat zero.
        //
        // Each period is one grouped query with the buckets filled in afterwards.
        // Querying per point instead (7 + 30 + 12) would be 49 round trips for
        // what three GROUP BYs answer.
        $payrollTrend = [
            'week'  => $this->payrollByDay($payrollAnchor->copy()->subDays(6), $payrollAnchor, 'D'),
            'month' => $this->payrollByDay($payrollAnchor->copy()->subDays(29), $payrollAnchor, 'M j'),
            'year'  => $this->payrollByMonth($payrollAnchor->copy()->subMonths(11), $payrollAnchor),
            'anchorDate'  => $payrollAnchor->format('M j, Y'),
            'anchorMonth' => $payrollAnchor->format('M Y'),
        ];

        // Employee growth — headcount as at the end of each bucket, so the line
        // is a running total rather than per-period hires. Anchored to today,
        // not the payroll anchor: personnel records don't lag the way payroll
        // computations do, and projecting the roster into empty future months
        // would just draw a flat tail.
        $growthAnchor = Carbon::now();
        $employeeGrowth = [
            'week'  => $this->employeeGrowthByDay($growthAnchor->copy()->subDays(6), $growthAnchor, 'D'),
            'month' => $this->employeeGrowthByDay($growthAnchor->copy()->subDays(29), $growthAnchor, 'M j'),
            'year'  => $this->employeeGrowthByMonth($growthAnchor->copy()->subMonths(11), $growthAnchor),
            'anchorDate'  => $growthAnchor->format('M j, Y'),
            'anchorMonth' => $growthAnchor->format('M Y'),
        ];

        // Payroll split by designation — the top earners-by-role, each a series
        // over the same buckets as the payroll trend.
        $topDesignations = DB::table('daily_salary_computations')
            ->join('employment_details', 'daily_salary_computations.employee_id', '=', 'employment_details.employee_id')
            ->join('designations', 'employment_details.designation_id', '=', 'designations.id')
            ->selectRaw('designations.id, designations.title, SUM(daily_salary_computations.daily_basic_pay + daily_salary_computations.ot_pay) AS total')
            // Ranked over the widest window the chart can show, not just the
            // anchor month: picking the top 5 from one month left the year view
            // with a single line, because only that month's payer qualified.
            ->whereBetween('daily_salary_computations.work_date', [
                $payrollAnchor->copy()->subMonths(11)->startOfMonth()->toDateString(),
                $payrollAnchor->copy()->endOfMonth()->toDateString(),
            ])
            ->groupBy('designations.id', 'designations.title')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $payrollByDesignation = [
            'week'  => $this->designationSeriesByDay($payrollAnchor->copy()->subDays(6), $payrollAnchor, 'D', $topDesignations),
            'month' => $this->designationSeriesByDay($payrollAnchor->copy()->subDays(29), $payrollAnchor, 'M j', $topDesignations),
            'year'  => $this->designationSeriesByMonth($payrollAnchor->copy()->subMonths(11), $payrollAnchor, $topDesignations),
            'anchorDate'  => $payrollAnchor->format('M j, Y'),
            'anchorMonth' => $payrollAnchor->format('M Y'),
        ];

        // Compact doughnut: leave requests this month, by status
        $leaveBreakdown = [
            'approved' => LeaveApplication::where('status', 'approved')
                ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'pending' => LeaveApplication::where('status', 'pending')
                ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'rejected' => LeaveApplication::where('status', 'rejected')
                ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        // Highlights panel: top attendance performers this month
        $prevMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $prevMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        // Denominator = the days the office actually recorded attendance, read
        // from the table. Counting Mon–Fri instead assumed a calendar the roster
        // does not follow (22 assumed vs 23 recorded for Jun 2026), which skews
        // every percentage on the leaderboard. Falls back to weekdays only if
        // the period has no records at all.
        $workingDaysMonth = (int) Attendance::whereBetween('date', [$prevMonthStart->toDateString(), $prevMonthEnd->toDateString()])
            ->distinct('date')
            ->count('date');

        if ($workingDaysMonth === 0) {
            for ($d = $prevMonthStart->copy(); $d->lte($prevMonthEnd); $d->addDay()) {
                if (!in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) $workingDaysMonth++;
            }
        }
        $workingDaysMonth = max($workingDaysMonth, 1);

        $attendanceByEmployee = Attendance::whereBetween('date', [$prevMonthStart->toDateString(), $prevMonthEnd->toDateString()])
            ->whereNotNull('am_in')
            ->selectRaw('employee_id, COUNT(*) as present_days')
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $colors = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];

        $topPerformers = Employee::with(['employmentDetail.designationRelation'])
            ->get()
            ->map(function ($emp) use ($attendanceByEmployee, $workingDaysMonth, $colors) {
                $presentDays = $attendanceByEmployee->get($emp->id)->present_days ?? 0;
                return [
                    'name'     => $emp->first_name . ' ' . $emp->last_name,
                    'initials' => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                    'color'    => $colors[$emp->id % count($colors)],
                    'photo'    => $emp->photo,
                    'position' => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'rate'     => min(round(($presentDays / $workingDaysMonth) * 100), 100),
                    // The rate's denominator, so a row states what it is a
                    // percentage of rather than leaving it to be inferred.
                    'days_note' => min($presentDays, $workingDaysMonth) . ' of ' . $workingDaysMonth . ' days',
                ];
            })
            ->sortByDesc('rate')
            ->take(5)
            ->values();

        $perfPeriodMonth = $prevMonthStart->format('F Y');

        // Highlights panel: top 5 highest earners this month
        $topEarners = DB::table('daily_salary_computations')
            ->join('employees', 'daily_salary_computations.employee_id', '=', 'employees.id')
            ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->join('designations', 'employment_details.designation_id', '=', 'designations.id')
            ->selectRaw('employees.id, employees.first_name, employees.last_name, employees.photo, designations.title as designation, AVG(daily_salary_computations.daily_basic_pay + daily_salary_computations.ot_pay) as avg_earnings')
            ->whereMonth('daily_salary_computations.work_date', $payrollAnchor->month)
            ->whereYear('daily_salary_computations.work_date', $payrollAnchor->year)
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name', 'employees.photo', 'designations.title')
            ->orderByDesc('avg_earnings')
            ->limit(5)
            ->get()
            ->map(function ($earner, $index) use ($colors) {
                return [
                    'rank'         => $index + 1,
                    'name'         => $earner->first_name . ' ' . $earner->last_name,
                    'initials'     => strtoupper(substr($earner->first_name, 0, 1) . substr($earner->last_name, 0, 1)),
                    'color'        => $colors[$index % count($colors)],
                    'photo'        => $earner->photo,
                    'designation'  => $earner->designation,
                    'avg_earnings' => round($earner->avg_earnings, 2),
                ];
            });

        /* Pay has no natural ceiling the way an attendance rate does, so the
           earnings meter is scaled to the leader instead of to some invented
           maximum. Each row states its own share in words next to the bar, so
           the relative scale is labelled rather than assumed. */
        $earnerTop = (float) ($topEarners->max('avg_earnings') ?: 0);
        $topEarners = $topEarners->map(function ($earner) use ($earnerTop) {
            $earner['share'] = $earnerTop > 0
                ? (int) round($earner['avg_earnings'] / $earnerTop * 100)
                : 0;
            return $earner;
        });

        // Highlights panel: recent leave activity (status display only)
        $recentLeaveFilers = LeaveApplication::with(['employee.employmentDetail.designationRelation', 'leaveType'])
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($leave) use ($colors) {
                $emp = $leave->employee;
                if (!$emp) return null;

                $start = Carbon::parse($leave->start_date);
                $end   = Carbon::parse($leave->end_date);

                /* The filed figure, not a calendar diff. A Friday-to-Monday
                   application is 2 days on the form and 4 by subtraction, and
                   the panel should agree with the form. Falls back to the span
                   only for rows saved before the column was populated. */
                $days = $leave->number_of_days !== null
                    ? (float) $leave->number_of_days
                    : (int) $start->diffInDays($end) + 1;

                /* "Mar 3 – 7" inside one month, "Mar 30 – Apr 2" across two,
                   and a bare "Mar 3" for a single day. The year is only spelled
                   out when the leave is not in the current one, which keeps the
                   common case short. */
                if ($start->isSameDay($end)) {
                    $range = $start->format('M j');
                } elseif ($start->isSameMonth($end)) {
                    $range = $start->format('M j') . ' – ' . $end->format('j');
                } else {
                    $range = $start->format('M j') . ' – ' . $end->format('M j');
                }
                // Either end being off-year earns the suffix: a range that opens
                // in December and closes in January is in the current year by
                // its end date alone, and "Dec 28 – Jan 4" on its own does not
                // say which December.
                if (!$start->isCurrentYear() || !$end->isCurrentYear()) {
                    $range .= ', ' . $end->format('Y');
                }

                $filed = $leave->created_at ? Carbon::parse($leave->created_at) : null;

                return [
                    'initials'    => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                    'color'       => $colors[$emp->id % count($colors)],
                    'photo'       => $emp->photo,
                    'name'        => $emp->first_name . ' ' . $emp->last_name,
                    'position'    => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'leave_type'  => $leave->leaveType->leave_name ?? 'Leave',
                    'days'        => $days,
                    'days_label'  => rtrim(rtrim(number_format($days, 1), '0'), '.')
                                     . ' ' . ($days == 1 ? 'day' : 'days'),
                    'date_range'  => $range,
                    'status'      => ucfirst($leave->status),
                    // Drives the CSS tone class; the hex pairs this used to ship
                    // are now .is-approved / .is-pending / .is-rejected rules.
                    'status_key'  => strtolower($leave->status),
                    'filed_short' => $filed?->diffForHumans(['short' => true, 'parts' => 1]),
                    'filed_full'  => $filed?->format('M j, Y · g:i A'),
                ];
            })
            ->filter()
            ->values();

        return view('mayor.dashboard.mayorDashboard', compact(
            'stats',
            'departments',
            'attendanceToday',
            'attendanceAnchor',
            'payrollAnchor',
            'payrollTrend',
            'payrollByDesignation',
            'employeeGrowth',
            'leaveBreakdown',
            'topPerformers',
            'perfPeriodMonth',
            'topEarners',
            'recentLeaveFilers'
        ));
    }

    /**
     * Payroll totalled per day across an inclusive date range.
     *
     * One grouped query; days with no computations are filled with 0 so the
     * line keeps an even time axis instead of skipping straight over gaps.
     */
    private function payrollByDay(Carbon $from, Carbon $to, string $labelFormat): array
    {
        $totals = DB::table('daily_salary_computations')
            ->selectRaw('work_date, SUM(daily_basic_pay + ot_pay) AS total')
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('work_date')
            ->pluck('total', 'work_date');

        // Keys come back as Y-m-d strings on MySQL but as datetimes on some
        // drivers — normalise before looking them up.
        $byDate = [];
        foreach ($totals as $date => $total) {
            $byDate[Carbon::parse($date)->toDateString()] = (float) $total;
        }

        $trend = ['labels' => [], 'data' => []];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $trend['labels'][] = $day->format($labelFormat);
            $trend['data'][]   = round($byDate[$day->toDateString()] ?? 0, 2);
        }

        return $trend;
    }

    /**
     * Minutes of grace allowed past the scheduled start before a time-in counts
     * as late. Named rather than baked into a '08:05:00' literal.
     */
    private const LATE_GRACE_MINUTES = 5;

    /** Fallback start time for employees with no schedule row on the date. */
    private const DEFAULT_SHIFT_START = '08:00:00';

    /**
     * Employees who clocked in later than their own scheduled start (plus grace).
     *
     * Two queries — the day's time-ins and the schedules covering that day —
     * compared in PHP, because the comparison is per employee.
     */
    private function countLate(Carbon $date): int
    {
        $timeIns = Attendance::whereDate('date', $date)
            ->whereNotNull('am_in')
            ->pluck('am_in', 'employee_id');

        if ($timeIns->isEmpty()) {
            return 0;
        }

        $starts = DB::table('schedules')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->pluck('am_in', 'employee_id');

        $late = 0;
        foreach ($timeIns as $employeeId => $actual) {
            $scheduled = $starts[$employeeId] ?? self::DEFAULT_SHIFT_START;
            $cutoff = Carbon::parse($scheduled)->addMinutes(self::LATE_GRACE_MINUTES);

            if (Carbon::parse($actual)->gt($cutoff)) {
                $late++;
            }
        }

        return $late;
    }

    /** Distinct employees on approved leave covering the given date. */
    private function employeesOnLeave(Carbon $date): int
    {
        return (int) LeaveApplication::where('status', 'approved')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->distinct('employee_id')
            ->count('employee_id');
    }

    /** Distinct employees on an approved travel order covering the given date. */
    private function employeesOnTravel(Carbon $date): int
    {
        if (!Schema::hasTable('travel_orders')) {
            return 0;
        }

        return (int) DB::table('travel_orders')
            ->where('status', 'approved')
            ->whereDate('travel_date', '<=', $date->toDateString())
            ->whereDate('return_date', '>=', $date->toDateString())
            ->distinct('employee_id')
            ->count('employee_id');
    }

    /**
     * Categorical palette for the designation series.
     *
     * Assigned in fixed order, never cycled. The admin dashboard's equivalent
     * set fails colour-vision checks — its first two slots (#8b5cf6 vs #3b82f6)
     * score ΔE 1.3 under deuteranopia and 12.0 for normal vision, so the two
     * largest designations are near-indistinguishable. These five are validated
     * (worst adjacent CVD ΔE 9.1, normal-vision 19.6); the sub-3:1 contrast on
     * three of them is relieved by the legend, which is always present here.
     */
    private const SERIES_COLORS = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4'];

    /**
     * Payroll per designation, bucketed by day.
     *
     * One grouped query for the whole matrix, pivoted in PHP. The per-point
     * pattern would be designations × buckets — 5 × 30 = 150 queries for the
     * month range alone.
     */
    private function designationSeriesByDay(Carbon $from, Carbon $to, string $labelFormat, $designations): array
    {
        $ids = collect($designations)->pluck('id')->all();

        $rows = empty($ids) ? collect() : DB::table('daily_salary_computations')
            ->join('employment_details', 'daily_salary_computations.employee_id', '=', 'employment_details.employee_id')
            ->selectRaw('employment_details.designation_id AS did, daily_salary_computations.work_date AS bucket, SUM(daily_basic_pay + ot_pay) AS total')
            ->whereIn('employment_details.designation_id', $ids)
            ->whereBetween('daily_salary_computations.work_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('did', 'bucket')
            ->get();

        $matrix = [];
        foreach ($rows as $row) {
            $matrix[$row->did][Carbon::parse($row->bucket)->toDateString()] = (float) $row->total;
        }

        $labels = [];
        $keys = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $labels[] = $day->format($labelFormat);
            $keys[]   = $day->toDateString();
        }

        return ['labels' => $labels, 'datasets' => $this->buildDesignationDatasets($designations, $matrix, $keys)];
    }

    /**
     * Payroll per designation, bucketed by calendar month.
     */
    private function designationSeriesByMonth(Carbon $from, Carbon $to, $designations): array
    {
        $ids = collect($designations)->pluck('id')->all();

        $rows = empty($ids) ? collect() : DB::table('daily_salary_computations')
            ->join('employment_details', 'daily_salary_computations.employee_id', '=', 'employment_details.employee_id')
            ->selectRaw("employment_details.designation_id AS did, DATE_FORMAT(daily_salary_computations.work_date, '%Y-%m') AS bucket, SUM(daily_basic_pay + ot_pay) AS total")
            ->whereIn('employment_details.designation_id', $ids)
            ->whereBetween('daily_salary_computations.work_date', [$from->copy()->startOfMonth()->toDateString(), $to->copy()->endOfMonth()->toDateString()])
            ->groupBy('did', 'bucket')
            ->get();

        $matrix = [];
        foreach ($rows as $row) {
            $matrix[$row->did][$row->bucket] = (float) $row->total;
        }

        $labels = [];
        $keys = [];
        for ($month = $from->copy()->startOfMonth(); $month->lte($to); $month->addMonth()) {
            $labels[] = $month->format('M');
            $keys[]   = $month->format('Y-m');
        }

        return ['labels' => $labels, 'datasets' => $this->buildDesignationDatasets($designations, $matrix, $keys)];
    }

    /**
     * Turn the pivoted matrix into one dataset per designation, zero-filled so
     * every series spans the same buckets.
     */
    private function buildDesignationDatasets($designations, array $matrix, array $keys): array
    {
        $datasets = [];
        foreach ($designations as $index => $designation) {
            $data = [];
            foreach ($keys as $key) {
                $data[] = round($matrix[$designation->id][$key] ?? 0, 2);
            }
            $datasets[] = [
                'label' => $designation->title,
                'data'  => $data,
                'color' => self::SERIES_COLORS[$index % count(self::SERIES_COLORS)],
            ];
        }

        return $datasets;
    }

    /**
     * Headcount as at the end of each day in an inclusive range.
     */
    private function employeeGrowthByDay(Carbon $from, Carbon $to, string $labelFormat): array
    {
        $trend = ['labels' => [], 'data' => []];
        $ends = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $trend['labels'][] = $day->format($labelFormat);
            $ends[] = $day->copy()->endOfDay();
        }
        $trend['data'] = $this->cumulativeEmployees($ends);

        return $trend;
    }

    /**
     * Headcount as at the end of each month in an inclusive range.
     */
    private function employeeGrowthByMonth(Carbon $from, Carbon $to): array
    {
        $trend = ['labels' => [], 'data' => []];
        $ends = [];
        for ($month = $from->copy()->startOfMonth(); $month->lte($to); $month->addMonth()) {
            $trend['labels'][] = $month->format('M');
            $ends[] = $month->copy()->endOfMonth();
        }
        $trend['data'] = $this->cumulativeEmployees($ends);

        return $trend;
    }

    /**
     * Running headcount at each of the given cut-offs (which must be ascending).
     *
     * Reads every hire date once and walks it alongside the buckets, rather than
     * issuing a COUNT per point — the per-point pattern costs 49 queries across
     * the three ranges to answer what one query and a pointer already can.
     */
    private function cumulativeEmployees(array $cutoffs): array
    {
        // Memoised: the three ranges are built in one request, and they all walk
        // the same list of hire dates.
        $hireDates = $this->hireDates ??= Employee::orderBy('created_at')
            ->pluck('created_at')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date))
            ->values();

        $counts = [];
        $index = 0;
        $running = 0;
        $total = $hireDates->count();

        foreach ($cutoffs as $cutoff) {
            while ($index < $total && $hireDates[$index]->lte($cutoff)) {
                $running++;
                $index++;
            }
            $counts[] = $running;
        }

        return $counts;
    }

    /**
     * Payroll totalled per calendar month across an inclusive month range.
     */
    private function payrollByMonth(Carbon $from, Carbon $to): array
    {
        $totals = DB::table('daily_salary_computations')
            ->selectRaw("DATE_FORMAT(work_date, '%Y-%m') AS ym, SUM(daily_basic_pay + ot_pay) AS total")
            ->whereBetween('work_date', [$from->copy()->startOfMonth()->toDateString(), $to->copy()->endOfMonth()->toDateString()])
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $trend = ['labels' => [], 'data' => []];
        for ($month = $from->copy()->startOfMonth(); $month->lte($to); $month->addMonth()) {
            $trend['labels'][] = $month->format('M');
            $trend['data'][]   = round((float) ($totals[$month->format('Y-m')] ?? 0), 2);
        }

        return $trend;
    }
}
