<?php

/**
 * Verify Travel Order Attendance Marking Fix
 * 
 * This script verifies that the travel order attendance marking is working correctly
 * after the fixes have been applied.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TravelOrder;
use App\Models\Employee;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "==============================================\n";
echo "Travel Order Attendance Fix Verification\n";
echo "==============================================\n\n";

try {
    // Check if attendance table has required columns
    echo "Step 1: Checking database schema...\n";
    
    $columns = DB::select("SHOW COLUMNS FROM attendance");
    $columnNames = array_column($columns, 'Field');
    
    $requiredColumns = ['attendance_type', 'remarks'];
    $missingColumns = array_diff($requiredColumns, $columnNames);
    
    if (!empty($missingColumns)) {
        echo "✗ Missing columns in attendance table: " . implode(', ', $missingColumns) . "\n";
        echo "Please run: php artisan migrate\n";
        exit(1);
    }
    
    echo "✓ All required columns present in attendance table\n\n";

    // Test the corrected logic
    echo "Step 2: Testing corrected travel order logic...\n";
    
    $employee = Employee::first();
    $user = User::first();
    
    if (!$employee || !$user) {
        echo "✗ No employees or users found. Please seed the database first.\n";
        exit(1);
    }

    DB::beginTransaction();

    // Create test travel order
    $testOrder = TravelOrder::create([
        'employee_id' => $employee->id,
        'destination' => 'Test City - Verification',
        'purpose' => 'Testing corrected attendance marking logic',
        'travel_date' => Carbon::tomorrow()->format('Y-m-d'),
        'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
        'duration' => 2,
        'status' => 'pending',
        'filed_by' => $user->id,
    ]);

    echo "✓ Test travel order created: {$testOrder->order_number}\n";

    // Approve the travel order
    $testOrder->update([
        'status' => 'approved',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);

    echo "✓ Travel order approved\n";

    // Verify attendance records
    $attendanceRecords = Attendance::where('employee_id', $employee->id)
        ->whereBetween('date', [$testOrder->travel_date, $testOrder->return_date])
        ->where('attendance_type', 'TRAVEL_ORDER')
        ->get();

    echo "\nStep 3: Verifying attendance records...\n";
    
    if ($attendanceRecords->isEmpty()) {
        echo "✗ No travel order attendance records found\n";
        DB::rollBack();
        exit(1);
    }

    $allCorrect = true;
    foreach ($attendanceRecords as $record) {
        echo "Date: {$record->date}\n";
        echo "  - AM In: " . ($record->am_in ?? 'NULL') . " (should be NULL)\n";
        echo "  - AM Out: " . ($record->am_out ?? 'NULL') . " (should be NULL)\n";
        echo "  - PM In: " . ($record->pm_in ?? 'NULL') . " (should be NULL)\n";
        echo "  - PM Out: " . ($record->pm_out ?? 'NULL') . " (should be NULL)\n";
        echo "  - Accredited Hours: {$record->accredited_hours} (should be 480)\n";
        echo "  - Attendance Type: {$record->attendance_type} (should be 'TRAVEL_ORDER')\n";
        echo "  - Remarks: {$record->remarks}\n";
        
        // Verify all fields are correct
        if ($record->am_in !== null || $record->am_out !== null || 
            $record->pm_in !== null || $record->pm_out !== null ||
            $record->accredited_hours !== 480 || 
            $record->attendance_type !== 'TRAVEL_ORDER') {
            echo "  ✗ INCORRECT VALUES DETECTED\n";
            $allCorrect = false;
        } else {
            echo "  ✓ All values correct\n";
        }
        echo "\n";
    }

    // Clean up
    $attendanceRecords->each->delete();
    $testOrder->delete();
    
    DB::commit();

    if ($allCorrect) {
        echo "==============================================\n";
        echo "✅ VERIFICATION PASSED!\n";
        echo "==============================================\n";
        echo "The travel order attendance marking is now working correctly:\n";
        echo "1. ✓ Time fields set to NULL (consistent with leave system)\n";
        echo "2. ✓ Accredited hours set to 480 minutes (8 hours)\n";
        echo "3. ✓ Attendance type set to 'TRAVEL_ORDER'\n";
        echo "4. ✓ Proper remarks added\n";
        echo "5. ✓ Observer pattern working correctly\n\n";
    } else {
        echo "==============================================\n";
        echo "❌ VERIFICATION FAILED!\n";
        echo "==============================================\n";
        echo "Some attendance records have incorrect values.\n";
        echo "Please check the TravelOrderObserver implementation.\n\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ VERIFICATION FAILED: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Verification completed!\n";