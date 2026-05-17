-- Generate salary computation for Juan Dela Cruz (employee_id = 9)
-- Monthly Rate: 14,308.00
-- Period: January 1, 2026 to May 17, 2026

-- Step 1: Insert daily salary computations for all attendance records
INSERT INTO daily_salary_computations (
    employee_id,
    accredited_hours_log_id,
    work_date,
    monthly_rate,
    daily_rate,
    hourly_rate,
    daily_basic_pay,
    ot_pay,
    late_deduction,
    undertime_deduction,
    daily_gross_pay,
    created_at,
    updated_at
)
SELECT 
    ahl.employee_id,
    ahl.id,
    a.date,
    14308.00 AS monthly_rate,
    ROUND(14308.00 / 22, 2) AS daily_rate,
    ROUND(14308.00 / 22 / 8, 2) AS hourly_rate,
    ROUND((14308.00 / 22 / 8) * (ahl.total_accredited_minutes / 60), 2) AS daily_basic_pay,
    ROUND((14308.00 / 22 / 8) * 1.25 * (ahl.ot_minutes / 60), 2) AS ot_pay,
    ROUND((14308.00 / 22 / 8) * (ahl.late_minutes / 60), 2) AS late_deduction,
    ROUND((14308.00 / 22 / 8) * (ahl.undertime_minutes / 60), 2) AS undertime_deduction,
    ROUND((14308.00 / 22 / 8) * (ahl.total_accredited_minutes / 60), 2) + 
    ROUND((14308.00 / 22 / 8) * 1.25 * (ahl.ot_minutes / 60), 2) -
    ROUND((14308.00 / 22 / 8) * (ahl.late_minutes / 60), 2) -
    ROUND((14308.00 / 22 / 8) * (ahl.undertime_minutes / 60), 2) AS daily_gross_pay,
    NOW(),
    NOW()
FROM accredited_hours_log ahl
INNER JOIN attendance a ON a.id = ahl.attendance_id
WHERE ahl.employee_id = 9
AND NOT EXISTS (
    SELECT 1 FROM daily_salary_computations 
    WHERE accredited_hours_log_id = ahl.id
);

-- Step 2: Insert monthly salary computation summary for January 2026
INSERT INTO salary_computations (
    employee_id,
    period_start,
    period_end,
    payroll_type,
    monthly_rate,
    daily_rate,
    hourly_rate,
    total_days_present,
    total_days_absent,
    total_hours_worked,
    total_accredited_hours,
    total_late_minutes,
    total_undertime_minutes,
    total_ot_minutes,
    basic_pay,
    ot_pay,
    late_deduction,
    undertime_deduction,
    other_deductions,
    gross_pay,
    net_pay,
    status,
    created_at,
    updated_at
)
SELECT 
    9 AS employee_id,
    '2026-01-01' AS period_start,
    '2026-01-31' AS period_end,
    'monthly' AS payroll_type,
    14308.00 AS monthly_rate,
    ROUND(14308.00 / 22, 2) AS daily_rate,
    ROUND(14308.00 / 22 / 8, 2) AS hourly_rate,
    COUNT(DISTINCT dsc.work_date) AS total_days_present,
    0 AS total_days_absent,
    SUM(ahl.total_actual_minutes / 60) AS total_hours_worked,
    SUM(ahl.total_accredited_minutes / 60) AS total_accredited_hours,
    SUM(ahl.late_minutes) AS total_late_minutes,
    SUM(ahl.undertime_minutes) AS total_undertime_minutes,
    SUM(ahl.ot_minutes) AS total_ot_minutes,
    SUM(dsc.daily_basic_pay) AS basic_pay,
    SUM(dsc.ot_pay) AS ot_pay,
    SUM(dsc.late_deduction) AS late_deduction,
    SUM(dsc.undertime_deduction) AS undertime_deduction,
    0.00 AS other_deductions,
    SUM(dsc.daily_gross_pay) AS gross_pay,
    SUM(dsc.daily_gross_pay) AS net_pay,
    'draft' AS status,
    NOW(),
    NOW()
FROM daily_salary_computations dsc
INNER JOIN accredited_hours_log ahl ON ahl.id = dsc.accredited_hours_log_id
WHERE dsc.employee_id = 9
AND dsc.work_date BETWEEN '2026-01-01' AND '2026-01-31';

-- Step 3: Insert monthly salary computation summary for February 2026
INSERT INTO salary_computations (
    employee_id, period_start, period_end, payroll_type, monthly_rate, daily_rate, hourly_rate,
    total_days_present, total_days_absent, total_hours_worked, total_accredited_hours,
    total_late_minutes, total_undertime_minutes, total_ot_minutes,
    basic_pay, ot_pay, late_deduction, undertime_deduction, other_deductions,
    gross_pay, net_pay, status, created_at, updated_at
)
SELECT 
    9, '2026-02-01', '2026-02-28', 'monthly', 14308.00, ROUND(14308.00 / 22, 2), ROUND(14308.00 / 22 / 8, 2),
    COUNT(DISTINCT dsc.work_date), 0, SUM(ahl.total_actual_minutes / 60), SUM(ahl.total_accredited_minutes / 60),
    SUM(ahl.late_minutes), SUM(ahl.undertime_minutes), SUM(ahl.ot_minutes),
    SUM(dsc.daily_basic_pay), SUM(dsc.ot_pay), SUM(dsc.late_deduction), SUM(dsc.undertime_deduction), 0.00,
    SUM(dsc.daily_gross_pay), SUM(dsc.daily_gross_pay), 'draft', NOW(), NOW()
