# PRIME HRIS RDBMS Structure & Chatbot Integration Guide

## Database Architecture Overview

### Core Entity-Relationship Model

```
employees (Master Table)
    ↓ (1:N)
    ├── attendance (Daily time records)
    │   ↓ (1:1)
    │   └── accredited_hours_log (Computed hours with late/undertime tracking)
    │
    ├── leave_balances (Leave credits by year)
    │   ↓ (N:1)
    │   └── leave_types_config (Leave type definitions)
    │
    ├── leave_applications (Leave requests)
    │   ↓ (1:N)
    │   └── leave_transactions (Credit/debit history)
    │
    ├── employee_deductions (Assigned deductions)
    │   ↓ (N:1)
    │   └── deduction_types (Deduction categories)
    │
    └── schedules (Work schedules)
```

## Key Tables & Relationships

### 1. **employees** (Primary Entity)
- **Primary Key**: `id` (bigint)
- **Unique Keys**: `employee_id` (varchar), `email`
- **Purpose**: Master employee data
- **Key Fields**:
  - `employee_id`: Human-readable ID (e.g., "2024001")
  - `first_name`, `middle_name`, `last_name`: Name components
  - `birth_date`, `sex`, `civil_status`, `citizenship`

**Chatbot Usage**:
```sql
-- Search by name (flexible)
SELECT * FROM employees 
WHERE first_name LIKE '%Jeremy%' 
   OR last_name LIKE '%Pogi%' 
   OR middle_name LIKE '%Reyes%';
```

---

### 2. **attendance** (Time Records)
- **Primary Key**: `id`
- **Foreign Key**: `employee_id` → `employees(id)` ON DELETE CASCADE
- **Unique Constraint**: (`employee_id`, `date`)
- **Purpose**: Daily time logs
- **Key Fields**:
  - `date`: Attendance date
  - `am_in`, `am_out`, `pm_in`, `pm_out`: Time stamps
  - `ot_in`, `ot_out`: Overtime tracking
  - `accredited_hours`: Computed hours (in minutes)

**Chatbot Usage**:
```sql
-- Get attendance for specific employee
SELECT a.date, a.am_in, a.pm_in, e.first_name, e.last_name
FROM attendance a
JOIN employees e ON a.employee_id = e.id
WHERE e.employee_id = '2024001'
ORDER BY a.date DESC
LIMIT 10;
```

---

### 3. **accredited_hours_log** (Computed Hours)
- **Primary Key**: `id`
- **Foreign Keys**: 
  - `attendance_id` → `attendance(id)` ON DELETE CASCADE
  - `employee_id` → `employees(id)` ON DELETE CASCADE
  - `schedule_id` → `schedules(id)` ON DELETE SET NULL
- **Purpose**: Detailed computation of work hours with late/undertime tracking
- **Key Fields**:
  - `late_minutes`: Total late minutes
  - `late_deducted_from_leave`: Boolean flag
  - `late_deduction_leave_type`: Which leave type (VL/SL)
  - `lwop_minutes`: Leave Without Pay minutes
  - `undertime_minutes`: Undertime tracking
  - `total_accredited_minutes`: Final credited hours
  - `am_grace_applied`, `pm_grace_applied`: Grace period flags

**Chatbot Usage**:
```sql
-- Find last time employee was late
SELECT e.first_name, e.last_name, a.date, ahl.late_minutes
FROM accredited_hours_log ahl
JOIN attendance a ON ahl.attendance_id = a.id
JOIN employees e ON ahl.employee_id = e.id
WHERE e.employee_id = '2024001' 
  AND ahl.late_minutes > 0
ORDER BY a.date DESC
LIMIT 1;
```

---

### 4. **leave_balances** (Leave Credits)
- **Primary Key**: `id`
- **Foreign Keys**:
  - `employee_id` → `employees(id)` ON DELETE CASCADE
  - `leave_code` → `leave_types_config(leave_code)` ON DELETE CASCADE
- **Unique Constraint**: (`employee_id`, `leave_code`, `year`)
- **Purpose**: Track leave credits per employee per year
- **Key Fields**:
  - `leave_code`: VL, SL, SPL, ML, PL, etc.
  - `year`: Calendar year
  - `total_credits`: Total earned (decimal 10,6)
  - `used_credits`: Already consumed
  - `pending_credits`: Pending approval
  - `available_credits`: Remaining balance

**Chatbot Usage**:
```sql
-- Get leave balance for employee
SELECT e.first_name, e.last_name, lb.leave_code, 
       lb.available_credits, lb.used_credits
FROM leave_balances lb
JOIN employees e ON lb.employee_id = e.id
WHERE e.employee_id = '2024001' 
  AND lb.year = 2026;
```

---

