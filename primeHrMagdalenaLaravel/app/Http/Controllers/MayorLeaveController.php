<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;

class MayorLeaveController extends Controller
{
    public function index()
    {
        $leaveApplications = LeaveApplication::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'leaveType',
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total'    => $leaveApplications->count(),
            'approved' => $leaveApplications->where('status', 'approved')->count(),
            'pending'  => $leaveApplications->where('status', 'pending')->count(),
            'rejected' => $leaveApplications->where('status', 'rejected')->count(),
        ];

        $leaveTypes = $leaveApplications->pluck('leaveType.leave_name')->filter()->unique()->sort()->values();

        return view('mayor.leave.mayorLeave', compact('leaveApplications', 'stats', 'leaveTypes'));
    }
}
