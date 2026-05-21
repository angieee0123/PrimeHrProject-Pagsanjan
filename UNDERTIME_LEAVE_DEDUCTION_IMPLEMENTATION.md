# Undertime Leave Deduction Implementation

## Overview
This document explains how to implement the same leave credit deduction logic for **UNDERTIME** that currently exists for **LATE** arrivals.

---

## Database Changes

### Migration Created
**File**: `2026_05_17_000001_add_undertime_leave_deduction_tracking.php`

### New Fields Added to `accredited_hours_log` table:

```sql
undertime_deducted_from_leave TINYINT(1) DEFAULT 0
undertime_deduction_leave_type VARCHAR(50) NULL
```

### Run Migration
```bash
cd primeHrMagdalenaLaravel
php artisan migrate
```

---

## Current vs New Structure

### BEFORE (Late Only):
```
accredited_hours_log:
├── late_minutes
├── late_deducted_from_leave ✅
├── late_deduction_leave_type ✅
├── undertime_minutes
└── (no undertime leave tracking) ❌
```

### AFTER (Late + Undertime):
```
accredited_hours_log:
├── late_minutes
├── late_deducted_from_leave ✅
├── late_deduction_leave_type ✅
├── undertime_minutes
├── undertime_deducted_from_leave ✅ NEW
└── undertime_deduction_leave_type ✅ NEW
```

---

## Implementation Steps

### 1. Update Model Cast (AccreditedHoursLog.php)

**Location**: `app/Models/AccreditedHoursLog.php`

Add to the `$casts` array:
```php
protected $casts = [
    // ... existing casts ...
    'late_deducted_from_leave' => 'boolean',
    'undertime_deducted_from_leave' => 'boolean',  // ADD THIS
];
```

---

### 2. Update Attendance Correction Logic

**Location**: `app/Http/Controllers/Admin/AttendanceCorrectionController.php`

Find the method that handles late deductions and duplicate the logic for undertime.

#### Current Late Logic (Reference):
```php
// Check if late should be deducted from leave
if ($request->has('deduct_late_from_leave') && $request->deduct_late_from_leave) {
    $leaveType = $request->late_leave_type; // 'VL' or 'SL'
    
    // Convert minutes to days
    $lateDays = $lateMinutes / 480; // 480 minutes = 8 hours = 1 day
    
    // Deduct from leave balance
    $this->deductFromLeaveBalance($employee, $leaveType, $lateDays, $attendance->id);
    
    // Update accredited hours log
    $accreditedLog->late_deducted_from_leave = true;
    $accreditedLog->late_deduction_leave_type = $leaveType . ' (full)';
}
```

#### New Undertime Logic (Add This):
```php
// Check if undertime should be deducted from leave
if ($request->has('deduct_undertime_from_leave') && $request->deduct_undertime_from_leave) {
    $leaveType = $request->undertime_leave_type; // 'VL' or 'SL'
    
    // Convert minutes to days
    $undertimeDays = $undertimeMinutes / 480; // 480 minutes = 8 hours = 1 day
    
    // Deduct from leave balance
    $this->deductFromLeaveBalance($employee, $leaveType, $undertimeDays, $attendance->id);
    
    // Update accredited hours log
    $accreditedLog->undertime_deducted_from_leave = true;
    $accreditedLog->undertime_deduction_leave_type = $leaveType . ' (full)';
}
```

---

### 3. Update Frontend Modal (Attendance Correction Form)

**Location**: `resources/views/admin/attendance/index.blade.php` or similar

#### Add Undertime Leave Deduction Checkbox:

```html
<!-- LATE DEDUCTION (Existing) -->
<div class="form-check mb-3">
    <input type="checkbox" 
           class="form-check-input" 
           id="deduct_late_from_leave" 
           name="deduct_late_from_leave">
    <label class="form-check-label" for="deduct_late_from_leave">
        Deduct late from leave credits
    </label>
</div>

<div id="late_leave_type_selection" style="display: none;">
    <select name="late_leave_type" class="form-select">
        <option value="VL">Vacation Leave (VL)</option>
        <option value="SL">Sick Leave (SL)</option>
    </select>
</div>

<!-- UNDERTIME DEDUCTION (NEW - Add This) -->
<div class="form-check mb-3">
    <input type="checkbox" 
           class="form-check-input" 
           id="deduct_undertime_from_leave" 
           name="deduct_undertime_from_leave">
    <label class="form-check-label" for="deduct_undertime_from_leave">
        Deduct undertime from leave credits
    </label>
</div>

<div id="undertime_leave_type_selection" style="display: none;">
    <select name="undertime_leave_type" class="form-select">
        <option value="VL">Vacation Leave (VL)</option>
        <option value="SL">Sick Leave (SL)</option>
    </select>
</div>
```

