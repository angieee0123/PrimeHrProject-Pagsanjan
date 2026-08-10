@extends('layouts.app')

@push('styles')
    @vite(['resources/css/admin/adminLeaveAndBenefits.css', 'resources/css/passSlip.css'])
@endpush

@section('content')

@include('admin.topbar.passSlipTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

<div class="stats-grid stats-grid-4 ps-stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Pass Slips</p>
            <div class="stat-icon-wrap ps-icon-purple">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingSlips->total() + $approvedSlips->total() + $disapprovedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot ps-dot-purple"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Approval</p>
            <div class="stat-icon-wrap ps-icon-yellow">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot ps-dot-yellow"></span>
            <p class="stat-sub">Needs action</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap ps-icon-green">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $approvedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot ps-dot-green"></span>
            <p class="stat-sub">This period</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Disapproved</p>
            <div class="stat-icon-wrap ps-icon-red">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $disapprovedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot ps-dot-red"></span>
            <p class="stat-sub">Rejected</p>
        </div>
    </div>
</div>

{{-- Filter Toolbar --}}
<div class="filter-card">
    <div class="filter-card-fields">
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="date" class="fc-input" id="passSlipFilterDateFrom" title="Date from" onchange="filterPassSlipRows('pending'); filterPassSlipRows('approved'); filterPassSlipRows('disapproved');">
        </div>
        <span class="fc-sep">to</span>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="date" class="fc-input" id="passSlipFilterDateTo" title="Date to" onchange="filterPassSlipRows('pending'); filterPassSlipRows('approved'); filterPassSlipRows('disapproved');">
        </div>
        <div class="fc-divider"></div>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
            <select class="fc-select" id="passSlipFilterDept" onchange="filterPassSlipRows('pending'); filterPassSlipRows('approved'); filterPassSlipRows('disapproved');">
                <option value="all">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <select class="fc-select" id="passSlipFilterType" onchange="filterPassSlipRows('pending'); filterPassSlipRows('approved'); filterPassSlipRows('disapproved');">
                <option value="all">All Types</option>
                <option value="official_activity">Official Activity</option>
                <option value="personal_reason">Personal Reason</option>
            </select>
        </div>
    </div>
    <div class="filter-card-actions">
        <button class="btn-ghost">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </button>
    </div>
</div>

<!-- Tabs -->
<div class="seg-tabs">
    <button class="tab-btn active" onclick="switchPassSlipTab('pending')">Pending</button>
    <button class="tab-btn" onclick="switchPassSlipTab('approved')">Approved</button>
    <button class="tab-btn" onclick="switchPassSlipTab('disapproved')">Disapproved</button>
</div>

@php $passSlipColors = ['#0b044d','#8e1e18','#150c63','#a52820','#150c63','#56547a']; @endphp

{{-- Pending Pass Slips --}}
<section class="table-section ps-table-section" id="passslip-pending-tab">
    <div class="table-header ps-table-header ps-header-purple">
        <div>
            <h3 class="table-title ps-table-title">Pending Pass Slips</h3>
            <p class="table-sub ps-table-sub">Awaiting approval · {{ $pendingSlips->total() }} records</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="ps-th">Employee</th>
                    <th class="ps-th ps-th-center">Type</th>
                    <th class="ps-th">Reason</th>
                    <th class="ps-th">Date</th>
                    <th class="ps-th ps-th-center">Time Out</th>
                    <th class="ps-th ps-th-center">Time In</th>
                    <th class="ps-th ps-th-center row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingSlips as $slip)
                <tr class="passslip-pending-row ps-row" data-department="{{ $slip->employee->employmentDetail->departmentRelation->name ?? '' }}" data-type="{{ $slip->type }}" data-slip-date="{{ $slip->date ? $slip->date->format('Y-m-d') : '' }}">
                    <td class="ps-td">
                        <div class="emp-cell ps-emp-cell">
                            @if($slip->employee->photo)
                                <img src="{{ $slip->employee->photo }}" alt="{{ $slip->employee->first_name }}" class="ps-avatar-img">
                            @else
                                <div style="background: {{ $passSlipColors[$loop->index % 6] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid var(--theme-neutral-300); flex-shrink: 0;">{{ strtoupper(substr($slip->employee->first_name ?? 'N', 0, 1) . substr($slip->employee->last_name ?? 'A', 0, 1)) }}</div>
                            @endif
                            <div class="ps-emp-info">
                                <p class="ps-emp-name">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</p>
                                <p class="ps-emp-id">{{ $slip->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="ps-td ps-td-center">
                        <span style="display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; background: {{ $slip->type === 'official_activity' ? 'var(--gp-bg-tint-2)' : '#fbf6e3' }}; color: {{ $slip->type === 'official_activity' ? 'var(--gp-pri)' : '#c9a227' }};">{{ $slip->type === 'official_activity' ? 'Official' : 'Personal' }}</span>
                    </td>
                    <td class="ps-td ps-td-muted">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td class="ps-td ps-td-strong">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td class="ps-td ps-td-center">{{ $slip->time_out ? \Carbon\Carbon::parse($slip->time_out)->format('g:i A') : '' }}</td>
                    <td class="ps-td ps-td-center">{{ $slip->time_in ? \Carbon\Carbon::parse($slip->time_in)->format('g:i A') : '' }}</td>
                    <td class="ps-td row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="pendingSlipMenu{{ $slip->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for {{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}'s pass slip">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="pendingSlipMenu{{ $slip->id }}" role="menu" aria-label="Pass slip actions">
                            <form method="POST" action="{{ route('admin.passslip.approve', $slip->id) }}" class="row-menu-form">
                                @csrf
                                <button type="submit" role="menuitem" class="row-menu-item is-accept" onclick="return confirm('Approve this pass slip?')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Approve
                                </button>
                            </form>
                            <button type="button" role="menuitem" class="row-menu-item is-danger"
                                    onclick="closeRowMenu(); disapprovePassSlip({{ $slip->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                                Disapprove
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="ps-empty-td">
                        <div class="ps-empty-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <p class="ps-empty-title">No pending pass slips</p>
                        <p class="ps-empty-sub">Pending pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p class="ps-footer-text">Showing <strong class="ps-footer-strong">{{ $pendingSlips->firstItem() ?? 0 }}</strong>–<strong class="ps-footer-strong">{{ $pendingSlips->lastItem() ?? 0 }}</strong> of <strong class="ps-footer-strong">{{ $pendingSlips->total() }}</strong> records</p>
    </div>
</section>

{{-- Approved Pass Slips --}}
<section class="table-section ps-table-section ps-hidden" id="passslip-approved-tab">
    <div class="table-header ps-table-header ps-header-green">
        <div>
            <h3 class="table-title ps-table-title">Approved Pass Slips</h3>
            <p class="table-sub ps-table-sub">Successfully approved · {{ $approvedSlips->total() }} records</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="ps-th">Employee</th>
                    <th class="ps-th ps-th-center">Type</th>
                    <th class="ps-th">Reason</th>
                    <th class="ps-th">Date</th>
                    <th class="ps-th">Approved By</th>
                    <th class="ps-th">Approved Date</th>
                    <th class="ps-th ps-th-center row-menu-head">Form</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedSlips as $slip)
                <tr class="passslip-approved-row ps-row" data-department="{{ $slip->employee->employmentDetail->departmentRelation->name ?? '' }}" data-type="{{ $slip->type }}" data-slip-date="{{ $slip->date ? $slip->date->format('Y-m-d') : '' }}">
                    <td class="ps-td">
                        <div class="emp-cell ps-emp-cell">
                            @if($slip->employee->photo)
                                <img src="{{ $slip->employee->photo }}" alt="{{ $slip->employee->first_name }}" class="ps-avatar-img">
                            @else
                                <div style="background: {{ $passSlipColors[$loop->index % 6] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid var(--theme-neutral-300); flex-shrink: 0;">{{ strtoupper(substr($slip->employee->first_name ?? 'N', 0, 1) . substr($slip->employee->last_name ?? 'A', 0, 1)) }}</div>
                            @endif
                            <div class="ps-emp-info">
                                <p class="ps-emp-name">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</p>
                                <p class="ps-emp-id">{{ $slip->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="ps-td ps-td-center">
                        <span style="display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; background: {{ $slip->type === 'official_activity' ? 'var(--gp-bg-tint-2)' : '#fbf6e3' }}; color: {{ $slip->type === 'official_activity' ? 'var(--gp-pri)' : '#c9a227' }};">{{ $slip->type === 'official_activity' ? 'Official' : 'Personal' }}</span>
                    </td>
                    <td class="ps-td ps-td-muted">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td class="ps-td ps-td-strong">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td class="ps-td ps-td-green">{{ $slip->approver->name ?? 'Admin User' }}</td>
                    <td class="ps-td ps-td-muted">{{ $slip->approved_at ? $slip->approved_at->format('M d, Y') : 'N/A' }}</td>
                    <td class="ps-td row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="approvedSlipMenu{{ $slip->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for pass slip {{ $slip->slip_number }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="approvedSlipMenu{{ $slip->id }}" role="menu" aria-label="Pass slip actions">
                            <button type="button" role="menuitem" class="row-menu-item" onclick="closeRowMenu(); openPassSlipFormModal(
                                {{ $slip->id }},
                                '{{ addslashes(($slip->employee->first_name ?? '') . ' ' . ($slip->employee->last_name ?? '')) }}',
                                '{{ $slip->employee->employee_id ?? 'N/A' }}',
                                '{{ $slip->type === 'official_activity' ? 'Official Activity' : 'Personal Reason' }}',
                                '{{ $slip->slip_number }}'
                            )">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View form
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="ps-empty-td">
                        <div class="ps-empty-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <p class="ps-empty-title">No approved pass slips</p>
                        <p class="ps-empty-sub">Approved pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p class="ps-footer-text">Showing <strong class="ps-footer-strong">{{ $approvedSlips->firstItem() ?? 0 }}</strong>–<strong class="ps-footer-strong">{{ $approvedSlips->lastItem() ?? 0 }}</strong> of <strong class="ps-footer-strong">{{ $approvedSlips->total() }}</strong> records</p>
    </div>
</section>

{{-- Disapproved Pass Slips --}}
<section class="table-section ps-table-section ps-hidden" id="passslip-disapproved-tab">
    <div class="table-header ps-table-header ps-header-red">
        <div>
            <h3 class="table-title ps-table-title">Disapproved Pass Slips</h3>
            <p class="table-sub ps-table-sub">Rejected requests · {{ $disapprovedSlips->total() }} records</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="ps-th">Employee</th>
                    <th class="ps-th">Reason</th>
                    <th class="ps-th">Date</th>
                    <th class="ps-th">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disapprovedSlips as $slip)
                <tr class="passslip-disapproved-row ps-row" data-department="{{ $slip->employee->employmentDetail->departmentRelation->name ?? '' }}" data-type="{{ $slip->type }}" data-slip-date="{{ $slip->date ? $slip->date->format('Y-m-d') : '' }}">
                    <td class="ps-td">
                        <div class="emp-cell ps-emp-cell">
                            @if($slip->employee->photo)
                                <img src="{{ $slip->employee->photo }}" alt="{{ $slip->employee->first_name }}" class="ps-avatar-img">
                            @else
                                <div style="background: {{ $passSlipColors[$loop->index % 6] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid var(--theme-neutral-300); flex-shrink: 0;">{{ strtoupper(substr($slip->employee->first_name ?? 'N', 0, 1) . substr($slip->employee->last_name ?? 'A', 0, 1)) }}</div>
                            @endif
                            <div class="ps-emp-info">
                                <p class="ps-emp-name">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</p>
                                <p class="ps-emp-id">{{ $slip->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="ps-td ps-td-muted">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td class="ps-td ps-td-strong">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td class="ps-td ps-td-red">{{ $slip->remarks ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="ps-empty-td">
                        <div class="ps-empty-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </div>
                        <p class="ps-empty-title">No disapproved pass slips</p>
                        <p class="ps-empty-sub">Disapproved pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p class="ps-footer-text">Showing <strong class="ps-footer-strong">{{ $disapprovedSlips->firstItem() ?? 0 }}</strong>–<strong class="ps-footer-strong">{{ $disapprovedSlips->lastItem() ?? 0 }}</strong> of <strong class="ps-footer-strong">{{ $disapprovedSlips->total() }}</strong> records</p>
    </div>
</section>

</div>

{{-- Pass Slip Form Preview Modal --}}
<div class="modal-overlay" id="passSlipFormModal" onclick="closePassSlipFormModal()" style="display: none;">
    <div class="modal-box ps-modal-box-wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="psFormSlipNumber">PASS SLIP · PS-2026-0001</span>
                <h3 class="modal-title" id="psFormEmployeeName">Employee Name</h3>
                <p class="modal-sub">
                    <span id="psFormEmployeeId">PGS-0115</span>
                    · <span id="psFormType">Official Activity</span>
                </p>
            </div>
            <button class="modal-close" onclick="closePassSlipFormModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body ps-modal-body-flush">
            <iframe id="psFormFrame"
                title="Pass Slip"
                class="ps-modal-iframe"
                src="about:blank"></iframe>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closePassSlipFormModal()">Close</button>
            <div class="ps-modal-footer-actions">
                <button class="modal-btn-primary ps-btn-print" onclick="printPassSlipForm()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Form
                </button>
                <button class="modal-btn-primary ps-btn-download" onclick="downloadPassSlipForm()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/passSlip/passSlip.js')
@endpush
@endsection
