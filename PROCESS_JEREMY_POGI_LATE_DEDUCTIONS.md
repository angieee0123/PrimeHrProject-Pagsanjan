# Processing Script for Jeremy Pogi's Late Deductions

## Current Data Analysis

### May 05, 2026 (Tuesday)
```
AM In: 08:06 (6 minutes late)
AM Out: 12:02
PM In: 12:06
PM Out: 18:05
Late: 6 min
Total Hours: 9.9 hrs
Current Accredited: 7h 54m (474 minutes)
```

**Issue:** Should be 8 hrs (480 minutes) with late covered by VL

**Calculation:**
- Late: 6 minutes = 0.0125 days
- Should deduct from VL: 0.0125 days
- Should credit: 480 minutes (8 hrs)

---

### May 07, 2026 (Thursday)
```
AM In: 09:00 (60 minutes late)
AM Out: 12:02
PM In: 12:07
PM Out: 19:07
Late: 1 hr (60 min)
Total Hours: 10 hrs
Current Accredited: 7 hrs (420 minutes)
```

**Issue:** Should be 8 hrs (480 minutes) with late covered by VL

**Calculation:**
- Late: 60 minutes = 0.1250 days
- Should deduct from VL: 0.1250 days
- Should credit: 480 minutes (8 hrs)

---

## SQL Processing Script

