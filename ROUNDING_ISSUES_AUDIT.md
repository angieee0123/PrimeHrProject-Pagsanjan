# ROUNDING ISSUES AUDIT & FIX PLAN

## Critical Issue
The system is rounding days, hours, and minutes which causes **loss of precision** in HR records. This is unacceptable for:
- Payroll calculations
- Leave credit tracking
- Attendance records
- Salary computations

## All Rounding Instances Found

### 1. AttendanceController.php (5 instances)

#### Line 137: Attendance Rate Percentage
```php
// BEFORE (WRONG)
$rate = $totalDays > 0 ? round(($present / $totalDays) * 100) : 0;

// AFTER (CORRECT) - Keep 2 decimals for percentage
$rate = $totalDays > 0 ? number_format(($present / $totalDays) * 100, 2, '.', '') : 0;
```
**Impact:** Low (display only, not used in calculations)

#### Line 156: Overtime Hours
```php
// BEFORE (WRONG)
'overtime' => round($overtime, 1),

// AFTER (CORRECT) - Keep exact hours with 4 decimals
'overtime' => number_format($overtime, 4, '.', ''),
```
**Impact:** HIGH - Affects overtime pay calculations

#### Lines 362, 366: Display Minutes
```php
// BEFORE (WRONG)
return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ' . round($mins) . ' min';
return round($mins) . ' min';

// AFTER (CORRECT) - Show exact minutes
return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ' . $mins . ' min';
return $mins . ' min';
```
**Impact:** MEDIUM - Display only, but misleading to users

#### Line 615: Total Hours Display
```php
// BEFORE (WRONG)
$totalHours = $totalHoursMinutes ? round($totalHoursMinutes / 60, 1) : 0;

// AFTER (CORRECT) - Keep exact hours
$totalHours = $totalHoursMinutes ? number_format($totalHoursMinutes / 60, 4, '.', '') : 0;
```
**Impact:** MEDIUM - Display in DTR

### 2. DailySalaryComputation.php (9 instances)

#### Lines 142-149: All Salary Calculations
```php
// BEFORE (WRONG) - Rounds to 2 decimals
'monthly_rate' => round($monthlyRate, 2),
'daily_rate' => round($dailyRate, 2),
'hourly_rate' => round($hourlyRate, 2),
'daily_basic_pay' => round($dailyBasicPay, 2),
'ot_pay' => round($otPay, 2),
'late_deduction' => round($lateDeduction, 2),
'undertime_deduction' => round($undertimeDeduction, 2),
'daily_gross_pay' => round($dailyGrossPay, 2),

// AFTER (CORRECT) - Keep 4 decimals for precision
'monthly_rate' => number_format($monthlyRate, 4, '.', ''),
'daily_rate' => number_format($dailyRate, 4, '.', ''),
'hourly_rate' => number_format($hourlyRate, 4, '.', ''),
'daily_basic_pay' => number_format($dailyBasicPay, 4, '.', ''),
'ot_pay' => number_format($otPay, 4, '.', ''),
'late_deduction' => number_format($lateDeduction, 4, '.', ''),
'undertime_deduction' => number_format($undertimeDeduction, 4, '.', ''),
'daily_gross_pay' => number_format($dailyGrossPay, 4, '.', ''),
```
**Impact:** CRITICAL - Affects all payroll calculations

### 3. LateDeductionService.php (2 instances)

#### Line 21: Late Days Calculation
```php
// BEFORE (WRONG)
$lateDays = round($lateMinutes / 480, 4);

// AFTER (CORRECT) - No rounding, keep exact calculation
$lateDays = $lateMinutes / 480;
```
**Impact:** CRITICAL - Affects leave deductions

#### Line 80: Display in Remarks
```php
// BEFORE (WRONG)
'remarks' => "Late deduction: {$log->late_minutes} minutes (" . round($amount, 4) . " days) from attendance on ..."

// AFTER (CORRECT) - Show exact amount
'remarks' => "Late deduction: {$log->late_minutes} minutes (" . number_format($amount, 6, '.', '') . " days) from attendance on ..."
```
**Impact:** MEDIUM - Audit trail accuracy

