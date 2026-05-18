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
        
        // Employee directory
        $employees = Employee::with(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($emp) {
                $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                $colors = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];
                $color = $colors[array_rand($colors)];
                
                return [
                    'initials' => $initials,
                    'color' => $color,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'id' => $emp->employee_id,
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
        
        return view('admin.dashboard.adminDashboard', compact('stats', 'employees', 'leaveRequests', 'departments'));
    }
}
