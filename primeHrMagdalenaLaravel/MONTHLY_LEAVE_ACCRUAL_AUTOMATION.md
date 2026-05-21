# Monthly Leave Accrual Automation

## Overview
Automated system for processing monthly leave accruals (VL and SL) and year-end carryovers.

## Commands Created

### 1. Monthly Accrual Command
**Command:** `php artisan leave:process-monthly-accrual`

**Purpose:** Automatically credits 1.25 days of VL and SL to eligible employees every month.

**Options:**
- `--month=` : Specify month (1-12), defaults to current month
- `--year=` : Specify year, defaults to current year

**Example Usage:**
```bash
# Process current month
php artisan leave:process-monthly-accrual

# Process specific month
php artisan leave:process-monthly-accrual --month=6 --year=2026
```

**Features:**
- ✅ Credits 1.25 days per month for VL and SL
- ✅ Checks 6-month requirement for VL
- ✅ Prevents duplicate processing for same month
- ✅ Respects annual limits (15 days max)
- ✅ Creates transaction logs for audit trail
- ✅ Skips employees not yet hired

### 2. Year-End Carryover Command
**Command:** `php artisan leave:process-year-end-carryover`

**Purpose:** Carries over unused VL and SL credits to the next year.

**Options:**
- `--year=` : Specify year to carry over from, defaults to previous year

**Example Usage:**
```bash
# Carryover from previous year
php artisan leave:process-year-end-carryover

# Carryover from specific year
php artisan leave:process-year-end-carryover --year=2025
```

**Features:**
- ✅ Only carries over cumulative leave types (VL and SL)
- ✅ Transfers all unused credits to next year
- ✅ Prevents duplicate carryover processing
- ✅ Creates transaction logs
- ✅ Updates carried_over field in balances

## Automated Scheduling

The system is configured to run automatically:

### Monthly Accrual
- **Schedule:** Last day of every month at 11:59 PM
- **Command:** `leave:process-monthly-accrual`
- **Configured in:** `routes/console.php`

### Year-End Carryover
- **Schedule:** January 1st at 12:01 AM
- **Command:** `leave:process-year-end-carryover`
- **Configured in:** `routes/console.php`

## Running the Scheduler

To enable automatic execution, you need to add this cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

For Windows Task Scheduler:
```
Program: C:\path\to\php.exe
Arguments: artisan schedule:run
Start in: C:\path\to\your\project
Trigger: Every 1 minute
```

## Testing

### Test Monthly Accrual
```bash
# Test for June 2026
php artisan leave:process-monthly-accrual --month=6 --year=2026

# Test for July 2026 (when 6-month requirement is met)
php artisan leave:process-monthly-accrual --month=7 --year=2026
```

### Test Year-End Carryover
```bash
# Test carryover from 2025 to 2026
php artisan leave:process-year-end-carryover --year=2025
```

## Transaction Logs

All accruals and carryovers are logged in the `leave_transactions` table:

**Accrual Transaction:**
- `transaction_type`: credit
- `reference_type`: accrual
- `amount`: 1.25 (days)
- `remarks`: "Monthly accrual for [Month Year] - 1.25 days"

**Carryover Transaction:**
- `transaction_type`: credit
- `reference_type`: carryover
- `amount`: [unused credits from previous year]
- `remarks`: "Carried over from [Year] - [X] days"

## Business Rules

### VL (Vacation Leave)
- ✅ Accrues 1.25 days per month
- ✅ Requires 6 months of service before use
- ✅ Annual limit: 15 days
- ✅ Cumulative (carries over to next year)
- ✅ Can be monetized

### SL (Sick Leave)
- ✅ Accrues 1.25 days per month
- ✅ No waiting period
- ✅ Annual limit: 15 days
- ✅ Cumulative (carries over to next year)
- ✅ Can be monetized

### Other Leave Types
- Fixed allocation (SPL: 3 days, Wellness: 5 days, etc.)
- Non-cumulative (expires at year-end)
- Granted at initialization only

## Verification

Check accrual results:
```bash
php artisan tinker
```

```php
// Check employee balance
$balance = App\Models\LeaveBalance::where('employee_id', 6)
    ->where('leave_code', 'VL')
    ->first();
echo "Available: " . $balance->available_credits . " days";

// Check transaction history
$transactions = App\Models\LeaveTransaction::where('employee_id', 6)
    ->where('leave_code', 'VL')
    ->orderBy('transaction_date')
    ->get();
```

## Files Created

1. `app/Console/Commands/ProcessMonthlyLeaveAccrual.php`
2. `app/Console/Commands/ProcessYearEndCarryover.php`
3. `routes/console.php` (updated with schedules)

## Next Steps

1. ✅ Monthly accrual automation - COMPLETED
2. ⏳ Build leave application form
3. ⏳ Create approval workflow
4. ⏳ Build leave balance dashboard
