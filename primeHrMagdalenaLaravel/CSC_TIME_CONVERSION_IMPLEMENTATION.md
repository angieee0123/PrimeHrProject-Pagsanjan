# CSC Time Conversion Standards - Implementation Guide

## 📋 Overview

This document outlines the implementation of Civil Service Commission (CSC) time conversion standards for the Philippine government service HRIS system.

**Implementation Date:** 2026-01-XX  
**Status:** ✅ COMPLETE  
**Compliance:** CSC Government Service Standards

---

## 🎯 CSC Standards

### Official Working Day Definition

According to CSC regulations for Philippine government service:

| Unit | Value | CSC Standard |
|------|-------|--------------|
| 1 official working day | 8.000 hours | Exact |
| 0.5 day (Half-day) | 4.000 hours | Exact |
| 1 hour | 0.125 days | 1 / 8 |
| 1 minute | 0.002083 days | 1 / 480 (CSC rounding) |
| 1 work day | 480 minutes | 8 hours × 60 minutes |

### Working Day Exclusions

Working days **automatically exclude**:
- ✅ Saturdays (Weekend)
- ✅ Sundays (Weekend)
- ✅ Legal holidays (when provided)

---

## 🔧 Implementation

### 1. Core Service: CscTimeConversionService

**Location:** `app/Services/CscTimeConversionService.php`

**Purpose:** Centralized service for all CSC-compliant time conversions

#### Constants Defined

```php
const MINUTES_PER_WORK_DAY = 480;      // 8 hours * 60 minutes
const HOURS_PER_WORK_DAY = 8;          // Official working hours per day
const MINUTES_PER_HOUR = 60;
const DAYS_PER_MINUTE = 0.002083;      // CSC standard (1/480)
const DAYS_PER_HOUR = 0.125;           // 1/8
const HOURS_PER_HALF_DAY = 4;
const MINUTES_PER_HALF_DAY = 240;
```

#### Core Functions

##### Days ↔ Hours Conversion

```php
// Convert days to hours (1 day = 8 hours)
CscTimeConversionService::convertDaysToHours(float $days): float

// Convert hours to days (8 hours = 1 day)
CscTimeConversionService::convertHoursToDays(float $hours): float
```

**Examples:**
```php
CscTimeConversionService::convertDaysToHours(1);    // Returns: 8.0
CscTimeConversionService::convertDaysToHours(0.5);  // Returns: 4.0
CscTimeConversionService::convertDaysToHours(2.5);  // Returns: 20.0

CscTimeConversionService::convertHoursToDays(8);    // Returns: 1.0
CscTimeConversionService::convertHoursToDays(4);    // Returns: 0.5
CscTimeConversionService::convertHoursToDays(1);    // Returns: 0.125
```

##### Days ↔ Minutes Conversion

```php
// Convert days to minutes (1 day = 480 minutes)
CscTimeConversionService::convertDaysToMinutes(float $days): int

// Convert minutes to days (480 minutes = 1 day)
CscTimeConversionService::convertMinutesToDays(int $minutes): float
```

**Examples:**
```php
CscTimeConversionService::convertDaysToMinutes(1);     // Returns: 480
CscTimeConversionService::convertDaysToMinutes(0.5);   // Returns: 240
CscTimeConversionService::convertDaysToMinutes(2);     // Returns: 960

CscTimeConversionService::convertMinutesToDays(480);   // Returns: 1.0
CscTimeConversionService::convertMinutesToDays(240);   // Returns: 0.5
CscTimeConversionService::convertMinutesToDays(60);    // Returns: 0.125
```

##### Hours ↔ Minutes Conversion

```php
// Convert hours to minutes
CscTimeConversionService::convertHoursToMinutes(float $hours): int

// Convert minutes to hours
CscTimeConversionService::convertMinutesToHours(int $minutes): float
```

**Examples:**
```php
CscTimeConversionService::convertHoursToMinutes(8);    // Returns: 480
CscTimeConversionService::convertHoursToMinutes(1);    // Returns: 60
CscTimeConversionService::convertHoursToMinutes(0.5);  // Returns: 30

CscTimeConversionService::convertMinutesToHours(480);  // Returns: 8.0
CscTimeConversionService::convertMinutesToHours(60);   // Returns: 1.0
CscTimeConversionService::convertMinutesToHours(30);   // Returns: 0.5
```

##### Minutes to Leave Credits

