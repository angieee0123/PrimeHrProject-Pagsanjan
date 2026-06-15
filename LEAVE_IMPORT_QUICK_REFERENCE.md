# Quick Reference: Pagsanjan Leave Import

## Column Mapping (Your @ate beng.xlsx)

```
A = Month/Year       (2012, August, September, etc.)
B = Notes            (VL1, SL1, FL1, T(0-2-10), combinations)
D = VL Earned        (1.25, 0, etc.)
F = VL Used          (0.271, etc.)
H = SL Earned        (1.25, 0, etc.)
J = SL Used          (blank or 0)
M = VL Balance       (9.729, 10.906, etc.)
N = SL Balance       (10.000, 11.250, etc.)
```

---

## Notes Column (B) - Parsing Rules

### Rule 1: Leave Type Codes
Extract codes in format `[CODE][NUMBER]`

```
VL1  → 1 day Vacation Leave
SL1  → 1 day Sick Leave  
FL1  → 1 day Forced Leave
FL2  → 2 days Forced Leave
ML1  → 1 day Maternity Leave
PL1  → 1 day Paternity Leave
BL1  → 1 day Birthday Leave
AL1  → 1 day Annual Leave
```

**Action:** Create DEBIT transaction, reduce leave balance

---

### Rule 2: Tardiness Format
Extract codes in format `T(HOURS-MINUTES-SECONDS)`

```
T(0-2-10)   → 2 minutes 10 seconds
T(0-0-35)   → 35 seconds
T(1-30-0)   → 1 hour 30 minutes
T(2-45-30)  → 2 hours 45 minutes 30 seconds
```

**Calculation:**
```
Minutes = (hours × 60) + minutes + (seconds ÷ 60)
Days = Minutes ÷ 480
```

**Action:** Convert to days, deduct from VL first, then SL if needed

---

### Rule 3: Combined Entries
Entries separated by `/` are processed as both occur in same month

```
VL1/T(0-1-2)        → 1 day VL used + 1 min 2 sec tardiness
FL1/T(0-0-32)       → 1 day FL used + 32 sec tardiness  
VL1/T(0-1-2)        → 1 day VL used + 1 min 2 sec tardiness
VL1/T(0-0-3)        → 1 day VL used + 3 sec tardiness
FL2/T(0-0-59)       → 2 days FL used + 59 sec tardiness
```

**Action:** Process both components in same transaction date

---

## Date Assignment

**Important:** All records use **first day of month**

```
August 2012    → 2012-08-01
January 2013   → 2013-01-01
March 2013     → 2013-03-01
December 2013  → 2013-12-01
```

This ensures consistent database tracking despite original Excel having only month names.

---

## Examples from Your @ate beng.xlsx

### Example 1: Simple Earned/Used (August 2012)
```
Month:        August
Notes:        [blank]
VL Earned:    1.25
VL Used:      [blank or 0]
SL Earned:    1.25
SL Used:      [blank or 0]

Result:
  Date: 2012-08-01
  +1.25 days VL (earned)
  +1.25 days SL (earned)
```

### Example 2: Tardiness Only (March 2013)
```
Month:        March
Notes:        T(0-2-10)
VL Earned:    1.25
VL Used:      0.271
SL Earned:    1.25

Result:
  Date: 2013-03-01
  +1.25 days VL earned
  -0.271 days VL used
  -0.004515 days VL (tardiness: 2.167 min ÷ 480)
  +1.25 days SL earned
```

### Example 3: Leave Used with Tardiness (May 2013)
```
Month:        May
Notes:        VL1/T(0-1-2)
VL Earned:    1.25
VL Used:      1.129
SL Earned:    1.25

Result:
  Date: 2013-05-01
  -1 day VL (from VL1 code)
  -0.002083 days VL (tardiness: 1 min ÷ 480)
  +1.25 days SL earned
```

### Example 4: Forced Leave (July 2013)
```
Month:        July
Notes:        FL1/T(0-0-32)
VL Earned:    1.25
VL Used:      [blank]
SL Earned:    1.25

Result:
  Date: 2013-07-01
  -1 day FL (forced leave)
  -0.000667 days (tardiness: 32 sec ÷ 480)
  +1.25 days VL earned
  +1.25 days SL earned
```

---

## System Processing Flow

