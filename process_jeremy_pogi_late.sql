-- ============================================
-- Process Jeremy Pogi's Late Deductions
-- Employee: jeremypogi@gmail.com (ID: 8)
-- Dates: May 05 & May 07, 2026
-- ============================================

-- Display current state
SELECT '========================================' AS '';
SELECT 'STEP 1: Current State' AS '';
SELECT '========================================' AS '';

SELECT 
    a.date,
    a.am_in,
    ahl.late_minutes,
    ahl.total_accredited_minutes AS current_accredited,
    ahl.late_deducted_from_leave AS is_processed
FROM attendance a
LEFT JOIN accredited_hours_log ahl ON ahl.attendance_id = a.id
WHERE a.employee_id = 8
AND a.date IN ('2026-05-05', '2026-05-07')
ORDER BY a.date;

SELECT 
    leave_code,
    available_credits,
    used_credits
FROM leave_balances
WHERE employee_id = 8 
AND leave_code = 'VL' 
AND year = 2026;

-- ============================================
-- Process May 05, 2026 (6 minutes late)
-- ============================================
SELECT '========================================' AS '';
SELECT 'STEP 2: Processing May 05, 2026' AS '';
SELECT '========================================' AS '';

-- Set variables
SET @employee_id = 8;
SET @log_id_may05 = (SELECT ahl.id FROM accredited_hours_log ahl 
                     JOIN attendance a ON a.id = ahl.attendance_id 
                     WHERE a.employee_id = 8 AND a.date = '2026-05-05');
SET @attendance_id_may05 = (SELECT id FROM attendance WHERE employee_id = 8 AND date = '2026-05-05');
SET @late_minutes_may05 = 6;
SET @late_days_may05 = 0.0125;

-- Get current VL balance
SET @vl_balance_before_may05 = (SELECT available_credits FROM leave_balances 
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
    -@late_days_may05, @vl_balance_before_may05, 
    @vl_balance_before_may05 - @late_days_may05,
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

SELECT 'May 05, 2026 processed successfully' AS result;
SELECT CONCAT('VL Balance: ', @vl_balance_before_may05, ' → ', @vl_balance_before_may05 - @late_days_may05, ' days') AS change;

-- ============================================
-- Process May 07, 2026 (60 minutes late)
-- ============================================
SELECT '========================================' AS '';
SELECT 'STEP 3: Processing May 07, 2026' AS '';
SELECT '========================================' AS '';

-- Set variables
SET @log_id_may07 = (SELECT ahl.id FROM accredited_hours_log ahl 
                     JOIN attendance a ON a.id = ahl.attendance_id 
                     WHERE a.employee_id = 8 AND a.date = '2026-05-07');
SET @attendance_id_may07 = (SELECT id FROM attendance WHERE employee_id = 8 AND date = '2026-05-07');
SET @late_minutes_may07 = 60;
SET @late_days_may07 = 0.1250;

-- Get current VL balance (after first deduction)
SET @vl_balance_before_may07 = (SELECT available_credits FROM leave_balances 
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
    -@late_days_may07, @vl_balance_before_may07, 
    @vl_balance_before_may07 - @late_days_may07,
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

SELECT 'May 07, 2026 processed successfully' AS result;
SELECT CONCAT('VL Balance: ', @vl_balance_before_may07, ' → ', @vl_balance_before_may07 - @late_days_may07, ' days') AS change;

-- ============================================
-- Verify Results
-- ============================================
SELECT '========================================' AS '';
SELECT 'STEP 4: Verification' AS '';
SELECT '========================================' AS '';

SELECT 
    'Updated Attendance Records' AS description,
    a.date,
    a.am_in,
    ahl.late_minutes,
    ahl.total_accredited_minutes AS accredited,
    ahl.late_deducted_from_leave,
    ahl.late_deduction_leave_type
FROM attendance a
JOIN accredited_hours_log ahl ON ahl.attendance_id = a.id
WHERE a.employee_id = 8
AND a.date IN ('2026-05-05', '2026-05-07')
ORDER BY a.date;

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

SELECT 
    'Leave Transactions' AS description,
    DATE(created_at) AS date,
    amount AS days_deducted,
    balance_before,
    balance_after,
    remarks
FROM leave_transactions
WHERE employee_id = 8 
AND reference_type = 'manual_adjustment'
AND remarks LIKE '%Late deduction%'
ORDER BY created_at DESC
LIMIT 2;

-- ============================================
-- Summary
-- ============================================
SELECT '========================================' AS '';
SELECT 'STEP 5: Summary' AS '';
SELECT '========================================' AS '';

SELECT 
    'Total Late Minutes' AS metric,
    66 AS value,
    'minutes' AS unit
UNION ALL
SELECT 
    'Total Days Deducted',
    0.1375,
    'days'
UNION ALL
SELECT 
    'VL Balance Change',
    7.95 - (SELECT available_credits FROM leave_balances WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026),
    'days deducted'
UNION ALL
SELECT 
    'Accredited Hours (Both Days)',
    16,
    'hours (2 × 8 hrs)';

SELECT '========================================' AS '';
SELECT 'Processing Complete!' AS '';
SELECT 'Refresh the browser to see the updated DTR' AS '';
SELECT '========================================' AS '';
