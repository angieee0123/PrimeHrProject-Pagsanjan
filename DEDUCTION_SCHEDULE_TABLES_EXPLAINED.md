# Deduction Schedule Tables - Explanation

## Overview
There are TWO schedule-related concepts in the deduction system:

---

## 1. `deduction_schedules` Table
**Purpose:** Global schedule configuration for deduction TYPES

**Scope:** Applies to ALL employees who have this deduction type

**Structure:**
```sql
- id
- deduction_type_id (FK to deduction_types)
- cutoff_schedule (1ST_ONLY, 2ND_ONLY, BOTH_SPLIT, BOTH_FULL)
- priority_order
- is_active
- effective_date
```

**Example:**
- GSIS deduction type → 1ST_ONLY cutoff
- This means ALL employees with GSIS will be deducted on 1st cutoff only

**Use Case:**
- Setting default deduction schedules for government mandated contributions
- GSIS: 1st cutoff only
- PhilHealth: 1st cutoff only  
- Pag-IBIG: 2nd cutoff only
- Withholding Tax: Both cutoffs (split)

---

## 2. `employee_deduction_schedules` Table (NOT YET CREATED)
**Purpose:** Per-employee custom schedule overrides

**Scope:** Applies to SPECIFIC employee only

**Structure (if we create it):**
```sql
- id
- employee_id (FK to employees)
- employee_deduction_id (FK to employee_deductions)
- cutoff_schedule (1ST_ONLY, 2ND_ONLY, BOTH_SPLIT, BOTH_FULL)
- start_month
- end_month
- is_active
- created_at
- updated_at
```

**Example:**
- Employee A has GSIS loan
- Custom schedule: Deduct on 2ND cutoff only for Jan-Jun 2024
- This overrides the global GSIS schedule for this employee

**Use Case:**
- Special payment arrangements
- Temporary schedule changes
- Employee-specific loan payment schedules

---

## Current Implementation

### What We Have:
✅ `deduction_schedules` - Global type-level schedules
✅ `employee_deductions` - Employee-specific deduction records
✅ Manage Schedule modal UI

### What We DON'T Have:
❌ `employee_deduction_schedules` table
❌ Per-employee schedule overrides
❌ Schedule history tracking

---

## How "Manage Schedule" Currently Works

When you click "Manage Schedule" for an employee:

1. **Loads employee's active deductions** from `employee_deductions`
2. **Shows current schedule** from `deduction_schedules` (global)
3. **Updates the global schedule** when you save

**Important:** Currently, changing the schedule affects ALL employees with that deduction type, not just the selected employee.

---

## Recommendation

### Option 1: Keep Current System (Simpler)
- Use only `deduction_schedules` for global type-level schedules
- Remove "Manage Schedule" per employee (since it affects everyone)
- Add a separate "Manage Global Schedules" page for deduction types

### Option 2: Implement Per-Employee Schedules (More Flexible)
- Create `employee_deduction_schedules` table
- Update modal to save per-employee schedules
- Add schedule history and audit trail
- Allow overrides of global schedules

---

## Current Route Implementation

The route `/admin/deductions/schedules/update` currently:
- Takes employee_id and schedules array
- Updates the GLOBAL `deduction_schedules` table
- Affects ALL employees with those deduction types

**This is a limitation** - it's not truly per-employee scheduling.

---

## Files Modified

1. **Route:** `routes/web.php` - Added `/admin/deductions/schedules/update`
2. **Modal:** `assignDeductionScheduleModal.blade.php` - Connected form to backend
3. **Model:** `PayrollDeduction.php` - Fixed table name to `payroll_deductions`

---

## Next Steps (If You Want Per-Employee Schedules)

1. Create migration for `employee_deduction_schedules` table
2. Create `EmployeeDeductionSchedule` model
3. Update route to save to employee-specific table
4. Update DeductionService to check employee schedules first, then global
5. Add schedule history view in modal

---

## Testing Current Implementation

1. Go to Admin → Deductions → Schedules tab
2. Click "Manage Schedule" for any employee
3. Change cutoff periods for their deductions
4. Click "Save Schedule"
5. **Note:** This changes the schedule for ALL employees with those deduction types

---

## Summary

**Current State:**
- ✅ Manage Schedule modal works
- ✅ Saves to database
- ⚠️ Changes affect ALL employees (not per-employee)

**To Make It Per-Employee:**
- Need to create `employee_deduction_schedules` table
- Update backend logic to save per-employee
- Add schedule priority (employee override > global default)
