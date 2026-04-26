# SQL Queries for Attendance Analysis

## 📊 Basic Queries

### 1. View Today's Attendance
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    a.am_in,
    a.am_out,
    a.pm_in,
    a.pm_out,
    a.ot_in,
    a.ot_out
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.date = CURDATE()
ORDER BY e.first_name;
```

### 2. View Specific Employee's Attendance
```sql
SELECT 
    date,
    am_in,
    am_out,
    pm_in,
    pm_out,
    ot_in,
    ot_out,
    created_at,
    updated_at
FROM attendance
WHERE employee_id = 1  -- Change to your employee ID
ORDER BY date DESC
LIMIT 30;
```

### 3. View This Week's Attendance
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    a.am_in,
    a.am_out,
    a.pm_in,
    a.pm_out,
    a.ot_in,
    a.ot_out
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
ORDER BY a.date DESC, e.first_name;
```

### 4. View This Month's Attendance
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    a.am_in,
    a.am_out,
    a.pm_in,
    a.pm_out,
    a.ot_in,
    a.ot_out
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE MONTH(a.date) = MONTH(CURDATE())
  AND YEAR(a.date) = YEAR(CURDATE())
ORDER BY a.date DESC, e.first_name;
```

---

## ⏰ Time Calculation Queries

### 5. Calculate Total Hours Per Day
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    -- AM Hours
    CASE 
        WHEN a.am_in IS NOT NULL AND a.am_out IS NOT NULL 
        THEN TIMESTAMPDIFF(MINUTE, a.am_in, a.am_out)
        ELSE 0 
    END as am_minutes,
    -- PM Hours
    CASE 
        WHEN a.pm_in IS NOT NULL AND a.pm_out IS NOT NULL 
        THEN TIMESTAMPDIFF(MINUTE, a.pm_in, a.pm_out)
        ELSE 0 
    END as pm_minutes,
    -- OT Hours
    CASE 
        WHEN a.ot_in IS NOT NULL AND a.ot_out IS NOT NULL 
        THEN TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out)
        ELSE 0 
    END as ot_minutes,
    -- Total Hours
    (
        COALESCE(TIMESTAMPDIFF(MINUTE, a.am_in, a.am_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.pm_in, a.pm_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out), 0)
    ) / 60.0 as total_hours
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.date = CURDATE()
ORDER BY e.first_name;
```

### 6. Calculate Total Hours This Month
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    COUNT(DISTINCT a.date) as days_present,
    SUM(
        COALESCE(TIMESTAMPDIFF(MINUTE, a.am_in, a.am_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.pm_in, a.pm_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out), 0)
    ) / 60.0 as total_hours,
    SUM(COALESCE(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out), 0)) / 60.0 as ot_hours
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE MONTH(a.date) = MONTH(CURDATE())
  AND YEAR(a.date) = YEAR(CURDATE())
GROUP BY e.id, e.employee_id, e.first_name, e.last_name
ORDER BY total_hours DESC;
```

### 7. Calculate Average Daily Hours
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    COUNT(DISTINCT a.date) as days_worked,
    AVG(
        COALESCE(TIMESTAMPDIFF(MINUTE, a.am_in, a.am_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.pm_in, a.pm_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out), 0)
    ) / 60.0 as avg_hours_per_day
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY e.id, e.employee_id, e.first_name, e.last_name
ORDER BY avg_hours_per_day DESC;
```

---

## 📈 Analysis Queries

### 8. Find Incomplete Attendance (Missing Time Out)
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    CASE 
        WHEN a.am_in IS NOT NULL AND a.am_out IS NULL THEN 'Missing AM Out'
        WHEN a.pm_in IS NOT NULL AND a.pm_out IS NULL THEN 'Missing PM Out'
        WHEN a.ot_in IS NOT NULL AND a.ot_out IS NULL THEN 'Missing OT Out'
    END as issue
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE (a.am_in IS NOT NULL AND a.am_out IS NULL)
   OR (a.pm_in IS NOT NULL AND a.pm_out IS NULL)
   OR (a.ot_in IS NOT NULL AND a.ot_out IS NULL)
