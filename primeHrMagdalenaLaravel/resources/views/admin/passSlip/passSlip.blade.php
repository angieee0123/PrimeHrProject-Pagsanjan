@extends('layouts.app')

@section('content')

@include('admin.topbar.passSlipTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

<div class="stats-grid stats-grid-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Pass Slips</p>
            <div class="stat-icon-wrap" style="background: #f2f1fb;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingSlips->total() + $approvedSlips->total() + $disapprovedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Approval</p>
            <div class="stat-icon-wrap" style="background: #fbf6e3;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #c9a227;"></span>
            <p class="stat-sub">Needs action</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap" style="background: #e8f9ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $approvedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">This period</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Disapproved</p>
            <div class="stat-icon-wrap" style="background: #fdf0ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $disapprovedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
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
<section class="table-section" id="passslip-pending-tab" style="border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #f2f1fb 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Pending Pass Slips</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Awaiting approval · {{ $pendingSlips->total() }} records</p>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Type</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Reason</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Time Out</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Time In</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingSlips as $slip)
                <tr class="passslip-pending-row" data-department="{{ $slip->employee->employmentDetail->departmentRelation->name ?? '' }}" data-type="{{ $slip->type }}" data-slip-date="{{ $slip->date ? $slip->date->format('Y-m-d') : '' }}" style="transition: all 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div class="emp-cell" style="display: flex; align-items: center; gap: 12px;">
                            @if($slip->employee->photo)
                                <img src="{{ $slip->employee->photo }}" alt="{{ $slip->employee->first_name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;">
                            @else
                                <div style="background: {{ $passSlipColors[$loop->index % 6] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid #e2e8f0; flex-shrink: 0;">{{ strtoupper(substr($slip->employee->first_name ?? 'N', 0, 1) . substr($slip->employee->last_name ?? 'A', 0, 1)) }}</div>
                            @endif
                            <div style="min-width: 0;">
                                <p style="margin: 0 0 2px; font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</p>
                                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 500;">{{ $slip->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center;">
                        <span style="display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; background: {{ $slip->type === 'official_activity' ? '#f2f1fb' : '#fbf6e3' }}; color: {{ $slip->type === 'official_activity' ? '#0b044d' : '#c9a227' }};">{{ $slip->type === 'official_activity' ? 'Official' : 'Personal' }}</span>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #111827; font-weight: 600;">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center;">{{ $slip->time_out ? \Carbon\Carbon::parse($slip->time_out)->format('g:i A') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center;">{{ $slip->time_in ? \Carbon\Carbon::parse($slip->time_in)->format('g:i A') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <form method="POST" action="{{ route('admin.passslip.approve', $slip->id) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" onclick="return confirm('Approve this pass slip?')" style="padding: 6px 12px; border: 1px solid #bbf7d0; border-radius: 8px; background: #f0fdf4; color: #15803d; font-size: 11px; font-weight: 700; cursor: pointer;">Approve</button>
                            </form>
                            <button onclick="disapprovePassSlip({{ $slip->id }})" style="padding: 6px 12px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #b42318; font-size: 11px; font-weight: 700; cursor: pointer;">Disapprove</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No pending pass slips</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Pending pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;">{{ $pendingSlips->firstItem() ?? 0 }}</strong>–<strong style="color: #0b044d; font-weight: 700;">{{ $pendingSlips->lastItem() ?? 0 }}</strong> of <strong style="color: #0b044d; font-weight: 700;">{{ $pendingSlips->total() }}</strong> records</p>
    </div>
</section>

{{-- Approved Pass Slips --}}
<section class="table-section" id="passslip-approved-tab" style="display: none; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Approved Pass Slips</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Successfully approved · {{ $approvedSlips->total() }} records</p>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Type</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Reason</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Approved By</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Approved Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Form</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedSlips as $slip)
                <tr class="passslip-approved-row" data-department="{{ $slip->employee->employmentDetail->departmentRelation->name ?? '' }}" data-type="{{ $slip->type }}" data-slip-date="{{ $slip->date ? $slip->date->format('Y-m-d') : '' }}" style="transition: all 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div class="emp-cell" style="display: flex; align-items: center; gap: 12px;">
                            @if($slip->employee->photo)
                                <img src="{{ $slip->employee->photo }}" alt="{{ $slip->employee->first_name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;">
                            @else
                                <div style="background: {{ $passSlipColors[$loop->index % 6] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid #e2e8f0; flex-shrink: 0;">{{ strtoupper(substr($slip->employee->first_name ?? 'N', 0, 1) . substr($slip->employee->last_name ?? 'A', 0, 1)) }}</div>
                            @endif
                            <div style="min-width: 0;">
                                <p style="margin: 0 0 2px; font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</p>
                                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 500;">{{ $slip->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center;">
                        <span style="display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; background: {{ $slip->type === 'official_activity' ? '#f2f1fb' : '#fbf6e3' }}; color: {{ $slip->type === 'official_activity' ? '#0b044d' : '#c9a227' }};">{{ $slip->type === 'official_activity' ? 'Official' : 'Personal' }}</span>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #111827; font-weight: 600;">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #15803d; font-weight: 600;">{{ $slip->approver->name ?? 'Admin User' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ $slip->approved_at ? $slip->approved_at->format('M d, Y') : 'N/A' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div style="position: relative; display: flex; justify-content: center;">
                            <button class="ps-ellipsis-btn" onclick="togglePassSlipActionMenu(event, this)" title="Actions">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                            </button>
                            <div class="ps-action-menu" style="display: none;">
                                <button onclick="openPassSlipFormModal(
                                    {{ $slip->id }},
                                    '{{ addslashes(($slip->employee->first_name ?? '') . ' ' . ($slip->employee->last_name ?? '')) }}',
                                    '{{ $slip->employee->employee_id ?? 'N/A' }}',
                                    '{{ $slip->type === 'official_activity' ? 'Official Activity' : 'Personal Reason' }}',
                                    '{{ $slip->slip_number }}'
                                )">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Form
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No approved pass slips</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Approved pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;">{{ $approvedSlips->firstItem() ?? 0 }}</strong>–<strong style="color: #0b044d; font-weight: 700;">{{ $approvedSlips->lastItem() ?? 0 }}</strong> of <strong style="color: #0b044d; font-weight: 700;">{{ $approvedSlips->total() }}</strong> records</p>
    </div>
