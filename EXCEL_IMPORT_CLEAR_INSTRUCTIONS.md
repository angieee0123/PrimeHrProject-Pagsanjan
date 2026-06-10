# Excel Leave Records Import - Clear Instructions

## 📋 OVERVIEW
When you import an Excel file (like "ate beng.xlsx"), the system will **RECORD EVERYTHING** from the employee's leave history:
- Initial leave credits they started with
- All monthly earnings and deductions
- Running balances throughout the year
- Complete historical transaction log

---

## 🎯 WHAT GETS RECORDED IN THE DATABASE

### 1. **LEAVE BALANCES** (Current Summary)
The system records the **final balance** for each leave type:
```
For each employee and leave type (VL, SL, etc.):
- Total Credits Earned: Sum of all earnings
- Used Credits: Sum of all deductions  
- Available Balance: Credits - Used
- Year: The year data applies to
```

**Database Table:** `leave_balances`

**Example:**
```
Employee: Ate Beng
Year: 2024
Vacation Leave (VL):
  - Total Credits: 12.5 days
  - Used: 3.0 days
  - Available: 9.5 days

Sick Leave (SL):
  - Total Credits: 10.0 days
  - Used: 1.5 days
  - Available: 8.5 days
```

---

### 2. **LEAVE TRANSACTIONS** (Complete History)
Every action from the Excel gets recorded as a transaction:

**Database Table:** `leave_transactions`

Each transaction records:
```
- Employee ID: Who this is for
- Leave Code: Type (VL, SL, BL, etc.)
- Year: What year
- Transaction Type: 'adjustment' (for imported data)
- Amount: Positive (earned) or Negative (deducted)
- Balance Before: Balance before this transaction
- Balance After: Balance after this transaction
- Reference Type: 'leave_import' (marks it as imported)
- Transaction Date: When it occurred
- Remarks: Details (e.g., "VL Earned - January", "SL Used - Sick day")
```

---

## 📊 EXCEL FORMAT SPECIFICATION

Your Excel file should look like this:

```
ROW 1-5: HEADER INFORMATION
- Row 1: Employee Name, Position, Department
- Row 2: Leave Year
- Rows 3-5: Any other metadata

ROW 6+: DATA STARTS HERE
         A           B              D              F              H              J              M           N
         Month    Notes        VL Earned      VL Used       SL Earned      SL Used       VL Balance  SL Balance
-------- ------- --------- -------------- -------------- -------------- -------------- --------- ---------
Row 6:   January  VL1 FL1       1.0            0              0.833          0              1.0        0.833
Row 7:   February VL1           1.0            0              0.833          0              2.0        1.666
Row 8:   March    FL1           0              1.0            0.833          0              1.0        2.499
...
```

### Column Mapping:
| Column | Field | Purpose |
|--------|-------|---------|
| A | Month/Period | Month name or date (used for reference) |
| B | Notes | Leave type codes (VL1, FL1, etc.) |
| D | VL Earned | Vacation Leave earned this period |
| F | VL Used | Vacation Leave deducted this period |
| H | SL Earned | Sick Leave earned this period |
| J | SL Used | Sick Leave deducted this period |
| M | VL Balance | Running balance for VL |
| N | SL Balance | Running balance for SL |

**NOTE:** Empty columns between data columns are SKIPPED. Only the specified columns are read.

---

## 🔄 HOW THE IMPORT WORKS - STEP BY STEP

### Step 1: Parse Excel File
```
✓ Read Excel starting from row 6
✓ Extract month/period from Column A
✓ Extract data from columns: B, D, F, H, J, M, N
✓ Validate all numeric values are proper decimals
```

### Step 2: Calculate Each Transaction
For each row, the system creates TWO transactions (one for each leave type):

