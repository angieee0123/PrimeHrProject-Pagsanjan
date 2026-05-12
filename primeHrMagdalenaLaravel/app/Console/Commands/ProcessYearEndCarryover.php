<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessYearEndCarryover extends Command
{
    protected $signature = 'leave:process-year-end-carryover {--year=}';
    protected $description = 'Process year-end carryover for cumulative leave types (VL and SL)';

    public function handle()
    {
        $year = $this->option('year') ?? date('Y') - 1;
        $nextYear = $year + 1;
        $processDate = Carbon::create($year, 12, 31);
        
        $this->info("Processing year-end carryover from {$year} to {$nextYear}...\n");
        
        // Get cumulative leave types (VL and SL)
        $cumulativeLeaveTypes = LeaveType::where('is_cumulative', true)
            ->where('is_active', true)
            ->get();
        
        if ($cumulativeLeaveTypes->isEmpty()) {
            $this->warn('No cumulative leave types found.');
            return 0;
        }
        
        $totalProcessed = 0;
        $totalCarriedOver = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($cumulativeLeaveTypes as $leaveType) {
                // Get all balances for this leave type in the old year
                $oldBalances = LeaveBalance::where('leave_code', $leaveType->leave_code)
                    ->where('year', $year)
                    ->where('available_credits', '>', 0)
                    ->get();
                
                foreach ($oldBalances as $oldBalance) {
                    $employee = $oldBalance->employee;
                    
                    if (!$employee) {
                        continue;
                    }
                    
                    // Get or create balance for next year
                    $newBalance = LeaveBalance::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'leave_code' => $leaveType->leave_code,
                            'year' => $nextYear,
                        ],
                        [
                            'total_credits' => 0,
                            'used_credits' => 0,
                            'pending_credits' => 0,
                            'available_credits' => 0,
                            'carried_over' => 0,
                        ]
                    );
                    
                    // Check if carryover already processed
                    $existingCarryover = LeaveTransaction::where('employee_id', $employee->id)
                        ->where('leave_code', $leaveType->leave_code)
                        ->where('year', $nextYear)
                        ->where('reference_type', 'carryover')
                        ->exists();
                    
                    if ($existingCarryover) {
                        $this->info("Carryover already processed for {$employee->first_name} {$employee->last_name} - {$leaveType->leave_name}");
                        continue;
                    }
                    
                    $creditsToCarryOver = $oldBalance->available_credits;
                    
                    // Update new year balance
                    $balanceBefore = $newBalance->available_credits;
                    $newBalance->carried_over += $creditsToCarryOver;
                    $newBalance->total_credits += $creditsToCarryOver;
                    $newBalance->available_credits += $creditsToCarryOver;
                    $newBalance->save();
                    
                    // Create transaction log
                    LeaveTransaction::create([
                        'employee_id' => $employee->id,
                        'leave_code' => $leaveType->leave_code,
                        'year' => $nextYear,
                        'transaction_type' => 'credit',
                        'amount' => $creditsToCarryOver,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $newBalance->available_credits,
                        'reference_type' => 'carryover',
                        'reference_id' => $oldBalance->id,
                        'transaction_date' => Carbon::create($nextYear, 1, 1),
                        'processed_by' => 1, // System
                        'remarks' => "Carried over from {$year} - {$creditsToCarryOver} days",
                    ]);
                    
                    $totalProcessed++;
                    $totalCarriedOver += $creditsToCarryOver;
                    
                    $this->info("✓ {$employee->first_name} {$employee->last_name} - {$leaveType->leave_name}: {$creditsToCarryOver} days carried over");
                }
            }
            
            DB::commit();
            
            $this->info("\n✅ Year-end carryover completed!");
            $this->info("Total records processed: {$totalProcessed}");
            $this->info("Total credits carried over: " . number_format($totalCarriedOver, 2) . " days");
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
