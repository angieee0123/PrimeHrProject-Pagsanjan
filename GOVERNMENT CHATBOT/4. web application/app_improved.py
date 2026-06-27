#imports with new importsssss
from flask import Flask, render_template, request, jsonify, session
from flask_cors import CORS
import json
import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
import os
from functools import lru_cache
from datetime import datetime
import re
from difflib import SequenceMatcher
from groq import Groq

app = Flask(__name__)
app.secret_key = os.urandom(24)
# Allow all origins for production (or specify Railway domains)
CORS(app, resources={r"/chat": {"origins": "*"}})

# Load models and data
print("Loading models and data...")
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, '..', '3. training script', 'models')
DATA_DIR = os.path.join(BASE_DIR, '..', '1. raw dataset')

with open(os.path.join(DATA_DIR, 'citizens_charter_2025_sampaloc_quezon.json'), 'r', encoding='utf-8') as f:
    charter_data = json.load(f)

print(f"Municipality: {charter_data['municipality']}, {charter_data['province']}")
print(f"Total Services: {charter_data['total_services']}")

# Initialize Groq client
groq_client = Groq(api_key="***REMOVED-GROQ-KEY***")

embedder = SentenceTransformer('all-MiniLM-L6-v2')
faiss_index = faiss.read_index(os.path.join(MODELS_DIR, 'faiss_index.bin'))
with open(os.path.join(MODELS_DIR, 'documents.json'), 'r', encoding='utf-8') as f:
    knowledge_base = json.load(f)

print("✓ All models loaded")

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

def search_knowledge(query, top_k=3):
    """Enhanced search with query expansion and caching"""
    cache_key = f"{query}_{top_k}"
    if cache_key in response_cache:
        return response_cache[cache_key]
    
    # Try expanded queries
    expanded_queries = expand_query(query)
    all_results = []
    
    for exp_query in expanded_queries:
        query_embedding = get_embedding(exp_query)
        distances, indices = faiss_index.search(query_embedding, top_k)
        
        for idx, score in zip(indices[0], distances[0]):
            all_results.append({
                'service': knowledge_base[idx]['service_name'],
                'office': knowledge_base[idx]['office'],
                'score': float(score),
                'metadata': knowledge_base[idx]
            })
    
    # Deduplicate and sort
    seen = set()
    unique_results = []
    for r in sorted(all_results, key=lambda x: x['score']):
        if r['service'] not in seen:
            seen.add(r['service'])
            unique_results.append(r)
            if len(unique_results) >= top_k:
                break
    
    # Fallback to fuzzy matching if poor results
    if not unique_results or unique_results[0]['score'] > 100:
        fuzzy_results = fuzzy_match_service(query)
        if fuzzy_results:
            unique_results = [{
                'service': s[0]['service_name'],
                'office': s[0]['office'],
                'score': (1 - s[1]) * 50,
                'metadata': s[0]
            } for s in fuzzy_results]
    
    response_cache[cache_key] = unique_results
    return unique_results

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


