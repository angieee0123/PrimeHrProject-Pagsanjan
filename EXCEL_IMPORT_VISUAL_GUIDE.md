# Excel Import - Quick Visual Guide

## 🎯 The Big Picture

```
┌─────────────────────────────────────────────────────────────────┐
│                    EXCEL FILE INPUT                              │
│  ate_beng.xlsx (Employee's historical leave records for 2024)   │
└──────────────────────────────┬──────────────────────────────────┘
                               │
                               ▼
         ┌─────────────────────────────────────────┐
         │      IMPORT SYSTEM (Parses Excel)       │
         │                                         │
         │  - Reads rows 6 onwards                 │
         │  - Extracts month, earnings, deductions│
         │  - Validates all numeric values        │
         │  - Calculates running balances         │
         └──────────────────┬──────────────────────┘
                            │
                ┌───────────┴───────────┐
                ▼                       ▼
        ┌─────────────────┐    ┌──────────────────┐
        │ LEAVE BALANCES  │    │ TRANSACTIONS     │
        │ (Final Summary) │    │ (Audit Trail)    │
        │                 │    │                  │
        │ Total Earned    │    │ Each Action:     │
        │ Total Deducted  │    │ - Date           │
        │ Final Balance   │    │ - Amount         │
        │ Per Leave Type  │    │ - Balance Before │
        │ Per Year        │    │ - Balance After  │
        │                 │    │ - Remarks        │
        └─────────────────┘    └──────────────────┘
                │                       │
                └───────────┬───────────┘
                            ▼
            ✅ DATABASE UPDATED SUCCESSFULLY
               - Leave history now in system
               - Available for leave requests
               - Visible in transaction reports
```

---

## 📊 Excel Format Visual

```
Your Excel File Layout:
═════════════════════════════════════════════════════════════════

ROWS 1-5: HEADER (Employee info)
│ Name: Ate Beng
│ Position: Administrative Officer
│ Department: Human Resources
│ Year: 2024
│ (any other header info)

ROW 6: COLUMN HEADERS
│ A: Month    │ B: Notes    │ ... │ D: VL Earn │ F: VL Use │ ... │ M: VL Bal │ N: SL Bal

ROW 7+: ACTUAL DATA (Monthly records)
├─ January:   │ VL1         │ ... │ 1.0        │ 0         │ ... │ 1.0       │ 0.833
├─ February:  │ VL1         │ ... │ 1.0        │ 0         │ ... │ 2.0       │ 1.666
├─ March:     │ FL1         │ ... │ 0          │ 1.0       │ ... │ 1.0       │ 2.499
├─ April:     │ SL(1-2-10)  │ ... │ 1.0        │ 0         │ ... │ 2.0       │ 2.332
└─ ... more months ...
```

---

## 🔄 Data Flow - Step by Step

### INPUT: Excel Row
```
┌──────────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│ A: January   │ B: VL1   │ D: 1.0   │ F: 0     │ H: 0.833 │ J: 0     │
│              │          │ (VL earn)│ (VL use) │ (SL earn)│ (SL use) │
└──────────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
                           │ SYSTEM PROCESSES │
                                   ▼
              ┌─────────────────────────────────┐
              │  CREATES 4 TRANSACTIONS:        │
              ├─────────────────────────────────┤
              │ 1. VL +1.0 (Earned)             │
              │ 2. VL -0.0 (Used)               │
              │ 3. SL +0.833 (Earned)           │
              │ 4. SL -0.0 (Used)               │
              └─────────────────────────────────┘
```

### OUTPUT: Database Records
```
leave_transactions:
┌────┬──────┬────┬─────────┬────────┬───────────────┬──────────────┐
│ ID │ Type │ Amt│Before   │ After  │ Remarks       │ Date         │
├────┼──────┼────┼─────────┼────────┼───────────────┼──────────────┤
│ 1  │ VL   │+1.0│ 0       │ 1.0    │ VL Earned Jan │ 2024-01-31   │
│ 2  │ SL   │+0.8│ 0       │ 0.833  │ SL Earned Jan │ 2024-01-31   │
│ 3  │ VL   │+1.0│ 1.0     │ 2.0    │ VL Earned Feb │ 2024-02-29   │
│ 4  │ SL   │+0.8│ 0.833   │ 1.666  │ SL Earned Feb │ 2024-02-29   │
└────┴──────┴────┴─────────┴────────┴───────────────┴──────────────┘

leave_balances:
┌──────────────┬──────┬───────┬────────┬──────────┐
│ Leave Type   │ Earn │ Used  │ Avail. │ Year     │
├──────────────┼──────┼───────┼────────┼──────────┤
│ VL (Vacation)│ 12.5 │ 3.0   │ 9.5    │ 2024     │
│ SL (Sick)    │ 10.0 │ 1.5   │ 8.5    │ 2024     │
└──────────────┴──────┴───────┴────────┴──────────┘
```

---

## 👥 System Usage Flow