**Example - January Row:**
```
Input from Excel:
  VL Earned: 1.0, VL Used: 0, VL Balance: 1.0
  SL Earned: 0.833, SL Used: 0, SL Balance: 0.833

Generated Transactions:

TRANSACTION 1 (VL - Earned):
  Amount: +1.0
  Remarks: "VL Earned - January"
  Balance After: 1.0

TRANSACTION 2 (SL - Earned):
  Amount: +0.833
  Remarks: "SL Earned - January"
  Balance After: 0.833
```

### Step 3: Build Transaction History
Each transaction shows:
- The action (earned/used)
- The amount
- The running balance after that action
- Reference type: 'leave_import' (so you know it came from import)

**Transaction Record:**
```
employee_id: 15
leave_code: 'VL'
year: 2024
transaction_type: 'adjustment'
amount: +1.0
balance_before: 0
balance_after: 1.0
reference_type: 'leave_import'
remarks: 'VL Earned - January'
transaction_date: 2024-01-31
```

### Step 4: Update Final Balances
After processing all rows, calculate totals:

```
leave_balances Table:

Employee: 15, Leave: VL, Year: 2024
- total_credits = sum of all VL Earned (1.0 + 1.0 + 0 + ...)
- used_credits = sum of all VL Used (0 + 0 + 1.0 + ...)
- available_credits = total_credits - used_credits
- carried_over = final balance from Column M
```

---

## 💾 DATABASE RECORDS CREATED

### For "ate beng.xlsx" Example:

**In `leave_balances` table:**
```
| id | employee_id | leave_code | year | total_credits | used_credits | available_credits | carried_over |
|----|-------------|-----------|------|--------------|--------------|-------------------|--------------|
| 1  | 15          | VL        | 2024 | 12.5         | 3.0          | 9.5              | 9.5          |
| 2  | 15          | SL        | 2024 | 10.0         | 1.5          | 8.5              | 8.5          |
```

**In `leave_transactions` table:**
```
| id | employee_id | leave_code | year | transaction_type | amount | balance_before | balance_after | reference_type | remarks              | transaction_date |
|----|-----------  |----------|------|-----------------|--------|----------------|---------------|----------------|----------------------|------------------|
| 1  | 15          | VL       | 2024 | adjustment      | 1.0    | 0              | 1.0           | leave_import   | VL Earned - January  | 2024-01-31       |
| 2  | 15          | SL       | 2024 | adjustment      | 0.833  | 0              | 0.833         | leave_import   | SL Earned - January  | 2024-01-31       |
| 3  | 15          | VL       | 2024 | adjustment      | 1.0    | 1.0            | 2.0           | leave_import   | VL Earned - February | 2024-02-28       |
| 4  | 15          | SL       | 2024 | adjustment      | 0.833  | 0.833          | 1.666         | leave_import   | SL Earned - February | 2024-02-28       |
| ... (continues for each row) |
```

---

## 🚀 HOW TO USE THE IMPORT FEATURE

### Step 1: Access Import Records
1. Go to **Leave & Benefits** page
2. Click the **"Import Records"** tab
3. Click **"Import Leave Records"** button

### Step 2: Fill Import Form
```
1. Select Employee: 
   - Choose from dropdown (shows Employee ID + Name + Department)

2. Upload Excel File:
   - Click file upload area
   - Select your .xlsx or .xls file
   - Max 5MB
```

### Step 3: Confirm Import
```
1. Click "Import Records" button
2. System validates the file
3. Shows preview/summary of what will be imported
4. Confirm to proceed
```

### Step 4: View Results
```
✓ Success message appears
✓ Records saved to database
✓ Automatically redirects to Transaction History
✓ See all imported transactions listed
```

---

## 📝 WHAT DATA IS ACTUALLY STORED

### Initial State
Before import: Leave balances = 0

### After Import
```
From Excel data:
- All monthly earnings → recorded as positive transactions
- All monthly deductions → recorded as negative transactions
- Running balances → used to verify calculation accuracy
- Employee history → complete year-by-year record
```

### Transaction Audit Trail
Every imported record shows:
- ✓ When it was added (transaction_date)
- ✓ Who added it (reference_type: 'leave_import')
- ✓ Amount changed
- ✓ Balance before and after
- ✓ Detailed remarks