def generate_conversational_response(query, service):
    """Generate natural conversational response using Groq Llama 3.3 70B"""
    
    # Build context from service data
    context = f"""Service: {service['service_name']}
Office: {service['office']}
Description: {service.get('description', '')}
Who may avail: {service.get('who_may_avail', '')}"""
    
    if service.get('requirements'):
        reqs = [req.get('document', '') for req in service['requirements'][:3]]
        context += f"\nRequirements: {'; '.join([r for r in reqs if r])}"
    
    if service.get('fees_text'):
        context += f"\nFees: {service['fees_text']}"
    if service.get('time_text'):
        context += f"\nProcessing time: {service['time_text']}"
    
    prompt = f"""Ikaw ay isang magalang at matulunging government assistant ng Municipal Government ng Pagsanjan, Laguna. Sagutin ang tanong ng mamamayan nang malinaw at tiyak sa Filipino o Taglish (2-3 pangungusap lang). Banggitin ang opisina, bayad, o requirements kung relevant.

Impormasyon ng Serbisyo:
{context[:800]}

Tanong: {query}

Sagot:"""
    
    try:
        chat_completion = groq_client.chat.completions.create(
            messages=[{"role": "user", "content": prompt}],
            model="llama-3.3-70b-versatile",
            temperature=0.7,
            max_tokens=200
        )
        llm_response = chat_completion.choices[0].message.content
    except Exception as e:
        print(f"Groq Error: {e}")
        llm_response = f"Here's information about {service['service_name']}:"
    
    # Short response (summary)
    short_response = f"{llm_response}\n\n**{service['service_name']}**\n\n📍 Office: {service['office']}\n"
    if service.get('fees_text'):
        short_response += f"💰 Total Fees: {calculate_total_fees(service['fees_text'])}\n"
    if service.get('time_text'):
        short_response += f"⏱️ Total Processing Time: {calculate_total_time(service['time_text'])}"
    
    # Full response (detailed)
    full_response = f"{llm_response}\n\n**{service['service_name']}**\n\n📍 Office: {service['office']}\n\n"
    
    if service.get('requirements'):
        full_response += "📋 Requirements:\n"
        for req in service['requirements'][:5]:
            req_text = req.get('document', '')
            where = req.get('where_to_secure', '')
            if req_text:
                full_response += f"• {req_text[:150]}"
                if where:
                    full_response += f" *(from {where})*"
                full_response += "\n"
        full_response += "\n"
    
    if service.get('process_flow'):
        full_response += "📝 Step-by-Step Process:\n"
        for step in service['process_flow']:
            step_num = step.get('step_number') or step.get('step')
            client_action = step.get('client_action', '')
            if client_action:
                full_response += f"\nStep {step_num}: {client_action}\n"
                if step.get('agency_actions'):
                    for action in step['agency_actions']:
                        if action.get('processing_time') and 'included' not in action.get('processing_time', '').lower():
                            full_response += f"⏱️ {action['processing_time']}"
                            if action.get('fees') and action['fees'].lower() not in ['none', '']:
                                full_response += f" | 💰 {action['fees']}"
                            full_response += "\n"
                            break
        full_response += "\n"
    
    if service.get('fees_text') and service['fees_text'] != 'No fees':
        calculated_fees = calculate_total_fees(service['fees_text'])
        full_response += f"💰 Total Fees: {calculated_fees}\n\n"
    
    if service.get('time_text'):
        calculated_time = calculate_total_time(service['time_text'])
        full_response += f"⏱️ Total Processing Time: {calculated_time}"
    
    return {'short': short_response, 'full': full_response}

