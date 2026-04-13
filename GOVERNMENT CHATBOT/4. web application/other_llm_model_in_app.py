#imports
from flask import Flask, render_template, request, jsonify
import json
import pickle
import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
from transformers import AutoTokenizer, AutoModelForCausalLM
import torch
import os

app = Flask(__name__)

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

embedder = SentenceTransformer('all-MiniLM-L6-v2')
faiss_index = faiss.read_index(os.path.join(MODELS_DIR, 'faiss_index.bin'))
with open(os.path.join(MODELS_DIR, 'documents.json'), 'r', encoding='utf-8') as f:
    knowledge_base = json.load(f)

# Load conversational model (TinyLlama)
print("Loading TinyLlama conversational model...")
tokenizer = AutoTokenizer.from_pretrained("TinyLlama/TinyLlama-1.1B-Chat-v1.0")
llm = AutoModelForCausalLM.from_pretrained(
    "TinyLlama/TinyLlama-1.1B-Chat-v1.0",
    torch_dtype=torch.float32,
    device_map="cpu"
)
print("✓ TinyLlama loaded successfully")

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
    
    if 'business permit' in query_lower or 'business license' in query_lower or ('how' in query_lower and 'business' in query_lower):
        for result in results:
            service_name_lower = result['service'].lower()
            if 'business permit new and renewal' in service_name_lower or 'one-stop shop business' in service_name_lower:
                result['score'] = result['score'] * 0.05
            elif 'business registration' in service_name_lower:
                result['score'] = result['score'] * 0.1
            elif 'inspection' in service_name_lower or 'building permit' in service_name_lower or 'electrical' in service_name_lower or 'tricycle' in service_name_lower:
                result['score'] = result['score'] * 5.0
        results.sort(key=lambda x: x['score'])
        return results
    
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
    """Check if user input is valid and meaningful"""
    import re
    
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
    """Parse and calculate total processing time from string"""
    if not time_string:
        return "Not specified"
    
    import re
    
    total_minutes = 0
    total_hours = 0
    total_days = 0
    
    minutes = re.findall(r'(\d+)\s*(?:minute|min)', time_string, re.IGNORECASE)
    hours = re.findall(r'(\d+)\s*(?:hour|hr)', time_string, re.IGNORECASE)
    days = re.findall(r'(\d+)\s*(?:day)', time_string, re.IGNORECASE)
    
    total_minutes = sum(int(m) for m in minutes)
    total_hours = sum(int(h) for h in hours)
    total_days = sum(int(d) for d in days)
    
    total_minutes += total_hours * 60
    total_minutes += total_days * 1440
    
    if total_minutes == 0:
        return time_string
    
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
    
    amounts = re.findall(r'PHP\s*([\d,]+\.?\d*)', fees_string, re.IGNORECASE)
    
    if not amounts:
        return fees_string
    
    total = sum(float(amt.replace(',', '')) for amt in amounts)
    
    return f"PHP {total:,.2f}"

def generate_llm_response(query, context_info):
    """Generate natural response using TinyLlama for ANY query"""
    prompt = f"""<|system|>You are a helpful assistant for Sampaloc, Quezon municipal services.<|user|>
{context_info}

Question: {query}

Answer in 2-3 sentences using the information above.<|assistant|>"""
    
    inputs = tokenizer(prompt, return_tensors="pt", truncation=True, max_length=512)
    
    with torch.no_grad():
        outputs = llm.generate(
            **inputs,
            max_new_tokens=100,
            temperature=0.3,
            do_sample=True,
            top_p=0.85,
            repetition_penalty=1.2,
            pad_token_id=tokenizer.eos_token_id
        )
    
    response = tokenizer.decode(outputs[0], skip_special_tokens=True)
    
    # Extract only the assistant's response
    if "<|assistant|>" in response:
        response = response.split("<|assistant|>")[-1].strip()
    
    # Remove any remaining prompt text
    if "Question:" in response:
        response = response.split("Question:")[0].strip()
    if "User question:" in response:
        response = response.split("User question:")[0].strip()
    
    return response

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/chat', methods=['POST'])
def chat():
    try:
        user_input = request.json.get('message', '').strip()
        if not user_input:
            return jsonify({'error': 'No message provided'}), 400
        
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
        
        # Greetings - pass through TinyLlama
        greeting_keywords = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 
                            'can you help', 'help me', 'i need help', 'assist me']
        
        if any(keyword in query_lower for keyword in greeting_keywords) and len(user_input.split()) <= 6:
            context_info = "The user is greeting you. Respond warmly and briefly explain you can help with municipal services, requirements, procedures, and office information in Sampaloc, Quezon."
            response = generate_llm_response(user_input, context_info)
            
            follow_ups = [
                "What are all the offices?",
                "Show me all services",
                "How do I get a birth certificate?"
            ]
            return jsonify({'response': response, 'follow_up_questions': follow_ups, 'status': 'success'})
        
        # List all offices - pass through TinyLlama
        if 'all' in query_lower and 'office' in query_lower:
            offices = sorted(set(service['office'] for service in charter_data['services']))
            offices_list = "\n".join([f"{i}. {office}" for i, office in enumerate(offices, 1)])
            context_info = f"The municipality has {len(offices)} offices:\n{offices_list}\n\nProvide a friendly response listing these offices."
            response = generate_llm_response(user_input, context_info)
            
            follow_ups = [
                "What services does Municipal Health Office offer?",
                "What services does Municipal Agriculture Office offer?",
                "What services does Municipal Treasurer's Office offer?"
            ]
            return jsonify({'response': response, 'follow_up_questions': follow_ups, 'status': 'success'})
        
        # List all services - pass through TinyLlama
        if 'all' in query_lower and 'service' in query_lower:
            context_info = f"The municipality offers {charter_data['total_services']} services across various offices. Provide a friendly response mentioning this and suggest they ask about specific services."
            response = generate_llm_response(user_input, context_info)
            
            follow_ups = [
                "Tell me about business permit",
                "How do I get a birth certificate?",
                "What are the health services available?"
            ]
            return jsonify({'response': response, 'follow_up_questions': follow_ups, 'status': 'success'})
        
        # Search for specific service
        results = search_knowledge(user_input, top_k=3)
        results = refine_search_with_keywords(user_input, results)
        
        if not results or results[0]['score'] > 100:
            context_info = "The user's question is unclear. Politely ask them to rephrase and provide examples like 'How do I get a birth certificate?' or 'What are the requirements for business permit?'"
            response = generate_llm_response(user_input, context_info)
            
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
        
        # Found service - pass ALL details through TinyLlama
        service = results[0]['metadata']
        service_name = service['service_name']
        
        # Build comprehensive context with explicit instructions
        context = f"""Service: {service['service_name']}
Office: {service['office']}"""
        
        if service.get('requirements'):
            context += "\n\nRequired Documents:"
            for i, req in enumerate(service['requirements'][:5], 1):
                req_text = req.get('document', '')
                if req_text:
                    context += f"\n{i}. {req_text[:120]}"
        
        if service.get('fees_text'):
            calculated_fees = calculate_total_fees(service['fees_text'])
            context += f"\n\nFees: {calculated_fees}"
        
        if service.get('time_text'):
            calculated_time = calculate_total_time(service['time_text'])
            context += f"\nProcessing Time: {calculated_time}"
        
        # Generate response through TinyLlama
        response = generate_llm_response(user_input, context)
        
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
        print(f"Error: {str(e)}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'response': 'Sorry, an error occurred. Please try again.',
            'error': str(e),
            'status': 'error'
        }), 500

if __name__ == '__main__':
    app.run(debug=True, port=5000)
