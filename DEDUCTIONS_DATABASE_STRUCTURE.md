# Deductions System - Database Structure

## ✅ Database Schema with Foreign Keys (RDBMS)

### 1. **deduction_types** (Parent Table)
```
id (PK)
code (UNIQUE)
name
category (MANDATORY, LOAN, OTHER)
computation_type (PERCENTAGE, FIXED, CUSTOM)
percentage_rate
base_salary_type (BASIC, GROSS, CUSTOM)
max_amount
is_active
created_at
updated_at
```

### 2. **deduction_schedules** (Child of deduction_types)
```
id (PK)
deduction_type_id (FK → deduction_types.id) CASCADE DELETE
cutoff_schedule (1ST_ONLY, 2ND_ONLY, BOTH_SPLIT, BOTH_FULL)
priority_order
is_active
effective_date
created_at
updated_at
```

### 3. **employee_deductions** (Child of deduction_types & employees)
```
id (PK)
employee_id (FK → employees.id) CASCADE DELETE
deduction_type_id (FK → deduction_types.id) CASCADE DELETE
amount
start_date
end_date
remaining_balance
total_amount
installment_amount
status (ACTIVE, COMPLETED, SUSPENDED)
remarks
created_at
updated_at
```

### 4. **loan_types** (Child of deduction_types)
```
id (PK)
code (UNIQUE)
name
deduction_type_id (FK → deduction_types.id) CASCADE DELETE
max_loanable_amount
interest_rate
max_terms_months
is_active
created_at
updated_at
```

### 5. **payroll_deductions** (Child of employee_deductions)
```
id (PK)
employee_deduction_id (FK → employee_deductions.id) CASCADE DELETE
payroll_id (FK → payrolls.id) CASCADE DELETE
amount_deducted
cutoff_period (1ST, 2ND)
deduction_date
created_at
updated_at
```

## 🔗 Relationships

### DeductionType Model
- `hasMany` → DeductionSchedule
- `hasMany` → EmployeeDeduction
- `hasMany` → LoanType

### DeductionSchedule Model
- `belongsTo` → DeductionType

### EmployeeDeduction Model
- `belongsTo` → Employee
- `belongsTo` → DeductionType
- `hasMany` → PayrollDeduction

### LoanType Model
- `belongsTo` → DeductionType

## 📊 Data Flow

```
DeductionType (Master Data)
    ↓
    ├─→ DeductionSchedule (When to deduct)
    ├─→ EmployeeDeduction (Who has this deduction)
    │       ↓
    │       └─→ PayrollDeduction (Transaction history)
    └─→ LoanType (Loan-specific details)
```

## 🌱 Seeded Data

The system comes pre-seeded with:

**Mandatory Deductions:**
1. GSIS (9% of Basic, 1st Cutoff)
2. PhilHealth (2.5% of Basic, 1st Cutoff)
3. Pag-IBIG (2% of Basic, max ₱100, 2nd Cutoff)
4. Withholding Tax (Custom, Both Cutoffs Split)

**Loan Types:**
5. GSIS Salary Loan
6. GSIS Policy Loan
7. Pag-IBIG MPL
8. Pag-IBIG Housing Loan

## 🔧 To Initialize Database

```bash
# Run migrations
php artisan migrate

# Seed default deductions
php artisan db:seed --class=DeductionTypesSeeder
```

## ✅ Verification

The deduction-types.blade.php now fetches data from database:
- Uses `DeductionType::with('schedules')` for eager loading
- Displays all deduction types dynamically
- Shows proper badges for categories
- Handles empty state
- Counts are dynamic

## 🎯 Benefits of This Structure

1. **Referential Integrity**: Foreign keys ensure data consistency
2. **Cascade Deletes**: Removing a deduction type cleans up related data
3. **Normalized**: No data duplication
4. **Scalable**: Easy to add new deduction types
5. **Auditable**: Timestamps on all tables
6. **Flexible**: Supports percentage, fixed, and custom computations
