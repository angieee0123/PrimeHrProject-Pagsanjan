# Detailed DTR - Late Deduction Display Fix

## Issue
The Detailed DTR modal was showing late deduction information, but it was displaying the WRONG calculation formula (dividing by 480 instead of 1440).

## What Was Wrong

### In the JavaScript (adminAttendance.js)
```javascript
// OLD (INCORRECT)
const lateDays = (lateMinutes / 480).toFixed(4);
accreditedDisplay += `<br><small>...${lateMinutes} min late deducted (${lateDays} days)</small>`;
```

This would show:
- 60 minutes late → 0.1250 days (WRONG!)
- 120 minutes late → 0.2500 days (WRONG!)

## What Was Fixed

### Updated JavaScript Calculation
```javascript
// NEW (CORRECT)
const lateDays = (lateMinutes / 1440).toFixed(6);
accreditedDisplay += `<br><small>...${lateMinutes} min late → ${lateDays} days deducted</small>`;
```

Now correctly shows:
- 60 minutes late → 0.041667 days ✓
- 120 minutes late → 0.083333 days ✓

## How It Works Now

When viewing an employee's Detailed DTR:

1. **Backend** (AttendanceController.php) returns:
   - `late_deducted_from_leave`: true/false
   - `late_deduction_leave_type`: "VL" or "SL"
   - `late_minutes`: actual minutes late

2. **Frontend** (adminAttendance.js) displays:
   ```
   ✓ Late Covered by VL
   120 min late → 0.083333 days deducted
   ```

## Example Display

For an employee who was 2 hours (120 minutes) late and had it deducted from VL:

**Accredited Hours Column shows:**
```
8 hrs
✓ Grace: AM
📋 From Log
✓ Late Covered by VL
120 min late → 0.083333 days deducted
```

## Files Modified
1. `resources/js/adminAttendance.js` - Fixed calculation formula and improved display message

## Verification
- Open Detailed DTR for any employee with late deductions
- Check the "Accredited Hours" column
- Verify the calculation shows: minutes ÷ 1440 = days

## Note
The backend (LateDeductionService.php) was already using the correct formula (1440). This fix only corrected the DISPLAY in the frontend JavaScript.
