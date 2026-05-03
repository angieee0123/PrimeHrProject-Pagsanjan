{{-- AI Chatbot FAB --}}
<button class="chat-fab" id="chat-fab" onclick="togglePermanentChat()" title="AI Assistant">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    <span class="chat-fab-badge" id="chat-fab-badge">AI</span>
</button>

{{-- Chatbot Window --}}
<div class="chatbot-window" id="chatbot-window" style="display:none">
    <div class="chatbot-header">
        <div class="chatbot-header-left">
            <div class="chatbot-avatar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div>
                <p class="chatbot-name">PRIME HRIS Assistant</p>
                <p class="chatbot-status">● Online</p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
            <button class="chatbot-clear" onclick="clearPermanentChat()" title="Clear conversation">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                </svg>
            </button>
            <button class="chatbot-close" onclick="togglePermanentChat()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="chatbot-quick-actions">
        <button class="chatbot-quick-btn" onclick="quickAskPermanent('How do I file a leave request?')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
            Leave
        </button>
        <button class="chatbot-quick-btn" onclick="quickAskPermanent('Check my payslip')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payslip
        </button>
        <button class="chatbot-quick-btn" onclick="quickAskPermanent('View my attendance')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Attendance
        </button>
        <button class="chatbot-quick-btn" onclick="quickAskPermanent('My training programs')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            Training
        </button>
    </div>

    <div class="chatbot-messages" id="chatbot-messages">
        <div class="chat-msg bot">
            <div class="chat-msg-avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="chat-msg-bubble">Hello! I'm your PRIME HRIS assistant. I can help you with leave requests, payslip inquiries, attendance records, training programs, and performance evaluations. How can I assist you today?<span class="chat-ts"></span></div>
        </div>
    </div>

    <div class="chatbot-input-row">
        <input type="text" id="chat-input" placeholder="Ask about leave, payroll, training..." onkeydown="if(event.key==='Enter') sendPermanentMessage()">
        <button class="chatbot-send" onclick="sendPermanentMessage()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
        </button>
    </div>
</div>

<script>
function getPermanentTimestamp() {
    return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function togglePermanentChat() {
    const win = document.getElementById('chatbot-window');
    const badge = document.getElementById('chat-fab-badge');
    const isOpen = win.style.display === 'flex';
    win.style.display = isOpen ? 'none' : 'flex';
    badge.style.display = isOpen ? 'block' : 'none';
}

function addPermanentMessage(text, isUser) {
    const container = document.getElementById('chatbot-messages');
    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg ' + (isUser ? 'user' : 'bot');

    if (!isUser) {
        const avatar = document.createElement('div');
        avatar.className = 'chat-msg-avatar';
        avatar.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
        wrapper.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = 'chat-msg-bubble';
    const ts = document.createElement('span');
    ts.className = 'chat-ts';
    ts.textContent = getPermanentTimestamp();
    bubble.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
    bubble.appendChild(ts);
    wrapper.appendChild(bubble);
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}

function showPermanentTyping() {
    const container = document.getElementById('chatbot-messages');
    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg bot';
    wrapper.id = 'chat-typing';
    wrapper.innerHTML = '<div class="chat-msg-avatar"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><div class="chat-typing-indicator"><span></span><span></span><span></span></div>';
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}

function removePermanentTyping() {
    const el = document.getElementById('chat-typing');
    if (el) el.remove();
}

function clearPermanentChat() {
    if (!confirm('Clear the conversation?')) return;
    const container = document.getElementById('chatbot-messages');
    container.innerHTML = '';
    addPermanentMessage("Hello! I'm your PRIME HRIS assistant. I can help you with leave requests, payslip inquiries, attendance records, training programs, and performance evaluations. How can I assist you today?", false);
}

function quickAskPermanent(question) {
    document.getElementById('chat-input').value = question;
    sendPermanentMessage();
}

function sendPermanentMessage() {
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;

    addPermanentMessage(text, true);
    input.value = '';
    showPermanentTyping();

    setTimeout(() => {
        removePermanentTyping();
        const response = getPermanentResponse(text);
        addPermanentMessage(response, false);
    }, 800);
}

function getPermanentResponse(question) {
    const q = question.toLowerCase();
    
    if (q.includes('leave') || q.includes('vacation') || q.includes('sick')) {
        return "To file a leave request:\n\n**1.** Go to the **Leave** section in the sidebar\n**2.** Click **File Leave Request**\n**3.** Select leave type (Vacation, Sick, Emergency)\n**4.** Choose dates and provide reason\n**5.** Submit for approval\n\nYour current leave balance:\n• Vacation: 12.5 days\n• Sick: 10 days\n• Emergency: 3 days";
    }
    
    if (q.includes('payslip') || q.includes('payroll') || q.includes('salary')) {
        return "Your latest payslip for **Jun 16-30, 2025**:\n\n**Basic Pay:** ₱16,921.50\n**Deductions:** ₱3,384.00\n**Net Pay:** ₱13,537.50\n\nYou can view and download your payslip from the **Dashboard** or **Payroll** section. Next pay date is **Jun 30, 2025**.";
    }
    
    if (q.includes('attendance') || q.includes('dtr') || q.includes('time')) {
        return "Your attendance summary:\n\n**Current Month:** 95% attendance\n**Days Present:** 20 days\n**Days Absent:** 1 day\n\nYou can view your complete DTR records in the **Attendance** section. Make sure to log in and out daily using the biometric system.";
    }
    
    if (q.includes('training') || q.includes('course') || q.includes('program')) {
        return "Your training programs:\n\n**In Progress:**\n• Leadership Development Program (65% complete)\n\n**Completed:**\n• Customer Service Excellence\n• Digital Literacy Training\n\n**Available:**\n• Advanced Communication Skills\n• Project Management Basics\n\nVisit the **Training** section to enroll in new programs or continue your current training.";
    }
    
    if (q.includes('performance') || q.includes('evaluation') || q.includes('rating')) {
        return "Your performance summary:\n\n**Latest Rating:** 4.8 / 5.0 (Jan-Jun 2025)\n**Average Rating:** 4.7 / 5.0\n**Total Evaluations:** 4\n\n**Active Goals:**\n• Complete Advanced Leadership Training (65%)\n• Reduce Processing Time by 20% (45%)\n• Complete Safety Certification (30%)\n\nView detailed reports in the **Performance** section.";
    }
    
    if (q.includes('contact') || q.includes('hr') || q.includes('help')) {
        return "**HR Contact Information:**\n\n📧 Email: hr@primehris.gov.ph\n📞 Phone: (123) 456-7890\n🏢 Office: Municipal Hall, 2nd Floor\n⏰ Hours: Mon-Fri, 8:00 AM - 5:00 PM\n\nFor urgent concerns, you can also visit the HR office directly or send a message through the system.";
    }
    
    return `I'm processing your request about "${question}". I can help you with:\n\n• **Leave requests** and balance inquiries\n• **Payslip** and payroll information\n• **Attendance** and DTR records\n• **Training programs** and enrollment\n• **Performance** evaluations and goals\n• **HR contact** information\n\nPlease ask me anything about these topics!`;
}
</script>
