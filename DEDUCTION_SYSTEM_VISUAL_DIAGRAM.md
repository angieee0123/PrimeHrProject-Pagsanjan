# Unified Deduction System - Visual Diagram

## Database Relationship Diagram

```
┌─────────────────────┐
│  deduction_types    │
│─────────────────────│
│ id (PK)             │
│ code                │◄──────────┐
│ name                │           │
│ category            │           │
│ computation_type    │           │
│ percentage_rate     │           │
│ base_salary_type    │           │
│ max_amount          │           │
│ is_active           │           │
└─────────────────────┘           │
         │                        │
         │ 1:N                    │
         ▼                        │
┌─────────────────────┐           │
│ deduction_schedules │           │
│─────────────────────│           │
│ id (PK)             │           │
│ deduction_type_id   │───────────┘
│ cutoff_schedule     │
│ priority_order      │
│ is_active           │
│ effective_date      │
└─────────────────────┘


┌─────────────────────┐           ┌─────────────────────┐
│     employees       │           │  deduction_types    │
│─────────────────────│           │─────────────────────│
│ id (PK)             │◄──┐   ┌──►│ id (PK)             │
│ first_name          │   │   │   │ code                │
│ last_name           │   │   │   │ name                │
│ ...                 │   │   │   │ ...                 │
└─────────────────────┘   │   │   └─────────────────────┘
                          │   │
                          │   │
                    ┌─────┴───┴─────┐
                    │employee_       │
                    │deductions      │
                    │────────────────│
                    │ id (PK)        │
                    │ employee_id    │
                    │ deduction_     │
                    │   type_id      │
                    │ amount         │
                    │ start_date     │
                    │ end_date       │
                    │ remaining_     │
                    │   balance      │
                    │ total_amount   │
                    │ installment_   │
                    │   amount       │
                    │ status         │
                    │ remarks        │
                    └────────────────┘
                          │
                          │ 1:N
                          ▼
                    ┌─────────────────────┐
                    │ payroll_deductions  │
                    │─────────────────────│
                    │ id (PK)             │
                    │ payroll_id          │
                    │ employee_id         │
                    │ employee_           │
                    │   deduction_id      │
                    │ deduction_type_id   │
                    │ cutoff_period       │
                    │ amount_deducted     │
                    │ computation_        │
                    │   details (JSON)    │
                    │ deduction_date      │
                    └─────────────────────┘


┌─────────────────────┐
│  deduction_types    │
│─────────────────────│
│ id (PK)             │◄──────────┐
│ code                │           │
│ name                │           │
│ ...                 │           │
└─────────────────────┘           │
                                  │
                          ┌───────┴────────┐
                          │  loan_types    │
                          │────────────────│
                          │ id (PK)        │
                          │ code           │
                          │ name           │
                          │ deduction_     │
                          │   type_id      │
                          │ max_loanable_  │
                          │   amount       │
                          │ interest_rate  │
                          │ max_terms_     │
                          │   months       │
                          │ is_active      │
                          └────────────────┘
```

---

## Payroll Processing Flow

```
┌─────────────────────────────────────────────────────────┐
│                    START PAYROLL                        │
│              (Employee, Cutoff Period)                  │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  Get Active Employee Deductions                         │
│  - Status = ACTIVE                                      │
│  - start_date <= today                                  │
│  - end_date >= today OR NULL                            │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  For Each Deduction:                                    │
│  1. Get Deduction Type                                  │
│  2. Get Deduction Schedule                              │
│  3. Check if applies to current cutoff                  │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
         ┌───────────┴───────────┐
         │                       │
         ▼                       ▼
┌────────────────┐      ┌────────────────┐
│  1ST Cutoff?   │      │  2ND Cutoff?   │
│  - 1ST_ONLY    │      │  - 2ND_ONLY    │
│  - BOTH_SPLIT  │      │  - BOTH_SPLIT  │
│  - BOTH_FULL   │      │  - BOTH_FULL   │
└────────┬───────┘      └────────┬───────┘
         │                       │
         └───────────┬───────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  Calculate Deduction Amount                             │
│  - PERCENTAGE: salary × rate ÷ 100                      │
│  - FIXED: installment_amount                            │
│  - CUSTOM: custom logic (e.g., tax table)               │
│                                                          │
│  If BOTH_SPLIT: amount ÷ 2                              │
│  If max_amount: MIN(amount, max_amount)                 │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  Create Payroll Deduction Record                        │
│  - employee_id                                          │
│  - deduction_type_id                                    │
│  - amount_deducted                                      │
│  - cutoff_period                                        │
│  - computation_details (JSON)                           │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
         ┌───────────┴───────────┐
         │                       │
         ▼                       ▼
┌────────────────┐      ┌────────────────┐
│  Is Loan?      │      │  Is Mandatory? │
│  YES           │      │  YES           │
└────────┬───────┘      └────────┬───────┘
         │                       │
         ▼                       │
┌────────────────┐               │
│ Update Balance │               │
│ - remaining -= │               │
│   amount       │               │
│ - If balance   │               │
│   <= 0:        │               │
│   status =     │               │
│   COMPLETED    │               │
└────────┬───────┘               │
         │                       │
         └───────────┬───────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                    END PAYROLL                          │
│         Return Total Deductions & Net Pay               │
└─────────────────────────────────────────────────────────┘
```

