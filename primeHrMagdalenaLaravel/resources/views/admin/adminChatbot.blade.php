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
            <div class="chat-msg-bubble">Hello! I'm the PRIME HRIS Assistant. How can I help you today? You can ask me about payroll, attendance, leave management, or any HR-related queries.</div>
        </div>
    </div>

    <div class="chatbot-faqs">
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('How do I process payroll?')">How do I process payroll?</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('How to approve leave requests?')">How to approve leave requests?</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('View employee attendance records')">View employee attendance records</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('Generate payroll reports')">Generate payroll reports</button>
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
// Chatbot responses
const chatResponses = {
    'How do I process payroll?': 'To process payroll: 1) Go to Payroll section, 2) Click "Process Payroll" button, 3) Review employee records, 4) Approve and generate payslips. The system automatically calculates deductions.',
    'How to approve leave requests?': 'Navigate to Leave & Benefits section. You\'ll see pending requests with employee details. Click "View" to review, then "Approve" or "Reject" with optional comments.',
    'View employee attendance records': 'Go to Attendance section. Use the search bar to find specific employees or filter by date range. You can export records for reporting purposes.',
    'Generate payroll reports': 'Visit the Reports section. Select "Payroll Reports", choose the period, and click "Generate". You can export to PDF or Excel format.'
};

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
    
    // Add user message
    addChatMessage('user', message);
    
    // Get bot response
    const response = chatResponses[message] || 'Thank you for your question. For detailed assistance, please refer to the user manual or contact the system administrator.';
    
    // Add bot response after a short delay
    setTimeout(() => {
        addChatMessage('bot', response);
    }, 500);
    
    input.value = '';
}

function sendPredefinedMessage(message) {
    document.getElementById('chatInput').value = message;
    sendChatMessage();
}

function addChatMessage(from, text) {
    const messagesContainer = document.getElementById('chatbotMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-msg ${from}`;
    
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

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
    }
}
</script>
