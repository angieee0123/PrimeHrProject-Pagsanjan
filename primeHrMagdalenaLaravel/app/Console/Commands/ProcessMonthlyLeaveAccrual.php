<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveAccrualRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessMonthlyLeaveAccrual extends Command
{
    protected $signature = 'leave:process-monthly-accrual {--month=} {--year=}';
    protected $description = 'Process monthly leave accrual for VL and SL (1.25 days per month)';

    public function handle()
    {
        $month = $this->option('month') ?? date('m');
        $year = $this->option('year') ?? date('Y');
        $processDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $this->info("Processing monthly leave accrual for {$processDate->format('F Y')}...\n");
        
        // Get accrued leave types (VL and SL)
        $accruedLeaveTypes = LeaveType::where('is_accrued', true)
            ->where('is_active', true)
            ->get();
        
        if ($accruedLeaveTypes->isEmpty()) {
            $this->warn('No accrued leave types found.');
            return 0;
        }
        
        // Get all active employees
        $employees = Employee::with('employmentDetail')->get();
        
        $totalProcessed = 0;
        $totalCredits = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($employees as $employee) {
                $hireDate = $employee->employmentDetail?->appointment_date 
                    ? Carbon::parse($employee->employmentDetail->appointment_date) 
                    : null;
                
                if (!$hireDate) {
                    $this->warn("Employee {$employee->first_name} {$employee->last_name} has no appointment date. Skipping...");
                    continue;
                }
                
                // Check if employee was hired before or during this month
                if ($hireDate->year > $year || ($hireDate->year == $year && $hireDate->month > $month)) {
                    $this->info("Employee {$employee->first_name} {$employee->last_name} not yet hired in {$processDate->format('F Y')}. Skipping...");
                    continue;
                }
                
                // Check if employee has completed 6 months for VL
                $monthsOfService = $hireDate->diffInMonths($processDate);
                
                foreach ($accruedLeaveTypes as $leaveType) {
                    // Get accrual rate
                    $accrualRate = LeaveAccrualRate::where('leave_type_id', $leaveType->id)
                        ->where('is_active', true)
                        ->where('effective_date', '<=', $processDate)
                        ->where(function($query) use ($processDate) {
                            $query->whereNull('end_date')
                                  ->orWhere('end_date', '>=', $processDate);
                        })
                        ->first();
                    
                    if (!$accrualRate) {
                        $this->warn("No accrual rate found for {$leaveType->leave_name}. Skipping...");
                        continue;
                    }
                    
                    // Check 6-month requirement for VL
                    if ($leaveType->requires_6_months && $monthsOfService < 6) {
                        $this->info("Employee {$employee->first_name} {$employee->last_name} hasn't completed 6 months for {$leaveType->leave_name}. Skipping...");
                        continue;
                    }
                    
                    // Get or create leave balance for this year
                    $leaveBalance = LeaveBalance::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'leave_code' => $leaveType->leave_code,
                            'year' => $year,
                        ],
                        [
                            'total_credits' => 0,
                            'used_credits' => 0,
                            'pending_credits' => 0,
                            'available_credits' => 0,
                            'carried_over' => 0,
                        ]
                    );
                    
                    // Check if already processed for this month
                    $existingTransaction = LeaveTransaction::where('employee_id', $employee->id)
                        ->where('leave_code', $leaveType->leave_code)
                        ->where('year', $year)
                        ->where('reference_type', 'accrual')
                        ->whereYear('transaction_date', $year)
                        ->whereMonth('transaction_date', $month)
                        ->exists();
                    
                    if ($existingTransaction) {
                        $this->info("Accrual already processed for {$employee->first_name} {$employee->last_name} - {$leaveType->leave_name} in {$processDate->format('F Y')}");
                        continue;
                    }
                    
                    // Calculate credits to add (1.25 days per month)
                    $creditsToAdd = $accrualRate->credits_earned_per_period;
                    
                    // Check if adding credits would exceed annual limit
                    $newTotalCredits = $leaveBalance->total_credits + $creditsToAdd;
                    if ($newTotalCredits > $leaveType->annual_limit) {
                        $creditsToAdd = $leaveType->annual_limit - $leaveBalance->total_credits;
                        if ($creditsToAdd <= 0) {
                            $this->info("Employee {$employee->first_name} {$employee->last_name} has reached annual limit for {$leaveType->leave_name}");
                            continue;
                        }
                    }
                    
                    // Update balance
                    $balanceBefore = $leaveBalance->available_credits;
                    $leaveBalance->total_credits += $creditsToAdd;
                    $leaveBalance->available_credits += $creditsToAdd;
                    $leaveBalance->save();
                    
                    // Create transaction log
                    LeaveTransaction::create([
                        'employee_id' => $employee->id,
                        'leave_code' => $leaveType->leave_code,
                        'year' => $year,
                        'transaction_type' => 'credit',
                        'amount' => $creditsToAdd,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $leaveBalance->available_credits,
                        'reference_type' => 'accrual',
                        'reference_id' => null,
                        'transaction_date' => $processDate,
                        'processed_by' => 1, // System
                        'remarks' => "Monthly accrual for {$processDate->format('F Y')} - {$accrualRate->credits_earned_per_period} days",
                    ]);
                    
                    $totalProcessed++;
                    $totalCredits += $creditsToAdd;
                    
                    $this->info("✓ {$employee->first_name} {$employee->last_name} - {$leaveType->leave_name}: +{$creditsToAdd} days (Total: {$leaveBalance->total_credits})");
                }
            }
            
            DB::commit();
            
            $this->info("\n✅ Monthly accrual completed!");
            $this->info("Total records processed: {$totalProcessed}");
            $this->info("Total credits added: " . number_format($totalCredits, 2) . " days");
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
