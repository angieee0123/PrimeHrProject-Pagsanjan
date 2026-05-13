# Late Deduction Calculation Fix

## Problem
The system was incorrectly calculating late deductions by dividing by **480 minutes (8-hour workday)** instead of **1440 minutes (24-hour day)**.

### Example Issue
For an employee late by **2 hours (120 minutes)**:
- **Incorrect calculation**: 120 ÷ 480 = 0.250000 days
- **Correct calculation**: 120 ÷ 1440 = 0.083333 days
- **Over-deducted**: 0.166667 days (approximately 4 hours worth of leave!)

### Root Causes
1. **Wrong formula**: Used 480 minutes (8-hour workday) instead of 1440 minutes (24-hour day)
2. **Database precision**: Columns used `decimal(8,2)` which only stored 2 decimal places, causing additional rounding errors

## Solution Implemented

### 1. Fixed Calculation Formula
**File**: `app/Services/LateDeductionService.php`

**Old (INCORRECT)**:
```php
$lateDays = $lateMinutes / 480; // Wrong: 8-hour workday
```

**New (CORRECT)**:
```php
$lateDays = $lateMinutes / 1440; // Correct: 24-hour day
```

### 2. Database Schema Update
Updated decimal precision from `decimal(8,2)` to `decimal(10,6)` for exact calculations.

**Migration**: `2026_05_13_183745_update_leave_precision_to_exact_calculations.php`

Updated tables:
- `leave_balances`: total_credits, used_credits, pending_credits, available_credits, carried_over
- `leave_transactions`: amount, balance_before, balance_after

### 3. Correction Command
**Command**: `php artisan leave:recalculate-late-deductions`

This command:
- Finds all late deduction transactions
- Recalculates using correct formula (minutes ÷ 1440)
- Credits back over-deducted amounts
- Creates audit trail transactions

### 4. Results for Employee ID 8 (jeremypogi@gmail.com)
**Late by**: 120 minutes (2 hours)

- **Old (incorrect) deduction**: 0.250000 days
- **Correct deduction**: 0.083333 days
- **Refunded**: 0.166667 days
- **New VL balance**: 1.170000 days

## Correct Calculation Examples

| Late Time | Minutes | Calculation | Days Deducted |
|-----------|---------|-------------|---------------|
| 1 minute  | 1       | 1 ÷ 1440    | 0.000694      |
| 15 minutes| 15      | 15 ÷ 1440   | 0.010417      |
| 30 minutes| 30      | 30 ÷ 1440   | 0.020833      |
| 1 hour    | 60      | 60 ÷ 1440   | 0.041667      |
| 2 hours   | 120     | 120 ÷ 1440  | 0.083333      |
| 4 hours   | 240     | 240 ÷ 1440  | 0.166667      |

## Verification

### Check Leave Balance
```sql
SELECT employee_id, leave_code, available_credits, used_credits
FROM leave_balances
WHERE employee_id = 8 AND leave_code = 'VL';
```

### Check Recent Transactions
```sql
SELECT id, employee_id, leave_code, amount, remarks, created_at
FROM leave_transactions
WHERE employee_id = 8 AND leave_code = 'VL'
ORDER BY created_at DESC
LIMIT 5;
```

## Files Modified
1. `app/Services/LateDeductionService.php` - Fixed calculation formula
2. `database/migrations/2026_06_06_000001_create_leave_balances_table.php` - Updated precision
3. `database/migrations/2026_06_07_000002_create_leave_transactions_table.php` - Updated precision
4. `database/migrations/2026_05_13_183745_update_leave_precision_to_exact_calculations.php` - Migration for existing DB
5. `app/Console/Commands/RecalculateLateDeductions.php` - Correction command

## Important Notes
- Late deductions are now calculated based on a **24-hour day** (1440 minutes)
- All calculations maintain **6 decimal places** for accuracy
- Every minute is accurately tracked: 1 minute = 0.000694 days
- Previous over-deductions have been refunded to affected employees
