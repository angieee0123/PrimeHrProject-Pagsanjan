# Late Deduction with Full Hours Credit Feature

## Overview
When an employee is late and the late time is deducted from their leave balance (VL or SL), the system now:
1. **Credits full 8 hours** (480 minutes) as accredited hours
2. **Shows a note** in the Detailed DTR indicating the late was covered by leave

## Changes Made

### 1. LateDeductionService.php
**File:** `app/Services/LateDeductionService.php`

**Updated:** `deductFromLeave()` method

**Changes:**
- When late is deducted from leave, set `total_accredited_minutes` to 480 (full 8 hours)
- Update the attendance record's `accredited_hours` to 480
- Keep the `late_deducted_from_leave` flag and `late_deduction_leave_type`

```php
// Credit full 8 hours (480 minutes) when late is deducted from leave
$log->update([
    'total_accredited_minutes' => 480,
    'late_deducted_from_leave' => true,
    'late_deduction_leave_type' => $leaveType
]);

// Also update the attendance record's accredited_hours
if ($log->attendance) {
    $log->attendance->update(['accredited_hours' => 480]);
}
```

### 2. AttendanceController.php
**File:** `app/Http/Controllers/AttendanceController.php`

**Updated:** `generateDetailedRecords()` method

**Changes:**
- Added `late_deducted_from_leave` field to the response
- Added `late_deduction_leave_type` field to the response

```php
'late_deducted_from_leave' => $hasLog && $log->late_deducted_from_leave,
'late_deduction_leave_type' => $hasLog ? $log->late_deduction_leave_type : null,
```

### 3. adminAttendance.js
**File:** `resources/js/adminAttendance.js`

**Updated:** `renderDetailedDTR()` function

**Changes:**
- Added display logic for late deduction note in Accredited Hours column
- Shows leave type used (VL or SL)
- Shows late minutes and days deducted

```javascript
// Add late deduction indicator if late was deducted from leave
if (record.late_deducted_from_leave && record.late_deduction_leave_type) {
    const lateMinutes = record.late_minutes || 0;
    const lateDays = (lateMinutes / 480).toFixed(4);
    accreditedDisplay += `<br><small style="color: #0b044d; font-size: 10px; font-weight: 600;">✓ Late Covered by ${record.late_deduction_leave_type}</small>`;
    accreditedDisplay += `<br><small style="color: #6b6a8a; font-size: 9px;">${lateMinutes} min late deducted (${lateDays} days)</small>`;
}
```

## How It Works

### Scenario: Employee is 60 minutes late

#### Before Late Deduction Processing:
```
Date: May 07, 2026
AM In: 09:00 (1 hour late)
AM Out: 12:02
PM In: 12:07
PM Out: 19:07

Late Minutes: 60
Accredited Hours: 7 hrs (420 minutes)
VL Balance: 7.95 days
```

#### After Late Deduction Processing:
```
Date: May 07, 2026
AM In: 09:00
AM Out: 12:02
PM In: 12:07
PM Out: 19:07

Late Minutes: 60
Accredited Hours: 8 hrs ✓ Late Covered by VL
                  60 min late deducted (0.1250 days)
VL Balance: 7.825 days (7.95 - 0.125)
```

## Display Format in Detailed DTR

### Example 1: Late Covered by Vacation Leave
```
Accredited Hours:
8 hrs
✓ Grace: PM
📋 From Log
✓ Late Covered by VL
60 min late deducted (0.1250 days)
```

### Example 2: Late Covered by Sick Leave
```
Accredited Hours:
8 hrs
✓ Late Covered by SL
6 min late deducted (0.0125 days)
```

### Example 3: No Late (Normal)
```
Accredited Hours:
8 hrs
✓ Grace: AM, PM
📋 From Log
```

## Visual Indicators

