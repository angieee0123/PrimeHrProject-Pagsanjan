# Per-Employee Custom Deduction Schedules

## Overview

The system now supports **custom deduction schedules per employee**, allowing each employee to have different cutoff schedules for their deductions, overriding the default deduction type schedules.

## How It Works

### Schedule Priority Hierarchy

1. **Employee Custom Schedule** (Highest Priority)
   - Set individually for each employee's deduction
   - Overrides the deduction type's default schedule
   
2. **Deduction Type Default Schedule** (Fallback)
   - Applied when no custom schedule is set
   - Affects all employees with that deduction type

3. **System Default** (Last Resort)
   - BOTH_SPLIT (split amount in half for each cutoff)
   - Used when neither custom nor type schedule exists

## Use Cases

### Scenario 1: Different Loan Payment Schedules

**Employee A:**
- GSIS Loan: Custom schedule = 1ST_ONLY
- Pag-IBIG Loan: Custom schedule = 2ND_ONLY
- PhilHealth: Uses default (BOTH_SPLIT)

**Employee B:**
- GSIS Loan: Custom schedule = BOTH_FULL (pays full amount twice per month)
- Pag-IBIG Loan: Uses default (BOTH_SPLIT)
- PhilHealth: Uses default (BOTH_SPLIT)

### Scenario 2: Special Payment Arrangements

**Employee C (Financial Hardship):**
- All loans: Custom schedule = 2ND_ONLY
- Reason: Receives additional income on 2nd cutoff

**Employee D (High Earner):**
- All loans: Custom schedule = BOTH_FULL
- Reason: Wants to pay off loans faster

## Database Structure

### Migration Added
```php
// employee_deductions table
custom_cutoff_schedule ENUM('1ST_ONLY', '2ND_ONLY', 'BOTH_FULL', 'BOTH_SPLIT') NULL
```

### Schedule Options

| Schedule | Description | Example (₱2,000/month) |
|----------|-------------|------------------------|
| **1ST_ONLY** | Deduct only on 1st cutoff | 1st: ₱2,000, 2nd: ₱0 |
| **2ND_ONLY** | Deduct only on 2nd cutoff | 1st: ₱0, 2nd: ₱2,000 |
| **BOTH_FULL** | Full amount on both cutoffs | 1st: ₱2,000, 2nd: ₱2,000 |
| **BOTH_SPLIT** | Split in half (default) | 1st: ₱1,000, 2nd: ₱1,000 |
| **DEFAULT** | Use deduction type's schedule | Varies by type |

## Setting Custom Schedules

### Via Admin Interface

1. Go to **Admin → Deductions → Schedules** tab
2. Select employee
3. For each deduction, choose:
   - **DEFAULT** - Use the deduction type's default schedule
   - **1ST** - Deduct only on 1st cutoff
   - **2ND** - Deduct only on 2nd cutoff
   - **BOTH** - Split amount between cutoffs
4. Click "Update Schedules"

### Via API/Route
```php
POST /admin/deductions/schedules/update
{
    "employee_id": 123,
    "start_month": "2026-01",
    "end_month": "2026-12",
    "schedules": [
        {
            "deduction_id": 456,
            "cutoff": "1ST"  // or "2ND", "BOTH", "DEFAULT"
        }
    ]
}
```

## Payroll Processing Logic

```php
// For each employee deduction
if ($deduction->custom_cutoff_schedule) {
    // Use employee's custom schedule
    $cutoffSchedule = $deduction->custom_cutoff_schedule;
} else {
    // Use deduction type's default schedule
    $schedule = $deductionType->schedules->first();
    $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
}

// Apply schedule
if ($cutoffSchedule === '1ST_ONLY') {
    $amount = $isCutoff1st ? $fullAmount : 0;
} elseif ($cutoffSchedule === '2ND_ONLY') {
    $amount = $isCutoff1st ? 0 : $fullAmount;
} elseif ($cutoffSchedule === 'BOTH_FULL') {
    $amount = $fullAmount;
} else { // BOTH_SPLIT
    $amount = $fullAmount / 2;
}
```

## Real-World Examples

### Example 1: Employee with Mixed Schedules

