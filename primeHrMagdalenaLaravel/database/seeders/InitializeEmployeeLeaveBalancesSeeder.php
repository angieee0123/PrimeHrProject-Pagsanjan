<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InitializeEmployeeLeaveBalancesSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = date('Y');
        $currentDate = Carbon::now();
        
        // Get all active employees
        $employees = Employee::with('employmentDetail')->get();
        
        // Get all active leave types
        $leaveTypes = LeaveType::where('is_active', true)->get();
        
        DB::beginTransaction();
        
        try {
            foreach ($employees as $employee) {
                $hireDate = $employee->employmentDetail?->appointment_date 
                    ? Carbon::parse($employee->employmentDetail->appointment_date) 
                    : null;
                
                if (!$hireDate) {
                    $this->command->warn("Employee {$employee->first_name} {$employee->last_name} has no hire date. Skipping...");
                    continue;
                }
                
                // Calculate months of service in current year
                $startOfYear = Carbon::create($currentYear, 1, 1);
                $serviceStartDate = $hireDate->year == $currentYear ? $hireDate : $startOfYear;
                $monthsWorked = $serviceStartDate->diffInMonths($currentDate) + 1;
                
                // Check if employee has completed 6 months of service
                $hasCompleted6Months = $hireDate->diffInMonths($currentDate) >= 6;
                
                foreach ($leaveTypes as $leaveType) {
                    // Check if balance already exists
                    $existingBalance = LeaveBalance::where('employee_id', $employee->id)
                        ->where('leave_code', $leaveType->leave_code)
                        ->where('year', $currentYear)
                        ->first();
                    
                    if ($existingBalance) {
                        $this->command->info("Balance already exists for {$employee->first_name} {$employee->last_name} - {$leaveType->leave_name}");
                        continue;
                    }
                    
                    $totalCredits = 0;
                    
                    // Calculate credits based on leave type
                    if ($leaveType->is_accrued) {
                        // VL and SL: 1.25 days per month
                        $totalCredits = $monthsWorked * 1.25;
                        
                        // Cap at annual limit
                        if ($totalCredits > $leaveType->annual_limit) {
                            $totalCredits = $leaveType->annual_limit;
                        }
                    } else {
                        // Fixed leave types get full allocation
                        // But some require 6 months service
                        if ($leaveType->requires_6_months && !$hasCompleted6Months) {
                            $totalCredits = 0;
                        } else {
                            $totalCredits = $leaveType->annual_limit;
                        }
                    }
                    
                    // Create leave balance
                    $leaveBalance = LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'leave_code' => $leaveType->leave_code,
                        'year' => $currentYear,
                        'total_credits' => $totalCredits,
                        'used_credits' => 0,
                        'pending_credits' => 0,
                        'available_credits' => $totalCredits,
                        'carried_over' => 0,
                    ]);
                    
                    // Create initialization transaction log
                    LeaveTransaction::create([
                        'employee_id' => $employee->id,
                        'leave_code' => $leaveType->leave_code,
                        'year' => $currentYear,
                        'transaction_type' => 'credit',
                        'amount' => $totalCredits,
                        'balance_before' => 0,
                        'balance_after' => $totalCredits,
                        'reference_type' => 'initialization',
                        'reference_id' => null,
                        'transaction_date' => $currentDate,
                        'processed_by' => 1, // System/Admin user
                        'remarks' => "Initial leave balance for {$currentYear}. " . 
                                   ($leaveType->is_accrued ? "Prorated for {$monthsWorked} months of service." : "Fixed allocation."),
                    ]);
                    
                    $this->command->info("✓ Created {$leaveType->leave_name} balance for {$employee->first_name} {$employee->last_name}: {$totalCredits} days");
                }
            }
            
            DB::commit();
            $this->command->info("\n✅ Leave balances initialized successfully for all employees!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error: " . $e->getMessage());
            throw $e;
        }
    }
}
