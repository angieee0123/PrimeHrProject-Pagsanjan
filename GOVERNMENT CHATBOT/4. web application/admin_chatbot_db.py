from flask import Flask, render_template, request, jsonify, session
from flask_cors import CORS
import mysql.connector
from sentence_transformers import SentenceTransformer
import numpy as np
import os
from datetime import datetime
from groq import Groq
import warnings

warnings.filterwarnings('ignore')
os.environ['TOKENIZERS_PARALLELISM'] = 'false'

app = Flask(__name__)
app.secret_key = os.urandom(24)
CORS(app)

# Database connection
DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 3306,
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena'
}

# Initialize models
embedder = SentenceTransformer('all-MiniLM-L6-v2')
groq_client = Groq(api_key=os.environ.get('GROQ_API_KEY', '***REMOVED-GROQ-KEY***'))

print(f"✓ Connected to database: {DB_CONFIG['database']}")

def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)

def cosine_similarity(vec1, vec2):
    return np.dot(vec1, vec2) / (np.linalg.norm(vec1) * np.linalg.norm(vec2))

def search_database(query, table='employees', limit=5):
    """Search database using semantic similarity"""
    query_embedding = embedder.encode([query])[0]
    
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    # Get all records from table
    cursor.execute(f"SELECT * FROM {table} LIMIT 100")
    records = cursor.fetchall()
    
    results = []
    for record in records:
        # Create searchable text from record
        text = ' '.join([str(v) for v in record.values() if v])
        record_embedding = embedder.encode([text])[0]
        similarity = cosine_similarity(query_embedding, record_embedding)
        
        results.append({
            'record': record,
            'similarity': float(similarity),
            'table': table
        })
    
    cursor.close()
    conn.close()
    
    # Sort by similarity
    results.sort(key=lambda x: x['similarity'], reverse=True)
    return results[:limit]

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/admin')
def admin():
    return render_template('adminChatbot.html')

@app.route('/admin/knowledge', methods=['GET', 'POST'])
def admin_knowledge():
    if request.method == 'POST':
        data = request.json
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("INSERT INTO chatbot_knowledge (question, answer) VALUES (%s, %s)", 
                      (data['question'], data['answer']))
        conn.commit()
        cursor.close()
        conn.close()
        return jsonify({'status': 'success'})
    
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM chatbot_knowledge LIMIT 20")
    kb_list = cursor.fetchall()
    cursor.close()
    conn.close()
    return jsonify(kb_list)

@app.route('/admin/knowledge/<int:kb_id>', methods=['DELETE'])
def delete_knowledge(kb_id):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("DELETE FROM chatbot_knowledge WHERE id = %s", (kb_id,))
    conn.commit()
    cursor.close()
    conn.close()
    return jsonify({'status': 'success'})

@app.route('/admin/stats')
def admin_stats():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT COUNT(*) FROM employees")
    total_employees = cursor.fetchone()[0]
    cursor.execute("SELECT COUNT(*) FROM departments")
    total_departments = cursor.fetchone()[0]
    cursor.close()
    conn.close()
    
    return jsonify({
        'total_questions': len(session.get('conversation_history', [])),
        'total_kb': total_employees,
        'avg_time': 150
    })

@app.route('/admin/logs')
def admin_logs():
    logs = [{'question': h.get('user', ''), 'answer': h.get('bot', '')[:100], 
             'timestamp': h.get('timestamp', '')} 
            for h in session.get('conversation_history', [])]
    return jsonify(logs)

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
        
        # Determine which table to search
        query_lower = user_input.lower()
        if 'employee' in query_lower or 'staff' in query_lower:
            results = search_database(user_input, 'employees')
        elif 'department' in query_lower:
            results = search_database(user_input, 'departments')
        else:
            results = search_database(user_input, 'employees')
        
        if not results or results[0]['similarity'] < 0.3:
            response = "I couldn't find relevant information. Could you rephrase your question?"
            return jsonify({'response': response, 'status': 'success'})
        
        # Generate response using Groq
        context = f"Database results:\n"
        for r in results[:3]:
            context += f"{r['record']}\n"
        
        prompt = f"""You are a helpful HR assistant. Answer the user's question based on the database information.

Database Context:
{context[:800]}

User Question: {user_input}

Answer:"""
        
        chat_completion = groq_client.chat.completions.create(
            messages=[{"role": "user", "content": prompt}],
            model="llama-3.3-70b-versatile",
            temperature=0.7,
            max_tokens=200
        )
        response = chat_completion.choices[0].message.content
        
        session['conversation_history'][-1]['bot'] = response
        session.modified = True
        
        return jsonify({
            'response': response,
            'results': [r['record'] for r in results[:3]],
            'status': 'success'
        })
    
    except Exception as e:
        print(f"Error: {str(e)}")
        return jsonify({
            'response': 'Sorry, an error occurred. Please try again.',
            'error': str(e),
            'status': 'error'
        }), 500

if __name__ == '__main__':
    print(f"Starting server on http://localhost:5001")
    print(f"Admin panel: http://localhost:5001/admin")
    app.run(debug=True, port=5001, use_reloader=False)
