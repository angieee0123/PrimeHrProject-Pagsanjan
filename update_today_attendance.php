<?php

// Run this script: php update_today_attendance.php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "Updating today's attendance to show only time in...\n";

$today = Carbon::today()->format('Y-m-d');

// Get all attendance records for today
$todayRecords = DB::table('attendance')
    ->where('date', $today)
    ->where('attendance_type', 'REGULAR')
    ->get();

echo "Found {$todayRecords->count()} records for today ({$today})\n";

foreach ($todayRecords as $record) {
    // Update to keep only AM IN (since it's still ongoing)
    DB::table('attendance')
        ->where('id', $record->id)
        ->update([
            'am_out' => null,
            'pm_in' => null,
            'pm_out' => null
        ]);
    
    echo "Employee {$record->employee_id} - Updated to show only AM IN\n";
}

echo "\n✅ Today's attendance updated successfully!\n";
echo "All employees now show only TIME IN for {$today}\n";
