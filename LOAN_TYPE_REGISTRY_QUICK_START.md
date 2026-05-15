# Loan Type Registry - Quick Start Guide

## ✅ Setup Complete!

The loan type registry has been successfully set up with proper RDBMS relationships:
- ✓ `loan_types` table created
- ✓ Foreign key to `deduction_types` table established
- ✓ 5 default loan types seeded

## 🎯 How to Use

### Step 1: View Registered Loan Types
1. Go to **Admin → Deductions**
2. Click on **"Loan Types"** tab
3. You'll see 5 pre-registered loan types:
   - GSIS Salary Loan (₱100,000 max, 6% interest, 36 months)
   - GSIS Policy Loan (₱50,000 max, 6% interest, 24 months)
   - GSIS Emergency Loan (₱20,000 max, 6% interest, 12 months)
   - Pag-IBIG Multi-Purpose Loan (₱80,000 max, 10.5% interest, 24 months)
   - Pag-IBIG Calamity Loan (₱40,000 max, 5.95% interest, 24 months)

### Step 2: Register a New Loan Type (Optional)
1. Click **"Register Loan Type"** button
2. Fill in the form:
   - **Provider:** Select from dropdown (GSIS, Pag-IBIG, SSS, Bank, Coop, Other)
   - **Code:** Auto-generated (e.g., GSIS_HOUSING)
   - **Name:** Descriptive name (e.g., Housing Loan)
   - **Max Loanable Amount:** Optional limit (e.g., 500000.00)
   - **Interest Rate:** Optional percentage (e.g., 6.00)
   - **Max Terms:** Optional months (e.g., 60)
   - **Status:** Active/Inactive
3. Click **"Register Loan Type"**

### Step 3: Assign Loan to Employee
1. Go to **"Loans"** tab
2. Click **"Add Loan"** button
3. Select **Employee** from dropdown
4. Select **Loan Type** from dropdown (shows all registered loan types)
   - Notice: Max loanable amount, interest rate, and max terms are displayed
5. Enter **Total Loan Amount**
   - System validates against max loanable amount (if set)
6. Enter **Monthly Installment**
7. Set **Start Date** and **End Date**
8. Select **Status** (Active/Suspended/Completed)
9. Add **Remarks** (optional)
10. Click **"Add Loan"**

## 🔗 RDBMS Relationships

### Database Structure
```
loan_types (Registry)
    ↓ (deduction_type_id FK)
deduction_types (Loan Definitions)
    ↓ (deduction_type_id FK)
employee_deductions (Employee Loans)
    ↓ (employee_id FK)
employees
```

### Key Points
- **One loan type** → **Many employees** can use it
- **Registered once** → **Assigned multiple times**
- **Foreign keys** ensure referential integrity
- **Cascade delete** removes related records

## 📊 Example Workflow

### Scenario: Assign GSIS Salary Loan to 3 Employees

**Loan Type (Already Registered):**
- Name: GSIS Salary Loan
- Max: ₱100,000
- Interest: 6%
- Terms: 36 months

**Employee 1: Juan Dela Cruz**
- Amount: ₱50,000
- Monthly: ₱2,500
- Duration: 20 months

**Employee 2: Maria Santos**
- Amount: ₱80,000
- Monthly: ₱4,000
- Duration: 20 months

**Employee 3: Pedro Reyes**
- Amount: ₱30,000
- Monthly: ₱1,500
- Duration: 20 months

**Result:**
- 1 loan type in registry
- 3 employee loans referencing the same loan type
- All linked via foreign keys

## 🎨 Features

### In "Loan Types" Tab
- ✓ View all registered loan types
- ✓ Filter by provider (GSIS, Pag-IBIG, Other)
- ✓ Filter by status (Active/Inactive)
- ✓ Search by name
- ✓ See employee count using each loan type
- ✓ View loan constraints (max amount, interest, terms)
- ✓ Edit loan type details
- ✓ Delete unused loan types

