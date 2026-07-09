@extends('layouts.employee')

@section('title', 'Travel Orders · PRIME HRIS')

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

        @include('employee.topbar.employeeTopbar')

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

        {{-- Tab Content --}}
        @include('employee.travelOrder.partials.travel-history-tab')

    </main>

</div>

@include('employee.travelOrder.modals.fileTravelOrderModal')
@include('employee.travelOrder.modals.viewTravelOrderModal')

@include('employee.chatbot.employeeChatbot')

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

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTravelOrderModal();
        }
    });
</script>

@endsection
