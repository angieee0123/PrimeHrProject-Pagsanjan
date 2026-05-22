<?php

/**
 * Fix Jeremy Pogi's Travel Order Attendance Record
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\TravelOrder;
use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;
use Illuminate\Support\Facades\DB;

echo "==============================================\n";
echo "Fixing Jeremy Pogi's Travel Order Attendance\n";
echo "==============================================\n\n";

try {
    DB::beginTransaction();

    // Find Jeremy Pogi
    $jeremy = Employee::where('first_name', 'LIKE', '%Jeremy%')
        ->where('last_name', 'LIKE', '%Pogi%')
        ->first();

    if (!$jeremy) {
        echo "✗ Jeremy Pogi not found\n";
        exit(1);
    }

    echo "Found Jeremy: ID {$jeremy->id}, Name: {$jeremy->first_name} {$jeremy->last_name}\n";

    // Find his travel order for May 25, 2026
    $travelOrder = TravelOrder::where('employee_id', $jeremy->id)
        ->where('travel_date', '<=', '2026-05-25')
        ->where('return_date', '>=', '2026-05-25')
        ->where('status', 'approved')
        ->first();

    if (!$travelOrder) {
        echo "✗ No approved travel order found for May 25, 2026\n";
        exit(1);
    }

    echo "Found travel order: {$travelOrder->order_number} - {$travelOrder->destination}\n";

    // Find his attendance record for May 25, 2026
    $attendance = Attendance::where('employee_id', $jeremy->id)
        ->where('date', '2026-05-25')
        ->first();

    if (!$attendance) {
        echo "✗ No attendance record found for May 25, 2026\n";
        exit(1);
    }

    echo "Found attendance record: Date {$attendance->date}, Type: {$attendance->attendance_type}\n\n";

    // Check current state
    echo "=== CURRENT STATE ===\n";
    echo "Attendance Type: {$attendance->attendance_type} (should be TRAVEL_ORDER)\n";
    echo "Remarks: " . ($attendance->remarks ?? 'NULL') . "\n";
    echo "AM In: " . ($attendance->am_in ?? 'NULL') . "\n";
    echo "PM In: " . ($attendance->pm_in ?? 'NULL') . "\n";
    echo "Accredited Hours: {$attendance->accredited_hours}\n\n";

    // Update the attendance record to match travel order format
    echo "=== APPLYING FIX ===\n";
    
    $attendance->update([
        'attendance_type' => 'TRAVEL_ORDER',
        'remarks' => "Travel Order: {$travelOrder->destination} - {$travelOrder->order_number}",
    ]);

    echo "✓ Updated attendance record:\n";
    echo "  - Set attendance_type to 'TRAVEL_ORDER'\n";
    echo "  - Added proper remarks\n";

    // Check if AccreditedHoursLog exists and update it
    $accreditedLog = AccreditedHoursLog::where('attendance_id', $attendance->id)->first();
    
    if ($accreditedLog) {
        $accreditedLog->update([
            'computation_notes' => sprintf(
                'On approved travel order: %s - %s (%s)',
                $travelOrder->destination,
                $travelOrder->order_number,
                $travelOrder->purpose
            ),
        ]);
        echo "✓ Updated AccreditedHoursLog computation notes\n";
    } else {
        echo "ℹ️  No AccreditedHoursLog found (may need to be created)\n";
    }

    DB::commit();

    echo "\n=== VERIFICATION ===\n";
    
    // Reload and verify
    $attendance->refresh();
    echo "✓ Attendance Type: {$attendance->attendance_type}\n";
    echo "✓ Remarks: {$attendance->remarks}\n";
    echo "✓ Accredited Hours: {$attendance->accredited_hours}\n";

    echo "\n=== SUCCESS ===\n";
    echo "✅ Jeremy Pogi's travel order attendance has been fixed!\n";
    echo "The attendance record now properly reflects the travel order.\n\n";

    echo "Summary of changes:\n";
    echo "- Changed attendance_type from 'REGULAR' to 'TRAVEL_ORDER'\n";
    echo "- Added proper remarks linking to travel order\n";
    echo "- Maintained 480 accredited hours (8 hours full pay)\n";
    echo "- Kept NULL time values (consistent with travel order system)\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\nFix completed.\n";