---

## ✅ VERIFICATION CHECKLIST

After import completes, verify in database:

```
✓ Leave Balances Updated
  - Check leave_balances table has new records
  - Verify totals match Excel final values
  
✓ Transaction History Complete
  - Check leave_transactions has all row entries
  - Verify balance_before → balance_after chain is correct
  - Confirm all amounts are decimal(10,6) precision
  
✓ Employee Connected
  - All records linked to correct employee_id
  - Employee name matches
  
✓ Year Correct
  - Year field matches Excel data year
  
✓ Leave Types Valid
  - Only leave_code values that exist in leave_types_config
```

---

## 🔍 DATA ACCURACY

The system ensures:
1. **Decimal Precision**: All amounts stored as decimal(10,6) ✓
2. **Balance Chain**: Each balance_after = previous balance_before + amount ✓
3. **No Partial Imports**: Either ALL data imports or NOTHING (transaction rollback) ✓
4. **Unique Records**: Can't import same employee/leave/year twice ✓
5. **Referential Integrity**: Only valid employee_ids and leave_codes accepted ✓

---

## 📊 EXAMPLE: COMPLETE FLOW

**File: ate_beng.xlsx**
- Employee: Ate Beng (ID: 15)
- Year: 2024

**Excel Data:**
```
January:  VL +1.0, SL +0.833
February: VL +1.0, SL +0.833
March:    VL +0, SL +0.833, VL Used -1.0
```

**System Processes:**
1. Parses 3 rows (January, February, March)
2. Creates 6 transactions (2 per row = 6 total)
3. Updates leave_balances: VL=2.0 earned, 1.0 used, 1.0 available
4. Updates leave_balances: SL=2.499 earned, 0 used, 2.499 available

**Database Result:**
```
leave_balances:
  - VL: total=2.0, used=1.0, available=1.0
  - SL: total=2.499, used=0, available=2.499

leave_transactions:
  1. VL +1.0 (January) → balance: 0→1.0
  2. SL +0.833 (January) → balance: 0→0.833
  3. VL +1.0 (February) → balance: 1.0→2.0
  4. SL +0.833 (February) → balance: 0.833→1.666
  5. VL -1.0 (March) → balance: 2.0→1.0
  6. SL +0.833 (March) → balance: 1.666→2.499
```

---

## 🛠️ TECHNICAL REFERENCE

### Service Class
`app/Services/LeaveImportService.php`

Methods:
- `parseExcelFile()` - Reads Excel file
- `importLeaveRecords()` - Main import logic
- `createOrUpdateLeaveBalance()` - Updates balances
- `parseMonthYear()` - Extracts date from month name

### Controller Method
`app/Http/Controllers/LeaveController.php@importLeaveRecords`

### Route
`POST /admin/leave/import`

### Database Tables
- `leave_balances` - Current leave summary
- `leave_transactions` - Transaction history
- `leave_types_config` - Valid leave types (VL, SL, etc.)

---

## ❌ COMMON ERRORS & SOLUTIONS

| Error | Cause | Solution |
|-------|-------|----------|
| "Invalid Excel format" | Wrong file type | Use .xlsx or .xls format |
| "File size exceeds 5MB" | Large file | Compress or split Excel |
| "Invalid employee" | Employee not found | Double-check employee ID |
| "Data starts before row 6" | Wrong format | Ensure data starts at row 6 |
| "Invalid numeric values" | Non-numbers in data | Check column D,F,H,J,M,N have only numbers |
| "Import failed" | Database error | Check database connection |

---

## 📌 SUMMARY

**When you import Excel:**
1. ✅ All employee leave history is recorded
2. ✅ Every earning/deduction creates a transaction
3. ✅ Final balances calculated and stored
4. ✅ Complete audit trail maintained
5. ✅ Data available for leave requests and payroll

**Result:** Employee's complete leave history migrated into system with full transaction record!
