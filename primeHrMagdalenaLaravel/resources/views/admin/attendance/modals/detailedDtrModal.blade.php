<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay ddtr-overlay" style="display: none;" onclick="closeDetailedDTRModal()">
    <div class="ddtr-modal" onclick="event.stopPropagation()" role="dialog" aria-modal="true" aria-labelledby="ddtrModalTitle">

        {{-- ── HEADER ── --}}
        <div class="ddtr-header">
            {{-- Left: branding --}}
            <div class="ddtr-header-brand">
                <div class="ddtr-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="ddtr-header-titles">
                    <span class="ddtr-title" id="ddtrModalTitle">Detailed Time Record</span>
                    <span class="ddtr-subtitle">PRIME HRIS · Attendance</span>
                </div>
                <span class="ddtr-period-pill" id="detailedPeriod">{{ $periodDisplay }}</span>
            </div>

            {{-- Right: close --}}
            <button class="ddtr-close" onclick="closeDetailedDTRModal()" aria-label="Close">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- ── SCROLLABLE BODY ── --}}
        <div class="ddtr-body">

            {{-- ── KPI CARDS ── --}}
            <section class="ddtr-kpis">
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon green">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Present</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiPresent">0</strong>
                        <span class="ddtr-kpi-sub" id="detailedKpiPresentSub">0 days</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon red">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Absent</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiAbsent">0</strong>
                        <span class="ddtr-kpi-sub">days missed</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon amber">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Late</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiLate">0</strong>
                        <span class="ddtr-kpi-sub" id="detailedKpiLateSub">0 min total</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon blue">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Leave</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiLeave">0</strong>
                        <span class="ddtr-kpi-sub">approved days</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon rose">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Undertime</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiUndertime">0</strong>
                        <span class="ddtr-kpi-sub">total</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon purple">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><line x1="22" y1="2" x2="18" y2="6"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Overtime</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiOvertime">0</strong>
                        <span class="ddtr-kpi-sub">extra hours</span>
                    </div>
                </div>
            </section>

            {{-- ── FILTER TOOLBAR ── --}}
            <div class="ddtr-toolbar">
                <div class="ddtr-fld">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <input type="date" id="detailedStartDate" class="ddtr-input" aria-label="Start date">
                </div>
                <span class="ddtr-sep">to</span>
                <div class="ddtr-fld">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <input type="date" id="detailedEndDate" class="ddtr-input" aria-label="End date">
                </div>

                <div class="ddtr-toolbar-divider"></div>

                {{-- View dropdown --}}
                <div class="ddtr-view-wrap" id="ddtrViewWrap">
                    <button class="ddtr-view-btn" id="ddtrViewBtn" onclick="toggleDdtrDropdown()" aria-haspopup="true">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <span id="ddtrViewLabel">All Records</span>
                        <svg class="ddtr-caret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    <div class="ddtr-dropdown" id="ddtrDropdown">
                        <div class="ddtr-dd-section">Show</div>
                        <button class="ddtr-dd-item active" data-chip="all">All Records</button>
                        <div class="ddtr-dd-sep"></div>
                        <div class="ddtr-dd-section">By Day</div>
                        <button class="ddtr-dd-item" data-chip="mon">Mondays only</button>
                        <button class="ddtr-dd-item" data-chip="tue">Tuesdays only</button>
                        <button class="ddtr-dd-item" data-chip="wed">Wednesdays only</button>
                        <button class="ddtr-dd-item" data-chip="thu">Thursdays only</button>
                        <button class="ddtr-dd-item" data-chip="fri">Fridays only</button>
                        <button class="ddtr-dd-item" data-chip="weekdays">Weekdays (Mon–Fri)</button>
                        <button class="ddtr-dd-item" data-chip="weekend">Weekends only</button>
                        <div class="ddtr-dd-sep"></div>
                        <div class="ddtr-dd-section">By Status</div>
                        <button class="ddtr-dd-item" data-chip="present">
                            <span class="ddtr-dd-dot" style="background:#2fa860"></span>Present
                        </button>
                        <button class="ddtr-dd-item" data-chip="absent">
                            <span class="ddtr-dd-dot" style="background:#e5484d"></span>Absent
                        </button>
                        <button class="ddtr-dd-item" data-chip="late">
                            <span class="ddtr-dd-dot" style="background:#eba417"></span>Late
                        </button>
                        <button class="ddtr-dd-item" data-chip="leave">
                            <span class="ddtr-dd-dot" style="background:#4F7CFF"></span>On Leave
                        </button>
                        <button class="ddtr-dd-item" data-chip="incomplete">
                            <span class="ddtr-dd-dot" style="background:#7C5CFF"></span>Incomplete
                        </button>
                    </div>

                </div>

                <div class="ddtr-toolbar-actions">
                    <button class="ddtr-btn-solid" onclick="loadDetailedDTR()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Apply
                    </button>
                    <button class="ddtr-btn-ghost" onclick="exportDetailedDTR()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                </div>
            </div>

            {{-- ── TABLE ── --}}
            <div class="ddtr-table-card">
                <div id="detailedDTRLoading" class="ddtr-loading">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#9aa1b5" stroke-width="2.5" style="animation:spin 1s linear infinite">
                        <circle cx="12" cy="12" r="10" opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" opacity=".75"/>
                    </svg>
                    <p>Loading attendance records…</p>
                </div>

                <div class="ddtr-table-scroll">
                    <table class="detailed-dtr-table" id="detailedDTRTable" style="display:none;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>AM <span class="ddtr-th-hint">8:00 – 12:00</span></th>
                                <th>PM <span class="ddtr-th-hint">1:00 – 5:00</span></th>
                                <th>OT</th>
                                <th>Undertime</th>
                                <th>Late</th>
                                <th>Total Hrs</th>
                                <th>Accredited Hrs</th>
                                <th>Leave Deduction</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="detailedDTRBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── FOOTER ── --}}
        <div class="ddtr-footer">
            <button class="ddtr-btn-ghost" onclick="closeDetailedDTRModal()">Close</button>
        </div>
    </div>
