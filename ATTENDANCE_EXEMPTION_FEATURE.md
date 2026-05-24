# Attendance Exemption Configuration Feature

## Overview
This feature allows administrators to configure exemptions for employees, departments, or designations that should not be flagged as "Abandoned" or "Incomplete" in their Daily Time Records (DTR) due to the nature of their work.

## Use Cases
- **Field Workers**: Employees who work in the field and may only clock in AM IN and PM OUT
- **Flexible Schedules**: Personnel with non-standard work arrangements
- **Special Assignments**: Employees on special duty or assignments with different attendance requirements
- **Department-wide Policies**: Entire departments with flexible work arrangements
- **Position-based Rules**: Specific positions/designations that require flexible attendance tracking

## How It Works

### 1. Access the Configuration
1. Navigate to **Admin > Attendance**
2. Click on the **"Attendance Config"** tab
3. You'll see a list of all configured exemptions

### 2. Add an Exemption
1. Click the **"Add Exemption"** button
2. Select the **Exemption Type**:
   - **Employee**: Apply exemption to a specific employee
   - **Department**: Apply exemption to all employees in a department
   - **Designation**: Apply exemption to all employees with a specific position/title
3. Select the specific **Employee/Department/Designation** from the dropdown
4. Choose which flags to exempt:
   - ☑ **Exempt from "Abandoned" flag**: Employee won't be marked as abandoned for having only AM IN without AM OUT and PM IN
   - ☑ **Exempt from "Incomplete" flag**: Employee won't be marked as incomplete for missing some time entries
5. Provide a **Reason** for the exemption (optional but recommended)
6. Click **"Save Exemption"**

### 3. Edit an Exemption
1. Click the **Edit** button (pencil icon) next to the exemption
2. Modify the settings as needed
3. Click **"Save Exemption"**

### 4. Delete an Exemption
1. Click the **Delete** button (trash icon) next to the exemption
2. Confirm the deletion

## Technical Details

### Database Structure
The exemptions are stored in the `attendance_exemptions` table with the following fields:
- `exemption_type`: employee, department, or designation
- `reference_id`: ID of the employee, department, or position name for designation
- `reference_name`: Display name for easy identification
- `exempt_from_abandoned`: Boolean flag
- `exempt_from_incomplete`: Boolean flag
- `reason`: Text explanation for the exemption
- `created_by`: User who created the exemption

### Exemption Priority
The system checks exemptions in the following order:
1. **Employee-specific** exemptions (highest priority)
2. **Department-wide** exemptions
3. **Designation/Position** exemptions

If any level grants an exemption, the employee will not be flagged.

### Impact on Attendance Records
- **Abandoned Flag**: Normally triggered when an employee has only AM IN without AM OUT and PM IN
  - With exemption: The system will process the attendance normally without marking it as abandoned
  
- **Incomplete Flag**: Normally triggered when an employee has partial attendance (e.g., only AM or only PM)
  - With exemption: The system will process the attendance normally without marking it as incomplete

### API Endpoints
- `GET /admin/attendance/exemptions/options?type={type}` - Get dropdown options
- `GET /admin/attendance/exemptions/{id}` - Get single exemption
- `POST /admin/attendance/exemptions` - Create new exemption
- `PUT /admin/attendance/exemptions/{id}` - Update exemption
- `DELETE /admin/attendance/exemptions/{id}` - Delete exemption

## Example Scenarios

### Scenario 1: Field Worker
**Problem**: A field worker only clocks in at 8:00 AM and clocks out at 5:00 PM, without the AM OUT and PM IN entries.

**Solution**: 
1. Add an exemption for the employee
2. Check both "Exempt from Abandoned" and "Exempt from Incomplete"
3. Reason: "Field worker - works outside office, only required to clock in/out at start and end of day"

### Scenario 2: Entire Department with Flexible Schedule
**Problem**: The IT Department has flexible work arrangements and may have incomplete time entries.

**Solution**:
1. Add an exemption for the "IT Department"
2. Check "Exempt from Incomplete"
3. Reason: "Flexible work arrangement - department policy"

### Scenario 3: All Managers
**Problem**: All employees with "Manager" designation have flexible schedules.

**Solution**:
1. Add an exemption for the "Manager" designation
2. Check both exemption flags
3. Reason: "Management positions have flexible work schedules"

## Notes
- Exemptions do not affect the actual time calculation or accredited hours
- Exemptions only prevent the "Abandoned" and "Incomplete" status labels from appearing
- The attendance records are still tracked and visible in the detailed DTR
- Audit trail: All exemptions record who created them and when

## Migration
The feature uses the migration file: `2026_05_24_040836_create_attendance_exemptions_table.php`

If the table doesn't exist, run:
```bash
php artisan migrate
```
