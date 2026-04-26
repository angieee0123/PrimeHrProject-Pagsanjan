# QR Attendance System Setup Guide

## Overview
Static QR code-based attendance system for employee time in/out tracking.

## Installation

### 1. Install Dependencies
```bash
cd "GOVERNMENT CHATBOT/4. web application"
pip install opencv-python qrcode Pillow mysql-connector-python
```

**Note**: We use OpenCV's built-in QRCodeDetector (no external DLLs needed on Windows).

### 2. Database Setup
Run the SQL file to create attendance table:
```sql
mysql -u root -p primehrismagdalena < ../../database/primehrismagdalena_attendance.sql
```

### 3. Environment Variables (Optional)
Create `.env` file in `4. web application` folder:
```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=primehrismagdalena
```

## Usage

### Start the Server
```bash
python app.py
```

### Access QR Scanner
Navigate to: `http://localhost:5000/attendance`

### Generate Employee QR Codes
```bash
python generate_employee_qr.py
```
This will create QR codes for all employees in `qr_codes/` folder.

## How It Works

1. **QR Code Generation**: Each employee gets a unique QR code containing their employee ID
2. **Scanning**: Camera scans QR code → extracts employee ID
3. **Time Periods**:
   - **AM**: 6:00 AM - 12:00 PM (am_in, am_out)
   - **PM**: 12:00 PM - 5:00 PM (pm_in, pm_out)
   - **OT**: 5:00 PM onwards (ot_in, ot_out)
4. **Smart Detection**: System automatically determines which field to update based on current time
5. **Database**: All records stored in `attendance` table with separate columns for each period

## API Endpoints

### Scan QR Code
- **URL**: `/scan_qr`
- **Method**: POST
- **Body**: FormData with `qr_image` file
- **Response**:
```json
{
  "success": true,
  "action": "AM TIME IN",
  "employee": {
    "name": "John Doe",
    "position": "Developer",
    "time": "08:30 AM"
  }
}
```

**Possible Actions**:
- `AM TIME IN` - Morning time in (6:00 AM - 12:00 PM)
- `AM TIME OUT` - Morning time out
- `PM TIME IN` - Afternoon time in (12:00 PM - 5:00 PM)
- `PM TIME OUT` - Afternoon time out
- `OT TIME IN` - Overtime time in (5:00 PM onwards)
- `OT TIME OUT` - Overtime time out

### Generate QR Code
- **URL**: `/generate_qr/<employee_id>`
- **Method**: GET
- **Response**:
```json
{
  "qr_code": "data:image/png;base64,..."
}
```

## Features

✅ Static QR codes (no expiration)
✅ Camera-based scanning
✅ Automatic time in/out detection
✅ Real-time feedback
✅ Mobile-friendly interface
✅ Database integration

## Security Notes

- QR codes are static and linked to employee ID
- Consider adding location verification for enhanced security
- Implement admin dashboard for attendance monitoring
- Add manual override for admins

## Troubleshooting

**Camera not working?**
- Ensure HTTPS or localhost
- Grant camera permissions in browser
- Check if camera is being used by another app

**QR not scanning?**
- Ensure good lighting
- Hold QR code steady
- Try different distances from camera

**Database errors?**
- Verify database credentials
- Check if attendance table exists
- Ensure employees table has data
