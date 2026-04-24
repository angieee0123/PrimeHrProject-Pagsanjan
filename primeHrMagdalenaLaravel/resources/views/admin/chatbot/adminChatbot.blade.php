<!-- AI Chatbot FAB Button -->
<button class="chat-fab" onclick="toggleChatbot()" title="HRIS Assistant">
    <svg class="chat-fab-icon-open" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    <svg class="chat-fab-icon-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: none;">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
    <span class="chat-fab-badge">AI</span>
</button>

<!-- Chatbot Window -->
<div class="chatbot-window" id="chatbotWindow" style="display: none;">
    <div class="chatbot-header">
        <div class="chatbot-header-left">
            <div class="chatbot-avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div>
                <p class="chatbot-name">PRIME HRIS Assistant</p>
                <p class="chatbot-status">● Online</p>
            </div>
        </div>
        <button class="chatbot-close" onclick="toggleChatbot()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="chatbot-messages" id="chatbotMessages">
        <div class="chat-msg bot">
            <div class="chat-msg-avatar">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="chat-msg-bubble">Hello! I'm the PRIME HRIS Assistant. I can help you with employee information, departments, and HR data. I understand natural questions like "How many people work here?" or "Find John Doe" or "Who's in the Mayor's office?" Try asking me anything!</div>
        </div>
    </div>

    <div class="chatbot-faqs">
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('How many people work here?')">Total employees</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('Show me active employees')">Active staff</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('Who works in Mayor office?')">Mayor's Office</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('Find administrator')">Find employee</button>
    </div>

    <div class="chatbot-input-row">
        <input type="text" id="chatInput" placeholder="Type your question..." onkeypress="handleChatKeyPress(event)">
        <button class="chatbot-send" onclick="sendChatMessage()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
        </button>
    </div>
</div>

<script>
function toggleChatbot() {
    const window = document.getElementById('chatbotWindow');
    const fab = document.querySelector('.chat-fab');
    const openIcon = document.querySelector('.chat-fab-icon-open');
    const closeIcon = document.querySelector('.chat-fab-icon-close');
    const badge = document.querySelector('.chat-fab-badge');
    
    if (window.style.display === 'none') {
        window.style.display = 'flex';
        fab.classList.add('open');
        openIcon.style.display = 'none';
        closeIcon.style.display = 'block';
        badge.style.display = 'none';
    } else {
        window.style.display = 'none';
        fab.classList.remove('open');
        openIcon.style.display = 'block';
        closeIcon.style.display = 'none';
        badge.style.display = 'block';
    }
}

function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    addChatMessage('user', message);
    input.value = '';
    
    addTypingIndicator();
    
    fetch('/api/chatbot', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        removeTypingIndicator();
        if (data.status === 'success') {
            addChatMessage('bot', data.response);
        } else {
            addChatMessage('bot', 'Sorry, I encountered an error. Please try again.');
        }
    })
    .catch(error => {
        removeTypingIndicator();
        console.error('Error:', error);
        addChatMessage('bot', 'Sorry, I couldn\'t process your request. Please try again.');
    });
}

function sendPredefinedMessage(message) {
    document.getElementById('chatInput').value = message;
    sendChatMessage();
}

function addChatMessage(from, text) {
    const messagesContainer = document.getElementById('chatbotMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-msg ${from}`;
    
    // Convert markdown-style formatting to HTML
    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\n/g, '<br>');
    
    if (from === 'bot') {
        messageDiv.innerHTML = `
            <div class="chat-msg-avatar">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="chat-msg-bubble">${text}</div>
        `;
    } else {
        messageDiv.innerHTML = `<div class="chat-msg-bubble">${text}</div>`;
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function addTypingIndicator() {
    const messagesContainer = document.getElementById('chatbotMessages');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'chat-msg bot typing-indicator';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `
        <div class="chat-msg-avatar">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <div class="chat-msg-bubble">Typing...</div>
    `;
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
        indicator.remove();
    }
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
    }
}
</script>
