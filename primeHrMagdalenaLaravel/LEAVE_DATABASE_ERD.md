# Leave System - Entity Relationship Diagram (ERD)

## 🗺️ Visual Database Schema

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         LEAVE MANAGEMENT SYSTEM                              │
│                         Database Relationships                               │
└─────────────────────────────────────────────────────────────────────────────┘


                              ┌──────────────────────┐
                              │   EMPLOYEES          │
                              │  ─────────────────   │
                              │  PK: id              │
                              │  • employee_id       │
                              │  • first_name        │
                              │  • last_name         │
                              └──────────┬───────────┘
                                         │
                    ┌────────────────────┼────────────────────┐
                    │                    │                    │
                    │ 1:N                │ 1:N                │ 1:N
                    ↓                    ↓                    ↓
        ┌───────────────────┐ ┌──────────────────┐ ┌──────────────────┐
        │ LEAVE_BALANCES    │ │ LEAVE_           │ │ LEAVE_           │
        │ ───────────────── │ │ APPLICATIONS     │ │ TRANSACTIONS     │
        │ PK: id            │ │ ──────────────── │ │ ──────────────── │
        │ FK: employee_id ──┼─┤ PK: id           │ │ PK: id           │
        │ FK: leave_code    │ │ FK: employee_id ─┼─┤ FK: employee_id  │
        │ • year            │ │ FK: leave_code   │ │ FK: leave_code   │
        │ • total_credits   │ │ FK: filed_by     │ │ FK: processed_by │
        │ • used_credits    │ │ FK: approved_by  │ │ • reference_type │
        │ • pending_credits │ │ • start_date     │ │ • reference_id   │
        │ • available_      │ │ • end_date       │ │ • amount         │
        │   credits         │ │ • number_of_days │ │ • balance_before │
        │ • carried_over    │ │ • status         │ │ • balance_after  │
        └─────────┬─────────┘ └────────┬─────────┘ └────────┬─────────┘
                  │                    │                    │
                  │                    │                    │
                  │ N:1                │ N:1                │ N:1
                  │                    │                    │
                  └────────────────────┼────────────────────┘
                                       ↓
                          ┌────────────────────────┐
                          │ LEAVE_TYPES_CONFIG     │
                          │ ────────────────────── │
                          │ PK: id                 │
                          │ UK: leave_code         │
                          │ • leave_name           │
                          │ • annual_limit         │
                          │ • is_accrued           │
                          │ • is_cumulative        │
                          │ • requires_6_months    │
                          │ • is_monetizable       │
                          │ • requires_attachment  │
                          │ • is_active            │
                          └────────────┬───────────┘
                                       │
                                       │ 1:N
                                       ↓
                          ┌────────────────────────┐
                          │ LEAVE_ACCRUAL_RATES    │
                          │ ────────────────────── │
                          │ PK: id                 │
                          │ FK: leave_type_id      │
                          │ • accrual_frequency    │
                          │ • days_of_service_req  │
                          │ • credits_earned       │
                          │ • effective_date       │
                          │ • end_date             │
                          │ • is_active            │
                          └────────────────────────┘


                              ┌──────────────────────┐
                              │   USERS              │
                              │  ─────────────────   │
                              │  PK: id              │
                              │  • username          │
                              │  • role              │
                              └──────────┬───────────┘
                                         │
                    ┌────────────────────┼────────────────────┐
                    │                    │                    │
                    │ 1:N                │ 1:N                │ 1:N
                    ↓                    ↓                    ↓
        ┌───────────────────┐ ┌──────────────────┐ ┌──────────────────┐
        │ filed_by          │ │ approved_by      │ │ processed_by     │
        │ (applications)    │ │ (applications)   │ │ (transactions)   │
        └───────────────────┘ └──────────────────┘ └──────────────────┘
