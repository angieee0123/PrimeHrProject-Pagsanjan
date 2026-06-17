<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\LeaveTransaction;
use App\Services\LeaveCreditsComputationService;

$jeremy = Employee::find(8);

if (!$jeremy) {
    echo "❌ Jeremy Pogi not found\n";
    exit(1);
}

echo "=== TESTING JEREMY POGI'S LEAVE DATA ===\n\n";

// Get all years with transactions
$years = LeaveTransaction::where('employee_id', 8)
    ->whereIn('transaction_type', ['credit', 'debit'])
    ->select('year')
    ->distinct()
    ->orderBy('year', 'desc')
    ->pluck('year');

echo "Years with migrated data: " . implode(', ', $years->toArray()) . "\n\n";

if ($years->isNotEmpty()) {
    $testYear = $years->first();
    
    echo "Testing with year: $testYear\n\n";
    
    // Get transactions for this year
    $transactions = LeaveTransaction::where('employee_id', 8)
        ->whereIn('transaction_type', ['credit', 'debit'])
        ->where('year', $testYear)
        ->get();
    
    echo "Transactions: " . $transactions->count() . "\n";
    foreach ($transactions->groupBy('leave_code') as $code => $trans) {
        echo "  - $code: " . $trans->count() . " transactions\n";
    }
    
    // Now sync and check
    echo "\nSyncing leave credits for $testYear...\n";
    LeaveCreditsComputationService::syncAllLeaveCreditsForEmployee(8);
    
    // Check balances
    $balances = \App\Models\LeaveBalance::where('employee_id', 8)
        ->where('year', $testYear)
        ->get();
    
    echo "\nLeave Balances after sync:\n";
    foreach ($balances as $b) {
        echo "  - {$b->leave_code}: Total={$b->total_credits}, Used={$b->used_credits}, Available={$b->available_credits}\n";
    }
}
