# Employee Loans Table - Database Integration

## Overview
The Employee Loans table displays all loans (GSIS, Pag-IBIG, and other) with automatic balance tracking, progress visualization, and per-cutoff calculations.

---

## Features Implemented

### ✅ **1. Comprehensive Loan Display**
**Columns:**
- Employee (with avatar and ID)
- Department
- Loan Type (with code)
- Provider (badge: GSIS/Pag-IBIG/Other)
- Total Amount
- Remaining Balance (with % paid)
- Monthly Installment
- Per Cutoff (auto-calculated)
- Progress (visual progress bar)
- Start Date
- End Date
- Status (badge)
- Actions (View/Edit/Delete)

### ✅ **2. Smart Calculations**
```php
// Per Cutoff Amount
$perCutoff = $monthlyInstallment / 2

// Progress Percentage
$progress = (($totalAmount - $remainingBalance) / $totalAmount) * 100

// Months Remaining
$monthsRemaining = ceil($remainingBalance / $monthlyInstallment)

// Amount Paid
$amountPaid = $totalAmount - $remainingBalance
```

### ✅ **3. Visual Progress Bar**
- Shows loan repayment progress (0-100%)
- Color-coded:
  - **Blue (#0b044d):** In progress
  - **Green (#15803d):** Completed (100%)
- Displays percentage below bar

### ✅ **4. Provider Detection**
Automatically detects provider from loan type code:
```php
if (str_contains($code, 'GSIS')) → GSIS
if (str_contains($code, 'PAGIBIG')) → Pag-IBIG
else → Other
```

### ✅ **5. Real-Time Filtering**
- **Search:** Filter by employee name
- **Loan Type:** Filter by specific loan type
- **Status:** Filter by ACTIVE/COMPLETED/SUSPENDED
- Updates count dynamically

### ✅ **6. Loan Details View**
Displays formatted loan information:
```
╔════════════════════════════════════════════╗
║          LOAN DETAILS                      ║
╠════════════════════════════════════════════╣
║ Employee: Juan Dela Cruz                   ║
║ Loan Type: GSIS Salary Loan                ║
╠════════════════════════════════════════════╣
║ Total Amount: ₱50,000.00                   ║
║ Amount Paid: ₱5,000.00                     ║
║ Remaining Balance: ₱45,000.00              ║
║ Progress: 10.0%                            ║
╠════════════════════════════════════════════╣
║ Monthly Installment: ₱2,500.00             ║
║ Per Cutoff: ₱1,250.00                      ║
║ Months Remaining: 18 months                ║
╠════════════════════════════════════════════╣
║ Start Date: 1/1/2024                       ║
║ End Date: 6/30/2025                        ║
║ Status: ACTIVE                             ║
╠════════════════════════════════════════════╣
║ Remarks: Approved loan                     ║
╚════════════════════════════════════════════╝
```

### ✅ **7. Export to CSV**
Exports detailed loan information including:
- Employee details
- Loan amounts and balances
- Progress percentage
- Monthly and per-cutoff amounts
- Months remaining
- All dates and status

---

## Key Features

### **Per-Cutoff Display**
Shows how much is deducted per cutoff period:
```
Monthly Installment: ₱2,500.00
Per Cutoff: ₱1,250.00
```

This helps employees understand:
- **1st Cutoff (Days 1-15):** ₱1,250 deducted
- **2nd Cutoff (Days 16-31):** ₱1,250 deducted
- **Total per month:** ₱2,500

### **Progress Visualization**
```html
<div style="width: 100%; height: 6px; background: #f0effe;">
    <div style="width: 45%; background: #0b044d;"></div>
</div>
45%
```

### **Balance Color Coding**
- **Yellow (#d9bb00):** Has remaining balance
- **Green (#15803d):** Fully paid (₱0.00)

---

## Database Query

```php
$loans = EmployeeDeduction::with([
    'employee.employmentDetail.departmentRelation',
    'deductionType'
])
->whereHas('deductionType', function($q) {
    $q->where('category', 'LOAN');
})
->orderBy('created_at', 'desc')
->get();
```

---

## JavaScript Functions

### **filterLoans()**
- Filters by employee name, loan type, and status
- Updates visible count
- Shows/hides "no data" row

### **viewLoanDetails(id)**
- Fetches loan data via AJAX
- Calculates progress, amount paid, months remaining
- Displays formatted alert box with all details

### **exportLoans()**
- Redirects to export route
- Downloads CSV with comprehensive loan data

---

## CSV Export Format

**Columns:**
1. Employee ID
2. Employee Name
3. Department
4. Loan Type
5. Provider
6. Total Amount
7. Amount Paid
8. Remaining Balance
9. Progress %
10. Monthly Installment
11. Per Cutoff
12. Months Remaining
13. Start Date
14. End Date
15. Status
16. Remarks

**Example Row:**
```csv
EMP001,Juan Dela Cruz,Municipal Health Office,GSIS Salary Loan,GSIS,50000.00,5000.00,45000.00,10.00,2500.00,1250.00,18,2024-01-01,2025-06-30,ACTIVE,Approved loan
```

---

## Sample Data Display

### **Active Loan:**
```
Employee: Juan Dela Cruz (EMP001)
Department: Municipal Health Office
Loan Type: GSIS Salary Loan (LOAN_GSIS_SALARY)
Provider: GSIS
Total Amount: ₱50,000.00
Remaining Balance: ₱45,000.00 (10.0% paid)
Monthly Installment: ₱2,500.00
Per Cutoff: ₱1,250.00
Progress: [████░░░░░░] 10%
Start Date: Jan 01, 2024
End Date: Jun 30, 2025
Status: ACTIVE
```

### **Completed Loan:**
```
Employee: Maria Santos (EMP002)
Department: Office of the Mayor
Loan Type: Pag-IBIG MPL (LOAN_PAGIBIG_MPL)
Provider: Pag-IBIG
Total Amount: ₱20,000.00
Remaining Balance: ₱0.00 (Fully paid)
Monthly Installment: ₱1,000.00
Per Cutoff: ₱500.00
Progress: [██████████] 100%
Start Date: Jan 01, 2023
End Date: Aug 31, 2024
Status: COMPLETED
```

---

## How Loans Are Processed

### **During Payroll:**

**1st Cutoff (Days 1-15):**
```php
$deduction = $loan->installment_amount / 2; // ₱1,250
$loan->remaining_balance -= $deduction;     // ₱45,000 - ₱1,250 = ₱43,750
```

**2nd Cutoff (Days 16-31):**
```php
$deduction = $loan->installment_amount / 2; // ₱1,250
$loan->remaining_balance -= $deduction;     // ₱43,750 - ₱1,250 = ₱42,500
```

**When Balance Reaches Zero:**
```php
if ($loan->remaining_balance <= 0) {
    $loan->status = 'COMPLETED';
    $loan->end_date = now();
    $loan->remaining_balance = 0;
}
```

---

## Provider Badge Colors

| Provider | Background | Text Color |
|----------|------------|------------|
| GSIS | #0b044d18 | #0b044d (Dark Blue) |
| Pag-IBIG | #15803d18 | #15803d (Green) |
| Other | #6b6a8a18 | #6b6a8a (Gray) |

---

## Status Badge Colors

| Status | Background | Text Color |
|--------|------------|------------|
| ACTIVE | #15803d18 | #15803d (Green) |
| SUSPENDED | #d9bb0018 | #d9bb00 (Yellow) |
| COMPLETED | #6b6a8a18 | #6b6a8a (Gray) |

---

## Use Cases

### **Use Case 1: Check Employee's Loan Balance**
1. Go to **Loans** tab
2. Search for employee name
3. View remaining balance and progress bar
4. Click "View Details" for full information

### **Use Case 2: Monitor Loan Progress**
1. Filter by loan type (e.g., "GSIS Salary Loan")
2. Check progress bars for all employees
3. Identify loans nearing completion (90%+)

### **Use Case 3: Export Loan Report**
1. Apply filters (if needed)
2. Click "Export" button
3. Open CSV in Excel
4. Analyze loan data, calculate totals

### **Use Case 4: Edit Loan Details**
1. Find loan in table
2. Click "Edit" button
3. Update remaining balance or installment
4. Save changes

---

## Testing Checklist

- [x] Table displays loans from database
- [x] Per-cutoff amount calculated correctly (monthly ÷ 2)
- [x] Progress bar displays correct percentage
- [x] Progress bar color changes at 100%
- [x] Provider badge shows correct provider
- [x] Search filter works
- [x] Loan type filter works
- [x] Status filter works
- [x] View details shows formatted information
- [x] Edit opens modal with loan data
- [x] Delete removes loan
- [x] Export downloads CSV
- [x] CSV contains all loan details
- [x] Remaining balance color-coded
- [x] Fully paid loans show "Fully paid"

---

## Important Notes

### **Monthly vs Per-Cutoff:**
⚠️ **The system stores MONTHLY installments, not per-cutoff!**

When entering loans:
- Enter the **monthly** installment amount
- System automatically divides by 2 for display
- Payroll deducts half per cutoff

**Example:**
```
Database: installment_amount = 2500.00 (monthly)
Display: Per Cutoff = ₱1,250.00 (calculated)
Payroll: Deducts ₱1,250 on 1st cutoff, ₱1,250 on 2nd cutoff
```

### **Progress Calculation:**
```php
$amountPaid = $totalAmount - $remainingBalance
$progress = ($amountPaid / $totalAmount) * 100
```

### **Months Remaining:**
```php
$monthsRemaining = ceil($remainingBalance / $monthlyInstallment)
```

---

## Files Modified

1. **routes/web.php** - Added loans export route
2. **adminDeductions.blade.php** - Pass loans data to view
3. **loans.blade.php** - Complete rewrite with database integration

---

## Future Enhancements

1. **Payment History** - Show all deductions made for each loan
2. **Loan Amortization Schedule** - Display payment schedule
3. **Interest Calculation** - Add interest rates and calculations
4. **Bulk Loan Import** - Import multiple loans from CSV
5. **Loan Approval Workflow** - Require approval before activation
6. **Email Notifications** - Notify employees of loan status changes
7. **Loan Summary Report** - Total outstanding by department/provider
8. **Early Payment** - Allow lump sum payments to reduce balance
