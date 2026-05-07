<?php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Diagnosing Employee ID 8 salary issue...\n\n";

// Check employee
$employee = DB::table('employees')->where('id', 8)->first();
if (!$employee) {
    echo "✗ Employee ID 8 not found\n";
    exit(1);
}
echo "✓ Employee: {$employee->first_name} {$employee->last_name}\n\n";

// Check employment details
$employment = DB::table('employment_details')->where('employee_id', 8)->first();
if (!$employment) {
    echo "✗ No employment details found\n";
    exit(1);
}
echo "✓ Employment Details:\n";
echo "  - Department ID: {$employment->department_id}\n";
echo "  - Designation ID: {$employment->designation_id}\n\n";

// Check designation
if ($employment->designation_id) {
    $designation = DB::table('designations')->where('id', $employment->designation_id)->first();
    if ($designation) {
        echo "✓ Designation: {$designation->title}\n";
        echo "  - Monthly Rate: ₱" . number_format($designation->monthly_rate, 2) . "\n";
        echo "  - Department ID: {$designation->department_id}\n\n";
        
        if ($designation->monthly_rate == 0 || $designation->monthly_rate === null) {
            echo "⚠ WARNING: Monthly rate is 0 or NULL!\n";
            echo "  This is why daily_gross_pay is 0.\n\n";
            
            // Show what the rate should be
            echo "Checking designation table for this position...\n";
            $dept = DB::table('departments')->where('id', $designation->department_id)->first();
            echo "Department: {$dept->name}\n";
        }
    } else {
        echo "✗ Designation ID {$employment->designation_id} not found\n";
    }
} else {
    echo "✗ No designation_id in employment_details\n";
}

// Check the actual query used in computation
echo "\n--- Testing Query ---\n";
$monthlyRate = DB::table('employees')
    ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
    ->join('designations', 'employment_details.designation_id', '=', 'designations.id')
    ->where('employees.id', 8)
    ->select('designations.monthly_rate', 'designations.title')
    ->first();

if ($monthlyRate) {
    echo "Query Result:\n";
    echo "  - Title: {$monthlyRate->title}\n";
    echo "  - Monthly Rate: ₱" . number_format($monthlyRate->monthly_rate, 2) . "\n";
} else {
    echo "✗ Query returned no results\n";
}

// Show current daily_salary_computations
echo "\n--- Current Daily Salary Computations ---\n";
$dailyComps = DB::table('daily_salary_computations')
    ->where('employee_id', 8)
    ->get();

foreach ($dailyComps as $comp) {
    echo "Date: {$comp->work_date}\n";
    echo "  Monthly Rate: ₱{$comp->monthly_rate}\n";
    echo "  Daily Rate: ₱{$comp->daily_rate}\n";
    echo "  Accredited Minutes: {$comp->accredited_minutes}\n";
    echo "  Daily Gross Pay: ₱{$comp->daily_gross_pay}\n\n";
}
