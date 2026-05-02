# Schedule Update with Automatic Attendance Recalculation

## Overview
When admins update employee work schedules in the Personnel module, the system now automatically recalculates all affected attendance records to reflect the new schedule times.

## What Gets Recalculated

When a schedule is created or updated, the system recalculates:
- **Accredited Hours** - Based on new schedule times with 15-min grace period
- **Late Minutes** - Recalculated against new AM In time
- **Undertime Minutes** - Recalculated against new PM Out time
- **Grace Period Application** - Re-evaluated for AM and PM shifts
- **Accredited Hours Log** - Updated with new computation details

## How It Works

### 1. Schedule Assignment/Update
Location: `routes/web.php` - `admin.schedules.assign` route

When admin assigns or updates a schedule:
```php
POST /admin/schedules/assign
{
    "employee_id": 8,
    "start_date": "2026-05-01",
    "end_date": "2026-05-31",
    "am_in": "08:00",
    "am_out": "12:00",
    "pm_in": "13:00",
    "pm_out": "17:00"
}
```

### 2. Automatic Recalculation
After schedule is saved, system automatically:
1. Finds all attendance records within the schedule date range
2. Recalculates accredited hours using new schedule
3. Updates `attendance.accredited_hours` field
4. Updates or creates `accredited_hours_log` entries
5. Adds computation note: "Recalculated due to schedule update at [timestamp]"

### 3. Success Message
Admin sees confirmation:
```
"Schedule updated successfully. Recalculated 15 attendance record(s)."
```

## Implementation Details

### New Method: `recalculateAttendanceForSchedule()`
Location: `app/Http/Controllers/AttendanceController.php`

```php
public function recalculateAttendanceForSchedule($employeeId, $startDate, $endDate)
{
    // 1. Get all attendance records in date range
    $attendances = Attendance::where('employee_id', $employeeId)
        ->whereBetween('date', [$startDate, $endDate])
        ->get();

    // 2. For each attendance record:
    foreach ($attendances as $attendance) {
        // Skip if no time records
        if (!$attendance->am_in && !$attendance->pm_in) continue;

        // 3. Recompute accredited hours with new schedule
        $computationResult = $this->computeAccreditedHours(...);

        // 4. Update attendance record
        $attendance->update(['accredited_hours' => $computationResult['accredited_minutes']]);

        // 5. Update log with new computation
        AccreditedHoursLog::updateOrCreate(
            ['attendance_id' => $attendance->id],
            [...$computationResult['log_data']]
        );
    }

    return $recalculatedCount;
}
```

### Integration Points

**Schedule Create/Update:**
```php
// After creating/updating schedule
$attendanceController = app(\App\Http\Controllers\AttendanceController::class);
$recalculatedCount = $attendanceController->recalculateAttendanceForSchedule(
    $employeeId,
    $startDate,
    $endDate
);
```

## Example Scenario

### Before Schedule Update
- **Schedule**: AM 07:00-11:00, PM 12:00-16:00
- **Attendance**: AM In 08:00, AM Out 11:00, PM In 12:00, PM Out 16:00
- **Accredited**: 7 hours (late 1 hour in AM)

### After Schedule Update
- **New Schedule**: AM 08:00-12:00, PM 13:00-17:00
- **Same Attendance**: AM In 08:00, AM Out 11:00, PM In 12:00, PM Out 16:00
- **Recalculated Accredited**: 6 hours (now early out in AM, early out in PM)

## Benefits

1. **Accuracy** - Attendance records always reflect current schedule
2. **Automation** - No manual recalculation needed
3. **Audit Trail** - Log shows when recalculation occurred
4. **Transparency** - Admin sees how many records were affected
5. **Data Integrity** - Ensures consistency between schedules and attendance

## Testing

### Test Case 1: Update Existing Schedule
```bash
# Update schedule for employee 8 (May 2026)
# Change AM: 05:00-09:00 → 08:00-12:00
# Change PM: 11:00-15:00 → 13:00-17:00

Result: 2 attendance records recalculated
- May 01: 183 min → 480 min (8h 0m)
- May 02: Updated with new schedule
```

### Test Case 2: Create New Schedule
```bash
# Assign new schedule for June 2026
# If attendance records exist for June, they will be recalculated
```

## Notes

- Only attendance records with time entries are recalculated
- Absent days (no time records) are skipped
- Recalculation uses the same logic as manual attendance correction
- Log entries are updated (not duplicated) using `updateOrCreate`
- Computation notes indicate "Recalculated due to schedule update"

## Future Enhancements

Potential improvements:
1. Bulk schedule updates with batch recalculation
2. Preview of affected records before schedule update
3. Option to skip recalculation if needed
4. Notification to employees when their schedule changes
5. Report showing before/after comparison of accredited hours
