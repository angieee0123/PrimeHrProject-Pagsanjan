# How to View Attendance Records - Complete Guide

## 🎯 3 Ways to View Attendance

### Method 1: Web Dashboard (Easiest) ⭐
### Method 2: SQL Queries (Most Flexible)
### Method 3: Export to Excel/CSV

---

## 📊 Method 1: Web Dashboard

### Access the Report:
```
http://localhost:5000/attendance/report
```

### Features:
- ✅ **Visual interface** - Easy to read
- ✅ **Date filters** - Select date range
- ✅ **Employee filter** - View specific employee
- ✅ **Statistics** - Total hours, OT, etc.
- ✅ **Export CSV** - Download for Excel
- ✅ **Color coding** - Green (in), Red (out)

### What You'll See:

```
┌─────────────────────────────────────────────────────────┐
│  📊 Attendance Report                                   │
├─────────────────────────────────────────────────────────┤
│  Filters:                                               │
│  Date From: [2025-01-15]  Date To: [2025-01-15]        │
│  Employee ID: [All]       [🔍 Search]                   │
├─────────────────────────────────────────────────────────┤
│  Stats:                                                 │
│  Total Records: 5    Present Today: 5                   │
│  Average Hours: 8h   Total OT: 2h                       │
├─────────────────────────────────────────────────────────┤
│  Date       │ Employee │ AM In │ AM Out │ PM In │ ...  │
│  Jan 15     │ John Doe │ 08:00 │ 12:00  │ 13:00 │ ...  │
│  Jan 15     │ Jane S.  │ 08:30 │ 12:15  │ 13:00 │ ...  │
└─────────────────────────────────────────────────────────┘
```

### How to Use:

1. **View Today's Attendance**:
   - Open report page
   - Default shows today
   - See all employees who scanned

2. **View Specific Date**:
   - Change "Date From" and "Date To"
   - Click "Search"

3. **View Specific Employee**:
   - Enter employee ID
   - Click "Search"
   - See all their records

4. **Export to Excel**:
   - Click "Export CSV" button
   - Open in Excel
   - Analyze further

---

## 🔍 Method 2: SQL Queries

### Quick Queries:

#### Today's Attendance:
```sql
SELECT 
    CONCAT(e.first_name, ' ', e.last_name) as name,
    a.am_in, a.am_out,
    a.pm_in, a.pm_out,
    a.ot_in, a.ot_out
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.date = CURDATE();
```

#### Specific Employee:
```sql
SELECT * FROM attendance 
WHERE employee_id = 1 
ORDER BY date DESC 
LIMIT 10;
```

#### Calculate Hours:
```sql
SELECT 
    date,
    (TIMESTAMPDIFF(MINUTE, am_in, am_out) +
     TIMESTAMPDIFF(MINUTE, pm_in, pm_out) +
     COALESCE(TIMESTAMPDIFF(MINUTE, ot_in, ot_out), 0)) / 60.0 as total_hours
FROM attendance
WHERE employee_id = 1
  AND date = CURDATE();
```

**See `ATTENDANCE_SQL_QUERIES.md` for 20+ more queries!**

---

## 📈 Understanding the Data

### Time Fields Explained:

| Field | Meaning | Example |
|-------|---------|---------|
| **am_in** | Morning time in | 08:00:00 |
| **am_out** | Morning time out | 12:00:00 |
| **pm_in** | Afternoon time in | 13:00:00 |
| **pm_out** | Afternoon time out | 17:00:00 |
| **ot_in** | Overtime time in | 18:00:00 |
| **ot_out** | Overtime time out | 20:00:00 |

### Status Indicators:

| Status | Meaning |
|--------|---------|
| **Complete** | All time in/out recorded |
| **Incomplete** | Missing time out |
| **Absent** | No record for the day |

### Time Periods:

```
06:00 AM ─────────────────────────────────────────────────▶
         │ AM PERIOD                                       │
         │ am_in → am_out                                  │
12:00 PM ─────────────────────────────────────────────────▶
         │ PM PERIOD                                       │
         │ pm_in → pm_out                                  │
05:00 PM ─────────────────────────────────────────────────▶
         │ OT PERIOD                                       │
         │ ot_in → ot_out                                  │
         ▼                                                  │
```

---

## 📊 Common Scenarios

### Scenario 1: Check if Employee Scanned Today
```sql
SELECT 
    CONCAT(first_name, ' ', last_name) as name,
    CASE 
        WHEN a.id IS NOT NULL THEN 'Present'
        ELSE 'Absent'
    END as status
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = CURDATE()
WHERE e.id = 1;
```

### Scenario 2: Calculate Total Hours Today
```sql
SELECT 
    (COALESCE(TIMESTAMPDIFF(MINUTE, am_in, am_out), 0) +
     COALESCE(TIMESTAMPDIFF(MINUTE, pm_in, pm_out), 0) +
     COALESCE(TIMESTAMPDIFF(MINUTE, ot_in, ot_out), 0)) / 60.0 as hours
FROM attendance
WHERE employee_id = 1 AND date = CURDATE();
```