### 5. **leave_types_config** (Leave Type Definitions)
- **Primary Key**: `leave_code` (varchar 10)
- **Purpose**: Define leave types and their properties
- **Key Fields**:
  - `leave_code`: VL, SL, SPL, ML, PL, etc.
  - `leave_name`: Full name
  - `is_accrued`: Boolean (monthly accrual)
  - `is_cumulative`: Can carry over
  - `is_monetizable`: Can be converted to cash
  - `max_days_per_year`: Annual limit

---

### 6. **leave_applications** (Leave Requests)
- **Primary Key**: `id`
- **Foreign Keys**:
  - `employee_id` → `employees(id)` ON DELETE CASCADE
  - `leave_code` → `leave_types_config(leave_code)`
- **Purpose**: Track leave requests
- **Key Fields**:
  - `application_number`: Unique ID (e.g., "LA-2026-0001")
  - `start_date`, `end_date`: Leave period
  - `total_days`: Duration
  - `status`: pending/approved/rejected
  - `reason`: Leave justification

---

### 7. **leave_transactions** (Leave History)
- **Primary Key**: `id`
- **Foreign Keys**:
  - `employee_id` → `employees(id)` ON DELETE CASCADE
  - `leave_application_id` → `leave_applications(id)` ON DELETE CASCADE
- **Purpose**: Audit trail of leave credit changes
- **Key Fields**:
  - `transaction_type`: credit/debit/adjustment
  - `amount`: Change in credits (decimal)
  - `balance_after`: New balance
  - `description`: Transaction reason

---

## RDBMS Principles Applied

### 1. **Referential Integrity**
All foreign keys use `ON DELETE CASCADE` or `ON DELETE SET NULL` to maintain data consistency:
- Delete employee → cascades to attendance, leave_balances, etc.
- Delete attendance → cascades to accredited_hours_log

### 2. **Normalization**
- **1NF**: All fields are atomic (no repeating groups)
- **2NF**: No partial dependencies (all non-key fields depend on entire primary key)
- **3NF**: No transitive dependencies (leave_code references leave_types_config, not duplicated)

### 3. **Data Types**
- `decimal(10,6)`: Precise leave credits (e.g., 2.895833 days)
- `smallint unsigned`: Minutes (0-65535)
- `date`: Date only (no time component)
- `time`: Time only (HH:MM:SS)
- `timestamp`: Full datetime with timezone

### 4. **Constraints**
- **UNIQUE**: Prevents duplicate records (employee_id + date)
- **NOT NULL**: Ensures required fields
- **DEFAULT**: Auto-values (e.g., `0.000000` for credits)

---

## Chatbot Integration Strategy

### Current Implementation Analysis

Your chatbot uses:
1. **Dynamic Schema Fetching**: `get_db_schema()` retrieves all tables
2. **AI-Powered SQL Generation**: Groq converts natural language → SQL
3. **Query Execution**: `execute_query()` runs SQL safely
4. **Natural Response**: Groq converts results → conversational text

### Key Improvements Needed

#### 1. **Use Proper Table Names**
```python
# ❌ WRONG (your current code sometimes uses)
"SELECT * FROM accredited_hours_logs"  # Plural - doesn't exist!

# ✅ CORRECT
"SELECT * FROM accredited_hours_log"  # Singular
```

#### 2. **Always JOIN with employees**
```python
# ❌ WRONG - No employee name
"SELECT late_minutes FROM accredited_hours_log WHERE employee_id = 8"

# ✅ CORRECT - Include employee name
"""
SELECT e.first_name, e.last_name, ahl.late_minutes, a.date
FROM accredited_hours_log ahl
JOIN attendance a ON ahl.attendance_id = a.id
JOIN employees e ON ahl.employee_id = e.id
WHERE e.employee_id = '2024001'
"""
```

#### 3. **Handle Date Formats**
```python
# Natural language: "May 18, 2026"
# SQL format: '2026-05-18'

# Your prompt should instruct:
"Convert date strings to MySQL date format 'YYYY-MM-DD'"
```

#### 4. **Use Proper Aggregations**
```python
# For "How many employees?"
"SELECT COUNT(*) as total_employees FROM employees"

# For "Total late minutes this month"
"""
SELECT e.first_name, e.last_name, SUM(ahl.late_minutes) as total_late
FROM accredited_hours_log ahl
JOIN employees e ON ahl.employee_id = e.id
JOIN attendance a ON ahl.attendance_id = a.id
WHERE MONTH(a.date) = 5 AND YEAR(a.date) = 2026
GROUP BY e.id, e.first_name, e.last_name
"""
```

---

## Updated Chatbot Code

### Enhanced `generate_sql_query()` Prompt

