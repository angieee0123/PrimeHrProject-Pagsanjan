# How System Handles Different Loans Per Employee

## Real-World Example

### Employee Setup

**Juan Dela Cruz (Employee ID: 2023-001)**
- GSIS_CONSO: ₱3,000/month (Custom: 1ST_ONLY)
- PAGIBIG_MPL: ₱2,000/month (Custom: 2ND_ONLY)
- LBP Loan: ₱1,500/month (Default: BOTH_SPLIT)
- PhilHealth: ₱450/month (Default: BOTH_SPLIT)
- GSIS Premium: ₱800/month (Default: BOTH_SPLIT)

**Jeremy Pogi (Employee ID: 2023-002)**
- PhilHealth: ₱300/month (Default: BOTH_SPLIT)
- GSIS Premium: ₱600/month (Default: BOTH_SPLIT)
- (No loans)

**Maria Santos (Employee ID: 2023-003)**
- UCPB Loan: ₱2,500/month (Custom: BOTH_FULL)
- PAGIBIG_MPL: ₱1,000/month (Default: BOTH_SPLIT)
- PhilHealth: ₱400/month (Default: BOTH_SPLIT)
- GSIS Premium: ₱700/month (Default: BOTH_SPLIT)

## Payroll Register Output (1st Cutoff)

### Dynamic Column Generation

The system scans all three employees and finds these deduction types:
1. PhilHealth (all 3 have it)
2. GSIS Premium (all 3 have it)
3. GSIS_CONSO (only Juan)
4. PAGIBIG_MPL (Juan and Maria)
5. LBP (only Juan)
6. UCPB (only Maria)

### Table Display

```
┌──────────────────┬───────────┬────────────┬─────────────┬──────────────┬─────────────┬──────────┬──────────┬──────────────────┬─────────┐
│ Employee         │ Basic Pay │ PhilHealth │ GSIS Premium│ GSIS_CONSO   │ PAGIBIG_MPL │ LBP      │ UCPB     │ Total Deductions │ Net Pay │
├──────────────────┼───────────┼────────────┼─────────────┼──────────────┼─────────────┼──────────┼──────────┼──────────────────┼─────────┤
│ Juan Dela Cruz   │ ₱15,000   │ ₱225       │ ₱400        │ ₱3,000       │ ₱0          │ ₱750     │ ₱0       │ ₱4,375           │ ₱10,625 │
│ Jeremy Pogi      │ ₱12,000   │ ₱150       │ ₱300        │ ₱0           │ ₱0          │ ₱0       │ ₱0       │ ₱450             │ ₱11,550 │
│ Maria Santos     │ ₱14,000   │ ₱200       │ ₱350        │ ₱0           │ ₱500        │ ₱0       │ ₱2,500   │ ₱3,550           │ ₱10,450 │
└──────────────────┴───────────┴────────────┴─────────────┴──────────────┴─────────────┴──────────┴──────────┴──────────────────┴─────────┘
```

### Breakdown by Employee

#### Juan Dela Cruz (1st Cutoff)
```php
Deductions calculated:
- PhilHealth: ₱450 / 2 = ₱225 (BOTH_SPLIT)
- GSIS Premium: ₱800 / 2 = ₱400 (BOTH_SPLIT)
- GSIS_CONSO: ₱3,000 (1ST_ONLY - full amount on 1st)
- PAGIBIG_MPL: ₱0 (2ND_ONLY - nothing on 1st)
- LBP: ₱1,500 / 2 = ₱750 (BOTH_SPLIT)
- UCPB: ₱0 (doesn't have this loan)

Total: ₱4,375
```

#### Jeremy Pogi (1st Cutoff)
```php
Deductions calculated:
- PhilHealth: ₱300 / 2 = ₱150 (BOTH_SPLIT)
- GSIS Premium: ₱600 / 2 = ₱300 (BOTH_SPLIT)
- GSIS_CONSO: ₱0 (doesn't have this loan)
- PAGIBIG_MPL: ₱0 (doesn't have this loan)
- LBP: ₱0 (doesn't have this loan)
- UCPB: ₱0 (doesn't have this loan)

Total: ₱450
```

#### Maria Santos (1st Cutoff)
```php
Deductions calculated:
- PhilHealth: ₱400 / 2 = ₱200 (BOTH_SPLIT)
- GSIS Premium: ₱700 / 2 = ₱350 (BOTH_SPLIT)
- GSIS_CONSO: ₱0 (doesn't have this loan)
- PAGIBIG_MPL: ₱1,000 / 2 = ₱500 (BOTH_SPLIT)
- LBP: ₱0 (doesn't have this loan)
- UCPB: ₱2,500 (BOTH_FULL - full amount on 1st)

Total: ₱3,550
```

## Code Flow

### Step 1: Query Employee Deductions
```php
'employee.deductions' => function($q) use ($startDate, $endDate) {
    $q->where('status', 'ACTIVE')
      ->where('start_date', '<=', $endDate)
      ->where(function($query) use ($endDate) {
          $query->whereNull('end_date')->orWhere('end_date', '>=', $endDate);
      })
      ->with('deductionType.schedules');
}
```

**Result:**
- Juan: Returns 5 deductions
- Jeremy: Returns 2 deductions
- Maria: Returns 4 deductions

