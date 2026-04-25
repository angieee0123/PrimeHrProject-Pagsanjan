# Attendance Correction Feature

## Overview
Admin users can now correct employee attendance time records when biometric failures occur. All corrections require supporting documentation (PDF/images) and a reason.

## Features Implemented

### 1. Database Structure
- **Table**: `attendance_corrections`
- **Fields**:
  - Old values (am_in, am_out, pm_in, pm_out, ot_in, ot_out)
  - New values (am_in, am_out, pm_in, pm_out, ot_in, ot_out)
  - Reason for correction
  - Attachments (JSON array of file paths)
  - Corrected by (admin user ID)
  - Timestamps

### 2. File Upload
- **Accepted formats**: PDF, JPG, JPEG, PNG
- **Max file size**: 5MB per file
- **Multiple files**: Yes (one or more required)
- **Storage location**: `storage/app/public/attendance_corrections/`

### 3. User Interface
- **Edit button** added to each row in Detailed DTR modal
- **Correction modal** with:
  - Time input fields (AM In/Out, PM In/Out, OT In/Out)
  - Reason textarea (required)
  - File upload input (required, multiple files)
  - File preview with icons
  - Save/Cancel buttons

### 4. Validation
- All time fields are optional (can be left blank)
- Reason is required (max 500 characters)
- At least one attachment file is required
- Files must be PDF, JPG, JPEG, or PNG format
- Each file must be under 5MB

### 5. Audit Trail
Every correction is logged with:
- Original values
- New values
- Reason for change
- Supporting documents
- Who made the correction
- When it was made

## How to Use

1. Navigate to **Admin > Attendance**
2. Click **Detailed DTR** for any employee
3. Click **Edit** button on any attendance record row
4. Fill in the correct time values
5. Enter a reason for the correction
6. Upload supporting documents (required)
7. Click **Save Correction**

## API Endpoints

### Get Attendance Record
```
GET /admin/attendance/record/{attendanceId}
```
Returns the current attendance record data for editing.

### Submit Correction
```
POST /admin/attendance/correct
```
**Parameters**:
- attendance_id (required)
- date (required)
- am_in, am_out, pm_in, pm_out, ot_in, ot_out (optional)
- reason (required, max 500 chars)
- attachments[] (required, multiple files)

## Total Hours Calculation

The system now calculates total hours using:
```
Total Hours = (PM_Out - AM_In) - 60min_Break + Overtime - Late - Undertime
```

This handles cases where AM Out and PM In logs are missing (lunch break not recorded).

## Security
- Only authenticated admin users can access correction features
- All corrections are logged with admin user ID
- Original values are preserved in corrections table
- File uploads are validated and stored securely

## Notes
- Corrections are applied immediately to the attendance record
- Original values are preserved in the corrections history
- The Edit button only appears for records that have attendance data
- Weekend and absent days show "—" instead of Edit button