```
┌──────────────────────────────────┐
│  1. ADMIN NAVIGATES               │
│     Leave & Benefits → Import Tab │
└──────────┬───────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  2. CLICK "Import Leave Records"  │
│     Opens import modal            │
└──────────┬───────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  3. SELECT EMPLOYEE               │
│     Choose from dropdown          │
│     (Shows ID, Name, Department) │
└──────────┬───────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  4. UPLOAD EXCEL FILE             │
│     Select .xlsx or .xls file     │
│     (Max 5MB)                    │
└──────────┬───────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  5. CLICK "Import Records"        │
│     System processes file         │
└──────────┬───────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  6. VALIDATION & PROCESSING       │
│     ✓ File format check           │
│     ✓ Data validation             │
│     ✓ Create transactions         │
│     ✓ Update balances             │
└──────────┬───────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  7. SUCCESS MESSAGE               │
│     "Imported X records"          │
│     Auto-redirects to History     │
└──────────┬───────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  8. VIEW RESULTS                  │
│     See Transaction History       │
│     All imported records visible  │
└──────────────────────────────────┘
```

---

## 💾 What Gets Saved (Database Perspective)

### Before Import
```
Employee: Ate Beng (ID: 15)
leave_balances: EMPTY
leave_transactions: EMPTY
System view: No leave history
```

### After Import
```
Employee: Ate Beng (ID: 15)

leave_balances (Summary):
├─ VL: 12.5 earned, 3.0 used, 9.5 available
└─ SL: 10.0 earned, 1.5 used, 8.5 available

leave_transactions (Audit Trail):
├─ Jan VL +1.0    (balance: 0 → 1.0)
├─ Jan SL +0.833  (balance: 0 → 0.833)
├─ Feb VL +1.0    (balance: 1.0 → 2.0)
├─ Feb SL +0.833  (balance: 0.833 → 1.666)
├─ Mar VL -1.0    (balance: 2.0 → 1.0)
├─ Mar SL +0.833  (balance: 1.666 → 2.499)
└─ ... (more transactions)

System view: COMPLETE leave history available
```

---

## 📈 Column Mapping Reference

```
EXCEL COLUMN    →    FIELD NAME         →    DATA TYPE
─────────────────────────────────────────────────────
     A          →    Month/Period       →    Text
     B          →    Notes              →    Text
     D          →    VL Earned          →    Decimal(10,6)
     F          →    VL Used            →    Decimal(10,6)
     H          →    SL Earned          →    Decimal(10,6)
     J          →    SL Used            →    Decimal(10,6)
     M          →    VL Balance         →    Decimal(10,6)
     N          →    SL Balance         →    Decimal(10,6)

All other columns: IGNORED (not processed)
```

---

## ✅ Validation Rules

```
BEFORE PROCESSING:
├─ File is .xlsx or .xls? ✓
├─ File size < 5MB? ✓
├─ Employee exists? ✓
├─ Data starts at row 6? ✓
├─ All numeric columns have numbers? ✓
└─ Leave types (VL, SL) are valid? ✓

IF ANY CHECK FAILS:
└─ Import cancelled, rollback, show error message
```

---

## 🔍 Transaction Reference Types

```
When you import, all transactions get marked with:

reference_type = 'leave_import'

This means:
✓ You can filter imported records
✓ Audit trail shows origin
✓ Distinguishes from manual entries
✓ Easy to identify batch imports
```

---

## 📊 Math Verification

```
The system verifies:

balance_after = balance_before + amount

Example transaction chain:

Transaction 1: Jan VL +1.0
  balance_before: 0
  amount: +1.0
  balance_after: 0 + 1.0 = 1.0 ✓

Transaction 2: Feb VL +1.0
  balance_before: 1.0 (from previous transaction)
  amount: +1.0
  balance_after: 1.0 + 1.0 = 2.0 ✓

Transaction 3: Mar VL -1.0
  balance_before: 2.0 (from previous transaction)
  amount: -1.0
  balance_after: 2.0 - 1.0 = 1.0 ✓

Final Balance: 1.0 ✓ (matches Excel column M)
```

---

## 🎓 Real Example: ate_beng.xlsx

```
EMPLOYEE: Ate Beng (ID: 15)
YEAR: 2024

EXCEL DATA:
Month      VL Earn  VL Used  SL Earn  SL Used  VL Bal  SL Bal
January    1.0      0        0.833    0        1.0     0.833
February   1.0      0        0.833    0        2.0     1.666
March      0        1.0      0.833    0        1.0     2.499
April      1.0      0        0        0.166    2.0     2.333
May        1.0      0.5      0.667    0        2.5     3.0

SYSTEM CREATES:
✓ 10 Transactions (2 per month x 5 months)
✓ 2 Balance Records (one for VL, one for SL)

FINAL RESULT:
leave_balances for Ate Beng, 2024:
  VL: Total 4.0, Used 1.5, Available 2.5
  SL: Total 3.166, Used 0.166, Available 3.0

leave_transactions shows all steps:
  Jan: +1.0 VL, +0.833 SL
  Feb: +1.0 VL, +0.833 SL  
  Mar: -1.0 VL, +0.833 SL
  Apr: +1.0 VL, -0.166 SL
  May: +1.0 VL, +0.667 SL
       -0.5 VL

Employee's complete leave history now in system! ✅
```

---

## 🚀 Key Takeaway

```
WHEN YOU IMPORT AN EXCEL FILE:

WHAT GETS RECORDED:
├─ Every month's earnings  ─────────► leave_transactions
├─ Every month's deductions ────────► leave_transactions  
├─ Running balances ────────────────► verified accuracy
└─ Final totals ────────────────────► leave_balances

RESULT:
✅ Complete leave history in database
✅ Full audit trail of all changes
✅ Ready for leave requests and payroll
✅ Employee data migrated successfully
```
