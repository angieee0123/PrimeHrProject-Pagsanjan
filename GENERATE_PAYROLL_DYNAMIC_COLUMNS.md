# Generate Payroll - Dynamic Loan Columns

## What Changed

The **Generate Payroll** modal now displays **dynamic deduction columns** including all individual loans, just like the Payroll Register.

## Before vs After

### Before (Static Columns)
```
Employee | Basic Pay | OT Pay | Late | Undertime | SSS/GSIS | Loans | Total Deductions | Net Pay
```
- Only 2 deduction columns: "SSS/GSIS" and "Loans"
- All loans lumped together
- Can't see individual loan amounts

### After (Dynamic Columns)
```
Employee | Basic Pay | OT Pay | Late | Undertime | PhilHealth | GSIS Premium | GSIS_CONSO | PAGIBIG_MPL | LBP | Total Deductions | Net Pay
```
- Individual column for each deduction type
- Shows exact amount per deduction
- Easy to verify each loan amount
- Matches Payroll Register format

## Example Output

### Scenario: 3 Employees with Different Loans

**Juan Dela Cruz:**
- GSIS_CONSO: ₱3,000 (1ST_ONLY)
- PAGIBIG_MPL: ₱2,000 (2ND_ONLY)
- PhilHealth: ₱450 (BOTH_SPLIT)

**Jeremy Pogi:**
- PhilHealth: ₱300 (BOTH_SPLIT)
- (No loans)

**Maria Santos:**
- UCPB Loan: ₱2,500 (BOTH_FULL)
- PhilHealth: ₱400 (BOTH_SPLIT)

### Generate Payroll Modal (1st Cutoff)

| No. | Employee | Days | Basic Pay | OT | Late | UT | PhilHealth | GSIS_CONSO | PAGIBIG_MPL | UCPB | Total Ded | Net Pay |
|-----|----------|------|-----------|----|----|-----|-----------|------------|-------------|------|-----------|---------|
| 1 | Juan Dela Cruz | 11 | ₱15,000 | ₱0 | ₱0 | ₱0 | ₱225 | ₱3,000 | ₱0 | ₱0 | ₱3,225 | ₱11,775 |
| 2 | Jeremy Pogi | 11 | ₱12,000 | ₱0 | ₱0 | ₱0 | ₱150 | ₱0 | ₱0 | ₱0 | ₱150 | ₱11,850 |
| 3 | Maria Santos | 11 | ₱14,000 | ₱0 | ₱0 | ₱0 | ₱200 | ₱0 | ₱0 | ₱2,500 | ₱2,700 | ₱11,300 |
| **TOTAL** | | | **₱41,000** | **₱0** | **₱0** | **₱0** | **₱575** | **₱3,000** | **₱0** | **₱2,500** | **₱6,075** | **₱34,925** |

## Key Features

### 1. Dynamic Column Generation
- System scans all employees in the payroll
- Creates columns for ALL deduction types found
- Shows ₱0 for employees who don't have that deduction

### 2. Cutoff Schedule Aware
- Respects custom employee schedules
- Falls back to deduction type defaults
- Calculates correct amounts per cutoff

### 3. Clear Visibility
- See exactly which loans each employee has
- Verify individual loan amounts
- Easy to spot discrepancies

### 4. Consistent with Register
- Same logic as Payroll Register
- Same column structure
- Same calculations

## Technical Implementation

### Backend Changes (web.php)

#### Updated `/admin/payroll/calculate` Route
```php
// Collect all unique deduction types
$allDeductionTypes = collect();

foreach ($employees as $employee) {
    // Calculate deductions with cutoff schedule
    foreach ($employee->deductions as $deduction) {
        // ... calculation logic ...
        
        // Collect unique types
        if (!$allDeductionTypes->contains($code)) {
            $allDeductionTypes->push($code);
        }
    }
    
    // Store deductions array per employee
    $payrollData[] = [
        // ... other fields ...
        'deductions' => $deductions, // Array of code => amount
    ];
}

// Return deduction types and names
return response()->json([
    'data' => [
        'employees' => $payrollData,
        'deduction_types' => $allDeductionTypes->toArray(),
        'deduction_names' => $deductionTypeNames,
    ]
]);
```

### Frontend Changes (payroll-result-modal.blade.php)

#### Dynamic Header Generation
```javascript
const deductionTypes = data.deduction_types || [];
const deductionNames = data.deduction_names || {};

thead.innerHTML = `
    <tr>
        <th rowspan="2">No.</th>
        <!-- ... other headers ... -->
        <th colspan="${2 + deductionTypes.length}">Deductions</th>
        <!-- ... -->
    </tr>
    <tr>
        <th>Late</th>
        <th>Undertime</th>
        ${deductionTypes.map(code => 
            `<th>${deductionNames[code] || code}</th>`
        ).join('')}
    </tr>
