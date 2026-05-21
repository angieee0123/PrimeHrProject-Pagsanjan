# DATABASE RELATIONSHIP ANALYSIS & BUGS FOUND

## Critical Bug Identified

### BUG #1: Missing Automatic Attendance Records for Approved Leaves

**Current Behavior:**
- Employee files leave in advance (e.g., May 15-19, 2026)
- Leave gets approved
- DetailedDTR shows "ON LEAVE" and counts as present ✓ (CORRECT)
- **BUT**: No attendance record is created in the `attendance` table ✗ (BUG)

**Evidence from Database:**
```sql
-- Leave Application (employee_id=9, Juan Dela Cruz)
LA-2026-0001: May 15-19, 2026 (3 days, BL - Bereavement Leave)
Status: approved

-- Attendance Table Check
SELECT * FROM attendance WHERE employee_id=9 AND date BETWEEN '2026-05-15' AND '2026-05-19';
-- Result: NO RECORDS FOUND
```

**Why This is a Problem:**
1. **RDBMS Integrity**: Breaks referential integrity - leave exists but no attendance record
2. **Salary Computation**: `daily_salary_computations` table depends on `accredited_hours_log`
3. **Accredited Hours Log**: Depends on `attendance` table
4. **Broken Chain**: Leave → ❌ No Attendance → ❌ No Accredited Log → ❌ No Salary Computation

## Database Relationship Map

```
┌─────────────────────────────────────────────────────────────────┐
│                    LEAVE & ATTENDANCE FLOW                       │
└─────────────────────────────────────────────────────────────────┘

employees (id)
    │
    ├──→ schedules (employee_id)
    │       │
    │       └──→ Defines: am_in, am_out, pm_in, pm_out
    │
    ├──→ leave_applications (employee_id)
    │       │
    │       ├──→ leave_types_config (leave_code)
    │       ├──→ users (filed_by, approved_by)
    │       │
    │       └──→ When APPROVED:
    │           ├──→ leave_balances (UPDATE: used_credits++)
    │           ├──→ leave_transactions (INSERT: debit transaction)
    │           └──→ ❌ BUG: Should create attendance records
    │
    ├──→ attendance (employee_id) ← MISSING FOR LEAVES!
    │       │
    │       └──→ accredited_hours_log (attendance_id)
    │               │
    │               ├──→ schedules (schedule_id)
    │               │
    │               └──→ daily_salary_computations (accredited_hours_log_id)
    │                       │
    │                       └──→ Used for payroll
    │
    └──→ leave_balances (employee_id)
            │
            └──→ leave_transactions (employee_id)
```

## Cascading Effects of the Bug

### 1. Attendance Table (PRIMARY BUG)
**Missing Records:**
- Employee on approved leave = NO attendance record
- Should have: `am_in='ON_LEAVE'`, `am_out='ON_LEAVE'`, etc.
- Should have: `accredited_hours=480` (8 hours)

### 2. Accredited Hours Log (SECONDARY BUG)
**Missing Records:**
- No attendance = No accredited_hours_log entry
- Should have: Full 8 hours credited (480 minutes)
- Should have: Reference to schedule_id
- Should have: Computation notes indicating "On approved leave"

### 3. Daily Salary Computations (TERTIARY BUG)
**Missing Records:**
- No accredited_hours_log = No daily_salary_computation
- Should have: Full daily_basic_pay
- Should have: No deductions
- Should have: Notes indicating "Paid leave"

### 4. Reporting Issues
**Broken Reports:**
- DTR Export: Shows "ON LEAVE" but no database record
- Payroll: Missing salary computation for leave days
- Audit Trail: Incomplete attendance history

## Solution: Automatic Attendance Record Creation

### When to Create Attendance Records

**Trigger Point:** When leave application status changes to 'approved'

**What to Create:**
1. **Attendance Records** (for each leave day)
   ```sql
   INSERT INTO attendance (
       employee_id, date, 
       am_in, am_out, pm_in, pm_out,
       accredited_hours, total_hours
   ) VALUES (
       {employee_id}, {leave_date},
       'ON_LEAVE', 'ON_LEAVE', 'ON_LEAVE', 'ON_LEAVE',
       480, 480  -- Full 8 hours
   );
   ```

