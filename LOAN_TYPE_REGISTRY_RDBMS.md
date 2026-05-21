# Loan Type Registry - RDBMS Implementation

## Database Structure

### Tables and Relationships

```
┌─────────────────────┐         ┌──────────────────────┐         ┌─────────────────────────┐
│   loan_types        │         │  deduction_types     │         │  employee_deductions    │
├─────────────────────┤         ├──────────────────────┤         ├─────────────────────────┤
│ id (PK)             │         │ id (PK)              │         │ id (PK)                 │
│ code                │         │ code                 │         │ employee_id (FK)        │
│ name                │    ┌───>│ name                 │<────┐   │ deduction_type_id (FK)  │
│ deduction_type_id ──┘    │    │ category             │     └───│ total_amount            │
│ max_loanable_amount │    │    │ computation_type     │         │ remaining_balance       │
│ interest_rate       │    │    │ is_active            │         │ installment_amount      │
│ max_terms_months    │    │    └──────────────────────┘         │ start_date              │
│ is_active           │    │                                     │ end_date                │
└─────────────────────┘    │                                     │ status                  │
                           │                                     └─────────────────────────┘
                           │
                           │    ┌──────────────────────┐
                           └────│  employees           │
                                ├──────────────────────┤
                                │ id (PK)              │
                                │ employee_id          │
                                │ first_name           │
                                │ last_name            │
                                └──────────────────────┘
```

## How It Works

### 1. Register Loan Type (One-Time Setup)

**Location:** Admin → Deductions → Loan Types Tab → "Register Loan Type" button

**What Happens:**
- Creates a record in `loan_types` table
- Automatically creates a linked record in `deduction_types` table
- Foreign key: `loan_types.deduction_type_id` → `deduction_types.id`

**Example:**
```sql
-- loan_types table
INSERT INTO loan_types (code, name, deduction_type_id, max_loanable_amount, interest_rate, max_terms_months, is_active)
VALUES ('GSIS_HOUSING', 'GSIS Housing Loan', 5, 500000.00, 6.00, 60, 1);

-- deduction_types table (auto-created)
INSERT INTO deduction_types (code, name, category, computation_type, is_active)
VALUES ('LOAN_GSIS_HOUSING', 'GSIS Housing Loan', 'LOAN', 'FIXED', 1);
```

### 2. Assign Loan to Employee (Multiple Times)

**Location:** Admin → Deductions → Loans Tab → "Add Loan" button

**What Happens:**
- Dropdown shows all active loan types from `loan_types` table
- When selected, uses `deduction_type_id` to link to employee
- Creates record in `employee_deductions` table

**Example:**
```sql
-- employee_deductions table
INSERT INTO employee_deductions (employee_id, deduction_type_id, total_amount, remaining_balance, installment_amount, start_date, status)
VALUES (1, 5, 50000.00, 50000.00, 2500.00, '2026-01-01', 'ACTIVE');
```

## Key Benefits

### ✅ RDBMS Compliance
- **Referential Integrity:** Foreign key constraints ensure data consistency
- **Cascade Delete:** Deleting a loan type removes all related records
- **Normalization:** Loan type details stored once, referenced many times

### ✅ Reusability
- Register "GSIS Housing Loan" once
- Assign to 100 employees with different amounts
- Update loan type details in one place

### ✅ Data Consistency
- All employees with "GSIS Housing Loan" reference the same loan type
- Changes to loan type (e.g., interest rate) reflect everywhere
- No duplicate or inconsistent loan type names

## Usage Flow

### Step 1: Register Loan Types (Admin Setup)
```
1. Go to: Admin → Deductions → Loan Types Tab
2. Click: "Register Loan Type"
3. Fill in:
   - Provider: GSIS
   - Code: GSIS_HOUSING (auto-generated)
   - Name: Housing Loan
   - Max Loanable: ₱500,000.00
   - Interest Rate: 6.00%
   - Max Terms: 60 months
4. Submit
```

**Result:** Loan type is now available for assignment

### Step 2: Assign Loan to Employee
```
1. Go to: Admin → Deductions → Loans Tab
2. Click: "Add Loan"
3. Select Employee: Juan Dela Cruz
4. Select Loan Type: GSIS Housing Loan (from dropdown)
5. Enter Amount: ₱50,000.00
6. Enter Monthly Installment: ₱2,500.00
7. Set Dates: Start & End
8. Submit
```

**Result:** Employee now has an active loan linked to the registered loan type

## Database Queries

### Get All Registered Loan Types
```sql
SELECT lt.*, dt.name as deduction_name, dt.category
FROM loan_types lt
INNER JOIN deduction_types dt ON lt.deduction_type_id = dt.id
WHERE lt.is_active = 1;
```

