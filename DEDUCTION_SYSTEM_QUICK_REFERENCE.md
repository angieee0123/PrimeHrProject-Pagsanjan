# Unified Deduction System - Quick Reference

## Installation Steps

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed default deductions
php artisan db:seed --class=DeductionTypesSeeder
```

---

## Default Deduction Schedule

| Deduction | Rate | Base | Max | Cutoff |
|-----------|------|------|-----|--------|
| GSIS | 9% | Basic Salary | - | 1st Only |
| PhilHealth | 2.5% | Basic Salary | - | 1st Only |
| Pag-IBIG | 2% | Basic Salary | ₱100 | 2nd Only |
| Withholding Tax | Custom | - | - | Both (Split) |

---

## Common Operations

### 1. Add Employee Loan
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
    'remarks' => 'Approved loan',
]);
```

### 2. Process Payroll Deductions
```php
use App\Services\DeductionService;

$service = new DeductionService();
$basicSalary = 25000.00;
$cutoffPeriod = '1ST'; // or '2ND'

// Process all deductions
$deductions = $service->processEmployeeDeductions(
    employeeId: 1,
    cutoffPeriod: $cutoffPeriod,
    basicSalary: $basicSalary,
    payrollId: 123
);

// Get total only
$total = $service->getTotalDeductions(1, $cutoffPeriod, $basicSalary);
```

### 3. Change Deduction Schedule
```php
use App\Models\DeductionSchedule;

// Change GSIS to 2nd cutoff
DeductionSchedule::where('deduction_type_id', 1)
    ->update(['cutoff_schedule' => '2ND_ONLY']);
```

### 4. Add New Deduction Type
```php
use App\Models\DeductionType;

DeductionType::create([
    'code' => 'UNION_DUES',
    'name' => 'Union Dues',
    'category' => 'OTHER',
    'computation_type' => 'FIXED',
    'is_active' => true,
]);
```

---

## Cutoff Schedule Options

- `1ST_ONLY` - Deduct only on 1st cutoff (days 1-15)
- `2ND_ONLY` - Deduct only on 2nd cutoff (days 16-31)
- `BOTH_SPLIT` - Split monthly amount 50-50 across both cutoffs
- `BOTH_FULL` - Deduct full amount on both cutoffs (rare)

---

## Computation Types

- `PERCENTAGE` - Based on percentage of salary
- `FIXED` - Fixed amount (loans, union dues)
- `CUSTOM` - Custom logic (withholding tax)

---

## Database Tables

1. **deduction_types** - Master list of deductions
2. **deduction_schedules** - When to deduct
3. **employee_deductions** - Employee-specific records
4. **payroll_deductions** - Transaction history
5. **loan_types** - Loan configurations (optional)

---

## Models

- `DeductionType`
- `DeductionSchedule`
- `EmployeeDeduction`
- `PayrollDeduction`
- `LoanType`

---

## Service Class

`App\Services\DeductionService` - Handles all deduction computations

**Methods:**
- `calculateDeduction()` - Calculate single deduction
- `processEmployeeDeductions()` - Process all deductions
- `getTotalDeductions()` - Get total amount
- `updateLoanBalance()` - Update loan after payment

---

## Example: Monthly Deductions for ₱25,000 Basic Salary

### 1st Cutoff (Days 1-15)
- GSIS: ₱2,250.00 (9% of 25,000)
- PhilHealth: ₱625.00 (2.5% of 25,000)
- Withholding Tax: ₱500.00 (example, 50% of monthly)
- **Total: ₱3,375.00**

### 2nd Cutoff (Days 16-31)
- Pag-IBIG: ₱100.00 (2% of 25,000, capped at 100)
- Withholding Tax: ₱500.00 (remaining 50%)
- **Total: ₱600.00**

### Take-Home Pay
- 1st Cutoff: ₱12,500 - ₱3,375 = **₱9,125.00**
- 2nd Cutoff: ₱12,500 - ₱600 = **₱11,900.00**

---

## Files Created

### Migrations
- `2026_06_08_000001_create_deduction_types_table.php`
- `2026_06_08_000002_create_deduction_schedules_table.php`
- `2026_06_08_000003_create_employee_deductions_table.php`
- `2026_06_08_000004_create_payroll_deductions_table.php`
- `2026_06_08_000005_create_loan_types_table.php`

### Models
- `app/Models/DeductionType.php`
- `app/Models/DeductionSchedule.php`
- `app/Models/EmployeeDeduction.php`
- `app/Models/PayrollDeduction.php`
- `app/Models/LoanType.php`

### Seeders
- `database/seeders/DeductionTypesSeeder.php`

### Services
- `app/Services/DeductionService.php`

### Documentation
- `UNIFIED_DEDUCTION_SYSTEM.md`
- `DEDUCTION_SYSTEM_QUICK_REFERENCE.md` (this file)
