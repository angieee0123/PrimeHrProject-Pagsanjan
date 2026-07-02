<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay ddtr-overlay" style="display: none;" onclick="closeDetailedDTRModal()">
    <div class="ddtr-modal" onclick="event.stopPropagation()">

        {{-- ── HEADER TOOLBAR ── --}}
        <div class="ddtr-header">
            {{-- Left: branding --}}
            <div class="ddtr-header-brand">
                <div class="ddtr-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <span class="ddtr-title">Detailed Time Record</span>
                <span class="ddtr-period-pill" id="detailedPeriod">{{ $periodDisplay }}</span>
            </div>

            {{-- Right: close --}}
            <button class="ddtr-close" onclick="closeDetailedDTRModal()" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- ── SCROLLABLE BODY ── --}}
        <div class="ddtr-body">

            {{-- ── KPI CARDS ── --}}
            <section class="ddtr-kpis">
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon green">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Present</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiPresent">0</strong>
                        <span class="ddtr-kpi-sub" id="detailedKpiPresentSub">0 days</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon red">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Absent</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiAbsent">0</strong>
                        <span class="ddtr-kpi-sub">days missed</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon amber">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Late</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiLate">0</strong>
                        <span class="ddtr-kpi-sub" id="detailedKpiLateSub">0 min total</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Leave</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiLeave">0</strong>
                        <span class="ddtr-kpi-sub">approved days</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon rose">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Undertime</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiUndertime">0</strong>
                        <span class="ddtr-kpi-sub">total</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon purple">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><line x1="22" y1="2" x2="18" y2="6"/></svg>
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
                <div class="ddtr-toolbar-fields">
                    <div class="ddtr-fld">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <input type="date" id="detailedStartDate" class="ddtr-input">
                    </div>
                    <span class="ddtr-sep">to</span>
                    <div class="ddtr-fld">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <input type="date" id="detailedEndDate" class="ddtr-input">
                    </div>
                </div>
                <div class="ddtr-toolbar-actions">
                    <button class="ddtr-btn-solid" onclick="loadDetailedDTR()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filter
                    </button>
                    <button class="ddtr-btn-ghost" onclick="exportDetailedDTR()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                </div>
            </div>

            {{-- ── TABLE ── --}}
            <div class="ddtr-table-card">
                <div id="detailedDTRLoading" class="ddtr-loading">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9aa1b5" stroke-width="2.5" style="animation:spin 1s linear infinite">
                        <circle cx="12" cy="12" r="10" opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" opacity=".75"/>
                    </svg>
                    <p>Loading attendance records…</p>
                </div>

                <div class="ddtr-table-scroll">
                    <table class="detailed-dtr-table" id="detailedDTRTable" style="display:none;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>AM</th>
                                <th>PM</th>
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
/* ══════════════ DETAILED DTR MODAL — premium redesign ══════════════ */
.ddtr-overlay {
    --pri: #0B0A4D;
    --card: #FFFFFF;
    --line: #eceaf8;
    --ink: #1E2247;
    --muted: #7C839D;
    padding: 24px;
}
.ddtr-modal {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 1440px;
    height: 90vh;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 30px 80px rgba(15, 23, 42, .18);
    overflow: hidden;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    animation: ddtrIn .25s cubic-bezier(.22,.61,.36,1);
}
@keyframes ddtrIn { from { opacity: 0; transform: translateY(16px) scale(.99); } to { opacity: 1; transform: none; } }

