## How to Track User in Chatbot

### Method 1: Send in Chat Message (Preferred)
```javascript
// From frontend when user sends a message
fetch('http://localhost:5001/chat', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    message: "How do I register a new employee?",
    user_id: 1  // ✅ Send user_id here
  })
})
```

### Method 2: Pre-authenticate User
```javascript
// Call this once when user logs in
fetch('http://localhost:5001/set-user', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    user_id: 1  // From your Laravel auth
  })
})
// Then all future /chat calls will include this user_id automatically
```

### Method 3: Send in Header
```javascript
// Alternative: Send user_id in custom header
fetch('http://localhost:5001/chat', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-User-ID': '1'  // ✅ User ID in header
  },
  body: JSON.stringify({
    message: "How do I register a new employee?"
  })
})
```

### Method 4: Clear User (Go Anonymous)
```javascript
// Call this when user logs out
fetch('http://localhost:5001/clear-user', {
  method: 'POST'
})
```

---

## Testing

### Check Saved Chat History
```bash
# View latest chats with user_id
curl "http://localhost:5001/debug/chat-history?limit=10"

# View specific user's chats
curl "http://localhost:5001/admin/user-conversations/1"

# View all stats
curl "http://localhost:5001/admin/chat-stats"
```

### Test with curl
```bash
# Test 1: Send message with user_id in JSON
curl -X POST http://localhost:5001/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "How do I register?", "user_id": 1}'

# Test 2: Send message with X-User-ID header
curl -X POST http://localhost:5001/chat \
  -H "Content-Type: application/json" \
  -H "X-User-ID: 1" \
  -d '{"message": "How do I register?"}'
```

---

## Example Response
```json
{
  "response": "Here's how to register a new employee...",
  "question_type": "system",
  "follow_up_questions": [...],
  "status": "success"
}
```

## What Gets Saved
When you send a message, this gets saved to `chat_history` table:
- ✅ `user_id` - Your user ID (or NULL if anonymous)
- ✅ `session_id` - Unique session identifier
- ✅ `question` - What the user asked
- ✅ `response` - The bot's answer
- ✅ `question_type` - 'system', 'database', 'greeting', 'error'
- ✅ `follow_up_questions` - Suggested next questions (JSON)
- ✅ `codebase_files_used` - Which code files were referenced (JSON)
- ✅ `created_at` - Timestamp

---

## Integration with Laravel

If you're using Laravel for authentication, do this when a user logs in:

```javascript
// After successful Laravel login
const userId = {{ auth()->id() }};  // Get from Laravel Blade

fetch('http://localhost:5001/set-user', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({ user_id: userId })
})
.then(res => res.json())
.then(data => console.log('✅ User set:', data))
```

Then all subsequent messages will automatically include the user_id!
