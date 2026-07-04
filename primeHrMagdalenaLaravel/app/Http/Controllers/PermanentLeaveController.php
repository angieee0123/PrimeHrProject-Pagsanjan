<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Load all active leave types
        $leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('leave_name')
            ->get();
        
        // Manually attach the latest balance for each leave type
        foreach ($leaveTypes as $leaveType) {
            $latestBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_code', $leaveType->leave_code)
                ->orderBy('year', 'desc')
                ->first();
            
            // Create a collection with just this balance for consistency with blade template
            $leaveType->setRelation('leaveBalances', $latestBalance ? collect([$latestBalance]) : collect());
        }

        $leaveApplications = LeaveApplication::where('employee_id', $employee->id)
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->get();

        // Build leave statistics history directly from database
        $leaveStatsHistory = [];
        $monthYearExpr = DB::connection()->getDriverName() === 'pgsql'
            ? "to_char(transaction_date, 'YYYY-MM')"
            : 'DATE_FORMAT(transaction_date, "%Y-%m")';

        $allDebits = LeaveTransaction::where('employee_id', $employee->id)
            ->where('transaction_type', 'debit')
            ->selectRaw("$monthYearExpr as month_year, leave_code, reference_type, COUNT(*) as count")
            ->groupByRaw("$monthYearExpr, leave_code, reference_type")
            ->get();

        foreach ($allDebits as $row) {
            if (!isset($leaveStatsHistory[$row->month_year])) {
                $leaveStatsHistory[$row->month_year] = ['leaves_by_type' => [], 'tardiness_count' => 0];
            }
            if ($row->reference_type === 'tardiness_deduction') {
                $leaveStatsHistory[$row->month_year]['tardiness_count'] += $row->count;
            } else {
                $code = $row->leave_code;
                $leaveStatsHistory[$row->month_year]['leaves_by_type'][$code] = ($leaveStatsHistory[$row->month_year]['leaves_by_type'][$code] ?? 0) + $row->count;
            }
        }

        // Get leave history by type
        $leaveHistory = [];
        foreach ($leaveTypes as $type) {
            $history = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_code', $type->leave_code)
                ->orderBy('year')
                ->get();
            $leaveHistory[$type->leave_code] = $history;
        }

        $selectedYear = $currentYear;

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

        return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('employee', 'leaveTypes', 'leaveApplications', 'employeeTransactions', 'leaveStatsHistory', 'leaveHistory', 'selectedYear'));
    }
}
