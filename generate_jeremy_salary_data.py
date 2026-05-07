import mysql.connector
from datetime import datetime

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor(dictionary=True)

# Get Jeremy's salary info
cursor.execute("""
    SELECT e.id, d.monthly_rate 
    FROM employees e
    JOIN employment_details ed ON e.id = ed.employee_id
    JOIN designations d ON ed.designation_id = d.id
    WHERE e.id = 8
""")
salary_info = cursor.fetchone()

if not salary_info:
    print("Error: Could not find salary info for Jeremy Pogi")
    exit()

monthly_rate = float(salary_info['monthly_rate'])
daily_rate = monthly_rate / 22  # 22 working days per month
hourly_rate = daily_rate / 8    # 8 hours per day

print(f"Monthly Rate: {monthly_rate}")
print(f"Daily Rate: {daily_rate:.2f}")
print(f"Hourly Rate: {hourly_rate:.2f}")

# Get all April 2026 attendance records for Jeremy
cursor.execute("""
    SELECT id, date, am_in, am_out, pm_in, pm_out 
    FROM attendance 
    WHERE employee_id = 8 AND date BETWEEN '2026-04-01' AND '2026-04-30'
    ORDER BY date
""")
attendance_records = cursor.fetchall()

print(f"\nFound {len(attendance_records)} attendance records")

inserted_hours = 0
inserted_salary = 0

for record in attendance_records:
    attendance_id = record['id']
    work_date = record['date']
    
    # Insert accredited_hours_log
    accredited_sql = """
        INSERT INTO accredited_hours_log 
        (attendance_id, employee_id, schedule_id, am_accredited_minutes, pm_accredited_minutes, 
         ot_minutes, late_minutes, undertime_minutes, total_accredited_minutes, total_actual_minutes,
         am_grace_applied, pm_grace_applied, computation_notes, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    
    now = datetime.now()
    accredited_data = (
        attendance_id, 8, 1,  # attendance_id, employee_id, schedule_id
        240, 240,  # am_accredited_minutes, pm_accredited_minutes (4 hours each)
        0, 0, 0,   # ot_minutes, late_minutes, undertime_minutes
        480, 480,  # total_accredited_minutes, total_actual_minutes (8 hours)
        0, 0,      # am_grace_applied, pm_grace_applied
        f'Auto-generated for April 2026', now, now
    )
    
    try:
        cursor.execute(accredited_sql, accredited_data)
        accredited_hours_log_id = cursor.lastrowid
        inserted_hours += 1
        
        # Insert daily_salary_computations
        salary_sql = """
            INSERT INTO daily_salary_computations
            (employee_id, accredited_hours_log_id, work_date, monthly_rate, daily_rate, hourly_rate,
             daily_basic_pay, ot_pay, late_deduction, undertime_deduction, daily_gross_pay,
             is_holiday, is_rest_day, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        salary_data = (
            8, accredited_hours_log_id, work_date,
            monthly_rate, daily_rate, hourly_rate,
            daily_rate, 0.00, 0.00, 0.00, daily_rate,  # full pay, no deductions
            0, 0, now, now
        )
        
        cursor.execute(salary_sql, salary_data)
        inserted_salary += 1
        
    except mysql.connector.IntegrityError as e:
        print(f"Skipped {work_date}: {e}")

conn.commit()
cursor.close()
conn.close()

print(f"\nSuccessfully inserted:")
print(f"  - {inserted_hours} accredited_hours_log records")
print(f"  - {inserted_salary} daily_salary_computations records")
