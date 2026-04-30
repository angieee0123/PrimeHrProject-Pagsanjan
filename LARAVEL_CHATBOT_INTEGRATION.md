# Laravel Session Integration for Chatbot

## 🎯 Overview

The chatbot now **automatically fetches the user_id from Laravel session** when a user logs in and accesses the chatbot page.

### How It Works:

```
User Login → Laravel Session Created → User visits /admin/chatbot
    ↓
JavaScript fetches /api/auth/user-id → Gets user_id, email, name
    ↓
Chatbot pre-fills user_id → All messages sent with user_id
    ↓
Chat History saved with user_id to database ✅
```

---

## 📋 Setup

### 1. **Laravel Routes Added** ✅

Two new routes have been added to `/routes/web.php`:

```php
// Get current authenticated user's ID
Route::get('/api/auth/user-id', function () {
    if (Auth::check()) {
        return response()->json([
            'status' => 'success',
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'name' => Auth::user()->employee->first_name . ' ' . Auth::user()->employee->last_name
        ]);
    }
    return response()->json(['status' => 'unauthenticated'], 401);
});

// Display chatbot page
Route::get('/admin/chatbot', function () {
    return view('admin.chatbot');
})->middleware('auth')->name('admin.chatbot');
```

### 2. **Chatbot Blade View** ✅

Created: `/resources/views/admin/chatbot.blade.php`

This file contains a complete HTML/JavaScript interface that:
- Automatically fetches user_id on page load
- Displays authenticated user info in header
- Sends all messages WITH user_id
- Has demo user buttons for testing

---

## 🚀 Usage

### For Authenticated Users (Recommended)

1. **User logs into Laravel:**
   ```
   http://localhost:8000/login
   email: admin@gmail.com
   password: [your password]
   ```

2. **Access the chatbot:**
   ```
   http://localhost:8000/admin/chatbot
   ```

3. **Automatically:**
   - ✅ User ID is fetched from session
   - ✅ User name displays in header
   - ✅ All messages saved with user_id

### For Testing (Demo Mode)

- If not logged in, click demo buttons:
  - "Maria (ID: 1)"
  - "Juan (ID: 6)"
  - "Ana (ID: 8)"

---

## 📝 Code Flow (Frontend)

```javascript
// 1. On page load
async function init() {
    const response = await fetch(
        'http://localhost:8000/api/auth/user-id',
        { credentials: 'include' }  // Include session cookies
    );
    const data = await response.json();
    
    if (data.status === 'success') {
        currentUser = {
            id: data.user_id,
            email: data.email,
            name: data.name
        };
    }
}

// 2. When sending message
async function sendMessage() {
    const response = await fetch('http://localhost:5001/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            message: userMessage,
            user_id: currentUser.id  // ✅ FROM LARAVEL
        })
    });
}
```

---

## ✅ Database Verification

After chatting as an authenticated user, verify the data:

```bash
# Get all chats
curl "http://localhost:5001/admin/chat-history"

# Get specific user's chats
curl "http://localhost:5001/admin/user-conversations/1"

# View statistics
curl "http://localhost:5001/admin/chat-stats"
```

Expected result:
```json
{
  "id": 10,
  "user_id": 1,
  "session_id": "1777574209.690193",
  "question": "How do I register a new employee?",
  "response": "To register...",
  "question_type": "system",
  "created_at": "2026-05-01 02:36:50"
}
```

---

## 🔄 Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│              LARAVEL USER LOGIN                              │
│  Email: admin@gmail.com → Password → Session Created         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│              USER VISITS /admin/chatbot                      │
│  http://localhost:8000/admin/chatbot                         │
│  (requires authentication)                                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    PAGE LOADS - FETCH USER_ID FROM LARAVEL API               │
│  GET /api/auth/user-id                                       │
│  Headers: { credentials: 'include' }                         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│       RESPONSE WITH USER DATA                                │
│  {                                                            │
│    "status": "success",                                       │
│    "user_id": 1,                                              │
│    "email": "admin@gmail.com",                                │
│    "name": "Admin User"                                       │
│  }                                                            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│     CHATBOT INTERFACE READY                                  │
│  ✅ User ID pre-filled (currentUser.id = 1)                  │
│  ✅ User name in header                                      │
│  ✅ Chat input enabled                                       │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│        USER SENDS MESSAGE                                    │
│  "How do I register a new employee?"                         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    SEND TO CHATBOT WITH USER_ID                              │
│  POST /chat                                                  │
│  {                                                            │
│    "message": "How do I register...",                         │
│    "user_id": 1  ← FROM LARAVEL SESSION                      │
│  }                                                            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│        CHATBOT PROCESSES & SAVES                             │
│  save_chat_history(                                          │
│    user_id=1,      ← FROM REQUEST                            │
│    message="...",                                             │
│    response="..."                                             │
│  )                                                            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│      SAVED TO DATABASE                                       │
│  chat_history table:                                         │
│  ✅ user_id = 1 (NOT NULL!)                                  │
│  ✅ question = "How do I register..."                        │
│  ✅ response = "To register..."                              │
│  ✅ created_at = NOW()                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Troubleshooting

### Issue: "Not Authenticated" message

**Cause:** User is not logged in to Laravel

**Solution:**
1. Go to http://localhost:8000/login
2. Log in with credentials
3. Navigate to /admin/chatbot

### Issue: Can't connect to chatbot

**Cause:** Chatbot server not running

**Solution:**
```powershell
.\env\Scripts\python "GOVERNMENT CHATBOT\4. web application\chatbot_unified.py"
```

### Issue: CORS error in browser console

**Cause:** Chatbot and Laravel on different ports

**Solution:** Already configured with CORS headers in chatbot_unified.py

---

## 🎯 What You Get

✅ **Automatic User Tracking** - No manual user selection needed  
✅ **Laravel Integration** - Uses existing authentication  
✅ **Persistent History** - Every chat tied to user_id  
✅ **Admin Reporting** - Can view any user's conversations  
✅ **Zero Configuration** - Just visit the page!  

---

## 📊 Admin Analytics

Access these endpoints to see user chat activity:

```bash
# Get all chats with user_id
curl "http://localhost:5001/admin/chat-history?user_id=1&limit=50"

# Get specific user's conversations
curl "http://localhost:5001/admin/user-conversations/1"

# Get chat statistics
curl "http://localhost:5001/admin/chat-stats"
```

Sample response:
```json
{
  "total": 42,
  "today": 8,
  "by_question_type": [
    { "question_type": "system", "count": 25 },
    { "question_type": "database", "count": 15 },
    { "question_type": "greeting", "count": 2 }
  ],
  "top_users": [
    { "user_id": 1, "count": 12 },
    { "user_id": 6, "count": 8 },
    { "user_id": 8, "count": 5 }
  ]
}
```

---

## 🔐 Security

- ✅ Session-based authentication (Laravel handles it)
- ✅ Credentials included in API calls (session cookies)
- ✅ CORS configured for same-origin
- ✅ 401 response if not authenticated
- ✅ User can only access after login

