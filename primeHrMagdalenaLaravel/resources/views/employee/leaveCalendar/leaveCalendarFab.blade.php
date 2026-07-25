{{-- Global floating button → opens the employee's personal Leave & Travel
     calendar as a popup modal (loaded in an iframe via ?embed=1). Included from
     layouts/employee, so it's available on every employee page. --}}
<button type="button" class="ecal-fab" onclick="openEcalModal()" title="My Leave & Travel Calendar" aria-label="Open my Leave & Travel Calendar">
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

<div id="ecalModal" class="ecal-modal-overlay" style="display:none" onclick="closeEcalModal(event)">
    <div class="ecal-modal-panel" onclick="event.stopPropagation()">
        <div class="ecal-modal-head">
            <div class="ecal-modal-head-title">
                <span class="ecal-modal-head-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </span>
                <div class="ecal-modal-head-text">
                    <span class="ecal-modal-head-h3">My Leave &amp; Travel Calendar</span>
                    <span class="ecal-modal-head-sub">PRIME HRIS · My time off this month</span>
                </div>
            </div>
            <button type="button" class="ecal-modal-close" onclick="closeEcalModal()" aria-label="Close">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="ecal-modal-body">
            <div id="ecalLoader" class="ecal-modal-loader">
                <span class="ecal-spinner"></span>
                <p>Loading calendar…</p>
            </div>
            <iframe id="ecalFrame" title="My Leave & Travel Calendar" src="about:blank" loading="lazy"
                    onload="if(this.src.indexOf('about:blank')===-1){document.getElementById('ecalLoader').style.display='none';}"></iframe>
        </div>
    </div>
</div>

<script>
    function openEcalModal() {
        var modal = document.getElementById('ecalModal');
        var frame = document.getElementById('ecalFrame');
        if (frame.getAttribute('src') === 'about:blank') {
            frame.src = "{{ route('employee.leaveCalendar', ['embed' => 1]) }}";
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeEcalModal(event) {
        if (event && event.target !== event.currentTarget) return;
        document.getElementById('ecalModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeEcalModal();
    });
</script>

<style>
    @keyframes ecalOverlayIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes ecalPanelIn { from { opacity: 0; transform: translateY(14px) scale(0.98); } to { opacity: 1; transform: none; } }
    @keyframes ecalSpin { to { transform: rotate(360deg); } }

    .ecal-fab {
        /* Stacked just above the employee chatbot FAB (bottom:28px, z-index:999)
           so the two don't overlap — the calendar sits on top of the chatbot. */
        position: fixed; right: 28px; bottom: 96px; z-index: 997;
        width: 56px; height: 56px; border: none; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: #0b044d; color: #fff; cursor: pointer;
        box-shadow: 0 8px 30px rgba(11, 4, 77, 0.32);
        transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .ecal-fab:hover { background: #150c63; transform: translateY(-2px) scale(1.03); box-shadow: 0 12px 30px rgba(11, 4, 77, 0.35); }

    .ecal-modal-overlay {
        position: fixed; inset: 0; z-index: 3000;
        background: rgba(20, 20, 32, 0.45);
        backdrop-filter: blur(12px) saturate(160%); -webkit-backdrop-filter: blur(12px) saturate(160%);
        display: flex; align-items: center; justify-content: center; padding: 32px;
        animation: ecalOverlayIn 0.24s ease;
    }
    .ecal-modal-panel {
        width: 100%; max-width: 1280px; height: 90vh; position: relative; isolation: isolate;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.58));
        backdrop-filter: blur(30px) saturate(180%); -webkit-backdrop-filter: blur(30px) saturate(180%);
        border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.65);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8), 0 30px 70px rgba(11, 4, 77, 0.22), 0 8px 24px rgba(15, 23, 42, 0.1);
        display: flex; flex-direction: column; overflow: hidden;
        animation: ecalPanelIn 0.38s cubic-bezier(0.32, 0.72, 0, 1);
    }
    .ecal-modal-panel::before {
        content: ''; position: absolute; inset: 0; z-index: -1;
        background:
            radial-gradient(480px 320px at 8% -12%, rgba(99, 102, 241, 0.12), transparent 60%),
            radial-gradient(420px 300px at 100% 0%, rgba(56, 189, 248, 0.1), transparent 60%);
    }
    .ecal-modal-head {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        padding: 0 24px; height: 64px; flex-shrink: 0;
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: saturate(180%) blur(24px); -webkit-backdrop-filter: saturate(180%) blur(24px);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }
    .ecal-modal-head-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .ecal-modal-head-icon {
        width: 36px; height: 36px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #1b1464, #0b044d); border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 4px 12px rgba(11, 10, 77, 0.3);
    }
    .ecal-modal-head-text { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .ecal-modal-head-h3 { font-size: 14px; font-weight: 700; color: #1a1f36; letter-spacing: -0.1px; line-height: 1.2; white-space: nowrap; }
    .ecal-modal-head-sub { font-size: 10.5px; font-weight: 500; color: #9aa1b5; letter-spacing: 0.2px; line-height: 1.2; white-space: nowrap; }
    .ecal-modal-close {
        width: 32px; height: 32px; border-radius: 999px; flex-shrink: 0; border: 1px solid transparent;
        background: rgba(15, 23, 42, 0.05); color: #9aa1b5;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
    }
    .ecal-modal-close:hover { background: rgba(11, 10, 77, 0.1); color: #1a1f36; }
    .ecal-modal-close:active { transform: scale(0.9); }
    .ecal-modal-body { flex: 1; min-height: 0; position: relative; }
    .ecal-modal-body iframe { width: 100%; height: 100%; border: none; display: block; background: transparent; position: relative; z-index: 1; }

    .ecal-modal-loader { position: absolute; inset: 0; z-index: 2; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; background: transparent; }
    .ecal-modal-loader p { margin: 0; font-size: 12.5px; font-weight: 600; color: #7c839d; }
    .ecal-spinner { width: 36px; height: 36px; border-radius: 50%; border: 3px solid rgba(15, 23, 42, 0.1); border-top-color: #0b044d; animation: ecalSpin 0.7s linear infinite; }

    @media (prefers-reduced-motion: reduce) { .ecal-modal-overlay, .ecal-modal-panel { animation: none; } }
    @media (max-width: 640px) {
        .ecal-fab { right: 20px; bottom: 84px; }
        .ecal-modal-overlay { padding: 10px; }
        .ecal-modal-panel { height: 94vh; border-radius: 18px; }
    }
</style>
