@extends('layouts.app')

@section('content')
@include('admin.topbar.attendanceTopbar')
@include('admin.notification.adminNotification')

@php
$avatarColors = ['#0b044d', '#8e1e18', '#150c63', '#a52820', '#150c63', '#56547a'];
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        if (preg_match('/^[A-Z]/', $part)) {
            $initials .= $part[0];
        }
    }
    return strtoupper(substr($initials, 0, 2));
}

$startDateDisplay = request('start_date', now()->startOfMonth()->format('Y-m-d'));
$endDateDisplay = request('end_date', now()->endOfMonth()->format('Y-m-d'));
$periodDisplay = date('M d, Y', strtotime($startDateDisplay)) . ' - ' . date('M d, Y', strtotime($endDateDisplay));
@endphp

<style>
.attendance-dashboard {
    --pri: #0b044d;
    --pri-2: #150c63;
    --bg: #F7F8FC;
    --card: #FFFFFF;
    --line: #eceaf8;
    --ink: #1E2247;
    --muted: #7C839D;
    --success: #2fa860;
    --danger: #e5484d;
    --warning: #eba417;
    --blue: #4F7CFF;
    --purple: #7C5CFF;
    --glass: rgba(255, 255, 255, .58);
    --glass-strong: rgba(255, 255, 255, .8);
    --glass-border: rgba(255, 255, 255, .65);
    --shadow: 0 1px 3px rgba(15, 23, 42, .03), 0 1px 2px rgba(15, 23, 42, .02);
    --shadow-hover: 0 6px 22px rgba(11, 4, 77, .08), 0 2px 6px rgba(11, 4, 77, .04);
    --radius-card: 18px;
    --radius-input: 8px;
    --radius-table: 20px;
    color: var(--ink);
    min-height: 100vh;
    padding: 0 0 48px;
    position: relative;
    isolation: isolate;
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", 'Poppins', sans-serif;
}
.attendance-dashboard::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: -1;
    background:
        radial-gradient(720px 480px at 6% -8%, rgba(99, 102, 241, .14), transparent 60%),
        radial-gradient(640px 440px at 100% 0%, rgba(56, 189, 248, .12), transparent 60%),
        radial-gradient(900px 620px at 50% 112%, rgba(79, 124, 255, .08), transparent 60%),
        var(--bg);
}

/* ============ STATISTICS CARDS ============ */
.attendance-dashboard .stats-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}

.attendance-dashboard .stat-card {
    border: 1px solid var(--glass-border);
    border-radius: 18px;
    background: linear-gradient(180deg, var(--glass-strong), var(--glass));
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .8),
        0 1px 3px rgba(15, 23, 42, .04),
        0 10px 30px rgba(15, 23, 42, .07);
    min-height: 132px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease;
}

.attendance-dashboard .stat-card:hover {
    border-color: rgba(255, 255, 255, .9);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .9),
        0 2px 4px rgba(15, 23, 42, .05),
        0 16px 40px rgba(11, 4, 77, .12);
    transform: translateY(-2px);
}

.attendance-dashboard .stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.attendance-dashboard .stat-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, .7);
    background: rgba(255, 255, 255, .55);
    backdrop-filter: blur(10px) saturate(160%);
    -webkit-backdrop-filter: blur(10px) saturate(160%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.attendance-dashboard .stat-label {
    color: var(--muted);
    font-size: 12px;
    font-weight: 700;
    margin: 0;
}

.attendance-dashboard .stat-value {
    color: var(--ink);
    font-size: 30px;
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -1px;
    margin: 0 0 16px;
}

.attendance-dashboard .stat-footer {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: auto;
}

.attendance-dashboard .stat-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.attendance-dashboard .stat-sub {
    color: var(--muted);
    font-size: 12px;
    margin: 0;
    font-weight: 500;
}

/* ============ ATTENDANCE OVERVIEW PANEL ============ */
.attendance-dashboard .overview-panel {
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-card);
    background: linear-gradient(180deg, var(--glass-strong), var(--glass));
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .8),
        0 10px 30px rgba(15, 23, 42, .06);
    padding: 18px 8px;
    margin-bottom: 18px;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    align-items: center;
}

.attendance-dashboard .ov-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 0 24px;
    position: relative;
}

