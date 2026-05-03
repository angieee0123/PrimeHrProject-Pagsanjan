@extends('layouts.app')

@section('title', 'Leave & Benefits · PRIME HRIS')

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

    @include('permanent.permanent-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('permanent.permanent-notification')

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <h2>Leave & Benefits</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Nurse II · Municipal Health Office · PGS-0115</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    VL: 10 days
                </span>
                <span class="banner-badge outline">SL: 11 days</span>
            </div>
        </div>
        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Leave Filed</p>
                    <div class="stat-icon-wrap" style="background:#0b044d15"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                </div>
                <h2 class="stat-value">6</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">All time</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Days Used</p>
                    <div class="stat-icon-wrap" style="background:#8e1e1815"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                </div>
                <h2 class="stat-value">13</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">Across all types</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Pending Requests</p>
                    <div class="stat-icon-wrap" style="background:#d9bb0015"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                </div>
                <h2 class="stat-value">1</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#d9bb00"></span>
                    <p class="stat-sub">Awaiting approval</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">VL + SL Balance</p>
                    <div class="stat-icon-wrap" style="background:#15803d15"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                </div>
                <h2 class="stat-value">21</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#15803d"></span>
                    <p class="stat-sub">10 VL · 11 SL</p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('leave', this)">My Leave Requests</button>
            <button class="tab-btn" onclick="switchTab('credits', this)">Leave Credits</button>
            <button class="tab-btn" onclick="switchTab('benefits', this)">My Benefits</button>
        </div>

        {{-- Tab Content --}}
        <div id="tab-leave" class="tab-content">
            <section class="table-section">
                    <div class="table-header">
                        <div>
                            <h3 class="table-title">My Leave Requests</h3>
                            <p class="table-sub">6 of 6 records</p>
                        </div>
                        <div class="table-actions">
                            <select class="filter-select" id="filterType" onchange="applyLeaveFilters()">
                                <option value="">All Types</option>
                                <option>Vacation Leave</option>
                                <option>Sick Leave</option>
                                <option>Emergency Leave</option>
                                <option>Special Leave</option>
                            </select>
                            <select class="filter-select" id="filterStatus" onchange="applyLeaveFilters()">
                                <option value="">All Status</option>
                                <option>Approved</option>
                                <option>Pending</option>
                                <option>Rejected</option>
                            </select>
                            <button class="btn-export" onclick="openFileModal()">+ File Leave</button>
                        </div>
                    </div>
                    
                    <div class="table-wrapper">
                        <table class="payroll-table">
                            <thead>
                                <tr>
                                    <th>Leave ID</th>
                                    <th>Leave Type</th>
                                    <th>Date From</th>
                                    <th>Date To</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-type="Sick Leave" data-status="Approved">
                                    <td style="font-size:12;color:#9999bb;font-weight:500;">LV-2025-002</td>
                                    <td style="font-weight:600;">Sick Leave</td>
                                    <td>Jun 15, 2025</td>
                                    <td>Jun 16, 2025</td>
                                    <td style="font-weight:700;">2</td>
                                    <td style="font-size:12.5;color:#5a5888;">Medical consultation</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Sick Leave', 'Jun 15, 2025', 'Jun 16, 2025', 2, 'Medical consultation', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Vacation Leave" data-status="Approved">
                                    <td style="font-size:12;color:#9999bb;font-weight:500;">LV-2025-007</td>
                                    <td style="font-weight:600;">Vacation Leave</td>
                                    <td>Jun 10, 2025</td>
                                    <td>Jun 11, 2025</td>
                                    <td style="font-weight:700;">2</td>
                                    <td style="font-size:12.5;color:#5a5888;">Rest and recreation</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Vacation Leave', 'Jun 10, 2025', 'Jun 11, 2025', 2, 'Rest and recreation', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Emergency Leave" data-status="Approved">
                                    <td style="font-size:12;color:#9999bb;font-weight:500;">LV-2025-010</td>
                                    <td style="font-weight:600;">Emergency Leave</td>
                                    <td>May 22, 2025</td>
                                    <td>May 22, 2025</td>
                                    <td style="font-weight:700;">1</td>
                                    <td style="font-size:12.5;color:#5a5888;">Family emergency</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Emergency Leave', 'May 22, 2025', 'May 22, 2025', 1, 'Family emergency', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Sick Leave" data-status="Approved">
                                    <td style="font-size:12;color:#9999bb;font-weight:500;">LV-2025-013</td>
                                    <td style="font-weight:600;">Sick Leave</td>
                                    <td>May 5, 2025</td>
                                    <td>May 6, 2025</td>
                                    <td style="font-weight:700;">2</td>
                                    <td style="font-size:12.5;color:#5a5888;">Flu and fever</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Sick Leave', 'May 5, 2025', 'May 6, 2025', 2, 'Flu and fever', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Vacation Leave" data-status="Approved">
                                    <td style="font-size:12;color:#9999bb;font-weight:500;">LV-2025-018</td>
                                    <td style="font-weight:600;">Vacation Leave</td>
                                    <td>Apr 14, 2025</td>
                                    <td>Apr 16, 2025</td>
                                    <td style="font-weight:700;">3</td>
                                    <td style="font-size:12.5;color:#5a5888;">Family vacation</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Vacation Leave', 'Apr 14, 2025', 'Apr 16, 2025', 3, 'Family vacation', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Vacation Leave" data-status="Pending">
                                    <td style="font-size:12;color:#9999bb;font-weight:500;">LV-2025-021</td>
                                    <td style="font-weight:600;">Vacation Leave</td>
                                    <td>Jul 7, 2025</td>
                                    <td>Jul 9, 2025</td>
                                    <td style="font-weight:700;">3</td>
                                    <td style="font-size:12.5;color:#5a5888;">Personal trip</td>
                                    <td><span class="badge-status pending">Pending</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Vacation Leave', 'Jul 7, 2025', 'Jul 9, 2025', 3, 'Personal trip', 'Pending')">View</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                <div class="table-footer">
                    <span id="leaveCount">Showing <strong>6</strong> of <strong>6</strong> records</span>
                </div>
            </section>
        </div>

        <div id="tab-credits" class="tab-content hidden">
                <div class="credits-grid">
                    <div class="credit-card">
                        <div class="credit-header">
                            <div>
                                <label>Vacation Leave</label>
                                <h2 style="color:#0b044d">10</h2>
                                <p>days remaining</p>
                            </div>
                            <div class="credit-stats">
                                <p>Earned: <strong>15</strong></p>
                                <p>Used: <strong style="color:#8e1e18">5</strong></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:67%;background:#0b044d"></div>
                        </div>
                        <div class="progress-labels">
                            <span>0</span>
                            <span>15 days max</span>
                        </div>
                    </div>
                    <div class="credit-card">
                        <div class="credit-header">
                            <div>
                                <label>Sick Leave</label>
                                <h2 style="color:#15803d">11</h2>
                                <p>days remaining</p>
                            </div>
                            <div class="credit-stats">
                                <p>Earned: <strong>15</strong></p>
                                <p>Used: <strong style="color:#8e1e18">4</strong></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:73%;background:#15803d"></div>
                        </div>
                        <div class="progress-labels">
                            <span>0</span>
                            <span>15 days max</span>
                        </div>
                    </div>
                    <div class="credit-card">
                        <div class="credit-header">
                            <div>
                                <label>Emergency Leave</label>
                                <h2 style="color:#8e1e18">2</h2>
                                <p>days remaining</p>
                            </div>
                            <div class="credit-stats">
                                <p>Earned: <strong>3</strong></p>
                                <p>Used: <strong style="color:#8e1e18">1</strong></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:67%;background:#8e1e18"></div>
                        </div>
                        <div class="progress-labels">
                            <span>0</span>
                            <span>3 days max</span>
                        </div>
                    </div>
                    <div class="credit-card">
                        <div class="credit-header">
                            <div>
                                <label>Special Leave</label>
                                <h2 style="color:#d9bb00">3</h2>
                                <p>days remaining</p>
                            </div>
                            <div class="credit-stats">
                                <p>Earned: <strong>3</strong></p>
                                <p>Used: <strong style="color:#8e1e18">0</strong></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:100%;background:#d9bb00"></div>
                        </div>
                        <div class="progress-labels">
                            <span>0</span>
                            <span>3 days max</span>
                        </div>
                </div>
            </div>
        </div>

        <div id="tab-benefits" class="tab-content hidden">
                <div class="benefits-grid">
                    <div class="stat-card">
                        <div class="stat-top">
                            <p class="stat-label">GSIS Premium</p>
                            <div class="stat-icon-wrap" style="background:#0b044d15"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg></div>
                        </div>
                        <h2 class="stat-value">₱3,046</h2>
                        <div class="stat-footer">
                            <span class="stat-dot" style="background:#0b044d"></span>
                            <p class="stat-sub">Monthly contribution</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-top">
                            <p class="stat-label">PhilHealth</p>
                            <div class="stat-icon-wrap" style="background:#15803d15"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
                        </div>
                        <h2 class="stat-value">₱850</h2>
                        <div class="stat-footer">
                            <span class="stat-dot" style="background:#15803d"></span>
                            <p class="stat-sub">Monthly contribution</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-top">
                            <p class="stat-label">Pag-IBIG</p>
                            <div class="stat-icon-wrap" style="background:#8e1e1815"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg></div>
                        </div>
                        <h2 class="stat-value">₱100</h2>
                        <div class="stat-footer">
                            <span class="stat-dot" style="background:#8e1e18"></span>
                            <p class="stat-sub">Monthly contribution</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-top">
                            <p class="stat-label">Withholding Tax</p>
                            <div class="stat-icon-wrap" style="background:#d9bb0015"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
                        </div>
                        <h2 class="stat-value">₱2,772</h2>
                        <div class="stat-footer">
                            <span class="stat-dot" style="background:#d9bb00"></span>
                            <p class="stat-sub">Monthly deduction</p>
                        </div>
                    </div>
                </div>
                
                <section class="table-section">
                    <div class="table-header">
                        <div>
                            <h3 class="table-title">Benefits Breakdown — June 2025</h3>
                            <p class="table-sub">Government-mandated contributions and deductions</p>
                        </div>
                    </div>
                    <div class="table-wrapper">
                        <table class="payroll-table">
                            <thead>
                                <tr>
                                    <th>Benefit / Contribution</th>
                                    <th>Type</th>
                                    <th>Monthly Amount</th>
                                    <th>Annual Estimate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-weight:600;">GSIS Premium</td>
                                    <td><span class="dept-tag">Retirement & Insurance</span></td>
                                    <td class="deduction">₱3,046</td>
                                    <td style="font-weight:600;color:#5a5888;">₱36,552</td>
                                    <td><span class="badge-status processed">Active</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">PhilHealth</td>
                                    <td><span class="dept-tag">Health Insurance</span></td>
                                    <td class="deduction">₱850</td>
                                    <td style="font-weight:600;color:#5a5888;">₱10,200</td>
                                    <td><span class="badge-status processed">Active</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">Pag-IBIG</td>
                                    <td><span class="dept-tag">Housing Fund</span></td>
                                    <td class="deduction">₱100</td>
                                    <td style="font-weight:600;color:#5a5888;">₱1,200</td>
                                    <td><span class="badge-status processed">Active</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">Withholding Tax</td>
                                    <td><span class="dept-tag">Government Tax</span></td>
                                    <td class="deduction">₱2,772</td>
                                    <td style="font-weight:600;color:#5a5888;">₱33,264</td>
                                    <td><span class="badge-status processed">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-footer">
                        <p>🔒 Benefits data is confidential and visible only to you.</p>
                </div>
            </section>
        </div>

    </main>

</div>

{{-- Detail Modal --}}
<div class="modal-overlay" id="detailModal" style="display:none" onclick="closeModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">LEAVE REQUEST · LV-2025-002</span>
                <h3 class="modal-title" id="detailType">Sick Leave</h3>
                <p class="modal-sub" id="detailDates">Jun 15, 2025 — Jun 16, 2025</p>
            </div>
            <button class="modal-close" onclick="closeModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar" style="background:#8e1e18;width:48px;height:48px;border-radius:12px;font-size:16px">AR</div>
                <div>
                    <p class="modal-emp-id">PGS-0115</p>
                    <span class="badge-status processed" id="detailStatus">Approved</span>
                </div>
            </div>
            <span class="modal-section-label">LEAVE DETAILS</span>
            <div class="modal-row"><span>Leave Type</span><strong id="detailType2">Sick Leave</strong></div>
            <div class="modal-row"><span>Date From</span><strong id="detailFrom">Jun 15, 2025</strong></div>
            <div class="modal-row"><span>Date To</span><strong id="detailTo">Jun 16, 2025</strong></div>
            <div class="modal-row"><span>No. of Days</span><strong id="detailDays">2 days</strong></div>
            <span class="modal-section-label" style="margin-top:16px;">REASON</span>
            <div class="modal-row"><span id="detailReason">Medical consultation</span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal()">Close</button>
            <button class="modal-btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download
            </button>
        </div>
    </div>
</div>

{{-- File Leave Modal --}}
<div class="modal-overlay" id="fileModal" style="display:none" onclick="closeFileModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">NEW LEAVE REQUEST</span>
                <h3 class="modal-title">File a Leave</h3>
                <p class="modal-sub">Ana R. Reyes · PGS-0115</p>
            </div>
            <button class="modal-close" onclick="closeFileModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-field">
                    <label>Leave Type</label>
                    <select id="leaveType">
                        <option>Vacation Leave</option>
                        <option>Sick Leave</option>
                        <option>Emergency Leave</option>
                        <option>Special Leave</option>
                    </select>
                </div>
                <div class="form-field">
                    <label>No. of Days</label>
                    <input type="number" min="1" value="1" id="leaveDays">
                </div>
                <div class="form-field">
                    <label>Date From</label>
                    <input type="date" id="leaveFrom">
                </div>
                <div class="form-field">
                    <label>Date To</label>
                    <input type="date" id="leaveTo">
                </div>
            </div>
            <div class="form-field" style="margin-top:12px;">
                <label>Reason</label>
                <input type="text" id="leaveReason" placeholder="Brief reason for leave">
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeFileModal()">Cancel</button>
            <button class="modal-btn-primary" onclick="submitLeave()">Submit Request</button>
        </div>
    </div>
</div>

<style>
.modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(11,4,77,0.6); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; z-index:1000; padding:20px; }
.modal-box { background:#fff; border-radius:16px; width:100%; max-width:480px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); animation:slideUp 0.3s ease; }
@keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }
.modal-header { display:flex; justify-content:space-between; align-items:flex-start; padding:24px 24px 0; }
.modal-eyebrow { font-size:10.5px; color:#9999bb; font-weight:700; letter-spacing:1px; }
.modal-title { font-size:18px; font-weight:700; color:#0b044d; margin:4px 0 2px; }
.modal-sub { font-size:13px; color:#6b6a8a; margin:0; }
.modal-close { background:none; border:none; cursor:pointer; padding:4px; color:#9999bb; }
.modal-close:hover { color:#0b044d; }
.modal-body { padding:20px 24px; }
.modal-emp-row { display:flex; align-items:center; gap:16px; margin-bottom:20px; padding:16px; background:#f7f6ff; border-radius:12px; }
.modal-emp-id { font-size:11px; color:#9999bb; margin:0 0 4px; }
.modal-section-label { font-size:10.5px; font-weight:700; color:#9999bb; letter-spacing:1px; margin-bottom:12px; display:block; }
.modal-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0effe; }
.modal-row span { font-size:13px; color:#9999bb; font-weight:600; }
.modal-row strong { font-size:13px; color:#0b044d; font-weight:600; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px 24px; }
.modal-btn-ghost { padding:9px 18px; border-radius:9px; border:1.5px solid #dddcf0; background:#fff; font-size:13px; font-weight:600; color:#6b6a8a; cursor:pointer; }
.modal-btn-ghost:hover { border-color:#0b044d; color:#0b044d; }
.modal-btn-primary { padding:9px 18px; border-radius:9px; border:none; background:linear-gradient(135deg,#0b044d,#1a0f6e); color:#fff; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px; }
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.form-field label { display:block; font-size:11px; font-weight:700; color:#9999bb; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.form-field input, .form-field select { width:100%; padding:10px 12px; border-radius:8px; border:1.5px solid #e5e4f0; font-size:13px; font-family:'Poppins',sans-serif; }
.form-field input:focus, .form-field select:focus { outline:none; border-color:#a16207; }
.hidden { display:none; }
.credits-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.credit-card { background:#fff; border-radius:14px; border:1.5px solid #e5e4f0; padding:22px; }
.credit-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; }
.credit-header label { font-size:12px; color:#9999bb; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px; display:block; }
.credit-header h2 { font-size:28px; font-weight:800; margin:0; }
.credit-header p { font-size:12px; color:#9999bb; margin-top:2px; }
.credit-stats { text-align:right; }
.credit-stats p { font-size:11.5px; color:#9999bb; margin-bottom:4px; }
.credit-stats strong { color:#0b044d; }
.progress-bar { height:8px; background:#f0effe; border-radius:4px; overflow:hidden; }
.progress-fill { height:100%; border-radius:4px; transition:width 0.4s; }
.progress-labels { display:flex; justify-content:space-between; margin-top:6px; }
.progress-labels span { font-size:11px; color:#9999bb; }
.dept-tag { font-size:10px; font-weight:600; padding:3px 8px; border-radius:20px; background:#f0effe; color:#6b3fa0; }
.deduction { color:#dc2626; font-weight:600; }
.benefits-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
.benefits-grid .stat-card { margin-bottom:0; }
.tabs { display:flex; gap:4px; margin-bottom:24px; border-bottom:1.5px solid #eceaf8; }
.tab-btn { background:none; border:none; cursor:pointer; padding:10px 20px; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; color:#9999bb; border-bottom:2.5px solid transparent; margin-bottom:-1.5px; transition:all 0.2s; }
.tab-btn:hover { color:#0b044d; }
.tab-btn.active { color:#0b044d; border-bottom-color:#0b044d; }

@media (max-width: 768px) {
    .welcome-banner { flex-direction:column; gap:16px; }
    .banner-left { flex-direction:column; align-items:flex-start; }
    .banner-left p { font-size:11px; }
    .banner-right { flex-wrap:wrap; gap:8px; }
    .stats-grid-4 { grid-template-columns:1fr !important; }
    .table-header { flex-direction:column; gap:12px; }
    .table-actions { flex-direction:column; width:100%; }
    .table-actions select, .table-actions .btn-export { width:100%; }
    .table-wrapper { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .payroll-table { min-width:800px; }
    .tabs { overflow-x:auto; -webkit-overflow-scrolling:touch; flex-wrap:nowrap; }
    .tab-btn { white-space:nowrap; padding:10px 16px; font-size:12px; }
    .credits-grid { grid-template-columns:1fr; }
    .benefits-grid { grid-template-columns:1fr !important; }
    .form-grid { grid-template-columns:1fr; }
    .modal-box { max-width:calc(100% - 32px); }
    .modal-header { padding:20px 20px 0; }
    .modal-body { padding:16px 20px; }
    .modal-footer { padding:12px 20px 20px; flex-direction:column; }
    .modal-btn-ghost, .modal-btn-primary { width:100%; justify-content:center; }
}

@media (max-width: 480px) {
    .banner-icon { width:40px; height:40px; }
    .banner-icon svg { width:18px; height:18px; }
    .welcome-banner h2 { font-size:18px; }
    .stat-card { padding:16px; }
    .stat-value { font-size:24px; }
    .credit-card { padding:16px; }
    .credit-header h2 { font-size:24px; }
    .table-title { font-size:16px; }
    .modal-title { font-size:16px; }
}
</style>

<script>
    const sidebar   = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const logoText  = document.getElementById('logo-text');
    const navLabel  = document.getElementById('nav-label');
    const userInfo  = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const overlay   = document.getElementById('mobile-overlay');

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

    function applyLeaveFilters() {
        const type   = document.getElementById('filterType').value;
        const status = document.getElementById('filterStatus').value;
        const rows   = document.querySelectorAll('#tab-leave tbody tr');
        let visible  = 0;
        rows.forEach(row => {
            const matchType   = !type   || row.dataset.type   === type;
            const matchStatus = !status || row.dataset.status === status;
            const show = matchType && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const total = rows.length;
        document.getElementById('leaveCount').innerHTML =
            visible === total
                ? 'Showing <strong>' + total + '</strong> of <strong>' + total + '</strong> records'
                : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> records';
    }

    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function openDetailModal(type, from, to, days, reason, status) {
        document.getElementById('detailType').textContent = type;
        document.getElementById('detailType2').textContent = type;
        document.getElementById('detailFrom').textContent = from;
        document.getElementById('detailTo').textContent = to;
        document.getElementById('detailDays').textContent = days + ' day' + (days > 1 ? 's' : '');
        document.getElementById('detailReason').textContent = reason;
        document.getElementById('detailDates').textContent = from + ' — ' + to;
        document.getElementById('detailStatus').textContent = status;
        document.getElementById('detailStatus').className = 'badge-status ' + (status === 'Approved' ? 'processed' : 'pending');
        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    function openFileModal() {
        document.getElementById('fileModal').style.display = 'flex';
    }

    function closeFileModal() {
        document.getElementById('fileModal').style.display = 'none';
    }

    function submitLeave() {
        alert('Leave request submitted successfully!');
        closeFileModal();
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeFileModal();
        }
    });
</script>

@include('permanent.permanent-chatbot')

@endsection