# Late Deduction from Leave Balances Feature

## Overview
This feature automatically deducts late time from employee leave balances following this priority:
1. **Vacation Leave (VL)** - First priority
2. **Sick Leave (SL)** - Second priority  
3. **Recorded as Late** - If both VL and SL have zero balance

## How It Works

### Conversion Formula
- Late minutes are converted to days: `lateDays = lateMinutes / 480`
- 480 minutes = 8 hours (1 working day)

### Deduction Logic
1. **Check VL Balance**
   - If VL balance >= late days → Deduct from VL only
   - If VL balance > 0 but < late days → Deduct all VL, remaining from SL

2. **Check SL Balance** (if VL insufficient)
   - If SL balance >= remaining late days → Deduct from SL
   - If SL balance > 0 but < remaining → Deduct all SL, record rest as late

3. **Record as Late** (if both VL and SL are zero)
   - No deduction from leave balances
   - Late minutes remain in attendance record

## Database Changes

### Migration: `2026_05_14_000001_add_late_deduction_tracking_to_accredited_hours_log.php`

Adds tracking columns to `accredited_hours_log` table:
```sql
late_deducted_from_leave BOOLEAN DEFAULT FALSE
late_deduction_leave_type VARCHAR(10) NULLABLE
```

### Run Migration
```bash
php artisan migrate
```

## Implementation Files

### 1. Service: `LateDeductionService.php`
Location: `app/Services/LateDeductionService.php`

Main method: `processLateDeduction(AccreditedHoursLog $log)`

### 2. Controller Integration
Location: `app/Http/Controllers/AttendanceController.php`

The service is called after attendance correction:
```php
$lateDeductionService = new LateDeductionService();
$lateDeductionService->processLateDeduction($accreditedLog);
```

## Transaction Recording

All deductions are recorded in `leave_transactions` table with:
- `transaction_type`: 'debit'
- `reference_type`: 'manual_adjustment'
- `reference_id`: accredited_hours_log.id
- `remarks`: Details about late minutes and days deducted

## Example Scenarios

### Scenario 1: Sufficient VL Balance
- Employee late: 60 minutes (0.125 days)
- VL balance: 5.0 days
- **Result**: VL balance becomes 4.875 days

### Scenario 2: Partial VL, Use SL
- Employee late: 120 minutes (0.25 days)
- VL balance: 0.1 days
- SL balance: 3.0 days
- **Result**: 
  - VL balance becomes 0 days
  - SL balance becomes 2.85 days (3.0 - 0.15)

### Scenario 3: Zero Leave Balances
- Employee late: 30 minutes (0.0625 days)
- VL balance: 0 days
- SL balance: 0 days
- **Result**: Recorded as late, no leave deduction

## Viewing Deductions

### Leave Transactions Table
Query to view late deductions:
```sql
SELECT * FROM leave_transactions 
WHERE reference_type = 'manual_adjustment' 
AND remarks LIKE '%Late deduction%'
ORDER BY transaction_date DESC;
```

### Accredited Hours Log
Check if late was deducted:
```sql
SELECT 
    id,
    employee_id,
    late_minutes,
    late_deducted_from_leave,
    late_deduction_leave_type,
    created_at
FROM accredited_hours_log
WHERE late_deducted_from_leave = TRUE;
```

## Testing

### Test Case 1: Create Late Attendance
1. Go to Attendance Correction
2. Set AM In time 30 minutes late (e.g., 08:30 instead of 08:00)
3. Submit correction
4. Check `leave_transactions` table for deduction record
5. Verify `leave_balances` updated correctly

### Test Case 2: Insufficient Leave Balance
1. Manually set employee VL balance to 0.05 days
2. Create late attendance of 60 minutes (0.125 days)
3. Verify:
   - VL balance becomes 0
   - SL balance reduced by 0.075 days
   - Two transaction records created

## Notes

- Deductions only process once per attendance record
- If `late_deducted_from_leave` is TRUE, no re-processing occurs
- All operations are wrapped in database transactions for data integrity
- Requires authenticated user (uses `auth()->id()` for processed_by)

## Future Enhancements

1. Add configuration for late threshold (e.g., only deduct if late > 15 minutes)
2. Add admin interface to view/reverse late deductions
3. Add notification to employees when leave is deducted for lateness
4. Add monthly report of late deductions per employee
