<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\LeaveTransaction;

$employee = Employee::has('user')->first();

if (!$employee) {
    echo "❌ No employee found\n";
    exit(1);
}

echo "=== CHECKING ALL YEARS WITH TRANSACTIONS ===\n";

$allTransactions = LeaveTransaction::where('employee_id', $employee->id)
    ->whereIn('transaction_type', ['credit', 'debit'])
    ->select('year', 'leave_code')
    ->distinct()
    ->orderBy('year', 'desc')
    ->get();

if ($allTransactions->isEmpty()) {
    echo "❌ No credit/debit transactions found for this employee\n";
} else {
    echo "✅ Found transactions for:\n";
    foreach ($allTransactions as $t) {
        echo "   - Year {$t->year}, Leave Code: {$t->leave_code}\n";
    }
}

// Also check all employees with transactions
echo "\n=== CHECKING ALL EMPLOYEES WITH TRANSACTIONS ===\n";
$employeesWithData = LeaveTransaction::whereIn('transaction_type', ['credit', 'debit'])
    ->select('employee_id')
    ->distinct()
    ->get();

echo "Total employees with migrated data: " . $employeesWithData->count() . "\n";

if ($employeesWithData->isNotEmpty()) {
    foreach ($employeesWithData->take(5) as $record) {
        $emp = Employee::find($record->employee_id);
        $transCount = LeaveTransaction::where('employee_id', $record->employee_id)
            ->whereIn('transaction_type', ['credit', 'debit'])
            ->count();
        echo "   - Employee ID {$record->employee_id} ({$emp->first_name} {$emp->last_name}): $transCount transactions\n";
    }
}
