from flask import Flask, render_template, request, jsonify, session
from flask_cors import CORS
import mysql.connector
from groq import Groq
import os
from datetime import datetime
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

app = Flask(__name__)
app.secret_key = os.urandom(24)
CORS(app, resources={r"/chat": {"origins": ["http://localhost:8000", "http://127.0.0.1:8000"]}})

# MySQL config
DB_CONFIG = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'user': os.getenv('DB_USER', 'root'),
    'password': os.getenv('DB_PASSWORD', 'admin'),
    'database': os.getenv('DB_NAME', 'primehrismagdalena'),
    'auth_plugin': 'mysql_native_password'
}

# Groq client
groq_client = Groq(api_key=os.getenv('GROQ_API_KEY'))

def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)

def get_policy_answer(question):
    """Return direct answers for common policy questions"""
    question_lower = question.lower()
    
    # Late deduction questions
    if any(word in question_lower for word in ['late', 'na-late', 'nalate']):
        if any(word in question_lower for word in ['calculate', 'compute', 'paano', 'how']):
            return {
                'answer': "Late deduction is calculated automatically: Late minutes are deducted from VL (Vacation Leave) first, then SL (Sick Leave). The system uses 480 minutes = 1 work day. There's a 5-minute grace period for both AM In and PM In. If late is fully covered by leave, you get full 8 hours accredited. If not enough leave credits, remaining becomes LWOP (Leave Without Pay).",
                'follow_up': [
                    "What is LWOP?",
                    "Show my leave balance",
                    "What is the grace period?"
                ]
            }
        if any(word in question_lower for word in ['bawas', 'deduct', 'nababawasan']):
            return {
                'answer': "Oo, nababawasan ang vacation leave (VL) kapag na-late. Ang sistema ay awtomatikong nagbabawas mula sa VL, pagkatapos sa SL (Sick Leave). 480 minuto = 1 araw ng trabaho. May 5 minuto grace period para sa AM at PM.",
                'follow_up': [
                    "Paano kung kulang ang leave credits?",
                    "Ano ang LWOP?",
                    "Ipakita ang leave balance"
                ]
            }
    
    # Grace period questions
    if 'grace' in question_lower:
        return {
            'answer': "The grace period is 5 minutes for both AM In (8:00) and PM In (13:00). If you clock in within 5 minutes after the scheduled time, it's not counted as late.",
            'follow_up': [
                "What are the working hours?",
                "How is late deduction calculated?",
                "Show attendance records"
            ]
        }
    
    # Working hours questions
    if any(word in question_lower for word in ['working hours', 'schedule', 'oras ng trabaho']):
        return {
            'answer': "Standard working hours: AM 8:00-12:00, PM 13:00-17:00 (8 hours total per day). Weekends (Saturday and Sunday) are non-working days. Overtime is tracked separately after PM Out.",
            'follow_up': [
                "What is the grace period?",
                "Show my attendance",
                "What leave types are available?"
            ]
        }
    
    # LWOP questions
    if 'lwop' in question_lower:
        return {
            'answer': "LWOP (Leave Without Pay) is applied when your late time exceeds your available leave credits. For example, if you're late 180 minutes but only have 60 minutes worth of VL/SL, the remaining 120 minutes becomes LWOP and will be deducted from your salary.",
            'follow_up': [
                "How is late deduction calculated?",
                "Show my leave balance",
                "What are the leave types?"
            ]
        }
    
    return None

def get_db_schema():
    """Fetch all table schemas from the database dynamically with sample data"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()

        cursor.execute("SHOW TABLES")
        tables = [row[0] for row in cursor.fetchall()]

        schema = "=== DATABASE SCHEMA ===\n\n"
        for table in tables:
            cursor.execute(f"DESCRIBE `{table}`")
            columns = cursor.fetchall()
            col_defs = ", ".join([f"{col[0]} ({col[1]})" for col in columns])
            schema += f"Table `{table}`: {col_defs}\n"
            
            # Get row count for context
            cursor.execute(f"SELECT COUNT(*) FROM `{table}`")
            count = cursor.fetchone()[0]
            schema += f"  → {count} records\n"

        cursor.close()
        conn.close()
        return schema
    except Exception as e:
        print(f"Schema fetch error: {str(e)}")
        return "Schema unavailable"

def generate_sql_query(user_question, schema):
    """Use Groq to convert natural language question to SQL"""
    
    # HRIS System Knowledge Base with RDBMS Relationships
    system_knowledge = """