.attendance-dashboard .ov-item + .ov-item::before {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    height: 40px;
    width: 1px;
    background: rgba(15, 23, 42, .08);
}

.attendance-dashboard .ov-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, .6);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.attendance-dashboard .ov-label {
    color: var(--muted);
    font-size: 11px;
    font-weight: 500;
    margin: 0 0 2px;
}
.attendance-dashboard .ov-value {
    color: var(--ink);
    font-size: 18px;
    font-weight: 800;
    line-height: 1.1;
    margin: 0;
    letter-spacing: -.5px;
}
.attendance-dashboard .ov-pct {
    color: var(--muted);
    font-size: 10px;
    font-weight: 500;
    margin: 2px 0 0;
}

/* ============ FILTER CARD ============ */
.attendance-dashboard .filter-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: linear-gradient(180deg, var(--glass-strong), var(--glass));
    border: 1px solid var(--glass-border);
    border-radius: 18px;
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8), 0 10px 30px rgba(15, 23, 42, .06);
    padding: 10px 14px;
    margin-bottom: 18px;
}
.attendance-dashboard .filter-card-fields {
    display: flex;
    align-items: center;
    gap: 4px;
    flex: 1;
    flex-wrap: wrap;
}
.attendance-dashboard .filter-card-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.attendance-dashboard .fld {
    position: relative;
    display: flex;
    align-items: center;
    flex: 1;
}
.attendance-dashboard .fld > svg {
    position: absolute;
    left: 10px;
    color: #9aa1b5;
    pointer-events: none;
    flex-shrink: 0;
}
.attendance-dashboard .fc-input,
.attendance-dashboard .fc-select {
    height: 38px;
    border: 1px solid rgba(15, 23, 42, .08);
    border-radius: 999px;
    background: rgba(255, 255, 255, .55);
    backdrop-filter: blur(10px) saturate(160%);
    -webkit-backdrop-filter: blur(10px) saturate(160%);
    color: var(--ink);
    font-size: 12.5px;
    font-family: inherit;
    outline: none;
    padding: 0 12px 0 32px;
    transition: border-color .2s ease, background .2s ease;
}
.attendance-dashboard .fc-input { flex: 1; min-width: 120px; }
.attendance-dashboard .fc-select {
    flex: 1; min-width: 120px;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    padding-right: 30px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%237C839D' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}
.attendance-dashboard .fc-input:hover,
.attendance-dashboard .fc-select:hover { background: rgba(255, 255, 255, .8); }
.attendance-dashboard .fc-input:focus,
.attendance-dashboard .fc-select:focus {
    border-color: var(--pri);
    background: rgba(255, 255, 255, .95);
    box-shadow: 0 0 0 3px rgba(11,10,77,.07);
}
.attendance-dashboard .fc-sep {
    color: var(--muted);
    font-size: 11px;
    font-weight: 600;
    padding: 0 6px;
    flex-shrink: 0;
}
.attendance-dashboard .fc-divider {
    width: 1px;
    height: 24px;
    background: rgba(15, 23, 42, .1);
    flex-shrink: 0;
    margin: 0 6px;
}

.attendance-dashboard .btn-solid,
.attendance-dashboard .btn-ghost {
    height: 44px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 20px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: transform .18s ease, background .18s ease, box-shadow .18s ease;
    white-space: nowrap;
    flex-shrink: 0;
}
.attendance-dashboard .btn-solid {
    background: linear-gradient(135deg, var(--pri-2), var(--pri));
    color: #fff;
    border: 1px solid transparent;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.25), 0 8px 20px rgba(11,10,77,.3);
}
.attendance-dashboard .btn-solid:hover { transform: translateY(-1px); box-shadow: inset 0 1px 0 rgba(255,255,255,.3), 0 10px 24px rgba(11,10,77,.38); }
.attendance-dashboard .btn-ghost {
    background: rgba(255, 255, 255, .55);
    backdrop-filter: blur(10px) saturate(160%);
    -webkit-backdrop-filter: blur(10px) saturate(160%);
    color: var(--ink);
    border: 1px solid rgba(15, 23, 42, .08);
    box-shadow: none;
}
.attendance-dashboard .btn-ghost:hover { background: rgba(255, 255, 255, .85); transform: translateY(-1px); }

