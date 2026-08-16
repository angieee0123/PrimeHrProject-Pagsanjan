@php
/*
 * Grouped rather than one flat list of 14. Past roughly seven items the eye
 * stops scanning and starts hunting, and the four natural clusters were
 * invisible without headers.
 *
 * Ordering notes:
 *  - Personnel leads ORGANIZATION because it is the daily driver; Departments
 *    sits beside it (employees belong to departments) instead of being stranded
 *    between Deductions and Reports as it was.
 *  - Leave & Travel Calendar is deliberately absent: the floating button on
 *    every admin page already opens it, so a nav row would be a second door
 *    to the same screen.
 */
// Dashboard sits above the groups on its own — a collapsible header wrapping a
// single link is just an extra click.
$dashboardItem = ['id' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('admin.dashboard')];
$aiAssistantItem = ['id' => 'admin.ai-assistant', 'label' => 'AI Assistant', 'icon' => 'ai-assistant', 'route' => route('admin.ai-assistant')];

$navGroups = [
    'Organization' => [
        ['id' => 'admin.personnel',   'label' => 'Personnel',              'icon' => 'personnel',   'route' => route('admin.personnel')],
        ['id' => 'admin.departments', 'label' => 'Departments',            'icon' => 'departments', 'route' => route('admin.departments')],
        ['id' => 'admin.recruitment', 'label' => 'Recruitment',            'icon' => 'recruitment', 'route' => route('admin.recruitment')],
        ['id' => 'admin.training',    'label' => 'Training & Development', 'icon' => 'training',    'route' => route('admin.training')],
        ['id' => 'admin.performance', 'label' => 'Performance Management', 'icon' => 'performance', 'route' => route('admin.performance')],
    ],
    'Time & Absence' => [
        ['id' => 'admin.attendance',   'label' => 'Attendance',            'icon' => 'attendance',    'route' => route('admin.attendance')],
        ['id' => 'admin.attendance.scanner', 'label' => 'Attendance Scanner', 'icon' => 'scanner',    'route' => route('admin.attendance.scanner')],
        ['id' => 'admin.leave',        'label' => 'Leave & Benefits',      'icon' => 'leave',         'route' => route('admin.leave')],
        ['id' => 'admin.travelorder',  'label' => 'Travel Orders',         'icon' => 'travelorder',   'route' => route('admin.travelorder')],
        ['id' => 'admin.passslip',     'label' => 'Pass Slips',            'icon' => 'passslip',      'route' => route('admin.passslip')],
    ],
    'Compensation' => [
        ['id' => 'admin.payroll',    'label' => 'Payroll',    'icon' => 'payroll',    'route' => route('admin.payroll')],
        ['id' => 'admin.deductions', 'label' => 'Deductions', 'icon' => 'deductions', 'route' => route('admin.deductions')],
    ],
    'System' => [
        ['id' => 'admin.reports',  'label' => 'Reports',  'icon' => 'reports',  'route' => route('admin.reports')],
        // Administrators only — WebsiteContentController 403s anyone else, so
        // hiding the row here is tidiness, not the permission.
        ...(auth()->user()?->hasRole('admin')
            ? [['id' => 'admin.website', 'label' => 'Website Content', 'icon' => 'website', 'route' => route('admin.website')]]
            : []),
        ...(auth()->user()?->hasRole('admin')
            ? [['id' => 'admin.audit', 'label' => 'Audit Trail', 'icon' => 'audit', 'route' => route('admin.audit')]]
            : []),
        ['id' => 'admin.settings', 'label' => 'Settings', 'icon' => 'settings', 'route' => route('admin.settings')],
    ],
];
$currentRoute = Route::currentRouteName();

/*
 * Which sections start open is resolved here, not left to app.js alone.
 *
 * app.js kept the open list in localStorage, which PHP cannot see, so every
 * response went out with all four sections expanded and the script snapped
 * them shut on DOMContentLoaded — one painted frame of a fully open rail on
 * every navigation. It now mirrors the list into a plain `openNavGroups`
 * cookie so the collapsed state ships in the initial HTML and nothing has to
 * be corrected after paint.
 *
 * The rules below deliberately mirror initNavGroups(): the group holding the
 * current page is always open, the first group is the fallback, and the cap is
 * NAV_MAX_OPEN. Height-based eviction stays client-side — it needs measurement.
 */
$groupSlugs = collect($navGroups)->keys()->map(fn ($l) => Str::slug($l))->all();

$currentGroup = null;
foreach ($navGroups as $groupLabel => $groupItems) {
    if (collect($groupItems)->contains(fn ($i) => $i['id'] === $currentRoute)) {
        $currentGroup = Str::slug($groupLabel);
        break;
    }
}

// Mirrors openNavGroups below: written by app.js on toggle, read here so a
// collapsed rail ships correctly in the initial HTML instead of flashing
// expanded for a frame on every full-page navigation.
$sidebarCollapsed = request()->cookie('sidebarCollapsed') === '1';

// Oldest first — the same order app.js writes, so eviction stays FIFO.
$storedGroups = json_decode(request()->cookie('openNavGroups') ?? '', true);
$openGroups = array_values(array_intersect(
    is_array($storedGroups) ? $storedGroups : [],
    $groupSlugs
));

if ($currentGroup && !in_array($currentGroup, $openGroups, true)) {
    $openGroups[] = $currentGroup;
}
if (!$openGroups) {
    $openGroups = [$groupSlugs[0]];
}