```sql
-- ============================================
-- Process Jeremy Pogi's Late Deductions
-- Employee ID: 8 (jeremypogi@gmail.com)
-- ============================================

-- STEP 1: Find the attendance IDs for these dates
-- ============================================
SELECT 
    a.id AS attendance_id,
    a.date,
    a.am_in,
    ahl.id AS log_id,
    ahl.late_minutes,
    ahl.total_accredited_minutes AS current_accredited,
    ahl.late_deducted_from_leave
FROM attendance a
LEFT JOIN accredited_hours_log ahl ON ahl.attendance_id = a.id
WHERE a.employee_id = 8
AND a.date IN ('2026-05-05', '2026-05-07')
ORDER BY a.date;

-- Expected Output:
-- May 05: attendance_id=25, log_id=25, late_minutes=6, current_accredited=474
-- May 07: attendance_id=27, log_id=27, late_minutes=60, current_accredited=420

-- ============================================
-- STEP 2: Check Current VL Balance
-- ============================================
SELECT 
    leave_code,
    available_credits,
    used_credits
FROM leave_balances
WHERE employee_id = 8 
AND leave_code = 'VL' 
AND year = 2026;

-- Expected: available_credits = 7.95, used_credits = 0.00

-- ============================================
-- STEP 3: Process May 05, 2026 (6 minutes late)
-- ============================================

-- Set variables
SET @employee_id = 8;
SET @log_id_may05 = (SELECT ahl.id FROM accredited_hours_log ahl 
                     JOIN attendance a ON a.id = ahl.attendance_id 
                     WHERE a.employee_id = 8 AND a.date = '2026-05-05');
SET @attendance_id_may05 = (SELECT id FROM attendance WHERE employee_id = 8 AND date = '2026-05-05');
SET @late_minutes_may05 = 6;
SET @late_days_may05 = 0.0125;

-- Get current VL balance
SET @vl_balance_before = (SELECT available_credits FROM leave_balances 
                          WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026);

-- Update VL balance
UPDATE leave_balances
SET 
    used_credits = used_credits + @late_days_may05,
    available_credits = available_credits - @late_days_may05,
    updated_at = NOW()
WHERE employee_id = @employee_id 
AND leave_code = 'VL' 
AND year = 2026;

-- Create leave transaction
INSERT INTO leave_transactions (
    employee_id, leave_code, year, transaction_type,
    amount, balance_before, balance_after,
    reference_type, reference_id, transaction_date,
    processed_by, remarks, created_at, updated_at
) VALUES (
    @employee_id, 'VL', 2026, 'debit',
    -@late_days_may05, @vl_balance_before, 
    @vl_balance_before - @late_days_may05,
    'manual_adjustment', @log_id_may05, '2026-05-05',
    1, 'Late deduction: 6 minutes (0.0125 days) from attendance on 2026-05-05',
    NOW(), NOW()
);

-- Update accredited hours log - Credit full 8 hours
UPDATE accredited_hours_log
SET 
    total_accredited_minutes = 480,
    late_deducted_from_leave = 1,
    late_deduction_leave_type = 'VL',
    updated_at = NOW()
WHERE id = @log_id_may05;

-- Update attendance record
UPDATE attendance
SET 
    accredited_hours = 480,
    updated_at = NOW()
WHERE id = @attendance_id_may05;

SELECT 'May 05, 2026 processed - 6 min late covered by VL' AS result;

-- ============================================
-- STEP 4: Process May 07, 2026 (60 minutes late)
-- ============================================

-- Set variables
SET @log_id_may07 = (SELECT ahl.id FROM accredited_hours_log ahl 
                     JOIN attendance a ON a.id = ahl.attendance_id 
                     WHERE a.employee_id = 8 AND a.date = '2026-05-07');
SET @attendance_id_may07 = (SELECT id FROM attendance WHERE employee_id = 8 AND date = '2026-05-07');
SET @late_minutes_may07 = 60;
SET @late_days_may07 = 0.1250;

-- Get current VL balance (after first deduction)
SET @vl_balance_before = (SELECT available_credits FROM leave_balances 
                          WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026);

-- Update VL balance
UPDATE leave_balances
SET 
    used_credits = used_credits + @late_days_may07,
    available_credits = available_credits - @late_days_may07,
    updated_at = NOW()
WHERE employee_id = @employee_id 
AND leave_code = 'VL' 
AND year = 2026;

-- Create leave transaction
INSERT INTO leave_transactions (
    employee_id, leave_code, year, transaction_type,
    amount, balance_before, balance_after,
    reference_type, reference_id, transaction_date,
    processed_by, remarks, created_at, updated_at
) VALUES (
    @employee_id, 'VL', 2026, 'debit',
    -@late_days_may07, @vl_balance_before, 
    @vl_balance_before - @late_days_may07,
    'manual_adjustment', @log_id_may07, '2026-05-07',
    1, 'Late deduction: 60 minutes (0.1250 days) from attendance on 2026-05-07',
    NOW(), NOW()
);

-- Update accredited hours log - Credit full 8 hours
UPDATE accredited_hours_log
SET 
    total_accredited_minutes = 480,
    late_deducted_from_leave = 1,
    late_deduction_leave_type = 'VL',
    updated_at = NOW()
WHERE id = @log_id_may07;

-- Update attendance record
UPDATE attendance
SET 
    accredited_hours = 480,
    updated_at = NOW()
WHERE id = @attendance_id_may07;

SELECT 'May 07, 2026 processed - 60 min late covered by VL' AS result;

-- ============================================
-- STEP 5: Verify Results
-- ============================================

-- Check updated records
SELECT 
    'Updated Attendance Records' AS description,
    a.date,
    a.am_in,
    ahl.late_minutes,
    ahl.total_accredited_minutes AS accredited,
    ahl.late_deducted_from_leave,
    ahl.late_deduction_leave_type,
    a.accredited_hours
FROM attendance a
JOIN accredited_hours_log ahl ON ahl.attendance_id = a.id
WHERE a.employee_id = 8
AND a.date IN ('2026-05-05', '2026-05-07')
ORDER BY a.date;

-- Expected Output:
-- May 05: accredited=480, late_deducted_from_leave=1, late_deduction_leave_type='VL'
-- May 07: accredited=480, late_deducted_from_leave=1, late_deduction_leave_type='VL'

-- Check VL balance
SELECT 
    'Final VL Balance' AS description,
    available_credits,
    used_credits,
    CASE 
        WHEN ROUND(available_credits, 4) = 7.8125 THEN '✓ CORRECT'
        ELSE '✗ INCORRECT'
    END AS status
FROM leave_balances
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

-- Expected: available_credits = 7.8125, used_credits = 0.1375

-- Check leave transactions
SELECT 
    'Leave Transactions' AS description,
    id,
    amount,
    balance_before,
    balance_after,
    remarks,
    created_at
FROM leave_transactions
WHERE employee_id = 8 
AND reference_type = 'manual_adjustment'
AND remarks LIKE '%Late deduction%'
ORDER BY created_at DESC;

-- Expected: 2 transactions showing the deductions

-- ============================================
-- STEP 6: Summary
-- ============================================
SELECT 
    'Summary' AS section,
    'Total Late Minutes' AS metric,
    66 AS value,
    'minutes' AS unit
UNION ALL
SELECT 
    'Summary',
    'Total Days Deducted',
    0.1375,
    'days'
UNION ALL
SELECT 
    'Summary',
    'VL Balance Change',
    7.95 - (SELECT available_credits FROM leave_balances WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026),
    'days deducted'
UNION ALL
SELECT 
    'Summary',
    'Accredited Hours (Both Days)',
    16,
    'hours (2 × 8 hrs)';
```

