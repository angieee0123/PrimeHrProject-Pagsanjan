import mysql.connector
import os

# Database connection
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv('DB_HOST', 'localhost'),
        user=os.getenv('DB_USER', 'root'),
        password=os.getenv('DB_PASSWORD', 'admin'),
        database=os.getenv('DB_NAME', 'primehrismagdalena')
    )

try:
    print("Testing database connection...")
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    # Check if attendance table exists
    cursor.execute("SHOW TABLES LIKE 'attendance'")
    result = cursor.fetchone()
    if result:
        print("[OK] Attendance table exists")
    else:
        print("[ERROR] Attendance table NOT found")
    
    # Check attendance records
    cursor.execute("SELECT COUNT(*) as count FROM attendance")
    result = cursor.fetchone()
    print(f"[OK] Total attendance records: {result['count']}")
    
    # Check employees
    cursor.execute("SELECT COUNT(*) as count FROM employees")
    result = cursor.fetchone()
    print(f"[OK] Total employees: {result['count']}")
    
    # Get sample attendance data
    cursor.execute("""
        SELECT 
            a.*,
            CONCAT(e.first_name, ' ', e.last_name) as employee_name
        FROM attendance a
        JOIN employees e ON a.employee_id = e.id
        LIMIT 5
    """)
    records = cursor.fetchall()
    
    if records:
        print(f"\n[OK] Sample attendance records:")
        for record in records:
            print(f"  - {record['employee_name']}: {record['date']}")
    else:
        print("\n[WARNING] No attendance records found in database")
    
    cursor.close()
    conn.close()
    print("\n[OK] Database connection successful!")
    
except Exception as e:
    print(f"\n[ERROR] Error: {e}")
