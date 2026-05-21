# Prime HRIS Database Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         PRIME HRIS DATABASE                              │
│                    Entity-Relationship Diagram                           │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────┐
│     employees        │  ◄─── MASTER TABLE (All employees)
├──────────────────────┤
│ PK: id               │
│ UK: employee_id      │
│     first_name       │
│     last_name        │
│     middle_name      │
│     email            │
└──────────┬───────────┘
           │
           │ 1:N (One employee has many records)
           │
    ┌──────┴──────┬──────────────┬─────────────┬──────────────┐
    │             │              │             │              │
    ▼             ▼              ▼             ▼              ▼
┌─────────┐  ┌─────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
│attendance│  │  leave  │  │employee  │  │schedules │  │  leave   │
│         │  │balances │  │deductions│  │          │  │applications│
└────┬────┘  └─────────┘  └──────────┘  └──────────┘  └──────────┘
     │
     │ 1:1 (Each attendance has one log)
     │
     ▼
┌─────────────────────┐
│accredited_hours_log │  ◄─── COMPUTED DATA (Late, undertime, LWOP)
└─────────────────────┘


═══════════════════════════════════════════════════════════════════════════
                        DETAILED RELATIONSHIPS
═══════════════════════════════════════════════════════════════════════════

1. EMPLOYEES → ATTENDANCE (1:N)
   ┌──────────────────────┐         ┌──────────────────────┐
   │     employees        │         │     attendance       │
   ├──────────────────────┤         ├──────────────────────┤
   │ PK: id (8)          │◄────────┤ FK: employee_id (8)  │
   │     employee_id      │         │     date             │
   │     first_name       │         │     am_in, am_out    │
   │     last_name        │         │     pm_in, pm_out    │
   └──────────────────────┘         └──────────────────────┘
   
   One employee (Jeremy Pogi, id=8) has many attendance records


2. ATTENDANCE → ACCREDITED_HOURS_LOG (1:1)
   ┌──────────────────────┐         ┌──────────────────────────┐
   │     attendance       │         │  accredited_hours_log    │
   ├──────────────────────┤         ├──────────────────────────┤
   │ PK: id (279)        │◄────────┤ FK: attendance_id (279)  │
   │     employee_id      │         │ FK: employee_id          │
   │     date             │         │     late_minutes         │
   │     am_in, pm_in     │         │     undertime_minutes    │
   └──────────────────────┘         │     lwop_minutes         │
                                    └──────────────────────────┘
   
   Each attendance record has ONE computed hours log


3. EMPLOYEES → LEAVE_BALANCES (1:N)
   ┌──────────────────────┐         ┌──────────────────────┐
   │     employees        │         │   leave_balances     │
   ├──────────────────────┤         ├──────────────────────┤
   │ PK: id (8)          │◄────────┤ FK: employee_id (8)  │
   │     employee_id      │         │ FK: leave_code       │
   │     first_name       │         │     year (2026)      │
   └──────────────────────┘         │     total_credits    │
                                    │     used_credits     │
                                    │     available_credits│
                                    └──────────┬───────────┘
                                               │
                                               │ N:1
                                               ▼
                                    ┌──────────────────────┐
                                    │ leave_types_config   │
                                    ├──────────────────────┤
                                    │ PK: leave_code (VL)  │
                                    │     leave_name       │
                                    │     is_accrued       │
                                    └──────────────────────┘
   
   One employee has multiple leave balances (VL, SL, SPL, etc.)
   Each leave balance references a leave type definition


═══════════════════════════════════════════════════════════════════════════
                        EXAMPLE DATA FLOW
═══════════════════════════════════════════════════════════════════════════

SCENARIO: "What is Jeremy Pogi's attendance on May 18, 2026?"

Step 1: Find employee
┌──────────────────────┐
│     employees        │
├──────────────────────┤
│ id: 8                │  ◄─── Found: Jeremy Pogi
│ employee_id: 2024001 │
│ first_name: Jeremy   │
│ last_name: Pogi      │
└──────────────────────┘

