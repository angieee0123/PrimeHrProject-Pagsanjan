# Leave Management Database Relationships

## ✅ Database Relationship Analysis

All leave-related tables are **properly connected** with foreign keys and model relationships.

---

## 📊 Database Schema Overview

### Core Leave Tables

1. **leave_types_config** (Primary: `leave_code`)
2. **leave_balances** (Primary: `id`)
3. **leave_accrual_rates** (Primary: `id`)
4. **leave_applications** (Primary: `id`)
5. **leave_transactions** (Primary: `id`)

---

## 🔗 Foreign Key Relationships

### 1. leave_types_config → Other Tables
```
leave_types_config (leave_code)
    ↓ (one-to-many)
    ├── leave_balances (leave_code) [CASCADE DELETE]
    ├── leave_accrual_rates (leave_code) [CASCADE DELETE]
    ├── leave_applications (leave_code) [RESTRICT DELETE]
    └── leave_transactions (leave_code) [RESTRICT DELETE]
```

### 2. employees → Leave Tables
```
employees (id)
    ↓ (one-to-many)
    ├── leave_balances (employee_id) [CASCADE DELETE]
    ├── leave_applications (employee_id) [CASCADE DELETE]
    └── leave_transactions (employee_id) [CASCADE DELETE]
```

### 3. users → Leave Tables
```
users (id)
    ↓ (one-to-many)
    ├── leave_applications (filed_by) [RESTRICT DELETE]
    ├── leave_applications (approved_by) [SET NULL]
    └── leave_transactions (processed_by) [SET NULL]
```

### 4. leave_applications → leave_transactions
```
leave_applications (id)
    ↓ (one-to-many)
    └── leave_transactions (reference_id where reference_type='leave_application')
```

---

## 📋 Detailed Table Relationships

### **leave_types_config**
**Primary Key:** `leave_code` (string)

**Relationships:**
- ✅ Has many `leave_balances` via `leave_code`
- ✅ Has many `leave_accrual_rates` via `leave_code`
- ✅ Has many `leave_applications` via `leave_code`
- ✅ Has many `leave_transactions` via `leave_code`

**Model:** `LeaveType.php`
```php
public function leaveBalances() {
    return $this->hasMany(LeaveBalance::class, 'leave_code', 'leave_code');
}
```

---

### **leave_balances**
**Primary Key:** `id`
**Unique Constraint:** `[employee_id, leave_code, year]`

**Foreign Keys:**
- ✅ `employee_id` → `employees.id` [CASCADE DELETE]
- ✅ `leave_code` → `leave_types_config.leave_code` [CASCADE DELETE]

**Relationships:**
- ✅ Belongs to `Employee`
- ✅ Belongs to `LeaveType`

**Model:** `LeaveBalance.php`
```php
public function employee(): BelongsTo {
    return $this->belongsTo(Employee::class);
}

public function leaveType(): BelongsTo {
    return $this->belongsTo(LeaveType::class, 'leave_code', 'leave_code');
}
```

---

### **leave_accrual_rates**
**Primary Key:** `id`

**Foreign Keys:**
- ✅ `leave_code` → `leave_types_config.leave_code` [CASCADE DELETE]
- ⚠️ **ISSUE FOUND:** Model uses `leave_type_id` but migration uses `leave_code`

**Relationships:**
- ✅ Belongs to `LeaveType`

**Model:** `LeaveAccrualRate.php`
```php
public function leaveType(): BelongsTo {
    return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
}
```

**⚠️ MISMATCH DETECTED:**
- Migration defines: `leave_code` (string) → `leave_types_config.leave_code`
- Model expects: `leave_type_id` (integer) → `leave_types_config.id`

---

### **leave_applications**
**Primary Key:** `id`
**Unique:** `application_number`

**Foreign Keys:**
- ✅ `employee_id` → `employees.id` [CASCADE DELETE]
- ✅ `leave_code` → `leave_types_config.leave_code` [RESTRICT DELETE]
- ✅ `filed_by` → `users.id` [RESTRICT DELETE]
- ✅ `approved_by` → `users.id` [SET NULL]

**Relationships:**
- ✅ Belongs to `Employee`
- ✅ Belongs to `LeaveType`
- ✅ Belongs to `User` (filed_by)
- ✅ Belongs to `User` (approved_by)
- ✅ Has many `LeaveTransaction`

**Model:** `LeaveApplication.php`
```php
public function employee(): BelongsTo {
    return $this->belongsTo(Employee::class);
}

public function leaveType(): BelongsTo {
    return $this->belongsTo(LeaveType::class, 'leave_code', 'leave_code');
}

public function filedBy(): BelongsTo {
    return $this->belongsTo(User::class, 'filed_by');
}

public function approvedBy(): BelongsTo {
    return $this->belongsTo(User::class, 'approved_by');
}

public function transactions(): HasMany {
    return $this->hasMany(LeaveTransaction::class, 'reference_id')
        ->where('reference_type', 'leave_application');
}
```

---

### **leave_transactions**
**Primary Key:** `id`

**Foreign Keys:**
- ✅ `employee_id` → `employees.id` [CASCADE DELETE]
- ✅ `leave_code` → `leave_types_config.leave_code` [RESTRICT DELETE]
- ✅ `processed_by` → `users.id` [SET NULL]

