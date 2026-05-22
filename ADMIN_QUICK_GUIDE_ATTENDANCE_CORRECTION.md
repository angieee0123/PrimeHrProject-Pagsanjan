# Admin Quick Guide: Attendance Correction & Leave Recalculation

## What's New?

When you correct an employee's attendance times (AM In, AM Out, PM In, PM Out), the system now **automatically recalculates** their leave balances. This ensures employees are not unfairly penalized or credited based on incorrect attendance records.

## How It Works

### Before (Old System)
- Admin corrects attendance time
- Old leave deduction remains
- Employee's leave balance is incorrect
- Manual adjustment needed

### Now (New System)
- Admin corrects attendance time
- System automatically:
  - ✅ Credits back old leave deduction
  - ✅ Calculates new deduction (if any)
  - ✅ Updates leave balance
  - ✅ Records everything in transaction history

## Step-by-Step Guide

### 1. Open Attendance Correction Modal
- Navigate to Admin → Attendance
- Find the employee's attendance record
- Click "Edit" or "Correct" button

### 2. Edit Attendance Times
- Update AM In, AM Out, PM In, PM Out as needed
- Provide a reason for the correction
- Attach supporting documents (if required)

### 3. Submit Correction
- Click "Submit" or "Save"
- System processes the correction
- Success message appears

### 4. Verify Changes
- Check employee's transaction history
- You'll see:
  - **Original deduction** (if there was one)
  - **Reversal credit** (cyan icon with circular arrow)
  - **New deduction** (if applicable)

## Example Scenarios

### Scenario 1: Employee Was Actually On Time

**Original Record:**
- Clock In: 8:30 AM (30 minutes late)
- Leave Deducted: 0.0625 days from VL

**Your Correction:**
- Clock In: 8:00 AM (on time)

**System Action:**
- ✅ Credits back 0.0625 days to VL
- ✅ No new deduction (employee was on time)
- ✅ Net result: Employee gains 0.0625 days VL

### Scenario 2: Late Time Was Less Than Recorded

**Original Record:**
- Clock In: 8:45 AM (45 minutes late)
- Leave Deducted: 0.09375 days from VL

**Your Correction:**
- Clock In: 8:15 AM (15 minutes late)

**System Action:**
- ✅ Credits back 0.09375 days to VL
- ✅ Deducts 0.03125 days from VL (15 minutes)
- ✅ Net result: Employee gains 0.0625 days VL

### Scenario 3: Late Time Was More Than Recorded

**Original Record:**
- Clock In: 8:15 AM (15 minutes late)
- Leave Deducted: 0.03125 days from VL

**Your Correction:**
- Clock In: 8:45 AM (45 minutes late)

**System Action:**
- ✅ Credits back 0.03125 days to VL
- ✅ Deducts 0.09375 days from VL (45 minutes)
- ✅ Net result: Employee loses 0.0625 days VL

## Understanding Transaction History

### Transaction Types You'll See

| Icon | Color | Type | Meaning |
|------|-------|------|---------|
| 🔄 | Cyan | Attendance Correction Reversal | Credits back previous deduction |
| 🕐 | Amber | Late Deduction | Deduction for late arrival |
| 🕐 | Red | Undertime Deduction | Deduction for early departure |
| 📅 | Purple | Leave Application | Approved leave usage |
| ✏️ | Purple | Manual Adjustment | HR manual adjustment |
| ✓ | Green | Monthly Accrual | Monthly leave credit |

### Reading Transaction Details

Each transaction shows:
- **Leave Type**: VL, SL, etc.
- **Transaction Type**: Credit (added) or Debit (deducted)
- **Amount**: Number of days (+ for credit, - for debit)
- **Balance Before**: Leave balance before transaction
- **Balance After**: Leave balance after transaction
- **Date**: When the transaction occurred
- **Source/Reason**: Why the transaction happened
- **Remarks**: Detailed explanation

## Important Notes

### ✅ Do's
- ✅ Always provide a clear reason for corrections
- ✅ Attach supporting documents when available
- ✅ Verify the corrected times are accurate
- ✅ Check transaction history after correction
- ✅ Inform employee of significant corrections

### ❌ Don'ts
- ❌ Don't make corrections without valid reason
- ❌ Don't correct attendance to favor employees unfairly
- ❌ Don't forget to document the reason
- ❌ Don't make multiple corrections unnecessarily
- ❌ Don't ignore system warnings or errors

## Frequently Asked Questions

### Q: What if I make a mistake in my correction?
**A:** You can correct the attendance again. The system will handle multiple corrections properly, reversing the previous correction and applying the new one.

### Q: Will employees see these changes?
**A:** Yes, employees can view their complete transaction history, including reversals and adjustments. All changes are transparent.

### Q: What if the employee doesn't have enough leave credits?
**A:** The system will:
1. Use available VL first
2. Use available SL if VL is insufficient
3. Mark remaining time as LWOP (Leave Without Pay)

### Q: Can I reverse a correction?
**A:** Yes, simply correct the attendance back to the original times. The system will reverse the correction and restore the original deduction.

### Q: How do I know if the recalculation worked?
**A:** Check the employee's transaction history. You should see:
- The reversal transaction (cyan icon)
- The new deduction (if any)
- Updated leave balance

### Q: What if I don't see the reversal transaction?
**A:** This could mean:
- There was no previous deduction to reverse
- The attendance record was new (first-time entry)
- Check the system logs or contact IT support

## Troubleshooting

### Issue: Correction doesn't save
**Solution:**
- Check all required fields are filled
- Ensure time format is correct (HH:MM)
- Verify you have permission to make corrections
- Check for system error messages

### Issue: Leave balance looks wrong
**Solution:**
- Review complete transaction history
- Verify all transactions are accounted for
- Check if there are pending leave applications
- Contact IT support if discrepancy persists

### Issue: Can't find the correction button
**Solution:**
- Ensure you're logged in as admin
- Check you have attendance correction permissions
- Verify you're on the correct attendance page
- Try refreshing the page

## Best Practices

1. **Document Everything**
   - Always provide detailed reasons
   - Attach supporting evidence
   - Keep records of corrections made

2. **Verify Before Submitting**
   - Double-check corrected times
   - Ensure dates are correct
   - Review impact on leave balance

3. **Communicate with Employees**
   - Inform employees of corrections
   - Explain the reason for changes
   - Address any concerns promptly

4. **Regular Audits**
   - Review corrections periodically
   - Check for patterns or issues
   - Ensure compliance with policies

5. **Training**
   - Stay updated on system features
   - Attend training sessions
   - Share knowledge with team

## Need Help?

- **Technical Issues**: Contact IT Support
- **Policy Questions**: Contact HR Department
- **System Training**: Request training session
- **Documentation**: Review full documentation in `ATTENDANCE_CORRECTION_LEAVE_RECALCULATION.md`

## Quick Reference

### Time Conversion
- 1 work day = 8 hours = 480 minutes
- 1 hour = 60 minutes = 0.125 days
- 30 minutes = 0.0625 days
- 15 minutes = 0.03125 days

### Leave Deduction Priority
1. VL (Vacation Leave) - deducted first
2. SL (Sick Leave) - deducted if VL insufficient
3. LWOP - applied if both insufficient

### Common Corrections
- **Wrong clock-in time**: Update AM In
- **Wrong clock-out time**: Update AM Out / PM Out
- **Missing punch**: Add missing time
- **Duplicate entry**: Correct to actual time

---

**Last Updated:** May 22, 2026  
**Version:** 1.0  
**For:** Admin Users
