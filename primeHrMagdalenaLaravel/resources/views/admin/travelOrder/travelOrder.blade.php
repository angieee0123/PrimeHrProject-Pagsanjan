@extends('layouts.app')

@push('styles')
    @vite(['resources/css/adminLeaveAndBenefits.css', 'resources/css/travelOrder.css'])
@endpush

@section('content')

@include('admin.topbar.travelOrderTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

<div class="stats-grid stats-grid-4 to-stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Travel Orders</p>
            <div class="stat-icon-wrap to-icon-purple">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingOrders->total() + $approvedOrders->total() + $disapprovedOrders->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot to-dot-purple"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Approval</p>
            <div class="stat-icon-wrap to-icon-yellow">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingOrders->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot to-dot-yellow"></span>
            <p class="stat-sub">Needs action</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap to-icon-green">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $approvedOrders->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot to-dot-green"></span>
            <p class="stat-sub">This period</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Disapproved</p>
            <div class="stat-icon-wrap to-icon-red">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $disapprovedOrders->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot to-dot-red"></span>
            <p class="stat-sub">Rejected</p>
        </div>
    </div>
</div>

{{-- Filter Toolbar --}}
<div class="filter-card">
    <div class="filter-card-fields">
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="date" class="fc-input" id="travelOrderFilterDateFrom" title="Travel date from" onchange="filterPendingOrders(); filterApprovedOrders(); filterDisapprovedOrders();">
        </div>
        <span class="fc-sep">to</span>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="date" class="fc-input" id="travelOrderFilterDateTo" title="Travel date to" onchange="filterPendingOrders(); filterApprovedOrders(); filterDisapprovedOrders();">
        </div>
        <div class="fc-divider"></div>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
            <select class="fc-select" id="travelOrderFilterDept" onchange="filterPendingOrders(); filterApprovedOrders(); filterDisapprovedOrders();">
                <option value="all">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <select class="fc-select" id="travelOrderFilterMode" onchange="filterPendingOrders(); filterApprovedOrders(); filterDisapprovedOrders();">
                <option value="all">All Modes</option>
                <option value="Private Vehicle">Private Vehicle</option>
                <option value="Government Vehicle">Government Vehicle</option>
                <option value="Public Transportation">Public Transportation</option>
                <option value="Air Travel">Air Travel</option>
                <option value="Other">Other</option>
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
    <button class="tab-btn active" onclick="switchTab('pending')">Pending</button>
    <button class="tab-btn" onclick="switchTab('approved')">Approved</button>
    <button class="tab-btn" onclick="switchTab('disapproved')">Disapproved</button>
</div>

@include('admin.travelOrder.partials.pending-orders-tab')

@include('admin.travelOrder.partials.approved-orders-tab')

@include('admin.travelOrder.partials.disapproved-orders-tab')

</div>

@include('admin.travelOrder.modals.viewTravelOrderModal')

@push('scripts')
    @vite('resources/js/travelOrder/travelOrder.js')
@endpush
@endsection
