<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\LeaveTransaction;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveCreditsComputationService;

// Get first employee with user account
$employee = Employee::has('user')->first();

if (!$employee) {
    echo "❌ No employee found with user account\n";
    exit(1);
}

echo "=== TESTING LEAVE DATA FOR: {$employee->first_name} {$employee->last_name} (ID: {$employee->id}) ===\n\n";

$currentYear = now()->year;

// Check 1: Migrated transactions
echo "1️⃣ CHECKING MIGRATED TRANSACTIONS (credit/debit):\n";
$transactions = LeaveTransaction::where('employee_id', $employee->id)
    ->whereIn('transaction_type', ['credit', 'debit'])
    ->where('year', $currentYear)
    ->get();

if ($transactions->isEmpty()) {
    echo "   ❌ No credit/debit transactions found for year $currentYear\n";
} else {
    echo "   ✅ Found " . $transactions->count() . " transactions:\n";
    foreach ($transactions as $t) {
        echo "      - {$t->leave_code}: {$t->transaction_type} {$t->amount} (before: {$t->balance_before}, after: {$t->balance_after})\n";
    }
}

// Check 2: Existing leave balances
echo "\n2️⃣ CHECKING EXISTING LEAVE BALANCES:\n";
$balances = LeaveBalance::where('employee_id', $employee->id)
    ->where('year', $currentYear)
    ->get();

if ($balances->isEmpty()) {
    echo "   ❌ No leave balances found for year $currentYear\n";
} else {
    echo "   ✅ Found " . $balances->count() . " balances:\n";
    foreach ($balances as $b) {
        echo "      - {$b->leave_code}: Total={$b->total_credits}, Used={$b->used_credits}, Available={$b->available_credits}\n";
    }
}

// Check 3: Active leave types
echo "\n3️⃣ CHECKING ACTIVE LEAVE TYPES:\n";
$leaveTypes = LeaveType::where('is_active', true)->get();
echo "   Found " . $leaveTypes->count() . " active leave types:\n";
foreach ($leaveTypes as $lt) {
    echo "      - {$lt->leave_code}: {$lt->leave_name}\n";
}

// Check 4: Run sync for each leave type
echo "\n4️⃣ RUNNING SYNC FOR EACH LEAVE TYPE:\n";
foreach ($leaveTypes as $lt) {
    echo "   Syncing {$lt->leave_code}...\n";
    try {
        LeaveCreditsComputationService::syncLeaveCreditsForYear($employee->id, $currentYear, $lt->leave_code);
        echo "      ✅ Synced\n";
    } catch (\Exception $e) {
        echo "      ❌ Error: {$e->getMessage()}\n";
    }
}

// Check 5: Verify balances after sync
echo "\n5️⃣ LEAVE BALANCES AFTER SYNC:\n";
$balances = LeaveBalance::where('employee_id', $employee->id)
    ->where('year', $currentYear)
    ->get();

if ($balances->isEmpty()) {
    echo "   ❌ Still no leave balances after sync\n";
} else {
    echo "   ✅ Found " . $balances->count() . " balances:\n";
    foreach ($balances as $b) {
        echo "      - {$b->leave_code}: Total={$b->total_credits}, Used={$b->used_credits}, Available={$b->available_credits}\n";
    }
}

// Check 6: Test the route logic
echo "\n6️⃣ TESTING ROUTE LOGIC (leave types with balances):\n";
$leaveTypesWithBalances = LeaveType::where('is_active', true)
    ->with(['leaveBalances' => function($query) use ($employee, $currentYear) {
        $query->where('employee_id', $employee->id)
              ->where('year', $currentYear);
    }])
    ->orderBy('leave_name')
    ->get()
    ->map(function($leaveType) use ($employee, $currentYear) {
        $balance = $leaveType->leaveBalances->first();
        
        if (!$balance) {
            echo "   - {$leaveType->leave_code}: No balance, trying sync...\n";
            LeaveCreditsComputationService::syncLeaveCreditsForYear($employee->id, $currentYear, $leaveType->leave_code);
            $balance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_code', $leaveType->leave_code)
                ->where('year', $currentYear)
                ->first();
        }
        
        if ($balance && ($balance->total_credits > 0 || $balance->available_credits > 0 || $balance->used_credits > 0)) {
            echo "   ✅ {$leaveType->leave_code}: Total={$balance->total_credits}, Available={$balance->available_credits}\n";
            $leaveType->leaveBalances = collect([$balance]);
            return $leaveType;
        }
        
        return null;
    })
    ->filter(fn($lt) => $lt !== null)
    ->values();

echo "   Total leave types to display: " . $leaveTypesWithBalances->count() . "\n";

echo "\n=== DEBUG COMPLETE ===\n";
