import requests
import json

API_URL = "http://localhost:5001/chat"

test_cases = [
    ("ilan ang empleyado na registerdito sa sytem?", "Filipino"),
    ("How many employees are registered in the system?", "English"),
    ("How many employees are there?", "English (Simple)"),
]

print("=" * 70)
print("Comparing Different Language Versions")
print("=" * 70)

for question, language in test_cases:
    print()
    print(f"[{language}] Question: {question}")
    print("-" * 70)
    
    try:
        response = requests.post(
            API_URL,
            json={"message": question, "user_id": 1},
            timeout=30
        )
        
        data = response.json()
        question_type = data.get('question_type')
        response_text = data.get('response', '')[:150]  # First 150 chars
        
        print(f"Classification: {question_type}")
        print(f"Response Preview: {response_text}...")
        print()
        
    except Exception as e:
        print(f"Error: {e}")
        print()

print("=" * 70)
print("ANALYSIS:")
print("=" * 70)
print("""
The Filipino question is being classified as 'system' (how-to) instead of 
'database' (data query) because the keyword matching is only in English.

SOLUTION: We need to either:
1. Add Filipino keywords to the classify_question() function
2. Translate Filipino to English before classification
3. Use AI to better understand the question intent

Let's implement option 2: Add Filipino translation support
""")
