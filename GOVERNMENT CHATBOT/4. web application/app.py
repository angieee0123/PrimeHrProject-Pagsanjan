#imports
from flask import Flask, render_template, request, jsonify
from flask_cors import CORS
import json
import pickle
import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
from transformers import AutoTokenizer, AutoModelForSeq2SeqLM
import torch
import os

app = Flask(__name__)
CORS(app, resources={r"/chat": {"origins": ["http://localhost:8000", "http://127.0.0.1:8000"]}})

# Load models and data
print("Loading models and data...")
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, '..', '3. training script', 'models')
DATA_DIR = os.path.join(BASE_DIR, '..', '1. raw dataset')

# Load citizen charter data
with open(os.path.join(DATA_DIR, 'citizens_charter_2025_sampaloc_quezon.json'), 'r', encoding='utf-8') as f:
    charter_data = json.load(f)

print(f"Municipality: {charter_data['municipality']}, {charter_data['province']}")
print(f"Total Services: {charter_data['total_services']}")

embedder = SentenceTransformer(os.path.join(MODELS_DIR, 'sentence_transformer'))
faiss_index = faiss.read_index(os.path.join(MODELS_DIR, 'faiss_index.bin'))
with open(os.path.join(MODELS_DIR, 'documents.json'), 'r', encoding='utf-8') as f:
    knowledge_base = json.load(f)

# Load conversational model
print("Loading conversational model...")
tokenizer = AutoTokenizer.from_pretrained("google/flan-t5-small")
llm = AutoModelForSeq2SeqLM.from_pretrained("google/flan-t5-small")
print("✓ All models loaded")

def search_knowledge(query, top_k=3):
    """Search knowledge base using semantic similarity"""
    query_embedding = embedder.encode([query])
    distances, indices = faiss_index.search(query_embedding.astype('float32'), top_k)
    
    results = []
    for idx, score in zip(indices[0], distances[0]):
        results.append({
            'service': knowledge_base[idx]['service_name'],
            'office': knowledge_base[idx]['office'],
            'score': float(score),
            'metadata': knowledge_base[idx]
        })
    return results

def refine_search_with_keywords(query, results):
    """Refine search results using keyword matching for better accuracy"""
    import re
    
    # Extract important keywords from query
    query_lower = query.lower()
    
    # Define key service keywords
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
    
    # Check if query contains specific keywords
    for keyword, service_names in service_keywords.items():
        if keyword in query_lower:
            # Re-rank results: prioritize services matching the keyword
            for result in results:
                service_name_lower = result['service'].lower()
                if any(svc in service_name_lower for svc in service_names):
                    result['score'] = result['score'] * 0.5  # Boost by reducing distance
            
            # Re-sort by score
            results.sort(key=lambda x: x['score'])
            break
    
    return results

def is_valid_query(query):
    """Check if user input is valid and meaningful"""
    import re
    
    # Check if too short
    if len(query.strip()) < 3:
        return False, "Your message is too short. Could you please provide more details?"
    
    # Check if mostly gibberish (high ratio of consonants without vowels)
    vowels = len(re.findall(r'[aeiouAEIOU]', query))
    consonants = len(re.findall(r'[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]', query))
    
    if consonants > 0 and vowels / max(consonants, 1) < 0.2:  # Less than 20% vowels
        return False, "I couldn't understand your message. Could you please rephrase your question?"
    
    # Check if contains mostly repeated characters
    if len(set(query.lower().replace(' ', ''))) < 3:
        return False, "I couldn't understand your message. Could you please ask a clear question?"
    
    # Check if contains too many special characters
    special_chars = len(re.findall(r'[^a-zA-Z0-9\s]', query))
    if special_chars > len(query) * 0.5:  # More than 50% special characters
        return False, "I couldn't understand your message. Could you please use regular text?"
    
    return True, None

def calculate_total_time(time_string):
    """Parse and calculate total processing time from string"""
    if not time_string:
        return "Not specified"
    
    import re
    
    # Extract all time values
    total_minutes = 0
    total_hours = 0
    total_days = 0
    
    # Find all patterns like "20 minutes", "2 hours", "3 days"
    minutes = re.findall(r'(\d+)\s*(?:minute|min)', time_string, re.IGNORECASE)
    hours = re.findall(r'(\d+)\s*(?:hour|hr)', time_string, re.IGNORECASE)
    days = re.findall(r'(\d+)\s*(?:day)', time_string, re.IGNORECASE)
    
    # Sum up all values
    total_minutes = sum(int(m) for m in minutes)
    total_hours = sum(int(h) for h in hours)
    total_days = sum(int(d) for d in days)
    
    # Convert everything to minutes first
    total_minutes += total_hours * 60
    total_minutes += total_days * 1440  # 24 hours * 60 minutes
    
    if total_minutes == 0:
        return time_string  # Return original if no parseable time found
    
    # Convert back to readable format
    if total_minutes < 60:
        return f"{total_minutes} minutes"
    elif total_minutes < 1440:
        hours = total_minutes // 60
        mins = total_minutes % 60
        if mins > 0:
            return f"{hours} hour{'s' if hours > 1 else ''} {mins} minutes"
        return f"{hours} hour{'s' if hours > 1 else ''}"
    else:
        days = total_minutes // 1440
        remaining_mins = total_minutes % 1440
        hours = remaining_mins // 60
        if hours > 0:
            return f"{days} day{'s' if days > 1 else ''} {hours} hour{'s' if hours > 1 else ''}"
        return f"{days} day{'s' if days > 1 else ''}"

