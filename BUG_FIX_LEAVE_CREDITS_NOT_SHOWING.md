# BUG FIX: Leave Credits Showing 0.0 Days Available

## Problem
When Jeremy Pogi (and other employees) try to file leave in `fileLeaveModal.blade.php`, the dropdown shows **0.0 days available** for VL and SL, even though they have imported leave balances from the Excel migration.

## Root Cause
The `PermanentLeaveController.php` was querying leave balances using **only the current year (2026)**:

```php
// OLD CODE - BUG
$leaveTypes = LeaveType::where('is_active', true)
    ->with(['leaveBalances' => function($q) use ($employee, $currentYear) {
        $q->where('employee_id', $employee->id)
          ->where('year', $currentYear); // Only gets 2026 records!
    }])
    ->orderBy('leave_name')
    ->get();
```

### Why This Was a Problem
- Jeremy Pogi's leave records were migrated from Excel with historical data (2012-2023)
- His latest VL/SL balances are in **year 2023**:
  - VL 2023: 80.032 days available ✅
  - SL 2023: 141.250 days available ✅
- But the system was looking for **year 2026** records, which don't exist for VL/SL
- Only ML (Maternity Leave) had a 2026 record (with 0.0 balance)

## Solution
Modified the controller to fetch the **latest available year's balance** for each leave type, regardless of what year it is:

```php
// NEW CODE - FIXED
// Load all active leave types
$leaveTypes = LeaveType::where('is_active', true)
    ->orderBy('leave_name')
    ->get();

// Manually attach the latest balance for each leave type
foreach ($leaveTypes as $leaveType) {
    $latestBalance = LeaveBalance::where('employee_id', $employee->id)
        ->where('leave_code', $leaveType->leave_code)
        ->orderBy('year', 'desc') // Get the most recent year
        ->first();
    
    // Create a collection with just this balance for consistency with blade template
    $leaveType->setRelation('leaveBalances', $latestBalance ? collect([$latestBalance]) : collect());
}
```

## What This Fix Does
1. **Fetches all active leave types** (VL, SL, ML, etc.)
2. **For each leave type**, queries the `leave_balances` table for the employee
3. **Orders by year descending** (`orderBy('year', 'desc')`) to get the most recent record
4. **Attaches the latest balance** to the leave type object as a collection
5. **Maintains compatibility** with the blade template's `$type->leaveBalances->first()` syntax

## Result
Now when Jeremy Pogi (or any employee with historical leave data) opens the file leave modal:
- **VL (Vacation Leave)**: Shows 80.0 days available ✅ (from 2023 balance)
- **SL (Sick Leave)**: Shows 141.3 days available ✅ (from 2023 balance)
- **Other leave types**: Show their respective latest balances

## Files Modified
- `app/Http/Controllers/PermanentLeaveController.php` (lines 27-44)

## Database Context
- **Table**: `leave_balances`
- **Key columns**: 
  - `employee_id` - Links to employee
  - `leave_code` - Type of leave (VL, SL, etc.)
  - `year` - Calendar year for the balance
  - `available_credits` - **This is what shows in the dropdown**

## Testing Recommendations
1. Login as Jeremy Pogi (employee_id: 8, email: maria.cruz@primehr.com)
2. Navigate to Leave & Benefits → File Leave
3. Click on "Leave Type" dropdown
4. Verify that VL shows ~80 days and SL shows ~141 days available
5. Test with other employees who have imported historical leave data

## Notes
- This fix ensures **cumulative leave types** (VL, SL) that carry over from previous years are properly displayed
- The fix respects the business rule that leave credits accumulate over time
- For newly hired employees (no historical data), the system will still work correctly by showing 0.0 or current year balances
