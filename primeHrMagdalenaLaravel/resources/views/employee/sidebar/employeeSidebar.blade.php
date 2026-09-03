@php
/*
 * Grouped to match the admin rail. No accordion here — ten items across three
 * short sections stay scannable without one, and collapsing would only add a
 * click on a nav this size.
 */
$isPermanent = $isPermanent ?? false;

// Dashboard sits above the sections on its own.
$dashboardItem = ['id' => 'employee.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('employee.dashboard')];
$aiAssistantItem = ['id' => 'employee.ai-assistant', 'label' => 'AI Assistant', 'icon' => 'ai-assistant', 'route' => route('employee.ai-assistant')];

$timeItems = [
    ['id' => 'employee.attendance', 'label' => 'Attendance', 'icon' => 'attendance', 'route' => route('employee.attendance')],
];
if ($isPermanent) {
    $timeItems[] = ['id' => 'employee.leave', 'label' => 'Leave & Benefits', 'icon' => 'leave', 'route' => route('employee.leave')];
}
$timeItems = [...$timeItems,
    ['id' => 'employee.travelorder', 'label' => 'Travel Orders', 'icon' => 'travelorder', 'route' => route('employee.travelorder')],
    ['id' => 'employee.passslip',    'label' => 'Pass Slips',    'icon' => 'passslip',    'route' => route('employee.passslip')],
];

$navGroups = [
    'Time & Absence' => $timeItems,
    'Pay & Records'  => [
        ['id' => 'employee.payslip',     'label' => 'Payslip',     'icon' => 'payslip',     'route' => route('employee.payslip')],
        ['id' => 'employee.training',    'label' => 'Training',    'icon' => 'training',    'route' => route('employee.training')],
        ['id' => 'employee.performance', 'label' => 'Performance', 'icon' => 'performance', 'route' => route('employee.performance')],
    ],
    'Account' => [
        ['id' => 'employee.profile',  'label' => 'Profile',  'icon' => 'profile',  'route' => route('employee.profile')],
        ['id' => 'employee.settings', 'label' => 'Settings', 'icon' => 'settings', 'route' => route('employee.settings')],
    ],
];

$currentRoute = Route::currentRouteName() ?? '';

/*
 * Exact route name, or a child of it — so employee.attendance.detailed and
 * employee.payslip.details still light up their parent.
 *
 * The old test was str_contains($currentRoute, 'leave'), which also matched
 * employee.leaveCalendar and lit "Leave & Benefits" on the calendar page.
 * Requiring the trailing dot for children keeps sibling names apart.
 */
$isActive = fn (string $id) => $currentRoute === $id || Str::startsWith($currentRoute, $id . '.');
@endphp

<aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <div class="logo">
            <div class="logo-mark">
                <img src="{{ \App\Services\SiteContentService::logoUrl() }}" alt="Pagsanjan Logo"
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

    <nav class="sidebar-nav" id="sidebar-nav">
        {{-- Ungrouped: the landing page, always first. --}}
        <a href="{{ $dashboardItem['route'] }}"
           class="nav-item {{ $isActive($dashboardItem['id']) ? 'active' : '' }}"
           title="{{ $dashboardItem['label'] }}">
            <span class="nav-icon"><x-nav-icon :name="$dashboardItem['icon']" /></span>
            <span class="nav-label">{{ $dashboardItem['label'] }}</span>
            @if($isActive($dashboardItem['id']))
            <span class="nav-active-bar"></span>
            @endif
        </a>

        <a href="{{ $aiAssistantItem['route'] }}"
           class="nav-item {{ $isActive($aiAssistantItem['id']) ? 'active' : '' }}"
           title="{{ $aiAssistantItem['label'] }}">
            <span class="nav-icon"><x-nav-icon :name="$aiAssistantItem['icon']" /></span>
            <span class="nav-label">{{ $aiAssistantItem['label'] }}</span>
            @if($isActive($aiAssistantItem['id']))
            <span class="nav-active-bar"></span>
            @endif
        </a>

        {{-- Same position as the admin and mayor rails: somebody holding two
             roles should not have to hunt for it again after switching. --}}
        @include('partials.navNotificationRow', ['area' => 'employee'])

        @foreach($navGroups as $groupLabel => $items)
        <p class="nav-section-label">{{ strtoupper($groupLabel) }}</p>
        @foreach($items as $item)
        <a href="{{ $item['route'] }}"
           class="nav-item {{ $isActive($item['id']) ? 'active' : '' }}"
           title="{{ $item['label'] }}">
            <span class="nav-icon"><x-nav-icon :name="$item['icon']" /></span>
            <span class="nav-label">{{ $item['label'] }}</span>
            @if($isActive($item['id']))
            <span class="nav-active-bar"></span>
            @endif
        </a>
        @endforeach
        @endforeach

        {{-- Divider separating the nav items from the logout --}}
        <div class="nav-divider"></div>

        {{-- Logout — last item in the nav, styled like the others --}}
        <button type="button" class="nav-item nav-logout" onclick="openLogoutModal()" title="Logout">
            <span class="nav-icon"><x-nav-icon name="logout" /></span>
            <span class="nav-label">Log Out</span>
        </button>
    </nav>

    <div class="sidebar-footer" id="sidebar-footer">
        {{-- This only ever rendered initials, even though the employee's photo is
             on the record and the admin rail already showed it. If the file is
             missing the img hands off to the initials rather than leaving a
             broken-image icon. --}}
        <div class="user-avatar-wrap">
            @php $avatarPhoto = ($authEmployee ?? null)?->photo; @endphp
            @if($avatarPhoto)
                <img src="{{ $avatarPhoto }}" alt=""
                     class="user-avatar user-avatar-img"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="user-avatar" style="display:none">{{ $authInitials ?? 'PE' }}</div>
            @else
                <div class="user-avatar">{{ $authInitials ?? 'PE' }}</div>
            @endif
            <span class="user-status-dot"></span>
        </div>
        <div class="user-info" id="user-info">
            <p class="user-name">{{ $authFullName ?? 'Permanent Employee' }}</p>
            <p class="user-role">{{ $authRole ?? 'Permanent Employee' }}</p>
        </div>
        @if(Auth::check() && count(Auth::user()->dashboardRoutes()) > 1)
            <a href="{{ route('select-role') }}" class="switch-role-btn" title="Switch Role">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            </a>
        @endif
    </div>

</aside>

@include('partials.logoutConfirmModal', ['firstName' => isset($authFullName) ? explode(' ', trim($authFullName))[0] : 'there'])
