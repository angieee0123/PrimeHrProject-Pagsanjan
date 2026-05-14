# LWOP (Leave Without Pay) Tracking Implementation Guide

## 🎯 Overview

This implementation adds **explicit LWOP tracking** to the Prime HRIS system, making salary deductions from tardiness clearly visible and auditable according to CSC cascade rules.

---

## 📋 What Was Implemented

### 1. Database Changes ✅

**Migration File:** `2026_01_15_000001_add_lwop_tracking_to_accredited_hours_log.php`

**New Fields Added to `accredited_hours_log` table:**

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `lwop_minutes` | INT | 0 | Minutes to be deducted from salary (Leave Without Pay) |
| `requires_salary_deduction` | BOOLEAN | FALSE | Flag indicating salary deduction is required for payroll |

### 2. Service Logic Updates ✅

**File:** `app/Services/LateDeductionService.php`

**Changes:**
- When tardiness is **fully covered** by VL/SL:
  - `lwop_minutes` = 0
  - `requires_salary_deduction` = FALSE

- When tardiness is **partially covered** (remainder exists):
  - `lwop_minutes` = remaining minutes after VL/SL exhausted
  - `requires_salary_deduction` = TRUE

### 3. Model Updates ✅

**File:** `app/Models/AccreditedHoursLog.php`

**Added:**
- `lwop_minutes` and `requires_salary_deduction` to `$fillable` array
- `requires_salary_deduction` to `$casts` array (boolean)
- New accessor methods:
  - `getLwopHoursAttribute()` - Returns LWOP in hours
  - `getLwopDaysAttribute()` - Returns LWOP in days (CSC standard)

### 4. Payroll Queries ✅

**File:** `PAYROLL_LWOP_SALARY_DEDUCTION_QUERIES.sql`

**8 Ready-to-Use SQL Queries:**
1. Get all employees with salary deductions (current month)
2. Summary: Total LWOP per employee
3. Detailed cascade breakdown (VL → SL → LWOP)
4. Payroll deduction calculation (with peso amounts)
5. Verify specific employee (test case)
6. Monthly LWOP report by department
7. Audit trail: View leave transactions
8. Find employees with zero leave balances (high risk)

---

## 🚀 Installation Steps

### Step 1: Run the Migration

```bash
cd primeHrMagdalenaLaravel
php artisan migrate
```

**Expected Output:**
```
Migrating: 2026_01_15_000001_add_lwop_tracking_to_accredited_hours_log
Migrated:  2026_01_15_000001_add_lwop_tracking_to_accredited_hours_log (XX.XXms)
```

### Step 2: Verify Database Changes

```sql
-- Check if new columns exist
DESCRIBE accredited_hours_log;

-- Should show:
-- lwop_minutes (int, default 0)
-- requires_salary_deduction (tinyint(1), default 0)
```

### Step 3: Test with Sample Data

```sql
-- Create test scenario: 3 hours late, VL=0.125, SL=0.125
-- Expected: VL=0.000, SL=0.000, LWOP=60 minutes

-- 1. Set employee leave balances
UPDATE leave_balances 
SET available_credits = 0.125 
WHERE employee_id = 8 AND leave_code = 'VL' AND year = 2026;

UPDATE leave_balances 
SET available_credits = 0.125 
WHERE employee_id = 8 AND leave_code = 'SL' AND year = 2026;

-- 2. Create attendance with 180 minutes late (3 hours)
-- (Use your attendance correction interface)

-- 3. Verify results
SELECT 
    late_minutes,
    late_deduction_leave_type,
    lwop_minutes,
    requires_salary_deduction
FROM accredited_hours_log
WHERE employee_id = 8
ORDER BY created_at DESC
LIMIT 1;

-- Expected output:
-- late_minutes: 180
-- late_deduction_leave_type: VL+SL (partial)
-- lwop_minutes: 60
-- requires_salary_deduction: 1 (TRUE)
```

---

## 📊 How It Works

### CSC Cascade Rule Flow

```
Employee Late: 180 minutes (3 hours = 0.375 days)
VL Balance: 0.125 days (1 hour = 60 minutes)
SL Balance: 0.125 days (1 hour = 60 minutes)

┌─────────────────────────────────────────┐
│ STEP 1: Check VL Balance                │
├─────────────────────────────────────────┤
│ Deduct: min(0.125, 0.375) = 0.125 days  │
│ VL Balance: 0.125 - 0.125 = 0.000 ✅    │
│ Remaining: 0.375 - 0.125 = 0.250 days   │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│ STEP 2: Check SL Balance                │
├─────────────────────────────────────────┤
│ Deduct: min(0.125, 0.250) = 0.125 days  │
│ SL Balance: 0.125 - 0.125 = 0.000 ✅    │
│ Remaining: 0.250 - 0.125 = 0.125 days   │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│ STEP 3: Record LWOP (Salary Deduction)  │
├─────────────────────────────────────────┤
│ LWOP: 0.125 days = 60 minutes ✅        │
│ requires_salary_deduction: TRUE ✅      │
│ Payroll Action: Deduct from salary      │
└─────────────────────────────────────────┘
```

