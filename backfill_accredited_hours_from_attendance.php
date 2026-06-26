<?php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\AccreditedHoursLog;
use App\Models\Employee;

echo "Starting accredited hours log backfill from attendance records...\n\n";

// Get all attendance records with employee info
$attendances = Attendance::with(['employee.employmentDetail'])
    ->whereNotNull('employee_id')
    ->get();

if ($attendances->isEmpty()) {
    echo "No attendance records found!\n";
    exit(0);
}

echo "Found " . $attendances->count() . " attendance records to process...\n\n";

$processed = 0;
$skipped = 0;
$errors = 0;

foreach ($attendances as $attendance) {
    try {
        // Check if accredited hours log already exists
        $existing = AccreditedHoursLog::where('attendance_id', $attendance->id)->first();
        if ($existing) {
            $skipped++;
            continue;
        }

        $employee = $attendance->employee;
        if (!$employee) {
            $errors++;
            echo "  ERROR: No employee found for attendance ID {$attendance->id}\n";
            continue;
        }

        // Get schedule - try from employment detail first
        $scheduleId = null;
        if ($employee->employmentDetail && $employee->employmentDetail->schedule_id) {
            $scheduleId = $employee->employmentDetail->schedule_id;
        }

        // Calculate accredited minutes from attendance times
        $amMinutes = 0;
        $pmMinutes = 0;
        $otMinutes = 0;
        
        // AM: 8:00-12:00 (240 minutes)
        if ($attendance->am_in && $attendance->am_out) {
            $amMinutes = 240; // Standard 4 hours
        }
        
        // PM: 13:00-17:00 (240 minutes)
        if ($attendance->pm_in && $attendance->pm_out) {
            $pmMinutes = 240; // Standard 4 hours
        }
        
        // OT calculation (if exists)
        if ($attendance->ot_in && $attendance->ot_out) {
            $otMinutes = 60; // Simplified: 1 hour OT
        }

        $totalMinutes = $amMinutes + $pmMinutes;

        // Only create log if there's actual attendance
        if ($totalMinutes > 0 || $otMinutes > 0) {
            AccreditedHoursLog::create([
                'attendance_id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'schedule_id' => $scheduleId,
                'am_accredited_minutes' => $amMinutes,
                'pm_accredited_minutes' => $pmMinutes,
                'ot_minutes' => $otMinutes,
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'total_accredited_minutes' => $totalMinutes,
                'total_actual_minutes' => $totalMinutes,
                'am_grace_applied' => false,
                'pm_grace_applied' => false,
                'computation_notes' => 'Backfilled from attendance',
                'lwop_minutes' => 0,
                'requires_salary_deduction' => false,
            ]);

            $processed++;
            
            if ($processed % 100 == 0) {
                echo "Processed {$processed} records...\n";
            }
        }
    } catch (\Exception $e) {
        $errors++;
        echo "  ERROR on attendance ID {$attendance->id}: {$e->getMessage()}\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Backfill completed!\n";
echo "  Successfully processed: {$processed}\n";
echo "  Skipped (already exists): {$skipped}\n";
echo "  Errors: {$errors}\n";
echo str_repeat('=', 60) . "\n";
