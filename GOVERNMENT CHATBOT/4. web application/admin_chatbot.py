from flask import Flask, render_template, request, jsonify, session
from flask_cors import CORS
import json
import mysql.connector
from sentence_transformers import SentenceTransformer
import os
from functools import lru_cache
from datetime import datetime
import re
from difflib import SequenceMatcher
from groq import Groq
from sklearn.metrics.pairwise import cosine_similarity
import numpy as np

app = Flask(__name__)
app.secret_key = os.urandom(24)
CORS(app, resources={r"/chat": {"origins": ["http://localhost:8000", "http://127.0.0.1:8000"]}})

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_DIR = os.path.join(BASE_DIR, '..', '1. raw dataset')

with open(os.path.join(DATA_DIR, 'citizens_charter_2025_sampaloc_quezon.json'), 'r', encoding='utf-8') as f:
    charter_data = json.load(f)

groq_client = Groq(api_key=os.environ.get('GROQ_API_KEY', '***REMOVED-GROQ-KEY***'))

import warnings
warnings.filterwarnings('ignore')
os.environ['TOKENIZERS_PARALLELISM'] = 'false'

embedder = SentenceTransformer('all-MiniLM-L6-v2')

db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena'
}

def get_db_connection():
    return mysql.connector.connect(**db_config)

print(f"✓ Database connection configured for {db_config['database']}")

# Query expansion dictionary
QUERY_SYNONYMS = {
    'birth certificate': ['birth cert', 'certificate of birth', 'birth record', 'birth document'],
    'death certificate': ['death cert', 'certificate of death', 'death record'],
    'marriage certificate': ['marriage cert', 'certificate of marriage', 'marriage license'],
    'business permit': ['business license', 'business registration', 'one-stop shop business', 'business permit new', 'business permit renewal'],
    'barangay clearance': ['brgy clearance', 'barangay certificate'],
    'medical certificate': ['health certificate', 'medical clearance', 'health clearance'],
}

# Response cache
response_cache = {}

@lru_cache(maxsize=1000)
def get_embedding(query):
    """Cache embeddings for repeated queries"""
    return embedder.encode([query]).astype('float32')

def expand_query(query):
    """Expand query with synonyms"""
    query_lower = query.lower()
    expanded = [query]
    
    for term, synonyms in QUERY_SYNONYMS.items():
        if term in query_lower:
            for syn in synonyms:
                expanded.append(query_lower.replace(term, syn))
        for syn in synonyms:
            if syn in query_lower:
                expanded.append(query_lower.replace(syn, term))
    
    return list(set(expanded))[:3]