### In "Add Loan" Modal
- ✓ Dropdown shows all active loan types
- ✓ Grouped by provider (GSIS, Pag-IBIG, Other)
- ✓ Displays loan constraints when selected
- ✓ Validates amount against max loanable
- ✓ Auto-calculates installment based on dates
- ✓ Shows provider name automatically

## 🛡️ Validation Rules

### When Registering Loan Type
- Code must be unique
- Name is required
- Provider is required
- Max loanable amount must be positive (if provided)
- Interest rate must be 0-100% (if provided)
- Max terms must be positive (if provided)

### When Assigning Loan
- Employee must exist
- Loan type must be active
- Total amount must be positive
- Total amount cannot exceed max loanable (if set)
- Monthly installment must be positive
- Start date is required
- End date must be after start date

## 🔍 Verification

### Check Loan Types Table
```sql
SELECT * FROM loan_types WHERE is_active = 1;
```

### Check Deduction Types (Linked)
```sql
SELECT dt.* 
FROM deduction_types dt
INNER JOIN loan_types lt ON dt.id = lt.deduction_type_id
WHERE dt.category = 'LOAN';
```

### Check Employee Loans
```sql
SELECT e.employee_id, e.first_name, e.last_name, 
       lt.name as loan_type, ed.total_amount, ed.remaining_balance
FROM employee_deductions ed
INNER JOIN employees e ON ed.employee_id = e.id
INNER JOIN deduction_types dt ON ed.deduction_type_id = dt.id
INNER JOIN loan_types lt ON dt.id = lt.deduction_type_id
WHERE ed.status = 'ACTIVE';
```

## 📝 Files Modified

### Views
- `resources/views/admin/deductions/modals/addLoanModal.blade.php`
  - Updated to pull from `loan_types` table
  - Shows loan constraints (max amount, interest, terms)
  - Validates against loan type limits

- `resources/views/admin/deductions/modals/addLoanTypeModal.blade.php`
  - Clarified RDBMS relationship in info box

- `resources/views/admin/deductions/partials/loan-types.blade.php`
  - Updated info message about dropdown integration

### Database
- `database/migrations/2026_06_08_000005_create_loan_types_table.php`
  - Creates `loan_types` table with foreign key to `deduction_types`

- `database/seeders/LoanTypesSeeder.php`
  - Seeds 5 default loan types (GSIS and Pag-IBIG loans)

### Models
- `app/Models/LoanType.php`
  - Relationship: `belongsTo(DeductionType::class)`

- `app/Models/DeductionType.php`
  - Relationship: `hasMany(LoanType::class)`

### Routes
- `routes/web.php`
  - POST `/admin/deductions/loan-types/store` (already exists)
  - DELETE `/admin/deductions/loan-types/{id}` (already exists)

## 🚀 Next Steps

1. **Test the system:**
   - Register a new loan type
   - Assign it to an employee
   - Verify the dropdown shows the new loan type

2. **Customize loan types:**
   - Add your organization's specific loan types
   - Set appropriate limits and interest rates

3. **Assign loans to employees:**
   - Use the "Add Loan" modal
   - Select from registered loan types
   - Enter employee-specific amounts

## 📚 Documentation

For detailed information, see:
- `LOAN_TYPE_REGISTRY_RDBMS.md` - Complete RDBMS documentation
- `DEDUCTION_SYSTEM_QUICK_REFERENCE.md` - Deduction system overview

## ✨ Summary

The loan type registry is now fully functional with proper RDBMS design:
- ✅ Loan types stored in `loan_types` table
- ✅ Linked to `deduction_types` via foreign key
- ✅ Appears in "Add Employee Loan" dropdown
- ✅ Multiple employees can use the same loan type
- ✅ Referential integrity maintained
- ✅ 5 default loan types ready to use

You can now register loan types once and assign them to multiple employees!
