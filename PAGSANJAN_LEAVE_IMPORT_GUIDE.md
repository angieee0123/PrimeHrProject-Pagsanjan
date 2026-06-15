# Pagsanjan Leave Records Import - REVISED LOGIC

## Overview
This document explains the improved import logic for Pagsanjan leave records Excel files (like @ate beng.xlsx).

---

## 1. Excel File Structure

Your Excel file contains the following layout:

### Header Section (Rows 1-5)
```
Name:           Dimalanta, Ruby
Position:       [Title]
Period in Service:  [Date range]
```

### Data Section (Rows 6+)
```
Column A:  Month/Year or Year header (2012, 2013, etc.)
Column B:  Notes (Leave types & Tardiness)
Column D:  Vacation Leave Earned
Column F:  Vacation Leave Used  
Column H:  Sick Leave Earned
Column J:  Sick Leave Used
Column M:  VL Balance (running total)
Column N:  SL Balance (running total)
```

---

## 2. Parsing Column A (Month/Year)

### Year Detection
When a 4-digit year is encountered (e.g., "2012", "2013"), it's treated as a year header. Data that follows belongs to that year.

### Month Detection
After a year is detected, month names are parsed:
- Full names: "January", "February", "August", etc.
- Abbreviations: "Jan", "Feb", "Aug", etc.

### Date Assignment
**Important:** The system assigns **first day of month** to all records:
- August 2012 → 2012-08-01
- December 2013 → 2013-12-01
- March 2014 → 2014-03-01

This ensures consistent date tracking in the database.

---

## 3. Parsing Column B (Notes) - NEW FEATURE

Column B contains codes that must be parsed to extract:
1. **Leave types used** (VL1, SL1, FL1, etc.)
2. **Tardiness records** (T(hours-minutes-seconds))

### Leave Type Codes

| Code | Meaning | Days | Example |
|------|---------|------|---------|
| VL1 | 1 day Vacation Leave used | 1 | `VL1` |
| VL2 | 2 days Vacation Leave used | 2 | `VL2` |
| SL1 | 1 day Sick Leave used | 1 | `SL1` |
| FL1 | 1 day Forced Leave | 1 | `FL1` |
| FL2 | 2 days Forced Leave | 2 | `FL2` |
| ML1 | 1 day Maternity Leave | 1 | `ML1` |
| PL1 | 1 day Paternity Leave | 1 | `PL1` |
| BL1 | 1 day Birthday Leave | 1 | `BL1` |

### Tardiness Format

**Format:** `T(hours-minutes-seconds)`

| Example | Means | Converts To |
|---------|-------|-------------|
| `T(0-2-10)` | 0 hours, 2 min, 10 sec | 2.167 minutes |
| `T(0-0-35)` | 0 hours, 0 min, 35 sec | 0.583 minutes |
| `T(1-30-0)` | 1 hour, 30 min, 0 sec | 90 minutes |
| `T(2-0-0)` | 2 hours, 0 min, 0 sec | 120 minutes |

**Conversion Formula:**
```
Total Minutes = (hours × 60) + minutes + (seconds ÷ 60)
Total Days = Total Minutes ÷ 480
```

Example: `T(0-2-10)` → 2.167 min ÷ 480 = 0.00451 days

### Combined Entries

Some rows contain both leave types AND tardiness, separated by `/`:

```
VL1/T(0-1-2)      → 1 day VL used + 1 min 2 sec tardiness
FL1/T(0-0-32)     → 1 day FL used + 32 sec tardiness
VL1/T(0-1-2)      → 1 day VL used + 1 min 2 sec tardiness
```

The system parses both components:
- Deduct 1 day from VL balance
- Deduct tardiness from remaining leave balance (or salary if no leave)

---

## 4. Import Logic Flow

### Step 1: Parse Excel
```
Read file → Detect data start row → Parse each row
↓
Extract: Month/Year, Notes, Earned, Used, Balances
```

### Step 2: Parse Notes Column
```
For each Notes entry (Column B):
  ├─ Extract leave type codes (VL1, SL1, FL1, etc.)
  ├─ Extract tardiness T(h-m-s)
  └─ Convert to standardized format
```

