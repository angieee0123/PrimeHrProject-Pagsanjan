# Leave Records Import Feature - Implementation Guide

## Overview
This document describes the new **Excel Import Feature** added to the Prime HR System that allows administrators to migrate historical leave records from Excel files into the database for specific employees.

## Feature Description

### Purpose
The feature enables the admin to:
- Select an employee
- Upload their historical leave records from an Excel file
- Automatically parse and import leave balance data into the system
- Record transaction history for auditing purposes

### Use Case
This feature is designed for clients like Pagsanjan Municipal Government who have existing leave records in Excel format and need to migrate them into the new HR system.

---

## Files Created/Modified

### 1. **Service Class: LeaveImportService**
**Path:** `app/Services/LeaveImportService.php`

**Functionality:**
- `parseExcelFile($filePath)` - Parses Excel file and extracts leave records
- `importLeaveRecords($employeeId, $records)` - Imports parsed records into database
- Private helper methods for data transformation

**Key Features:**
- Handles Excel file parsing using PhpOffice/PhpSpreadsheet
- Validates data structure (Month/Year format, balance columns)
- Creates/updates LeaveBalance and LeaveTransaction records
- Supports both Vacation Leave (VL) and Sick Leave (SL)

### 2. **Controller Method: importLeaveRecords**
**File:** `app/Http/Controllers/LeaveController.php`

**Route:** `POST /admin/leave/import`

**Validation:**
- `employee_id` - Required, must exist in employees table
- `excel_file` - Required, must be .xlsx or .xls format, max 5MB

**Response:** JSON with success/error status and imported count

### 3. **Modal Component**
**Path:** `resources/views/admin/leaveAndBenefits/modals/import-leave-records-modal.blade.php`

**Features:**
- Employee selection dropdown (populated from database)
- File upload input (accepts .xlsx, .xls)
- Format guide showing expected Excel structure
- Loading state during upload
- Error/success feedback

### 4. **Updated Main View**
**File:** `resources/views/admin/leaveAndBenefits/adminLeaveAndBenefits.blade.php`

**Changes:**
- Added "Import Records" tab to tab navigation
- Added import-tab div with instructions and button
- Included import modal component
- Added tab switching logic for the new import tab

### 5. **Route**
**File:** `routes/web.php`

**Added Route:**
```php
Route::post('/admin/leave/import', [LeaveController::class, 'importLeaveRecords'])
    ->middleware('auth')
    ->name('admin.leave.import');
```

---

## Excel File Format

The import feature expects Excel files in the following format:

### Expected Structure:
```
Row 1-5: Header Information (Employee name, position, etc.)
Row 6+:  Data rows with the following columns:

Column A: Month/Year (e.g., "January", "February", "2023", etc.)
Column B: Notes (Optional - VL1, FL1, T(0-2-10), etc.)
Column C: (Unused)
Column D: Vacation Leave Earned
Column E: (Unused)
Column F: Vacation Leave Used
Column G: (Unused)
Column H: Sick Leave Earned
Column I: (Unused)
Column J: Sick Leave Used
Column K: (Unused)
Column L: Total Balance
Column M: VL Balance
Column N: SL Balance
```

### Example Data Row:
```
January | T(0-2-10) | | 1.25 | | 0 | | 1.25 | | 0 | | 2.5 | 1.25 | 1.25
```

---

## How It Works - Step by Step

### User Flow:

1. **Admin navigates to Leave & Benefits > Import Records tab**
   - Clicks the "Import Leave Records" button

2. **Modal opens with two inputs:**
   - Employee selector dropdown
   - File upload field

3. **Admin selects employee and Excel file**
   - Employee list is dynamically populated from database
   - File size is limited to 5MB

4. **Admin clicks "Import Records"**
   - Frontend validates inputs
   - Sends AJAX POST request to `/admin/leave/import`

### Server-side Processing:

1. **Validate input:**
   - Employee ID must exist
   - File must be valid Excel format

2. **Parse Excel file:**
   - Load workbook using PhpOffice/PhpSpreadsheet
   - Extract data starting from row 6
   - Map columns to leave record data

3. **Import records:**
   - For each row, parse month/year
   - Create/update LeaveBalance records for VL and SL
   - Maintain transaction history

