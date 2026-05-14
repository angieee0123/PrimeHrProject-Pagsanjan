# CSC Time Conversion - Quick Reference Card

## 🎯 CSC Standards (Memorize These!)

```
1 work day    = 8 hours     = 480 minutes
0.5 day       = 4 hours     = 240 minutes
1 hour        = 0.125 days  = 60 minutes
1 minute      = 0.002083 days

Working days  = Exclude weekends (Sat & Sun) + holidays
```

---

## 🔧 Common Conversions

### Days → Hours → Minutes

```php
use App\Services\CscTimeConversionService as CSC;

// Days to Hours
CSC::convertDaysToHours(1);      // 8.0
CSC::convertDaysToHours(0.5);    // 4.0
CSC::convertDaysToHours(2.5);    // 20.0

// Days to Minutes
CSC::convertDaysToMinutes(1);    // 480
CSC::convertDaysToMinutes(0.5);  // 240
CSC::convertDaysToMinutes(2);    // 960

// Hours to Minutes
CSC::convertHoursToMinutes(8);   // 480
CSC::convertHoursToMinutes(1);   // 60
CSC::convertHoursToMinutes(0.5); // 30
```

### Minutes → Hours → Days

```php
// Minutes to Hours
CSC::convertMinutesToHours(480); // 8.0
CSC::convertMinutesToHours(60);  // 1.0
CSC::convertMinutesToHours(30);  // 0.5

// Minutes to Days
CSC::convertMinutesToDays(480);  // 1.0
CSC::convertMinutesToDays(240);  // 0.5
CSC::convertMinutesToDays(60);   // 0.125

// Hours to Days
CSC::convertHoursToDays(8);      // 1.0
CSC::convertHoursToDays(4);      // 0.5
CSC::convertHoursToDays(1);      // 0.125
```

---

## 📅 Working Days

### Calculate Working Days

```php
// Basic (excludes weekends)
$days = CSC::calculateWorkingDays('2026-01-05', '2026-01-09');
// Returns: 5 (Mon-Fri)

// With holidays
$holidays = ['2026-01-07']; // Wednesday
$days = CSC::calculateWorkingDays('2026-01-05', '2026-01-09', $holidays);
// Returns: 4 (Mon, Tue, Thu, Fri)

// Get working hours
$hours = CSC::calculateWorkingHours('2026-01-05', '2026-01-09');
// Returns: 40.0 (5 days × 8 hours)

// Get working minutes
$minutes = CSC::calculateWorkingMinutes('2026-01-05', '2026-01-09');
// Returns: 2400 (5 days × 480 minutes)
```

### Check Working Day

```php
// Is weekend?
CSC::isWeekend('2026-01-10');    // true (Saturday)
CSC::isWeekend('2026-01-05');    // false (Monday)

// Is working day?
CSC::isWorkingDay('2026-01-05'); // true (Monday)
CSC::isWorkingDay('2026-01-10'); // false (Saturday)

// With holidays
$holidays = ['2026-01-07'];
CSC::isWorkingDay('2026-01-07', $holidays); // false (Holiday)
```

### Get Working Dates

```php
// Get array of working dates
$dates = CSC::getWorkingDates('2026-01-05', '2026-01-11');
// Returns: [Carbon(Mon), Carbon(Tue), Carbon(Wed), Carbon(Thu), Carbon(Fri)]
// Excludes: Sat, Sun

// With holidays
$holidays = ['2026-01-07'];
$dates = CSC::getWorkingDates('2026-01-05', '2026-01-09', $holidays);
// Returns: [Carbon(Mon), Carbon(Tue), Carbon(Thu), Carbon(Fri)]
// Excludes: Wed (holiday)
```

---

## 💰 Leave Deductions

### Convert to Leave Credits

```php
// Late 60 minutes = -0.125 days
$deduction = CSC::convertMinutesToLeaveCredits(60);
// Returns: -0.125

// Late 480 minutes (full day) = -1.0 day
$deduction = CSC::convertMinutesToLeaveCredits(480);
// Returns: -1.0

// Get positive value
$credits = CSC::convertMinutesToLeaveCredits(60, false);
// Returns: 0.125
```

### Calculate Leave Deduction

```php
// Late 60 min + Undertime 30 min
$result = CSC::calculateLeaveDeduction(60, 30);

// Returns:
[
    'late_minutes' => 60,
    'undertime_minutes' => 30,
    'total_minutes' => 90,
    'total_hours' => 1.5,
    'total_days' => 0.1875,
    'leave_deduction' => -0.1875,
    'formatted' => [
        'minutes' => '1 hr 30 min',
        'hours' => '1.5 hrs',
        'days' => '0.19 days',
    ],
]
```

---

## ✅ Validation

### Validate Leave Days

```php
// Valid: 5 working days (Mon-Fri)
$result = CSC::validateLeaveDays('2026-01-05', '2026-01-09', 5.0);
// Returns: ['is_valid' => true, ...]

// Invalid: Includes weekend
$result = CSC::validateLeaveDays('2026-01-05', '2026-01-11', 7.0);
// Returns: [
//     'is_valid' => false,
//     'requested_days' => 7.0,
//     'actual_working_days' => 5,
//     'difference' => -2,
//     'message' => 'Mismatch: Requested 7 days but date range has 5 working days'
// ]
```

---

## 🎨 Formatting

### Format Time Values

