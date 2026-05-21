"""
Test the CORRECT SQL syntax
"""
import mysql.connector

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

# CORRECT SQL
correct_sql = """
SELECT e.first_name, e.last_name, a.date, a.am_in, a.am_out, a.pm_in, a.pm_out 
FROM attendance a 
JOIN employees e ON a.employee_id = e.id
WHERE (e.first_name LIKE '%Jeremy%' OR e.last_name LIKE '%Pogi%')
  AND a.date = '2026-05-18'
"""

# WRONG SQL (what the AI was generating)
wrong_sql = """
SELECT e.first_name, e.last_name, a.date, a.am_in, a.am_out, a.pm_in, a.pm_out 
FROM attendance a 
JOIN employees e ON a.employee_id = e.employee_id
WHERE (e.first_name LIKE '%Jeremy%' OR e.last_name LIKE '%Pogi%')
  AND a.date = '2026-05-18'
"""

print("="*70)
print("TESTING CORRECT SQL")
print("="*70)
print(correct_sql)

try:
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute(correct_sql)
    results = cursor.fetchall()
    
    print(f"\nResults: {len(results)} row(s) found")
    if results:
        print("\nSUCCESS! Data found:")
        for row in results:
            print(f"  Employee: {row['first_name']} {row['last_name']}")
            print(f"  Date: {row['date']}")
            print(f"  AM In: {row['am_in']}")
            print(f"  AM Out: {row['am_out']}")
            print(f"  PM In: {row['pm_in']}")
            print(f"  PM Out: {row['pm_out']}")
    
    cursor.close()
    conn.close()
    
except Exception as e:
    print(f"\nERROR: {str(e)}")

print("\n" + "="*70)
print("TESTING WRONG SQL (for comparison)")
print("="*70)
print(wrong_sql)

try:
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute(wrong_sql)
    results = cursor.fetchall()
    
    print(f"\nResults: {len(results)} row(s) found")
    if results:
        print("\nData found:")
        for row in results:
            print(row)
    else:
        print("\nNO DATA FOUND (as expected - wrong JOIN syntax)")
    
    cursor.close()
    conn.close()
    
except Exception as e:
    print(f"\nERROR: {str(e)}")

print("\n" + "="*70)
print("CONCLUSION")
print("="*70)
print("The CORRECT SQL uses: JOIN employees e ON a.employee_id = e.id")
print("The WRONG SQL uses: JOIN employees e ON a.employee_id = e.employee_id")
print("\nYour chatbot MUST use the correct syntax!")
