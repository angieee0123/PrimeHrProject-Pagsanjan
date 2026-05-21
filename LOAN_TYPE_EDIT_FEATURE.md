# Loan Type Edit Functionality

## Overview

The **Edit Loan Type** feature allows administrators to update loan type information while protecting critical fields and warning about impacts on existing employee loans.

## Features

### ✅ What Can Be Edited
- **Loan Type Name** - Update the display name
- **Max Loanable Amount** - Change the maximum loan limit
- **Interest Rate** - Update the interest percentage
- **Max Terms (Months)** - Modify the maximum loan duration
- **Status** - Activate or deactivate the loan type
- **Description** - Update the loan type description

### 🔒 What Cannot Be Edited
- **Loan Type Code** - Locked to prevent breaking references
- **Provider** - Locked to maintain categorization
- **Category** - Always "LOAN" (system-defined)

## How to Use

### Step 1: Access Edit Modal
1. Go to **Admin → Deductions → Loan Types** tab
2. Find the loan type you want to edit
3. Click the **Edit** button (pencil icon)

### Step 2: Update Information
- Modify any editable fields
- Review the warning if employees are using this loan type
- Click **Update Loan Type**

### Step 3: Confirmation
- System updates both `deduction_types` and `loan_types` tables
- Success message confirms the update
- Changes apply immediately to future payroll calculations

## Example Scenarios

### Scenario 1: Update Interest Rate
**Before:**
- GSIS Housing Loan
- Interest Rate: 6.00%

**Action:**
- Edit loan type
- Change interest rate to 5.50%
- Save

**Result:**
- New loans will use 5.50% rate
- Existing loans keep their original rate (stored in employee_deductions)

### Scenario 2: Increase Max Loanable Amount
**Before:**
- Pag-IBIG MPL
- Max Loanable: ₱500,000

**Action:**
- Edit loan type
- Change max to ₱750,000
- Save

**Result:**
- Employees can now apply for up to ₱750,000
- Existing loans remain at their current amounts

### Scenario 3: Deactivate Loan Type
**Before:**
- LBP Salary Loan
- Status: Active
- 5 employees using it

**Action:**
- Edit loan type
- Change status to Inactive
- Warning shows: "5 employees using this"
- Save

**Result:**
- Loan type hidden from "Add Loan" dropdown
- Existing employee loans continue normally
- Can be reactivated later

## Technical Implementation

### Backend Route (web.php)

```php
Route::put('/admin/deductions/loan-types/{id}', function ($request, $id) {
    $deductionType = DeductionType::findOrFail($id);
    
    // Validate input
    $data = $request->validate([
        'name' => 'required|string|max:100',
        'max_loanable_amount' => 'nullable|numeric|min:0',
        'interest_rate' => 'nullable|numeric|min:0|max:100',
        'max_terms_months' => 'nullable|integer|min:1',
        'is_active' => 'required|boolean',
    ]);
    
    // Update deduction_types table
    $deductionType->update([
        'name' => $data['name'],
        'percentage_rate' => $data['interest_rate'],
        'max_amount' => $data['max_loanable_amount'],
        'is_active' => $data['is_active'],
    ]);
    
    // Update loan_types table (if exists)
    $loanType = LoanType::where('deduction_type_id', $id)->first();
    if ($loanType) {
        $loanType->update([
            'name' => $data['name'],
            'max_loanable_amount' => $data['max_loanable_amount'],
            'interest_rate' => $data['interest_rate'],
            'max_terms_months' => $data['max_terms_months'],
            'is_active' => $data['is_active'],
        ]);
    }
    
    return redirect()->back()->with('success', 'Loan type updated!');
});
```

### Frontend JavaScript

```javascript
function editLoanType(id) {
    // Fetch current data
    fetch(`/admin/deductions/types/${id}`)
        .then(response => response.json())
        .then(data => {
            // Populate form fields
            document.getElementById('editLoanTypeName').value = data.name;
            document.getElementById('editMaxLoanable').value = data.max_amount || '';
            document.getElementById('editInterestRate').value = data.percentage_rate || '';
            document.getElementById('editIsActive').value = data.is_active ? '1' : '0';
            
            // Show warning if in use
            if (data.employees_count > 0) {
                showWarning(data.employees_count);
            }
            
            // Set form action
            document.getElementById('editLoanTypeForm').action = 
                `/admin/deductions/loan-types/${data.id}`;
            
            // Open modal
            openEditModal();
        });
}
```

