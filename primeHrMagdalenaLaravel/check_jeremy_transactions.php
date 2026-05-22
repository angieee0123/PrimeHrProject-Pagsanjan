<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\LeaveTransaction;
use App\Models\LeaveBalance;

// Find Jeremy
$employee = Employee::whereHas('user', function($q) {
    $q->where('username', 'LIKE', '%jeremy%')
      ->orWhere('email', 'LIKE', '%jeremy%');
})->first();

// If not found, try by employee name
if (!$employee) {
    $employee = Employee::where('first_name', 'LIKE', '%Jeremy%')
        ->orWhere('last_name', 'LIKE', '%Pogi%')
        ->first();
}

if (!$employee) {
    echo "Employee Jeremy not found\n";
    exit;
}

echo "===========================================\n";
echo "Employee: " . $employee->user->name . " (ID: " . $employee->id . ")\n";
echo "===========================================\n\n";

// Check current leave balances
echo "CURRENT LEAVE BALANCES:\n";
echo "-------------------------------------------\n";
$balances = LeaveBalance::where('employee_id', $employee->id)
    ->where('year', 2026)
    ->get();

foreach ($balances as $balance) {
    echo $balance->leave_code . ": " . number_format($balance->available_credits, 6) . " days available\n";
    echo "   Total: " . number_format($balance->total_credits, 6) . " | Used: " . number_format($balance->used_credits, 6) . "\n";
}

echo "\n";

// Check transactions for May 18, 2026
echo "LEAVE TRANSACTIONS (May 18, 2026 onwards):\n";
echo "-------------------------------------------\n";

$transactions = LeaveTransaction::where('employee_id', $employee->id)
    ->whereDate('transaction_date', '>=', '2026-05-18')
    ->orderBy('transaction_date', 'desc')
    ->orderBy('created_at', 'desc')
    ->get();

if ($transactions->count() > 0) {
    foreach ($transactions as $t) {
        echo "\n";
        echo "Transaction ID: " . $t->id . "\n";
        echo "Date: " . $t->transaction_date->format('Y-m-d') . "\n";
        echo "Leave Code: " . $t->leave_code . "\n";
        echo "Type: " . strtoupper($t->transaction_type) . "\n";
        echo "Amount: " . ($t->amount >= 0 ? '+' : '') . number_format($t->amount, 6) . " days\n";
        echo "Balance Before: " . number_format($t->balance_before, 6) . "\n";
        echo "Balance After: " . number_format($t->balance_after, 6) . "\n";
        echo "Reference Type: " . $t->reference_type . "\n";
        echo "Remarks: " . $t->remarks . "\n";
        echo "Created: " . $t->created_at->format('Y-m-d H:i:s') . "\n";
        echo "-------------------------------------------\n";
    }
} else {
    echo "No transactions found for May 18, 2026 onwards\n";
}

// Check all transactions for May 2026
echo "\n\nALL MAY 2026 TRANSACTIONS:\n";
echo "-------------------------------------------\n";

$allMayTransactions = LeaveTransaction::where('employee_id', $employee->id)
    ->whereYear('transaction_date', 2026)
    ->whereMonth('transaction_date', 5)
    ->orderBy('transaction_date', 'asc')
    ->orderBy('created_at', 'asc')
    ->get();

echo "Total May 2026 transactions: " . $allMayTransactions->count() . "\n\n";

foreach ($allMayTransactions as $t) {
    echo $t->transaction_date->format('M d') . " | " . 
         $t->leave_code . " | " . 
         strtoupper($t->transaction_type) . " | " . 
         ($t->amount >= 0 ? '+' : '') . number_format($t->amount, 6) . " | " . 
         $t->reference_type . "\n";
    echo "  → " . substr($t->remarks, 0, 80) . "\n";
}

echo "\n===========================================\n";
echo "Check complete!\n";