def search_database(query, top_k=5):
    """Search all database tables using semantic similarity for HR queries"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        query_lower = query.lower()
        
        all_results = []
        
        # Determine search strategy based on query
        search_all = any(word in query_lower for word in ['all', 'list', 'show', 'employees', 'active', 'permanent', 'contractual', 'male', 'female', 'married', 'single', 'recent', 'hired'])
        
        print(f"\n[DEBUG] Query: {query}")
        print(f"[DEBUG] Search all mode: {search_all}")
        
        # Search employees table with joins
        cursor.execute("""
            SELECT e.*, ed.position, ed.department_id, ed.employment_status, ed.date_hired,
                   d.name as department_name
            FROM employees e
            LEFT JOIN employment_details ed ON e.id = ed.employee_id
            LEFT JOIN departments d ON ed.department_id = d.id
            LIMIT 100
        """)
        employees = cursor.fetchall()
        print(f"[DEBUG] Found {len(employees)} employees in database")
        
        # If asking for all/list, return all employees
        if search_all or len(employees) > 0:
            query_embedding = embedder.encode([query])[0]
            for emp in employees:
                full_name = f"{emp.get('first_name', '')} {emp.get('middle_name', '')} {emp.get('last_name', '')}".strip()
                doc_text = f"{full_name} {emp.get('employee_id', '')} {emp.get('email', '')} {emp.get('position', '')} {emp.get('department_name', '')} {emp.get('sex', '')} {emp.get('civil_status', '')} {emp.get('employment_status', '')}"
                doc_embedding = embedder.encode([doc_text])[0]
                similarity = cosine_similarity([query_embedding], [doc_embedding])[0][0]
                
                # Filter based on query keywords
                include = True
                if 'active' in query_lower and emp.get('employment_status', '').lower() != 'active':
                    include = False
                if 'permanent' in query_lower and emp.get('employment_status', '').lower() != 'permanent':
                    include = False
                if 'contractual' in query_lower and emp.get('employment_status', '').lower() != 'contractual':
                    include = False
                if 'male' in query_lower and emp.get('sex', '').lower() != 'male':
                    include = False
                if 'female' in query_lower and emp.get('sex', '').lower() != 'female':
                    include = False
                if 'married' in query_lower and emp.get('civil_status', '').lower() != 'married':
                    include = False
                if 'single' in query_lower and emp.get('civil_status', '').lower() != 'single':
                    include = False
                
                if include:
                    all_results.append({
                        'type': 'employee',
                        'name': full_name,
                        'score': float(similarity) + 0.3,  # Boost score
                        'data': emp
                    })
        
        print(f"[DEBUG] Total results after employee search: {len(all_results)}")
        
        # Search other tables if query mentions them
        if any(word in query_lower for word in ['address', 'location', 'where', 'live']):
            cursor.execute("SELECT a.*, e.first_name, e.last_name FROM addresses a LEFT JOIN employees e ON a.employee_id = e.id LIMIT 50")
            query_embedding = embedder.encode([query])[0]
            for row in cursor.fetchall():
                doc_text = f"{row.get('first_name', '')} {row.get('last_name', '')} {row.get('street', '')} {row.get('city', '')} {row.get('province', '')}"
                doc_embedding = embedder.encode([doc_text])[0]
                similarity = cosine_similarity([query_embedding], [doc_embedding])[0][0]
                all_results.append({'type': 'address', 'name': doc_text[:100], 'score': float(similarity), 'data': row})
        
        if any(word in query_lower for word in ['contact', 'phone', 'mobile', 'number']):
            cursor.execute("SELECT c.*, e.first_name, e.last_name FROM contacts c LEFT JOIN employees e ON c.employee_id = e.id LIMIT 50")
            query_embedding = embedder.encode([query])[0]
            for row in cursor.fetchall():
                doc_text = f"{row.get('first_name', '')} {row.get('last_name', '')} {row.get('mobile_number', '')} {row.get('telephone_number', '')}"
                doc_embedding = embedder.encode([doc_text])[0]
                similarity = cosine_similarity([query_embedding], [doc_embedding])[0][0]
                all_results.append({'type': 'contact', 'name': doc_text[:100], 'score': float(similarity), 'data': row})
        
        if any(word in query_lower for word in ['education', 'degree', 'school', 'college', 'university']):
            cursor.execute("SELECT ed.*, e.first_name, e.last_name FROM educations ed LEFT JOIN employees e ON ed.employee_id = e.id LIMIT 50")
            query_embedding = embedder.encode([query])[0]
            for row in cursor.fetchall():
                doc_text = f"{row.get('first_name', '')} {row.get('last_name', '')} {row.get('level', '')} {row.get('school_name', '')} {row.get('degree', '')}"
                doc_embedding = embedder.encode([doc_text])[0]
                similarity = cosine_similarity([query_embedding], [doc_embedding])[0][0]
                all_results.append({'type': 'education', 'name': doc_text[:100], 'score': float(similarity), 'data': row})
        
        if any(word in query_lower for word in ['training', 'seminar', 'workshop']):
            cursor.execute("SELECT t.*, e.first_name, e.last_name FROM trainings t LEFT JOIN employees e ON t.employee_id = e.id LIMIT 50")
            query_embedding = embedder.encode([query])[0]
            for row in cursor.fetchall():
                doc_text = f"{row.get('first_name', '')} {row.get('last_name', '')} {row.get('title', '')} {row.get('type', '')}"
                doc_embedding = embedder.encode([doc_text])[0]
                similarity = cosine_similarity([query_embedding], [doc_embedding])[0][0]
                all_results.append({'type': 'training', 'name': doc_text[:100], 'score': float(similarity), 'data': row})
        
        if any(word in query_lower for word in ['work', 'experience', 'previous', 'employment']):
            cursor.execute("SELECT w.*, e.first_name, e.last_name FROM work_experiences w LEFT JOIN employees e ON w.employee_id = e.id LIMIT 50")
            query_embedding = embedder.encode([query])[0]
            for row in cursor.fetchall():
                doc_text = f"{row.get('first_name', '')} {row.get('last_name', '')} {row.get('position_title', '')} {row.get('company_name', '')}"
                doc_embedding = embedder.encode([doc_text])[0]
                similarity = cosine_similarity([query_embedding], [doc_embedding])[0][0]
                all_results.append({'type': 'work_experience', 'name': doc_text[:100], 'score': float(similarity), 'data': row})
        
        cursor.close()
        conn.close()
        
        sorted_results = sorted(all_results, key=lambda x: x['score'], reverse=True)[:top_k]
        print(f"[DEBUG] Returning {len(sorted_results)} results")
        if sorted_results:
            print(f"[DEBUG] Top result score: {sorted_results[0]['score']:.4f}")
        
        return sorted_results
    except Exception as e:
        print(f"Database search error: {e}")
        import traceback
        traceback.print_exc()
        return []

def is_mayor_question(query):
    """Detect mayor questions including name mentions and misspellings"""
    query_lower = query.lower().strip()

    # Exact keywords (English + Tagalog)
    mayor_keywords = [
        'who is the mayor', 'name of the mayor', 'mayor name', 'current mayor',
        'municipal mayor', 'who is mayor', 'sino ang mayor', 'sino mayor',
        'pangalan ng mayor', 'sino ang ating mayor', 'sino po ang mayor',
        'januario', 'garcia', 'ferry', 'januario ferry', 'ferry garcia',
        'sino si garcia', 'sino si mayor garcia', 'sino si ferry'
    ]
    if any(keyword in query_lower for keyword in mayor_keywords):
        return True

    # Fuzzy phrase matching
    mayor_phrases = [
        'who is the mayor', 'what is the name of the mayor',
        'who is the municipal mayor', 'sino ang mayor'
    ]
    for phrase in mayor_phrases:
        if SequenceMatcher(None, query_lower, phrase).ratio() > 0.6:
            return True

    # Token-level fuzzy match for 'mayor' and name parts
    name_tokens = ['mayor', 'januario', 'ferry', 'garcia']
    for word in query_lower.split():
        for token in name_tokens:
            if SequenceMatcher(None, word, token).ratio() > 0.7:
                return True

    return False

def fuzzy_match_service(query, threshold=0.6):
    """Find services using fuzzy matching for typo tolerance"""
    query_lower = query.lower()
    matches = []
    
    for service in charter_data['services']:
        service_name = service['service_name'].lower()
        ratio = SequenceMatcher(None, query_lower, service_name).ratio()
        if ratio > threshold:
            matches.append((service, ratio))
    
    return sorted(matches, key=lambda x: x[1], reverse=True)[:3]

def search_knowledge(query, top_k=5):
    """Search knowledge base with caching"""
    cache_key = f"{query}_{top_k}"
    if cache_key in response_cache:
        return response_cache[cache_key]
    
    results = search_database(query, top_k)
    response_cache[cache_key] = results
    return results

def refine_search_with_keywords(query, results):
    """Refine search results using keyword matching"""
    query_lower = query.lower()
    
    service_keywords = {
        'birth': ['birth certificate', 'birth cert', 'certificate of birth'],
        'death': ['death certificate', 'death cert', 'certificate of death'],
        'marriage': ['marriage certificate', 'marriage cert', 'certificate of marriage'],
        'business permit': ['business permit', 'business registration', 'one-stop shop'],
        'medical': ['medical certificate', 'health certificate', 'medical clearance'],
        'barangay': ['barangay clearance', 'barangay certificate'],
        'cedula': ['cedula', 'community tax', 'residence certificate'],
    }
    
    # Special handling for business permit queries
    if 'business permit' in query_lower or 'business license' in query_lower or ('how' in query_lower and 'business' in query_lower):
        for result in results:
            service_name_lower = result['service'].lower()
            # Highest priority: Main business permit services
            if 'business permit new and renewal' in service_name_lower or 'one-stop shop business' in service_name_lower:
                result['score'] = result['score'] * 0.05  # Highest priority
            elif 'business registration' in service_name_lower:
                result['score'] = result['score'] * 0.1
            # Deprioritize other permits and certificates
            elif 'inspection' in service_name_lower or 'building permit' in service_name_lower or 'electrical' in service_name_lower or 'tricycle' in service_name_lower:
                result['score'] = result['score'] * 5.0  # Much lower priority
        results.sort(key=lambda x: x['score'])
        return results
    
    # Standard keyword matching for other services
    for keyword, service_names in service_keywords.items():
        if keyword in query_lower:
            for result in results:
                service_name_lower = result['service'].lower()
                if any(svc in service_name_lower for svc in service_names):
                    result['score'] = result['score'] * 0.5
            results.sort(key=lambda x: x['score'])
            break
    
    return results

def is_valid_query(query):
    """Check if user input is valid"""
    if len(query.strip()) < 3:
        return False, "Your message is too short. Could you please provide more details?"
    
    vowels = len(re.findall(r'[aeiouAEIOU]', query))
    consonants = len(re.findall(r'[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]', query))
    
    if consonants > 0 and vowels / max(consonants, 1) < 0.2:
        return False, "I couldn't understand your message. Could you please rephrase your question?"
    
    if len(set(query.lower().replace(' ', ''))) < 3:
        return False, "I couldn't understand your message. Could you please ask a clear question?"
    
    special_chars = len(re.findall(r'[^a-zA-Z0-9\s]', query))
    if special_chars > len(query) * 0.5:
        return False, "I couldn't understand your message. Could you please use regular text?"
    
    return True, None

def calculate_total_time(time_string):
    """Parse and calculate total processing time"""
    if not time_string:
        return "Not specified"
    
    total_minutes = 0
    minutes = re.findall(r'(\d+)\s*(?:minute|min)', time_string, re.IGNORECASE)
    hours = re.findall(r'(\d+)\s*(?:hour|hr)', time_string, re.IGNORECASE)
    days = re.findall(r'(\d+)\s*(?:day)', time_string, re.IGNORECASE)
    
    total_minutes = sum(int(m) for m in minutes)
    total_minutes += sum(int(h) for h in hours) * 60
    total_minutes += sum(int(d) for d in days) * 1440
    
    if total_minutes == 0:
        return time_string
    
    if total_minutes < 60:
        return f"{total_minutes} minutes"
    elif total_minutes < 1440:
        hours = total_minutes // 60
        mins = total_minutes % 60
        return f"{hours} hour{'s' if hours > 1 else ''}" + (f" {mins} minutes" if mins > 0 else "")
    else:
        days = total_minutes // 1440
        hours = (total_minutes % 1440) // 60
        return f"{days} day{'s' if days > 1 else ''}" + (f" {hours} hour{'s' if hours > 1 else ''}" if hours > 0 else "")

def calculate_total_fees(fees_string):
    """Parse and calculate total fees"""
    if not fees_string or fees_string.lower() in ['no fees', 'none', 'free']:
        return "No fees"
    
    amounts = re.findall(r'PHP\s*([\d,]+\.?\d*)', fees_string, re.IGNORECASE)
    if not amounts:
        return fees_string
    
    total = sum(float(amt.replace(',', '')) for amt in amounts)
    return f"PHP {total:,.2f}"

def detect_update_intent(query):
    """Detect if user wants to update/modify data"""
    update_keywords = [
        'update', 'change', 'modify', 'edit', 'set', 'i-update', 'baguhin', 
        'palitan', 'ayusin', 'correct', 'fix', 'itama'
    ]
    return any(keyword in query.lower() for keyword in update_keywords)

def extract_update_info(query):
    """Extract employee identifier and field to update from query"""
    query_lower = query.lower()
    
    # Extract employee ID or name
    employee_id = None
    employee_name = None
    
    # Pattern: "employee id [number]"
    id_match = re.search(r'(?:employee\s+id|emp\s+id|id)\s*[:#]?\s*(?:number\s+)?(\d+)', query_lower)
    if id_match:
        employee_id = id_match.group(1)
    
    # Pattern: name (first word after "of" or "for")
    if not employee_id:
        name_match = re.search(r'(?:of|for|ng)\s+([a-z]+)', query_lower)
        if name_match:
            employee_name = name_match.group(1)
    
    # Extract field and new value
    field = None
    new_value = None
    
    # Email patterns
    if 'email' in query_lower:
        field = 'email'
        email_match = re.search(r'([\w\.-]+@[\w\.-]+\.\w+)', query)
        if email_match:
            new_value = email_match.group(1)
        else:
            value_match = re.search(r'(?:to|into|as)\s+["\']?([^"\',\.]+)', query_lower)
            if value_match:
                new_value = value_match.group(1).strip()
    
    # Status patterns
    elif any(word in query_lower for word in ['status', 'employment status']):
        field = 'employment_status'
        # Look for "to [status]" pattern first (target value)
        to_match = re.search(r'(?:to|into)\s+(inactive|active|permanent|contractual|probationary)', query_lower)
        if to_match:
            status_map = {
                'inactive': 'Inactive',
                'active': 'Active',
                'permanent': 'Permanent',
                'contractual': 'Contractual',
                'probationary': 'Probationary'
            }
            new_value = status_map.get(to_match.group(1).lower())
        elif 'inactive' in query_lower or 'not active' in query_lower:
            new_value = 'Inactive'
        elif 'active' in query_lower:
            new_value = 'Active'
        elif 'permanent' in query_lower:
            new_value = 'Permanent'
        elif 'contractual' in query_lower:
            new_value = 'Contractual'
    
    # Position patterns
    elif any(word in query_lower for word in ['position', 'job', 'role']):
        field = 'position'
        value_match = re.search(r'(?:to|into|as)\s+["\']?([^"\',\.]+)', query_lower)
        if value_match:
            new_value = value_match.group(1).strip()
    
    # Mobile patterns
    elif any(word in query_lower for word in ['mobile', 'phone', 'contact']):
        field = 'mobile_number'
        value_match = re.search(r'(?:to|into|as)\s+["\']?([\d\s\-\+]+)', query)
        if value_match:
            new_value = value_match.group(1).strip()
    
    # Civil status patterns
    elif 'civil status' in query_lower or 'marital' in query_lower:
        field = 'civil_status'
        if 'married' in query_lower:
            new_value = 'Married'
        elif 'single' in query_lower:
            new_value = 'Single'
        elif 'widowed' in query_lower:
            new_value = 'Widowed'
    
    return employee_id, employee_name, field, new_value

def update_employee_data(employee_id, employee_name, field, new_value):
    """Update employee data in database with validation"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Find employee by name if ID not provided
        if not employee_id and employee_name:
            cursor.execute("SELECT id, employee_id, first_name, last_name FROM employees WHERE first_name LIKE %s OR last_name LIKE %s", (f"%{employee_name}%", f"%{employee_name}%"))
            emp = cursor.fetchone()
            if emp:
                employee_id = emp['employee_id']
                full_name = f"{emp['first_name']} {emp['last_name']}"
            else:
                return False, f"Employee '{employee_name}' not found", None
        
        if not employee_id:
            return False, "Employee not found", None
        
        # Validate field values
        if field == 'employment_status':
            valid_statuses = ['Active', 'Inactive', 'Permanent', 'Contractual', 'Probationary']
            if new_value not in valid_statuses:
                return False, f"Invalid status. Use: {', '.join(valid_statuses)}", None
        
        elif field == 'civil_status':
            valid_civil = ['Single', 'Married', 'Widowed', 'Divorced', 'Separated']
            if new_value not in valid_civil:
                return False, f"Invalid civil status. Use: {', '.join(valid_civil)}", None
        
        elif field == 'email':
            if '@' not in new_value or '.' not in new_value:
                return False, "Invalid email format", None
        
        elif field == 'mobile_number':
            clean_mobile = re.sub(r'[^0-9+]', '', new_value)
            if len(clean_mobile) < 10:
                return False, "Invalid mobile number", None
            new_value = clean_mobile
        
        # Get old value for confirmation
        old_value = None
        if field in ['email', 'civil_status']:
            cursor.execute(f"SELECT {field} FROM employees WHERE employee_id = %s", (employee_id,))
            result = cursor.fetchone()
            old_value = result[field] if result else None
            cursor.execute(f"UPDATE employees SET {field} = %s WHERE employee_id = %s", (new_value, employee_id))
        elif field in ['position', 'employment_status']:
            cursor.execute(f"SELECT {field} FROM employment_details WHERE employee_id = (SELECT id FROM employees WHERE employee_id = %s)", (employee_id,))
            result = cursor.fetchone()
            old_value = result[field] if result else None
            cursor.execute(f"UPDATE employment_details SET {field} = %s WHERE employee_id = (SELECT id FROM employees WHERE employee_id = %s)", (new_value, employee_id))
        elif field == 'mobile_number':
            cursor.execute(f"SELECT {field} FROM contacts WHERE employee_id = (SELECT id FROM employees WHERE employee_id = %s)", (employee_id,))
            result = cursor.fetchone()
            old_value = result[field] if result else None
            cursor.execute(f"UPDATE contacts SET {field} = %s WHERE employee_id = (SELECT id FROM employees WHERE employee_id = %s)", (new_value, employee_id))
        else:
            return False, "Field not supported for update", None
        
        conn.commit()
        affected = cursor.rowcount
        cursor.close()
        conn.close()
        
        return affected > 0, None, old_value
    except Exception as e:
        return False, str(e), None

