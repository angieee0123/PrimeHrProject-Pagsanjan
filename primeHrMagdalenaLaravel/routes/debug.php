<?php

// Add this debug route temporarily to routes/web.php to diagnose the issue

Route::get('/debug/leave-balances', function () {
    $user = Auth::user();
    $employee = $user instanceof User ? $user->employee : null;

    if (!$employee) {
        return 'No employee found';
    }

    $balances = \App\Models\LeaveBalance::where('employee_id', $employee->id)->get();
    
    return view('debug', [
        'employee_id' => $employee->id,
        'employee_name' => $employee->first_name . ' ' . $employee->last_name,
        'balances' => $balances,
    ]);
})->middleware('auth');
