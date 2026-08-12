// Full-page AI Assistant (admin/employee/mayor). All rendering happens here —
// the Blade partial only outputs the initial conversation list as JSON so the
// list-rendering logic lives in exactly one place (see page.blade.php).
(function () {
    const page = document.getElementById('ai-page');
    if (!page) return;

    const sendUrl = page.dataset.sendUrl;
    const searchUrl = page.dataset.searchUrl;
    const conversationsUrl = page.dataset.conversationsUrl;
    const exportUrl = page.dataset.exportUrl;
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

    function appendMessage(role, content, timestamp, attachments) {
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

        // Files first — when someone asks for a photo or a contract, the file
        // is the answer and the prose is the caption. Then charts, the table,
        // and the export button.
        if (attachments) {
            // The server withholds a stored payload it cannot re-verify against
            // the reader's current access; say so rather than showing a reply
            // whose table has silently vanished.
            if (attachments.withheld) {
                bubble.appendChild(buildNote(
                    'The table and files from this answer are not shown — they could not be re-checked against your current access. Ask again to see what you can view now.'
                ));
            } else {
                if (attachments.files && attachments.files.length) {
                    bubble.appendChild(buildFiles(attachments.files));
                }

                if (attachments.chart_svg) {
                    attachments.chart_svg.forEach(function (svg) {
                        bubble.appendChild(buildChart(svg));
                    });
                }

                if (attachments.table && attachments.data && attachments.data.length) {
                    bubble.appendChild(buildTable(attachments.table, attachments.data));
                }

                if (attachments.data_omitted) {
                    bubble.appendChild(buildNote('The rows behind this answer were too large to keep. Ask again for the current figures.'));
                }

                if (attachments.export_token && exportUrl) {
                    bubble.appendChild(buildExportButton(attachments.export_token));
                }

                if (attachments.follow_ups && attachments.follow_ups.length) {
                    bubble.appendChild(buildFollowUps(attachments.follow_ups));
                }
            }

            // Only replayed turns carry a generation time. Figures in HR move,
            // so an old table is labelled with the day it was true.
            if (attachments.generated_at) {
                bubble.appendChild(buildSnapshotNote(attachments.generated_at));
            }
        }

        const ts = document.createElement('span');
        ts.className = 'ai-msg-ts';
        ts.textContent = timestamp || new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        bubble.appendChild(ts);

        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    /**
     * File results, rendered as cards. Images preview inline and open in a
     * lightbox; everything else gets an icon card that opens or downloads.
     *
     * Every URL here came from the server as a reference to a database row, so
     * a card can only ever point at a real record the user is allowed to see —
     * the endpoint re-checks that on click.
     */
    function buildFiles(files) {
        const grid = document.createElement('div');
        grid.className = 'ai-files';

        files.forEach(function (file) {
            grid.appendChild(file.is_image ? buildImageCard(file) : buildDocCard(file));
        });

        return grid;
    }

    function buildImageCard(file) {
        const card = document.createElement('figure');
        card.className = 'ai-file ai-file-image';

        const img = document.createElement('img');
        img.src = file.url;
        img.alt = file.label || file.name;
        img.loading = 'lazy';
        // A record can point at a file the browser cannot render; say so rather
        // than leaving a broken image icon in the thread. The actions below stay
        // put, because a preview the browser refuses to draw is still a file the
        // user is entitled to download — removing them would strand it.
        img.addEventListener('error', function () {
            card.classList.add('is-broken');
            img.remove();
        });
        img.addEventListener('click', function () { openLightbox(file); });

        const caption = document.createElement('figcaption');
        caption.appendChild(fileMeta(file));
        // An image used to be viewable but not savable: the thumbnail opened a
        // lightbox and that was all, so download_url went unused on exactly the
        // cards people most often want to keep — ID scans and photos.
        caption.appendChild(fileActions(file));

        card.appendChild(img);
        card.appendChild(caption);
        return card;
    }

    /**
     * Open (inline, new tab) and Download (forced attachment) for any card.
     * Both point at AiFileController, which re-checks access per request.
     */
    function fileActions(file) {
        const actions = document.createElement('div');
        actions.className = 'ai-file-actions';
        actions.appendChild(fileLink(file.url, 'Open', '_blank', false));
        actions.appendChild(fileLink(file.download_url, 'Download', null, true));
        return actions;
    }

    function buildDocCard(file) {
        const card = document.createElement('div');
        card.className = 'ai-file ai-file-doc';

        const icon = document.createElement('span');
        icon.className = 'ai-file-icon ai-file-icon-' + (file.type || 'bin');
        icon.textContent = (file.type || 'file').toUpperCase().slice(0, 4);

        const body = document.createElement('div');
        body.className = 'ai-file-body';
        body.appendChild(fileMeta(file));

        card.appendChild(icon);
        card.appendChild(body);
        card.appendChild(fileActions(file));
        return card;
    }

    function fileMeta(file) {
        const meta = document.createElement('div');
        meta.className = 'ai-file-meta';

        const name = document.createElement('strong');
        name.className = 'ai-file-name';
        name.textContent = file.label || file.name;
        name.title = file.name;
        meta.appendChild(name);

        const detail = [file.employee_name, formatFileDate(file.uploaded_at), file.size]
            .filter(Boolean)
            .join(' · ');

        if (detail) {
            const sub = document.createElement('span');
            sub.className = 'ai-file-sub';
            sub.textContent = detail;
            meta.appendChild(sub);
        }

        return meta;
    }

    function fileLink(href, label, target, download) {
        const link = document.createElement('a');
        link.className = 'ai-file-link';
        link.href = href;
        link.textContent = label;
        if (target) {
            link.target = target;
            link.rel = 'noopener';
        }
        if (download) link.setAttribute('download', '');
        return link;
    }

    function formatFileDate(value) {
        if (!value) return '';
        const date = new Date(value);
        return isNaN(date.getTime())
            ? ''
            : date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    }

    /** Full-size view for image results, dismissed with a click or Escape. */
    function openLightbox(file) {
        const overlay = document.createElement('div');
        overlay.className = 'ai-lightbox';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-label', file.label || file.name);

        const img = document.createElement('img');
        img.src = file.url;
        img.alt = file.label || file.name;

        const caption = document.createElement('div');
        caption.className = 'ai-lightbox-caption';

        const title = document.createElement('span');
        title.textContent = [file.label, file.employee_name].filter(Boolean).join(' — ');
        caption.appendChild(title);

        const actions = fileActions(file);
        actions.className = 'ai-file-actions ai-lightbox-actions';
        // The overlay closes on click, so a click that lands on a link would
        // dismiss the lightbox and swallow the navigation with it.
        actions.addEventListener('click', function (e) { e.stopPropagation(); });
        caption.appendChild(actions);

        function close() {
            overlay.remove();
            document.removeEventListener('keydown', onKey);
        }

        function onKey(e) {
            if (e.key === 'Escape') close();
        }

        overlay.addEventListener('click', close);
        document.addEventListener('keydown', onKey);

        overlay.appendChild(img);
        overlay.appendChild(caption);
        document.body.appendChild(overlay);
    }

    function buildNote(text) {
        const note = document.createElement('p');
        note.className = 'ai-msg-note';
        note.textContent = text;
        return note;
    }

    /**
     * Marks a replayed attachment with when its figures were pulled. Headcount,
     * balances, and pending approvals all move; a table reopened next month is
     * a record of that day, not of today.
     */
    function buildSnapshotNote(iso) {
        const date = new Date(iso);
        const note = document.createElement('p');
        note.className = 'ai-msg-note ai-msg-snapshot';
        note.textContent = isNaN(date.getTime())
            ? 'Saved from an earlier answer.'
            : 'Data as of ' + date.toLocaleString('en-US', {
                month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
            });
        return note;
    }

    /**
     * Chart SVG is generated server-side, so it renders identically in a PDF.
     * Here it is wrapped in the dashboard's chart-card shell: a titled header
     * over the plot. The title rides on the SVG as `data-chart-title` (see
     * ChartRenderer) rather than in a parallel payload field, so a replayed
     * turn — which is re-rendered from its stored spec and ships the SVG alone
     * — gets its header back too.
     */
    function buildChart(svg) {
        const holder = document.createElement('div');
        holder.className = 'ai-chart';

        const body = document.createElement('div');
        body.className = 'ai-chart-body';
        body.innerHTML = svg;

        const root = body.querySelector('svg');
        const title = root ? root.getAttribute('data-chart-title') : null;
        const sub = root ? root.getAttribute('data-chart-sub') : null;

        if (title) holder.appendChild(buildCardHead(title, sub));
        holder.appendChild(body);

        return holder;
    }

    /** The .table-header / .chart-header block shared by both card types. */
    function buildCardHead(title, sub) {
        const head = document.createElement('div');
        head.className = 'ai-card-head';

        const block = document.createElement('div');
        const heading = document.createElement('p');
        heading.className = 'ai-card-title';
        heading.textContent = title;
        block.appendChild(heading);

        if (sub) {
            const caption = document.createElement('p');
            caption.className = 'ai-card-sub';
            caption.textContent = sub;
            block.appendChild(caption);
        }

        head.appendChild(block);
        return head;
    }

    /**
     * Suggested next questions, as chips that ask themselves when clicked.
     *
     * Discoverability is the whole point: most people do not know what to ask
     * an HR assistant, and a list of real questions teaches that faster than
     * any help text. Only the latest turn keeps its chips — see sendMessage,
     * which clears older ones so the thread does not fill with stale prompts.
     */
    function buildFollowUps(items) {
        const holder = document.createElement('div');
        holder.className = 'ai-followups';

        items.forEach(function (question) {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'ai-followup-chip';
            chip.textContent = question;
            chip.addEventListener('click', function () {
                if (inputEl.disabled) return;
                inputEl.value = question;
                sendMessage();
            });
            holder.appendChild(chip);
        });

        return holder;
    }

    function buildExportButton(token) {
        const link = document.createElement('a');
        link.className = 'ai-export-btn';
        link.href = exportUrl.replace('__TOKEN__', encodeURIComponent(token));
        link.setAttribute('download', '');
        link.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg><span>Download PDF</span>';
        return link;
    }

    /**
     * Renders rows as a real table. Long result sets collapse to the first
     * 25 rows behind a "show all" toggle so one query cannot flood the thread.
     */
    function buildTable(spec, rows) {
        const COLLAPSE_AT = 25;

        const holder = document.createElement('div');
        holder.className = 'ai-table-wrap';

        if (spec.title) {
            holder.appendChild(buildCardHead(spec.title, rowCount(rows.length)));
        }

        const scroller = document.createElement('div');
        scroller.className = 'ai-table-scroll';

        const table = document.createElement('table');
        table.className = 'ai-table';

        const columns = (spec.columns && spec.columns.length)
            ? spec.columns
            : Object.keys(rows[0]).map(function (k) { return { key: k, label: k }; });

        const thead = document.createElement('thead');
        const headRow = document.createElement('tr');
        columns.forEach(function (col) {
            const th = document.createElement('th');
            th.textContent = col.label || col.key;
            if (col.align === 'right') th.className = 'num';
            headRow.appendChild(th);
        });
        thead.appendChild(headRow);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        rows.forEach(function (row, index) {
            const tr = document.createElement('tr');
            if (index >= COLLAPSE_AT) tr.className = 'ai-row-hidden';

            columns.forEach(function (col) {
                const td = document.createElement('td');
                const value = row[col.key];
                td.textContent = formatCell(value, col.format);
                if (col.align === 'right') td.className = 'num';
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        scroller.appendChild(table);
        holder.appendChild(scroller);

        // Footer strip, the dashboard's .table-footer: how much of the result
        // set is on screen (plus any totals) on the left, the control that
        // changes it on the right.
        const collapsed = rows.length > COLLAPSE_AT;
        const totals = spec.totals && Object.keys(spec.totals).length ? spec.totals : null;

        if (collapsed || totals) {
            const foot = document.createElement('div');
            foot.className = 'ai-table-foot';

            const left = document.createElement('div');
            left.className = 'ai-table-foot-left';

            if (collapsed) {
                const count = document.createElement('span');
                count.className = 'ai-table-count';
                count.innerHTML = 'Showing <strong>' + COLLAPSE_AT + '</strong> of <strong>'
                    + rows.length + '</strong> rows';
                left.appendChild(count);
            }

            if (totals) {
                const chips = document.createElement('div');
                chips.className = 'ai-table-totals';
                Object.keys(totals).forEach(function (label) {
                    const chip = document.createElement('span');
                    chip.innerHTML = '<em>' + escapeHtml(label) + '</em> ' + escapeHtml(String(totals[label]));
                    chips.appendChild(chip);
                });
                left.appendChild(chips);
            }

            foot.appendChild(left);

            if (collapsed) {
                const count = left.querySelector('.ai-table-count');
                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'ai-table-toggle';
                toggle.textContent = 'Show all ' + rows.length + ' rows';
                toggle.addEventListener('click', function () {
                    const expanded = holder.classList.toggle('expanded');
                    toggle.textContent = expanded
                        ? 'Show fewer rows'
                        : 'Show all ' + rows.length + ' rows';
                    count.innerHTML = expanded
                        ? 'Showing all <strong>' + rows.length + '</strong> rows'
                        : 'Showing <strong>' + COLLAPSE_AT + '</strong> of <strong>'
                            + rows.length + '</strong> rows';
                });
                foot.appendChild(toggle);
            }

            holder.appendChild(foot);
        }

        return holder;
    }

    /** Sub-line under a generated table's title: "48 rows", "1 row". */
    function rowCount(total) {
        return total + (total === 1 ? ' row' : ' rows');
    }

    function formatCell(value, format) {
        if (value === null || value === undefined || value === '') return '—';
        if (format === 'money' && !isNaN(value)) {
            return Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        if (typeof value === 'boolean') return value ? 'Yes' : 'No';
        return String(value);
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
                    appendMessage(
                        m.role,
                        m.content,
                        new Date(m.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
                        m.attachments
                    );
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

        // Suggestions belong to the newest turn only; leaving them behind would
        // stack a thread's worth of chips that no longer follow from anything.
        messagesEl.querySelectorAll('.ai-followups').forEach(function (el) { el.remove(); });

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
            body: JSON.stringify(Object.assign(
                { message: text },
                activeConversationId != null ? { conversation_id: activeConversationId } : { reset: true }
            )),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                removeTyping();
                appendMessage('assistant', data.response || "Sorry, I couldn't process that.", null, {
                    table: data.table,
                    data: data.data,
                    files: data.files,
                    chart_svg: data.chart_svg,
                    export_token: data.export_token,
                    follow_ups: data.follow_ups,
                });

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
