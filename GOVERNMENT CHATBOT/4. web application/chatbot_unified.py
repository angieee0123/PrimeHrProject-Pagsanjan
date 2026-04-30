from flask import Flask, render_template, request, jsonify, session
from flask_cors import CORS
import mysql.connector
from groq import Groq
import os
import json
import pickle
from datetime import datetime
from pathlib import Path
import re

app = Flask(__name__)
app.secret_key = os.urandom(24)
CORS(app, resources={r"/chat": {"origins": ["http://localhost:8000", "http://127.0.0.1:8000"]}})

# MySQL config
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

# Groq client
groq_client = Groq(api_key="***REMOVED-GROQ-KEY***")

# Paths
LARAVEL_PATH = r"f:\PrimeHrProject-Magdalena\primeHrMagdalenaLaravel"
CACHE_FILE = os.path.join(LARAVEL_PATH, ".codebase_cache.pkl")
CACHE_METADATA = os.path.join(LARAVEL_PATH, ".codebase_metadata.json")

# ==================== DATABASE FUNCTIONS ====================

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
- All monetary values are in Philippine Peso (PHP), never use dollar signs

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

Answer in a friendly, concise tone (3-5 sentences max). If no results, say so politely.
IMPORTANT: All monetary amounts must be expressed in Philippine Peso (PHP). Never use dollar signs ($). Use the format "PHP X,XXX.XX" or "X,XXX.XX Philippine Pesos"."""

    response = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.7,
        max_tokens=300
    )
    return response.choices[0].message.content.strip()

# ==================== CODEBASE INDEXING FUNCTIONS ====================

def index_codebase(force_refresh=False):
    """
    Index all PHP, Laravel config, and route files.
    Returns cached data or rebuilds if force_refresh=True or cache is missing.
    """
    # Check if cache exists and is recent
    if os.path.exists(CACHE_FILE) and not force_refresh:
        try:
            with open(CACHE_FILE, 'rb') as f:
                cached_data = pickle.load(f)
            # Verify metadata
            if os.path.exists(CACHE_METADATA):
                with open(CACHE_METADATA, 'r') as f:
                    metadata = json.load(f)
                    if metadata.get('version') == 1:
                        return cached_data
        except Exception as e:
            print(f"Cache load failed: {e}, rebuilding...")

    # Build fresh index
    codebase = {}
    file_count = 0

    # Directories to index
    index_dirs = [
        ('app', ['*.php']),
        ('routes', ['*.php']),
        ('config', ['*.php']),
        ('database/migrations', ['*.php']),
        ('resources/views', ['*.php', '*.blade.php']),
    ]

    print("Indexing codebase...")
    for dir_name, extensions in index_dirs:
        dir_path = os.path.join(LARAVEL_PATH, dir_name)
        if os.path.exists(dir_path):
            for ext in extensions:
                for file_path in Path(dir_path).rglob(ext):
                    try:
                        rel_path = os.path.relpath(file_path, LARAVEL_PATH)
                        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                            content = f.read()
                            codebase[rel_path] = content
                            file_count += 1
                    except Exception as e:
                        print(f"Error reading {file_path}: {e}")

    # Save cache
    try:
        with open(CACHE_FILE, 'wb') as f:
            pickle.dump(codebase, f)
        
        with open(CACHE_METADATA, 'w') as f:
            json.dump({
                'version': 1,
                'indexed_at': datetime.now().isoformat(),
                'file_count': file_count
            }, f)
    except Exception as e:
        print(f"Cache save failed: {e}")

    print(f"Indexed {file_count} files")
    return codebase

def search_codebase(codebase, query, top_k=5):
    """
    Search codebase for relevant files matching the query.
    Returns top_k most relevant files with content.
    """
    query_lower = query.lower()
    keywords = query_lower.split()
    
    # Score each file
    scores = {}
    for file_path, content in codebase.items():
        content_lower = content.lower()
        score = 0
        
        # Check filename and path
        for keyword in keywords:
            if keyword in file_path.lower():
                score += 10
            # Check content (count occurrences)
            score += content_lower.count(keyword) * 2
        
        if score > 0:
            scores[file_path] = score
    
    # Get top results
    top_files = sorted(scores.items(), key=lambda x: x[1], reverse=True)[:top_k]
    
    results = []
    for file_path, score in top_files:
        results.append({
            'path': file_path,
            'content': codebase[file_path],
            'score': score
        })
    
    return results

# ==================== QUESTION CLASSIFICATION ====================

def classify_question(user_question):
    """
    Classify if question is about:
    - 'database': Data queries (user counts, records, reports)
    - 'system': How-to, process flows, features
    - 'both': Could be either
    """
    db_keywords = ['how many', 'count', 'list', 'show', 'find', 'search', 'where', 'filter', 'report', 'total']
    process_keywords = ['how', 'how do', 'how to', 'process', 'flow', 'step', 'register', 'create', 'add', 'submit', 'upload', 'scan', 'work', 'use']
    
    question_lower = user_question.lower()
    
    db_score = sum(1 for kw in db_keywords if kw in question_lower)
    process_score = sum(1 for kw in process_keywords if kw in question_lower)
    
    if process_score > db_score:
        return 'system'
    elif db_score > process_score:
        return 'database'
    else:
        return 'both'

# ==================== SYSTEM PROCESS RESPONSE ====================

def generate_process_response(user_question, codebase_results):
    """
    Use Groq to explain the process flow based on codebase files.
    Focus on USER EXPERIENCE, not backend details.
    """
    codebase_context = "\n\n".join([
        f"FILE: {result['path']}\n```php\n{result['content'][:2000]}\n```"
        for result in codebase_results
    ])

    prompt = f"""You are a system guide expert. Based on the Laravel codebase files below, explain HOW A USER would accomplish this task.

