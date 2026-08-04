{{-- Global floating button → opens the Leave & Travel Calendar as a popup modal
     over the current page (calendar loads in an iframe via ?embed=1). Included
     from layouts/app (the admin layout), so it's available on every admin page.
     Same size as the chatbot FAB (56x56 / 22px icon), stacked just above it. --}}
<button type="button" class="leave-cal-fab" onclick="openLeaveCalModal()" title="Leave & Travel Calendar" aria-label="Open Leave & Travel Calendar">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
        <circle cx="8" cy="15" r="1.3" fill="currentColor" stroke="none"/>
        <circle cx="12" cy="15" r="1.3" fill="currentColor" stroke="none"/>
        <circle cx="16" cy="15" r="1.3" fill="currentColor" stroke="none"/>
    </svg>
</button>

{{-- Popup modal shell — the calendar renders inside the iframe. --}}
<div id="leaveCalModal" class="lc-modal-overlay" style="display:none" onclick="closeLeaveCalModal(event)">
    <div class="lc-modal-panel" onclick="event.stopPropagation()">
        <div class="lc-modal-head">
            <div class="lc-modal-head-title">
                <span class="lc-modal-head-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </span>
                <div class="lc-modal-head-text">
                    <span class="lc-modal-head-h3">Leave &amp; Travel Calendar</span>
                    <span class="lc-modal-head-sub">PRIME HRIS · Who is out on leave or travel</span>
                </div>
            </div>
            <button type="button" class="lc-modal-close" onclick="closeLeaveCalModal()" aria-label="Close">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="lc-modal-body">
            <div id="leaveCalLoader" class="lc-modal-loader">
                <span class="lc-spinner"></span>
                <p>Loading calendar…</p>
            </div>
            <iframe id="leaveCalFrame" title="Leave & Travel Calendar" src="about:blank" loading="lazy"
                    onload="leaveCalFrameLoaded(this)"></iframe>
        </div>
    </div>
</div>