### 4. Blade Templates (15 instances)

#### Leave Credits Display
```blade
{{-- BEFORE (WRONG) --}}
{{ number_format($totalCredits, 1) }} days
{{ number_format($used, 1) }}
{{ number_format($pending, 1) }}
{{ number_format($available, 1) }}

{{-- AFTER (CORRECT) - Show exact values --}}
{{ number_format($totalCredits, 4) }} days
{{ number_format($used, 4) }}
{{ number_format($pending, 4) }}
{{ number_format($available, 4) }}
```
**Impact:** HIGH - Users see incorrect balances

#### Transaction History
```blade
{{-- BEFORE (WRONG) --}}
{{ number_format($transaction->amount, 2) }} days
{{ number_format($transaction->balance_before, 2) }}
{{ number_format($transaction->balance_after, 2) }}

{{-- AFTER (CORRECT) --}}
{{ number_format($transaction->amount, 4) }} days
{{ number_format($transaction->balance_before, 4) }}
{{ number_format($transaction->balance_after, 4) }}
```
**Impact:** HIGH - Audit trail accuracy

#### Leave Requests
```blade
{{-- BEFORE (WRONG) --}}
{{ number_format($application->number_of_days, 0) }}

{{-- AFTER (CORRECT) --}}
{{ number_format($application->number_of_days, 4) }}
```
**Impact:** HIGH - Shows wrong leave duration

## Precision Standards

### Recommended Decimal Places

| Data Type | Decimals | Reason |
|-----------|----------|--------|
| **Leave Days** | 4 | Handles 0.0625 days (30 min) |
| **Hours** | 4 | Handles 0.0167 hours (1 min) |
| **Minutes** | 0 | Already integer |
| **Money (PHP)** | 4 | Handles centavos precisely |
| **Percentages** | 2 | Standard display |

### Why 4 Decimals?

**Example: 30-minute late**
```
Minutes: 30
Hours: 30/60 = 0.5
Days: 30/480 = 0.0625

With 2 decimals: 0.06 days (WRONG - loses 0.0025 days)
With 4 decimals: 0.0625 days (CORRECT)

Over 1 year (260 working days):
- Loss with 2 decimals: 0.0025 × 260 = 0.65 days
- That's 5.2 hours of unpaid work!
```

## Database Schema Check

### Current Schema
```sql
-- leave_balances table
total_credits DECIMAL(8,2)      -- ❌ Only 2 decimals
used_credits DECIMAL(8,2)       -- ❌ Only 2 decimals
available_credits DECIMAL(8,2)  -- ❌ Only 2 decimals

-- daily_salary_computations table
monthly_rate DECIMAL(12,2)      -- ❌ Only 2 decimals
daily_rate DECIMAL(12,2)        -- ❌ Only 2 decimals
hourly_rate DECIMAL(12,2)       -- ❌ Only 2 decimals
daily_basic_pay DECIMAL(12,2)   -- ❌ Only 2 decimals
```

### Recommended Schema
```sql
-- leave_balances table
total_credits DECIMAL(8,4)      -- ✓ 4 decimals
used_credits DECIMAL(8,4)       -- ✓ 4 decimals
available_credits DECIMAL(8,4)  -- ✓ 4 decimals

-- daily_salary_computations table
monthly_rate DECIMAL(12,4)      -- ✓ 4 decimals
daily_rate DECIMAL(12,4)        -- ✓ 4 decimals
hourly_rate DECIMAL(12,4)       -- ✓ 4 decimals
daily_basic_pay DECIMAL(12,4)   -- ✓ 4 decimals
```

## Migration Required

