# ✅ UNDERTIME LEAVE DEDUCTION - FULLY IMPLEMENTED

## Status: COMPLETE ✅

The system now automatically deducts **UNDERTIME** from leave credits (VL/SL), exactly like it handles **LATE** deductions.

---

## What Changed

### Before:
- ❌ Late → Deducted from VL/SL ✅
- ❌ Undertime → Only salary deduction (LWOP) ❌

### After:
- ✅ Late → Deducted from VL/SL ✅
- ✅ Undertime → Deducted from VL/SL ✅

---

## Files Created/Modified

### ✅ Created:
1. **Migration**: `2026_05_17_000001_add_undertime_leave_deduction_tracking.php`
   - Status: ✅ Ran (Batch 19)
   - Added: `undertime_deducted_from_leave`, `undertime_deduction_leave_type`

2. **Service**: `app/Services/UndertimeDeductionService.php`
   - Handles undertime deduction logic
   - Mirrors LateDeductionService

3. **Documentation**: 
   - `UNDERTIME_LEAVE_DEDUCTION_IMPLEMENTATION.md`
   - `UNDERTIME_LEAVE_DEDUCTION_COMPLETE.md`
   - `UNDERTIME_LEAVE_DEDUCTION_SUMMARY.md` (this file)

### ✅ Modified:
1. **Controller**: `app/Http/Controllers/AttendanceController.php`
   - Added UndertimeDeductionService import
   - Calls `processUndertimeDeduction()` after attendance correction
   - Added undertime fields to `generateDetailedRecords()`

2. **Frontend**: `resources/js/adminAttendance.js`
   - Added undertime deduction display logic
   - Shows full/partial coverage indicators
   - Displays LWOP for uncovered undertime

---

## How to Test

### Step 1: Create/Edit Attendance with Undertime

Example: May 22, 2026 - Jeremy Pogi
```
AM In:  05:01
AM Out: 10:00
PM In:  12:04
PM Out: 18:07  ← Left 1 hour 7 minutes LATE (overtime, not undertime)

Wait, this is OVERTIME, not undertime!

Let me use a real undertime example:
AM In:  08:00
AM Out: 12:00
PM In:  13:00
PM Out: 15:00  ← Left 2 hours EARLY (undertime = 120 minutes)
```

### Step 2: Check Leave Balance Before
```sql
SELECT leave_code, available_credits 
FROM leave_balances 
WHERE employee_id = 8 AND year = 2026;

-- Example:
-- VL: 0.989583 days
-- SL: 1.250000 days
```

### Step 3: Correct Attendance
- Admin corrects the attendance record
- System automatically:
  1. Calculates undertime (120 minutes)
  2. Converts to days (120 ÷ 480 = 0.250000 days)
  3. Deducts from VL first
  4. Records transaction

### Step 4: Verify Results

**Check Accredited Hours Log**:
```sql
SELECT 
    undertime_minutes,
    undertime_deducted_from_leave,
    undertime_deduction_leave_type,
    total_accredited_minutes,
    lwop_minutes
FROM accredited_hours_log
WHERE attendance_id = [attendance_id];

-- Expected:
-- undertime_minutes: 120
-- undertime_deducted_from_leave: 1
-- undertime_deduction_leave_type: 'VL (full)'
-- total_accredited_minutes: 480 (full 8 hours credited)
-- lwop_minutes: 0
```

**Check Leave Balance After**:
```sql
SELECT leave_code, available_credits 
FROM leave_balances 
WHERE employee_id = 8 AND year = 2026;

-- Expected:
-- VL: 0.739583 days (0.989583 - 0.250000)
-- SL: 1.250000 days (unchanged)
```

**Check Transaction History**:
```sql
SELECT 
    leave_code,
    amount,
    balance_before,
    balance_after,
    remarks
FROM leave_transactions
WHERE employee_id = 8 
AND remarks LIKE '%undertime%'
ORDER BY created_at DESC
LIMIT 1;

-- Expected:
-- leave_code: VL
-- amount: -0.250000
-- balance_before: 0.989583
-- balance_after: 0.739583
-- remarks: "Undertime deduction: 120 minutes (0.250000 days) from attendance on 2026-05-22"
```

**Check DTR Display**:
```
✓ Undertime Fully Covered by VL
120 min undertime → 0.250000 days deducted
```

---

## Example Scenarios

### Scenario 1: Full Coverage
```
Employee: Jeremy Pogi
Undertime: 120 minutes (2 hours)
VL Available: 1.000 days

Result:
✅ VL Deducted: 0.250 days
✅ Accredited Hours: 480 minutes (full 8 hours)
✅ Salary Deduction: ₱0.00
✅ VL Balance: 0.750 days
```

