<?php

/**
 * Quick Test - Travel Order Attendance Fix
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

echo "Testing Travel Order Attendance Fix...\n\n";

try {
    $employee = Employee::first();
    $user = User::first();
    
    if (!$employee || !$user) {
        echo "No employees or users found.\n";
        exit(1);
    }

    DB::beginTransaction();

    // Create and approve travel order
    $testOrder = TravelOrder::create([
        'employee_id' => $employee->id,
        'destination' => 'Test City',
        'purpose' => 'Testing fix',
        'travel_date' => Carbon::tomorrow()->format('Y-m-d'),
        'return_date' => Carbon::tomorrow()->format('Y-m-d'),
        'duration' => 1,
        'status' => 'pending',
        'filed_by' => $user->id,
    ]);

    echo "Created travel order: {$testOrder->order_number}\n";

    // Approve it
    $testOrder->update([
        'status' => 'approved',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);

    echo "Approved travel order\n";

    // Check attendance
    $attendance = Attendance::where('employee_id', $employee->id)
        ->where('date', $testOrder->travel_date)
        ->where('attendance_type', 'TRAVEL_ORDER')
        ->first();

    if ($attendance) {
        echo "✓ Attendance record created successfully\n";
        echo "  - Date: {$attendance->date}\n";
        echo "  - Type: {$attendance->attendance_type}\n";
        echo "  - Hours: {$attendance->accredited_hours}\n";
        echo "  - Remarks: {$attendance->remarks}\n";
    } else {
        echo "✗ No attendance record found\n";
    }

    // Cleanup
    $attendance?->delete();
    $testOrder->delete();
    
    DB::commit();
    echo "\n✓ Test completed successfully!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ Test failed: {$e->getMessage()}\n";
}