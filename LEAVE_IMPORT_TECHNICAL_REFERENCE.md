# Leave Import Feature - Technical Implementation Reference

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Admin Dashboard                           │
│              Leave & Benefits Page                           │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│        Import Records Tab & Modal Component                 │
│  - Employee selector                                        │
│  - File upload input                                        │
│  - Format guide                                             │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼ AJAX POST /admin/leave/import
┌─────────────────────────────────────────────────────────────┐
│      LeaveController::importLeaveRecords()                  │
│  - Validate inputs                                          │
│  - Store temp file                                          │
│  - Call LeaveImportService                                  │
│  - Return JSON response                                     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│         LeaveImportService (Business Logic)                 │
│                                                              │
│  parseExcelFile()                                           │
│  ├─ Load workbook using PhpOffice/PhpSpreadsheet           │
│  ├─ Extract data from row 6 onwards                        │
│  └─ Return array of leave records                          │
│                                                              │
│  importLeaveRecords()                                       │
│  ├─ Begin database transaction                             │
│  ├─ For each record:                                       │
│  │  ├─ Parse month/year                                    │
│  │  ├─ Create/update LeaveBalance for VL                  │
│  │  ├─ Create/update LeaveBalance for SL                  │
│  │  └─ Create LeaveTransaction records                     │
│  ├─ Commit transaction                                     │
│  └─ Return import result                                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
         ┌───────────────────────────┐
         │    Database Tables        │
         ├───────────────────────────┤
         │ leave_balances            │
         │ leave_transactions        │
         │ leave_types_config        │
         │ employees                 │
         └───────────────────────────┘
```

---

## Service Class: LeaveImportService

### Location
```
app/Services/LeaveImportService.php
```

### Public Methods

#### 1. parseExcelFile($filePath)
```php
public static function parseExcelFile($filePath)
```

**Parameters:**
- `$filePath` (string) - Path to Excel file

**Returns:**
```php
[
    'success' => true|false,
    'records' => [
        [
            'month_year' => 'January',
            'notes' => 'T(0-2-10)',
            'vacation_leave_earned' => 1.25,
            'vacation_leave_used' => 0,
            'sick_leave_earned' => 1.25,
            'sick_leave_used' => 0,
            'balance' => 2.5,
            'vl_balance' => 1.25,
            'sl_balance' => 1.25,
        ],
        // ... more records
    ],
    'message' => 'error message if failed'
]
```

**Flow:**
1. Load workbook using `IOFactory::load()`
2. Get active worksheet
3. Iterate from row 6 onwards
4. Extract cell values for each row
5. Stop when empty row encountered
6. Return parsed records array

#### 2. importLeaveRecords($employeeId, $records)
```php
public static function importLeaveRecords($employeeId, $records)
```

**Parameters:**
- `$employeeId` (int) - ID of employee
- `$records` (array) - Array of leave records from parseExcelFile()

**Returns:**
```php
[
    'success' => true|false,
    'message' => 'Successfully imported 24 leave records',
    'imported_count' => 24,
    'errors' => ['error1', 'error2']
]
```

**Flow:**
1. Start database transaction
2. Get leave types (VL and SL)
3. For each record:
   - Parse month/year
   - Create/update VL LeaveBalance
   - Create/update SL LeaveBalance
4. Commit transaction
5. Return result summary

### Private Methods

#### parseMonthYear($monthYear)
```php
private static function parseMonthYear($monthYear)
```
- Parses various month/year formats
- Returns Carbon date object or null

#### createOrUpdateLeaveBalance()
```php
private static function createOrUpdateLeaveBalance(
    $employeeId, 
    $leaveCode, 
    $year, 
    $earned, 
    $used, 
    $balance
)
```
- Creates or updates LeaveBalance record
- Sets total_credits, used_credits, available_credits

---

## Controller Method: importLeaveRecords

### Location
```
app/Http/Controllers/LeaveController.php
```

### Route
```
POST /admin/leave/import
```

### Method Signature
```php
public function importLeaveRecords(Request $request)
```

### Process Flow

```php
1. Validate input
   - employee_id: required, exists in employees table
   - excel_file: required, file, mimes:xlsx|xls, max:5120