=== PRIME HRIS MAGDALENA SYSTEM RULES ===

ATTENDANCE & LEAVE POLICIES:
1. LATE DEDUCTION FROM LEAVE:
   - YES, vacation leave (VL) is deducted when an employee is late
   - System automatically deducts late minutes from VL first, then SL (Sick Leave)
   - Conversion: 480 minutes = 1 work day (8 hours)
   - If late is fully covered by leave credits, employee gets full 8 hours accredited
   - If partially covered, remaining late time becomes LWOP (Leave Without Pay)
   - Grace period: 5 minutes for both AM and PM

2. ATTENDANCE STATUS:
   - Present: All 4 time logs (AM In/Out, PM In/Out) recorded
   - Absent: No time logs at all on working day
   - Abandoned: Clocked in but never clocked out (single period only)
   - Incomplete: Has some attendance but missing logs
   - On Leave: Approved leave application

3. WORKING HOURS:
   - Standard schedule: AM 8:00-12:00, PM 13:00-17:00 (8 hours total)
   - Weekends (Saturday/Sunday) are non-working days
   - Overtime (OT) is tracked separately after PM Out

4. LEAVE TYPES:
   - VL (Vacation Leave): Accrued, cumulative, monetizable
   - SL (Sick Leave): Accrued, cumulative, monetizable
   - SPL (Special Privilege Leave): 3 days annually
   - ML (Maternity Leave): 105 days
   - PL (Paternity Leave): 7 days
   - VAWC Leave: 10 days
   - Solo Parent Leave: 7 days
   - Study Leave, Rehabilitation Leave, etc.

5. ACCREDITED HOURS:
   - Calculated based on actual time worked minus late/undertime
   - Grace period applied: 5 minutes for AM In and PM In
   - Late deductions automatically processed from leave balances
   - LWOP (Leave Without Pay) applied when insufficient leave credits

6. DEDUCTIONS:
   - GSIS (Government Service Insurance System)
   - PhilHealth (Philippine Health Insurance)
   - Pag-IBIG (Home Development Mutual Fund)
   - Loans: GSIS Salary, GSIS Policy, GSIS Emergency, Pag-IBIG MPL, Pag-IBIG Calamity
   - Tax withholding

=== DATABASE STRUCTURE & RELATIONSHIPS ===

CORE TABLES:
1. employees (id, employee_id, first_name, last_name, middle_name, email)
   - Primary Key: id (bigint) ← THIS IS WHAT FOREIGN KEYS REFERENCE!
   - Unique: employee_id (e.g., '2024001') ← This is just a display ID, NOT used in JOINs

2. attendance (id, employee_id, date, am_in, am_out, pm_in, pm_out, accredited_hours)
   - Primary Key: id
   - Foreign Key: employee_id → employees.id (NOT employees.employee_id!)
   - Unique: (employee_id, date)

3. accredited_hours_log (id, attendance_id, employee_id, late_minutes, undertime_minutes, lwop_minutes)
   - Primary Key: id
   - Foreign Key: attendance_id → attendance.id
   - Foreign Key: employee_id → employees.id (NOT employees.employee_id!)
   - NOTE: Table name is SINGULAR 'accredited_hours_log' NOT 'accredited_hours_logs'

4. leave_balances (id, employee_id, leave_code, year, total_credits, used_credits, available_credits)
   - Primary Key: id
   - Foreign Key: employee_id → employees(id) ON DELETE CASCADE
   - Foreign Key: leave_code → leave_types_config(leave_code)
   - Unique: (employee_id, leave_code, year)

5. leave_types_config (leave_code, leave_name, is_accrued, is_cumulative, is_monetizable)
   - Primary Key: leave_code (e.g., 'VL', 'SL', 'SPL')

6. leave_applications (id, employee_id, leave_code, start_date, end_date, status)
   - Foreign Key: employee_id → employees(id)
   - Foreign Key: leave_code → leave_types_config(leave_code)

=== QUERY PATTERNS (RDBMS BEST PRACTICES) ===

1. EMPLOYEE SEARCH (Flexible Name Matching):
   SELECT * FROM employees 
   WHERE first_name LIKE '%name%' 
      OR last_name LIKE '%name%' 
      OR middle_name LIKE '%name%'