```php
// Format minutes
CSC::formatMinutes(150);  // "2 hrs 30 min"
CSC::formatMinutes(60);   // "1 hr"
CSC::formatMinutes(30);   // "30 min"

// Format hours
CSC::formatHours(8.5);    // "8.5 hrs"
CSC::formatHours(1);      // "1.0 hr"

// Format days
CSC::formatDays(2.5);     // "2.50 days"
CSC::formatDays(1);       // "1.00 day"
```

---

## 📋 Common Use Cases

### Use Case 1: Process Late Deduction

```php
use App\Services\CscTimeConversionService as CSC;

$lateMinutes = 60; // Employee late 1 hour

// Convert to days
$lateDays = CSC::convertMinutesToDays($lateMinutes);
// Result: 0.125 days

// Deduct from VL balance
$vlBalance->used_credits += $lateDays;
$vlBalance->available_credits -= $lateDays;
$vlBalance->save();

// Create transaction
LeaveTransaction::create([
    'amount' => -$lateDays,
    'remarks' => "Late deduction: {$lateMinutes} minutes (" . 
                 CSC::formatMinutes($lateMinutes) . ")",
]);
```

### Use Case 2: Validate Leave Application

```php
use App\Services\CscTimeConversionService as CSC;

// Validate working days
$validation = CSC::validateLeaveDays(
    $request->start_date,
    $request->end_date,
    $request->number_of_days
);

if (!$validation['is_valid']) {
    return response()->json([
        'error' => $validation['message']
    ], 422);
}

// Proceed with leave application...
```

### Use Case 3: Calculate Accredited Hours

```php
use App\Services\CscTimeConversionService as CSC;

// Store as minutes in database
$accreditedMinutes = 420; // 7 hours worked

// Convert for display
$hours = CSC::convertMinutesToHours($accreditedMinutes);
// Result: 7.0 hours

$days = CSC::convertMinutesToDays($accreditedMinutes);
// Result: 0.875 days

// Format for user
$formatted = CSC::formatMinutes($accreditedMinutes);
// Result: "7 hrs"
```

### Use Case 4: Create Leave Attendance

```php
use App\Services\CscTimeConversionService as CSC;

// Create attendance for approved leave
Attendance::create([
    'employee_id' => $employee->id,
    'date' => $date,
    'am_in' => 'ON_LEAVE',
    'am_out' => 'ON_LEAVE',
    'pm_in' => 'ON_LEAVE',
    'pm_out' => 'ON_LEAVE',
    'accredited_hours' => CSC::MINUTES_PER_WORK_DAY, // 480
    'total_hours' => CSC::MINUTES_PER_WORK_DAY,      // 480
]);

// Create log
AccreditedHoursLog::create([
    'am_accredited_minutes' => CSC::MINUTES_PER_HALF_DAY, // 240
    'pm_accredited_minutes' => CSC::MINUTES_PER_HALF_DAY, // 240
    'total_accredited_minutes' => CSC::MINUTES_PER_WORK_DAY, // 480
]);
```

---

## 🚫 DON'T DO THIS!

### ❌ Wrong (Old Way)

```php
// DON'T use 24-hour day
$days = $minutes / 1440; // WRONG!

// DON'T use 24 hours
$hours = $days * 24; // WRONG!

// DON'T manually check weekends
if ($date->dayOfWeek == 0 || $date->dayOfWeek == 6) // WRONG!
```

### ✅ Right (CSC Way)

```php
// DO use CSC service
$days = CSC::convertMinutesToDays($minutes); // CORRECT!

// DO use 8-hour work day
$hours = CSC::convertDaysToHours($days); // CORRECT!

// DO use CSC service
if (CSC::isWeekend($date)) // CORRECT!
```

---

## 📊 Conversion Table

| Minutes | Hours | Days | Use Case |
|---------|-------|------|----------|
| 30 | 0.5 | 0.0625 | 30 min late |
| 60 | 1.0 | 0.125 | 1 hour late |
| 120 | 2.0 | 0.25 | 2 hours late |
| 240 | 4.0 | 0.5 | Half-day |
| 480 | 8.0 | 1.0 | Full day |
| 960 | 16.0 | 2.0 | 2 days |
| 2400 | 40.0 | 5.0 | 1 week (5 days) |

---

## 🔗 Constants Reference

```php
use App\Services\CscTimeConversionService as CSC;

CSC::MINUTES_PER_WORK_DAY;    // 480
CSC::HOURS_PER_WORK_DAY;      // 8
CSC::MINUTES_PER_HOUR;        // 60
CSC::DAYS_PER_MINUTE;         // 0.002083
CSC::DAYS_PER_HOUR;           // 0.125
CSC::HOURS_PER_HALF_DAY;      // 4
CSC::MINUTES_PER_HALF_DAY;    // 240
```

---

## 💡 Pro Tips

1. **Always store as minutes** in database (integer, no decimals)
2. **Convert on-the-fly** when displaying or calculating
3. **Use accessor methods** in models for automatic conversion
4. **Validate working days** before accepting leave applications
5. **Use CSC constants** instead of hardcoded values
6. **Format for display** using CSC formatting functions

---

## 📞 Need Help?

- Full Documentation: `CSC_TIME_CONVERSION_IMPLEMENTATION.md`
- Service Location: `app/Services/CscTimeConversionService.php`
- Examples: See documentation for detailed examples

---

**Quick Reference Version:** 1.0  
**Last Updated:** 2026-01-XX  
**Print this and keep it handy!** 📌
