@extends('layouts.permanent')

@section('title', 'Leave & Benefits · PRIME HRIS')

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

    @include('permanent.sidebar.permanentSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-leavebenefits">

        @include('permanent.notification.permanentNotification')

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <h2>Leave & Benefits</h2>
                    <p><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; Nurse II · Municipal Health Office · PGS-0115</p>
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
                    <div class="stat-icon-wrap stat-icon-wrap-primary"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                </div>
                <h2 class="stat-value">6</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub">All time</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Days Used</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                </div>
                <h2 class="stat-value">13</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">Across all types</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Pending Requests</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                </div>
                <h2 class="stat-value">1</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">Awaiting approval</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">VL + SL Balance</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                </div>
                <h2 class="stat-value">21</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">10 VL · 11 SL</p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="lb-tabs">
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
                        <table class="payroll-table lb-leave-table">
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
                                    <td class="lb-leave-id">LV-2025-002</td>
                                    <td class="lb-leave-type">Sick Leave</td>
                                    <td>Jun 15, 2025</td>
                                    <td>Jun 16, 2025</td>
                                    <td class="lb-leave-days">2</td>
                                    <td class="lb-leave-reason">Medical consultation</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Sick Leave', 'Jun 15, 2025', 'Jun 16, 2025', 2, 'Medical consultation', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Vacation Leave" data-status="Approved">
                                    <td class="lb-leave-id">LV-2025-007</td>
                                    <td class="lb-leave-type">Vacation Leave</td>
                                    <td>Jun 10, 2025</td>
                                    <td>Jun 11, 2025</td>
                                    <td class="lb-leave-days">2</td>
                                    <td class="lb-leave-reason">Rest and recreation</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Vacation Leave', 'Jun 10, 2025', 'Jun 11, 2025', 2, 'Rest and recreation', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Emergency Leave" data-status="Approved">
                                    <td class="lb-leave-id">LV-2025-010</td>
                                    <td class="lb-leave-type">Emergency Leave</td>
                                    <td>May 22, 2025</td>
                                    <td>May 22, 2025</td>
                                    <td class="lb-leave-days">1</td>
                                    <td class="lb-leave-reason">Family emergency</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Emergency Leave', 'May 22, 2025', 'May 22, 2025', 1, 'Family emergency', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Sick Leave" data-status="Approved">
                                    <td class="lb-leave-id">LV-2025-013</td>
                                    <td class="lb-leave-type">Sick Leave</td>
                                    <td>May 5, 2025</td>
                                    <td>May 6, 2025</td>
                                    <td class="lb-leave-days">2</td>
                                    <td class="lb-leave-reason">Flu and fever</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Sick Leave', 'May 5, 2025', 'May 6, 2025', 2, 'Flu and fever', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Vacation Leave" data-status="Approved">
                                    <td class="lb-leave-id">LV-2025-018</td>
                                    <td class="lb-leave-type">Vacation Leave</td>
                                    <td>Apr 14, 2025</td>
                                    <td>Apr 16, 2025</td>
                                    <td class="lb-leave-days">3</td>
                                    <td class="lb-leave-reason">Family vacation</td>
                                    <td><span class="badge-status processed">Approved</span></td>
                                    <td><button class="btn-view" onclick="openDetailModal('Vacation Leave', 'Apr 14, 2025', 'Apr 16, 2025', 3, 'Family vacation', 'Approved')">View</button></td>
                                </tr>
                                <tr data-type="Vacation Leave" data-status="Pending">
                                    <td class="lb-leave-id">LV-2025-021</td>
                                    <td class="lb-leave-type">Vacation Leave</td>
                                    <td>Jul 7, 2025</td>
                                    <td>Jul 9, 2025</td>
                                    <td class="lb-leave-days">3</td>
                                    <td class="lb-leave-reason">Personal trip</td>
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
                                <h2 class="lb-credit-value lb-credit-value-primary">10</h2>
                                <p>days remaining</p>
                            </div>
                            <div class="credit-stats">
                                <p>Earned: <strong>15</strong></p>
                                <p>Used: <strong class="lb-credit-used">5</strong></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill lb-progress-primary lb-w-67"></div>
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
                                <h2 class="lb-credit-value lb-credit-value-success">11</h2>
                                <p>days remaining</p>
                            </div>
                            <div class="credit-stats">
                                <p>Earned: <strong>15</strong></p>
                                <p>Used: <strong class="lb-credit-used">4</strong></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill lb-progress-success lb-w-73"></div>
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
                                <h2 class="lb-credit-value lb-credit-value-danger">2</h2>
                                <p>days remaining</p>
                            </div>
                            <div class="credit-stats">
                                <p>Earned: <strong>3</strong></p>
                                <p>Used: <strong class="lb-credit-used">1</strong></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill lb-progress-danger lb-w-67"></div>
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
                                <h2 class="lb-credit-value lb-credit-value-warning">3</h2>
                                <p>days remaining</p>
                            </div>
                            <div class="credit-stats">
                                <p>Earned: <strong>3</strong></p>
                                <p>Used: <strong class="lb-credit-used">0</strong></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill lb-progress-warning lb-w-100"></div>
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
                            <div class="stat-icon-wrap stat-icon-wrap-primary"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg></div>
                        </div>
                        <h2 class="stat-value">₱3,046</h2>
                        <div class="stat-footer">
                            <span class="stat-dot stat-dot-primary"></span>
                            <p class="stat-sub">Monthly contribution</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-top">
                            <p class="stat-label">PhilHealth</p>
                            <div class="stat-icon-wrap stat-icon-wrap-success"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
                        </div>
                        <h2 class="stat-value">₱850</h2>
                        <div class="stat-footer">
                            <span class="stat-dot stat-dot-success"></span>
                            <p class="stat-sub">Monthly contribution</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-top">
                            <p class="stat-label">Pag-IBIG</p>
                            <div class="stat-icon-wrap stat-icon-wrap-danger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg></div>
                        </div>
                        <h2 class="stat-value">₱100</h2>
                        <div class="stat-footer">
                            <span class="stat-dot stat-dot-danger"></span>
                            <p class="stat-sub">Monthly contribution</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-top">
                            <p class="stat-label">Withholding Tax</p>
                            <div class="stat-icon-wrap stat-icon-wrap-warning"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
                        </div>
                        <h2 class="stat-value">₱2,772</h2>
                        <div class="stat-footer">
                            <span class="stat-dot stat-dot-amber"></span>
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
                        <table class="payroll-table lb-benefits-table">
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
                                    <td class="lb-benefit-name">GSIS Premium</td>
                                    <td><span class="dept-tag">Retirement & Insurance</span></td>
                                    <td class="deduction">₱3,046</td>
                                    <td class="lb-benefit-annual">₱36,552</td>
                                    <td><span class="badge-status processed">Active</span></td>
                                </tr>
                                <tr>
                                    <td class="lb-benefit-name">PhilHealth</td>
                                    <td><span class="dept-tag">Health Insurance</span></td>
                                    <td class="deduction">₱850</td>
                                    <td class="lb-benefit-annual">₱10,200</td>
                                    <td><span class="badge-status processed">Active</span></td>
                                </tr>
                                <tr>
                                    <td class="lb-benefit-name">Pag-IBIG</td>
                                    <td><span class="dept-tag">Housing Fund</span></td>
                                    <td class="deduction">₱100</td>
                                    <td class="lb-benefit-annual">₱1,200</td>
                                    <td><span class="badge-status processed">Active</span></td>
                                </tr>
                                <tr>
                                    <td class="lb-benefit-name">Withholding Tax</td>
                                    <td><span class="dept-tag">Government Tax</span></td>
                                    <td class="deduction">₱2,772</td>
                                    <td class="lb-benefit-annual">₱33,264</td>
                                    <td><span class="badge-status processed">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-footer">
                        <p class="lb-benefits-footnote">🔒 Benefits data is confidential and visible only to you.</p>
                </div>
            </section>
        </div>

    </main>

</div>

{{-- Detail Modal --}}
<div class="modal-overlay" id="detailModal" onclick="closeModal()">
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
                <div class="emp-avatar modal-emp-avatar">AR</div>
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
            <span class="modal-section-label modal-section-deductions">REASON</span>
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
<div class="modal-overlay" id="fileModal" onclick="closeFileModal()">
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
            <div class="form-field lb-form-field-gap">
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
        document.querySelectorAll('.tab-content').forEach(c => {
            c.classList.add('hidden');
            c.style.display = 'none';
        });
        const active = document.getElementById('tab-' + tabId);
        active.classList.remove('hidden');
        active.style.display = 'block';
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

@include('permanent.chatbot.permanentChatbot')

@endsection