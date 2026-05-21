# Deduction System - Quick Command Reference

## 🎯 Most Common Operations

### View All Deduction Types
```php
php artisan tinker

use App\Models\DeductionType;
DeductionType::all(['code', 'name', 'category'])->toArray();
```

### View Deduction Schedules
```php
use App\Models\DeductionSchedule;
DeductionSchedule::with('deductionType:id,code,name')
    ->get(['deduction_type_id', 'cutoff_schedule', 'priority_order'])
    ->toArray();
```

### Add Mandatory Deductions to Employee
```php
use App\Models\EmployeeDeduction;

$employeeId = 1; // Change this

// Add GSIS, PhilHealth, Pag-IBIG
foreach ([1, 2, 3] as $typeId) {
    EmployeeDeduction::create([
        'employee_id' => $employeeId,
        'deduction_type_id' => $typeId,
        'start_date' => now(),
        'status' => 'ACTIVE',
    ]);
}
```

### Add Employee Loan
```php
use App\Models\EmployeeDeduction;

EmployeeDeduction::create([
    'employee_id' => 1,
    'deduction_type_id' => 5, // GSIS Salary Loan
    'total_amount' => 50000.00,
    'remaining_balance' => 50000.00,
    'installment_amount' => 2500.00,
    'start_date' => now(),
    'end_date' => now()->addMonths(20),
    'status' => 'ACTIVE',
]);
```

### Calculate Total Deductions
```php
use App\Services\DeductionService;

$service = new DeductionService();
$total = $service->getTotalDeductions(
    employeeId: 1,
    cutoffPeriod: '1ST', // or '2ND'
    basicSalary: 25000.00
);

echo "Total: ₱" . number_format($total, 2);
```

### Process Payroll Deductions
```php
use App\Services\DeductionService;

$service = new DeductionService();
$deductions = $service->processEmployeeDeductions(
    employeeId: 1,
    cutoffPeriod: '1ST',
    basicSalary: 25000.00,
    payrollId: 123
);

echo "Processed: " . count($deductions) . " deductions";
```

### View Employee's Active Deductions
```php
use App\Models\EmployeeDeduction;

EmployeeDeduction::where('employee_id', 1)
    ->where('status', 'ACTIVE')
    ->with('deductionType:id,code,name')
    ->get()
    ->toArray();
```

### View Deduction History
```php
use App\Models\PayrollDeduction;

PayrollDeduction::where('employee_id', 1)
    ->with('deductionType:id,code,name')
    ->orderBy('deduction_date', 'desc')
    ->limit(10)
    ->get(['deduction_date', 'cutoff_period', 'deduction_type_id', 'amount_deducted'])
    ->toArray();
```

### Change Deduction Schedule
```php
use App\Models\DeductionSchedule;

// Move GSIS to 2nd cutoff
DeductionSchedule::where('deduction_type_id', 1)
    ->update(['cutoff_schedule' => '2ND_ONLY']);
```

### Check Loan Balance
```php
use App\Models\EmployeeDeduction;

$loan = EmployeeDeduction::where('employee_id', 1)
    ->where('deduction_type_id', 5)
    ->where('status', 'ACTIVE')
    ->first();

if ($loan) {
    echo "Remaining: ₱" . number_format($loan->remaining_balance, 2);
    echo "\nInstallment: ₱" . number_format($loan->installment_amount, 2);
}
```

---

## 📊 Sample Calculation (₱25,000 Basic Salary)

### 1st Cutoff
```
Gross:       ₱12,500.00
GSIS:        -₱2,250.00 (9%)
PhilHealth:    -₱625.00 (2.5%)
Net:          ₱9,625.00
```

### 2nd Cutoff
```
Gross:       ₱12,500.00
Pag-IBIG:      -₱100.00 (max)
Net:         ₱12,400.00
```

---

## 🗂️ Table Names

- `deduction_types` - Master list
- `deduction_schedules` - Cutoff configs
- `employee_deductions` - Employee records
- `deduction_transactions` - Transaction history
- `loan_types` - Loan configs

---

## 🔑 Key Enums

### cutoff_schedule
- `1ST_ONLY` - 1st cutoff only
- `2ND_ONLY` - 2nd cutoff only
- `BOTH_SPLIT` - Split 50-50
- `BOTH_FULL` - Full both times

### cutoff_period
- `1ST` - Days 1-15
- `2ND` - Days 16-31

### status
- `ACTIVE` - Currently deducting
- `COMPLETED` - Finished
- `SUSPENDED` - Temporarily stopped

### category
- `MANDATORY` - Required deductions
- `LOAN` - Loan payments
- `OTHER` - Optional deductions

---

## 📁 Files Location

### Migrations
`database/migrations/2026_06_08_*`

### Models
`app/Models/DeductionType.php`
`app/Models/DeductionSchedule.php`
`app/Models/EmployeeDeduction.php`
`app/Models/PayrollDeduction.php`
`app/Models/LoanType.php`

### Service
`app/Services/DeductionService.php`

### Seeder
`database/seeders/DeductionTypesSeeder.php`

---

## 🎯 Default Deduction IDs

1. GSIS (9%, 1st cutoff)
2. PhilHealth (2.5%, 1st cutoff)
3. Pag-IBIG (2% max ₱100, 2nd cutoff)
4. Withholding Tax (custom, both split)
5. GSIS Salary Loan
6. GSIS Policy Loan
7. Pag-IBIG MPL
8. Pag-IBIG Housing Loan

---

## ✅ Quick Verification

```bash
php artisan tinker --execute="echo 'Types: ' . DB::table('deduction_types')->count() . PHP_EOL . 'Schedules: ' . DB::table('deduction_schedules')->count();"
```

Expected: Types: 8, Schedules: 4

---

**Keep this file handy for quick reference!**