Step 2: Find attendance
┌──────────────────────┐
│     attendance       │
├──────────────────────┤
│ id: 279              │  ◄─── Found: May 18, 2026
│ employee_id: 8       │       (Links to Jeremy)
│ date: 2026-05-18     │
│ am_in: 10:26:00      │
│ am_out: 11:42:00     │
│ pm_in: 13:06:00      │
│ pm_out: 17:09:00     │
└──────────────────────┘

Step 3: Get computed hours
┌──────────────────────────┐
│  accredited_hours_log    │
├──────────────────────────┤
│ id: 278                  │  ◄─── Computed data
│ attendance_id: 279       │       (Links to attendance)
│ employee_id: 8           │
│ late_minutes: 152        │       (2 hours 32 min late)
│ undertime_minutes: 18    │       (18 min undertime)
│ total_accredited: 480    │       (Full 8 hours credited)
└──────────────────────────┘

Step 4: Check leave deduction
┌──────────────────────┐
│   leave_balances     │
├──────────────────────┤
│ employee_id: 8       │  ◄─── VL was deducted
│ leave_code: VL       │
│ year: 2026           │
│ available: 2.895833  │       (152 min = 0.316667 days deducted)
└──────────────────────┘


═══════════════════════════════════════════════════════════════════════════
                        SQL QUERY PATTERN
═══════════════════════════════════════════════════════════════════════════

SELECT 
    e.first_name,           -- From employees table
    e.last_name,            -- From employees table
    a.date,                 -- From attendance table
    a.am_in,                -- From attendance table
    a.pm_in,                -- From attendance table
    ahl.late_minutes,       -- From accredited_hours_log table
    ahl.undertime_minutes   -- From accredited_hours_log table
FROM 
    accredited_hours_log ahl
    JOIN attendance a ON ahl.attendance_id = a.id      -- Link log to attendance
    JOIN employees e ON ahl.employee_id = e.id         -- Link to employee
WHERE 
    (e.first_name LIKE '%Jeremy%' OR e.last_name LIKE '%Pogi%')
    AND a.date = '2026-05-18'


═══════════════════════════════════════════════════════════════════════════
                        KEY RDBMS CONCEPTS
═══════════════════════════════════════════════════════════════════════════

1. PRIMARY KEY (PK)
   - Unique identifier for each row
   - Example: employees.id = 8

2. FOREIGN KEY (FK)
   - Links to another table's primary key
   - Example: attendance.employee_id → employees.id
   - Maintains referential integrity

3. CASCADE DELETE
   - Delete employee → automatically deletes all attendance
   - Delete attendance → automatically deletes accredited_hours_log

4. UNIQUE CONSTRAINT
   - Prevents duplicates
   - Example: (employee_id, date) in attendance table
   - Can't have two attendance records for same employee on same date

5. JOIN
   - Combines data from multiple tables
   - Example: JOIN employees to get first_name and last_name

6. NORMALIZATION
   - No duplicate data
   - Employee name stored once in employees table
   - Leave type defined once in leave_types_config


═══════════════════════════════════════════════════════════════════════════
                        CHATBOT QUERY FLOW
═══════════════════════════════════════════════════════════════════════════

User Question: "What is Jeremy Pogi's attendance on May 18, 2026?"
                              ↓
                    Parse natural language
                              ↓
                    Identify entities:
                    - Name: "Jeremy Pogi"
                    - Date: "May 18, 2026" → '2026-05-18'
                    - Action: Get attendance
                              ↓
                    Generate SQL with JOINs:
                    - attendance table (for time logs)
                    - employees table (for name)
                              ↓
                    Execute query on database
                              ↓
                    Get results (or empty if no data)
                              ↓
                    Generate natural language response
                              ↓
                    Return to user


═══════════════════════════════════════════════════════════════════════════
                        SUMMARY
═══════════════════════════════════════════════════════════════════════════

✅ RDBMS = Relational Database Management System
✅ Tables are connected through foreign keys
✅ JOINs combine data from multiple tables
✅ Referential integrity prevents orphaned records
✅ Normalization eliminates duplicate data
✅ Your chatbot now understands these relationships!

```
