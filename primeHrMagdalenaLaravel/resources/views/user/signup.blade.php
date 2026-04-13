<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account · PRIME HRIS</title>
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

    {{-- Success State --}}
    @if(session('signup_success'))
    <div class="auth-body">
        <div class="auth-card" style="text-align:center;border-top:3px solid #d9bb00;max-width:460px">
            <div style="width:60px;height:60px;background:#fefce8;border:1.5px solid #d9bb00;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <h2 style="font-size:20px;font-weight:800;color:#0b044d;margin-bottom:8px">Request Submitted!</h2>
            <p style="font-size:13.5px;color:#6b6a8a;line-height:1.75;margin-bottom:6px">
                Your account request has been sent to the <strong>HR Administrator</strong> for review.
            </p>
            <p style="font-size:13px;color:#9999bb;line-height:1.7;margin-bottom:24px">
                You will be notified once your account is approved. This usually takes <strong>1–2 business days</strong>.
            </p>
            <div style="background:#f7f6ff;border:1px solid #eceaf8;border-radius:10px;padding:14px 18px;margin-bottom:24px;text-align:left">
                <p style="font-size:11px;font-weight:700;color:#9999bb;text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">Submitted Details</p>
                <div style="display:flex;justify-content:space-between;font-size:13px;padding-bottom:8px;margin-bottom:8px;border-bottom:1px solid #f0effe">
                    <span style="color:#9999bb;font-weight:500">Name</span>
                    <strong style="color:#0b044d">{{ session('signup_name') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;padding-bottom:8px;margin-bottom:8px;border-bottom:1px solid #f0effe">
                    <span style="color:#9999bb;font-weight:500">Email</span>
                    <strong style="color:#0b044d">{{ session('signup_email') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:#9999bb;font-weight:500">Type</span>
                    <strong style="color:#0b044d">{{ session('signup_type') }}</strong>
                </div>
            </div>
            <a href="{{ route('login') }}" class="pub-hr-btn auth-submit" style="justify-content:center;text-decoration:none">
                Back to Sign In
            </a>
        </div>
    </div>

    {{-- Normal Signup Form --}}
    @else
    <div class="auth-body">

        <div class="auth-page-head">
            <span class="pub-eyebrow">EMPLOYEE PORTAL · PRIME HRIS</span>
            <h1 class="auth-page-title">Create an Account</h1>
            <p class="auth-page-sub">Fill in your details. Your request will be reviewed by the HR Administrator.</p>
        </div>

        <div class="auth-card auth-card-wide">
            <form class="auth-form" method="POST" action="{{ route('signup.post') }}">
                @csrf

                {{-- Name row --}}
                <div class="auth-row-2">
                    <div class="auth-field">
                        <label>First Name</label>
                        <input type="text" name="first_name" placeholder="Maria" value="{{ old('first_name') }}" required>
                        @error('first_name')<span style="font-size:11.5px;color:#8e1e18">{{ $message }}</span>@enderror
                    </div>
                    <div class="auth-field">
                        <label>Last Name</label>
                        <input type="text" name="last_name" placeholder="Santos" value="{{ old('last_name') }}" required>
                        @error('last_name')<span style="font-size:11.5px;color:#8e1e18">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- ID & Type row --}}
                <div class="auth-row-2">
                    <div class="auth-field">
                        <label>Employee / JO ID</label>
                        <input type="text" name="employee_id" placeholder="e.g. PGS-0001 or JO-0042" value="{{ old('employee_id') }}" required>
                        @error('employee_id')<span style="font-size:11.5px;color:#8e1e18">{{ $message }}</span>@enderror
                    </div>
                    <div class="auth-field">
                        <label>Employment Type</label>
                        <select name="employment_type" required>
                            <option value="">Select type</option>
                            <option value="Permanent"  {{ old('employment_type') === 'Permanent'  ? 'selected' : '' }}>Permanent</option>
                            <option value="Job Order"  {{ old('employment_type') === 'Job Order'  ? 'selected' : '' }}>Job Order</option>
                        </select>
                        @error('employment_type')<span style="font-size:11.5px;color:#8e1e18">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Position --}}
                <div class="auth-field">
                    <label>Designation / Position</label>
                    <input type="text" name="position" placeholder="e.g. Nurse II" value="{{ old('position') }}" required>
                    @error('position')<span style="font-size:11.5px;color:#8e1e18">{{ $message }}</span>@enderror
                </div>

                {{-- Email --}}
                <div class="auth-field">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" value="{{ old('email') }}" required>
                    @error('email')<span style="font-size:11.5px;color:#8e1e18">{{ $message }}</span>@enderror
                </div>

                {{-- Password row --}}
                <div class="auth-row-2">
                    <div class="auth-field">
                        <label>Password</label>
                        <div class="auth-pw-wrap">
                            <input type="password" name="password" id="pw1" placeholder="Create a password" required>
                            <button type="button" class="auth-eye" onclick="togglePw('pw1','eye1')">
                                <svg id="eye1" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')<span style="font-size:11.5px;color:#8e1e18">{{ $message }}</span>@enderror
                    </div>
                    <div class="auth-field">
                        <label>Confirm Password</label>
                        <div class="auth-pw-wrap">
                            <input type="password" name="password_confirmation" id="pw2" placeholder="Repeat password" required>
                            <button type="button" class="auth-eye" onclick="togglePw('pw2','eye2')">
                                <svg id="eye2" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Error --}}
                @if($errors->any())
                <div class="auth-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first() }}
                </div>
                @endif

                {{-- Confirm checkbox --}}
                <label class="auth-check">
                    <input type="checkbox" required>
                    <span class="auth-check-box"></span>
                    I confirm that the information I provided is accurate and I am an employee of the Municipal Government of Pagsanjan, Laguna.
                </label>

                {{-- Submit --}}
                <button type="submit" class="pub-hr-btn auth-submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
                    </svg>
                    Submit Request
                </button>

            </form>

            <div class="auth-card-footer">
                <p class="auth-note">Your account will be active once approved by the <strong>HR Administrator</strong>.</p>
                <p class="auth-switch">
                    Already have an account? <a href="{{ route('login') }}" class="auth-switch-btn">Sign in here</a>
                </p>
            </div>
        </div>

        <div class="auth-tags">
            <span class="pub-tag">✓ Pending Admin Approval</span>
            <span class="pub-tag">✓ RA 10173 Compliant</span>
            <span class="pub-tag">✓ Secure Registration</span>
        </div>

    </div>
    @endif

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