### Colors Used:
- **Green (#15803d):** Full 8 hours credited
- **Purple (#0b044d):** Late covered by leave (bold)
- **Gray (#6b6a8a):** Deduction details

### Icons Used:
- **✓:** Checkmark for grace period and late coverage
- **📋:** From Log indicator

## Business Logic

### Late Deduction Priority:
1. **First:** Deduct from Vacation Leave (VL)
2. **Second:** If VL insufficient, deduct from Sick Leave (SL)
3. **Third:** If both insufficient, partial deduction or no deduction

### Accredited Hours Calculation:
- **Without Late Deduction:** Actual worked hours minus late time
- **With Late Deduction:** Full 8 hours (480 minutes)

### Example Calculations:

#### Case 1: 6 minutes late
```
Late Minutes: 6
Late Days: 6 / 480 = 0.0125 days
VL Deduction: 0.0125 days
Accredited Hours: 480 minutes (8 hrs) ✓
```

#### Case 2: 60 minutes late
```
Late Minutes: 60
Late Days: 60 / 480 = 0.125 days
VL Deduction: 0.125 days
Accredited Hours: 480 minutes (8 hrs) ✓
```

#### Case 3: 120 minutes late
```
Late Minutes: 120
Late Days: 120 / 480 = 0.25 days
VL Deduction: 0.25 days
Accredited Hours: 480 minutes (8 hrs) ✓
```

## Database Updates

### accredited_hours_log table:
```sql
UPDATE accredited_hours_log
SET 
    total_accredited_minutes = 480,
    late_deducted_from_leave = 1,
    late_deduction_leave_type = 'VL'
WHERE id = [log_id];
```

### attendance table:
```sql
UPDATE attendance
SET accredited_hours = 480
WHERE id = [attendance_id];
```

### leave_balances table:
```sql
UPDATE leave_balances
SET 
    used_credits = used_credits + [late_days],
    available_credits = available_credits - [late_days]
WHERE employee_id = [employee_id]
AND leave_code = 'VL'
AND year = [year];
```

### leave_transactions table:
```sql
INSERT INTO leave_transactions (
    employee_id, leave_code, year, transaction_type,
    amount, balance_before, balance_after,
    reference_type, reference_id, transaction_date,
    processed_by, remarks
) VALUES (
    [employee_id], 'VL', [year], 'debit',
    -[late_days], [balance_before], [balance_after],
    'manual_adjustment', [log_id], [date],
    [user_id], 'Late deduction: [minutes] minutes ([days] days) from attendance on [date]'
);
```

## Benefits

### For Employees:
1. **Fair Treatment:** Late time is covered by leave, so they get full day credit
2. **Transparency:** Can see exactly how much leave was used for late time
3. **Accurate Records:** Accredited hours reflect the leave coverage

### For HR/Admin:
1. **Automated Processing:** System automatically handles late deductions
2. **Audit Trail:** Complete transaction history in leave_transactions
3. **Clear Display:** Easy to see which days had late covered by leave

### For Payroll:
1. **Accurate Calculations:** Full 8 hours credited when late is covered
2. **No Manual Adjustments:** System handles everything automatically
3. **Verifiable:** Can trace back to original late time and deduction

## Testing Scenarios

### Test 1: Small Late (6 minutes)
```
Input: 6 minutes late, VL balance = 7.95 days
Expected:
- Accredited Hours: 8 hrs
- VL Balance: 7.9375 days
- Display: "✓ Late Covered by VL - 6 min late deducted (0.0125 days)"
```

### Test 2: Large Late (60 minutes)
```
Input: 60 minutes late, VL balance = 7.95 days
Expected:
- Accredited Hours: 8 hrs
- VL Balance: 7.825 days
- Display: "✓ Late Covered by VL - 60 min late deducted (0.1250 days)"
```

### Test 3: VL Insufficient, Use SL
```
Input: 60 minutes late, VL balance = 0.05 days, SL balance = 9.20 days
Expected:
- Accredited Hours: 8 hrs
- VL Balance: 0 days (0.05 used)
- SL Balance: 9.125 days (0.075 used)
- Display: "✓ Late Covered by VL/SL - 60 min late deducted (0.1250 days)"
```

### Test 4: Both Insufficient
```
Input: 60 minutes late, VL balance = 0 days, SL balance = 0 days
Expected:
- Accredited Hours: 7 hrs (not credited full)
- VL Balance: 0 days
- SL Balance: 0 days
- Display: No late coverage note
```

## Troubleshooting

### Issue: Late deduction not showing
**Solution:** Check if `late_deducted_from_leave` is true in accredited_hours_log

### Issue: Accredited hours not 8 hrs
**Solution:** Verify the LateDeductionService was called after attendance correction

### Issue: Leave balance not updated
**Solution:** Check leave_transactions table for the deduction record

### Issue: Display not showing note
**Solution:** Clear browser cache and reload the page

## Related Files
- `app/Services/LateDeductionService.php`
- `app/Http/Controllers/AttendanceController.php`
- `resources/js/adminAttendance.js`
- `app/Models/AccreditedHoursLog.php`
- `app/Models/LeaveBalance.php`
- `app/Models/LeaveTransaction.php`

## Migration Required
No database migration required. The feature uses existing columns:
- `accredited_hours_log.late_deducted_from_leave`
- `accredited_hours_log.late_deduction_leave_type`
- `accredited_hours_log.total_accredited_minutes`
- `attendance.accredited_hours`
