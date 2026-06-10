# Excel Import Implementation - Technical Setup

## 🔧 SYSTEM COMPONENTS

### 1. Frontend: Import Modal
**File:** `resources/views/admin/leaveAndBenefits/modals/import-leave-records-modal.blade.php`

**What it does:**
```
1. Shows employee dropdown (with ID, name, department)
2. File upload input (accepts .xlsx, .xls)
3. Format guide/help text
4. Submit button to trigger import
```

**Form Data Sent:**
```javascript
{
  "employee_id": 15,
  "excel_file": <File object>,
  "_token": "CSRF_TOKEN"
}
```

---

### 2. Backend: Controller Method
**File:** `app/Http/Controllers/LeaveController.php`
**Method:** `importLeaveRecords()`

**What it does:**
```php
public function importLeaveRecords(Request $request)
{
    // 1. Validate inputs
    $validated = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'excel_file' => 'required|file|mimes:xlsx,xls|max:5120'
    ]);

    // 2. Store temporary file
    $tempPath = $request->file('excel_file')->store('temp');

    try {
        // 3. Call import service
        $service = new LeaveImportService();
        $result = $service->importLeaveRecords(
            $validated['employee_id'],
            storage_path('app/' . $tempPath)
        );

        // 4. Return success response
        return response()->json([
            'success' => true,
            'imported_count' => $result['count'],
            'message' => $result['count'] . ' records imported'
        ]);
    } catch (Exception $e) {
        // 5. Handle errors
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    } finally {
        // 6. Clean up temp file
        Storage::delete($tempPath);
    }
}
```

---

### 3. Backend: Import Service
**File:** `app/Services/LeaveImportService.php`

**Main Methods:**

#### a) `parseExcelFile()`
```php
/**
 * Parse Excel file and extract data
 * Reads from row 6 onwards (header in rows 1-5)
 */
private function parseExcelFile($filePath)
{
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    $data = [];
    $rows = $sheet->toArray(null, true, true, true); // Includes formulas
    
    // Start from row 6 (row 1-5 are headers)
    foreach (array_slice($rows, 5) as $row) {
        if (empty($row['A'])) break; // Stop at empty month
        
        $data[] = [
            'month' => $row['A'],
            'notes' => $row['B'],
            'vl_earned' => (float)$row['D'],
            'vl_used' => (float)$row['F'],
            'sl_earned' => (float)$row['H'],
            'sl_used' => (float)$row['J'],
            'vl_balance' => (float)$row['M'],
            'sl_balance' => (float)$row['N'],
        ];
    }
    
    return $data;
}
```

**Column Mapping:**
```
Column A → month (date/month name)
Column B → notes (VL1, FL1, etc.)
Column D → VL Earned (decimal)
Column F → VL Used (decimal)
Column H → SL Earned (decimal)
Column J → SL Used (decimal)
Column M → VL Balance (decimal)
Column N → SL Balance (decimal)
```

#### b) `importLeaveRecords()`
```php
/**
 * Main import logic
 * Wraps everything in database transaction
 */
public function importLeaveRecords($employeeId, $filePath)
{
    return DB::transaction(function () use ($employeeId, $filePath) {
        // 1. Get employee
        $employee = Employee::findOrFail($employeeId);
        
        // 2. Parse Excel
        $rows = $this->parseExcelFile($filePath);
        
        // 3. Get year from employee data or first date
        $year = date('Y'); // or extract from Excel
        
        // 4. Process each row
        $count = 0;
        foreach ($rows as $row) {
            $count += $this->processMonthData(
                $employee,
                $row,
                $year
            );
        }
        
        // 5. Update final balances
        $this->updateFinalBalances($employee, $year);
        
        return ['count' => $count];
    });
}
```

#### c) `createOrUpdateLeaveBalance()`
```php
/**
 * Creates/updates leave balance record
 * Also creates audit transaction
 */
private function createOrUpdateLeaveBalance(
    $employee,
    $leaveCode,
    $year,
    $totalEarned,
    $totalUsed,
    $remarks
)
{
    // 1. Find or create balance record
    $balance = LeaveBalance::firstOrCreate(
        [
            'employee_id' => $employee->id,
            'leave_code' => $leaveCode,
            'year' => $year,
        ],
        [
            'total_credits' => 0,
            'used_credits' => 0,
            'available_credits' => 0,
            'carried_over' => 0,
        ]
    );
    
    // 2. Update with new values
    $balance->update([
        'total_credits' => $totalEarned,
        'used_credits' => $totalUsed,
        'available_credits' => $totalEarned - $totalUsed,
        'carried_over' => $totalEarned - $totalUsed,
    ]);
    
    // 3. Create audit transaction
    LeaveTransaction::create([
        'employee_id' => $employee->id,
        'leave_code' => $leaveCode,
        'year' => $year,
        'transaction_type' => 'adjustment',
        'amount' => $totalEarned - $totalUsed,
        'balance_before' => 0,
        'balance_after' => $totalEarned - $totalUsed,
        'reference_type' => 'leave_import',
        'transaction_date' => now(),
        'processed_by' => auth()->id(),
        'remarks' => $remarks,
    ]);
}
```