```php
// Convert minutes to leave credits (for deductions)
CscTimeConversionService::convertMinutesToLeaveCredits(
    int $minutes, 
    bool $asNegative = true
): float
```

**Examples:**
```php
// 60 minutes late = -0.125 days deduction
CscTimeConversionService::convertMinutesToLeaveCredits(60);
// Returns: -0.125

// 480 minutes (full day) = -1.0 day deduction
CscTimeConversionService::convertMinutesToLeaveCredits(480);
// Returns: -1.0

// 30 minutes late = -0.0625 days deduction
CscTimeConversionService::convertMinutesToLeaveCredits(30);
// Returns: -0.0625
```

##### Working Days Calculation

```php
// Calculate working days between dates (excludes weekends & holidays)
CscTimeConversionService::calculateWorkingDays(
    $startDate, 
    $endDate, 
    array $holidays = []
): int

// Calculate working hours between dates
CscTimeConversionService::calculateWorkingHours(
    $startDate, 
    $endDate, 
    array $holidays = []
): float

// Calculate working minutes between dates
CscTimeConversionService::calculateWorkingMinutes(
    $startDate, 
    $endDate, 
    array $holidays = []
): int
```

**Examples:**
```php
// Monday to Friday (5 working days)
$workingDays = CscTimeConversionService::calculateWorkingDays(
    '2026-01-05', // Monday
    '2026-01-09'  // Friday
);
// Returns: 5

// Monday to Sunday (5 working days, excludes Sat & Sun)
$workingDays = CscTimeConversionService::calculateWorkingDays(
    '2026-01-05', // Monday
    '2026-01-11'  // Sunday
);
// Returns: 5

// With holidays
$holidays = ['2026-01-07']; // Wednesday is holiday
$workingDays = CscTimeConversionService::calculateWorkingDays(
    '2026-01-05', // Monday
    '2026-01-09', // Friday
    $holidays
);
// Returns: 4 (excludes Wednesday holiday)

// Calculate hours (5 days × 8 hours = 40 hours)
$workingHours = CscTimeConversionService::calculateWorkingHours(
    '2026-01-05',
    '2026-01-09'
);
// Returns: 40.0
```

##### Validation Functions

```php
// Check if date is weekend
CscTimeConversionService::isWeekend($date): bool

// Check if date is working day
CscTimeConversionService::isWorkingDay($date, array $holidays = []): bool

// Get array of working dates
CscTimeConversionService::getWorkingDates(
    $startDate, 
    $endDate, 
    array $holidays = []
): array

// Validate leave application days
CscTimeConversionService::validateLeaveDays(
    $startDate, 
    $endDate, 
    float $requestedDays, 
    array $holidays = []
): array
```

**Examples:**
```php
// Check weekend
CscTimeConversionService::isWeekend('2026-01-10'); // Saturday
// Returns: true

CscTimeConversionService::isWeekend('2026-01-05'); // Monday
// Returns: false

// Validate leave days
$validation = CscTimeConversionService::validateLeaveDays(
    '2026-01-05', // Monday
    '2026-01-09', // Friday
    5.0           // Requested 5 days
);
// Returns: [
//     'is_valid' => true,
//     'requested_days' => 5.0,
//     'actual_working_days' => 5,
//     'difference' => 0,
//     'message' => 'Leave days match working days'
// ]

// Invalid example (includes weekend)
$validation = CscTimeConversionService::validateLeaveDays(
    '2026-01-05', // Monday
    '2026-01-11', // Sunday
    7.0           // Requested 7 days (incorrect)
);
// Returns: [
//     'is_valid' => false,
//     'requested_days' => 7.0,
//     'actual_working_days' => 5,
//     'difference' => -2,
//     'message' => 'Mismatch: Requested 7 days but date range has 5 working days'
// ]
```

##### Formatting Functions

```php
// Format minutes to human-readable
CscTimeConversionService::formatMinutes(int $minutes): string

// Format hours to human-readable
CscTimeConversionService::formatHours(float $hours, int $decimals = 1): string

// Format days to human-readable
CscTimeConversionService::formatDays(float $days, int $decimals = 2): string
```

**Examples:**
```php
CscTimeConversionService::formatMinutes(150);
// Returns: "2 hrs 30 min"

CscTimeConversionService::formatMinutes(60);
// Returns: "1 hr"

CscTimeConversionService::formatMinutes(30);
// Returns: "30 min"

CscTimeConversionService::formatHours(8.5);
// Returns: "8.5 hrs"

CscTimeConversionService::formatDays(2.5);
// Returns: "2.50 days"
```

