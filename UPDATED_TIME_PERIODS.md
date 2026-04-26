# Updated Time Period Logic

## ✅ New Time Periods

### 🌅 AM Period
- **Time**: 8:00 AM - 12:00 PM
- **Fields**: `am_in`, `am_out`
- **Duration**: 4 hours

### ☀️ PM Period
- **Time**: 1:00 PM - 5:00 PM
- **Fields**: `pm_in`, `pm_out`
- **Duration**: 4 hours

### 🌙 OT Period
- **Time**: 6:00 PM - 6:00 AM (next day)
- **Fields**: `ot_in`, `ot_out`
- **Duration**: 12 hours (overnight)

---

## 📊 Visual Timeline

```
00:00 (12 AM) ─────────────────────────────────────────────▶
              │                                             │
              │  🌙 OT PERIOD (Overnight)                   │
              │  6:00 PM - 6:00 AM                          │
              │                                             │
06:00 AM ──────────────────────────────────────────────────▶
              │                                             │
              │  ⏸️  Break Time (6 AM - 8 AM)               │
              │                                             │
08:00 AM ──────────────────────────────────────────────────▶
              │                                             │
              │  🌅 AM PERIOD                               │
              │  8:00 AM - 12:00 PM                         │
              │  am_in → am_out                             │
              │                                             │
12:00 PM ──────────────────────────────────────────────────▶
              │                                             │
              │  🍽️  Lunch Break (12 PM - 1 PM)            │
              │                                             │
01:00 PM ──────────────────────────────────────────────────▶
              │                                             │
              │  ☀️ PM PERIOD                               │
              │  1:00 PM - 5:00 PM                          │
              │  pm_in → pm_out                             │
              │                                             │
05:00 PM ──────────────────────────────────────────────────▶
              │                                             │
              │  ⏸️  Break Time (5 PM - 6 PM)               │
              │                                             │
06:00 PM ──────────────────────────────────────────────────▶
              │                                             │
              │  🌙 OT PERIOD                               │
              │  6:00 PM - 6:00 AM                          │
              │  ot_in → ot_out                             │
              │                                             │
              ▼                                             │
```

---

## 🎯 How It Works

### Scenario 1: Regular Day Shift
```
08:00 AM → Scan QR → AM TIME IN
12:00 PM → Scan QR → AM TIME OUT
01:00 PM → Scan QR → PM TIME IN
05:00 PM → Scan QR → PM TIME OUT
```

**Total Hours**: 8 hours (4 AM + 4 PM)

### Scenario 2: With Overtime
```
08:00 AM → Scan QR → AM TIME IN
12:00 PM → Scan QR → AM TIME OUT
01:00 PM → Scan QR → PM TIME IN
05:00 PM → Scan QR → PM TIME OUT
06:00 PM → Scan QR → OT TIME IN
10:00 PM → Scan QR → OT TIME OUT
```

**Total Hours**: 12 hours (4 AM + 4 PM + 4 OT)

### Scenario 3: Night Shift Only
```
06:00 PM → Scan QR → OT TIME IN
02:00 AM → Scan QR → OT TIME OUT
```

**Total Hours**: 8 hours (OT only)

### Scenario 4: Overnight Shift
```
10:00 PM → Scan QR → OT TIME IN
06:00 AM → Scan QR → OT TIME OUT
```

**Total Hours**: 8 hours (overnight)

---

## 🕐 Time Period Detection

### When You Scan:

| Current Time | Period Detected | Action |
|--------------|----------------|--------|
| 6:00 AM - 7:59 AM | OT (ending) | OT OUT |
| 8:00 AM - 11:59 AM | AM | AM IN/OUT |
| 12:00 PM - 12:59 PM | Break | No action |
| 1:00 PM - 4:59 PM | PM | PM IN/OUT |
| 5:00 PM - 5:59 PM | Break | No action |
| 6:00 PM - 11:59 PM | OT | OT IN/OUT |
| 12:00 AM - 5:59 AM | OT (overnight) | OT IN/OUT |

---

## 📋 Break Times

### Lunch Break
- **Time**: 12:00 PM - 1:00 PM
- **Duration**: 1 hour
- **Note**: Between AM and PM periods

