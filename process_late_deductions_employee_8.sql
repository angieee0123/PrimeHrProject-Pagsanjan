-- ============================================
-- Manual Late Deduction Processing Script
-- Employee: jeremypogi@gmail.com (ID: 8)
-- ============================================
-- This script manually processes the pending late deductions
-- Use this if the automatic processing hasn't been triggered
-- ============================================

-- STEP 1: Verify Current State
-- ============================================
SELECT 'CURRENT STATE - Before Processing' AS step;

-- Check current leave balances
SELECT 
    'Current VL Balance' AS description,
    available_credits AS balance,
    used_credits AS used
FROM leave_balances
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

SELECT 
    'Current SL Balance' AS description,
    available_credits AS balance,
    used_credits AS used
FROM leave_balances
WHERE employee_id = 8 AND leave_code = 'SL' AND year = 2026;

-- Check pending late deductions
SELECT 
    'Pending Late Deductions' AS description,
    id AS log_id,
    attendance_id,
    late_minutes,
    ROUND(late_minutes / 480, 4) AS late_days,
    total_accredited_minutes AS current_accredited,
    late_deducted_from_leave AS is_processed
FROM accredited_hours_log
WHERE employee_id = 8 
AND late_minutes > 0
AND late_deducted_from_leave = 0;

-- ============================================
-- STEP 2: Process Log ID 25 (6 minutes late)
-- ============================================
SELECT 'PROCESSING LOG ID 25 - 6 minutes late' AS step;

-- Calculate deduction
SET @log_id_25 = 25;
SET @attendance_id_25 = 25;
SET @late_minutes_25 = 6;
SET @late_days_25 = 0.0125; -- 6 / 480
SET @employee_id = 8;
SET @year = 2026;
SET @processed_by = 1; -- Admin user ID
SET @transaction_date = CURDATE();

-- Get current VL balance
SET @vl_balance_before_25 = (
    SELECT available_credits 
    FROM leave_balances 
    WHERE employee_id = @employee_id 
    AND leave_code = 'VL' 
    AND year = @year
);

-- Update VL balance
UPDATE leave_balances
SET 
    used_credits = used_credits + @late_days_25,
    available_credits = available_credits - @late_days_25,
    updated_at = NOW()
WHERE employee_id = @employee_id 
AND leave_code = 'VL' 
AND year = @year;

-- Get new VL balance
SET @vl_balance_after_25 = (
    SELECT available_credits 
    FROM leave_balances 
    WHERE employee_id = @employee_id 
    AND leave_code = 'VL' 
    AND year = @year
);

-- Create leave transaction
INSERT INTO leave_transactions (
    employee_id, leave_code, year, transaction_type,
    amount, balance_before, balance_after,
    reference_type, reference_id, transaction_date,
    processed_by, remarks, created_at, updated_at
) VALUES (
    @employee_id, 'VL', @year, 'debit',
    -@late_days_25, @vl_balance_before_25, @vl_balance_after_25,
    'manual_adjustment', @log_id_25, @transaction_date,
    @processed_by, 
    CONCAT('Late deduction: ', @late_minutes_25, ' minutes (', @late_days_25, ' days) from attendance on ', @transaction_date),
    NOW(), NOW()
);

-- Update accredited hours log - Credit full 8 hours (480 minutes)
UPDATE accredited_hours_log
SET 
    total_accredited_minutes = 480,
    late_deducted_from_leave = 1,
    late_deduction_leave_type = 'VL',
    updated_at = NOW()
WHERE id = @log_id_25;

-- Update attendance record
UPDATE attendance
SET 
    accredited_hours = 480,
    updated_at = NOW()
WHERE id = @attendance_id_25;

SELECT 'Log ID 25 processed successfully' AS result;

-- ============================================
-- STEP 3: Process Log ID 27 (60 minutes late)
-- ============================================
SELECT 'PROCESSING LOG ID 27 - 60 minutes late' AS step;

-- Calculate deduction
SET @log_id_27 = 27;
SET @attendance_id_27 = 27;
SET @late_minutes_27 = 60;
SET @late_days_27 = 0.1250; -- 60 / 480

-- Get current VL balance (after first deduction)
SET @vl_balance_before_27 = (
    SELECT available_credits 
    FROM leave_balances 
    WHERE employee_id = @employee_id 
    AND leave_code = 'VL' 
    AND year = @year
);

-- Update VL balance
UPDATE leave_balances
SET 
    used_credits = used_credits + @late_days_27,
    available_credits = available_credits - @late_days_27,
    updated_at = NOW()
WHERE employee_id = @employee_id 
AND leave_code = 'VL' 
AND year = @year;

-- Get new VL balance
SET @vl_balance_after_27 = (
    SELECT available_credits 
    FROM leave_balances 
    WHERE employee_id = @employee_id 
    AND leave_code = 'VL' 
    AND year = @year
);

-- Create leave transaction
INSERT INTO leave_transactions (
    employee_id, leave_code, year, transaction_type,
    amount, balance_before, balance_after,
    reference_type, reference_id, transaction_date,
    processed_by, remarks, created_at, updated_at
) VALUES (
    @employee_id, 'VL', @year, 'debit',
    -@late_days_27, @vl_balance_before_27, @vl_balance_after_27,
    'manual_adjustment', @log_id_27, @transaction_date,
    @processed_by, 
    CONCAT('Late deduction: ', @late_minutes_27, ' minutes (', @late_days_27, ' days) from attendance on ', @transaction_date),
    NOW(), NOW()
);