ORDER BY a.date DESC;
```

### 9. Find Late Arrivals (After 8:30 AM)
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    a.am_in,
    TIMEDIFF(a.am_in, '08:30:00') as minutes_late
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.am_in > '08:30:00'
  AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY a.date DESC;
```

### 10. Find Employees with Overtime
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    a.ot_in,
    a.ot_out,
    TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out) / 60.0 as ot_hours
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE a.ot_in IS NOT NULL AND a.ot_out IS NOT NULL
  AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY a.date DESC, ot_hours DESC;
```

### 11. Attendance Summary by Employee
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    ed.position,
    COUNT(DISTINCT a.date) as days_present,
    SUM(CASE WHEN a.am_in IS NOT NULL THEN 1 ELSE 0 END) as am_checkins,
    SUM(CASE WHEN a.pm_in IS NOT NULL THEN 1 ELSE 0 END) as pm_checkins,
    SUM(CASE WHEN a.ot_in IS NOT NULL THEN 1 ELSE 0 END) as ot_checkins,
    SUM(
        COALESCE(TIMESTAMPDIFF(MINUTE, a.am_in, a.am_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.pm_in, a.pm_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out), 0)
    ) / 60.0 as total_hours
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id 
    AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
LEFT JOIN employment_details ed ON e.id = ed.employee_id
GROUP BY e.id, e.employee_id, e.first_name, e.last_name, ed.position
ORDER BY total_hours DESC;
```

### 12. Daily Attendance Count
```sql
SELECT 
    a.date,
    COUNT(DISTINCT a.employee_id) as employees_present,
    SUM(CASE WHEN a.am_in IS NOT NULL THEN 1 ELSE 0 END) as am_checkins,
    SUM(CASE WHEN a.pm_in IS NOT NULL THEN 1 ELSE 0 END) as pm_checkins,
    SUM(CASE WHEN a.ot_in IS NOT NULL THEN 1 ELSE 0 END) as ot_checkins
FROM attendance a
WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY a.date
ORDER BY a.date DESC;
```

---

## 🔍 Detailed Analysis

### 13. Employee Attendance Pattern
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    DAYNAME(a.date) as day_of_week,
    a.am_in,
    a.am_out,
    a.pm_in,
    a.pm_out,
    a.ot_in,
    a.ot_out,
    CASE 
        WHEN a.am_in IS NULL AND a.pm_in IS NULL AND a.ot_in IS NULL THEN 'Absent'
        WHEN (a.am_in IS NOT NULL AND a.am_out IS NULL) 
          OR (a.pm_in IS NOT NULL AND a.pm_out IS NULL)
          OR (a.ot_in IS NOT NULL AND a.ot_out IS NULL) THEN 'Incomplete'
        ELSE 'Complete'
    END as status
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id 
    AND a.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
WHERE e.id = 1  -- Change to your employee ID
ORDER BY a.date DESC;
```

### 14. Overtime Summary by Month
```sql
SELECT 
    DATE_FORMAT(a.date, '%Y-%m') as month,
    COUNT(DISTINCT a.employee_id) as employees_with_ot,
    COUNT(*) as ot_instances,
    SUM(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out)) / 60.0 as total_ot_hours,
    AVG(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out)) / 60.0 as avg_ot_hours
FROM attendance a
WHERE a.ot_in IS NOT NULL AND a.ot_out IS NOT NULL
  AND a.date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(a.date, '%Y-%m')
ORDER BY month DESC;
```

### 15. Perfect Attendance (No Missing Records)
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    COUNT(DISTINCT a.date) as days_present,
    SUM(
        CASE 
            WHEN (a.am_in IS NOT NULL AND a.am_out IS NOT NULL)
             AND (a.pm_in IS NOT NULL AND a.pm_out IS NOT NULL)
            THEN 1 
            ELSE 0 
        END
    ) as perfect_days
FROM employees e
JOIN attendance a ON e.id = a.employee_id
WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY e.id, e.employee_id, e.first_name, e.last_name
HAVING perfect_days = days_present
ORDER BY days_present DESC;
```

