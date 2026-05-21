-- =====================================================
-- PAYROLL LWOP (Leave Without Pay) SALARY DEDUCTION QUERIES
-- CSC Cascade Rule: VL → SL → LWOP/Salary Deduction
-- =====================================================

-- =====================================================
-- 1. GET ALL EMPLOYEES WITH SALARY DEDUCTIONS FOR CURRENT MONTH
-- =====================================================
-- Use this query to identify employees who need salary deductions
-- due to tardiness that exceeded their VL and SL balances

SELECT 
    e.id AS employee_id,
    e.employee_number,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    ed.designation_id,
    d.designation_name,
    ahl.attendance_id,
    a.date AS attendance_date,
    ahl.late_minutes AS total_late_minutes,
    ahl.lwop_minutes AS salary_deduction_minutes,
    ROUND(ahl.lwop_minutes / 60.0, 2) AS salary_deduction_hours,
    ROUND(ahl.lwop_minutes / 480.0, 6) AS salary_deduction_days,
    ahl.late_deduction_leave_type AS leave_coverage,
    ahl.created_at AS processed_date
FROM accredited_hours_log ahl
INNER JOIN employees e ON ahl.employee_id = e.id
INNER JOIN employment_details ed ON e.id = ed.employee_id
INNER JOIN designations d ON ed.designation_id = d.id
INNER JOIN attendances a ON ahl.attendance_id = a.id
WHERE ahl.requires_salary_deduction = TRUE
  AND MONTH(a.date) = MONTH(CURRENT_DATE())
  AND YEAR(a.date) = YEAR(CURRENT_DATE())
ORDER BY e.employee_number, a.date;


-- =====================================================
-- 2. SUMMARY: TOTAL LWOP DEDUCTIONS PER EMPLOYEE (CURRENT MONTH)
-- =====================================================
-- Use this for payroll summary report

SELECT 
    e.id AS employee_id,
    e.employee_number,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    COUNT(ahl.id) AS total_lwop_instances,
    SUM(ahl.lwop_minutes) AS total_lwop_minutes,
    ROUND(SUM(ahl.lwop_minutes) / 60.0, 2) AS total_lwop_hours,
    ROUND(SUM(ahl.lwop_minutes) / 480.0, 6) AS total_lwop_days,
    GROUP_CONCAT(
        CONCAT(DATE_FORMAT(a.date, '%Y-%m-%d'), ' (', ahl.lwop_minutes, ' min)')
        ORDER BY a.date
        SEPARATOR '; '
    ) AS lwop_breakdown
FROM accredited_hours_log ahl
INNER JOIN employees e ON ahl.employee_id = e.id
INNER JOIN attendances a ON ahl.attendance_id = a.id
WHERE ahl.requires_salary_deduction = TRUE
  AND MONTH(a.date) = MONTH(CURRENT_DATE())
  AND YEAR(a.date) = YEAR(CURRENT_DATE())
GROUP BY e.id, e.employee_number, e.first_name, e.last_name
ORDER BY total_lwop_minutes DESC;


-- =====================================================
-- 3. DETAILED BREAKDOWN: SHOW CASCADE DEDUCTION FLOW
-- =====================================================
-- Shows how tardiness was deducted (VL → SL → LWOP)

SELECT 
    e.employee_number,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    a.date AS attendance_date,
    ahl.late_minutes AS total_late_minutes,
    ahl.late_deduction_leave_type AS leave_used,
    ahl.lwop_minutes AS lwop_minutes,
    CASE 
        WHEN ahl.lwop_minutes = 0 THEN 'Fully covered by leave'
        WHEN ahl.late_deducted_from_leave = TRUE THEN 'Partially covered by leave'
        ELSE 'No leave coverage - Full LWOP'
    END AS coverage_status,
    -- Show the cascade breakdown
    CASE 
        WHEN ahl.late_deduction_leave_type LIKE '%VL+SL%' THEN 'Used VL then SL, remainder = LWOP'
        WHEN ahl.late_deduction_leave_type LIKE '%VL%' THEN 'Used VL only, remainder = LWOP'
        WHEN ahl.late_deduction_leave_type LIKE '%SL%' THEN 'Used SL only, remainder = LWOP'
        ELSE 'No leave used - Full LWOP'
    END AS cascade_flow
