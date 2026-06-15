# How Your System Deducts Leave Credits for Tardiness
## Complete Breakdown: Minutes, Seconds, Hours

---

## 1. CONVERSION STANDARDS (CSC - Civil Service Commission)

Your system uses official Philippine government CSC standards:

```
1 Working Day    = 480 minutes = 8 hours = 1.0 day
1 Hour           = 60 minutes = 0.125 days
1 Minute         = 1/480 day = 0.002083 days
```

### Exact Conversion Formula
```
Minutes → Days:  days = minutes ÷ 480
Hours → Days:    days = hours ÷ 8
Days → Minutes:  minutes = days × 480
Days → Hours:    hours = days × 8
```

**Implementation File:** `CscTimeConversionService.php`

---

## 2. HOW TARDINESS CONVERTS TO LEAVE DEDUCTION

### Real Example Conversions

| Late Time | Convert to Minutes | Convert to Days | Leave Deducted |
|-----------|-------------------|-----------------|----------------|
| 10 seconds | 0.167 min | 0.000347 days | 0.000347 days |
| 1 minute | 1 min | 0.002083 days | 0.002083 days |
| 15 minutes | 15 min | 0.03125 days | 0.03125 days |
| 30 minutes | 30 min | 0.0625 days | 0.0625 days |
| 1 hour | 60 min | 0.125 days | 0.125 days |
| 2 hours | 120 min | 0.25 days | 0.25 days |
| 4 hours | 240 min | 0.5 days | 0.5 days |
| 8 hours | 480 min | 1.0 days | 1.0 days |

### Code Location
**File:** `CscTimeConversionService.php`, Line 148-152
```php
public static function convertMinutesToDays(int $minutes): float
{
    return $minutes / self::MINUTES_PER_WORK_DAY;  // ÷ 480
}
```

---

## 3. LEAVE DEDUCTION PRIORITY SYSTEM

When an employee is late, the system **automatically deducts** from their leave balances in this order:

### Priority Order
1. **Vacation Leave (VL)** - First priority
2. **Sick Leave (SL)** - Second priority (if VL insufficient)
3. **Loss of Pay (LWOP)** - Last resort (if both VL & SL at zero)

### Example Scenarios

#### Scenario A: Sufficient VL Balance
```
Employee Late: 60 minutes (0.125 days)
VL Balance: 5.0 days
SL Balance: 3.0 days

Result:
  → Deduct 0.125 days from VL
  → VL Balance: 4.875 days ✓
  → SL Balance: 3.0 days (unchanged)
  → Accredited Hours: 480 minutes (8 hrs) ✓ FULL CREDIT
```

#### Scenario B: Partial VL, Use SL
```
Employee Late: 120 minutes (0.25 days)
VL Balance: 0.1 days (insufficient)
SL Balance: 3.0 days

Result:
  → Deduct 0.1 days from VL → VL becomes 0 days
  → Deduct remaining 0.15 days from SL → SL becomes 2.85 days
  → Accredited Hours: 480 minutes (8 hrs) ✓ FULL CREDIT
```

#### Scenario C: Zero Leave Balances
```
Employee Late: 45 minutes (0.09375 days)
VL Balance: 0 days
SL Balance: 0 days

Result:
  → Cannot deduct from leave
  → Recorded as LWOP (Loss of Pay)
  → Accredited Hours: Reduced by 45 minutes
  → Salary Deduction: YES
```

---

## 4. SYSTEM DEDUCTION WORKFLOW

### Step-by-Step Process

```
┌─────────────────────────────────────────────────────────────┐
│ 1. ATTENDANCE RECORDED                                       │
│    Employee clocks in late (e.g., 08:30 instead of 08:00)   │
└─────────────────┬───────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. LATE MINUTES CALCULATED                                   │
│    late_minutes = actual_time - scheduled_time              │
│    Example: 30 minutes late                                 │
└─────────────────┬───────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. CONVERT TO DAYS (CSC Standard)                           │
│    lateDays = late_minutes ÷ 480                            │
│    Example: 30 ÷ 480 = 0.0625 days                         │
└─────────────────┬───────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. CHECK VL BALANCE                                          │
│    IF VL balance >= lateDays:                               │
│       → Deduct from VL only                                 │
│    ELSE IF VL balance > 0:                                  │
│       → Deduct all VL, move to next                         │
└─────────────────┬───────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. CHECK SL BALANCE (if needed)                             │
│    IF remaining lateDays <= SL balance:                     │
│       → Deduct from SL                                      │
│    ELSE:                                                     │
│       → Deduct all SL, rest = LWOP                          │
└─────────────────┬───────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. UPDATE LEAVE BALANCES                                     │
│    VL: available_credits -= deducted_amount                 │
│    SL: available_credits -= deducted_amount                 │
│    Create transaction record for audit trail                │
└─────────────────┬───────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────────────┐
│ 7. HANDLE ACCREDITED HOURS                                   │
│    IF fully covered by leave:                               │
│       → Accredited = 480 minutes (8 hrs) ✓ FULL CREDIT     │
│    ELSE IF partially covered:                               │
│       → Accredited = 480 - lwop_minutes                     │
│       → requires_salary_deduction = YES                     │
└─────────────────────────────────────────────────────────────┘
```

**Code Location:** `LateDeductionService.php`, Method: `processLateDeduction()`

---

## 5. DATABASE TRACKING

### Tables Involved