### Get Employees with Specific Loan Type
```sql
SELECT e.employee_id, e.first_name, e.last_name, ed.total_amount, ed.remaining_balance
FROM employee_deductions ed
INNER JOIN employees e ON ed.employee_id = e.id
INNER JOIN deduction_types dt ON ed.deduction_type_id = dt.id
INNER JOIN loan_types lt ON dt.id = lt.deduction_type_id
WHERE lt.code = 'GSIS_HOUSING' AND ed.status = 'ACTIVE';
```

### Get All Loans for an Employee
```sql
SELECT lt.name as loan_type, ed.total_amount, ed.remaining_balance, ed.installment_amount, ed.status
FROM employee_deductions ed
INNER JOIN deduction_types dt ON ed.deduction_type_id = dt.id
INNER JOIN loan_types lt ON dt.id = lt.deduction_type_id
WHERE ed.employee_id = 1 AND dt.category = 'LOAN';
```

## Models and Relationships

### LoanType Model
```php
class LoanType extends Model
{
    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionType::class);
    }
}
```

### DeductionType Model
```php
class DeductionType extends Model
{
    public function loanTypes(): HasMany
    {
        return $this->hasMany(LoanType::class);
    }
    
    public function employeeDeductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class);
    }
}
```

### EmployeeDeduction Model
```php
class EmployeeDeduction extends Model
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionType::class);
    }
}
```

## Routes

### Register Loan Type
```php
POST /admin/deductions/loan-types/store
```

### Get Loan Types for Dropdown
```php
// In addLoanModal.blade.php
$loanTypes = \App\Models\LoanType::with('deductionType')
    ->where('is_active', true)
    ->orderBy('name')
    ->get();
```

### Assign Loan to Employee
```php
POST /admin/deductions/employee/store
```

## Migration

```php
Schema::create('loan_types', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique();
    $table->string('name', 100);
    $table->foreignId('deduction_type_id')->constrained()->onDelete('cascade');
    $table->decimal('max_loanable_amount', 12, 2)->nullable();
    $table->decimal('interest_rate', 5, 2)->nullable();
    $table->integer('max_terms_months')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

## Example Scenario

### Scenario: GSIS Housing Loan for 3 Employees

**Step 1:** Register loan type once
```
Loan Type: GSIS Housing Loan
Max Amount: ₱500,000
Interest: 6%
Max Terms: 60 months
```

**Step 2:** Assign to employees with different amounts
```
Employee 1: Juan Dela Cruz
- Amount: ₱50,000
- Monthly: ₱2,500
- Terms: 20 months

Employee 2: Maria Santos
- Amount: ₱100,000
- Monthly: ₱5,000
- Terms: 20 months

Employee 3: Pedro Reyes
- Amount: ₱75,000
- Monthly: ₱3,750
- Terms: 20 months
```

**Result:**
- 1 record in `loan_types`
- 1 record in `deduction_types`
- 3 records in `employee_deductions`
- All 3 employees reference the same loan type via foreign key

## Validation

### When Registering Loan Type
- Code must be unique
- Name is required
- Provider is required
- Max loanable amount must be positive (if provided)
- Interest rate must be 0-100% (if provided)

### When Assigning Loan
- Employee must exist
- Loan type must be active
- Total amount must be positive
- If max loanable amount is set, total amount cannot exceed it
- Monthly installment must be positive
- Start date is required
- End date must be after start date

## Troubleshooting

### Loan Type Not Appearing in Dropdown
**Check:**
1. Is `is_active` = 1 in `loan_types` table?
2. Is `is_active` = 1 in `deduction_types` table?
3. Does `deduction_type_id` foreign key exist?

### Cannot Delete Loan Type
**Reason:** Employees are currently using this loan type

**Solution:**
1. Complete or suspend all employee loans of this type
2. Then delete the loan type

### Duplicate Loan Types
**Prevention:** Use unique constraint on `loan_types.code`

**Fix:** Merge duplicate records and update foreign keys

## Best Practices

1. **Register loan types before assigning to employees**
2. **Use descriptive names** (e.g., "GSIS Housing Loan" not "Loan 1")
3. **Set realistic constraints** (max amount, interest rate, terms)
4. **Keep loan types active** unless permanently discontinued
5. **Don't delete loan types** that have active employee loans
6. **Use consistent naming** (e.g., all GSIS loans start with "GSIS")

## Summary

The Loan Type Registry implements proper RDBMS design by:
- Storing loan types once in `loan_types` table
- Linking to `deduction_types` via foreign key
- Allowing multiple employees to reference the same loan type
- Maintaining referential integrity through constraints
- Enabling reusability and consistency across the system

This ensures that loan types are managed centrally and can be assigned to multiple employees without duplication.
