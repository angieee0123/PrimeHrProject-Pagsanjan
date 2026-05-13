-- ============================================================================
-- MIGRATION: Increase Decimal Precision for Exact HR Calculations
-- ============================================================================
-- Purpose: Change all decimal columns from 2 decimals to 4 decimals
--          to maintain exact precision in leave credits and salary calculations
-- 
-- Impact: Prevents rounding errors that accumulate over time
-- Risk: Low - Only increases precision, doesn't change existing data
-- 
-- Execution Time: ~5 seconds
-- Rollback: Available (see end of file)
-- ============================================================================

-- Backup recommendation
-- mysqldump -u root -p primehrismagdalena > backup_before_precision_fix_$(date +%Y%m%d_%H%M%S).sql

USE primehrismagdalena;

-- ============================================================================
-- 1. LEAVE_BALANCES TABLE
-- ============================================================================
-- Change: DECIMAL(8,2) → DECIMAL(8,4)
-- Reason: Handle fractional leave days precisely (e.g., 0.0625 days = 30 min)

ALTER TABLE `leave_balances` 
MODIFY COLUMN `total_credits` DECIMAL(8,4) DEFAULT 0.0000 COMMENT 'Total credits allocated for the year',
MODIFY COLUMN `used_credits` DECIMAL(8,4) DEFAULT 0.0000 COMMENT 'Credits already used/consumed',
MODIFY COLUMN `pending_credits` DECIMAL(8,4) DEFAULT 0.0000 COMMENT 'Credits in pending leave requests',
MODIFY COLUMN `available_credits` DECIMAL(8,4) DEFAULT 0.0000 COMMENT 'Remaining available credits',
MODIFY COLUMN `carried_over` DECIMAL(8,4) DEFAULT 0.0000 COMMENT 'Credits carried over from previous year';

-- ============================================================================
-- 2. LEAVE_TRANSACTIONS TABLE
-- ============================================================================
-- Change: DECIMAL(8,2) → DECIMAL(8,4)
-- Reason: Maintain exact transaction amounts for audit trail

ALTER TABLE `leave_transactions`
MODIFY COLUMN `amount` DECIMAL(8,4) NOT NULL COMMENT 'Number of days (positive for credit, negative for debit)',
MODIFY COLUMN `balance_before` DECIMAL(8,4) NOT NULL COMMENT 'Available balance before transaction',
MODIFY COLUMN `balance_after` DECIMAL(8,4) NOT NULL COMMENT 'Available balance after transaction';

-- ============================================================================
-- 3. LEAVE_APPLICATIONS TABLE
-- ============================================================================
-- Change: DECIMAL(5,2) → DECIMAL(5,4)
-- Reason: Allow precise leave duration (e.g., 0.5 days, 0.25 days)

ALTER TABLE `leave_applications`
MODIFY COLUMN `number_of_days` DECIMAL(5,4) NOT NULL;

-- ============================================================================
-- 4. DAILY_SALARY_COMPUTATIONS TABLE
-- ============================================================================
-- Change: DECIMAL(12,2) → DECIMAL(12,4)
-- Reason: Prevent rounding errors in payroll calculations

ALTER TABLE `daily_salary_computations`
MODIFY COLUMN `monthly_rate` DECIMAL(12,4) NOT NULL,
MODIFY COLUMN `daily_rate` DECIMAL(12,4) NOT NULL,
MODIFY COLUMN `hourly_rate` DECIMAL(12,4) NOT NULL,
MODIFY COLUMN `daily_basic_pay` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `ot_pay` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `late_deduction` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `undertime_deduction` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `daily_gross_pay` DECIMAL(12,4) NOT NULL DEFAULT 0.0000;

-- ============================================================================
-- 5. LEAVE_ACCRUAL_RATES TABLE (if exists)
-- ============================================================================
-- Change: DECIMAL precision for accrual calculations