### Scenario 3: Find Missing Time Out
```sql
SELECT 
    date,
    CASE 
        WHEN am_in IS NOT NULL AND am_out IS NULL THEN 'Missing AM Out'
        WHEN pm_in IS NOT NULL AND pm_out IS NULL THEN 'Missing PM Out'
        WHEN ot_in IS NOT NULL AND ot_out IS NULL THEN 'Missing OT Out'
    END as issue
FROM attendance
WHERE employee_id = 1
  AND (
    (am_in IS NOT NULL AND am_out IS NULL) OR
    (pm_in IS NOT NULL AND pm_out IS NULL) OR
    (ot_in IS NOT NULL AND ot_out IS NULL)
  );
```

### Scenario 4: Monthly Summary
```sql
SELECT 
    COUNT(DISTINCT date) as days_worked,
    SUM(
        COALESCE(TIMESTAMPDIFF(MINUTE, am_in, am_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, pm_in, pm_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, ot_in, ot_out), 0)
    ) / 60.0 as total_hours,
    SUM(COALESCE(TIMESTAMPDIFF(MINUTE, ot_in, ot_out), 0)) / 60.0 as ot_hours
FROM attendance
WHERE employee_id = 1
  AND MONTH(date) = MONTH(CURDATE())
  AND YEAR(date) = YEAR(CURDATE());
```

---

## 📥 Method 3: Export to Excel

### Option A: From Web Dashboard
1. Open: `http://localhost:5000/attendance/report`
2. Set filters (date range, employee)
3. Click "Export CSV"
4. Open in Excel
5. Create pivot tables, charts, etc.

### Option B: From MySQL
1. Run query in MySQL Workbench
2. Right-click result → Export
3. Choose CSV format
4. Open in Excel

### Option C: Using Command Line
```bash
mysql -u root -p -e "
SELECT * FROM attendance 
WHERE date >= '2025-01-01'
" primehrismagdalena > attendance.csv
```

---

## 🎯 Quick Reference Table

| What You Want | How to Get It |
|---------------|---------------|
| **Today's attendance** | Web dashboard (default view) |
| **Specific employee** | Web dashboard → Enter employee ID |
| **Date range** | Web dashboard → Set date filters |
| **Calculate hours** | SQL Query #5 or #6 |
| **Find late arrivals** | SQL Query #9 |
| **Find overtime** | SQL Query #10 |
| **Monthly report** | SQL Query #19 |
| **Export to Excel** | Web dashboard → Export CSV |

---

## 💡 Pro Tips

### 1. Create Database Views
Save frequently used queries as views:
```sql
CREATE VIEW daily_attendance AS
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as name,
    a.*
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.date = CURDATE();

-- Then just: SELECT * FROM daily_attendance;
```

### 2. Schedule Reports
Use cron job or Task Scheduler to auto-generate daily reports:
```bash
# Linux cron
0 18 * * * mysql -u root -p < daily_report.sql > /reports/$(date +\%Y-\%m-\%d).csv
```

### 3. Create Excel Templates
- Create Excel template with formulas
- Import CSV data daily
- Auto-calculate totals, charts

### 4. Set Up Alerts
Monitor for:
- Missing time out (incomplete records)
- Late arrivals
- Excessive overtime
- Absent employees

---

## 🔧 Troubleshooting

### "No records found"
- Check date filters
- Verify employee has scanned
- Check employee ID is correct

### "Hours calculation wrong"
- Verify all time in/out are recorded
- Check for NULL values
- Ensure time format is correct (HH:MM:SS)

### "Can't export CSV"
- Check browser download permissions
- Try different browser
- Use SQL export instead

---

## 📞 Need Help?

### Check These:
1. **Web Dashboard**: `http://localhost:5000/attendance/report`
2. **SQL Queries**: `ATTENDANCE_SQL_QUERIES.md`
3. **Test Page**: `http://localhost:5000/attendance/test`

### Common Questions:

**Q: How do I see who's present right now?**
A: Web dashboard → Today's date → Look for records with time_in but no time_out

**Q: How do I calculate overtime pay?**
A: Use SQL Query #10 or #19 to get OT hours, multiply by OT rate

**Q: How do I fix missing time out?**
A: Use SQL Query #17 to manually update

**Q: How do I generate monthly payroll report?**
A: Use SQL Query #19, export to CSV, open in Excel

---

## ✅ Summary

**3 Ways to View Attendance:**
1. ⭐ **Web Dashboard** - Easiest, visual, filters
2. 🔍 **SQL Queries** - Most flexible, powerful
3. 📥 **Export CSV** - For Excel analysis

**Key URLs:**
- Report: `http://localhost:5000/attendance/report`
- Test: `http://localhost:5000/attendance/test`
- Scanner: `http://localhost:5000/attendance`

**You're all set!** 🎉
