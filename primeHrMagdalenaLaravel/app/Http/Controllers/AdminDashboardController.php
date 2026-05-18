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
        
        $stats = [
            'total_employees' => $totalEmployees,
            'new_this_month' => $newThisMonth,
            'present_today' => $presentToday,
            'attendance_rate' => $attendanceRate,
            'on_leave' => $onLeaveToday,
            'pending_leave' => $pendingLeave,
            'monthly_payroll' => $monthlyPayroll,
        ];
        
        return view('admin.dashboard.adminDashboard', compact('stats', 'employees', 'leaveRequests', 'departments', 'chartData'));
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