def detect_multiple_questions(query):
    """Detect if the user is asking multiple questions in one message."""
    query_lower = query.lower()
    # Split on common Filipino/English multi-question connectors
    splitters = [
        r'\band\b', r'\bat\b', r'\bpati\b', r'\btapos\b', r'\btsaka\b',
        r'\bdin\b', r'\bnaman\b', r'\bsaka\b', r'[?](?=\s*[a-zA-Z])',
        r'\bkahit\b', r'\bpaano\b.*\bat\b'
    ]
    # Check for question word repetition (two question intents)
    question_words = ['magkano', 'saan', 'paano', 'kailan', 'sino', 'ano',
                      'how much', 'where', 'how', 'when', 'who', 'what']
    found = [w for w in question_words if w in query_lower]
    if len(found) >= 2:
        return True
    # Check for connector words between two topics
    for pattern in splitters:
        if re.search(pattern, query_lower):
            parts = re.split(pattern, query_lower)
            if len(parts) >= 2 and all(len(p.strip()) > 5 for p in parts):
                return True
    return False


def split_into_sub_queries(query):
    """Split a multi-question into individual sub-queries."""
    # Split on Filipino/English connectors
    pattern = r'(?:\band\b|\bat\b|\bpati\b|\btapos\b|\btsaka\b|\bsaka\b|[?](?=\s*[a-zA-Z]))'
    parts = re.split(pattern, query, flags=re.IGNORECASE)
    # Clean and filter meaningful parts
    sub_queries = [p.strip() for p in parts if len(p.strip()) > 8]
    return sub_queries if len(sub_queries) >= 2 else [query]


