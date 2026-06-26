<?php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;

echo "Starting daily salary computation backfill...\n\n";

// Get all accredited hours logs that don't have salary computations
$logs = AccreditedHoursLog::whereDoesntHave('dailySalaryComputation')
    ->with('employee')
    ->get();

if ($logs->isEmpty()) {
    echo "No logs found without salary computations. All records are up to date!\n";
    exit(0);
}

echo "Found " . $logs->count() . " logs to process...\n\n";

$processed = 0;
$errors = 0;

foreach ($logs as $log) {
    try {
        $employeeName = $log->employee 
            ? "{$log->employee->first_name} {$log->employee->last_name}" 
            : "Employee ID {$log->employee_id}";
        
        echo "Processing: {$employeeName} - Date: {$log->attendance->date}\n";
        
        // Trigger the computation
        DailySalaryComputation::computeFromAccreditedLog($log);
        
        $processed++;
        
        if ($processed % 50 == 0) {
            echo "\n--- Processed {$processed} records so far ---\n\n";
        }
    } catch (\Exception $e) {
        $errors++;
        echo "  ERROR: {$e->getMessage()}\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Backfill completed!\n";
echo "  Successfully processed: {$processed}\n";
echo "  Errors: {$errors}\n";
echo str_repeat('=', 60) . "\n";