2. ATTENDANCE WITH EMPLOYEE NAME (Always JOIN):
   SELECT e.first_name, e.last_name, a.date, a.am_in, a.pm_in
   FROM attendance a
   JOIN employees e ON a.employee_id = e.id
   WHERE e.first_name LIKE '%name%' OR e.last_name LIKE '%name%'
   ORDER BY a.date DESC
   
   CRITICAL: JOIN ON a.employee_id = e.id (NOT e.employee_id!)

3. LATE MINUTES (Use accredited_hours_log - SINGULAR!):
   SELECT e.first_name, e.last_name, a.date, ahl.late_minutes
   FROM accredited_hours_log ahl
   JOIN attendance a ON ahl.attendance_id = a.id
   JOIN employees e ON ahl.employee_id = e.id
   WHERE ahl.late_minutes > 0
   ORDER BY a.date DESC

4. LEAVE BALANCE (JOIN with leave_types_config for names):
   SELECT e.first_name, e.last_name, lt.leave_name, 
          lb.available_credits, lb.used_credits
   FROM leave_balances lb
   JOIN employees e ON lb.employee_id = e.id
   JOIN leave_types_config lt ON lb.leave_code = lt.leave_code
   WHERE lb.year = YEAR(CURDATE())

5. LAST TIME LATE (Most Recent Record):
   SELECT e.first_name, e.last_name, a.date, ahl.late_minutes
   FROM accredited_hours_log ahl
   JOIN attendance a ON ahl.attendance_id = a.id
   JOIN employees e ON ahl.employee_id = e.id
   WHERE e.employee_id = 'EMP_ID' AND ahl.late_minutes > 0
   ORDER BY a.date DESC
   LIMIT 1

6. ATTENDANCE ON SPECIFIC DATE:
   SELECT e.first_name, e.last_name, a.date, a.am_in, a.am_out, a.pm_in, a.pm_out
   FROM attendance a
   JOIN employees e ON a.employee_id = e.id
   WHERE (e.first_name LIKE '%name%' OR e.last_name LIKE '%name%')
     AND a.date = '2026-05-19'

CRITICAL RULES:
- Table name is 'accredited_hours_log' (SINGULAR), NOT 'accredited_hours_logs'
- ALWAYS JOIN with employees table to include first_name and last_name in results
- JOIN SYNTAX: attendance.employee_id = employees.id (NOT employees.employee_id!)
- The employees table has TWO id columns:
  * id (bigint) ← PRIMARY KEY, used in JOINs
  * employee_id (varchar) ← Display ID like '2024001', NOT used in JOINs
- Use LIKE '%name%' with wildcards for flexible name search
- Date format: 'YYYY-MM-DD' (convert "May 18, 2026" to '2026-05-18')
- Minutes are integers (480 = 8 hours = 1 day)
- Leave credits are decimal(10,6) for precision
- Use proper JOINs to maintain referential integrity
"""
    
    prompt = f"""You are a MySQL expert for the Prime HRIS Magdalena system. Given the database schema and system knowledge below, generate a valid MySQL SELECT query to answer the user's question.

{system_knowledge}

Database Schema:
{schema}

Rules:
- Only generate SELECT queries, never INSERT, UPDATE, DELETE, or DROP
- Return ONLY the raw SQL query, no explanation, no markdown, no backticks
- If the question cannot be answered from the schema, return: CANNOT_ANSWER
- All monetary values are in Philippine Peso (PHP), never use dollar signs
- Use the system knowledge above to understand HR policies and business rules
- For late minutes queries, use accredited_hours_log table (singular, not plural) with late_minutes column
- For employee name searches, use LIKE '%name%' with wildcards on first_name, last_name, or middle_name
- For "last time" or "most recent", use ORDER BY date DESC LIMIT 1
- Convert date strings to MySQL date format 'YYYY-MM-DD' (e.g., "May 19, 2026" becomes '2026-05-19')
- Always include employee name in results by JOINing with employees table
- For attendance queries, use the attendance table and JOIN with employees
- When user asks about specific date, use WHERE a.date = 'YYYY-MM-DD'

CRITICAL JOIN SYNTAX:
- CORRECT: JOIN employees e ON a.employee_id = e.id
- WRONG: JOIN employees e ON a.employee_id = e.employee_id
- The foreign key references employees.id (PRIMARY KEY), NOT employees.employee_id (display ID)

User Question: {user_question}