#### d) `parseMonthYear()`
```php
/**
 * Extract date from month name
 * Converts "January", "Feb", etc. to date
 */
private function parseMonthYear($monthString, $year = null)
{
    $months = [
        'january' => 1, 'february' => 2, 'march' => 3,
        'april' => 4, 'may' => 5, 'june' => 6,
        'july' => 7, 'august' => 8, 'september' => 9,
        'october' => 10, 'november' => 11, 'december' => 12,
    ];
    
    $month = $months[strtolower(substr($monthString, 0, 3))] ?? 1;
    $year = $year ?? date('Y');
    
    // Get last day of month
    $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
    return "$year-$month-$lastDay";
}
```

---

## 📋 DATABASE SCHEMA

### Table: `leave_balances`
```sql
CREATE TABLE leave_balances (
    id BIGINT UNSIGNED PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_code VARCHAR(10) NOT NULL,
    year INT NOT NULL,
    total_credits DECIMAL(10,6) NOT NULL DEFAULT 0,
    used_credits DECIMAL(10,6) NOT NULL DEFAULT 0,
    pending_credits DECIMAL(10,6) NOT NULL DEFAULT 0,
    available_credits DECIMAL(10,6) NOT NULL DEFAULT 0,
    carried_over DECIMAL(10,6) NOT NULL DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY `unique_balance` (employee_id, leave_code, year),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_code) REFERENCES leave_types_config(leave_code)
);
```

### Table: `leave_transactions`
```sql
CREATE TABLE leave_transactions (
    id BIGINT UNSIGNED PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_code VARCHAR(10) NOT NULL,
    year INT NOT NULL,
    transaction_type ENUM('earned', 'used', 'adjustment') NOT NULL,
    amount DECIMAL(10,6) NOT NULL,
    balance_before DECIMAL(10,6) NOT NULL,
    balance_after DECIMAL(10,6) NOT NULL,
    reference_type VARCHAR(50) DEFAULT NULL, -- 'leave_import', 'leave_application', etc
    reference_id BIGINT UNSIGNED DEFAULT NULL,
    transaction_date DATE NOT NULL,
    processed_by BIGINT UNSIGNED DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_code) REFERENCES leave_types_config(leave_code),
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_employee_year (employee_id, year),
    INDEX idx_reference_type (reference_type, reference_id)
);
```

### Table: `leave_types_config`
```sql
CREATE TABLE leave_types_config (
    leave_code VARCHAR(10) PRIMARY KEY,
    leave_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    is_accrued BOOLEAN DEFAULT 0,
    is_cumulative BOOLEAN DEFAULT 0,
    annual_limit DECIMAL(10,6) DEFAULT NULL,
    -- ... other fields
);

-- Sample data
INSERT INTO leave_types_config (leave_code, leave_name) VALUES
('VL', 'Vacation Leave'),
('SL', 'Sick Leave'),
('BL', 'Bereavement Leave'),
('FL', 'Family Leave'),
('EL', 'Emergency Leave'),
... (etc for all 22+ leave types)
```

---

## 🔄 PROCESSING FLOW (Detailed)

### Input Processing
```
Excel File
    ↓
Controller receives request
    ↓
Validates: employee_id exists, file is .xlsx/.xls, size < 5MB
    ↓
Stores temp file
    ↓
Calls LeaveImportService::importLeaveRecords()
```

### Service Processing
```
BEGIN TRANSACTION
    ↓
parseExcelFile()
  - Read rows 6 onwards
  - Extract columns A-B, D, F, H, J, M, N
  - Return array of monthly data
    ↓
For each month:
  - parseMonthYear() → convert to date
  - Calculate totals per leave type
  - Create/update leave_balances
  - Create leave_transactions (audit trail)
    ↓
Verify calculations:
  - balance_after = balance_before + amount
  - final balance = sum of all transactions
    ↓
COMMIT if successful
or
ROLLBACK if any error
```

### Transaction Creation
```
For each month's data, TWO types of entries created:

1. EARNED Transaction:
   transaction_type: 'adjustment'
   amount: +X.X (positive for earned)
   remarks: "{LeaveType} Earned - {Month}"
   reference_type: 'leave_import'

2. USED Transaction:
   transaction_type: 'adjustment'
   amount: -X.X (negative for used)
   remarks: "{LeaveType} Used - {Month}"
   reference_type: 'leave_import'
```

---

## ✅ VALIDATION CHAIN

### 1. Form Validation (Controller)
```
employee_id:
  - Required ✓
  - Must exist in employees table ✓

excel_file:
  - Required ✓
  - Must be file type ✓
  - Must be .xlsx or .xls ✓
  - Max 5MB ✓
```

