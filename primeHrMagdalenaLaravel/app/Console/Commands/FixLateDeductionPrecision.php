<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixLateDeductionPrecision extends Command
{
    protected $signature = 'leave:fix-precision';
    protected $description = 'Fix incorrect late deductions caused by decimal rounding';

    public function handle()
    {
        $this->info('Starting late deduction precision correction...');
        
        DB::beginTransaction();

        try {
            $affectedTransactions = LeaveTransaction::where('reference_type', 'manual_adjustment')
                ->where('remarks', 'LIKE', 'Late deduction:%')
                ->get();

            $this->info("Found {$affectedTransactions->count()} late deduction transactions to review.\n");

            $correctedCount = 0;

            foreach ($affectedTransactions as $transaction) {
                if (preg_match('/Late deduction: (\d+) minutes/', $transaction->remarks, $matches)) {
                    $lateMinutes = (int)$matches[1];
                    $correctDays = $lateMinutes / 480;
                    $recordedDays = abs($transaction->amount);
                    
                    $difference = $recordedDays - $correctDays;
                    
                    if (abs($difference) > 0.000001) {
                        $this->line("Transaction ID: {$transaction->id}");
                        $this->line("Employee ID: {$transaction->employee_id}");
                        $this->line("Leave Code: {$transaction->leave_code}");
                        $this->line("Late Minutes: {$lateMinutes}");
                        $this->line("Correct Days: " . number_format($correctDays, 6));
                        $this->line("Recorded Days: " . number_format($recordedDays, 6));
                        $this->warn("Difference: " . number_format($difference, 6) . " days (over-deducted)");
                        
                        $transaction->update([
                            'amount' => -$correctDays,
                            'balance_after' => $transaction->balance_before - $correctDays,
                        ]);
                        
                        $leaveBalance = LeaveBalance::where('employee_id', $transaction->employee_id)
                            ->where('leave_code', $transaction->leave_code)
                            ->where('year', $transaction->year)
                            ->first();
                        
                        if ($leaveBalance) {
                            $leaveBalance->used_credits -= $difference;
                            $leaveBalance->available_credits += $difference;
                            $leaveBalance->save();
                            
                            $this->info("✓ Corrected leave balance - credited back " . number_format($difference, 6) . " days");
                            $this->info("  New Available Credits: " . number_format($leaveBalance->available_credits, 6));
                            
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
                            
                            $this->info("✓ Created correction transaction\n");
                            $correctedCount++;
                        }
                    }
                }
            }
            
            DB::commit();
            $this->info("All corrections completed successfully!");
            $this->info("Total corrections made: {$correctedCount}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
