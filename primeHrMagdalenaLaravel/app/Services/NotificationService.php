<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a leave request notification for admin
     */
    public static function leaveRequestSubmitted($leaveApplication)
    {
        $employee = $leaveApplication->employee;
        $employeeName = $employee->first_name . ' ' . $employee->last_name;
        
        // Notify all admin users
        $admins = User::where('role', 'admin')->orWhere('role', 'hr')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'leave_request',
                'title' => 'New Leave Request',
                'message' => "{$employeeName} submitted a {$leaveApplication->leaveType->leave_name} request for {$leaveApplication->number_of_days} day(s).",
                'link' => route('admin.leave', ['tab' => 'requests']),
                'related_id' => $leaveApplication->id,
                'related_type' => 'App\Models\LeaveApplication',
            ]);
        }
    }

    /**
     * Create a leave request status notification for employee
     */
    public static function leaveRequestStatusChanged($leaveApplication, $status)
    {
        $employee = $leaveApplication->employee;
        
        if (!$employee->user) {
            \Log::warning('Cannot send notification: Employee has no user account', [
                'employee_id' => $employee->id
            ]);
            return;
        }
        
        $statusText = ucfirst($status);
        $message = "Your {$leaveApplication->leaveType->leave_name} request has been {$statusText}.";
        
        try {
            Notification::create([
                'user_id' => $employee->user->id,
                'type' => 'leave_request',
                'title' => "Leave Request {$statusText}",
                'message' => $message,
                'link' => route('permanent.leave'),
                'related_id' => $leaveApplication->id,
                'related_type' => 'App\Models\LeaveApplication',
            ]);
            
            \Log::info('Notification created successfully', ['user_id' => $employee->user->id]);
        } catch (\Exception $e) {
            \Log::error('Failed to create notification: ' . $e->getMessage());
        }
    }

    /**
     * Create a training submission notification for admin
     */
    public static function trainingSubmitted($training)
    {
        $employee = $training->employee;
        $employeeName = $employee->first_name . ' ' . $employee->last_name;
        
        $admins = User::where('role', 'admin')->orWhere('role', 'hr')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'training',
                'title' => 'New Training Submission',
                'message' => "{$employeeName} submitted a training record: {$training->title}",
                'link' => route('admin.training'),
                'related_id' => $training->id,
                'related_type' => 'App\Models\Training',
            ]);
        }
    }

    /**
     * Create a training verification notification for employee
     */
    public static function trainingVerified($training, $status)
    {
        $employee = $training->employee;
        
        if (!$employee->user) return;
        
        $statusText = $status === 'verified' ? 'Verified' : 'Rejected';
        $message = "Your training record '{$training->title}' has been {$statusText}.";
        
        Notification::create([
            'user_id' => $employee->user->id,
            'type' => 'training',
            'title' => "Training {$statusText}",
            'message' => $message,
            'link' => route('permanent.training'),
            'related_id' => $training->id,
            'related_type' => 'App\Models\Training',
        ]);
    }

    /**
     * Create a payroll generated notification
     */
    public static function payrollGenerated($startDate, $endDate, $employeeIds = [])
    {
        $period = date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate));
        
        if (empty($employeeIds)) {
            // Notify all employees
            $users = User::whereHas('employee')->get();
        } else {
            // Notify specific employees
            $users = User::whereHas('employee', function($q) use ($employeeIds) {
                $q->whereIn('id', $employeeIds);
            })->get();
        }
        
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'payroll',
                'title' => 'Payroll Available',
                'message' => "Your payslip for {$period} is now available.",
                'link' => route('permanent.payslip'),
            ]);
        }
    }

    /**
     * Create an attendance correction notification
     */
    public static function attendanceCorrected($attendance)
    {
        $employee = $attendance->employee;
        
        if (!$employee->user) return;
        
        Notification::create([
            'user_id' => $employee->user->id,
            'type' => 'attendance',
            'title' => 'Attendance Corrected',
            'message' => "Your attendance record for " . date('M d, Y', strtotime($attendance->date)) . " has been corrected by HR.",
            'link' => route('permanent.attendance'),
            'related_id' => $attendance->id,
            'related_type' => 'App\Models\Attendance',
        ]);
    }

    /**
     * Create a system notification for all users or specific role
     */
    public static function systemNotification($title, $message, $role = null)
    {
        $query = User::query();
        
        if ($role) {
            $query->where('role', $role);
        }
        
        $users = $query->get();
        
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'system',
                'title' => $title,
                'message' => $message,
            ]);
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userId)
    {
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Create a payslip request notification for admin
     */
    public static function payslipRequested($request)
    {
        $employee = $request->employee;
        $employeeName = $employee->first_name . ' ' . $employee->last_name;
        
        $admins = User::where('role', 'admin')->orWhere('role', 'hr')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'request',
                'title' => 'Payslip Request',
                'message' => "{$employeeName} requested a payslip: {$request->description}",
                'link' => route('admin.requests'),
                'related_id' => $request->id,
                'related_type' => 'App\\Models\\EmployeeRequest',
            ]);
        }
    }

    /**
     * Create a deduction inquiry notification for admin
     */
    public static function deductionInquiry($request)
    {
        $employee = $request->employee;
        $employeeName = $employee->first_name . ' ' . $employee->last_name;
        
        $admins = User::where('role', 'admin')->orWhere('role', 'hr')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'request',
                'title' => 'Deduction Inquiry',
                'message' => "{$employeeName} has a question about deductions: {$request->description}",
                'link' => route('admin.requests'),
                'related_id' => $request->id,
                'related_type' => 'App\\Models\\EmployeeRequest',
            ]);
        }
    }

    /**
     * Create a general employee request notification for admin
     */
    public static function employeeRequestSubmitted($request)
    {
        $employee = $request->employee;
        $employeeName = $employee->first_name . ' ' . $employee->last_name;
        
        $admins = User::where('role', 'admin')->orWhere('role', 'hr')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'request',
                'title' => $request->request_type_name,
                'message' => "{$employeeName} submitted a request: {$request->title}",
                'link' => route('admin.requests'),
                'related_id' => $request->id,
                'related_type' => 'App\\Models\\EmployeeRequest',
            ]);
        }
    }

    /**
     * Create a request status notification for employee
     */
    public static function requestStatusChanged($request, $status)
    {
        $employee = $request->employee;
        
        if (!$employee->user) return;
        
        $statusText = ucfirst($status);
        $message = "Your {$request->request_type_name} has been {$statusText}.";
        
        if ($request->admin_response) {
            $message .= " Response: {$request->admin_response}";
        }
        
        Notification::create([
            'user_id' => $employee->user->id,
            'type' => 'request',
            'title' => "Request {$statusText}",
            'message' => $message,
            'link' => route('permanent.requests'),
            'related_id' => $request->id,
            'related_type' => 'App\\Models\\EmployeeRequest',
        ]);
    }
}
