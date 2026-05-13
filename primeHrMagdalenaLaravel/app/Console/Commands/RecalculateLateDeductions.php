<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateLateDeductions extends Command
{
    protected $signature = 'leave:recalculate-late-deductions';
    protected $description = 'Recalculate late deductions using correct formula: minutes / 1440 (24-hour day)';

    public function handle()
    {
        $this->info('Starting late deduction recalculation...');
        $this->info('Correct formula: Late minutes ÷ 1440 minutes (24 hours) = Days');
        $this->newLine();
        
        DB::beginTransaction();

        try {
            $affectedTransactions = LeaveTransaction::where('reference_type', 'manual_adjustment')
                ->where('remarks', 'LIKE', 'Late deduction:%')
                ->get();

            $this->info("Found {$affectedTransactions->count()} late deduction transactions to recalculate.\n");

            $correctedCount = 0;

            foreach ($affectedTransactions as $transaction) {
                if (preg_match('/Late deduction: (\d+) minutes/', $transaction->remarks, $matches)) {
                    $lateMinutes = (int)$matches[1];
                    
                    // CORRECT FORMULA: minutes / 1440 (24 hours in a day)
                    $correctDays = $lateMinutes / 1440;
                    
                    // What was recorded in the database
                    $recordedDays = abs($transaction->amount);
                    
                    $difference = $recordedDays - $correctDays;
                    
                    if (abs($difference) > 0.000001) {
                        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                        $this->line("Transaction ID: {$transaction->id}");
                        $this->line("Employee ID: {$transaction->employee_id}");
                        $this->line("Leave Code: {$transaction->leave_code}");
                        $this->line("Late Minutes: {$lateMinutes}");
                        $this->line("Calculation: {$lateMinutes} ÷ 1440 = " . number_format($correctDays, 6));
                        $this->warn("Recorded Days: " . number_format($recordedDays, 6) . " (INCORRECT)");
                        $this->info("Correct Days: " . number_format($correctDays, 6));
                        $this->error("Over-deducted: " . number_format($difference, 6) . " days");
                        
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
                            $oldBalance = $leaveBalance->available_credits;
                            
                            // Credit back the over-deducted amount
                            $leaveBalance->used_credits -= $difference;
                            $leaveBalance->available_credits += $difference;
                            $leaveBalance->save();
                            
                            $this->info("✓ Leave balance corrected");
                            $this->line("  Old Available: " . number_format($oldBalance, 6) . " days");
                            $this->line("  Refunded: +" . number_format($difference, 6) . " days");
                            $this->line("  New Available: " . number_format($leaveBalance->available_credits, 6) . " days");
                            
                            // Create a correction transaction
                            LeaveTransaction::create([
                                'employee_id' => $transaction->employee_id,
                                'leave_code' => $transaction->leave_code,
                                'year' => $transaction->year,
                                'transaction_type' => 'adjustment',
                                'amount' => $difference,
                                'balance_before' => $oldBalance,
                                'balance_after' => $leaveBalance->available_credits,
                                'reference_type' => 'manual_adjustment',
                                'reference_id' => $transaction->id,
                                'transaction_date' => now()->format('Y-m-d'),
                                'processed_by' => 1,
                                'remarks' => "Formula correction: Refunding " . number_format($difference, 6) . " days. Corrected from incorrect 480-minute calculation to proper 1440-minute (24-hour day) calculation for {$lateMinutes} minutes late. Transaction #{$transaction->id}",
                            ]);
                            
                            $this->info("✓ Correction transaction created");
                            $correctedCount++;
                        }
                        
                        $this->newLine();
                    }
                }
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("✓ All corrections completed successfully!");
            $this->info("Total corrections made: {$correctedCount}");
            
            if ($correctedCount > 0) {
                $this->newLine();
                $this->comment("Formula used: Late Minutes ÷ 1440 = Days");
                $this->comment("Example: 60 minutes ÷ 1440 = 0.041667 days");
                $this->comment("Example: 120 minutes ÷ 1440 = 0.083333 days");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
