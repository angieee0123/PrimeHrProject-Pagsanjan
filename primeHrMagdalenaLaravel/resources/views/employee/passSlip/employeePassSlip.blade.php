@extends('layouts.employee')

@section('title', 'Pass Slip · PRIME HRIS')

@push('styles')
    @vite('resources/css/employee/employeePassSlip.css')
@endpush

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Pass Slip'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-leavebenefits glass-shell">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.passSlipTopbar')

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Pass Slips</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $passSlips->total() }}</h2>
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
                <h2 class="stat-value">{{ $passSlips->where('status', 'pending')->count() }}</h2>
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
                <h2 class="stat-value">{{ $passSlips->where('status', 'approved')->count() }}</h2>
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
                <h2 class="stat-value">{{ $passSlips->where('status', 'rejected')->count() }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">Rejected requests</p>
                </div>
            </div>
        </div>

        {{-- Tab Content --}}
        @include('employee.passSlip.partials.passslip-history-tab')

    </main>

</div>

@include('employee.passSlip.modals.filePassSlipModal')
@include('employee.passSlip.modals.viewPassSlipModal')

@include('employee.chatbot.employeeChatbot')

@push('scripts')
    @vite('resources/js/employee/employeePassSlip.js')
@endpush

@endsection
