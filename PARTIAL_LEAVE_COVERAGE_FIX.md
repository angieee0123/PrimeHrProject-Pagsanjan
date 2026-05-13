# PARTIAL LEAVE COVERAGE FIX - Complete Solution

## The Bug You Found

Employee jeremypogi@gmail.com on May 01, 2026:
- **Late**: 1 hour 45 minutes (105 minutes)
- **Available Leave**: VL = 0.01 days, SL = 0.01 days = **0.02 days total**
- **Late in days**: 105 ÷ 1440 = **0.072917 days**
- **System showed**: 8 hours accredited ❌ **WRONG!**

## The Problem

The old `LateDeductionService` logic:
1. If leave balance >= late → Deduct from leave, credit 8 hours
2. If leave balance < late → Do nothing, keep original accredited hours

**This was WRONG!** It didn't handle partial coverage.

## The Correct Logic (Now Implemented)

### Step 1: Try to Cover with Available Leave
- VL available: 0.01 days → Deduct 0.01 days
- SL available: 0.01 days → Deduct 0.01 days
- **Total covered**: 0.02 days

### Step 2: Calculate Remaining Late
- Total late: 0.072917 days
- Covered by leave: 0.02 days
- **Remaining**: 0.072917 - 0.02 = **0.052917 days** (76 minutes)

### Step 3: Deduct Remaining from Accredited Hours
- Original accredited: 480 minutes (8 hours)
- Remaining late: 76 minutes
- **New accredited**: 480 - 76 = **404 minutes (6.73 hours)** ✓

## What Was Fixed

### 1. Updated `LateDeductionService.php`

**New Logic**:
```php
public function processLateDeduction(AccreditedHoursLog $log): void
{
    $lateMinutes = $log->late_minutes;
    $lateDays = $lateMinutes / 1440;
    $remainingLateDays = $lateDays;
    
    // Try VL first
    if ($vlBalance && $vlBalance->available_credits > 0) {
        $deductAmount = min($vlBalance->available_credits, $remainingLateDays);
        $this->deductFromLeave($vlBalance, $deductAmount, $log, 'VL', false);
        $remainingLateDays -= $deductAmount;
    }
    
    // Try SL if still remaining
    if ($remainingLateDays > 0 && $slBalance && $slBalance->available_credits > 0) {
        $deductAmount = min($slBalance->available_credits, $remainingLateDays);
        $this->deductFromLeave($slBalance, $deductAmount, $log, 'SL', false);
        $remainingLateDays -= $deductAmount;
    }
    
    // Update accredited hours based on coverage
    if ($remainingLateDays <= 0) {
        // Fully covered - credit 8 hours
        $log->total_accredited_minutes = 480;
        $log->late_deduction_leave_type = 'VL+SL (full)';
    } else {
        // Partially covered - deduct remaining from hours
        $remainingLateMinutes = round($remainingLateDays * 1440);
        $log->total_accredited_minutes -= $remainingLateMinutes;
        $log->late_deduction_leave_type = 'VL+SL (partial)';
    }
}
```

### 2. Updated Database Schema
- Increased `late_deduction_leave_type` column from VARCHAR(10) to VARCHAR(50)
- Allows storing "VL+SL (partial)" or "VL+SL (full)"

### 3. Updated Frontend Display
**Full Coverage**:
```
8 hrs
✓ Grace: PM
📋 From Log
✓ Late Fully Covered by VL
105 min late → 0.072917 days deducted
```

**Partial Coverage**:
```
6.73 hrs
✓ Grace: PM
📋 From Log
⚠ Partial Coverage by VL+SL
105 min late, insufficient leave
Remaining deducted from hours
```

## Current State for jeremypogi@gmail.com (May 01, 2026)

### Accredited Hours Log
- Late minutes: 105
- Total accredited: **404 minutes (6.73 hours)** ✓
- Late deducted from leave: YES
- Leave type: **VL+SL (partial)**

### Leave Balances
- VL: 0.00 days (was 0.01, deducted 0.01)
- SL: 0.00 days (was 0.01, deducted 0.01)

### Leave Transactions
1. VL Debit: -0.01 days (covered 14.4 minutes of late)
2. SL Debit: -0.01 days (covered 14.4 minutes of late)
3. **Remaining 76 minutes deducted from accredited hours**

## How to Verify

### Check in Detailed DTR
1. Open Detailed DTR for jeremypogi@gmail.com
2. Look at May 01, 2026
3. Should show:
   - **Accredited Hours**: 6.73 hrs (not 8 hrs)
   - **Late indicator**: ⚠ Partial Coverage by VL+SL

### Check in Database
```sql
SELECT 
    ahl.late_minutes,
    ahl.total_accredited_minutes,
    ahl.late_deducted_from_leave,
    ahl.late_deduction_leave_type,
    a.date
FROM accredited_hours_log ahl
JOIN attendance a ON ahl.attendance_id = a.id
WHERE ahl.employee_id = 8 AND a.date = '2026-05-01';
```

Expected:
- `late_minutes`: 105
- `total_accredited_minutes`: 404
- `late_deducted_from_leave`: 1
- `late_deduction_leave_type`: VL+SL (partial)

## Files Modified
1. `app/Services/LateDeductionService.php` - Complete rewrite of logic
2. `database/migrations/2026_05_13_191501_update_late_deduction_leave_type_column_length.php` - Column size
3. `resources/js/adminAttendance.js` - Display logic for partial coverage
4. Built assets with `npm run build`

## Future Behavior

### Scenario 1: Full Coverage
- Late: 30 minutes (0.020833 days)
- Available: VL = 0.05 days
- **Result**: Deduct 0.020833 from VL, credit 8 hours

### Scenario 2: Partial Coverage
- Late: 120 minutes (0.083333 days)
- Available: VL = 0.02 days, SL = 0.01 days = 0.03 total
- **Result**: 
  - Deduct 0.02 from VL
  - Deduct 0.01 from SL
  - Remaining: 0.053333 days (77 minutes)
  - Accredited: 480 - 77 = **403 minutes (6.72 hours)**

### Scenario 3: No Coverage
- Late: 60 minutes (0.041667 days)
- Available: VL = 0 days, SL = 0 days
- **Result**: 
  - No leave deduction
  - Accredited: 480 - 60 = **420 minutes (7 hours)**

## Summary

✅ **FIXED**: System now correctly handles partial leave coverage
✅ **FIXED**: Remaining late is deducted from accredited hours
✅ **FIXED**: Display shows partial vs full coverage
✅ **FIXED**: Database stores accurate accredited hours
