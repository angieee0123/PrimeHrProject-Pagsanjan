# Monthly Salary Base for Deductions

## Overview
The deduction system now supports calculating deductions based on the employee's **Monthly Salary** from their designation, in addition to Basic and Gross salary options.

## Feature Details

### Base Salary Options
When creating or editing a deduction type with **Percentage** computation type, you can now choose from:

1. **BASIC** - Calculate percentage from the basic pay earned in the period
2. **GROSS** - Calculate percentage from gross pay (basic + overtime)
3. **MONTHLY** - Calculate percentage from the employee's monthly salary rate (from designation)
4. **CUSTOM** - For custom calculation logic

### Use Case Example
**PhilHealth Contribution (2.5% of Monthly Salary)**

1. Go to **Deductions** → **Deduction Types** tab
2. Click **Add Deduction Type**
3. Fill in the form:
   - **Code**: `PHILHEALTH`
   - **Name**: `PhilHealth Contribution`
   - **Category**: `MANDATORY`
   - **Computation Type**: `PERCENTAGE`
   - **Rate (%)**: `2.5`
   - **Base Salary**: `Monthly Salary`
   - **Max Amount**: `4,800.00` (optional cap)
   - **Status**: `Active`

### How It Works

#### For Monthly/Employee View:
- If an employee has a monthly salary of ₱30,000
- PhilHealth (2.5% of monthly salary) = ₱30,000 × 2.5% = ₱750.00

#### For Daily View:
- Monthly salary is prorated to daily (divided by 22 working days)
- Daily base = ₱30,000 ÷ 22 = ₱1,363.64
- Daily PhilHealth = ₱1,363.64 × 2.5% = ₱34.09

### Benefits
1. **Accurate Calculations** - Deductions based on official monthly salary rate
2. **Consistent** - Same rate regardless of actual days worked or overtime
3. **Compliant** - Follows government contribution rules (PhilHealth, GSIS, etc.)
4. **Flexible** - Can still use Basic or Gross for other deduction types

### Database Changes
- Migration added: `2026_06_09_000001_add_monthly_to_base_salary_type.php`
- Enum updated: `base_salary_type` now includes `'MONTHLY'` option

### Important Notes
- Monthly salary is retrieved from `designations.monthly_rate`
- If employee has no designation or monthly_rate is null, calculation defaults to 0
- For daily view, monthly salary is divided by 22 (standard working days)
- This option works best for MANDATORY deductions (SSS, GSIS, PhilHealth, Pag-IBIG)

## Migration Instructions
Run the following command to apply the database changes:
```bash
php artisan migrate
```

This will add the MONTHLY option to the base_salary_type enum in the deduction_types table.
