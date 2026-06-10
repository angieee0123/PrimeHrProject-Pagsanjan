# Leave Import Feature - Quick Reference Guide

## Quick Start

### How to Use:
1. Go to **Admin Dashboard → Leave & Benefits → Import Records tab**
2. Click **"Import Leave Records"** button
3. Select employee from dropdown
4. Choose Excel file (.xlsx or .xls)
5. Click **"Import Records"** and wait for confirmation

### Expected Excel Format:

```
Header rows 1-5: Employee information
Row 6 onwards: Leave records

Column layout:
A: Month/Year
B: Notes
C: (skip)
D: VL Earned
E: (skip)
F: VL Used
G: (skip)
H: SL Earned
I: (skip)
J: SL Used
K: (skip)
L: Total Balance
M: VL Balance
N: SL Balance
```

---

## Files Created

| File | Purpose |
|------|---------|
| `app/Services/LeaveImportService.php` | Core import logic |
| `app/Http/Controllers/LeaveController.php` | Added `importLeaveRecords()` method |
| `resources/views/admin/leaveAndBenefits/modals/import-leave-records-modal.blade.php` | Import modal UI |
| `resources/views/admin/leaveAndBenefits/adminLeaveAndBenefits.blade.php` | Updated main view |
| `routes/web.php` | Added `/admin/leave/import` route |

---

## Key Features

✅ **Employee Selection** - Dropdown with all employees  
✅ **File Upload** - Supports .xlsx and .xls (max 5MB)  
✅ **Data Parsing** - Automatic Excel parsing and validation  
✅ **Database Integration** - Creates LeaveBalance and LeaveTransaction records  
✅ **Error Handling** - Detailed error messages for troubleshooting  
✅ **Audit Trail** - All imports recorded in transaction history  
✅ **Transaction Rollback** - Cancels entire import if any error occurs  

---

## API Endpoint

**URL:** `POST /admin/leave/import`

**Request:**
```json
{
  "employee_id": 1,
  "excel_file": <file>
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Successfully imported X leave records",
  "imported_count": 24,
  "errors": []
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Import failed: [error details]"
}
```

---

## Database Changes

**No migrations required** - Uses existing tables:
- `leave_balances` - Stores leave credits per employee/year
- `leave_transactions` - Records all adjustments
- `leave_types_config` - VL and SL types must exist

---

## Testing Checklist

- [ ] Admin can access Import Records tab
- [ ] Employee dropdown loads correctly
- [ ] File upload accepts .xlsx and .xls
- [ ] Invalid files show error message
- [ ] Import with sample data succeeds
- [ ] Records appear in Transaction History
- [ ] Leave balances update correctly
- [ ] User gets success notification
- [ ] Audit trail shows import transactions

---

## Example Excel File Structure

```
Name:       John Doe
Position:   Municipal Health Officer
Status:     Permanent

Month      | Notes | | VL Earned | | VL Used | | SL Earned | | SL Used | | Balance | VL Bal | SL Bal
-----------|-------|--|-----------|--|---------|--|-----------|--|---------|---------|--------|-------
January    |       | | 1.25      | | 0       | | 1.25      | | 0       | | 2.5     | 1.25   | 1.25
February   |       | | 1.25      | | 0       | | 1.25      | | 0       | | 5.0     | 2.5    | 2.5
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| "Leave types not found" | Configure VL and SL in Leave Types tab first |
| "Invalid month/year format" | Use standard month names (January, February) or year |
| "File upload failed" | Check file is < 5MB and is valid Excel |
| No records imported | Check Excel column layout matches expected format |
| Wrong employee selected | Verify employee from dropdown before import |

---

## Support

For issues or questions:
1. Check the detailed documentation: `LEAVE_IMPORT_FEATURE_DOCUMENTATION.md`
2. Review error messages in the modal
3. Check Transaction History for imported records
4. Verify Excel file format matches specification

---

## Version Info
- **Feature:** Leave Records Import
- **Version:** 1.0
- **Status:** Production Ready
- **Created:** 2024