-- Update accredited hours log - Credit full 8 hours (480 minutes)
UPDATE accredited_hours_log
SET 
    total_accredited_minutes = 480,
    late_deducted_from_leave = 1,
    late_deduction_leave_type = 'VL',
    updated_at = NOW()
WHERE id = @log_id_27;

-- Update attendance record
UPDATE attendance
SET 
    accredited_hours = 480,
    updated_at = NOW()
WHERE id = @attendance_id_27;

SELECT 'Log ID 27 processed successfully' AS result;

-- ============================================
-- STEP 4: Verify Final State
-- ============================================
SELECT 'FINAL STATE - After Processing' AS step;

-- Check updated leave balances
SELECT 
    'Final VL Balance' AS description,
    available_credits AS balance,
    used_credits AS used,
    CASE 
        WHEN available_credits = 7.8125 THEN '✓ CORRECT'
        ELSE '✗ INCORRECT'
    END AS status
FROM leave_balances
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

SELECT 
    'Final SL Balance' AS description,
    available_credits AS balance,
    used_credits AS used,
    CASE 
        WHEN available_credits = 9.20 THEN '✓ CORRECT'
        ELSE '✗ INCORRECT'
    END AS status
FROM leave_balances
WHERE employee_id = 8 AND leave_code = 'SL' AND year = 2026;

-- Check processed late deductions
SELECT 
    'Processed Late Deductions' AS description,
    id AS log_id,
    attendance_id,
    late_minutes,
    ROUND(late_minutes / 480, 4) AS late_days,
    total_accredited_minutes AS accredited,
    late_deducted_from_leave AS is_processed,
    late_deduction_leave_type AS leave_type
FROM accredited_hours_log
WHERE employee_id = 8 
AND late_minutes > 0
ORDER BY id;

-- Check leave transactions
SELECT 
    'Leave Transactions' AS description,
    id,
    leave_code,
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

-- ============================================
-- STEP 5: Summary Report
-- ============================================
SELECT 'SUMMARY REPORT' AS step;

SELECT 
    'Total Late Minutes' AS metric,
    SUM(late_minutes) AS value,
    'minutes' AS unit
FROM accredited_hours_log
WHERE employee_id = 8 AND late_minutes > 0;

SELECT 
    'Total Late Days Deducted' AS metric,
    ROUND(SUM(late_minutes) / 480, 4) AS value,
    'days' AS unit
FROM accredited_hours_log
WHERE employee_id = 8 AND late_minutes > 0;

SELECT 
    'VL Balance Change' AS metric,
    CONCAT(7.95, ' → ', available_credits, ' days') AS value,
    CONCAT('-', ROUND(7.95 - available_credits, 4), ' days') AS change
FROM leave_balances
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

SELECT 
    'Total Accredited Hours' AS metric,
    SUM(total_accredited_minutes) / 60 AS value,
    'hours' AS unit
FROM accredited_hours_log
WHERE employee_id = 8 AND late_minutes > 0;

-- ============================================
-- ROLLBACK SCRIPT (If needed)
-- ============================================
-- Uncomment and run this section if you need to undo the changes

/*
-- Rollback Log ID 25
UPDATE leave_balances
SET 
    used_credits = used_credits - 0.0125,
    available_credits = available_credits + 0.0125,
    updated_at = NOW()
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

DELETE FROM leave_transactions
WHERE employee_id = 8 
AND reference_type = 'manual_adjustment'
AND reference_id = 25;

UPDATE accredited_hours_log
SET 
    total_accredited_minutes = 474,
    late_deducted_from_leave = 0,
    late_deduction_leave_type = NULL,
    updated_at = NOW()
WHERE id = 25;

UPDATE attendance
SET accredited_hours = 474
WHERE id = 25;

-- Rollback Log ID 27
UPDATE leave_balances
SET 
    used_credits = used_credits - 0.1250,
    available_credits = available_credits + 0.1250,
    updated_at = NOW()
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

DELETE FROM leave_transactions
WHERE employee_id = 8 
AND reference_type = 'manual_adjustment'
AND reference_id = 27;

UPDATE accredited_hours_log
SET 
    total_accredited_minutes = 420,
    late_deducted_from_leave = 0,
    late_deduction_leave_type = NULL,
    updated_at = NOW()
WHERE id = 27;

UPDATE attendance
SET accredited_hours = 420
WHERE id = 27;

SELECT 'Rollback completed' AS result;
*/

-- ============================================
-- NOTES
-- ============================================
/*
Expected Results After Processing:

1. VL Balance:
   - Before: 7.95 days
   - After: 7.8125 days
   - Deducted: 0.1375 days (0.0125 + 0.1250)

2. Accredited Hours:
   - Log 25: 480 minutes (8 hrs) - was 474 minutes
   - Log 27: 480 minutes (8 hrs) - was 420 minutes

3. Leave Transactions:
   - 2 new transactions with 'Late deduction' remarks

4. Accredited Hours Log:
   - late_deducted_from_leave = 1 for both logs
   - late_deduction_leave_type = 'VL' for both logs

To verify in the application:
1. Login as admin
2. Go to Attendance > Detailed DTR for Jeremy Pogi
3. Check May 05 and May 07, 2026
4. Should show "✓ Late Covered by VL" in Accredited Hours column
5. Should show 8 hrs instead of 7h 54m and 7 hrs
*/