/* ── HEADER TOOLBAR ── */
.ddtr-header {
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 0 20px;
    height: 56px; flex-shrink: 0;
    background: linear-gradient(90deg, #0B0A4D 0%, #1a1270 100%);
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.ddtr-header-brand {
    display: flex; align-items: center; gap: 10px; flex-shrink: 0;
}
.ddtr-header-icon {
    width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
    background: rgba(255,255,255,.12); color: rgba(255,255,255,.85);
    display: flex; align-items: center; justify-content: center;
}
.ddtr-title {
    font-size: 13px; font-weight: 700; color: #fff; margin: 0;
    white-space: nowrap; letter-spacing: .1px;
}
.ddtr-period-pill {
    font-size: 11px; font-weight: 600; color: rgba(255,255,255,.6);
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
    border-radius: 999px; padding: 2px 10px; white-space: nowrap;
}
/* Close */
.ddtr-close {
    width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
    border: 1px solid rgba(255,255,255,.18); background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.7);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s ease;
}
.ddtr-close:hover { background: rgba(255,255,255,.18); color: #fff; transform: rotate(90deg); }

/* ── BODY (scroll) ── */
.ddtr-body {
    flex: 1 1 0; min-height: 0; overflow-y: auto; overflow-x: hidden;
    padding: 14px 20px;
    background: #F7F8FC;
    display: flex; flex-direction: column; gap: 10px;
}


/* ── KPI CARDS ── */
.ddtr-kpis {
    display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px;
}
.ddtr-kpi {
    background: #fff; border: 1px solid var(--line); border-radius: 12px;
    box-shadow: 0 1px 3px rgba(15,23,42,.03);
    padding: 10px 12px; display: flex; flex-direction: row; align-items: center; gap: 10px;
    transition: transform .2s ease, box-shadow .2s ease;
}
.ddtr-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(15,23,42,.08); }
.ddtr-kpi-icon {
    width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.ddtr-kpi-icon.green  { background: #ecfdf3; color: #16a34a; }
.ddtr-kpi-icon.red    { background: #fef2f2; color: #ef4444; }
.ddtr-kpi-icon.amber  { background: #fff7ed; color: #f59e0b; }
.ddtr-kpi-icon.blue   { background: #eef4ff; color: #4F7CFF; }
.ddtr-kpi-icon.rose   { background: #fef1f6; color: #e11d73; }
.ddtr-kpi-icon.purple { background: #f4f1ff; color: #7C5CFF; }
.ddtr-kpi-text { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.ddtr-kpi-label { font-size: 10px; font-weight: 600; color: var(--muted); }
.ddtr-kpi-val { font-size: 18px; font-weight: 800; color: var(--ink); line-height: 1; letter-spacing: -.5px; }
.ddtr-kpi-sub { font-size: 10px; color: var(--muted); font-weight: 500; }

/* ── TOOLBAR ── */
.ddtr-toolbar {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    background: #fff; border: 1px solid var(--line); border-radius: 14px;
    box-shadow: 0 1px 3px rgba(15,23,42,.03);
    padding: 12px 16px; flex-wrap: wrap;
}
.ddtr-toolbar-fields { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.ddtr-toolbar-actions { display: flex; align-items: center; gap: 10px; }
.ddtr-fld { position: relative; display: flex; align-items: center; }
.ddtr-fld > svg { position: absolute; left: 12px; color: #9aa1b5; pointer-events: none; }
.ddtr-input {
    height: 44px; border: 1px solid var(--line); border-radius: 10px;
    background: #f8fafc; color: var(--ink); font-size: 13px; font-family: 'Poppins', sans-serif;
    outline: none; padding: 0 14px 0 36px; transition: all .2s ease;
}
.ddtr-input:focus { border-color: var(--pri); background: #fff; box-shadow: 0 0 0 3px rgba(11,10,77,.07); }
.ddtr-sep { font-size: 12px; font-weight: 600; color: var(--muted); }
.ddtr-btn-solid, .ddtr-btn-ghost {
    height: 44px; display: inline-flex; align-items: center; gap: 8px; padding: 0 20px;
    border-radius: 10px; font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif;
    cursor: pointer; transition: all .2s ease; white-space: nowrap;
}
.ddtr-btn-solid { background: var(--pri); color: #fff; border: 1px solid var(--pri); box-shadow: 0 6px 16px rgba(11,10,77,.20); }
.ddtr-btn-solid:hover { background: #1a0f6e; transform: translateY(-2px); }
.ddtr-btn-ghost { background: #fff; color: var(--ink); border: 1px solid var(--line); }
.ddtr-btn-ghost:hover { background: #f6f7fc; border-color: #cdd4ea; transform: translateY(-2px); }

/* ── TABLE CARD ── */
.ddtr-table-card {
    background: #fff; border: 1px solid var(--line); border-radius: 18px;
    box-shadow: 0 1px 3px rgba(15,23,42,.03);
    display: flex; flex-direction: column;
    flex: 1 1 0; min-height: 0;
}
.ddtr-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 56px; color: var(--muted); font-size: 13px; }
.ddtr-table-scroll {
    flex: 1 1 0; min-height: 0;
    overflow-x: auto; overflow-y: auto;
    border-radius: 18px;
    -webkit-overflow-scrolling: touch;
}

.detailed-dtr-table { width: 100%; min-width: 700px; border-collapse: separate; border-spacing: 0; }
.detailed-dtr-table thead th {
    position: sticky; top: 0; z-index: 2;
    background: #f7f8fc; color: var(--muted);
    font-size: 10px; font-weight: 600; text-transform: none;
    padding: 7px 8px; text-align: left; white-space: nowrap;
    border-bottom: 1px solid var(--line);
}
.detailed-dtr-table td {
    padding: 0 8px; border-bottom: 1px solid #f1f2f8;
    font-size: 11px; color: var(--ink); height: 52px; vertical-align: middle; white-space: nowrap;
}

/* ── WEEK SEPARATOR ── */
.detailed-dtr-table tr.week-sep td {
    background: #f0f2fa; padding: 5px 14px;
    font-size: 9.5px; font-weight: 700; color: #9aa1b5;
    text-transform: uppercase; letter-spacing: .7px;
    border-top: 1px solid var(--line); border-bottom: 1px solid var(--line);
    height: auto;
}

/* ── TIMELINE DATE CELL ── */
.dtr-date-cell { display: flex; align-items: stretch; min-width: 110px; height: 52px; }
.dtr-tl-track {
    display: flex; flex-direction: column; align-items: center;
    width: 18px; flex-shrink: 0;
}
.dtr-tl-line { flex: 1; width: 2px; }
.dtr-tl-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    border: 2px solid #fff; box-shadow: 0 0 0 2px currentColor;
}
.dtr-date-info { display: flex; flex-direction: column; justify-content: center; padding-left: 9px; gap: 2px; }
.dtr-date-num  { font-size: 16px; font-weight: 800; color: var(--ink); line-height: 1; }
.dtr-date-meta { display: flex; align-items: center; gap: 4px; }
.dtr-date-sub  { font-size: 9.5px; font-weight: 600; color: var(--muted); }

/* dot + line colors */
.tl-present { color: #16a34a; background: #16a34a; }
.tl-absent  { color: #ef4444; background: #ef4444; }
.tl-weekend { color: #94a3b8; background: #cbd5e1; }
.tl-leave   { color: #4F7CFF; background: #4F7CFF; }
.tl-late    { color: #f59e0b; background: #f59e0b; }
.tl-review  { color: #7C5CFF; background: #7C5CFF; }
.tl-line-present { background: #bbf7d0; }
.tl-line-absent  { background: #fecaca; }
.tl-line-weekend { background: #e2e8f0; }
.tl-line-leave   { background: #bfdbfe; }
.tl-line-late    { background: #fde68a; }
.tl-line-review  { background: #ede9fe; }
.detailed-dtr-table td:last-child, .detailed-dtr-table th:last-child { text-align: center; }
.detailed-dtr-table tbody tr { transition: background .15s ease; }
.detailed-dtr-table tbody tr:hover { background: #f6f9ff; }
.detailed-dtr-table tbody tr:last-child td { border-bottom: none; }

/* Row states */
.detailed-dtr-table tr.day-weekend { background: #f4f7ff; }
.detailed-dtr-table tr.day-weekend:hover { background: #eaf0ff; }
.detailed-dtr-table tr.day-absent { background: #fff7f7; }
.detailed-dtr-table tr.day-absent:hover { background: #fff0f0; }
.detailed-dtr-table tr.day-needs-review { background: #fffdf3; }
.detailed-dtr-table tr.day-today td { box-shadow: inset 0 0 0 1.5px rgba(79,124,255,.35); }

/* ── TIME SLOT CELL ── */
.time-slot {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f8fafc; border: 1px solid #eceaf8;
    border-radius: 8px; padding: 4px 8px;
}
.time-entry { display: flex; align-items: center; gap: 4px; }
.time-lbl {
    font-size: 8.5px; font-weight: 700; letter-spacing: .4px;
    padding: 1px 4px; border-radius: 4px; flex-shrink: 0;
}
.time-lbl.in  { background: #ecfdf3; color: #15803d; }
.time-lbl.out { background: #fef2f2; color: #dc2626; }
.time-val {
    font-size: 11px; font-weight: 600; color: #1e2247; white-space: nowrap;
}
.time-val.time-missing { color: #c4c9d8; font-weight: 500; }
.time-divider {
    width: 1px; height: 16px; background: #e2e5f0; flex-shrink: 0;
}

/* ── ACCREDITED PILL ── */
.acc-pill {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px;
    white-space: nowrap; cursor: default; position: relative;
}
.acc-full       { background: #ecfdf3; color: #15803d; }
.acc-partial    { background: #fff7ed; color: #a16207; }
.acc-absent     { background: #fef2f2; color: #dc2626; }
.acc-leave      { background: #eef4ff; color: #4F7CFF; }
.acc-incomplete { background: #f4f1ff; color: #7C5CFF; }
.acc-info-ico   { opacity: .6; flex-shrink: 0; }

/* tooltip */
.acc-pill[data-acc-tip]:hover::after {
    content: attr(data-acc-tip);
    position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);
    background: #1e2247; color: #fff; font-size: 10px; font-weight: 500;
    padding: 5px 10px; border-radius: 8px; white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,.18); z-index: 99; pointer-events: none;
    max-width: 320px; white-space: normal; text-align: center; line-height: 1.5;
}
.acc-pill[data-acc-tip]:hover::before {
    content: '';
    position: absolute; bottom: calc(100% + 1px); left: 50%; transform: translateX(-50%);
    border: 5px solid transparent; border-top-color: #1e2247; z-index: 99;
}
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 999px;
    margin-left: 6px; vertical-align: middle;
}
.detailed-dtr-table .badge-absent { background: #fef2f2; color: #dc2626; }
.detailed-dtr-table .badge-incomplete { background: #fefce8; color: #a16207; }
.detailed-dtr-table .badge-needs-review { background: #f4f1ff; color: #7C5CFF; }
.detailed-dtr-table .log-missing {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 600; padding: 3px 8px; border-radius: 7px;
    background: #fff7ed; color: #d97706;
}
.detailed-dtr-table .log-missing::before { content: "⚠"; font-size: 10px; }
.detailed-dtr-table .log-late { color: #d97706; font-weight: 600; }

/* Icon action button */
.detailed-dtr-table .btn-edit-time {
    width: 34px; height: 34px; border-radius: 9px;
    border: 1px solid var(--line); background: #fff; color: var(--muted);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s ease; padding: 0;
}
.detailed-dtr-table .btn-edit-time:hover { color: #fff; background: var(--pri); border-color: var(--pri); transform: translateY(-2px); }

/* ── FOOTER ── */
.ddtr-footer {
    display: flex; align-items: center; justify-content: flex-end;
    padding: 10px 20px; border-top: 1px solid var(--line); background: #fff;
    flex-shrink: 0;
}

/* ── RESPONSIVE ── */
@media (max-width: 1100px) {
    .ddtr-kpis { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 820px) {
    .ddtr-overlay { padding: 0; }
    .ddtr-modal { max-width: 100%; height: 100vh; border-radius: 0; }

    .ddtr-header { padding: 0 12px; gap: 10px; }
    .ddtr-header-brand { gap: 8px; }
    .ddtr-period-pill { display: none; }
    .ddtr-title { font-size: 12px; }
    .ddtr-body { padding: 14px 12px; gap: 14px; }

    .ddtr-kpis { grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .ddtr-kpi { padding: 14px 12px; }
    .ddtr-kpi-val { font-size: 22px; }

    .ddtr-toolbar { flex-direction: column; align-items: stretch; gap: 10px; padding: 12px; }
    .ddtr-toolbar-fields { flex-direction: column; align-items: stretch; gap: 8px; }
    .ddtr-fld { width: 100%; }
    .ddtr-input { width: 100%; box-sizing: border-box; }
    .ddtr-sep { text-align: center; }
    .ddtr-toolbar-actions { flex-direction: row; }
    .ddtr-toolbar-actions .ddtr-btn-solid,
    .ddtr-toolbar-actions .ddtr-btn-ghost { flex: 1; justify-content: center; }

    .ddtr-footer { padding: 10px 12px; }
}
@media (max-width: 480px) {
    .ddtr-kpis { grid-template-columns: repeat(2, 1fr); }
}
</style>
