<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\AccrualRate;

class SyncLeaveBalances extends Command
{
    protected $signature = 'leave:sync-balances {--year= : Specific year to sync}';
    protected $description = 'Create leave balances for all employees for the current or specified year';

    public function handle()
    {
        $year = $this->option('year') ?? now()->year;
        $employees = Employee::all();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $created = 0;
        $skipped = 0;

        $this->info("Syncing leave balances for year: $year");
        $bar = $this->output->createProgressBar(count($employees) * count($leaveTypes));

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $existing = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_code', $leaveType->leave_code)
                    ->where('year', $year)
                    ->first();

                if ($existing) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $accrualRate = AccrualRate::where('leave_code', $leaveType->leave_code)->first();
                $totalCredits = $accrualRate?->annual_credits ?? 0;

                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_code' => $leaveType->leave_code,
                    'year' => $year,
                    'total_credits' => $totalCredits,
                    'used_credits' => 0,
                    'pending_credits' => 0,
                    'available_credits' => $totalCredits,
                    'carried_over' => 0,
                ]);

                $created++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->info("\n\nLeave balances sync completed!");
        $this->info("Created: $created | Skipped: $skipped");
    }
}