4. **Return result:**
   - JSON response with count of imported records
   - Any validation errors or warnings

5. **User sees success message:**
   - Redirects to Transaction History tab
   - Shows summary of imported records

---

## Database Tables Used

### LeaveBalance
- Stores leave credit balances per employee per year
- Updated during import with cumulative earned/used amounts

### LeaveTransaction
- Records all leave credit movements
- Tracks adjustments made during import process
- Provides audit trail

### LeaveType
- Must have VL and SL leave types configured
- Import checks for these types before processing

---

## Error Handling

The system handles various error scenarios:

1. **File parsing errors:**
   - Invalid Excel format
   - Missing columns
   - Corrupt file

2. **Data validation errors:**
   - Invalid month/year format
   - Missing leave types (VL/SL)
   - Invalid numeric values

3. **Database errors:**
   - Transaction rollback on failure
   - Detailed error messages to user

All errors are returned as JSON responses with appropriate HTTP status codes (422 for validation, 500 for server errors).

---

## Security Considerations

1. **Authentication:**
   - Only authenticated admin users can access the import feature
   - Middleware enforces authorization

2. **File Validation:**
   - File type restricted to .xlsx and .xls
   - File size limited to 5MB
   - Uploaded files stored temporarily and deleted after processing

3. **Data Validation:**
   - All input validated server-side
   - SQL injection prevention through Eloquent ORM
   - XSS protection through Laravel templating

4. **Audit Trail:**
   - All import transactions recorded in LeaveTransaction table
   - User ID stored for accountability
   - Transaction remarks indicate import source

---

## Database Transactions

Import operations use database transactions to ensure data consistency:

```php
DB::beginTransaction();
try {
    // Import all records
    // Create balance records
    // Create transaction records
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    // Return error to user
}
```

If any error occurs during import, all changes are rolled back and no partial data is saved.

---

## Installation & Requirements

### Dependencies:
- PHP 8.0+
- Laravel 10+
- PhpOffice/PhpSpreadsheet (already included in project)

### Setup:
1. Files are already created/modified
2. Route is added to web.php
3. No database migrations required (uses existing tables)

### Testing the Feature:
1. Navigate to Admin Dashboard > Leave & Benefits
2. Click the "Import Records" tab
3. Select an employee
4. Upload an Excel file matching the expected format
5. Click "Import Records"
6. Check the result in Transaction History tab

---

## Limitations & Future Enhancements

### Current Limitations:
- Only imports VL and SL leave types
- Month/year must be in recognized format
- Does not handle multiple payroll years in single file
- No duplicate detection (re-importing will update existing records)

### Potential Enhancements:
1. Support for additional leave types (study leave, training, etc.)
2. Dry-run mode to preview changes before importing
3. Bulk import for multiple employees at once
4. Template download for users
5. More detailed import reports
6. Duplicate record detection and conflict resolution

---

## Support & Troubleshooting

### Common Issues:

**Issue:** "File not found" error
- **Solution:** Ensure Excel file exists and is accessible

**Issue:** "Leave types VL or SL not found"
- **Solution:** Configure leave types in Leave Types tab first

**Issue:** "Invalid month/year format"
- **Solution:** Ensure Column A contains recognizable month names or years

**Issue:** Import succeeds but no records appear
- **Solution:** Check Transaction History tab - records are added there with adjustment type

---

## Code Examples

### Basic Import Usage:

```php
// From any controller
$parseResult = LeaveImportService::parseExcelFile($filePath);

if ($parseResult['success']) {
    $importResult = LeaveImportService::importLeaveRecords(
        $employeeId,
        $parseResult['records']
    );
}
```

### API Endpoint:

```javascript
// Frontend AJAX call
fetch('/admin/leave/import', {
    method: 'POST',
    body: formData,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
})
.then(response => response.json())
.then(data => {
    console.log(`Imported ${data.imported_count} records`);
});
```

---

## Summary

The Excel Import Feature provides a seamless way to migrate historical leave records from legacy systems into the Prime HR platform. It's designed to be user-friendly while maintaining data integrity and audit trails.

The implementation follows Laravel best practices with proper error handling, transaction management, and security measures.
