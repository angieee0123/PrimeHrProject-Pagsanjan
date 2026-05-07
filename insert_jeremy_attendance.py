import mysql.connector

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor()

with open(r'c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\database\jeremy_pogi_attendance_april_2026.sql', 'r') as f:
    sql_content = f.read()
    
for statement in sql_content.split(';'):
    statement = statement.strip()
    if statement and not statement.startswith('--'):
        cursor.execute(statement)

conn.commit()
cursor.close()
conn.close()

print("Successfully inserted 22 attendance records for Jeremy Pogi (April 2026)")
