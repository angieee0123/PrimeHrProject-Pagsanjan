// Full-page AI Assistant (admin/employee/mayor). All rendering happens here —
// the Blade partial only outputs the initial conversation list as JSON so the
// list-rendering logic lives in exactly one place (see page.blade.php).
(function () {
    const page = document.getElementById('ai-page');
    if (!page) return;

    const sendUrl = page.dataset.sendUrl;
    const searchUrl = page.dataset.searchUrl;
    const conversationsUrl = page.dataset.conversationsUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const listEl = document.getElementById('ai-conversations-list');
    const messagesEl = document.getElementById('ai-messages');
    const welcomeEl = document.getElementById('ai-welcome');
    const inputForm = document.getElementById('ai-input-form');
    const inputEl = document.getElementById('ai-input');
    const sendBtn = document.getElementById('ai-send-btn');
    const searchInput = document.getElementById('ai-search-input');
    const newChatBtn = document.getElementById('ai-new-chat-btn');
    const sidebarEl = document.getElementById('ai-page-sidebar');
    const mobileToggle = document.getElementById('ai-mobile-list-toggle');

    let conversations = [];
    try {
        conversations = JSON.parse(page.dataset.initialConversations || '[]');
    } catch (e) {
        conversations = [];
    }

    let activeConversationId = null;
    let isSearching = false;
    let searchDebounce = null;
    let renderedItems = conversations;

    function timeAgo(iso) {
        if (!iso) return '';
        const diffMs = Date.now() - new Date(iso).getTime();
        const mins = Math.round(diffMs / 60000);
        if (mins < 1) return 'just now';
        if (mins < 60) return mins + 'm ago';
        const hours = Math.round(mins / 60);
        if (hours < 24) return hours + 'h ago';
        const days = Math.round(hours / 24);
        if (days < 7) return days + 'd ago';
        return new Date(iso).toLocaleDateString();
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function renderConversationList(items, opts) {
        opts = opts || {};
        renderedItems = items;
        listEl.innerHTML = '';

        if (!items.length) {
            const empty = document.createElement('p');
            empty.className = 'ai-page-empty';
            empty.textContent = opts.emptyText || 'No conversations yet. Ask something to get started.';
            listEl.appendChild(empty);
            return;
        }

        items.forEach(function (conv) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ai-page-conversation-item' + (conv.id === activeConversationId ? ' active' : '');
            btn.dataset.id = conv.id;

            const main = document.createElement('div');
            main.className = 'ai-conv-main';

            const title = document.createElement('span');
            title.className = 'ai-conv-title';
            title.textContent = conv.title || 'New conversation';
            main.appendChild(title);

            if (conv.snippet) {
                const snippet = document.createElement('span');
                snippet.className = 'ai-conv-snippet';
                snippet.textContent = conv.snippet;
                main.appendChild(snippet);
            } else {
                const date = document.createElement('span');
                date.className = 'ai-conv-date';
                date.textContent = timeAgo(conv.updated_at);
                main.appendChild(date);
            }

            btn.appendChild(main);

            if (!isSearching) {
                const del = document.createElement('button');
                del.type = 'button';
                del.className = 'ai-conv-delete';
                del.title = 'Delete conversation';
                del.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>';
                del.addEventListener('click', function (e) {
                    e.stopPropagation();
                    deleteConversation(conv.id);
                });
                btn.appendChild(del);
            }

            btn.addEventListener('click', function () {
                selectConversation(conv.id);
                if (window.innerWidth <= 860) sidebarEl.classList.remove('open');
            });

            listEl.appendChild(btn);
        });
    }

    function showWelcome() {
        messagesEl.innerHTML = '';
        messagesEl.appendChild(welcomeEl);
    }

    function appendMessage(role, content, timestamp) {
        if (welcomeEl.parentNode === messagesEl) messagesEl.removeChild(welcomeEl);

        const wrap = document.createElement('div');
        wrap.className = 'ai-msg ' + role;

        const avatar = document.createElement('div');
        avatar.className = 'ai-msg-avatar';
        avatar.innerHTML = role === 'assistant'
            ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>'
            : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
        wrap.appendChild(avatar);

        const bubble = document.createElement('div');
        bubble.className = 'ai-msg-bubble';
        bubble.innerHTML = escapeHtml(content).replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');

        const ts = document.createElement('span');
        ts.className = 'ai-msg-ts';
        ts.textContent = timestamp || new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        bubble.appendChild(ts);

        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function showTyping() {
        const wrap = document.createElement('div');
        wrap.className = 'ai-msg assistant';
        wrap.id = 'ai-typing';
        wrap.innerHTML = '<div class="ai-msg-avatar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><div class="ai-typing-indicator"><span></span><span></span><span></span></div>';
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function removeTyping() {
        const el = document.getElementById('ai-typing');
        if (el) el.remove();
    }

    function selectConversation(id) {
        activeConversationId = id;
        renderConversationList(renderedItems, {});
        messagesEl.innerHTML = '';

        fetch(conversationsUrl + '/' + id, { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.messages || !data.messages.length) {
                    showWelcome();
                    return;
                }
                data.messages.forEach(function (m) {
                    appendMessage(m.role, m.content, new Date(m.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }));
                });
            })
            .catch(function () { showWelcome(); });
    }

    function startNewChat() {
        activeConversationId = null;
        showWelcome();
        renderConversationList(conversations, {});
        inputEl.focus();
    }

    function deleteConversation(id) {
        if (!confirm('Delete this conversation? This cannot be undone.')) return;

        fetch(conversationsUrl + '/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
        }).then(function () {
            conversations = conversations.filter(function (c) { return c.id !== id; });
            if (activeConversationId === id) startNewChat();
            else renderConversationList(conversations, {});
        });
    }

    function sendMessage() {
        const text = inputEl.value.trim();
        if (!text) return;

        appendMessage('user', text);
        inputEl.value = '';
        inputEl.disabled = true;
        sendBtn.disabled = true;
        showTyping();

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                message: text,
                conversation_id: activeConversationId,
            }),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                removeTyping();
                appendMessage('assistant', data.response || "Sorry, I couldn't process that.");

                const isNewConversation = activeConversationId === null;
                activeConversationId = data.conversation_id;

                if (isNewConversation) {
                    conversations.unshift({ id: data.conversation_id, title: data.title, updated_at: new Date().toISOString() });
                } else {
                    const conv = conversations.find(function (c) { return c.id === data.conversation_id; });
                    if (conv) {
                        conv.updated_at = new Date().toISOString();
                        conversations = [conv].concat(conversations.filter(function (c) { return c.id !== data.conversation_id; }));
                    }
                }

                if (!isSearching) renderConversationList(conversations, {});
            })
            .catch(function () {
                removeTyping();
                appendMessage('assistant', "Sorry, something went wrong reaching the assistant. Please try again.");
            })
            .finally(function () {
                inputEl.disabled = false;
                sendBtn.disabled = false;
                inputEl.focus();
            });
    }

    function runSearch(query) {
        if (!query) {
            isSearching = false;
            renderConversationList(conversations, {});
            return;
        }

        isSearching = true;
        fetch(searchUrl + '?q=' + encodeURIComponent(query), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                renderConversationList(data.results || [], { emptyText: 'No conversations match "' + query + '".' });
            });
    }

    inputForm.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage();
    });

    newChatBtn.addEventListener('click', startNewChat);

    searchInput.addEventListener('input', function () {
        clearTimeout(searchDebounce);
        const query = searchInput.value.trim();
        searchDebounce = setTimeout(function () { runSearch(query); }, 300);
    });

    mobileToggle.addEventListener('click', function () {
        sidebarEl.classList.toggle('open');
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') sidebarEl.classList.remove('open');
    });

    renderConversationList(conversations, {});
})();
