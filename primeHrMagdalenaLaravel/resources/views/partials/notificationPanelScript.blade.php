{{-- The notification panel's behaviour, shared by the admin and employee
     panels — they differ only in which audience they poll for. Kept in one
     file because the two must not drift: the employee panel was already a
     copy of this one, and copies are how one of them silently stops
     refreshing. Expects $audience ('admin' or 'employee'). --}}
<script>
/* The panel is server-rendered once, so without this it only ever showed the
   notifications that existed when the page loaded. It now polls
   /api/notifications/feed, which re-renders the same Blade partial the first
   paint came from, and refreshes on the moments a stale list is most visible:
   opening the panel, and coming back to the tab. */
(function () {
    const AUDIENCE = @json($audience);
    const POLL_MS = 15000;

    const panel   = document.getElementById('notifPanel');
    const body    = document.getElementById('notifBody');
    const dot     = document.getElementById('notifDot');
    const countEl = document.getElementById('unreadCount');

    let timer = null;
    let inFlight = false;
    let lastHtml = null;

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function setUnread(count) {
        countEl.textContent = count;
        dot.textContent = count > 9 ? '9+' : count;
        dot.classList.toggle('active', count > 0);
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
            // An expired session answers with the login page, not JSON. Keep the
            // last known list rather than blanking the panel.
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

    function start() {
        if (timer === null) timer = setInterval(refresh, POLL_MS);
    }

    function stop() {
        if (timer !== null) { clearInterval(timer); timer = null; }
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stop();
        } else {
            refresh();
            start();
        }
    });

    start();

    window.toggleNotif = function () {
        panel.classList.toggle('open');
        // Whatever the poll interval is, the list is current the moment it is
        // opened.
        if (panel.classList.contains('open')) refresh();
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
            body.querySelectorAll('.notif-card.new').forEach(card => card.classList.remove('new'));
            setUnread(0);
            lastHtml = null;   // the stored markup still says unread
            refresh();
        })
        .catch(() => {});
    };

    // Delegated, so cards replaced by a poll stay clickable — and so the link
    // never has to survive a trip through an inline onclick attribute.
    body.addEventListener('click', (e) => {
        const card = e.target.closest('.notif-card');
        if (!card) return;

        const id = card.dataset.notifId;
        const link = card.dataset.notifLink;
        if (!id) return;

        fetch(`/api/notifications/${id}/mark-read`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            credentials: 'same-origin',
        })
        .then(() => {
            if (link) {
                window.location.href = link;
                return;
            }
            // No link to follow, so the panel stays open: show the read state here.
            card.classList.remove('new');
            lastHtml = null;
            refresh();
        })
        .catch(() => {});
    });

    document.addEventListener('click', (e) => {
        const wrap = document.querySelector('.notif-wrap');
        if (wrap && !wrap.contains(e.target)) panel.classList.remove('open');
    });
})();
</script>