<script>
    // Root-relative on purpose: an absolute URL built from APP_URL (127.0.0.1)
    // is a different host than a browser sitting on localhost, so the session
    // cookie would not be sent and every open would land on /login.
    var LEAVE_CAL_PATH = "{{ route('admin.leaveCalendar', [], false) }}";
    var LEAVE_CAL_SRC  = "{{ route('admin.leaveCalendar', ['embed' => 1], false) }}";

    function openLeaveCalModal() {
        var modal = document.getElementById('leaveCalModal');
        var frame = document.getElementById('leaveCalFrame');
        // Reload every time so the month is current and an expired session is
        // caught on open rather than showing a stale calendar.
        document.getElementById('leaveCalLoader').style.display = '';
        frame.src = LEAVE_CAL_SRC;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // The iframe renders whatever the server returns. If `auth` redirected the
    // request (expired session), that response is the full portal/login page —
    // send the whole window there instead of framing the portal inside the modal.
    function leaveCalFrameLoaded(frame) {
        var loc;
        try {
            loc = frame.contentWindow.location;
        } catch (e) {
            return;   // cross-origin: nothing we can inspect
        }
        if (loc.href === 'about:blank') return;

        if (loc.pathname !== LEAVE_CAL_PATH) {
            window.location.href = loc.href;
            return;
        }
        document.getElementById('leaveCalLoader').style.display = 'none';
    }
    function closeLeaveCalModal(event) {
        if (event && event.target !== event.currentTarget) return;
        document.getElementById('leaveCalModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLeaveCalModal();
    });
</script>

<style>
    .leave-cal-fab {
        position: fixed;
        right: 28px;
        bottom: 96px;              /* stacked above the chatbot FAB (bottom:28px) */
        z-index: 997;              /* below chatbot toggle (999) / panel (998) */
        width: 56px;
        height: 56px;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--theme-primary);
        color: #fff;
        cursor: pointer;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
        transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .leave-cal-fab:hover {
        background: var(--theme-primary-2);
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.28);
    }

    /* ---- Popup modal — matches the Detailed DTR modal's glass treatment ---- */
    @keyframes lcOverlayIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes lcPanelIn {
        from { opacity: 0; transform: translateY(14px) scale(0.98); }
        to   { opacity: 1; transform: none; }
    }
    @keyframes lcSpin { to { transform: rotate(360deg); } }

    .lc-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 3000;             /* above sidebar (200) and chatbot (999) */
        background: rgba(20, 20, 32, 0.45);
        backdrop-filter: blur(12px) saturate(160%);
        -webkit-backdrop-filter: blur(12px) saturate(160%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px;
        animation: lcOverlayIn 0.24s ease;
    }
    .lc-modal-panel {
        width: 100%;
        max-width: 1280px;
        height: 90vh;
        position: relative;
        isolation: isolate;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.58));
        backdrop-filter: blur(30px) saturate(180%);
        -webkit-backdrop-filter: blur(30px) saturate(180%);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.65);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.8),
            0 30px 70px rgba(11, 4, 77, 0.22),
            0 8px 24px rgba(15, 23, 42, 0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: lcPanelIn 0.38s cubic-bezier(0.32, 0.72, 0, 1);
    }
    /* Decorative colour wash, same idea as the DTR modal */
    .lc-modal-panel::before {
        content: '';
        position: absolute; inset: 0; z-index: -1;
        background:
            radial-gradient(480px 320px at 8% -12%, rgba(99, 102, 241, 0.12), transparent 60%),
            radial-gradient(420px 300px at 100% 0%, rgba(56, 189, 248, 0.1), transparent 60%);
    }
    /* ── Header · light, minimal ── */
    .lc-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 0 24px;
        height: 64px;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: saturate(180%) blur(24px);
        -webkit-backdrop-filter: saturate(180%) blur(24px);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }
    .lc-modal-head-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .lc-modal-head-icon {
        width: 36px; height: 36px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--theme-primary-2), var(--theme-primary));
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    .lc-modal-head-text { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .lc-modal-head-h3 { font-size: 14px; font-weight: 700; color: #1a1f36; letter-spacing: -0.1px; line-height: 1.2; white-space: nowrap; }
    .lc-modal-head-sub { font-size: 10.5px; font-weight: 500; color: #9aa1b5; letter-spacing: 0.2px; line-height: 1.2; white-space: nowrap; }
    .lc-modal-close {
        width: 32px; height: 32px; border-radius: 999px; flex-shrink: 0;
        border: 1px solid transparent;
        background: rgba(15, 23, 42, 0.05); color: #9aa1b5;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
    }
    .lc-modal-close:hover { background: rgba(11, 10, 77, 0.1); color: #1a1f36; }
    .lc-modal-close:active { transform: scale(0.9); }
    .lc-modal-body { flex: 1; min-height: 0; position: relative; }
    .lc-modal-body iframe {
        width: 100%; height: 100%;
        border: none; display: block; background: transparent;
        position: relative; z-index: 1;
    }

    /* Loading state shown until the iframe calendar finishes loading */
    .lc-modal-loader {
        position: absolute; inset: 0; z-index: 2;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 14px;
        background: transparent;
    }
    .lc-modal-loader p { margin: 0; font-size: 12.5px; font-weight: 600; color: #7c839d; }
    .lc-spinner {
        width: 36px; height: 36px; border-radius: 50%;
        border: 3px solid rgba(15, 23, 42, 0.1);
        border-top-color: var(--theme-primary);
        animation: lcSpin 0.7s linear infinite;
    }

    @media (prefers-reduced-motion: reduce) {
        .lc-modal-overlay, .lc-modal-panel { animation: none; }
    }
    @media (max-width: 640px) {
        .leave-cal-fab { right: 20px; bottom: 84px; }
        .lc-modal-overlay { padding: 10px; }
        .lc-modal-panel { height: 94vh; border-radius: 18px; }
    }
</style>
