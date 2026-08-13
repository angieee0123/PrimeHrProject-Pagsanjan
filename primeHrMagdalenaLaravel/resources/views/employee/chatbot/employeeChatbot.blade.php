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
            {{-- Expand leaves the widget for the full AI Assistant page, which is
                 the same assistant with the conversation list, search and history
                 the panel has no room for. The thread continues there: both go
                 through AiConversationStore::continueLatestOrStart(). --}}
            <a class="chatbot-fullscreen" href="{{ route('employee.ai-assistant') }}"
               title="Open full AI Assistant" aria-label="Open the full AI Assistant page">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                </svg>
            </a>
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
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('What is my leave balance?')">Leave balance</button>
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('Show my latest payslip')">Payslip</button>
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('What is my attendance this month?')">Attendance</button>
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('What are my trainings?')">Training</button>
                {{-- "Performance" and "HR contact" were offered here while the
                     schema has no performance/evaluation table and no HR contact
                     record — the old canned answers invented a 4.8/5.0 rating and
                     a phone number. Replaced with questions the assistant can
                     actually answer from the database. --}}
                <button class="chatbot-quick-btn" onclick="quickAskPermanent('What leave types can I file?')">Leave types</button>
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
// No "performance evaluations": this schema has no performance table, so
// offering it invites a question the assistant cannot answer from data.
const CHAT_GREETING = "Hello! I'm your PRIME HRIS assistant. I can look up your own leave balances, payslips, attendance records, trainings and travel orders, and explain how to use the system. How can I assist you today?";
const CHAT_BOT_ICON = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

let chatBusy = false;
// Captured at load so "Clear conversation" can put the starter prompts back.
let chatSuggestHtml = '';

/* `iso` is the stored time of a turn replayed from history; live turns pass
   nothing and are stamped now. */
function getPermanentTimestamp(iso) {
    const at = iso ? new Date(iso) : new Date();
    return (isNaN(at) ? new Date() : at)
        .toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
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

function addPermanentMessage(text, isUser, at) {
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
    ts.textContent = getPermanentTimestamp(at);
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
            'This starts a fresh chat, so the assistant stops using these ' + bubbles
            + ' messages as context. The conversation is kept, and your leave, payslip '
            + 'and training records are not affected.';
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
    // Tell the server too, not just the bubbles. The assistant resolves
    // pronouns against the stored thread — wiping only the screen would leave
    // "cleared" turns still steering the next answer while the employee
    // believes they started fresh. The thread itself is kept; the next question
    // simply opens a new one.
    fetch('/chatbot/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ message: '', reset: true }),
    }).catch(function () { /* The bubbles still clear; nothing useful to show. */ });

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

/* Asks the same assistant the full AI Assistant page asks.

   This used to answer from a hard-coded if/else that never contacted the
   server: it told every employee their vacation balance was 12.5 days, quoted a
   payslip for "Jun 16-30, 2025", and reported a 4.8/5.0 performance rating from
   a table this schema does not have. None of it was read from anywhere, and the
   widget ships on ten employee pages.

   /chatbot/chat is ChatbotController, which calls AiQueryService — the same
   orchestrator, AiAccessPolicy scoping, HrPolicyFactsService rules and audit
   logging as the full page. Answers here are now the employee's real records or
   an honest refusal. */
async function sendPermanentMessage() {
    if (chatBusy) return;

    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;

    addPermanentMessage(text, true);
    input.value = '';
    autosizeChatInput();
    setChatBusy(true);
    showPermanentTyping();

    try {
        const response = await fetch('/chatbot/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: text }),
        });

        const data = await response.json().catch(() => ({}));

        removePermanentTyping();

        if (!response.ok || !data.response) {
            // Say that the answer could not be fetched. Never substitute a
            // stand-in figure — an invented balance is worse than no answer.
            addPermanentMessage(
                data.error
                    || "Sorry, I couldn't reach your records just now. Please try again in a moment.",
                false
            );
        } else {
            addPermanentMessage(data.response, false);
            renderChatFollowUps(data.follow_ups);
        }
    } catch (e) {
        removePermanentTyping();
        addPermanentMessage(
            "Sorry, I couldn't reach your records just now. Please check your connection and try again.",
            false
        );
    } finally {
        setChatBusy(false);
    }
}

/* Suggested next questions, as the full page renders them: newest turn only,
   so a thread does not accumulate stale chips. */
function renderChatFollowUps(followUps) {
    document.querySelectorAll('#chatbot-messages .chat-followups').forEach(el => el.remove());

    if (!Array.isArray(followUps) || followUps.length === 0) return;

    const container = document.getElementById('chatbot-messages');
    const wrap = document.createElement('div');
    wrap.className = 'chat-followups';

    followUps.forEach(function (question) {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'chatbot-quick-btn';
        chip.textContent = question;
        chip.addEventListener('click', function () { quickAskPermanent(question); });
        wrap.appendChild(chip);
    });

    container.appendChild(wrap);
    scrollChatToBottom();
}


/* The conversation is stored in `ai_conversations` (see ChatbotController), and
   it keeps accumulating as the employee moves between pages — the assistant
   resolves "his"/"that one" against it. Re-rendering only the greeting would
   hide the turns those pronouns refer to, so restore them, exactly as the admin
   chathead does.

   It used to be kept in the Laravel session, which logout discards, so an
   employee came back the next morning to an empty panel and an assistant that
   had forgotten the thread. */
function restoreChatHistory() {
    fetch('/chatbot/history', {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
    })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success' || !Array.isArray(data.history) || data.history.length === 0) {
                return;
            }

            const container = document.getElementById('chatbot-messages');
            container.innerHTML = '';
            data.history.forEach(function (turn) {
                addPermanentMessage(turn.content, turn.role === 'user', turn.created_at);
            });
        })
        .catch(function () { /* Keep the greeting; a lost history is not an error worth showing. */ });
}

document.addEventListener('DOMContentLoaded', function () {
    const suggestions = document.getElementById('chat-suggest');
    if (suggestions) chatSuggestHtml = suggestions.outerHTML;

    const greeting = document.getElementById('chat-greeting');
    if (greeting) {
        greeting.innerHTML = formatChatMessage(CHAT_GREETING)
            + '<span class="chat-ts">' + getPermanentTimestamp() + '</span>';
    }

    restoreChatHistory();

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
