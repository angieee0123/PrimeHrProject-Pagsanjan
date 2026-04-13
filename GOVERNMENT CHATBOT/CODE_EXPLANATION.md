# Government Chatbot - Complete Code Explanation

## 📋 Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture](#architecture)
3. [Models Used](#models-used)
4. [Models NOT Used](#models-not-used)
5. [How Each Component Works](#how-each-component-works)
6. [Code Flow](#code-flow)
7. [Frontend Explanation](#frontend-explanation)

---

## 🎯 Project Overview

This is a **Government Services Chatbot** for the Municipality of Sampaloc, Quezon. It helps citizens get information about 109 government services including:
- Birth/Death/Marriage certificates
- Business permits
- Health services
- Building permits
- Tax services
- And more...

**Technology Stack:**
- **Backend:** Flask (Python web framework)
- **Frontend:** HTML, CSS, JavaScript
- **AI/ML:** Multiple NLP models for understanding and responding
- **Database:** JSON files for service information

---

## 🏗️ Architecture

```
User Question
    ↓
[Input Validation] ← Checks if query is valid
    ↓
[Query Expansion] ← Adds synonyms (birth cert → birth certificate)
    ↓
[Semantic Search] ← Finds relevant service using embeddings
    ↓
[FAISS Index] ← Fast similarity search in vector database
    ↓
[Keyword Refinement] ← Improves results with keyword matching
    ↓
[Response Generation] ← Creates natural language response
    ↓
User sees answer
```

---

## 🤖 Models Used

### 1. **Sentence Transformer (all-MiniLM-L6-v2)** ✅ ACTIVELY USED
**Purpose:** Convert text into numerical vectors (embeddings)

**How it works:**
```python
embedder = SentenceTransformer('all-MiniLM-L6-v2')
query_embedding = embedder.encode("How do I get a birth certificate?")
# Output: [0.234, -0.567, 0.891, ...] (384 numbers)
```

**Why we use it:**
- Converts user questions into vectors
- Converts service descriptions into vectors
- Enables semantic search (finding similar meanings, not just keywords)

**Example:**
- "birth certificate" and "certificate of birth" have similar vectors
- Even if user says "birth cert", it finds the right service

**Where used:**
- `app.py` line 28: Loading the model
- `search_knowledge()` function: Creating embeddings for search
- Training script: Creating embeddings for all 109 services

---

### 2. **FAISS (Facebook AI Similarity Search)** ✅ ACTIVELY USED
**Purpose:** Fast vector similarity search

**How it works:**
```python
# Training: Store all service embeddings
faiss_index.add(service_embeddings)  # 109 services stored

# Runtime: Find similar services
distances, indices = faiss_index.search(query_embedding, top_k=3)
# Returns: 3 most similar services
```

**Why we use it:**
- Searches through 109 services in milliseconds
- Uses L2 distance (Euclidean distance) to find similar vectors
- Much faster than comparing text directly

**Where used:**
- `app.py` line 29: Loading the index
- `search_knowledge()` function: Searching for relevant services
- Training script: Building the index with all services

---

### 3. **FLAN-T5 (Google)** ⚠️ LOADED BUT MINIMALLY USED
**Purpose:** Generate natural language responses

**Model Details:**
- **Type:** Sequence-to-sequence language model
- **Size:** 80M parameters (flan-t5-small in app.py)
- **Training:** Pre-trained on instruction-following tasks

**How it works:**
```python
tokenizer = AutoTokenizer.from_pretrained("google/flan-t5-small")
llm = AutoModelForSeq2SeqLM.from_pretrained("google/flan-t5-small")

# Generate response
inputs = tokenizer(prompt, return_tensors="pt")
outputs = llm.generate(**inputs)
response = tokenizer.decode(outputs[0])
```

**Current Status in app.py:**
- ✅ Model is loaded (lines 34-36)
- ❌ NOT actually used for generation
- ❌ Replaced by template-based responses

**Why not used:**
- Template responses are faster
- More predictable output
- Sufficient for structured government data

**Where it COULD be used:**
- `generate_conversational_response()` function
- Currently uses string formatting instead

---

### 4. **Groq Llama 3.3 70B** ✅ USED IN app_improved.py
**Purpose:** Generate high-quality conversational responses

**Model Details:**
- **Type:** Large Language Model (LLM)
- **Size:** 70 billion parameters
- **Provider:** Groq (cloud API)
- **Speed:** 0.5-2 seconds per response

**How it works:**
```python
groq_client = Groq(api_key="your_api_key")

chat_completion = groq_client.chat.completions.create(
    messages=[{"role": "user", "content": prompt}],
    model="llama-3.3-70b-versatile",
    temperature=0.7,
    max_tokens=200
)
response = chat_completion.choices[0].message.content
```

**Why we use it (in app_improved.py):**
- Much better quality than FLAN-T5
- Natural, conversational responses
- No local GPU needed
- Fast cloud inference

**Example:**
```
Input: "How do I get a birth certificate?"

Groq Response: "To obtain a birth certificate in Sampaloc, Quezon, 
you'll need to visit the Municipal Civil Registry Office. The process 
is straightforward and typically takes about 20 minutes..."
```

**Where used:**
- `app_improved.py` only
- `generate_conversational_response()` function
- Generates the intro paragraph for responses

---

### 5. **TF-IDF + Logistic Regression** ⚠️ TRAINED BUT NOT USED
**Purpose:** Time waster detection (filter spam/gibberish)

**How it works:**
```python
# Training
tfidf = TfidfVectorizer()
X = tfidf.fit_transform(["hello", "test", "asdfgh"])  # Spam examples
y = [1, 1, 1]  # Label as time waster

classifier = LogisticRegression()
classifier.fit(X, y)

# Prediction
is_spam = classifier.predict(tfidf.transform(["How to get permit?"]))
```

**Current Status:**
- ✅ Model trained in training script
- ✅ Saved to `models/time_waster_*.pkl`
- ❌ NOT loaded in app.py
- ❌ Replaced by `is_valid_query()` function

**Why not used:**
- Simple rule-based validation is sufficient
- Checks vowel/consonant ratio, length, special characters
- Faster than loading ML model

---

### 6. **DistilBERT Sentiment Analysis** ⚠️ TRAINED BUT NOT USED
**Purpose:** Detect user sentiment (positive/negative)

**Model Details:**
- **Type:** Transformer-based classifier
- **Pre-trained:** distilbert-base-uncased-finetuned-sst-2-english
- **Output:** Positive or Negative sentiment

**How it works:**
```python
sentiment_tokenizer = AutoTokenizer.from_pretrained("distilbert-...")
sentiment_model = AutoModelForSequenceClassification.from_pretrained("distilbert-...")

inputs = sentiment_tokenizer("I'm frustrated with this service", return_tensors="pt")
outputs = sentiment_model(**inputs)
sentiment = "negative" if outputs.logits[0][0] > outputs.logits[0][1] else "positive"
```

**Current Status:**
- ✅ Model trained and saved
- ❌ NOT loaded in app.py
- ❌ NOT used in responses

**Why not used:**
- Not critical for government service chatbot
- All queries treated equally regardless of sentiment
- Could be added for analytics/monitoring

---

## ❌ Models NOT Used (But Available)

### 1. **FLAN-T5 Base (248M parameters)**
- **Trained:** Yes, saved in `models/flan_t5_model/`
- **Used:** No
- **Reason:** app.py uses flan-t5-small instead
- **Could use for:** Better quality responses (but slower)

### 2. **Sentiment Analysis Model**
- **Trained:** Yes, saved in `models/sentiment_model/`
- **Used:** No
- **Reason:** Not needed for current functionality
- **Could use for:** 
  - Prioritize frustrated users
  - Analytics dashboard
  - Improve service based on negative feedback

### 3. **Time Waster Classifier**
- **Trained:** Yes, saved in `models/time_waster_*.pkl`
- **Used:** No
- **Reason:** Rule-based validation is simpler
- **Could use for:** More sophisticated spam detection

### 4. **Custom Fine-tuned FLAN-T5**
- **Training Data:** 596 examples in `training_data.csv`
- **Trained:** No (only base model loaded)
- **Reason:** Base model + templates work well enough
- **Could use for:** Domain-specific responses

---

## 🔧 How Each Component Works

### 1. Input Validation (`is_valid_query()`)
```python
def is_valid_query(query):
    # Check 1: Too short?
    if len(query.strip()) < 3:
        return False, "Your message is too short..."
    
    # Check 2: Gibberish? (low vowel ratio)
    vowels = len(re.findall(r'[aeiouAEIOU]', query))
    consonants = len(re.findall(r'[bcdfghjklmnpqrstvwxyz...]', query))
    if vowels / consonants < 0.2:  # Less than 20% vowels
        return False, "I couldn't understand..."
    
    # Check 3: Too many special characters?
    special_chars = len(re.findall(r'[^a-zA-Z0-9\s]', query))
    if special_chars > len(query) * 0.5:
        return False, "Please use regular text..."
    
    return True, None
```

**Examples:**
- ✅ "How do I get a birth certificate?" → Valid
- ❌ "hi" → Too short
- ❌ "asdfghjkl" → Gibberish (low vowels)
- ❌ "!!!???###" → Too many special characters

---

### 2. Query Expansion (app_improved.py only)
```python
QUERY_SYNONYMS = {
    'birth certificate': ['birth cert', 'certificate of birth', 'birth record'],
    'business permit': ['business license', 'business registration'],
}

def expand_query(query):
    expanded = [query]
    for term, synonyms in QUERY_SYNONYMS.items():
        if term in query.lower():
            for syn in synonyms:
                expanded.append(query.replace(term, syn))
    return expanded[:3]
```

**Example:**
```
Input: "birth cert"
Expanded: ["birth cert", "birth certificate", "certificate of birth"]
→ Searches with all 3 variations
→ Better chance of finding the right service
```

---

### 3. Semantic Search (`search_knowledge()`)
```python
def search_knowledge(query, top_k=3):
    # Step 1: Convert query to vector
    query_embedding = embedder.encode([query])
    # Output: [0.234, -0.567, ...] (384 numbers)
    
    # Step 2: Search FAISS index
    distances, indices = faiss_index.search(query_embedding, top_k)
    # distances: [12.5, 18.3, 25.7] (lower = more similar)
    # indices: [45, 12, 89] (service IDs)
    
    # Step 3: Get service details
    results = []
    for idx, score in zip(indices[0], distances[0]):
        results.append({
            'service': knowledge_base[idx]['service_name'],
            'office': knowledge_base[idx]['office'],
            'score': float(score),
            'metadata': knowledge_base[idx]
        })
    
    return results
```

**Example:**
```
Query: "How do I get a birth certificate?"

Step 1: Embedding
[0.234, -0.567, 0.891, ...] (384 numbers)

Step 2: FAISS Search
Found 3 similar services:
1. "Issuance of Birth Certificate" (distance: 12.5) ← Best match
2. "Issuance of Death Certificate" (distance: 18.3)
3. "Issuance of Marriage Certificate" (distance: 25.7)

Step 3: Return
Returns service #1 with all details
```

---

### 4. Keyword Refinement (`refine_search_with_keywords()`)
```python
def refine_search_with_keywords(query, results):
    query_lower = query.lower()
    
    # Special handling for business permits
    if 'business permit' in query_lower:
        for result in results:
            service_name = result['service'].lower()
            
            # Boost main business permit service
            if 'business permit new and renewal' in service_name:
                result['score'] = result['score'] * 0.05  # Much lower distance
            
            # Deprioritize other permits
            elif 'building permit' in service_name:
                result['score'] = result['score'] * 5.0  # Much higher distance
        
        results.sort(key=lambda x: x['score'])
    
    return results
```

**Why needed:**
- Semantic search sometimes returns related but wrong services
- Example: "business permit" might return "building permit"
- Keyword matching ensures exact service is prioritized

---

### 5. Response Generation

#### **app.py (Template-based)**
```python
def generate_conversational_response(query, service):
    response = f"**{service['service_name']}**\n\n"
    response += f"📍 **Office:** {service['office']}\n\n"
    
    if service.get('requirements'):
        response += "📋 **Requirements:**\n"
        for req in service['requirements'][:5]:
            response += f"• {req['document']}\n"
    
    if service.get('fees_text'):
        response += f"💰 **Total Fees:** {calculate_total_fees(service['fees_text'])}\n"
    
    if service.get('time_text'):
        response += f"⏱️ **Processing Time:** {calculate_total_time(service['time_text'])}"
    
    return response
```

**Output:**
```
**Issuance of Birth Certificate**

📍 Office: Municipal Civil Registry Office

📋 Requirements:
• Duly accomplished application form
• Valid ID of applicant
• Payment receipt

💰 Total Fees: PHP 55.00
⏱️ Processing Time: 20 minutes
```

#### **app_improved.py (Groq LLM)**
```python
def generate_conversational_response(query, service):
    # Build context
    context = f"""Service: {service['service_name']}
Office: {service['office']}
Requirements: {service['requirements_text']}
Fees: {service['fees_text']}"""
    
    # Call Groq API
    prompt = f"""You are a helpful government assistant. 
Answer in 2-3 sentences.

Service Information:
{context}

Question: {query}

Answer:"""
    
    chat_completion = groq_client.chat.completions.create(
        messages=[{"role": "user", "content": prompt}],
        model="llama-3.3-70b-versatile",
        temperature=0.7,
        max_tokens=200
    )
    
    llm_intro = chat_completion.choices[0].message.content
    
    # Combine LLM intro + structured details
    response = f"{llm_intro}\n\n**{service['service_name']}**\n..."
    
    return response
```

**Output:**
```
To obtain a birth certificate in Sampaloc, Quezon, you'll need to 
visit the Municipal Civil Registry Office with a valid ID and completed 
application form. The process is quick, taking only about 20 minutes, 
and costs PHP 55.00.

**Issuance of Birth Certificate**

📍 Office: Municipal Civil Registry Office
...
```

---

## 🔄 Code Flow (Complete Request)

### Example: User asks "How do I get a birth certificate?"

```
1. USER TYPES: "How do I get a birth certificate?"
   ↓

2. FRONTEND (script.js)
   - sendMessage() function called
   - Shows typing indicator
   - Sends POST request to /chat
   ↓

3. BACKEND (app.py)
   - Receives request at @app.route('/chat')
   - Extracts message: "How do I get a birth certificate?"
   ↓

4. INPUT VALIDATION
   - is_valid_query() checks:
     ✓ Length > 3 characters
     ✓ Vowel ratio > 20%
     ✓ Special chars < 50%
   - Result: Valid ✓
   ↓

5. QUERY CLASSIFICATION
   - Check if greeting? No
   - Check if listing request? No
   - Check if office-specific? No
   - Proceed to semantic search
   ↓

6. SEMANTIC SEARCH
   - embedder.encode("How do I get a birth certificate?")
   - Returns: [0.234, -0.567, 0.891, ...] (384 numbers)
   ↓

7. FAISS SEARCH
   - faiss_index.search(embedding, top_k=3)
   - Compares with 109 service embeddings
   - Returns top 3 matches:
     1. "Issuance of Birth Certificate" (score: 12.5)
     2. "Issuance of Death Certificate" (score: 18.3)
     3. "Issuance of Marriage Certificate" (score: 25.7)
   ↓

8. KEYWORD REFINEMENT
   - refine_search_with_keywords()
   - Checks for "birth" keyword
   - Boosts "Birth Certificate" service
   - Final ranking unchanged (already #1)
   ↓

9. RESPONSE GENERATION
   - Takes best match: "Issuance of Birth Certificate"
   - Extracts service details from knowledge_base
   - Formats response with:
     • Service name
     • Office location
     • Requirements list
     • Fees (PHP 55.00)
     • Processing time (20 minutes)
   ↓

10. FOLLOW-UP QUESTIONS
    - Generates 3 related questions:
      • "What are the requirements for Birth Certificate?"
      • "How much does Birth Certificate cost?"
      • "How long does Birth Certificate take?"
   ↓

11. SEND RESPONSE
    - Returns JSON:
      {
        "response": "**Issuance of Birth Certificate**...",
        "follow_up_questions": [...],
        "status": "success"
      }
   ↓

12. FRONTEND RECEIVES
    - removeTypingIndicator()
    - addMessage(response, isUser=false)
    - Creates message bubble with avatar
    - Adds follow-up buttons
    - Scrolls to bottom
   ↓

13. USER SEES ANSWER
    - Message appears with bot avatar (logo)
    - Formatted with emojis and structure
    - Follow-up buttons clickable
```

**Total Time:** ~0.5-1 second

---

## 🎨 Frontend Explanation

### HTML Structure (index.html)
```html
<div class="container">
    <!-- Header with logo and clear button -->
    <div class="header">
        <h1>🏛️ Municipality of Sampaloc Quezon</h1>
        <button class="clear-btn" onclick="clearChat()">🗑️</button>
    </div>
    
    <!-- Quick action buttons -->
    <div class="quick-actions">
        <button onclick="quickAsk('What services do you offer?')">📋 Services</button>
        <button onclick="quickAsk('What are your office hours?')">🕐 Hours</button>
        ...
    </div>
    
    <!-- Chat messages area -->
    <div class="chat-container" id="chatContainer">
        <!-- Messages appear here -->
        <div class="message-wrapper bot-wrapper">
            <div class="avatar bot-avatar">
                <img src="logo.png" alt="Bot">
            </div>
            <div class="message bot-message">
                <p>Hello! How can I help?</p>
                <span class="timestamp">10:30 AM</span>
            </div>
        </div>
    </div>
    
    <!-- Input area -->
    <div class="input-container">
        <input type="text" id="userInput" placeholder="Ask about our services..." />
        <button onclick="sendMessage()">Send 📤</button>
    </div>
</div>
```

### CSS Styling (style.css)
```css
/* Modern glassmorphism design */
.container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

/* Dark blue gradient (government theme) */
.header {
    background: linear-gradient(135deg, #00008b 0%, #0000cd 100%);
    color: white;
}

/* Message bubbles */
.bot-message {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
}

.user-message {
    background: linear-gradient(135deg, #00008b 0%, #0000cd 100%);
    color: white;
}

/* Avatar circles */
.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
}

/* Smooth animations */
@keyframes slideIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
```

### JavaScript Logic (script.js)
```javascript
// Add message to chat
function addMessage(text, isUser, followUpQuestions = []) {
    // Create message wrapper
    const messageWrapper = document.createElement('div');
    messageWrapper.className = `message-wrapper ${isUser ? 'user-wrapper' : 'bot-wrapper'}`;
    
    // Add avatar (bot logo or user icon)
    if (!isUser) {
        const avatar = document.createElement('div');
        avatar.className = 'avatar bot-avatar';
        const img = document.createElement('img');
        img.src = '/static/LGU-Sampaloc-Quezon-Official-Logo-scaled.png';
        avatar.appendChild(img);
        messageWrapper.appendChild(avatar);
    }
    
    // Add message bubble
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
    messageDiv.innerHTML = text;
    
    // Add timestamp
    const timestamp = document.createElement('span');
    timestamp.className = 'timestamp';
    timestamp.textContent = getTimestamp();
    messageDiv.appendChild(timestamp);
    
    messageWrapper.appendChild(messageDiv);
    chatContainer.appendChild(messageWrapper);
    
    // Add follow-up buttons
    if (followUpQuestions.length > 0) {
        followUpQuestions.forEach(question => {
            const button = document.createElement('button');
            button.className = 'follow-up-button';
            button.textContent = question;
            button.onclick = () => {
                userInput.value = question;
                sendMessage();
            };
            chatContainer.appendChild(button);
        });
    }
    
    // Scroll to bottom
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// Send message to backend
async function sendMessage() {
    const message = userInput.value.trim();
    if (!message) return;
    
    // Show user message
    addMessage(message, true);
    userInput.value = '';
    
    // Show typing indicator
    showTypingIndicator();
    
    // Call API
    try {
        const response = await fetch('/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message })
        });
        
        const data = await response.json();
        
        // Remove typing indicator
        removeTypingIndicator();
        
        // Show bot response
        addMessage(data.response, false, data.follow_up_questions || []);
    } catch (error) {
        removeTypingIndicator();
        addMessage('Sorry, I could not connect to the server.', false);
    }
}

// Quick action buttons
function quickAsk(question) {
    userInput.value = question;
    sendMessage();
}

// Clear chat history
function clearChat() {
    if (confirm('Are you sure you want to clear the conversation?')) {
        chatContainer.innerHTML = '';
        // Add welcome message back
        addMessage('Hello! How can I assist you today?', false);
    }
}
```

---

## 📊 Data Flow Summary

### Training Phase (One-time)
```
1. Load citizens_charter_2025_sampaloc_quezon.json (109 services)
   ↓
2. Process into documents with text descriptions
   ↓
3. Generate embeddings using Sentence Transformer
   ↓
4. Build FAISS index for fast search
   ↓
5. Save models and index to disk
```

### Runtime Phase (Every request)
```
1. User types question
   ↓
2. Validate input
   ↓
3. Convert to embedding (384 numbers)
   ↓
4. Search FAISS index (find similar services)
   ↓
5. Refine with keywords
   ↓
6. Format response
   ↓
7. Send to user
```

---

## 🎯 Key Takeaways

### What Makes This Chatbot Work:

1. **Semantic Search (Sentence Transformer + FAISS)**
   - Understands meaning, not just keywords
   - Fast search through 109 services
   - Handles typos and variations

2. **Template-based Responses**
   - Predictable, structured output
   - Fast generation
   - Perfect for government data

3. **Smart Keyword Refinement**
   - Fixes semantic search mistakes
   - Ensures correct service is prioritized

4. **Modern UI**
   - Clean, professional design
   - Government color scheme (dark blue)
   - Responsive and mobile-friendly

### Models Actually Used:
✅ Sentence Transformer (all-MiniLM-L6-v2)
✅ FAISS Index
✅ Groq Llama 3.3 70B (app_improved.py only)

### Models Trained But Not Used:
⚠️ FLAN-T5 (loaded but not generating)
⚠️ Sentiment Analysis (saved but not loaded)
⚠️ Time Waster Classifier (saved but not loaded)

### Why Some Models Aren't Used:
- Template responses are sufficient
- Rule-based validation is simpler
- Sentiment not needed for current features
- Focus on speed and reliability

---

## 🚀 Potential Improvements

### 1. Use FLAN-T5 for Generation
```python
# Replace template with LLM
def generate_with_flan_t5(query, service):
    prompt = f"Answer this question about {service['service_name']}: {query}"
    inputs = tokenizer(prompt, return_tensors="pt")
    outputs = llm.generate(**inputs, max_length=200)
    return tokenizer.decode(outputs[0])
```

### 2. Add Sentiment Analysis
```python
# Prioritize negative sentiment
def analyze_sentiment(query):
    inputs = sentiment_tokenizer(query, return_tensors="pt")
    outputs = sentiment_model(**inputs)
    if outputs.logits[0][0] > outputs.logits[0][1]:
        return "negative"  # Prioritize this user
    return "positive"
```

### 3. Fine-tune FLAN-T5
```python
# Use the 596 training examples
# Train on government-specific responses
# Better quality than base model
```

### 4. Add Multi-language Support
```python
# Detect Tagalog
# Translate to English
# Search and respond
# Translate back to Tagalog
```

---

## 📝 Conclusion

This chatbot uses a **hybrid approach**:
- **AI Models** for understanding (Sentence Transformer, FAISS)
- **Template-based** for responding (fast, predictable)
- **Optional LLM** for natural language (Groq in app_improved.py)

The architecture is **efficient, scalable, and maintainable** - perfect for a government service application!
