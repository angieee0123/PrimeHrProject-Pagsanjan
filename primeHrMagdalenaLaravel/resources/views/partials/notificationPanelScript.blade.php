{{-- The notification panel's behaviour, shared by all three bells — they
     differ only in which audience they poll for. Expects $audience. --}}
<script>
/* The panel is server-rendered once, so without this it only ever shows the
   notifications that existed when the page loaded. It polls
   /api/notifications/feed, which re-renders the same Blade partial the first
   paint came from, and refreshes on the moments a stale list is most visible:
   opening the panel, and coming back to the tab.

   What this script deliberately does *not* do is navigate. Cards are ordinary
   links to /notifications/{id}/open, so marking-read-then-going-somewhere is
   one server round trip that re-checks who may see the destination — rather
   than a fetch followed by a client-side jump to a URL read out of the DOM. */
(function () {
    const AUDIENCE = @json($audience);
    const POLL_MS  = {{ max(5, (int) config('notifications.poll_seconds', 20)) * 1000 }};

    const panel    = document.getElementById('notifPanel');
    const body     = document.getElementById('notifBody');
    const dot      = document.getElementById('notifDot');
    const button   = document.getElementById('notifBtn');
    const summary  = document.getElementById('notifSummary');
    const clearBtn = document.getElementById('notifClearBtn');

    if (!panel || !body || !dot) return;

    let timer = null;
    let inFlight = false;
    let lastHtml = null;

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function setUnread(count) {
        dot.textContent = count > 99 ? '99+' : count;   // same cap as the sidebar row's badge
        dot.classList.toggle('active', count > 0);

        if (button) {
            button.setAttribute('aria-label', count > 0
                ? 'Notifications, ' + count + ' unread'
                : 'Notifications');
        }

        if (summary) {
            // The three phrasings are authored in Blade beside the server-
            // rendered one, so the polled sentence and the first-paint
            // sentence cannot drift.
            summary.textContent = count === 0
                ? summary.dataset.zero
                : (count === 1 ? summary.dataset.one : summary.dataset.many.replace(':count', count));
        }

        if (clearBtn) clearBtn.disabled = count === 0;
    }

    async function refresh() {
        // A hidden tab is not watching, and overlapping polls would fight over
        // the same innerHTML.
        if (inFlight || document.hidden) return;
        inFlight = true;
        try {
            const res = await fetch(`/api/notifications/feed?audience=${AUDIENCE}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            // An expired session answers with the login page, not JSON. Keep
            // the last known list rather than blanking the panel.
            if (!res.ok) return;
            const data = await res.json();
            setUnread(data.unread_count);
            if (typeof data.html === 'string' && data.html !== lastHtml) {
                // Only touch the DOM when something actually changed, so a poll
                // does not scroll the list out from under whoever is reading it.
                const scrolled = body.scrollTop;
                body.innerHTML = data.html;
                body.scrollTop = scrolled;
                lastHtml = data.html;
            }
        } catch (e) {
            /* offline or mid-deploy: the panel keeps showing what it last had */
        } finally {
            inFlight = false;
        }
    }

    function start() { if (timer === null) timer = setInterval(refresh, POLL_MS); }
    function stop()  { if (timer !== null) { clearInterval(timer); timer = null; } }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stop();
        } else {
            refresh();
            start();
        }
    });

    start();

    function setOpen(open) {
        panel.classList.toggle('open', open);
        if (button) button.setAttribute('aria-expanded', open ? 'true' : 'false');
        // Whatever the poll interval is, the list is current the moment it is
        // opened.
        if (open) refresh();
    }

    window.toggleNotif = function () {
        setOpen(!panel.classList.contains('open'));
    };

    window.markAllAsRead = function () {
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            credentials: 'same-origin',
            body: JSON.stringify({ audience: AUDIENCE }),
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;
            body.querySelectorAll('.notif-card.is-unread').forEach(card => card.classList.remove('is-unread'));
            setUnread(0);
            lastHtml = null;   // the stored markup still says unread
            refresh();
        })
        .catch(() => {});
    };

    document.addEventListener('click', (e) => {
        const wrap = document.querySelector('.notif-wrap');
        if (wrap && !wrap.contains(e.target)) setOpen(false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && panel.classList.contains('open')) {
            setOpen(false);
            if (button) button.focus();
        }
    });
})();
</script>