SQL Query:"""

    try:
        response = groq_client.chat.completions.create(
            messages=[{"role": "user", "content": prompt}],
            model="llama-3.3-70b-versatile",
            temperature=0.1,
            max_tokens=300
        )
        return response.choices[0].message.content.strip()
    except Exception as e:
        print(f"SQL generation error: {str(e)}")
        return "CANNOT_ANSWER"

def execute_query(sql):
    """Execute SQL and return results with error handling"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute(sql)
        results = cursor.fetchall()
        cursor.close()
        conn.close()
        print(f"Query executed successfully. Rows returned: {len(results)}")
        return results
    except mysql.connector.Error as e:
        print(f"SQL Execution Error: {str(e)}")
        print(f"SQL Query: {sql}")
        raise Exception(f"Database query failed: {str(e)}")

def generate_natural_response(user_question, sql, results):
    """Use Groq to convert SQL results into a conversational response"""
    results_preview = str(results[:10]) if results else "No results found"
    
    # HRIS System Knowledge for responses
    system_knowledge = """
=== PRIME HRIS MAGDALENA SYSTEM RULES ===

KEY POLICIES TO MENTION WHEN RELEVANT:
1. Late Deduction: YES, late minutes are automatically deducted from VL first, then SL. 480 minutes = 1 day.
2. Grace Period: 5 minutes for AM In and PM In
3. Working Hours: 8:00-12:00 (AM), 13:00-17:00 (PM) = 8 hours total
4. Weekends: Saturday and Sunday are non-working days
5. Leave Types: VL, SL (accrued, cumulative, monetizable), SPL (3 days), ML (105 days), PL (7 days), etc.
6. LWOP: Applied when late time exceeds available leave credits
7. Accredited Hours: Actual work time minus late/undertime, with grace period applied
"""

    prompt = f"""You are a friendly HR assistant for the Prime HRIS Magdalena system. A user asked a question, a SQL query was run, and here are the results. 
Answer the user's question naturally and conversationally based on the results and system knowledge.

{system_knowledge}

User Question: {user_question}
SQL Query Used: {sql}
Query Results: {results_preview}
Total Records Found: {len(results)}

Answer in a friendly, concise tone (3-5 sentences max). 
IMPORTANT: 
- If no results found, politely say "No attendance record found for [name] on [date]." and suggest checking nearby dates or if it was a weekend/holiday.
- All monetary amounts must be expressed in Philippine Peso (PHP). Never use dollar signs ($). Use the format "PHP X,XXX.XX" or "X,XXX.XX Philippine Pesos".
- When answering policy questions (like late deductions, leave rules), use the system knowledge above to provide accurate information.
- Be helpful and explain HR policies clearly in Tagalog or English based on the user's language.
- If the date might be a weekend (Saturday/Sunday), mention that weekends are non-working days."""

    response = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.7,
        max_tokens=300
    )
    return response.choices[0].message.content.strip()

@app.route('/')
def home():
    session['conversation_history'] = []
    return render_template('index.html')

