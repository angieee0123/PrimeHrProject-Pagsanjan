# Enhanced Leave Transaction History Display

## Overview

The "My Leave Transaction History" section now clearly shows **WHERE** each leave deduction came from with visual indicators and detailed information.

---

## What Changed

### Before:
- ❌ Generic "Reference" column showing only "Manual" or "Leave App"
- ❌ Remarks hidden or not prominent
- ❌ Hard to identify late vs undertime deductions

### After:
- ✅ **"Source/Reason"** column with icons and labels
- ✅ Clear identification of deduction types
- ✅ Detailed remarks shown below each entry
- ✅ Color-coded for easy recognition

---

## Transaction Types with Visual Indicators

### 1. 🕐 Late Deduction (Orange)
**Icon**: Clock with forward hand  
**Color**: `#a16207` (Orange)  
**Label**: "Late Deduction"  
**Example Remark**: "Late deduction: 60 minutes (0.125000 days) from attendance on 2026-05-22"

### 2. ⏰ Undertime Deduction (Red)
**Icon**: Clock with backward hand  
**Color**: `#8e1e18` (Red)  
**Label**: "Undertime Deduction"  
**Example Remark**: "Undertime deduction: 120 minutes (0.250000 days) from attendance on 2026-05-22"

### 3. 📅 Leave Application (Purple)
**Icon**: Calendar  
**Color**: `#0b044d` (Purple)  
**Label**: "Leave Application"  
**Example Remark**: "Approved leave application LA-2026-0001"

### 4. ✏️ Manual Adjustment (Violet)
**Icon**: Edit/Pen  
**Color**: `#6b3fa0` (Violet)  
**Label**: "Manual Adjustment"  
**Example Remark**: "[ADDITION] PARA SA BAKASYON MO HAHAHA"

### 5. ✓ Monthly Accrual (Green)
**Icon**: Checkmark in circle  
**Color**: `#15803d` (Green)  
**Label**: "Monthly Accrual"  
**Example Remark**: "Monthly accrual for May 2026"

---

## Table Display

### Column Structure:
```
| Leave Type | Transaction Type | Amount | Balance Before | Balance After | Date | Source/Reason | Actions |
```

### Example Row (Late Deduction):
```
┌──────────────┬──────────────────┬──────────────┬────────────────┬───────────────┬─────────────┬─────────────────────────────────────┬─────────┐
│ VL           │ Debit            │ -0.125000    │ 1.250000       │ 1.125000      │ May 22, 2026│ 🕐 Late Deduction                   │ [View]  │
│              │                  │ days         │                │               │             │ Late deduction: 60 minutes...       │         │
└──────────────┴──────────────────┴──────────────┴────────────────┴───────────────┴─────────────┴─────────────────────────────────────┴─────────┘
```

### Example Row (Undertime Deduction):
```
┌──────────────┬──────────────────┬──────────────┬────────────────┬───────────────┬─────────────┬─────────────────────────────────────┬─────────┐
│ VL           │ Debit            │ -0.250000    │ 1.125000       │ 0.875000      │ May 22, 2026│ ⏰ Undertime Deduction              │ [View]  │
│              │                  │ days         │                │               │             │ Undertime deduction: 120 minutes... │         │
└──────────────┴──────────────────┴──────────────┴────────────────┴───────────────┴─────────────┴─────────────────────────────────────┴─────────┘
```

---

## Detail Modal Enhancement

When clicking "View" on any transaction, the modal now shows:

### Enhanced Layout:
```
┌─────────────────────────────────────────────────┐
│ TRANSACTION DETAILS                             │
│ Leave Credit Transaction                        │
├─────────────────────────────────────────────────┤
│ TRANSACTION INFORMATION                         │
│ Leave Type:          VL                         │
│ Transaction Type:    [Debit]                    │
│ Amount:              -0.125000 days             │
│ Balance Before:      1.250000 days              │
│ Balance After:       1.125000 days              │
│ Transaction Date:    May 22, 2026               │
├─────────────────────────────────────────────────┤
│ SOURCE/REASON                                   │
│ 🕐 Late Deduction                               │
│ ┌─────────────────────────────────────────────┐ │
│ │ Late deduction: 60 minutes (0.125000 days)  │ │
│ │ from attendance on 2026-05-22               │ │
│ └─────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────┤
│                                    [Close]      │
└─────────────────────────────────────────────────┘
```

---

## Benefits for Employees

### 1. **Transparency**
Employees can now clearly see:
- Why their leave credits were deducted
- Exact date of the deduction
- How many minutes/hours were involved
- Which attendance record triggered it

### 2. **Easy Identification**
- Color-coded icons make it easy to scan
- Late deductions are orange
- Undertime deductions are red
- Leave applications are purple
- Manual adjustments are violet
- Accruals are green

### 3. **Detailed Information**
- Full remarks shown in table
- Expandable detail modal for more info
- Exact conversion shown (minutes → days)
- Date reference for verification

