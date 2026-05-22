<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LeaveTransaction;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "FIXING JEREMY'S LEAVE BALANCE\n";
echo "===========================================\n\n";

$employeeId = 8;
$year = 2026;

// The original incorrect deductions that need to be reversed
$lateDeduction = 0.316667; // 152 minutes
$undertimeDeduction = 0.037500; // 18 minutes
$totalToReverse = $lateDeduction + $undertimeDeduction; // 0.354167 days

echo "Original incorrect deductions:\n";
echo "- Late: " . number_format($lateDeduction, 6) . " days (152 minutes)\n";
echo "- Undertime: " . number_format($undertimeDeduction, 6) . " days (18 minutes)\n";
echo "- Total to reverse: " . number_format($totalToReverse, 6) . " days\n\n";

// Get current VL balance
$vlBalance = LeaveBalance::where('employee_id', $employeeId)
    ->where('leave_code', 'VL')
    ->where('year', $year)
    ->first();

if (!$vlBalance) {
    echo "❌ VL balance not found!\n";
    exit;
}

echo "Current VL Balance:\n";
echo "- Available: " . number_format($vlBalance->available_credits, 6) . " days\n";
echo "- Used: " . number_format($vlBalance->used_credits, 6) . " days\n";
echo "- Total: " . number_format($vlBalance->total_credits, 6) . " days\n\n";

echo "After reversal, VL Balance will be:\n";
echo "- Available: " . number_format($vlBalance->available_credits + $totalToReverse, 6) . " days\n";
echo "- Used: " . number_format($vlBalance->used_credits - $totalToReverse, 6) . " days\n\n";

echo "Do you want to proceed with this fix? (yes/no): ";
$handle = fopen ("php://stdin","r");
$line = trim(fgets($handle));

if(strtolower($line) !== 'yes'){
    echo "Operation cancelled.\n";
    exit;
}

DB::transaction(function () use ($employeeId, $year, $vlBalance, $lateDeduction, $undertimeDeduction, $totalToReverse) {
    $balanceBefore = $vlBalance->available_credits;
    
    // Update the balance
    $vlBalance->used_credits -= $totalToReverse;
    $vlBalance->available_credits += $totalToReverse;
    $vlBalance->save();
    
    // Create reversal transaction for late deduction
    LeaveTransaction::create([
        'employee_id' => $employeeId,
        'leave_code' => 'VL',
        'year' => $year,
        'transaction_type' => 'credit',
        'amount' => $lateDeduction,
        'balance_before' => $balanceBefore,
        'balance_after' => $balanceBefore + $lateDeduction,
        'reference_type' => 'attendance_correction_reversal',
        'reference_id' => 278, // AccreditedHoursLog ID
        'transaction_date' => date('Y-m-d'),
        'processed_by' => 1, // Admin user
        'remarks' => "Reversal of previous late deduction due to attendance correction on 2026-05-18. Original deduction: " . number_format($lateDeduction, 6) . " days (152 minutes late). Attendance was corrected from 10:26 AM to 08:00 AM.",
    ]);
    
    // Create reversal transaction for undertime deduction
    LeaveTransaction::create([
        'employee_id' => $employeeId,
        'leave_code' => 'VL',
        'year' => $year,
        'transaction_type' => 'credit',
        'amount' => $undertimeDeduction,
        'balance_before' => $balanceBefore + $lateDeduction,
        'balance_after' => $balanceBefore + $totalToReverse,
        'reference_type' => 'attendance_correction_reversal',
        'reference_id' => 278, // AccreditedHoursLog ID
        'transaction_date' => date('Y-m-d'),
        'processed_by' => 1, // Admin user
        'remarks' => "Reversal of previous undertime deduction due to attendance correction on 2026-05-18. Original deduction: " . number_format($undertimeDeduction, 6) . " days (18 minutes undertime). Attendance times were corrected.",
    ]);
    
    echo "\n✅ SUCCESS!\n\n";
    echo "Created 2 reversal transactions:\n";
    echo "1. Late deduction reversal: +" . number_format($lateDeduction, 6) . " days\n";
    echo "2. Undertime deduction reversal: +" . number_format($undertimeDeduction, 6) . " days\n\n";
    echo "New VL Balance:\n";
    echo "- Available: " . number_format($vlBalance->available_credits, 6) . " days\n";
    echo "- Used: " . number_format($vlBalance->used_credits, 6) . " days\n";
});

echo "\n===========================================\n";
echo "Fix complete! Jeremy's leave balance has been corrected.\n";
echo "===========================================\n";
