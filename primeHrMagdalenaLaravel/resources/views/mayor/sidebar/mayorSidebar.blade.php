@php
$navItems = [
    ['id' => 'mayor.dashboard',    'label' => 'Dashboard',           'route' => route('mayor.dashboard')],
    ['id' => 'mayor.personnel',    'label' => 'Personnel Directory', 'route' => route('mayor.personnel')],
    ['id' => 'mayor.leave',        'label' => 'Leave Applications',  'route' => route('mayor.leave')],
    ['id' => 'mayor.travelorder',  'label' => 'Travel Orders',       'route' => route('mayor.travelorder')],
    ['id' => 'mayor.passslip',     'label' => 'Pass Slips',          'route' => route('mayor.passslip')],
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
           class="nav-item {{ $currentRoute === $item['id'] ? 'active' : '' }}"
           title="{{ $item['label'] }}">
            <span class="nav-icon">
                @if($item['id'] === 'mayor.dashboard')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                @elseif($item['id'] === 'mayor.personnel')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                @elseif($item['id'] === 'mayor.leave')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                @elseif($item['id'] === 'mayor.travelorder')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                @elseif($item['id'] === 'mayor.passslip')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                @endif
            </span>
            <span class="nav-label">{{ $item['label'] }}</span>
            @if($currentRoute === $item['id'])
            <span class="nav-active-bar"></span>
            @endif
        </a>
        @endforeach
    </nav>

    @php
        $mayorEmployee = Auth::check() ? Auth::user()->employee : null;
        $mayorName = $mayorEmployee ? trim($mayorEmployee->first_name . ' ' . $mayorEmployee->last_name) : 'Mayor';
        $mayorInitials = $mayorEmployee ? strtoupper(substr($mayorEmployee->first_name, 0, 1) . substr($mayorEmployee->last_name, 0, 1)) : 'MY';
    @endphp
    <div class="sidebar-footer" id="sidebar-footer">
        <div class="user-avatar-wrap">
            @if($mayorEmployee && $mayorEmployee->photo)
                <img src="{{ $mayorEmployee->photo }}" class="user-avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #ecebf6;">
            @else
                <div class="user-avatar" style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#0b044d; color:white; font-weight:600; font-size:13px; border:2px solid #ecebf6;">
                    {{ $mayorInitials }}
                </div>
            @endif
            <span class="user-status-dot"></span>
        </div>
        <div class="user-info" id="user-info">
            <p class="user-name">{{ $mayorName }}</p>
            <p class="user-role">Municipal Mayor</p>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="logout-btn" title="Logout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
        </form>
    </div>
</aside>
