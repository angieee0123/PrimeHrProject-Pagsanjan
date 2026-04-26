# Manual Attendance Entry - User Guide

## 🎯 Overview

The Manual Attendance Entry feature allows admins to record attendance using **AM/PM/OT buttons** instead of QR scanning. Perfect for:
- Employees who forgot to scan
- Manual corrections
- Backup attendance recording
- Testing the system

---

## 🚀 Access the Feature

```
http://localhost:5000/attendance/manual
```

---

## 📋 How It Works

### Step 1: Select Employee
1. Open the manual entry page
2. Click the dropdown menu
3. Select an employee from the list
4. Employee info will appear

### Step 2: View Current Status
Once you select an employee, you'll see:
- **Today's attendance status** (what's already recorded)
- **Time displays** for each period
- **Enabled/disabled buttons** (already recorded times are disabled)

### Step 3: Record Time
Click the appropriate button:
- **AM Time In** - Morning arrival
- **AM Time Out** - Morning departure
- **PM Time In** - Afternoon arrival
- **PM Time Out** - Afternoon departure
- **OT Time In** - Overtime start
- **OT Time Out** - Overtime end

---

## 🎨 Interface Layout

```
┌─────────────────────────────────────────────────────────┐
│  ⏰ Manual Attendance Entry                             │
├─────────────────────────────────────────────────────────┤
│  Select Employee: [John Doe (EMP-001)        ▼]        │
│                                                         │
│  📊 Today's Attendance Status                           │
│  AM In: 08:00  AM Out: 12:00  PM In: --:--             │
│  PM Out: --:--  OT In: --:--  OT Out: --:--            │
├─────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │
│  │ 🌅 AM Period│  │ ☀️ PM Period│  │ 🌙 OT Period│    │
│  │ 6AM - 12PM  │  │ 12PM - 5PM  │  │ 5PM onwards │    │
│  │             │  │             │  │             │    │
│  │ [Time In]   │  │ [Time In]   │  │ [Time In]   │    │
│  │ [Time Out]  │  │ [Time Out]  │  │ [Time Out]  │    │
│  │             │  │             │  │             │    │
│  │ In: 08:00   │  │ In: --:--   │  │ In: --:--   │    │
│  │ Out: 12:00  │  │ Out: --:--  │  │ Out: --:--  │    │
│  └─────────────┘  └─────────────┘  └─────────────┘    │
└─────────────────────────────────────────────────────────┘
```

---

## 💡 Features

### 1. Smart Button States
- ✅ **Enabled** - Can be clicked (green/red)
- ❌ **Disabled** - Already recorded (grayed out)

### 2. Real-time Status
- Shows what's already recorded today
- Updates immediately after recording
- Color-coded (green = recorded, gray = empty)

### 3. Visual Feedback
- Success message with time
- Error messages if already recorded
- Auto-hide after 3 seconds

### 4. Period Cards
- **AM Card** - Green border
- **PM Card** - Orange border
- **OT Card** - Purple border

---

## 📖 Use Cases

### Use Case 1: Employee Forgot to Scan
**Scenario**: John forgot to scan when he arrived at 8:00 AM

**Solution**:
1. Open manual entry page
2. Select "John Doe"
3. Click "AM Time In" button
4. System records current time
5. Done!

### Use Case 2: Correct Wrong Time
**Scenario**: Jane scanned at wrong time, need to fix

**Solution**:
1. Delete wrong record from database
2. Open manual entry page
3. Select "Jane Smith"
4. Click appropriate button
5. System records correct time

### Use Case 3: Bulk Entry
**Scenario**: System was down, need to enter multiple employees

**Solution**:
1. Open manual entry page
2. For each employee:
   - Select employee
   - Click appropriate buttons
   - Move to next employee
3. All records saved

### Use Case 4: Testing
**Scenario**: Want to test attendance system

**Solution**:
1. Open manual entry page
2. Select test employee
3. Click buttons to simulate full day
4. Check report to verify

---

## 🎯 Button Behavior

### AM Time In Button
- **When Enabled**: Records current time as am_in
- **When Disabled**: Already recorded (grayed out)
- **Color**: Green
- **Icon**: ⬇️

### AM Time Out Button
- **When Enabled**: Records current time as am_out
- **When Disabled**: Already recorded (grayed out)
- **Color**: Red
- **Icon**: ⬆️

### PM Time In Button
- **When Enabled**: Records current time as pm_in
- **When Disabled**: Already recorded (grayed out)
- **Color**: Green
- **Icon**: ⬇️

