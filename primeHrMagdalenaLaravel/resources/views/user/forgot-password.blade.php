<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password · PRIME HRIS</title>
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
        <a href="{{ url('/') }}" class="auth-nav-back">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M19 12H5M12 5l-7 7 7 7"/>
            </svg>
            Back to Portal
        </a>
    </nav>

    {{-- Body --}}
    <div class="auth-body">

        {{-- Page Head --}}
        <div class="auth-page-head">
            <span class="pub-eyebrow">EMPLOYEE PORTAL · PASSWORD RECOVERY</span>
            <h1 class="auth-page-title" id="step-title">Reset your password</h1>
            <p class="auth-page-sub" id="step-sub">Enter your email address and we'll send you a verification code.</p>
        </div>

        {{-- Step Progress --}}
        <div class="fp-steps">
            @foreach([1,2,3] as $s)
            <div class="fp-step-wrap">
                <div class="fp-step-circle {{ $s === 1 ? 'active' : '' }}" id="step-circle-{{ $s }}">
                    <span id="step-num-{{ $s }}">{{ $s }}</span>
                </div>
                @if($s < 3)
                <div class="fp-step-line" id="step-line-{{ $s }}"></div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="auth-card">

            {{-- Step 1: Email --}}
            <div id="step-1">
                <form class="auth-form" onsubmit="sendCode(event)">
                    <div class="auth-field">
                        <label>Email Address</label>
                        <div style="position:relative">
                            <input type="email" id="fp-email" placeholder="e.g. juan.cruz@pagsanjan.gov.ph"
                                   style="padding-left:40px" required>
                            <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div id="fp-error-1" class="auth-error" style="display:none"></div>
                    <div id="fp-success-1" class="fp-success" style="display:none"></div>
                    <button type="submit" class="pub-hr-btn auth-submit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                        </svg>
                        Send Verification Code
                    </button>
                    <div class="auth-card-footer">
                        <p class="auth-switch">
                            Remember your password?
                            <a href="{{ route('login') }}" class="auth-switch-btn">Back to Sign In</a>
                        </p>
                    </div>
                </form>
            </div>

            {{-- Step 2: Verify Code --}}
            <div id="step-2" style="display:none">
                <form class="auth-form" onsubmit="verifyCode(event)">
                    <div class="fp-email-info" id="fp-email-display"></div>
                    <div class="auth-field">
                        <label>Verification Code</label>
                        <input type="text" id="fp-code" placeholder="Enter 6-digit code"
                               maxlength="6" inputmode="numeric"
                               style="letter-spacing:4px;font-size:18px;font-weight:700;text-align:center"
                               oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)" required>
                    </div>
                    <div id="fp-error-2" class="auth-error" style="display:none"></div>
                    <div id="fp-success-2" class="fp-success" style="display:none"></div>
                    <button type="submit" class="pub-hr-btn auth-submit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Verify Code
                    </button>
                    <div class="auth-card-footer">
                        <p class="auth-switch">
                            Didn't receive the code?
                            <button type="button" class="auth-switch-btn" onclick="resendCode()">Resend Code</button>
                        </p>
                        <p class="auth-switch">
                            <button type="button" class="auth-switch-btn" onclick="goStep(1)">Change Email Address</button>
                        </p>
                    </div>
                </form>
            </div>

            {{-- Step 3: New Password --}}
            <div id="step-3" style="display:none">
                <form class="auth-form" onsubmit="resetPassword(event)">
                    <div class="auth-field">
                        <label>New Password</label>
                        <div class="auth-pw-wrap">
                            <input type="password" id="fp-pw1" placeholder="Enter new password" required>
                            <button type="button" class="auth-eye" onclick="togglePw('fp-pw1','fp-eye1')">
                                <svg id="fp-eye1" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label>Confirm New Password</label>
                        <div class="auth-pw-wrap">
                            <input type="password" id="fp-pw2" placeholder="Re-enter new password" required>
                            <button type="button" class="auth-eye" onclick="togglePw('fp-pw2','fp-eye2')">
                                <svg id="fp-eye2" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="fp-pw-hint">
                        <strong>Password Requirements:</strong><br>
                        • At least 8 characters long<br>
                        • Mix of uppercase and lowercase letters<br>
                        • Include numbers and special characters
                    </div>
                    <div id="fp-error-3" class="auth-error" style="display:none"></div>
                    <div id="fp-success-3" class="fp-success" style="display:none"></div>
                    <button type="submit" class="pub-hr-btn auth-submit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Reset Password
                    </button>
                </form>
            </div>

        </div>

        <div class="auth-tags">
            <span class="pub-tag">✓ Secure Recovery</span>
            <span class="pub-tag">✓ Encrypted</span>
            <span class="pub-tag">✓ Email Verified</span>
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

<script>
const stepTitles = ['Reset your password', 'Verify your email', 'Create new password'];
const stepSubs   = [
    "Enter your email address and we'll send you a verification code.",
    'Enter the 6-digit code we sent to your email address.',
    'Choose a strong password to secure your account.'
];

function goStep(n) {
    [1,2,3].forEach(s => {
        document.getElementById('step-' + s).style.display = s === n ? 'block' : 'none';
        const circle = document.getElementById('step-circle-' + s);
        circle.classList.toggle('active', s <= n);
        circle.classList.toggle('done',   s < n);
        if (s < 3) document.getElementById('step-line-' + s).classList.toggle('done', s < n);
    });
    document.getElementById('step-title').textContent = stepTitles[n - 1];
    document.getElementById('step-sub').textContent   = stepSubs[n - 1];
}

function showError(step, msg) {
    const el = document.getElementById('fp-error-' + step);
    el.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ${msg}`;
    el.style.display = 'flex';
}
function hideError(step) { document.getElementById('fp-error-' + step).style.display = 'none'; }

function showSuccess(step, msg) {
    const el = document.getElementById('fp-success-' + step);
    el.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> ${msg}`;
    el.style.display = 'flex';
    setTimeout(() => { el.style.display = 'none'; }, 2000);
}

function sendCode(e) {
    e.preventDefault();
    hideError(1);
    const email = document.getElementById('fp-email').value.trim();
    if (!email) { showError(1, 'Please enter your email address.'); return; }
    showSuccess(1, 'Verification code sent to your email!');
    document.getElementById('fp-email-display').innerHTML =
        `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <div><p style="font-size:12.5px;color:#0b044d;font-weight:600;margin-bottom:4px">Code sent to:</p><p style="font-size:13px;color:#6b6a8a;margin:0">${email}</p></div>`;
    setTimeout(() => goStep(2), 1500);
}

function verifyCode(e) {
    e.preventDefault();
    hideError(2);
    const code = document.getElementById('fp-code').value.trim();
    if (!code) { showError(2, 'Please enter the verification code.'); return; }
    if (code !== '123456') { showError(2, 'Invalid verification code. Please try again.'); return; }
    goStep(3);
}

function resendCode() {
    showSuccess(2, 'Verification code resent to your email!');
}

function resetPassword(e) {
    e.preventDefault();
    hideError(3);
    const pw1 = document.getElementById('fp-pw1').value;
    const pw2 = document.getElementById('fp-pw2').value;
    if (!pw1 || !pw2)    { showError(3, 'Please fill in all password fields.'); return; }
    if (pw1.length < 8)  { showError(3, 'Password must be at least 8 characters long.'); return; }
    if (pw1 !== pw2)     { showError(3, 'Passwords do not match.'); return; }
    showSuccess(3, 'Password reset successfully!');
    setTimeout(() => { window.location.href = '{{ route("login") }}'; }, 2000);
}

function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}
</script>

</body>
</html>