FROM daily_salary_computations dsc
INNER JOIN accredited_hours_log ahl ON ahl.id = dsc.accredited_hours_log_id
WHERE dsc.employee_id = 9 AND dsc.work_date BETWEEN '2026-02-01' AND '2026-02-28';

-- Step 4: Insert monthly salary computation summary for March 2026
INSERT INTO salary_computations (
    employee_id, period_start, period_end, payroll_type, monthly_rate, daily_rate, hourly_rate,
    total_days_present, total_days_absent, total_hours_worked, total_accredited_hours,
    total_late_minutes, total_undertime_minutes, total_ot_minutes,
    basic_pay, ot_pay, late_deduction, undertime_deduction, other_deductions,
    gross_pay, net_pay, status, created_at, updated_at
)
SELECT 
    9, '2026-03-01', '2026-03-31', 'monthly', 14308.00, ROUND(14308.00 / 22, 2), ROUND(14308.00 / 22 / 8, 2),
    COUNT(DISTINCT dsc.work_date), 0, SUM(ahl.total_actual_minutes / 60), SUM(ahl.total_accredited_minutes / 60),
    SUM(ahl.late_minutes), SUM(ahl.undertime_minutes), SUM(ahl.ot_minutes),
    SUM(dsc.daily_basic_pay), SUM(dsc.ot_pay), SUM(dsc.late_deduction), SUM(dsc.undertime_deduction), 0.00,
    SUM(dsc.daily_gross_pay), SUM(dsc.daily_gross_pay), 'draft', NOW(), NOW()
FROM daily_salary_computations dsc
INNER JOIN accredited_hours_log ahl ON ahl.id = dsc.accredited_hours_log_id
WHERE dsc.employee_id = 9 AND dsc.work_date BETWEEN '2026-03-01' AND '2026-03-31';

-- Step 5: Insert monthly salary computation summary for April 2026
INSERT INTO salary_computations (
    employee_id, period_start, period_end, payroll_type, monthly_rate, daily_rate, hourly_rate,
    total_days_present, total_days_absent, total_hours_worked, total_accredited_hours,
    total_late_minutes, total_undertime_minutes, total_ot_minutes,
    basic_pay, ot_pay, late_deduction, undertime_deduction, other_deductions,
    gross_pay, net_pay, status, created_at, updated_at
)
SELECT 
    9, '2026-04-01', '2026-04-30', 'monthly', 14308.00, ROUND(14308.00 / 22, 2), ROUND(14308.00 / 22 / 8, 2),
    COUNT(DISTINCT dsc.work_date), 0, SUM(ahl.total_actual_minutes / 60), SUM(ahl.total_accredited_minutes / 60),
    SUM(ahl.late_minutes), SUM(ahl.undertime_minutes), SUM(ahl.ot_minutes),
    SUM(dsc.daily_basic_pay), SUM(dsc.ot_pay), SUM(dsc.late_deduction), SUM(dsc.undertime_deduction), 0.00,
    SUM(dsc.daily_gross_pay), SUM(dsc.daily_gross_pay), 'draft', NOW(), NOW()
FROM daily_salary_computations dsc
INNER JOIN accredited_hours_log ahl ON ahl.id = dsc.accredited_hours_log_id
WHERE dsc.employee_id = 9 AND dsc.work_date BETWEEN '2026-04-01' AND '2026-04-30';

-- Step 6: Insert monthly salary computation summary for May 2026 (partial)
INSERT INTO salary_computations (
    employee_id, period_start, period_end, payroll_type, monthly_rate, daily_rate, hourly_rate,
    total_days_present, total_days_absent, total_hours_worked, total_accredited_hours,
    total_late_minutes, total_undertime_minutes, total_ot_minutes,
    basic_pay, ot_pay, late_deduction, undertime_deduction, other_deductions,
    gross_pay, net_pay, status, created_at, updated_at
)
SELECT 
    9, '2026-05-01', '2026-05-17', 'monthly', 14308.00, ROUND(14308.00 / 22, 2), ROUND(14308.00 / 22 / 8, 2),
    COUNT(DISTINCT dsc.work_date), 0, SUM(ahl.total_actual_minutes / 60), SUM(ahl.total_accredited_minutes / 60),
    SUM(ahl.late_minutes), SUM(ahl.undertime_minutes), SUM(ahl.ot_minutes),
    SUM(dsc.daily_basic_pay), SUM(dsc.ot_pay), SUM(dsc.late_deduction), SUM(dsc.undertime_deduction), 0.00,
    SUM(dsc.daily_gross_pay), SUM(dsc.daily_gross_pay), 'draft', NOW(), NOW()
FROM daily_salary_computations dsc
INNER JOIN accredited_hours_log ahl ON ahl.id = dsc.accredited_hours_log_id
WHERE dsc.employee_id = 9 AND dsc.work_date BETWEEN '2026-05-01' AND '2026-05-17';
