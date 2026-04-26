# QR Attendance - Quick Start Guide

## 🚀 Start in 3 Steps

### Step 1: Start the Server
```bash
cd "GOVERNMENT CHATBOT/4. web application"
python app.py
```

### Step 2: Generate a Test QR Code
Open browser: `http://localhost:8000/admin/personnel`
- Click "QR Code" button for any employee
- Click "Download" to save QR code
- Print or display on phone screen

### Step 3: Test the Scanner
Open browser: `http://localhost:5000/attendance`
- Click "Start Camera"
- Hold QR code in front of webcam
- Watch it auto-scan and record attendance!

---

## ✅ What You'll See

### On Scanner Screen:
```
🎯 QR Attendance Scanner
[Camera feed showing]
📷 Scanning... Hold QR code in front of camera

[When QR detected]
✅ AM TIME IN
John Doe
Developer
Time: 08:30 AM
```

### In Database:
```sql
SELECT * FROM attendance WHERE date = CURDATE();

| id | employee_id | date       | am_in    | am_out | pm_in | pm_out | ot_in | ot_out |
|----|-------------|------------|----------|--------|-------|--------|-------|--------|
| 1  | 1           | 2025-01-15 | 08:30:00 | NULL   | NULL  | NULL   | NULL  | NULL   |
```

---

## 🎯 Test Scenarios

### Scenario 1: Full Day Attendance
1. **8:00 AM** - Scan QR → Records `am_in`
2. **12:00 PM** - Scan QR → Records `am_out`
3. **1:00 PM** - Scan QR → Records `pm_in`
4. **5:00 PM** - Scan QR → Records `pm_out`

### Scenario 2: With Overtime
1. **8:00 AM** - Scan QR → Records `am_in`
2. **12:00 PM** - Scan QR → Records `am_out`
3. **1:00 PM** - Scan QR → Records `pm_in`
4. **5:00 PM** - Scan QR → Records `pm_out`
5. **6:00 PM** - Scan QR → Records `ot_in`
6. **8:00 PM** - Scan QR → Records `ot_out`

---

## 🔧 Quick Troubleshooting

### Camera Not Starting?
```
✗ Error accessing camera
→ Grant camera permission in browser
→ Use Chrome or Edge browser
→ Ensure you're on localhost or HTTPS
```

### QR Not Scanning?
```
✗ No QR code detected
→ Improve lighting
→ Hold QR code closer (10-30cm from camera)
→ Ensure QR code is not blurry
→ Try printing larger QR code
```

### Employee Not Found?
```
✗ Employee not found
→ Check if employee exists in database
→ Verify employee ID in QR code matches database
→ Run: SELECT * FROM employees WHERE id = [employee_id];
```

---

## 📊 Check Attendance Records

### Via MySQL:
```sql
-- Today's attendance
SELECT e.first_name, e.last_name, a.* 
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.date = CURDATE();

-- Specific employee
SELECT * FROM attendance 
WHERE employee_id = 1 
ORDER BY date DESC;

-- This week
SELECT * FROM attendance 
WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);
```

---

## 🎨 Customization

### Change Time Periods:
Edit `qr_attendance.py`:
```python
# Current settings
am_start = datetime.strptime('06:00', '%H:%M').time()  # 6 AM
am_end = datetime.strptime('12:00', '%H:%M').time()    # 12 PM
pm_end = datetime.strptime('17:00', '%H:%M').time()    # 5 PM

# Example: Change to 7 AM - 12 PM - 6 PM
am_start = datetime.strptime('07:00', '%H:%M').time()
am_end = datetime.strptime('12:00', '%H:%M').time()
pm_end = datetime.strptime('18:00', '%H:%M').time()
```

### Change Scanner Colors:
Edit `attendance.html`:
```css
/* Current gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Example: Blue gradient */
background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%);
```

---

## 📱 Production Deployment

### For Dedicated Terminal:
1. Use a tablet or PC with webcam
2. Mount at entrance/exit
3. Open scanner in full-screen mode (F11)
4. Keep browser always open
5. Consider kiosk mode for security

### Auto-Start on Boot (Windows):
1. Create `start_scanner.bat`:
```batch
@echo off
cd "C:\path\to\GOVERNMENT CHATBOT\4. web application"
start python app.py
timeout /t 5
start chrome --kiosk http://localhost:5000/attendance
```
2. Add to Windows Startup folder

### Auto-Start on Boot (Linux):
```bash
# Create systemd service
sudo nano /etc/systemd/system/qr-attendance.service

[Unit]
Description=QR Attendance Scanner
After=network.target

[Service]
Type=simple
User=yourusername
WorkingDirectory=/path/to/GOVERNMENT CHATBOT/4. web application
ExecStart=/usr/bin/python3 app.py
Restart=always

[Install]
WantedBy=multi-user.target

# Enable and start
sudo systemctl enable qr-attendance
sudo systemctl start qr-attendance
```

---

## ✨ You're Ready!

Your QR attendance system is **fully operational**:
- ✅ Webcam scanning works
- ✅ Auto-detection enabled
- ✅ Database integration complete
- ✅ Admin QR generation ready

**Just start the server and scan!** 🎉

---

## 📞 Need Help?

Check these files:
- `QR_ATTENDANCE_COMPLETE_GUIDE.md` - Full documentation
- `test_qr_system.py` - System verification
- `QR_ATTENDANCE_GUIDE.md` - Setup instructions

Run test: `python test_qr_system.py`
