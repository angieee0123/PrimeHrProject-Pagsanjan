# QR Attendance System - Complete Guide

## ✅ YES, Your System CAN Scan QR Codes Using Webcam!

Your QR attendance system is **fully functional** and ready to use with webcam scanning.

---

## 🎯 System Overview

### What It Does:
1. **Admin generates QR codes** for employees (via Laravel admin panel)
2. **Employees receive printed QR cards** with their unique ID
3. **Attendance terminal scans QR codes** using webcam (Python Flask app)
4. **System records attendance** automatically (AM/PM/OT periods)

---

## 🔧 Technical Architecture

### Frontend (attendance.html)
- **Webcam Access**: `navigator.mediaDevices.getUserMedia()`
- **QR Detection**: jsQR library (real-time scanning)
- **Auto-Scan**: Continuous scanning mode (no button clicks needed)
- **Visual Feedback**: Success/error messages with employee info

### Backend (qr_attendance.py)
- **Image Processing**: OpenCV + pyzbar
- **QR Decoding**: Extracts employee ID from QR code
- **Database**: MySQL with attendance table
- **Time Logic**: Smart AM/PM/OT detection

### Admin Panel (Laravel)
- **QR Generation**: QRCode.js (client-side)
- **Download/Print**: PNG export with employee details
- **Integration**: Seamless with existing personnel management

---

## 📋 How It Works Step-by-Step

### 1. Admin Generates QR Code
```
Admin Panel → Personnel → Click "QR Code" button
→ Modal opens with QR code
→ Download or Print
→ Give to employee
```

### 2. Employee Uses QR Code
```
Employee arrives at attendance terminal
→ Holds QR code in front of webcam
→ System auto-detects and scans
→ Attendance recorded instantly
→ Success message displayed
```

### 3. System Records Attendance
```
QR Code scanned → Extract employee ID
→ Check current time
→ Determine period (AM/PM/OT)
→ Update database
→ Show confirmation
```

---

## ⏰ Time Period Logic

| Period | Time Range | Fields |
|--------|------------|--------|
| **AM** | 6:00 AM - 12:00 PM | `am_in`, `am_out` |
| **PM** | 12:00 PM - 5:00 PM | `pm_in`, `pm_out` |
| **OT** | 5:00 PM onwards | `ot_in`, `ot_out` |

### Rules:
- ✅ First scan of the day = AM TIME IN
- ✅ Second scan before 12 PM = AM TIME OUT
- ✅ Third scan after 12 PM = PM TIME IN
- ✅ Fourth scan before 5 PM = PM TIME OUT
- ✅ Fifth scan after 5 PM = OT TIME IN
- ✅ Sixth scan = OT TIME OUT

---

## 🚀 Setup Instructions

### 1. Install Dependencies
```bash
cd "GOVERNMENT CHATBOT/4. web application"
pip install opencv-python pyzbar qrcode Pillow mysql-connector-python flask flask-cors
```

### 2. Create Database Table
```bash
mysql -u root -p primehrismagdalena < ../../database/primehrismagdalena_attendance.sql
```

### 3. Start Flask Server
```bash
python app.py
```

### 4. Access Scanner
```
Open browser: http://localhost:5000/attendance
```

---

## 🎮 Usage Guide

### For Admins:

#### Generate QR Codes:
1. Go to Admin → Personnel
2. Find employee in table
3. Click yellow "QR Code" button
4. Modal opens with QR code
5. Click "Download" (saves PNG) or "Print" (prints directly)
6. Give QR card to employee

#### Bulk Generation:
```bash
python generate_employee_qr.py
```
Creates QR codes for all employees in `qr_codes/` folder.

### For Employees:

#### Record Attendance:
1. Go to attendance terminal
2. Wait for "Scanning..." message
3. Hold QR code in front of camera
4. Wait for beep/confirmation
5. Check screen for success message

---

## 🔍 Testing the System

### Run Test Script:
```bash
python test_qr_system.py
```

