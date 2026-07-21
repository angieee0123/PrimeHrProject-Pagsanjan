<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\AccrualRate;
use Carbon\Carbon;

class LeaveBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = now()->year;
        $employees = Employee::all();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                // Check if balance already exists
                $existingBalance = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_code', $leaveType->leave_code)
                    ->where('year', $currentYear)
                    ->first();

                if ($existingBalance) {
                    continue;
                }

                // Get accrual rate for this leave type
                $accrualRate = AccrualRate::where('leave_code', $leaveType->leave_code)->first();
                $totalCredits = $accrualRate ? $accrualRate->annual_credits : 0;

                // Create new leave balance
                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_code' => $leaveType->leave_code,
                    'year' => $currentYear,
                    'total_credits' => $totalCredits,
                    'used_credits' => 0,
                    'pending_credits' => 0,
                    'available_credits' => $totalCredits,
                    'carried_over' => 0,
                ]);
            }
        }

        $this->command->info('Leave balances seeded successfully for ' . $employees->count() . ' employees!');
    }
}
