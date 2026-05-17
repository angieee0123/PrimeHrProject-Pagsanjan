<?php

/**
 * Data Fix Script: Update Government Share Deductions
 * 
 * This script updates existing deduction types to correctly set
 * the deducted_from_employee flag for government/employer shares.
 * 
 * Run this script using:
 * php artisan tinker < database/scripts/fix_government_shares.php
 */

use App\Models\DeductionType;

echo "=== Updating Government Share Deductions ===\n\n";

// Government shares that should NOT be deducted from employee
$governmentShares = [
    'PhilHeath GS' => 'PhilHealth Government Share',
    'GSIS GS' => 'GSIS Government Share',
    'PAG-IBIG GS' => 'PAG-IBIG GOVERNMENT SHARE',
];

echo "Setting government shares to deducted_from_employee = false:\n";
foreach ($governmentShares as $code => $name) {
    $updated = DeductionType::where('code', $code)
        ->update(['deducted_from_employee' => false]);
    
    if ($updated) {
        echo "  ✓ {$name} ({$code})\n";
    } else {
        echo "  ✗ {$name} ({$code}) - Not found or already updated\n";
    }
}

echo "\n";

// Employee shares that SHOULD be deducted from employee
$employeeShares = [
    'PhilHeath PS' => 'PhilHealth Personal Share',
    'GSIS PS' => 'GSIS Personal Share',
    'GSIS-SI' => 'GSIS State Insurance',
    'PAG-IBIG PS' => 'PAG-IBIG PERSONAL SHARE',
];

echo "Verifying employee shares are set to deducted_from_employee = true:\n";
foreach ($employeeShares as $code => $name) {
    $updated = DeductionType::where('code', $code)
        ->update(['deducted_from_employee' => true]);
    
    if ($updated) {
        echo "  ✓ {$name} ({$code})\n";
    } else {
        echo "  ✗ {$name} ({$code}) - Not found or already updated\n";
    }
}

echo "\n";

// All loans should be deducted from employee
echo "Verifying all loans are set to deducted_from_employee = true:\n";
$loansUpdated = DeductionType::where('category', 'LOAN')
    ->update(['deducted_from_employee' => true]);
echo "  ✓ Updated {$loansUpdated} loan type(s)\n";

echo "\n=== Summary ===\n";
$summary = DeductionType::selectRaw('
    deducted_from_employee,
    COUNT(*) as count,
    GROUP_CONCAT(name SEPARATOR ", ") as names
')
->groupBy('deducted_from_employee')
->get();

foreach ($summary as $row) {
    $type = $row->deducted_from_employee ? 'Employee Shares (Deducted)' : 'Employer Shares (NOT Deducted)';
    echo "\n{$type}: {$row->count} type(s)\n";
    echo "  - " . $row->names . "\n";
}

echo "\n✅ Done!\n";
