# Unified Deduction System - LGU Philippines

## Overview
Flexible deduction management system for LGU permanent employees in the Philippines, supporting mandatory contributions (GSIS, PhilHealth, Pag-IBIG, Withholding Tax) and loans.

---

## Database Schema

### 1. deduction_types
Master list of all deduction types (mandatory, loans, others)

**Columns:**
- `id` - Primary key
- `code` - Unique code (e.g., 'GSIS', 'PHILHEALTH', 'LOAN_GSIS_SALARY')
- `name` - Display name
- `category` - MANDATORY | LOAN | OTHER
- `computation_type` - PERCENTAGE | FIXED | CUSTOM
- `percentage_rate` - Rate for percentage-based deductions
- `base_salary_type` - BASIC | GROSS | CUSTOM
- `max_amount` - Maximum deduction amount (e.g., ₱100 for Pag-IBIG)
- `is_active` - Active status

### 2. deduction_schedules
Defines when deductions are applied (1st cutoff, 2nd cutoff, or both)

**Columns:**
- `id` - Primary key
- `deduction_type_id` - Foreign key to deduction_types
- `cutoff_schedule` - 1ST_ONLY | 2ND_ONLY | BOTH_SPLIT | BOTH_FULL
- `priority_order` - Deduction sequence
- `is_active` - Active status
- `effective_date` - When this schedule becomes effective

### 3. employee_deductions
Employee-specific deduction records

**Columns:**
- `id` - Primary key
- `employee_id` - Foreign key to employees
- `deduction_type_id` - Foreign key to deduction_types
- `amount` - Fixed amount (for loans or custom deductions)
- `start_date` - When deduction starts
- `end_date` - When deduction ends (NULL for mandatory)
- `remaining_balance` - For loan tracking
- `total_amount` - Original loan amount
- `installment_amount` - Monthly payment for loans
- `status` - ACTIVE | COMPLETED | SUSPENDED
- `remarks` - Additional notes

### 4. payroll_deductions
Actual deductions per payroll run (audit trail)

**Columns:**
- `id` - Primary key
- `payroll_id` - Reference to payroll run
- `employee_id` - Foreign key to employees
- `employee_deduction_id` - Foreign key to employee_deductions
- `deduction_type_id` - Foreign key to deduction_types
- `cutoff_period` - 1ST | 2ND
- `amount_deducted` - Actual amount deducted
- `computation_details` - JSON with calculation details
- `deduction_date` - Date of deduction

### 5. loan_types (Optional)
Detailed loan type configurations

**Columns:**
- `id` - Primary key
- `code` - Unique code
- `name` - Loan type name
- `deduction_type_id` - Links to deduction_types
- `max_loanable_amount` - Maximum loan amount
- `interest_rate` - Interest rate
- `max_terms_months` - Maximum payment terms
- `is_active` - Active status

---

## Default Configuration

### Mandatory Deductions (Seeded)

1. **GSIS** - 9% of basic salary, 1st cutoff only
2. **PhilHealth** - 2.5% of basic salary, 1st cutoff only
3. **Pag-IBIG** - 2% of basic salary (max ₱100), 2nd cutoff only
4. **Withholding Tax** - Custom computation, split 50-50 both cutoffs

### Loan Types (Seeded)

- GSIS Salary Loan
- GSIS Policy Loan
- Pag-IBIG Multi-Purpose Loan
- Pag-IBIG Housing Loan

---

## Installation

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Default Data
```bash
php artisan db:seed --class=DeductionTypesSeeder
```

---

## Usage Examples

### Add Mandatory Deduction to Employee
```php
// Automatically created for all permanent employees
// Based on deduction_types and deduction_schedules
```

### Add Loan to Employee
```php
EmployeeDeduction::create([
    'employee_id' => 1,
    'deduction_type_id' => 5, // GSIS Salary Loan
    'total_amount' => 50000.00,
    'remaining_balance' => 50000.00,
    'installment_amount' => 2500.00,
    'start_date' => '2026-06-01',
    'end_date' => '2028-05-31', // 24 months
    'status' => 'ACTIVE',
]);
```

### Process Payroll Deductions
```php
// Get active deductions for employee
$deductions = EmployeeDeduction::where('employee_id', $employeeId)
    ->where('status', 'ACTIVE')
    ->with('deductionType.schedules')
    ->get();

// Filter by cutoff period
$cutoffPeriod = '1ST'; // or '2ND'

foreach ($deductions as $deduction) {
    $schedule = $deduction->deductionType->schedules()
        ->where('is_active', true)
        ->first();
    
    if ($schedule->cutoff_schedule === $cutoffPeriod . '_ONLY' || 
        $schedule->cutoff_schedule === 'BOTH_SPLIT' ||
        $schedule->cutoff_schedule === 'BOTH_FULL') {
        
        // Calculate amount
        $amount = calculateDeduction($deduction, $cutoffPeriod);
        
        // Record deduction
        PayrollDeduction::create([
            'employee_id' => $employeeId,
            'employee_deduction_id' => $deduction->id,
            'deduction_type_id' => $deduction->deduction_type_id,
            'cutoff_period' => $cutoffPeriod,
            'amount_deducted' => $amount,
            'deduction_date' => now(),
        ]);
    }
}
```

### Update Loan Balance
```php
$employeeDeduction = EmployeeDeduction::find($id);
$employeeDeduction->remaining_balance -= $installmentAmount;

if ($employeeDeduction->remaining_balance <= 0) {
    $employeeDeduction->status = 'COMPLETED';
    $employeeDeduction->end_date = now();
}

$employeeDeduction->save();
```

---

## Flexibility Features

✓ **Configurable Schedules** - Change when deductions are applied per type
✓ **Priority Order** - Control deduction sequence
✓ **Employee Overrides** - Custom amounts per employee
✓ **Loan Tracking** - Built-in balance and installment management
✓ **Audit Trail** - Complete history in payroll_deductions
✓ **Easy Extension** - Add new deduction types without schema changes

---

## Models

- `DeductionType` - Master deduction types
- `DeductionSchedule` - Cutoff schedules
- `EmployeeDeduction` - Employee-specific deductions
- `PayrollDeduction` - Payroll transaction records
- `LoanType` - Loan configurations

---

## Next Steps

1. Create admin interface to manage deduction types and schedules
2. Build payroll computation service
3. Add withholding tax computation logic
4. Create employee loan application workflow
5. Generate payroll reports with deduction breakdown
