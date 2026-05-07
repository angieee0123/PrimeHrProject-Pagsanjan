# Salary Computation System Guide

## Overview
The PrimeHRIS system automatically computes daily salaries based on attendance records.

## Database Tables

### 1. employees
- Stores employee basic information
- Key fields: `id`, `employee_id`, `first_name`, `middle_name`, `last_name`

### 2. attendance
- Stores raw biometric/manual attendance records
- Key fields: `id`, `employee_id`, `attendance_date`, `am_in`, `am_out`, `pm_in`, `pm_out`

### 3. accredited_hours_log
- Stores computed attendance hours after corrections
- Key fields:
  - `id`, `employee_id`, `attendance_id`
  - `late_minutes` - minutes late for work
  - `undertime_minutes` - minutes left early
  - `ot_minutes` - overtime minutes worked
  - `total_accredited_minutes` - total work minutes credited

### 4. daily_salary_computations
- Stores computed salary amounts for each work day
- Key fields:
  - `id`, `employee_id`, `accredited_hours_log_id`, `work_date`
  - `monthly_rate`, `daily_rate`, `hourly_rate`
  - `daily_basic_pay` - pay for accredited work hours
  - `ot_pay` - overtime pay earned
  - `late_deduction` - deduction for late arrival
  - `undertime_deduction` - deduction for leaving early
  - `daily_gross_pay` - net pay for the day

## Computation Formulas

### Rate Calculations
```
daily_rate = monthly_rate ÷ 22 working days
hourly_rate = daily_rate ÷ 8 hours
```

### Pay Calculations
```
daily_basic_pay = (accredited_minutes ÷ 480) × daily_rate
ot_pay = (ot_minutes ÷ 60) × hourly_rate × 1.25
```

### Deduction Calculations
```
late_deduction = (late_minutes ÷ 60) × hourly_rate
undertime_deduction = (undertime_minutes ÷ 60) × hourly_rate
```

### Net Pay
```
daily_gross_pay = daily_basic_pay + ot_pay - late_deduction - undertime_deduction
```

## Example Queries

### Find employee by name
```sql
SELECT * FROM employees 
WHERE CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) LIKE '%Jeremy%';
```

### Get salary computations for an employee
```sql
SELECT e.first_name, e.last_name, d.work_date, 
       d.late_deduction, d.undertime_deduction, d.ot_pay, d.daily_gross_pay
FROM daily_salary_computations d
JOIN employees e ON d.employee_id = e.id
WHERE e.first_name = 'Jeremy';
```

### Get detailed breakdown with attendance
```sql
SELECT e.first_name, e.last_name, d.work_date,
       a.late_minutes, a.undertime_minutes, a.ot_minutes,
       d.hourly_rate, d.late_deduction, d.undertime_deduction, d.ot_pay
FROM daily_salary_computations d
JOIN employees e ON d.employee_id = e.id
JOIN accredited_hours_log a ON d.accredited_hours_log_id = a.id
WHERE e.first_name = 'Jeremy';
```

## Why Deductions Occur

### Late Deduction
- Occurs when employee arrives late (after scheduled time)
- Calculated from `late_minutes` in accredited_hours_log
- Example: 60 minutes late × PHP 689/hour = PHP 689 late deduction

### Undertime Deduction
- Occurs when employee leaves before scheduled end time
- Calculated from `undertime_minutes` in accredited_hours_log
- Example: 120 minutes undertime × PHP 689/hour = PHP 1,378 undertime deduction

### OT Pay
- Earned when employee works beyond scheduled hours
- Paid at 1.25× hourly rate (25% premium)
- Example: 60 minutes OT × PHP 689/hour × 1.25 = PHP 861.25 OT pay

## Data Flow
1. Employee scans biometric → `attendance` table
2. Admin corrects/verifies → `accredited_hours_log` table (computes late/undertime/OT minutes)
3. System auto-computes salary → `daily_salary_computations` table (converts minutes to peso amounts)
4. Payroll aggregates daily computations → `salary_computations` table (period totals)

## Important Notes
- All monetary values are in Philippine Peso (PHP)
- Standard work day = 480 minutes (8 hours)
- Standard work month = 22 days
- Deductions are based on actual hourly rate
- OT has 25% premium (1.25× multiplier)
