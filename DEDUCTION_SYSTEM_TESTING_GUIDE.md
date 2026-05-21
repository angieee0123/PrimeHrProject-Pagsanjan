# Deduction System - Testing Guide

## Quick Test Commands

### 1. Install & Verify

```bash
# Navigate to Laravel project
cd primeHrMagdalenaLaravel

# Run migrations
php artisan migrate

# Seed default data
php artisan db:seed --class=DeductionTypesSeeder

# Verify installation
php artisan tinker
```

### 2. Verify Tables Created

```php
// In tinker
DB::table('deduction_types')->count();
// Expected: 8 (4 mandatory + 4 loan types)

DB::table('deduction_schedules')->count();
// Expected: 4 (GSIS, PhilHealth, Pag-IBIG, W-Tax)

// View deduction types
DB::table('deduction_types')->get();

// View schedules
DB::table('deduction_schedules')->get();
```

---

## Test Scenarios

### Test 1: Verify Default Deductions

```php
use App\Models\DeductionType;
use App\Models\DeductionSchedule;

// Check GSIS
$gsis = DeductionType::where('code', 'GSIS')->first();
echo "GSIS Rate: " . $gsis->percentage_rate . "%\n";
echo "Schedule: " . $gsis->schedules->first()->cutoff_schedule . "\n";
// Expected: 9%, 1ST_ONLY

// Check PhilHealth
$philhealth = DeductionType::where('code', 'PHILHEALTH')->first();
echo "PhilHealth Rate: " . $philhealth->percentage_rate . "%\n";
// Expected: 2.5%

// Check Pag-IBIG
$pagibig = DeductionType::where('code', 'PAGIBIG')->first();
echo "Pag-IBIG Rate: " . $pagibig->percentage_rate . "%\n";
echo "Max Amount: " . $pagibig->max_amount . "\n";
// Expected: 2%, 100.00
```

### Test 2: Add Employee Loan

```php
use App\Models\EmployeeDeduction;

// Add GSIS Salary Loan for employee ID 1
$loan = EmployeeDeduction::create([
    'employee_id' => 1, // Change to valid employee ID
    'deduction_type_id' => 5, // GSIS Salary Loan
    'total_amount' => 50000.00,
    'remaining_balance' => 50000.00,
    'installment_amount' => 2500.00,
    'start_date' => now(),
    'end_date' => now()->addMonths(20),
    'status' => 'ACTIVE',
    'remarks' => 'Test loan',
]);

echo "Loan created: ID " . $loan->id . "\n";
echo "Remaining balance: " . $loan->remaining_balance . "\n";
```

### Test 3: Calculate Deductions

```php
use App\Services\DeductionService;

$service = new DeductionService();
$employeeId = 1; // Change to valid employee ID
$basicSalary = 25000.00;

// Calculate 1st cutoff deductions
$total1st = $service->getTotalDeductions($employeeId, '1ST', $basicSalary);
echo "1st Cutoff Total: ₱" . number_format($total1st, 2) . "\n";
// Expected: ₱2,250 (GSIS) + ₱625 (PhilHealth) = ₱2,875

// Calculate 2nd cutoff deductions
$total2nd = $service->getTotalDeductions($employeeId, '2ND', $basicSalary);
echo "2nd Cutoff Total: ₱" . number_format($total2nd, 2) . "\n";
// Expected: ₱100 (Pag-IBIG)
```

### Test 4: Process Payroll Deductions

```php
use App\Services\DeductionService;

$service = new DeductionService();

// First, create employee deductions (mandatory)
$deductionTypes = [1, 2, 3]; // GSIS, PhilHealth, Pag-IBIG

foreach ($deductionTypes as $typeId) {
    EmployeeDeduction::create([
        'employee_id' => 1,
        'deduction_type_id' => $typeId,
        'start_date' => now(),
        'status' => 'ACTIVE',
    ]);
}

// Process 1st cutoff
$deductions = $service->processEmployeeDeductions(
    employeeId: 1,
    cutoffPeriod: '1ST',
    basicSalary: 25000.00,
    payrollId: null
);

echo "Processed " . count($deductions) . " deductions\n";

// View results
foreach ($deductions as $deduction) {
    echo "Type: " . $deduction->deductionType->name . "\n";
    echo "Amount: ₱" . number_format($deduction->amount_deducted, 2) . "\n";
}
```

### Test 5: Update Loan Balance

```php
use App\Models\EmployeeDeduction;

// Get the loan
$loan = EmployeeDeduction::where('employee_id', 1)
    ->where('deduction_type_id', 5)
    ->where('status', 'ACTIVE')
    ->first();

if ($loan) {
    echo "Before: ₱" . number_format($loan->remaining_balance, 2) . "\n";
    
    // Deduct installment
    $loan->remaining_balance -= $loan->installment_amount;
    
    if ($loan->remaining_balance <= 0) {
        $loan->status = 'COMPLETED';
        $loan->end_date = now();
        $loan->remaining_balance = 0;
    }
    
    $loan->save();
    
    echo "After: ₱" . number_format($loan->remaining_balance, 2) . "\n";
    echo "Status: " . $loan->status . "\n";
}
```

### Test 6: Change Deduction Schedule

```php
use App\Models\DeductionSchedule;

// Move GSIS from 1st to 2nd cutoff
$schedule = DeductionSchedule::where('deduction_type_id', 1)->first();
echo "Before: " . $schedule->cutoff_schedule . "\n";

$schedule->cutoff_schedule = '2ND_ONLY';
$schedule->save();

echo "After: " . $schedule->cutoff_schedule . "\n";

// Change back
$schedule->cutoff_schedule = '1ST_ONLY';
$schedule->save();
```

