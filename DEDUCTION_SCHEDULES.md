# Deduction Schedules - Database Integration

## Overview
The Deduction Schedules tab displays all employees with active deductions and allows HR to manage when each deduction is applied per cutoff period.

---

## What is Deduction Scheduling?

**Purpose:** Configure which cutoff period (1st, 2nd, or Both) each deduction will be applied for each employee.

**Example:**
```
Employee: Juan Dela Cruz
├─ GSIS Contribution → 1st Cutoff only
├─ PhilHealth → 1st Cutoff only
├─ Pag-IBIG → 2nd Cutoff only
└─ GSIS Salary Loan → Both cutoffs (split)
```

**Result:**
- **1st Cutoff:** GSIS + PhilHealth + Half of Loan
- **2nd Cutoff:** Pag-IBIG + Half of Loan

---

## Features Implemented

### ✅ **1. Employee List with Active Deductions**
**Displays:**
- Employee (with avatar and ID)
- Department
- Active Deductions count
- Active Loans count
- Last Updated date
- "Manage Schedule" button

**Data Source:**
```php
Employees with at least one ACTIVE deduction
Grouped by employee
Shows counts of deductions and loans
```

### ✅ **2. Real-Time Filtering**
- **Search:** Filter by employee name
- **Department:** Filter by department
- Updates count dynamically

### ✅ **3. Schedule Management Modal**
**Features:**
- Shows all active deductions for selected employee
- Radio buttons for each deduction:
  - **1st Cutoff** - Deduct only on 1st cutoff (Days 1-15)
  - **2nd Cutoff** - Deduct only on 2nd cutoff (Days 16-31)
  - **Both** - Split deduction across both cutoffs
- Displays current schedule for each deduction
- Period selection (From Month - To Month)
- Non-destructive (preserves history)

### ✅ **4. Export to CSV**
Exports all employees with their deduction schedules:
- Employee details
- Each deduction with cutoff schedule
- Category and amount
- Status

---

## Database Query

```php
$employeesWithDeductions = Employee::with([
    'employmentDetail.departmentRelation',
    'deductions' => function($q) {
        $q->where('status', 'ACTIVE')->with('deductionType');
    }
])
->whereHas('deductions', function($q) {
    $q->where('status', 'ACTIVE');
})
->orderBy('last_name')
->get();
```

---

## API Endpoints

### **GET /admin/deductions/employee/{employeeId}/deductions**
**Purpose:** Fetch active deductions for an employee to display in schedule modal

**Response:**
```json
{
  "deductions": [
    {
      "id": 1,
      "deduction_type_id": 1,
      "name": "GSIS Contribution",
      "code": "GSIS",
      "category": "MANDATORY",
      "computation_type": "PERCENTAGE",
      "amount": "9%",
      "current_schedule": "1ST_ONLY"
    },
    {
      "id": 4,
      "deduction_type_id": 5,
      "name": "GSIS Salary Loan",
      "code": "LOAN_GSIS_SALARY",
      "category": "LOAN",
      "computation_type": "FIXED",
      "amount": "₱2,500.00/month",
      "current_schedule": "BOTH_SPLIT"
    }
  ]
}
```

### **GET /admin/deductions/schedules/export**
**Purpose:** Export all employee deduction schedules to CSV

**CSV Columns:**
1. Employee ID
2. Employee Name
3. Department
4. Deduction Type
5. Category
6. Amount
7. Cutoff Schedule
8. Status

---

## Cutoff Schedule Options

| Value | Description | Example |
|-------|-------------|---------|
| **1ST_ONLY** | Deduct only on 1st cutoff (Days 1-15) | GSIS, PhilHealth |
| **2ND_ONLY** | Deduct only on 2nd cutoff (Days 16-31) | Pag-IBIG |
| **BOTH_SPLIT** | Split monthly amount 50-50 across both cutoffs | Withholding Tax, Loans |
| **BOTH_FULL** | Deduct full amount on both cutoffs (rare) | Special cases |

---

## How It Works

### **Current Implementation:**
The schedule is stored in the `deduction_schedules` table, linked to `deduction_types`.

**Structure:**
```
deduction_types (GSIS, PhilHealth, etc.)
    └─ deduction_schedules (cutoff_schedule: 1ST_ONLY, 2ND_ONLY, BOTH_SPLIT)
```

**This means:**
- All employees with GSIS get the same schedule (e.g., 1ST_ONLY)
- All employees with Pag-IBIG get the same schedule (e.g., 2ND_ONLY)

### **Modal Functionality (Planned):**
The modal allows **per-employee customization**, but this requires:
1. New table: `employee_deduction_schedules`
2. Columns: employee_id, deduction_id, cutoff_schedule, start_month, end_month
3. Backend route to save custom schedules

**Note:** The modal UI is ready, but backend integration is pending.

---

## Use Cases

### **Use Case 1: View Employees with Deductions**
1. Go to **Schedules** tab
2. See all employees with active deductions
3. Check deduction and loan counts
4. Filter by department if needed

### **Use Case 2: Check Current Schedules**
1. Click "Manage Schedule" for an employee
2. Modal shows all deductions with current cutoff settings
3. Review which deductions are on 1st, 2nd, or both cutoffs

### **Use Case 3: Export Schedule Report**
1. Apply filters (if needed)
2. Click "Export" button
3. Open CSV in Excel
4. Review all employee deduction schedules