IMPORTANT GUIDELINES:
- Focus on user actions and steps (UI workflow), NOT backend mechanics
- Explain the logical flow step-by-step from the user's perspective
- Include what fields to fill, what buttons to click, what happens next
- Mention validation rules if relevant
- Keep it friendly and clear for a non-technical user
- If it's about admin, specify "Admin steps:" clearly

Codebase Context:
{codebase_context}

User Question: {user_question}

Please provide a step-by-step guide for how to {user_question}"""

    response = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.7,
        max_tokens=500
    )
    
    return response.choices[0].message.content.strip()

# ==================== FLASK ROUTES ====================

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
        if len(session['conversation_history']) > 10:
            session['conversation_history'] = session['conversation_history'][-10:]

        # Greeting handler
        greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening']
        if any(g in user_input.lower() for g in greetings) and len(user_input.split()) <= 6:
            return jsonify({
                'response': "Hello! I'm your system assistant. I can help you with:\n• System usage: How to register users, submit attendance, scan QR codes, upload documents, etc.\n• Database queries: Find user records, view attendance reports, search employees, etc.\n\nWhat would you like help with?",
                'question_type': 'greeting',
                'follow_up_questions': [
                    "How do I register a new user?",
                    "How many users are in the system?",
                    "How do I scan QR code attendance?"
                ],
                'status': 'success'
            })

        # Classify question
        question_type = classify_question(user_input)

        if question_type == 'database' or question_type == 'both':
            # Try database route first
            schema = get_db_schema()
            sql = generate_sql_query(user_input, schema)

            if sql != "CANNOT_ANSWER" and sql.lower().startswith("select"):
                results = execute_query(sql)
                response_text = generate_natural_response(user_input, sql, results)
                
                session['conversation_history'][-1]['bot'] = response_text
                session['conversation_history'][-1]['type'] = 'database'
                session.modified = True

                return jsonify({
                    'response': response_text,
                    'question_type': 'database',
                    'follow_up_questions': [
                        "Show me more details",
                        "Filter by a different criteria",
                        "How many total records are there?"
                    ],
                    'status': 'success'
                })

        # Use system/codebase route
        codebase = index_codebase()
        codebase_results = search_codebase(codebase, user_input)

        if codebase_results:
            response_text = generate_process_response(user_input, codebase_results)
        else:
            response_text = "I couldn't find relevant information in the codebase about this topic. Could you rephrase your question?"

        session['conversation_history'][-1]['bot'] = response_text
        session['conversation_history'][-1]['type'] = 'system'
        session.modified = True

        return jsonify({
            'response': response_text,
            'question_type': 'system',
            'follow_up_questions': [
                "What happens after that?",
                "Where do I find that option?",
                "What if I need to...?"
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

@app.route('/refresh-cache', methods=['POST'])
def refresh_cache():
    """Admin endpoint to refresh the codebase cache"""
    try:
        codebase = index_codebase(force_refresh=True)
        return jsonify({
            'status': 'success',
            'message': f'Codebase cache refreshed with {len(codebase)} files'
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/cache-status', methods=['GET'])
def cache_status():
    """Admin endpoint to check cache status"""
    if os.path.exists(CACHE_METADATA):
        with open(CACHE_METADATA, 'r') as f:
            metadata = json.load(f)
        return jsonify({
            'status': 'success',
            'cached': True,
            'metadata': metadata
        })
    else:
        return jsonify({
            'status': 'success',
            'cached': False,
            'message': 'No cache exists. Will be created on first use.'
        })

if __name__ == '__main__':
    app.run(debug=True, port=5001)
