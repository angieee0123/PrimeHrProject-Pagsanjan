<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay ddtr-overlay" style="display: none;" onclick="closeDetailedDTRModal()">
    <div class="ddtr-modal" onclick="event.stopPropagation()">

        {{-- ── HEADER ── --}}
        <div class="ddtr-header">
            <div class="ddtr-header-title">
                <div class="ddtr-header-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <h2 class="ddtr-title">Detailed Time Record</h2>
                    <p class="ddtr-period" id="detailedPeriod">{{ $periodDisplay }}</p>
                </div>
            </div>
            <button class="ddtr-close" onclick="closeDetailedDTRModal()" aria-label="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- ── SCROLLABLE BODY ── --}}
        <div class="ddtr-body">

            {{-- ── EMPLOYEE PROFILE HERO ── --}}
            <section class="ddtr-profile">
                <div class="ddtr-profile-left">
                    <div class="ddtr-avatar" id="detailedAvatar"></div>
                    <div class="ddtr-identity">
                        <h3 class="ddtr-name" id="detailedName">—</h3>
                        <p class="ddtr-empid" id="detailedEmpId">—</p>
                        <div class="ddtr-meta-grid">
                            <div class="ddtr-meta-item">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                                <div>
                                    <span class="ddtr-meta-label">Department</span>
                                    <span class="ddtr-meta-value" id="detailedDept">—</span>
                                </div>
                            </div>
                            <div class="ddtr-meta-item">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <div>
                                    <span class="ddtr-meta-label">Position</span>
                                    <span class="ddtr-meta-value" id="detailedPosition">—</span>
                                </div>
                            </div>
                            <div class="ddtr-meta-item">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <div>
                                    <span class="ddtr-meta-label">Employment Status</span>
                                    <span class="ddtr-meta-value" id="detailedStatus">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Attendance Score Ring --}}
                <div class="ddtr-score">
                    <div class="ddtr-score-ring">
                        <svg width="120" height="120" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="52" fill="none" stroke="#eef0f6" stroke-width="10"/>
                            <circle id="detailedScoreCircle" cx="60" cy="60" r="52" fill="none" stroke="#4F7CFF" stroke-width="10"
                                stroke-linecap="round" transform="rotate(-90 60 60)"
                                stroke-dasharray="326.7" stroke-dashoffset="326.7"
                                style="transition: stroke-dashoffset 1s cubic-bezier(.22,.61,.36,1), stroke .4s ease;"/>
                        </svg>
                        <div class="ddtr-score-center">
                            <span class="ddtr-score-val" id="detailedScore">0%</span>
                        </div>
                    </div>
                    <p class="ddtr-score-label">Attendance Score</p>
                    <span class="ddtr-score-tag" id="detailedScoreTag">—</span>
                </div>
            </section>

            {{-- ── KPI CARDS ── --}}
            <section class="ddtr-kpis">
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <span class="ddtr-kpi-label">Present</span>
                    <strong class="ddtr-kpi-val" id="detailedKpiPresent">0</strong>
                    <span class="ddtr-kpi-sub" id="detailedKpiPresentSub">0 days</span>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon red">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <span class="ddtr-kpi-label">Absent</span>
                    <strong class="ddtr-kpi-val" id="detailedKpiAbsent">0</strong>
                    <span class="ddtr-kpi-sub">days missed</span>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon amber">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <span class="ddtr-kpi-label">Late</span>
                    <strong class="ddtr-kpi-val" id="detailedKpiLate">0</strong>
                    <span class="ddtr-kpi-sub" id="detailedKpiLateSub">0 min total</span>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <span class="ddtr-kpi-label">Leave</span>
                    <strong class="ddtr-kpi-val" id="detailedKpiLeave">0</strong>
                    <span class="ddtr-kpi-sub">approved days</span>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon rose">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                    </div>
                    <span class="ddtr-kpi-label">Undertime</span>
                    <strong class="ddtr-kpi-val" id="detailedKpiUndertime">0</strong>
                    <span class="ddtr-kpi-sub">total</span>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon purple">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><line x1="22" y1="2" x2="18" y2="6"/></svg>
                    </div>
                    <span class="ddtr-kpi-label">Overtime</span>
                    <strong class="ddtr-kpi-val" id="detailedKpiOvertime">0</strong>
                    <span class="ddtr-kpi-sub">extra hours</span>
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
                                <th>Day</th>
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

        {{-- ── PINNED SUMMARY FOOTER ── --}}
        <div class="ddtr-footer">
            <div class="ddtr-summary">
                <div class="ddtr-sum-card">
                    <span class="ddtr-sum-label">Total Days</span>
                    <strong class="ddtr-sum-val" id="detailedTotalDays">0</strong>
                </div>
                <div class="ddtr-sum-card">
                    <span class="ddtr-sum-label">Present</span>
                    <strong class="ddtr-sum-val" style="color:#15803d;" id="detailedTotalPresent">0</strong>
                </div>
                <div class="ddtr-sum-card">
                    <span class="ddtr-sum-label">Absent</span>
                    <strong class="ddtr-sum-val" style="color:#dc2626;" id="detailedTotalAbsent">0</strong>
                </div>
                <div class="ddtr-sum-card">
                    <span class="ddtr-sum-label">Late</span>
                    <strong class="ddtr-sum-val" style="color:#d97706;" id="detailedTotalLate">0 times</strong>
                </div>
                <div class="ddtr-sum-card">
                    <span class="ddtr-sum-label">Total Late</span>
                    <strong class="ddtr-sum-val" style="color:#d97706;" id="detailedTotalLateMinutes">0 min</strong>
                </div>
                <div class="ddtr-sum-card">
                    <span class="ddtr-sum-label">Undertime</span>
                    <strong class="ddtr-sum-val" style="color:#dc2626;" id="detailedTotalUndertime">0 min</strong>
                </div>
                <div class="ddtr-sum-card">
                    <span class="ddtr-sum-label">Worked Hours</span>
                    <strong class="ddtr-sum-val" id="detailedTotalWorked">0h</strong>
                </div>
                <div class="ddtr-sum-card">
                    <span class="ddtr-sum-label">Overtime</span>
                    <strong class="ddtr-sum-val" style="color:#7C5CFF;" id="detailedTotalOvertime">0h</strong>
                </div>
            </div>
            <button class="ddtr-btn-ghost ddtr-close-btn" onclick="closeDetailedDTRModal()">Close</button>
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
    max-height: 90vh;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 30px 80px rgba(15, 23, 42, .18);
    overflow: hidden;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    animation: ddtrIn .25s cubic-bezier(.22,.61,.36,1);
}
@keyframes ddtrIn { from { opacity: 0; transform: translateY(16px) scale(.99); } to { opacity: 1; transform: none; } }