```python
def generate_sql_query(user_question, schema):
    system_knowledge = """
=== DATABASE RELATIONSHIPS ===

1. CORE TABLES:
   - employees (id, employee_id, first_name, last_name, middle_name)
   - attendance (id, employee_id, date, am_in, am_out, pm_in, pm_out)
   - accredited_hours_log (id, attendance_id, employee_id, late_minutes, undertime_minutes)
   - leave_balances (id, employee_id, leave_code, year, available_credits, used_credits)
   - leave_types_config (leave_code, leave_name)

2. FOREIGN KEY RELATIONSHIPS:
   - attendance.employee_id → employees.id
   - accredited_hours_log.attendance_id → attendance.id
   - accredited_hours_log.employee_id → employees.id
   - leave_balances.employee_id → employees.id
   - leave_balances.leave_code → leave_types_config.leave_code

3. QUERY PATTERNS:
   
   a) Employee Search (flexible name matching):
      SELECT * FROM employees 
      WHERE first_name LIKE '%name%' 
         OR last_name LIKE '%name%' 
         OR middle_name LIKE '%name%'
   
   b) Attendance with Employee Name:
      SELECT e.first_name, e.last_name, a.date, a.am_in, a.pm_in
      FROM attendance a
      JOIN employees e ON a.employee_id = e.id
      WHERE e.employee_id = 'EMP_ID'
   
   c) Late Minutes (use accredited_hours_log, NOT accredited_hours_logs):
      SELECT e.first_name, e.last_name, a.date, ahl.late_minutes
      FROM accredited_hours_log ahl
      JOIN attendance a ON ahl.attendance_id = a.id
      JOIN employees e ON ahl.employee_id = e.id
      WHERE ahl.late_minutes > 0
      ORDER BY a.date DESC
   
   d) Leave Balance:
      SELECT e.first_name, e.last_name, lt.leave_name, 
             lb.available_credits, lb.used_credits
      FROM leave_balances lb
      JOIN employees e ON lb.employee_id = e.id
      JOIN leave_types_config lt ON lb.leave_code = lt.leave_code
      WHERE lb.year = YEAR(CURDATE())

4. IMPORTANT RULES:
   - Table name is 'accredited_hours_log' (singular), NOT 'accredited_hours_logs'
   - Always JOIN with employees table to get names
   - Use LIKE '%name%' for flexible name search
   - Date format: 'YYYY-MM-DD'
   - Minutes are stored as integers (480 = 8 hours)
   - Leave credits are decimal(10,6) for precision
"""
    
    prompt = f"""You are a MySQL expert for Prime HRIS. Generate a valid SELECT query.

{system_knowledge}

Database Schema:
{schema}

User Question: {user_question}

Return ONLY the SQL query, no explanation, no markdown.
"""
    # ... rest of function
```

---

## Testing Queries

### Test 1: Employee Search
```sql
-- Question: "Show me Jeremy Pogi's information"
SELECT * FROM employees 
WHERE first_name LIKE '%Jeremy%' 
  AND last_name LIKE '%Pogi%';
```

### Test 2: Late Minutes
```sql
-- Question: "When was Jeremy Pogi last late?"
SELECT e.first_name, e.last_name, a.date, ahl.late_minutes
FROM accredited_hours_log ahl
JOIN attendance a ON ahl.attendance_id = a.id
JOIN employees e ON ahl.employee_id = e.id
WHERE e.employee_id = '2024001' 
  AND ahl.late_minutes > 0
ORDER BY a.date DESC
LIMIT 1;
```

### Test 3: Leave Balance
```sql
-- Question: "What is Jeremy's leave balance?"
SELECT e.first_name, e.last_name, lt.leave_name, 
       lb.available_credits, lb.used_credits, lb.total_credits
FROM leave_balances lb
JOIN employees e ON lb.employee_id = e.id
JOIN leave_types_config lt ON lb.leave_code = lt.leave_code
WHERE e.employee_id = '2024001' 
  AND lb.year = 2026;
```

---

## Summary

### RDBMS Principles in Your System:
1. ✅ **Normalization**: No data duplication
2. ✅ **Referential Integrity**: Foreign keys with cascades
3. ✅ **Data Types**: Appropriate precision (decimal for leave, int for minutes)
4. ✅ **Constraints**: Unique keys prevent duplicates
5. ✅ **Indexing**: Foreign keys are indexed automatically

### Chatbot Must:
1. Use correct table name: `accredited_hours_log` (singular)
2. Always JOIN with `employees` for names
3. Use `LIKE '%name%'` for flexible search
4. Convert dates to 'YYYY-MM-DD' format
5. Understand relationships (attendance → accredited_hours_log)

### Next Steps:
1. Update your `generate_sql_query()` prompt with relationship knowledge
2. Test with complex queries (JOINs across 3+ tables)
3. Add error handling for missing relationships
4. Cache schema to reduce database calls