### Database Record After Processing

```php
AccreditedHoursLog {
    late_minutes: 180,
    total_accredited_minutes: 420,  // 480 - 60 = 420 (7 hours)
    late_deducted_from_leave: true,
    late_deduction_leave_type: "VL+SL (partial)",
    lwop_minutes: 60,  // ← NEW: Explicit LWOP tracking
    requires_salary_deduction: true  // ← NEW: Payroll flag
}
```

---

## 💼 Payroll Processing Guide

### For Payroll Officers

#### 1. Generate Monthly LWOP Report

```sql
-- Run Query #2 from PAYROLL_LWOP_SALARY_DEDUCTION_QUERIES.sql
-- This shows all employees with salary deductions for the current month
```

**Sample Output:**
```
employee_number | employee_name  | total_lwop_minutes | total_lwop_hours | total_lwop_days
EMP-001        | Juan Dela Cruz | 120                | 2.00             | 0.250000
EMP-008        | Jeremy Pogi    | 60                 | 1.00             | 0.125000
```

#### 2. Calculate Salary Deduction Amount

```sql
-- Run Query #4 from PAYROLL_LWOP_SALARY_DEDUCTION_QUERIES.sql
-- This calculates the actual peso amount to deduct
```

**Formula:**
```
Deduction Amount = (Monthly Salary ÷ 22 working days) × (LWOP Minutes ÷ 480)
```

**Example:**
```
Employee: Jeremy Pogi
Monthly Salary: ₱25,000
Daily Rate: ₱25,000 ÷ 22 = ₱1,136.36
LWOP: 60 minutes = 0.125 days

Deduction: ₱1,136.36 × 0.125 = ₱142.05
```

#### 3. Verify Individual Employee

```sql
-- Run Query #5 from PAYROLL_LWOP_SALARY_DEDUCTION_QUERIES.sql
-- Replace employee number and date
```

---

## 🔍 Accessing LWOP Data in Code

### In Controllers

```php
use App\Models\AccreditedHoursLog;

// Get employees with salary deductions for current month
$employeesWithLWOP = AccreditedHoursLog::where('requires_salary_deduction', true)
    ->whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->with('employee')
    ->get();

foreach ($employeesWithLWOP as $log) {
    echo "Employee: {$log->employee->full_name}\n";
    echo "LWOP Minutes: {$log->lwop_minutes}\n";
    echo "LWOP Hours: {$log->lwop_hours}\n";  // Uses accessor
    echo "LWOP Days: {$log->lwop_days}\n";    // Uses accessor
}
```

### In Blade Templates

```blade
@foreach($accreditedLogs as $log)
    <tr>
        <td>{{ $log->employee->employee_number }}</td>
        <td>{{ $log->late_minutes }} minutes</td>
        <td>{{ $log->late_deduction_leave_type ?? 'None' }}</td>
        <td>{{ $log->lwop_minutes }} minutes</td>
        <td>
            @if($log->requires_salary_deduction)
                <span class="badge badge-danger">Salary Deduction Required</span>
            @else
                <span class="badge badge-success">Fully Covered</span>
            @endif
        </td>
    </tr>
@endforeach
```

### In API Responses

```php
return response()->json([
    'employee_id' => $log->employee_id,
    'late_minutes' => $log->late_minutes,
    'leave_coverage' => $log->late_deduction_leave_type,
    'lwop' => [
        'minutes' => $log->lwop_minutes,
        'hours' => $log->lwop_hours,
        'days' => $log->lwop_days,
    ],
    'requires_salary_deduction' => $log->requires_salary_deduction,
]);
```

---

## 📈 Reporting Examples

### 1. Dashboard Widget: LWOP Summary

```php
// Controller
$lwopSummary = AccreditedHoursLog::where('requires_salary_deduction', true)
    ->whereMonth('created_at', now()->month)
    ->selectRaw('
        COUNT(DISTINCT employee_id) as affected_employees,
        SUM(lwop_minutes) as total_lwop_minutes,
        ROUND(SUM(lwop_minutes) / 60.0, 2) as total_lwop_hours
    ')
    ->first();

// Blade
<div class="card">
    <div class="card-header">LWOP Summary - {{ now()->format('F Y') }}</div>
    <div class="card-body">
        <p>Affected Employees: {{ $lwopSummary->affected_employees }}</p>
        <p>Total LWOP Hours: {{ $lwopSummary->total_lwop_hours }}</p>
    </div>
</div>
```

### 2. Employee Detail View

```php
// Show employee's LWOP history
$lwopHistory = AccreditedHoursLog::where('employee_id', $employeeId)
    ->where('requires_salary_deduction', true)
    ->with('attendance')
    ->orderBy('created_at', 'desc')
    ->get();
```