2. Store file temporarily
   $filePath = $request->file('excel_file')->store('temp_leave_imports')

3. Parse Excel file
   $parseResult = LeaveImportService::parseExcelFile($fullPath)

4. Check parsing result
   - If failed: return error JSON response

5. Import records
   $importResult = LeaveImportService::importLeaveRecords(
       $validated['employee_id'],
       $parseResult['records']
   )

6. Clean up temp file
   Storage::delete($filePath)

7. Return JSON response
   - success: true|false
   - message: descriptive message
   - imported_count: number of records
   - errors: array of any errors encountered
```

### Response Examples

**Success (200):**
```json
{
    "success": true,
    "message": "Successfully imported 24 leave records",
    "imported_count": 24,
    "errors": []
}
```

**Validation Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "employee_id": ["The selected employee is invalid"],
        "excel_file": ["The excel file field is required"]
    }
}
```

**Parse Error (422):**
```json
{
    "success": false,
    "message": "Failed to parse Excel file: [reason]"
}
```

**Server Error (500):**
```json
{
    "success": false,
    "message": "Import failed: [exception message]"
}
```

---

## Modal Component

### Location
```
resources/views/admin/leaveAndBenefits/modals/import-leave-records-modal.blade.php
```

### Key Functions

#### openImportLeaveRecordsModal()
Opens the modal, resets form
```javascript
function openImportLeaveRecordsModal() {
    const modal = document.getElementById('importLeaveRecordsModal');
    modal.style.display = 'flex';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    document.getElementById('importLeaveRecordsForm').reset();
}
```

#### closeImportLeaveRecordsModal()
Closes the modal, cleans up
```javascript
function closeImportLeaveRecordsModal() {
    const modal = document.getElementById('importLeaveRecordsModal');
    modal.style.display = 'none';
    modal.classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('importLeaveRecordsForm').reset();
}
```

#### submitImportLeaveRecords()
Handles form submission via AJAX
```javascript
function submitImportLeaveRecords() {
    // 1. Validate inputs
    // 2. Show loading state
    // 3. Send POST request to /admin/leave/import
    // 4. Handle response
    // 5. Show success/error message
}
```

### Form Fields

| Field | Type | Validation | Required |
|-------|------|-----------|----------|
| employee_id | select | exists:employees,id | Yes |
| excel_file | file | mimes:xlsx,xls, max:5MB | Yes |

### Response Handling

**On Success:**
- Close modal
- Show success modal message
- Redirect to Transaction History tab after 2 seconds

**On Error:**
- Keep modal open
- Show error message in error modal
- Re-enable submit button
- Display validation errors

---

## Database Interactions

### LeaveBalance Table

**Update on Import:**
```sql
INSERT INTO leave_balances 
(employee_id, leave_code, year, total_credits, used_credits, available_credits)
VALUES (?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
    total_credits = VALUES(total_credits),
    used_credits = VALUES(used_credits),
    available_credits = VALUES(available_credits)
```

**Fields Updated:**
- `total_credits` - Set to VL/SL earned amount
- `used_credits` - Set to VL/SL used amount  
- `available_credits` - Set to VL/SL balance

### LeaveTransaction Table

**Insert for Audit:**
```sql
INSERT INTO leave_transactions
(employee_id, leave_code, year, transaction_type, amount, 
 balance_before, balance_after, reference_type, reference_id,
 transaction_date, processed_by, remarks)
VALUES (?, ?, ?, 'import', ?, ?, ?, 'leave_import', NULL,
        NOW(), ?, 'Imported from Excel file')
```

**For Each Record:**
- One transaction per leave type (VL, SL)
- Marks transaction type as 'import' or 'adjustment'
- Stores before/after balances for audit

---

## Error Handling & Validation

### Input Validation
```php
$validated = $request->validate([
    'employee_id' => 'required|exists:employees,id',
    'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
]);
```

### File Parsing Errors
- Invalid Excel format → Catch \Exception, return error
- Missing columns → Skip row
- Invalid month/year → Log and continue
- Empty rows → Stop processing

