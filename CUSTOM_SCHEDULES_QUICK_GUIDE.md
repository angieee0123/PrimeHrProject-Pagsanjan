# Custom Deduction Schedules - Quick Reference

## What Changed

✅ Each employee can now have **custom deduction schedules** that override the default

## Key Features

### 1. Schedule Priority
```
Employee Custom Schedule (if set)
    ↓ (if not set)
Deduction Type Default Schedule
    ↓ (if not set)
System Default (BOTH_SPLIT)
```

### 2. Schedule Options

| Option | 1st Cutoff | 2nd Cutoff | Use Case |
|--------|-----------|-----------|----------|
| **1ST_ONLY** | Full amount | ₱0 | Pay all on 1st cutoff |
| **2ND_ONLY** | ₱0 | Full amount | Pay all on 2nd cutoff |
| **BOTH_FULL** | Full amount | Full amount | Double payment (accelerated) |
| **BOTH_SPLIT** | Half amount | Half amount | Normal split (default) |
| **DEFAULT** | - | - | Use deduction type's schedule |

## Quick Examples

### Example 1: Spread Out Payments
```
Employee: Juan
- GSIS Loan ₱3,000/mo → Custom: 1ST_ONLY
- Pag-IBIG ₱2,000/mo → Custom: 2ND_ONLY

Result:
1st Cutoff: -₱3,000 (GSIS only)
2nd Cutoff: -₱2,000 (Pag-IBIG only)
```

### Example 2: Accelerated Payment
```
Employee: Maria
- GSIS Loan ₱5,000/mo → Custom: BOTH_FULL

Result:
1st Cutoff: -₱5,000
2nd Cutoff: -₱5,000
Total: ₱10,000/month (pays off loan faster)
```

### Example 3: Financial Hardship
```
Employee: Pedro
- All loans → Custom: 2ND_ONLY
- Reason: Gets extra income on 2nd cutoff

Result:
1st Cutoff: No loan deductions
2nd Cutoff: All loan deductions
```

## How to Set

### Admin Interface
1. Go to **Deductions → Schedules** tab
2. Select employee
3. Choose schedule for each deduction:
   - **DEFAULT** = Use type's default
   - **1ST** = 1st cutoff only
   - **2ND** = 2nd cutoff only
   - **BOTH** = Split between cutoffs
4. Save

### Database
```sql
-- Set custom schedule
UPDATE employee_deductions 
SET custom_cutoff_schedule = '1ST_ONLY' 
WHERE id = 123;

-- Remove custom schedule (use default)
UPDATE employee_deductions 
SET custom_cutoff_schedule = NULL 
WHERE id = 123;
```

## Files Modified

1. **Migration**: `2026_06_08_000010_add_custom_cutoff_schedule_to_employee_deductions.php`
   - Adds `custom_cutoff_schedule` column

2. **Model**: `EmployeeDeduction.php`
   - Added to fillable array

3. **Routes**: `web.php`
   - Updated payroll calculation logic
   - Updated schedule update route
   - Updated exports

## Payroll Logic

```php
// Check for custom schedule first
if ($deduction->custom_cutoff_schedule) {
    $schedule = $deduction->custom_cutoff_schedule;
} else {
    // Fall back to deduction type default
    $schedule = $deductionType->schedules->first()->cutoff_schedule ?? 'BOTH_SPLIT';
}
```

## Testing Checklist

- [ ] Set custom schedule for employee
- [ ] Generate payroll for 1st cutoff
- [ ] Verify correct amount deducted
- [ ] Generate payroll for 2nd cutoff
- [ ] Verify correct amount deducted
- [ ] Export schedules - verify "Custom" type shown
- [ ] Remove custom schedule (set to DEFAULT)
- [ ] Verify falls back to deduction type default

## Common Scenarios

### Scenario: Employee wants to pay loan faster
**Solution**: Set custom schedule to BOTH_FULL

### Scenario: Employee has cash flow issues
**Solution**: Set all loans to 2ND_ONLY (when they have more money)

### Scenario: Employee wants balanced deductions
**Solution**: Alternate loans between 1ST_ONLY and 2ND_ONLY

### Scenario: Reset to normal
**Solution**: Set custom schedule to DEFAULT or NULL

## Benefits

✅ **Flexibility** - Each employee can have unique schedules
✅ **Accuracy** - Deductions match agreements
✅ **Transparency** - Clear visibility of custom vs default
✅ **No Manual Work** - System handles calculations automatically

## Migration Command

```bash
php artisan migrate
```

This adds the `custom_cutoff_schedule` column. Existing data is unaffected (NULL = use default).