#### Add JavaScript Toggle:

```javascript
// Late checkbox toggle (existing)
document.getElementById('deduct_late_from_leave').addEventListener('change', function() {
    document.getElementById('late_leave_type_selection').style.display = 
        this.checked ? 'block' : 'none';
});

// Undertime checkbox toggle (NEW - Add This)
document.getElementById('deduct_undertime_from_leave').addEventListener('change', function() {
    document.getElementById('undertime_leave_type_selection').style.display = 
        this.checked ? 'block' : 'none';
});
```

---

### 4. Update DTR Display

**Location**: DTR modal or detailed attendance view

#### Show Undertime Deduction Info:

```php
@if($log->undertime_deducted_from_leave)
    <div class="alert alert-info">
        <i class="fas fa-clock"></i>
        Undertime ({{ $log->undertime_minutes }} mins) covered by 
        <strong>{{ $log->undertime_deduction_leave_type }}</strong>
    </div>
@endif
```

---

## Testing Checklist

### ✅ Database
- [ ] Run migration successfully
- [ ] Verify new columns exist in `accredited_hours_log`
- [ ] Check column types and defaults

### ✅ Backend
- [ ] Model casts updated
- [ ] Controller logic handles undertime deduction
- [ ] Leave balance deduction works correctly
- [ ] Leave transaction recorded properly

### ✅ Frontend
- [ ] Checkbox appears in attendance correction modal
- [ ] Leave type dropdown shows/hides correctly
- [ ] Form submission includes new fields
- [ ] DTR display shows undertime deduction info

### ✅ Business Logic
- [ ] Undertime minutes converted to days correctly (÷ 480)
- [ ] Leave balance decreases appropriately
- [ ] Salary deduction NOT applied when leave is used
- [ ] Transaction history shows undertime deduction

---

## Example Scenarios

### Scenario 1: Employee with 3 hours undertime

**Before**:
```
Undertime: 180 minutes
Salary Deduction: ₱2,067.00
Leave Credits: No change
```

**After (with VL deduction)**:
```
Undertime: 180 minutes
Salary Deduction: ₱0.00
VL Deducted: 0.375 days (180 ÷ 480)
Leave Credits: VL reduced by 0.375
```

### Scenario 2: Employee with both late and undertime

**Input**:
- Late: 60 minutes → Deduct from VL
- Undertime: 120 minutes → Deduct from SL

**Result**:
```
VL Deducted: 0.125 days (60 ÷ 480)
SL Deducted: 0.250 days (120 ÷ 480)
Salary Deduction: ₱0.00
```

---

## SQL Verification Queries

### Check if migration ran:
```sql
SELECT * FROM migrations 
WHERE migration LIKE '%undertime_leave_deduction%';
```

### Verify new columns:
```sql
DESCRIBE accredited_hours_log;
```

### Check existing undertime records:
```sql
SELECT 
    employee_id,
    DATE(created_at) as date,
    undertime_minutes,
    undertime_deducted_from_leave,
    undertime_deduction_leave_type
FROM accredited_hours_log
WHERE undertime_minutes > 0
ORDER BY created_at DESC
LIMIT 10;
```

---

## Rollback (If Needed)

```bash
php artisan migrate:rollback --step=1
```

This will remove the new columns and revert to the previous state.

---

## Summary

This implementation mirrors the existing late deduction logic for undertime, giving employees the option to:

1. **Use leave credits** (VL or SL) to cover undertime
2. **Or accept salary deduction** (LWOP)

This aligns with CSC rules and provides flexibility for employees while maintaining accurate payroll calculations.
