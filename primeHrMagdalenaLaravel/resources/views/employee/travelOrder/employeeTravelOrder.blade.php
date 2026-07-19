@extends('layouts.employee')

@section('title', 'Travel Orders · PRIME HRIS')

@push('styles')
    @vite('resources/css/employee/employeeTravelOrder.css')
@endpush

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Travel Order'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-leavebenefits glass-shell">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.travelOrderTopbar')

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Travel Orders</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $travelOrders->total() }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub">All time</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Pending Approval</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $travelOrders->where('status', 'pending')->count() }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">Awaiting approval</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Approved</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $travelOrders->where('status', 'approved')->count() }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">Successfully approved</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Rejected</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $travelOrders->where('status', 'rejected')->count() }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">Rejected requests</p>
                </div>
            </div>
        </div>

        {{-- Companion Requests (invitations from other employees) --}}
        @include('employee.travelOrder.partials.companion-invitations')

        {{-- Tab Content --}}
        @include('employee.travelOrder.partials.travel-history-tab')

    </main>

</div>

@include('employee.travelOrder.modals.fileTravelOrderModal')
@include('employee.travelOrder.modals.viewTravelOrderModal')

@include('employee.chatbot.employeeChatbot')

@push('scripts')
<script>
    window.travelOrderPageData = {
        employeeId: @json(auth()->user()->employee->employee_id ?? ''),
    };
</script>
    @vite('resources/js/employee/employeeTravelOrder.js')
@endpush

@endsection
