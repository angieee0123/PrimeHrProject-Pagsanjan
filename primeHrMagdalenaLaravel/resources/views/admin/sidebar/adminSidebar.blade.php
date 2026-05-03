<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <div class="logo-mark">
                <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
            </div>
            <div class="logo-text-wrap">
                <span class="logo-text">PRIME HRIS</span>
                <span class="logo-sub">Pagsanjan, Laguna</span>
            </div>
        </div>
        <button class="toggle-btn" onclick="toggleSidebar()">
            <svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>
    </div>

    <p class="nav-section-label">NAVIGATION</p>

    <nav class="sidebar-nav">
        @php
        $navItems = [
            ['label' => 'Dashboard', 'id' => 'dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Recruitment', 'id' => 'recruitment', 'route' => 'admin.recruitment'],
            ['label' => 'Personnel', 'id' => 'personnel', 'route' => 'admin.personnel'],
            ['label' => 'Training & Development', 'id' => 'training', 'route' => 'admin.training'],
            ['label' => 'Performance Management', 'id' => 'performance', 'route' => 'admin.performance'],
            ['label' => 'Attendance', 'id' => 'attendance', 'route' => 'admin.attendance'],
            ['label' => 'Leave & Benefits', 'id' => 'leave', 'route' => 'admin.leave'],
            ['label' => 'Payroll', 'id' => 'payroll', 'route' => 'admin.payroll'],
            ['label' => 'Departments', 'id' => 'departments', 'route' => 'admin.departments'],
            ['label' => 'Reports', 'id' => 'reports', 'route' => 'admin.reports'],
            ['label' => 'Settings', 'id' => 'settings', 'route' => '#'],
        ];
        @endphp

        @foreach($navItems as $item)
        <a href="{{ $item['route'] === '#' ? '#' : route($item['route']) }}"
           class="nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}"
           title="{{ $item['label'] }}">
            <span class="nav-icon">
                @if($item['id'] === 'dashboard')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                @elseif($item['id'] === 'recruitment')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                @elseif($item['id'] === 'personnel')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                @elseif($item['id'] === 'training')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                @elseif($item['id'] === 'performance')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                @elseif($item['id'] === 'attendance')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                @elseif($item['id'] === 'leave')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                @elseif($item['id'] === 'payroll')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
                @elseif($item['id'] === 'departments')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                @elseif($item['id'] === 'reports')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                @elseif($item['id'] === 'settings')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                @endif
            </span>
            <span class="nav-label">{{ $item['label'] }}</span>
            @if(request()->routeIs($item['route']))
            <span class="nav-active-bar"></span>
            @endif
        </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="user-avatar-wrap">
            <div class="user-avatar">AD</div>
            <span class="user-status-dot"></span>
        </div>
        <div class="user-info">
            <p class="user-name">Admin User</p>
            <p class="user-role">HR Staff</p>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn" title="Logout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
        </form>
    </div>
</aside>

<style>
.sidebar.collapsed { width: 72px; }
.sidebar.collapsed .logo-text-wrap { display: none; }
.sidebar.collapsed .nav-section-label { display: none; }
.sidebar.collapsed .nav-label { display: none; }
.sidebar.collapsed .user-info { display: none; }
.sidebar.collapsed .logout-btn { width: 40px; }
.sidebar.collapsed .toggle-icon { transform: rotate(180deg); }
.toggle-icon { transition: transform 0.3s; }
</style>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('collapsed');

    // Save state to localStorage
    if (sidebar.classList.contains('collapsed')) {
        localStorage.setItem('sidebarCollapsed', 'true');
    } else {
        localStorage.setItem('sidebarCollapsed', 'false');
    }
}

// Mobile menu functionality
const mobileBtn = document.getElementById('mobile-menu-btn');
const overlay = document.getElementById('mobile-overlay');
const sidebar = document.querySelector('.sidebar');

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

// Restore sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        document.querySelector('.sidebar').classList.add('collapsed');
    }
});
</script>
