"""
Test script to verify policy response functionality
"""
from groq import Groq

# Initialize Groq client
groq_client = Groq(api_key="***REMOVED-GROQ-KEY***")

# Test policy question
user_input = "How is late deduction calculated?"

policy_prompt = f"""You are an HR assistant for Prime HRIS Magdalena. Answer this HR policy question based on the system rules below:

=== SYSTEM RULES ===
1. Late Deduction: YES, late minutes are automatically deducted from VL (Vacation Leave) first, then SL (Sick Leave). 480 minutes = 1 work day.
2. Grace Period: 5 minutes for both AM In and PM In
3. Working Hours: 8:00-12:00 (AM), 13:00-17:00 (PM) = 8 hours total per day
4. Weekends: Saturday and Sunday are non-working days
5. Leave Types: VL, SL (accrued, cumulative, monetizable), SPL (3 days), ML (105 days), PL (7 days), VAWC (10 days), Solo Parent (7 days)
6. LWOP (Leave Without Pay): Applied when late time exceeds available leave credits
7. Accredited Hours: Actual work time minus late/undertime, with 5-minute grace period applied

User Question: {user_input}

Provide a clear, friendly answer in 2-4 sentences. Match the user's language (Tagalog or English)."""

try:
    print("Testing policy response...")
    print(f"Question: {user_input}\n")
    
    policy_response = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": policy_prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.7,
        max_tokens=300
    )
    
    response_text = policy_response.choices[0].message.content.strip()
    print(f"Response: {response_text}\n")
    print("✓ Test successful!")
    
except Exception as e:
    print(f"✗ Error: {str(e)}")
    import traceback
    traceback.print_exc()