##### Leave Deduction Calculation

```php
// Calculate leave deduction for tardiness/undertime
CscTimeConversionService::calculateLeaveDeduction(
    int $lateMinutes, 
    int $undertimeMinutes
): array
```

**Example:**
```php
$deduction = CscTimeConversionService::calculateLeaveDeduction(
    60,  // 1 hour late
    30   // 30 minutes undertime
);

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

## 📦 Updated Files

### Services

1. ✅ **CscTimeConversionService.php** (NEW)
   - Core conversion service
   - All CSC-compliant functions

2. ✅ **LateDeductionService.php** (UPDATED)
   - Now uses `CscTimeConversionService::convertMinutesToDays()`
   - Now uses `CscTimeConversionService::convertDaysToMinutes()`

### Controllers

3. ✅ **LeaveController.php** (UPDATED)
   - Added working day validation using `validateLeaveDays()`
   - Prevents leave applications that include weekends

4. ✅ **AttendanceController.php** (UPDATED)
   - Uses `getWorkingDates()` for working day calculation
   - Uses `formatMinutes()` for consistent formatting

### Observers

5. ✅ **LeaveApplicationObserver.php** (UPDATED)
   - Uses `isWeekend()` to skip weekends
   - Uses CSC constants for leave attendance records

### Models

6. ✅ **AccreditedHoursLog.php** (UPDATED)
   - Added accessor methods for CSC conversions
   - `getTotalAccreditedHoursAttribute()`
   - `getTotalAccreditedDaysAttribute()`
   - `getLateDaysAttribute()`
   - `getUndertimeDaysAttribute()`
   - `getLeaveDeductionAttribute()`

---

## 🔄 Conversion Examples

### Scenario 1: Employee Late by 1 Hour

```php
$lateMinutes = 60;

// Convert to days for leave deduction
$lateDays = CscTimeConversionService::convertMinutesToDays($lateMinutes);
// Result: 0.125 days (1/8 of a day)

// As leave credit deduction
$deduction = CscTimeConversionService::convertMinutesToLeaveCredits($lateMinutes);
// Result: -0.125 days
```

### Scenario 2: 2-Day Leave Application

```php
$leaveDays = 2;

// Convert to hours
$leaveHours = CscTimeConversionService::convertDaysToHours($leaveDays);
// Result: 16.0 hours

// Convert to minutes
$leaveMinutes = CscTimeConversionService::convertDaysToMinutes($leaveDays);
// Result: 960 minutes

// Validate date range (Monday to Tuesday)
$validation = CscTimeConversionService::validateLeaveDays(
    '2026-01-05', // Monday
    '2026-01-06', // Tuesday
    2.0
);
// Result: Valid (2 working days)
```

### Scenario 3: Leave Application Including Weekend (INVALID)

```php
// Employee requests 5 days from Friday to Tuesday
$validation = CscTimeConversionService::validateLeaveDays(
    '2026-01-09', // Friday
    '2026-01-13', // Tuesday (next week)
    5.0           // Requested 5 days
);

// Result:
[
    'is_valid' => false,
    'requested_days' => 5.0,
    'actual_working_days' => 3, // Fri, Mon, Tue (excludes Sat & Sun)
    'difference' => -2,
    'message' => 'Mismatch: Requested 5 days but date range has 3 working days'
]

// Correct request should be 3 days
```

### Scenario 4: Late Deduction from Leave Balance

```php
// Employee late 480 minutes (full day)
$lateMinutes = 480;

// Convert to days
$lateDays = CscTimeConversionService::convertMinutesToDays($lateMinutes);
// Result: 1.0 day

// Deduct from VL balance
$vlBalance->used_credits += $lateDays;
$vlBalance->available_credits -= $lateDays;

// Before fix: 480 / 1440 = 0.333 days (WRONG!)
// After fix:  480 / 480 = 1.0 day (CORRECT!)
```

---

## 🎯 Business Logic Flow

### Leave Application Process

```
1. Employee submits leave request
   ↓
2. System validates working days
   - CscTimeConversionService::validateLeaveDays()
   - Excludes weekends automatically
   - Excludes holidays if provided
   ↓
3. If valid, create leave application
   ↓
4. Deduct from leave balance
   - Uses CSC standard: 1 day = 8 hours = 480 minutes
   ↓
5. When approved, create attendance records
   - LeaveApplicationObserver uses CSC constants
   - 480 minutes per day
   - 240 minutes per half-day
