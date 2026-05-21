"""
Debug script to test SQL generation for Jeremy Pogi May 18, 2026
"""
from groq import Groq
import mysql.connector

# Groq client
groq_client = Groq(api_key="***REMOVED-GROQ-KEY***")

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

def get_db_schema():
    """Fetch schema"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("SHOW TABLES")
        tables = [row[0] for row in cursor.fetchall()]
        schema = "=== DATABASE SCHEMA ===\n\n"
        for table in tables:
            cursor.execute(f"DESCRIBE `{table}`")
            columns = cursor.fetchall()
            col_defs = ", ".join([f"{col[0]} ({col[1]})" for col in columns])
            schema += f"Table `{table}`: {col_defs}\n"
        cursor.close()
        conn.close()
        return schema
    except Exception as e:
        return f"Schema error: {str(e)}"

def test_sql_generation():
    """Test SQL generation"""
    
    user_question = "What is the attendance record of Jeremy Pogi last may 18, 2026"
    
    print("="*70)
    print("TESTING SQL GENERATION")
    print("="*70)
    print(f"\nUser Question: {user_question}\n")
    
    schema = get_db_schema()
    print("Schema fetched successfully\n")
    
    # Simplified prompt
    prompt = f"""You are a MySQL expert. Generate a SELECT query to answer this question.

Database has these key tables:
- employees (id, employee_id, first_name, last_name, middle_name)
  * id = PRIMARY KEY (bigint) <- Use this in JOINs!
  * employee_id = Display ID (varchar like '2024001') <- NOT used in JOINs!
  
- attendance (id, employee_id, date, am_in, am_out, pm_in, pm_out)
  * employee_id is FOREIGN KEY that references employees.id

Rules:
- Return ONLY the SQL query, no explanation
- Use JOIN to get employee names
- CRITICAL: JOIN employees e ON a.employee_id = e.id (NOT e.employee_id!)
- Use LIKE '%name%' for name search
- Convert "may 18, 2026" to '2026-05-18'
- If you can't answer, return: CANNOT_ANSWER

Question: {user_question}

SQL Query:"""
    
    print("Sending to Groq API...")
    print("-"*70)
    
    try:
        response = groq_client.chat.completions.create(
            messages=[{"role": "user", "content": prompt}],
            model="llama-3.3-70b-versatile",
            temperature=0.1,
            max_tokens=300
        )
        
        sql = response.choices[0].message.content.strip()
        sql = sql.replace('```sql', '').replace('```', '').strip()
        
        print(f"\nGenerated SQL:\n{sql}\n")
        print("="*70)
        
        if sql == "CANNOT_ANSWER" or not sql.lower().startswith("select"):
            print("\n❌ PROBLEM: AI returned CANNOT_ANSWER or invalid SQL")
            return
        
        # Test execution
        print("\nTesting query execution...")
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute(sql)
        results = cursor.fetchall()
        cursor.close()
        conn.close()
        
        print(f"Query executed successfully!")
        print(f"Results: {len(results)} row(s) found\n")
        
        if results:
            for row in results:
                print(row)
        else:
            print("No results found")
            
    except Exception as e:
        print(f"\nERROR: {str(e)}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    test_sql_generation()
