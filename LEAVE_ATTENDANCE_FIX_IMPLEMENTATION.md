# IMPLEMENTATION GUIDE: Automatic Attendance Records for Approved Leaves

## Overview
This guide provides step-by-step instructions to fix the bug where approved leave applications don't create corresponding attendance records in the database.

## Files Created
1. `app/Observers/LeaveApplicationObserver.php` - Observer to handle leave approval
2. `DATABASE_RELATIONSHIP_BUGS_ANALYSIS.md` - Detailed bug analysis
3. This implementation guide

## Step-by-Step Implementation

### Step 1: Register the Observer

Edit: `app/Providers/AppServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\LeaveApplication;
use App\Observers\LeaveApplicationObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register LeaveApplication Observer
        LeaveApplication::observe(LeaveApplicationObserver::class);
    }

    public function register(): void
    {
        //
    }
}
```

### Step 2: Verify Employee Model Method

Ensure `app/Models/Employee.php` has the `getScheduleForDate()` method:

```php
public function getScheduleForDate($date)
{
    $dateCarbon = \Carbon\Carbon::parse($date);
    
    return $this->schedules()
        ->where(function($query) use ($dateCarbon) {
            $query->whereNull('start_date')
                  ->whereNull('end_date');
        })
        ->orWhere(function($query) use ($dateCarbon) {
            $query->where('start_date', '<=', $dateCarbon)
                  ->where('end_date', '>=', $dateCarbon);
        })
        ->first();
}
```

### Step 3: Update Attendance Model

Ensure `app/Models/Attendance.php` allows 'ON_LEAVE' as valid values:

```php
protected $fillable = [
    'employee_id',
    'date',
    'am_in',
    'am_out',
    'pm_in',
    'pm_out',
    'ot_in',
    'ot_out',
    'accredited_hours',
    'total_hours',
];

// No need to cast time fields if storing 'ON_LEAVE' string
// Remove or adjust casts if they exist
protected $casts = [
    'date' => 'date',
    // Don't cast am_in, am_out, pm_in, pm_out to time if using 'ON_LEAVE'
];
```

### Step 4: Modify Attendance Table Schema (if needed)

If your database has strict TIME column types, you may need to change them to VARCHAR:

```sql
-- Run this migration if needed
ALTER TABLE `attendance` 
MODIFY COLUMN `am_in` VARCHAR(20) NULL,
MODIFY COLUMN `am_out` VARCHAR(20) NULL,
MODIFY COLUMN `pm_in` VARCHAR(20) NULL,
MODIFY COLUMN `pm_out` VARCHAR(20) NULL,
MODIFY COLUMN `ot_in` VARCHAR(20) NULL,
MODIFY COLUMN `ot_out` VARCHAR(20) NULL;
```

**OR** use a different approach with a separate column:

```sql
-- Alternative: Add a status column
ALTER TABLE `attendance`
ADD COLUMN `status` ENUM('present', 'absent', 'on_leave', 'holiday') DEFAULT 'present' AFTER `date`;
```

### Step 5: Update AttendanceController

Modify `generateDetailedRecords()` method to handle database-backed leave records:

```php
// In AttendanceController.php, around line 450
if ($attendance && $attendance->am_in === 'ON_LEAVE') {
    // This is a database-backed leave record
    $records[] = [
        'date' => $current->format('M d, Y'),
        'day' => $current->format('l'),
        'am_in' => 'ON LEAVE',
        'am_out' => 'ON LEAVE',
        'pm_in' => 'ON LEAVE',
        'pm_out' => 'ON LEAVE',
        'ot_in' => null,
        'ot_out' => null,
        'late_minutes' => 0,
        'late_display' => '-',
        'undertime' => 0,
        'undertime_display' => '-',
        'total_hours' => '8.0 hrs',
        'accredited_minutes' => 480,
        'am_accredited_minutes' => 240,
        'pm_accredited_minutes' => 240,
        'am_grace_applied' => false,
        'pm_grace_applied' => false,
        'schedule' => [
            'am_in' => $expectedAmIn->format('H:i'),
            'am_out' => $expectedAmOut->format('H:i'),
            'pm_in' => $expectedPmIn->format('H:i'),
            'pm_out' => $expectedPmOut->format('H:i'),
        ],
        'has_log' => true,
        'needs_review' => false,
        'is_incomplete' => false,
        'attendance_id' => $attendance->id,
        'date_key' => $current->format('Y-m-d'),
        'is_on_leave' => true,
        'leave_info' => null, // Can be populated from attendance notes
    ];
    $current->addDay();
    continue;
}
```

### Step 6: Test the Implementation

