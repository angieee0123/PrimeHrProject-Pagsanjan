from flask import Flask, render_template, request, jsonify, session
from flask_cors import CORS
import mysql.connector
from groq import Groq
import os
from datetime import datetime

app = Flask(__name__)
app.secret_key = os.urandom(24)
CORS(app, resources={r"/chat": {"origins": ["http://localhost:8000", "http://127.0.0.1:8000"]}})

# MySQL config
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

# Groq client
groq_client = Groq(api_key="***REMOVED-GROQ-KEY***")

def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)

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
    prompt = f"""You are a MySQL expert. Given the database schema below, generate a valid MySQL SELECT query to answer the user's question.

Database Schema:
{schema}

Rules:
- Only generate SELECT queries, never INSERT, UPDATE, DELETE, or DROP
- Return ONLY the raw SQL query, no explanation, no markdown, no backticks
- If the question cannot be answered from the schema, return: CANNOT_ANSWER

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

    prompt = f"""You are a friendly database assistant. A user asked a question, a SQL query was run, and here are the results. 
Answer the user's question naturally and conversationally based on the results.

User Question: {user_question}
SQL Query Used: {sql}
Query Results: {results_preview}
Total Records Found: {len(results)}

Answer in a friendly, concise tone (3-5 sentences max). If no results, say so politely."""

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
        greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening']
        if any(g in user_input.lower() for g in greetings) and len(user_input.split()) <= 6:
            return jsonify({
                'response': "Hello! I'm your database assistant. You can ask me anything about the data — like finding a person, listing records, or filtering by city, gender, and more. What would you like to know?",
                'follow_up_questions': [
                    "How many users are there?",
                    "Show all users from Calamba",
                    "List all female users"
                ],
                'status': 'success'
            })

        # Get schema dynamically
        schema = get_db_schema()

        # Generate SQL from question
        sql = generate_sql_query(user_input, schema)

        if sql == "CANNOT_ANSWER" or not sql.lower().startswith("select"):
            return jsonify({
                'response': "I'm not sure how to answer that based on the available data. Could you rephrase or ask something about the records in the database?",
                'follow_up_questions': [
                    "How many users are there?",
                    "Show users from Laguna",
                    "Find users named Juan"
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
    app.run(debug=True, port=5001)