FROM accredited_hours_log ahl
INNER JOIN employees e ON ahl.employee_id = e.id
INNER JOIN attendances a ON ahl.attendance_id = a.id
WHERE ahl.late_minutes > 0
  AND MONTH(a.date) = MONTH(CURRENT_DATE())
  AND YEAR(a.date) = YEAR(CURRENT_DATE())
ORDER BY e.employee_number, a.date;


-- =====================================================
-- 4. PAYROLL DEDUCTION CALCULATION (WITH DAILY RATE)
-- =====================================================
-- Calculate actual peso amount to deduct from salary
-- Assumes: 1 month = 22 working days, Daily rate = Monthly salary / 22

SELECT 
    e.employee_number,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    ed.monthly_salary,
    ROUND(ed.monthly_salary / 22, 2) AS daily_rate,
    ROUND((ed.monthly_salary / 22) / 8, 2) AS hourly_rate,
    SUM(ahl.lwop_minutes) AS total_lwop_minutes,
    ROUND(SUM(ahl.lwop_minutes) / 480.0, 6) AS total_lwop_days,
    ROUND(
        (ed.monthly_salary / 22) * (SUM(ahl.lwop_minutes) / 480.0),
        2
    ) AS total_salary_deduction_amount
FROM accredited_hours_log ahl
INNER JOIN employees e ON ahl.employee_id = e.id
INNER JOIN employment_details ed ON e.id = ed.employee_id
WHERE ahl.requires_salary_deduction = TRUE
  AND MONTH(ahl.created_at) = MONTH(CURRENT_DATE())
  AND YEAR(ahl.created_at) = YEAR(CURRENT_DATE())
GROUP BY e.id, e.employee_number, e.first_name, e.last_name, ed.monthly_salary
ORDER BY total_salary_deduction_amount DESC;


-- =====================================================
-- 5. VERIFY SPECIFIC EMPLOYEE (TEST CASE)
-- =====================================================
-- Use this to verify the test case: 3 hours late, VL=0.125, SL=0.125
-- Expected: VL=0.000, SL=0.000, LWOP=0.125 days (60 minutes)

SELECT 
    e.employee_number,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    a.date AS attendance_date,
    
    -- Late time breakdown
    ahl.late_minutes AS total_late_minutes,
    ROUND(ahl.late_minutes / 60.0, 2) AS late_hours,
    ROUND(ahl.late_minutes / 480.0, 6) AS late_days,
    
    -- Leave coverage
    ahl.late_deducted_from_leave AS covered_by_leave,
    ahl.late_deduction_leave_type AS leave_types_used,
    
    -- LWOP (salary deduction)
    ahl.lwop_minutes AS lwop_minutes,
    ROUND(ahl.lwop_minutes / 60.0, 2) AS lwop_hours,
    ROUND(ahl.lwop_minutes / 480.0, 6) AS lwop_days,
    ahl.requires_salary_deduction AS needs_salary_deduction,
    
    -- Current leave balances
    (SELECT available_credits FROM leave_balances 
     WHERE employee_id = e.id AND leave_code = 'VL' AND year = YEAR(a.date)) AS current_vl_balance,
    (SELECT available_credits FROM leave_balances 
     WHERE employee_id = e.id AND leave_code = 'SL' AND year = YEAR(a.date)) AS current_sl_balance
     
FROM accredited_hours_log ahl
INNER JOIN employees e ON ahl.employee_id = e.id
INNER JOIN attendances a ON ahl.attendance_id = a.id
WHERE e.employee_number = 'EMP-XXX'  -- Replace with actual employee number
  AND a.date = '2026-01-XX'           -- Replace with actual date
ORDER BY a.date DESC;


-- =====================================================
-- 6. MONTHLY LWOP REPORT FOR ALL DEPARTMENTS
-- =====================================================

SELECT 
    dept.department_name,
    COUNT(DISTINCT e.id) AS employees_with_lwop,
    COUNT(ahl.id) AS total_lwop_instances,
    SUM(ahl.lwop_minutes) AS total_lwop_minutes,
    ROUND(SUM(ahl.lwop_minutes) / 60.0, 2) AS total_lwop_hours,
    ROUND(SUM(ahl.lwop_minutes) / 480.0, 6) AS total_lwop_days