### Step 3: Create Leave Balances
```
For each month's records:
  ├─ Get/Create LeaveBalance record (employee_id, leave_code, year)
  ├─ Calculate earned credits (Column D, H)
  ├─ Calculate used credits (Column F, J)
  └─ Record final balance (Column M, N)
```

### Step 4: Record Transactions
```
For each earned/used amount:
  ├─ Create LeaveTransaction (credit or debit)
  ├─ Update running balance
  └─ Store remarks with month label
```

### Step 5: Process Notes Data
```
For each Notes column entry:
  ├─ If leave type code (VL1, SL1, etc.)
  │  ├─ Create debit transaction
  │  └─ Reduce corresponding leave balance
  │
  └─ If tardiness T(h-m-s)
     ├─ Convert to days
     ├─ Try deduct from VL
     ├─ If VL insufficient, try SL
     └─ Create transaction with remaining minutes
```

---

## 5. Example: Complete Import Process

### Input Data (March 2013)
```
Column A: March
Column B: T(0-2-10)
Column D: 1.25 (VL Earned)
Column F: 0.271 (VL Used)
Column H: 1.25 (SL Earned)
Column J: (SL Used)
Column M: 9.729 (VL Balance)
Column N: 10.000 (SL Balance)
```

### System Actions

1. **Set Transaction Date:** 2013-03-01 (first day of month)

2. **Process Earned Credits:**
   - VL: +1.25 → Create credit transaction
   - SL: +1.25 → Create credit transaction

3. **Process Used Credits:**
   - VL: -0.271 → Create debit transaction
   - SL: 0 (skip)

4. **Process Notes Column (T(0-2-10)):**
   - Parse tardiness: 0 hours, 2 minutes, 10 seconds
   - Convert: 2.167 minutes
   - Convert to days: 2.167 ÷ 480 = 0.004515 days
   - Deduct from VL: VL balance reduced by 0.004515 days
   - Create debit transaction with remarks: "Tardiness deduction: 2.167 minutes (0.004515 days) from VL - March"

5. **Update Balances:**
   - VL Balance: 9.729 days
   - SL Balance: 10.000 days

---

## 6. Database Records Created

For March 2013 entry shown above, the system creates:

### LeaveTransaction Records

| Type | Amount | Balance Before | Balance After | Remarks |
|------|--------|-----------------|---|---------|
| credit | 1.25 | 8.479 | 9.729 | [IMPORT] Earned 1.25 credits (March) |
| debit | 0.271 | 10.0 | 9.729 | [IMPORT] Used 0.271 credits (March) |
| debit | 0.004515 | 9.729 | 9.724485 | [IMPORT] Tardiness deduction: 2.167 minutes from VL - March |
| credit | 1.25 | 8.75 | 10.0 | [IMPORT] Earned 1.25 credits (March) |

### LeaveBalance Updates
```
VL (Year 2013):
  - total_credits: 15.0 (sum of all earned)
  - used_credits: 6.271 (sum of all used)
  - available_credits: 9.729 (final balance)

SL (Year 2013):
  - total_credits: 15.0 (sum of all earned)
  - used_credits: 5.0 (sum of all used)
  - available_credits: 10.0 (final balance)
```

---

## 7. Key Features

### ✓ Automatic Date Assignment
- Always uses first day of month (2013-03-01)
- Consistent across all records

### ✓ Precise Tardiness Calculation
- Handles hours, minutes, and seconds
- Example: T(0-2-10) = 0 hrs + 2 min + 10 sec
- Converts accurately to decimal days (6 decimal precision)

### ✓ Combined Entry Support
- Handles entries like "VL1/T(0-1-2)"
- Processes both leave deduction AND tardiness in same month

### ✓ Leave Type Mapping
- Maps Pagsanjan codes to system codes
- Supports: VL, SL, FL, ML, PL, BL, AL

### ✓ Intelligent Tardiness Deduction
1. First tries to deduct from VL
2. If insufficient, tries SL
3. If both insufficient, records as LWOP (Loss of Pay)

### ✓ Audit Trail
- All transactions logged in leave_transactions table
- Remarks show source (IMPORT), type, and month
- Full traceability for compliance

---

## 8. Error Handling

