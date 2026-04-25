import mysql.connector
from datetime import datetime
import hashlib

db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena'
}

employee_ids = ['2024001', '2024002', '2024003', '2024004', '2024005', 
                '2024006', '2024007', '2024008', '2024009', '2024010']

try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    # First check the users table structure
    cursor.execute("DESCRIBE users")
    columns = cursor.fetchall()
    print("Users table columns:")
    for col in columns:
        print(f"  - {col['Field']} ({col['Type']})")
    
    print("\n" + "="*50 + "\n")
    
    # Get employees
    cursor.execute("SELECT id, employee_id, first_name, last_name, email FROM employees WHERE employee_id IN (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                   tuple(employee_ids))
    employees = cursor.fetchall()
    
    print(f"Creating user accounts for {len(employees)} employees...\n")
    
    for emp in employees:
        emp_id = emp['id']
        emp_code = emp['employee_id']
        name = f"{emp['first_name']} {emp['last_name']}"
        email = emp['email']
        
        # Create username from first name + last name (lowercase, no spaces)
        username = f"{emp['first_name'].lower()}.{emp['last_name'].lower().replace(' ', '')}"
        
        # Default password: "password123" (should be changed on first login)
        password = "password123"
        
        # Check if username already exists
        cursor.execute("SELECT id FROM users WHERE username = %s", (username,))
        existing = cursor.fetchone()
        
        if existing:
            print(f"  [SKIP] User already exists: {username}")
            continue
        
        # Insert user (role: employee, status: Active)
        cursor.execute("""
            INSERT INTO users (username, email, password, employee_id, role, status)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (username, email, password, emp_id, 'employee', 'Active'))
        
        print(f"  [OK] Created user: {username} for {name}")
    
    conn.commit()
    print(f"\n[SUCCESS] User accounts created!")
    print("\nDefault credentials:")
    print("  Username: firstname.lastname (e.g., maria.cruz)")
    print("  Password: password123")
    
    cursor.close()
    conn.close()
    
except Exception as e:
    print(f"[ERROR] {e}")
    import traceback
    traceback.print_exc()