def generate_multi_response(user_input, sub_queries):
    """Handle multiple questions by searching and answering each separately."""
    municipality = charter_data.get('municipality', 'Pagsanjan')
    province = charter_data.get('province', 'Laguna')

    combined_context = ""
    service_blocks = []

    for sub_q in sub_queries[:3]:  # max 3 sub-questions
        results = search_knowledge(sub_q, top_k=1)
        results = refine_search_with_keywords(sub_q, results)
        if results and results[0]['score'] <= 100:
            svc = results[0]['metadata']
            block = f"Service: {svc['service_name']}\nOffice: {svc['office']}\n"
            if svc.get('who_may_avail'):
                block += f"Who may avail: {svc['who_may_avail']}\n"
            if svc.get('requirements'):
                reqs = [r.get('document', '') or r.get('requirement', '') for r in svc['requirements'][:3]]
                block += f"Requirements: {'; '.join([r for r in reqs if r])}\n"
            if svc.get('fees_text'):
                block += f"Fees: {calculate_total_fees(svc['fees_text'])}\n"
            if svc.get('time_text'):
                block += f"Processing time: {calculate_total_time(svc['time_text'])}\n"
            combined_context += f"\n---\n{block}"
            service_blocks.append(svc)

    if not service_blocks:
        return None

    prompt = f"""Ikaw ay isang magalang at matulunging government assistant ng Municipal Government ng {municipality}, {province}.

Sumagot sa BAWAT tanong ng mamamayan nang malinaw at magkakahiwalay. Gamitin ang Filipino o Taglish. Maging tiyak sa bawat sagot — banggitin ang opisina, bayad, at requirements kung available.

Impormasyon mula sa Citizens Charter:
{combined_context[:1200]}

Tanong ng mamamayan: "{user_input}"

Sagutin ang bawat tanong nang maayos at magkakahiwalay. Gumamit ng numbering (1., 2.) para sa bawat sagot."""

    try:
        chat_completion = groq_client.chat.completions.create(
            messages=[{"role": "user", "content": prompt}],
            model="llama-3.3-70b-versatile",
            temperature=0.6,
            max_tokens=400
        )
        llm_response = chat_completion.choices[0].message.content
    except Exception as e:
        print(f"Groq Error: {e}")
        llm_response = "Narito ang impormasyon para sa inyong mga katanungan:"

    # Build structured details for each service
    full_response = llm_response + "\n\n"
    for svc in service_blocks:
        full_response += f"\n**{svc['service_name']}**\n"
        full_response += f"📍 Office: {svc['office']}\n"
        if svc.get('fees_text'):
            full_response += f"💰 Fees: {calculate_total_fees(svc['fees_text'])}\n"
        if svc.get('time_text'):
            full_response += f"⏱️ Processing Time: {calculate_total_time(svc['time_text'])}\n"
        if svc.get('requirements'):
            full_response += "📋 Requirements:\n"
            for req in svc['requirements'][:3]:
                req_text = req.get('document', '') or req.get('requirement', '')
                if req_text:
                    full_response += f"• {req_text[:120]}\n"
        full_response += "\n"

    follow_ups = []
    for svc in service_blocks[:2]:
        follow_ups.append(f"Full details: {svc['service_name'][:50]}")

    return {
        'short': llm_response,
        'full': full_response,
        'follow_ups': follow_ups
    }