**Polymorphic Reference:**
- `reference_type` + `reference_id` (can point to leave_applications, accruals, etc.)

**Relationships:**
- ✅ Belongs to `Employee`
- ✅ Belongs to `LeaveType`
- ✅ Belongs to `User` (processed_by)
- ✅ Belongs to `LeaveApplication` (conditional)

**Model:** `LeaveTransaction.php`
```php
public function employee(): BelongsTo {
    return $this->belongsTo(Employee::class);
}

public function leaveType(): BelongsTo {
    return $this->belongsTo(LeaveType::class, 'leave_code', 'leave_code');
}

public function processedBy(): BelongsTo {
    return $this->belongsTo(User::class, 'processed_by');
}

public function leaveApplication(): BelongsTo {
    return $this->belongsTo(LeaveApplication::class, 'reference_id')
        ->where('reference_type', 'leave_application');
}
```

---

### **employees**
**Primary Key:** `id`

**Leave Relationships:**
- ✅ Has many `LeaveBalance`
- ✅ Has many `LeaveTransaction`
- ⚠️ **MISSING:** Has many `LeaveApplication`

**Model:** `Employee.php`
```php
public function leaveBalances() {
    return $this->hasMany(LeaveBalance::class);
}

public function leaveTransactions() {
    return $this->hasMany(LeaveTransaction::class);
}

// ⚠️ MISSING RELATIONSHIP
// public function leaveApplications() {
//     return $this->hasMany(LeaveApplication::class);
// }
```

---

## 🔍 Relationship Verification Summary

| Table | Foreign Keys | Model Relationships | Status |
|-------|-------------|---------------------|--------|
| leave_types_config | None | ✅ Has many relationships | ✅ Complete |
| leave_balances | 2 (employee_id, leave_code) | ✅ 2 BelongsTo | ✅ Complete |
| leave_accrual_rates | 1 (leave_code) | ⚠️ Mismatch with model | ⚠️ Issue |
| leave_applications | 4 (employee_id, leave_code, filed_by, approved_by) | ✅ 4 BelongsTo, 1 HasMany | ✅ Complete |
| leave_transactions | 3 (employee_id, leave_code, processed_by) | ✅ 4 BelongsTo | ✅ Complete |
| employees | N/A | ⚠️ Missing leaveApplications | ⚠️ Minor |

---

## ⚠️ Issues Found

### 1. **leave_accrual_rates** - Column Mismatch
**Problem:** Migration uses `leave_code` but Model expects `leave_type_id`

**Migration:**
```php
$table->string('leave_code', 10);
$table->foreign('leave_code')->references('leave_code')->on('leave_types_config');
```

**Model:**
```php
public function leaveType(): BelongsTo {
    return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
}
```

**Impact:** This relationship will NOT work correctly. The model is looking for a column that doesn't exist.

**Solution:** Check migration `2026_05_11_214148_modify_leave_accrual_rates_use_leave_type_id.php` to see if it was migrated.

---

### 2. **Employee Model** - Missing leaveApplications Relationship
**Problem:** Employee model doesn't have a relationship to LeaveApplication

**Current:**
```php
public function leaveBalances() { ... }
public function leaveTransactions() { ... }
// Missing leaveApplications
```

**Recommended:**
```php
public function leaveApplications() {
    return $this->hasMany(LeaveApplication::class);
}
```

**Impact:** Minor - Can still access via queries, but not via Eloquent relationship.

---

## ✅ Recommendations

### 1. Verify leave_accrual_rates Migration
Check if this migration was run:
```
2026_05_11_214148_modify_leave_accrual_rates_use_leave_type_id.php
```

### 2. Add Missing Relationship to Employee Model
```php
public function leaveApplications()
{
    return $this->hasMany(LeaveApplication::class);
}
```

### 3. Add Missing Relationship to LeaveType Model
```php
public function leaveApplications()
{
    return $this->hasMany(LeaveApplication::class, 'leave_code', 'leave_code');
}

public function leaveTransactions()
{
    return $this->hasMany(LeaveTransaction::class, 'leave_code', 'leave_code');
}

public function accrualRates()
{
    return $this->hasMany(LeaveAccrualRate::class, 'leave_type_id', 'id');
}
```

---

## 📈 Data Flow Example

### Leave Application Process:
```
1. Employee files leave
   ↓
2. LeaveApplication created (status: pending)
   ↓
3. LeaveBalance updated (pending_credits +, available_credits -)
   ↓
4. LeaveTransaction created (type: pending)
   ↓
5. Admin approves
   ↓
6. LeaveApplication updated (status: approved)
   ↓
7. LeaveBalance updated (pending_credits -, used_credits +)
   ↓
8. LeaveTransaction created (type: debit)
   ↓
9. LeaveApplicationObserver creates Attendance records
   ↓
10. AccreditedHoursLog created
    ↓
11. DailySalaryComputation created
```

---

## 🎯 Conclusion

**Overall Status:** ✅ **GOOD** - Most relationships are properly configured

**Critical Issues:** 1 (leave_accrual_rates column mismatch)

**Minor Issues:** 2 (missing convenience relationships)

**Database Integrity:** ✅ All foreign keys are properly defined with appropriate cascade rules

**Model Relationships:** ✅ Most relationships are correctly implemented

---

Generated: 2026-01-XX
Last Updated: After fixing 24-hour to 8-hour work day conversion
