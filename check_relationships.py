import mysql.connector
from datetime import datetime, date

db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena'
}

try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()
    
    # Get all tables
    cursor.execute("SHOW TABLES")
    tables = cursor.fetchall()
    
    print("Tables in database:")
    for table in tables:
        print(f"  - {table[0]}")
    
    print("\n" + "="*50)
    
    # Check foreign key relationships
    cursor.execute("""
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'primehrismagdalena'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    """)
    
    relationships = cursor.fetchall()
    
    print("\nForeign Key Relationships:")
    for rel in relationships:
        print(f"  {rel[0]}.{rel[1]} -> {rel[2]}.{rel[3]}")
    
    cursor.close()
    conn.close()
    
except Exception as e:
    print(f"[ERROR] {e}")
