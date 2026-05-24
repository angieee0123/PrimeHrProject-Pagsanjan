# Absence and Abandoned Attendance Logic Documentation

## Overview

The system has logic to automatically mark employees as **ABSENT** or **ABANDONED** when they don't have complete attendance records, specifically when they're missing AM Out and PM In.

## Key Files Containing This Logic

### 1. **AttendanceController.php**
**Location:** `app/Http/Controllers/AttendanceController.php`

This is the main file containing the absence/abandoned detection logic.

#### Lines 708-747: Abandoned Detection in Detailed DTR

```php
// Check if employee only timed in AM without returning (no AM out and no PM in)
// This means they left and never came back - mark as ABSENT
$isAbandoned = false;
if ($attendance && $attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !in_array($current->dayOfWeek, [0, 6])) {
    $isAbandoned = true;
}

// Check if truly absent (no time records at all)
$isTrulyAbsent = !$attendance || (!$attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !$attendance->pm_out);

// Determine if incomplete vs absent
// INCOMPLETE: Has substantial attendance but missing some entries
// ABSENT: No attendance, abandoned, or only single time-in without pair
$isIncomplete = false;
$isAbsent = false;

if ($attendance && !in_array($current->dayOfWeek, [0, 6])) {
    $hasAmPair = $attendance->am_in && $attendance->am_out;
    $hasPmPair = $attendance->pm_in && $attendance->pm_out;
    $hasOnlyAmIn = $attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !$attendance->pm_out;
    $hasOnlyPmIn = !$attendance->am_in && !$attendance->am_out && $attendance->pm_in && !$attendance->pm_out;
    
    // ABSENT cases:
    // 1. Abandoned (AM in only, no AM out, no PM in)
    // 2. Only single time-in without any out (suspicious)
    if ($isAbandoned || $hasOnlyAmIn || $hasOnlyPmIn) {
        $isAbsent = true;
    }
    // INCOMPLETE cases:
    // 1. Has AM pair but incomplete PM
    // 2. Has PM pair but incomplete AM  
    // 3. Has AM in, AM out, PM in but no PM out (worked but forgot to clock out)
    else if (($hasAmPair && !$hasPmPair) || (!$hasAmPair && $hasPmPair) || 
             ($attendance->am_in && $attendance->am_out && $attendance->pm_in && !$attendance->pm_out)) {
        $isIncomplete = true;
    }
}

// If abandoned or only single time-in, treat as ABSENT
if ($isAbandoned || $isAbsent) {
    $statusLabel = $isAbandoned ? 'ABANDONED' : 'ABSENT';
    // ... mark as absent
}
```

#### Lines 1035-1055: Abandoned Detection in Accredited Hours Computation

```php
// Check if employee abandoned (only AM in, no AM out, no PM in)
// This means they left and never came back - treat as absent (0 accredited hours)
if ($amIn && !$amOut && !$pmIn) {
    return [
        'accredited_minutes' => 0,
        'log_data' => [
            'schedule_id' => $schedule ? $schedule->id : null,
            'am_accredited_minutes' => 0,
            'pm_accredited_minutes' => 0,
            'ot_minutes' => 0,
            'late_minutes' => 0,
            'undertime_minutes' => 480, // 8 hours absent
            'total_accredited_minutes' => 0,
            'total_actual_minutes' => 0,
            'am_grace_applied' => false,
            'pm_grace_applied' => false,
        ]
    ];
}
```

### 2. **permanentAttendance.blade.php**
**Location:** `resources/views/permanent/attendance/permanentAttendance.blade.php`

#### Lines 442-443: Frontend Absence Detection

```javascript
const isOnLeave = record.is_on_leave;
const isAbsent = !record.am_in && !record.pm_in && !isOnLeave;
```

#### Lines 676-680: Frontend Abandoned Detection

```javascript
} else if ((hasAmIn && !hasAmOut && !hasPmIn) || (hasPmIn && !hasPmOut && !hasAmIn)) {
    // Clocked in but never clocked out (single period only) = Abandoned
    status = 'Abandoned';
    statusColor = '#d97706';
    statusBg = '#d9770618';
}
```