### Step 2: Calculate Per Employee
```php
foreach ($employee->deductions as $deduction) {
    // Only loops through deductions THIS employee has
    
    // Get schedule (custom or default)
    $cutoffSchedule = $deduction->custom_cutoff_schedule 
        ?? $deductionType->schedules->first()->cutoff_schedule 
        ?? 'BOTH_SPLIT';
    
    // Calculate amount
    $deductionAmount = /* calculation based on type */;
    
    // Apply cutoff schedule
    $deductions[$code] = /* amount based on schedule */;
}
```

**Result:**
- Juan's `$deductions` array has 5 entries
- Jeremy's `$deductions` array has 2 entries
- Maria's `$deductions` array has 4 entries

### Step 3: Collect All Unique Deduction Types
```php
$deductionTypes = collect();
foreach ($payrollRecords as $record) {
    if (isset($record['deductions'])) {
        foreach (array_keys($record['deductions']) as $code) {
            if (!$deductionTypes->contains($code)) {
                $deductionTypes->push($code);
            }
        }
    }
}
```

**Result:**
```php
$deductionTypes = [
    'PHILHEALTH',
    'GSIS_PREMIUM',
    'GSIS_CONSO',
    'PAGIBIG_MPL',
    'LBP',
    'UCPB'
]
```

### Step 4: Render Table with All Columns
```blade
@foreach($deductionTypes as $code)
    <th>{{ $deductionTypeNames[$code] ?? $code }}</th>
@endforeach
```

**Result:** 6 deduction columns created

### Step 5: Display Values (with fallback to 0)
```blade
@foreach($deductionTypes as $code)
    <td class="deduction">{{ peso($record['deductions'][$code] ?? 0) }}</td>
@endforeach
```

**Result:**
- Juan: Shows actual values for his 5 deductions, ₱0 for UCPB
- Jeremy: Shows actual values for his 2 deductions, ₱0 for 4 loans
- Maria: Shows actual values for her 4 deductions, ₱0 for GSIS_CONSO and LBP

## Export Behavior

### CSV Export
```csv
Employee,PhilHealth,GSIS Premium,GSIS_CONSO,PAGIBIG_MPL,LBP,UCPB,Total Deductions,Net Pay
Juan Dela Cruz,225.00,400.00,3000.00,0.00,750.00,0.00,4375.00,10625.00
Jeremy Pogi,150.00,300.00,0.00,0.00,0.00,0.00,450.00,11550.00
Maria Santos,200.00,350.00,0.00,500.00,0.00,2500.00,3550.00,10450.00
```

### Key Points
1. **All columns included** even if some employees don't have them
2. **Zero values** shown for missing deductions
3. **Easy to compare** across employees
4. **Totals are accurate** per employee

## Filtering Behavior

### Filter by Employee
If you filter to show only "Juan Dela Cruz":
- Columns shown: PhilHealth, GSIS Premium, GSIS_CONSO, PAGIBIG_MPL, LBP
- UCPB column NOT shown (Juan doesn't have it)

### Filter by Department
If you filter to show "Municipal Health Office" (Juan and Maria):
- Columns shown: All 6 deduction types
- Jeremy's data not included

## Database Queries

### Efficient Loading
```php
// Single query with eager loading
$query = DailySalaryComputation::with([
    'employee.deductions' => function($q) {
        $q->where('status', 'ACTIVE')
          ->with('deductionType.schedules');
    }
])
```

**Result:**
- No N+1 query problem
- All deductions loaded in one go
- Efficient even with 100+ employees

## Edge Cases Handled

### 1. Employee with No Loans
**Jeremy Pogi**: Only mandatory deductions
- System shows ₱0 for all loan columns
- Total deductions = sum of mandatory only

### 2. Employee with All Loans
**Juan Dela Cruz**: Has most loans
- System calculates each individually
- Respects custom schedules per loan

### 3. New Loan Added Mid-Period
**Scenario**: Maria gets a new GSIS loan on Jan 15
- Payroll for Jan 1-15: No GSIS_CONSO column (she didn't have it)
- Payroll for Jan 16-31: GSIS_CONSO column appears

### 4. Loan Completed
**Scenario**: Juan finishes LBP loan on Jan 20
- Status changed to 'COMPLETED'
- No longer included in active deductions query
- LBP column disappears from future payrolls

## Summary

### How It Works
1. ✅ **Query**: Fetch only ACTIVE deductions per employee
2. ✅ **Calculate**: Process only deductions each employee has
3. ✅ **Collect**: Gather all unique deduction types across all employees
4. ✅ **Display**: Show all columns, use ₱0 for missing deductions
5. ✅ **Export**: Include all columns for consistency

### Benefits
- ✅ **Flexible**: Each employee can have different loans
- ✅ **Accurate**: Only calculates what each employee actually has
- ✅ **Clear**: Easy to see who has what
- ✅ **Consistent**: Same columns for all employees in a period
- ✅ **Efficient**: No unnecessary calculations

### No Manual Work Needed
The system automatically:
- Detects which deductions each employee has
- Creates appropriate columns
- Calculates correct amounts
- Shows zeros where needed
- Exports complete data