```sql
-- Migration: increase_decimal_precision.sql

-- Leave Balances
ALTER TABLE `leave_balances` 
MODIFY COLUMN `total_credits` DECIMAL(8,4) DEFAULT 0.0000,
MODIFY COLUMN `used_credits` DECIMAL(8,4) DEFAULT 0.0000,
MODIFY COLUMN `pending_credits` DECIMAL(8,4) DEFAULT 0.0000,
MODIFY COLUMN `available_credits` DECIMAL(8,4) DEFAULT 0.0000,
MODIFY COLUMN `carried_over` DECIMAL(8,4) DEFAULT 0.0000;

-- Leave Transactions
ALTER TABLE `leave_transactions`
MODIFY COLUMN `amount` DECIMAL(8,4) NOT NULL,
MODIFY COLUMN `balance_before` DECIMAL(8,4) NOT NULL,
MODIFY COLUMN `balance_after` DECIMAL(8,4) NOT NULL;

-- Leave Applications
ALTER TABLE `leave_applications`
MODIFY COLUMN `number_of_days` DECIMAL(5,4) NOT NULL;

-- Daily Salary Computations
ALTER TABLE `daily_salary_computations`
MODIFY COLUMN `monthly_rate` DECIMAL(12,4) NOT NULL,
MODIFY COLUMN `daily_rate` DECIMAL(12,4) NOT NULL,
MODIFY COLUMN `hourly_rate` DECIMAL(12,4) NOT NULL,
MODIFY COLUMN `daily_basic_pay` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `ot_pay` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `late_deduction` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `undertime_deduction` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `daily_gross_pay` DECIMAL(12,4) NOT NULL DEFAULT 0.0000;
```

## Real-World Impact Examples

### Example 1: Late Deduction
```
Employee: 15 minutes late
Current (2 decimals): 15/480 = 0.03 days (rounded)
Correct (4 decimals): 15/480 = 0.0313 days

Difference: 0.0013 days
Over 100 lates: 0.13 days = 1.04 hours
Employee loses 1 hour of leave credits!
```

### Example 2: Overtime Pay
```
Employee: 1.5 hours OT
Hourly rate: ₱689.00
Current (rounded): 1.5 × 689 × 1.25 = ₱1,291.88 (rounded to ₱1,292)
Correct (exact): 1.5 × 689 × 1.25 = ₱1,291.875

Difference: ₱0.125 per OT
Over 100 OT sessions: ₱12.50 lost
```

### Example 3: Leave Balance
```
Employee files 0.5 day leave (4 hours)
Current balance: 7.95 days
After deduction (2 decimals): 7.95 - 0.50 = 7.45 days
After deduction (4 decimals): 7.9500 - 0.5000 = 7.4500 days

Seems same, but with multiple transactions:
Transaction 1: -0.0625 days → rounds to -0.06
Transaction 2: -0.0625 days → rounds to -0.06
Transaction 3: -0.0625 days → rounds to -0.06
Total deducted: 0.18 days (WRONG)
Actual should be: 0.1875 days
Loss: 0.0075 days = 3.6 minutes
```

## Implementation Priority

### Phase 1: CRITICAL (Do First)
1. ✅ Database schema migration
2. ✅ DailySalaryComputation.php (payroll)
3. ✅ LateDeductionService.php (leave deductions)

### Phase 2: HIGH (Do Next)
4. ✅ AttendanceController.php (overtime)
5. ✅ Blade templates (leave credits, transactions)

### Phase 3: MEDIUM (Do Last)
6. ✅ Display formatting (minutes, hours)
7. ✅ Percentage displays

## Testing Checklist

- [ ] Run database migration
- [ ] Test leave deduction with 15 minutes late
- [ ] Verify leave balance shows 4 decimals
- [ ] Test overtime calculation
- [ ] Check payroll computation accuracy
- [ ] Verify transaction history shows exact amounts
- [ ] Test leave application with 0.5 days
- [ ] Check DTR export has exact values
- [ ] Verify salary computations are precise

## Rollback Plan

If issues occur:
1. Revert database migration
2. Restore original round() functions
3. Revert blade template changes
4. Test with previous data

---

**Status:** 🔴 CRITICAL - Needs immediate fix
**Impact:** Financial accuracy, employee trust, legal compliance
**Estimated Time:** 2 hours (migration + code changes + testing)
