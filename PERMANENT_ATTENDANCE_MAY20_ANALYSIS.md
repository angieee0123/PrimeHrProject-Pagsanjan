# Attendance Check for permanent@gmail.com - May 20, 2026

## Issue Found
**No attendance record exists** in the database for permanent@gmail.com on May 20, 2026.

## Employee Details
- **Email**: permanent@gmail.com
- **Name**: Juan Reyes Dela Cruz
- **Employee ID**: 2024002
- **Database ID**: 9

## Expected DTR Data (May 20, 2026 - Wednesday)
- **AM In**: 10:00
- **AM Out**: 12:07
- **PM In**: 13:00
- **PM Out**: 18:09
- **OT In**: —
- **OT Out**: —
- **Undertime**: 0 min
- **Late**: 2 hrs (120 minutes)
- **Total Hours**: 7.3 hrs (438 minutes actual work)
- **Accredited Hours**: 8 hrs (480 minutes)
- **Grace**: PM (arrived at 13:00, within grace period)
- **Source**: From Log

## Analysis
1. **Late Arrival**: Employee arrived at 10:00 AM instead of 08:00 AM (2 hours late)
2. **AM Session**: 10:00-12:07 = 127 minutes actual work
3. **PM Session**: 13:00-18:09 = 309 minutes actual work
4. **PM Grace Applied**: Arrived at 13:00 (on time with grace period)
5. **Overtime**: 18:09 - 17:00 = 69 minutes overtime
6. **Full Credit Given**: Despite being 2 hours late, 8 full hours were accredited

## Accredited Hours Breakdown
- **AM Accredited**: 240 minutes (4 hours) - Full credit despite late arrival
- **PM Accredited**: 240 minutes (4 hours) - 13:00-17:00 with grace applied
- **Total Accredited**: 480 minutes (8 hours)
- **Late Deduction**: 120 minutes (2 hours)
- **Overtime**: 69 minutes (not counted in accredited hours)

## Solution
A SQL script has been created: `fix_permanent_attendance_may20.sql`

This script will:
1. Insert the attendance record for May 20, 2026
2. Create a schedule for employee 9 (if not exists)
3. Insert the accredited hours log with proper calculations

## How to Apply
Run the SQL script in your MySQL database:
```bash
mysql -u your_username -p primehrismagdalena < fix_permanent_attendance_may20.sql
```

Or execute it through phpMyAdmin or your preferred MySQL client.

## Notes
- The system shows "From Log" which indicates this should have an accredited_hours_log entry
- The full 8 hours credit despite 2-hour late arrival suggests a special policy or manual adjustment
- PM grace was properly applied (arrived at 13:00, within 5-minute grace period)
- Overtime of 69 minutes (17:00-18:09) is recorded but not added to accredited hours