**Juan Dela Cruz**
- Monthly Salary: ₱30,000
- GSIS Loan: ₱3,000/month (Custom: 1ST_ONLY)
- Pag-IBIG Loan: ₱2,000/month (Custom: 2ND_ONLY)
- PhilHealth: ₱450/month (Default: BOTH_SPLIT)

**1st Cutoff (Jan 1-15):**
- Basic Pay: ₱15,000
- GSIS Loan: -₱3,000
- Pag-IBIG Loan: -₱0
- PhilHealth: -₱225
- Net Pay: ₱11,775

**2nd Cutoff (Jan 16-31):**
- Basic Pay: ₱15,000
- GSIS Loan: -₱0
- Pag-IBIG Loan: -₱2,000
- PhilHealth: -₱225
- Net Pay: ₱12,775

### Example 2: Accelerated Loan Payment

**Maria Santos**
- GSIS Loan: ₱5,000/month (Custom: BOTH_FULL)
- Wants to pay ₱10,000/month to finish loan faster

**1st Cutoff:**
- GSIS Loan: -₱5,000

**2nd Cutoff:**
- GSIS Loan: -₱5,000

**Total per month: ₱10,000** (double the normal amount)

## Benefits

### 1. Flexibility
- Accommodate individual employee circumstances
- Support special payment arrangements
- Handle financial hardship cases

### 2. Accuracy
- Correct deductions per employee agreement
- Avoid manual adjustments
- Reduce payroll errors

### 3. Transparency
- Clear schedule visibility in exports
- Shows "Custom" vs "Default" schedule type
- Easy to audit and verify

### 4. Scalability
- Each employee can have unique schedules
- No limit on customization
- Maintains system-wide defaults

## Reporting & Exports

### Schedules Export
The schedules export now includes:
- **Cutoff Schedule**: The actual schedule being used
- **Schedule Type**: "Custom" or "Default"

Example CSV output:
```
Employee ID, Name, Deduction, Amount, Schedule, Type
2023-001, Juan Dela Cruz, GSIS Loan, ₱3,000, 1ST_ONLY, Custom
2023-001, Juan Dela Cruz, PhilHealth, ₱450, BOTH_SPLIT, Default
2023-002, Maria Santos, GSIS Loan, ₱5,000, BOTH_FULL, Custom
```

## API Response

When fetching employee deductions:
```json
{
  "deductions": [
    {
      "id": 456,
      "name": "GSIS Loan",
      "amount": "3000.00",
      "current_schedule": "1ST_ONLY",
      "has_custom_schedule": true,
      "default_schedule": "BOTH_SPLIT"
    }
  ]
}
```

## Migration Instructions

### Step 1: Run Migration
```bash
php artisan migrate
```

This adds the `custom_cutoff_schedule` column to `employee_deductions` table.

### Step 2: Existing Data
All existing deductions will have `custom_cutoff_schedule = NULL`, meaning they use the default schedule. No data migration needed.

### Step 3: Set Custom Schedules
Use the admin interface to set custom schedules for employees who need them.

## Important Notes

1. **NULL = Default**: If `custom_cutoff_schedule` is NULL, the system uses the deduction type's default schedule

2. **Per Deduction**: Custom schedules are set per employee deduction, not per employee. This means:
   - Employee can have GSIS loan on 1ST_ONLY
   - Same employee can have Pag-IBIG loan on 2ND_ONLY

3. **Overrides Only**: Custom schedules override the default but don't change the default for other employees

4. **Audit Trail**: The `updated_at` timestamp tracks when custom schedules are modified

5. **Validation**: The system validates that custom schedules are one of the allowed enum values

## Troubleshooting

### Issue: Custom schedule not applying
**Solution**: Check that `custom_cutoff_schedule` is not NULL in the database

### Issue: Wrong amount deducted
**Solution**: Verify the cutoff period detection (1st vs 2nd cutoff) is correct

### Issue: Export shows wrong schedule type
**Solution**: Ensure the export query includes the `custom_cutoff_schedule` field

## Future Enhancements

Potential features to add:
- Bulk set custom schedules for multiple employees
- Schedule change history/audit log
- Temporary schedule overrides (e.g., skip one cutoff)
- Schedule templates for common patterns
- Automatic schedule suggestions based on salary patterns
