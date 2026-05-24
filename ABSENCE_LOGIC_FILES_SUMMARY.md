# Files Containing Absence/Abandoned Logic - Quick Reference

## Summary

Yes, your system **DOES have logic** that automatically marks employees as **ABSENT** or **ABANDONED** when they don't have AM Out and PM In.

## Key Files (In Order of Importance)

### 1. ⭐ **Main Backend Logic**
**File:** `app/Http/Controllers/AttendanceController.php`

**Lines 708-747:** Abandoned detection in detailed DTR view
```php
// Check if employee only timed in AM without returning
$isAbandoned = false;
if ($attendance && $attendance->am_in && !$attendance->am_out && !$attendance->pm_in) {
    $isAbandoned = true;
}
```

**Lines 1035-1055:** Abandoned detection in accredited hours computation
```php
// Treat as absent (0 accredited hours)
if ($amIn && !$amOut && !$pmIn) {
    return ['accredited_minutes' => 0, ...];
}
```

### 2. **Frontend Display - Permanent Employee View**
**File:** `resources/views/permanent/attendance/permanentAttendance.blade.php`

**Line 442:** Absence detection
```javascript
const isAbsent = !record.am_in && !record.pm_in && !isOnLeave;
```

**Lines 676-680:** Abandoned detection
```javascript
if ((hasAmIn && !hasAmOut && !hasPmIn) || (hasPmIn && !hasPmOut && !hasAmIn)) {
    status = 'Abandoned';
}
```

### 3. **Frontend Display - Detailed DTR Modal**
**File:** `resources/views/permanent/attendance/modals/detailedDtrModal.blade.php`

**Lines 291-296:** Abandoned status display
```javascript
if ((hasAmIn && !hasAmOut && !hasPmIn) || (hasPmIn && !hasPmOut && !hasAmIn)) {
    status = 'Abandoned';
    totalAbandoned++;
}
```

### 4. **Admin View - Detailed Time Record**
**File:** `resources/views/admin/attendance/partials/detailed-time-record-tab.blade.php`

**Lines 74-78:** Absence/incomplete detection
```php
@elseif(!$record['am_in'] && !$record['am_out'] && !$record['pm_in'] && !$record['pm_out'])
    $rowClass = 'day-absent';
@elseif(($record['am_in'] && !$record['am_out']) || ($record['pm_in'] && !$record['pm_out']))
    $rowClass = 'day-needs-review';
```

## Quick Logic Reference

### ABANDONED Status
**Condition:**
```
AM In: ✓ (has value)
AM Out: ✗ (NULL)
PM In: ✗ (NULL)
PM Out: ✗ (NULL)
```

**Result:**
- Status: "ABANDONED"
- Accredited Hours: 0
- Undertime: 480 minutes (full day)
- Leave Deduction: 1.0 day from VL/SL

### ABSENT Status
**Condition:**
```
AM In: ✗ (NULL)
AM Out: ✗ (NULL)
PM In: ✗ (NULL)
PM Out: ✗ (NULL)
On Leave: NO
```

**Result:**
- Status: "ABSENT"
- Accredited Hours: 0
- No automatic leave deduction

### INCOMPLETE Status
**Condition:**
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
- Status: "INCOMPLETE"
- Partial accredited hours
- Partial leave deduction

## File Locations (Full Paths)

```
c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel\
├── app\
│   └── Http\
│       └── Controllers\
│           └── AttendanceController.php ⭐ MAIN LOGIC
│
└── resources\
    └── views\
        ├── permanent\
        │   └── attendance\
        │       ├── permanentAttendance.blade.php
        │       └── modals\
        │           └── detailedDtrModal.blade.php
        │
        └── admin\
            └── attendance\
                └── partials\
                    └── detailed-time-record-tab.blade.php
```

## How to Find These Files

### Method 1: Direct Path
Navigate to:
```
c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel\app\Http\Controllers\AttendanceController.php
```

### Method 2: Search in VS Code
1. Press `Ctrl + Shift + F` (Find in Files)
2. Search for: `isAbandoned`
3. Or search for: `am_in && !am_out && !pm_in`

### Method 3: Using Command Line
```bash
cd c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel
grep -r "isAbandoned" app/
grep -r "am_in.*!am_out.*!pm_in" app/
```

## Testing the Logic

### Test Scenario 1: Create Abandoned Record
1. Go to Admin → Attendance
2. Create attendance with:
   - AM In: 08:00
   - AM Out: (leave empty)
   - PM In: (leave empty)
   - PM Out: (leave empty)
3. Check employee's DTR - should show "ABANDONED"
4. Check leave balance - should deduct 1.0 day

### Test Scenario 2: Correct Abandoned Record
1. Find the abandoned record
2. Click "Correct" button
3. Set AM Out: 12:00
4. Set PM In: 13:00
5. Set PM Out: 17:00
6. Submit correction
7. Check leave balance - should credit back the deduction

## Related Documentation

- **Full Documentation:** `ABSENCE_AND_ABANDONED_LOGIC_DOCUMENTATION.md`
- **Attendance Correction:** `ATTENDANCE_CORRECTION_LEAVE_RECALCULATION.md`
- **Implementation Summary:** `IMPLEMENTATION_SUMMARY.md`

## Quick Answer to Your Question

**Q: Does the system automatically mark as absent when employees don't have AM out and PM in?**

**A: YES!** The system has TWO types of automatic marking:

1. **ABANDONED** - When employee has AM In but NO AM Out and NO PM In
   - Treated as suspicious/abandoned
   - Automatically deducts 1.0 day from leave
   - Accredited hours = 0

2. **ABSENT** - When employee has NO time records at all
   - Marked as absent
   - No automatic leave deduction (different from abandoned)

The main logic is in **AttendanceController.php** at lines 708-747 and 1035-1055.

---

**Created:** May 22, 2026  
**Purpose:** Quick reference for absence/abandoned logic files  
**For:** Development team and system administrators
