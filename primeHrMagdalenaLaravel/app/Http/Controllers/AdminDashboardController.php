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
            ->limit(5)
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
                    'start_date' => Carbon::parse($leave->start_date)->format('M d, Y'),
                    'end_date' => Carbon::parse($leave->end_date)->format('M d, Y'),
                    'id' => $leave->id,
                ];
            });
        
        // Top 5 Recent Leave Filers
        $recentLeaveFilers = LeaveApplication::with(['employee.employmentDetail.designationRelation', 'leaveType'])
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($leave, $index) {
                $emp = $leave->employee;
                if (!$emp) return null;
                
                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];
                
                $days = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
                
                // Status badge
                $statusColor = $leave->status === 'approved' ? '#22c55e' : ($leave->status === 'pending' ? '#f59e0b' : '#ef4444');
                $statusBg = $leave->status === 'approved' ? '#f0fdf4' : ($leave->status === 'pending' ? '#fffbeb' : '#fef2f2');
                
                return [
                    'rank' => $index + 1,
                    'initials' => $initials,
                    'color' => $colors[$index % count($colors)],
                    'photo' => $emp->photo,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'position' => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'leave_type' => $leave->leaveType->leave_name ?? 'Leave',
                    'days' => $days,
                    'start_date' => Carbon::parse($leave->start_date)->format('M d'),
                    'end_date' => Carbon::parse($leave->end_date)->format('M d'),
                    'status' => ucfirst($leave->status),
                    'status_color' => $statusColor,
                    'status_bg' => $statusBg,
                    'filed_date' => Carbon::parse($leave->created_at)->diffForHumans(),
                ];
            })
            ->filter();
        
        // Department breakdown - ordered by member count (most to least)
        $departments = Department::where('status', 'Active')
            ->withCount(['employmentDetails as employee_count'])
            ->orderByDesc('employee_count')
            ->limit(5)
            ->get()
            ->map(function($dept, $index) {
                $colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#ef4444', '#6366f1', '#14b8a6'];
                return [
                    'name' => $dept->name,
                    'count' => $dept->employee_count,
                    'color' => $colors[$index % count($colors)],
                ];
            });
        
        // Calculate percentage for each department
        $totalInDepts = $departments->sum('count');
        $departments = $departments->map(function($dept) use ($totalInDepts) {
            $dept['percentage'] = $totalInDepts > 0 ? round(($dept['count'] / $totalInDepts) * 100) : 0;
            return $dept;
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
        $earlyBirds = Attendance::with(['employee.employmentDetail.designationRelation', 'employee.employmentDetail.departmentRelation', 'employee.schedule'])
            ->whereDate('date', $today)
            ->whereNotNull('am_in')
            ->orderBy('am_in', 'asc')
            ->limit(5)
            ->get()
            ->map(function($attendance, $index) use ($today) {
                $emp = $attendance->employee;
                if (!$emp) return null;

                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];

                $amIn = Carbon::parse($attendance->am_in);
                $startTime = Carbon::parse('08:00:00');
                $earlyMinutes = $startTime->diffInMinutes($amIn);

                $todayStr = $today->toDateString();
                $activeSchedule = $emp->schedule->first(function($s) use ($todayStr) {
                    $startOk = is_null($s->start_date) || $s->start_date <= $todayStr;
                    $endOk   = is_null($s->end_date)   || $s->end_date   >= $todayStr;
                    return $startOk && $endOk;
                });

                return [
                    'rank'          => $index + 1,
                    'initials'      => $initials,
                    'color'         => $colors[$index % count($colors)],
                    'photo'         => $emp->photo,
                    'name'          => $emp->first_name . ' ' . $emp->last_name,
                    'position'      => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'department'    => $emp->employmentDetail->departmentRelation->name ?? 'N/A',
                    'time_in'       => $amIn->format('h:i A'),
                    'early_minutes' => $earlyMinutes,
                    'schedule'      => $activeSchedule ? [
                        'am_in'      => Carbon::parse($activeSchedule->am_in)->format('h:i A'),
                        'am_out'     => Carbon::parse($activeSchedule->am_out)->format('h:i A'),
                        'pm_in'      => Carbon::parse($activeSchedule->pm_in)->format('h:i A'),
                        'pm_out'     => Carbon::parse($activeSchedule->pm_out)->format('h:i A'),
                        'start_date' => $activeSchedule->start_date ? Carbon::parse($activeSchedule->start_date)->format('M d, Y') : null,
                        'end_date'   => $activeSchedule->end_date   ? Carbon::parse($activeSchedule->end_date)->format('M d, Y')   : null,
                    ] : null,
                ];
            })
            ->filter();

        // Top 5 Late Birds (latest time-in today, must be after 8:05 AM grace period)
        $lateBirds = Attendance::with(['employee.employmentDetail.designationRelation', 'employee.employmentDetail.departmentRelation', 'employee.schedule'])
            ->whereDate('date', $today)
            ->whereNotNull('am_in')
            ->whereTime('am_in', '>', '08:05:00')
            ->orderBy('am_in', 'desc')
            ->limit(5)
            ->get()
            ->map(function($attendance, $index) use ($today) {
                $emp = $attendance->employee;
                if (!$emp) return null;

                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors = ['#8e1e18', '#a16207', '#0b044d', '#7c3aed', '#15803d'];
                $amIn = Carbon::parse($attendance->am_in);
                $lateMinutes = $amIn->diffInMinutes(Carbon::parse('08:05:00'));

                $todayStr = $today->toDateString();
                $activeSchedule = $emp->schedule->first(function($s) use ($todayStr) {
                    $startOk = is_null($s->start_date) || $s->start_date <= $todayStr;
                    $endOk   = is_null($s->end_date)   || $s->end_date   >= $todayStr;
                    return $startOk && $endOk;
                });

                return [
                    'rank'         => $index + 1,
                    'initials'     => $initials,
                    'color'        => $colors[$index % count($colors)],
                    'photo'        => $emp->photo,
                    'name'         => $emp->first_name . ' ' . $emp->last_name,
                    'position'     => $emp->employmentDetail->designationRelation->title ?? 'N/A',
                    'department'   => $emp->employmentDetail->departmentRelation->name ?? 'N/A',
                    'time_in'      => $amIn->format('h:i A'),
                    'late_minutes' => $lateMinutes,
                    'schedule'     => $activeSchedule ? [
                        'am_in'      => Carbon::parse($activeSchedule->am_in)->format('h:i A'),
                        'am_out'     => Carbon::parse($activeSchedule->am_out)->format('h:i A'),
                        'pm_in'      => Carbon::parse($activeSchedule->pm_in)->format('h:i A'),
                        'pm_out'     => Carbon::parse($activeSchedule->pm_out)->format('h:i A'),
                        'start_date' => $activeSchedule->start_date ? Carbon::parse($activeSchedule->start_date)->format('M d, Y') : null,
                        'end_date'   => $activeSchedule->end_date   ? Carbon::parse($activeSchedule->end_date)->format('M d, Y')   : null,
                    ] : null,
                ];
            })
            ->filter();
        
        // Top 5 Highest Earners
        $topEarners = DB::table('daily_salary_computations')
            ->join('employees', 'daily_salary_computations.employee_id', '=', 'employees.id')
            ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->join('designations', 'employment_details.designation_id', '=', 'designations.id')
            ->selectRaw('employees.id, employees.first_name, employees.last_name, employees.photo, designations.title as designation, AVG(daily_salary_computations.daily_basic_pay + daily_salary_computations.ot_pay) as avg_earnings')
            ->whereMonth('daily_salary_computations.work_date', now()->month)
            ->whereYear('daily_salary_computations.work_date', now()->year)
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name', 'employees.photo', 'designations.title')
            ->orderByDesc('avg_earnings')
            ->limit(5)
            ->get()
            ->map(function($earner, $index) {
                $initials = strtoupper(substr($earner->first_name, 0, 1) . substr($earner->last_name, 0, 1));
                $colors = ['#0b044d', '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#14b8a6', '#8e1e18', '#a16207'];
                return [
                    'rank' => $index + 1,
                    'name' => $earner->first_name . ' ' . $earner->last_name,
                    'initials' => $initials,
                    'color' => $colors[$index % count($colors)],
                    'photo' => $earner->photo,
                    'designation' => $earner->designation,
                    'avg_earnings' => round($earner->avg_earnings, 2),
                ];
            });
        
        $stats = [
            'total_employees' => $totalEmployees,
            'new_this_month' => $newThisMonth,
            'present_today' => $presentToday,
            'attendance_rate' => $attendanceRate,
            'on_leave' => $onLeaveToday,
            'pending_leave' => $pendingLeave,
            'monthly_payroll' => $monthlyPayroll,
        ];
        
        return view('admin.dashboard.adminDashboard', compact('stats', 'employees', 'leaveRequests', 'departments', 'chartData', 'earlyBirds', 'lateBirds', 'attendancePerformanceMonth', 'attendancePerformanceWeek', 'topEarners', 'recentLeaveFilers'));
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
            $late = Attendance::whereDate('date', $date)->whereTime('am_in', '>', '08:05:00')->distinct('employee_id')->count();
            $rate = $totalEmp > 0 ? round(($present / $totalEmp) * 100, 1) : 0;
            $lateRate = $totalEmp > 0 ? round(($late / $totalEmp) * 100, 1) : 0;
            $attendanceWeek['labels'][] = $date->format('D');
            $attendanceWeek['data'][] = $rate;
            $attendanceWeek['lateData'][] = $lateRate;
        }
        
        // Month data
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $present = Attendance::whereDate('date', $date)->whereNotNull('am_in')->distinct('employee_id')->count();
            $late = Attendance::whereDate('date', $date)->whereTime('am_in', '>', '08:05:00')->distinct('employee_id')->count();
            $rate = $totalEmp > 0 ? round(($present / $totalEmp) * 100, 1) : 0;
            $lateRate = $totalEmp > 0 ? round(($late / $totalEmp) * 100, 1) : 0;
            $attendanceMonth['labels'][] = $date->format('M j');
            $attendanceMonth['data'][] = $rate;
            $attendanceMonth['lateData'][] = $lateRate;
        }
        
        // Year data (monthly average)
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $avgPresent = Attendance::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->whereNotNull('am_in')
                ->distinct('employee_id')
                ->count();
            $avgLate = Attendance::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->whereTime('am_in', '>', '08:05:00')
                ->distinct('employee_id')
                ->count();
            $daysInMonth = $date->daysInMonth;
            $rate = $totalEmp > 0 ? round(($avgPresent / ($totalEmp * $daysInMonth)) * 100, 1) : 0;
            $lateRate = $totalEmp > 0 ? round(($avgLate / ($totalEmp * $daysInMonth)) * 100, 1) : 0;
            $attendanceYear['labels'][] = $date->format('M');
            $attendanceYear['data'][] = min($rate, 100);
            $attendanceYear['lateData'][] = min($lateRate, 100);
        }
        
        
        // Payroll by designation trends
        $salaryWeek = [];
        $salaryMonth = [];
        $salaryYear = [];
        
        $topDesignations = DB::table('daily_salary_computations')
            ->join('employees', 'daily_salary_computations.employee_id', '=', 'employees.id')
            ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->join('designations', 'employment_details.designation_id', '=', 'designations.id')
            ->selectRaw('designations.id, designations.title, SUM(daily_salary_computations.daily_basic_pay + daily_salary_computations.ot_pay) as total_payroll')
            ->whereMonth('daily_salary_computations.work_date', now()->month)
            ->whereYear('daily_salary_computations.work_date', now()->year)
            ->groupBy('designations.id', 'designations.title')
            ->orderByDesc('total_payroll')
            ->limit(5)
            ->get();
        
        $colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'];
        
        // Week data - cumulative payroll per day
        $salaryWeek['labels'] = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $salaryWeek['labels'][] = $date->format('D');
        }
        $salaryWeek['datasets'] = [];
        foreach ($topDesignations as $index => $designation) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $totalPayroll = DB::table('daily_salary_computations')
                    ->join('employees', 'daily_salary_computations.employee_id', '=', 'employees.id')
                    ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                    ->where('employment_details.designation_id', $designation->id)
                    ->whereDate('daily_salary_computations.work_date', $date)
                    ->sum(DB::raw('daily_basic_pay + ot_pay'));
                $data[] = round($totalPayroll ?? 0, 2);
            }
            $salaryWeek['datasets'][] = ['label' => $designation->title, 'data' => $data, 'color' => $colors[$index]];
        }
        
        // Month data - cumulative payroll per day
        $salaryMonth['labels'] = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $salaryMonth['labels'][] = $date->format('M j');
        }
        $salaryMonth['datasets'] = [];
        foreach ($topDesignations as $index => $designation) {
            $data = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $totalPayroll = DB::table('daily_salary_computations')
                    ->join('employees', 'daily_salary_computations.employee_id', '=', 'employees.id')
                    ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                    ->where('employment_details.designation_id', $designation->id)
                    ->whereDate('daily_salary_computations.work_date', $date)
                    ->sum(DB::raw('daily_basic_pay + ot_pay'));
                $data[] = round($totalPayroll ?? 0, 2);
            }
            $salaryMonth['datasets'][] = ['label' => $designation->title, 'data' => $data, 'color' => $colors[$index]];
        }
        
        // Year data - monthly total payroll
        $salaryYear['labels'] = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $salaryYear['labels'][] = $date->format('M');
        }
        $salaryYear['datasets'] = [];
        foreach ($topDesignations as $index => $designation) {
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = $now->copy()->subMonths($i);
                $totalPayroll = DB::table('daily_salary_computations')
                    ->join('employees', 'daily_salary_computations.employee_id', '=', 'employees.id')
                    ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                    ->where('employment_details.designation_id', $designation->id)
                    ->whereYear('daily_salary_computations.work_date', $date->year)
                    ->whereMonth('daily_salary_computations.work_date', $date->month)
                    ->sum(DB::raw('daily_basic_pay + ot_pay'));
                $data[] = round($totalPayroll ?? 0, 2);
            }
            $salaryYear['datasets'][] = ['label' => $designation->title, 'data' => $data, 'color' => $colors[$index]];
        }
        
        return [
            'employees' => [
                'week' => $employeeWeek,
                'month' => $employeeMonth,
                'year' => $employeeYear,
            ],
            'salaryTrends' => [
                'week' => $salaryWeek,
                'month' => $salaryMonth,
                'year' => $salaryYear,
            ],
            'attendance' => [
                'week' => $attendanceWeek,
                'month' => $attendanceMonth,
                'year' => $attendanceYear,
            ],
        ];
    }
}