def calculate_total_fees(fees_string):
    """Parse and calculate total fees from string"""
    if not fees_string or fees_string.lower() in ['no fees', 'none', 'free']:
        return "No fees"
    
    import re
    
    # Find all PHP amounts like "PHP 55.00" or "PHP 35"
    amounts = re.findall(r'PHP\s*([\d,]+\.?\d*)', fees_string, re.IGNORECASE)
    
    if not amounts:
        return fees_string  # Return original if no parseable fees found
    
    # Sum all amounts
    total = sum(float(amt.replace(',', '')) for amt in amounts)
    
    # Format with 2 decimal places
    return f"PHP {total:,.2f}"


def generate_conversational_response(query, service):
    """Generate natural conversational response using FLAN-T5-Base"""
    
    # Skip LLM for full details requests - just show structured info
    response = f"**{service['service_name']}**\n\n"
    response += f"📍 **Office:** {service['office']}\n\n"
    
    if service.get('requirements'):
        response += "📋 **Requirements:**\n"
        for req in service['requirements'][:5]:
            req_text = req.get('document', '')
            where = req.get('where_to_secure', '')
            if req_text:
                response += f"• {req_text[:150]}"
                if where:
                    response += f" *(from {where})*"
                response += "\n"
        response += "\n"
    
    if service.get('process_flow'):
        response += "📝 **Step-by-Step Process:**\n"
        for step in service['process_flow']:
            step_num = step.get('step_number') or step.get('step')
            client_action = step.get('client_action', '')
            if client_action:
                response += f"\n**Step {step_num}:** {client_action}\n"
                if step.get('agency_actions'):
                    for action in step['agency_actions']:
                        if action.get('processing_time') and 'included' not in action.get('processing_time', '').lower():
                            response += f"   ⏱️ {action['processing_time']}"
                            if action.get('fees') and action['fees'].lower() not in ['none', '']:
                                response += f" | 💰 {action['fees']}"
                            response += "\n"
                            break
        response += "\n"
    
    if service.get('fees_text') and service['fees_text'] != 'No fees':
        calculated_fees = calculate_total_fees(service['fees_text'])
        response += f"💰 **Total Fees:** {calculated_fees}\n\n"
    
    if service.get('time_text'):
        calculated_time = calculate_total_time(service['time_text'])
        response += f"⏱️ **Total Processing Time:** {calculated_time}"
    
    return response

@app.route('/')
def home():
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
        for service in charter_data['services'][:30]:  # Show first 30
            office = service['office']
            if office not in services_by_office:
                services_by_office[office] = []
            services_by_office[office].append(service['service_name'])
        
        for office, services in list(services_by_office.items())[:10]:  # Show 10 offices
            response += f"\n**{office}:**\n"
            for svc in services[:3]:  # Max 3 services per office
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
    
    # Generate shorter follow-up questions
    follow_ups = []
    for service in services[:3]:  # First 3 services
        # Shorten service name if too long
        short_name = service['service_name'][:50] + '...' if len(service['service_name']) > 50 else service['service_name']
        follow_ups.append(f"Details: {short_name}")
    
    return response, follow_ups

