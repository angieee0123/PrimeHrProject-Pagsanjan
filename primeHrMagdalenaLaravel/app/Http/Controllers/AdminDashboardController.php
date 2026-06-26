<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Total employees
        $totalEmployees = Employee::count();
        $newThisMonth = Employee::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Present today
        $presentToday = Attendance::whereDate('date', $today)
            ->whereNotNull('am_in')
            ->distinct('employee_id')
            ->count();
        $attendanceRate = $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100, 1) : 0;
        
        // On leave
        $onLeaveToday = LeaveApplication::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();
        $pendingLeave = LeaveApplication::where('status', 'pending')->count();
        
        // Monthly payroll (from salary computations)
        $monthlyPayroll = DB::table('daily_salary_computations')
            ->whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->sum(DB::raw('daily_basic_pay + ot_pay'));
        
        // Chart data
        $chartData = $this->getChartData();
        
        // Employee directory
        $employees = Employee::with(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->through(function($emp) {
                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];
                $color = $colors[array_rand($colors)];
                
                return [
                    'id' => $emp->id,
                    'initials' => $initials,
                    'color' => $color,
                    'photo' => $emp->photo,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'employee_id' => $emp->employee_id,
                    'position' => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'dept' => $emp->employmentDetail->departmentRelation->name ?? 'N/A',
                    'type' => $emp->employmentDetail->employment_status ?? 'N/A',
                    'status' => $emp->user && $emp->user->status === 'Active' ? 'active' : 'inactive',
                ];
            });
        
        // Pending leave requests
        $leaveRequests = LeaveApplication::with(['employee', 'leaveType'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($leave) {
                $emp = $leave->employee;
                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors = ['#0b044d', '#8e1e18', '#a16207'];
                $color = $colors[array_rand($colors)];
                
                $days = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
                
                return [
                    'initials' => $initials,
                    'color' => $color,
                    'photo' => $emp->photo,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'type' => $leave->leaveType->leave_name ?? 'Leave',
                    'days' => $days . ' day' . ($days > 1 ? 's' : ''),
                    'id' => $leave->id,
                ];
            });
        
        // Department breakdown
        $departments = Department::where('status', 'Active')
            ->withCount(['employmentDetails as employee_count'])
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(function($dept) {
                $colors = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];
                return [
                    'name' => $dept->name,
                    'count' => $dept->employee_count,
                    'color' => $colors[array_rand($colors)],
                ];
            });
        
        // Attendance Performance Rating (this month)
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();
        $weekStart  = Carbon::now()->startOfWeek();
        $weekEnd    = Carbon::now()->endOfWeek();

        $workingDaysMonth = 0;
        for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
            if (!in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) $workingDaysMonth++;
        }
        $workingDaysWeek = 0;
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            if (!in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) $workingDaysWeek++;
        }
        $workingDaysMonth = max($workingDaysMonth, 1);
        $workingDaysWeek  = max($workingDaysWeek, 1);

        $allEmployees = Employee::with(['employmentDetail.designationRelation', 'employmentDetail.departmentRelation'])->get();

        $buildPerformance = function($start, $end, $workingDays) use ($allEmployees) {
            $attendanceData = Attendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereNotNull('am_in')
                ->selectRaw('employee_id, COUNT(*) as present_days, SUM(CASE WHEN TIME(am_in) > "08:05:00" THEN 1 ELSE 0 END) as late_days')
                ->groupBy('employee_id')
                ->get()
                ->keyBy('employee_id');

            return $allEmployees->map(function($emp) use ($attendanceData, $workingDays) {
                $record     = $attendanceData->get($emp->id);
                $presentDays = $record ? $record->present_days : 0;
                $lateDays    = $record ? $record->late_days    : 0;
                $absentDays  = $workingDays - $presentDays;
                $rate        = round(($presentDays / $workingDays) * 100);

                if ($rate >= 95)      $tier = 'excellent';
                elseif ($rate >= 80)  $tier = 'good';
                elseif ($rate >= 60)  $tier = 'needs_improvement';
                else                  $tier = 'poor';

                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors   = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];

                return [
                    'id'           => $emp->id,
                    'name'         => $emp->first_name . ' ' . $emp->last_name,
                    'initials'     => $initials,
                    'color'        => $colors[$emp->id % count($colors)],
                    'photo'        => $emp->photo,
                    'position'     => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'department'   => $emp->employmentDetail->departmentRelation->name ?? 'N/A',
                    'present_days' => $presentDays,
                    'absent_days'  => max($absentDays, 0),
                    'late_days'    => $lateDays,
                    'rate'         => $rate,
                    'tier'         => $tier,
                    'working_days' => $workingDays,
                ];
            })->sortByDesc('rate')->values();
        };

        $attendancePerformanceMonth = $buildPerformance($monthStart, Carbon::now(), $workingDaysMonth);
        $attendancePerformanceWeek  = $buildPerformance($weekStart, Carbon::now(), $workingDaysWeek);
        $earlyBirds = Attendance::with(['employee.employmentDetail.designationRelation'])
            ->whereDate('date', $today)
            ->whereNotNull('am_in')
            ->orderBy('am_in', 'asc')
            ->limit(10)
            ->get()
            ->map(function($attendance, $index) {
                $emp = $attendance->employee;
                if (!$emp) return null;
                
                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];
                
                return [
                    'rank' => $index + 1,
                    'initials' => $initials,
                    'color' => $colors[$index % count($colors)],
                    'photo' => $emp->photo,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'position' => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'time_in' => Carbon::parse($attendance->am_in)->format('h:i A'),
                ];
            })
            ->filter();

        // Top 10 Late Birds (latest time-in today, must be after 8:05 AM grace period)
        $lateBirds = Attendance::with(['employee.employmentDetail.designationRelation'])
            ->whereDate('date', $today)
            ->whereNotNull('am_in')
            ->whereTime('am_in', '>', '08:05:00')
            ->orderBy('am_in', 'desc')
            ->limit(10)
            ->get()
            ->map(function($attendance, $index) {
                $emp = $attendance->employee;
                if (!$emp) return null;

                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors = ['#8e1e18', '#a16207', '#0b044d', '#7c3aed', '#15803d'];
                $amIn = Carbon::parse($attendance->am_in);
                $lateMinutes = $amIn->diffInMinutes(Carbon::parse('08:05:00'));

                return [
                    'rank' => $index + 1,
                    'initials' => $initials,
                    'color' => $colors[$index % count($colors)],
                    'photo' => $emp->photo,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'position' => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'time_in' => $amIn->format('h:i A'),
                    'late_minutes' => $lateMinutes,
                ];
            })
            ->filter();
        
        $stats = [
            'total_employees' => $totalEmployees,
            'new_this_month' => $newThisMonth,
            'present_today' => $presentToday,
            'attendance_rate' => $attendanceRate,
            'on_leave' => $onLeaveToday,
            'pending_leave' => $pendingLeave,
            'monthly_payroll' => $monthlyPayroll,
        ];
        
        return view('admin.dashboard.adminDashboard', compact('stats', 'employees', 'leaveRequests', 'departments', 'chartData', 'earlyBirds', 'lateBirds', 'attendancePerformanceMonth', 'attendancePerformanceWeek'));
    }
    
    private function getChartData()
    {
        $now = Carbon::now();
        
        // Employee growth data
        $employeeWeek = [];
        $employeeMonth = [];
        $employeeYear = [];
        
        // Week data (last 7 days)
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $count = Employee::whereDate('created_at', '<=', $date)->count();
            $employeeWeek['labels'][] = $date->format('D');
            $employeeWeek['data'][] = $count;
        }
        
        // Month data (last 30 days)
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $count = Employee::whereDate('created_at', '<=', $date)->count();
            $employeeMonth['labels'][] = $date->format('M j');
            $employeeMonth['data'][] = $count;
        }
        
        // Year data (last 12 months)
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $count = Employee::whereYear('created_at', '<=', $date->year)
                ->whereMonth('created_at', '<=', $date->month)
                ->count();
            $employeeYear['labels'][] = $date->format('M');
            $employeeYear['data'][] = $count;
        }
        
        // Attendance rate data
        $attendanceWeek = [];
        $attendanceMonth = [];
        $attendanceYear = [];
        
        $totalEmp = Employee::count();
        
        // Week data
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $present = Attendance::whereDate('date', $date)->whereNotNull('am_in')->distinct('employee_id')->count();
            $rate = $totalEmp > 0 ? round(($present / $totalEmp) * 100, 1) : 0;
            $attendanceWeek['labels'][] = $date->format('D');
            $attendanceWeek['data'][] = $rate;
        }
        
        // Month data
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $present = Attendance::whereDate('date', $date)->whereNotNull('am_in')->distinct('employee_id')->count();
            $rate = $totalEmp > 0 ? round(($present / $totalEmp) * 100, 1) : 0;
            $attendanceMonth['labels'][] = $date->format('M j');
            $attendanceMonth['data'][] = $rate;
        }
        
        // Year data (monthly average)
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $avgPresent = Attendance::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->whereNotNull('am_in')
                ->distinct('employee_id')
                ->count();
            $daysInMonth = $date->daysInMonth;
            $rate = $totalEmp > 0 ? round(($avgPresent / ($totalEmp * $daysInMonth)) * 100, 1) : 0;
            $attendanceYear['labels'][] = $date->format('M');
            $attendanceYear['data'][] = min($rate, 100);
        }
        
        return [
            'employees' => [
                'week' => $employeeWeek,
                'month' => $employeeMonth,
                'year' => $employeeYear,
            ],
            'attendance' => [
                'week' => $attendanceWeek,
                'month' => $attendanceMonth,
                'year' => $attendanceYear,
            ],
        ];
    }
}