---

## Example Scenarios

### Scenario 1: Employee Checks Why VL Decreased

**Employee sees**:
```
VL | Debit | -0.125000 days | May 22, 2026
🕐 Late Deduction
Late deduction: 60 minutes (0.125000 days) from attendance on 2026-05-22
```

**Employee understands**:
- "I was late on May 22"
- "60 minutes late = 0.125 days deducted"
- "This came from my attendance, not a leave application"

---

### Scenario 2: Employee Checks Multiple Deductions

**Employee sees**:
```
1. VL | Debit | -0.125000 days | May 22, 2026
   🕐 Late Deduction (60 minutes)

2. VL | Debit | -0.250000 days | May 22, 2026
   ⏰ Undertime Deduction (120 minutes)

3. VL | Debit | -3.000000 days | May 15, 2026
   📅 Leave Application (LA-2026-0001)
```

**Employee understands**:
- "On May 22, I was late (60 min) AND left early (120 min)"
- "On May 15, I filed a leave application for 3 days"
- "Total VL used: 3.375 days"

---

## Technical Implementation

### Backend (Already Working):
The `remarks` field in `leave_transactions` table already contains:
- "Late deduction: X minutes (Y days) from attendance on DATE"
- "Undertime deduction: X minutes (Y days) from attendance on DATE"
- "Approved leave application LA-XXXX-XXXX"
- Manual adjustment remarks from admin

### Frontend (Enhanced):
1. **Table Display**: Parses remarks to show appropriate icon and label
2. **Color Coding**: Different colors for different transaction types
3. **Detail Modal**: Enhanced layout with icon and formatted remarks
4. **Responsive**: Works on mobile and desktop

---

## User Guide

### For Employees:

**To view your transaction history**:
1. Go to "Leave and Benefits" page
2. Click "Transaction History" tab
3. See all your leave credit changes

**To understand a deduction**:
1. Look at the "Source/Reason" column
2. Icon and color tell you the type:
   - 🕐 Orange = Late
   - ⏰ Red = Undertime
   - 📅 Purple = Leave Application
   - ✏️ Violet = Manual Adjustment
   - ✓ Green = Monthly Accrual
3. Read the details below the label
4. Click "View" for full information

**To filter transactions**:
- Use "All Leave Types" dropdown to filter by VL, SL, etc.
- Use "All Types" dropdown to filter by Credit/Debit
- Use date picker to filter by specific date

---

## Verification Queries

### Check Late Deductions:
```sql
SELECT 
    employee_id,
    leave_code,
    amount,
    transaction_date,
    remarks
FROM leave_transactions
WHERE remarks LIKE '%Late deduction%'
ORDER BY transaction_date DESC;
```

### Check Undertime Deductions:
```sql
SELECT 
    employee_id,
    leave_code,
    amount,
    transaction_date,
    remarks
FROM leave_transactions
WHERE remarks LIKE '%Undertime deduction%'
ORDER BY transaction_date DESC;
```

### Check All Deductions for Employee:
```sql
SELECT 
    leave_code,
    transaction_type,
    amount,
    balance_before,
    balance_after,
    transaction_date,
    reference_type,
    remarks
FROM leave_transactions
WHERE employee_id = 8
ORDER BY transaction_date DESC;
```

---

## Files Modified

### 1. Transaction History Tab
**File**: `resources/views/permanent/leaveandbenefits/tabs/transaction-history/transactionHistoryTab.blade.php`

**Changes**:
- Changed "Reference" column to "Source/Reason"
- Added icon display logic
- Added color coding
- Enhanced detail modal
- Updated JavaScript for modal display

---

## Success Indicators

✅ Employees can see clear labels for each transaction  
✅ Icons and colors make it easy to identify types  
✅ Remarks are prominently displayed  
✅ Detail modal shows enhanced information  
✅ Late and undertime deductions are clearly distinguished  
✅ Leave applications are easily identified  

---

## Future Enhancements (Optional)

1. **Export to PDF**: Allow employees to download transaction history
2. **Email Notifications**: Send email when leave is deducted
3. **Monthly Summary**: Show summary of deductions per month
4. **Comparison**: Compare current month vs previous month
5. **Charts**: Visual representation of leave usage

---

**Implementation Date**: May 17, 2026  
**Status**: ✅ COMPLETE  
**User Impact**: HIGH - Employees now have full transparency  
**Training Required**: Minimal - Self-explanatory UI  

---

## Quick Reference

| Icon | Color | Label | Meaning |
|------|-------|-------|---------|
| 🕐 | Orange | Late Deduction | Deducted due to late arrival |
| ⏰ | Red | Undertime Deduction | Deducted due to early departure |
| 📅 | Purple | Leave Application | Deducted for approved leave |
| ✏️ | Violet | Manual Adjustment | Admin added/removed credits |
| ✓ | Green | Monthly Accrual | Earned credits for the month |

---

**Employees now have complete visibility into their leave credit transactions!** 🎉