@app.route('/chat', methods=['POST'])
def chat():
    try:
        user_input = request.json.get('message', '').strip()
        if not user_input:
            return jsonify({'error': 'No message provided'}), 400
        
        # Validate user input first
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
        
        # Check for mayor-related questions
        mayor_keywords = ['who is the mayor', 'name of the mayor', 'mayor name', 'current mayor', 'municipal mayor', 'who is mayor', 'sino ang mayor', 'sino mayor', 'pangalan ng mayor', 'sino ang ating mayor', 'sino po ang mayor']
        if any(keyword in query_lower for keyword in mayor_keywords):
            mayor_name = charter_data.get('mayor', 'Nicolai Andrei T. Devanadera')
            response = f"The mayor of the municipality is: **{mayor_name}**\n\n"
            response += "\U0001f4cd Office: Municipal Mayor's Office\n"
            response += "\U0001f3db\ufe0f Municipality of Sampaloc, Quezon"
            follow_ups = [
                "What services does the Mayor's Office offer?",
                "How do I get a Mayor's Clearance?",
                "How do I apply for a business permit?"
            ]
            return jsonify({'response': response, 'follow_up_questions': follow_ups, 'status': 'success'})

        # Check for greetings and general questions first
        greeting_keywords = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 
                            'can you help', 'help me', 'i need help', 'assist me']
        
        if any(keyword in query_lower for keyword in greeting_keywords) and len(user_input.split()) <= 6:
            response = "Hello! I'm here to assist you with municipal services in Sampaloc, Quezon. I can help you with:\n\n"
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
        
        # Check if asking about services from a specific office
        office_keywords = ['accounting', 'agriculture', 'assessor', 'budget', 'civil registry', 
                          'disaster', 'engineering', 'environment', 'health', 'human resource',
                          'mayor', 'planning', 'social welfare', 'treasurer', 'sangguniang', 
                          'tourism', 'ict', 'information']
        
        asking_about_office = any(keyword in query_lower for keyword in office_keywords)
        asking_about_services = 'service' in query_lower
        
        if asking_about_office and asking_about_services:
            # User asking about services from specific office
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
        
        # Check if user wants to list ALL services or offices
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
        
        # Check if asking for "full details" or "tell me about"
        detail_keywords = ['full details', 'show full', 'complete information', 'tell me about', 'show details', 'how to get', 'how do i get', 'how can i get']
        wants_full_details = any(keyword in query_lower for keyword in detail_keywords)
        
        # Default: semantic search for specific service
        results = search_knowledge(user_input, top_k=3)
        
        # Refine results with keyword matching
        results = refine_search_with_keywords(user_input, results)
        
        # Check if search results are too poor (low confidence)
        if not results or results[0]['score'] > 100:  # High distance = low similarity
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
        
        # Detect if asking about specific aspects (fees, requirements, time)
        asking_about_fees = any(word in query_lower for word in ['fee', 'cost', 'pay', 'price', 'charge', 'how much'])
        asking_about_requirements = any(word in query_lower for word in ['requirement', 'need', 'document', 'bring'])
        asking_about_time = any(word in query_lower for word in ['how long', 'duration', 'time', 'take'])
        
        # If asking for full details, show everything
        if wants_full_details:
            response = generate_conversational_response(user_input, service)
            follow_ups = [
                f"What are the requirements for {service_name}?",
                f"How much does {service_name} cost?",
                f"How long does {service_name} take?"
            ]
        elif asking_about_fees:
            # Show brief summary with fee highlighted
            response = f"{service.get('description', service['service_name'])}\n\n"
            response += f"**{service['service_name']}**\n"
            response += f"📍 Office: {service['office']}\n"
            
            # Add fee information
            if service.get('fees_text') and service['fees_text'] != 'No fees':
                calculated_fees = calculate_total_fees(service['fees_text'])
                response += f"💰 **Fee:** {calculated_fees}"
            else:
                response += f"💰 **Fee:** No fees required"
            
            follow_ups = [
                f"What are the requirements for {service_name}?",
                f"How long does {service_name} take?",
                f"Show full details for {service_name}"
            ]
        elif asking_about_requirements:
            # Show brief summary with requirements highlighted
            response = f"{service.get('description', service['service_name'])}\n\n"
            response += f"**{service['service_name']}**\n"
            response += f"📍 Office: {service['office']}\n\n"
            
            if service.get('requirements'):
                response += "📋 **Key Requirements:**\n"
                for req in service['requirements'][:3]:
                    req_text = req.get('document', '')
                    if req_text:
                        response += f"• {req_text[:100]}\n"
            else:
                response += "📋 **Requirements:** No specific requirements listed"
            
            follow_ups = [
                f"How much does {service_name} cost?",
                f"How long does {service_name} take?",
                f"Show full details for {service_name}"
            ]
        elif asking_about_time:
            # Show brief summary with processing time highlighted
            response = f"{service.get('description', service['service_name'])}\n\n"
            response += f"**{service['service_name']}**\n"
            response += f"📍 Office: {service['office']}\n"
            
            if service.get('time_text'):
                calculated_time = calculate_total_time(service['time_text'])
                response += f"⏱️ **Processing Time:** {calculated_time}"
            else:
                response += f"⏱️ **Processing Time:** Not specified"
            
            follow_ups = [
                f"What are the requirements for {service_name}?",
                f"How much does {service_name} cost?",
                f"Show full details for {service_name}"
            ]
        else:
            # Otherwise, show brief summary only
            response = f"{service.get('description', service['service_name'])}\n\n"
            response += f"**{service['service_name']}**\n"
            response += f"📍 Office: {service['office']}\n"
            if service.get('who_may_avail'):
                response += f"👥 Who may avail: {service['who_may_avail']}"
            
            follow_ups = [
                f"What are the requirements for {service_name}?",
                f"How much does {service_name} cost?",
                f"How long does {service_name} take?"
            ]
        
        return jsonify({
            'response': response,
            'relevant_services': [r['service'] for r in results],
            'follow_up_questions': follow_ups,
            'status': 'success'
        })
    
    except Exception as e:
        print(f"Error: {str(e)}")  # Log error
        import traceback
        traceback.print_exc()
        return jsonify({
            'response': 'Sorry, an error occurred. Please try again.',
            'error': str(e),
            'status': 'error'
        }), 500

if __name__ == '__main__':
    app.run(debug=True, port=5000)