def generate_hr_response(query, results):
    """Generate HR-focused response using Groq"""
    if not results:
        return None
    
    context = "Employee Data:\n"
    for i, result in enumerate(results[:3], 1):
        data = result['data']
        if result['type'] == 'employee':
            context += f"\n{i}. {result['name']}\n"
            context += f"   Employee ID: {data.get('employee_id', 'N/A')}\n"
            context += f"   Position: {data.get('position', 'N/A')}\n"
            context += f"   Department: {data.get('department_name', 'N/A')}\n"
            context += f"   Email: {data.get('email', 'N/A')}\n"
            context += f"   Status: {data.get('employment_status', 'N/A')}\n"
            context += f"   Date Hired: {data.get('date_hired', 'N/A')}\n"
        elif result['type'] == 'address':
            context += f"\n{i}. Address for {data.get('first_name', '')} {data.get('last_name', '')}\n"
            context += f"   {data.get('street', '')}, {data.get('city', '')}, {data.get('province', '')}\n"
        elif result['type'] == 'contact':
            context += f"\n{i}. Contact for {data.get('first_name', '')} {data.get('last_name', '')}\n"
            context += f"   Mobile: {data.get('mobile_number', 'N/A')}\n"
            context += f"   Phone: {data.get('telephone_number', 'N/A')}\n"
        elif result['type'] == 'education':
            context += f"\n{i}. Education for {data.get('first_name', '')} {data.get('last_name', '')}\n"
            context += f"   {data.get('level', '')}: {data.get('degree', '')} at {data.get('school_name', '')}\n"
        elif result['type'] == 'training':
            context += f"\n{i}. Training for {data.get('first_name', '')} {data.get('last_name', '')}\n"
            context += f"   {data.get('title', '')} ({data.get('type', '')})\n"
        elif result['type'] == 'work_experience':
            context += f"\n{i}. Work Experience for {data.get('first_name', '')} {data.get('last_name', '')}\n"
            context += f"   {data.get('position_title', '')} at {data.get('company_name', '')}\n"
    
    prompt = f"""Ikaw ay isang HR assistant na tumutulong sa HR manager na mag-query ng employee data.

Ang HR manager ay nagtanong: "{query}"

Narito ang relevant data mula sa database:
{context[:1500]}

Magbigay ng malinaw at propesyonal na sagot sa Filipino o Taglish (2-3 pangungusap lang). I-format ang sagot para madaling basahin."""
    
    try:
        chat_completion = groq_client.chat.completions.create(
            messages=[{"role": "user", "content": prompt}],
            model="llama-3.3-70b-versatile",
            temperature=0.3,
            max_tokens=300
        )
        llm_response = chat_completion.choices[0].message.content
    except Exception as e:
        print(f"Groq Error: {e}")
        if "rate_limit" in str(e).lower() or "429" in str(e):
            llm_response = "Pasensya na, naubos na ang aking daily token limit. Subukan ulit mamaya. Salamat!"
        else:
            llm_response = "Narito ang nakita ko:"
    
    # Build detailed response
    full_response = f"{llm_response}\n\n"
    
    for i, result in enumerate(results[:5], 1):
        data = result['data']
        if result['type'] == 'employee':
            full_response += f"\n**{i}. {result['name']}**\n"
            full_response += f"👤 Employee ID: {data.get('employee_id', 'N/A')}\n"
            full_response += f"💼 Position: {data.get('position', 'N/A')}\n"
            full_response += f"🏢 Department: {data.get('department_name', 'N/A')}\n"
            full_response += f"📧 Email: {data.get('email', 'N/A')}\n"
            full_response += f"📊 Status: {data.get('employment_status', 'N/A')}\n"
            full_response += f"📅 Date Hired: {data.get('date_hired', 'N/A')}\n"
            full_response += f"🎂 Birth Date: {data.get('birth_date', 'N/A')}\n"
            full_response += f"⚧ Sex: {data.get('sex', 'N/A')}\n"
            full_response += f"💑 Civil Status: {data.get('civil_status', 'N/A')}\n"
        elif result['type'] == 'address':
            full_response += f"\n**{i}. Address - {data.get('first_name', '')} {data.get('last_name', '')}**\n"
            full_response += f"📍 {data.get('street', '')}, {data.get('barangay', '')}, {data.get('city', '')}, {data.get('province', '')} {data.get('zip_code', '')}\n"
        elif result['type'] == 'contact':
            full_response += f"\n**{i}. Contact - {data.get('first_name', '')} {data.get('last_name', '')}**\n"
            full_response += f"📱 Mobile: {data.get('mobile_number', 'N/A')}\n"
            full_response += f"☎️ Phone: {data.get('telephone_number', 'N/A')}\n"
        elif result['type'] == 'education':
            full_response += f"\n**{i}. Education - {data.get('first_name', '')} {data.get('last_name', '')}**\n"
            full_response += f"🎓 {data.get('level', '')}: {data.get('degree', '')}\n"
            full_response += f"🏫 School: {data.get('school_name', '')}\n"
            full_response += f"📅 Period: {data.get('period_from', '')} - {data.get('period_to', '')}\n"
        elif result['type'] == 'training':
            full_response += f"\n**{i}. Training - {data.get('first_name', '')} {data.get('last_name', '')}**\n"
            full_response += f"📚 {data.get('title', '')}\n"
            full_response += f"🏷️ Type: {data.get('type', '')}\n"
            full_response += f"📅 Date: {data.get('date_from', '')} - {data.get('date_to', '')}\n"
            full_response += f"⏱️ Hours: {data.get('number_of_hours', 'N/A')}\n"
        elif result['type'] == 'work_experience':
            full_response += f"\n**{i}. Work Experience - {data.get('first_name', '')} {data.get('last_name', '')}**\n"
            full_response += f"💼 Position: {data.get('position_title', '')}\n"
            full_response += f"🏢 Company: {data.get('company_name', '')}\n"
            full_response += f"📅 Period: {data.get('date_from', '')} - {data.get('date_to', '')}\n"
    
    return {
        'short': llm_response,
        'full': full_response
    }

