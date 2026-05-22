# Attendance Correction Leave Recalculation Feature

## Overview

This feature automatically recalculates leave balance deductions when an admin edits attendance records (AM In, AM Out, PM In, PM Out). The system ensures that:

1. **Previous leave deductions are reversed** - Any leave credits that were deducted based on the old attendance times are credited back
2. **New deductions are applied** - Leave deductions are recalculated based on the corrected attendance times
3. **Complete audit trail** - All adjustments are recorded in the transaction history with clear remarks
4. **No data loss** - Previous transactions are never deleted or updated, only new reversal and adjustment transactions are created

## How It Works

### Scenario Example

**Original Attendance:**
- Employee clocks in at 8:30 AM (30 minutes late)
- System deducts 0.0625 days (30 minutes ÷ 480 minutes) from VL
- Leave balance: 15.000000 → 14.937500 days

**Admin Correction:**
- Admin corrects clock-in time to 8:00 AM (on time)
- System automatically:
  1. Credits back 0.0625 days to VL (reversal)
  2. Recalculates: No late time, no deduction needed
  3. Final balance: 14.937500 → 15.000000 days

### Transaction History Records

The system creates clear transaction records:

1. **Original Deduction** (remains unchanged):
   - Type: Debit
   - Amount: -0.0625 days
   - Remarks: "Late deduction: 30 minutes (0.062500 days) from attendance on 2026-05-22"

2. **Reversal Transaction** (new):
   - Type: Credit
   - Amount: +0.0625 days
   - Reference Type: `attendance_correction_reversal`
   - Remarks: "Reversal of previous late deduction due to attendance correction on 2026-05-22. Original deduction: 0.062500 days."

3. **New Deduction** (if applicable):
   - Type: Debit
   - Amount: Based on corrected times
   - Remarks: "Late deduction: X minutes (Y days) from attendance on 2026-05-22"

## Technical Implementation

### Key Components

1. **AttendanceCorrectionLeaveRecalculationService**
   - Location: `app/Services/AttendanceCorrectionLeaveRecalculationService.php`
   - Handles the complete recalculation logic
   - Methods:
     - `recalculateLeaveDeductions()` - Main recalculation method
     - `reversePreviousDeductions()` - Credits back previous deductions
     - `getSummaryMessage()` - Generates human-readable summary

2. **AttendanceController::correctAttendance()**
   - Location: `app/Http/Controllers/AttendanceController.php`
   - Updated to detect when attendance is being corrected
   - Calls the recalculation service when needed

3. **Leave Transaction Reference Types**
   - `manual_adjustment` - Original late/undertime deductions
   - `attendance_correction_reversal` - Reversal of previous deductions
   - `leave_application` - Leave application deductions
   - `accrual` - Monthly leave accruals

### Process Flow

```
1. Admin submits attendance correction
   ↓
2. System retrieves old AccreditedHoursLog
   ↓
3. System computes new attendance times
   ↓
4. System creates new AccreditedHoursLog
   ↓
5. AttendanceCorrectionLeaveRecalculationService.recalculateLeaveDeductions()
   ↓
6. Find all previous leave deduction transactions
   ↓
7. For each previous deduction:
   - Credit back the amount to leave balance
   - Create reversal transaction with clear remarks
   ↓
8. Reset flags on old log to prevent double processing
   ↓
9. Process new late/undertime deductions
   - LateDeductionService.processLateDeduction()
   - UndertimeDeductionService.processUndertimeDeduction()
   ↓
10. Return summary of changes
```

### Database Schema

**leave_transactions table:**
```sql
- id (primary key)
- employee_id (foreign key)
- leave_code (VL, SL, etc.)
- year
- transaction_type (credit, debit, pending, adjustment)
- amount (positive for credit, negative for debit)
- balance_before
- balance_after
- reference_type (manual_adjustment, attendance_correction_reversal, etc.)
- reference_id (links to accredited_hours_log.id)
- transaction_date
- processed_by (admin user id)
- remarks (detailed description)
- created_at
- updated_at
```