### Evening Break
- **Time**: 5:00 PM - 6:00 PM
- **Duration**: 1 hour
- **Note**: Between PM and OT periods

### Morning Break
- **Time**: 6:00 AM - 8:00 AM
- **Duration**: 2 hours
- **Note**: Between OT and AM periods

---

## 🔍 Examples by Time

### Morning (8 AM - 12 PM)
```
08:00 AM - First scan → AM TIME IN
08:30 AM - Scan again → AM TIME OUT (if needed)
11:00 AM - Scan again → AM TIME OUT
```

### Afternoon (1 PM - 5 PM)
```
01:00 PM - First scan → PM TIME IN
01:30 PM - Scan again → PM TIME OUT (if needed)
04:00 PM - Scan again → PM TIME OUT
```

### Evening/Night (6 PM - 6 AM)
```
06:00 PM - First scan → OT TIME IN
10:00 PM - Scan again → OT TIME OUT
11:00 PM - Scan again → OT TIME IN (new OT)
02:00 AM - Scan again → OT TIME OUT
```

---

## 💡 Important Notes

### 1. Flexible Scanning
- Can scan at any time within the period
- System auto-detects which period you're in
- No need to scan exactly at start/end times

### 2. Multiple Scans
- First scan in period = TIME IN
- Second scan in period = TIME OUT
- Can't scan more than twice per period

### 3. Overnight OT
- OT period spans midnight
- 6 PM to 6 AM = 12 hours
- Can clock in at night, out in morning

### 4. Break Times
- 12 PM - 1 PM: Lunch break
- 5 PM - 6 PM: Evening break
- 6 AM - 8 AM: Morning break
- Scanning during breaks = OT period

---

## 🎨 Updated Interfaces

### QR Scanner
- Auto-detects period based on current time
- Shows appropriate message (AM/PM/OT)

### Manual Entry
- 3 cards: AM (8-12), PM (1-5), OT (6-6)
- 6 buttons total
- Color-coded by period

### Test Page
- Shows current period highlighted
- Updates every second
- Visual indicators

### Report Page
- Displays all time fields
- Calculates total hours
- Shows OT separately

---

## 📊 Database Records

### Example Record:
```sql
employee_id: 1
date: 2025-01-15
am_in: 08:00:00
am_out: 12:00:00
pm_in: 13:00:00
pm_out: 17:00:00
ot_in: 18:00:00
ot_out: 22:00:00
```

### Calculations:
- **AM Hours**: 12:00 - 08:00 = 4 hours
- **PM Hours**: 17:00 - 13:00 = 4 hours
- **OT Hours**: 22:00 - 18:00 = 4 hours
- **Total**: 12 hours

---

## 🔧 Configuration

If you need to change time periods, edit `qr_attendance.py`:

```python
# Current settings
am_start = datetime.strptime('08:00', '%H:%M').time()  # 8 AM
am_end = datetime.strptime('12:00', '%H:%M').time()    # 12 PM
pm_start = datetime.strptime('13:00', '%H:%M').time()  # 1 PM
pm_end = datetime.strptime('17:00', '%H:%M').time()    # 5 PM
ot_start = datetime.strptime('18:00', '%H:%M').time()  # 6 PM
ot_end = datetime.strptime('06:00', '%H:%M').time()    # 6 AM
```

---

## ✅ Summary

**New Time Periods:**
- 🌅 **AM**: 8:00 AM - 12:00 PM (4 hours)
- ☀️ **PM**: 1:00 PM - 5:00 PM (4 hours)
- 🌙 **OT**: 6:00 PM - 6:00 AM (12 hours)

**Break Times:**
- Lunch: 12:00 PM - 1:00 PM
- Evening: 5:00 PM - 6:00 PM
- Morning: 6:00 AM - 8:00 AM

**Total Regular Hours**: 8 hours (AM + PM)
**OT Available**: 12 hours (overnight)

---

## 🚀 Ready to Use!

Restart the server and the new time periods will be active:

```bash
python app.py
```

All pages updated:
- ✅ QR Scanner
- ✅ Manual Entry
- ✅ Test Page
- ✅ Report Page

**Perfect for your schedule!** 🎉
