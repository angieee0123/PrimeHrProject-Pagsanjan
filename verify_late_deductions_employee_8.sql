-- ============================================
-- Late Deduction Verification Script
-- Employee: jeremypogi@gmail.com (ID: 8)
-- ============================================

-- 1. Employee Information
SELECT 
    e.id,
    e.employee_id,
    CONCAT(e.first_name, ' ', e.middle_name, ' ', e.last_name) AS full_name,
    u.email,
    u.username
FROM employees e
JOIN users u ON u.employee_id = e.id
WHERE u.email = 'jeremypogi@gmail.com';

-- Expected Result:
-- id: 8, employee_id: 2024001, full_name: Jeremy Reyes Pogi, email: jeremypogi@gmail.com

-- ============================================
-- 2. Current Leave Balances
-- ============================================
SELECT 
    lb.leave_code,
    lt.leave_name,
    lb.year,
    lb.total_credits,
    lb.used_credits,
    lb.pending_credits,
    lb.available_credits,
    lb.carried_over,
    lb.updated_at
FROM leave_balances lb
JOIN leave_types_config lt ON lt.leave_code = lb.leave_code
WHERE lb.employee_id = 8 
AND lb.leave_code IN ('VL', 'SL')
AND lb.year = 2026
ORDER BY lb.leave_code;

-- Expected Current Results:
-- VL: total=7.95, used=0.00, available=7.95
-- SL: total=9.20, used=0.00, available=9.20

-- Expected After Deduction:
-- VL: total=7.95, used=0.1375, available=7.8125
-- SL: total=9.20, used=0.00, available=9.20

-- ============================================
-- 3. Accredited Hours Log with Late Minutes
-- ============================================
SELECT 
    ahl.id AS log_id,
    ahl.attendance_id,
    a.date AS attendance_date,
    ahl.late_minutes,
    ROUND(ahl.late_minutes / 480, 4) AS late_days,
    ahl.late_deducted_from_leave,
    ahl.late_deduction_leave_type,
    ahl.am_accredited_minutes,
    ahl.pm_accredited_minutes,
    ahl.total_accredited_minutes,
    ahl.created_at,
    ahl.updated_at
FROM accredited_hours_log ahl
JOIN attendance a ON a.id = ahl.attendance_id
WHERE ahl.employee_id = 8 
AND ahl.late_minutes > 0
ORDER BY ahl.created_at DESC;

-- Expected Results:
-- Log 25: 6 minutes (0.0125 days), late_deducted_from_leave = 0 or 1
-- Log 27: 60 minutes (0.125 days), late_deducted_from_leave = 0 or 1

-- ============================================
-- 4. Leave Transactions for Late Deductions
-- ============================================
SELECT 
    lt.id,
    lt.leave_code,
    lt.transaction_type,
    lt.amount,
    lt.balance_before,
    lt.balance_after,
    lt.reference_type,
    lt.reference_id,
    lt.transaction_date,
    lt.remarks,
    lt.created_at
FROM leave_transactions lt
WHERE lt.employee_id = 8 
AND lt.reference_type = 'manual_adjustment'
AND lt.remarks LIKE '%Late deduction%'
ORDER BY lt.created_at DESC;

-- Expected Results (if processed):
-- 2 transactions:
-- 1. VL deduction: -0.0125 days (6 minutes)
-- 2. VL deduction: -0.125 days (60 minutes)

-- ============================================
-- 5. All Leave Transactions for Employee 8
-- ============================================
SELECT 
    lt.id,
    lt.leave_code,
    lt.transaction_type,
    lt.amount,
    lt.balance_before,
    lt.balance_after,
    lt.reference_type,
    lt.transaction_date,
    lt.remarks,
    lt.created_at
FROM leave_transactions lt
WHERE lt.employee_id = 8 
ORDER BY lt.created_at DESC;

-- ============================================
-- 6. Attendance Records with Late
-- ============================================
SELECT 
    a.id,
    a.date,
    a.am_in,
    a.am_out,
    a.pm_in,
    a.pm_out,
    a.accredited_hours,
    ahl.late_minutes,
    ahl.late_deducted_from_leave,
    ahl.late_deduction_leave_type
FROM attendance a
LEFT JOIN accredited_hours_log ahl ON ahl.attendance_id = a.id
WHERE a.employee_id = 8
AND ahl.late_minutes > 0
ORDER BY a.date DESC;