#### Test Case 1: New Leave Application
```bash
# 1. File a leave application for future dates
# 2. Approve the leave
# 3. Check database:

SELECT * FROM attendance 
WHERE employee_id = {employee_id} 
AND date BETWEEN '{start_date}' AND '{end_date}';

# Expected: Records with am_in='ON_LEAVE', accredited_hours=480

SELECT * FROM accredited_hours_log 
WHERE employee_id = {employee_id}
AND attendance_id IN (
    SELECT id FROM attendance 
    WHERE employee_id = {employee_id} 
    AND date BETWEEN '{start_date}' AND '{end_date}'
);

# Expected: Records with total_accredited_minutes=480

SELECT * FROM daily_salary_computations
WHERE employee_id = {employee_id}
AND work_date BETWEEN '{start_date}' AND '{end_date}';

# Expected: Records with full daily_basic_pay, no deductions
```

#### Test Case 2: Existing Approved Leave (Backfill)
For the existing approved leave (LA-2026-0001, May 15-19, 2026):

```sql
-- Run this script to backfill existing approved leaves
-- File: backfill_leave_attendance.php

<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LeaveApplication;
use App\Models\Attendance;
use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$approvedLeaves = LeaveApplication::where('status', 'approved')->get();

foreach ($approvedLeaves as $leave) {
    echo "Processing leave: {$leave->application_number}\n";
    
    $employee = $leave->employee;
    $startDate = Carbon::parse($leave->start_date);
    $endDate = Carbon::parse($leave->end_date);
    $current = $startDate->copy();

    while ($current->lte($endDate)) {
        if (!in_array($current->dayOfWeek, [0, 6])) {
            $dateKey = $current->format('Y-m-d');
            
            $existing = Attendance::where('employee_id', $employee->id)
                ->where('date', $dateKey)
                ->first();

            if (!$existing) {
                echo "  Creating attendance for {$dateKey}\n";
                
                $schedule = $employee->getScheduleForDate($dateKey);
                
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $dateKey,
                    'am_in' => 'ON_LEAVE',
                    'am_out' => 'ON_LEAVE',
                    'pm_in' => 'ON_LEAVE',
                    'pm_out' => 'ON_LEAVE',
                    'accredited_hours' => 480,
                    'total_hours' => 480,
                ]);

                $accreditedLog = AccreditedHoursLog::create([
                    'attendance_id' => $attendance->id,
                    'employee_id' => $employee->id,
                    'schedule_id' => $schedule ? $schedule->id : null,
                    'am_accredited_minutes' => 240,
                    'pm_accredited_minutes' => 240,
                    'ot_minutes' => 0,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'total_accredited_minutes' => 480,
                    'total_actual_minutes' => 480,
                    'am_grace_applied' => false,
                    'pm_grace_applied' => false,
                    'computation_notes' => "Backfilled: On approved leave - {$leave->application_number}",
                ]);

                DailySalaryComputation::computeFromAccreditedLog($accreditedLog);
            }
        }
        $current->addDay();
    }
}

echo "Backfill complete!\n";
```

### Step 7: Clear Cache and Restart

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## Verification Checklist

- [ ] Observer registered in AppServiceProvider
- [ ] LeaveApplicationObserver file created
- [ ] Employee model has getScheduleForDate() method
- [ ] Attendance model allows 'ON_LEAVE' values
- [ ] Database schema supports VARCHAR or has status column
- [ ] AttendanceController handles 'ON_LEAVE' records
- [ ] Test: File new leave → Approve → Check database
- [ ] Test: Backfill existing approved leaves
- [ ] Test: DTR export shows correct data
- [ ] Test: Salary computation includes leave days
- [ ] Test: Delete approved leave → Attendance records removed

## Rollback Plan

If issues occur:

1. **Disable Observer:**
   ```php
   // In AppServiceProvider.php, comment out:
   // LeaveApplication::observe(LeaveApplicationObserver::class);
   ```

2. **Remove Created Records:**
   ```sql
   DELETE FROM attendance 
   WHERE am_in = 'ON_LEAVE' 
   AND am_out = 'ON_LEAVE';
   ```

3. **Revert Schema Changes:**
   ```sql
   ALTER TABLE `attendance` 
   MODIFY COLUMN `am_in` TIME NULL,
   MODIFY COLUMN `am_out` TIME NULL,
   MODIFY COLUMN `pm_in` TIME NULL,
   MODIFY COLUMN `pm_out` TIME NULL;
   ```

## Performance Considerations

- Observer runs synchronously during leave approval
- For large date ranges (e.g., 30-day leave), may take a few seconds
- Consider adding a job queue for very long leaves:

```php
// In LeaveApplicationObserver.php
use App\Jobs\CreateLeaveAttendanceRecords;

private function createAttendanceRecordsForLeave(LeaveApplication $leaveApplication)
{
    // Dispatch job instead of processing immediately
    CreateLeaveAttendanceRecords::dispatch($leaveApplication);
}
```

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check database integrity
3. Verify all relationships are properly defined
4. Test with a single-day leave first

## Success Criteria

✅ Approved leave creates attendance records
✅ Attendance records have accredited_hours_log entries
✅ Accredited logs have daily_salary_computations
✅ DTR shows "ON LEAVE" with database backing
✅ Payroll includes leave days
✅ Data integrity maintained across all tables
