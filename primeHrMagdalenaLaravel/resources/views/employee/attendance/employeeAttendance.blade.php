@extends('layouts.employee')

@section('title', 'Attendance · PRIME HRIS')

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Attendance'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-attendance glass-shell">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.attendanceTopbar')

        {{-- ══════════ DETAILED TIME RECORD ══════════ --}}
        <div class="ddtr-page">

            {{-- ── HEADER ── --}}
            <div class="ddtr-header">
                <div class="ddtr-header-brand">
                    <div class="ddtr-header-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="ddtr-header-titles">
                        <span class="ddtr-title">Detailed Time Record</span>
                        <span class="ddtr-subtitle">{{ $employee->first_name }} {{ $employee->last_name }} · {{ $employee->employee_id }}</span>
                    </div>
                    <span class="ddtr-period-pill" id="detailedPeriod">{{ $periodDisplay }}</span>
                </div>
            </div>

            {{-- ── BODY ── --}}
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
                    <span class="ddtr-toolbar-label">Date Range</span>
                    <div class="ddtr-fld">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <input type="date" id="detailedStartDate" class="ddtr-input" aria-label="Start date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <span class="ddtr-sep">to</span>
                    <div class="ddtr-fld">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <input type="date" id="detailedEndDate" class="ddtr-input" aria-label="End date" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
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
                        <button class="ddtr-btn-ghost" onclick="resetDetailedDTR()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Reset
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
                                </tr>
                            </thead>
                            <tbody id="detailedDTRBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

</div>

<style>
/* ══════════════ DETAILED TIME RECORD · employee page ══════════════
   Same design system as the admin Detailed DTR modal, laid out as a page. */
.ddtr-page {
    --pri: #0B0A4D;
    --pri-2: #1b1464;
    --pri-soft: #eef0fb;
    --card: #FFFFFF;
    --line: #e9eaf2;
    --ink: #1a1f36;
    --muted: #697086;
    --faint: #9aa1b5;
    --bg: #f8f9fc;
    --glass: rgba(255, 255, 255, .58);
    --glass-strong: rgba(255, 255, 255, .8);
    --glass-border: rgba(255, 255, 255, .65);

    display: flex;
    flex-direction: column;
    position: relative;
    isolation: isolate;
    background: linear-gradient(180deg, var(--glass-strong), var(--glass));
    backdrop-filter: blur(30px) saturate(180%);
    -webkit-backdrop-filter: blur(30px) saturate(180%);
    border-radius: 24px;
    border: 1px solid var(--glass-border);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .8),
        0 30px 70px rgba(11, 4, 77, .12),
        0 8px 24px rgba(15, 23, 42, .06);
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", 'Poppins', sans-serif;
}
.ddtr-page::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: -1;
    background:
        radial-gradient(480px 320px at 8% -12%, rgba(99, 102, 241, .12), transparent 60%),
        radial-gradient(420px 300px at 100% 0%, rgba(56, 189, 248, .1), transparent 60%);
}
@media (prefers-reduced-motion: reduce) {
    .ddtr-page .ddtr-dropdown { animation: none; }
    .ddtr-page * { transition: none !important; }
}

/* ── HEADER ── */
.ddtr-page .ddtr-header {
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 0 24px;
    height: 64px; flex-shrink: 0;
    background: rgba(255,255,255,.5);
    backdrop-filter: saturate(180%) blur(24px);
    -webkit-backdrop-filter: saturate(180%) blur(24px);
    border-bottom: 1px solid rgba(15,23,42,.06);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
}
.ddtr-header-brand { display: flex; align-items: center; gap: 12px; min-width: 0; }
.ddtr-header-icon {
    width: 36px; height: 36px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--pri-2), var(--pri)); color: #fff;
    border: 1px solid rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.25), 0 4px 12px rgba(11,10,77,.3);
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
    background: rgba(255,255,255,.5); border: 1px solid rgba(15,23,42,.08);
    backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    border-radius: 999px; padding: 4px 12px; white-space: nowrap;
    margin-left: 8px;
}

/* ── BODY ── */
.ddtr-page .ddtr-body {
    padding: 24px;
    background: transparent;
    display: flex; flex-direction: column; gap: 16px;
}

