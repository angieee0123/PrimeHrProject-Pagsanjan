@extends('layouts.app')

@section('content')

@php
    $adminName = optional(Auth::user())->name ?? 'Admin';
    $adminInitials = collect(explode(' ', trim($adminName)))->filter()->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('') ?: 'AD';
@endphp

<main class="enterprise-hr-dashboard">

@include('admin.topbar.adminTopbar')
@include('admin.notification.adminNotification')


{{-- Enhanced Stats Grid with Trends --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card" style="cursor:pointer;transition:all 0.3s" onclick="window.location.href='#employee-directory'" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-top">
            <p class="stat-label">Total Employees</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['total_employees']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">+{{ $stats['new_this_month'] }} new this month</p>
        </div>
    </div>

    <div class="stat-card" style="cursor:pointer;transition:all 0.3s" onclick="document.getElementById('birdsTabTitle').scrollIntoView({behavior:'smooth'})" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-top">
            <p class="stat-label">Present Today</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['present_today']) }}<span style="font-size:14px;color:#8f8daf;font-weight:500"> / {{ number_format($stats['total_employees']) }}</span></p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#22c55e"></span>
            <p class="stat-sub">{{ $stats['attendance_rate'] }}% attendance rate</p>
        </div>
    </div>

    <div class="stat-card" style="cursor:pointer;transition:all 0.3s" onclick="document.querySelector('.table-header .table-title').scrollIntoView({behavior:'smooth'})" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-top">
            <p class="stat-label" style="display:flex;align-items:center;gap:6px">
                On Leave Today
                @if($stats['pending_leave'] > 0)
                <span style="font-size:10px;font-weight:700;color:#fff;background:#8e1e18;padding:2px 7px;border-radius:4px;line-height:1.5">{{ $stats['pending_leave'] }}</span>
                @endif
            </p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['on_leave']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#c9a227"></span>
            <p class="stat-sub">{{ $stats['pending_leave'] }} pending approval</p>
        </div>
    </div>

    <div class="stat-card" style="cursor:pointer;transition:all 0.3s" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-top">
            <p class="stat-label">Monthly Payroll</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="#0b044d" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
            </div>
        </div>
        <p class="stat-value" style="font-size:20px">₱{{ number_format($stats['monthly_payroll'] / 1000000, 2) }}M</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">{{ now()->format('M Y') }} total</p>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.welcome-banner {
    position: relative;
    overflow: hidden;
    border-radius: 20px !important;
    border: 1px solid rgba(255, 255, 255, .18) !important;
    background:
        radial-gradient(340px 200px at 100% -20%, rgba(129, 140, 248, .35), transparent 70%),
        linear-gradient(135deg, #0b044d 0%, #150c63 100%) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .16),
        0 16px 40px rgba(11, 4, 77, .28) !important;
}

.welcome-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255, 255, 255, .08), transparent 45%);
    pointer-events: none;
}

.banner-icon {
    border: 1px solid rgba(255, 255, 255, .22) !important;
    background: rgba(255, 255, 255, .12) !important;
    backdrop-filter: blur(10px) saturate(180%);
    -webkit-backdrop-filter: blur(10px) saturate(180%);
}

.banner-badge {
    border: 1px solid rgba(255, 255, 255, .16);
    background: rgba(255, 255, 255, .12) !important;
    backdrop-filter: blur(10px) saturate(180%);
    -webkit-backdrop-filter: blur(10px) saturate(180%);
}

.banner-badge.outline {
    background: transparent !important;
}

.enterprise-hr-dashboard {
    --eh-blue: #0b044d;
    --eh-blue-2: #1b1464;
    --eh-bg: #f6f8fb;
    --eh-card: #ffffff;
    --eh-ink: #111827;
    --eh-muted: #667085;
    --eh-soft: #f2f4f7;
    --eh-line: #e5e7eb;
    --eh-green: #15803d;
    --eh-red: #b42318;
    --eh-amber: #b7791f;
    --eh-glass: rgba(255, 255, 255, .58);
    --eh-glass-strong: rgba(255, 255, 255, .78);
    --eh-glass-border: rgba(255, 255, 255, .65);
    --eh-radius: 18px;
    color: var(--eh-ink);
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", 'Poppins', sans-serif;
    min-height: 100vh;
    padding: 20px 0 28px;
    position: relative;
    isolation: isolate;
}

.enterprise-hr-dashboard::before {
    content: '';
    position: fixed;
    inset: 0;
    z-index: -1;
    background:
        radial-gradient(720px 480px at 6% -8%, rgba(99, 102, 241, .18), transparent 60%),
        radial-gradient(640px 440px at 100% 0%, rgba(56, 189, 248, .16), transparent 60%),
        radial-gradient(900px 620px at 50% 112%, rgba(236, 72, 153, .12), transparent 60%),
        var(--eh-bg);
}

.enterprise-hr-dashboard,
.enterprise-hr-dashboard * {
    letter-spacing: 0;
}

.enterprise-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
    padding: 22px 24px;
    border: 1px solid var(--eh-line);
    border-radius: 12px;
    background: var(--eh-card);
    box-shadow: 0 2px 8px rgba(15, 23, 42, .04), 0 1px 3px rgba(15, 23, 42, .03);
}

.enterprise-kicker {
    display: inline-flex;
    margin-bottom: 7px;
    color: var(--eh-blue);
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}

.enterprise-header h1 {
    margin: 0;
    color: var(--eh-ink);
    font-size: clamp(24px, 3vw, 34px);
    line-height: 1.1;
    font-weight: 800;
}

.enterprise-header p {
    margin: 8px 0 0;
    color: var(--eh-muted);
    font-size: 13px;
}

.enterprise-header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.enterprise-icon-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border: 1px solid var(--eh-line);
    border-radius: 10px;
    background: #fff;
    color: var(--eh-blue);
    cursor: pointer;
    transition: all .18s ease;
}

.enterprise-icon-btn:hover {
    border-color: #ecebf6;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
    transform: translateY(-1px);
}

.enterprise-dot {
    position: absolute;
    top: 9px;
    right: 10px;
    width: 8px;
    height: 8px;
    border: 2px solid #fff;
    border-radius: 50%;
    background: #ef4444;
}

.enterprise-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 188px;
    padding: 8px 12px 8px 8px;
    border: 1px solid var(--eh-line);
    border-radius: 10px;
    background: #fff;
}

.enterprise-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--eh-blue);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
}

.enterprise-profile strong,
.enterprise-profile span {
    display: block;
    white-space: nowrap;
}

.enterprise-profile strong {
    color: var(--eh-ink);
    font-size: 13px;
    font-weight: 750;
}

.enterprise-profile span {
    color: var(--eh-muted);
    font-size: 11px;
}

.enterprise-hr-dashboard .stats-grid-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}

.enterprise-hr-dashboard .stat-card,
.enterprise-hr-dashboard .table-section,
.enterprise-hr-dashboard .chart-card,
.enterprise-card {
    border: 1px solid var(--eh-glass-border) !important;
    border-radius: var(--eh-radius) !important;
    background: linear-gradient(180deg, var(--eh-glass-strong), var(--eh-glass)) !important;
    backdrop-filter: blur(24px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .8),
        0 1px 3px rgba(15, 23, 42, .04),
        0 10px 30px rgba(15, 23, 42, .07) !important;
    position: relative;
    isolation: isolate;
}

.enterprise-hr-dashboard .stat-card {
    min-height: 132px;
    padding: 20px !important;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}

.enterprise-hr-dashboard .stat-card:hover,
.enterprise-hr-dashboard .table-section:hover,
.enterprise-hr-dashboard .chart-card:hover {
    border-color: rgba(255, 255, 255, .9) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .9),
        0 2px 4px rgba(15, 23, 42, .05),
        0 16px 40px rgba(11, 4, 77, .12) !important;
}

@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
    .enterprise-hr-dashboard .stat-card,
    .enterprise-hr-dashboard .table-section,
    .enterprise-hr-dashboard .chart-card,
    .enterprise-card {
        background: rgba(255, 255, 255, .96) !important;
    }
}

.enterprise-hr-dashboard .stat-top {
    margin-bottom: 16px;
}

.enterprise-hr-dashboard .stat-label,
.enterprise-hr-dashboard .table-sub,
.enterprise-hr-dashboard .chart-sub {
    color: var(--eh-muted) !important;
}

.enterprise-hr-dashboard .stat-label {
    font-size: 12px !important;
    font-weight: 700 !important;
}

.enterprise-hr-dashboard .stat-value {
    color: var(--eh-ink) !important;
    font-size: 30px !important;
    line-height: 1.05;
}

.enterprise-hr-dashboard .stat-value span {
    color: var(--eh-muted) !important;
}

.enterprise-hr-dashboard .stat-icon-wrap {
    width: 40px !important;
    height: 40px !important;
    border: 1px solid rgba(255, 255, 255, .7);
    border-radius: 12px !important;
    background: rgba(255, 255, 255, .55) !important;
    backdrop-filter: blur(10px) saturate(160%);
    -webkit-backdrop-filter: blur(10px) saturate(160%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
}

.enterprise-hr-dashboard .stat-sub {
    color: var(--eh-muted) !important;
    font-size: 12px !important;
}

.enterprise-action-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    padding: 14px 16px;
    border: 1px solid var(--eh-glass-border) !important;
    border-radius: var(--eh-radius) !important;
    background: linear-gradient(180deg, var(--eh-glass-strong), var(--eh-glass)) !important;
    backdrop-filter: blur(24px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8), 0 10px 30px rgba(15, 23, 42, .06);
}

.enterprise-action-spacer {
    margin-right: auto;
}

.enterprise-overview-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 18px;
    align-items: stretch;
    margin-bottom: 18px;
}

.enterprise-secondary-grid {
    display: grid;
    grid-template-columns: .78fr 1fr 360px;
    gap: 18px;
    align-items: stretch;
    margin-bottom: 18px;
}

.enterprise-compact-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 18px;
}

.enterprise-card-body {
    padding: 16px 20px 20px;
}