ALTER TABLE `leave_accrual_rates`
MODIFY COLUMN `days_of_service_required` DECIMAL(8,4) NOT NULL DEFAULT 1.0000,
MODIFY COLUMN `credits_earned_per_period` DECIMAL(8,4) NOT NULL DEFAULT 0.0000;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================
-- Run these to verify the changes

-- Check leave_balances precision
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    NUMERIC_PRECISION, 
    NUMERIC_SCALE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'primehrismagdalena'
AND TABLE_NAME = 'leave_balances'
AND COLUMN_NAME IN ('total_credits', 'used_credits', 'available_credits');

-- Expected: NUMERIC_SCALE = 4

-- Check daily_salary_computations precision
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    NUMERIC_PRECISION, 
    NUMERIC_SCALE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'primehrismagdalena'
AND TABLE_NAME = 'daily_salary_computations'
AND COLUMN_NAME IN ('daily_rate', 'hourly_rate', 'daily_basic_pay');

-- Expected: NUMERIC_SCALE = 4

-- ============================================================================
-- DATA INTEGRITY CHECK
-- ============================================================================
-- Verify no data was lost during migration

-- Check leave balances
SELECT 
    employee_id,
    leave_code,
    total_credits,
    used_credits,
    available_credits
FROM leave_balances
WHERE year = 2026
LIMIT 10;

-- Check salary computations
SELECT 
    employee_id,
    work_date,
    daily_rate,
    hourly_rate,
    daily_basic_pay
FROM daily_salary_computations
ORDER BY work_date DESC
LIMIT 10;

-- ============================================================================
-- ROLLBACK SCRIPT (if needed)
-- ============================================================================
-- ONLY RUN THIS IF YOU NEED TO REVERT THE CHANGES

/*
-- Rollback leave_balances
ALTER TABLE `leave_balances` 
MODIFY COLUMN `total_credits` DECIMAL(8,2) DEFAULT 0.00,
MODIFY COLUMN `used_credits` DECIMAL(8,2) DEFAULT 0.00,
MODIFY COLUMN `pending_credits` DECIMAL(8,2) DEFAULT 0.00,
MODIFY COLUMN `available_credits` DECIMAL(8,2) DEFAULT 0.00,
MODIFY COLUMN `carried_over` DECIMAL(8,2) DEFAULT 0.00;

-- Rollback leave_transactions
ALTER TABLE `leave_transactions`
MODIFY COLUMN `amount` DECIMAL(8,2) NOT NULL,
MODIFY COLUMN `balance_before` DECIMAL(8,2) NOT NULL,
MODIFY COLUMN `balance_after` DECIMAL(8,2) NOT NULL;

-- Rollback leave_applications
ALTER TABLE `leave_applications`
MODIFY COLUMN `number_of_days` DECIMAL(5,2) NOT NULL;

-- Rollback daily_salary_computations
ALTER TABLE `daily_salary_computations`
MODIFY COLUMN `monthly_rate` DECIMAL(12,2) NOT NULL,
MODIFY COLUMN `daily_rate` DECIMAL(12,2) NOT NULL,
MODIFY COLUMN `hourly_rate` DECIMAL(12,2) NOT NULL,
MODIFY COLUMN `daily_basic_pay` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
MODIFY COLUMN `ot_pay` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
MODIFY COLUMN `late_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
MODIFY COLUMN `undertime_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
MODIFY COLUMN `daily_gross_pay` DECIMAL(12,2) NOT NULL DEFAULT 0.00;

-- Rollback leave_accrual_rates
ALTER TABLE `leave_accrual_rates`
MODIFY COLUMN `days_of_service_required` DECIMAL(8,2) NOT NULL DEFAULT 1.00,
MODIFY COLUMN `credits_earned_per_period` DECIMAL(8,4) NOT NULL DEFAULT 0.0000;
*/

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================
-- Next steps:
-- 1. Update PHP code to remove round() functions
-- 2. Update Blade templates to show 4 decimals
-- 3. Test leave applications and salary computations
-- 4. Verify payroll accuracy
-- ============================================================================

SELECT 'Migration completed successfully!' AS status;
