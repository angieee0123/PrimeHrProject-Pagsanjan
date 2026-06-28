from flask import Flask, render_template, request, jsonify, session
from flask_cors import CORS
import mysql.connector
from groq import Groq
import os
from datetime import datetime

app = Flask(__name__)
app.secret_key = os.urandom(24)
# Allow all origins for production (or specify Railway domains)
CORS(app, resources={r"/chat": {"origins": "*"}})

# MySQL config - Using environment variables for production
import os

DB_CONFIG = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'user': os.getenv('DB_USER', 'root'),
    'password': os.getenv('DB_PASSWORD', 'admin'),
    'database': os.getenv('DB_DATABASE', 'primehrismagdalena')
}

#wwwwwwww

# Groq client
groq_client = Groq(api_key="***REMOVED-GROQ-KEY***")

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
    """Fetch all table schemas from the database dynamically"""
    conn = get_db_connection()
    cursor = conn.cursor()

    cursor.execute("SHOW TABLES")
    tables = [row[0] for row in cursor.fetchall()]

    schema = ""
    for table in tables:
        cursor.execute(f"DESCRIBE `{table}`")
        columns = cursor.fetchall()
        col_defs = ", ".join([f"{col[0]} ({col[1]})" for col in columns])
        schema += f"Table `{table}`: {col_defs}\n"

    cursor.close()
    conn.close()
    return schema

def generate_sql_query(user_question, schema):
    """Use Groq to convert natural language question to SQL"""
    
    # HRIS System Knowledge Base
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

DATABASE TABLES:
- employees: Employee master data
- attendances: Daily time records (AM/PM/OT In/Out)
- accredited_hours_logs: Computed accredited hours with late/undertime
- leave_types_config: Leave type definitions
- leave_balances: Employee leave credits by year
- leave_applications: Leave requests (pending/approved/rejected)
- leave_transactions: Leave credit/debit history
- schedules: Employee work schedules
- deduction_types: Deduction categories
- employee_deductions: Employee-specific deductions
- loan_types: Loan type definitions with max amounts and interest rates
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

User Question: {user_question}

SQL Query:"""

    response = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.1,
        max_tokens=300
    )
    return response.choices[0].message.content.strip()

def execute_query(sql):
    """Execute SQL and return results"""
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute(sql)
    results = cursor.fetchall()
    cursor.close()
    conn.close()
    return results

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

Answer in a friendly, concise tone (3-5 sentences max). If no results, say so politely.
IMPORTANT: 
- All monetary amounts must be expressed in Philippine Peso (PHP). Never use dollar signs ($). Use the format "PHP X,XXX.XX" or "X,XXX.XX Philippine Pesos".
- When answering policy questions (like late deductions, leave rules), use the system knowledge above to provide accurate information.
- Be helpful and explain HR policies clearly in Tagalog or English based on the user's language."""

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
                'response': "Hello! I'm your Prime HRIS assistant. You can ask me about employees, attendance, leave balances, deductions, and HR policies. What would you like to know?",
                'follow_up_questions': [
                    "Sa ating system, nababawasan ba ang vacation leave kapag na-late?",
                    "How many employees are there?",
                    "Show leave balances for VL and SL",
                    "What are the leave types available?"
                ],
                'status': 'success'
            })

        # Get schema dynamically
        schema = get_db_schema()

        # Generate SQL from question
        sql = generate_sql_query(user_input, schema)

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
        results = execute_query(sql)

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
    port = int(os.getenv('PORT', 5001))
    app.run(debug=False, host='0.0.0.0', port=port)