</section>

{{-- Disapproved Pass Slips --}}
<section class="table-section" id="passslip-disapproved-tab" style="display: none; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #fdf0ef 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Disapproved Pass Slips</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Rejected requests · {{ $disapprovedSlips->total() }} records</p>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Reason</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disapprovedSlips as $slip)
                <tr class="passslip-disapproved-row" data-department="{{ $slip->employee->employmentDetail->departmentRelation->name ?? '' }}" data-type="{{ $slip->type }}" data-slip-date="{{ $slip->date ? $slip->date->format('Y-m-d') : '' }}" style="transition: all 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div class="emp-cell" style="display: flex; align-items: center; gap: 12px;">
                            @if($slip->employee->photo)
                                <img src="{{ $slip->employee->photo }}" alt="{{ $slip->employee->first_name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;">
                            @else
                                <div style="background: {{ $passSlipColors[$loop->index % 6] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid #e2e8f0; flex-shrink: 0;">{{ strtoupper(substr($slip->employee->first_name ?? 'N', 0, 1) . substr($slip->employee->last_name ?? 'A', 0, 1)) }}</div>
                            @endif
                            <div style="min-width: 0;">
                                <p style="margin: 0 0 2px; font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</p>
                                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 500;">{{ $slip->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #111827; font-weight: 600;">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #8e1e18;">{{ $slip->remarks ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No disapproved pass slips</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Disapproved pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedSlips->firstItem() ?? 0 }}</strong>–<strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedSlips->lastItem() ?? 0 }}</strong> of <strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedSlips->total() }}</strong> records</p>
    </div>
</section>

</div>

