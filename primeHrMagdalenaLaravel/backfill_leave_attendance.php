<?php

/**
 * Backfill Attendance Records for Existing Approved Leaves
 * 
 * This script creates attendance records for all approved leaves
 * that don't have corresponding attendance entries in the database.
 * 
 * Usage: php backfill_leave_attendance.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LeaveApplication;
use App\Models\Attendance;
use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "==============================================\n";
echo "Backfill Attendance Records for Approved Leaves\n";
echo "==============================================\n\n";

try {
    // Get all approved leaves
    $approvedLeaves = LeaveApplication::where('status', 'approved')
        ->with(['employee', 'leaveType'])
        ->orderBy('start_date')
        ->get();

    echo "Found " . $approvedLeaves->count() . " approved leave applications\n\n";

    $totalRecordsCreated = 0;
    $totalLeavesProcessed = 0;
    $totalSkipped = 0;

    foreach ($approvedLeaves as $leave) {
        echo "Processing: {$leave->application_number}\n";
        echo "  Employee: {$leave->employee->first_name} {$leave->employee->last_name} (ID: {$leave->employee->id})\n";
        echo "  Leave Type: {$leave->leaveType->leave_name} ({$leave->leaveType->leave_code})\n";
        echo "  Dates: {$leave->start_date} to {$leave->end_date} ({$leave->number_of_days} days)\n";

        $employee = $leave->employee;
        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);
        $current = $startDate->copy();
        $recordsCreatedForThisLeave = 0;

        DB::beginTransaction();

        try {
            while ($current->lte($endDate)) {
                // Skip weekends (Saturday = 6, Sunday = 0)
                if (!in_array($current->dayOfWeek, [0, 6])) {
                    $dateKey = $current->format('Y-m-d');
                    
                    // Check if attendance record already exists
                    $existing = Attendance::where('employee_id', $employee->id)
                        ->where('date', $dateKey)
                        ->first();

                    if (!$existing) {
                        echo "    Creating attendance for {$dateKey} ({$current->format('l')})\n";
                        
                        // Get employee schedule for this date
                        $schedule = $employee->getScheduleForDate($dateKey);
                        
                        if (!$schedule) {
                            echo "      WARNING: No schedule found for employee, using defaults\n";
                        }

                        // Create attendance record
                        $attendance = Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $dateKey,
                            'am_in' => 'ON_LEAVE',
                            'am_out' => 'ON_LEAVE',
                            'pm_in' => 'ON_LEAVE',
                            'pm_out' => 'ON_LEAVE',
                            'ot_in' => null,
                            'ot_out' => null,
                            'accredited_hours' => 480, // 8 hours in minutes
                            'total_hours' => 480,
                        ]);

                        // Create accredited hours log
                        $accreditedLog = AccreditedHoursLog::create([
                            'attendance_id' => $attendance->id,
                            'employee_id' => $employee->id,
                            'schedule_id' => $schedule ? $schedule->id : null,
                            'am_accredited_minutes' => 240, // 4 hours
                            'pm_accredited_minutes' => 240, // 4 hours
                            'ot_minutes' => 0,
                            'late_minutes' => 0,
                            'undertime_minutes' => 0,
                            'total_accredited_minutes' => 480, // 8 hours
                            'total_actual_minutes' => 480,
                            'am_grace_applied' => false,
                            'pm_grace_applied' => false,
                            'computation_notes' => sprintf(
                                'Backfilled: On approved leave - %s (%s) - %s',
                                $leave->leaveType->leave_name,
                                $leave->leaveType->leave_code,
                                $leave->application_number
                            ),
                        ]);

                        // Create daily salary computation
                        DailySalaryComputation::computeFromAccreditedLog($accreditedLog);

                        $recordsCreatedForThisLeave++;
                        $totalRecordsCreated++;
                    } else {
                        echo "    Skipping {$dateKey} - attendance already exists\n";
                        $totalSkipped++;
                    }
                } else {
                    echo "    Skipping {$dateKey} ({$current->format('l')}) - weekend\n";
                }

                $current->addDay();
            }

            DB::commit();
            $totalLeavesProcessed++;
            echo "  ✓ Created {$recordsCreatedForThisLeave} attendance records\n\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "  ✗ ERROR: {$e->getMessage()}\n";
            echo "  Rolling back changes for this leave application\n\n";
        }
    }

    echo "==============================================\n";
    echo "Backfill Summary\n";
    echo "==============================================\n";
    echo "Total approved leaves found: " . $approvedLeaves->count() . "\n";
    echo "Leaves successfully processed: {$totalLeavesProcessed}\n";
    echo "Total attendance records created: {$totalRecordsCreated}\n";
    echo "Records skipped (already exist): {$totalSkipped}\n";
    echo "==============================================\n\n";

    if ($totalRecordsCreated > 0) {
        echo "✓ Backfill completed successfully!\n";
        echo "\nNext steps:\n";
        echo "1. Verify attendance records in database\n";
        echo "2. Check accredited_hours_log entries\n";
        echo "3. Verify daily_salary_computations\n";
        echo "4. Test DTR export functionality\n";
        echo "5. Verify payroll calculations\n";
    } else {
        echo "ℹ No new records needed to be created.\n";
        echo "All approved leaves already have attendance records.\n";
    }

} catch (\Exception $e) {
    echo "\n✗ FATAL ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nBackfill script completed.\n";
