{{-- AI Chatbot FAB --}}
<button class="chat-fab" id="chat-fab" onclick="togglePermanentChat()"
        aria-label="Open AI assistant" aria-expanded="false" aria-controls="chatbot-window">
    <svg class="chat-fab-ico is-chat" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    <svg class="chat-fab-ico is-close" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
    <span class="chat-fab-badge" id="chat-fab-badge">AI</span>
</button>

{{-- Chatbot Window --}}
<div class="chatbot-window" id="chatbot-window" role="dialog" aria-labelledby="chatbot-title" aria-hidden="true">
    <div class="chatbot-header">
        <div class="chatbot-header-left">
            <div class="chatbot-avatar">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div>
                <p class="chatbot-name" id="chatbot-title">PRIME HRIS Assistant</p>
                <p class="chatbot-status"><span class="chatbot-status-dot" aria-hidden="true"></span>Online</p>
            </div>
        </div>
        <div class="chatbot-header-actions">
            <button class="chatbot-clear" onclick="clearPermanentChat()" title="Clear conversation" aria-label="Clear conversation">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                </svg>
            </button>
            <button class="chatbot-close" onclick="togglePermanentChat()" title="Close" aria-label="Close assistant">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="chatbot-messages" id="chatbot-messages" role="log" aria-live="polite" aria-label="Conversation">
        <div class="chat-msg bot">
            <div class="chat-msg-avatar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="chat-msg-bubble" id="chat-greeting"></div>
        </div>

        {{-- Starter prompts live in the conversation, not in a bar above it:
             they are only useful before the first question, and a pinned strip
             cost a permanent row of the panel. Removed on first send. --}}
        <div class="chat-suggest-wrap" id="chat-suggest">
            <p class="chat-suggest-label">Try asking</p>
            <div class="chat-suggest" role="group" aria-label="Suggested questions">
                @if($isPermanent ?? false)
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('How do I file a leave request?')">Leave</button>
                @endif
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('Check my payslip')">Payslip</button>
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('View my attendance')">Attendance</button>
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('My training programs')">Training</button>
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('My performance rating')">Performance</button>
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('HR contact information')">HR contact</button>
            </div>
        </div>
    </div>

    <div class="chatbot-composer">
        <div class="chatbot-input-row">
            <textarea id="chat-input" rows="1" maxlength="500" aria-label="Message"
                      placeholder="Ask about leave, payroll, training…"></textarea>
            <button class="chatbot-send" id="chat-send" onclick="sendPermanentMessage()" aria-label="Send message" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>
        <p class="chatbot-hint"><kbd>Enter</kbd> to send · <kbd>Shift</kbd>+<kbd>Enter</kbd> for a new line</p>
    </div>

    {{-- In-panel confirmation, in place of the browser's confirm() dialog --}}
    <div class="chat-confirm" id="chat-confirm">
        <div class="chat-confirm-card" role="alertdialog" aria-modal="true"
             aria-labelledby="chat-confirm-title" aria-describedby="chat-confirm-text">
            <div class="chat-confirm-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                </svg>
            </div>
            <p class="chat-confirm-title" id="chat-confirm-title">Clear this conversation?</p>
            <p class="chat-confirm-text" id="chat-confirm-text"></p>
            <div class="chat-confirm-actions">
                <button type="button" class="chat-confirm-btn is-cancel" id="chat-confirm-cancel" onclick="closeClearConfirm()">Keep it</button>
                <button type="button" class="chat-confirm-btn is-danger" onclick="confirmClearPermanentChat()">Clear chat</button>
            </div>
        </div>
    </div>
</div>

<script>
const CHAT_GREETING = "Hello! I'm your PRIME HRIS assistant. I can help you with leave requests, payslip inquiries, attendance records, training programs, and performance evaluations. How can I assist you today?";
const CHAT_BOT_ICON = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

let chatBusy = false;
// Captured at load so "Clear conversation" can put the starter prompts back.
let chatSuggestHtml = '';

function getPermanentTimestamp() {
    return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function escapeChatHtml(text) {
    const el = document.createElement('div');
    el.textContent = text;
    return el.innerHTML;
}

/* Escape first, then apply the small markdown subset the canned answers use, so
   a message can never inject markup. Bullet runs become a real list and blank
   lines become paragraph breaks — previously every newline was a <br>, which
   left long answers as one dense block. */
function formatChatMessage(raw) {
    const lines = escapeChatHtml(raw).split('\n');
    let html = '';
    let inList = false;

    lines.forEach(line => {
        const bullet = line.match(/^\s*[•\-]\s+(.*)$/);
        if (bullet) {
            if (!inList) { html += '<ul class="chat-list">'; inList = true; }
            html += '<li>' + bullet[1] + '</li>';
            return;
        }
        if (inList) { html += '</ul>'; inList = false; }
        if (line.trim() !== '') html += '<p class="chat-p">' + line + '</p>';
    });
    if (inList) html += '</ul>';

    return html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
}

function scrollChatToBottom() {
    const container = document.getElementById('chatbot-messages');
    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
}

function togglePermanentChat() {
    const fab = document.getElementById('chat-fab');
    const win = document.getElementById('chatbot-window');
    const badge = document.getElementById('chat-fab-badge');
    const isOpen = win.classList.contains('open');

    win.classList.toggle('open', !isOpen);
    fab.classList.toggle('open', !isOpen);
    fab.setAttribute('aria-expanded', String(!isOpen));
    fab.setAttribute('aria-label', isOpen ? 'Open AI assistant' : 'Close AI assistant');
    win.setAttribute('aria-hidden', String(isOpen));
    badge.style.display = isOpen ? 'block' : 'none';

    if (isOpen) {
        fab.focus();
    } else {
        const input = document.getElementById('chat-input');
        if (input) input.focus();
        scrollChatToBottom();
    }
}

function removeChatSuggestions() {
    const el = document.getElementById('chat-suggest');
    if (el) el.remove();
}

function addPermanentMessage(text, isUser) {
    const container = document.getElementById('chatbot-messages');
    if (isUser) removeChatSuggestions();

    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg ' + (isUser ? 'user' : 'bot');

    if (!isUser) {
        const avatar = document.createElement('div');
        avatar.className = 'chat-msg-avatar';
        avatar.innerHTML = CHAT_BOT_ICON;
        wrapper.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = 'chat-msg-bubble';
    bubble.innerHTML = formatChatMessage(text);

    const ts = document.createElement('span');
    ts.className = 'chat-ts';
    ts.textContent = getPermanentTimestamp();
    bubble.appendChild(ts);

    wrapper.appendChild(bubble);
    container.appendChild(wrapper);
    scrollChatToBottom();
}

function showPermanentTyping() {
    const container = document.getElementById('chatbot-messages');
    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg bot';
    wrapper.id = 'chat-typing';
    wrapper.innerHTML = '<div class="chat-msg-avatar">' + CHAT_BOT_ICON + '</div>'
        + '<div class="chat-typing-indicator" role="status" aria-label="Assistant is typing">'
        + '<span></span><span></span><span></span></div>';
    container.appendChild(wrapper);
    scrollChatToBottom();
}

function removePermanentTyping() {
    const el = document.getElementById('chat-typing');
    if (el) el.remove();
}

function setChatBusy(busy) {
    chatBusy = busy;
    const input = document.getElementById('chat-input');
    input.disabled = busy;
    syncChatSendState();
    if (!busy) input.focus();
}

// The send button is only live when there is something to send.
function syncChatSendState() {
    const input = document.getElementById('chat-input');
    document.getElementById('chat-send').disabled = chatBusy || input.value.trim() === '';
}

function autosizeChatInput() {
    const input = document.getElementById('chat-input');
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 96) + 'px';
}

/* Opens the in-panel sheet rather than a browser confirm(), and names what is
   actually at stake — the count of messages, and the fact that HR records are
   not touched, which is the thing people are unsure about. */
function clearPermanentChat() {
    const sheet = document.getElementById('chat-confirm');
    const bubbles = document.querySelectorAll('#chatbot-messages .chat-msg').length;

    if (bubbles <= 1) {
        document.getElementById('chat-confirm-text').textContent =
            'There is nothing to clear yet — this chat only has the welcome message.';
    } else {
        document.getElementById('chat-confirm-text').textContent =
            'This removes all ' + bubbles + ' messages from this chat. Your leave, payslip and '
            + 'training records are not affected.';
    }

    sheet.classList.add('is-open');
    document.getElementById('chat-confirm-cancel').focus();
}

function closeClearConfirm() {
    document.getElementById('chat-confirm').classList.remove('is-open');
    const input = document.getElementById('chat-input');
    if (input) input.focus();
}

function isClearConfirmOpen() {
    return document.getElementById('chat-confirm').classList.contains('is-open');
}

function confirmClearPermanentChat() {
    const container = document.getElementById('chatbot-messages');
    container.innerHTML = '';
    addPermanentMessage(CHAT_GREETING, false);
    container.insertAdjacentHTML('beforeend', chatSuggestHtml);
    closeClearConfirm();
}

function quickAskPermanent(question) {
    if (chatBusy) return;
    const input = document.getElementById('chat-input');
    input.value = question;
    sendPermanentMessage();
}

function sendPermanentMessage() {
    if (chatBusy) return;

    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;

    addPermanentMessage(text, true);
    input.value = '';
    autosizeChatInput();
    setChatBusy(true);
    showPermanentTyping();

    setTimeout(() => {
        removePermanentTyping();
        addPermanentMessage(getPermanentResponse(text), false);
        setChatBusy(false);
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

document.addEventListener('DOMContentLoaded', function () {
    const suggestions = document.getElementById('chat-suggest');
    if (suggestions) chatSuggestHtml = suggestions.outerHTML;

    const greeting = document.getElementById('chat-greeting');
    if (greeting) {
        greeting.innerHTML = formatChatMessage(CHAT_GREETING)
            + '<span class="chat-ts">' + getPermanentTimestamp() + '</span>';
    }

    const input = document.getElementById('chat-input');
    input.addEventListener('input', function () {
        autosizeChatInput();
        syncChatSendState();
    });
    // Enter sends; Shift+Enter is a new line.
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendPermanentMessage();
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    // Escape backs out of the confirmation first, not the whole panel.
    if (isClearConfirmOpen()) { closeClearConfirm(); return; }

    const win = document.getElementById('chatbot-window');
    if (win.classList.contains('open')) togglePermanentChat();
});

document.addEventListener('click', function(e) {
    const win = document.getElementById('chatbot-window');
    const fab = document.getElementById('chat-fab');
    if (!win.classList.contains('open')) return;

    /* A click whose target its own handler already detached — a suggestion chip
       removing the chip group on first send — is no longer inside the panel by
       the time this runs, and would read as an outside click and close it.
       Anything no longer in the document cannot be judged, so ignore it. */
    if (!document.contains(e.target)) return;

    if (!win.contains(e.target) && !fab.contains(e.target)) {
        togglePermanentChat();
    }
});
</script>