### Test 7: Query Payroll Deductions

```php
use App\Models\PayrollDeduction;

// Get all deductions for an employee
$deductions = PayrollDeduction::where('employee_id', 1)
    ->with('deductionType')
    ->orderBy('deduction_date', 'desc')
    ->get();

foreach ($deductions as $deduction) {
    echo $deduction->deduction_date->format('Y-m-d') . " | ";
    echo $deduction->cutoff_period . " | ";
    echo $deduction->deductionType->name . " | ";
    echo "₱" . number_format($deduction->amount_deducted, 2) . "\n";
}
```

---

## Expected Results

### For ₱25,000 Basic Salary

#### 1st Cutoff
```
GSIS:        ₱2,250.00 (9% of 25,000)
PhilHealth:    ₱625.00 (2.5% of 25,000)
W-Tax:         ₱0.00 (not implemented yet)
-----------
Total:       ₱2,875.00
Net Pay:     ₱9,625.00 (12,500 - 2,875)
```

#### 2nd Cutoff
```
Pag-IBIG:    ₱100.00 (2% of 25,000, capped at 100)
W-Tax:         ₱0.00 (not implemented yet)
-----------
Total:         ₱100.00
Net Pay:    ₱12,400.00 (12,500 - 100)
```

---

## Validation Checks

### ✓ Database Structure
```sql
-- Check tables exist
SHOW TABLES LIKE '%deduction%';

-- Check foreign keys
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'your_database_name'
AND REFERENCED_TABLE_NAME IS NOT NULL
AND TABLE_NAME LIKE '%deduction%';
```

### ✓ Seeded Data
```sql
-- Check deduction types
SELECT code, name, category, computation_type 
FROM deduction_types;

-- Check schedules
SELECT dt.code, ds.cutoff_schedule, ds.priority_order
FROM deduction_schedules ds
JOIN deduction_types dt ON ds.deduction_type_id = dt.id;
```

### ✓ Relationships
```php
// Test relationships
$deductionType = DeductionType::with('schedules')->first();
echo "Type: " . $deductionType->name . "\n";
echo "Schedule: " . $deductionType->schedules->first()->cutoff_schedule . "\n";

$employeeDeduction = EmployeeDeduction::with(['employee', 'deductionType'])->first();
if ($employeeDeduction) {
    echo "Employee: " . $employeeDeduction->employee->first_name . "\n";
    echo "Deduction: " . $employeeDeduction->deductionType->name . "\n";
}
```

---

## Common Issues & Solutions

### Issue 1: Migration Fails
```bash
# Check if tables already exist
php artisan tinker
>>> Schema::hasTable('deduction_types')

# If true, drop and recreate
php artisan migrate:rollback --step=5
php artisan migrate
```

### Issue 2: Foreign Key Constraint Fails
```bash
# Make sure employees table exists
php artisan tinker
>>> Schema::hasTable('employees')

# Check employee ID exists before creating deductions
>>> DB::table('employees')->where('id', 1)->exists()
```

### Issue 3: Seeder Fails
```bash
# Clear and reseed
php artisan db:seed --class=DeductionTypesSeeder

# If duplicate error, truncate first
php artisan tinker
>>> DB::table('deduction_schedules')->truncate();
>>> DB::table('deduction_types')->truncate();
>>> exit

php artisan db:seed --class=DeductionTypesSeeder
```

---

## Performance Testing

### Test Large Dataset
```php
// Create deductions for 100 employees
for ($i = 1; $i <= 100; $i++) {
    foreach ([1, 2, 3] as $typeId) {
        EmployeeDeduction::create([
            'employee_id' => $i,
            'deduction_type_id' => $typeId,
            'start_date' => now(),
            'status' => 'ACTIVE',
        ]);
    }
}

// Test processing time
$start = microtime(true);

$service = new DeductionService();
for ($i = 1; $i <= 100; $i++) {
    $service->getTotalDeductions($i, '1ST', 25000.00);
}

$end = microtime(true);
echo "Time: " . ($end - $start) . " seconds\n";
```

---

## Integration Testing

### Test with Existing Payroll
```php
// Assuming you have a payroll computation
$employee = Employee::find(1);
$basicSalary = $employee->employmentDetail->designation->monthly_rate ?? 25000;

$service = new DeductionService();

// Get deductions for both cutoffs
$deductions1st = $service->getTotalDeductions($employee->id, '1ST', $basicSalary);
$deductions2nd = $service->getTotalDeductions($employee->id, '2ND', $basicSalary);

// Calculate net pay
$grossPerCutoff = $basicSalary / 2;
$netPay1st = $grossPerCutoff - $deductions1st;
$netPay2nd = $grossPerCutoff - $deductions2nd;

echo "1st Cutoff Net: ₱" . number_format($netPay1st, 2) . "\n";
echo "2nd Cutoff Net: ₱" . number_format($netPay2nd, 2) . "\n";
```

---

## Success Criteria

✅ All migrations run successfully
✅ Seeder populates 8 deduction types
✅ Seeder creates 4 deduction schedules
✅ Can create employee deductions
✅ Can calculate deductions correctly
✅ Can process payroll deductions
✅ Loan balances update automatically
✅ All relationships work correctly
✅ No foreign key errors

---

## Next: Build Admin Interface

Once testing is complete, proceed to build:
1. Deduction type management UI
2. Employee deduction assignment UI
3. Loan application workflow
4. Payroll processing interface
5. Deduction reports
