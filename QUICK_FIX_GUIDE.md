# QUICK FIX GUIDE: Leave Attendance Bug

## The Bug
Approved leaves don't create attendance records in database → breaks payroll & reporting

## The Fix
Auto-create attendance records when leave is approved

## Files to Use
1. `app/Observers/LeaveApplicationObserver.php` ← Copy this
2. `backfill_leave_attendance.php` ← Run this once
3. Implementation guides ← Read these

## 3-Step Implementation

### Step 1: Register Observer (2 minutes)
Edit `app/Providers/AppServiceProvider.php`:

```php
use App\Models\LeaveApplication;
use App\Observers\LeaveApplicationObserver;

public function boot(): void
{
    LeaveApplication::observe(LeaveApplicationObserver::class);
}
```

### Step 2: Fix Database Schema (5 minutes)
Run this SQL if your attendance table uses TIME columns:

```sql
ALTER TABLE `attendance` 
MODIFY COLUMN `am_in` VARCHAR(20) NULL,
MODIFY COLUMN `am_out` VARCHAR(20) NULL,
MODIFY COLUMN `pm_in` VARCHAR(20) NULL,
MODIFY COLUMN `pm_out` VARCHAR(20) NULL;
```

### Step 3: Backfill Existing Leaves (5 minutes)
```bash
cd primeHrMagdalenaLaravel
php backfill_leave_attendance.php
```

## Test It
1. File a leave for tomorrow
2. Approve it
3. Check database:
```sql
SELECT * FROM attendance WHERE employee_id = X AND date = 'YYYY-MM-DD';
-- Should show: am_in='ON_LEAVE', accredited_hours=480
```

## Verify Success
✅ Attendance record exists
✅ Accredited hours log exists
✅ Daily salary computation exists
✅ DTR shows "ON LEAVE"
✅ Payroll includes leave day

## Rollback (if needed)
Comment out in `AppServiceProvider.php`:
```php
// LeaveApplication::observe(LeaveApplicationObserver::class);
```

## That's It!
Future leave approvals will automatically create attendance records.

---
**Time Required:** 15 minutes
**Risk:** Low
**Impact:** High (fixes payroll & data integrity)