@app.route('/')
def home():
    session['conversation_history'] = []
    return render_template('index.html')

@app.route('/admin')
def admin():
    session['conversation_history'] = []
    return render_template('adminChatbot.html')


@app.route('/admin/knowledge', methods=['GET', 'POST'])
def admin_knowledge():
    if request.method == 'POST':
        data = request.json
        try:
            conn = get_db_connection()
            cursor = conn.cursor()
            cursor.execute(
                "INSERT INTO employees (employee_id, first_name, last_name, email, sex, civil_status, birth_date) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                (data.get('employee_id'), data.get('first_name'), data.get('last_name'), data.get('email'), data.get('sex'), data.get('civil_status'), data.get('birth_date'))
            )
            conn.commit()
            cursor.close()
            conn.close()
            return jsonify({'status': 'success'})
        except Exception as e:
            return jsonify({'status': 'error', 'message': str(e)}), 500
    
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, employee_id, first_name, last_name, email FROM employees LIMIT 20")
        kb_list = cursor.fetchall()
        cursor.close()
        conn.close()
        return jsonify(kb_list)
    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route('/admin/knowledge/<int:kb_id>', methods=['DELETE'])
def delete_knowledge(kb_id):
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("DELETE FROM documents WHERE id = %s", (kb_id,))
        conn.commit()
        cursor.close()
        conn.close()
        return jsonify({'status': 'success'})
    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route('/admin/stats')
