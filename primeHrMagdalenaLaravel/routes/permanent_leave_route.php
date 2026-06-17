Route::get('/permanent/leave', function () {
    $user = Auth::user();
    $employee = $user instanceof User ? $user->employee : null;

    if (!$employee) {
        $leaveTypes = \\App\\Models\\LeaveType::where('is_active', true)
            ->orderBy('leave_name')
            ->get();

        $leaveApplications = collect();
        $employeeTransactions = new \\Illuminate\\Pagination\\LengthAwarePaginator([], 0, 15);

        return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('leaveTypes', 'leaveApplications', 'employeeTransactions'))
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

    return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('employee', 'leaveTypes', 'leaveApplications', 'employeeTransactions'));
})->middleware('auth')->name('permanent.leave');
