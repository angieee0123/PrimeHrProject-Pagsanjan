<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password · PRIME HRIS</title>
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
                    {{-- Required is what the server actually enforces (min:8, the
                         same rule registration and Settings use). The rest are
                         listed as advice rather than requirements because a
                         screen that states a rule the system does not apply is
                         how the two drift. --}}
                    <div class="fp-pw-hint">
                        <strong>Required:</strong> at least 8 characters<br>
                        <strong>Recommended:</strong> mix of uppercase and lowercase letters,
                        numbers and special characters
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

        @php
        $fpCheck = '<svg class="pub-check" width="14" height="14" viewBox="0 0 24 24" fill="#15803d" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.1 14.3-3.6-3.6 1.4-1.4 2.2 2.2 4.9-4.9 1.4 1.4Z"/></svg>';
        @endphp
        <div class="auth-tags">
            <span class="pub-tag">{!! $fpCheck !!} Secure Recovery</span>
            <span class="pub-tag">{!! $fpCheck !!} Encrypted</span>
            <span class="pub-tag">{!! $fpCheck !!} Email Verified</span>
        </div>

    </div>

    <x-public-footer class="auth-footer" />

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

/*
 * Every step below talks to the server. None of them may decide anything.
 *
 * All three used to be answered here in the page, against a code compiled into
 * this file, and the last one announced success without a request going
 * anywhere — so the password never changed. Same rule as the chatbot widgets:
 * this page must never answer from a local string, because a false "Password
 * reset successfully!" is worse than an error message. See
 * PasswordResetController and PasswordResetCodeService for the real rules.
 */

// What survives between steps. The ticket is minted server-side once the code
// is accepted and is the only thing the reset endpoint takes; there is
// deliberately no client-side "verified" flag to flip.
const fpState = { email: '', ticket: '' };

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function postJson(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });

    let data = {};
    try { data = await res.json(); } catch (_) { /* non-JSON error page */ }

    if (res.status === 429) {
        return { ok: false, message: 'Too many attempts. Please wait a minute and try again.' };
    }
    if (res.status === 422 && data.errors) {
        return { ok: false, message: Object.values(data.errors)[0][0] };
    }
    if (!res.ok && !data.message) {
        return { ok: false, message: 'Something went wrong. Please try again.' };
    }
    return { ok: res.ok && data.ok !== false, message: data.message, ticket: data.ticket };
}

// A submit that is in flight must not be submittable again - a second "Send
// Code" would spend another SMTP send, and a second "Reset" races the first.
function busy(step, on, label) {
    const btn = document.querySelector('#step-' + step + ' .auth-submit');
    if (!btn) return;
    if (on) {
        btn.dataset.label = btn.innerHTML;
        btn.disabled = true;
        btn.textContent = label;
    } else {
        btn.disabled = false;
        if (btn.dataset.label) btn.innerHTML = btn.dataset.label;
    }
}

async function sendCode(e) {
    e.preventDefault();
    hideError(1);
    const email = document.getElementById('fp-email').value.trim();
    if (!email) { showError(1, 'Please enter your email address.'); return; }

    busy(1, true, 'Sending…');
    const res = await postJson('{{ route('password.forgot.send') }}', { email });
    busy(1, false);

    if (!res.ok) { showError(1, res.message); return; }

    // The reply is the same whether or not that address has an account, so this
    // screen cannot be used to find out which staff addresses are registered.
    fpState.email = email;
    showSuccess(1, res.message);
    document.getElementById('fp-email-display').innerHTML =
        `<svg class="fp-email-info-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <div><p class="fp-email-info-label">Code sent to:</p><p class="fp-email-info-value">${email}</p></div>`;
    setTimeout(() => goStep(2), 1500);
}

async function verifyCode(e) {
    e.preventDefault();
    hideError(2);
    const code = document.getElementById('fp-code').value.trim();
    if (!code) { showError(2, 'Please enter the verification code.'); return; }

    busy(2, true, 'Verifying…');
    const res = await postJson('{{ route('password.forgot.verify') }}', { email: fpState.email, code });
    busy(2, false);

    if (!res.ok) { showError(2, res.message); return; }

    fpState.ticket = res.ticket;
    goStep(3);
}

async function resendCode() {
    hideError(2);
    const res = await postJson('{{ route('password.forgot.send') }}', { email: fpState.email });
    if (!res.ok) { showError(2, res.message); return; }

    // The server's wording, not "Code resent!" — a request inside the
    // per-address cooldown is deliberately a no-op, and announcing a send that
    // did not happen sets someone watching an inbox for a mail never sent.
    showSuccess(2, res.message);
}

async function resetPassword(e) {
    e.preventDefault();
    hideError(3);
    const pw1 = document.getElementById('fp-pw1').value;
    const pw2 = document.getElementById('fp-pw2').value;
    if (!pw1 || !pw2)    { showError(3, 'Please fill in all password fields.'); return; }
    if (pw1.length < 8)  { showError(3, 'Password must be at least 8 characters long.'); return; }
    if (pw1 !== pw2)     { showError(3, 'Passwords do not match.'); return; }

    busy(3, true, 'Saving…');
    const res = await postJson('{{ route('password.forgot.reset') }}', {
        email: fpState.email,
        ticket: fpState.ticket,
        password: pw1,
        password_confirmation: pw2,
    });
    busy(3, false);

    // Only claim the password changed once the server says it wrote it.
    if (!res.ok) { showError(3, res.message); return; }

    showSuccess(3, res.message);
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