def admin_stats():
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT COUNT(*) as count FROM employees")
        total_kb = cursor.fetchone()[0]
        cursor.close()
        conn.close()
    except:
        total_kb = 0
    
    return jsonify({
        'total_questions': len(session.get('conversation_history', [])),
        'total_kb': total_kb,
        'avg_time': 150
    })

@app.route('/admin/logs')
def admin_logs():
    logs = [{'question': h.get('user', ''), 'answer': h.get('bot', '')[:100], 
             'timestamp': h.get('timestamp', '')} 
            for h in session.get('conversation_history', [])]
    return jsonify(logs)

def generate_list_response(query, list_type):
    """Generate response for listing services or offices"""
    if list_type == 'offices':
        offices = sorted(set(service['office'] for service in charter_data['services']))
        response = f"The Municipality of {charter_data['municipality']}, {charter_data['province']} has the following offices:\n\n"
        for i, office in enumerate(offices, 1):
            response += f"{i}. {office}\n"
        response += f"\n📊 Total: {len(offices)} offices\n\nYou can ask me about specific services from any of these offices!"
        return response
    
    elif list_type == 'services':
        response = f"The Municipality of {charter_data['municipality']}, {charter_data['province']} offers {charter_data['total_services']} services. Here are some key services:\n\n"
        services_by_office = {}
        for service in charter_data['services'][:30]:
            office = service['office']
            if office not in services_by_office:
                services_by_office[office] = []
            services_by_office[office].append(service['service_name'])
        
        for office, services in list(services_by_office.items())[:10]:
            response += f"\n**{office}:**\n"
            for svc in services[:3]:
                response += f"• {svc}\n"
        
        response += f"\n💡 Ask me about a specific service or office for detailed information!"
        return response

