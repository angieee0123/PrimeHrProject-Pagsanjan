# Quick Guide: How to Generate Payroll

## 🚀 Quick Start (3 Steps)

### Step 1: Configure Period
1. Go to **Payroll** → **Generate Payroll** tab
2. Fill in:
   - **Start Date**: First day of payroll period (e.g., Jan 1)
   - **End Date**: Last day of payroll period (e.g., Jan 15)
   - **Pay Date**: When employees will be paid
   - **Payroll Type**: Regular, 13th Month, Bonus, or Special

### Step 2: Preview & Verify
1. Click **"Generate Payroll"** button
2. Review the payroll summary modal:
   - Check employee list
   - Verify amounts (basic pay, deductions, net pay)
   - Review totals at the bottom
3. Optional: Click **"Export to Excel"** to save a copy

### Step 3: Save to Database
1. Click **"Confirm & Save"** button
2. Wait for success message
3. Click **"View Records"** to see the payslips

**Done!** Payslips are now saved and visible to employees.

---

## 📋 Common Scenarios

### Scenario 1: Generate 1st Cutoff Payroll
```
Start Date: January 1, 2024
End Date: January 15, 2024
Pay Date: January 20, 2024
Payroll Type: Regular Payroll
Department: All Departments
Employment Status: All Status
```

### Scenario 2: Generate 2nd Cutoff Payroll
```
Start Date: January 16, 2024
End Date: January 31, 2024
Pay Date: February 5, 2024
Payroll Type: Regular Payroll
Department: All Departments
Employment Status: All Status
```

### Scenario 3: Generate for Specific Department
```
Start Date: January 1, 2024
End Date: January 31, 2024
Pay Date: February 5, 2024
Payroll Type: Regular Payroll
Department: Municipal Health Office
Employment Status: All Status
```

### Scenario 4: Generate 13th Month Pay
```
Start Date: January 1, 2024
End Date: December 31, 2024
Pay Date: December 15, 2024
Payroll Type: 13th Month Pay
Department: All Departments
Employment Status: Permanent
```

---

## ⚙️ Payroll Options Explained

### ✅ Include Deductions
- **Checked**: SSS, PhilHealth, Pag-IBIG, Tax will be deducted
- **Unchecked**: No mandatory deductions applied

### ✅ Include Loan Deductions
- **Checked**: Active loans will be deducted
- **Unchecked**: Loans will not be deducted

### ✅ Include Overtime Pay
- **Checked**: Overtime hours will be paid
- **Unchecked**: Overtime will not be included

### ✅ Auto-approve after generation
- **Checked**: Payslips will be approved automatically
- **Unchecked**: Payslips will be saved as draft (requires manual approval)

---

## 🔍 Preview Summary Explained

The preview card on the right shows real-time estimates:

- **Employees**: Number of employees that will be included
- **Estimated Gross**: Total basic pay + OT pay
- **Estimated Deductions**: Total late + undertime + deductions
- **Estimated Net Pay**: Gross - Deductions

This updates automatically when you change dates or filters.

---

## 📊 Payroll Modal Columns

| Column | Description |
|--------|-------------|
| **No.** | Row number |
| **Employee Name** | Full name of employee |
| **Position** | Job title/designation |
| **Department** | Department/office |
| **Days Worked** | Number of days with attendance |
| **Daily Rate** | Monthly rate ÷ 22 |
| **Basic Pay** | Daily rate × days worked |
| **OT Pay** | Overtime hours × OT rate |
| **Late** | Late deduction amount |
| **Undertime** | Undertime deduction amount |
| **SSS/GSIS** | Social security contribution |
| **PhilHealth** | Health insurance contribution |
| **Pag-IBIG** | Housing fund contribution |
| **Tax** | Withholding tax |
| **Loans** | Active loan deductions |
| **Total Deductions** | Sum of all deductions |
| **Net Pay** | Take-home pay (Gross - Deductions) |

---

## ⚠️ Important Notes

### Before Generating Payroll:
1. ✅ Ensure attendance records are complete
2. ✅ Verify employee deductions are up-to-date
3. ✅ Check that daily salary computations exist
4. ✅ Review employee schedules are assigned

### After Generating Payroll:
1. ✅ Review the preview carefully before saving
2. ✅ Export to Excel for record-keeping
3. ✅ Verify totals match your expectations
4. ✅ Check payslips in the Payslips tab

### Cutoff Schedules:
- **1st Cutoff**: Days 1-15 of the month
- **2nd Cutoff**: Days 16-31 of the month
- Some deductions apply only to specific cutoffs
- Check deduction schedules in Deductions module

---

## 🐛 Troubleshooting

### "No employees found"
**Solution:** 
- Check if filters are too restrictive
- Verify employees have attendance for the period
- Ensure employees are active

### "No daily computations found"
**Solution:**
- Go to Attendance module
- Process attendance for the period
- Daily computations are created automatically

### Deductions not showing
**Solution:**
- Go to Deductions > Employee Deductions
- Verify employees have active deductions
- Check deduction start/end dates

### Wrong amounts
**Solution:**
- Verify employee monthly rates in Personnel
- Check deduction computation types
- Review cutoff schedules

---

## 📞 Need Help?

If you encounter issues:
1. Check the error message displayed
2. Review the troubleshooting section above
3. Check the detailed documentation: `PAYROLL_GENERATION_FIXED.md`
4. Contact system administrator

---

## ✨ Tips for Best Results

1. **Generate payroll at the end of each cutoff period**
2. **Always review the preview before saving**
3. **Export to Excel for your records**
4. **Process attendance before generating payroll**
5. **Keep employee deductions up-to-date**
6. **Use filters to process specific groups**
7. **Generate test payroll first with one department**

---

**Last Updated:** January 2024
**Status:** ✅ Fully Working
