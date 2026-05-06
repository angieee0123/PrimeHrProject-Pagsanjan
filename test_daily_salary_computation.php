<?php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AccreditedHoursLog;
use App\Models\Attendance;
use App\Models\DailySalaryComputation;
use Illuminate\Support\Facades\DB;

echo "Testing daily salary auto-computation...\n\n";

// Get the latest accredited hours log
$log = AccreditedHoursLog::with(['employee.employmentDetail.designationRelation', 'attendance'])
    ->orderBy('id', 'desc')
    ->first();

if (!$log) {
    echo "No accredited hours log found.\n";
    exit(1);
}

echo "Testing with AccreditedHoursLog ID: {$log->id}\n";
echo "Employee ID: {$log->employee_id}\n";
echo "Attendance Date: {$log->attendance->date}\n\n";

// Check if relationships are loaded
echo "Checking relationships...\n";
echo "  Employee loaded: " . ($log->employee ? "✓" : "✗") . "\n";
echo "  Employment Detail loaded: " . ($log->employee->employmentDetail ? "✓" : "✗") . "\n";
echo "  Designation loaded: " . ($log->employee->employmentDetail->designationRelation ?? false ? "✓" : "✗") . "\n";

if ($log->employee->employmentDetail->designationRelation ?? false) {
    $designation = $log->employee->employmentDetail->designationRelation;
    echo "  Designation: {$designation->title}\n";
    echo "  Monthly Rate: ₱" . number_format($designation->monthly_rate, 2) . "\n\n";
}

// Manually trigger computation
echo "Triggering daily salary computation...\n";
$dailySalary = DailySalaryComputation::computeFromAccreditedLog($log);

echo "\n✓ Computation complete!\n\n";

// Display results
echo "--- Daily Salary Computation ---\n";
echo "ID: {$dailySalary->id}\n";
echo "Work Date: {$dailySalary->work_date}\n";
echo "Monthly Rate: ₱" . number_format($dailySalary->monthly_rate, 2) . "\n";
echo "Daily Rate: ₱" . number_format($dailySalary->daily_rate, 2) . "\n";
echo "Hourly Rate: ₱" . number_format($dailySalary->hourly_rate, 2) . "\n";
echo "Accredited Minutes: {$dailySalary->accredited_minutes}\n";
echo "Late Minutes: {$dailySalary->late_minutes}\n";
echo "Undertime Minutes: {$dailySalary->undertime_minutes}\n";
echo "OT Minutes: {$dailySalary->ot_minutes}\n";
echo "Basic Pay: ₱" . number_format($dailySalary->daily_basic_pay, 2) . "\n";
echo "OT Pay: ₱" . number_format($dailySalary->ot_pay, 2) . "\n";
echo "Late Deduction: ₱" . number_format($dailySalary->late_deduction, 2) . "\n";
echo "Undertime Deduction: ₱" . number_format($dailySalary->undertime_deduction, 2) . "\n";
echo "DAILY GROSS PAY: ₱" . number_format($dailySalary->daily_gross_pay, 2) . "\n";

if ($dailySalary->monthly_rate == 0) {
    echo "\n⚠ WARNING: Monthly rate is still 0!\n";
    echo "This means the relationship chain is still failing.\n";
} else {
    echo "\n✓ SUCCESS: Daily salary computed correctly!\n";
}
