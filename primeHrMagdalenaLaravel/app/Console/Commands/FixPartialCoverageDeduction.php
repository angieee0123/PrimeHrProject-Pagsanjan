<?php

namespace App\Console\Commands;

use App\Models\AccreditedHoursLog;
use Illuminate\Console\Command;

class FixPartialCoverageDeduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'late:fix-partial-coverage
                            {--dry-run : Show what would be fixed without making changes}
                            {--employee= : Fix only specific employee ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix double-deduction bug in partial leave coverage records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $employeeId = $this->option('employee');

        $this->info('🔍 Scanning for affected records...');
        $this->newLine();

        // Find affected records (partial coverage with LWOP)
        $query = AccreditedHoursLog::where('late_deducted_from_leave', true)
            ->where('late_deduction_leave_type', 'LIKE', '%(partial)%')
            ->where('lwop_minutes', '>', 0);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $affectedLogs = $query->with(['employee', 'attendance'])->get();

        if ($affectedLogs->isEmpty()) {
            $this->info('✅ No affected records found. All records are correct!');
            return 0;
        }

        $this->warn("Found {$affectedLogs->count()} affected records");
        $this->newLine();

        // Display table of affected records
        $tableData = [];
        foreach ($affectedLogs as $log) {
            $lateMinutes = $log->late_minutes;
            $lwopMinutes = $log->lwop_minutes;
            $coveredByLeaveMinutes = $lateMinutes - $lwopMinutes;
            
            // Calculate what the correct accredited minutes should be
            // Correct formula: 480 - LWOP (not 480 - total_late)
            $correctAccreditedMinutes = 480 - $lwopMinutes;
            $currentAccreditedMinutes = $log->total_accredited_minutes;
            $difference = $correctAccreditedMinutes - $currentAccreditedMinutes;

            $tableData[] = [
                $log->id,
                $log->employee_id,
                $log->employee->employee_number ?? 'N/A',
                $log->attendance?->date ?? 'N/A',
                $lateMinutes,
                $coveredByLeaveMinutes,
                $lwopMinutes,
                $currentAccreditedMinutes,
                $correctAccreditedMinutes,
                $difference,
            ];
        }

        $this->table(
            ['ID', 'Emp ID', 'Emp #', 'Date', 'Late', 'Covered', 'LWOP', 'Current Hrs', 'Correct Hrs', 'Diff'],
            $tableData
        );

        $this->newLine();

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->info('Run without --dry-run to apply fixes');
            return 0;
        }

        // Confirm before proceeding
        if (!$this->confirm('Do you want to fix these records?', true)) {
            $this->info('Operation cancelled');
            return 0;
        }

        $this->newLine();
        $this->info('🔧 Fixing records...');
        $this->newLine();

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($affectedLogs as $log) {
            try {
                $lateMinutes = $log->late_minutes;
                $lwopMinutes = $log->lwop_minutes;
                $coveredByLeaveMinutes = $lateMinutes - $lwopMinutes;
                
                // Calculate correct accredited minutes
                // The employee should only lose the LWOP time, not the full late time
                $correctAccreditedMinutes = 480 - $lwopMinutes;
                $oldAccreditedMinutes = $log->total_accredited_minutes;

                // Update the log
                $log->update([
                    'total_accredited_minutes' => $correctAccreditedMinutes
                ]);

                // Update the attendance record
                if ($log->attendance) {
                    $log->attendance->update([
                        'accredited_hours' => $correctAccreditedMinutes
                    ]);
                }

                $difference = $correctAccreditedMinutes - $oldAccreditedMinutes;
                $this->info("✅ Fixed Log ID {$log->id} (Employee {$log->employee_id}): {$oldAccreditedMinutes} → {$correctAccreditedMinutes} (+{$difference} minutes)");
                
                $fixedCount++;
            } catch (\Exception $e) {
                $this->error("❌ Error fixing Log ID {$log->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("✅ Fixed {$fixedCount} records");
        
        if ($errorCount > 0) {
            $this->warn("⚠️  {$errorCount} records had errors");
        }

        $this->newLine();
        $this->info('🎉 Done!');

        return 0;
    }
}
