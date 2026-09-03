@extends('layouts.mayor')

@section('title', "Leave Applications · PRIME HRIS")

@section('content')

<main class="enterprise-hr-dashboard">

@include('mayor.notification.mayorNotification')

@include('mayor.topbar.leaveTopbar')

{{-- Stats Grid --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Applications</p>
            <div class="stat-icon-wrap" style="background:var(--gp-bg-tint-2)">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['total']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:var(--gp-pri)"></span>
            <p class="stat-sub">All records</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap" style="background:#e9f9ef">
                <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['approved']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:var(--theme-success)"></span>
            <p class="stat-sub">Approved leave</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending</p>
            <div class="stat-icon-wrap" style="background:var(--theme-warning-subtle)">
                <svg width="17" height="17" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['pending']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#c9a227"></span>
            <p class="stat-sub">Awaiting approval</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Rejected</p>
            <div class="stat-icon-wrap" style="background:#fdedec">
                <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['rejected']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:var(--theme-danger)"></span>
            <p class="stat-sub">Rejected applications</p>
        </div>
    </div>
</div>

{{-- Applications Table --}}
<div class="table-section" style="margin:0">
    <div class="table-header" style="background:linear-gradient(135deg,var(--gp-bg-tint-2) 0%,#fff 100%)">
        <div>
            <p class="table-title">Leave Applications</p>
            <p class="table-sub">{{ number_format($stats['total']) }} filed · read-only</p>
        </div>
        <div class="table-actions">
            <div style="position:relative">
                <svg width="14" height="14" fill="none" stroke="#8f8daf" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" id="mayorLeaveSearch" placeholder="Search employees..." oninput="mayorFilterLeave()" style="font-size:11px;padding:6px 12px 6px 32px;border-radius:6px;border:1.5px solid var(--theme-neutral-300);width:200px;font-family:inherit">
            </div>
            <select class="filter-select" id="mayorLeaveType" onchange="mayorFilterLeave()">
                <option value="">All Leave Types</option>
                @foreach($leaveTypes as $type)
                <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="mayorLeaveStatus" onchange="mayorFilterLeave()">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table" id="mayorLeaveTable">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Dates</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Filed</th>
                </tr>
            </thead>
            <tbody>
                @php $avatarColors = ['#0b044d', '#8e1e18', '#150c63', '#a52820', '#c9a227', '#56547a']; @endphp
                @forelse($leaveApplications as $index => $leave)
                    @php
                        $emp = $leave->employee;
                        if (!$emp) continue;
                        $fullName = trim($emp->first_name . ' ' . $emp->last_name);
                        $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                        $leaveTypeName = $leave->leaveType->leave_name ?? $leave->leave_code;
                        $statusClass = $leave->status === 'approved' ? 'processed' : ($leave->status === 'pending' ? 'pending' : 'on-hold');
                    @endphp
                    <tr data-status="{{ $leave->status }}" data-type="{{ $leaveTypeName }}" data-search="{{ strtolower($fullName) }}">
                        <td>
                            <div class="emp-cell">
                                @if($emp->photo)
                                    <img src="{{ $emp->photo }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--gp-border)">
                                @else
                                    <div style="width:36px;height:36px;border-radius:50%;background:{{ $avatarColors[$index % count($avatarColors)] }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:12px;border:2px solid var(--gp-border)">{{ $initials }}</div>
                                @endif
                                <div>
                                    <p class="emp-name">{{ $fullName }}</p>
                                    <p class="emp-id">{{ $emp->employmentDetail->departmentRelation->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">{{ $leaveTypeName }}</span></td>
                        <td style="font-size:12px;color:var(--gp-text-mid);white-space:nowrap">{{ $leave->start_date->format('M d') }} – {{ $leave->end_date->format('M d, Y') }}</td>
                        <td style="text-align:center">{{ $leave->number_of_days }}</td>
                        <td><span class="badge-status {{ $statusClass }}">{{ ucfirst($leave->status) }}</span></td>
                        <td style="font-size:11.5px;color:var(--gp-text-soft);white-space:nowrap">{{ $leave->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;padding:32px 0;color:var(--theme-neutral-600);font-size:12.5px">No leave applications yet</td></tr>
                @endforelse
            </tbody>
        </table>
        <p id="mayorLeaveNoResults" style="display:none;text-align:center;padding:32px 0;color:var(--theme-neutral-600);font-size:12.5px;margin:0">No applications match your search/filter</p>
    </div>
</div>

</main>

@push('scripts')
    @vite('resources/js/mayor/leave/mayorLeave.js')
@endpush
@endsection
