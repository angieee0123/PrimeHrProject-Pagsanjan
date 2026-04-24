import requests
import json

BASE_URL = "http://localhost:5001"

test_queries = [
    # Employee Queries
    "Show me all employees",
    "Who is John Randolf?",
    "Find employee with ID 1000",
    "What is the email of John Randolf?",
    "List all employees",
    "How many employees do we have?",
    "Show me employee 1000",
    
    # Department Queries
    "What departments do we have?",
    "List all departments",
    "Show me IT department employees",
    
    # Position/Role Queries
    "Who are the managers?",
    "Show me all developers",
    "List employees by position",
    
    # Contact Information
    "What is John's phone number?",
    "Show me contact details for employee 1000",
    "Get me the mobile number of John Randolf",
    "What is the email address of employee 1000?",
    
    # Address Queries
    "Where does John Randolf live?",
    "Show me addresses of all employees",
    "What is the address of employee 1000?",
    
    # Education Queries
    "What is John's educational background?",
    "Show me employees with college degrees",
    "List all education records",
    "Who graduated from LSPU?",
    
    # Training Queries
    "Show me training records",
    "What trainings did John attend?",
    "List all seminars",
    "Show me recent trainings",
    
    # Work Experience
    "Show me work experience of John Randolf",
    "What is John's previous employment?",
    "List all work experiences",
    
    # Employment Status
    "Show me active employees",
    "List all permanent employees",
    "Who are the contractual employees?",
    
    # Date-based Queries
    "Show me recent hires",
    "Who was hired in 2026?",
    "List employees by hire date",
    
    # Personal Information
    "What is John's birth date?",
    "Show me male employees",
    "List married employees",
    "What is the civil status of employee 1000?",
]

def test_chatbot():
    print("=" * 80)
    print("CHATBOT DATABASE TEST")
    print("=" * 80)
    print(f"\nTesting {len(test_queries)} queries...\n")
    
    successful = 0
    failed = 0
    
    for i, query in enumerate(test_queries, 1):
        try:
            response = requests.post(
                f"{BASE_URL}/chat",
                json={"message": query},
                timeout=10
            )
            
            if response.status_code == 200:
                data = response.json()
                status = data.get('status', 'unknown')
                response_text = data.get('response', '')[:100]
                
                print(f"[{i:2d}] ✓ Query: {query}")
                print(f"     Status: {status}")
                print(f"     Response: {response_text}...")
                print()
                successful += 1
            else:
                print(f"[{i:2d}] ✗ Query: {query}")
                print(f"     Error: HTTP {response.status_code}")
                print()
                failed += 1
        except Exception as e:
            print(f"[{i:2d}] ✗ Query: {query}")
            print(f"     Error: {str(e)}")
            print()
            failed += 1
    
    print("=" * 80)
    print(f"RESULTS: {successful} successful, {failed} failed out of {len(test_queries)} queries")
    print("=" * 80)

if __name__ == "__main__":
    print("\nMake sure the Flask server is running on http://localhost:5001")
    print("Start it with: python admin_chatbot.py\n")
    input("Press Enter to start testing...")
    test_chatbot()
