<?php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing daily salary computations...\n\n";

// Get all daily salary computations
$computations = DB::table('daily_salary_computations')->get();

echo "Found {$computations->count()} records to fix\n\n";

foreach ($computations as $comp) {
    // Get employee's monthly rate
    $result = DB::table('employees')
        ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
        ->join('designations', 'employment_details.designation_id', '=', 'designations.id')
        ->where('employees.id', $comp->employee_id)
        ->select('designations.monthly_rate', 'designations.title')
        ->first();
    
    if (!$result || !$result->monthly_rate) {
        echo "⚠ Employee ID {$comp->employee_id}: No monthly rate found, skipping\n";
        continue;
    }
    
    $monthlyRate = $result->monthly_rate;
    $dailyRate = $monthlyRate / 22;
    $hourlyRate = $dailyRate / 8;
    
    // Recalculate pay components
    $dailyBasicPay = ($comp->accredited_minutes / 480) * $dailyRate;
    $otPay = ($comp->ot_minutes / 60) * $hourlyRate * 1.25;
    $lateDeduction = ($comp->late_minutes / 60) * $hourlyRate;
    $undertimeDeduction = ($comp->undertime_minutes / 60) * $hourlyRate;
    $dailyGrossPay = $dailyBasicPay + $otPay - $lateDeduction - $undertimeDeduction;
    
    // Update the record
    DB::table('daily_salary_computations')
        ->where('id', $comp->id)
        ->update([
            'monthly_rate' => $monthlyRate,
            'daily_rate' => round($dailyRate, 2),
            'hourly_rate' => round($hourlyRate, 2),
            'daily_basic_pay' => round($dailyBasicPay, 2),
            'ot_pay' => round($otPay, 2),
            'late_deduction' => round($lateDeduction, 2),
            'undertime_deduction' => round($undertimeDeduction, 2),
            'daily_gross_pay' => round($dailyGrossPay, 2),
            'updated_at' => now(),
        ]);
    
    echo "✓ ID {$comp->id} - Employee {$comp->employee_id} ({$comp->work_date})\n";
    echo "  Position: {$result->title}\n";
    echo "  Monthly Rate: ₱" . number_format($monthlyRate, 2) . "\n";
    echo "  Daily Rate: ₱" . number_format($dailyRate, 2) . "\n";
    echo "  Accredited: {$comp->accredited_minutes} mins\n";
    echo "  Basic Pay: ₱" . number_format($dailyBasicPay, 2) . "\n";
    echo "  OT Pay: ₱" . number_format($otPay, 2) . "\n";
    echo "  Late Deduction: ₱" . number_format($lateDeduction, 2) . "\n";
    echo "  Undertime Deduction: ₱" . number_format($undertimeDeduction, 2) . "\n";
    echo "  Daily Gross Pay: ₱" . number_format($dailyGrossPay, 2) . "\n\n";
}

echo "✓ All records fixed!\n\n";

// Show summary
echo "--- Updated Summary ---\n";
$summary = DB::table('daily_salary_computations')
    ->selectRaw('
        employee_id,
        COUNT(*) as days_worked,
        SUM(accredited_minutes) / 60 as total_hours,
        SUM(daily_basic_pay) as total_basic,
        SUM(ot_pay) as total_ot,
        SUM(late_deduction) as total_late_deduction,
        SUM(undertime_deduction) as total_undertime_deduction,
        SUM(daily_gross_pay) as total_gross_pay
    ')
    ->groupBy('employee_id')
    ->get();

foreach ($summary as $row) {
    echo "\nEmployee ID {$row->employee_id}:\n";
    echo "  Days Worked: {$row->days_worked}\n";
    echo "  Total Hours: " . number_format($row->total_hours, 2) . "\n";
    echo "  Basic Pay: ₱" . number_format($row->total_basic, 2) . "\n";
    echo "  OT Pay: ₱" . number_format($row->total_ot, 2) . "\n";
    echo "  Late Deduction: ₱" . number_format($row->total_late_deduction, 2) . "\n";
    echo "  Undertime Deduction: ₱" . number_format($row->total_undertime_deduction, 2) . "\n";
    echo "  GROSS PAY: ₱" . number_format($row->total_gross_pay, 2) . "\n";
}
