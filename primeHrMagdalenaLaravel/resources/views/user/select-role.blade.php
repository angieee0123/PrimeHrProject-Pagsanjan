<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role · PRIME HRIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        <div class="auth-card">
            <form method="POST" action="{{ route('select-role.post') }}" class="auth-form">
                @csrf
                @php
                    $roleLabels = [
                        'admin' => ['Administrator', 'Full system administration access'],
                        'hr'    => ['HR Staff', 'Manage employee records and HR operations'],
                        'mayor' => ['Municipal Mayor', 'Read-only oversight dashboard'],
                        'employee' => ['Employee', 'Access your personal employee records'],
                    ];
                @endphp
                @foreach($options as $option)
                    @php [$label, $desc] = $roleLabels[$option['role']] ?? [ucfirst($option['role']), '']; @endphp
                    <button type="submit" name="role" value="{{ $option['role'] }}" class="pub-hr-btn auth-submit" style="display:flex;flex-direction:column;align-items:flex-start;gap:2px;margin-bottom:12px;">
                        <span style="font-weight:700;">Continue as {{ $label }}</span>
                        @if($desc)
                            <span style="font-weight:400;font-size:12px;opacity:.85;">{{ $desc }}</span>
                        @endif
                    </button>
                @endforeach
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
