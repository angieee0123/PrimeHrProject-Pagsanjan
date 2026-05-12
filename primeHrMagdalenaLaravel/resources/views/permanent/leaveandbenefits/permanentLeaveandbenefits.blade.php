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
                <section class="table-section" style="margin-bottom: 24px;">
                    <div class="table-header">
                        <div>
                            <h3 class="table-title">My Leave Credits Balance</h3>
                            <p class="table-sub">Current year leave balances and usage · Updated in real-time</p>
                        </div>
                        <div class="table-actions">
                            <select class="filter-select" id="filterLeaveCategory" onchange="filterLeaveCredits()">
                                <option value="all">All Leave Types</option>
                                <option value="accrued">Accrued Only</option>
                                <option value="fixed">Fixed Only</option>
                                <option value="available">With Balance</option>
                            </select>
                            <button class="btn-export">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Export Report
                            </button>
                        </div>
                    </div>
                    <div class="table-wrapper" style="overflow-x: auto;">
                        <table class="payroll-table" style="min-width: 900px;">
                            <thead>
                                <tr>
                                    <th style="width: 80px; text-align: center;">Code</th>
                                    <th style="min-width: 200px;">Leave Type</th>
                                    <th style="width: 120px; text-align: center;">Total Credits</th>
                                    <th style="width: 110px; text-align: center;">Used</th>
                                    <th style="width: 110px; text-align: center;">Pending</th>
                                    <th style="width: 120px; text-align: center;">Available</th>
                                    <th style="width: 100px; text-align: center;">Type</th>
                                    <th style="width: 140px; text-align: center;">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveTypes ?? [] as $type)
                                <tr class="leave-credit-row" data-type="{{ $type->is_accrued ? 'accrued' : 'fixed' }}" data-available="{{ $type->annual_limit }}">
                                    <td style="text-align: center;">
                                        <div style="display: inline-block; padding: 6px 10px; background: {{ ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'][$loop->index % 6] }}; color: white; border-radius: 6px; font-weight: 700; font-size: 11px; letter-spacing: 0.5px;">
                                            {{ $type->leave_code }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 36px; height: 36px; border-radius: 8px; background: {{ ['#ede9fe', '#fee2e2', '#dbeafe', '#fef3c7', '#d1fae5', '#fce7f3'][$loop->index % 6] }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ ['#7c3aed', '#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$loop->index % 6] }}" stroke-width="2">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                    <polyline points="14 2 14 8 20 8"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p style="font-size: 13px; color: #0b044d; font-weight: 600; margin: 0; line-height: 1.3;">{{ $type->leave_name }}</p>
                                                @if($type->attachment_info)
                                                <p style="font-size: 11px; color: #6b6a8a; margin: 2px 0 0 0; line-height: 1.4;">{{ Str::limit($type->attachment_info, 60) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @php
                                            $totalCredits = $type->annual_limit > 0 ? $type->annual_limit : 0;
                                        @endphp
                                        <div style="font-size: 15px; font-weight: 700; color: #0b044d;">
                                            {{ number_format($totalCredits, 1) }}
                                        </div>
                                        <div style="font-size: 10px; color: #9ca3af; margin-top: 2px;">days</div>
                                    </td>
                                    <td style="text-align: center;">
                                        @php
                                            $used = 0; // TODO: Get from actual balance
                                        @endphp
                                        <div style="display: inline-block; padding: 4px 10px; background: #fee2e2; border-radius: 6px;">
                                            <span style="font-size: 14px; font-weight: 700; color: #991b1b;">{{ number_format($used, 1) }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @php
                                            $pending = 0; // TODO: Get from actual balance
                                        @endphp
                                        <div style="display: inline-block; padding: 4px 10px; background: #fef3c7; border-radius: 6px;">
                                            <span style="font-size: 14px; font-weight: 700; color: #92400e;">{{ number_format($pending, 1) }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @php
                                            $available = $totalCredits - $used - $pending;
                                        @endphp
                                        <div style="display: inline-block; padding: 6px 12px; background: #d1fae5; border-radius: 6px; border: 2px solid #10b981;">
                                            <span style="font-size: 15px; font-weight: 800; color: #065f46;">{{ number_format($available, 1) }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @if($type->is_accrued)
                                            <span style="display: inline-block; padding: 4px 10px; background: #dbeafe; color: #1e40af; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 3px;">
                                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                                    <polyline points="17 6 23 6 23 12"/>
                                                </svg>
                                                Accrued
                                            </span>
                                        @else
                                            <span style="display: inline-block; padding: 4px 10px; background: #f3f4f6; color: #4b5563; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 3px;">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                </svg>
                                                Fixed
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @php
                                            $percentage = $totalCredits > 0 ? (($available / $totalCredits) * 100) : 0;
                                            $barColor = $percentage > 70 ? '#10b981' : ($percentage > 30 ? '#f59e0b' : '#ef4444');
                                        @endphp
                                        <div style="width: 100%; max-width: 120px; margin: 0 auto;">
                                            <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                                <div style="width: {{ $percentage }}%; height: 100%; background: {{ $barColor }}; transition: width 0.3s ease;"></div>
                                            </div>
                                            <div style="font-size: 10px; color: #6b7280; margin-top: 4px; font-weight: 600;">{{ number_format($percentage, 0) }}%</div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 60px 20px;">
                                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No leave credits available</p>
                                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af;">Leave balances will appear here once initialized</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="table-footer" style="margin-top: 16px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <p style="margin: 0;" id="leaveCreditsCount">Showing <strong>{{ $leaveTypes->count() }}</strong> of <strong>{{ $leaveTypes->count() }}</strong> leave types</p>
                        </div>
                        <div class="pagination" id="leaveCreditspagination">
                            <button class="page-btn" id="prevPageBtn" onclick="changeLeaveCreditsPage('prev')" disabled>‹</button>
                            <button class="page-btn active" data-page="1">1</button>
                            <button class="page-btn" id="nextPageBtn" onclick="changeLeaveCreditsPage('next')" disabled>›</button>
                        </div>
                    </div>
                    
                    {{-- Summary Cards --}}
                    <div style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div>
                                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Total Leave Credits</div>
                                <div style="font-size: 28px; font-weight: 800;">{{ $leaveTypes->sum('annual_limit') }}</div>
                                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">days allocated</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Available Balance</div>
                                <div style="font-size: 28px; font-weight: 800;">{{ $leaveTypes->sum('annual_limit') }}</div>
                                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">days remaining</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Leave Types</div>
                                <div style="font-size: 28px; font-weight: 800;">{{ $leaveTypes->count() }}</div>
                                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">types available</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Utilization Rate</div>
                                <div style="font-size: 28px; font-weight: 800;">0%</div>
                                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">of total credits</div>
                            </div>
                        </div>
                    </div>
                </section>

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
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 700px;">
        <form id="leaveApplicationForm" method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <div>
                    <span class="modal-eyebrow">NEW LEAVE REQUEST</span>
                    <h3 class="modal-title">File a Leave Application</h3>
                    <p class="modal-sub">{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</p>
                </div>
                <button type="button" class="modal-close" onclick="closeFileModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                
                {{-- Leave Type Selection --}}
                <div class="form-field" style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Leave Type <span style="color: #8e1e18;">*</span>
                    </label>
                    <select name="leave_code" id="leaveType" required onchange="updateLeaveInfo()" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit; background: white; cursor: pointer; transition: all 0.2s;">
                        <option value="">Select leave type...</option>
                        @foreach($leaveTypes ?? [] as $type)
                            <option value="{{ $type->leave_code }}" 
                                    data-requires-attachment="{{ $type->requires_attachment }}" 
                                    data-attachment-info="{{ $type->attachment_info }}"
                                    data-available="{{ $type->annual_limit }}"
                                    data-is-accrued="{{ $type->is_accrued }}">
                                {{ $type->leave_name }} ({{ $type->leave_code }}) @if($type->annual_limit > 0) - {{ number_format($type->annual_limit, 0) }} days max @endif
                            </option>
                        @endforeach
                    </select>
                    <div id="leaveTypeInfo" style="display: none; margin-top: 8px; padding: 10px; background: #f0f9ff; border-left: 3px solid #0ea5e9; border-radius: 4px;">
                        <div style="display: flex; align-items: start; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            <div>
                                <p id="leaveTypeInfoText" style="margin: 0; font-size: 12px; color: #0369a1; line-height: 1.5;"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Date Range --}}
                <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 12px; font-size: 13px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Leave Period
                    </label>
                    <div class="form-grid" style="gap: 12px;">
                        <div class="form-field">
                            <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Date From <span style="color: #8e1e18;">*</span></label>
                            <input type="date" name="start_date" id="leaveFrom" required onchange="calculateDays()" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                        </div>
                        <div class="form-field">
                            <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Date To <span style="color: #8e1e18;">*</span></label>
                            <input type="date" name="end_date" id="leaveTo" required onchange="calculateDays()" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                        </div>
                    </div>
                    
                    {{-- Days Display --}}
                    <div style="margin-top: 12px; padding: 12px; background: white; border-radius: 6px; border: 2px dashed #d1d5db;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 12px; color: #6b7280;">Total Business Days</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="number" name="number_of_days" id="leaveDays" min="0.5" step="0.5" value="0" readonly style="width: 70px; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 14px; font-weight: 700; color: #0b044d; text-align: center; background: #f9fafb;">
                                <span style="font-size: 13px; color: #6b7280; font-weight: 500;">days</span>
                            </div>
                        </div>
                        <p style="margin: 8px 0 0 0; font-size: 11px; color: #9ca3af;">Weekends are automatically excluded</p>
                    </div>
                </div>
                
                {{-- Reason --}}
                <div class="form-field" style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Reason for Leave <span style="color: #8e1e18;">*</span>
                    </label>
                    <textarea name="reason" id="leaveReason" rows="4" placeholder="Please provide a brief reason for your leave request..." required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical; line-height: 1.6;"></textarea>
                    <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                        <small style="color: #9ca3af; font-size: 11px;">Be specific and concise</small>
                        <small id="reasonCounter" style="color: #9ca3af; font-size: 11px;">0 / 500</small>
                    </div>
                </div>
                
                {{-- Attachment --}}
                <div class="form-field" id="attachmentField" style="display: none; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                        </svg>
                        Supporting Document <span style="color: #8e1e18;">*</span>
                    </label>
                    <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; background: #fafafa; transition: all 0.2s;" id="attachmentDropZone">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin: 0 auto 12px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <input type="file" name="attachment" id="leaveAttachment" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" onchange="handleFileSelect(this)">
                        <label for="leaveAttachment" style="cursor: pointer;">
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #374151; font-weight: 500;">Click to upload or drag and drop</p>
                            <p style="margin: 0; font-size: 11px; color: #9ca3af;">PDF, JPG, PNG (Max 5MB)</p>
                        </label>
                        <div id="fileNameDisplay" style="display: none; margin-top: 12px; padding: 8px 12px; background: #f0f9ff; border-radius: 4px; font-size: 12px; color: #0369a1;"></div>
                    </div>
                    <div id="attachmentInfo" style="margin-top: 8px; padding: 10px; background: #fef3c7; border-left: 3px solid #f59e0b; border-radius: 4px;">
                        <div style="display: flex; align-items: start; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            <p id="attachmentInfoText" style="margin: 0; font-size: 12px; color: #92400e; line-height: 1.5;">Required document for this leave type</p>
                        </div>
                    </div>
                </div>
                
                {{-- Error Message --}}
                <div id="errorMessage" style="display: none; padding: 12px; background: #fee2e2; border-left: 3px solid #ef4444; border-radius: 6px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <p id="errorMessageText" style="margin: 0; color: #991b1b; font-size: 13px; line-height: 1.5;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px; background: #f9fafb;">
                <button type="button" class="modal-btn-ghost" onclick="closeFileModal()" style="padding: 10px 20px;">
                    Cancel
                </button>
                <button type="submit" class="modal-btn-primary" id="submitBtn" style="padding: 10px 24px; display: flex; align-items: center; gap: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Submit Leave Request
                </button>
            </div>
        </form>
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

    function filterLeaveCredits() {
        const category = document.getElementById('filterLeaveCategory').value;
        const rows = document.querySelectorAll('.leave-credit-row');
        let visible = 0;
        
        rows.forEach(row => {
            let show = true;
            
            if (category === 'accrued') {
                show = row.dataset.type === 'accrued';
            } else if (category === 'fixed') {
                show = row.dataset.type === 'fixed';
            } else if (category === 'available') {
                show = parseFloat(row.dataset.available) > 0;
            }
            
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        
        // Reset pagination after filter
        initLeaveCreditsTable();
    }

    // Leave Credits Table Pagination
    let leaveCreditsCurrentPage = 1;
    let leaveCreditsRowsPerPage = 10;
    let leaveCreditsVisibleRows = [];

    function initLeaveCreditsTable() {
        const allRows = document.querySelectorAll('.leave-credit-row');
        leaveCreditsVisibleRows = Array.from(allRows).filter(row => row.style.display !== 'none');
        leaveCreditsCurrentPage = 1;
        displayLeaveCreditsPage();
        updateLeaveCreditsPageButtons();
    }

    function displayLeaveCreditsPage() {
        const startIndex = (leaveCreditsCurrentPage - 1) * leaveCreditsRowsPerPage;
        const endIndex = startIndex + leaveCreditsRowsPerPage;
        
        leaveCreditsVisibleRows.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        updateLeaveCreditsCounter();
    }

    function updateLeaveCreditsCounter() {
        const total = leaveCreditsVisibleRows.length;
        const startIndex = (leaveCreditsCurrentPage - 1) * leaveCreditsRowsPerPage + 1;
        const endIndex = Math.min(leaveCreditsCurrentPage * leaveCreditsRowsPerPage, total);
        
        const counter = document.getElementById('leaveCreditsCount');
        if (counter) {
            if (total === 0) {
                counter.innerHTML = 'No leave types found';
            } else {
                counter.innerHTML = `Showing <strong>${startIndex}</strong> to <strong>${endIndex}</strong> of <strong>${total}</strong> leave types`;
            }
        }
    }

    function updateLeaveCreditsPageButtons() {
        const totalPages = Math.ceil(leaveCreditsVisibleRows.length / leaveCreditsRowsPerPage);
        const pagination = document.getElementById('leaveCreditspagination');
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        
        if (!pagination) return;
        
        // Update prev/next buttons
        prevBtn.disabled = leaveCreditsCurrentPage === 1;
        nextBtn.disabled = leaveCreditsCurrentPage === totalPages || totalPages === 0;
        
        // Clear existing page buttons (except prev/next)
        const pageButtons = pagination.querySelectorAll('.page-btn:not(#prevPageBtn):not(#nextPageBtn)');
        pageButtons.forEach(btn => btn.remove());
        
        // Add page buttons
        if (totalPages > 0) {
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'page-btn' + (i === leaveCreditsCurrentPage ? ' active' : '');
                pageBtn.textContent = i;
                pageBtn.onclick = () => goToLeaveCreditsPage(i);
                pagination.insertBefore(pageBtn, nextBtn);
            }
        }
    }

    function changeLeaveCreditsPage(direction) {
        const totalPages = Math.ceil(leaveCreditsVisibleRows.length / leaveCreditsRowsPerPage);
        
        if (direction === 'prev' && leaveCreditsCurrentPage > 1) {
            leaveCreditsCurrentPage--;
        } else if (direction === 'next' && leaveCreditsCurrentPage < totalPages) {
            leaveCreditsCurrentPage++;
        }
        
        displayLeaveCreditsPage();
        updateLeaveCreditsPageButtons();
    }

    function goToLeaveCreditsPage(page) {
        leaveCreditsCurrentPage = page;
        displayLeaveCreditsPage();
        updateLeaveCreditsPageButtons();
    }

    // Initialize pagination when credits tab is shown
    document.addEventListener('DOMContentLoaded', function() {
        initLeaveCreditsTable();
    });

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
        
        // Initialize pagination when switching to credits tab
        if (tabId === 'credits') {
            setTimeout(() => initLeaveCreditsTable(), 100);
        }
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
        document.getElementById('leaveApplicationForm').reset();
        document.getElementById('errorMessage').style.display = 'none';
        document.getElementById('attachmentField').style.display = 'none';
    }

    function calculateDays() {
        const from = document.getElementById('leaveFrom').value;
        const to = document.getElementById('leaveTo').value;
        
        if (from && to) {
            const startDate = new Date(from);
            const endDate = new Date(to);
            
            if (endDate < startDate) {
                document.getElementById('errorMessage').textContent = 'End date cannot be before start date';
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('leaveDays').value = 0;
                return;
            }
            
            // Calculate business days (excluding weekends)
            let days = 0;
            let currentDate = new Date(startDate);
            
            while (currentDate <= endDate) {
                const dayOfWeek = currentDate.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Sunday (0) or Saturday (6)
                    days++;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            document.getElementById('leaveDays').value = days;
            document.getElementById('errorMessage').style.display = 'none';
        }
    }

    function updateLeaveInfo() {
        const select = document.getElementById('leaveType');
        const option = select.options[select.selectedIndex];
        const requiresAttachment = option.dataset.requiresAttachment === '1';
        const attachmentInfo = option.dataset.attachmentInfo;
        const available = option.dataset.available;
        const isAccrued = option.dataset.isAccrued === '1';
        
        const attachmentField = document.getElementById('attachmentField');
        const leaveTypeInfo = document.getElementById('leaveTypeInfo');
        const leaveTypeInfoText = document.getElementById('leaveTypeInfoText');
        const attachmentInput = document.getElementById('leaveAttachment');
        const attachmentInfoText = document.getElementById('attachmentInfoText');
        
        if (requiresAttachment) {
            attachmentField.style.display = 'block';
            attachmentInput.required = true;
            if (attachmentInfo) {
                attachmentInfoText.textContent = attachmentInfo;
            }
        } else {
            attachmentField.style.display = 'none';
            attachmentInput.required = false;
        }
        
        if (select.value) {
            let infoText = '';
            if (available && available > 0) {
                infoText = `Available balance: ${available} days`;
            }
            if (isAccrued) {
                infoText += (infoText ? ' • ' : '') + 'This leave accrues monthly (1.25 days/month)';
            }
            if (infoText) {
                leaveTypeInfoText.textContent = infoText;
                leaveTypeInfo.style.display = 'block';
            } else {
                leaveTypeInfo.style.display = 'none';
            }
        } else {
            leaveTypeInfo.style.display = 'none';
        }
    }

    function handleFileSelect(input) {
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const dropZone = document.getElementById('attachmentDropZone');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            
            if (file.size > 5 * 1024 * 1024) {
                document.getElementById('errorMessageText').textContent = 'File size exceeds 5MB limit';
                document.getElementById('errorMessage').style.display = 'block';
                input.value = '';
                fileNameDisplay.style.display = 'none';
                return;
            }
            
            fileNameDisplay.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <span style="font-weight: 500;">${file.name}</span>
                    </div>
                    <span style="color: #6b7280; font-size: 11px;">${fileSize} MB</span>
                </div>
            `;
            fileNameDisplay.style.display = 'block';
            dropZone.style.borderColor = '#0ea5e9';
            dropZone.style.background = '#f0f9ff';
            document.getElementById('errorMessage').style.display = 'none';
        }
    }

    // Character counter for reason
    document.getElementById('leaveReason')?.addEventListener('input', function() {
        const counter = document.getElementById('reasonCounter');
        const length = this.value.length;
        counter.textContent = `${length} / 500`;
        
        if (length > 500) {
            counter.style.color = '#dc2626';
            this.value = this.value.substring(0, 500);
        } else if (length > 450) {
            counter.style.color = '#f59e0b';
        } else {
            counter.style.color = '#9ca3af';
        }
    });

    // Form submission
    document.getElementById('leaveApplicationForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const originalBtnContent = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/></svg><style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style> Submitting...';
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                submitBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Success!';
                submitBtn.style.background = '#15803d';
                setTimeout(() => {
                    closeFileModal();
                    location.reload();
                }, 1000);
            } else {
                document.getElementById('errorMessageText').textContent = data.message || 'Failed to submit leave request';
                document.getElementById('errorMessage').style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                document.getElementById('errorMessage').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorMessageText').textContent = 'An error occurred. Please try again.';
            document.getElementById('errorMessage').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
        });
    });

    function submitLeave() {
        document.getElementById('leaveApplicationForm').submit();
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