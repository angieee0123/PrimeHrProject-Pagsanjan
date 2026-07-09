Route::get('/employee/leave', function () {
    $user = Auth::user();
    $employee = $user instanceof User ? $user->employee : null;

    if (!$employee) {
        $leaveTypes = \App\Models\LeaveType::where('is_active', true)
            ->orderBy('leave_name')
            ->get();

        $leaveApplications = collect();
        $employeeTransactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

        return view('employee.leaveandbenefits.employeeLeaveandbenefits', compact('leaveTypes', 'leaveApplications', 'employeeTransactions'))
            ->with('warning', 'Employee record not found. Displaying leave types without balance information.');
    }

    $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

    $currentYear = now()->year;

    $leaveTypes = \App\Models\LeaveType::where('is_active', true)
        ->with(['leaveBalances' => function($query) use ($employee, $currentYear) {
            $query->where('employee_id', $employee->id)
                  ->where('year', $currentYear);
        }])
        ->orderBy('leave_name')
        ->get()
        ->filter(function($leaveType) use ($employee, $currentYear) {
            $balance = $leaveType->leaveBalances->first();
            if (!$balance) {
                \App\Services\LeaveCreditsComputationService::syncLeaveCreditsForYear($employee->id, $currentYear, $leaveType->leave_code);
                $balance = \App\Models\LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_code', $leaveType->leave_code)
                    ->where('year', $currentYear)
                    ->first();
                if ($balance) {
                    $leaveType->setRelation('leaveBalances', collect([$balance]));
                }
            }
            return $balance && ($balance->total_credits > 0 || $balance->available_credits > 0);
        })
        ->values();

    $leaveApplications = \App\Models\LeaveApplication::where('employee_id', $employee->id)
        ->with('leaveType')
        ->orderBy('created_at', 'desc')
        ->get();

    $transactionQuery = \App\Models\LeaveTransaction::where('employee_id', $employee->id)
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

    return view('employee.leaveandbenefits.employeeLeaveandbenefits', compact('employee', 'leaveTypes', 'leaveApplications', 'employeeTransactions'));
})->middleware('auth')->name('employee.leave');