### PM Time Out Button
- **When Enabled**: Records current time as pm_out
- **When Disabled**: Already recorded (grayed out)
- **Color**: Red
- **Icon**: ⬆️

### OT Time In Button
- **When Enabled**: Records current time as ot_in
- **When Disabled**: Already recorded (grayed out)
- **Color**: Green
- **Icon**: ⬇️

### OT Time Out Button
- **When Enabled**: Records current time as ot_out
- **When Disabled**: Already recorded (grayed out)
- **Color**: Red
- **Icon**: ⬆️

---

## 🔍 What Gets Recorded

When you click a button, the system records:
- **Employee ID** - From selected employee
- **Date** - Today's date
- **Time** - Current time (HH:MM:SS)
- **Field** - Specific field (am_in, am_out, etc.)

### Example:
```
Button Clicked: AM Time In
Time: 08:30:15 AM
Date: 2025-01-15

Database Record:
employee_id: 1
date: 2025-01-15
am_in: 08:30:15
am_out: NULL
pm_in: NULL
pm_out: NULL
ot_in: NULL
ot_out: NULL
```

---

## ⚠️ Important Notes

### 1. One Record Per Day
- Each employee can have only ONE record per day
- All time fields are in the same record
- Cannot create duplicate records

### 2. Cannot Override
- Once a time is recorded, button is disabled
- To change, must delete from database first
- This prevents accidental overwrites

### 3. Current Time Only
- System always uses current time
- Cannot manually enter specific time
- For custom times, use SQL UPDATE

### 4. No Validation
- System doesn't check if times make sense
- Admin responsible for logical order
- Example: Can record PM before AM (not recommended)

---

## 🛠️ Advanced Usage

### Record Custom Time (SQL)
If you need to record a specific time (not current time):

```sql
-- Insert with specific time
INSERT INTO attendance (employee_id, date, am_in)
VALUES (1, '2025-01-15', '08:00:00');

-- Update existing record
UPDATE attendance
SET am_in = '08:00:00'
WHERE employee_id = 1 AND date = '2025-01-15';
```

### Delete Wrong Entry
```sql
-- Delete specific field
UPDATE attendance
SET am_in = NULL
WHERE employee_id = 1 AND date = CURDATE();

-- Delete entire record
DELETE FROM attendance
WHERE employee_id = 1 AND date = CURDATE();
```

### Bulk Entry Script
```python
import mysql.connector
from datetime import datetime, time

conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='primehrismagdalena'
)
cursor = conn.cursor()

# Bulk insert
employees = [1, 2, 3, 4, 5]
for emp_id in employees:
    cursor.execute("""
        INSERT INTO attendance (employee_id, date, am_in, am_out, pm_in, pm_out)
        VALUES (%s, CURDATE(), '08:00:00', '12:00:00', '13:00:00', '17:00:00')
    """, (emp_id,))

conn.commit()
cursor.close()
conn.close()
```

---

## 🎨 Comparison: Manual vs QR Scanner

| Feature | Manual Entry | QR Scanner |
|---------|--------------|------------|
| **Speed** | Slower (select + click) | Faster (just scan) |
| **Accuracy** | Admin controlled | Automatic |
| **Use Case** | Corrections, forgot scan | Daily attendance |
| **Time** | Current time only | Current time only |
| **Access** | Admin only | Anyone with QR |
| **Flexibility** | Can record any period | Auto-detects period |

---

## 📊 Workflow Comparison

### QR Scanner Workflow:
```
Employee arrives → Holds QR code → Auto-scans → Records time
(5 seconds)
```

### Manual Entry Workflow:
```
Admin opens page → Selects employee → Clicks button → Records time
(15 seconds)
```

**Recommendation**: Use QR scanner for daily attendance, manual entry for exceptions.

---

## 🔗 Related Pages

- **QR Scanner**: `http://localhost:5000/attendance`
- **Test Page**: `http://localhost:5000/attendance/test`
- **Report**: `http://localhost:5000/attendance/report`
- **Manual Entry**: `http://localhost:5000/attendance/manual`

---

## ✅ Summary

**Manual Attendance Entry** provides:
- ✅ Easy button-based recording
- ✅ Visual status display
- ✅ Smart button states
- ✅ Real-time updates
- ✅ Perfect for corrections

**Best For**:
- Forgot to scan
- System testing
- Manual corrections
- Backup method

**Access**: `http://localhost:5000/attendance/manual`

---

## 🎉 You're Ready!

The manual entry system is fully functional:
1. Open the page
2. Select employee
3. Click buttons
4. Done!

**Simple and effective!** 🚀