@app.route('/chat', methods=['POST'])
def chat():
    try:
        user_input = request.json.get('message', '').strip()
        if not user_input:
            return jsonify({'error': 'No message provided'}), 400

        if 'conversation_history' not in session:
            session['conversation_history'] = []

        session['conversation_history'].append({
            'user': user_input,
            'timestamp': datetime.now().isoformat()
        })
        if len(session['conversation_history']) > 5:
            session['conversation_history'] = session['conversation_history'][-5:]

        # Greeting handler
        greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'kumusta', 'kamusta']
        if any(g in user_input.lower() for g in greetings) and len(user_input.split()) <= 6:
            return jsonify({
                'response': "Hello! I'm your Prime HRIS assistant. I can fetch real-time data from the database about employees, attendance, leave balances, deductions, and HR policies. What would you like to know?",
                'follow_up_questions': [
                    "How many employees are in the system?",
                    "Show recent attendance records",
                    "What are the leave types available?",
                    "Show employee deductions"
                ],
                'status': 'success'
            })

        # Get schema dynamically
        schema = get_db_schema()

        # Generate SQL from question
        sql = generate_sql_query(user_input, schema)
        
        # Clean up SQL (remove markdown formatting if present)
        sql = sql.replace('```sql', '').replace('```', '').strip()
        
        print(f"Generated SQL: {sql}")  # Debug logging

        if sql == "CANNOT_ANSWER" or not sql.lower().startswith("select"):
            # Check if it's a policy question that doesn't need database query
            policy_keywords = ['late', 'deduct', 'leave', 'policy', 'rule', 'grace', 'working hours', 'schedule', 'lwop', 'bawas', 'na-late']
            if any(keyword in user_input.lower() for keyword in policy_keywords):
                # Try to get direct answer first
                direct_answer = get_policy_answer(user_input)
                if direct_answer:
                    session['conversation_history'][-1]['bot'] = direct_answer['answer']
                    session.modified = True
                    return jsonify({
                        'response': direct_answer['answer'],
                        'follow_up_questions': direct_answer['follow_up'],
                        'status': 'success'
                    })
                
                # Fallback to Groq API for complex policy questions
                policy_prompt = f"""You are an HR assistant for Prime HRIS Magdalena. Answer this HR policy question based on the system rules below:

=== SYSTEM RULES ===
1. Late Deduction: YES, late minutes are automatically deducted from VL (Vacation Leave) first, then SL (Sick Leave). 480 minutes = 1 work day.
2. Grace Period: 5 minutes for both AM In and PM In
3. Working Hours: 8:00-12:00 (AM), 13:00-17:00 (PM) = 8 hours total per day
4. Weekends: Saturday and Sunday are non-working days
5. Leave Types: VL, SL (accrued, cumulative, monetizable), SPL (3 days), ML (105 days), PL (7 days), VAWC (10 days), Solo Parent (7 days)
6. LWOP (Leave Without Pay): Applied when late time exceeds available leave credits
7. Accredited Hours: Actual work time minus late/undertime, with 5-minute grace period applied

User Question: {user_input}

Provide a clear, friendly answer in 2-4 sentences. Match the user's language (Tagalog or English)."""
                
                try:
                    policy_response = groq_client.chat.completions.create(
                        messages=[{"role": "user", "content": policy_prompt}],
                        model="llama-3.3-70b-versatile",
                        temperature=0.7,
                        max_tokens=300
                    )
                    
                    response_text = policy_response.choices[0].message.content.strip()
                    
                    # Save to session
                    session['conversation_history'][-1]['bot'] = response_text
                    session.modified = True
                    
                    return jsonify({
                        'response': response_text,
                        'follow_up_questions': [
                            "How is accredited hours calculated?",
                            "What happens if I don't have enough leave credits?",
                            "Show my leave balance"
                        ],
                        'status': 'success'
                    })
                except Exception as policy_err:
                    print(f"Policy response error: {str(policy_err)}")
                    import traceback
                    traceback.print_exc()
                    # Fallback to manual response
                    return jsonify({
                        'response': "Late deduction is calculated automatically in our system. When you're late, the system deducts from your VL (Vacation Leave) first, then SL (Sick Leave). The conversion is 480 minutes = 1 work day. There's a 5-minute grace period for both AM and PM.",
                        'follow_up_questions': [
                            "What is LWOP?",
                            "Show leave balances",
                            "What are the working hours?"
                        ],
                        'status': 'success'
                    })
            
            return jsonify({
                'response': "I'm not sure how to answer that based on the available data. Could you rephrase or ask about employees, attendance, leave balances, or HR policies?",
                'follow_up_questions': [
                    "How many employees are there?",
                    "Show leave balances",
                    "What are the attendance rules?"
                ],
                'status': 'success'
            })

        # Execute query
        try:
            results = execute_query(sql)
        except Exception as query_err:
            print(f"Query execution failed: {str(query_err)}")
            return jsonify({
                'response': f"I tried to fetch the data but encountered an issue with the query. The error was: {str(query_err)}. Could you rephrase your question?",
                'follow_up_questions': [
                    "Show all employees",
                    "What are the attendance records?",
                    "Show leave balances"
                ],
                'status': 'error'
            })

        # Generate natural language response
        response_text = generate_natural_response(user_input, sql, results)

        session['conversation_history'][-1]['bot'] = response_text
        session.modified = True

        return jsonify({
            'response': response_text,
            'follow_up_questions': [
                "Show me more details",
                "Filter by a different city",
                "How many total records are there?"
            ],
            'status': 'success'
        })

    except mysql.connector.Error as db_err:
        return jsonify({
            'response': f"Database error: {str(db_err)}",
            'status': 'error'
        }), 500
    except Exception as e:
        print(f"Error: {str(e)}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'response': 'Sorry, an error occurred. Please try again.',
            'error': str(e),
            'status': 'error'
        }), 500

if __name__ == '__main__':
    app.run(debug=True, port=5001)
