// Full-page AI Assistant screen — talks to the same /chatbot/chat and
// /chatbot/history endpoints as the floating chat widgets, so conversation
// history is shared with whichever widget the user was using before.

function aiPageTimestamp() {
    return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function aiPageAddMessage(text, isUser) {
    const container = document.getElementById('ai-assistant-messages');
    if (!container) return;

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
    bubble.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');

    const ts = document.createElement('span');
    ts.className = 'chat-ts';
    ts.textContent = aiPageTimestamp();
    bubble.appendChild(ts);

    wrapper.appendChild(bubble);
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}

function aiPageShowTyping() {
    const container = document.getElementById('ai-assistant-messages');
    if (!container) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg bot';
    wrapper.id = 'ai-page-typing';
    wrapper.innerHTML = '<div class="chat-msg-avatar"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><div class="chat-typing-indicator"><span></span><span></span><span></span></div>';
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}

function aiPageRemoveTyping() {
    const el = document.getElementById('ai-page-typing');
    if (el) el.remove();
}

function aiPageSend() {
    const input = document.getElementById('ai-assistant-input');
    if (!input) return;
    const text = input.value.trim();
    if (!text) return;

    aiPageAddMessage(text, true);
    input.value = '';
    aiPageShowTyping();

    fetch('/chatbot/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ message: text }),
    })
        .then((response) => response.json())
        .then((data) => {
            aiPageRemoveTyping();
            if (data.status === 'success') {
                aiPageAddMessage(data.response, false);
            } else {
                aiPageAddMessage('Sorry, I encountered an error. Please try again.', false);
            }
        })
        .catch(() => {
            aiPageRemoveTyping();
            aiPageAddMessage("Sorry, I couldn't process your request. Please try again.", false);
        });
}

function aiPageQuickAsk(question) {
    const input = document.getElementById('ai-assistant-input');
    if (!input) return;
    input.value = question;
    aiPageSend();
}

function aiPageClear() {
    if (!confirm('Clear the conversation?')) return;

    fetch('/chatbot/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ message: '', reset: true }),
    }).catch(() => {});

    const container = document.getElementById('ai-assistant-messages');
    if (container) container.innerHTML = '';
    aiPageAddMessage(window.aiAssistantGreeting || "Hello! I'm your PRIME HRIS AI Assistant. How can I help you today?", false);
}

function aiPageRestoreHistory() {
    fetch('/chatbot/history', { headers: { Accept: 'application/json' } })
        .then((response) => response.json())
        .then((data) => {
            if (data.status === 'success' && Array.isArray(data.history) && data.history.length > 0) {
                const container = document.getElementById('ai-assistant-messages');
                if (!container) return;
                container.innerHTML = '';
                data.history.forEach((turn) => aiPageAddMessage(turn.content, turn.role === 'user'));
            }
        })
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', aiPageRestoreHistory);

window.aiPageSend = aiPageSend;
window.aiPageQuickAsk = aiPageQuickAsk;
window.aiPageClear = aiPageClear;