### 3. **detailedDtrModal.blade.php**
**Location:** `resources/views/permanent/attendance/modals/detailedDtrModal.blade.php`

#### Lines 291-296: Modal Abandoned Detection

```javascript
} else if ((hasAmIn && !hasAmOut && !hasPmIn) || (hasPmIn && !hasPmOut && !hasAmIn)) {
    // Clocked in but never clocked out (single period only) = Abandoned
    status = 'Abandoned';
    statusColor = '#d97706';
    statusBg = '#d9770618';
    totalAbandoned++;
    leaveDeductionCell = record.leave_deduction;
}
```

### 4. **detailed-time-record-tab.blade.php**
**Location:** `resources/views/admin/attendance/partials/detailed-time-record-tab.blade.php`

#### Lines 74-78: Admin View Absence/Incomplete Detection

```php
@elseif(!$record['am_in'] && !$record['am_out'] && !$record['pm_in'] && !$record['pm_out'] && !$record['is_on_leave'])
    $rowClass = 'day-absent';
@elseif(($record['am_in'] && !$record['am_out']) || ($record['pm_in'] && !$record['pm_out']) || (!$record['am_in'] && $record['am_out']) || (!$record['pm_in'] && $record['pm_out']))
    $rowClass = 'day-needs-review';
@endif
```

## Logic Summary

### Attendance Status Categories

The system categorizes attendance into several statuses:

1. **PRESENT** - Complete attendance with all required time records
2. **INCOMPLETE** - Has some attendance but missing certain records
3. **ABSENT** - No attendance records at all
4. **ABANDONED** - Clocked in but never clocked out (suspicious)

### Specific Rules

