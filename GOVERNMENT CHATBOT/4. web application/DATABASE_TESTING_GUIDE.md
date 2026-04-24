# Chatbot Database Testing Guide

## Overview
The updated `admin_chatbot.py` now searches across ALL database tables using semantic similarity instead of FAISS index.

## Database Tables Supported
1. **employees** - Employee records
2. **addresses** - Address information
3. **contacts** - Contact details
4. **departments** - Department information
5. **documents** - Document records
6. **educations** - Education history
7. **eligibilities** - Eligibility records
8. **employment_details** - Employment information
9. **family_members** - Family member records
10. **government_ids** - Government ID information
11. **legal_requirements** - Legal requirement records
12. **trainings** - Training records
13. **work_experiences** - Work experience history

## How to Test

### Step 1: Start the Flask Server
```bash
cd "c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\GOVERNMENT CHATBOT\4. web application"
python admin_chatbot.py
```

### Step 2: Run the Test Script
```bash
python test_chatbot_db.py
```

## Sample Questions by Table

### Employees
- "Show me all employees"
- "Who is John Randolf?"
- "employees"
- "Find employee with ID 1000"
- "What employees are in the system?"

### Addresses
- "What are the addresses in the database?"
- "Show me all addresses"
- "addresses"
- "What address information is available?"

### Contacts
- "Show me contact information"
- "contacts"
- "What contact details are available?"
- "List all contacts"

### Departments
- "What departments exist?"
- "Show all departments"
- "departments"
- "What department information is available?"

### Documents
- "What documents are in the system?"
- "Show me documents"
- "documents"
- "List all documents"

### Educations
- "Show education records"
- "educations"
- "What education data is available?"
- "List all education records"

### Eligibilities
- "Show eligibility information"
- "eligibilities"
- "What eligibilities are recorded?"
- "List all eligibilities"

### Employment Details
- "Show employment details"
- "employment details"
- "What employment information is available?"
- "List all employment details"

### Family Members
- "Show family members"
- "family members"
- "What family data is available?"
- "List all family members"

### Government IDs
- "Show government IDs"
- "government ids"
- "What government ID information is available?"
- "List all government IDs"

### Legal Requirements
- "Show legal requirements"
- "legal requirements"
- "What legal requirements are recorded?"
- "List all legal requirements"

### Trainings
- "Show training records"
- "trainings"
- "What training data is available?"
- "List all trainings"

### Work Experiences
- "Show work experiences"
- "work experiences"
- "What work experience data is available?"
- "List all work experiences"

## How It Works

1. **Query Processing**: User input is converted to embeddings using SentenceTransformer
2. **Multi-Table Search**: The chatbot searches across all 13 database tables
3. **Semantic Similarity**: Results are ranked by cosine similarity score
4. **Response Generation**: Top results are formatted and returned to user

## Expected Response Format

```json
{
  "response": "Summary of findings...",
  "full_response": "Detailed information...",
  "has_details": true,
  "follow_up_questions": ["Question 1", "Question 2"],
  "status": "success"
}
```

## Troubleshooting

### Issue: "Database search error"
- Check MySQL connection: `host=127.0.0.1, user=root, password=admin, database=primehrismagdalena`
- Verify all tables exist in the database
- Check database credentials in `admin_chatbot.py`

### Issue: Low similarity scores
- Try more specific queries
- Use table names in your question (e.g., "employees", "addresses")
- Ask about specific fields (e.g., "names", "emails", "dates")

### Issue: No results found
- Ensure database has data in the queried tables
- Check if the query is too specific
- Try simpler, more general queries

## Performance Notes

- Each query searches up to 50 records per table
- Results are cached for repeated queries
- Semantic search is slower than keyword search but more accurate
- First query may take 2-3 seconds due to model loading

## Next Steps

1. Test with various queries
2. Monitor response quality
3. Adjust `top_k` parameter if needed (currently set to 3)
4. Add more specific table queries as needed