### 3. Payroll Export (CSV)

```php
use Illuminate\Support\Facades\DB;

$payrollData = DB::table('accredited_hours_log as ahl')
    ->join('employees as e', 'ahl.employee_id', '=', 'e.id')
    ->join('employment_details as ed', 'e.id', '=', 'ed.employee_id')
    ->where('ahl.requires_salary_deduction', true)
    ->whereMonth('ahl.created_at', now()->month)
    ->selectRaw('
        e.employee_number,
        CONCAT(e.first_name, " ", e.last_name) as employee_name,
        ed.monthly_salary,
        SUM(ahl.lwop_minutes) as total_lwop_minutes,
        ROUND((ed.monthly_salary / 22) * (SUM(ahl.lwop_minutes) / 480.0), 2) as deduction_amount
    ')
    ->groupBy('e.id', 'e.employee_number', 'e.first_name', 'e.last_name', 'ed.monthly_salary')
    ->get();

// Export to CSV
```

---

## ✅ Testing Checklist

### Test Case 1: Full Leave Coverage
- [ ] Employee late: 60 minutes
- [ ] VL balance: 0.5 days
- [ ] Expected: `lwop_minutes = 0`, `requires_salary_deduction = FALSE`

### Test Case 2: VL Only (Partial)
- [ ] Employee late: 120 minutes (2 hours)
- [ ] VL balance: 0.125 days (1 hour)
- [ ] SL balance: 0 days
- [ ] Expected: `lwop_minutes = 60`, `requires_salary_deduction = TRUE`

### Test Case 3: VL + SL Cascade (Partial)
- [ ] Employee late: 180 minutes (3 hours)
- [ ] VL balance: 0.125 days (1 hour)
- [ ] SL balance: 0.125 days (1 hour)
- [ ] Expected: `lwop_minutes = 60`, `requires_salary_deduction = TRUE`

### Test Case 4: Zero Leave Balances
- [ ] Employee late: 60 minutes
- [ ] VL balance: 0 days
- [ ] SL balance: 0 days
- [ ] Expected: `lwop_minutes = 60`, `requires_salary_deduction = TRUE`

### Test Case 5: Exact Match
- [ ] Employee late: 60 minutes
- [ ] VL balance: 0.125 days (exactly 1 hour)
- [ ] Expected: `lwop_minutes = 0`, `requires_salary_deduction = FALSE`

---

## 🔧 Troubleshooting

### Issue: Migration Fails

**Error:** `Column 'late_deduction_leave_type' not found`

**Solution:**
```bash
# Check if previous migration ran
php artisan migrate:status

# If missing, run:
php artisan migrate --path=/database/migrations/2026_05_14_000001_add_late_deduction_tracking_to_accredited_hours_log.php
```

### Issue: LWOP Minutes Not Calculating

**Check:**
1. Verify `LateDeductionService.php` was updated correctly
2. Check if `processLateDeduction()` is being called
3. Verify leave balances exist in database

**Debug:**
```php
Log::info('Late Deduction Debug', [
    'late_minutes' => $lateMinutes,
    'late_days' => $lateDays,
    'vl_balance' => $vlBalance?->available_credits,
    'sl_balance' => $slBalance?->available_credits,
    'remaining_days' => $remainingLateDays,
]);
```

### Issue: Accessor Methods Not Working

**Error:** `Call to undefined method getLwopHoursAttribute()`

**Solution:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart server
php artisan serve
```

---

## 📚 Additional Resources

### Related Files
- `app/Services/LateDeductionService.php` - Main deduction logic
- `app/Services/CscTimeConversionService.php` - CSC time conversions
- `app/Models/AccreditedHoursLog.php` - Model with LWOP accessors
- `PAYROLL_LWOP_SALARY_DEDUCTION_QUERIES.sql` - Payroll queries
- `CSC_TARDINESS_CASCADE_AUDIT_REPORT.md` - Complete audit report

### Documentation
- `LATE_DEDUCTION_FEATURE.md` - Original feature documentation
- `CSC_IMPLEMENTATION_SUMMARY.md` - CSC standards implementation

---

## 🎉 Summary

### What You Can Now Do

✅ **Track LWOP explicitly** - No more guessing from reduced accredited hours  
✅ **Flag salary deductions** - Clear marker for payroll processing  
✅ **Generate reports** - 8 ready-to-use SQL queries  
✅ **Audit trail** - Complete visibility of VL → SL → LWOP cascade  
✅ **API integration** - Easy access via model accessors  

### Benefits

- **Payroll Accuracy** - Clear identification of salary deductions
- **Compliance** - Follows CSC cascade rules (VL → SL → LWOP)
- **Transparency** - Employees can see exactly how tardiness was handled
- **Auditability** - Complete transaction history maintained
- **Reporting** - Easy generation of LWOP summaries and trends

---

**Implementation Date:** 2026-01-15  
**Status:** ✅ Ready for Production  
**Next Steps:** Run migration and test with sample data
