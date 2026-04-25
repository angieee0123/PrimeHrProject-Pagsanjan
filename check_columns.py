import mysql.connector

db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena'
}

tables = ['addresses', 'contacts', 'employment_details', 'educations', 'trainings', 
          'work_experiences', 'government_ids', 'eligibilities']

try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()
    
    for table in tables:
        cursor.execute(f"DESCRIBE {table}")
        columns = cursor.fetchall()
        print(f"\n{table}:")
        for col in columns:
            print(f"  - {col[0]} ({col[1]})")
    
    cursor.close()
    conn.close()
    
except Exception as e:
    print(f"[ERROR] {e}")