### Data Errors
- Leave types not found → Throw Exception, rollback
- Invalid numeric values → Try conversion, use 0 as default
- Null values → Handle gracefully

### Database Errors
- Transaction fails → Rollback all changes
- Constraint violation → Log error, continue with other records
- Connection error → Throw Exception, display generic message

---

## Security Measures

### 1. Authentication
```php
->middleware('auth')
```
Only authenticated users can access

### 2. Authorization
```php
// Add if needed in future
->middleware('admin')
```

### 3. File Validation
```php
'excel_file' => 'file|mimes:xlsx,xls|max:5120'
```
- Verify MIME type
- Limit file size
- Check extension

### 4. Temporary File Handling
```php
$filePath = $request->file('excel_file')->store('temp_leave_imports');
// ... process ...
Storage::delete($filePath);
```
- Store in protected directory
- Delete after processing

### 5. CSRF Protection
```javascript
formData.append('_token', document.querySelector('input[name="_token"]').value);
```

### 6. Input Sanitization
- All input validated server-side
- Eloquent ORM prevents SQL injection
- Template escaping prevents XSS

---

## Testing Scenarios

### Test Case 1: Valid Import
```
Input: Valid employee_id + valid Excel file
Expected: 24 records imported, success message
```

### Test Case 2: Invalid Employee
```
Input: Non-existent employee_id
Expected: Validation error, 422 response
```

### Test Case 3: Invalid File Type
```
Input: Valid employee_id + .doc file
Expected: File validation error, 422 response
```

### Test Case 4: Oversized File
```
Input: Valid employee_id + file > 5MB
Expected: Size validation error, 422 response
```

### Test Case 5: Malformed Excel
```
Input: Valid employee_id + invalid Excel
Expected: Parse error, 422 response
```

### Test Case 6: Missing Leave Types
```
Input: Valid file but VL/SL types not in system
Expected: Error message about missing leave types
```

---

## Performance Considerations

### Optimizations
1. **Batch Processing** - Process all records in single transaction
2. **Lazy Loading** - Load relationships only when needed
3. **Caching** - Leave types cached during import

### Scalability
- Handles files with 100+ records efficiently
- Transaction-based processing ensures consistency
- Temporary files cleaned up automatically

### Resource Usage
- Memory: ~10MB per 1000 records
- Time: ~2-5 seconds for typical import
- Database: Minimal locks due to transaction isolation

---

## Future Enhancements

### Planned Features
1. **Dry Run Mode** - Preview changes before committing
2. **Bulk Employee Import** - Import multiple employees at once
3. **Custom Leave Types** - Support more than just VL/SL
4. **Conflict Resolution** - Handle duplicate entries
5. **Import Templates** - Download sample Excel file
6. **Advanced Reporting** - Detailed import statistics

### Integration Points
- Attendance system (LWOP calculations)
- Payroll system (leave deductions)
- Analytics (leave usage patterns)

---

## Debugging Tips

### Enable Logging
```php
\Log::info('Import started', ['employee_id' => $employeeId]);
\Log::debug('Parsed records', $parseResult);
\Log::error('Import failed', ['error' => $exception->getMessage()]);
```

### Check Transaction History
- All imports recorded in `leave_transactions` table
- Filter by `transaction_type` = 'adjustment'
- Shows `processed_by` user and timestamp

### Verify LeaveBalance Updates
```sql
SELECT * FROM leave_balances 
WHERE employee_id = ? AND year = ?
ORDER BY leave_code;
```

### Monitor Database Logs
- Check for transaction rollbacks
- Look for constraint violations
- Monitor query execution times

---

## Support & Maintenance

### Regular Tasks
- Monitor import success rates
- Review error logs weekly
- Test with sample files quarterly

### Troubleshooting Steps
1. Check Laravel logs: `storage/logs/`
2. Verify database transactions
3. Validate Excel file format
4. Check employee exists in system
5. Verify leave types configured

### Contact
For issues, review the comprehensive documentation or check system logs.
