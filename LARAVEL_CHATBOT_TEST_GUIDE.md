# Laravel Chatbot Integration - Test Guide

## ✅ Prerequisites

Before testing, ensure:
1. ✅ MySQL database running with `primehrismagdalena` database
2. ✅ Laravel server ready to start
3. ✅ Chatbot server ready to start
4. ✅ User already exists in `users` table (use admin@gmail.com if available)

---

## 🚀 Quick Start (3 Steps)

### Step 1: Start Chatbot Server

```powershell
# Open PowerShell in project folder
cd "f:\PrimeHrProject-Magdalena"

# Activate virtual environment
.\env\Scripts\Activate

# Start chatbot
python "GOVERNMENT CHATBOT\4. web application\chatbot_unified.py"
```

Expected output:
```
 * Running on http://127.0.0.1:5001
```

**✅ Leave this running in the terminal**

---

### Step 2: Start Laravel Server

```powershell
# Open NEW PowerShell window
cd "f:\PrimeHrProject-Magdalena\primeHrMagdalenaLaravel"

# Start Laravel
php artisan serve

# Or if you have .bat file
.\start-all.bat
```

Expected output:
```
Laravel development server started on [http://127.0.0.1:8000]
```

**✅ Leave this running**

---

### Step 3: Test the Chatbot

1. **Open browser:**
   - URL: http://localhost:8000/login

2. **Log in:**
   - Email: `admin@gmail.com` (or valid user email)
   - Password: [your password]

3. **Go to chatbot:**
   - URL: http://localhost:8000/admin/chatbot

4. **Verify authentication:**
   - ✅ Header should show: "Logged in as [Admin User]"
   - ✅ Status shows: "✅ Authenticated (ID: 1)"

5. **Send a message:**
   - Type: "How do I register a new employee?"
   - Click: Send
   - Wait for response

6. **Verify data saved:**
   - Should show: "✅ Saved (User: Admin User, ID: 1)"

---

## 📊 Verify Data in Database

### Option A: MySQL Command Line

```bash
# Connect to MySQL
mysql -u root -p primehrismagdalena

# View latest chat
SELECT id, user_id, question, response, created_at 
FROM chat_history 
ORDER BY created_at DESC 
LIMIT 1;
```

Expected result:
```
id  | user_id | question                           | response | created_at
----|---------|-----------------------------------|----------|--------------------
10  |    1    | How do I register a new employee? | To ...   | 2026-05-01 14:35:22
```

**✅ user_id should NOT be NULL**

### Option B: Admin Endpoints

```bash
# Get all chats
curl "http://localhost:5001/admin/chat-history"

# Get specific user's chats
curl "http://localhost:5001/admin/user-conversations/1"

# Get stats
curl "http://localhost:5001/admin/chat-stats"
```

---

## 🔍 Troubleshooting

### Problem: "Connecting to your Laravel session..."

**Cause:** /api/auth/user-id endpoint not responding

**Solution:**
1. Check Laravel is running: http://localhost:8000 (should show Laravel page)
2. Check endpoint manually: http://localhost:8000/api/auth/user-id
3. Should show: `{"status":"unauthenticated"}` if not logged in

### Problem: "Not Authenticated (Demo Mode)"

**Cause:** User is logged out or Laravel session not detected

**Solution:**
1. Go to http://localhost:8000/login
2. Log in with valid credentials
3. Refresh the chatbot page (F5)
4. Should now show: "Authenticated (ID: X)"

### Problem: "Cannot connect to chatbot"

**Cause:** Chatbot server not running on port 5001

**Solution:**
1. Check terminal running Python script
2. Should see: `Running on http://127.0.0.1:5001`
3. Test manually: http://localhost:5001 (should return JSON error, not "connection refused")
4. Restart: Stop script, run again

### Problem: CORS error in browser console

**Cause:** Chatbot CORS not configured for new port

**Solution:**
1. Check chatbot_unified.py line with CORS setup
2. Should include `localhost:8000` in allowed origins
3. Already configured, but if custom port, edit and restart

### Problem: Chat saved but user_id is NULL

**Cause:** Frontend not sending user_id in request

**Solution:**
1. Open browser DevTools (F12)
2. Go to Network tab
3. Send a chat message
4. Click on POST request to `localhost:5001/chat`
5. Check Request body - should include `"user_id": 1`
6. If missing, check currentUser.id is set (see Console tab)

---

## ✅ Success Checklist

After completing the test:

- [ ] Laravel shows "Logged in as [User Name]" in chatbot header
- [ ] Authentication status shows green: "✅ Authenticated"
- [ ] Chat message sends successfully
- [ ] Response received from chatbot
- [ ] Status shows: "✅ Saved (User: X, ID: X)"
- [ ] Database has new record with user_id (not NULL)
- [ ] Multiple chats can be sent and all saved with user_id

---

## 📝 Sample Queries to Test

Try these questions in the chatbot:

1. **Database query (SQL):**
   - "How many employees do we have?"
   - "Show me all employees in the IT department"
   - "List employees hired in 2024"

2. **System process question:**
   - "How do I register a new employee?"
   - "What's the attendance process?"
   - "How do I create a user account?"

3. **General greeting:**
   - "Hello"
   - "What can you do?"
   - "Help"

---

## 🎯 What Data Is Saved

Each chat message saves:

```json
{
  "id": 10,
  "user_id": 1,
  "session_id": "abc123...",
  "question": "How do I register a new employee?",
  "response": "To register a new employee...",
  "question_type": "system",
  "follow_up_questions": ["How do I...", "What about..."],
  "codebase_files_used": ["employee-model.php", "employee-controller.php"],
  "created_at": "2026-05-01 14:35:22",
  "updated_at": "2026-05-01 14:35:22"
}
```

---

## 🚀 Demo Mode (Optional)

If you want to test WITHOUT logging in:

1. Don't log in to Laravel
2. Go to http://localhost:8000/admin/chatbot (will show demo mode)
3. Click: "Maria (ID: 1)" or other demo user
4. Send a message
5. Data saved with demo user_id

(This is for testing only - production should require login)

---

## 📞 Still Having Issues?

Check these files for configuration:

1. **Chatbot Settings:**
   - File: `GOVERNMENT CHATBOT\4. web application\chatbot_unified.py`
   - Check: Line with `app = Flask(__name__)`
   - Check: CORS allowed origins

2. **Laravel Configuration:**
   - File: `primeHrMagdalenaLaravel\.env`
   - Check: `APP_URL=http://localhost:8000`
   - Check: Database connection settings

3. **Frontend:**
   - File: `primeHrMagdalenaLaravel\resources\views\admin\chatbot.blade.php`
   - Check: Lines with `chatAPI` and `laravelAPI` variable definitions