---

## 🛠️ Utility Queries

### 16. Delete Today's Test Records
```sql
-- BE CAREFUL! This deletes data
DELETE FROM attendance 
WHERE date = CURDATE() 
  AND employee_id = 1;  -- Specify employee ID for safety
```

### 17. Update Missing Time Out
```sql
-- Manually set time out if employee forgot to scan
UPDATE attendance 
SET am_out = '12:00:00'
WHERE employee_id = 1 
  AND date = CURDATE() 
  AND am_in IS NOT NULL 
  AND am_out IS NULL;
```

### 18. View Last 10 Scans
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date,
    a.updated_at as last_scan_time,
    CASE 
        WHEN a.ot_out IS NOT NULL THEN 'OT Out'
        WHEN a.ot_in IS NOT NULL THEN 'OT In'
        WHEN a.pm_out IS NOT NULL THEN 'PM Out'
        WHEN a.pm_in IS NOT NULL THEN 'PM In'
        WHEN a.am_out IS NOT NULL THEN 'AM Out'
        WHEN a.am_in IS NOT NULL THEN 'AM In'
    END as last_action
FROM attendance a
JOIN employees e ON a.employee_id = e.id
ORDER BY a.updated_at DESC
LIMIT 10;
```

---

## 📊 Export Queries

### 19. Monthly Report for Payroll
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    ed.position,
    ed.salary_grade,
    COUNT(DISTINCT a.date) as days_worked,
    SUM(
        COALESCE(TIMESTAMPDIFF(MINUTE, a.am_in, a.am_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.pm_in, a.pm_out), 0)
    ) / 60.0 as regular_hours,
    SUM(COALESCE(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out), 0)) / 60.0 as overtime_hours,
    SUM(
        COALESCE(TIMESTAMPDIFF(MINUTE, a.am_in, a.am_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.pm_in, a.pm_out), 0) +
        COALESCE(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out), 0)
    ) / 60.0 as total_hours
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id 
    AND MONTH(a.date) = MONTH(CURDATE())
    AND YEAR(a.date) = YEAR(CURDATE())
LEFT JOIN employment_details ed ON e.id = ed.employee_id
GROUP BY e.id, e.employee_id, e.first_name, e.last_name, ed.position, ed.salary_grade
ORDER BY e.employee_id;
```

### 20. Daily Attendance Sheet
```sql
SELECT 
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    ed.position,
    COALESCE(a.am_in, '--:--') as am_in,
    COALESCE(a.am_out, '--:--') as am_out,
    COALESCE(a.pm_in, '--:--') as pm_in,
    COALESCE(a.pm_out, '--:--') as pm_out,
    COALESCE(a.ot_in, '--:--') as ot_in,
    COALESCE(a.ot_out, '--:--') as ot_out,
    CASE 
        WHEN a.id IS NULL THEN 'Absent'
        WHEN (a.am_in IS NOT NULL AND a.am_out IS NULL) 
          OR (a.pm_in IS NOT NULL AND a.pm_out IS NULL)
          OR (a.ot_in IS NOT NULL AND a.ot_out IS NULL) THEN 'Incomplete'
        ELSE 'Present'
    END as status
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = CURDATE()
LEFT JOIN employment_details ed ON e.id = ed.employee_id
ORDER BY e.employee_id;
```

---

## 💡 Tips:

1. **Replace `CURDATE()`** with specific date: `'2025-01-15'`
2. **Replace `employee_id = 1`** with your actual employee ID
3. **Use `LIMIT`** to prevent large result sets
4. **Export to CSV** in MySQL Workbench or phpMyAdmin
5. **Create views** for frequently used queries

---

## 🎯 Quick Reference:

| What You Want | Query Number |
|---------------|--------------|
| Today's attendance | #1 |
| Specific employee | #2 |
| Calculate hours | #5, #6 |
| Find late arrivals | #9 |
| Find overtime | #10 |
| Monthly report | #19 |
| Daily sheet | #20 |