-- ============================================
-- 7. Summary Statistics
-- ============================================
SELECT 
    COUNT(*) AS total_attendance_records,
    SUM(CASE WHEN ahl.late_minutes > 0 THEN 1 ELSE 0 END) AS records_with_late,
    SUM(ahl.late_minutes) AS total_late_minutes,
    ROUND(SUM(ahl.late_minutes) / 480, 4) AS total_late_days,
    SUM(CASE WHEN ahl.late_deducted_from_leave = 1 THEN 1 ELSE 0 END) AS deductions_processed,
    SUM(CASE WHEN ahl.late_deducted_from_leave = 0 AND ahl.late_minutes > 0 THEN 1 ELSE 0 END) AS deductions_pending
FROM accredited_hours_log ahl
WHERE ahl.employee_id = 8;

-- Expected Results:
-- total_attendance_records: 27
-- records_with_late: 2
-- total_late_minutes: 66
-- total_late_days: 0.1375
-- deductions_processed: 0 or 2
-- deductions_pending: 2 or 0

-- ============================================
-- 8. Verify Calculation Accuracy
-- ============================================
-- This query shows the expected vs actual deductions
SELECT 
    'Expected VL Balance' AS description,
    7.95 AS original_balance,
    0.1375 AS total_deduction,
    7.95 - 0.1375 AS expected_balance,
    (SELECT available_credits FROM leave_balances WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026) AS actual_balance,
    CASE 
        WHEN (SELECT available_credits FROM leave_balances WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026) = 7.8125 
        THEN '✓ CORRECT' 
        ELSE '✗ INCORRECT' 
    END AS status
UNION ALL
SELECT 
    'Expected SL Balance' AS description,
    9.20 AS original_balance,
    0.00 AS total_deduction,
    9.20 AS expected_balance,
    (SELECT available_credits FROM leave_balances WHERE employee_id = 8 AND leave_code = 'SL' AND year = 2026) AS actual_balance,
    CASE 
        WHEN (SELECT available_credits FROM leave_balances WHERE employee_id = 8 AND leave_code = 'SL' AND year = 2026) = 9.20 
        THEN '✓ CORRECT' 
        ELSE '✗ INCORRECT' 
    END AS status;

-- ============================================
-- 9. Check if Deductions Match Transactions
-- ============================================
SELECT 
    'Late Deductions' AS category,
    SUM(ahl.late_minutes) / 480 AS calculated_days,
    (SELECT COALESCE(SUM(ABS(amount)), 0) 
     FROM leave_transactions 
     WHERE employee_id = 8 
     AND reference_type = 'manual_adjustment' 
     AND remarks LIKE '%Late deduction%') AS transaction_days,
    CASE 
        WHEN SUM(ahl.late_minutes) / 480 = (SELECT COALESCE(SUM(ABS(amount)), 0) 
                                             FROM leave_transactions 
                                             WHERE employee_id = 8 
                                             AND reference_type = 'manual_adjustment' 
                                             AND remarks LIKE '%Late deduction%')
        THEN '✓ MATCH'
        ELSE '✗ MISMATCH'
    END AS status
FROM accredited_hours_log ahl
WHERE ahl.employee_id = 8 
AND ahl.late_minutes > 0;

-- ============================================
-- 10. Detailed Breakdown by Date
-- ============================================
SELECT 
    a.date,
    DAYNAME(a.date) AS day_name,
    a.am_in,
    a.am_out,
    a.pm_in,
    a.pm_out,
    ahl.late_minutes,
    ROUND(ahl.late_minutes / 480, 4) AS late_days,
    ahl.late_deducted_from_leave,
    ahl.late_deduction_leave_type,
    ahl.computation_notes
FROM attendance a
JOIN accredited_hours_log ahl ON ahl.attendance_id = a.id
WHERE a.employee_id = 8
AND ahl.late_minutes > 0
ORDER BY a.date;

-- ============================================
-- INTERPRETATION GUIDE
-- ============================================
/*
CORRECT SCENARIO (Deductions Processed):
- Query 2: VL available_credits = 7.8125, used_credits = 0.1375
- Query 3: late_deducted_from_leave = 1 for both records
- Query 4: Shows 2 transactions with late deduction remarks
- Query 7: deductions_processed = 2, deductions_pending = 0
- Query 8: Status = '✓ CORRECT' for both VL and SL
- Query 9: Status = '✓ MATCH'

INCORRECT SCENARIO (Deductions NOT Processed):
- Query 2: VL available_credits = 7.95, used_credits = 0.00
- Query 3: late_deducted_from_leave = 0 for both records
- Query 4: No results (no transactions)
- Query 7: deductions_processed = 0, deductions_pending = 2
- Query 8: Status = '✗ INCORRECT' for VL
- Query 9: Status = '✗ MISMATCH'

ACTION REQUIRED IF INCORRECT:
1. Run: php artisan leave:process-late-deductions --employee_id=8
2. Or manually trigger attendance correction for the affected dates
3. Check application logs for errors
*/