.enterprise-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.enterprise-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #eef2f6;
}

.enterprise-list-item:last-child {
    border-bottom: 0;
}

.enterprise-rank {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 8px;
    background: #f2f4f7;
    color: var(--eh-blue);
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
}

.enterprise-person {
    min-width: 0;
    flex: 1;
}

.enterprise-person strong,
.enterprise-person span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.enterprise-person strong {
    color: var(--eh-ink);
    font-size: 12.5px;
    font-weight: 800;
}

.enterprise-person span {
    color: var(--eh-muted);
    font-size: 10.5px;
}

.enterprise-metric {
    color: var(--eh-blue);
    font-size: 12px;
    font-weight: 850;
    white-space: nowrap;
}

.enterprise-progress {
    height: 6px;
    overflow: hidden;
    border-radius: 999px;
    background: #eef2f6;
}

.enterprise-progress > span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: var(--eh-blue);
}

.enterprise-sidebar-card .payroll-table th,
.enterprise-sidebar-card .payroll-table td {
    padding-left: 12px;
    padding-right: 12px;
}

.enterprise-hr-dashboard .table-header,
.enterprise-hr-dashboard .chart-header {
    align-items: center;
    padding: 18px 20px !important;
    border-bottom: 1px solid rgba(15, 23, 42, .06) !important;
    background: transparent !important;
    border-radius: var(--eh-radius) var(--eh-radius) 0 0 !important;
}

.enterprise-hr-dashboard .table-title,
.enterprise-hr-dashboard .chart-title {
    color: var(--eh-ink) !important;
    font-size: 15px !important;
    font-weight: 800 !important;
}

.enterprise-hr-dashboard .charts-grid {
    gap: 18px;
}

.enterprise-hr-dashboard .chart-card {
    padding: 0 !important;
}

.enterprise-hr-dashboard .chart-card canvas {
    padding: 0 16px 16px;
}

.enterprise-hr-dashboard .chart-tab,
.enterprise-hr-dashboard .btn-export,
.enterprise-hr-dashboard .modal-btn-primary,
.enterprise-hr-dashboard .filter-select,
.enterprise-hr-dashboard .btn-view,
.enterprise-hr-dashboard input[type="text"] {
    border-radius: 999px !important;
    font-weight: 700 !important;
    transition: all .22s cubic-bezier(.4, 0, .2, 1) !important;
}

.enterprise-hr-dashboard .chart-tab.active,
.enterprise-hr-dashboard .modal-btn-primary {
    border-color: transparent !important;
    background: linear-gradient(135deg, var(--eh-blue-2), var(--eh-blue)) !important;
    color: #fff !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .25),
        0 8px 20px rgba(11, 4, 77, .35) !important;
}

.enterprise-hr-dashboard .chart-tab.active:hover,
.enterprise-hr-dashboard .modal-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .3),
        0 10px 26px rgba(11, 4, 77, .42) !important;
}

.enterprise-hr-dashboard .btn-export,
.enterprise-hr-dashboard .filter-select,
.enterprise-hr-dashboard input[type="text"] {
    border-color: rgba(15, 23, 42, .08) !important;
    background: rgba(255, 255, 255, .55) !important;
    backdrop-filter: blur(12px) saturate(160%) !important;
    -webkit-backdrop-filter: blur(12px) saturate(160%) !important;
    color: #56547a !important;
}

.enterprise-hr-dashboard .btn-export:hover {
    background: rgba(255, 255, 255, .85) !important;
    border-color: rgba(15, 23, 42, .12) !important;
    transform: translateY(-1px);
}

.enterprise-hr-dashboard .chart-tab:not(.active):hover {
    background: rgba(11, 4, 77, .06) !important;
}

.enterprise-hr-dashboard .payroll-table {
    border-collapse: separate;
    border-spacing: 0;
}

.enterprise-hr-dashboard .table-wrapper {
    max-width: 100%;
    overflow: auto;
}

.enterprise-hr-dashboard .payroll-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: rgba(248, 250, 252, .75);
    backdrop-filter: blur(10px) saturate(160%);
    -webkit-backdrop-filter: blur(10px) saturate(160%);
    color: #667085;
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
}

.enterprise-hr-dashboard .payroll-table td {
    border-bottom: 1px solid rgba(15, 23, 42, .05);
}

.enterprise-hr-dashboard .payroll-table tbody tr:hover {
    background: rgba(11, 4, 77, .035);
}

.enterprise-hr-dashboard .dept-tag,
.enterprise-hr-dashboard .badge-status {
    border-radius: 999px !important;
    font-weight: 800 !important;
}

.enterprise-hr-dashboard #panelEarly > div,
.enterprise-hr-dashboard #panelLate > div {
    border-color: rgba(255, 255, 255, .6) !important;
    border-radius: 14px !important;
    background: rgba(255, 255, 255, .5) !important;
    backdrop-filter: blur(14px) saturate(160%);
    -webkit-backdrop-filter: blur(14px) saturate(160%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7), 0 2px 6px rgba(15, 23, 42, .03);
    transition: all .22s cubic-bezier(.4, 0, .2, 1);
}

.enterprise-hr-dashboard #panelEarly > div:hover,
.enterprise-hr-dashboard #panelLate > div:hover {
    border-color: rgba(255, 255, 255, .85) !important;
    background: rgba(255, 255, 255, .72) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .85), 0 10px 24px rgba(11, 4, 77, .1);
    transform: translateY(-2px);
}

.enterprise-hr-dashboard .event-icon {
    border-radius: 8px !important;
}

#employee-directory {
    overflow: hidden;
}

#employee-directory .table-header {
    gap: 16px;
}

@media (max-width: 1200px) {
    .enterprise-hr-dashboard .stats-grid-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .enterprise-overview-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 1024px) {
    .enterprise-secondary-grid {
        grid-template-columns: 1fr 1fr;
    }

    .enterprise-compact-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .enterprise-hr-dashboard .chart-header {
        flex-wrap: wrap;
        gap: 10px;
    }
}

@media (max-width: 860px) {
    .enterprise-secondary-grid,
    .enterprise-compact-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 760px) {
    .enterprise-header,
    .enterprise-header-actions,
    .enterprise-hr-dashboard .table-header,
    .enterprise-hr-dashboard .table-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .enterprise-header {
        padding: 18px;
    }

    .enterprise-profile {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .enterprise-icon-btn {
        width: 100%;
    }

    .enterprise-action-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .enterprise-action-bar button {
        width: 100% !important;
        justify-content: center;
    }

    .enterprise-action-spacer {
        margin-right: 0;
        margin-bottom: 4px;
    }

    .enterprise-hr-dashboard .stats-grid-4,
    .enterprise-hr-dashboard .charts-grid {
        grid-template-columns: 1fr !important;
    }

    #panelEarly,
    #panelLate {
        grid-template-columns: 1fr !important;
    }

    .enterprise-hr-dashboard .table-actions > *,
    .enterprise-hr-dashboard .table-actions input,
    .enterprise-hr-dashboard .table-actions select,
    .enterprise-hr-dashboard .table-actions button {
        width: 100% !important;
    }

    .enterprise-hr-dashboard .chart-header > div:last-child {
        flex-wrap: wrap;
        gap: 6px;
        border-left: none !important;
        padding-left: 0 !important;
    }

    #employee-directory .table-header {
        gap: 12px;
    }
}

@media (max-width: 540px) {
    .enterprise-hr-dashboard .stats-grid-4 {
        grid-template-columns: 1fr !important;
    }

    .enterprise-hr-dashboard .stat-value {
        font-size: 24px !important;
    }

    #viewEmployeeDashboardContent > div {
        grid-template-columns: 1fr !important;
        display: grid !important;
    }

    #performerDetailsModal > div {
        margin: 0 8px;
        border-radius: 14px;
    }

    #performerDetailsModal [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }

    .enterprise-hr-dashboard .chart-header {
        padding: 14px 16px !important;
    }
}

#birdScheduleTooltip {
    position: fixed;
    z-index: 9999;
    background: linear-gradient(180deg, rgba(27, 20, 100, .82), rgba(11, 4, 77, .88));
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, .12);
    color: #fff;
    border-radius: 16px;
    min-width: 220px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.1), 0 12px 32px rgba(11,4,77,0.35), 0 4px 8px rgba(0,0,0,0.15);
    pointer-events: none;
    font-family: inherit;
    overflow: hidden;
}

#birdScheduleTooltip::after {
    content: '';
    position: absolute;
    border: 7px solid transparent;
    left: 50%;
    transform: translateX(-50%);
    pointer-events: none;
}

#birdScheduleTooltip.bst-arrow-down::after { top: 100%; border-top-color: #1b1464; }
#birdScheduleTooltip.bst-arrow-up::after   { bottom: 100%; border-bottom-color: #1b1464; }

