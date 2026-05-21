# Detailed DTR Modal - Leave Deduction Display Update

## Overview
Updated the Detailed DTR Modal to display leave deduction information when employees are on approved leave (Vacation Leave or Sick Leave).

## Changes Made

### 1. Modal View Update (`detailedDtrModal.blade.php`)
**File:** `primeHrMagdalenaLaravel\resources\views\admin\attendance\modals\detailedDtrModal.blade.php`

**Change:** Added new column "Leave Deduction" to the table header
- Added column between "Accredited Hours" and "Action" columns
- This column will display the leave type code and details when an employee is on approved leave

```html
<th>Leave Deduction</th>
```

### 2. JavaScript Update (`adminAttendance.js`)
**File:** `primeHrMagdalenaLaravel\resources\js\adminAttendance.js`

**Changes:**

#### a) Added Leave Deduction Display Logic
In the `renderDetailedDTR()` function, added logic to display leave information:

```javascript
// Build leave deduction display
let leaveDeductionDisplay = '—';
if (record.is_on_leave && record.leave_info) {
    const leaveType = record.leave_info.leave_type || 'Leave';
    const leaveCode = record.leave_info.leave_code || 'N/A';
    const days = record.leave_info.days || 1;
    leaveDeductionDisplay = `<span style="color: #0b044d; font-weight: 600;">${leaveCode}</span><br><small style="color: #6b6a8a; font-size: 10px;">${leaveType} (${days} day${days > 1 ? 's' : ''})</small>`;
}
```

#### b) Updated Accredited Hours Display for Leave
Enhanced the accredited hours display to show "8 hrs" with "✓ On Leave" indicator when employee is on approved leave:

```javascript
} else if (record.is_on_leave) {
    // On approved leave - show full 8 hours
    accreditedDisplay = '<strong style="color:#15803d;">8 hrs</strong><br><small style="color: #15803d; font-size: 10px;">✓ On Leave</small>';
```

#### c) Added Leave Deduction Column to Table Row
Updated the table row HTML to include the new leave deduction column:

```javascript
<td>${leaveDeductionDisplay}</td>
```

## How It Works

### Backend Data (Already Implemented)
The `AttendanceController::detailedDTR()` method already provides:
- `is_on_leave`: Boolean flag indicating if employee is on approved leave
- `leave_info`: Object containing:
  - `leave_type`: Full name of leave type (e.g., "Vacation Leave", "Sick Leave")
  - `leave_code`: Short code (e.g., "VL", "SL")
  - `application_number`: Leave application reference
  - `days`: Number of days for this leave

### Frontend Display
When an employee has an approved leave for a specific date:

1. **Time Columns**: Display "ON LEAVE" instead of time entries
2. **Accredited Hours**: Shows "8 hrs" with green checkmark "✓ On Leave"
3. **Leave Deduction**: Shows:
   - Leave code in bold (e.g., "VL", "SL")
   - Leave type name and duration (e.g., "Vacation Leave (3 days)")

### Example Display
For an employee on 3-day Vacation Leave:

| Date | ... | Accredited Hours | Leave Deduction | Action |
|------|-----|------------------|-----------------|--------|
| May 13, 2026 | ... | **8 hrs**<br><small>✓ On Leave</small> | **VL**<br><small>Vacation Leave (3 days)</small> | Edit |

## Leave Types Supported
Based on the database structure, the system supports:
- **VL** - Vacation Leave
- **SL** - Sick Leave
- **AL** - Annual Leave
- **BL** - Birthday Leave
- **ML** - Maternity Leave
- **PL** - Paternity Leave
- And many more (20 leave types total)

## Database Integration
The system automatically:
1. Checks for approved leave applications in the date range
2. Deducts leave credits from employee's leave balance
3. Records transactions in `leave_transactions` table
4. Updates `leave_balances` table (used_credits, available_credits)

## Benefits
1. **Transparency**: Employees and admins can see exactly which leave type was used
2. **Audit Trail**: Clear display of leave deductions alongside attendance records
3. **Balance Tracking**: Easy to verify leave usage against available balance
4. **Compliance**: Meets government requirements for leave tracking

## Testing Recommendations
1. Test with employee who has approved Vacation Leave
2. Test with employee who has approved Sick Leave
3. Test with multiple consecutive leave days
4. Test with partial month leave (e.g., 2 days in middle of month)
5. Verify leave balance deduction in Leave & Benefits module

## Related Files
- Controller: `app/Http/Controllers/AttendanceController.php` (already handles leave data)
- Models: 
  - `app/Models/LeaveApplication.php`
  - `app/Models/LeaveBalance.php`
  - `app/Models/LeaveTransaction.php`
- Database Tables:
  - `leave_applications`
  - `leave_balances`
  - `leave_transactions`
  - `leave_types_config`

## Notes
- The backend logic for leave deduction was already implemented
- This update only adds the visual display of leave information in the DTR modal
- No database changes required
- No controller changes required
- Changes are purely frontend (view + JavaScript)