</div>

<style>
/* ══════════════ DETAILED DTR MODAL — premium refinement ══════════════
   8px spacing system · light header · soft-badge status system
   Every class emitted by adminAttendance.js is styled below. */
.ddtr-overlay {
    --pri: #0B0A4D;
    --pri-soft: #eef0fb;
    --card: #FFFFFF;
    --line: #e9eaf2;
    --ink: #1a1f36;
    --muted: #697086;
    --faint: #9aa1b5;
    --bg: #f8f9fc;
    padding: 32px;
    background: rgba(20, 20, 32, .38);
    backdrop-filter: blur(9px) saturate(150%);
    -webkit-backdrop-filter: blur(9px) saturate(150%);
}
.ddtr-modal {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 1400px;
    height: 90vh;
    background: #fff;
    border-radius: 20px;
    border: 1px solid rgba(15,23,42,.06);
    box-shadow: 0 20px 50px rgba(15, 23, 42, .14), 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    animation: ddtrIn .38s cubic-bezier(.32,.72,0,1);
}
@keyframes ddtrIn { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) {
    .ddtr-modal, .ddtr-dropdown { animation: none; }
    .ddtr-overlay * { transition: none !important; }
}

/* ── HEADER · light, minimal ── */
.ddtr-header {
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 0 24px;
    height: 64px; flex-shrink: 0;
    background: rgba(255,255,255,.82);
    backdrop-filter: saturate(180%) blur(20px);
    -webkit-backdrop-filter: saturate(180%) blur(20px);
    border-bottom: 1px solid rgba(15,23,42,.06);
}
.ddtr-header-brand {
    display: flex; align-items: center; gap: 12px; min-width: 0;
}
.ddtr-header-icon {
    width: 36px; height: 36px; border-radius: 11px; flex-shrink: 0;
    background: var(--pri); color: #fff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 8px rgba(11,10,77,.2);
}
.ddtr-header-titles { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.ddtr-title {
    font-size: 14px; font-weight: 600; color: var(--ink); margin: 0;
    white-space: nowrap; letter-spacing: -.1px; line-height: 1.2;
}
.ddtr-subtitle {
    font-size: 10.5px; font-weight: 500; color: var(--faint);
    white-space: nowrap; letter-spacing: .2px; line-height: 1.2;
}
.ddtr-period-pill {
    font-size: 11px; font-weight: 600; color: var(--muted);
    background: var(--bg); border: 1px solid var(--line);
    border-radius: 999px; padding: 4px 12px; white-space: nowrap;
    margin-left: 8px;
}
/* Close */
.ddtr-close {
    width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
    border: none; background: transparent;
    color: var(--faint);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .2s cubic-bezier(.4,0,.2,1), color .2s cubic-bezier(.4,0,.2,1), transform .15s ease;
}
.ddtr-close:active { transform: scale(.9); }
.ddtr-close:hover { background: var(--bg); color: var(--ink); }

/* ── BODY (scroll) ── */
.ddtr-body {
    flex: 1 1 0; min-height: 0; overflow-y: auto; overflow-x: hidden;
    padding: 24px;
    background: var(--bg);
    display: flex; flex-direction: column; gap: 16px;
}

/* ── KPI CARDS ── */
.ddtr-kpis {
    display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px;
}
.ddtr-kpi {
    background: #fff; border: 1px solid var(--line); border-radius: 14px;
    box-shadow: 0 1px 2px rgba(15,23,42,.03);
    padding: 16px; display: flex; flex-direction: row; align-items: center; gap: 12px;
    transition: box-shadow .25s cubic-bezier(.4,0,.2,1), border-color .25s cubic-bezier(.4,0,.2,1), transform .25s cubic-bezier(.4,0,.2,1);
}
.ddtr-kpi:hover { border-color: #dcdfeb; box-shadow: 0 8px 20px rgba(15,23,42,.06); transform: translateY(-1px); }
.ddtr-kpi-icon {
    width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.ddtr-kpi-icon.green  { background: #e9f9ef; color: #2fa860; }
.ddtr-kpi-icon.red    { background: #fdedec; color: #e5484d; }
.ddtr-kpi-icon.amber  { background: #fdf3e3; color: #eba417; }
.ddtr-kpi-icon.blue   { background: #eaf1ff; color: #4F7CFF; }
.ddtr-kpi-icon.rose   { background: #fdedf3; color: #e3568b; }
.ddtr-kpi-icon.purple { background: #f2effd; color: #8a6ff0; }
.ddtr-kpi-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.ddtr-kpi-label { font-size: 11px; font-weight: 500; color: var(--muted); line-height: 1.2; }
.ddtr-kpi-val { font-size: 20px; font-weight: 700; color: var(--ink); line-height: 1.1; letter-spacing: -.4px; }
.ddtr-kpi-sub {
    font-size: 10.5px; color: var(--faint); font-weight: 500; line-height: 1.3;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ── FILTER TOOLBAR · unified control bar ── */
.ddtr-toolbar {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    background: #fff; border: 1px solid var(--line); border-radius: 14px;
    box-shadow: 0 1px 2px rgba(15,23,42,.03);
    padding: 8px 16px;
}
.ddtr-toolbar-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.ddtr-toolbar-divider { width: 1px; height: 24px; background: var(--line); margin: 0 4px; }
.ddtr-fld { position: relative; display: flex; align-items: center; }
.ddtr-fld > svg { position: absolute; left: 10px; color: var(--faint); pointer-events: none; }
.ddtr-input {
    height: 36px; border: 1px solid var(--line); border-radius: 9px;
    background: #fff; color: var(--ink); font-size: 12px; font-family: 'Poppins', sans-serif;
    outline: none; padding: 0 10px 0 30px; transition: border-color .2s cubic-bezier(.4,0,.2,1), box-shadow .2s cubic-bezier(.4,0,.2,1); width: 140px;
}
.ddtr-input:hover { border-color: #cdd2e3; }
.ddtr-input:focus { border-color: var(--pri); box-shadow: 0 0 0 4px rgba(11,10,77,.08); }
.ddtr-sep { font-size: 11px; font-weight: 500; color: var(--faint); padding: 0 2px; }
.ddtr-btn-solid, .ddtr-btn-ghost {
    height: 36px; display: inline-flex; align-items: center; gap: 6px; padding: 0 16px;
    border-radius: 9px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif;
    cursor: pointer; transition: background .2s cubic-bezier(.4,0,.2,1), border-color .2s cubic-bezier(.4,0,.2,1), box-shadow .2s cubic-bezier(.4,0,.2,1), transform .15s ease; white-space: nowrap;
}
.ddtr-btn-solid { background: var(--pri); color: #fff; border: 1px solid var(--pri); box-shadow: 0 1px 3px rgba(11,10,77,.22); }
.ddtr-btn-solid:hover { background: #191670; border-color: #191670; }
.ddtr-btn-solid:active, .ddtr-btn-ghost:active { transform: scale(.97); }
.ddtr-btn-ghost { background: #fff; color: var(--ink); border: 1px solid var(--line); }
.ddtr-btn-ghost:hover { background: var(--bg); border-color: #cdd2e3; }
.ddtr-btn-solid:focus-visible, .ddtr-btn-ghost:focus-visible,
.ddtr-view-btn:focus-visible, .ddtr-close:focus-visible {
    outline: 2px solid var(--pri); outline-offset: 2px;
}

/* ── VIEW DROPDOWN ── */
.ddtr-view-wrap { position: relative; }
.ddtr-view-btn {
    height: 36px; display: inline-flex; align-items: center; gap: 6px; padding: 0 12px;
    border-radius: 9px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif;
    border: 1px solid var(--line); background: #fff; color: var(--ink);
    cursor: pointer; transition: background .2s cubic-bezier(.4,0,.2,1), border-color .2s cubic-bezier(.4,0,.2,1), box-shadow .2s cubic-bezier(.4,0,.2,1); white-space: nowrap;
}
.ddtr-view-btn:hover { background: var(--bg); border-color: #cdd2e3; }
.ddtr-view-btn.open  { border-color: var(--pri); background: #fff; box-shadow: 0 0 0 4px rgba(11,10,77,.08); }
.ddtr-caret { transition: transform .25s cubic-bezier(.4,0,.2,1); color: var(--faint); }
.ddtr-view-btn.open .ddtr-caret { transform: rotate(180deg); }
.ddtr-dropdown {
    display: none; position: absolute; top: calc(100% + 8px); left: 0;
    min-width: 208px; background: rgba(255,255,255,.9); backdrop-filter: saturate(180%) blur(20px); -webkit-backdrop-filter: saturate(180%) blur(20px);
    border: 1px solid var(--line);
    border-radius: 14px; box-shadow: 0 16px 40px rgba(15,23,42,.14), 0 2px 8px rgba(15,23,42,.05);
    padding: 6px; z-index: 999; animation: ddFadeIn .18s cubic-bezier(.32,.72,0,1);
}
.ddtr-dropdown.open { display: block; }
@keyframes ddFadeIn { from { opacity:0; transform:translateY(-4px) scale(.98); } to { opacity:1; transform:none; } }
.ddtr-dd-section {
    font-size: 10px; font-weight: 600; color: var(--faint);
    text-transform: uppercase; letter-spacing: .6px;
    padding: 8px 10px 4px;
}
.ddtr-dd-sep { height: 1px; background: var(--line); margin: 4px 6px; }
.ddtr-dd-item {
    display: flex; align-items: center; gap: 8px; width: 100%;
    padding: 8px 10px; border-radius: 9px; border: none; background: none;
    font-size: 12px; font-weight: 500; color: var(--ink); font-family: 'Poppins', sans-serif;
    cursor: pointer; text-align: left; transition: background .15s cubic-bezier(.4,0,.2,1);
}
.ddtr-dd-item:hover  { background: var(--bg); }
.ddtr-dd-item.active { background: var(--pri-soft); color: var(--pri); font-weight: 600; }
.ddtr-dd-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* ── TABLE CARD ── */
.ddtr-table-card {
    background: #fff; border: 1px solid var(--line); border-radius: 14px;
    box-shadow: 0 1px 2px rgba(15,23,42,.03);
    display: flex; flex-direction: column;
    flex: 1 1 0; min-height: 0;
    overflow: hidden;
}
/* JS toggles this element to display:block — style for block flow */
.ddtr-loading { text-align: center; padding: 64px 24px; color: var(--muted); font-size: 13px; }
.ddtr-loading svg { display: inline-block; }
.ddtr-loading p { margin: 12px 0 0; }
.ddtr-table-scroll {
    flex: 1 1 0; min-height: 0;
    overflow-x: auto; overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

.detailed-dtr-table { width: 100%; min-width: 760px; border-collapse: separate; border-spacing: 0; }
.detailed-dtr-table thead th {
    position: sticky; top: 0; z-index: 2;
    background: #fff; color: var(--muted);
    font-size: 11px; font-weight: 500;
    padding: 12px; text-align: left; white-space: nowrap;
    border-bottom: 1px solid var(--line);
    box-shadow: 0 1px 0 var(--line);
}
.ddtr-th-hint { font-weight: 400; color: var(--faint); font-size: 9.5px; margin-left: 3px; }
.detailed-dtr-table td {
    padding: 0 12px; border-bottom: 1px solid #f1f2f8;
    font-size: 12px; color: var(--ink); height: 56px; vertical-align: middle; white-space: nowrap;
}

/* ── WEEK SEPARATOR ── */
.detailed-dtr-table tr.week-sep td {
    background: var(--bg); padding: 6px 16px;
    font-size: 10px; font-weight: 600; color: var(--faint);
    text-transform: uppercase; letter-spacing: .8px;
    border-top: 1px solid var(--line); border-bottom: 1px solid var(--line);
    height: auto;
}

/* ── TIMELINE DATE CELL ── */
.dtr-date-cell { display: flex; align-items: stretch; min-width: 112px; height: 56px; }
.dtr-tl-track {
    display: flex; flex-direction: column; align-items: center;
    width: 18px; flex-shrink: 0;
}
.dtr-tl-line { flex: 1; width: 2px; }
.dtr-tl-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    border: 2px solid #fff; box-shadow: 0 0 0 2px currentColor;
}
.dtr-date-info { display: flex; flex-direction: column; justify-content: center; padding-left: 10px; gap: 2px; }
.dtr-date-num  { font-size: 16px; font-weight: 700; color: var(--ink); line-height: 1; letter-spacing: -.3px; }
.dtr-date-meta { display: flex; align-items: center; gap: 6px; }
.dtr-date-sub  { font-size: 10px; font-weight: 500; color: var(--faint); }

/* dot + line colors */
.tl-present { color: #2fa860; background: #2fa860; }
.tl-absent  { color: #e5484d; background: #e5484d; }
.tl-weekend { color: #94a3b8; background: #cbd5e1; }
.tl-leave   { color: #4F7CFF; background: #4F7CFF; }
.tl-late    { color: #eba417; background: #eba417; }
.tl-review  { color: #7C5CFF; background: #7C5CFF; }
.tl-line-present, .tl-line-absent, .tl-line-weekend,
.tl-line-leave, .tl-line-late, .tl-line-review { background: #eceef4; }

.detailed-dtr-table td:last-child, .detailed-dtr-table th:last-child { text-align: center; }
.detailed-dtr-table tbody tr { transition: background .15s ease; }
.detailed-dtr-table tbody tr:hover { background: #f7f9fd; }
.detailed-dtr-table tbody tr:last-child td { border-bottom: none; }

/* Row states */
.detailed-dtr-table tr.day-weekend { background: #fafbfd; }
.detailed-dtr-table tr.day-weekend:hover { background: #f3f5f9; }
.detailed-dtr-table tr.day-absent { background: #fcfcfd; }
.detailed-dtr-table tr.day-absent:hover { background: #f5f5f7; }
.detailed-dtr-table tr.day-needs-review { background: #fcfcfd; }
.detailed-dtr-table tr.day-today td { box-shadow: inset 0 0 0 1.5px rgba(79,124,255,.25); }

/* ── TIME VALUES ── */
.time-val { font-size: 12px; font-weight: 500; color: var(--ink); white-space: nowrap; font-variant-numeric: tabular-nums; }
.time-val.time-missing { color: #c4c9d8; font-weight: 400; }

/* ── STATUS BADGES · soft rounded pills ── */
.detailed-dtr-table .badge-absent,
.detailed-dtr-table .badge-incomplete,
.detailed-dtr-table .badge-needs-review {
    display: inline-flex; align-items: center;
    font-size: 9.5px; font-weight: 600; letter-spacing: .3px;
    padding: 2px 8px; border-radius: 999px;
    vertical-align: middle; line-height: 1.5;
}
.detailed-dtr-table .badge-absent { background: #fdedec; color: #d5433c; }
.detailed-dtr-table .badge-incomplete { background: #fdf3e3; color: #a6720c; }
.detailed-dtr-table .badge-needs-review { background: #f2effd; color: #7C5CFF; }
.detailed-dtr-table .log-late { color: var(--ink); font-weight: 600; }

/* ── ACCREDITED PILL · soft backgrounds ── */
.acc-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px;
    white-space: nowrap; cursor: default; position: relative;
}
.acc-full       { background: #e9f9ef; color: #23875a; }
.acc-partial    { background: #fdf3e3; color: #a6720c; }
.acc-absent     { background: #fdedec; color: #d5433c; }
.acc-leave      { background: #eaf1ff; color: #4F7CFF; }
.acc-incomplete { background: #f3f4f8; color: var(--faint); }
.acc-info-ico   { opacity: .55; flex-shrink: 0; }

/* tooltip */
.acc-pill[data-acc-tip]:hover::after {
    content: attr(data-acc-tip);
    position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);
    background: var(--ink); color: #fff; font-size: 10.5px; font-weight: 500;
    padding: 6px 10px; border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,.18); z-index: 99; pointer-events: none;
    max-width: 320px; white-space: normal; text-align: center; line-height: 1.5;
}
.acc-pill[data-acc-tip]:hover::before {
    content: '';
    position: absolute; bottom: calc(100% + 1px); left: 50%; transform: translateX(-50%);
    border: 5px solid transparent; border-top-color: var(--ink); z-index: 99;
}

/* ── ROW ACTION · ghost, revealed on hover ── */
.detailed-dtr-table .btn-edit-time {
    width: 32px; height: 32px; border-radius: 9px;
    border: 1px solid transparent; background: transparent; color: var(--faint);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .2s cubic-bezier(.4,0,.2,1), color .2s cubic-bezier(.4,0,.2,1), border-color .2s cubic-bezier(.4,0,.2,1), opacity .2s cubic-bezier(.4,0,.2,1); padding: 0;
}
.detailed-dtr-table .btn-edit-time:hover { color: var(--pri); background: var(--pri-soft); border-color: #dfe2f4; }
.detailed-dtr-table .btn-edit-time:active { transform: scale(.9); }
.detailed-dtr-table .btn-edit-time:focus-visible { outline: 2px solid var(--pri); outline-offset: 2px; opacity: 1; }
@media (hover: hover) {
    .detailed-dtr-table tbody tr .btn-edit-time { opacity: 0; }
    .detailed-dtr-table tbody tr:hover .btn-edit-time,
    .detailed-dtr-table tbody tr:focus-within .btn-edit-time { opacity: 1; }
}

/* ── FOOTER ── */
.ddtr-footer {
    display: flex; align-items: center; justify-content: flex-end;
    padding: 12px 24px; border-top: 1px solid var(--line); background: #fff;
    flex-shrink: 0;
}

/* ── RESPONSIVE ── */
@media (max-width: 1100px) {
    .ddtr-kpis { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 820px) {
    .ddtr-overlay { padding: 0; }
    .ddtr-modal { max-width: 100%; height: 100vh; border-radius: 0; border: none; }

    .ddtr-header { padding: 0 16px; gap: 10px; height: 56px; }
    .ddtr-header-brand { gap: 10px; }
    .ddtr-header-icon { width: 32px; height: 32px; }
    .ddtr-period-pill { display: none; }
    .ddtr-subtitle { display: none; }
    .ddtr-title { font-size: 13px; }
    .ddtr-body { padding: 16px; gap: 12px; }

    .ddtr-kpis { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .ddtr-kpi { padding: 12px; gap: 10px; }
    .ddtr-kpi-icon { width: 34px; height: 34px; }
    .ddtr-kpi-val { font-size: 18px; }

    .ddtr-toolbar { flex-direction: column; align-items: stretch; gap: 8px; padding: 12px; }
    .ddtr-toolbar-divider { display: none; }
    .ddtr-fld { width: 100%; }
    .ddtr-input { width: 100%; box-sizing: border-box; }
    .ddtr-sep { text-align: center; }
    .ddtr-view-btn { width: 100%; justify-content: center; }
    .ddtr-dropdown { left: 0; right: 0; }
    .ddtr-toolbar-actions { flex-direction: row; margin-left: 0; }
    .ddtr-toolbar-actions .ddtr-btn-solid,
    .ddtr-toolbar-actions .ddtr-btn-ghost { flex: 1; justify-content: center; }

    /* touch devices: keep row action always visible */
    .detailed-dtr-table tbody tr .btn-edit-time { opacity: 1; border-color: var(--line); }

    .ddtr-footer { padding: 12px 16px; }
}
@media (max-width: 480px) {
    .ddtr-kpis { grid-template-columns: repeat(2, 1fr); }
}
</style>
