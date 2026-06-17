<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveType;
use App\Models\LeaveApplication;
use App\Models\LeaveTransaction;
use App\Models\LeaveBalance;

class PermanentLeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user?->employee;

        if (!$employee) {
            $leaveTypes = LeaveType::where('is_active', true)->orderBy('leave_name')->get();
            $leaveApplications = collect();
            $employeeTransactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

            return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('leaveTypes', 'leaveApplications', 'employeeTransactions'))
                ->with('warning', 'Employee record not found.');
        }

        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');
        $currentYear = now()->year;

        // Load all active leave types with balances
        $leaveTypes = LeaveType::where('is_active', true)
            ->with(['leaveBalances' => function($q) use ($employee, $currentYear) {
                $q->where('employee_id', $employee->id)->where('year', $currentYear);
            }])
            ->orderBy('leave_name')
            ->get();

        $leaveApplications = LeaveApplication::where('employee_id', $employee->id)
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->get();

        $transactionQuery = LeaveTransaction::where('employee_id', $employee->id)
            ->with('processedBy.employee');

        if (request('filter_type')) {
            $transactionQuery->where('transaction_type', request('filter_type'));
        }
        if (request('filter_leave_code')) {
            $transactionQuery->where('leave_code', request('filter_leave_code'));
        }
        if (request('filter_date')) {
            $transactionQuery->whereDate('transaction_date', request('filter_date'));
        }

        $sortBy = request('sort_by', 'transaction_date');
        $sortOrder = request('sort_order', 'desc');
        $allowedColumns = ['transaction_date', 'leave_code', 'transaction_type', 'amount', 'balance_before', 'balance_after'];

        if (in_array($sortBy, $allowedColumns)) {
            $transactionQuery->orderBy($sortBy, $sortOrder);
        } else {
            $transactionQuery->orderBy('transaction_date', 'desc');
        }

        $transactionQuery->orderBy('created_at', 'desc');
        $perPage = request('employee_transaction_per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $employeeTransactions = $transactionQuery->paginate($perPage)->appends(request()->except('page'));

        return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('employee', 'leaveTypes', 'leaveApplications', 'employeeTransactions'));
    }
}
