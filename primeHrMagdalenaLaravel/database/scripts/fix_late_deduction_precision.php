<?php

/**
 * Script to fix incorrect late deductions caused by decimal(8,2) rounding.
 * This script recalculates and corrects the leave balances for affected employees.
 * 
 * Run this script using: php artisan tinker < database/scripts/fix_late_deduction_precision.php
 */

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    // Find all late deduction transactions that were affected by rounding
    $affectedTransactions = LeaveTransaction::where('reference_type', 'manual_adjustment')
        ->where('remarks', 'LIKE', 'Late deduction:%')
        ->get();

    echo "Found " . $affectedTransactions->count() . " late deduction transactions to review.\n\n";

    foreach ($affectedTransactions as $transaction) {
        // Extract minutes from remarks: "Late deduction: 60 minutes (0.125000 days) from attendance on 2026-05-13"
        if (preg_match('/Late deduction: (\d+) minutes/', $transaction->remarks, $matches)) {
            $lateMinutes = (int)$matches[1];
            $correctDays = $lateMinutes / 480; // Exact calculation
            $recordedDays = abs($transaction->amount); // Current amount in DB
            
            $difference = $recordedDays - $correctDays;
            
            if (abs($difference) > 0.000001) { // If there's a significant difference
                echo "Transaction ID: {$transaction->id}\n";
                echo "Employee ID: {$transaction->employee_id}\n";
                echo "Leave Code: {$transaction->leave_code}\n";
                echo "Late Minutes: {$lateMinutes}\n";
                echo "Correct Days: " . number_format($correctDays, 6) . "\n";
                echo "Recorded Days: " . number_format($recordedDays, 6) . "\n";
                echo "Difference: " . number_format($difference, 6) . " days (over-deducted)\n";
                
                // Update the transaction with correct amount
                $transaction->update([
                    'amount' => -$correctDays,
                    'balance_after' => $transaction->balance_before - $correctDays,
                ]);
                
                // Find and update the leave balance
                $leaveBalance = LeaveBalance::where('employee_id', $transaction->employee_id)
                    ->where('leave_code', $transaction->leave_code)
                    ->where('year', $transaction->year)
                    ->first();
                
                if ($leaveBalance) {
                    // Credit back the over-deducted amount
                    $leaveBalance->used_credits -= $difference;
                    $leaveBalance->available_credits += $difference;
                    $leaveBalance->save();
                    
                    echo "✓ Corrected leave balance - credited back " . number_format($difference, 6) . " days\n";
                    echo "  New Available Credits: " . number_format($leaveBalance->available_credits, 6) . "\n";
                    
                    // Create a correction transaction
                    LeaveTransaction::create([
                        'employee_id' => $transaction->employee_id,
                        'leave_code' => $transaction->leave_code,
                        'year' => $transaction->year,
                        'transaction_type' => 'adjustment',
                        'amount' => $difference,
                        'balance_before' => $leaveBalance->available_credits - $difference,
                        'balance_after' => $leaveBalance->available_credits,
                        'reference_type' => 'manual_adjustment',
                        'reference_id' => $transaction->id,
                        'transaction_date' => now()->format('Y-m-d'),
                        'processed_by' => 1,
                        'remarks' => "Precision correction: Refunding " . number_format($difference, 6) . " days over-deducted due to decimal rounding in transaction #{$transaction->id}",
                    ]);
                    
                    echo "✓ Created correction transaction\n";
                }
                
                echo "\n";
            }
        }
    }
    
    DB::commit();
    echo "All corrections completed successfully!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