2. **Accredited Hours Log** (for each attendance record)
   ```sql
   INSERT INTO accredited_hours_log (
       attendance_id, employee_id, schedule_id,
       am_accredited_minutes, pm_accredited_minutes,
       ot_minutes, late_minutes, undertime_minutes,
       total_accredited_minutes, total_actual_minutes,
       am_grace_applied, pm_grace_applied,
       computation_notes
   ) VALUES (
       {attendance_id}, {employee_id}, {schedule_id},
       240, 240,  -- Full AM and PM
       0, 0, 0,   -- No OT, late, or undertime
       480, 480,  -- Full 8 hours
       0, 0,      -- No grace needed
       'On approved leave: {leave_type} - {application_number}'
   );
   ```

3. **Daily Salary Computation** (for each accredited log)
   ```sql
   INSERT INTO daily_salary_computations (
       employee_id, accredited_hours_log_id, work_date,
       monthly_rate, daily_rate, hourly_rate,
       daily_basic_pay, ot_pay,
       late_deduction, undertime_deduction,
       daily_gross_pay, notes
   ) VALUES (
       {employee_id}, {log_id}, {leave_date},
       {monthly_rate}, {daily_rate}, {hourly_rate},
       {daily_rate}, 0.00,  -- Full daily pay
       0.00, 0.00,          -- No deductions
       {daily_rate},        -- Full gross pay
       'Paid leave: {leave_type} - {application_number}'
   );
   ```

## Implementation Requirements

### 1. LeaveApplication Model Observer
Create: `app/Observers/LeaveApplicationObserver.php`

**Method:** `updated()`
- Detect status change from 'pending' → 'approved'
- Get employee schedule
- Loop through leave dates (start_date to end_date)
- Skip weekends (Saturday, Sunday)
- Create attendance, accredited_hours_log, daily_salary_computation

### 2. Database Transaction
- Wrap all inserts in DB transaction
- Rollback if any step fails
- Ensure data consistency

### 3. Validation
- Check if attendance already exists (avoid duplicates)
- Verify employee has active schedule
- Verify employee has employment_details for salary computation

## Additional Bugs Found

### BUG #2: Orphaned Leave Transactions
**Issue:** Leave transactions exist without corresponding attendance records
**Impact:** Incomplete audit trail

### BUG #3: Salary Computation Gap
**Issue:** Employees on leave don't get daily_salary_computations
**Impact:** Payroll calculations incomplete

### BUG #4: DTR Export Inconsistency
**Issue:** Export shows "ON LEAVE" but database has no record
**Impact:** Data integrity violation

## Testing Checklist

After implementing the fix:

- [ ] File leave application
- [ ] Approve leave application
- [ ] Verify attendance records created for each leave day
- [ ] Verify accredited_hours_log entries created
- [ ] Verify daily_salary_computations entries created
- [ ] Check leave_balances updated correctly
- [ ] Check leave_transactions recorded
- [ ] Verify DTR shows "ON LEAVE" with database backing
- [ ] Export DTR and verify data consistency
- [ ] Check payroll computation includes leave days
- [ ] Test with multi-day leave (e.g., 5 days)
- [ ] Test with single-day leave
- [ ] Test with leave spanning weekends (should skip Sat/Sun)

## Database Integrity Rules

### Rule 1: Every Working Day Must Have Attendance Record
- Present: Normal time logs
- Absent: NULL time logs
- On Leave: 'ON_LEAVE' markers
- Holiday: 'HOLIDAY' markers (future enhancement)

### Rule 2: Every Attendance Must Have Accredited Hours Log
- Tracks actual vs accredited hours
- Required for salary computation
- Maintains audit trail

### Rule 3: Every Accredited Hours Log Must Have Salary Computation
- Ensures payroll completeness
- Tracks daily earnings
- Supports monthly salary aggregation

## Recommended Database Constraints

```sql
-- Add constraint to ensure attendance exists for approved leaves
-- (Implement via application logic, not DB constraint)

-- Add index for faster leave-attendance lookups
CREATE INDEX idx_attendance_employee_date 
ON attendance(employee_id, date);

-- Add index for leave application date ranges
CREATE INDEX idx_leave_app_dates 
ON leave_applications(employee_id, start_date, end_date, status);
```

## Priority: CRITICAL
This bug affects:
- ✗ Data integrity
- ✗ Payroll accuracy
- ✗ Audit compliance
- ✗ Reporting reliability

**Recommendation:** Implement immediately before production deployment.
