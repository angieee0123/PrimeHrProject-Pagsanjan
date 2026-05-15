# Loan Type Registry Feature - Complete Guide

## ✅ Feature Overview

The **Loan Type Registry** allows admins to register reusable loan types that can be assigned to multiple employees with different amounts and payment terms.

---

## How It Works

### 1. Register a Loan Type (One Time)
Admin registers a loan type with:
- Provider (GSIS, Pag-IBIG, SSS, Bank, etc.)
- Loan name (e.g., "Housing Loan", "Salary Loan")
- Max loanable amount (optional)
- Interest rate (optional)
- Max terms in months (optional)

### 2. Assign to Multiple Employees
Once registered, the loan type appears in the "Add Loan" modal and can be assigned to any employee with:
- **Different loan amounts** (e.g., Employee A: ₱50,000, Employee B: ₱100,000)
- **Different monthly payments** (e.g., Employee A: ₱2,500/month, Employee B: ₱5,000/month)
- **Different start/end dates**
- **Different statuses**

---

## Access the Feature

1. Go to **Admin → Deductions**
2. Click on **"Loan Types"** tab (new tab added)
3. Click **"Register Loan Type"** button

---

## Register a New Loan Type

### Step-by-Step:

1. **Click "Register Loan Type"**
   - Opens the registration modal

2. **Fill in the form:**
   - **Loan Provider** (required): Select from dropdown
     - GSIS
     - Pag-IBIG
     - SSS
     - Bank / Financial Institution
     - Cooperative
     - Other
   
   - **Loan Type Code** (required): Auto-generated, can be edited
     - Example: `GSIS_HOUS` for GSIS Housing Loan
     - Must be unique
   
   - **Loan Type Name** (required): Descriptive name
     - Example: "Housing Loan", "Salary Loan", "Emergency Loan"
   
   - **Max Loanable Amount** (optional): Maximum amount that can be borrowed
     - Example: ₱500,000.00
   
   - **Interest Rate** (optional): Annual interest rate percentage
     - Example: 6.00%
   
   - **Max Terms** (optional): Maximum loan duration in months
     - Example: 60 months (5 years)
   
   - **Status** (required): Active or Inactive
   
   - **Description** (optional): Additional notes

3. **Click "Register Loan Type"**
   - Saves to database
   - Appears in Loan Types tab
   - Available in Add Loan modal

---

## Example Scenario

### Scenario: Register GSIS Housing Loan

**Step 1: Register the Loan Type**
```
Provider: GSIS
Code: GSIS_HOUSING
Name: GSIS Housing Loan
Max Loanable: ₱2,000,000.00
Interest Rate: 6.00%
Max Terms: 360 months (30 years)
Status: Active
```

**Step 2: Assign to Employee A**
```
Employee: Juan Dela Cruz
Loan Type: GSIS Housing Loan (from dropdown)
Total Amount: ₱500,000.00
Monthly Installment: ₱3,000.00
Start Date: Jan 1, 2024
End Date: Dec 31, 2037 (14 years)
Status: Active
```

**Step 3: Assign to Employee B**
```
Employee: Maria Santos
Loan Type: GSIS Housing Loan (same type!)
Total Amount: ₱1,200,000.00
Monthly Installment: ₱8,000.00
Start Date: Mar 1, 2024
End Date: Feb 28, 2039 (15 years)
Status: Active
```

**Result:**
- Same loan type (GSIS Housing Loan)
- Different amounts (₱500K vs ₱1.2M)
- Different monthly payments (₱3K vs ₱8K)
- Different durations (14 years vs 15 years)

---

## Benefits

### ✅ Consistency
- All GSIS Housing Loans use the same loan type
- Standardized naming and categorization
- Easy to filter and report

### ✅ Flexibility
- Each employee can have different loan amounts
- Different payment terms per employee
- Different start/end dates

### ✅ Reusability
- Register once, use many times
- No need to create duplicate loan types
- Easier to manage and maintain

### ✅ Reporting
- Group loans by type
- See how many employees have each loan type
- Track total outstanding per loan type

---

## Database Structure

### Tables Involved:

1. **`deduction_types`** - Stores the loan type definition
   - Used for all deduction types (mandatory, loans, other)
   - Category = 'LOAN' for loan types

2. **`loan_types`** - Stores additional loan metadata (optional)
   - Links to deduction_types
   - Stores max loanable, interest rate, max terms

3. **`employee_deductions`** - Stores individual employee loans
   - Links to deduction_types (the loan type)
   - Stores employee-specific amounts and terms

### Relationship:
```
deduction_types (1) ←→ (many) employee_deductions
     ↓
loan_types (optional metadata)
```

---

## Features in Loan Types Tab

### View All Registered Loan Types
- Code
- Name
- Provider (GSIS, Pag-IBIG, etc.)
- Max Loanable Amount
- Interest Rate
- Max Terms
- Number of employees using it
- Status (Active/Inactive)