---

## Sample Data Display

### **Schedules Table:**
```
┌────────────────────────────────────────────────────────────┐
│ Employee: Juan Dela Cruz (EMP001)                          │
│ Department: Municipal Health Office                        │
│ Active Deductions: 4 Deductions                            │
│ Active Loans: 1 Loan                                       │
│ Last Updated: Jan 15, 2024                                 │
│ [Manage Schedule]                                          │
└────────────────────────────────────────────────────────────┘
```

### **Schedule Modal:**
```
╔════════════════════════════════════════════════════════════╗
║ DEDUCTION SCHEDULE - Juan Dela Cruz                       ║
╠════════════════════════════════════════════════════════════╣
║ Effective Period: Jan 2024 - Dec 2024                     ║
╠════════════════════════════════════════════════════════════╣
║ GSIS Contribution (MANDATORY) - 9%                         ║
║ ○ 1st Cutoff  ○ 2nd Cutoff  ○ Both                        ║
║                                                            ║
║ PhilHealth Contribution (MANDATORY) - 2.5%                 ║
║ ○ 1st Cutoff  ○ 2nd Cutoff  ○ Both                        ║
║                                                            ║
║ Pag-IBIG Contribution (MANDATORY) - ₱100                   ║
║ ○ 1st Cutoff  ○ 2nd Cutoff  ○ Both                        ║
║                                                            ║
║ GSIS Salary Loan (LOAN) - ₱2,500/month                     ║
║ ○ 1st Cutoff  ○ 2nd Cutoff  ○ Both                        ║
╠════════════════════════════════════════════════════════════╣
║ [Cancel]  [Save Schedule]                                 ║
╚════════════════════════════════════════════════════════════╝
```

---

## CSV Export Format

**Example:**
```csv
Employee ID,Employee Name,Department,Deduction Type,Category,Amount,Cutoff Schedule,Status
EMP001,Juan Dela Cruz,Municipal Health Office,GSIS Contribution,MANDATORY,9%,1ST_ONLY,ACTIVE
EMP001,Juan Dela Cruz,Municipal Health Office,PhilHealth Contribution,MANDATORY,2.5%,1ST_ONLY,ACTIVE
EMP001,Juan Dela Cruz,Municipal Health Office,Pag-IBIG Contribution,MANDATORY,₱100.00,2ND_ONLY,ACTIVE
EMP001,Juan Dela Cruz,Municipal Health Office,GSIS Salary Loan,LOAN,₱2500.00/month,BOTH_SPLIT,ACTIVE
```

---

## Testing Checklist

- [x] Table displays employees with active deductions
- [x] Deduction and loan counts are correct
- [x] Search filter works
- [x] Department filter works
- [x] Visible count updates correctly
- [x] "Manage Schedule" opens modal
- [x] Modal fetches employee deductions via API
- [x] Modal displays current schedules
- [x] Radio buttons work correctly
- [x] Export downloads CSV
- [x] CSV contains all deduction schedules
- [x] Avatars display correctly
- [x] Last updated date shows correctly

---

## Future Enhancements (Backend Integration Needed)

### **1. Per-Employee Custom Schedules**
**Current:** All employees with GSIS use the same schedule  
**Planned:** Each employee can have custom schedule

**Implementation:**
```sql
CREATE TABLE employee_deduction_schedules (
    id BIGINT PRIMARY KEY,
    employee_id BIGINT,
    employee_deduction_id BIGINT,
    cutoff_schedule ENUM('1ST_ONLY', '2ND_ONLY', 'BOTH_SPLIT', 'BOTH_FULL'),
    start_month DATE,
    end_month DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **2. Schedule History**
- Track all schedule changes
- Show who made changes and when
- Allow reverting to previous schedules

### **3. Bulk Schedule Updates**
- Apply same schedule to multiple employees
- Department-wide schedule changes
- Template-based scheduling

### **4. Schedule Conflicts Detection**
- Warn if schedule change affects payroll
- Prevent overlapping schedules
- Validate date ranges

### **5. Schedule Preview**
- Show how schedule affects payroll
- Calculate total deductions per cutoff
- Preview before saving

---

## Important Notes

### **Current Limitation:**
The modal is **UI-ready** but the backend route to save custom schedules is **not yet implemented**.

**What works:**
✅ Display employees with deductions  
✅ Fetch employee deductions  
✅ Show current schedules  
✅ Filter and export  

**What's pending:**
⏳ Save custom per-employee schedules  
⏳ Schedule history tracking  
⏳ Apply schedules to payroll processing  

### **Workaround:**
Currently, schedules are managed at the **deduction type level** via the `deduction_schedules` table. To change when a deduction is applied, update the schedule in the "Deduction Types" tab.

---

## Files Modified

1. **routes/web.php** - Added employee deductions API and export route
2. **adminDeductions.blade.php** - Pass employees with deductions to view
3. **schedules.blade.php** - Display real data from database
4. **assignDeductionScheduleModal.blade.php** - Fetch real deductions via API

---

## Next Steps

To fully implement per-employee scheduling:

1. Create `employee_deduction_schedules` table
2. Add route: `POST /admin/deductions/schedules/save`
3. Update `DeductionService` to check employee-specific schedules first
4. Add schedule history tracking
5. Implement schedule validation logic