/* ── HEADER ── */
.ddtr-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 22px 28px;
    border-bottom: 1px solid var(--line);
    flex-shrink: 0;
}
.ddtr-header-title { display: flex; align-items: center; gap: 14px; }
.ddtr-header-icon {
    width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
    background: #eef0f8; color: var(--pri);
    display: flex; align-items: center; justify-content: center;
}
.ddtr-title { font-size: 22px; font-weight: 800; color: var(--pri); margin: 0; letter-spacing: -.5px; }
.ddtr-period { font-size: 13px; color: var(--muted); margin: 2px 0 0; font-weight: 500; }
.ddtr-close {
    width: 40px; height: 40px; border-radius: 12px;
    border: 1px solid var(--line); background: #fff; color: var(--muted);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s ease; flex-shrink: 0;
}
.ddtr-close:hover { background: #f6f7fc; color: var(--pri); border-color: #cdd4ea; transform: rotate(90deg); }

/* ── BODY (scroll) ── */
.ddtr-body {
    flex: 1; overflow-y: auto; overflow-x: hidden;
    padding: 24px 28px;
    background: #F7F8FC;
    display: flex; flex-direction: column; gap: 20px;
}

/* ── PROFILE HERO ── */
.ddtr-profile {
    display: flex; align-items: center; justify-content: space-between; gap: 24px;
    background: #fff; border: 1px solid var(--line); border-radius: 18px;
    box-shadow: 0 1px 3px rgba(15,23,42,.03);
    padding: 26px 30px;
}
.ddtr-profile-left { display: flex; align-items: center; gap: 22px; min-width: 0; }
.ddtr-avatar {
    width: 84px; height: 84px; border-radius: 20px; flex-shrink: 0;
    background: linear-gradient(135deg, #0b044d, #2d1a8e);
    color: #fff; font-size: 28px; font-weight: 800; letter-spacing: .5px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 20px rgba(11,10,77,.22);
}
.ddtr-identity { min-width: 0; }
.ddtr-name { font-size: 26px; font-weight: 800; color: var(--pri); margin: 0 0 3px; letter-spacing: -.6px; }
.ddtr-empid {
    display: inline-block; font-size: 12px; font-weight: 600; color: var(--muted);
    background: #f4f5fa; border: 1px solid var(--line); border-radius: 8px;
    padding: 3px 10px; margin: 0 0 14px;
}
.ddtr-meta-grid { display: flex; flex-wrap: wrap; gap: 12px 28px; }
.ddtr-meta-item { display: flex; align-items: center; gap: 10px; }
.ddtr-meta-item > svg { color: #9aa1b5; flex-shrink: 0; }
.ddtr-meta-item > div { display: flex; flex-direction: column; line-height: 1.3; }
.ddtr-meta-label { font-size: 11px; font-weight: 600; color: var(--muted); }
.ddtr-meta-value { font-size: 14px; font-weight: 700; color: var(--ink); }

/* ── SCORE RING ── */
.ddtr-score { display: flex; flex-direction: column; align-items: center; gap: 4px; flex-shrink: 0; }
.ddtr-score-ring { position: relative; width: 120px; height: 120px; }
.ddtr-score-center {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
}
.ddtr-score-val { font-size: 30px; font-weight: 800; color: var(--pri); letter-spacing: -1px; }
.ddtr-score-label { font-size: 12px; font-weight: 600; color: var(--muted); margin: 6px 0 0; }
.ddtr-score-tag {
    font-size: 11px; font-weight: 700; padding: 3px 12px; border-radius: 999px;
    background: #eef4ff; color: #3a5bd9;
}

/* ── KPI CARDS ── */
.ddtr-kpis {
    display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px;
}
.ddtr-kpi {
    background: #fff; border: 1px solid var(--line); border-radius: 16px;
    box-shadow: 0 1px 3px rgba(15,23,42,.03);
    padding: 18px; display: flex; flex-direction: column; gap: 6px;
    transition: transform .2s ease, box-shadow .2s ease;
}
.ddtr-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(15,23,42,.08); }
.ddtr-kpi-icon {
    width: 38px; height: 38px; border-radius: 11px; margin-bottom: 4px;
    display: flex; align-items: center; justify-content: center;
}
.ddtr-kpi-icon.green  { background: #ecfdf3; color: #16a34a; }
.ddtr-kpi-icon.red    { background: #fef2f2; color: #ef4444; }
.ddtr-kpi-icon.amber  { background: #fff7ed; color: #f59e0b; }
.ddtr-kpi-icon.blue   { background: #eef4ff; color: #4F7CFF; }
.ddtr-kpi-icon.rose   { background: #fef1f6; color: #e11d73; }
.ddtr-kpi-icon.purple { background: #f4f1ff; color: #7C5CFF; }
.ddtr-kpi-label { font-size: 12px; font-weight: 600; color: var(--muted); }
.ddtr-kpi-val { font-size: 26px; font-weight: 800; color: var(--ink); line-height: 1; letter-spacing: -.8px; }
.ddtr-kpi-sub { font-size: 11px; color: var(--muted); font-weight: 500; }

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
    box-shadow: 0 1px 3px rgba(15,23,42,.03); overflow: hidden;
}
.ddtr-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 56px; color: var(--muted); font-size: 13px; }
.ddtr-table-scroll { overflow-x: auto; }

.detailed-dtr-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.detailed-dtr-table thead th {
    position: sticky; top: 0; z-index: 2;
    background: #f7f8fc; color: var(--muted);
    font-size: 12px; font-weight: 600; text-transform: none;
    padding: 14px 16px; text-align: left; white-space: nowrap;
    border-bottom: 1px solid var(--line);
}
.detailed-dtr-table td {
    padding: 14px 16px; border-bottom: 1px solid #f1f2f8;
    font-size: 13.5px; color: var(--ink); height: 64px; vertical-align: middle; white-space: nowrap;
}
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

/* Pills & inline markers */
.detailed-dtr-table .badge-absent,
.detailed-dtr-table .badge-incomplete,
.detailed-dtr-table .badge-needs-review {
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

/* ── FOOTER SUMMARY ── */
.ddtr-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 16px 28px; border-top: 1px solid var(--line); background: #fff;
    flex-shrink: 0; flex-wrap: wrap;
}
.ddtr-summary { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.ddtr-sum-card {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    background: #f7f8fc; border: 1px solid var(--line); border-radius: 12px;
    padding: 10px 16px; min-width: 84px;
}
.ddtr-sum-label { font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
.ddtr-sum-val { font-size: 16px; font-weight: 800; color: var(--pri); }
.ddtr-close-btn { flex-shrink: 0; }

/* ── RESPONSIVE ── */
@media (max-width: 1100px) {
    .ddtr-kpis { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 820px) {
    .ddtr-overlay { padding: 0; }
    .ddtr-modal { max-width: 100%; max-height: 100vh; border-radius: 0; }
    .ddtr-profile { flex-direction: column; align-items: flex-start; }
    .ddtr-score { align-self: center; }
    .ddtr-kpis { grid-template-columns: repeat(2, 1fr); }
    .ddtr-toolbar { flex-direction: column; align-items: stretch; }
    .ddtr-toolbar-actions .ddtr-btn-solid, .ddtr-toolbar-actions .ddtr-btn-ghost { flex: 1; justify-content: center; }
}
</style>
