<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveApplication;
use App\Models\LeaveTransaction;

class PermanentLeaveBalanceController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            $leaveTypes = LeaveType::where('is_active', true)
                ->with('leaveBalances')
                ->orderBy('leave_name')
                ->get();

            $leaveApplications = collect();
            $employeeTransactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

            return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('leaveTypes', 'leaveApplications', 'employeeTransactions'))
                ->with('warning', 'Employee record not found. Displaying leave types without balance information.');
        }

        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        // Get all leave codes with data for this employee
        $leaveCodesWithData = DB::table('leave_balances')
            ->where('employee_id', $employee->id)
            ->distinct()
            ->pluck('leave_code')
            ->toArray();

        // Get most recent year with leave balance data
        $selectedYear = DB::table('leave_balances')
            ->where('employee_id', $employee->id)
            ->orderByDesc('year')
            ->limit(1)
            ->value('year') ?? now()->year;

        // Get all years with leave balance data for this employee
        $availableYears = DB::table('leave_balances')
            ->where('employee_id', $employee->id)
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values();

        // Get filter parameters
        $startDate = request('start_date');
        $endDate = request('end_date');
        $filterLeaveType = request('leave_type');
        $viewMode = request('view_mode', 'current');

        // Load only leave types with data for this employee
        $leaveTypes = LeaveType::where('is_active', true)
            ->whereIn('leave_code', $leaveCodesWithData)
            ->with(['leaveBalances' => function($query) use ($employee, $selectedYear) {
                $query->where('employee_id', $employee->id)
                      ->where('year', $selectedYear);
            }])
            ->orderBy('leave_name')
            ->get();

        // Load yearly history for all leave types if in history view
        $leaveHistory = [];
        $leaveStatsHistory = [];
        if ($viewMode === 'history') {
            foreach ($leaveTypes as $leaveType) {
                $leaveHistory[$leaveType->leave_code] = DB::table('leave_balances')
                    ->where('employee_id', $employee->id)
                    ->where('leave_code', $leaveType->leave_code)
                    ->orderBy('year')
                    ->get()
                    ->toArray();
            }
            
            // Get leave filings and tardiness stats
            $leaveStatsHistory = $this->getLeaveFilingStats($employee->id);
        }

        // Apply filters
        if ($filterLeaveType) {
            $leaveTypes = $leaveTypes->filter(fn($t) => $t->leave_code === $filterLeaveType)->values();
        }

        // Calculate usage in period for each leave type
        foreach ($leaveTypes as $leaveType) {
            if ($startDate && $endDate) {
                $usageInPeriod = LeaveTransaction::where('employee_id', $employee->id)
                    ->where('leave_code', $leaveType->leave_code)
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->where('transaction_type', 'DEDUCTION')
                    ->sum('amount') ?? 0;
                
                $leaveType->usage_in_period = $usageInPeriod;
            }
        }

        $leaveApplications = LeaveApplication::where('employee_id', $employee->id)
            ->with('leaveType')
            ->when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch employee transactions with filtering and sorting
        $transactionQuery = LeaveTransaction::where('employee_id', $employee->id)
            ->with('processedBy.employee');

        if ($startDate && $endDate) {
            $transactionQuery->whereBetween('transaction_date', [$startDate, $endDate]);
        }
        if (request('filter_type')) {
            $transactionQuery->where('transaction_type', request('filter_type'));
        }
        if ($filterLeaveType) {
            $transactionQuery->where('leave_code', $filterLeaveType);
        }
        if (request('filter_date')) {
            $transactionQuery->whereDate('transaction_date', request('filter_date'));
        }

        $sortBy = request('sort_by', 'transaction_date');
        $sortOrder = request('sort_order', 'desc');
        $allowedSortColumns = ['transaction_date', 'leave_code', 'transaction_type', 'amount', 'balance_before', 'balance_after'];

        if (in_array($sortBy, $allowedSortColumns)) {
            $transactionQuery->orderBy($sortBy, $sortOrder);
        } else {
            $transactionQuery->orderBy('transaction_date', 'desc');
        }

        $transactionQuery->orderBy('created_at', 'desc');

        $perPage = request('employee_transaction_per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $employeeTransactions = $transactionQuery->paginate($perPage)->appends(request()->except('page'));

        return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('employee', 'leaveTypes', 'leaveApplications', 'employeeTransactions', 'selectedYear', 'availableYears', 'leaveHistory', 'leaveStatsHistory', 'viewMode'));
    }

    private function getLeaveFilingStats($employeeId)
    {
        $transactions = DB::table('leave_transactions')
            ->where('employee_id', $employeeId)
            ->whereIn('reference_type', ['leave_application', 'leave_import', 'ledger_entry'])
            ->select('leave_code', 'transaction_date', 'reference_type')
            ->orderBy('transaction_date')
            ->get();

        $tardiness = DB::table('leave_transactions')
            ->where('employee_id', $employeeId)
            ->where('reference_type', 'tardiness_deduction')
            ->select('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        $stats = [];
        foreach ($transactions as $trans) {
            $monthYear = date('Y-m', strtotime($trans->transaction_date));
            if (!isset($stats[$monthYear])) {
                $stats[$monthYear] = ['leaves_by_type' => [], 'tardiness_count' => 0];
            }
            $stats[$monthYear]['leaves_by_type'][$trans->leave_code] = ($stats[$monthYear]['leaves_by_type'][$trans->leave_code] ?? 0) + 1;
        }

        foreach ($tardiness as $tard) {
            $monthYear = date('Y-m', strtotime($tard->transaction_date));
            if (!isset($stats[$monthYear])) {
                $stats[$monthYear] = ['leaves_by_type' => [], 'tardiness_count' => 0];
            }
            $stats[$monthYear]['tardiness_count']++;
        }

        krsort($stats);
        return $stats;
    }
}
