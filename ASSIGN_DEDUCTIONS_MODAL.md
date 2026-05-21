# Assign Deductions Modal - Functional Documentation

## Overview
The Assign Deductions modal allows HR admins to assign multiple deduction types to an employee at once, making it easy to set up mandatory deductions (GSIS, PhilHealth, Pag-IBIG) in bulk.

---

## Features

### ✅ **1. Bulk Assignment**
- Select multiple deduction types at once
- Assign all selected deductions with the same start date, end date, and status
- Saves time compared to adding deductions one by one

### ✅ **2. Smart Duplicate Prevention**
- Automatically detects existing active deductions for the selected employee
- Disables checkboxes for deductions already assigned
- Shows warning message listing existing deductions
- Prevents duplicate deduction assignments

### ✅ **3. Quick Selection Helpers**
- **Select All** - Check all available deductions
- **Deselect All** - Uncheck all deductions
- **Mandatory Only** - Quickly select only GSIS, PhilHealth, Pag-IBIG, and Withholding Tax

### ✅ **4. Visual Feedback**
- Shows count of selected deductions: "(3 selected)"
- Grouped by category (MANDATORY, LOAN, OTHER)
- Displays deduction code and percentage rate
- Disabled checkboxes appear grayed out
- Submit button disabled until at least one deduction is selected

---

## How to Use

### **Step 1: Open Modal**
Click "Assign Deductions" button on the deductions page.

### **Step 2: Select Employee**
Choose an employee from the dropdown. The system will:
- Check for existing active deductions
- Disable checkboxes for deductions already assigned
- Show warning if employee has existing deductions

### **Step 3: Select Deduction Types**
Use one of these methods:
- **Manual Selection** - Check individual deductions
- **Select All** - Assign all available deductions
- **Mandatory Only** - Quick-assign government-mandated deductions

### **Step 4: Set Dates and Status**
- **Start Date** (required) - When deductions begin
- **End Date** (optional) - When deductions end (leave blank for ongoing)
- **Status** - ACTIVE, SUSPENDED, or COMPLETED

### **Step 5: Add Remarks (Optional)**
Add any notes about the deduction assignment.

### **Step 6: Submit**
Click "Assign Deductions" to save.

---

## Use Cases

### **Use Case 1: New Employee Setup**
**Scenario:** New permanent employee needs all mandatory deductions.

**Steps:**
1. Select employee
2. Click "Mandatory Only"
3. Set start date to employment date
4. Submit

**Result:** GSIS, PhilHealth, Pag-IBIG, and Withholding Tax assigned instantly.

---

### **Use Case 2: Adding Missing Deductions**
**Scenario:** Employee is missing PhilHealth deduction.

**Steps:**
1. Select employee
2. System shows: "Already has: GSIS, Pag-IBIG" (grayed out)
3. Check PhilHealth (only available option)
4. Submit

**Result:** PhilHealth added without duplicating existing deductions.

---

### **Use Case 3: Bulk Loan Assignment**
**Scenario:** Employee has multiple loans (GSIS Salary Loan + Pag-IBIG MPL).

**Steps:**
1. Select employee
2. Check both loan types
3. Set start date and end date
4. Add remarks: "Approved loans - see HR files"
5. Submit

**Result:** Both loans assigned with same dates and remarks.

---

## Backend Logic

### **Route: `/admin/deductions/employee/bulk-assign`**

**Validation:**
```php
- employee_id: required, must exist
- deduction_types: required array, min 1 item
- start_date: required date
- end_date: optional, must be after start_date
- status: required (ACTIVE/SUSPENDED/COMPLETED)
- remarks: optional string
```

**Process:**
1. Loop through selected deduction types
2. Check if employee already has each deduction type (status = ACTIVE)
3. Skip duplicates, track skipped types
4. Create EmployeeDeduction records for new assignments
5. Return success message with count and skipped list

**Response Messages:**
- ✅ Success: "3 deduction(s) assigned to John Doe successfully."
- ⚠️ Partial: "2 deduction(s) assigned. Skipped (already active): GSIS, PhilHealth"
- ⚠️ None: "No deductions assigned. All selected deductions are already active."

---

## API Endpoint

### **GET `/admin/deductions/employee/{employeeId}/active`**

**Purpose:** Fetch active deductions for an employee to prevent duplicates.

**Response:**
```json
{
  "deductions": [
    {
      "id": 1,
      "name": "GSIS Contribution",
      "code": "GSIS"
    },
    {
      "id": 3,
      "name": "Pag-IBIG Contribution",
      "code": "PAGIBIG"
    }
  ]
}
```

**Used by:** JavaScript function `checkExistingDeductions()` when employee is selected.

---

## Database Structure

### **employee_deductions table:**
```sql
- id (PK)
- employee_id (FK to employees)
- deduction_type_id (FK to deduction_types)
- amount (nullable - for fixed amounts)
- total_amount (nullable - for loans)
- remaining_balance (nullable - for loans)
- installment_amount (nullable - for loans)
- start_date (required)
- end_date (nullable)
- status (ACTIVE/SUSPENDED/COMPLETED)
- remarks (nullable)
- created_at
- updated_at
```

**Note:** For bulk assignments, `amount`, `total_amount`, `remaining_balance`, and `installment_amount` are NULL. These are set later when editing individual deductions or adding loans.

---

## JavaScript Functions

### **handleCheckboxChange()**
- Updates selected count display
- Enables/disables submit button
- Called on every checkbox change

### **selectAllDeductions()**
- Checks all enabled checkboxes
- Skips disabled ones (already assigned)

### **deselectAllDeductions()**
- Unchecks all checkboxes

### **selectMandatoryOnly()**
- Deselects all first
- Checks only deductions with `data-category="MANDATORY"`

### **checkExistingDeductions()**
- Fetches active deductions via AJAX
- Disables checkboxes for existing deductions
- Shows/hides warning message
- Called when employee is selected

---

## UI Components

### **Warning Box**
```html
<div class="warning-box">
  ⚠️ Existing Deductions:
  This employee already has: GSIS, PhilHealth
</div>
```
- Yellow background (#fff8e1)
- Shows when employee has active deductions
- Lists deduction names

### **Selection Counter**
```
Deduction Types * (3 selected)
```
- Updates in real-time
- Shows number of checked items

### **Quick Action Buttons**
```
[Select All] [Deselect All] [Mandatory Only]
```
- Styled as text links
- Hover effect for better UX

---

## Testing Checklist

- [x] Select employee → Existing deductions detected
- [x] Checkboxes for existing deductions disabled
- [x] Warning message displays correctly
- [x] Select All skips disabled checkboxes
- [x] Mandatory Only selects only MANDATORY category
- [x] Selected count updates correctly
- [x] Submit button disabled when no selection
- [x] Bulk assignment creates multiple records
- [x] Duplicate prevention works
- [x] Success message shows correct count
- [x] Skipped deductions listed in message

---

## Future Enhancements

1. **Preset Templates** - Save common deduction combinations (e.g., "New Permanent Employee")
2. **Bulk Edit** - Edit multiple deductions at once
3. **Deduction Preview** - Show estimated amounts before assigning
4. **Department-Based Defaults** - Auto-suggest deductions based on department
5. **Approval Workflow** - Require approval for certain deduction types

---

## Files Modified

1. `assignDeductionModal.blade.php` - Added bulk selection features and existing deduction checking
2. `routes/web.php` - Added bulk assign route and active deductions API endpoint