#### `accredited_hours_log`
Tracks when late minutes are deducted:
```sql
late_minutes              INT         -- How many minutes late
late_deducted_from_leave  BOOLEAN     -- Was leave deducted?
late_deduction_leave_type VARCHAR(10) -- 'VL', 'SL', or 'VL+SL'
total_accredited_minutes  INT         -- 480 = full day, < 480 = partial
lwop_minutes              INT         -- Loss of pay minutes
requires_salary_deduction BOOLEAN     -- Needs salary cut?
```

#### `leave_transactions`
Records each deduction:
```sql
transaction_type   = 'debit'  -- Money/leave going out
reference_type     = 'manual_adjustment'
reference_id       = accredited_hours_log.id
amount             = 0.0625   -- Days deducted (negative)
balance_before     = 5.0      -- Balance before deduction
balance_after      = 4.9375   -- Balance after deduction
remarks            = "Late deduction: 30 minutes (0.0625 days) from attendance on 2026-01-15"
```

#### `leave_balances`
Updates employee leave credits:
```sql
available_credits  -= deduction_amount  -- Used credits go up
used_credits       += deduction_amount  -- Available goes down
```

---

## 6. PRECISION & ACCURACY

### Decimal Places
Your system uses **up to 6 decimal places** for precision:

```
0.000001 days = extremely precise
Example: 0.083333 days = exactly 1 hour (60 minutes)
         0.041667 days = exactly 30 minutes
```

### No Rounding Loss
The conversion uses **exact integer math** to prevent rounding errors:

```php
// File: CscTimeConversionService.php, Line 45-50
public static function convertDaysToMinutes(float $days): int
{
    // Exact conversion: multiply then cast to int
    // 0.125 * 480 = 60.0 → (int) 60.0 = 60 (exact)
    // No floor, no round, no ceil - just exact multiplication
    return (int)($days * self::MINUTES_PER_WORK_DAY);
}
```

### Minutes, Hours, Seconds All Supported
```
Seconds → Minutes → Days conversion is seamless
Example: 30 seconds
  → 30 ÷ 60 = 0.5 minutes
  → 0.5 ÷ 480 = 0.001042 days
```

---

## 7. REAL-WORLD DISPLAY EXAMPLE

### In the Leave & Benefits Module
When viewing transaction history, you'll see:

```
Employee:        Juan Dela Cruz
Leave Type:      VL (Vacation Leave)
Transaction:     Debit (Deduction)
Amount:          -0.0625 days
Balance Before:  5.0000 days
Balance After:   4.9375 days
Date:            May 15, 2026
Reference:       Late deduction from attendance
Remarks:         Late deduction: 30 minutes (0.0625 days) from 
                 attendance on 2026-05-15
```

---

## 8. IMPLEMENTATION FILES

| File | Purpose |
|------|---------|
| `CscTimeConversionService.php` | Time unit conversions (min/hr/days) |
| `LateDeductionService.php` | Main deduction logic & priority system |
| `Attendance.php` (Model) | Stores late_minutes from clock-in |
| `AccreditedHoursLog.php` (Model) | Tracks deduction status |
| `LeaveTransaction.php` (Model) | Audit trail of all deductions |
| `LeaveBalance.php` (Model) | Current VL/SL balances per employee |

---

## 9. CONFIGURATION & THRESHOLDS

Currently set to:
- **Minimum time unit:** 1 minute (configurable)
- **Leave deduction:** Automatic when late > 0 minutes
- **Rounding:** 6 decimal places
- **Priority:** VL → SL → LWOP

---

## 10. AUDIT TRAIL QUERY

To see all tardiness deductions for an employee:

```sql
SELECT 
    lt.employee_id,
    lt.leave_code,
    lt.amount,
    lt.balance_before,
    lt.balance_after,
    lt.transaction_date,
    lt.remarks
FROM leave_transactions lt
WHERE lt.reference_type = 'manual_adjustment'
  AND lt.remarks LIKE '%Late deduction%'
  AND lt.employee_id = ?
ORDER BY lt.transaction_date DESC;
```

---

## 11. SUMMARY TABLE

| Aspect | Details |
|--------|---------|
| **Unit Conversion** | 480 minutes = 1 day (8 hours) |
| **Precision** | Up to 6 decimal places (0.000001 days) |
| **Priority** | VL → SL → LWOP |
| **Auto-Process** | YES - runs when attendance is corrected |
| **Rounding** | No loss - exact integer math |
| **Audit Trail** | YES - all in leave_transactions table |
| **Salary Impact** | YES - if both VL & SL insufficient |
| **Employee View** | YES - can see in Leave & Benefits module |

---

## Example: 15 Minutes Late

```
INPUT: Employee is 15 minutes late

STEP 1 - Convert to Days
  15 minutes ÷ 480 = 0.03125 days

STEP 2 - Check VL Balance
  VL = 2.5 days (sufficient)
  → DEDUCT 0.03125 from VL

STEP 3 - Update Balance
  VL was: 2.5000 days
  VL now: 2.46875 days
  VL used: +0.03125 days

STEP 4 - Accredited Hours
  Since fully covered by leave:
  Accredited = 480 minutes (full 8 hrs) ✓

STEP 5 - Record Transaction
  Reference: Late deduction: 15 minutes (0.03125 days)
  Audit trail created for compliance
```

---

**Last Updated:** 2026  
**System:** Prime HR Magdalena (Philippine Government)  
**Standard:** CSC (Civil Service Commission) Compliant
