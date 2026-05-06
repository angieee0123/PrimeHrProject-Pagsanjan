<?php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking daily_salary_computations table...\n\n";

// Check if table exists
try {
    $count = DB::table('daily_salary_computations')->count();
    echo "✓ Table exists\n";
    echo "✓ Current records: {$count}\n\n";
} catch (Exception $e) {
    echo "✗ Table does not exist. Run migrations first:\n";
    echo "  php artisan migrate\n\n";
    exit(1);
}

// Check accredited_hours_log data
$accreditedLogs = DB::table('accredited_hours_log')
    ->join('attendance', 'accredited_hours_log.attendance_id', '=', 'attendance.id')
    ->select('accredited_hours_log.*', 'attendance.date')
    ->get();

echo "Found {$accreditedLogs->count()} accredited hours logs\n\n";

if ($accreditedLogs->isEmpty()) {
    echo "No accredited hours logs found. Daily salary cannot be computed.\n";
    exit(0);
}

echo "Computing daily salaries...\n\n";

foreach ($accreditedLogs as $log) {
    // Get employee's monthly rate
    $monthlyRate = DB::table('employees')
        ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
        ->join('designations', 'employment_details.designation_id', '=', 'designations.id')
        ->where('employees.id', $log->employee_id)
        ->value('designations.monthly_rate');
    
    if (!$monthlyRate) {
        echo "⚠ Employee ID {$log->employee_id}: No monthly rate found\n";
        continue;
    }
    
    // Calculate rates
    $dailyRate = $monthlyRate / 22;
    $hourlyRate = $dailyRate / 8;
    
    // Calculate pay components
    $dailyBasicPay = ($log->total_accredited_minutes / 480) * $dailyRate;
    $otPay = ($log->ot_minutes / 60) * $hourlyRate * 1.25;
    $lateDeduction = ($log->late_minutes / 60) * $hourlyRate;
    $undertimeDeduction = ($log->undertime_minutes / 60) * $hourlyRate;
    $dailyGrossPay = $dailyBasicPay + $otPay - $lateDeduction - $undertimeDeduction;
    
    // Insert or update
    DB::table('daily_salary_computations')->updateOrInsert(
        [
            'employee_id' => $log->employee_id,
            'work_date' => $log->date,
        ],
        [
            'attendance_id' => $log->attendance_id,
            'accredited_hours_log_id' => $log->id,
            'monthly_rate' => $monthlyRate,
            'daily_rate' => round($dailyRate, 2),
            'hourly_rate' => round($hourlyRate, 2),
            'required_minutes' => 480,
            'accredited_minutes' => $log->total_accredited_minutes,
            'actual_minutes' => $log->total_actual_minutes,
            'late_minutes' => $log->late_minutes,
            'undertime_minutes' => $log->undertime_minutes,
            'ot_minutes' => $log->ot_minutes,
            'daily_basic_pay' => round($dailyBasicPay, 2),
            'ot_pay' => round($otPay, 2),
            'late_deduction' => round($lateDeduction, 2),
            'undertime_deduction' => round($undertimeDeduction, 2),
            'daily_gross_pay' => round($dailyGrossPay, 2),
            'is_present' => $log->total_accredited_minutes > 0 ? 1 : 0,
            'is_absent' => $log->total_accredited_minutes == 0 ? 1 : 0,
            'is_holiday' => 0,
            'is_rest_day' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    
    echo "✓ Employee ID {$log->employee_id} - {$log->date}: ₱" . number_format($dailyGrossPay, 2) . "\n";
}

$finalCount = DB::table('daily_salary_computations')->count();
echo "\n✓ Computation complete!\n";
echo "✓ Total daily salary records: {$finalCount}\n";

// Show summary
echo "\n--- Summary ---\n";
$summary = DB::table('daily_salary_computations')
    ->selectRaw('
        employee_id,
        COUNT(*) as days_worked,
        SUM(daily_gross_pay) as total_gross_pay
    ')
    ->groupBy('employee_id')
    ->get();

foreach ($summary as $row) {
    echo "Employee ID {$row->employee_id}: {$row->days_worked} days, ₱" . number_format($row->total_gross_pay, 2) . "\n";
}
