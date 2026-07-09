Route::get('/employee/leave', function () {
    $user = Auth::user();
    $employee = $user instanceof User ? $user->employee : null;

    if (!$employee) {
        $leaveTypes = \\App\\Models\\LeaveType::where('is_active', true)
            ->orderBy('leave_name')
            ->get();

        $leaveApplications = collect();
        $employeeTransactions = new \\Illuminate\\Pagination\\LengthAwarePaginator([], 0, 15);

        return view('employee.leaveandbenefits.employeeLeaveandbenefits', compact('leaveTypes', 'leaveApplications', 'employeeTransactions'))
            ->with('warning', 'Employee record not found. Displaying leave types without balance information.');
    }

    // Load employee relationships for topbar
    $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

    // Get all years with leave transaction data for this employee
    $yearsWithData = \\App\\Models\\LeaveTransaction::where('employee_id', $employee->id)
        ->whereIn('transaction_type', ['credit', 'debit'])
        ->select('year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    // Use most recent year with data, or current year if no data exists
    $targetYear = $yearsWithData->isNotEmpty() ? $yearsWithData->first() : now()->year;

    // Get all leave types with their balances
    $leaveTypes = \\App\\Models\\LeaveType::where('is_active', true)
        ->with(['leaveBalances' => function($query) use ($employee, $targetYear) {
            $query->where('employee_id', $employee->id)
                  ->where('year', $targetYear);
        }])
        ->orderBy('leave_name')
        ->get()
        ->map(function($leaveType) use ($employee, $targetYear) {
            $balance = $leaveType->leaveBalances->first();
            
            // If no balance, sync from transactions
            if (!$balance) {
                \\App\\Services\\LeaveCreditsComputationService::syncLeaveCreditsForYear($employee->id, $targetYear, $leaveType->leave_code);
                $balance = \\App\\Models\\LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_code', $leaveType->leave_code)
                    ->where('year', $targetYear)
                    ->first();
            }
            
            // Include type if it has any balance data
            if ($balance && ($balance->total_credits > 0 || $balance->available_credits > 0 || $balance->used_credits > 0)) {
                $leaveType->leaveBalances = collect([$balance]);
                return $leaveType;
            }
            
            return null;
        })
        ->filter(fn($lt) => $lt !== null)
        ->values();

    $leaveApplications = \\App\\Models\\LeaveApplication::where('employee_id', $employee->id)
        ->with('leaveType')
        ->orderBy('created_at', 'desc')
        ->get();

    // Fetch employee transactions with filtering and sorting
    $transactionQuery = \\App\\Models\\LeaveTransaction::where('employee_id', $employee->id)
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

    // Build leave statistics history directly from database
    $leaveStatsHistory = [];
    $allDebits = \App\Models\LeaveTransaction::where('employee_id', $employee->id)
        ->where('transaction_type', 'debit')
        ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month_year, leave_code, reference_type, COUNT(*) as count')
        ->groupByRaw('DATE_FORMAT(transaction_date, "%Y-%m"), leave_code, reference_type')
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
        $history = \App\Models\LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_code', $type->leave_code)
            ->orderBy('year')
            ->get();
        $leaveHistory[$type->leave_code] = $history;
    }

    $selectedYear = $targetYear;
    $viewMode = request('view_mode', 'current');

    return view('employee.leaveandbenefits.employeeLeaveandbenefits', compact('employee', 'leaveTypes', 'leaveApplications', 'employeeTransactions', 'leaveStatsHistory', 'leaveHistory', 'selectedYear', 'viewMode'));
})->middleware('auth')->name('employee.leave');
