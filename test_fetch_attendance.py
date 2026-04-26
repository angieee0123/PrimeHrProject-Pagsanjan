import mysql.connector
import os
from datetime import datetime

# Database connection
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv('DB_HOST', 'localhost'),
        user=os.getenv('DB_USER', 'root'),
        password=os.getenv('DB_PASSWORD', 'admin'),
        database=os.getenv('DB_NAME', 'primehrismagdalena')
    )

try:
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    # Fetch all attendance records
    query = """
        SELECT 
            a.id,
            a.employee_id,
            a.date,
            a.am_in,
            a.am_out,
            a.pm_in,
            a.pm_out,
            a.ot_in,
            a.ot_out,
            CONCAT(e.first_name, ' ', e.last_name) as employee_name,
            e.employee_id as emp_id
        FROM attendance a
        JOIN employees e ON a.employee_id = e.id
        ORDER BY a.date DESC, e.first_name ASC
    """
    
    cursor.execute(query)
    records = cursor.fetchall()
    
    print(f"Total records found: {len(records)}\n")
    
    if records:
        print("Attendance Records:")
        print("-" * 100)
        for record in records:
            print(f"Date: {record['date']}")
            print(f"Employee: {record['employee_name']} (ID: {record['emp_id']})")
            print(f"  AM In: {record['am_in'] or '--:--'} | AM Out: {record['am_out'] or '--:--'}")
            print(f"  PM In: {record['pm_in'] or '--:--'} | PM Out: {record['pm_out'] or '--:--'}")
            print(f"  OT In: {record['ot_in'] or '--:--'} | OT Out: {record['ot_out'] or '--:--'}")
            print("-" * 100)
    else:
        print("No attendance records found")
    
    cursor.close()
    conn.close()
    
except Exception as e:
    print(f"Error: {e}")