{{-- Pass Slip Form Preview Modal --}}
<div class="modal-overlay" id="passSlipFormModal" onclick="closePassSlipFormModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 920px; width: 95vw;">
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
        <div class="modal-body" style="padding: 0; background: #e8e8f0;">
            <iframe id="psFormFrame"
                title="Pass Slip"
                style="width: 100%; height: 65vh; border: none; display: block; background: #fff;"
                src="about:blank"></iframe>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closePassSlipFormModal()">Close</button>
            <div style="display: flex; gap: 8px; margin-left: auto;">
                <button class="modal-btn-primary" onclick="printPassSlipForm()" style="background: #6366f1;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Form
                </button>
                <button class="modal-btn-primary" onclick="downloadPassSlipForm()" style="background: #10b981;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.ps-ellipsis-btn {
    background: none;
    border: none;
    color: #8f8daf;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.ps-ellipsis-btn:hover {
    background: #f1f5f9;
    color: #0b044d;
}
.ps-action-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.15);
    z-index: 100;
    min-width: 160px;
    margin-top: 6px;
    overflow: hidden;
    animation: psSlideDown 0.2s ease-out;
}
.ps-action-menu button {
    width: 100%;
    padding: 11px 14px;
    border: none;
    background: none;
    text-align: left;
    font-size: 12px;
    color: #0b044d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ps-action-menu button:hover {
    background: #f2f1fb;
}
@keyframes psSlideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

@vite(['resources/css/adminLeaveAndBenefits.css'])

<script>
function togglePassSlipActionMenu(event, btn) {
    event.stopPropagation();
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.ps-action-menu').forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', () => {
    document.querySelectorAll('.ps-action-menu').forEach(m => m.style.display = 'none');
});

window.currentPassSlipId = null;

function openPassSlipFormModal(id, name, empId, type, slipNumber) {
    window.currentPassSlipId = id;
    document.getElementById('psFormSlipNumber').textContent = 'PASS SLIP · ' + slipNumber;
    document.getElementById('psFormEmployeeName').textContent = name;
    document.getElementById('psFormEmployeeId').textContent = empId;
    document.getElementById('psFormType').textContent = type;

    const frame = document.getElementById('psFormFrame');
    frame.src = `/admin/passslip/${id}/view-form?embed=1`;

    document.getElementById('passSlipFormModal').style.display = 'flex';
}

function closePassSlipFormModal() {
    document.getElementById('psFormFrame').src = 'about:blank';
    document.getElementById('passSlipFormModal').style.display = 'none';
    window.currentPassSlipId = null;
}

function printPassSlipForm() {
    const frame = document.getElementById('psFormFrame');
    if (frame?.contentWindow) {
        frame.contentWindow.print();
        return;
    }
    if (!window.currentPassSlipId) return;
    const printWindow = window.open(`/admin/passslip/${window.currentPassSlipId}/view-form`, '_blank');
    if (printWindow) {
        printWindow.addEventListener('load', () => printWindow.print());
    }
}

function downloadPassSlipForm() {
    if (!window.currentPassSlipId) return;
    window.location.href = `/admin/passslip/${window.currentPassSlipId}/download-form`;
}

function switchPassSlipTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.table-section').forEach(section => section.style.display = 'none');

    event.target.classList.add('active');
    document.getElementById('passslip-' + tabName + '-tab').style.display = 'block';
}

function filterPassSlipRows(tabName) {
    const dept = document.getElementById('passSlipFilterDept').value;
    const type = document.getElementById('passSlipFilterType').value;
    const dateFrom = document.getElementById('passSlipFilterDateFrom').value;
    const dateTo = document.getElementById('passSlipFilterDateTo').value;
    const search = (document.getElementById('passSlipSearchInput')?.value || '').toLowerCase().trim();
    document.querySelectorAll('.passslip-' + tabName + '-row').forEach(row => {
        const matchDept = dept === 'all' || row.dataset.department === dept;
        const matchType = type === 'all' || row.dataset.type === type;
        const matchDateFrom = !dateFrom || row.dataset.slipDate >= dateFrom;
        const matchDateTo = !dateTo || row.dataset.slipDate <= dateTo;
        const matchSearch = search === '' || row.textContent.toLowerCase().includes(search);
        row.style.display = matchDept && matchType && matchDateFrom && matchDateTo && matchSearch ? '' : 'none';
    });
}

function searchPassSlips() {
    filterPassSlipRows('pending');
    filterPassSlipRows('approved');
    filterPassSlipRows('disapproved');
}

function disapprovePassSlip(id) {
    const reason = prompt('Reason for disapproval:');
    if (!reason) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/passslip/${id}/disapprove`;
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="reason" value="${reason}">`;
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