```

### Late Deduction Process

```
1. Employee late (e.g., 60 minutes)
   ↓
2. AccreditedHoursLog records late_minutes
   ↓
3. LateDeductionService processes
   - Converts: 60 min ÷ 480 = 0.125 days
   - Uses CscTimeConversionService::convertMinutesToDays()
   ↓
4. Deduct from VL/SL balance
   - 0.125 days deducted
   ↓
5. Create leave transaction
   - Amount: -0.125
   - Type: debit
```

### Attendance Calculation

```
1. Employee clocks in/out
   ↓
2. System calculates accredited hours
   - Uses employee schedule
   - Applies 5-minute grace period
   ↓
3. AccreditedHoursLog created
   - Stores minutes (not hours or days)
   - total_accredited_minutes
   - late_minutes
   - undertime_minutes
   ↓
4. Accessor methods provide conversions
   - $log->total_accredited_hours (uses CSC service)
   - $log->total_accredited_days (uses CSC service)
   - $log->late_days (uses CSC service)
```

---

## ✅ Validation Rules

### Leave Application Validation

1. **Working Days Only**
   - System automatically excludes weekends
   - System excludes holidays when provided
   - Requested days must match actual working days

2. **Minimum Increment**
   - 0.5 days (half-day) minimum
   - CSC standard: 4 hours = 0.5 day

3. **Date Range Validation**
   - Start date ≤ End date
   - Requested days = Actual working days in range

### Late/Undertime Deduction

1. **Conversion Standard**
   - 1 minute = 0.002083 days
   - 60 minutes = 0.125 days (1 hour)
   - 480 minutes = 1.0 day (full day)

2. **Precision**
   - Store as minutes (integer)
   - Convert to days with 6 decimal precision
   - Display with appropriate formatting

---

## 🧪 Testing Examples

### Test Case 1: Days to Hours Conversion

```php
// Test: 1 day = 8 hours
$hours = CscTimeConversionService::convertDaysToHours(1);
assert($hours === 8.0);

// Test: 0.5 day = 4 hours
$hours = CscTimeConversionService::convertDaysToHours(0.5);
assert($hours === 4.0);

// Test: 2.5 days = 20 hours
$hours = CscTimeConversionService::convertDaysToHours(2.5);
assert($hours === 20.0);
```

### Test Case 2: Minutes to Days Conversion

```php
// Test: 480 minutes = 1 day
$days = CscTimeConversionService::convertMinutesToDays(480);
assert($days === 1.0);

// Test: 60 minutes = 0.125 days
$days = CscTimeConversionService::convertMinutesToDays(60);
assert($days === 0.125);

// Test: 240 minutes = 0.5 days
$days = CscTimeConversionService::convertMinutesToDays(240);
assert($days === 0.5);
```

### Test Case 3: Working Days Calculation

```php
// Test: Monday to Friday = 5 working days
$workingDays = CscTimeConversionService::calculateWorkingDays(
    '2026-01-05', // Monday
    '2026-01-09'  // Friday
);
assert($workingDays === 5);

// Test: Monday to Sunday = 5 working days (excludes weekend)
$workingDays = CscTimeConversionService::calculateWorkingDays(
    '2026-01-05', // Monday
    '2026-01-11'  // Sunday
);
assert($workingDays === 5);

// Test: With holiday
$holidays = ['2026-01-07']; // Wednesday
$workingDays = CscTimeConversionService::calculateWorkingDays(
    '2026-01-05',
    '2026-01-09',
    $holidays
);
assert($workingDays === 4);
```

### Test Case 4: Leave Validation

```php
// Test: Valid leave (5 working days)
$validation = CscTimeConversionService::validateLeaveDays(
    '2026-01-05', // Monday
    '2026-01-09', // Friday
    5.0
);
assert($validation['is_valid'] === true);

