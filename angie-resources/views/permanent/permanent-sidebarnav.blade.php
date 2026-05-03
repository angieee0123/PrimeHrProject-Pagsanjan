@php
$navItems = [
    ['id' => 'dashboard',   'label' => 'Dashboard',              'route' => route('permanent.dashboard')],
    ['id' => 'payslip',     'label' => 'Payslip',                'route' => route('permanent.payslip')],
    ['id' => 'attendance',  'label' => 'Attendance',             'route' => route('permanent.attendance')],
    ['id' => 'leave',       'label' => 'Leave & Benefits',       'route' => route('permanent.leave')],
    ['id' => 'training',    'label' => 'Training',               'route' => route('permanent.training')],
    ['id' => 'performance', 'label' => 'Performance',            'route' => route('permanent.performance')],
    ['id' => 'profile',     'label' => 'Profile',                'route' => route('permanent.profile')],
    ['id' => 'settings',    'label' => 'Settings',               'route' => route('permanent.settings')],
];
$currentRoute = Route::currentRouteName();
@endphp

<aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <div class="logo">
            <div class="logo-mark">
                <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover"
                     onerror="this.style.display='none'">
            </div>
            <div class="logo-text-wrap" id="logo-text">
                <span class="logo-text">PRIME HRIS</span>
                <span class="logo-sub">Pagsanjan, Laguna</span>
            </div>
        </div>
        <button class="toggle-btn" id="toggle-btn" aria-label="Toggle sidebar">‹</button>
    </div>

    <p class="nav-section-label" id="nav-label">NAVIGATION</p>

    <nav class="sidebar-nav" id="sidebar-nav">
        @foreach($navItems as $item)
        <a href="{{ $item['route'] }}"
           class="nav-item {{ str_contains($currentRoute, $item['id']) ? 'active' : '' }}"
           title="{{ $item['label'] }}">
            <span class="nav-icon">
                @if($item['id'] === 'dashboard')
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                @elseif($item['id'] === 'payslip')
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                @elseif($item['id'] === 'attendance')
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                @elseif($item['id'] === 'leave')
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                @elseif($item['id'] === 'training')
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                @elseif($item['id'] === 'performance')
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                @elseif($item['id'] === 'profile')
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                @elseif($item['id'] === 'settings')
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                @endif
            </span>
            <span class="nav-label">{{ $item['label'] }}</span>
            @if(str_contains($currentRoute, $item['id']))
                <span class="nav-active-bar"></span>
            @endif
        </a>
        @endforeach
    </nav>

    <div class="sidebar-footer" id="sidebar-footer">
        <div class="user-avatar-wrap">
            <div class="user-avatar">AR</div>
            <span class="user-status-dot"></span>
        </div>
        <div class="user-info" id="user-info">
            <p class="user-name">Ana R. Reyes</p>
            <p class="user-role">Permanent Employee</p>
        </div>
        <form method="POST" action="{{ route('logout') }}" id="logout-form" style="margin: 0;">
            @csrf
            <button type="submit" class="logout-btn" title="Logout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>
    </div>

</aside>
