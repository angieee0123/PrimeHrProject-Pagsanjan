# Admin Quick Guide: Attendance Exemptions

## What Are Attendance Exemptions?

Attendance exemptions allow you to configure which employees, departments, or positions should NOT be flagged as "Abandoned" or "Incomplete" in their Daily Time Records (DTR), even if they have irregular time entries.

## When to Use This Feature

Use attendance exemptions for:
- ✅ **Field workers** who only clock in at the start and end of their shift
- ✅ **Flexible schedule employees** who may have incomplete time entries
- ✅ **Special assignments** with non-standard attendance requirements
- ✅ **Entire departments** with flexible work policies
- ✅ **Specific positions** (e.g., all Managers, all Field Officers)

## Quick Start Guide

### Step 1: Access the Configuration
1. Log in as Admin
2. Go to **Attendance** page
3. Click the **"Attendance Config"** tab (third tab)

### Step 2: Add an Exemption

#### For a Single Employee:
1. Click **"Add Exemption"** button
2. Select **"Employee"** from the dropdown
3. Choose the employee's name
4. Check the boxes for:
   - ☑ Exempt from "Abandoned" flag
   - ☑ Exempt from "Incomplete" flag
5. Enter a reason (e.g., "Field worker - flexible schedule")
6. Click **"Save Exemption"**

#### For an Entire Department:
1. Click **"Add Exemption"** button
2. Select **"Department"** from the dropdown
3. Choose the department name
4. Check the appropriate exemption boxes
5. Enter a reason (e.g., "Department has flexible work arrangements")
6. Click **"Save Exemption"**

#### For a Position/Designation:
1. Click **"Add Exemption"** button
2. Select **"Designation"** from the dropdown
3. Choose the position (e.g., "Manager", "Field Officer")
4. Check the appropriate exemption boxes
5. Enter a reason (e.g., "All managers have flexible schedules")
6. Click **"Save Exemption"**

### Step 3: Manage Exemptions

#### To Edit an Exemption:
1. Find the exemption in the table
2. Click the **pencil icon** (Edit button)
3. Make your changes
4. Click **"Save Exemption"**

#### To Delete an Exemption:
1. Find the exemption in the table
2. Click the **trash icon** (Delete button)
3. Confirm the deletion

## Understanding the Flags

### "Abandoned" Flag
**What it means**: Employee clocked in but never clocked out or returned
- Example: Only has AM IN (8:00 AM) but no AM OUT, PM IN, or PM OUT
- **When to exempt**: Field workers who only clock in at start and end of day

### "Incomplete" Flag
**What it means**: Employee has partial attendance records
- Example: Has AM IN and AM OUT, but no PM IN and PM OUT
- **When to exempt**: Part-time workers, flexible schedules, or special work arrangements

## Common Scenarios

### Scenario 1: Field Worker
**Problem**: Maria is a field worker who clocks in at 8:00 AM and clocks out at 5:00 PM. She doesn't clock out for lunch or clock back in. The system marks her as "Abandoned".

**Solution**:
1. Add exemption for Maria (Employee type)
2. Check both "Abandoned" and "Incomplete" boxes
3. Reason: "Field worker - only required to clock in/out at start and end of day"

### Scenario 2: IT Department Flexible Schedule
**Problem**: The entire IT Department has flexible work hours and often has incomplete time entries.

**Solution**:
1. Add exemption for "IT Department" (Department type)
2. Check "Incomplete" box
3. Reason: "Flexible work arrangement - department policy"

### Scenario 3: All Managers
**Problem**: All employees with "Manager" position have flexible schedules and shouldn't be flagged.

**Solution**:
1. Add exemption for "Manager" (Designation type)
2. Check both boxes
3. Reason: "Management positions have flexible work schedules"

## Important Notes

⚠️ **What Exemptions DO**:
- Prevent "Abandoned" and "Incomplete" status labels from appearing
- Allow flexible attendance tracking for specific employees/groups

⚠️ **What Exemptions DON'T DO**:
- Change the actual time records or hours worked
- Affect salary calculations or deductions
- Hide the attendance records (they're still visible in DTR)

## Viewing the Results

After adding an exemption:
1. Go to the **"Detailed Time Record"** tab
2. Find the exempted employee
3. Check their attendance records
4. Verify they are NOT marked as "Abandoned" or "Incomplete"

## Tips for Best Practices

1. **Always provide a reason**: This helps track why exemptions were created
2. **Use department exemptions** when possible: Easier to manage than individual exemptions
3. **Review regularly**: Periodically check if exemptions are still needed
4. **Document your policy**: Keep a record of which positions/departments have exemptions and why

## Troubleshooting

### Problem: Employee still showing as "Abandoned"
**Solution**: 
- Check if the exemption was saved correctly
- Verify the exemption type matches (Employee/Department/Designation)
- Refresh the attendance page

### Problem: Can't find employee in dropdown
**Solution**:
- Make sure the employee exists in the system
- Check if you selected the correct exemption type

### Problem: Exemption not working for entire department
**Solution**:
- Verify the employee is actually in that department
- Check the employee's employment details

## Need Help?

If you encounter issues:
1. Check the exemptions table to verify the exemption exists
2. Verify the employee's department and position in their profile
3. Contact your system administrator

---

**Last Updated**: May 24, 2026
**Feature Version**: 1.0
