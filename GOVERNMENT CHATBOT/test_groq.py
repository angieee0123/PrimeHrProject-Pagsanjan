"""
Test script to verify Groq Llama 3.3 70B is working properly
"""
from groq import Groq
import time

# Initialize Groq client
groq_client = Groq(api_key="<YOUR_NEW_GROQ_API_KEY>")

print("=" * 60)
print("TESTING GROQ LLAMA 3.3 70B")
print("=" * 60)

# Test 1: Simple question
print("\n[TEST 1] Simple Government Service Question")
print("-" * 60)

service_info = """
Service: BUSINESS PERMIT NEW AND RENEWAL ONLINE APPLICATION
Office: Municipal Treasurer's Office
Description: Application for new and renewal of business permits
Requirements: DTI Registration, Barangay Clearance, Fire Safety Certificate
Fees: PHP 500.00
Processing time: 3 days
"""

prompt = f"""You are a helpful government service assistant for Sampaloc, Quezon. Answer the citizen's question in a friendly, conversational tone (2-3 sentences max).

Service Information:
{service_info}

Citizen's Question: How do I get a business permit?

Answer:"""

print("Sending to Groq...")
start_time = time.time()

try:
    chat_completion = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.7,
        max_tokens=200
    )
    
    response = chat_completion.choices[0].message.content
    elapsed = time.time() - start_time
    
    print(f"\n[SUCCESS] (took {elapsed:.2f} seconds)")
    print(f"\nGroq Response:")
    print(f"'{response}'")
    
except Exception as e:
    print(f"\n[ERROR]: {e}")

# Test 2: Different question
print("\n" + "=" * 60)
print("[TEST 2] Birth Certificate Question")
print("-" * 60)

service_info2 = """
Service: Issuance of Certificate of Live Birth
Office: Municipal Civil Registry Office
Description: Issuance of certified true copy of birth certificate
Requirements: Valid ID, Birth details (name, date, place)
Fees: PHP 150.00
Processing time: 30 minutes
"""

prompt2 = f"""You are a helpful government service assistant for Sampaloc, Quezon. Answer the citizen's question in a friendly, conversational tone (2-3 sentences max).

Service Information:
{service_info2}

Citizen's Question: What do I need to get my birth certificate?

Answer:"""

print("Sending to Groq...")
start_time = time.time()

try:
    chat_completion = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt2}],
        model="llama-3.3-70b-versatile",
        temperature=0.7,
        max_tokens=200
    )
    
    response = chat_completion.choices[0].message.content
    elapsed = time.time() - start_time
    
    print(f"\n[SUCCESS] (took {elapsed:.2f} seconds)")
    print(f"\nGroq Response:")
    print(f"'{response}'")
    
except Exception as e:
    print(f"\n[ERROR]: {e}")

print("\n" + "=" * 60)
print("TESTING COMPLETE")
print("=" * 60)
print("\nIf both tests show [SUCCESS], Groq is working properly!")
print("The responses should be natural, friendly, and conversational.")
print("\nCompare with old FLAN-T5 responses to see the quality difference.")