### Search & Filter
- Search by loan type name
- Filter by provider (GSIS, Pag-IBIG, Other)
- Filter by status (Active/Inactive)

### Actions
- **View Details** - See full loan type information
- **Edit** - Modify loan type (coming soon)
- **Delete** - Remove loan type (only if not in use)

### Protection
- Cannot delete loan types that are assigned to employees
- Shows count of employees using each loan type
- Delete button disabled if in use

---

## How Loan Types Appear in Add Loan Modal

After registering loan types, they automatically appear in the "Add Loan" modal grouped by provider:

```
Loan Type Dropdown:
├── GSIS Loans
│   ├── GSIS Housing Loan
│   ├── GSIS Salary Loan
│   └── GSIS Emergency Loan
├── Pag-IBIG Loans
│   ├── Pag-IBIG Multi-Purpose Loan
│   └── Pag-IBIG Housing Loan
└── Other Loans
    ├── SSS Salary Loan
    └── Cooperative Emergency Loan
```

---

## Routes Created

### Register Loan Type
```
POST /admin/deductions/loan-types/store
```

### View Loan Type Details
```
GET /admin/deductions/types/{id}
```

### Delete Loan Type
```
DELETE /admin/deductions/loan-types/{id}
```

---

## Files Created/Modified

### New Files:
1. **`resources/views/admin/deductions/partials/loan-types.blade.php`**
   - Loan Types tab content
   - Table display
   - Search and filter functionality

### Modified Files:
1. **`resources/views/admin/deductions/adminDeductions.blade.php`**
   - Added "Loan Types" tab button
   - Added tab content section

2. **`resources/views/admin/deductions/modals/addLoanTypeModal.blade.php`**
   - Connected form to backend route
   - Removed placeholder submit handler

3. **`routes/web.php`**
   - Added loan type store route
   - Added loan type view route
   - Added loan type delete route

---

## Testing the Feature

### Test 1: Register a New Loan Type
1. Go to Deductions → Loan Types tab
2. Click "Register Loan Type"
3. Fill in:
   - Provider: GSIS
   - Name: Test Housing Loan
   - Max Loanable: 1000000
   - Interest Rate: 5.5
   - Max Terms: 240
   - Status: Active
4. Click "Register Loan Type"
5. ✅ Should redirect with success message
6. ✅ Should appear in Loan Types table

### Test 2: Assign to Multiple Employees
1. Go to Deductions → Loans tab
2. Click "Add Loan"
3. Select "Test Housing Loan" from dropdown
4. Assign to Employee A with ₱50,000
5. Repeat for Employee B with ₱100,000
6. ✅ Both should use the same loan type
7. ✅ Different amounts and terms

### Test 3: View Loan Type Details
1. In Loan Types tab, click "View" icon
2. ✅ Should show loan type details in alert

### Test 4: Delete Protection
1. Try to delete a loan type that's in use
2. ✅ Delete button should be disabled
3. ✅ Tooltip shows "Cannot delete - in use"

### Test 5: Delete Unused Loan Type
1. Register a new loan type
2. Don't assign it to anyone
3. Click delete button
4. Confirm deletion
5. ✅ Should delete successfully

---

## Database Queries

### View all registered loan types
```sql
SELECT 
    dt.code,
    dt.name,
    dt.max_amount as max_loanable,
    dt.percentage_rate as interest_rate,
    dt.is_active,
    COUNT(DISTINCT ed.employee_id) as employees_count
FROM deduction_types dt
LEFT JOIN employee_deductions ed ON dt.id = ed.deduction_type_id AND ed.status = 'ACTIVE'
WHERE dt.category = 'LOAN'
GROUP BY dt.id
ORDER BY dt.name;
```

### View employees using a specific loan type
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    ed.total_amount,
    ed.remaining_balance,
    ed.installment_amount,
    ed.status
FROM employee_deductions ed
JOIN employees e ON ed.employee_id = e.id
WHERE ed.deduction_type_id = 1  -- Replace with loan type ID
ORDER BY e.last_name;
```

---

## Success Indicators

✅ Loan Types tab appears in Deductions page
✅ "Register Loan Type" button works
✅ Form validates and saves to database
✅ Registered loan types appear in table
✅ Loan types appear in Add Loan modal dropdown
✅ Can assign same loan type to multiple employees
✅ Each employee can have different amounts
✅ Delete protection works for in-use loan types
✅ Can delete unused loan types

---

## Future Enhancements

### Phase 2 (Optional):
- Edit loan type functionality
- Bulk import loan types from CSV
- Loan type templates (pre-configured common loans)
- Loan type history/audit trail
- Loan type categories/tags
- Advanced filtering and sorting

---

## Conclusion

The Loan Type Registry feature is now **fully functional**. Admins can:

1. ✅ Register reusable loan types
2. ✅ Assign them to multiple employees
3. ✅ Each employee gets different amounts/terms
4. ✅ View all registered loan types
5. ✅ Delete unused loan types
6. ✅ Protected from deleting in-use types

This ensures consistency while maintaining flexibility for individual employee loan records.