### Modal Structure

```html
<div id="editLoanTypeModal">
    <form id="editLoanTypeForm" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Read-only fields -->
        <input type="text" name="code" readonly>
        <select name="provider" disabled>
        
        <!-- Editable fields -->
        <input type="text" name="name" required>
        <input type="number" name="max_loanable_amount">
        <input type="number" name="interest_rate">
        <input type="number" name="max_terms_months">
        <select name="is_active" required>
        
        <!-- Warning (conditional) -->
        <div id="warning" style="display: none;">
            This loan type is used by X employees
        </div>
        
        <button type="submit">Update</button>
    </form>
</div>
```

## Field Protection

### Why Code Cannot Be Changed
```
Code: GSIS_HOUSING
↓ Used in
- employee_deductions.deduction_type_id (foreign key)
- deduction_schedules.deduction_type_id (foreign key)
- payroll calculations (code-based lookups)

Changing code would break all references!
```

### Why Provider Cannot Be Changed
```
Provider determines:
- Categorization in reports
- Filtering in UI
- Export grouping
- Compliance reporting

Changing provider would cause inconsistencies!
```

## Impact on Existing Loans

### What Changes Affect Existing Loans
❌ **None** - Existing employee loans are independent records

### What Changes Affect New Loans
✅ **All** - New loan applications use updated values

### Example
```
Loan Type: GSIS Housing
- Original: Max ₱500,000, 6% interest
- Updated: Max ₱750,000, 5.5% interest

Employee A (existing loan):
- Amount: ₱400,000
- Interest: 6% (unchanged)
- Continues with original terms

Employee B (new loan):
- Can apply up to: ₱750,000
- Interest: 5.5% (new rate)
- Uses updated terms
```

## Validation Rules

| Field | Rule | Example |
|-------|------|---------|
| Name | Required, max 100 chars | "GSIS Housing Loan" |
| Max Loanable | Optional, numeric, min 0 | 500000.00 |
| Interest Rate | Optional, numeric, 0-100 | 6.00 |
| Max Terms | Optional, integer, min 1 | 60 |
| Status | Required, boolean | Active/Inactive |

## Warning System

### When Warning Appears
```javascript
if (employees_count > 0) {
    showWarning(`This loan type is used by ${employees_count} employee(s)`);
}
```

### Warning Message
```
⚠️ Warning: This loan type is currently assigned to 5 employee(s). 
Changes will affect their loan records.
```

### What It Means
- Changes to **name** affect display only
- Changes to **max amount** don't affect existing loans
- Changes to **interest rate** don't affect existing loans
- Changes to **status** affect visibility only

## Best Practices

### ✅ Do
- Update name for clarity
- Increase max loanable amount as needed
- Adjust interest rates based on policy
- Deactivate unused loan types
- Review warning before saving

### ❌ Don't
- Change code (it's locked anyway)
- Change provider (it's locked anyway)
- Deactivate if employees are actively using it
- Make changes without reviewing impact
- Update without notifying HR staff

## Testing Checklist

- [ ] Edit loan type name
- [ ] Update max loanable amount
- [ ] Change interest rate
- [ ] Modify max terms
- [ ] Toggle status (Active/Inactive)
- [ ] Verify code is read-only
- [ ] Verify provider is disabled
- [ ] Check warning appears when in use
- [ ] Confirm changes save correctly
- [ ] Verify existing loans unaffected
- [ ] Test new loan uses updated values

## Error Handling

### Validation Errors
```
- Name is required
- Max loanable must be positive
- Interest rate must be 0-100
- Max terms must be at least 1
```

### Database Errors
```
- Loan type not found
- Update failed
- Foreign key constraint
```

### User Feedback
```
Success: "Loan type 'GSIS Housing' updated successfully!"
Error: "Failed to update loan type. Please try again."
```

## Future Enhancements

Potential improvements:
- Audit log of changes
- Bulk edit multiple loan types
- Version history
- Change preview before saving
- Email notification to affected employees
- Automatic recalculation option
