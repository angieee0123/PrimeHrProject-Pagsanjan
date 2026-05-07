# Daily Salary Auto-Computation System

## Overview
The system automatically computes daily salaries whenever attendance records are created or updated through the Detailed DTR modal.

## How It Works

### 1. **Automatic Triggers**
Daily salary computation is triggered automatically in these scenarios:

- ✅ **Attendance Correction** - When admin clicks "Save Correction" in Detailed DTR
- ✅ **Schedule Recalculation** - When employee schedules are updated
- ✅ **AccreditedHoursLog Changes** - Any create/update via Observer

### 2. **Computation Formula**

```
Monthly Rate (from designation) → Daily Rate (÷22) → Hourly Rate (÷8)

Daily Basic Pay = (Accredited Minutes ÷ 480) × Daily Rate
OT Pay = (OT Minutes ÷ 60) × Hourly Rate × 1.25
Late Deduction = (Late Minutes ÷ 60) × Hourly Rate
Undertime Deduction = (Undertime Minutes ÷ 60) × Hourly Rate

Daily Gross Pay = Basic Pay + OT Pay - Late Deduction - Undertime Deduction
```

### 3. **Database Tables**

#### `daily_salary_computations`
Stores daily salary breakdown for each work day:
- `monthly_rate`, `daily_rate`, `hourly_rate`
- `accredited_minutes`, `late_minutes`, `undertime_minutes`, `ot_minutes`
- `daily_basic_pay`, `ot_pay`, `late_deduction`, `undertime_deduction`
- `daily_gross_pay`

#### `salary_computations`
Aggregates daily records for payroll periods:
- `period_start`, `period_end`, `payroll_type` (monthly/semi-monthly/weekly)
- `total_days_present`, `total_days_absent`
- `basic_pay`, `ot_pay`, deductions
- `gross_pay`, `net_pay`
- `status` (draft/pending/approved/paid)

## Implementation Details

### Models
- **DailySalaryComputation** - Handles daily computation logic
- **SalaryComputation** - Aggregates daily records for periods
- **AccreditedHoursLog** - Triggers computation via Observer

### Observer
`AccreditedHoursLogObserver` automatically calls:
```php
DailySalaryComputation::computeFromAccreditedLog($accreditedHoursLog);
```

### Controller Integration
`AttendanceController::correctAttendance()` triggers computation after saving:
```php
$accreditedLog = AccreditedHoursLog::updateOrCreate(...);
DailySalaryComputation::computeFromAccreditedLog($accreditedLog);
```

## Key Features

### ✅ Relationship Chain
```
AccreditedHoursLog 
  → Employee 
    → EmploymentDetail 
      → DesignationRelation 
        → monthly_rate
```

### ✅ Fallback Mechanism
If relationships fail, uses direct DB query:
```php
DB::table('employees')
    ->join('employment_details', ...)
    ->join('designations', ...)
    ->value('designations.monthly_rate');
```

### ✅ Error Handling
- Zero-division protection
- Null-safe operators
- Logging for debugging
- Graceful degradation

## Usage

### For Admins
1. Open Detailed DTR modal
2. Click "Correct Attendance" on any date
3. Fill in time fields
4. Click "Save Correction"
5. ✅ Daily salary automatically computed!

### For Developers

#### Compute Single Employee Period
```php
SalaryComputation::computePeriod(
    $employeeId,
    '2026-05-01',  // period_start
    '2026-05-31',  // period_end
    'monthly',     // payroll_type
    Auth::id()     // computed_by
);
```

#### Compute All Employees
```php
$employees = Employee::whereHas('employmentDetail')->get();
foreach ($employees as $employee) {
    SalaryComputation::computePeriod(
        $employee->id,
        $periodStart,
        $periodEnd,
        'monthly',
        Auth::id()
    );
}
```

## Testing

### Manual Test
```bash
php test_daily_salary_computation.php
```

### Fix Existing Records
```bash
php fix_daily_salaries.php
```

## Example Output

```
Employee ID 8 (Municipal Mayor)
Work Date: 2026-05-04
Monthly Rate: ₱121,264.00
Daily Rate: ₱5,512.00
Hourly Rate: ₱689.00

Accredited: 480 mins (8 hours)
OT: 240 mins (4 hours)

Basic Pay: ₱5,512.00
OT Pay: ₱3,445.00 (4 hrs × ₱689 × 1.25)
Late Deduction: ₱0.00
Undertime Deduction: ₱0.00

DAILY GROSS PAY: ₱8,957.00
```

## Files Modified

### Models
- `app/Models/DailySalaryComputation.php`
- `app/Models/SalaryComputation.php`
- `app/Models/AccreditedHoursLog.php`

### Observers
- `app/Observers/AccreditedHoursLogObserver.php`

### Controllers
- `app/Http/Controllers/AttendanceController.php`
- `app/Http/Controllers/SalaryComputationController.php`

### Migrations
- `database/migrations/2026_05_04_000001_create_daily_salary_computations_table.php`
- `database/migrations/2026_05_04_000002_create_salary_computations_table.php`

### Providers
- `app/Providers/AppServiceProvider.php` (Observer registration)

## Troubleshooting

### Issue: Monthly rate is 0
**Solution:** Check if employee has:
1. Employment detail record
2. Designation assigned
3. Monthly rate set in designation

### Issue: Computation not triggering
**Solution:** 
1. Verify Observer is registered in AppServiceProvider
2. Check if AccreditedHoursLog is being created/updated
3. Review Laravel logs for errors

### Issue: Wrong calculations
**Solution:**
1. Verify accredited_minutes are correct
2. Check if schedule is properly assigned
3. Run `php fix_daily_salaries.php` to recompute

## Future Enhancements

- [ ] Add holiday pay computation (2x rate)
- [ ] Add night differential (10% of basic rate)
- [ ] Add government deductions (SSS, PhilHealth, Pag-IBIG, Tax)
- [ ] Add allowances and bonuses
- [ ] Generate payslips
- [ ] Export to accounting software