This checks:
- ✅ Required packages installed
- ✅ Database connection
- ✅ Attendance table structure
- ✅ Flask app configuration
- ✅ Template files

### Manual Test:
1. Generate QR code for test employee
2. Open scanner: `http://localhost:5000/attendance`
3. Click "Start Camera"
4. Show QR code to webcam
5. Verify attendance recorded in database

---

## 📊 Database Schema

```sql
CREATE TABLE attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    am_in TIME NULL,
    am_out TIME NULL,
    pm_in TIME NULL,
    pm_out TIME NULL,
    ot_in TIME NULL,
    ot_out TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, date)
);
```

---

## 🎨 Features

### ✅ Implemented:
- [x] Webcam QR scanning
- [x] Auto-continuous scanning
- [x] AM/PM/OT time periods
- [x] Real-time feedback
- [x] Admin QR generation
- [x] Download/Print QR cards
- [x] Database integration
- [x] Employee validation
- [x] Duplicate prevention

### 🔮 Future Enhancements:
- [ ] Sound notification on scan
- [ ] Attendance history view
- [ ] Daily attendance report
- [ ] Late/absent notifications
- [ ] Mobile app version
- [ ] Facial recognition backup
- [ ] GPS location verification
- [ ] Bulk QR email distribution

---

## 🐛 Troubleshooting

### Camera Not Working?
**Problem**: "Error accessing camera"
**Solution**:
- Grant camera permissions in browser
- Use HTTPS or localhost
- Check if camera is used by another app
- Try different browser (Chrome recommended)

### QR Not Detected?
**Problem**: Scanner doesn't detect QR code
**Solution**:
- Ensure good lighting
- Hold QR code steady
- Move closer/farther from camera
- Clean camera lens
- Print QR code larger

### Database Error?
**Problem**: "Employee not found" or connection error
**Solution**:
- Check database credentials in `qr_attendance.py`
- Verify attendance table exists
- Ensure employee exists in database
- Check foreign key constraints

### Wrong Time Period?
**Problem**: System records wrong AM/PM/OT
**Solution**:
- Check server time: `datetime.now()`
- Verify time period definitions in code
- Adjust time ranges if needed

---

## 🔒 Security Considerations

### Current Security:
- ✅ QR codes contain only employee ID (no sensitive data)
- ✅ Database validation prevents invalid IDs
- ✅ Unique constraint prevents duplicate entries
- ✅ Foreign key ensures data integrity

### Recommended Additions:
- 🔐 Add location verification (GPS check)
- 🔐 Implement rate limiting (prevent spam scans)
- 🔐 Add admin authentication for scanner access
- 🔐 Log all scan attempts for audit trail
- 🔐 Consider time-based QR codes (TOTP)

---

## 📱 Deployment Options

### Option 1: Local Terminal (Current)
- Dedicated PC/tablet at entrance
- Webcam mounted at eye level
- Always-on scanner page
- Best for: Single office location

### Option 2: Mobile Scanner
- Install on tablet/phone
- Portable attendance terminal
- Best for: Multiple locations

### Option 3: Kiosk Mode
- Full-screen browser
- Disable navigation
- Auto-restart on error
- Best for: Unattended terminals

---

## 📞 Support

### Files to Check:
- `qr_attendance.py` - Backend logic
- `attendance.html` - Scanner interface
- `adminPersonnel.blade.php` - QR generation
- `adminPersonnel.js` - QR functions

### Common Issues:
1. **Import errors** → Run `pip install -r requirements_qr.txt`
2. **Database errors** → Check MySQL connection
3. **Camera errors** → Check browser permissions
4. **QR not scanning** → Improve lighting/distance

---

## ✨ Summary

Your QR attendance system is **fully functional** with:
- ✅ **Webcam scanning** - Real-time QR detection
- ✅ **Auto-scanning** - No button clicks needed
- ✅ **Smart time tracking** - AM/PM/OT periods
- ✅ **Admin QR generation** - Easy QR code creation
- ✅ **Database integration** - Seamless data storage

**Ready to use!** Just start the Flask server and open the scanner page. 🎉