```
┌─────────────────────────────────┐
│ 1. User Uploads @ate beng.xlsx  │
└────────────┬────────────────────┘
             ↓
┌─────────────────────────────────┐
│ 2. Parse Excel Structure        │
│    • Detect year headers        │
│    • Identify month rows        │
│    • Set date = 1st of month   │
└────────────┬────────────────────┘
             ↓
┌─────────────────────────────────┐
│ 3. Parse Column B (Notes)       │
│    • Extract leave codes        │
│    • Extract tardiness T(h-m-s) │
│    • Convert to standard format │
└────────────┬────────────────────┘
             ↓
┌─────────────────────────────────┐
│ 4. Create Transactions          │
│    • Earned credits (credit)    │
│    • Used credits (debit)       │
│    • Notes deductions (debit)   │
└────────────┬────────────────────┘
             ↓
┌─────────────────────────────────┐
│ 5. Update Leave Balances        │
│    • Calculate running totals   │
│    • Set final balances         │
└────────────┬────────────────────┘
             ↓
┌─────────────────────────────────┐
│ ✓ Import Complete              │
│   X records imported           │
└─────────────────────────────────┘
```

---

## Tardiness Conversion Table

| Time | Minutes | Days (÷480) |
|------|---------|------------|
| 1 sec | 0.0167 | 0.0000348 |
| 10 sec | 0.167 | 0.000348 |
| 30 sec | 0.5 | 0.001042 |
| 1 min | 1.0 | 0.002083 |
| 2 min | 2.0 | 0.004167 |
| 5 min | 5.0 | 0.010417 |
| 10 min | 10.0 | 0.020833 |
| 15 min | 15.0 | 0.03125 |
| 30 min | 30.0 | 0.0625 |
| 1 hour | 60.0 | 0.125 |
| 2 hours | 120.0 | 0.25 |
| 4 hours | 240.0 | 0.5 |
| 8 hours | 480.0 | 1.0 |

---

## What Gets Recorded in Database

### LeaveTransaction Table
```
employee_id        = Selected employee ID
leave_code         = VL, SL, FL, ML, etc.
year               = 2012, 2013, etc.
transaction_type   = 'credit', 'debit', 'adjustment'
amount             = Days earned/used/deducted (decimal)
balance_before     = Balance before transaction
balance_after      = Balance after transaction
reference_type     = 'leave_import'
transaction_date   = First day of month (e.g., 2013-03-01)
remarks            = Details about import source & type
```

### LeaveBalance Table
```
employee_id        = Selected employee ID
leave_code         = VL, SL, FL, etc.
year               = 2012, 2013, etc.
total_credits      = Sum of all earned for year
used_credits       = Sum of all used for year
available_credits  = Final balance from Excel
```

---

## Error Messages & Solutions

| Error | Cause | Fix |
|-------|-------|-----|
| "No leave records found" | Data not in expected location | Verify data starts row 6, months in column A |
| "At least VL and SL must exist" | Leave types not configured | Create VL and SL in Leave Types first |
| "Failed to parse Excel file" | Corrupted or wrong format | Save as .xlsx, ensure month names in column A |
| Balances don't match | Some rows not imported | Check transaction history for all months |
| Tardiness not deducted | Invalid T() format | Use T(h-m-s) exactly, e.g., T(0-2-10) |

---

## Column Letter Reference

```
A = Month        E = [unused]     I = [unused]     M = VL Balance
B = Notes        F = VL Used      J = SL Used      N = SL Balance  
C = [unused]     G = [unused]     K = [unused]     O = [unused]
D = VL Earned    H = SL Earned    L = [unused]     ...
```

---

## Key Points

✓ **Date:** Always 1st of month (2013-03-01, not 2013-03-15)
✓ **Precision:** 6 decimals (0.004515 days, not 0.004)
✓ **Parsing:** Split by `/` for combined entries
✓ **Tardiness:** T(hours-minutes-seconds) format required
✓ **Leave Codes:** Must be CODE + NUMBER (VL1, not VL or V1)
✓ **Audit:** All transactions logged with "[IMPORT]" prefix
✓ **Deduction Order:** VL first, then SL, then LWOP

---

**Quick Test:**
- Upload @ate beng.xlsx
- System should find ~60+ records (2012-2013)
- Check Transaction History for Ruby Dimalanta
- Verify balances match: VL=9.729, SL=10.0 for March 2013