// Drop the oldest, but never the section holding the page being viewed.
while (count($openGroups) > 3) {
    $oldest = collect($openGroups)->search(fn ($s) => $s !== $currentGroup);
    if ($oldest === false) break;
    array_splice($openGroups, $oldest, 1);
}
@endphp

<aside class="sidebar @if($sidebarCollapsed) collapsed @endif" id="sidebar">

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
        <button class="toggle-btn" id="toggle-btn" aria-label="Toggle sidebar">{{ $sidebarCollapsed ? '›' : '‹' }}</button>
    </div>

    <nav class="sidebar-nav" id="sidebar-nav">
        {{-- Ungrouped: always visible, never collapses. --}}
        <a href="{{ $dashboardItem['route'] }}"
           class="nav-item {{ $currentRoute === $dashboardItem['id'] ? 'active' : '' }}"
           title="{{ $dashboardItem['label'] }}">
            <span class="nav-icon"><x-nav-icon :name="$dashboardItem['icon']" /></span>
            <span class="nav-label">{{ $dashboardItem['label'] }}</span>
            @if($currentRoute === $dashboardItem['id'])
            <span class="nav-active-bar"></span>
            @endif
        </a>

        <a href="{{ $aiAssistantItem['route'] }}"
           class="nav-item {{ $currentRoute === $aiAssistantItem['id'] ? 'active' : '' }}"
           title="{{ $aiAssistantItem['label'] }}">
            <span class="nav-icon"><x-nav-icon :name="$aiAssistantItem['icon']" /></span>
            <span class="nav-label">{{ $aiAssistantItem['label'] }}</span>
            @if($currentRoute === $aiAssistantItem['id'])
            <span class="nav-active-bar"></span>
            @endif
        </a>

        @foreach($navGroups as $groupLabel => $items)
        @php
            $slug = Str::slug($groupLabel);
            // The group holding the current page starts open regardless of the
            // stored preference, so you can always see where you are.
            $holdsCurrent = $slug === $currentGroup;
            $isOpen = in_array($slug, $openGroups, true);
        @endphp
        <div class="nav-group @if(!$isOpen) is-collapsed @endif" data-nav-group="{{ $slug }}" @if($holdsCurrent) data-holds-current @endif>
            <button type="button" class="nav-section-toggle"
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="nav-group-{{ $slug }}">
                <span class="nav-section-label">{{ strtoupper($groupLabel) }}</span>
                {{-- Only shown while the group is shut, so a row of closed
                     sections still tells you what is behind each one. --}}
                <span class="nav-section-count">{{ count($items) }}</span>
                <svg class="nav-section-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
            <div class="nav-group-items" id="nav-group-{{ $slug }}">
                @foreach($items as $item)
                <a href="{{ $item['route'] }}"
                   class="nav-item {{ $currentRoute === $item['id'] ? 'active' : '' }}"
                   title="{{ $item['label'] }}">
                    <span class="nav-icon"><x-nav-icon :name="$item['icon']" /></span>
                    <span class="nav-label">{{ $item['label'] }}</span>
                    @if($currentRoute === $item['id'])
                    <span class="nav-active-bar"></span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Divider separating the nav items from the logout --}}
        <div class="nav-divider"></div>

        {{-- Logout — last item in the nav, styled like the others --}}
        <button type="button" class="nav-item nav-logout" onclick="openLogoutModal()" title="Logout">
            <span class="nav-icon"><x-nav-icon name="logout" /></span>
            <span class="nav-label">Log Out</span>
        </button>
    </nav>

    <div class="sidebar-footer @if($sidebarCollapsed) collapsed-footer @endif" id="sidebar-footer">
        {{-- Sizing/shape now live in .user-avatar rather than inline styles, so
             this rail and the employee one stay in step. --}}
        <div class="user-avatar-wrap">
            @php
                $avatarEmployee = $authEmployee ?? (Auth::check() ? Auth::user()->employee : null);
                $avatarInitials = $avatarEmployee
                    ? strtoupper(substr($avatarEmployee->first_name ?? 'A', 0, 1) . substr($avatarEmployee->last_name ?? 'D', 0, 1))
                    : 'AD';
            @endphp
            @if($avatarEmployee?->photo)
                {{-- If the stored file has gone missing, hand off to the initials
                     instead of leaving a broken-image icon. --}}
                <img src="{{ $avatarEmployee->photo }}" alt=""
                     class="user-avatar user-avatar-img"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="user-avatar" style="display:none">{{ $avatarInitials }}</div>
            @else
                <div class="user-avatar">{{ $avatarInitials }}</div>
            @endif
            <span class="user-status-dot"></span>
        </div>
        <div class="user-info" id="user-info">
            <p class="user-name">{{ Auth::check() ? (Auth::user()->employee->first_name ?? 'Admin') . ' ' . (Auth::user()->employee->last_name ?? 'User') : 'Admin User' }}</p>
            <p class="user-role">{{ ($authRole ?? null) === 'Admin' ? 'Administrator' : (($authRole ?? null) === 'Hr' ? 'HR Staff' : ($authRole ?? 'HR Staff')) }}</p>
        </div>
        @if(Auth::check() && count(Auth::user()->dashboardRoutes()) > 1)
            <a href="{{ route('select-role') }}" class="switch-role-btn" title="Switch Role">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            </a>
        @endif
    </div>
</aside>

@include('partials.logoutConfirmModal', ['firstName' => Auth::check() && Auth::user()->employee ? (Auth::user()->employee->first_name ?? 'Admin') : 'Admin'])