---

## Example: Employee Monthly Deductions

### Employee: Juan Dela Cruz
### Basic Salary: ₱25,000/month

```
┌──────────────────────────────────────────────────────────┐
│                    1ST CUTOFF (Days 1-15)                │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Gross Pay (Half Month):           ₱12,500.00           │
│                                                          │
│  Deductions:                                             │
│  ┌────────────────────────────────────────────────┐     │
│  │ GSIS (9% of ₱25,000)           ₱2,250.00      │     │
│  │ PhilHealth (2.5% of ₱25,000)     ₱625.00      │     │
│  │ Withholding Tax (50%)            ₱500.00      │     │
│  │ GSIS Loan (if any)               ₱500.00      │     │
│  └────────────────────────────────────────────────┘     │
│                                                          │
│  Total Deductions:                  ₱3,875.00           │
│                                                          │
│  NET PAY:                           ₱8,625.00            │
│                                                          │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│                   2ND CUTOFF (Days 16-31)                │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Gross Pay (Half Month):           ₱12,500.00           │
│                                                          │
│  Deductions:                                             │
│  ┌────────────────────────────────────────────────┐     │
│  │ Pag-IBIG (2% of ₱25,000, max ₱100) ₱100.00   │     │
│  │ Withholding Tax (50%)            ₱500.00      │     │
│  └────────────────────────────────────────────────┘     │
│                                                          │
│  Total Deductions:                    ₱600.00           │
│                                                          │
│  NET PAY:                          ₱11,900.00            │
│                                                          │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│                    MONTHLY SUMMARY                       │
├──────────────────────────────────────────────────────────┤
│  Total Gross:                      ₱25,000.00            │
│  Total Deductions:                  ₱4,475.00            │
│  Total Net Pay:                    ₱20,525.00            │
└──────────────────────────────────────────────────────────┘
```

---

## Deduction Categories

```
┌─────────────────────────────────────────────────────────┐
│                    DEDUCTION TYPES                      │
└─────────────────────────────────────────────────────────┘
                          │
        ┌─────────────────┼─────────────────┐
        │                 │                 │
        ▼                 ▼                 ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  MANDATORY   │  │    LOAN      │  │    OTHER     │
├──────────────┤  ├──────────────┤  ├──────────────┤
│ • GSIS       │  │ • GSIS Salary│  │ • Union Dues │
│ • PhilHealth │  │ • GSIS Policy│  │ • Coop Share │
│ • Pag-IBIG   │  │ • Pag-IBIG   │  │ • Insurance  │
│ • W-Tax      │  │   MPL        │  │ • Others     │
│              │  │ • Pag-IBIG   │  │              │
│              │  │   Housing    │  │              │
└──────────────┘  └──────────────┘  └──────────────┘
```

---

## Flexibility Features

```
┌─────────────────────────────────────────────────────────┐
│              SYSTEM FLEXIBILITY FEATURES                │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ✓ Change deduction schedule per type                   │
│    (1st only, 2nd only, both, split)                    │
│                                                          │
│  ✓ Set priority order for deductions                    │
│    (important when net pay is insufficient)             │
│                                                          │
│  ✓ Employee-specific overrides                          │
│    (custom amounts, dates, status)                      │
│                                                          │
│  ✓ Automatic loan balance tracking                      │
│    (remaining balance, auto-complete)                   │
│                                                          │
│  ✓ Complete audit trail                                 │
│    (payroll_deductions table)                           │
│                                                          │
│  ✓ Easy to add new deduction types                      │
│    (no schema changes needed)                           │
│                                                          │
│  ✓ Effective date support                               │
│    (schedule changes with history)                      │
│                                                          │
└─────────────────────────────────────────────────────────┘
```