`;
```

#### Dynamic Row Generation
```javascript
data.employees.forEach((emp, index) => {
    // Build deduction columns
    const deductionCells = deductionTypes.map(code => {
        const amount = emp.deductions[code] || 0;
        return `<td class="text-right">₱${amount.toLocaleString()}</td>`;
    }).join('');
    
    row.innerHTML = `
        <!-- ... other cells ... -->
        ${deductionCells}
        <!-- ... -->
    `;
});
```

#### Dynamic Footer Totals
```javascript
// Calculate totals per deduction type
deductionTypes.forEach(code => {
    totals.deductions[code] = 0;
});

data.employees.forEach(emp => {
    deductionTypes.forEach(code => {
        totals.deductions[code] += emp.deductions[code] || 0;
    });
});

// Build footer cells
const deductionTotalCells = deductionTypes.map(code => {
    return `<td>₱${totals.deductions[code].toLocaleString()}</td>`;
}).join('');
```

## Benefits

### For HR Staff
✅ **Verify Individual Loans** - See each loan amount clearly
✅ **Spot Errors Quickly** - Easy to identify incorrect deductions
✅ **Match with Records** - Compare with loan agreements
✅ **Audit Trail** - Clear breakdown for auditing

### For Employees
✅ **Transparency** - See exactly what's being deducted
✅ **Verify Amounts** - Check against loan schedules
✅ **Understand Payslip** - Clear breakdown of deductions

### For System
✅ **Consistency** - Same logic everywhere
✅ **Flexibility** - Handles any number of loans
✅ **Accuracy** - Respects cutoff schedules
✅ **Scalability** - Works with 1 or 100 deduction types

## Example Scenarios

### Scenario 1: Employee with Multiple Loans
**Juan has 3 loans:**
- GSIS_CONSO: ₱3,000/month
- PAGIBIG_MPL: ₱2,000/month
- LBP: ₱1,500/month

**Modal shows:**
- 3 separate columns for each loan
- Individual amounts per loan
- Total of all loans
- Easy to verify each amount

### Scenario 2: Employees with Different Loans
**Department has 10 employees:**
- 5 have GSIS loans
- 3 have Pag-IBIG loans
- 2 have bank loans
- All have PhilHealth

**Modal shows:**
- All loan types as columns
- ₱0 for employees without specific loans
- Clear comparison across employees

### Scenario 3: New Loan Added
**Maria gets a new GSIS loan mid-month:**
- Previous payrolls: No GSIS column
- Current payroll: GSIS column appears
- System automatically detects and adds column

## Comparison with Payroll Register

Both now use the **same logic**:

| Feature | Payroll Register | Generate Payroll Modal |
|---------|-----------------|----------------------|
| Dynamic Columns | ✅ Yes | ✅ Yes |
| Individual Loans | ✅ Yes | ✅ Yes |
| Cutoff Schedules | ✅ Yes | ✅ Yes |
| Custom Schedules | ✅ Yes | ✅ Yes |
| Zero Values | ✅ Yes | ✅ Yes |
| Totals | ✅ Yes | ✅ Yes |

## Testing

### Test Case 1: Single Employee, Multiple Loans
1. Generate payroll for employee with 3 loans
2. Verify 3 loan columns appear
3. Verify amounts match loan agreements
4. Verify total is correct

### Test Case 2: Multiple Employees, Different Loans
1. Generate payroll for 5 employees
2. Employee A has GSIS only
3. Employee B has Pag-IBIG only
4. Employee C has both
5. Verify all columns appear
6. Verify ₱0 for missing loans

### Test Case 3: Cutoff Schedule
1. Set GSIS loan to 1ST_ONLY
2. Generate 1st cutoff payroll
3. Verify GSIS amount shows
4. Generate 2nd cutoff payroll
5. Verify GSIS shows ₱0

### Test Case 4: Custom Schedule
1. Set employee's GSIS to custom 2ND_ONLY
2. Generate 1st cutoff payroll
3. Verify employee's GSIS shows ₱0
4. Verify other employees' GSIS shows amount (if default is BOTH_SPLIT)

## Notes

- **No Manual Configuration** - System detects automatically
- **Responsive Layout** - Table scrolls horizontally if many columns
- **Export Compatible** - Export includes all columns
- **Performance** - Efficient even with many deduction types
- **Backward Compatible** - Works with existing data

## Future Enhancements

Potential improvements:
- Group deductions by category (Mandatory, Loans, Other)
- Color-code loan columns
- Show loan balance remaining
- Highlight custom schedules
- Add deduction schedule indicator
