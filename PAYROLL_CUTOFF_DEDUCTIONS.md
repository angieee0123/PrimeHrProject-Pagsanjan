# Payroll Register - Cutoff-Based Deductions & Loan Display

## What Changed

The payroll register now displays deductions based on cutoff schedules (1st or 2nd half of the month) and properly shows all loan deductions.

## Key Features

### 1. Cutoff Schedule Detection
- Automatically detects if the payroll period is for the 1st cutoff (days 1-15) or 2nd cutoff (days 16-31)
- Based on the start date of the payroll period

### 2. Deduction Schedule Types
Each deduction (including loans) can have one of these schedules:

- **1ST_ONLY** - Deducted only on 1st cutoff
- **2ND_ONLY** - Deducted only on 2nd cutoff  
- **BOTH_FULL** - Full amount deducted on both cutoffs
- **BOTH_SPLIT** - Amount split in half for each cutoff (default)

### 3. Loan Deductions
All active loans are now displayed in the payroll register:
- GSIS loans (GSIS_CONSO, GFAL)
- Pag-IBIG loans (PAGIBIG_MPL)
- Bank loans (LBP, UCPB)
- Custom/Other loans

Each loan respects its cutoff schedule configuration.

## How It Works

### For Monthly/Employee View:
```php
// Example: GSIS loan with ₱5,000 monthly installment
// Schedule: BOTH_SPLIT

1st Cutoff: ₱2,500 deducted
2nd Cutoff: ₱2,500 deducted
Total: ₱5,000/month
```

### For Daily View:
Deductions are prorated per day based on 22 working days and cutoff schedule.

## Deduction Columns Displayed

The payroll register dynamically shows columns for:
- Mandatory deductions (SSS, GSIS, PhilHealth, Pag-IBIG, Tax)
- All active loan deductions (GSIS loans, Pag-IBIG loans, bank loans, etc.)
- Late and undertime deductions
- Total deductions
- Net pay

## Example Scenarios

### Scenario 1: Employee with GSIS Loan
**Loan Details:**
- Type: GSIS_CONSO
- Monthly: ₱3,000
- Schedule: BOTH_SPLIT

**Payroll Register Display:**
- 1st Cutoff (Jan 1-15): Shows ₱1,500 under GSIS_CONSO column
- 2nd Cutoff (Jan 16-31): Shows ₱1,500 under GSIS_CONSO column

### Scenario 2: Employee with Multiple Loans
**Loans:**
- GSIS_CONSO: ₱3,000/month (BOTH_SPLIT)
- PAGIBIG_MPL: ₱2,000/month (1ST_ONLY)
- LBP: ₱1,500/month (2ND_ONLY)

**1st Cutoff Display:**
- GSIS_CONSO: ₱1,500
- PAGIBIG_MPL: ₱2,000
- LBP: ₱0
- Total Loan Deductions: ₱3,500

**2nd Cutoff Display:**
- GSIS_CONSO: ₱1,500
- PAGIBIG_MPL: ₱0
- LBP: ₱1,500
- Total Loan Deductions: ₱3,000

## Configuration

To set deduction schedules, use the Deductions module:
1. Go to Admin → Deductions → Schedules tab
2. Select employee
3. Configure cutoff schedule for each deduction/loan
4. Schedules are automatically applied in payroll generation

## Technical Implementation

### Files Modified:
- `routes/web.php` - Added cutoff detection and schedule logic
- Payroll register route now includes `deductionType.schedules` relationship

### Key Logic:
```php
// Detect cutoff
$startDay = (int) date('d', strtotime($startDate));
$isCutoff1st = $startDay <= 15;

// Apply schedule
if ($cutoffSchedule === '1ST_ONLY') {
    $deductions[$code] = $isCutoff1st ? $deductionAmount : 0;
} elseif ($cutoffSchedule === '2ND_ONLY') {
    $deductions[$code] = $isCutoff1st ? 0 : $deductionAmount;
} elseif ($cutoffSchedule === 'BOTH_FULL') {
    $deductions[$code] = $deductionAmount;
} else { // BOTH_SPLIT
    $deductions[$code] = $deductionAmount / 2;
}
```

## Benefits

1. **Accurate Deductions** - Respects cutoff schedules configured per deduction type
2. **Loan Visibility** - All loans are clearly displayed with their amounts
3. **Flexible Scheduling** - Different deductions can have different cutoff schedules
4. **Automatic Calculation** - No manual intervention needed once schedules are set

## Notes

- Default schedule is BOTH_SPLIT if no schedule is configured
- Only employee shares are deducted (employer shares are excluded)
- Schedules can be updated anytime in the Deductions module
- Changes apply to future payroll generations
