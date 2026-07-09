<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MayorDashboardController extends Controller
{
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
        $latestAttendanceDate = Attendance::whereNotNull('am_in')->max('date');
        $attendanceAnchor = $latestAttendanceDate ? Carbon::parse($latestAttendanceDate) : $today;
        $attendanceIsLive = $attendanceAnchor->isSameDay($today);
        $attendanceLabel = $attendanceIsLive ? 'Today' : $attendanceAnchor->format('M d');

        $presentToday = Attendance::whereDate('date', $attendanceAnchor)
            ->whereNotNull('am_in')
            ->distinct('employee_id')
            ->count();
        $lateToday = Attendance::whereDate('date', $attendanceAnchor)
            ->whereNotNull('am_in')
            ->whereTime('am_in', '>', '08:05:00')
            ->distinct('employee_id')
            ->count();
        $absentToday = max($totalEmployees - $presentToday, 0);
        $attendanceRate = $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100, 1) : 0;

        $onLeaveToday = LeaveApplication::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();
        $pendingLeave = LeaveApplication::where('status', 'pending')->count();

        // Payroll computations may also lag behind the real calendar month.
        // Anchor "monthly payroll" to the most recent month that has computations.
        $latestPayrollDate = DB::table('daily_salary_computations')->max('work_date');
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
            'on_leave'           => $onLeaveToday,
            'pending_leave'      => $pendingLeave,
            'monthly_payroll'    => $monthlyPayroll,
            'payroll_label'      => $payrollLabel,
            'payroll_is_live'    => $payrollIsLive,
        ];

        // Compact doughnut: workforce by department
        $deptColors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#ef4444'];
        $departments = Department::where('status', 'Active')
            ->withCount(['employmentDetails as employee_count'])
            ->orderByDesc('employee_count')
            ->limit(6)
            ->get()
            ->values()
            ->map(function ($dept, $index) use ($deptColors) {
                return [
                    'name'  => $dept->name,
                    'count' => $dept->employee_count,
                    'color' => $deptColors[$index % count($deptColors)],
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

        // Compact bar: payroll trend, 6 months ending at the anchor month (total only, not by designation)
        $payrollTrend = ['labels' => [], 'data' => []];
        for ($i = 5; $i >= 0; $i--) {
            $date = $payrollAnchor->copy()->subMonths($i);
            $total = DB::table('daily_salary_computations')
                ->whereMonth('work_date', $date->month)
                ->whereYear('work_date', $date->year)
                ->sum(DB::raw('daily_basic_pay + ot_pay'));
            $payrollTrend['labels'][] = $date->format('M');
            $payrollTrend['data'][] = round($total ?? 0, 2);
        }

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
        $workingDaysMonth = 0;
        for ($d = $prevMonthStart->copy(); $d->lte($prevMonthEnd); $d->addDay()) {
            if (!in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) $workingDaysMonth++;
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

        // Highlights panel: recent leave activity (status display only)
        $recentLeaveFilers = LeaveApplication::with(['employee.employmentDetail.designationRelation', 'leaveType'])
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($leave) use ($colors) {
                $emp = $leave->employee;
                if (!$emp) return null;

                $days = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
                $statusColor = $leave->status === 'approved' ? '#22c55e' : ($leave->status === 'pending' ? '#f59e0b' : '#ef4444');
                $statusBg = $leave->status === 'approved' ? '#f0fdf4' : ($leave->status === 'pending' ? '#fffbeb' : '#fef2f2');

                return [
                    'initials'     => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                    'color'        => $colors[$emp->id % count($colors)],
                    'photo'        => $emp->photo,
                    'name'         => $emp->first_name . ' ' . $emp->last_name,
                    'position'     => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'leave_type'   => $leave->leaveType->leave_name ?? 'Leave',
                    'days'         => $days,
                    'status'       => ucfirst($leave->status),
                    'status_color' => $statusColor,
                    'status_bg'    => $statusBg,
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
            'leaveBreakdown',
            'topPerformers',
            'perfPeriodMonth',
            'topEarners',
            'recentLeaveFilers'
        ));
    }
}