```

---

## 🔗 Foreign Key Relationships

### Table: leave_balances
```sql
FK: employee_id    → employees.id         [CASCADE DELETE]
FK: leave_code     → leave_types_config.leave_code [CASCADE DELETE]
UNIQUE: (employee_id, leave_code, year)
```

### Table: leave_applications
```sql
FK: employee_id    → employees.id         [CASCADE DELETE]
FK: leave_code     → leave_types_config.leave_code [RESTRICT DELETE]
FK: filed_by       → users.id             [RESTRICT DELETE]
FK: approved_by    → users.id             [SET NULL]
UNIQUE: application_number
```

### Table: leave_transactions
```sql
FK: employee_id    → employees.id         [CASCADE DELETE]
FK: leave_code     → leave_types_config.leave_code [RESTRICT DELETE]
FK: processed_by   → users.id             [SET NULL]
INDEX: (employee_id, leave_code, year)
INDEX: transaction_date
INDEX: (reference_type, reference_id)
```

### Table: leave_accrual_rates
```sql
FK: leave_type_id  → leave_types_config.id [CASCADE DELETE]
```

---

## 📊 Cardinality Summary

| Relationship | Type | Description |
|--------------|------|-------------|
| Employee → LeaveBalance | 1:N | One employee has many leave balances (per year, per type) |
| Employee → LeaveApplication | 1:N | One employee can file many leave applications |
| Employee → LeaveTransaction | 1:N | One employee has many leave transactions |
| LeaveType → LeaveBalance | 1:N | One leave type has many employee balances |
| LeaveType → LeaveApplication | 1:N | One leave type has many applications |
| LeaveType → LeaveTransaction | 1:N | One leave type has many transactions |
| LeaveType → LeaveAccrualRate | 1:N | One leave type has many accrual rate versions |
| User → LeaveApplication (filed) | 1:N | One user can file many applications |
| User → LeaveApplication (approved) | 1:N | One user can approve many applications |
| User → LeaveTransaction (processed) | 1:N | One user can process many transactions |
| LeaveApplication → LeaveTransaction | 1:N | One application generates multiple transactions |

---

## 🎯 Key Constraints

### Primary Keys
- ✅ All tables have auto-increment `id` as primary key
- ✅ `leave_types_config` also has unique `leave_code`

### Unique Constraints
- ✅ `leave_balances`: (employee_id, leave_code, year)
- ✅ `leave_applications`: application_number
- ✅ `leave_types_config`: leave_code

### Indexes
- ✅ All foreign key columns are indexed
- ✅ Date columns are indexed for performance
- ✅ Composite indexes on frequently queried combinations

---

## 🔄 Data Flow Diagram

```
┌─────────────┐
│  Employee   │
│  Files      │
│  Leave      │
└──────┬──────┘
       │
       ↓
┌─────────────────────────────────────────────────────────┐
│  1. CREATE leave_applications                           │
│     - status: 'pending'                                 │
│     - filed_by: user_id                                 │
└──────┬──────────────────────────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────────────────────────┐
│  2. UPDATE leave_balances                               │
│     - pending_credits += number_of_days                 │
│     - available_credits -= number_of_days               │
└──────┬──────────────────────────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────────────────────────┐
│  3. CREATE leave_transactions                           │
│     - transaction_type: 'pending'                       │
│     - amount: -number_of_days                           │
│     - reference_type: 'leave_application'               │
│     - reference_id: application.id                      │
└──────┬──────────────────────────────────────────────────┘
       │
       ↓
┌─────────────┐
│   Admin     │
│  Approves   │
└──────┬──────┘
       │
       ↓
┌─────────────────────────────────────────────────────────┐
│  4. UPDATE leave_applications                           │
│     - status: 'approved'                                │
│     - approved_by: user_id                              │
│     - approved_at: timestamp                            │
└──────┬──────────────────────────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────────────────────────┐
│  5. UPDATE leave_balances                               │
│     - pending_credits -= number_of_days                 │
│     - used_credits += number_of_days                    │
└──────┬──────────────────────────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────────────────────────┐
│  6. CREATE leave_transactions                           │
│     - transaction_type: 'debit'                         │
│     - amount: -number_of_days                           │
└──────┬──────────────────────────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────────────────────────┐
│  7. LeaveApplicationObserver TRIGGERED                  │
│     - Creates attendance records (ON_LEAVE)             │
│     - Creates accredited_hours_log                      │
│     - Creates daily_salary_computation                  │
└─────────────────────────────────────────────────────────┘
```

---

## 🛡️ Data Integrity Rules

### CASCADE DELETE
When a parent record is deleted, child records are automatically deleted:
- Delete `employee` → Deletes all their `leave_balances`, `leave_applications`, `leave_transactions`
- Delete `leave_type` → Deletes all `leave_balances`, `leave_accrual_rates`

### RESTRICT DELETE
Prevents deletion if child records exist:
- Cannot delete `leave_type` if `leave_applications` or `leave_transactions` exist
- Cannot delete `user` if they filed any `leave_applications`

### SET NULL
When parent is deleted, foreign key is set to NULL:
- Delete `user` → Sets `approved_by` to NULL in `leave_applications`
- Delete `user` → Sets `processed_by` to NULL in `leave_transactions`

---

## 📈 Query Examples

### Get Employee Leave Balance
```php
$employee->leaveBalances()
    ->where('year', 2026)
    ->where('leave_code', 'VL')
    ->first();
```

### Get All Applications for a Leave Type
```php
$leaveType->leaveApplications()
    ->where('status', 'approved')
    ->get();
```

### Get Transaction History
```php
$employee->leaveTransactions()
    ->where('leave_code', 'SL')
    ->where('year', 2026)
    ->orderBy('transaction_date', 'desc')
    ->get();
```

### Get Application with All Related Data
```php
LeaveApplication::with([
    'employee',
    'leaveType',
    'filedBy',
    'approvedBy',
    'transactions'
])->find($id);
```

---

## ✅ Verification Checklist

- [x] All tables created with proper structure
- [x] All foreign keys defined and working
- [x] All model relationships implemented
- [x] Cascade rules properly configured
- [x] Unique constraints in place
- [x] Indexes optimized for queries
- [x] Data integrity maintained
- [x] Business logic aligned with database structure

---

**Status:** ✅ **ALL RELATIONSHIPS VERIFIED AND WORKING**

**Last Updated:** After fixing 24-hour to 8-hour work day conversion

**Confidence:** 100% - All relationships tested and documented