/* ============ SEGMENTED TABS ============ */
.attendance-dashboard .seg-tabs {
    display: inline-flex;
    gap: 4px;
    padding: 5px;
    background: rgba(255, 255, 255, .5);
    border: 1px solid rgba(255, 255, 255, .6);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7), 0 4px 14px rgba(15,23,42,.04);
    border-radius: 16px;
    margin-bottom: 18px;
}
.attendance-dashboard .tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: none;
    background: transparent;
    color: var(--muted);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 12px;
    transition: all .2s ease;
    font-family: inherit;
}
.attendance-dashboard .tab-btn:hover { color: var(--pri); }
.attendance-dashboard .tab-btn.active {
    color: #fff;
    background: linear-gradient(135deg, var(--pri-2), var(--pri));
    box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 6px 16px rgba(11,4,77,.28);
}
.attendance-dashboard .tab-btn.active::after {
    content: "";
    display: block;
}

/* ============ TABLE CONTAINER ============ */
.attendance-dashboard .table-section {
    border: 1px solid var(--line);
    border-radius: var(--radius-table);
    background: var(--card);
    box-shadow: var(--shadow);
    margin-bottom: 18px;
}

.attendance-dashboard .table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    background: #fff;
}

.attendance-dashboard .table-title {
    color: var(--ink);
    font-size: 15px;
    font-weight: 800;
    margin: 0;
    letter-spacing: -.3px;
}

.attendance-dashboard .table-sub {
    color: var(--muted);
    font-size: 12px;
    margin: 4px 0 0;
    font-weight: 500;
}

.attendance-dashboard .table-wrapper { overflow-x: auto; }

.attendance-dashboard .payroll-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.attendance-dashboard .payroll-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8fafc;
    color: #667085;
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    padding: 8px 20px;
    text-align: left;
    white-space: nowrap;
}

.attendance-dashboard .payroll-table td {
    padding: 8px 20px;
    border-bottom: 1px solid var(--line);
    font-size: 13px;
    height: 44px;
    vertical-align: middle;
}
.attendance-dashboard .payroll-table tbody tr:nth-child(even) { background: #fcfdff; }
.attendance-dashboard .payroll-table tbody tr {
    transition: background .2s ease;
}
.attendance-dashboard .payroll-table tbody tr:hover { background: #eef4ff; }
.attendance-dashboard .payroll-table tbody tr:last-child td { border-bottom: none; }

.attendance-dashboard .emp-cell {
    display: flex;
    align-items: center;
    gap: 14px;
}
.attendance-dashboard .emp-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}
.attendance-dashboard .emp-id {
    font-size: 11px;
    color: var(--muted);
    margin: 2px 0 0;
    font-weight: 500;
}

.attendance-dashboard .dept-tag {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    background: #e3ecff;
    color: #2547b0;
    white-space: nowrap;
    position: relative;
    cursor: default;
}
.attendance-dashboard .dept-tag[data-tooltip]:hover::after {
    content: attr(data-tooltip);
    position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);
    background: #1a1f36; color: #fff; font-size: 11px; font-weight: 500;
    padding: 6px 10px; border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,.18); z-index: 99; pointer-events: none;
    max-width: 240px; white-space: normal; text-align: center; line-height: 1.4;
}
.attendance-dashboard .dept-tag[data-tooltip]:hover::before {
    content: '';
    position: absolute; bottom: calc(100% + 1px); left: 50%; transform: translateX(-50%);
    border: 5px solid transparent; border-top-color: #1a1f36; z-index: 99;
}