#### 1. ABANDONED Status
**Condition:** Employee has AM In but NO AM Out and NO PM In
```
AM In: ✓
AM Out: ✗
PM In: ✗
PM Out: ✗
```
**Result:** 
- Marked as "ABANDONED"
- Accredited hours: 0 minutes
- Undertime: 480 minutes (full day)
- Status color: Orange (#d97706)

**Rationale:** Employee clocked in but left and never returned. This is suspicious behavior.

#### 2. ABSENT Status
**Condition:** No time records at all
```
AM In: ✗
AM Out: ✗
PM In: ✗
PM Out: ✗
```
**Result:**
- Marked as "ABSENT"
- No accredited hours
- Status color: Red (#8e1e18)

#### 3. INCOMPLETE Status
**Conditions:**
- Has AM pair (AM In + AM Out) but incomplete PM
- Has PM pair (PM In + PM Out) but incomplete AM
- Has AM In, AM Out, PM In but no PM Out (worked but forgot to clock out)

```
Example 1:
AM In: ✓
AM Out: ✓
PM In: ✗
PM Out: ✗

Example 2:
AM In: ✓
AM Out: ✓
PM In: ✓
PM Out: ✗
```

**Result:**
- Marked as "INCOMPLETE"
- Partial accredited hours calculated
- Status color: Yellow (#d9bb00)
- Needs review/correction

## Impact on Leave Deductions

### Abandoned Attendance
When an employee is marked as ABANDONED:
1. **Accredited hours = 0**
2. **Undertime = 480 minutes** (full 8-hour day)
3. **Leave deduction:** System will attempt to deduct from VL/SL
4. **If insufficient leave:** Marked as LWOP (Leave Without Pay)

### Example Scenario

**Employee clocks in at 8:00 AM but never clocks out:**
```
Date: May 22, 2026
AM In: 08:00
AM Out: NULL
PM In: NULL
PM Out: NULL
```

**System Action:**
1. Detects: `am_in && !am_out && !pm_in` = ABANDONED
2. Sets accredited_hours = 0
3. Sets undertime_minutes = 480
4. Attempts to deduct 1.0 day (480 min ÷ 480) from VL
5. If VL insufficient, tries SL
6. If both insufficient, marks as LWOP
7. Creates leave transaction record

## Weekend Handling

The system excludes weekends (Saturday = 0, Sunday = 6) from abandoned/absent checks:

```php
if ($attendance && $attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !in_array($current->dayOfWeek, [0, 6])) {
    $isAbandoned = true;
}
```

This prevents false positives on weekends when employees might not be required to work.

## Leave Status Handling

The system also checks if the employee is on approved leave:

```php
const isOnLeave = record.is_on_leave;
const isAbsent = !record.am_in && !record.pm_in && !isOnLeave;
```

If an employee is on approved leave, they are NOT marked as absent even if they have no time records.

## Visual Indicators

### Status Colors
- **ABANDONED:** Orange (#d97706) with light orange background (#d9770618)
- **ABSENT:** Red (#8e1e18) with light red background (#8e1e1818)
- **INCOMPLETE:** Yellow (#d9bb00) with light yellow background
- **PRESENT:** Green (#15803d)

### Status Badges
- **ABANDONED:** Orange badge with "ABANDONED" text
- **ABSENT:** Red badge with "ABSENT" text
- **INCOMPLETE:** Yellow badge with "Incomplete" text

## Related Features

### 1. Attendance Correction
When admin corrects an abandoned/absent record:
- System recalculates accredited hours
- Reverses previous leave deductions (if any)
- Applies new deductions based on corrected times
- Creates reversal transactions in leave history

### 2. Leave Transaction History
All leave deductions from abandoned/absent days are recorded:
- Transaction type: "debit"
- Reference type: "manual_adjustment"
- Remarks: "Late deduction: X minutes" or "Undertime deduction: X minutes"
- Amount: Calculated in days (minutes ÷ 480)

## Testing Scenarios

### Test Case 1: Abandoned (AM In Only)
```
Input:
- AM In: 08:00
- AM Out: NULL
- PM In: NULL
- PM Out: NULL

Expected:
- Status: ABANDONED
- Accredited: 0 minutes
- Undertime: 480 minutes
- Leave Deduction: 1.0 day
```

### Test Case 2: Incomplete (Missing PM Out)
```
Input:
- AM In: 08:00
- AM Out: 12:00
- PM In: 13:00
- PM Out: NULL

Expected:
- Status: INCOMPLETE
- Accredited: 240 minutes (AM only)
- Undertime: 240 minutes (PM missing)
- Leave Deduction: 0.5 day
```

### Test Case 3: Absent (No Records)
```
Input:
- AM In: NULL
- AM Out: NULL
- PM In: NULL
- PM Out: NULL
- On Leave: NO

Expected:
- Status: ABSENT
- Accredited: 0 minutes
- No leave deduction (different from abandoned)
```

## Configuration

### CSC Time Standards
- 1 work day = 480 minutes (8 hours)
- AM Period: 08:00 - 12:00 (240 minutes)
- PM Period: 13:00 - 17:00 (240 minutes)
- Grace Period: 5 minutes

### Leave Deduction Priority
1. VL (Vacation Leave) - deducted first
2. SL (Sick Leave) - deducted if VL insufficient
3. LWOP - applied if both insufficient

## Recommendations

### For Employees
1. Always clock out when leaving
2. If you forget to clock out, request attendance correction immediately
3. Check your DTR regularly for abandoned/incomplete records

### For Admins
1. Review abandoned records daily
2. Investigate patterns of abandoned attendance
3. Correct legitimate abandoned records promptly
4. Monitor leave deductions from abandoned days

### For Developers
1. Consider adding notifications for abandoned attendance
2. Implement automatic reminders for incomplete records
3. Add bulk correction feature for common scenarios
4. Create reports for abandoned attendance patterns

## Future Enhancements

1. **Auto-correction:** Automatically set AM Out to 12:00 if only AM In exists after certain time
2. **Notifications:** Alert employees when they have abandoned/incomplete records
3. **Approval workflow:** Require supervisor approval for abandoned day corrections
4. **Analytics:** Dashboard showing abandoned attendance trends
5. **Mobile alerts:** Push notifications when employee forgets to clock out

## Support

For questions or issues related to absence/abandoned logic:
- Review this documentation
- Check AttendanceController.php for backend logic
- Check blade files for frontend display logic
- Contact development team for technical support

---

**Last Updated:** May 22, 2026  
**Version:** 1.0  
**Maintained By:** Development Team
