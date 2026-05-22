<?php

/**
 * Investigate Jeremy Pogi's Leave Issue - May 25, 2026
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use Carbon\Carbon;

echo "==============================================\n";
echo "Jeremy Pogi Leave Investigation - May 25, 2026\n";
echo "==============================================\n\n";

try {
    // Find Jeremy Pogi
    $jeremy = Employee::where('first_name', 'LIKE', '%Jeremy%')
        ->orWhere('last_name', 'LIKE', '%Pogi%')
        ->orWhere('first_name', 'LIKE', '%JEREMY%')
        ->orWhere('last_name', 'LIKE', '%POGI%')
        ->first();

    if (!$jeremy) {
        echo "Jeremy Pogi not found. Searching for similar names...\n";
        $employees = Employee::where('first_name', 'LIKE', '%Jeremy%')
            ->orWhere('last_name', 'LIKE', '%Pogi%')
            ->get();
        
        if ($employees->count() > 0) {
            echo "Found similar employees:\n";
            foreach ($employees as $emp) {
                echo "  ID: {$emp->id}, Name: {$emp->first_name} {$emp->last_name}\n";
            }
        } else {
            echo "No employees found with Jeremy or Pogi in their name.\n";
        }
        exit(1);
    }

    echo "Found Jeremy: ID {$jeremy->id}, Name: {$jeremy->first_name} {$jeremy->last_name}\n\n";

    // Check attendance for May 25, 2026
    $targetDate = '2026-05-25';
    echo "=== ATTENDANCE RECORD FOR MAY 25, 2026 ===\n";
    
    $attendance = Attendance::where('employee_id', $jeremy->id)
        ->where('date', $targetDate)
        ->first();

    if ($attendance) {
        echo "✓ Attendance record found:\n";
        echo "  Date: {$attendance->date}\n";
        echo "  AM In: " . ($attendance->am_in ?? 'NULL') . "\n";
        echo "  AM Out: " . ($attendance->am_out ?? 'NULL') . "\n";
        echo "  PM In: " . ($attendance->pm_in ?? 'NULL') . "\n";
        echo "  PM Out: " . ($attendance->pm_out ?? 'NULL') . "\n";
        echo "  Accredited Hours: {$attendance->accredited_hours}\n";
        echo "  Total Hours: {$attendance->total_hours}\n";
        echo "  Attendance Type: " . ($attendance->attendance_type ?? 'NULL') . "\n";
        echo "  Remarks: " . ($attendance->remarks ?? 'NULL') . "\n";
    } else {
        echo "✗ No attendance record found for May 25, 2026\n";
    }

    echo "\n=== LEAVE APPLICATIONS COVERING MAY 25, 2026 ===\n";
    
    $leaves = LeaveApplication::where('employee_id', $jeremy->id)
        ->where('start_date', '<=', $targetDate)
        ->where('end_date', '>=', $targetDate)
        ->get();

    if ($leaves->count() > 0) {
        foreach ($leaves as $leave) {
            echo "✓ Leave Application Found:\n";
            echo "  ID: {$leave->id}\n";
            echo "  Application Number: {$leave->application_number}\n";
            echo "  Start Date: {$leave->start_date}\n";
            echo "  End Date: {$leave->end_date}\n";
            echo "  Status: {$leave->status}\n";
            echo "  Approved At: " . ($leave->approved_at ?? 'NULL') . "\n";
            echo "  Approved By: " . ($leave->approved_by ?? 'NULL') . "\n";
            
            if ($leave->leaveType) {
                echo "  Leave Type: {$leave->leaveType->leave_name}\n";
            } else {
                echo "  Leave Type: NULL (Missing relationship)\n";
            }
            echo "\n";
        }
    } else {
        echo "✗ No leave applications found covering May 25, 2026\n";
    }

    // Check all recent leave applications
    echo "=== ALL LEAVE APPLICATIONS (May 2026) ===\n";
    
    $allLeaves = LeaveApplication::where('employee_id', $jeremy->id)
        ->where(function($query) {
            $query->whereBetween('start_date', ['2026-05-01', '2026-05-31'])
                  ->orWhereBetween('end_date', ['2026-05-01', '2026-05-31']);
        })
        ->orderBy('start_date')
        ->get();

    if ($allLeaves->count() > 0) {
        foreach ($allLeaves as $leave) {
            echo "  {$leave->application_number} | {$leave->start_date} to {$leave->end_date} | Status: {$leave->status}";
            if ($leave->approved_at) {
                echo " | Approved: {$leave->approved_at}";
            }
            echo "\n";
        }
    } else {
        echo "  No leave applications found for May 2026\n";
    }

    // Check if LeaveApplicationObserver is registered
    echo "\n=== SYSTEM CHECK ===\n";
    
    // Check if attendance_type column exists
    $columns = \DB::select("SHOW COLUMNS FROM attendance");
    $columnNames = array_column($columns, 'Field');
    
    if (in_array('attendance_type', $columnNames)) {
        echo "✓ attendance_type column exists\n";
    } else {
        echo "✗ attendance_type column missing - run migrations\n";
    }
    
    if (in_array('remarks', $columnNames)) {
        echo "✓ remarks column exists\n";
    } else {
        echo "✗ remarks column missing - run migrations\n";
    }

    echo "\n=== DIAGNOSIS ===\n";
    
    if (!$attendance && $leaves->count() > 0) {
        $approvedLeave = $leaves->where('status', 'approved')->first();
        if ($approvedLeave) {
            echo "❌ ISSUE FOUND: Leave is approved but no attendance record created\n";
            echo "   This indicates the LeaveApplicationObserver is not working\n";
            echo "   Possible causes:\n";
            echo "   1. Observer not registered in AppServiceProvider\n";
            echo "   2. Observer code has errors\n";
            echo "   3. Database transaction rollback\n";
        } else {
            echo "ℹ️  Leave exists but not approved - no attendance record expected\n";
        }
    } else if ($attendance && $leaves->count() == 0) {
        echo "ℹ️  Attendance exists but no leave found - manual entry or different system\n";
    } else if (!$attendance && $leaves->count() == 0) {
        echo "ℹ️  No attendance or leave records found for this date\n";
    } else {
        echo "✓ Both attendance and leave records found - system working correctly\n";
    }

} catch (\Exception $e) {
    echo "\n✗ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\nInvestigation completed.\n";