// Test: Invalid leave (includes weekend)
$validation = CscTimeConversionService::validateLeaveDays(
    '2026-01-05', // Monday
    '2026-01-11', // Sunday
    7.0           // Wrong! Should be 5
);
assert($validation['is_valid'] === false);
assert($validation['actual_working_days'] === 5);
```

---

## 📊 Before vs After Comparison

### Late Deduction (60 minutes)

| Aspect | Before (24-hour) | After (CSC 8-hour) |
|--------|------------------|---------------------|
| Conversion | 60 / 1440 | 60 / 480 |
| Result | 0.0417 days | 0.125 days |
| Leave Deduction | -0.0417 | -0.125 |
| Accuracy | ❌ Wrong | ✅ Correct |

### 2-Day Leave Application

| Aspect | Before (24-hour) | After (CSC 8-hour) |
|--------|------------------|---------------------|
| Hours | 48 hours | 16 hours |
| Minutes | 2880 minutes | 960 minutes |
| Deduction | 6 work days | 2 work days |
| Accuracy | ❌ Wrong | ✅ Correct |

### Working Days Calculation

| Aspect | Before | After (CSC) |
|--------|--------|-------------|
| Weekend Handling | Manual check | Automatic exclusion |
| Holiday Handling | Not implemented | Supported |
| Validation | None | Built-in validation |
| Consistency | ❌ Inconsistent | ✅ Consistent |

---

## 🚀 Usage in Code

### In Controllers

```php
use App\Services\CscTimeConversionService;

// Validate leave application
$validation = CscTimeConversionService::validateLeaveDays(
    $request->start_date,
    $request->end_date,
    $request->number_of_days
);

if (!$validation['is_valid']) {
    return response()->json([
        'error' => $validation['message']
    ], 422);
}
```

### In Services

```php
use App\Services\CscTimeConversionService;

// Convert late minutes to days for deduction
$lateDays = CscTimeConversionService::convertMinutesToDays($lateMinutes);

// Deduct from leave balance
$leaveBalance->used_credits += $lateDays;
$leaveBalance->available_credits -= $lateDays;
```

### In Models

```php
use App\Services\CscTimeConversionService;

// Accessor method
public function getTotalAccreditedDaysAttribute(): float
{
    return CscTimeConversionService::convertMinutesToDays(
        $this->total_accredited_minutes
    );
}
```

### In Views/JavaScript

```javascript
// Format minutes for display
function formatMinutes(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    
    if (hours > 0 && mins > 0) {
        return `${hours} hr${hours > 1 ? 's' : ''} ${mins} min`;
    } else if (hours > 0) {
        return `${hours} hr${hours > 1 ? 's' : ''}`;
    } else {
        return `${mins} min`;
    }
}

// Calculate working days (client-side validation)
function calculateWorkingDays(startDate, endDate) {
    let count = 0;
    let current = new Date(startDate);
    const end = new Date(endDate);
    
    while (current <= end) {
        const dayOfWeek = current.getDay();
        // Exclude weekends (0 = Sunday, 6 = Saturday)
        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
            count++;
        }
        current.setDate(current.getDate() + 1);
    }
    
    return count;
}
```

---

## 📝 Migration Notes

### Database Changes

No database schema changes required. All conversions are handled in application logic.

### Existing Data

Existing data remains valid. The service handles conversions on-the-fly.

### Backward Compatibility

✅ Fully backward compatible. Old calculations are replaced with CSC-compliant ones.

---

## 🎓 Training Notes

### For HR Staff

1. **Leave Applications**
   - System now validates working days automatically
   - Weekends are excluded from leave counts
   - Error messages guide correct input

2. **Late Deductions**
   - 1 hour late = 0.125 days deducted from VL
   - 8 hours late = 1 full day deducted
   - Deductions are accurate and CSC-compliant

3. **Reports**
   - All time displays use consistent formatting
   - Days, hours, and minutes are properly converted
   - Working days exclude weekends automatically

### For Developers

1. **Always use CscTimeConversionService**
   - Never hardcode 24, 1440, or other non-CSC values
   - Use service constants for consistency
   - Use service methods for all conversions

2. **Store as Minutes**
   - Database stores minutes (integer)
   - Convert to hours/days when needed
   - Use accessor methods in models

3. **Validate Working Days**
   - Always validate leave applications
   - Use `validateLeaveDays()` method
   - Provide clear error messages

---

## ✅ Checklist

- [x] Created CscTimeConversionService
- [x] Updated LateDeductionService
- [x] Updated LeaveController
- [x] Updated AttendanceController
- [x] Updated LeaveApplicationObserver
- [x] Updated AccreditedHoursLog model
- [x] Added working day validation
- [x] Added weekend exclusion
- [x] Added holiday support
- [x] Created comprehensive documentation
- [x] Added usage examples
- [x] Added test cases

---

## 📚 References

- Civil Service Commission (CSC) Memorandum Circulars
- Philippine Government Service Standards
- CSC Leave Regulations
- Government Working Hours Standards

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-XX  
**Maintained By:** Development Team  
**Status:** ✅ PRODUCTION READY
