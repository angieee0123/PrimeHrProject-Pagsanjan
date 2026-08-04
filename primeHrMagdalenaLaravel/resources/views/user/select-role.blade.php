<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role · PRIME HRIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $__s = \App\Models\SystemAiSetting::current();
        $activeTheme = $__s->theme ?? 'default';
    @endphp
    <style>{!! \App\Services\SystemTheme::toCss($activeTheme, $__s->custom_theme_primary, $__s->theme_secondary, $__s->theme_accent, $__s->theme_muted) !!}</style>
</head>
<body>

<div class="auth-root">

    {{-- Gov Bar --}}
    <div class="pub-govbar">
        <span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:4px">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
            </svg>
            Republic of the Philippines &nbsp;·&nbsp; Province of Laguna
        </span>
        <span>Official Website of the Municipal Government of Pagsanjan</span>
    </div>

    {{-- Navbar --}}
    <nav class="pub-nav">
        <div class="pub-logo">
            <div class="pub-logo-seal">
                <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo"
                     onerror="this.style.display='none'"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover">
            </div>
            <div>
                <span class="pub-logo-name">Pagsanjan, Laguna</span>
                <span class="pub-logo-sub">Municipal Government</span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="auth-nav-back" style="background:none;border:none;cursor:pointer;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                Sign out
            </button>
        </form>
    </nav>

    {{-- Body --}}
    <div class="auth-body">

        <div class="auth-page-head">
            <span class="pub-eyebrow">EMPLOYEE PORTAL · PRIME HRIS</span>
            <h1 class="auth-page-title">Choose how you want to continue</h1>
            <p class="auth-page-sub">Your account has more than one role. Select the dashboard you want to access.</p>
        </div>

        <div class="auth-card role-select-card">
            <form method="POST" action="{{ route('select-role.post') }}">
                @csrf
                @php
                    $roleMeta = [
                        'admin' => [
                            'label' => 'Administrator', 'desc' => 'Full system administration access', 'accent' => 'var(--maroon)',
                            'icon' => '<path d="M12 21s7-3.5 7-10V5l-7-3-7 3v6c0 6.5 7 10 7 10z"/><path d="m9 12 2 2 4-4"/>',
                        ],
                        'hr' => [
                            'label' => 'HR Staff', 'desc' => 'Manage employee records and HR operations', 'accent' => 'var(--navy)',
                            'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                        ],
                        'mayor' => [
                            'label' => 'Municipal Mayor', 'desc' => 'Read-only oversight dashboard', 'accent' => 'var(--gold)',
                            'icon' => '<line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 21 7 3 7"/>',
                        ],
                        'employee' => [
                            'label' => 'Employee', 'desc' => 'Access your personal employee records', 'accent' => '#15803d',
                            'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.5 3.6-8 8-8s8 3.5 8 8"/>',
                        ],
                    ];
                    $fallback = [
                        'label' => null, 'desc' => '', 'accent' => 'var(--navy)',
                        'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.5 3.6-8 8-8s8 3.5 8 8"/>',
                    ];
                @endphp
                <div class="role-grid">
                    @foreach($options as $option)
                        @php
                            $meta = $roleMeta[$option['role']] ?? $fallback;
                            $label = $meta['label'] ?? ucfirst($option['role']);
                        @endphp
                        <button type="submit" name="role" value="{{ $option['role'] }}" class="role-card" style="--role-accent: {{ $meta['accent'] }};">
                            <span class="role-card-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    {!! $meta['icon'] !!}
                                </svg>
                            </span>
                            <span class="role-card-body">
                                <span class="role-card-title">{{ $label }}</span>
                                @if($meta['desc'])
                                    <span class="role-card-desc">{{ $meta['desc'] }}</span>
                                @endif
                            </span>
                            <svg class="role-card-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </button>
                    @endforeach
                </div>
            </form>
        </div>

    </div>

    {{-- Footer --}}
    <footer class="pub-footer auth-footer">
        <div class="pub-footer-inner">
            <div class="pub-footer-brand">
                <div class="pub-logo-seal sm">
                    <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo"
                         onerror="this.style.display='none'"
                         style="width:28px;height:28px;border-radius:50%;object-fit:cover">
                </div>
                <div>
                    <span class="pub-footer-name">Municipal Government of Pagsanjan</span>
                    <span class="pub-footer-sub">Province of Laguna · Republic of the Philippines</span>
                </div>
            </div>
            <p class="pub-footer-copy">© 2025 Municipal Government of Pagsanjan, Laguna. All rights reserved.</p>
        </div>
    </footer>

</div>

</body>
</html>
