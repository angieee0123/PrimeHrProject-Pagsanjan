# Quick Reference: Employee vs Employer Deductions

## 🎯 Feature Summary
Separate employee deductions (deducted from salary) from employer/government shares (record-keeping only).

---

## 📋 How to Use

### Step 1: Create Deduction Types

#### For Employee Shares (Deducted from Salary)
```
Code: GSIS_EMP
Name: GSIS Employee Contribution
Category: MANDATORY
Computation Type: PERCENTAGE
Rate: 9%
Deduction Type: ✅ Employee Share (Deducted from salary)
```

#### For Employer Shares (Record-Keeping Only)
```
Code: GSIS_GOV
Name: GSIS Government Contribution
Category: MANDATORY
Computation Type: PERCENTAGE
Rate: 12%
Deduction Type: ✅ Employer/Government Share (Record-keeping only)
```

### Step 2: Assign to Employees
- Go to **Deductions** → **Employee Deductions**
- Assign both types to permanent employees
- Both will appear in the employee's deduction list

### Step 3: Generate Payroll
- Go to **Payroll** → **Generate Payroll**
- Select date range and generate
- **Result**: Only employee shares will be deducted!

---

## 🔍 Where It Works

| Location | Behavior |
|----------|----------|
| **Payroll Register** | Shows only employee shares |
| **Payroll Export (CSV)** | Includes only employee share columns |
| **Net Pay Calculation** | Deducts only employee shares |
| **Deduction Schedules** | Shows only employee shares |
| **Payroll Preview** | Calculates only employee shares |

---

## 💡 Common Use Cases

### 1. GSIS (Government Service Insurance System)
- **Employee Share**: 9% → Deducted ✅
- **Government Share**: 12% → NOT Deducted ❌

### 2. PhilHealth
- **Employee Share**: 2.5% → Deducted ✅
- **Government Share**: 2.5% → NOT Deducted ❌

### 3. Pag-IBIG
- **Employee Share**: 2% → Deducted ✅
- **Government Share**: 2% → NOT Deducted ❌

### 4. Loans (Always Employee)
- **GSIS Loan**: Employee pays → Deducted ✅
- **Pag-IBIG Loan**: Employee pays → Deducted ✅

---

## 📊 Visual Example

### Employee: Juan Dela Cruz
**Monthly Salary**: ₱30,000

#### Deductions Setup:
| Deduction | Type | Rate | Amount | Deducted? |
|-----------|------|------|--------|-----------|
| GSIS Employee | Employee Share | 9% | ₱2,700 | ✅ YES |
| GSIS Government | Employer Share | 12% | ₱3,600 | ❌ NO |
| PhilHealth Employee | Employee Share | 2.5% | ₱750 | ✅ YES |
| PhilHealth Government | Employer Share | 2.5% | ₱750 | ❌ NO |
| Pag-IBIG Employee | Employee Share | 2% | ₱600 | ✅ YES |
| Pag-IBIG Government | Employer Share | 2% | ₱600 | ❌ NO |

#### Payroll Calculation:
```
Gross Pay:              ₱30,000.00
Less: GSIS Employee     - ₱2,700.00  ✅
Less: PhilHealth Emp    -   ₱750.00  ✅
Less: Pag-IBIG Emp      -   ₱600.00  ✅
                        ___________
NET PAY:                ₱26,050.00

Government Shares (NOT deducted from employee):
- GSIS Gov:             ₱3,600.00  ❌
- PhilHealth Gov:       ₱750.00    ❌
- Pag-IBIG Gov:         ₱600.00    ❌
Total Gov Share:        ₱4,950.00  (Paid by LGU)
```

---

## ✅ Benefits

1. **Accurate Payroll** - Employees only pay their share
2. **Compliance** - Follows government regulations
3. **Transparency** - Clear cost breakdown
4. **Record-Keeping** - Track all contributions
5. **Reporting** - Generate government remittance reports

---

## 🚨 Important Notes

- ⚠️ **Always set `deducted_from_employee` correctly** when creating deduction types
- ⚠️ **Employee shares** = Deducted from salary
- ⚠️ **Employer shares** = Paid by government, NOT deducted
- ⚠️ **Loans** = Always employee shares (deducted)
- ⚠️ **Review existing deductions** and update the flag if needed

---

## 🔧 Troubleshooting

### Problem: Deduction not appearing in payroll
**Solution**: Check if `deducted_from_employee = false`. If it's an employer share, this is correct behavior.

### Problem: Wrong amount deducted
**Solution**: Verify the deduction type is set to "Employee Share" not "Employer Share"

### Problem: Need to see employer shares
**Solution**: Employer shares are for record-keeping. Check the Deductions module to see all assigned deductions.

---

**Last Updated**: May 17, 2026
