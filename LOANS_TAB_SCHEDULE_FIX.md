# Loans Tab - Schedule Split Data Fix

## Issue Analysis
The loans tab was not correctly fetching and displaying custom schedule splits for employee loans.

## Problems Found

### 1. **Missing Eager Loading**
**Location:** `routes/web.php` - `/admin/deductions` route

**Problem:** The `$loans` query was not eager-loading the `deductionType.schedules` relationship:
```php
// BEFORE (Missing schedules)
$loans = \App\Models\EmployeeDeduction::with([
    'employee.employmentDetail.departmentRelation',
    'deductionType'  // <-- Missing .schedules
])
```

**Impact:** This could cause N+1 query issues and the schedules might not be available when rendering the view.

### 2. **Not Checking Custom Schedules**
**Location:** `resources/views/admin/deductions/partials/loans.blade.php`

**Problem:** The blade template was only checking the deduction type's default schedule, ignoring the employee's custom schedule stored in `employee_deductions.custom_cutoff_schedule`.

```php
// BEFORE (Only checking default)
$schedule = $loan->deductionType->schedules->first();
$cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
```

**Impact:** Even if an employee had a custom schedule set via the "Manage Schedules" feature, it wouldn't be displayed in the loans tab.

### 3. **No Visual Indicator for Custom Schedules**
**Problem:** There was no way to tell if a loan was using a custom schedule or the default schedule.

## Fixes Applied

### Fix 1: Added Eager Loading
**File:** `routes/web.php`

```php
// AFTER (With schedules eager-loaded)
$loans = \App\Models\EmployeeDeduction::with([
    'employee.employmentDetail.departmentRelation',
    'deductionType.schedules'  // ✅ Now includes schedules
])
```

### Fix 2: Prioritize Custom Schedules
**File:** `loans.blade.php`

```php
// AFTER (Checks custom first, then default)
$cutoffSchedule = 'BOTH_SPLIT'; // Default fallback

if ($loan->custom_cutoff_schedule) {
    // Use employee's custom schedule
    $cutoffSchedule = $loan->custom_cutoff_schedule;
} else {
    // Use deduction type's default schedule
    $schedule = $loan->deductionType->schedules->first();
    $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
}
```

### Fix 3: Added Custom Schedule Indicator
**File:** `loans.blade.php`

```php
// Add indicator if using custom schedule
$scheduleLabel = $cutoffSchedule;
if ($loan->custom_cutoff_schedule) {
    $scheduleLabel .= ' (Custom)';
}
```

**Display:**
```html
<p style="color: #9999bb; margin: 0; font-size: 11px;">{{ $scheduleLabel }}</p>
```

### Fix 4: Updated View Details Modal
**File:** `loans.blade.php` - JavaScript function `viewLoanDetails()`

```javascript
// Get schedule - prioritize custom schedule over default
let schedule = 'BOTH_SPLIT'; // Default
let scheduleSource = 'Default';

if (data.custom_cutoff_schedule) {
    schedule = data.custom_cutoff_schedule;
    scheduleSource = 'Custom';
} else if (data.deduction_type.schedules && data.deduction_type.schedules.length > 0) {
    schedule = data.deduction_type.schedules[0].cutoff_schedule;
    scheduleSource = 'Type Default';
}
```

**Added to modal display:**
```
║ Schedule Source: Custom/Type Default/Default ║
```

## How It Works Now

### Schedule Priority Logic:
1. **First:** Check if employee has `custom_cutoff_schedule` set
2. **Second:** Check deduction type's default schedule from `deduction_schedules` table
3. **Fallback:** Use `BOTH_SPLIT` as system default

### Per-Cutoff Calculation:
Based on the schedule type, the system calculates:

- **1ST_ONLY:** Full amount on 1st cutoff, ₱0 on 2nd
- **2ND_ONLY:** ₱0 on 1st cutoff, full amount on 2nd
- **BOTH_FULL:** Full amount on BOTH cutoffs (double deduction)
- **BOTH_SPLIT:** Half amount on each cutoff (default)

### Visual Indicators:
- Schedule label shows the schedule type (e.g., "1ST_ONLY (Custom)")
- "(Custom)" suffix indicates employee-specific override
- No suffix means using deduction type's default schedule

## Testing Checklist

- [x] Eager loading includes schedules relationship
- [x] Custom schedules are checked first
- [x] Default schedules are used as fallback
- [x] Per-cutoff amounts calculated correctly
- [x] Visual indicator shows custom vs default
- [x] View details modal shows schedule source
- [x] All schedule types display correctly:
  - [x] 1ST_ONLY
  - [x] 2ND_ONLY
  - [x] BOTH_FULL
  - [x] BOTH_SPLIT

## Example Display

### Loan with Custom Schedule:
```
Per Cutoff: ₱500.00 (1st only)
1ST_ONLY (Custom)
```

### Loan with Default Schedule:
```
Per Cutoff: ₱250.00 (split)
BOTH_SPLIT
```

## Files Modified

1. **routes/web.php**
   - Added `.schedules` to eager loading in `/admin/deductions` route

2. **resources/views/admin/deductions/partials/loans.blade.php**
   - Updated schedule fetching logic to prioritize custom schedules
   - Added custom schedule indicator
   - Updated `viewLoanDetails()` JavaScript function
   - Added "Schedule Source" field to details modal

## Related Features

This fix ensures consistency with:
- **Manage Schedules** feature (Schedules tab)
- **Payroll Generation** (uses same schedule logic)
- **Deduction Export** (shows correct per-cutoff amounts)

## Status
✅ **FIXED** - Loans tab now correctly displays custom and default schedule splits
