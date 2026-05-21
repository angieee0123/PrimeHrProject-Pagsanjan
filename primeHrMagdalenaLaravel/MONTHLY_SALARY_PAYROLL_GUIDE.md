# Monthly Salary Rate Determination in Payroll System

## Database Structure

### Table Relationships
```
employees
    └── employment_details (employee_id)
            ├── department_id → departments
            └── designation_id → designations
                    └── monthly_rate (decimal 12,2)
```

## How Monthly Salary is Determined

### 1. **Employee → Employment Details → Designation → Monthly Rate**

```php
// Get employee's monthly salary
$monthlySalary = $employee->employmentDetail
                         ?->designationRelation
                         ?->monthly_rate ?? 0;
```

### 2. **Database Tables Involved**

#### `employees` table
- `id` - Employee primary key
- `first_name`, `last_name` - Employee name
- Other personal information

#### `employment_details` table
- `id` - Primary key
- `employee_id` - Foreign key to employees
- `designation_id` - Foreign key to designations
- `department_id` - Foreign key to departments
- `employment_status` - Permanent, Casual, etc.
- `appointment_date`, `salary_grade`, `step_increment`

#### `designations` table
- `id` - Primary key
- `title` - Position title (e.g., "Municipal Health Officer")
- `department_id` - Foreign key to departments
- `salary_grade` - Salary grade (e.g., "SG-24")
- **`monthly_rate`** - Monthly salary amount (e.g., 35000.00)
- `employment_type` - Permanent, Casual, etc.

#### `departments` table
- `id` - Primary key
- `code` - Department code (e.g., "MHO")
- `name` - Department name (e.g., "Municipal Health Office")

## Payroll Deduction Calculation with Monthly Salary

### Current Implementation (Already Working!)

The payroll system **already correctly** retrieves and uses monthly salary rates for deductions:

#### For Monthly/Employee View:
```php
if ($deductionType->base_salary_type === 'MONTHLY') {
    // Get monthly salary from designation
    $baseAmount = $employee->employmentDetail
                          ?->designationRelation
                          ?->monthly_rate ?? 0;
}

$deductions[$code] = $baseAmount * ($deductionType->percentage_rate / 100);
```

#### For Daily View:
```php
if ($deductionType->base_salary_type === 'MONTHLY') {
    // Get monthly salary from designation and prorate to daily
    $monthlySalary = $employee->employmentDetail
                             ?->designationRelation
                             ?->monthly_rate ?? 0;
    $baseAmount = $monthlySalary / 22; // Prorated daily
}

$deductions[$code] = $baseAmount * ($deductionType->percentage_rate / 100);
```

## Example Scenarios

### Example 1: PhilHealth (2.5% of Monthly Salary)

**Employee:** Juan Dela Cruz
- **Department:** Municipal Health Office (MHO)
- **Designation:** Municipal Health Officer
- **Monthly Rate:** ₱35,000.00

**Deduction Type:** PhilHealth
- **Category:** MANDATORY
- **Computation Type:** PERCENTAGE
- **Rate:** 2.5%
- **Base Salary Type:** MONTHLY

**Calculation:**
```
PhilHealth = ₱35,000.00 × 2.5% = ₱875.00
```

### Example 2: GSIS (9% of Monthly Salary)

**Employee:** Maria Santos
- **Department:** Office of the Mayor (OM)
- **Designation:** Administrative Assistant
- **Monthly Rate:** ₱18,000.00

**Deduction Type:** GSIS
- **Category:** MANDATORY
- **Computation Type:** PERCENTAGE
- **Rate:** 9%
- **Base Salary Type:** MONTHLY

**Calculation:**
```
GSIS = ₱18,000.00 × 9% = ₱1,620.00
```

### Example 3: Pag-IBIG (2% of Monthly Salary, Max ₱100)

**Employee:** Pedro Reyes
- **Department:** Municipal Engineering Office (MEO)
- **Designation:** Municipal Engineer
- **Monthly Rate:** ₱40,000.00

**Deduction Type:** Pag-IBIG
- **Category:** MANDATORY
- **Computation Type:** PERCENTAGE
- **Rate:** 2%
- **Base Salary Type:** MONTHLY
- **Max Amount:** ₱100.00

