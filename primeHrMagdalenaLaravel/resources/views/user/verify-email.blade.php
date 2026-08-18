<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In · PRIME HRIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- The active palette. Organisation-wide: the theme lives on
         system_ai_settings and there is no per-user theme, so this is
         the same block for every viewer. Resolved in one place so the
         layouts cannot drift on how it is assembled. --}}
    <style>{!! \App\Services\SystemTheme::activeCss() !!}</style>
</head>
<body>

<div class="auth-root">

    <x-public-govbar />

    {{-- Navbar --}}
    <nav class="pub-nav">
        <x-public-brand />
        <a href="{{ url('/') }}" class="auth-nav-back">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M19 12H5M12 5l-7 7 7 7"/>
            </svg>
            Back to Portal
        </a>
    </nav>

    {{-- Body --}}
    <div class="auth-body">
        <div class="auth-page-head">
            <span class="pub-eyebrow">EMAIL VERIFICATION</span>
            <h1 class="auth-page-title">Verify Your Email</h1>
            <p class="auth-page-sub">Check your inbox for a verification link</p>
        </div>

        <div class="auth-card">
            @if (session('resent'))
            <div class="fp-success" style="margin-bottom:18px">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                A fresh verification link has been sent to your email address.
            </div>
            @endif

            <div style="background:#f7f6ff;border-radius:12px;padding:18px 20px;margin-bottom:20px;display:flex;align-items:flex-start;gap:14px">
                <svg width="22" height="22" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <div>
                    <p style="font-size:13.5px;font-weight:600;color:#0b044d;margin:0 0 4px">Check your email</p>
                    <p style="font-size:13px;color:#6b6a8a;margin:0;line-height:1.6">Before proceeding, please check your email for a verification link. If you did not receive the email, click the button below to request another.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="pub-btn-primary auth-submit">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
                    Resend Verification Email
                </button>
            </form>

            <div class="auth-card-footer">
                <p class="auth-switch">
                    Wrong account?
                    <a href="{{ route('logout') }}" class="auth-switch-btn"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
                </p>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
            </div>
        </div>
    </div>

    <x-public-footer class="auth-footer" />

</div>

<script>
function togglePassword() {
    const input = document.getElementById('password-input');
    const icon  = document.getElementById('eye-icon');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}
</script>

</body>
</html>
