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
    
    # Translate Filipino to English if needed
    question_to_process = user_question
    if _is_tagalog_question(user_question):
        question_to_process = translate_tagalog_to_english(user_question)
        print(f"🌐 Translated: '{user_question}' → '{question_to_process}'")
    
    prompt = f"""You are a MySQL expert. Given the database schema below, generate a valid MySQL SELECT query to answer the user's question.

Database Schema:
{schema}

Rules:
- Only generate SELECT queries, never INSERT, UPDATE, DELETE, or DROP
- Return ONLY the raw SQL query, no explanation, no markdown, no backticks
- If the question cannot be answered from the schema, return: CANNOT_ANSWER
- All monetary values are in Philippine Peso (PHP), never use dollar signs

User Question: {question_to_process}

SQL Query:"""

    response = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.1,
        max_tokens=300
    )
    return response.choices[0].message.content.strip()

def _is_tagalog_question(question):
    """Check if question contains Tagalog/Filipino keywords"""
    tagalog_words = ['ang', 'ilan', 'paano', 'ano', 'ilang', 'sa', 'ng', 'ay', 'para', 'mula']
    question_lower = question.lower()
    return any(word in question_lower for word in tagalog_words)

def translate_tagalog_to_english(tagalog_text):
    """Translate Tagalog question to English using Groq"""
    prompt = f"""Translate this Tagalog/Filipino question to English. 
Return ONLY the English translation, nothing else.

Tagalog: {tagalog_text}

English translation:"""
    
    response = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.3,
        max_tokens=100
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

def save_chat_history(user_id, session_id, question, response, question_type, follow_up_questions=None, codebase_files=None):
    """Save chat message to database"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        
        sql = """
        INSERT INTO chat_history 
        (user_id, session_id, question, response, question_type, follow_up_questions, codebase_files_used)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
        """
        
        values = (
            user_id,
            session_id,
            question,
            response,
            question_type,
            json.dumps(follow_up_questions) if follow_up_questions else None,
            json.dumps(codebase_files) if codebase_files else None
        )
        
        cursor.execute(sql, values)
        conn.commit()
        cursor.close()
        conn.close()
        
        # Debug logging
        print(f"✅ Chat saved: user_id={user_id}, session_id={session_id}, type={question_type}")
        
        return True
    except Exception as e:
        print(f"❌ Error saving chat history: {e}")
        import traceback
        traceback.print_exc()
        return False

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
    
    Supports English and Filipino (Tagalog)
    """
    question_lower = user_question.lower()
    
    # English database keywords
    db_keywords = [
        'how many', 'count', 'list', 'show', 'find', 'search', 'where', 
        'filter', 'report', 'total', 'number', 'many', 'all', 'view'
    ]
    
    # Filipino/Tagalog database keywords
    # ilan = how many, ilang = how many, total, lahat = all, dami = amount
    db_keywords_tagalog = [
        'ilan', 'ilang', 'dami', 'total', 'lahat', 'list', 'view', 'show', 'magpakita'
    ]
    
    # English process keywords
    process_keywords = [
        'how', 'how do', 'how to', 'process', 'flow', 'step', 'register', 
        'create', 'add', 'submit', 'upload', 'scan', 'work', 'use', 'make'
    ]
    
    # Filipino/Tagalog process keywords
    # paano = how, proseso = process, hakbang = steps, magparehistro = register
    process_keywords_tagalog = [
        'paano', 'proseso', 'hakbang', 'magparehistro', 'lumikha', 'magdagdag', 
        'magpadala', 'magskana', 'gamitin', 'trabaho'
    ]
    
    # Count database keywords found
    db_score = sum(1 for kw in db_keywords if kw in question_lower)
    db_score += sum(1 for kw in db_keywords_tagalog if kw in question_lower) * 1.5
    
    # Count process keywords found
    process_score = sum(1 for kw in process_keywords if kw in question_lower)
    process_score += sum(1 for kw in process_keywords_tagalog if kw in question_lower) * 1.5
    
    # Boost database score if question ends with "?" and contains count/number words
    if '?' in question_lower and any(word in question_lower for word in ['ilan', 'how many', 'count', 'total']):
        db_score += 5
    
    if process_score > db_score:
        return 'system'
    elif db_score > process_score:
        return 'database'
    else:
        # Default: if question starts with common DB pattern, use database
        if any(question_lower.startswith(kw) for kw in ['what', 'ano', 'show', 'magpakita']):
            return 'database'
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