**Calculation:**
```
Pag-IBIG = ₱40,000.00 × 2% = ₱800.00
But capped at ₱100.00 (max amount)
Final: ₱100.00
```

## Where Monthly Salary is Used in Payroll

### 1. **Payroll Register View** (`/admin/payroll`)
- Route: `Route::get('/admin/payroll', ...)`
- File: `routes/web.php` (lines ~600-750)
- Calculates deductions for each employee based on their monthly salary

### 2. **Payroll Export** (`/admin/payroll/export`)
- Route: `Route::get('/admin/payroll/export', ...)`
- File: `routes/web.php` (lines ~900-1100)
- Exports payroll with deductions calculated from monthly salary

### 3. **Payroll Calculate** (`/admin/payroll/calculate`)
- Route: `Route::post('/admin/payroll/calculate', ...)`
- File: `routes/web.php` (lines ~800-900)
- Used for payroll preview/calculation

## How to Set Up Monthly Salary-Based Deductions

### Step 1: Ensure Employees Have Designations with Monthly Rates

1. Go to **Departments** page
2. Add/Edit designations
3. Set the **Monthly Rate** for each designation
4. Example:
   - Municipal Health Officer: ₱35,000.00
   - Administrative Assistant: ₱18,000.00
   - Municipal Engineer: ₱40,000.00

### Step 2: Assign Designations to Employees

1. Go to **Personnel** page
2. Edit employee
3. Select their **Designation** (which has the monthly rate)
4. The system automatically uses the monthly rate from the designation

### Step 3: Create Deduction Types with Monthly Base

1. Go to **Deductions** → **Deduction Types**
2. Click **Add Deduction Type**
3. Fill in:
   - **Code:** PHILHEALTH
   - **Name:** PhilHealth Contribution
   - **Category:** MANDATORY
   - **Computation Type:** PERCENTAGE
   - **Rate (%):** 2.5
   - **Base Salary:** Monthly Salary ← **Select this!**
   - **Max Amount:** 4800.00 (optional)
   - **Status:** Active

### Step 4: Assign Deductions to Employees

1. Go to **Deductions** → **Employee Deductions**
2. Click **Assign Deduction**
3. Select employee and deduction type
4. The system will automatically calculate based on their monthly salary

### Step 5: Generate Payroll

1. Go to **Payroll** → **Generate Payroll**
2. Select date range and filters
3. Click **Generate Payroll**
4. The system calculates all deductions including those based on monthly salary

## Verification

To verify monthly salary is being used correctly:

1. **Check Employee's Monthly Rate:**
   ```sql
   SELECT e.first_name, e.last_name, d.title, d.monthly_rate
   FROM employees e
   JOIN employment_details ed ON e.id = ed.employee_id
   JOIN designations d ON ed.designation_id = d.id
   WHERE e.id = [employee_id];
   ```

2. **Check Deduction Calculation:**
   - Go to Payroll Register
   - Find employee
   - Verify deduction amount = monthly_rate × percentage_rate

3. **Example Verification:**
   - Employee monthly rate: ₱35,000.00
   - PhilHealth rate: 2.5%
   - Expected deduction: ₱875.00
   - Check if payroll shows ₱875.00 in PhilHealth column

## Important Notes

1. **Monthly Rate is Required:** If an employee's designation doesn't have a monthly_rate set, the deduction will be ₱0.00

2. **Daily Proration:** In daily view, monthly salary is divided by 22 working days

3. **Accurate Calculations:** Using monthly salary ensures consistent deductions regardless of:
   - Days worked in the period
   - Overtime hours
   - Late/undertime deductions

4. **Government Compliance:** This method follows government contribution rules where deductions are based on monthly salary, not actual earnings

## Summary

✅ **The system is already working correctly!**

The payroll system already:
- Retrieves monthly salary from `designations.monthly_rate`
- Uses the relationship: `employee → employment_details → designation → monthly_rate`
- Calculates deductions based on monthly salary when `base_salary_type = 'MONTHLY'`
- Works in both daily and monthly payroll views
- Includes monthly salary-based deductions in exports

**No code changes needed** - just ensure:
1. Run the migration to add MONTHLY to the enum
2. Set monthly rates in designations
3. Assign designations to employees
4. Create deduction types with MONTHLY base salary