@app.route('/')
def home():
    session['conversation_history'] = []
    return render_template('index.html')

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
        
        # Initialize conversation history
        if 'conversation_history' not in session:
            session['conversation_history'] = []
        
        # Add to conversation history
        session['conversation_history'].append({
            'user': user_input,
            'timestamp': datetime.now().isoformat()
        })
        
        # Keep only last 5 exchanges
        if len(session['conversation_history']) > 5:
            session['conversation_history'] = session['conversation_history'][-5:]
        
        is_valid, error_msg = is_valid_query(user_input)
        if not is_valid:
            follow_ups = [
                "What are all the offices?",
                "Show me all services",
                "How do I get a birth certificate?"
            ]
            return jsonify({
                'response': error_msg,
                'follow_up_questions': follow_ups,
                'status': 'success'
            })
        
        query_lower = user_input.lower()
        
        # Check for mayor-related questions (supports misspellings and name mentions)
        if is_mayor_question(user_input):
            mayor_name = charter_data.get('mayor', 'Mayor Januario Ferry G. Garcia')
            municipality = charter_data.get('municipality', 'Pagsanjan')
            province = charter_data.get('province', 'Laguna')

            prompt = f"""You are a helpful government assistant for the Municipality of {municipality}, {province}.

The current mayor is {mayor_name}.

A citizen asked: "{user_input}"

Answer in a friendly, conversational tone in 2-3 sentences. Include the mayor's full name in your response."""

            try:
                chat_completion = groq_client.chat.completions.create(
                    messages=[{"role": "user", "content": prompt}],
                    model="llama-3.3-70b-versatile",
                    temperature=0.7,
                    max_tokens=150
                )
                response = chat_completion.choices[0].message.content
            except Exception as e:
                print(f"Groq Error: {e}")
                response = f"The mayor of the municipality is: **{mayor_name}**"

            response += f"\n\n\U0001f4cd Office: Municipal Mayor's Office\n"
            response += f"\U0001f3db\ufe0f Municipality of {municipality}, {province}"
            follow_ups = [
                "What services does the Mayor's Office offer?",
                "How do I get a Mayor's Clearance?",
                "How do I apply for a business permit?"
            ]
            return jsonify({'response': response, 'follow_up_questions': follow_ups, 'status': 'success'})

        greeting_keywords = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 
                            'can you help', 'help me', 'i need help', 'assist me']
        
        if any(keyword in query_lower for keyword in greeting_keywords) and len(user_input.split()) <= 6:
            response = "Hello! I'm here to assist you with municipal services in Pagsanjan, Laguna. I can help you with:\n\n"
            response += "• Information about government services\n"
            response += "• Requirements and procedures\n"
            response += "• Office locations and contact details\n\n"
            response += "What would you like to know?"
            
            follow_ups = [
                "What are all the offices?",
                "Show me all services",
                "How do I get a birth certificate?"
            ]
            return jsonify({'response': response, 'follow_up_questions': follow_ups, 'status': 'success'})
        
        # --- Multi-question detection ---
        if detect_multiple_questions(user_input):
            sub_queries = split_into_sub_queries(user_input)
            multi_result = generate_multi_response(user_input, sub_queries)
            if multi_result:
                session['conversation_history'][-1]['bot'] = multi_result['full']
                session.modified = True
                return jsonify({
                    'response': multi_result['short'],
                    'full_response': multi_result['full'],
                    'has_details': True,
                    'follow_up_questions': multi_result['follow_ups'],
                    'status': 'success'
                })

        office_keywords = ['accounting', 'agriculture', 'assessor', 'budget', 'civil registry', 
                          'disaster', 'engineering', 'environment', 'health', 'human resource',
                          'mayor', 'planning', 'social welfare', 'treasurer', 'sangguniang', 
                          'tourism', 'ict', 'information']
        
        asking_about_office = any(keyword in query_lower for keyword in office_keywords)
        asking_about_services = 'service' in query_lower
        
        if asking_about_office and asking_about_services:
            for keyword in office_keywords:
                if keyword in query_lower:
                    office_response, follow_ups = generate_office_services_list(keyword)
                    if office_response:
                        return jsonify({
                            'response': office_response, 
                            'follow_up_questions': follow_ups,
                            'status': 'success'
                        })
                    break
        
        list_all_keywords = ['all', 'enumerate', 'list all', 'show all', 'what are all']
        wants_full_list = any(keyword in query_lower for keyword in list_all_keywords)
        
        if wants_full_list:
            if 'office' in query_lower:
                response = generate_list_response(user_input, 'offices')
                follow_ups = [
                    "What services does Municipal Health Office offer?",
                    "What services does Municipal Agriculture Office offer?",
                    "What services does Municipal Treasurer's Office offer?"
                ]
                return jsonify({'response': response, 'follow_up_questions': follow_ups, 'status': 'success'})
            elif 'service' in query_lower:
                response = generate_list_response(user_input, 'services')
                follow_ups = [
                    "Tell me about business permit",
                    "How do I get a birth certificate?",
                    "What are the health services available?"
                ]
                return jsonify({'response': response, 'follow_up_questions': follow_ups, 'status': 'success'})
        
        detail_keywords = ['full details', 'show full', 'complete information', 'tell me about', 'show details', 'how to get', 'how do i get', 'how can i get']
        wants_full_details = any(keyword in query_lower for keyword in detail_keywords)
        
        results = search_knowledge(user_input, top_k=3)
        results = refine_search_with_keywords(user_input, results)
        
        if not results or results[0]['score'] > 100:
            response = "I'm not sure I understood your question correctly. Could you please rephrase it or be more specific?\n\n"
            response += "For example, you can ask:\n"
            response += "• 'How do I get a birth certificate?'\n"
            response += "• 'What are the requirements for business permit?'\n"
            response += "• 'Show me all health services'"
            
            follow_ups = [
                "What are all the offices?",
                "Show me all services",
                "How do I get a birth certificate?"
            ]
            return jsonify({
                'response': response,
                'follow_up_questions': follow_ups,
                'status': 'success'
            })
        
        service = results[0]['metadata']
        service_name = service['service_name']
        
        # Always use Groq for natural conversational responses
        response_data = generate_conversational_response(user_input, service)
        follow_ups = [
            f"What are the requirements for {service_name}?",
            f"How much does {service_name} cost?",
            f"How long does {service_name} take?"
        ]
        
        # Add to conversation history
        session['conversation_history'][-1]['bot'] = response_data['full']
        session.modified = True
        
        return jsonify({
            'response': response_data['short'],
            'full_response': response_data['full'],
            'has_details': True,
            'relevant_services': [r['service'] for r in results],
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
    port = int(os.getenv('PORT', 5000))
    app.run(debug=False, host='0.0.0.0', port=port)