/* ── KPI CARDS ── */
.ddtr-kpis { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; }
.ddtr-kpi {
    background: linear-gradient(180deg, rgba(255,255,255,.8), rgba(255,255,255,.55));
    backdrop-filter: blur(16px) saturate(160%); -webkit-backdrop-filter: blur(16px) saturate(160%);
    border: 1px solid rgba(255,255,255,.7); border-radius: 16px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.8), 0 4px 14px rgba(15,23,42,.04);
    padding: 16px; display: flex; flex-direction: row; align-items: center; gap: 12px;
    transition: box-shadow .25s cubic-bezier(.4,0,.2,1), border-color .25s cubic-bezier(.4,0,.2,1), transform .25s cubic-bezier(.4,0,.2,1);
}
.ddtr-kpi:hover { border-color: rgba(255,255,255,.9); box-shadow: inset 0 1px 0 rgba(255,255,255,.9), 0 12px 28px rgba(11,4,77,.1); transform: translateY(-2px); }
.ddtr-kpi-icon {
    width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
    border: 1px solid rgba(255,255,255,.6);
    display: flex; align-items: center; justify-content: center;
}
.ddtr-kpi-icon.green  { background: #e9f9ef; color: #2fa860; }
.ddtr-kpi-icon.red    { background: #fdedec; color: #e5484d; }
.ddtr-kpi-icon.amber  { background: #fbf6e3; color: #eba417; }
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

/* ── FILTER TOOLBAR ── */
.ddtr-toolbar {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    background: rgba(255,255,255,.55); backdrop-filter: blur(20px) saturate(160%); -webkit-backdrop-filter: blur(20px) saturate(160%);
    border: 1px solid rgba(255,255,255,.65); border-radius: 16px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.7), 0 2px 8px rgba(15,23,42,.03);
    padding: 8px 16px;
    position: relative;
    z-index: 50;
}
.ddtr-toolbar-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.ddtr-toolbar-divider { width: 1px; height: 24px; background: var(--line); margin: 0 4px; }
.ddtr-toolbar-label {
    font-size: 10.5px; font-weight: 600; color: var(--faint);
    text-transform: uppercase; letter-spacing: .5px;
    padding-right: 2px; white-space: nowrap;
}
.ddtr-fld { position: relative; display: flex; align-items: center; }
.ddtr-fld > svg { position: absolute; left: 10px; color: var(--faint); pointer-events: none; }
.ddtr-input {
    height: 36px; border: 1px solid rgba(15,23,42,.08); border-radius: 999px;
    background: rgba(255,255,255,.6); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
    color: var(--ink); font-size: 12px; font-family: inherit;
    outline: none; padding: 0 10px 0 30px; transition: border-color .2s cubic-bezier(.4,0,.2,1), box-shadow .2s cubic-bezier(.4,0,.2,1); width: 140px;
}
.ddtr-input:hover { border-color: #cdd2e3; background: rgba(255,255,255,.85); }
.ddtr-input:focus { border-color: var(--pri); box-shadow: 0 0 0 4px rgba(11,10,77,.08); }
.ddtr-sep { font-size: 11px; font-weight: 500; color: var(--faint); padding: 0 2px; }
.ddtr-btn-solid, .ddtr-btn-ghost {
    height: 36px; display: inline-flex; align-items: center; gap: 6px; padding: 0 16px;
    border-radius: 999px; font-size: 12px; font-weight: 600; font-family: inherit;
    cursor: pointer; transition: all .22s cubic-bezier(.4,0,.2,1); white-space: nowrap;
}
.ddtr-btn-solid {
    background: linear-gradient(135deg, var(--pri-2), var(--pri)); color: #fff; border: 1px solid transparent;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.25), 0 8px 20px rgba(11,10,77,.3);
}
.ddtr-btn-solid:hover { transform: translateY(-1px); box-shadow: inset 0 1px 0 rgba(255,255,255,.3), 0 10px 24px rgba(11,10,77,.38); }
.ddtr-btn-solid:active, .ddtr-btn-ghost:active { transform: scale(.97); }
.ddtr-btn-ghost { background: rgba(255,255,255,.55); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); color: var(--ink); border: 1px solid rgba(15,23,42,.08); }
.ddtr-btn-ghost:hover { background: rgba(255,255,255,.85); border-color: #cdd2e3; }
.ddtr-btn-solid:focus-visible, .ddtr-btn-ghost:focus-visible,
.ddtr-view-btn:focus-visible { outline: 2px solid var(--pri); outline-offset: 2px; }

/* ── VIEW DROPDOWN ── */
.ddtr-view-wrap { position: relative; }
.ddtr-view-btn {
    height: 36px; display: inline-flex; align-items: center; gap: 6px; padding: 0 12px;
    border-radius: 999px; font-size: 12px; font-weight: 600; font-family: inherit;
    border: 1px solid rgba(15,23,42,.08); background: rgba(255,255,255,.55);
    backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); color: var(--ink);
    cursor: pointer; transition: background .2s cubic-bezier(.4,0,.2,1), border-color .2s cubic-bezier(.4,0,.2,1), box-shadow .2s cubic-bezier(.4,0,.2,1); white-space: nowrap;
}
.ddtr-view-btn:hover { background: rgba(255,255,255,.85); border-color: #cdd2e3; }
.ddtr-view-btn.open  { border-color: var(--pri); background: rgba(255,255,255,.9); box-shadow: 0 0 0 4px rgba(11,10,77,.08); }
.ddtr-caret { transition: transform .25s cubic-bezier(.4,0,.2,1); color: var(--faint); }
.ddtr-view-btn.open .ddtr-caret { transform: rotate(180deg); }
.ddtr-dropdown {
    display: none; position: absolute; top: calc(100% + 8px); left: 0;
    min-width: 208px; background: #ffffff;
    border: 1px solid rgba(255,255,255,.7);
    border-radius: 16px; box-shadow: inset 0 1px 0 rgba(255,255,255,.8), 0 20px 45px rgba(15,23,42,.16), 0 2px 8px rgba(15,23,42,.05);
    padding: 6px; z-index: 9999; animation: ddFadeIn .18s cubic-bezier(.32,.72,0,1);
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
    background: linear-gradient(180deg, rgba(255,255,255,.78), rgba(255,255,255,.6));
    backdrop-filter: blur(20px) saturate(160%); -webkit-backdrop-filter: blur(20px) saturate(160%);
    border: 1px solid rgba(255,255,255,.65); border-radius: 16px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.75), 0 2px 8px rgba(15,23,42,.03);
    display: flex; flex-direction: column;
    overflow: hidden;
    position: relative;
    z-index: 1;
}
.ddtr-loading { text-align: center; padding: 64px 24px; color: var(--muted); font-size: 13px; }
.ddtr-loading svg { display: inline-block; }
.ddtr-loading p { margin: 12px 0 0; }
.ddtr-table-scroll {
    max-height: 620px;
    overflow-x: auto; overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}
.ddtr-empty { text-align: center; padding: 64px 24px; color: var(--muted); font-size: 13px; }

.ddtr-page .detailed-dtr-table { width: 100%; min-width: 760px; border-collapse: separate; border-spacing: 0; }
.ddtr-page .detailed-dtr-table thead th {
    position: sticky; top: 0; z-index: 2;
    background: rgba(248,250,252,.9); backdrop-filter: blur(10px) saturate(160%); -webkit-backdrop-filter: blur(10px) saturate(160%);
    color: var(--muted);
    font-size: 11px; font-weight: 500;
    padding: 12px; text-align: left; white-space: nowrap;
    text-transform: none; letter-spacing: normal;
    border-bottom: 1px solid var(--line);
    box-shadow: 0 1px 0 var(--line);
}
.ddtr-th-hint { font-weight: 400; color: var(--faint); font-size: 9.5px; margin-left: 3px; }
.ddtr-page .detailed-dtr-table td {
    padding: 10px 12px; border-bottom: 1px solid #f1f2f8;
    font-size: 12px; color: var(--ink); vertical-align: middle; white-space: nowrap;
    background: transparent;
}

/* ── WEEK SEPARATOR ── */
.ddtr-page .detailed-dtr-table tr.week-sep td {
    background: var(--bg); padding: 6px 16px;
    font-size: 10px; font-weight: 600; color: var(--faint);
    text-transform: uppercase; letter-spacing: .8px;
    border-top: 1px solid var(--line); border-bottom: 1px solid var(--line);
    height: auto;
}

/* ── TIMELINE DATE CELL ── */
.dtr-date-cell { display: flex; align-items: stretch; min-width: 112px; height: 56px; }
.dtr-tl-track { display: flex; flex-direction: column; align-items: center; width: 18px; flex-shrink: 0; }
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

.ddtr-page .detailed-dtr-table tbody tr { transition: background .15s ease; }
.ddtr-page .detailed-dtr-table tbody tr:hover { background: #f7f9fd; }
.ddtr-page .detailed-dtr-table tbody tr:last-child td { border-bottom: none; }

/* Row states */
.ddtr-page .detailed-dtr-table tr.day-weekend { background: #fafbfd; }
.ddtr-page .detailed-dtr-table tr.day-weekend:hover { background: #f3f5f9; }
.ddtr-page .detailed-dtr-table tr.day-absent { background: #fcfcfd; }
.ddtr-page .detailed-dtr-table tr.day-absent:hover { background: #f5f5f7; }
.ddtr-page .detailed-dtr-table tr.day-today td { box-shadow: inset 0 0 0 1.5px rgba(24,75,68,.35); }
.ddtr-page .detailed-dtr-table tr.day-today .dtr-date-num {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 24px; height: 24px; padding: 0 6px;
    background: #184B44; color: #fff; border-radius: 999px;
    line-height: 1;
}

/* ── TIME VALUES ── */
.time-val { font-size: 12px; font-weight: 500; color: var(--ink); white-space: nowrap; font-variant-numeric: tabular-nums; }
.time-val.time-missing { color: #c4c9d8; font-weight: 400; }
.dtr-time-cell { display: flex; flex-direction: column; align-items: flex-start; gap: 5px; white-space: normal; }
.dtr-time-row { display: flex; align-items: baseline; gap: 6px; white-space: nowrap; }
.time-sep { color: #c4c9d8; font-size: 11px; }

/* ── STATUS BADGES ── */
.ddtr-page .detailed-dtr-table .badge-absent,
.ddtr-page .detailed-dtr-table .badge-incomplete,
.ddtr-page .detailed-dtr-table .badge-leave,
.ddtr-page .detailed-dtr-table .badge-travel {
    display: inline-flex; align-items: center;
    font-size: 9.5px; font-weight: 600; letter-spacing: .3px;
    padding: 2px 8px; border-radius: 999px;
    vertical-align: middle; line-height: 1.5;
}
.ddtr-page .detailed-dtr-table .badge-absent { background: #fdedec; color: #d5433c; }
.ddtr-page .detailed-dtr-table .badge-incomplete { background: #fbf6e3; color: #c9a227; }
.ddtr-page .detailed-dtr-table .badge-leave { background: #eaf1ff; color: #4F7CFF; }
.ddtr-page .detailed-dtr-table .badge-travel { background: #e6f7f5; color: #0d9488; }
.ddtr-page .detailed-dtr-table .log-late { color: var(--ink); font-weight: 600; }

/* ── ACCREDITED PILL ── */
.acc-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px;
    white-space: nowrap; cursor: default; position: relative;
}
.acc-full       { background: #e9f9ef; color: #15803d; }
.acc-partial    { background: #fbf6e3; color: #c9a227; }
.acc-absent     { background: #fdedec; color: #d5433c; }
.acc-leave      { background: #eaf1ff; color: #4F7CFF; }
.acc-incomplete { background: #f3f4f8; color: var(--faint); }

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* ── RESPONSIVE ── */
@media (max-width: 1100px) {
    .ddtr-kpis { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 820px) {
    .ddtr-page { border-radius: 18px; }
    .ddtr-page .ddtr-header { padding: 0 16px; gap: 10px; height: 56px; }
    .ddtr-header-brand { gap: 10px; }
    .ddtr-header-icon { width: 32px; height: 32px; }
    .ddtr-period-pill { display: none; }
    .ddtr-subtitle { display: none; }
    .ddtr-title { font-size: 13px; }
    .ddtr-page .ddtr-body { padding: 16px; gap: 12px; }

    .ddtr-kpis { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .ddtr-kpi { padding: 12px; gap: 10px; }
    .ddtr-kpi-icon { width: 34px; height: 34px; }
    .ddtr-kpi-val { font-size: 18px; }

    .ddtr-toolbar { flex-direction: column; align-items: stretch; gap: 8px; padding: 12px; }
    .ddtr-toolbar-divider { display: none; }
    .ddtr-toolbar-label { padding: 0 0 2px 2px; }
    .ddtr-fld { width: 100%; }
    .ddtr-input { width: 100%; box-sizing: border-box; }
    .ddtr-sep { text-align: center; }
    .ddtr-view-btn { width: 100%; justify-content: center; }
    .ddtr-dropdown { left: 0; right: 0; }
    .ddtr-toolbar-actions { flex-direction: row; margin-left: 0; }
    .ddtr-toolbar-actions .ddtr-btn-solid,
    .ddtr-toolbar-actions .ddtr-btn-ghost { flex: 1; justify-content: center; }
}
@media (max-width: 480px) {
    .ddtr-kpis { grid-template-columns: repeat(2, 1fr); }
}
</style>

<script>
    // ── Sidebar / mobile nav ──
    const sidebar       = document.getElementById('sidebar');
    const toggleBtn     = document.getElementById('toggle-btn');
    const logoText      = document.getElementById('logo-text');
    const navLabel      = document.getElementById('nav-label');
    const userInfo      = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn     = document.getElementById('mobile-menu-btn');
    const overlay       = document.getElementById('mobile-overlay');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');
            toggleBtn.textContent = collapsed ? '›' : '‹';
            if (logoText) logoText.style.display = collapsed ? 'none' : '';
            if (navLabel) navLabel.style.display = collapsed ? 'none' : '';
            if (userInfo) userInfo.style.display = collapsed ? 'none' : '';
            if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
            document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
                el.style.display = collapsed ? 'none' : '';
            });
        });
    }

    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }

    // ── Topbar search: filter visible DTR rows ──
    function filterPermanentAttendanceTable(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('#detailedDTRBody tr:not(.week-sep)').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
        syncWeekSeparators();
    }

    // ── Detailed DTR ──
    const DEFAULT_START = '{{ now()->startOfMonth()->format('Y-m-d') }}';
    const DEFAULT_END   = '{{ now()->endOfMonth()->format('Y-m-d') }}';
    let detailedRecords = [];

    document.addEventListener('DOMContentLoaded', loadDetailedDTR);

    function loadDetailedDTR() {
        const startDate = document.getElementById('detailedStartDate').value;
        const endDate   = document.getElementById('detailedEndDate').value;

        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date must be before or equal to end date');
            return;
        }

        document.getElementById('detailedDTRLoading').style.display = 'block';
        document.getElementById('detailedDTRTable').style.display = 'none';

        fetch(`{{ route('employee.attendance.detailed') }}?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                detailedRecords = data.records || [];
                renderDetailedDTR(detailedRecords);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('detailedDTRLoading').innerHTML =
                    '<p style="color:#d5433c;">Failed to load attendance records. Please refresh the page.</p>';
            });
    }

    function resetDetailedDTR() {
        document.getElementById('detailedStartDate').value = DEFAULT_START;
        document.getElementById('detailedEndDate').value = DEFAULT_END;
        loadDetailedDTR();
    }

    function getWeekNumber(date) {
        const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
    }

    function fmt12(t) {
        if (!t || !/^\d{1,2}:\d{2}/.test(t)) return null;
        const [h, m] = t.split(':').map(Number);
        const suffix = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return `${h12}:${String(m).padStart(2, '0')} ${suffix}`;
    }

    function renderTimePair(inTime, outTime) {
        const i = fmt12(inTime), o = fmt12(outTime);
        if (!i && !o) return '';
        return `<span class="time-val${i ? '' : ' time-missing'}">${i || '—'}</span>` +
               `<span class="time-sep">–</span>` +
               `<span class="time-val${o ? '' : ' time-missing'}">${o || '—'}</span>`;
    }

    function formatTotalMinutes(minutes) {
        if (minutes <= 0) return '0 min';
        const hours = Math.floor(minutes / 60);
        const mins = Math.round(minutes % 60);
        if (hours > 0 && mins > 0) return `${hours} hr${hours > 1 ? 's' : ''} ${mins} min`;
        if (hours > 0) return `${hours} hr${hours > 1 ? 's' : ''}`;
        return `${mins} min`;
    }

    function formatHours(minutes) {
        if (minutes <= 0) return '0h';
        const hours = Math.floor(minutes / 60);
        const mins = Math.round(minutes % 60);
        return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
    }

    function otMinutes(inT, outT) {
        if (!fmt12(inT) || !fmt12(outT)) return 0;
        const toMin = t => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };
        return Math.max(0, toMin(outT) - toMin(inT));
    }

    function renderDetailedDTR(records) {
        const tbody = document.getElementById('detailedDTRBody');
        tbody.innerHTML = '';

        let totalPresent = 0, totalAbsent = 0, totalLate = 0;
        let totalLateMinutes = 0, totalUndertimeMinutes = 0;
        let totalLeave = 0, totalOvertimeMinutes = 0;
        let lastWeekNum = null;

        const startDate = document.getElementById('detailedStartDate').value;
        const endDate   = document.getElementById('detailedEndDate').value;
        const fmtLong   = d => new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        document.getElementById('detailedPeriod').textContent = `${fmtLong(startDate)} – ${fmtLong(endDate)}`;

        const todayKey = new Date().toLocaleDateString('en-CA');

        records.forEach(record => {
            const tr = document.createElement('tr');

            const isWeekend  = record.day === 'Saturday' || record.day === 'Sunday';
            const isOnLeave  = !!record.is_on_leave;
            const isOnTravel = !!record.is_on_travel_order;

            // ON LEAVE / ON TRAVEL rows carry sentinel strings instead of times
            const amIn  = fmt12(record.am_in)  ? record.am_in  : null;
            const amOut = fmt12(record.am_out) ? record.am_out : null;
            const pmIn  = fmt12(record.pm_in)  ? record.pm_in  : null;
            const pmOut = fmt12(record.pm_out) ? record.pm_out : null;

            const hasAnyLog  = amIn || amOut || pmIn || pmOut;
            const isComplete = amIn && amOut && pmIn && pmOut;
            const isAbsent   = !isWeekend && !hasAnyLog && !isOnLeave && !isOnTravel;
            const isLate     = record.late_minutes > 0;

            if (isWeekend) tr.className = 'day-weekend';
            if (isAbsent) { tr.className = 'day-absent'; totalAbsent++; }
            else if (hasAnyLog) totalPresent++;

            if (isLate) { totalLate++; totalLateMinutes += record.late_minutes; }
            if (record.undertime > 0) totalUndertimeMinutes += record.undertime;
            if (isOnLeave) totalLeave++;
            totalOvertimeMinutes += otMinutes(record.ot_in, record.ot_out);

            if (record.date_key === todayKey) tr.classList.add('day-today');

            // ── Status badge ──
            let statusBadge = '';
            if (isOnTravel)      statusBadge = ' <span class="badge-travel">ON TRAVEL</span>';
            else if (isOnLeave)  statusBadge = ' <span class="badge-leave">ON LEAVE</span>';
            else if (isAbsent)   statusBadge = ' <span class="badge-absent">ABSENT</span>';
            else if (hasAnyLog && !isComplete) statusBadge = ' <span class="badge-incomplete">Incomplete</span>';

            // ── Accredited hours pill ──
            let accreditedDisplay;
            if (isOnLeave || isOnTravel) {
                accreditedDisplay = `<span class="acc-pill acc-leave">8 hrs</span>`;
            } else if (isAbsent) {
                accreditedDisplay = `<span class="acc-pill acc-absent">0 hrs</span>`;
            } else if (record.accredited_minutes > 0) {
                const hrs   = Math.floor(record.accredited_minutes / 60);
                const mins  = record.accredited_minutes % 60;
                const label = hrs > 0 && mins > 0 ? `${hrs}h ${mins}m` : hrs > 0 ? `${hrs} hrs` : `${mins} min`;
                const cls   = record.accredited_minutes >= 480 ? 'acc-full' : 'acc-partial';
                accreditedDisplay = `<span class="acc-pill ${cls}">${label}</span>`;
            } else {
                accreditedDisplay = `<span class="acc-pill acc-incomplete">Incomplete</span>`;
            }

            // ── Leave deduction ──
            let leaveDeductionDisplay = '—';
            if (isOnTravel && record.travel_order_info) {
                const t = record.travel_order_info;
                leaveDeductionDisplay = `<span style="color:#0b044d;font-weight:600;">${t.order_number}</span><br><small style="color:#6b6a8a;font-size:10px;">${t.destination} (${t.duration} day${t.duration > 1 ? 's' : ''})</small>`;
            } else if (isOnLeave && record.leave_info) {
                const l = record.leave_info;
                leaveDeductionDisplay = `<span style="color:#0b044d;font-weight:600;">${l.leave_code}</span><br><small style="color:#6b6a8a;font-size:10px;">${l.leave_type} (${l.days} day${l.days > 1 ? 's' : ''})</small>`;
            } else if (record.leave_deduction && record.leave_deduction !== '-') {
                leaveDeductionDisplay = `<span style="color:#0b044d;font-weight:600;">${record.leave_deduction}</span>`;
            }

            // ── Timeline date cell ──
            const dotState = isAbsent ? 'absent'
                : isOnLeave || isOnTravel ? 'leave'
                : isWeekend ? 'weekend'
                : isLate ? 'late'
                : hasAnyLog && !isComplete ? 'review'
                : 'present';

            const dateObj  = new Date(record.date_key + 'T00:00:00');
            const dayNum   = dateObj.getDate();
            const monthStr = dateObj.toLocaleDateString('en-US', { month: 'short' });
            const dayStr   = record.day.substring(0, 3).toUpperCase();

            // ── Week separator ──
            const weekNum = getWeekNumber(dateObj);
            if (weekNum !== lastWeekNum) {
                const sepTr = document.createElement('tr');
                sepTr.className = 'week-sep';
                const weekStart = new Date(dateObj);
                weekStart.setDate(dateObj.getDate() - ((dateObj.getDay() + 6) % 7)); // Monday
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                const fmt = d => d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                sepTr.innerHTML = `<td colspan="9">Week ${weekNum} &nbsp;·&nbsp; ${fmt(weekStart)} – ${fmt(weekEnd)}</td>`;
                tbody.appendChild(sepTr);
                lastWeekNum = weekNum;
            }

            const dateCellHtml = `
                <div class="dtr-date-cell">
                    <div class="dtr-tl-track">
                        <div class="dtr-tl-line tl-line-${dotState}"></div>
                        <div class="dtr-tl-dot tl-${dotState}"></div>
                        <div class="dtr-tl-line tl-line-${dotState}"></div>
                    </div>
                    <div class="dtr-date-info">
                        <span class="dtr-date-num">${dayNum}</span>
                        <div class="dtr-date-meta">
                            <span class="dtr-date-sub">${monthStr} · ${dayStr}</span>
                            ${statusBadge}
                        </div>
                    </div>
                </div>`;

            const noTime = isOnLeave || isOnTravel;

            tr.innerHTML = `
                <td style="padding:0 8px;">${dateCellHtml}</td>
                <td><div class="dtr-time-cell"><div class="dtr-time-row">${renderTimePair(amIn, amOut)}</div></div></td>
                <td><div class="dtr-time-cell"><div class="dtr-time-row">${renderTimePair(pmIn, pmOut)}</div></div></td>
                <td>${fmt12(record.ot_in) ? fmt12(record.ot_in) + '<br><span style="color:#9aa1b5;font-size:11px;">' + (fmt12(record.ot_out) || '—') + '</span>' : '—'}</td>
                <td>${noTime ? '' : (record.undertime > 0 ? '<span class="log-late">' + record.undertime_display + '</span>' : (pmOut ? '0 min' : '—'))}</td>
                <td>${noTime ? '' : (record.late_minutes > 0 ? '<span class="log-late">' + record.late_display + '</span>' : (amIn ? '0 min' : '—'))}</td>
                <td><strong>${record.total_hours}</strong></td>
                <td>${accreditedDisplay}</td>
                <td>${leaveDeductionDisplay}</td>
            `;

            // stamp data attrs for the view dropdown filter
            tr.dataset.day = record.day;
            tr.dataset.state = isAbsent ? 'absent'
                : isOnLeave || isOnTravel ? 'leave'
                : isWeekend ? 'weekend'
                : !isComplete ? 'incomplete'
                : isLate ? 'late'
                : 'present';

            tbody.appendChild(tr);
        });

        // ── KPI cards ──
        const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        const workingDays = totalPresent + totalAbsent;
        const presentPct = workingDays > 0 ? Math.round((totalPresent / workingDays) * 100) : 0;

        setText('detailedKpiPresent', totalPresent);
        setText('detailedKpiPresentSub', presentPct + '% of ' + workingDays + ' work days');
        setText('detailedKpiAbsent', totalAbsent);
        setText('detailedKpiLate', totalLate);
        setText('detailedKpiLateSub', formatTotalMinutes(totalLateMinutes) + ' total');
        setText('detailedKpiLeave', totalLeave);
        setText('detailedKpiUndertime', formatTotalMinutes(totalUndertimeMinutes));
        setText('detailedKpiOvertime', formatHours(totalOvertimeMinutes));

        document.getElementById('detailedDTRLoading').style.display = 'none';
        document.getElementById('detailedDTRTable').style.display = records.length ? 'table' : 'none';

        if (!records.length) {
            document.getElementById('detailedDTRLoading').style.display = 'block';
            document.getElementById('detailedDTRLoading').innerHTML =
                '<p>No attendance records found for this period.</p>';
        }

        // Reset the view dropdown on every fresh load
        document.querySelectorAll('#ddtrDropdown .ddtr-dd-item').forEach(i => i.classList.remove('active'));
        document.querySelector('#ddtrDropdown [data-chip="all"]')?.classList.add('active');
        const lbl = document.getElementById('ddtrViewLabel');
        if (lbl) lbl.textContent = 'All Records';
    }

    // ── View dropdown ──
    function toggleDdtrDropdown() {
        const btn = document.getElementById('ddtrViewBtn');
        const dd  = document.getElementById('ddtrDropdown');
        const open = dd.classList.toggle('open');
        btn.classList.toggle('open', open);
    }

    document.getElementById('ddtrDropdown')?.addEventListener('click', function(e) {
        const item = e.target.closest('.ddtr-dd-item');
        if (!item) return;
        document.querySelectorAll('#ddtrDropdown .ddtr-dd-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        const lbl = document.getElementById('ddtrViewLabel');
        if (lbl) lbl.textContent = item.textContent.trim();
        document.getElementById('ddtrDropdown').classList.remove('open');
        document.getElementById('ddtrViewBtn').classList.remove('open');
        applyDtrChip(item.dataset.chip);
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#ddtrViewWrap')) {
            document.getElementById('ddtrDropdown')?.classList.remove('open');
            document.getElementById('ddtrViewBtn')?.classList.remove('open');
        }
    });

    function applyDtrChip(chip) {
        const dayMap = { mon: 'Monday', tue: 'Tuesday', wed: 'Wednesday', thu: 'Thursday', fri: 'Friday' };
        document.querySelectorAll('#detailedDTRBody tr:not(.week-sep)').forEach(tr => {
            const day   = tr.dataset.day   || '';
            const state = tr.dataset.state || '';
            let show = true;
            if      (chip === 'all')        show = true;
            else if (dayMap[chip])          show = day === dayMap[chip];
            else if (chip === 'weekdays')   show = ['Monday','Tuesday','Wednesday','Thursday','Friday'].includes(day);
            else if (chip === 'weekend')    show = ['Saturday','Sunday'].includes(day);
            else if (chip === 'present')    show = state === 'present';
            else if (chip === 'absent')     show = state === 'absent';
            else if (chip === 'late')       show = state === 'late';
            else if (chip === 'leave')      show = state === 'leave';
            else if (chip === 'incomplete') show = state === 'incomplete';
            tr.style.display = show ? '' : 'none';
        });
        syncWeekSeparators();
    }

    // Hide week separators whose rows are all filtered out
    function syncWeekSeparators() {
        document.querySelectorAll('#detailedDTRBody tr.week-sep').forEach(sep => {
            let next = sep.nextElementSibling;
            let hasVisible = false;
            while (next && !next.classList.contains('week-sep')) {
                if (next.style.display !== 'none') { hasVisible = true; break; }
                next = next.nextElementSibling;
            }
            sep.style.display = hasVisible ? '' : 'none';
        });
    }

    // ── Export ──
    function exportDetailedDTR() {
        if (!detailedRecords.length) {
            alert('No records to export');
            return;
        }

        const startDate = document.getElementById('detailedStartDate').value;
        const endDate   = document.getElementById('detailedEndDate').value;
        const dateRange = startDate === endDate ? startDate : `${startDate}_to_${endDate}`;

        let csv = 'Date,Day,AM In,AM Out,PM In,PM Out,OT In,OT Out,Late,Undertime,Total Hours,Accredited Hours,Status\n';

        detailedRecords.forEach(record => {
            const isOnLeave  = !!record.is_on_leave;
            const isOnTravel = !!record.is_on_travel_order;
            const amIn  = fmt12(record.am_in)  ? record.am_in  : '';
            const amOut = fmt12(record.am_out) ? record.am_out : '';
            const pmIn  = fmt12(record.pm_in)  ? record.pm_in  : '';
            const pmOut = fmt12(record.pm_out) ? record.pm_out : '';
            const hasAnyLog  = amIn || amOut || pmIn || pmOut;
            const isComplete = amIn && amOut && pmIn && pmOut;

            let status = 'Present';
            if (isOnTravel)      status = 'On Travel';
            else if (isOnLeave)  status = 'On Leave';
            else if (!hasAnyLog) status = 'Absent';
            else if (!isComplete) status = 'Incomplete';
            else if (record.late_minutes > 0) status = 'Late';

            const accredited = ((record.accredited_minutes || 0) / 60).toFixed(1) + ' hrs';

            csv += `${record.date},${record.day},${amIn},${amOut},${pmIn},${pmOut},`;
            csv += `${fmt12(record.ot_in) ? record.ot_in : ''},${fmt12(record.ot_out) ? record.ot_out : ''},`;
            csv += `${record.late_display || '-'},${record.undertime_display || '-'},`;
            csv += `${record.total_hours},${accredited},${status}\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Detailed_DTR_{{ $employee->employee_id }}_${dateRange}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
</script>

@include('employee.chatbot.employeeChatbot')

@endsection