def generate_office_services_list(office_name):
    """List all services for a specific office"""
    services = [s for s in charter_data['services'] if office_name.lower() in s['office'].lower()]
    
    if not services:
        return None, []
    
    office = services[0]['office']
    response = f"**{office}** offers the following services:\n\n"
    
    for i, service in enumerate(services, 1):
        response += f"{i}. {service['service_name']}\n"
    
    response += f"\n📊 Total: {len(services)} service(s)"
    
    follow_ups = []
    for service in services[:3]:
        short_name = service['service_name'][:50] + '...' if len(service['service_name']) > 50 else service['service_name']
        follow_ups.append(f"Details: {short_name}")
    
    return response, follow_ups

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
        
        is_valid, error_msg = is_valid_query(user_input)
        if not is_valid:
            return jsonify({
                'response': error_msg,
                'follow_up_questions': ["Show me all employees"],
                'status': 'success'
            })
        
        # Check for update intent
        if detect_update_intent(user_input):
            print(f"[DEBUG] Update intent detected for: {user_input}")
            employee_id, employee_name, field, new_value = extract_update_info(user_input)
            print(f"[DEBUG] Extracted - ID: {employee_id}, Name: {employee_name}, Field: {field}, Value: {new_value}")
            
            if not (employee_id or employee_name) or not field or not new_value:
                return jsonify({
                    'response': "Para mag-update, sabihin mo:\n• 'Update status of Juan from active to inactive'\n• 'Update employee ID 1000 email to newemail@gmail.com'\n• 'Change position of Maria to Manager'",
                    'status': 'success'
                })
            
            success, error, old_value = update_employee_data(employee_id, employee_name, field, new_value)
            print(f"[DEBUG] Update result - Success: {success}, Error: {error}, Old: {old_value}")
            
            if success:
                identifier = employee_id or employee_name
                response = f"✅ Successfully updated!\n\n"
                response += f"Employee: {identifier}\n"
                response += f"Field: {field}\n"
                if old_value:
                    response += f"Old value: {old_value}\n"
                response += f"New value: {new_value}"
            else:
                response = f"❌ Update failed: {error or 'Employee not found.'}"
            
            return jsonify({
                'response': response,
                'status': 'success'
            })
        
        # HR-specific queries
        results = search_knowledge(user_input, top_k=10)
        
        if not results:
            return jsonify({
                'response': "I couldn't find any employee data. Try asking 'Show me all employees'",
                'follow_up_questions': ["Show me all employees"],
                'status': 'success'
            })
        
        response_data = generate_hr_response(user_input, results)
        
        if not response_data:
            return jsonify({
                'response': "No data found for your query.",
                'follow_up_questions': ["Show me all employees"],
                'status': 'success'
            })
        
        follow_ups = ["Show more details", "What is their contact information?"]
        
        session['conversation_history'][-1]['bot'] = response_data['full']
        session.modified = True
        
        return jsonify({
            'response': response_data['short'],
            'full_response': response_data['full'],
            'has_details': True,
            'follow_up_questions': follow_ups,
            'status': 'success'
        })
    
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