"""
Test script to verify attendance query for Jeremy Pogi on May 19, 2026
"""
import mysql.connector

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

def test_attendance_query():
    """Test the attendance query"""
    
    # The query that should be generated
    sql = """
    SELECT e.first_name, e.last_name, a.date, a.am_in, a.am_out, a.pm_in, a.pm_out
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    WHERE (e.first_name LIKE '%Jeremy%' OR e.last_name LIKE '%Pogi%')
      AND a.date = '2026-05-19'
    """
    
    print("Testing query:")
    print(sql)
    print("\n" + "="*60 + "\n")
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute(sql)
        results = cursor.fetchall()
        
        if results:
            print(f"Found {len(results)} record(s):")
            print()
            for row in results:
                print(f"Employee: {row['first_name']} {row['last_name']}")
                print(f"Date: {row['date']}")
                print(f"AM In: {row['am_in']}")
                print(f"AM Out: {row['am_out']}")
                print(f"PM In: {row['pm_in']}")
                print(f"PM Out: {row['pm_out']}")
        else:
            print("No attendance record found for Jeremy Pogi on May 19, 2026")
            print()
            print("Let's check what dates Jeremy Pogi has attendance:")
            
            check_sql = """
            SELECT e.first_name, e.last_name, a.date
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            WHERE (e.first_name LIKE '%Jeremy%' OR e.last_name LIKE '%Pogi%')
            ORDER BY a.date DESC
            LIMIT 10
            """
            cursor.execute(check_sql)
            dates = cursor.fetchall()
            
            if dates:
                print()
                print("Recent attendance dates for Jeremy Pogi:")
                for d in dates:
                    print(f"  - {d['date']}")
            else:
                print()
                print("No attendance records found for Jeremy Pogi at all!")
        
        cursor.close()
        conn.close()
        
    except mysql.connector.Error as e:
        print(f"Database error: {str(e)}")
    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    test_attendance_query()