### Validation Errors
- Employee not found → "Employee record not found"
- Leave types missing → "At least VL and SL leave types must exist"
- Invalid month → Row skipped, no error
- Empty rows → Stops after 3 consecutive empty rows

### Processing Warnings
- Negative balance from deductions → Logged but allowed (with warning)
- Unrecognized leave codes → Skipped, import continues
- Parse errors in tardiness → Logged, continues

---

## 9. Using the Import Feature

### Admin Steps

1. Go to **Leave & Benefits → Import Records** tab
2. Select **Employee** (e.g., "Ruby Dimalanta")
3. Click **"Choose File"** and select Excel file (e.g., @ate beng.xlsx)
4. Review the expected format guide
5. Click **"Import Records"**
6. System shows:
   - ✓ Imported X records for year(s): 2012, 2013
   - Any warnings/errors

### Verify Results

Check **Transaction History** tab:
- Filter by employee "Ruby Dimalanta"
- See all imported transactions
- Verify balances match Excel file

---

## 10. Example File Structure (Your @ate beng.xlsx)

```
Row 1:  Name:                  Dimalanta, Ruby
Row 2:  Position:              
Row 3:  Period in Service:     
Row 4:  [blank]
Row 5:  [blank]

Row 6:  [blank headers for columns]

Row 7:  2012
Row 8:  August      1.25  1.25  2.5   1.250  1.250  2.50
Row 9:  September   1.25  1.25  5     2.500  2.500  5.00
Row 10: October     1.25  1.25  7.5   3.750  3.750  7.50
...

Row 17: 2013
Row 18: [blank]
Row 19: January     1.25  1.25  15    7.500  7.500  15.00
Row 20: February    1.25  1.25  17.5  8.750  8.750  17.50
Row 21: March       T(0-2-10)  1.25  0.271  1.25        19.729  9.729  10.000
Row 22: April       T(0-0-35)  1.25  0.073  1.25        22.156  10.906 11.250
Row 23: May         VL1/T(0-1-2)  1.25  1.129  1.25    23.527  11.027 12.500
```

---

## 11. Transaction Recording Details

Each import creates transactions with:

### Transaction Fields
- **employee_id:** Selected employee
- **leave_code:** VL, SL, FL, etc.
- **year:** Extracted from Excel
- **transaction_type:** 'credit' (earned), 'debit' (used), 'adjustment'
- **amount:** Days earned/used/deducted
- **balance_before:** Balance before this transaction
- **balance_after:** Balance after this transaction
- **reference_type:** 'leave_import'
- **transaction_date:** First day of month (e.g., 2013-03-01)
- **processed_by:** Admin user ID
- **remarks:** Descriptive message showing source and details

### Example Remark
```
"[IMPORT] Tardiness deduction: 2.167 minutes (0.004515 days) from VL - March"
"[IMPORT] Used 1 VL (VL1) - March"
"[IMPORT] Earned 1.25 credits (March)"
```

---

## 12. Troubleshooting

### Issue: "No leave records found"
**Solution:** Ensure data starts at Row 6, Column A has month names

### Issue: Balances don't match Excel
**Solution:** Check if all rows were parsed - look at transaction history

### Issue: Tardiness not being deducted
**Solution:** Ensure leave balances exist; check if T() format is correct

### Issue: Leave type not imported
**Solution:** Verify leave type code matches (VL1 not V1, SL1 not S1)

---

## 13. Summary of Changes

| Aspect | Before | After |
|--------|--------|-------|
| Notes parsing | Not parsed | ✓ Full parsing of VL1, SL1, FL1, T(h-m-s) |
| Tardiness | Ignored | ✓ Converted to days and deducted |
| Combined entries | Not supported | ✓ Handles "VL1/T(0-1-2)" |
| Date assignment | Variable | ✓ Always first day of month |
| Leave types | VL, SL only | ✓ VL, SL, FL, ML, PL, BL, AL |
| Precision | 2 decimals | ✓ 6 decimals for accuracy |
| Audit trail | Basic | ✓ Detailed remarks & tracking |

---

**Version:** 2.0 (Revised for Pagsanjan format)  
**Last Updated:** 2026  
**Status:** Production Ready
