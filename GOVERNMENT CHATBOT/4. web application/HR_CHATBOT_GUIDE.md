# HR Admin Chatbot - Testing Guide

## Overview
The admin chatbot is now an **HR Assistant** that allows HR managers to query employee data naturally without scrolling through database tables.

## Key Features
✅ Natural language queries for employee data  
✅ Searches across all HR-related tables  
✅ Semantic search with AI-powered understanding  
✅ Professional HR-focused responses  
✅ Detailed employee information display  

## How to Use

### Start the Server
```bash
cd "c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\GOVERNMENT CHATBOT\4. web application"
python admin_chatbot.py
```

### Access the Chatbot
- Regular Interface: http://localhost:5001/
- Admin Interface: http://localhost:5001/admin

## Sample HR Questions

### 👤 Employee Information
```
"Show me all employees"
"Who is John Randolf?"
"Find employee with ID 1000"
"What is the email of John Randolf?"
"List all employees"
"How many employees do we have?"
```

### 🏢 Department Queries
```
"What departments do we have?"
"List all departments"
"Show me IT department employees"
"Who works in the HR department?"
```

### 💼 Position/Role Queries
```
"Who are the managers?"
"Show me all developers"
"List employees by position"
"What is John's position?"
```

### 📞 Contact Information
```
"What is John's phone number?"
"Show me contact details for employee 1000"
"Get me the mobile number of John Randolf"
"What is the email address of employee 1000?"
```

### 🏠 Address Queries
```
"Where does John Randolf live?"
"Show me addresses of all employees"
"What is the address of employee 1000?"
"Who lives in Pagsanjan?"
```

### 🎓 Education Queries
```
"What is John's educational background?"
"Show me employees with college degrees"
"List all education records"
"Who graduated from LSPU?"
"Show me employees with master's degrees"
```

### 📚 Training Queries
```
"Show me training records"
"What trainings did John attend?"
"List all seminars"
"Show me recent trainings"
"Who attended leadership training?"
```

### 💼 Work Experience
```
"Show me work experience of John Randolf"
"What is John's previous employment?"
"List all work experiences"
"Who worked at Google before?"
```

### 📊 Employment Status
```
"Show me active employees"
"List all permanent employees"
"Who are the contractual employees?"
"Show me probationary employees"
```

### 📅 Date-based Queries
```
"Show me recent hires"
"Who was hired in 2026?"
"List employees by hire date"
"Show me employees hired this year"
```

### 👥 Personal Information
```
"What is John's birth date?"
"Show me male employees"
"List married employees"
"What is the civil status of employee 1000?"
"How old is John Randolf?"
```

## Response Format

The chatbot provides two types of responses:

### Short Response (Summary)
Quick AI-generated answer with key information

### Full Response (Detailed)
Complete employee data with:
- 👤 Employee ID
- 💼 Position
- 🏢 Department
- 📧 Email
- 📊 Employment Status
- 📅 Date Hired
- 🎂 Birth Date
- ⚧ Sex
- 💑 Civil Status

## How It Works

1. **Natural Language Processing**: Your question is converted to embeddings
2. **Semantic Search**: AI searches across all HR tables (employees, addresses, contacts, education, training, work experience)
3. **Smart Matching**: Results are ranked by relevance using cosine similarity
4. **AI Response**: Groq LLM generates professional HR-focused answers
5. **Detailed Display**: Full employee data is formatted and displayed

## Database Tables Searched

| Table | Data Type |
|-------|-----------|
| employees | Basic employee info |
| employment_details | Position, department, status |
| addresses | Home addresses |
| contacts | Phone numbers, emails |
| educations | Educational background |
| trainings | Training records |
| work_experiences | Previous employment |
| departments | Department information |

## Tips for Better Results

✅ **Be specific**: "Show me John Randolf's email" vs "email"  
✅ **Use names**: "Who is John Randolf?" vs "employee"  
✅ **Use IDs**: "Find employee 1000" for exact matches  
✅ **Ask naturally**: "What is John's phone number?" works great  
✅ **Combine queries**: "Show me John's education and training"  

## Testing Script

Run the automated test:
```bash
python test_chatbot_db.py
```

This tests 50+ HR queries automatically.

## Example Conversation

**HR Manager**: "Who is John Randolf?"

**Chatbot**: "John Randolf Peñaredondo is an employee in our system with Employee ID 1000. He can be reached at rodolfotacords@gmail.com."

**Details**:
- 👤 Employee ID: 1000
- 💼 Position: [Position from employment_details]
- 🏢 Department: [Department name]
- 📧 Email: rodolfotacords@gmail.com
- 📊 Status: Active
- 📅 Date Hired: [Hire date]
- 🎂 Birth Date: 1999-01-01
- ⚧ Sex: Male
- 💑 Civil Status: Single

---

**HR Manager**: "What is his educational background?"

**Chatbot**: "Here are the education records for John Randolf Peñaredondo..."

[Shows education details from educations table]

---

**HR Manager**: "Show me his training records"

**Chatbot**: "Here are the training records for John Randolf Peñaredondo..."

[Shows training details from trainings table]

## Troubleshooting

### No results found
- Check if employee exists in database
- Try using employee ID instead of name
- Verify database connection

### Slow responses
- First query loads AI model (2-3 seconds)
- Subsequent queries are faster (cached)
- Reduce LIMIT in search_database() if needed

### Incorrect results
- Be more specific in your query
- Use exact names or IDs
- Check for typos in employee names

## Next Steps

1. Test with your actual employee data
2. Try various question formats
3. Check response accuracy
4. Provide feedback for improvements

## Support

For issues or questions, check:
- Database connection: `host=127.0.0.1, user=root, password=admin`
- Server running: http://localhost:5001
- Database: primehrismagdalena