FROM accredited_hours_log ahl
INNER JOIN employees e ON ahl.employee_id = e.id
INNER JOIN employment_details ed ON e.id = ed.employee_id
INNER JOIN designations d ON ed.designation_id = d.id
INNER JOIN departments dept ON d.department_id = dept.id
WHERE ahl.requires_salary_deduction = TRUE
  AND MONTH(ahl.created_at) = MONTH(CURRENT_DATE())
  AND YEAR(ahl.created_at) = YEAR(CURRENT_DATE())
GROUP BY dept.id, dept.department_name
ORDER BY total_lwop_minutes DESC;


-- =====================================================
-- 7. AUDIT TRAIL: VIEW LEAVE TRANSACTIONS FOR LATE DEDUCTIONS
-- =====================================================
-- Shows the complete audit trail of VL/SL deductions

SELECT 
    lt.id AS transaction_id,
    e.employee_number,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    lt.leave_code,
    lt.transaction_type,
    lt.amount AS deducted_days,
    lt.balance_before,
    lt.balance_after,
    lt.transaction_date,
    lt.remarks,
    ahl.late_minutes,
    ahl.lwop_minutes
FROM leave_transactions lt
INNER JOIN employees e ON lt.employee_id = e.id
LEFT JOIN accredited_hours_log ahl ON lt.reference_id = ahl.id AND lt.reference_type = 'manual_adjustment'
WHERE lt.reference_type = 'manual_adjustment'
  AND lt.remarks LIKE '%Late deduction%'
  AND MONTH(lt.transaction_date) = MONTH(CURRENT_DATE())
  AND YEAR(lt.transaction_date) = YEAR(CURRENT_DATE())
ORDER BY e.employee_number, lt.transaction_date, lt.leave_code;


-- =====================================================
-- 8. FIND EMPLOYEES WITH ZERO LEAVE BALANCES (HIGH RISK)
-- =====================================================
-- Employees with zero VL and SL will have full LWOP on next tardiness

SELECT 
    e.employee_number,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    vl.available_credits AS vl_balance,
    sl.available_credits AS sl_balance,
    CASE 
        WHEN vl.available_credits = 0 AND sl.available_credits = 0 THEN 'HIGH RISK - No leave coverage'
        WHEN vl.available_credits < 0.125 AND sl.available_credits < 0.125 THEN 'MEDIUM RISK - Less than 1 hour coverage'
        ELSE 'LOW RISK'
    END AS risk_level
FROM employees e
LEFT JOIN leave_balances vl ON e.id = vl.employee_id AND vl.leave_code = 'VL' AND vl.year = YEAR(CURRENT_DATE())
LEFT JOIN leave_balances sl ON e.id = sl.employee_id AND sl.leave_code = 'SL' AND sl.year = YEAR(CURRENT_DATE())
WHERE (vl.available_credits = 0 AND sl.available_credits = 0)
   OR (vl.available_credits < 0.125 AND sl.available_credits < 0.125)
ORDER BY vl.available_credits + sl.available_credits ASC;


-- =====================================================
-- NOTES FOR PAYROLL PROCESSING:
-- =====================================================
-- 
-- 1. CSC Standard Conversion:
--    - 1 work day = 8 hours = 480 minutes
--    - 1 hour = 0.125 days = 60 minutes
--    - 1 minute = 0.002083 days
--
-- 2. Cascade Priority:
--    - First: Deduct from VL (Vacation Leave)
--    - Second: If VL exhausted, deduct from SL (Sick Leave)
--    - Third: If both exhausted, remainder = LWOP (salary deduction)
--
-- 3. Database Fields:
--    - lwop_minutes: Minutes to deduct from salary
--    - requires_salary_deduction: TRUE if LWOP exists
--    - late_deduction_leave_type: Shows which leaves were used (VL, SL, VL+SL)
--
-- 4. Salary Deduction Formula:
--    Deduction Amount = (Monthly Salary / 22) × (LWOP Minutes / 480)
--
-- 5. Example Test Case:
--    Late: 180 minutes (3 hours)
--    VL: 0.125 days (60 min) → Deducted, Balance = 0.000
--    SL: 0.125 days (60 min) → Deducted, Balance = 0.000
--    LWOP: 60 minutes (1 hour) → Salary deduction required
--