.attendance-dashboard .num-cell { text-align: center; font-size: 12px; font-weight: 600; }
.attendance-dashboard .num-present { color: var(--success); }
.attendance-dashboard .num-leave   { color: var(--blue); }
.attendance-dashboard .num-absent  { color: var(--danger); }
.attendance-dashboard .num-late    { color: var(--warning); }
.attendance-dashboard .num-ot      { color: var(--pri); }
.attendance-dashboard .num-muted   { color: #b9bed0; font-weight: 500; }

/* rate progress bar */
.attendance-dashboard .rate-cell { display: flex; align-items: center; gap: 10px; min-width: 100px; }
.attendance-dashboard .rate-track {
    flex: 1;
    height: 6px;
    background: #eef0f6;
    border-radius: 999px;
    overflow: hidden;
    min-width: 50px;
}
.attendance-dashboard .rate-fill {
    height: 100%;
    border-radius: 999px;
    transition: width .8s cubic-bezier(.22,.61,.36,1);
}
.attendance-dashboard .rate-fill.good { background: linear-gradient(90deg, #2fa860, #15803d); }
.attendance-dashboard .rate-fill.mid  { background: linear-gradient(90deg, #eba417, #c98a0c); }
.attendance-dashboard .rate-fill.low  { background: linear-gradient(90deg, #e5484d, #c93a3a); }
.attendance-dashboard .rate-pct { font-size: 11px; font-weight: 700; color: var(--ink); white-space: nowrap; min-width: 32px; text-align: right; }

/* status pills */
.attendance-dashboard .badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    text-transform: none;
}
.attendance-dashboard .badge-status::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}
.attendance-dashboard .badge-status.processed { background: #e9f9ef; color: #15803d; }
.attendance-dashboard .badge-status.pending   { background: #fbf6e3; color: #c9a227; }

/* icon action buttons */
.attendance-dashboard .row-actions { display: flex; align-items: center; gap: 8px; }
.attendance-dashboard .act-btn {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: 1px solid var(--line);
    background: #fff;
    color: var(--muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s ease;
}
.attendance-dashboard .act-btn:hover {
    color: var(--pri);
    border-color: #cdd4ea;
    background: #f6f7fc;
    transform: translateY(-2px);
}
.attendance-dashboard .act-btn.primary:hover { color: #fff; background: var(--pri); border-color: var(--pri); }

/* Settings tab: primary action + icon buttons (unstyled elsewhere, scoped here) */
.attendance-dashboard .btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 16px;
    background: var(--pri);
    color: #fff;
    border: 1px solid var(--pri);
    border-radius: 9px;
    font-size: 12.5px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(11,4,77,.18);
    transition: background .2s cubic-bezier(.4,0,.2,1), transform .15s ease, box-shadow .2s cubic-bezier(.4,0,.2,1);
}
.attendance-dashboard .btn-primary:hover { background: var(--pri-2); }
.attendance-dashboard .btn-primary:active { transform: scale(.97); }

.attendance-dashboard .btn-edit,
.attendance-dashboard .btn-delete {
    width: 34px;
    height: 34px;
    padding: 0;
    border-radius: 9px;
    border: 1px solid var(--line);
    background: #fff;
    color: var(--muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background .2s cubic-bezier(.4,0,.2,1), color .2s cubic-bezier(.4,0,.2,1), border-color .2s cubic-bezier(.4,0,.2,1), transform .15s ease;
}
.attendance-dashboard .btn-edit:hover { color: var(--pri); border-color: #cdd4ea; background: #f6f7fc; }
.attendance-dashboard .btn-delete:hover { color: #e5484d; border-color: #f7d4d1; background: #fdedec; }
.attendance-dashboard .btn-edit:active,
.attendance-dashboard .btn-delete:active { transform: scale(.93); }

.attendance-dashboard .exempt-pill {
    background: #e9f9ef;
    color: #15803d;
    padding: 3px 9px;
    border-radius: 999px;
    font-weight: 600;
}

/* footer + pagination */
.attendance-dashboard .table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-top: 1px solid var(--line);
    background: #fcfdff;
    font-size: 12px;
    color: var(--muted);
    flex-wrap: wrap;
    gap: 12px;
}
.attendance-dashboard .rows-select {
    height: 36px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #fff;
    color: var(--ink);
    font-size: 12px;
    font-family: 'Poppins', sans-serif;
    padding: 0 10px;
    cursor: pointer;
    outline: none;
}
.attendance-dashboard .pagination { display: flex; gap: 6px; }
.attendance-dashboard .page-btn {
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #fff;
    color: var(--muted);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s ease;
    font-family: 'Poppins', sans-serif;
}
.attendance-dashboard .page-btn:hover:not(:disabled) { border-color: var(--pri); color: var(--pri); }
.attendance-dashboard .page-btn.active {
    background: var(--pri);
    color: #fff;
    border-color: var(--pri);
    box-shadow: 0 6px 16px rgba(11,10,77,.20);
}
.attendance-dashboard .page-btn:disabled { opacity: .5; cursor: not-allowed; }

/* ============ ACTION DROPDOWN ============ */
.action-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 6px);
    background: #fff;
    border: 1px solid #e9edf6;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(15,23,42,.12);
    z-index: 100;
    min-width: 160px;
    padding: 4px;
    white-space: nowrap;
}
.action-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 14px;
    border: none;
    background: transparent;
    color: #1E2247;
    font-size: 12.5px;
    font-weight: 500;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    border-radius: 7px;
    text-align: left;
    transition: background .15s ease;
}
.action-dropdown-item:hover { background: #f4f6ff; color: #0b044d; }

/* ============ RESPONSIVE ============ */
@media (max-width: 1280px) {
    .attendance-dashboard .overview-panel { grid-template-columns: repeat(3, 1fr); row-gap: 24px; }
    .attendance-dashboard .ov-item:nth-child(3n+1)::before { display: none; }
}
@media (max-width: 1100px) {
    .attendance-dashboard .stats-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 768px) {
    .attendance-dashboard .stats-grid-4 { grid-template-columns: 1fr; }
    .attendance-dashboard .overview-panel { grid-template-columns: repeat(2, 1fr); }
    .attendance-dashboard .ov-item::before { display: none !important; }
    .attendance-dashboard .filter-card { flex-direction: column; align-items: stretch; }
    .attendance-dashboard .filter-card-fields { flex-direction: column; }
    .attendance-dashboard .filter-card-actions { width: 100%; justify-content: stretch; }
    .attendance-dashboard .filter-card-actions .btn-solid,
    .attendance-dashboard .filter-card-actions .btn-ghost { flex: 1; justify-content: center; }
    .attendance-dashboard .fc-input, .attendance-dashboard .fc-select { width: 100%; }
    .attendance-dashboard .fc-divider { width: 100%; height: 1px; margin: 4px 0; }
    .attendance-dashboard .seg-tabs { display: flex; width: 100%; overflow-x: auto; }
    .attendance-dashboard .table-header { flex-direction: column; align-items: flex-start; gap: 12px; }
}
</style>

<main class="attendance-dashboard">

@php
    $totalRecords = $completeCount + $incompleteCount;
    $attendanceRate = $totalRecords > 0 ? round(($completeCount / $totalRecords) * 100, 1) : 0;
    $totalHalfDay = array_sum(array_column($attendanceRecords, 'halfday'));
    $onLeaveTotal = $totalOnLeave ?? 0;
    $dayTotal = $totalPresent + $totalAbsent + $onLeaveTotal;
    $presentPct = $dayTotal > 0 ? round(($totalPresent / $dayTotal) * 100, 1) : 0;
    $leavePct   = $dayTotal > 0 ? round(($onLeaveTotal / $dayTotal) * 100, 1) : 0;
    $absentPct  = $dayTotal > 0 ? round(($totalAbsent / $dayTotal) * 100, 1) : 0;
@endphp

{{-- ============ STATISTICS CARDS ============ --}}
<div class="stats-grid-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Present</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($totalPresent) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#2fa860"></span>
            <p class="stat-sub">Present days logged</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">On Leave</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($onLeaveTotal) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#4F7CFF"></span>
            <p class="stat-sub">Approved leave days</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Absent</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="8" x2="22" y2="13"/><line x1="22" y1="8" x2="17" y2="13"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($totalAbsent) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#e5484d"></span>
            <p class="stat-sub">Absences recorded</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Overtime Hours</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $totalOT }} hrs</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#eba417"></span>
            <p class="stat-sub">{{ $totalLate }} late arrivals</p>
        </div>
    </div>
</div>

{{-- ============ ATTENDANCE OVERVIEW PANEL ============ --}}
<div class="overview-panel">
    <div class="ov-item">
        <div class="ov-icon" style="background:#e9f9ef;color:#15803d;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <p class="ov-label">Present</p>
            <p class="ov-value">{{ number_format($totalPresent) }}</p>
            <p class="ov-pct">{{ $presentPct }}%</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon" style="background:#eaf1ff;color:#4F7CFF;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <div>
            <p class="ov-label">On Leave</p>
            <p class="ov-value">{{ number_format($onLeaveTotal) }}</p>
            <p class="ov-pct">{{ $leavePct }}%</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon" style="background:#fdedec;color:#e5484d;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
            <p class="ov-label">Absent</p>
            <p class="ov-value">{{ number_format($totalAbsent) }}</p>
            <p class="ov-pct">{{ $absentPct }}%</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon" style="background:#fbf6e3;color:#eba417;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><line x1="22" y1="2" x2="18" y2="6"/></svg>
        </div>
        <div>
            <p class="ov-label">Late Arrivals</p>
            <p class="ov-value">{{ number_format($totalLate) }}</p>
            <p class="ov-pct">Times late</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon" style="background:#f2effd;color:#7C5CFF;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <p class="ov-label">Overtime</p>
            <p class="ov-value">{{ $totalOT }} hrs</p>
            <p class="ov-pct">Total hours</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon" style="background:#eef0f8;color:#0B0A4D;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
            <p class="ov-label">Records</p>
            <p class="ov-value">{{ number_format(count($attendanceRecords)) }}</p>
            <p class="ov-pct">Total employees</p>
        </div>
    </div>
</div>

{{-- ============ FILTER TOOLBAR ============ --}}
<form method="GET" action="{{ route('admin.attendance') }}" id="attendanceFilterForm">
    <div class="filter-card">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" name="start_date" class="fc-input" value="{{ $startDateDisplay }}" title="Start date">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" name="end_date" class="fc-input" value="{{ $endDateDisplay }}" title="End date">
            </div>
            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                <select name="department" class="fc-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select name="status" class="fc-select">
                    <option value="">All Status</option>
                    <option value="Complete" {{ request('status') == 'Complete' ? 'selected' : '' }}>Complete</option>
                    <option value="Incomplete" {{ request('status') == 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button type="submit" class="btn-solid">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
            <button type="button" class="btn-ghost" id="attendanceExportBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>
</form>

{{-- ============ SEGMENTED TABS ============ --}}
<div class="seg-tabs">
    <button class="tab-btn active" onclick="switchTab('summary')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Attendance Summary
    </button>
    <button class="tab-btn" onclick="switchTab('detailed')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Detailed Time Record
    </button>
    <button class="tab-btn" onclick="switchTab('settings')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Attendance Config
    </button>
</div>

@include('admin.attendance.partials.attendance-summary-tab')
@include('admin.attendance.partials.detailed-time-record-tab')
@include('admin.attendance.partials.attendance-settings-tab')

</main>

{{-- Modals live outside .attendance-dashboard on purpose: that wrapper has
     `isolation: isolate` (to contain its own decorative ::before gradient),
     which makes it establish a stacking context with z-index:auto (≈0).
     Any position:fixed modal nested inside it — no matter how high its own
     z-index — can never out-rank the sidebar (z-index: 200), because the
     comparison happens one level up, between the whole .attendance-dashboard
     bubble (level 0) and the sidebar (level 200). Keeping modals as siblings
     of .attendance-dashboard lets each one's own z-index compete directly
     against the sidebar instead. --}}
@include('admin.attendance.modals.dtrDetailModal')
@include('admin.attendance.modals.detailedDtrModal')
@include('admin.attendance.modals.editDtrModal')
@include('admin.attendance.modals.correctAttendanceModal')
@include('admin.attendance.modals.successModal')

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    window.periodDisplay = '{{ $periodDisplay }}';
    window.periodDisplayFile = '{{ str_replace([' ', ',', '-'], '_', $periodDisplay) }}';

    // Tab switching functionality
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');

        event.target.closest('.tab-btn').classList.add('active');
        document.getElementById(tabName + '-tab').style.display = 'block';
    }

    // Check URL parameter and switch to correct tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');

        if (activeTab === 'detailed') {
            // Switch to detailed tab
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');

            document.querySelectorAll('.tab-btn')[1].classList.add('active');
            document.getElementById('detailed-tab').style.display = 'block';
        } else if (activeTab === 'settings') {
            // Switch to settings tab
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');

            document.querySelectorAll('.tab-btn')[2].classList.add('active');
            document.getElementById('settings-tab').style.display = 'block';
        }
    });

    // Search functionality
    function searchAttendance(query) {
        const searchTerm = query.toLowerCase().trim();
        const tbody = document.querySelector('.payroll-table tbody');
        if (!tbody) return;

        if (!window.allAttendanceRows || window.allAttendanceRows.length === 0) {
            window.allAttendanceRows = Array.from(tbody.querySelectorAll('tr'));
        }

        const filtered = window.allAttendanceRows.filter(row => {
            const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
            const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
            const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
            return searchTerm === '' || name.includes(searchTerm) || id.includes(searchTerm) || dept.includes(searchTerm);
        });

        tbody.innerHTML = '';
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" style="text-align: center; padding: 40px; color: #56547a;">No records found matching your search.</td></tr>';
        } else {
            filtered.forEach(row => tbody.appendChild(row.cloneNode(true)));
        }
    }

    // Attendance Summary Pagination
    window._attendanceCurrentPage = 1;
    window._attendanceRowsPerPage = 10;

    window.filterAttendanceSummary = function () {
        const allRows = document.querySelectorAll('#attendanceSummaryBody tr[data-id]');
        const filtered = [];

        allRows.forEach(row => {
            filtered.push(row);
        });

        window._attendanceFilteredRows = filtered;
        window._attendanceCurrentPage = 1;
        updateAttendancePagination();
    };

    window.updateAttendancePagination = function () {
        const rows = window._attendanceFilteredRows || [];
        const total = rows.length;
        const perPage = window._attendanceRowsPerPage;
        const totalPages = Math.ceil(total / perPage) || 1;
        const page = Math.min(window._attendanceCurrentPage, totalPages);
        window._attendanceCurrentPage = page;

        const start = (page - 1) * perPage;
        const end = Math.min(start + perPage, total);

        document.querySelectorAll('#attendanceSummaryBody tr[data-id]').forEach(row => row.style.display = 'none');
        rows.forEach((row, i) => { if (i >= start && i < end) row.style.display = ''; });

        document.getElementById('attendanceRowStart').textContent = total ? start + 1 : 0;
        document.getElementById('attendanceRowEnd').textContent = end;
        document.getElementById('attendanceRowTotal').textContent = total;

        const controls = document.getElementById('attendancePaginationControls');
        if (totalPages <= 1) { controls.innerHTML = ''; return; }

        let html = '';
        const maxVisible = 5;
        let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

        if (page > 1) html += '<button class="page-btn" onclick="goToAttendancePage(' + (page - 1) + ')">‹</button>';
        if (startPage > 1) {
            html += '<button class="page-btn" onclick="goToAttendancePage(1)">1</button>';
            if (startPage > 2) html += '<span style="padding:0 8px;color:#8f8daf;">...</span>';
        }
        for (let i = startPage; i <= endPage; i++) {
            html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToAttendancePage(' + i + ')">' + i + '</button>';
        }
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<span style="padding:0 8px;color:#8f8daf;">...</span>';
            html += '<button class="page-btn" onclick="goToAttendancePage(' + totalPages + ')">' + totalPages + '</button>';
        }
        if (page < totalPages) html += '<button class="page-btn" onclick="goToAttendancePage(' + (page + 1) + ')">›</button>';

        controls.innerHTML = html;
    };

    window.goToAttendancePage = function (page) {
        window._attendanceCurrentPage = page;
        updateAttendancePagination();
    };

    window.changeAttendanceRowsPerPage = function () {
        window._attendanceRowsPerPage = parseInt(document.getElementById('attendanceRowsPerPage').value) || 10;
        window._attendanceCurrentPage = 1;
        updateAttendancePagination();
    };

    // Initialize pagination on page load
    document.addEventListener('DOMContentLoaded', function() {
        filterAttendanceSummary();
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.row-actions')) closeAllActionMenus();
        });
    });

    function toggleActionMenu(e, id) {
        e.stopPropagation();
        const menu = document.getElementById(id);
        const isOpen = menu.style.display === 'block';
        closeAllActionMenus();
        if (!isOpen) menu.style.display = 'block';
    }

    function closeAllActionMenus() {
        document.querySelectorAll('.action-dropdown').forEach(m => m.style.display = 'none');
    }
</script>
@vite('resources/js/adminAttendance.js')
@endpush
@endsection