.bst-header {
    padding: 10px 14px 8px;
    font-size: 10px;
    font-weight: 800;
    color: #a5b4fc;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.bst-body {
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.bst-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.bst-period {
    font-size: 10px;
    font-weight: 800;
    color: #818cf8;
    min-width: 22px;
    text-transform: uppercase;
}

.bst-time {
    font-size: 12px;
    font-weight: 600;
    color: #e0e7ff;
}

.bst-sep { font-size: 10px; color: #6366f1; }

.bst-dates {
    padding: 6px 14px 10px;
    font-size: 10px;
    color: #94a3b8;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.bst-no-schedule {
    padding: 14px;
    font-size: 11px;
    color: #94a3b8;
    text-align: center;
}

.bird-item { position: relative; }
</style>

{{-- Quick Actions Bar --}}
<div class="enterprise-action-bar">
    <div style="display:flex;align-items:center;gap:10px" class="enterprise-action-spacer">
        <div style="width:34px;height:34px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;color:#0b044d">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
        <div>
            <p style="font-size:13px;font-weight:800;color:#111827;margin:0">Quick Actions</p>
            <p style="font-size:11px;color:#667085;margin:0">Frequently used HR workflows</p>
        </div>
    </div>
    <button class="modal-btn-primary" onclick="openAddEmployee()" style="font-size:11px;padding:0 14px;height:34px;display:flex;align-items:center;gap:6px">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        Add Employee
    </button>
    <button class="btn-export" onclick="window.location.href='/admin/attendance'" style="font-size:11px;padding:0 14px;height:34px">Attendance</button>
    <button class="btn-export" onclick="window.location.href='/admin/leave'" style="font-size:11px;padding:0 14px;height:34px">Leave Request</button>
    <button class="btn-export" onclick="window.location.href='/admin/payroll'" style="font-size:11px;padding:0 14px;height:34px">Payroll</button>
    <button class="btn-export" style="font-size:11px;padding:0 14px;height:34px">Report</button>
</div>

{{-- Main reference layout: wide chart + right requests --}}
<div class="enterprise-overview-grid">
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <p class="chart-title" id="dynamicChartTitle">Payroll by Designation</p>
                <p class="chart-sub" id="dynamicChartSub">Total payroll amounts per designation</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <div style="display:flex;gap:6px">
                    <button id="tabSalary" class="chart-tab active" onclick="switchMainChart('salary')" style="font-size:11px">Payroll by Designation</button>
                    <button id="tabEmployees" class="chart-tab" onclick="switchMainChart('employees')" style="font-size:11px">Employees</button>
                </div>
                <div class="chart-tabs" id="periodTabs" style="border-left:1px solid #e2e8f0;padding-left:8px">
                    <button class="chart-tab active" onclick="switchPeriodChart('week')">Week</button>
                    <button class="chart-tab" onclick="switchPeriodChart('month')">Month</button>
                    <button class="chart-tab" onclick="switchPeriodChart('year')">Year</button>
                </div>
            </div>
        </div>
        <canvas id="dynamicChart" style="max-height:310px"></canvas>
    </div>

    <div class="table-section enterprise-sidebar-card" style="margin-bottom:0">
        <div class="table-header">
            <div>
                <p class="table-title">Pending Requests</p>
                <p class="table-sub">Requires immediate approval</p>
            </div>
            <button class="btn-export" id="pendingRequestsViewAllBtn" style="font-size:11px;padding:6px 12px" onclick="window.location.href='/admin/leave'">View All</button>
        </div>
        <div style="display:flex;gap:6px;padding:0 20px 14px">
            <button type="button" class="chart-tab active" id="pendingTabLeaveBtn" onclick="switchPendingRequestsTab('leave')" style="font-size:11px">
                Leave{{ $stats['pending_leave'] > 0 ? ' (' . $stats['pending_leave'] . ')' : '' }}
            </button>
            <button type="button" class="chart-tab" id="pendingTabPassSlipBtn" onclick="switchPendingRequestsTab('passslip')" style="font-size:11px">
                Pass Slip{{ $pendingPassSlips > 0 ? ' (' . $pendingPassSlips . ')' : '' }}
            </button>
        </div>
        <div class="enterprise-card-body" id="pendingLeaveTabPanel">
            <div class="enterprise-list">
                @forelse($leaveRequests as $l)
                <div class="enterprise-list-item" style="cursor:default;padding:12px 0;position:relative">
                    @if($l['photo'])
                        <img src="{{ $l['photo'] }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $l['color'] }}" style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px">{{ $l['initials'] }}</div>
                    @endif
                    <div class="enterprise-person" style="flex:1">
                        <strong>{{ $l['name'] }}</strong>
                        <span>{{ $l['type'] }} · {{ $l['days'] }}</span>
                    </div>
                    <button onclick="toggleLeaveMenu(event)" style="background:none;border:none;color:#8f8daf;cursor:pointer;padding:4px 8px;border-radius:6px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;flex-shrink:0" onmouseover="this.style.background='#f1f5f9';this.style.color='#0b044d'" onmouseout="this.style.background='none';this.style.color='#8f8daf'">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                    </button>
                    <div class="leave-action-menu" style="display:none;position:absolute;right:0;top:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 12px rgba(15,23,42,0.12);z-index:100;min-width:140px;margin-top:4px">
                        <button onclick="approveLeave(event)" style="width:100%;padding:10px 12px;border:none;background:#0b044d;color:#fff;text-align:left;font-size:12px;font-weight:600;cursor:pointer;border-bottom:1px solid #e5e7eb;transition:all 0.2s;border-radius:6px 6px 0 0" onmouseover="this.style.background='#1b1464'" onmouseout="this.style.background='#0b044d'">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display:inline;margin-right:6px;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg>
                            Approve
                        </button>
                        <button onclick="disapproveLeave(event)" style="width:100%;padding:10px 12px;border:none;background:none;text-align:left;font-size:12px;color:#b42318;font-weight:600;cursor:pointer;border-bottom:1px solid #e5e7eb;transition:all 0.2s" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display:inline;margin-right:6px;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Disapprove
                        </button>
                        <button onclick="viewLeaveDetails(event)" style="width:100%;padding:10px 12px;border:none;background:none;text-align:left;font-size:12px;color:#0b044d;font-weight:600;cursor:pointer;transition:all 0.2s;border-radius:0 0 6px 6px" onmouseover="this.style.background='#f2f1fb'" onmouseout="this.style.background='none'">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display:inline;margin-right:6px;vertical-align:middle"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            View
                        </button>
                    </div>
                </div>
                @empty
                <p class="table-sub" style="text-align:center;padding:28px 0;margin:0">No pending requests</p>
                @endforelse
            </div>
        </div>
        <div class="enterprise-card-body" id="pendingPassSlipTabPanel" style="display:none">
            <div class="enterprise-list">
                @forelse($passSlipRequests as $p)
                <div class="enterprise-list-item" style="cursor:default;padding:12px 0;position:relative">
                    @if($p['photo'])
                        <img src="{{ $p['photo'] }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $p['color'] }}" style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px">{{ $p['initials'] }}</div>
                    @endif
                    <div class="enterprise-person" style="flex:1">
                        <strong>{{ $p['name'] }}</strong>
                        <span>{{ $p['type_label'] }}{{ $p['destination'] ? ' · ' . $p['destination'] : '' }}</span>
                    </div>
                    <button onclick="togglePassSlipMenuDash(event)" style="background:none;border:none;color:#8f8daf;cursor:pointer;padding:4px 8px;border-radius:6px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;flex-shrink:0" onmouseover="this.style.background='#f1f5f9';this.style.color='#0b044d'" onmouseout="this.style.background='none';this.style.color='#8f8daf'">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                    </button>
                    <div class="leave-action-menu" style="display:none;position:absolute;right:0;top:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 12px rgba(15,23,42,0.12);z-index:100;min-width:160px;margin-top:4px">
                        <button onclick="window.location.href='/admin/passslip'" style="width:100%;padding:10px 12px;border:none;background:#0b044d;color:#fff;text-align:left;font-size:12px;font-weight:600;cursor:pointer;border-radius:6px 6px 0 0" onmouseover="this.style.background='#1b1464'" onmouseout="this.style.background='#0b044d'">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display:inline;margin-right:6px;vertical-align:middle"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Review in Pass Slip
                        </button>
                    </div>
                </div>
                @empty
                <p class="table-sub" style="text-align:center;padding:28px 0;margin:0">No pending pass slips</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@php
    $top5Month = $attendancePerformanceMonth->whereIn('tier', ['excellent','good'])->take(5)->values();
    $top5Week  = $attendancePerformanceWeek->whereIn('tier',  ['excellent','good'])->take(5)->values();
    $bottom5Month = $attendancePerformanceMonth->whereIn('tier', ['needs_improvement','poor'])->sortBy('rate')->take(5)->values();
    $bottom5Week  = $attendancePerformanceWeek->whereIn('tier',  ['needs_improvement','poor'])->sortBy('rate')->take(5)->values();
@endphp

<div class="enterprise-secondary-grid">
    <div class="table-section" style="margin:0">
        <div class="table-header">
            <div>
                <p class="table-title">Top Performers</p>
                <p class="table-sub" id="perfPeriodSub">{{ $perfPeriodMonth }}</p>
            </div>
            <div style="display:flex;gap:6px">
                <button id="perfTabMonth" onclick="switchPerfTab('month')" class="chart-tab active">Prev Month</button>
                <button id="perfTabWeek" onclick="switchPerfTab('week')" class="chart-tab">Prev Week</button>
            </div>
        </div>
        <div class="enterprise-card-body">
            <div id="perfPanelMonth" class="enterprise-list">
                @forelse($top5Month as $i => $emp)
                <div class="enterprise-list-item" onclick='showPerformerDetails(@json($emp), "month", {{ $i + 1 }})' style="cursor:pointer">
                    <span class="enterprise-rank">{{ $i + 1 }}</span>
                    @if($emp['photo'])
                        <img src="{{ $emp['photo'] }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px">{{ $emp['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:6px">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <div class="enterprise-person" style="margin:0">
                                <strong>{{ $emp['name'] }}</strong>
                                <span>{{ $emp['position'] }}</span>
                            </div>
                            <span class="enterprise-metric">{{ $emp['rate'] }}%</span>
                        </div>
                        <div style="width:100%;height:6px;background:#eef2f6;border-radius:3px;overflow:hidden">
                            <div style="width:{{ $emp['rate'] }}%;height:100%;background:linear-gradient(90deg,#3b82f6,#2563eb);border-radius:3px;transition:width 0.3s ease"></div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="table-sub" style="text-align:center;padding:28px 0;margin:0">No top performers yet</p>
                @endforelse
            </div>
            <div id="perfPanelWeek" class="enterprise-list" style="display:none">
                @forelse($top5Week as $i => $emp)
                <div class="enterprise-list-item" onclick='showPerformerDetails(@json($emp), "week", {{ $i + 1 }})' style="cursor:pointer">
                    <span class="enterprise-rank">{{ $i + 1 }}</span>
                    @if($emp['photo'])
                        <img src="{{ $emp['photo'] }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px">{{ $emp['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:6px">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <div class="enterprise-person" style="margin:0">
                                <strong>{{ $emp['name'] }}</strong>
                                <span>{{ $emp['position'] }}</span>
                            </div>
                            <span class="enterprise-metric">{{ $emp['rate'] }}%</span>
                        </div>
                        <div style="width:100%;height:6px;background:#eef2f6;border-radius:3px;overflow:hidden">
                            <div style="width:{{ $emp['rate'] }}%;height:100%;background:linear-gradient(90deg,#3b82f6,#2563eb);border-radius:3px;transition:width 0.3s ease"></div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="table-sub" style="text-align:center;padding:28px 0;margin:0">No top performers yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="chart-card" style="display:flex;flex-direction:column">
        <div class="chart-header">
            <div>
                <p class="chart-title">Attendance Trend</p>
                <p class="chart-sub">Daily attendance rate</p>
            </div>
            <div class="chart-tabs">
                <button class="chart-tab active" onclick="switchAttendanceChart('week')">Week</button>
                <button class="chart-tab" onclick="switchAttendanceChart('month')">Month</button>
                <button class="chart-tab" onclick="switchAttendanceChart('year')">Year</button>
            </div>
        </div>
        <div style="flex:1;padding:0 16px 16px 16px;display:flex;align-items:stretch">
            <canvas id="attendanceChart" style="width:100%;height:100%"></canvas>
        </div>
    </div>

    <div class="table-section enterprise-sidebar-card" style="margin:0">
        <div class="table-header">
            <div>
                <p class="table-title">Top 5 Highest Earners</p>
                <p class="table-sub">Based on average daily earnings this month</p>
            </div>
        </div>
        <div style="padding:16px 20px 20px">
            <div style="display:flex;flex-direction:column;gap:12px">
                @forelse($topEarners as $earner)
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:{{ $earner['rank'] < 5 ? '1px solid #f1f5f9' : 'none' }}">
                        <div style="min-width:26px;height:26px;border-radius:9px;text-align:center;display:inline-flex;align-items:center;justify-content:center;font-size:{{ $earner['rank'] <= 3 ? '13px' : '12px' }};font-weight:{{ $earner['rank'] === 1 ? '800' : '700' }};background:{{ $earner['rank'] <= 3 ? '#fef3c7' : '#f1f5f9' }};color:{{ $earner['rank'] <= 3 ? '#f59e0b' : '#94a3b8' }}">{{ $earner['rank'] }}</div>
                        @if($earner['photo'])
                            <img src="{{ $earner['photo'] }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0">
                        @else
                            <div class="emp-avatar-dynamic" data-bg="{{ $earner['color'] }}" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;border:2px solid #e2e8f0;flex-shrink:0">{{ $earner['initials'] }}</div>
                        @endif
                        <div style="flex:1;min-width:0">
                            <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $earner['name'] }}</p>
                            <p style="font-size:11px;color:#64748b;margin:0;font-weight:500">{{ $earner['designation'] }}</p>
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <p style="font-size:14px;font-weight:700;color:#0b044d;margin:0">₱{{ number_format($earner['avg_earnings'], 2) }}</p>
                            <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:500">avg/day</p>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:32px 24px">
                        <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                            <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        </div>
                        <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Salary Data Available</p>
                        <p style="font-size:12px;color:#94a3b8;margin:0">Salary computations will appear here</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="enterprise-compact-grid">
    <div class="table-section" style="margin:0">
        <div class="table-header">
            <div>
                <p class="table-title" id="birdsTabTitle">Top 5 Early Birds</p>
                <p class="table-sub">{{ $attendanceDate->isToday() ? 'Today' : $attendanceDate->format('F d, Y') }} · Earliest clock-ins</p>
            </div>
            <div style="display:flex;gap:6px">
                <button id="tabEarly" onclick="switchBirdsTab('early')" class="chart-tab active">Earliest</button>
                <button id="tabLate" onclick="switchBirdsTab('late')" class="chart-tab">Late arrivals</button>
            </div>
        </div>
        <div id="panelEarly" style="padding:16px 20px 20px">
            <div style="display:flex;flex-direction:column;gap:12px">
                @forelse($earlyBirds as $index => $bird)
                    @php
                        $rank = $index + 1;
                        $avatarSize = $rank === 1 ? 48 : ($rank === 2 ? 44 : ($rank === 3 ? 40 : ($rank === 4 ? 38 : 36)));
                        $nameSize = $rank === 1 ? '13.5px' : ($rank === 2 ? '13px' : '12.5px');
                        $positionSize = $rank === 1 ? '11.5px' : '11px';
                        $timeSize = $rank === 1 ? '13px' : ($rank === 2 ? '12.5px' : '12px');
                        $padding = $rank === 1 ? '12px 0' : ($rank === 2 ? '10px 0' : '8px 0');
                    @endphp
                    <div class="bird-item" data-schedule='@json($bird['schedule'])' style="display:flex;align-items:center;gap:12px;padding:{{ $padding }};border-bottom:{{ $rank < 5 ? '1px solid #f1f5f9' : 'none' }}">
                        <div style="min-width:26px;text-align:center;font-size:{{ $rank <= 3 ? '13px' : '12px' }};font-weight:{{ $rank === 1 ? '800' : '700' }};color:{{ $rank === 1 ? '#fbbf24' : ($rank === 2 ? '#94a3b8' : ($rank === 3 ? '#fb923c' : '#94a3b8')) }}">{{ $rank }}</div>
                        @if($bird['photo'])
                            <img src="{{ $bird['photo'] }}" style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:10px;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0">
                        @else
                            <div class="emp-avatar-dynamic" data-bg="{{ $bird['color'] }}" style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:{{ $rank === 1 ? '18px' : ($rank === 2 ? '16px' : '14px') }};border:2px solid #e2e8f0;flex-shrink:0">{{ $bird['initials'] }}</div>
                        @endif
                        <div style="flex:1;min-width:0">
                            <p style="font-size:{{ $nameSize }};font-weight:{{ $rank === 1 ? '700' : '600' }};color:#1e293b;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $bird['name'] }}</p>
                            <p style="font-size:{{ $positionSize }};color:#64748b;margin:0;font-weight:500">{{ $bird['position'] }}</p>
                        </div>
                        <div style="text-align:right;flex-shrink:0;margin-right:16px">
                            <p style="font-size:{{ $timeSize }};font-weight:700;color:#0b044d;margin:0">{{ $bird['time_in'] }}</p>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:32px 24px">
                        <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                            <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Attendance Records Today</p>
                        <p style="font-size:12px;color:#94a3b8;margin:0">Check back later as employees clock in</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div id="panelLate" style="display:none;padding:16px 20px 20px">
            <div style="display:flex;flex-direction:column;gap:12px">
                @forelse($lateBirds as $index => $bird)
                    @php
                        $rank = $index + 1;
                        $avatarSize = $rank === 1 ? 48 : ($rank === 2 ? 44 : ($rank === 3 ? 40 : ($rank === 4 ? 38 : 36)));
                        $nameSize = $rank === 1 ? '13.5px' : ($rank === 2 ? '13px' : '12.5px');
                        $positionSize = $rank === 1 ? '11.5px' : '11px';
                        $timeSize = $rank === 1 ? '13px' : ($rank === 2 ? '12.5px' : '12px');
                        $padding = $rank === 1 ? '12px 0' : ($rank === 2 ? '10px 0' : '8px 0');
                    @endphp
                    <div class="bird-item" data-schedule='@json($bird['schedule'])' style="display:flex;align-items:center;gap:12px;padding:{{ $padding }};border-bottom:{{ $rank < 5 ? '1px solid #fee2e2' : 'none' }}">
                        <div style="min-width:26px;text-align:center;font-size:{{ $rank <= 3 ? '13px' : '12px' }};font-weight:700;color:#dc2626">{{ $rank }}</div>
                        @if($bird['photo'])
                            <img src="{{ $bird['photo'] }}" style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:10px;object-fit:cover;border:2px solid #fecaca;flex-shrink:0">
                        @else
                            <div class="emp-avatar-dynamic" data-bg="{{ $bird['color'] }}" style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:{{ $rank === 1 ? '18px' : ($rank === 2 ? '16px' : '14px') }};border:2px solid #fecaca;flex-shrink:0">{{ $bird['initials'] }}</div>
                        @endif
                        <div style="flex:1;min-width:0">
                            <p style="font-size:{{ $nameSize }};font-weight:{{ $rank === 1 ? '700' : '600' }};color:#1e293b;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $bird['name'] }}</p>
                            <p style="font-size:{{ $positionSize }};color:#64748b;margin:0;font-weight:500">{{ $bird['position'] }}</p>
                        </div>
                        <div style="text-align:right;flex-shrink:0;margin-right:16px">
                            <p style="font-size:{{ $timeSize }};font-weight:700;color:#0b044d;margin:0 0 3px">{{ $bird['time_in'] }}</p>
                            <p style="font-size:10px;color:#dc2626;font-weight:600;margin:0">+{{ $bird['late_minutes'] }}m</p>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:32px 24px">
                        <div style="width:48px;height:48px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                            <svg width="22" height="22" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Late Arrivals Today</p>
                        <p style="font-size:12px;color:#94a3b8;margin:0">All employees arrived on time! 🎉</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="table-section" style="margin:0">
        <div class="table-header">
            <div>
                <p class="table-title">Recent Leave Filers</p>
                <p class="table-sub">Latest 5 leave applications filed</p>
            </div>
            <button class="btn-export" style="font-size:11px;padding:6px 12px" onclick="window.location.href='/admin/leave'">View All</button>
        </div>
        <div style="padding:16px 20px 20px">
            <div style="display:flex;flex-direction:column;gap:12px">
                @forelse($recentLeaveFilers as $filer)
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:{{ $filer['rank'] < 5 ? '1px solid #f1f5f9' : 'none' }}">
                        <div style="min-width:26px;height:26px;border-radius:8px;text-align:center;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;background:#f1f5f9;color:#64748b">{{ $filer['rank'] }}</div>
                        @if($filer['photo'])
                            <img src="{{ $filer['photo'] }}" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0">
                        @else
                            <div class="emp-avatar-dynamic" data-bg="{{ $filer['color'] }}" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;border:2px solid #e2e8f0;flex-shrink:0">{{ $filer['initials'] }}</div>
                        @endif
                        <div style="flex:1;min-width:0">
                            <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $filer['name'] }}</p>
                            <p style="font-size:11px;color:#64748b;margin:0 0 3px;font-weight:500">{{ $filer['position'] }}</p>
                            <p style="font-size:10px;color:#94a3b8;margin:0">{{ $filer['leave_type'] }} · {{ $filer['days'] }} day{{ $filer['days'] > 1 ? 's' : '' }}</p>
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <div style="font-size:10px;padding:4px 8px;border-radius:6px;font-weight:700;background:{{ $filer['status_bg'] }};color:{{ $filer['status_color'] }};margin-bottom:4px">{{ $filer['status'] }}</div>
                            <p style="font-size:10px;color:#94a3b8;margin:0;font-weight:500">{{ $filer['start_date'] }} - {{ $filer['end_date'] }}</p>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:32px 24px">
                        <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                            <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Recent Leave Applications</p>
                        <p style="font-size:12px;color:#94a3b8;margin:0">Leave applications will appear here</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="table-section" style="margin:0">
        <div class="table-header">
            <div>
                <p class="table-title">Department Distribution</p>
                <p class="table-sub">Headcount by department</p>
            </div>
        </div>
        @php $total = $stats['total_employees']; $maxCount = $departments->max('count') ?? 1; @endphp
        <div class="enterprise-card-body" style="padding:20px 24px;max-height:100%;overflow-y:auto" id="deptDistContainer">
            @foreach($departments as $d)
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;height:46px">
                <div style="min-width:110px;font-weight:700;color:#111827;font-size:12px">{{ $d['name'] }}</div>
                <div style="flex:1;height:32px;background:#f3f4f6;border-radius:6px;position:relative;overflow:hidden">
                    <div style="height:100%;background:{{ $d['color'] }};border-radius:6px;display:flex;align-items:center;justify-content:flex-end;padding:0 10px;transition:width 0.3s ease;width:{{ $maxCount > 0 ? round($d['count']/$maxCount*100) : 0 }}%">
                        <span style="color:#fff;font-weight:800;font-size:11px;text-shadow:0 1px 2px rgba(0,0,0,0.15)">{{ $d['count'] }}</span>
                    </div>
                </div>
                <div style="min-width:60px;text-align:right;color:#667085;font-weight:600;font-size:11px">{{ $d['percentage'] }}%</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
{{-- Employee Directory Table - Full Width --}}
<div class="table-section" id="employee-directory">
    <div class="table-header" style="background:linear-gradient(135deg,#f2f1fb 0%,#fff 100%)">
        <div>
            <p class="table-title" style="display:flex;align-items:center;gap:8px">
                Employee Directory
            </p>
            <p class="table-sub">All active government personnel | Real-time data</p>
        </div>
        <div class="table-actions">
            <div style="position:relative;margin-right:8px">
                <svg width="14" height="14" fill="none" stroke="#8f8daf" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" id="searchEmployee" placeholder="Search employees..." style="font-size:11px;padding:6px 12px 6px 32px;border-radius:6px;border:1.5px solid #e5e4f0;width:200px;font-family:inherit" oninput="searchEmployees(this.value)">
            </div>
            <select class="filter-select" id="filterDept" onchange="applyFilters()" style="font-size:11px">
                <option value="">All Departments</option>
                <option>Administration</option>
                <option>Engineering</option>
                <option>Health</option>
                <option>Finance</option>
                <option>HRMO</option>
            </select>
            <select class="filter-select" id="filterType" onchange="applyFilters()" style="font-size:11px">
                <option value="">All Types</option>
                <option>Permanent</option>
                <option>Job Order</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <button class="modal-btn-primary" onclick="openAddEmployee()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Employee
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr data-dept="{{ $emp['dept'] }}" data-type="{{ $emp['type'] }}">
                    <td>
                        <div class="emp-cell">
                            @if($emp['photo'])
                                <img src="{{ $emp['photo'] }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #ecebf6;">
                            @else
                                <div class="emp-avatar emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:600; font-size:13px; border:2px solid #ecebf6;">{{ $emp['initials'] }}</div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $emp['name'] }}</p>
                                <p class="emp-id">{{ $emp['employee_id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="position-cell">{{ $emp['position'] }}</span></td>
                    <td><span class="dept-tag">{{ $emp['dept'] }}</span></td>
                    <td><span class="dept-tag emp-type-tag {{ $emp['type']==='Permanent' ? 'is-permanent' : 'is-joborder' }}">{{ $emp['type'] }}</span></td>
                    <td>
                        @if($emp['status']==='active')
                            <span class="badge-status processed">Active</span>
                        @else
                            <span class="badge-status pending">Inactive</span>
                        @endif
                    </td>
                    <td><button class="btn-view" onclick='viewEmployeeDashboard({{ $emp["id"] ?? 0 }})'>View</button></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:#8f8daf;">No employees found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span id="filterCount">Showing <strong>{{ $employees->firstItem() ?? 0 }}–{{ $employees->lastItem() ?? 0 }}</strong> of <strong>{{ $employees->total() }}</strong> employees</span>
        <div class="pagination">
            @if($employees->onFirstPage())
                <button class="page-btn" disabled>‹</button>
            @else
                <a href="{{ $employees->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                @if($page == $employees->currentPage())
                    <button class="page-btn active">{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if($employees->hasMorePages())
                <a href="{{ $employees->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <button class="page-btn" disabled>›</button>
            @endif
        </div>
    </div>
</div>

</main>

{{-- Performer Details Modal --}}
<div id="performerDetailsModal" style="display:none;position:fixed;inset:0;background:rgba(11,4,77,0.55);backdrop-filter:blur(10px) saturate(140%);-webkit-backdrop-filter:blur(10px) saturate(140%);z-index:2000;align-items:center;justify-content:center;padding:20px">
    <div style="position:relative;background:linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.78));backdrop-filter:blur(28px) saturate(180%);-webkit-backdrop-filter:blur(28px) saturate(180%);border:1px solid rgba(255,255,255,.7);border-radius:24px;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:inset 0 1px 0 rgba(255,255,255,.85), 0 30px 70px rgba(11,4,77,.28), 0 8px 24px rgba(15,23,42,.1)">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:28px 32px;border-bottom:1px solid rgba(15,23,42,.06)">
            <div style="display:flex;align-items:center;gap:20px">
                <div id="modalPerformerAvatar" style="width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid rgba(255,255,255,.8);box-shadow:0 8px 20px rgba(11,4,77,.15)"></div>
                <div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                        <h3 id="modalPerformerName" style="margin:0;font-size:22px;font-weight:700;color:#0b044d"></h3>
                        <span id="modalPerformerRank" style="font-size:22px"></span>
                    </div>
                    <p id="modalPerformerPosition" style="margin:0 0 2px;font-size:14px;color:#64748b;font-weight:500"></p>
                    <p id="modalPerformerDept" style="margin:0;font-size:13px;color:#94a3b8"></p>
                </div>
            </div>
            <button onclick="closePerformerModal()" style="background:rgba(15,23,42,.05);border:1px solid rgba(15,23,42,.06);font-size:18px;color:#64748b;cursor:pointer;width:36px;height:36px;border-radius:999px;display:flex;align-items:center;justify-content:center;transition:all .2s" onmouseover="this.style.background='rgba(11,4,77,.1)';this.style.color='#0b044d'" onmouseout="this.style.background='rgba(15,23,42,.05)';this.style.color='#64748b'">&times;</button>
        </div>
        <div style="padding:32px">
            <div style="background:linear-gradient(135deg, rgba(239,246,255,.85) 0%, rgba(219,234,254,.7) 100%);backdrop-filter:blur(16px) saturate(160%);-webkit-backdrop-filter:blur(16px) saturate(160%);border:1px solid rgba(255,255,255,.6);border-radius:20px;padding:24px;margin-bottom:20px;box-shadow:inset 0 1px 0 rgba(255,255,255,.7), 0 8px 24px rgba(59,130,246,.08)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                    <div>
                        <p style="font-size:11px;color:#3b82f6;font-weight:700;letter-spacing:0.5px;margin:0 0 4px">ATTENDANCE PERFORMANCE</p>
                        <p id="modalPeriodLabel" style="font-size:12px;color:#64748b;margin:0"></p>
                    </div>
                    <div style="text-align:right">
                        <p id="modalAttendanceRate" style="font-size:42px;font-weight:800;color:#3b82f6;margin:0;line-height:1"></p>
                        <p style="font-size:11px;color:#3b82f6;margin:6px 0 0;font-weight:600">Attendance Rate</p>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                    <div style="background:rgba(255,255,255,.65);backdrop-filter:blur(12px) saturate(160%);-webkit-backdrop-filter:blur(12px) saturate(160%);border:1px solid rgba(255,255,255,.7);border-radius:14px;padding:16px;text-align:center;box-shadow:inset 0 1px 0 rgba(255,255,255,.8), 0 4px 14px rgba(15,23,42,.05)">
                        <p style="font-size:28px;font-weight:800;color:#22c55e;margin:0" id="modalPresentDays"></p>
                        <p style="font-size:11px;color:#64748b;margin:6px 0 0;font-weight:600">Days Present</p>
                    </div>
                    <div style="background:rgba(255,255,255,.65);backdrop-filter:blur(12px) saturate(160%);-webkit-backdrop-filter:blur(12px) saturate(160%);border:1px solid rgba(255,255,255,.7);border-radius:14px;padding:16px;text-align:center;box-shadow:inset 0 1px 0 rgba(255,255,255,.8), 0 4px 14px rgba(15,23,42,.05)">
                        <p style="font-size:28px;font-weight:800;color:#ef4444;margin:0" id="modalAbsentDays"></p>
                        <p style="font-size:11px;color:#64748b;margin:6px 0 0;font-weight:600">Days Absent</p>
                    </div>
                    <div style="background:rgba(255,255,255,.65);backdrop-filter:blur(12px) saturate(160%);-webkit-backdrop-filter:blur(12px) saturate(160%);border:1px solid rgba(255,255,255,.7);border-radius:14px;padding:16px;text-align:center;box-shadow:inset 0 1px 0 rgba(255,255,255,.8), 0 4px 14px rgba(15,23,42,.05)">
                        <p style="font-size:28px;font-weight:800;color:#f59e0b;margin:0" id="modalLateDays"></p>
                        <p style="font-size:11px;color:#64748b;margin:6px 0 0;font-weight:600">Late Arrivals</p>
                    </div>
                </div>
            </div>

            <div style="background:rgba(248,250,252,.65);backdrop-filter:blur(20px) saturate(160%);-webkit-backdrop-filter:blur(20px) saturate(160%);border:1px solid rgba(255,255,255,.6);border-radius:20px;padding:24px;margin-bottom:20px;box-shadow:inset 0 1px 0 rgba(255,255,255,.7), 0 4px 16px rgba(15,23,42,.05)">
                <p style="font-size:13px;color:#0b044d;font-weight:700;margin:0 0 18px">Performance Breakdown</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Total Working Days</span>
                            <span id="modalWorkingDays" style="font-size:15px;color:#0b044d;font-weight:700"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Present Days</span>
                            <span id="modalPresentDays2" style="font-size:15px;color:#22c55e;font-weight:700"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Absent Days</span>
                            <span id="modalAbsentDays2" style="font-size:15px;color:#ef4444;font-weight:700"></span>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Late Instances</span>
                            <span id="modalLateDays2" style="font-size:15px;color:#f59e0b;font-weight:700"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Performance Tier</span>
                            <span id="modalTier" style="font-size:11px;padding:5px 12px;border-radius:999px;font-weight:700"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Attendance Rate</span>
                            <span id="modalRate2" style="font-size:15px;color:#3b82f6;font-weight:700"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background:linear-gradient(135deg, rgba(255,251,235,.8) 0%, rgba(254,243,199,.6) 100%);backdrop-filter:blur(16px) saturate(160%);-webkit-backdrop-filter:blur(16px) saturate(160%);border:1px solid rgba(245,158,11,.18);border-radius:20px;padding:24px;box-shadow:inset 0 1px 0 rgba(255,255,255,.6), 0 4px 16px rgba(245,158,11,.06)">
                <p style="font-size:13px;color:#0b044d;font-weight:700;margin:0 0 14px">🏆 Why Top Performer?</p>
                <div id="modalReason" style="font-size:13px;color:#64748b;line-height:1.7"></div>
            </div>
        </div>
    </div>
</div>

{{-- View Employee Modal --}}
<div id="viewEmployeeDashboardModal" style="display:none; position:fixed; inset:0; background:rgba(11,4,77,0.6); backdrop-filter:blur(4px); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:900px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px rgba(0,0,0,0.25);">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:24px; border-bottom:1.5px solid #f2f1fb;">
            <div>
                <h3 style="margin:0 0 4px; font-size:18px; font-weight:700; color:#0b044d;">Employee Details</h3>
                <p id="viewEmployeeDashboardId" style="margin:0; font-size:13px; color:#56547a;"></p>
            </div>
            <button onclick="closeViewDashboardModal()" style="background:none; border:none; font-size:28px; color:#56547a; cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>
        <div id="viewEmployeeDashboardContent" style="padding:24px;">
            <p style="text-align:center; color:#56547a;">Loading...</p>
        </div>
    </div>
</div>

{{-- Add Employee Modal --}}
<div class="modal-overlay" id="addEmployeeModal" onclick="closeAddEmployee()">
    <div class="modal-box" style="max-width:560px" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon" style="background:linear-gradient(135deg,#0b044d,#150c63)">
                    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                </div>
                <div>
                    <span class="modal-eyebrow">EMPLOYEE MANAGEMENT</span>
                    <h3 class="modal-title">Add New Employee</h3>
                    <p class="modal-sub">Fill in the details to register a new employee</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeAddEmployee()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:20px 24px;max-height:65vh;overflow-y:auto">
            <form id="addEmployeeForm" onsubmit="submitAddEmployee(event)">
                <div class="form-section-label">PERSONAL INFORMATION</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="first_name" placeholder="e.g. Juan" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="last_name" placeholder="e.g. Dela Cruz" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-input" name="middle_name" placeholder="e.g. Santos">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth <span class="form-required">*</span></label>
                        <input type="date" class="form-input" name="dob" required>
                    </div>
                </div>

                <div class="form-section-label" style="margin-top:18px">EMPLOYMENT DETAILS</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Type <span class="form-required">*</span></label>
                        <select class="form-input" name="emp_type" required>
                            <option value="">Select type</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Department <span class="form-required">*</span></label>
                        <select class="form-input" name="department" required>
                            <option value="">Select department</option>
                            <option>Administration</option>
                            <option>Engineering</option>
                            <option>Health</option>
                            <option>Finance</option>
                            <option>HRMO</option>
                            <option>General Services</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Position <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="position" placeholder="e.g. Administrative Officer II" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Hired <span class="form-required">*</span></label>
                        <input type="date" class="form-input" name="date_hired" required>
                    </div>
                </div>

                <div class="form-section-label" style="margin-top:18px">CONTACT INFORMATION</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" name="email" placeholder="e.g. juan@lgu.gov.ph">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-input" name="contact" placeholder="e.g. 09XX-XXX-XXXX">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:16px 24px 24px;border-top:1px solid #e5e4f0">
            <button class="modal-btn-ghost" onclick="closeAddEmployee()">Cancel</button>
            <button class="modal-btn-primary" onclick="submitAddEmployee(event)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Employee
            </button>
        </div>
    </div>
</div>

<script>
function switchPendingRequestsTab(tab) {
    const isLeave = tab === 'leave';
    document.getElementById('pendingLeaveTabPanel').style.display = isLeave ? 'block' : 'none';
    document.getElementById('pendingPassSlipTabPanel').style.display = isLeave ? 'none' : 'block';
    document.getElementById('pendingTabLeaveBtn').classList.toggle('active', isLeave);
    document.getElementById('pendingTabPassSlipBtn').classList.toggle('active', !isLeave);
    document.getElementById('pendingRequestsViewAllBtn').onclick = () => window.location.href = isLeave ? '/admin/leave' : '/admin/passslip';
    document.querySelectorAll('.leave-action-menu').forEach(m => m.style.display = 'none');
}

function togglePassSlipMenuDash(e) {
    e.stopPropagation();
    const menu = e.target.closest('button').nextElementSibling;
    const allMenus = document.querySelectorAll('.leave-action-menu');
    allMenus.forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function toggleLeaveMenu(e) {
    e.stopPropagation();
    const menu = e.target.closest('button').nextElementSibling;
    const allMenus = document.querySelectorAll('.leave-action-menu');
    allMenus.forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function approveLeave(e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    alert('Leave request for ' + name + ' has been approved!');
    listItem.querySelector('.leave-action-menu').style.display = 'none';
}

function disapproveLeave(e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    alert('Leave request for ' + name + ' has been disapproved.');
    listItem.querySelector('.leave-action-menu').style.display = 'none';
}

function viewLeaveDetails(e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    const details = listItem.querySelector('.enterprise-person span').textContent;
    alert('Leave details for ' + name + ':\n' + details);
    listItem.querySelector('.leave-action-menu').style.display = 'none';
}

document.addEventListener('click', function() {
    document.querySelectorAll('.leave-action-menu').forEach(m => m.style.display = 'none');
});

const perfPeriods = { month: '{{ $perfPeriodMonth }}', week: '{{ $perfPeriodWeek }}' };

function switchPerfTab(tab) {
    const isMonth = tab === 'month';
    document.getElementById('perfPanelMonth').style.display = isMonth ? 'flex' : 'none';
    document.getElementById('perfPanelWeek').style.display  = isMonth ? 'none'  : 'flex';
    document.getElementById('perfTabMonth').classList.toggle('active', isMonth);
    document.getElementById('perfTabWeek').classList.toggle('active', !isMonth);
    document.getElementById('perfPeriodSub').textContent = perfPeriods[tab];
}

// Initialize with week view by default
document.addEventListener('DOMContentLoaded', function() {
    switchPeriodChart('week');
    // Set week as active for attendance chart
    const attendanceChartCard = document.getElementById('attendanceChart').closest('.chart-card');
    attendanceChartCard.querySelectorAll('.chart-tab').forEach((t, idx) => {
        t.classList.toggle('active', idx === 0);
    });
    attendanceChart.data.labels = attendanceData['week'].labels;
    attendanceChart.data.datasets[0].data = attendanceData['week'].data;
    attendanceChart.data.datasets[1].data = attendanceData['week'].lateData;
    attendanceChart.update();
});


function switchBirdsTab(tab) {
    const isEarly = tab === 'early';
    document.getElementById('panelEarly').style.display = isEarly ? 'grid' : 'none';
    document.getElementById('panelLate').style.display = isEarly ? 'none' : 'grid';
    document.getElementById('birdsTabTitle').textContent = isEarly ? 'Earliest Time-ins Today' : 'Late Arrivals Today';
    document.getElementById('tabEarly').classList.toggle('active', isEarly);
    document.getElementById('tabLate').classList.toggle('active', !isEarly);
}

const employeeData = @json($chartData['employees']);
const salaryData = @json($chartData['salaryTrends']);
const attendanceData = @json($chartData['attendance']);

let currentChartType = 'salary';
let currentPeriod = 'week';
let dynamicChart;
let gradientPayroll;

function initCharts() {
    const ctx1 = document.getElementById('dynamicChart').getContext('2d');
    const ctx2 = document.getElementById('attendanceChart').getContext('2d');

    gradientPayroll = ctx1.createLinearGradient(0, 0, 0, 300);
    gradientPayroll.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
    gradientPayroll.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

    // Create gradient for Attendance Chart
    const gradientAtt = ctx2.createLinearGradient(0, 0, 0, 400);
    gradientAtt.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
    gradientAtt.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

    const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'];

    // Initialize with payroll by designation (week view)
    dynamicChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: salaryData.week.labels,
            datasets: salaryData.week.datasets.map((ds, index) => ({
                label: ds.label,
                data: ds.data,
                borderColor: ds.color,
                backgroundColor: index === 0 ? gradientPayroll : ds.color + '20',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: ds.color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 12,
                        font: { size: 11, family: 'Poppins', weight: '600' },
                        color: '#64748b',
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#fff',
                    titleColor: '#0b044d',
                    bodyColor: '#5a5888',
                    borderColor: '#eceaf8',
                    borderWidth: 1.5,
                    padding: 12,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f7f6fc', drawBorder: false },
                    ticks: {
                        color: '#8f8daf',
                        font: { size: 11, family: 'Poppins' },
                        callback: function(value) {
                            if (value >= 1000000) return '₱' + (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return '₱' + (value / 1000).toFixed(1) + 'K';
                            return '₱' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        color: '#8f8daf',
                        font: { size: 11, family: 'Poppins' },
                        callback: function(value, index) {
                            const labels = this.getLabelForValue(value);
                            return labels;
                        }
                    }
                }
            }
        }
    });

    attendanceChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: attendanceData.week.labels,
            datasets: [
                {
                    label: 'Attendance Rate (%)',
                    data: attendanceData.week.data,
                    borderColor: '#3b82f6',
                    backgroundColor: gradientAtt,
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 3
                },
                {
                    label: 'Late Arrivals (%)',
                    data: attendanceData.week.lateData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 2
                },
                {
                    label: 'Absent (%)',
                    data: attendanceData.week.absentData,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 12,
                        font: { size: 11, family: 'Poppins', weight: '600' },
                        color: '#64748b',
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#fff',
                    titleColor: '#0b044d',
                    bodyColor: '#5a5888',
                    borderColor: '#eceaf8',
                    borderWidth: 1.5,
                    padding: 12,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 120,
                    grid: { color: '#f7f6fc', drawBorder: false },
                    ticks: {
                        color: '#8f8daf',
                        font: { size: 11, family: 'Poppins' },
                        padding: 8
                    }
                },
                x: {
                    offset: false,
                    grid: { display: false, drawBorder: false, offset: false },
                    ticks: {
                        color: '#8f8daf',
                        font: { size: 11, family: 'Poppins' },
                        padding: 2,
                        autoSkip: true,
                        maxRotation: 0,
                        minRotation: 0
                    }
                }
            }
        }
    });
}

function switchMainChart(type) {
    currentChartType = type;
    document.getElementById('tabEmployees').classList.toggle('active', type === 'employees');
    document.getElementById('tabSalary').classList.toggle('active', type === 'salary');

    if (type === 'employees') {
        document.getElementById('dynamicChartTitle').textContent = 'Employee Growth';
        document.getElementById('dynamicChartSub').textContent = 'Total employees over time';
        // Keep current period for employees
        switchPeriodChart(currentPeriod);
    } else {
        document.getElementById('dynamicChartTitle').textContent = 'Payroll by Designation';
        document.getElementById('dynamicChartSub').textContent = 'Total payroll amounts per designation';
        // Default to week for payroll
        switchPeriodChart('week');
    }
}

function switchPeriodChart(period) {
    currentPeriod = period;
    const chartCard = document.getElementById('dynamicChart').closest('.chart-card');
    chartCard.querySelectorAll('#periodTabs .chart-tab').forEach(t => {
        t.classList.remove('active');
        if (t.textContent.toLowerCase() === period) {
            t.classList.add('active');
        }
    });

    if (currentChartType === 'employees') {
        dynamicChart.config.type = 'line';
        dynamicChart.data.labels = employeeData[period].labels;
        dynamicChart.data.datasets = [{
            label: 'Total Employees',
            data: employeeData[period].data,
            borderColor: '#0b044d',
            backgroundColor: 'rgba(11, 4, 77, 0.1)',
            borderWidth: 2.5,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#0b044d',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }];
        dynamicChart.options.plugins.legend.display = false;
        dynamicChart.options.scales.y.ticks.callback = function(value) {
            return value;
        };
    } else {
        // Payroll by designation
        dynamicChart.config.type = 'line';
        dynamicChart.data.labels = salaryData[period].labels;
        dynamicChart.data.datasets = salaryData[period].datasets.map((ds, index) => ({
            label: ds.label,
            data: ds.data,
            borderColor: ds.color,
            backgroundColor: index === 0 ? gradientPayroll : ds.color + '20',
            borderWidth: 2.5,
            tension: 0.4,
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: ds.color,
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }));
        if (dynamicChart.data.datasets.length > 0) {
            dynamicChart.data.datasets[0].backgroundColor = gradientPayroll;
        }
        dynamicChart.options.plugins.legend.display = true;
        dynamicChart.options.plugins.legend.position = 'top';
        dynamicChart.options.plugins.legend.align = 'end';
        dynamicChart.options.plugins.legend.labels = {
            boxWidth: 12,
            boxHeight: 12,
            padding: 12,
            font: { size: 11, family: 'Poppins', weight: '600' },
            color: '#64748b',
            usePointStyle: true,
            pointStyle: 'circle'
        };
        dynamicChart.options.scales.y.ticks.callback = function(value) {
            if (value >= 1000000) return '₱' + (value / 1000000).toFixed(1) + 'M';
            if (value >= 1000) return '₱' + (value / 1000).toFixed(1) + 'K';
            return '₱' + value.toLocaleString();
        };
    }

    dynamicChart.update();
}

function switchAttendanceChart(period) {
    const chartCard = document.getElementById('attendanceChart').closest('.chart-card');
    const buttons = chartCard.querySelectorAll('.chart-tab');
    buttons.forEach(t => t.classList.remove('active'));

    // Find and activate the correct button
    buttons.forEach((btn, idx) => {
        if ((period === 'week' && idx === 0) || (period === 'month' && idx === 1) || (period === 'year' && idx === 2)) {
            btn.classList.add('active');
        }
    });

    attendanceChart.data.labels = attendanceData[period].labels;
    attendanceChart.data.datasets[0].data = attendanceData[period].data;
    attendanceChart.data.datasets[1].data = attendanceData[period].lateData;
    attendanceChart.data.datasets[2].data = attendanceData[period].absentData;
    attendanceChart.update();
}

function adjustDeptDistribution() {
    const container = document.getElementById('deptDistContainer');
    if (!container) return;

    const containerHeight = container.parentElement.offsetHeight - 70;
    const itemHeight = 46;
    const visibleRows = Math.max(3, Math.floor(containerHeight / itemHeight));

    const items = container.querySelectorAll('[style*="height:46px"]');
    items.forEach((item, idx) => {
        item.style.display = idx < visibleRows ? 'flex' : 'none';
    });
}

window.addEventListener('load', () => {
    initCharts();
    adjustDeptDistribution();
});
window.addEventListener('resize', adjustDeptDistribution);

function viewEmployeeDashboard(employeeId) {
    document.getElementById('viewEmployeeDashboardModal').style.display = 'flex';
    document.getElementById('viewEmployeeDashboardContent').innerHTML = '<p style="text-align:center; color:#56547a;">Loading...</p>';

    fetch(`/admin/personnel/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewEmployeeDashboardId').textContent = data.employee_id;
            document.getElementById('viewEmployeeDashboardContent').innerHTML = generateEmployeeViewDashboard(data);
        })
        .catch(error => {
            document.getElementById('viewEmployeeDashboardContent').innerHTML = '<p style="text-align:center; color:#8e1e18;">Error loading employee details.</p>';
        });
}

function closeViewDashboardModal() {
    document.getElementById('viewEmployeeDashboardModal').style.display = 'none';
}

function generateEmployeeViewDashboard(data) {
    return `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">👤 Personal Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Full Name</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.first_name} ${data.middle_name || ''} ${data.last_name} ${data.suffix || ''}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Date of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.birth_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Place of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.place_of_birth || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Sex</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.sex || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Civil Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.civil_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Citizenship</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.citizenship || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Blood Type</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.blood_type || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">💼 Employment Details</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Designation</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.designation_relation?.title || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Department</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.department_relation?.name || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Employment Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.employment_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Appointment Date</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.appointment_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Salary Grade</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.salary_grade || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Step Increment</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.step_increment || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">📞 Contact Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Email</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.email || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Mobile Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.mobile_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Landline</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.landline_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Emergency Contact</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_person || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Emergency Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_number || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">🪪 Government IDs</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">GSIS Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.gsis_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">PhilHealth Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.philhealth_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">PAG-IBIG Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.pagibig_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">TIN Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.tin_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">License Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.license_no || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div>
            <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">📍 Address</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">House No.</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.house_no || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Street</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.street || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Barangay</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.barangay || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">City/Municipality</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.city || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Province</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.province || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Zip Code</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.zip_code || 'N/A'}</span></div>
            </div>
        </div>
    `;
}

function applyFilters() {
    const dept = document.getElementById('filterDept').value;
    const type = document.getElementById('filterType').value;
    const rows = document.querySelectorAll('.payroll-table tbody tr[data-dept]');
    let visible = 0;
    rows.forEach(row => {
        const matchDept = !dept || row.dataset.dept === dept;
        const matchType = !type || row.dataset.type === type;
        const show = matchDept && matchType;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const total = rows.length;
    document.getElementById('filterCount').innerHTML =
        visible === total
            ? 'Showing <strong>1–' + total + '</strong> of <strong>' + total + '</strong> employees'
            : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> employees';
}

function searchEmployees(query) {
    const searchTerm = query.toLowerCase();
    const rows = document.querySelectorAll('.payroll-table tbody tr[data-dept]');
    let visible = 0;
    rows.forEach(row => {
        const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
        const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const position = row.querySelector('.position-cell')?.textContent.toLowerCase() || '';
        const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
        const show = name.includes(searchTerm) || id.includes(searchTerm) || position.includes(searchTerm) || dept.includes(searchTerm);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const total = rows.length;
    document.getElementById('filterCount').innerHTML =
        visible === total
            ? 'Showing <strong>1–' + total + '</strong> of <strong>' + total + '</strong> employees'
            : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> employees (filtered)';
}

function openAddEmployee() {
    document.getElementById('addEmployeeModal').classList.add('show');
}

function closeAddEmployee() {
    document.getElementById('addEmployeeModal').classList.remove('show');
    document.getElementById('addEmployeeForm').reset();
}

function submitAddEmployee(e) {
    e.preventDefault();
    const form = document.getElementById('addEmployeeForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const data = Object.fromEntries(new FormData(form));
    alert('Employee added successfully!\\n\\n' + data.first_name + ' ' + data.last_name + ' (' + data.emp_type + ')\\n' + data.position + ' · ' + data.department);
    closeAddEmployee();
}

function viewEmployee(emp) {
    document.getElementById('viewEmpName').textContent = emp.name;
    document.getElementById('viewEmpId').textContent = emp.id;
    document.getElementById('viewPosition').textContent = emp.position;
    document.getElementById('viewDept').textContent = emp.dept;
    document.getElementById('viewType').textContent = emp.type;
    document.getElementById('viewStatus').textContent = emp.status.charAt(0).toUpperCase() + emp.status.slice(1);
    document.getElementById('viewEmployeeModal').classList.add('show');
}

function closeViewEmployee() {
    document.getElementById('viewEmployeeModal').classList.remove('show');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeAddEmployee();
        closeViewEmployee();
    }
});

// Apply dynamic colors (avoid Blade-in-style lint issues)
document.querySelectorAll('.emp-avatar-dynamic, .event-icon-dynamic, .dept-fill').forEach(el => {
    const bg = el.dataset.bg;
    if (bg) el.style.backgroundColor = bg;
    const w = el.dataset.w;
    if (w) el.style.width = w;
});

function showPerformerDetails(emp, period, rank) {
    const modal = document.getElementById('performerDetailsModal');
    const rankEmojis = ['🥇', '🥈', '🥉', '4', '5'];
    const periodLabels = {
        'month': 'Previous Month · ' + perfPeriods.month,
        'week':  'Previous Week · '  + perfPeriods.week
    };

    document.getElementById('modalPerformerName').textContent = emp.name;
    document.getElementById('modalPerformerRank').textContent = rankEmojis[rank - 1] || rank;
    document.getElementById('modalPerformerPosition').textContent = emp.position;
    document.getElementById('modalPerformerDept').textContent = emp.department;
    document.getElementById('modalPeriodLabel').textContent = periodLabels[period];
    document.getElementById('modalAttendanceRate').textContent = emp.rate + '%';
    document.getElementById('modalPresentDays').textContent = emp.present_days;
    document.getElementById('modalAbsentDays').textContent = emp.absent_days;
    document.getElementById('modalLateDays').textContent = emp.late_days;
    document.getElementById('modalWorkingDays').textContent = emp.working_days;
    document.getElementById('modalPresentDays2').textContent = emp.present_days;
    document.getElementById('modalAbsentDays2').textContent = emp.absent_days;
    document.getElementById('modalLateDays2').textContent = emp.late_days;
    document.getElementById('modalRate2').textContent = emp.rate + '%';

    const tierEl = document.getElementById('modalTier');
    const tierLabels = {
        'excellent': 'Excellent',
        'good': 'Good',
        'needs_improvement': 'Needs Improvement',
        'poor': 'Poor'
    };
    const tierColors = {
        'excellent': 'background:#e8f9ef;color:#15803d',
        'good': 'background:#e8f9ef;color:#15803d',
        'needs_improvement': 'background:#fbf6e3;color:#c9a227',
        'poor': 'background:#fde8e8;color:#dc2626'
    };
    tierEl.textContent = tierLabels[emp.tier] || emp.tier;
    tierEl.style.cssText = 'font-size:12px;padding:4px 10px;border-radius:999px;font-weight:700;' + tierColors[emp.tier];

    const avatar = document.getElementById('modalPerformerAvatar');
    if (emp.photo) {
        avatar.innerHTML = '<img src="' + emp.photo + '" style="width:100%;height:100%;border-radius:50%;object-fit:cover">';
    } else {
        avatar.innerHTML = '<span style="color:#fff;font-weight:700;font-size:24px">' + emp.initials + '</span>';
        avatar.style.backgroundColor = emp.color;
    }

    let reason = '<ul style="margin:0;padding-left:20px">';

    if (emp.rate >= 95) {
        reason += '<li style="margin-bottom:8px"><strong>Outstanding attendance rate of ' + emp.rate + '%</strong> - Near perfect attendance record!</li>';
    } else if (emp.rate >= 80) {
        reason += '<li style="margin-bottom:8px"><strong>Excellent attendance rate of ' + emp.rate + '%</strong> - Consistently present at work.</li>';
    }

    if (emp.absent_days === 0) {
        reason += '<li style="margin-bottom:8px"><strong>Zero absences</strong> during the evaluation period.</li>';
    } else if (emp.absent_days <= 2) {
        reason += '<li style="margin-bottom:8px">Only <strong>' + emp.absent_days + ' day(s) absent</strong> - Minimal absenteeism.</li>';
    }

    if (emp.late_days === 0) {
        reason += '<li style="margin-bottom:8px"><strong>Always on time</strong> - Zero late arrivals recorded.</li>';
    } else if (emp.late_days <= 2) {
        reason += '<li style="margin-bottom:8px">Punctual with only <strong>' + emp.late_days + ' late instance(s)</strong>.</li>';
    } else {
        reason += '<li style="margin-bottom:8px">Recorded <strong>' + emp.late_days + ' late arrivals</strong> but maintained excellent overall attendance.</li>';
    }

    reason += '<li style="margin-bottom:8px">Present for <strong>' + emp.present_days + ' out of ' + emp.working_days + ' working days</strong> in this period.</li>';

    if (rank === 1) {
        reason += '<li><strong>🏆 #1 Top Performer</strong> - Leading by example with exceptional dedication!</li>';
    } else if (rank === 2) {
        reason += '<li><strong>🥈 Silver Medal</strong> - Outstanding performance and reliability!</li>';
    } else if (rank === 3) {
        reason += '<li><strong>🥉 Bronze Medal</strong> - Excellent work ethic and consistency!</li>';
    } else {
        reason += '<li>Among the <strong>Top 5 Performers</strong> - Recognized for excellent attendance!</li>';
    }

    reason += '</ul>';
    document.getElementById('modalReason').innerHTML = reason;

    modal.style.display = 'flex';
}

function closePerformerModal() {
    document.getElementById('performerDetailsModal').style.display = 'none';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closePerformerModal();
    }
});
</script>

{{-- Bird Schedule Tooltip --}}
<div id="birdScheduleTooltip" style="display:none" aria-hidden="true">
    <div class="bst-header">&#128197; Work Schedule</div>
    <div class="bst-body">
        <div class="bst-row">
            <span class="bst-period">AM</span>
            <span class="bst-time" id="bstAmIn">—</span>
            <span class="bst-sep">→</span>
            <span class="bst-time" id="bstAmOut">—</span>
        </div>
        <div class="bst-row">
            <span class="bst-period">PM</span>
            <span class="bst-time" id="bstPmIn">—</span>
            <span class="bst-sep">→</span>
            <span class="bst-time" id="bstPmOut">—</span>
        </div>
    </div>
    <div class="bst-dates" id="bstDates" style="display:none"></div>
</div>

<script>
(function () {
    const tooltip   = document.getElementById('birdScheduleTooltip');
    const elAmIn    = document.getElementById('bstAmIn');
    const elAmOut   = document.getElementById('bstAmOut');
    const elPmIn    = document.getElementById('bstPmIn');
    const elPmOut   = document.getElementById('bstPmOut');
    const elDates   = document.getElementById('bstDates');
    const elBody    = tooltip.querySelector('.bst-body');
    const elNoSched = document.createElement('div');
    elNoSched.className = 'bst-no-schedule';
    elNoSched.textContent = 'No schedule assigned';

    function positionTooltip(rect) {
        requestAnimationFrame(() => {
            const tw  = tooltip.offsetWidth;
            const th  = tooltip.offsetHeight;
            const gap = 10;
            let left  = rect.left + rect.width / 2 - tw / 2;
            let top   = rect.top - th - gap;
            let arrow = 'bst-arrow-down';

            if (top < gap) {
                top   = rect.bottom + gap;
                arrow = 'bst-arrow-up';
            }
            left = Math.max(gap, Math.min(left, window.innerWidth - tw - gap));

            tooltip.style.left  = left + 'px';
            tooltip.style.top   = top  + 'px';
            tooltip.className   = arrow;
        });
    }

    function showTooltip(el) {
        const raw = el.dataset.schedule;
        elNoSched.remove();

        if (!raw || raw === 'null') {
            elBody.style.display  = 'none';
            elDates.style.display = 'none';
            tooltip.appendChild(elNoSched);
        } else {
            const s = JSON.parse(raw);
            elBody.style.display = '';
            elAmIn.textContent  = s.am_in;
            elAmOut.textContent = s.am_out;
            elPmIn.textContent  = s.pm_in;
            elPmOut.textContent = s.pm_out;

            if (s.start_date || s.end_date) {
                elDates.textContent   = 'Effective: ' + (s.start_date || 'Open') + ' – ' + (s.end_date || 'Ongoing');
                elDates.style.display = '';
            } else {
                elDates.style.display = 'none';
            }
        }

        tooltip.style.display = 'block';
        positionTooltip(el.getBoundingClientRect());
    }

    function hideTooltip() {
        tooltip.style.display = 'none';
    }

    document.querySelectorAll('.bird-item').forEach(el => {
        el.addEventListener('mouseenter', function () { showTooltip(this); });
        el.addEventListener('mouseleave', hideTooltip);
        el.addEventListener('touchstart', function () { showTooltip(this); }, { passive: true });
    });
})();
</script>
@endsection