@app.route('/set-user', methods=['POST'])
def set_user():
    """Set the current user for chat history tracking"""
    try:
        user_id = request.json.get('user_id', None)
        
        if not user_id:
            return jsonify({'error': 'user_id is required'}), 400
        
        try:
            user_id = int(user_id)
        except (ValueError, TypeError):
            return jsonify({'error': 'user_id must be an integer'}), 400
        
        # Verify user exists in database
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, email FROM users WHERE id = %s", (user_id,))
        user = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if not user:
            return jsonify({'error': 'User not found'}), 404
        
        # Set in session
        session['user_id'] = user_id
        session.modified = True
        
        return jsonify({
            'status': 'success',
            'message': f'User set: {user["email"]}',
            'user_id': user_id
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/clear-user', methods=['POST'])
def clear_user():
    """Clear the current user (go anonymous)"""
    try:
        if 'user_id' in session:
            del session['user_id']
        session.modified = True
        
        return jsonify({
            'status': 'success',
            'message': 'User cleared, now anonymous'
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/chat', methods=['POST'])
def chat():
    try:
        user_input = request.json.get('message', '').strip()
        
        # Try to get user_id from multiple sources
        user_id = request.json.get('user_id', None)  # From request JSON
        if not user_id:
            user_id = request.headers.get('X-User-ID', None)  # From header
        if not user_id:
            user_id = request.cookies.get('user_id', None)  # From cookie
        if not user_id and 'user_id' in session:
            user_id = session.get('user_id', None)  # From session
        
        # Convert to int if provided
        if user_id:
            try:
                user_id = int(user_id)
            except (ValueError, TypeError):
                user_id = None
        
        if not user_input:
            return jsonify({'error': 'No message provided'}), 400

        if 'conversation_history' not in session:
            session['conversation_history'] = []

        # Create or get session ID
        if 'session_id' not in session:
            session['session_id'] = str(datetime.now().timestamp())

        session['conversation_history'].append({
            'user': user_input,
            'timestamp': datetime.now().isoformat()
        })
        if len(session['conversation_history']) > 10:
            session['conversation_history'] = session['conversation_history'][-10:]

        # Greeting handler
        greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening']
        if any(g in user_input.lower() for g in greetings) and len(user_input.split()) <= 6:
            response_text = "Hello! I'm your system assistant. I can help you with:\n• System usage: How to register users, submit attendance, scan QR codes, upload documents, etc.\n• Database queries: Find user records, view attendance reports, search employees, etc.\n\nWhat would you like help with?"
            follow_ups = [
                "How do I register a new user?",
                "How many users are in the system?",
                "How do I scan QR code attendance?"
            ]
            
            save_chat_history(user_id, session['session_id'], user_input, response_text, 'greeting', follow_ups)
            
            return jsonify({
                'response': response_text,
                'question_type': 'greeting',
                'follow_up_questions': follow_ups,
                'status': 'success'
            })

        # Classify question
        question_type = classify_question(user_input)
        codebase_files_used = []

        if question_type == 'database' or question_type == 'both':
            # Try database route first
            schema = get_db_schema()
            sql = generate_sql_query(user_input, schema)

            if sql != "CANNOT_ANSWER" and sql.lower().startswith("select"):
                results = execute_query(sql)
                response_text = generate_natural_response(user_input, sql, results)
                follow_ups = [
                    "Show me more details",
                    "Filter by a different criteria",
                    "How many total records are there?"
                ]
                
                save_chat_history(user_id, session['session_id'], user_input, response_text, 'database', follow_ups, None)
                
                session['conversation_history'][-1]['bot'] = response_text
                session['conversation_history'][-1]['type'] = 'database'
                session.modified = True

                return jsonify({
                    'response': response_text,
                    'question_type': 'database',
                    'follow_up_questions': follow_ups,
                    'status': 'success'
                })

        # Use system/codebase route
        codebase = index_codebase()
        codebase_results = search_codebase(codebase, user_input)
        codebase_files_used = [result['path'] for result in codebase_results]

        if codebase_results:
            response_text = generate_process_response(user_input, codebase_results)
        else:
            response_text = "I couldn't find relevant information in the codebase about this topic. Could you rephrase your question?"

        follow_ups = [
            "What happens after that?",
            "Where do I find that option?",
            "What if I need to...?"
        ]
        
        save_chat_history(user_id, session['session_id'], user_input, response_text, 'system', follow_ups, codebase_files_used)

        session['conversation_history'][-1]['bot'] = response_text
        session['conversation_history'][-1]['type'] = 'system'
        session.modified = True

        return jsonify({
            'response': response_text,
            'question_type': 'system',
            'follow_up_questions': follow_ups,
            'status': 'success'
        })

    except mysql.connector.Error as db_err:
        error_msg = f"Database error: {str(db_err)}"
        save_chat_history(user_id if 'user_id' in locals() else None, 
                         session.get('session_id', 'unknown'), 
                         user_input if 'user_input' in locals() else 'error', 
                         error_msg, 'error', None, None)
        return jsonify({
            'response': error_msg,
            'status': 'error'
        }), 500
    except Exception as e:
        print(f"Error: {str(e)}")
        import traceback
        traceback.print_exc()
        error_msg = 'Sorry, an error occurred. Please try again.'
        save_chat_history(user_id if 'user_id' in locals() else None, 
                         session.get('session_id', 'unknown'), 
                         user_input if 'user_input' in locals() else 'error', 
                         error_msg, 'error', None, None)
        return jsonify({
            'response': error_msg,
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

@app.route('/admin/chat-history', methods=['GET'])
def get_chat_history():
    """Admin endpoint to view chat history"""
    try:
        user_id = request.args.get('user_id', None)
        limit = int(request.args.get('limit', 50))
        offset = int(request.args.get('offset', 0))
        
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        if user_id:
            query = "SELECT * FROM chat_history WHERE user_id = %s ORDER BY created_at DESC LIMIT %s OFFSET %s"
            cursor.execute(query, (user_id, limit, offset))
        else:
            query = "SELECT * FROM chat_history ORDER BY created_at DESC LIMIT %s OFFSET %s"
            cursor.execute(query, (limit, offset))
        
        results = cursor.fetchall()
        cursor.close()
        conn.close()
        
        return jsonify({
            'status': 'success',
            'count': len(results),
            'data': results
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/admin/chat-stats', methods=['GET'])
def get_chat_stats():
    """Admin endpoint to view chat statistics"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Total chats
        cursor.execute("SELECT COUNT(*) as total FROM chat_history")
        total = cursor.fetchone()['total']
        
        # By question type
        cursor.execute("""
            SELECT question_type, COUNT(*) as count 
            FROM chat_history 
            GROUP BY question_type
        """)
        by_type = cursor.fetchall()
        
        # By user
        cursor.execute("""
            SELECT user_id, COUNT(*) as count 
            FROM chat_history 
            WHERE user_id IS NOT NULL
            GROUP BY user_id 
            ORDER BY count DESC
            LIMIT 10
        """)
        by_user = cursor.fetchall()
        
        # Today's chats
        cursor.execute("""
            SELECT COUNT(*) as today_count 
            FROM chat_history 
            WHERE DATE(created_at) = CURDATE()
        """)
        today = cursor.fetchone()['today_count']
        
        cursor.close()
        conn.close()
        
        return jsonify({
            'status': 'success',
            'total': total,
            'today': today,
            'by_question_type': by_type,
            'top_users': by_user
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/admin/user-conversations/<int:user_id>', methods=['GET'])
def get_user_conversations(user_id):
    """Admin endpoint to view all conversations for a specific user"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT id, question, response, question_type, created_at 
            FROM chat_history 
            WHERE user_id = %s 
            ORDER BY created_at DESC
            LIMIT 100
        """
        cursor.execute(query, (user_id,))
        conversations = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return jsonify({
            'status': 'success',
            'user_id': user_id,
            'conversations': conversations,
            'total': len(conversations)
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/debug/chat-history', methods=['GET'])
def debug_chat_history():
    """Debug endpoint to view latest chat history entries"""
    try:
        limit = int(request.args.get('limit', 10))
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT id, user_id, session_id, question, response, question_type, created_at
            FROM chat_history 
            ORDER BY created_at DESC
            LIMIT %s
        """
        cursor.execute(query, (limit,))
        results = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return jsonify({
            'status': 'success',
            'count': len(results),
            'data': results
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

if __name__ == '__main__':
    app.run(debug=True, port=5001)