---

## Expected Display After Processing

### May 05, 2026 (Tuesday)
```
Date: May 05, 2026
Day: Tuesday
AM In: 08:06
AM Out: 12:02
PM In: 12:06
PM Out: 18:05
Late: 6 min
Total Hours: 9.9 hrs
Accredited Hours: 8 hrs
                  ✓ Grace: PM
                  📋 From Log
                  ✓ Late Covered by VL
                  6 min late deducted (0.0125 days)
```

### May 07, 2026 (Thursday)
```
Date: May 07, 2026
Day: Thursday
AM In: 09:00
AM Out: 12:02
PM In: 12:07
PM Out: 19:07
Late: 1 hr
Total Hours: 10 hrs
Accredited Hours: 8 hrs
                  ✓ Grace: PM
                  📋 From Log
                  ✓ Late Covered by VL
                  60 min late deducted (0.1250 days)
```

---

## Leave Balance Changes

```
Before Processing:
VL: 7.95 days

After May 05 Processing:
VL: 7.9375 days (7.95 - 0.0125)

After May 07 Processing:
VL: 7.8125 days (7.9375 - 0.1250)

Total Deducted: 0.1375 days
```

---

## How to Run

1. **Save the SQL script** to a file: `process_jeremy_pogi_late.sql`

2. **Run the script**:
   ```bash
   mysql -u root -p primehrismagdalena < process_jeremy_pogi_late.sql
   ```

3. **Refresh the browser** and check Jeremy Pogi's Detailed DTR

4. **Verify the display** shows:
   - May 05: 8 hrs with "✓ Late Covered by VL"
   - May 07: 8 hrs with "✓ Late Covered by VL"

---

## Verification Checklist

- [ ] May 05 shows 8 hrs (not 7h 54m)
- [ ] May 05 shows "✓ Late Covered by VL"
- [ ] May 07 shows 8 hrs (not 7 hrs)
- [ ] May 07 shows "✓ Late Covered by VL"
- [ ] VL balance is 7.8125 days
- [ ] 2 leave transactions created
- [ ] Both logs have late_deducted_from_leave = 1

---

## Rollback (If Needed)

```sql
-- Rollback May 05
UPDATE leave_balances
SET used_credits = used_credits - 0.0125,
    available_credits = available_credits + 0.0125
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

DELETE FROM leave_transactions
WHERE employee_id = 8 AND remarks LIKE '%2026-05-05%';

UPDATE accredited_hours_log ahl
JOIN attendance a ON a.id = ahl.attendance_id
SET ahl.total_accredited_minutes = 474,
    ahl.late_deducted_from_leave = 0,
    ahl.late_deduction_leave_type = NULL
WHERE a.employee_id = 8 AND a.date = '2026-05-05';

UPDATE attendance
SET accredited_hours = 474
WHERE employee_id = 8 AND date = '2026-05-05';

-- Rollback May 07
UPDATE leave_balances
SET used_credits = used_credits - 0.1250,
    available_credits = available_credits + 0.1250
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

DELETE FROM leave_transactions
WHERE employee_id = 8 AND remarks LIKE '%2026-05-07%';

UPDATE accredited_hours_log ahl
JOIN attendance a ON a.id = ahl.attendance_id
SET ahl.total_accredited_minutes = 420,
    ahl.late_deducted_from_leave = 0,
    ahl.late_deduction_leave_type = NULL
WHERE a.employee_id = 8 AND a.date = '2026-05-07';

UPDATE attendance
SET accredited_hours = 420
WHERE employee_id = 8 AND date = '2026-05-07';
```