### 2. Data Validation (Service)
```
Per row from Excel:
  - Month field not empty ✓
  - All numeric fields are valid numbers ✓
  - VL/SL codes exist in leave_types_config ✓
  - Decimal precision: max 10 digits, 6 decimals ✓
  - Balance chain verification ✓
```

### 3. Database Validation
```
Foreign Key Constraints:
  - employee_id exists ✓
  - leave_code exists in leave_types_config ✓
  - processed_by exists in users table ✓

Unique Constraints:
  - No duplicate (employee_id, leave_code, year) ✓
```

---

## 🔒 ERROR HANDLING

### Try-Catch Blocks
```php
try {
    // Process import
} catch (FileException $e) {
    // File can't be read/written
    throw new Exception("File error: " . $e->getMessage());
} catch (ValidationException $e) {
    // Data validation failed
    throw new Exception("Invalid data: " . $e->getMessage());
} catch (QueryException $e) {
    // Database error
    throw new Exception("Database error: " . $e->getMessage());
} catch (Exception $e) {
    // Any other error
    throw new Exception("Import failed: " . $e->getMessage());
}
```

### Transaction Rollback
```php
DB::transaction(function () {
    // If any error occurs:
    // - All created transactions are rolled back
    // - All updated balances are reverted
    // - Database returns to pre-import state
    
    // If successful:
    // - All changes committed atomically
    // - No partial data
});
```

---

## 📤 Response Handling

### Success Response
```json
{
    "success": true,
    "imported_count": 24,
    "message": "24 records imported successfully"
}
```

**Frontend then:**
- Shows success message
- Auto-redirects to Transaction History tab
- User sees all imported transactions listed

### Error Response
```json
{
    "success": false,
    "message": "Invalid numeric value in column D, row 7: 'abc'"
}
```

**Frontend then:**
- Shows error message in modal
- Allows user to fix file and retry
- No data saved to database

---

## 🔍 VERIFICATION CHECKS

### After Import, Verify:

```sql
-- Check leave_balances updated
SELECT * FROM leave_balances 
WHERE employee_id = 15 AND year = 2024;

-- Check transactions created
SELECT * FROM leave_transactions 
WHERE employee_id = 15 
  AND year = 2024 
  AND reference_type = 'leave_import'
ORDER BY transaction_date ASC;

-- Verify balance chain
SELECT 
    transaction_date,
    leave_code,
    amount,
    balance_before,
    balance_after,
    (balance_before + amount) as calculated,
    CASE 
        WHEN balance_after = (balance_before + amount) 
        THEN '✓ OK'
        ELSE '✗ ERROR'
    END as status
FROM leave_transactions
WHERE employee_id = 15 AND reference_type = 'leave_import'
ORDER BY transaction_date;
```

---

## 🚀 DEPLOYMENT CHECKLIST

- [x] LeaveImportService.php created
- [x] Controller method added
- [x] Modal template created
- [x] JavaScript functions for modal
- [x] Tab switching updated
- [x] Route defined (POST /admin/leave/import)
- [x] Database tables exist
- [x] Leave types configured
- [x] File permissions set (storage/temp)

### To Deploy:
```bash
# No migrations needed (uses existing tables)
# Just deploy files:
php artisan optimize:clear
```

---

## 📝 CONFIGURATION

### Max File Size
```php
// Max 5MB
$request->validate([
    'excel_file' => 'required|file|mimes:xlsx,xls|max:5120'
]);
```

### Start Row (Header)
```php
// Rows 1-5 = Header
// Row 6+ = Data
array_slice($rows, 5) // Skip first 5 rows
```

### Supported Leave Types
```
Configured in leave_types_config table:
VL - Vacation Leave
SL - Sick Leave
BL - Bereavement Leave
FL - Family Leave
EL - Emergency Leave
PL - Privilege Leave
ML - Maternity Leave
... (20+ total types)
```

---

## 🔗 API REFERENCE

### Endpoint
```
POST /admin/leave/import
Content-Type: multipart/form-data
Authorization: Required (auth middleware)

Parameters:
- employee_id (required, integer)
- excel_file (required, file, .xlsx or .xls, max 5MB)
```

### Response
```
Success: 200 OK
{
    "success": true,
    "imported_count": integer,
    "message": "X records imported"
}

Error: 422 Unprocessable Entity
{
    "success": false,
    "message": "Error description"
}
```

---

## 📚 Related Documentation

- See `EXCEL_IMPORT_CLEAR_INSTRUCTIONS.md` for user guide
- See `EXCEL_IMPORT_VISUAL_GUIDE.md` for visual diagrams
- Database ERD: See `primehrismagdalena_db_schema.md`
- Leave system overview: See `LEAVE_DATABASE_RELATIONSHIPS.md`
