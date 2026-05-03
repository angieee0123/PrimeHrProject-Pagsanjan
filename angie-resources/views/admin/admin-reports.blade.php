@extends('layouts.app')

@section('title', 'Reports · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('content')
<div class="app-layout">

    {{-- Mobile Menu Button --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('admin.admin-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('admin.admin-notification')

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <div>
                    <h2>Reports & Analytics</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Municipal Government of Pagsanjan</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    FY {{ now()->year }}
                </span>
                <span class="banner-badge outline">7 Report Types</span>
            </div>
        </div>

<style>
    .report-tab { display: flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600; border: 1.5px solid #e4e3f0; background: #fff; color: #5a5888; transition: all 0.18s; }
    .report-tab.active { border: 2px solid #0b044d; background: #0b044d; color: #fff; }
    .report-tab:hover:not(.active) { border-color: #0b044d; }
    .report-tab.active svg { stroke: #fff; }
    .search-box { display: flex; align-items: center; gap: 8px; height: 34px; padding: 0 12px; border: 1.5px solid #e4e3f0; border-radius: 8px; background: #fafafe; }
    .search-box input { border: none; background: transparent; outline: none; font-size: 12.5px; font-family: 'Poppins', sans-serif; color: #0b044d; width: 160px; }
    .search-box input::placeholder { color: #c0bedd; }
    .badge-emptype { display: inline-block; background: #f0effe; color: #0b044d; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 6px; border: 1px solid #dddcf0; }
</style>

<div id="reports-page">
    <!-- Report Type Tabs -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <button class="report-tab active" onclick="setActiveReport('payroll')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payroll Summary
        </button>
        <button class="report-tab" onclick="setActiveReport('department')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
            Department Breakdown
        </button>
        <button class="report-tab" onclick="setActiveReport('deductions')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
            Deductions Report
        </button>
        <button class="report-tab" onclick="setActiveReport('headcount')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Headcount Report
        </button>
        <button class="report-tab" onclick="setActiveReport('recruitment')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
            Recruitment Report
        </button>
        <button class="report-tab" onclick="setActiveReport('training')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Training Report
        </button>
        <button class="report-tab" onclick="setActiveReport('performance')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Performance Report
        </button>
    </div>

    <!-- Filters -->
    <div class="table-section" style="margin-bottom: 20px;">
        <div class="table-header">
            <div>
                <p class="table-title" id="reportTitle">Payroll Summary — June 16-30, 2025</p>
                <p class="table-sub">Municipal Government of Pagsanjan · Fiscal Year 2025</p>
            </div>
            <div class="table-actions">
                <div class="search-box">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" placeholder="Search..." id="searchInput" />
                </div>
                <select class="filter-select" id="semiFilter">
                    <option>1st (1-15)</option>
                    <option selected>2nd (16-30)</option>
                </select>
                <select class="filter-select" id="monthFilter">
                    <option>January</option><option>February</option><option>March</option><option>April</option><option>May</option>
                    <option selected>June</option><option>July</option><option>August</option><option>September</option><option>October</option><option>November</option><option>December</option>
                </select>
                <select class="filter-select" id="yearFilter">
                    <option selected>2025</option><option>2024</option><option>2023</option>
                </select>
                <button class="btn-export" onclick="window.print()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export / Print
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid stats-grid-4" style="margin-bottom: 20px;">
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">Gross Payroll</p>
                <div class="stat-icon-wrap" style="background:#f0effe">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M8 10h8M8 14h8"/></svg>
                </div>
            </div>
            <p class="stat-value" style="font-size:20px">₱127,485.50</p>
            <div class="stat-footer">
                <span class="stat-dot" style="background:#0b044d"></span>
                <p class="stat-sub">8 employees · Jun 16-30, 2025</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">Total Net Pay</p>
                <div class="stat-icon-wrap" style="background:#e8f9ef">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M8 10h8M8 14h8"/></svg>
                </div>
            </div>
            <p class="stat-value" style="font-size:20px">₱109,467.50</p>
            <div class="stat-footer">
                <span class="stat-dot" style="background:#22c55e"></span>
                <p class="stat-sub">After all deductions</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">Total Deductions</p>
                <div class="stat-icon-wrap" style="background:#fdf0ef">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
            </div>
            <p class="stat-value" style="font-size:20px">₱18,018.00</p>
            <div class="stat-footer">
                <span class="stat-dot" style="background:#8e1e18"></span>
                <p class="stat-sub">GSIS, PhilHealth, Pag-IBIG, Tax</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">Processed</p>
                <div class="stat-icon-wrap" style="background:#fefce8">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
            </div>
            <p class="stat-value">6</p>
            <div class="stat-footer">
                <span class="stat-dot" style="background:#f59e0b"></span>
                <p class="stat-sub">2 pending/on-hold</p>
            </div>
        </div>
    </div>

    <!-- Payroll Summary Report -->
    <div class="table-section" id="payrollReport">
        <div class="table-header">
            <div>
                <p class="table-title">Payroll Summary Register</p>
                <p class="table-sub">8 records · Jun 16-30, 2025 · Pay Date: Jun 30, 2025</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Basic Pay</th>
                        <th>Total Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="emp-id">PGS-0041</td><td class="emp-name">Maria B. Santos</td><td><span class="dept-tag">Office of the Mayor</span></td><td class="pay-cell">₱21,079.50</td><td class="deduction">₱4,215.50</td><td class="net-pay">₱16,864.00</td><td><span class="badge-status processed">Processed</span></td></tr>
                    <tr><td class="emp-id">PGS-0082</td><td class="emp-name">Juan P. dela Cruz</td><td><span class="dept-tag">Office of the Mun. Engineer</span></td><td class="pay-cell">₱19,042.50</td><td class="deduction">₱3,808.50</td><td class="net-pay">₱15,234.00</td><td><span class="badge-status processed">Processed</span></td></tr>
                    <tr><td class="emp-id">PGS-0115</td><td class="emp-name">Ana R. Reyes</td><td><span class="dept-tag">Municipal Health Office</span></td><td class="pay-cell">₱16,921.50</td><td class="deduction">₱3,384.00</td><td class="net-pay">₱13,537.50</td><td><span class="badge-status pending">Pending</span></td></tr>
                    <tr><td class="emp-id">PGS-0203</td><td class="emp-name">Carlos M. Mendoza</td><td><span class="dept-tag">Office of the Mun. Treasurer</span></td><td class="pay-cell">₱23,627.50</td><td class="deduction">₱4,725.50</td><td class="net-pay">₱18,902.00</td><td><span class="badge-status processed">Processed</span></td></tr>
                    <tr><td class="emp-id">PGS-0267</td><td class="emp-name">Liza G. Gomez</td><td><span class="dept-tag">MSWD - Pagsanjan</span></td><td class="pay-cell">₱17,548.50</td><td class="deduction">₱3,509.50</td><td class="net-pay">₱14,039.00</td><td><span class="badge-status on-hold">On Hold</span></td></tr>
                    <tr><td class="emp-id">PGS-0310</td><td class="emp-name">Roberto T. Flores</td><td><span class="dept-tag">Municipal Civil Registrar</span></td><td class="pay-cell">₱15,265.50</td><td class="deduction">₱3,053.00</td><td class="net-pay">₱12,212.50</td><td><span class="badge-status processed">Processed</span></td></tr>
                    <tr><td class="emp-id">PGS-0342</td><td class="emp-name">Grace A. Villanueva</td><td><span class="dept-tag">Office of the Mun. Budget</span></td><td class="pay-cell">₱14,500.00</td><td class="deduction">₱2,817.50</td><td class="net-pay">₱11,682.50</td><td><span class="badge-status pending">Pending</span></td></tr>
                    <tr><td class="emp-id">PGS-0358</td><td class="emp-name">Ramon D. Cruz</td><td><span class="dept-tag">Office of the Mun. Agriculturist</span></td><td class="pay-cell">₱13,500.00</td><td class="deduction">₱2,592.50</td><td class="net-pay">₱10,907.50</td><td><span class="badge-status processed">Processed</span></td></tr>
                </tbody>
                <tfoot>
                    <tr style="font-weight: 700; background: #f7f6ff;">
                        <td colSpan="3" style="padding: 10px 14px; font-size: 13;">TOTAL (8 employees)</td>
                        <td class="pay-cell">₱127,485.50</td>
                        <td class="deduction">₱18,018.00</td>
                        <td class="net-pay">₱109,467.50</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Department Breakdown Report -->
    <div class="table-section" id="departmentReport" style="display: none;">
        <div class="table-header">
            <div>
                <p class="table-title">Department Payroll Breakdown</p>
                <p class="table-sub">8 departments · Jun 16-30, 2025</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th>Department / Office</th>
                        <th>Headcount</th>
                        <th>Gross Payroll</th>
                        <th>Net Payroll</th>
                        <th>% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><span class="dept-tag">Office of the Mun. Treasurer</span></td><td style="font-weight: 600; color: #0b044d;">1</td><td class="pay-cell">₱23,627.50</td><td class="net-pay">₱18,902.00</td><td><div style="display: flex; align-items: center; gap: 8;"><div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 19%; height: 100%; background: #0b044d; border-radius: 4;"></div></div><span style="font-size: 12px; color: #6b6a8a; min-width: 32;">19%</span></div></td></tr>
                    <tr><td><span class="dept-tag">Office of the Mayor</span></td><td style="font-weight: 600; color: #0b044d;">1</td><td class="pay-cell">₱21,079.50</td><td class="net-pay">₱16,864.00</td><td><div style="display: flex; align-items: center; gap: 8;"><div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 17%; height: 100%; background: #0b044d; border-radius: 4;"></div></div><span style="font-size: 12px; color: #6b6a8a; min-width: 32;">17%</span></div></td></tr>
                    <tr><td><span class="dept-tag">Office of the Mun. Engineer</span></td><td style="font-weight: 600; color: #0b044d;">1</td><td class="pay-cell">₱19,042.50</td><td class="net-pay">₱15,234.00</td><td><div style="display: flex; align-items: center; gap: 8;"><div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 15%; height: 100%; background: #0b044d; border-radius: 4;"></div></div><span style="font-size: 12px; color: #6b6a8a; min-width: 32;">15%</span></div></td></tr>
                    <tr><td><span class="dept-tag">MSWD - Pagsanjan</span></td><td style="font-weight: 600; color: #0b044d;">1</td><td class="pay-cell">₱17,548.50</td><td class="net-pay">₱14,039.00</td><td><div style="display: flex; align-items: center; gap: 8;"><div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 14%; height: 100%; background: #0b044d; border-radius: 4;"></div></div><span style="font-size: 12px; color: #6b6a8a; min-width: 32;">14%</span></div></td></tr>
                    <tr><td><span class="dept-tag">Municipal Health Office</span></td><td style="font-weight: 600; color: #0b044d;">1</td><td class="pay-cell">₱16,921.50</td><td class="net-pay">₱13,537.50</td><td><div style="display: flex; align-items: center; gap: 8;"><div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 13%; height: 100%; background: #0b044d; border-radius: 4;"></div></div><span style="font-size: 12px; color: #6b6a8a; min-width: 32;">13%</span></div></td></tr>
                    <tr><td><span class="dept-tag">Municipal Civil Registrar</span></td><td style="font-weight: 600; color: #0b044d;">1</td><td class="pay-cell">₱15,265.50</td><td class="net-pay">₱12,212.50</td><td><div style="display: flex; align-items: center; gap: 8;"><div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 12%; height: 100%; background: #0b044d; border-radius: 4;"></div></div><span style="font-size: 12px; color: #6b6a8a; min-width: 32;">12%</span></div></td></tr>
                    <tr><td><span class="dept-tag">Office of the Mun. Budget</span></td><td style="font-weight: 600; color: #0b044d;">1</td><td class="pay-cell">₱14,500.00</td><td class="net-pay">₱11,682.50</td><td><div style="display: flex; align-items: center; gap: 8;"><div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 11%; height: 100%; background: #0b044d; border-radius: 4;"></div></div><span style="font-size: 12px; color: #6b6a8a; min-width: 32;">11%</span></div></td></tr>
                    <tr><td><span class="dept-tag">Office of the Mun. Agriculturist</span></td><td style="font-weight: 600; color: #0b044d;">1</td><td class="pay-cell">₱13,500.00</td><td class="net-pay">₱10,907.50</td><td><div style="display: flex; align-items: center; gap: 8;"><div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 11%; height: 100%; background: #0b044d; border-radius: 4;"></div></div><span style="font-size: 12px; color: #6b6a8a; min-width: 32;">11%</span></div></td></tr>
                </tbody>
                <tfoot>
                    <tr style="font-weight: 700; background: #f7f6ff;">
                        <td style="padding: 10px 14px; font-size: 13;">TOTAL</td>
                        <td style="padding: 10px 14px; font-weight: 700;">8</td>
                        <td class="pay-cell">₱127,485.50</td>
                        <td class="net-pay">₱109,467.50</td>
                        <td style="padding: 10px 14px; font-size: 13; color: #6b6a8a;">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Deductions Report -->
    <div class="table-section" id="deductionsReport" style="display: none;">
        <div class="table-header">
            <div>
                <p class="table-title">Deductions Breakdown Report</p>
                <p class="table-sub">8 employees · Jun 16-30, 2025</p>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; padding: 0 0 20px;">
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;">
                <p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">GSIS Premium</p>
                <p style="font-size: 17px; font-weight: 800; color: #0b044d; margin-bottom: 8;">₱12,053.50</p>
                <div style="height: 5px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 67%; height: 100%; background: #0b044d; border-radius: 4;"></div></div>
                <p style="font-size: 11px; color: #aaa8cc; margin-top: 5;">67% of total deductions</p>
            </div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;">
                <p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">PhilHealth</p>
                <p style="font-size: 17px; font-weight: 800; color: #8e1e18; margin-bottom: 8;">₱3,465.00</p>
                <div style="height: 5px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 19%; height: 100%; background: #8e1e18; border-radius: 4;"></div></div>
                <p style="font-size: 11px; color: #aaa8cc; margin-top: 5;">19% of total deductions</p>
            </div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;">
                <p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Pag-IBIG</p>
                <p style="font-size: 17px; font-weight: 800; color: #d9bb00; margin-bottom: 8;">₱400.00</p>
                <div style="height: 5px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 2%; height: 100%; background: #d9bb00; border-radius: 4;"></div></div>
                <p style="font-size: 11px; color: #aaa8cc; margin-top: 5;">2% of total deductions</p>
            </div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;">
                <p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Withholding Tax</p>
                <p style="font-size: 17px; font-weight: 800; color: #15803d; margin-bottom: 8;">₱2,099.50</p>
                <div style="height: 5px; background: #eceaf8; border-radius: 4; overflow: hidden;"><div style="width: 12%; height: 100%; background: #15803d; border-radius: 4;"></div></div>
                <p style="font-size: 11px; color: #aaa8cc; margin-top: 5;">12% of total deductions</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr><th>Employee</th><th>GSIS</th><th>PhilHealth</th><th>Pag-IBIG</th><th>Withholding Tax</th><th>Total Deductions</th></tr>
                </thead>
                <tbody>
                    <tr><td><p class="emp-name">Maria B. Santos</p><p class="emp-id">PGS-0041</p></td><td class="deduction">₱1,897.00</td><td class="deduction">₱525.00</td><td class="deduction">₱50.00</td><td class="deduction">₱1,743.50</td><td class="deduction" style="font-weight: 700;">₱4,215.50</td></tr>
                    <tr><td><p class="emp-name">Juan P. dela Cruz</p><p class="emp-id">PGS-0082</p></td><td class="deduction">₱1,714.00</td><td class="deduction">₱475.00</td><td class="deduction">₱50.00</td><td class="deduction">₱1,569.50</td><td class="deduction" style="font-weight: 700;">₱3,808.50</td></tr>
                    <tr><td><p class="emp-name">Ana R. Reyes</p><p class="emp-id">PGS-0115</p></td><td class="deduction">₱1,523.00</td><td class="deduction">₱425.00</td><td class="deduction">₱50.00</td><td class="deduction">₱1,386.00</td><td class="deduction" style="font-weight: 700;">₱3,384.00</td></tr>
                    <tr><td><p class="emp-name">Carlos M. Mendoza</p><p class="emp-id">PGS-0203</p></td><td class="deduction">₱2,126.50</td><td class="deduction">₱575.00</td><td class="deduction">₱50.00</td><td class="deduction">₱1,974.00</td><td class="deduction" style="font-weight: 700;">₱4,725.50</td></tr>
                    <tr><td><p class="emp-name">Liza G. Gomez</p><p class="emp-id">PGS-0267</p></td><td class="deduction">₱1,579.50</td><td class="deduction">₱437.50</td><td class="deduction">₱50.00</td><td class="deduction">₱1,442.50</td><td class="deduction" style="font-weight: 700;">₱3,509.50</td></tr>
                    <tr><td><p class="emp-name">Roberto T. Flores</p><p class="emp-id">PGS-0310</p></td><td class="deduction">₱1,374.00</td><td class="deduction">₱387.50</td><td class="deduction">₱50.00</td><td class="deduction">₱1,241.50</td><td class="deduction" style="font-weight: 700;">₱3,053.00</td></tr>
                    <tr><td><p class="emp-name">Grace A. Villanueva</p><p class="emp-id">PGS-0342</p></td><td class="deduction">₱1,305.00</td><td class="deduction">₱362.50</td><td class="deduction">₱50.00</td><td class="deduction">₱1,100.00</td><td class="deduction" style="font-weight: 700;">₱2,817.50</td></tr>
                    <tr><td><p class="emp-name">Ramon D. Cruz</p><p class="emp-id">PGS-0358</p></td><td class="deduction">₱1,215.00</td><td class="deduction">₱337.50</td><td class="deduction">₱50.00</td><td class="deduction">₱990.00</td><td class="deduction" style="font-weight: 700;">₱2,592.50</td></tr>
                </tbody>
                <tfoot>
                    <tr style="font-weight: 700; background: #f7f6ff;">
                        <td style="padding: 10px 14px; font-size: 13;">TOTAL</td>
                        <td class="deduction">₱12,053.50</td>
                        <td class="deduction">₱3,465.00</td>
                        <td class="deduction">₱400.00</td>
                        <td class="deduction">₱2,099.50</td>
                        <td class="deduction" style="font-weight: 700;">₱18,018.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Headcount Report -->
    <div class="table-section" id="headcountReport" style="display: none;">
        <div class="table-header">
            <div>
                <p class="table-title">Headcount Report</p>
                <p class="table-sub">8 total personnel · Jun 16-30, 2025</p>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; padding: 0 0 20px;">
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;">
                <p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Total Personnel</p>
                <p style="font-size: 28px; font-weight: 800; color: #0b044d;">8</p>
            </div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;">
                <p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Processed</p>
                <p style="font-size: 28px; font-weight: 800; color: #15803d;">6</p>
            </div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;">
                <p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Pending</p>
                <p style="font-size: 28px; font-weight: 800; color: #d9bb00;">2</p>
            </div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;">
                <p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">On Hold</p>
                <p style="font-size: 28px; font-weight: 800; color: #8e1e18;">0</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead><tr><th>Employee ID</th><th>Name</th><th>Department</th><th>Basic Pay</th><th>Status</th></tr></thead>
                <tbody>
                    <tr><td class="emp-id">PGS-0041</td><td class="emp-name">Maria B. Santos</td><td><span class="dept-tag">Office of the Mayor</span></td><td class="pay-cell">₱21,079.50</td><td><span class="badge-status processed">Processed</span></td></tr>
                    <tr><td class="emp-id">PGS-0082</td><td class="emp-name">Juan P. dela Cruz</td><td><span class="dept-tag">Office of the Mun. Engineer</span></td><td class="pay-cell">₱19,042.50</td><td><span class="badge-status processed">Processed</span></td></tr>
                    <tr><td class="emp-id">PGS-0115</td><td class="emp-name">Ana R. Reyes</td><td><span class="dept-tag">Municipal Health Office</span></td><td class="pay-cell">₱16,921.50</td><td><span class="badge-status pending">Pending</span></td></tr>
                    <tr><td class="emp-id">PGS-0203</td><td class="emp-name">Carlos M. Mendoza</td><td><span class="dept-tag">Office of the Mun. Treasurer</span></td><td class="pay-cell">₱23,627.50</td><td><span class="badge-status processed">Processed</span></td></tr>
                    <tr><td class="emp-id">PGS-0267</td><td class="emp-name">Liza G. Gomez</td><td><span class="dept-tag">MSWD - Pagsanjan</span></td><td class="pay-cell">₱17,548.50</td><td><span class="badge-status on-hold">On Hold</span></td></tr>
                    <tr><td class="emp-id">PGS-0310</td><td class="emp-name">Roberto T. Flores</td><td><span class="dept-tag">Municipal Civil Registrar</span></td><td class="pay-cell">₱15,265.50</td><td><span class="badge-status processed">Processed</span></td></tr>
                    <tr><td class="emp-id">PGS-0342</td><td class="emp-name">Grace A. Villanueva</td><td><span class="dept-tag">Office of the Mun. Budget</span></td><td class="pay-cell">₱14,500.00</td><td><span class="badge-status pending">Pending</span></td></tr>
                    <tr><td class="emp-id">PGS-0358</td><td class="emp-name">Ramon D. Cruz</td><td><span class="dept-tag">Office of the Mun. Agriculturist</span></td><td class="pay-cell">₱13,500.00</td><td><span class="badge-status processed">Processed</span></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recruitment Report -->
    <div class="table-section" id="recruitmentReport" style="display: none;">
        <div class="table-header">
            <div>
                <p class="table-title">Recruitment Report</p>
                <p class="table-sub">Job postings and applicant statistics · 2025</p>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; padding: 0 0 20px;">
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Total Job Postings</p><p style="font-size: 28px; font-weight: 800; color: #0b044d;">12</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Open Positions</p><p style="font-size: 28px; font-weight: 800; color: #15803d;">8</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Total Applicants</p><p style="font-size: 28px; font-weight: 800; color: #d9bb00;">145</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Hired</p><p style="font-size: 28px; font-weight: 800; color: #1a6e3c;">6</p></div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead><tr><th>Job ID</th><th>Position</th><th>Department</th><th>Applicants</th><th>Status</th><th>Posted Date</th></tr></thead>
                <tbody>
                    <tr><td class="emp-id">JOB-001</td><td class="emp-name">Administrative Officer IV</td><td><span class="dept-tag">Office of the Mayor</span></td><td style="font-weight: 600; color: #0b044d; text-align: center;">12</td><td><span class="badge-status processed">Open</span></td><td style="font-size: 12.5px; color: #6b6a8a;">Jun 1, 2025</td></tr>
                    <tr><td class="emp-id">JOB-002</td><td class="emp-name">Municipal Engineer II</td><td><span class="dept-tag">Office of the Mun. Engineer</span></td><td style="font-weight: 600; color: #0b044d; text-align: center;">8</td><td><span class="badge-status processed">Open</span></td><td style="font-size: 12.5px; color: #6b6a8a;">Jun 5, 2025</td></tr>
                    <tr><td class="emp-id">JOB-003</td><td class="emp-name">Nurse II</td><td><span class="dept-tag">Municipal Health Office</span></td><td style="font-weight: 600; color: #0b044d; text-align: center;">24</td><td><span class="badge-status on-hold">Closed</span></td><td style="font-size: 12.5px; color: #6b6a8a;">May 15, 2025</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Training Report -->
    <div class="table-section" id="trainingReport" style="display: none;">
        <div class="table-header">
            <div>
                <p class="table-title">Training & Development Report</p>
                <p class="table-sub">Training programs and participation · 2025</p>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; padding: 0 0 20px;">
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Total Programs</p><p style="font-size: 28px; font-weight: 800; color: #0b044d;">15</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Ongoing</p><p style="font-size: 28px; font-weight: 800; color: #15803d;">5</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Total Participants</p><p style="font-size: 28px; font-weight: 800; color: #d9bb00;">283</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Completed</p><p style="font-size: 28px; font-weight: 800; color: #1a6e3c;">8</p></div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead><tr><th>Training ID</th><th>Program Title</th><th>Type</th><th>Participants</th><th>Status</th><th>Duration</th></tr></thead>
                <tbody>
                    <tr><td class="emp-id">TRN-001</td><td class="emp-name">Leadership Development Program</td><td><span class="badge-emptype">Leadership</span></td><td style="font-weight: 600; color: #0b044d; text-align: center;">25 / 30</td><td><span class="badge-status processed">Ongoing</span></td><td style="font-size: 12.5px; color: #6b6a8a;">Jun 15 - Jul 15</td></tr>
                    <tr><td class="emp-id">TRN-002</td><td class="emp-name">Digital Literacy Training</td><td><span class="badge-emptype">Technical</span></td><td style="font-weight: 600; color: #0b044d; text-align: center;">18 / 20</td><td><span class="badge-status processed">Ongoing</span></td><td style="font-size: 12.5px; color: #6b6a8a;">Jun 20 - Jun 30</td></tr>
                    <tr><td class="emp-id">TRN-003</td><td class="emp-name">Customer Service Excellence</td><td><span class="badge-emptype">Soft Skills</span></td><td style="font-weight: 600; color: #0b044d; text-align: center;">30 / 30</td><td><span class="badge-status on-hold">Completed</span></td><td style="font-size: 12.5px; color: #6b6a8a;">May 10 - May 20</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Performance Report -->
    <div class="table-section" id="performanceReport" style="display: none;">
        <div class="table-header">
            <div>
                <p class="table-title">Performance Evaluation Report</p>
                <p class="table-sub">Employee performance ratings · Jan-Jun 2025</p>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; padding: 0 0 20px;">
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Total Evaluations</p><p style="font-size: 28px; font-weight: 800; color: #0b044d;">348</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Completed</p><p style="font-size: 28px; font-weight: 800; color: #15803d;">336</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Pending</p><p style="font-size: 28px; font-weight: 800; color: #d9bb00;">12</p></div>
            <div style="background: #fff; border: 1.5px solid #eceaf8; border-radius: 12px; padding: 16px 18px;"><p style="font-size: 11.5px; color: #9999bb; font-weight: 600; margin-bottom: 6;">Average Rating</p><p style="font-size: 28px; font-weight: 800; color: #1a6e3c;">4.7</p></div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead><tr><th>Employee ID</th><th>Name</th><th>Department</th><th>Rating</th><th>Status</th><th>Period</th></tr></thead>
                <tbody>
                    <tr><td class="emp-id">PGS-0041</td><td class="emp-name">Maria B. Santos</td><td><span class="dept-tag">Office of the Mayor</span></td><td style="font-weight: 700; color: #15803d; text-align: center;">4.8 / 5.0</td><td><span class="badge-status processed">Completed</span></td><td style="font-size: 12.5px; color: #6b6a8a;">Jan-Jun 2025</td></tr>
                    <tr><td class="emp-id">PGS-0082</td><td class="emp-name">Juan P. dela Cruz</td><td><span class="dept-tag">Office of the Mun. Engineer</span></td><td style="font-weight: 700; color: #15803d; text-align: center;">4.5 / 5.0</td><td><span class="badge-status processed">Completed</span></td><td style="font-size: 12.5px; color: #6b6a8a;">Jan-Jun 2025</td></tr>
                    <tr><td class="emp-id">PGS-0115</td><td class="emp-name">Ana R. Reyes</td><td><span class="dept-tag">Municipal Health Office</span></td><td style="font-weight: 700; color: #15803d; text-align: center;">4.9 / 5.0</td><td><span class="badge-status processed">Completed</span></td><td style="font-size: 12.5px; color: #6b6a8a;">Jan-Jun 2025</td></tr>
                    <tr><td class="emp-id">PGS-0267</td><td class="emp-name">Liza G. Gomez</td><td><span class="dept-tag">MSWD - Pagsanjan</span></td><td style="font-size: 12.5px; color: #9999bb; text-align: center;">Not rated</td><td><span class="badge-status pending">Pending</span></td><td style="font-size: 12.5px; color: #6b6a8a;">Jan-Jun 2025</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

    </main>

    @include('admin.admin-chatbot')

</div>

<script>
const sidebar      = document.getElementById('sidebar');
const toggleBtn    = document.getElementById('toggle-btn');
const logoText     = document.getElementById('logo-text');
const navLabel     = document.getElementById('nav-label');
const userInfo     = document.getElementById('user-info');
const sidebarFooter = document.getElementById('sidebar-footer');
const mobileBtn    = document.getElementById('mobile-menu-btn');
const overlay      = document.getElementById('mobile-overlay');

toggleBtn.addEventListener('click', () => {
    const collapsed = sidebar.classList.toggle('collapsed');
    toggleBtn.textContent = collapsed ? '›' : '‹';
    logoText.style.display  = collapsed ? 'none' : '';
    navLabel.style.display  = collapsed ? 'none' : '';
    userInfo.style.display  = collapsed ? 'none' : '';
    sidebarFooter.classList.toggle('collapsed-footer', collapsed);
    document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
        el.style.display = collapsed ? 'none' : '';
    });
});

mobileBtn.addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
});

const reportTitles = {
    payroll: 'Payroll Summary — June 16-30, 2025',
    department: 'Department Breakdown — June 16-30, 2025',
    deductions: 'Deductions Report — June 16-30, 2025',
    headcount: 'Headcount Report — June 16-30, 2025',
    recruitment: 'Recruitment Report — 2025',
    training: 'Training Report — 2025',
    performance: 'Performance Report — Jan-Jun 2025'
};

const allReports = ['payroll', 'department', 'deductions', 'headcount', 'recruitment', 'training', 'performance'];
let currentReport = 'payroll';

// Reports that use semi/month/year filters
const payrollReports = ['payroll', 'department', 'deductions', 'headcount'];

function setActiveReport(reportId) {
    document.querySelectorAll('.report-tab').forEach(tab => tab.classList.remove('active'));
    event.target.closest('.report-tab').classList.add('active');
    document.getElementById('reportTitle').textContent = reportTitles[reportId];
    currentReport = reportId;

    allReports.forEach(r => {
        document.getElementById(r + 'Report').style.display = r === reportId ? 'block' : 'none';
    });

    // Show/hide period filters based on report type
    const showPeriod = payrollReports.includes(reportId);
    document.getElementById('semiFilter').style.display = showPeriod ? '' : 'none';
    document.getElementById('monthFilter').style.display = showPeriod ? '' : 'none';

    // Reset search and re-apply
    document.getElementById('searchInput').value = '';
    applyFilters();
}

function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase().trim();
    const activeSection = document.getElementById(currentReport + 'Report');
    if (!activeSection) return;

    const rows = activeSection.querySelectorAll('tbody tr');
    let visible = 0;
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = !search || text.includes(search);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    // Update subtitle with visible count
    const sub = activeSection.querySelector('.table-sub');
    if (sub) {
        const total = rows.length;
        sub.textContent = visible === total
            ? sub.textContent.replace(/^\d+/, total)
            : visible + ' of ' + total + ' records shown';
    }
}

// Wire up all filter inputs
document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('semiFilter').addEventListener('change', applyFilters);
document.getElementById('monthFilter').addEventListener('change', applyFilters);
document.getElementById('yearFilter').addEventListener('change', applyFilters);
</script>
@endsection