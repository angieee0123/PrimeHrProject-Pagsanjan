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
        $admins = User::where(function ($q) {
            $q->whereJsonContains('roles', 'admin')->orWhereJsonContains('roles', 'hr');
        })->get();
        
        foreach ($admins as $admin) {
            if (!$admin->wantsNotification('leave_requests')) continue;

            Notification::create([
                'user_id' => $admin->id,
                'type' => 'leave_request',
                'audience' => 'admin',
                'title' => 'New Leave Request',
                'message' => "{$employeeName} submitted a {$leaveApplication->leaveType->leave_name} request for {$leaveApplication->number_of_days} day(s).",
                'link' => route('admin.leave', ['highlight' => $leaveApplication->id]),
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
                'audience' => 'employee',
                'title' => "Leave Request {$statusText}",
                'message' => $message,
                'link' => route('employee.leave', ['highlight' => $leaveApplication->id]),
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
        
        $admins = User::where(function ($q) {
            $q->whereJsonContains('roles', 'admin')->orWhereJsonContains('roles', 'hr');
        })->get();
        
        foreach ($admins as $admin) {
            if (!$admin->wantsNotification('training_submissions')) continue;

            Notification::create([
                'user_id' => $admin->id,
                'type' => 'training',
                'audience' => 'admin',
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
            'audience' => 'employee',
            'title' => "Training {$statusText}",
            'message' => $message,
            'link' => route('employee.training'),
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
                'audience' => 'employee',
                'title' => 'Payroll Available',
                'message' => "Your payslip for {$period} is now available.",
                'link' => route('employee.payslip'),
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
            'audience' => 'employee',
            'title' => 'Attendance Corrected',
            'message' => "Your attendance record for " . date('M d, Y', strtotime($attendance->date)) . " has been corrected by HR.",
            'link' => route('employee.attendance'),
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
            $query->whereJsonContains('roles', $role);
        }
        
        $users = $query->get();
        $audience = in_array($role, ['admin', 'hr'], true) ? 'admin' : 'system';

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'system',
                'audience' => $audience,
                'title' => $title,
                'message' => $message,
            ]);
        }
    }

    /**
     * Mark all notifications as read for a user. Pass the panel's audience
     * ('admin' or 'employee') so clearing one dashboard's bell does not also
     * clear the other's; null keeps the old clear-everything behavior.
     */
    public static function markAllAsRead($userId, $audience = null)
    {
        $query = Notification::where('user_id', $userId)
            ->where('is_read', false);

        if ($audience === 'admin') {
            $query->forAdmin();
        } elseif ($audience === 'employee') {
            $query->forEmployee();
        }

        $query->update([
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
        
        $admins = User::where(function ($q) {
            $q->whereJsonContains('roles', 'admin')->orWhereJsonContains('roles', 'hr');
        })->get();
        
        foreach ($admins as $admin) {
            if (!$admin->wantsNotification('employee_requests')) continue;

            Notification::create([
                'user_id' => $admin->id,
                'type' => 'request',
                'audience' => 'admin',
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
        
        $admins = User::where(function ($q) {
            $q->whereJsonContains('roles', 'admin')->orWhereJsonContains('roles', 'hr');
        })->get();
        
        foreach ($admins as $admin) {
            if (!$admin->wantsNotification('employee_requests')) continue;

            Notification::create([
                'user_id' => $admin->id,
                'type' => 'request',
                'audience' => 'admin',
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
        
        $admins = User::where(function ($q) {
            $q->whereJsonContains('roles', 'admin')->orWhereJsonContains('roles', 'hr');
        })->get();
        
        foreach ($admins as $admin) {
            if (!$admin->wantsNotification('employee_requests')) continue;

            Notification::create([
                'user_id' => $admin->id,
                'type' => 'request',
                'audience' => 'admin',
                'title' => $request->request_type_name,
                'message' => "{$employeeName} submitted a request: {$request->title}",
                'link' => route('admin.requests'),
                'related_id' => $request->id,
                'related_type' => 'App\\Models\\EmployeeRequest',
            ]);
        }
    }

    /**
     * Notify an employee that they were included as a companion on a travel order
     */
    public static function travelOrderCompanionInvited($travelOrder, $companion)
    {
        $companionEmployee = $companion->employee;

        if (!$companionEmployee || !$companionEmployee->user) return;

        $filer = $travelOrder->employee;
        $filerName = $filer->first_name . ' ' . $filer->last_name;
        $dates = $travelOrder->formatted_dates;

        Notification::create([
            'user_id' => $companionEmployee->user->id,
            'type' => 'travel_order',
            'audience' => 'employee',
            'title' => 'Travel Order Companion Request',
            'message' => "{$filerName} included you as a companion on travel order {$travelOrder->order_number} to {$travelOrder->destination} ({$dates}). Please accept or reject the request.",
            'link' => route('employee.travelorder', ['highlight' => $travelOrder->id]),
            'related_id' => $travelOrder->id,
            'related_type' => 'App\\Models\\TravelOrder',
        ]);
    }

    /**
     * Notify the filer that a companion accepted/rejected the travel order request
     */
    public static function travelOrderCompanionResponded($travelOrder, $companion)
    {
        $filer = $travelOrder->employee;

        if (!$filer || !$filer->user) return;

        $companionName = $companion->employee->first_name . ' ' . $companion->employee->last_name;
        $statusText = ucfirst($companion->status);
        $message = "{$companionName} has {$companion->status} your companion request for travel order {$travelOrder->order_number}.";

        if ($travelOrder->allCompanionsResponded()) {
            $message .= ' All companions have responded — you can now forward it to HR for approval.';
        }

        Notification::create([
            'user_id' => $filer->user->id,
            'type' => 'travel_order',
            'audience' => 'employee',
            'title' => "Companion Request {$statusText}",
            'message' => $message,
            'link' => route('employee.travelorder', ['highlight' => $travelOrder->id]),
            'related_id' => $travelOrder->id,
            'related_type' => 'App\\Models\\TravelOrder',
        ]);
    }

    /**
     * Notify admin/HR users that a travel order was forwarded for approval
     */
    public static function travelOrderForwarded($travelOrder)
    {
        $filer = $travelOrder->employee;
        $filerName = $filer->first_name . ' ' . $filer->last_name;
        $companionCount = $travelOrder->companions()->where('status', 'accepted')->count();
        $companionText = $companionCount > 0 ? " with {$companionCount} companion(s)" : '';

        $admins = User::where(function ($q) {
            $q->whereJsonContains('roles', 'admin')->orWhereJsonContains('roles', 'hr');
        })->get();

        foreach ($admins as $admin) {
            if (!$admin->wantsNotification('travel_orders')) continue;

            Notification::create([
                'user_id' => $admin->id,
                'type' => 'travel_order',
                'audience' => 'admin',
                'title' => 'New Travel Order Request',
                'message' => "{$filerName} submitted travel order {$travelOrder->order_number} to {$travelOrder->destination}{$companionText}.",
                'link' => route('admin.travelorder', ['highlight' => $travelOrder->id]),
                'related_id' => $travelOrder->id,
                'related_type' => 'App\\Models\\TravelOrder',
            ]);
        }
    }

    /**
     * Notify the filer and accepted companions that HR approved/disapproved the travel order
     */
    public static function travelOrderStatusChanged($travelOrder, $status)
    {
        $statusText = ucfirst($status);
        $reasonText = $travelOrder->remarks ?? $travelOrder->disapproval_reason;
        $reason = ($status !== 'approved' && $reasonText) ? " Reason: {$reasonText}" : '';

        $recipients = collect();

        if ($travelOrder->employee && $travelOrder->employee->user) {
            $recipients->push([
                'user_id' => $travelOrder->employee->user->id,
                'message' => "Your travel order {$travelOrder->order_number} to {$travelOrder->destination} has been {$status}.{$reason}",
            ]);
        }

        $acceptedCompanions = $travelOrder->companions()->where('status', 'accepted')->with('employee.user')->get();
        foreach ($acceptedCompanions as $companion) {
            if ($companion->employee && $companion->employee->user) {
                $recipients->push([
                    'user_id' => $companion->employee->user->id,
                    'message' => "Travel order {$travelOrder->order_number} to {$travelOrder->destination}, where you are a companion, has been {$status}.{$reason}",
                ]);
            }
        }

        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient['user_id'],
                'type' => 'travel_order',
                'audience' => 'employee',
                'title' => "Travel Order {$statusText}",
                'message' => $recipient['message'],
                'link' => route('employee.travelorder', ['highlight' => $travelOrder->id]),
                'related_id' => $travelOrder->id,
                'related_type' => 'App\\Models\\TravelOrder',
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
            'audience' => 'employee',
            'title' => "Request {$statusText}",
            'message' => $message,
            'link' => route('employee.requests'),
            'related_id' => $request->id,
            'related_type' => 'App\\Models\\EmployeeRequest',
        ]);
    }
}
