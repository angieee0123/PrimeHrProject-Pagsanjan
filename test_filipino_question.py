import requests
import json

# Test the chatbot with Filipino question
API_URL = "http://localhost:5001/chat"

# Filipino question: "How many employees are registered in the system?"
question = "ilan ang empleyado na registerdito sa sytem?"

print("=" * 60)
print("Testing Chatbot with Filipino Question")
print("=" * 60)
print(f"Question: {question}")
print(f"Translation: How many employees are registered in the system?")
print("-" * 60)

try:
    response = requests.post(
        API_URL,
        json={
            "message": question,
            "user_id": 1
        },
        timeout=30
    )
    
    data = response.json()
    
    print(f"Status: {response.status_code}")
    print(f"Response Status: {data.get('status')}")
    print(f"Question Type: {data.get('question_type')}")
    print()
    print("CHATBOT RESPONSE:")
    print("-" * 60)
    print(data.get('response'))
    print("-" * 60)
    print()
    print("Follow-up Questions:")
    for i, fq in enumerate(data.get('follow_up_questions', []), 1):
        print(f"  {i}. {fq}")
    print()
    print("Full Response JSON:")
    print(json.dumps(data, indent=2, ensure_ascii=False))
    
except Exception as e:
    print(f"Error: {e}")
    import traceback
    traceback.print_exc()
