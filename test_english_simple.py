import requests
import json

API_URL = "http://localhost:5001/chat"

print("=" * 70)
print("Testing English Question")
print("=" * 70)

question = "How many employees are there?"
print(f"Question: {question}")
print("-" * 70)

try:
    response = requests.post(
        API_URL,
        json={"message": question, "user_id": 1},
        timeout=30
    )
    
    print(f"Status Code: {response.status_code}")
    data = response.json()
    print(f"Response: {json.dumps(data, indent=2, ensure_ascii=False)}")
    
except Exception as e:
    print(f"Error: {e}")
    import traceback
    traceback.print_exc()