### Scenario 2: Partial Coverage
```
Employee: Jeremy Pogi
Undertime: 180 minutes (3 hours)
VL Available: 0.125 days (60 minutes)
SL Available: 0.125 days (60 minutes)

Result:
✅ VL Deducted: 0.125 days (60 minutes)
✅ SL Deducted: 0.125 days (60 minutes)
⚠️ LWOP: 60 minutes (uncovered)
✅ Accredited Hours: 360 minutes (6 hours)
✅ Salary Deduction: ₱689.00 (for 60 minutes only)
```

### Scenario 3: Both Late and Undertime
```
Employee: Jeremy Pogi
Late: 60 minutes
Undertime: 120 minutes
VL Available: 1.000 days

Result:
✅ Late covered by VL: 0.125 days
✅ Undertime covered by VL: 0.250 days
✅ Total VL Deducted: 0.375 days
✅ Accredited Hours: 480 minutes (full 8 hours)
✅ Salary Deduction: ₱0.00
✅ VL Balance: 0.625 days
```

---

## Verification Queries

### Check Current Implementation:
```sql
-- 1. Verify columns exist
DESCRIBE accredited_hours_log;
-- Look for: undertime_deducted_from_leave, undertime_deduction_leave_type

-- 2. Check existing records
SELECT 
    a.date,
    ahl.undertime_minutes,
    ahl.undertime_deducted_from_leave,
    ahl.undertime_deduction_leave_type,
    ahl.total_accredited_minutes,
    ahl.lwop_minutes
FROM accredited_hours_log ahl
JOIN attendance a ON ahl.attendance_id = a.id
WHERE ahl.employee_id = 8
AND ahl.undertime_minutes > 0
ORDER BY a.date DESC;

-- 3. Check leave transactions
SELECT 
    transaction_date,
    leave_code,
    amount,
    balance_before,
    balance_after,
    remarks
FROM leave_transactions
WHERE employee_id = 8
AND remarks LIKE '%undertime%'
ORDER BY transaction_date DESC;
```

---

## Key Features

✅ **Automatic Processing**: No manual intervention needed  
✅ **CSC Compliant**: 480 minutes = 1 work day  
✅ **Exact Precision**: 6 decimal places, no rounding  
✅ **Priority Order**: VL first, then SL  
✅ **Partial Coverage**: LWOP for uncovered minutes  
✅ **Transaction History**: All deductions recorded  
✅ **DTR Display**: Shows deduction info clearly  
✅ **Accredited Hours**: Full 8 hours when fully covered  

---

## Important Notes

1. **Automatic**: Processes automatically when attendance is corrected
2. **No Checkbox**: Unlike old system, no manual trigger needed
3. **Combined**: Handles both late AND undertime in same correction
4. **LWOP**: Tracks uncovered minutes for payroll
5. **Reversible**: Can be rolled back if needed

---

## Next Actions

1. ✅ Migration ran successfully
2. ✅ Service created and integrated
3. ✅ Frontend updated to display
4. ⏳ **Test with real attendance data**
5. ⏳ **Verify leave balances update correctly**
6. ⏳ **Confirm transaction history records**
7. ⏳ **Check DTR display shows properly**

---

## Support

**If undertime is NOT being deducted:**

1. Check migration status:
   ```bash
   php artisan migrate:status | grep undertime
   ```

2. Verify columns exist:
   ```sql
   DESCRIBE accredited_hours_log;
   ```

3. Check service is being called:
   - Look in `AttendanceController.php` line ~1237
   - Should see: `$undertimeDeductionService->processUndertimeDeduction($accreditedLog);`

4. Review logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## Success Indicators

When you correct an attendance record with undertime, you should see:

✅ Leave balance decreases  
✅ Transaction recorded in `leave_transactions`  
✅ DTR shows "✓ Undertime Fully Covered by VL"  
✅ Accredited hours = 480 (if fully covered)  
✅ LWOP = 0 (if fully covered)  

---

**Implementation Date**: May 17, 2026  
**Status**: ✅ COMPLETE  
**Migration**: ✅ Ran (Batch 19)  
**Ready for Testing**: ✅ YES  

---

## Quick Reference

| Aspect | Value |
|--------|-------|
| **Conversion** | 480 minutes = 1 day |
| **Priority** | VL → SL |
| **Precision** | 6 decimals |
| **Display** | Purple badge |
| **Transaction** | Recorded in `leave_transactions` |
| **LWOP** | For uncovered minutes only |
