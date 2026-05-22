<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AccreditedHoursLog;

// Find Jeremy
$employee = Employee::where('id', 8)->first();

if (!$employee) {
    echo "Employee not found\n";
    exit;
}

echo "===========================================\n";
echo "Employee ID: " . $employee->id . "\n";
echo "===========================================\n\n";

// Check attendance for May 18, 2026
echo "ATTENDANCE RECORD FOR MAY 18, 2026:\n";
echo "-------------------------------------------\n";

$attendance = Attendance::where('employee_id', $employee->id)
    ->whereDate('date', '2026-05-18')
    ->first();

if ($attendance) {
    echo "Attendance ID: " . $attendance->id . "\n";
    echo "Date: " . $attendance->date . "\n";
    echo "AM In: " . ($attendance->am_in ?? 'NULL') . "\n";
    echo "AM Out: " . ($attendance->am_out ?? 'NULL') . "\n";
    echo "PM In: " . ($attendance->pm_in ?? 'NULL') . "\n";
    echo "PM Out: " . ($attendance->pm_out ?? 'NULL') . "\n";
    echo "Accredited Hours: " . $attendance->accredited_hours . " minutes\n";
    echo "Total Hours: " . ($attendance->total_hours ?? 'NULL') . " minutes\n";
    
    // Check accredited hours log
    echo "\nACCREDITED HOURS LOG:\n";
    $logs = AccreditedHoursLog::where('attendance_id', $attendance->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    foreach ($logs as $log) {
        echo "\nLog ID: " . $log->id . "\n";
        echo "Late Minutes: " . $log->late_minutes . "\n";
        echo "Undertime Minutes: " . $log->undertime_minutes . "\n";
        echo "Total Accredited: " . $log->total_accredited_minutes . " minutes\n";
        echo "Late Deducted from Leave: " . ($log->late_deducted_from_leave ? 'YES' : 'NO') . "\n";
        echo "Undertime Deducted from Leave: " . ($log->undertime_deducted_from_leave ? 'YES' : 'NO') . "\n";
        echo "Late Deduction Type: " . ($log->late_deduction_leave_type ?? 'N/A') . "\n";
        echo "Undertime Deduction Type: " . ($log->undertime_deduction_leave_type ?? 'N/A') . "\n";
        echo "LWOP Minutes: " . ($log->lwop_minutes ?? 0) . "\n";
        echo "Created: " . $log->created_at->format('Y-m-d H:i:s') . "\n";
        echo "Updated: " . $log->updated_at->format('Y-m-d H:i:s') . "\n";
    }
    
    // Check attendance corrections
    echo "\n\nATTENDANCE CORRECTIONS:\n";
    echo "-------------------------------------------\n";
    $corrections = AttendanceCorrection::where('attendance_id', $attendance->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($corrections->count() > 0) {
        foreach ($corrections as $correction) {
            echo "\nCorrection ID: " . $correction->id . "\n";
            echo "Date: " . $correction->date . "\n";
            echo "OLD Times:\n";
            echo "  AM In: " . ($correction->old_am_in ?? 'NULL') . " → NEW: " . ($correction->new_am_in ?? 'NULL') . "\n";
            echo "  AM Out: " . ($correction->old_am_out ?? 'NULL') . " → NEW: " . ($correction->new_am_out ?? 'NULL') . "\n";
            echo "  PM In: " . ($correction->old_pm_in ?? 'NULL') . " → NEW: " . ($correction->new_pm_in ?? 'NULL') . "\n";
            echo "  PM Out: " . ($correction->old_pm_out ?? 'NULL') . " → NEW: " . ($correction->new_pm_out ?? 'NULL') . "\n";
            echo "Reason: " . $correction->reason . "\n";
            echo "Corrected By: User ID " . $correction->corrected_by . "\n";
            echo "Created: " . $correction->created_at->format('Y-m-d H:i:s') . "\n";
            echo "-------------------------------------------\n";
        }
    } else {
        echo "No corrections found for this attendance record.\n";
    }
    
} else {
    echo "No attendance record found for May 18, 2026\n";
}

echo "\n===========================================\n";
echo "Check complete!\n";