## User Experience

### For Employees

When viewing their transaction history, employees will see:

1. **Original deduction** - Shows the initial late/undertime deduction
2. **Reversal credit** - Shows the amount credited back with clear explanation
3. **New deduction** (if any) - Shows the corrected deduction amount

All transactions include:
- Clear icons indicating transaction type
- Detailed remarks explaining the reason
- Date of the attendance that was corrected
- Running balance (before/after)

### For Admins

When correcting attendance:

1. Admin opens the "Correct Attendance" modal
2. Admin edits the time fields (AM In, AM Out, PM In, PM Out)
3. Admin provides reason and attachments
4. Admin submits the correction
5. System automatically:
   - Recalculates accredited hours
   - Reverses old leave deductions
   - Applies new leave deductions
   - Updates daily salary computation
   - Logs all changes

## Benefits

1. **Accuracy** - Leave balances always reflect the correct attendance times
2. **Transparency** - Complete audit trail of all adjustments
3. **Fairness** - Employees are not penalized for admin corrections
4. **Compliance** - Follows CSC rules for leave credit management
5. **Automation** - No manual calculation needed by HR staff

## Edge Cases Handled

1. **Multiple corrections** - System handles repeated corrections correctly
2. **Partial coverage** - When leave credits don't fully cover late/undertime
3. **No previous deductions** - Works correctly for new attendance records
4. **Multiple leave types** - Handles VL + SL combinations
5. **LWOP scenarios** - Correctly manages Leave Without Pay situations

## Configuration

The system uses CSC standard conversion:
- 1 work day = 480 minutes (8 hours)
- Conversion handled by `CscTimeConversionService`

## Logging

All recalculations are logged for audit purposes:

```php
Log::info('Leave balance recalculation completed', [
    'attendance_id' => $attendance->id,
    'employee_id' => $employeeId,
    'date' => $date,
    'summary' => $summaryMessage,
]);
```

## Testing Scenarios

### Test Case 1: Reduce Late Time
- Original: 60 min late → 0.125 days deducted
- Corrected: 30 min late → 0.0625 days deducted
- Expected: +0.0625 days credited back

### Test Case 2: Increase Late Time
- Original: 30 min late → 0.0625 days deducted
- Corrected: 60 min late → 0.125 days deducted
- Expected: Additional 0.0625 days deducted

### Test Case 3: Remove Late Time
- Original: 60 min late → 0.125 days deducted
- Corrected: On time → No deduction
- Expected: Full 0.125 days credited back

### Test Case 4: Add Late Time
- Original: On time → No deduction
- Corrected: 60 min late → 0.125 days deducted
- Expected: 0.125 days deducted

### Test Case 5: Multiple Leave Types
- Original: 120 min late, VL covers 60 min, SL covers 60 min
- Corrected: 30 min late
- Expected: Both VL and SL credited back appropriately

## Future Enhancements

1. **Notification system** - Notify employees when their leave balance is adjusted
2. **Bulk corrections** - Handle multiple attendance corrections at once
3. **Approval workflow** - Require approval for corrections that significantly impact leave
4. **Reports** - Generate reports of all attendance corrections and their impact

## Related Files

- `app/Services/AttendanceCorrectionLeaveRecalculationService.php`
- `app/Services/LateDeductionService.php`
- `app/Services/UndertimeDeductionService.php`
- `app/Services/CscTimeConversionService.php`
- `app/Http/Controllers/AttendanceController.php`
- `app/Models/LeaveTransaction.php`
- `app/Models/AccreditedHoursLog.php`
- `app/Models/LeaveBalance.php`
- `resources/views/permanent/leaveandbenefits/tabs/transaction-history/transactionHistoryTab.blade.php`

## Support

For questions or issues, contact the development team or refer to the system documentation.
