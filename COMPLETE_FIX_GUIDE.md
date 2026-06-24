# FIX: Leave Credits Showing 0.0 Days - Complete Solution

## Problem
The dropdown in `fileLeaveModal.blade.php` shows **0.0 days available** for VL and SL even though Jeremy Pogi should have imported leave credits.

## Root Causes Found

### ✅ Issue #1: Wrong Controller Query (FIXED)
The `PermanentLeaveBalanceController.php` was filtering by `$selectedYear` (2026), but Jeremy's credits are in year 2023.

**Status**: ✅ FIXED - Controller now fetches latest year's balance

### ⚠️ Issue #2: Database Not Imported (NEEDS FIXING)
The SQL dump shows Jeremy has leave balances, but they're not in your **actual running database**.

**Status**: ⚠️ NEEDS ACTION - You must import the data

## Solution Steps

### Step 1: Import Leave Balance Data

You have TWO options:

#### Option A: Import via SQL (Quick Method)

1. **Open MySQL Workbench or Command Line**
2. **Run the SQL script**:
   ```bash
   mysql -u root -p primehrismagdalena < "C:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\IMPORT_LEAVE_BALANCES.sql"
   ```
   
   OR in MySQL Workbench:
   - File → Open SQL Script
   - Select: `C:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\IMPORT_LEAVE_BALANCES.sql`
   - Click Execute (⚡)

#### Option B: Import via Excel Migration Modal (Proper Method)

1. **Login as Admin** (admin@gmail.com)
2. **Navigate to**: Admin → Leave & Benefits → Transactions tab
3. **Click**: "Migrate Leave Records" button
4. **Select**: Jeremy Pogi (2024001 - Jeremy Pogi)
5. **Upload**: Jeremy's Excel leave ledger file
6. **Click**: "Migrate Records"

### Step 2: Verify the Import

Run this query in MySQL to check if data exists:

```sql
SELECT 
    employee_id,
    leave_code,
    year,
    available_credits
FROM leave_balances 
WHERE employee_id = 8 
  AND leave_code IN ('VL', 'SL')
ORDER BY leave_code, year DESC;
```

**Expected Result**:
```
+-------------+------------+------+-------------------+
| employee_id | leave_code | year | available_credits |
+-------------+------------+------+-------------------+
|           8 | SL         | 2023 |        141.250000 |
|           8 | SL         | 2022 |        128.250000 |
|           8 | VL         | 2023 |         80.032000 |
|           8 | VL         | 2022 |         67.032000 |
+-------------+------------+------+-------------------+
```

### Step 3: Clear Laravel Cache

After importing, clear the application cache:

```bash
cd C:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### Step 4: Test the Fix

1. **Login as Jeremy Pogi**:
   - Email: `maria.cruz@primehr.com`
   - Password: (your test password)

2. **Navigate to**: Permanent → Leave & Benefits

3. **Click**: "File Leave" button

4. **Check the dropdown**: Should now show:
   - ✅ VL (Vacation Leave) - **80.0 days available**
   - ✅ SL (Sick Leave) - **141.3 days available**

## How the Fix Works

### Before (Bug):
```php
// PermanentLeaveBalanceController - Line 66
->with(['leaveBalances' => function($query) use ($employee, $selectedYear) {
    $query->where('employee_id', $employee->id)
          ->where('year', $selectedYear); // ❌ Only gets 2026!
}])
```

### After (Fixed):
```php
// PermanentLeaveBalanceController - Line 66
foreach ($leaveTypes as $leaveType) {
    $latestBalance = \App\Models\LeaveBalance::where('employee_id', $employee->id)
        ->where('leave_code', $leaveType->leave_code)
        ->orderBy('year', 'desc') // ✅ Gets most recent year
        ->first();
    
    $leaveType->setRelation('leaveBalances', $latestBalance ? collect([$latestBalance]) : collect());
}
```

## Files Modified

1. ✅ `app/Http/Controllers/PermanentLeaveBalanceController.php` (lines 62-77)
2. ✅ `app/Http/Controllers/PermanentLeaveController.php` (lines 27-44) - Also fixed for consistency

## Database Tables Involved

- **`leave_balances`** - Stores employee leave credits by year
  - `employee_id` - Links to employee (8 = Jeremy Pogi)
  - `leave_code` - VL, SL, ML, etc.
  - `year` - Calendar year (2012-2026)
  - `available_credits` - **This shows in the dropdown**

## Expected Behavior After Fix

### For Jeremy Pogi (Employee 8):
- **VL**: Shows 80.0 days (from 2023 balance)
- **SL**: Shows 141.3 days (from 2023 balance)
- **Other leaves**: Show 0.0 if no balance exists

### For New Employees (No historical data):
- **All leave types**: Show 0.0 days (correct behavior)

### For Employees with Current Year Balances:
- **Shows current year (2026)** if available
- **Otherwise shows latest year** balance

## Troubleshooting

If the dropdown still shows 0.0 after following all steps:

1. **Check browser console** (F12) for JavaScript errors
2. **Hard refresh** the page (Ctrl+Shift+R)
3. **Verify database** using the SQL query in Step 2
4. **Check Laravel logs**: `storage/logs/laravel.log`
5. **Verify the route**: Should be `permanent.leave` → `PermanentLeaveBalanceController@show`

## Notes

- This fix ensures cumulative leave types (VL, SL) from previous years are shown
- The system now properly handles historical leave data from Excel migrations
- New leave credits for 2026 will still accrue normally via the accrual system
