import mysql.connector

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor(dictionary=True)

# Check attendance records for Jeremy Pogi (employee_id=8)
cursor.execute("SELECT * FROM attendance WHERE employee_id = 8 ORDER BY date")
results = cursor.fetchall()

print(f"Found {len(results)} attendance records for Jeremy Pogi (employee_id=8)")
for row in results:
    print(f"Date: {row['date']}, AM In: {row['am_in']}, AM Out: {row['am_out']}, PM In: {row['pm_in']}, PM Out: {row['pm_out']}")

cursor.close()
conn.close()
