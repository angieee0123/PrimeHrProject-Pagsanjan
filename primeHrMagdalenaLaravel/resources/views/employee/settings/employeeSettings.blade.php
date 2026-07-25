@extends('layouts.employee')

@section('title', 'Settings · PRIME HRIS')

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Settings'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    {{-- Main Content --}}
    <main class="main-content glass-shell">

        @include('employee.notification.employeeNotification')

        @php
            $position = $employee->employmentDetail?->designationRelation?->title ?? $employee->employmentDetail?->position ?? 'N/A';
            $department = $employee->employmentDetail?->departmentRelation?->name ?? 'N/A';
            $employmentType = $employee->employmentDetail?->employment_status ?? 'N/A';
            $dateHired = $employee->employmentDetail?->appointment_date ? \Carbon\Carbon::parse($employee->employmentDetail->appointment_date)->format('M d, Y') : 'N/A';
        @endphp

        <div class="settings-container">
            <div class="settings-sidebar">
                <div class="settings-profile-card">
                    <div class="settings-profile-avatar" id="sidebarAvatar">
                        @if($employee->photo)
                            <img src="{{ $employee->photo }}" alt="" class="settings-avatar-img">
                        @else
                            <span id="sidebarAvatarInitials">{{ $initials }}</span>
                        @endif
                    </div>
                    <h3 class="settings-profile-name">{{ $fullName }}</h3>
                    <p class="settings-profile-role">{{ $employee->employee_id }}</p>
                    <div class="settings-profile-info">
                        <div class="settings-profile-info-item">
                            <p>POSITION</p>
                            <p>{{ $position }}</p>
                        </div>
                        <div class="settings-profile-info-item">
                            <p>DEPARTMENT</p>
                            <p>{{ $department }}</p>
                        </div>
                        <div class="settings-profile-info-item">
                            <p>TYPE</p>
                            <p>{{ $employmentType }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="settings-nav">
                    <button class="settings-nav-item active" onclick="switchTab('profile', this)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Profile</span>
                    </button>
                    <button class="settings-nav-item" onclick="switchTab('security', this)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Security</span>
                    </button>
                    <button class="settings-nav-item" onclick="switchTab('notifications', this)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span>Notifications</span>
                    </button>
                </div>
                
                <div class="settings-tip">
                    <div class="settings-tip-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <p class="settings-tip-title">QUICK TIP</p>
                    </div>
                    <p class="settings-tip-text">Keep your profile updated for accurate payroll processing.</p>
                </div>
            </div>
            
            <div class="settings-content">
                <div id="tab-profile">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Personal Information</h3>
                        <div class="settings-section-content">
                            <div class="settings-form-wrapper">
                                <div class="settings-avatar-row">
                                    <div class="settings-avatar-upload-wrap">
                                        <div class="settings-avatar" id="mainAvatar">
                                            @if($employee->photo)
                                                <img src="{{ $employee->photo }}" alt="" class="settings-avatar-img">
                                            @else
                                                <span id="mainAvatarInitials">{{ $initials }}</span>
                                            @endif
                                        </div>
                                        <button type="button" class="settings-avatar-edit-btn" onclick="document.getElementById('avatarPhotoInput').click()" title="Change photo">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                        </button>
                                        <input type="file" id="avatarPhotoInput" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="uploadAvatarPhoto(this)">
                                    </div>
                                    <div class="settings-avatar-info">
                                        <p class="settings-avatar-name">{{ $fullName }}</p>
                                        <p class="settings-avatar-role">{{ $position }} · {{ $department }}</p>
                                    </div>
                                </div>
                                <p class="settings-message error hidden" id="avatarMsg"></p>

                                <div class="settings-form-grid">
                                    <div class="settings-form-field">
                                        <label>First Name</label>
                                        <input type="text" id="firstName" value="{{ $employee->first_name }}">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Last Name</label>
                                        <input type="text" id="lastName" value="{{ $employee->last_name }}">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Email Address</label>
                                        <input type="email" id="emailAddr" value="{{ auth()->user()->email }}">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Contact No.</label>
                                        <input type="text" id="contactNo" value="{{ $contactNumber }}">
                                    </div>
                                </div>
                                <p class="settings-message error hidden" id="profileMsg"></p>
                                <div class="settings-save-bar">
                                    <button class="settings-btn-reset" onclick="resetProfile()">Reset</button>
                                    <button class="settings-btn-save" id="profileSaveBtn" onclick="saveProfile()">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3 class="settings-section-title">Employment Details</h3>
                        <div class="settings-section-content">
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Employee ID</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">{{ $employee->employee_id }}</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Position</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">{{ $position }}</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Department</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">{{ $department }}</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Employment Type</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">{{ $employmentType }}</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Date Hired</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">{{ $dateHired }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="tab-security" class="hidden">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Change Password</h3>
                        <div class="settings-section-content">
                            <div class="settings-form-wrapper">
                                <div class="settings-form-field settings-field-spacing-sm">
                                    <label>Current Password</label>
                                    <input type="password" id="currentPw" placeholder="••••••••">
                                </div>
                                <div class="settings-form-field settings-field-spacing-sm">
                                    <label>New Password</label>
                                    <input type="password" id="newPw" placeholder="••••••••">
                                </div>
                                <div class="settings-form-field settings-field-spacing-md">
                                    <label>Confirm New Password</label>
                                    <input type="password" id="confirmPw" placeholder="••••••••">
                                </div>
                                <p class="settings-message error hidden" id="pwMsg"></p>
                                <button class="settings-btn-primary" id="pwSaveBtn" onclick="changePassword()">
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Read-only account facts, straight from the users table. The
                         previous Two-Factor Authentication toggle and Session
                         Timeout picker had no backing column and no enforcement —
                         a 2FA switch that protects nothing is worse than none. --}}
                    <div class="settings-section">
                        <h3 class="settings-section-title">Account</h3>
                        <div class="settings-section-content">
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Username</p>
                                    <p class="settings-row-desc">Used to sign in — assigned by HR</p>
                                </div>
                                <span class="notif-readonly">{{ auth()->user()->username ?? '—' }}</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Account Status</p>
                                    <p class="settings-row-desc">Deactivated accounts cannot sign in</p>
                                </div>
                                <span class="notif-readonly">{{ ucfirst(auth()->user()->status ?? 'active') }}</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Session Length</p>
                                    <p class="settings-row-desc">You are signed out automatically after this much inactivity</p>
                                </div>
                                <span class="notif-readonly">{{ config('session.lifetime') }} minutes</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="tab-notifications" class="hidden">
                    <div class="settings-section">
                        <h3 class="settings-section-title">In-App Notifications</h3>
                        <div class="settings-section-content">
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Payslip Available</p>
                                    <p class="settings-row-desc">Notify when your monthly payslip is ready</p>
                                </div>
                                <button class="settings-toggle {{ $prefs['payslip_available'] ? 'active' : '' }}" data-pref="payslip_available" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            @if($isPermanent ?? false)
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Leave Status Update</p>
                                    <p class="settings-row-desc">Notify when your leave request is approved or rejected</p>
                                </div>
                                <button class="settings-toggle {{ $prefs['leave_status'] ? 'active' : '' }}" data-pref="leave_status" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            @endif
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">DTR Deadline Reminder</p>
                                    <p class="settings-row-desc">Remind before DTR submission deadline</p>
                                </div>
                                <button class="settings-toggle {{ $prefs['dtr_reminder'] ? 'active' : '' }}" data-pref="dtr_reminder" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Attendance Alert</p>
                                    <p class="settings-row-desc">Notify when a late or absent entry is recorded</p>
                                </div>
                                <button class="settings-toggle {{ $prefs['attendance_alert'] ? 'active' : '' }}" data-pref="attendance_alert" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3 class="settings-section-title">Email Notifications</h3>
                        <div class="settings-section-content">
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Email Digest</p>
                                    <p class="settings-row-desc">Receive a daily summary of updates via email</p>
                                </div>
                                <button class="settings-toggle {{ $prefs['email_digest'] ? 'active' : '' }}" data-pref="email_digest" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <p class="settings-message error hidden" id="notifMsg"></p>
                            <div class="settings-form-wrapper">
                                <div class="settings-save-bar">
                                    <button class="settings-btn-reset" onclick="resetNotifications()">Reset</button>
                                    <button class="settings-btn-save" id="notifSaveBtn" onclick="saveNotifications()">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const logoText = document.getElementById('logo-text');
    const navLabel = document.getElementById('nav-label');
    const userInfo = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const overlay = document.getElementById('mobile-overlay');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');
            toggleBtn.textContent = collapsed ? '›' : '‹';
            if (logoText) logoText.style.display = collapsed ? 'none' : '';
            if (navLabel) navLabel.style.display = collapsed ? 'none' : '';
            if (userInfo) userInfo.style.display = collapsed ? 'none' : '';
            if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
            document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
                el.style.display = collapsed ? 'none' : '';
            });
        });
    }

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

    function switchTab(tabId, btn) {
        document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('#tab-profile, #tab-security, #tab-notifications').forEach(t => t.classList.add('hidden'));
        document.getElementById('tab-' + tabId).classList.remove('hidden');
    }

    function toggleSetting(btn) {
        btn.classList.toggle('active');
    }

    function setAvatarDisplay(containerId, initialsId, photoUrl) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = photoUrl
            ? `<img src="${photoUrl}" alt="" class="settings-avatar-img">`
            : (document.getElementById(initialsId)?.outerHTML || container.innerHTML);
    }

    function uploadAvatarPhoto(input) {
        const msg = document.getElementById('avatarMsg');
        msg.classList.add('hidden');

        const file = input.files ? input.files[0] : null;
        if (!file) return;

        const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            msg.textContent = 'Please upload a JPEG, PNG, or WebP image.';
            msg.className = 'settings-message error';
            input.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            msg.textContent = 'Image must be 5 MB or smaller.';
            msg.className = 'settings-message error';
            input.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('photo', file);
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        fetch('{{ route('employee.settings.photo') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: formData,
        })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Failed to upload photo.');
                return data;
            })
            .then(data => {
                setAvatarDisplay('sidebarAvatar', 'sidebarAvatarInitials', data.photo);
                setAvatarDisplay('mainAvatar', 'mainAvatarInitials', data.photo);
                saveSettings('photo');
            })
            .catch(err => {
                msg.textContent = err.message;
                msg.className = 'settings-message error';
            })
            .finally(() => { input.value = ''; });
    }

    const profileDefaults = {
        firstName: @json($employee->first_name),
        lastName: @json($employee->last_name),
        emailAddr: @json(auth()->user()->email),
        contactNo: @json($contactNumber),
    };
    const notificationDefaults = @json($prefs);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    /** POST JSON and surface the server's message on failure. */
    function settingsPost(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        }).then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Something went wrong. Please try again.');
            return data;
        });
    }

    function showFieldError(id, message) {
        const el = document.getElementById(id);
        el.textContent = message;
        el.className = 'settings-message error';
    }

    function hideFieldError(id) {
        document.getElementById(id).classList.add('hidden');
    }

    /** Brief green confirmation on the button itself, alongside the modal. */
    function flashSaved(btn, label = 'Saved') {
        if (!btn) return;
        if (btn.dataset.idleLabel === undefined) btn.dataset.idleLabel = btn.textContent.trim();
        clearTimeout(btn.savedTimer);
        btn.classList.add('saved');
        btn.textContent = label;
        btn.savedTimer = setTimeout(() => {
            btn.classList.remove('saved');
            btn.textContent = btn.dataset.idleLabel;
        }, 2200);
    }

    function saveProfile() {
        hideFieldError('profileMsg');
        const payload = {
            first_name: document.getElementById('firstName').value.trim(),
            last_name: document.getElementById('lastName').value.trim(),
            email: document.getElementById('emailAddr').value.trim(),
            contact_number: document.getElementById('contactNo').value.trim(),
        };

        if (!payload.first_name || !payload.last_name) {
            showFieldError('profileMsg', 'First and last name are required.');
            return;
        }
        if (!payload.email) {
            showFieldError('profileMsg', 'Email address is required.');
            return;
        }

        const btn = document.getElementById('profileSaveBtn');
        btn.disabled = true;

        settingsPost('{{ route('employee.settings.profile') }}', payload)
            .then(data => {
                // Keep Reset and the on-page name/initials in step with what was saved.
                profileDefaults.firstName = payload.first_name;
                profileDefaults.lastName = payload.last_name;
                profileDefaults.emailAddr = payload.email;
                profileDefaults.contactNo = payload.contact_number;
                document.querySelectorAll('.settings-profile-name, .settings-avatar-name')
                    .forEach(el => { el.textContent = data.fullName; });
                ['sidebarAvatarInitials', 'mainAvatarInitials'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = data.initials;
                });
                flashSaved(btn);
                saveSettings('profile');
            })
            .catch(err => showFieldError('profileMsg', err.message))
            .finally(() => { btn.disabled = false; });
    }

    function saveNotifications() {
        hideFieldError('notifMsg');
        const payload = {};
        Object.keys(notificationDefaults).forEach(key => {
            const toggle = document.querySelector(`#tab-notifications .settings-toggle[data-pref="${key}"]`);
            // A category hidden for this employment type keeps its stored value.
            payload[key] = toggle ? toggle.classList.contains('active') : notificationDefaults[key];
        });

        const btn = document.getElementById('notifSaveBtn');
        btn.disabled = true;

        settingsPost('{{ route('employee.settings.notifications') }}', payload)
            .then(() => {
                Object.assign(notificationDefaults, payload);
                flashSaved(btn);
                saveSettings('notifications');
            })
            .catch(err => showFieldError('notifMsg', err.message))
            .finally(() => { btn.disabled = false; });
    }

    function saveSettings(section) {
        const labels = { profile: 'Personal Information', notifications: 'Notification Preferences', password: 'Password', photo: 'Profile Photo' };
        const now = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true }) +
                    ', ' + new Date().toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
        document.getElementById('savedSection').textContent = labels[section] || section;
        document.getElementById('savedTime').textContent = now;
        document.getElementById('savedTitle').textContent = 'Settings Saved!';
        document.getElementById('savedMsg').textContent = 'Your ' + (labels[section] || section).toLowerCase() + ' settings have been saved successfully.';
        document.getElementById('savedIcon').style.background = '#e8f9ef';
        document.getElementById('savedIcon').innerHTML = '<svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>';
        const modal = document.getElementById('settingsSavedModal');
        modal.style.opacity = '1'; modal.style.visibility = 'visible';
        document.getElementById('settingsSavedBox').style.transform = 'translateY(0)';
    }

    function closeSavedModal() {
        const modal = document.getElementById('settingsSavedModal');
        modal.style.opacity = '0'; modal.style.visibility = 'hidden';
        document.getElementById('settingsSavedBox').style.transform = 'translateY(16px)';
    }

    function resetProfile() {
        Object.entries(profileDefaults).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        });
    }

    /** Restore the toggles to what is currently stored, not to a fixed pattern. */
    function resetNotifications() {
        hideFieldError('notifMsg');
        Object.entries(notificationDefaults).forEach(([key, on]) => {
            const toggle = document.querySelector(`#tab-notifications .settings-toggle[data-pref="${key}"]`);
            if (toggle) toggle.classList.toggle('active', !!on);
        });
    }

    function changePassword() {
        hideFieldError('pwMsg');
        const current = document.getElementById('currentPw').value;
        const newPw   = document.getElementById('newPw').value;
        const confirm = document.getElementById('confirmPw').value;

        if (!current || !newPw || !confirm) {
            showFieldError('pwMsg', 'Please fill in all password fields.');
            return;
        }
        if (newPw.length < 8) {
            showFieldError('pwMsg', 'New password must be at least 8 characters.');
            return;
        }
        if (newPw !== confirm) {
            showFieldError('pwMsg', 'New password and confirmation do not match.');
            return;
        }

        const btn = document.getElementById('pwSaveBtn');
        btn.disabled = true;

        settingsPost('{{ route('employee.settings.password') }}', {
            current_password: current,
            new_password: newPw,
            new_password_confirmation: confirm,
        })
            .then(() => {
                document.getElementById('currentPw').value = '';
                document.getElementById('newPw').value = '';
                document.getElementById('confirmPw').value = '';
                flashSaved(btn, 'Password Changed');
                saveSettings('password');
            })
            .catch(err => showFieldError('pwMsg', err.message))
            .finally(() => { btn.disabled = false; });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSavedModal(); });
</script>

@include('employee.chatbot.employeeChatbot')

{{-- Settings Save Success Modal --}}
<div class="modal-overlay settings-saved-modal" id="settingsSavedModal" onclick="closeSavedModal()">
    <div class="settings-saved-box" onclick="event.stopPropagation()" id="settingsSavedBox">
        <div class="settings-saved-content">
            <div id="savedIcon" class="settings-saved-icon">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 id="savedTitle" class="settings-saved-title">Settings Saved!</h3>
            <p id="savedMsg" class="settings-saved-message">Your changes have been saved successfully.</p>
            <div class="settings-saved-meta">
                <div class="settings-saved-meta-row settings-saved-meta-row-border"><span class="settings-saved-meta-label">Section</span><strong id="savedSection" class="settings-saved-meta-value">Profile</strong></div>
                <div class="settings-saved-meta-row"><span class="settings-saved-meta-label">Saved at</span><strong id="savedTime" class="settings-saved-meta-value">—</strong></div>
            </div>
        </div>
        <div class="settings-saved-footer">
            <button onclick="closeSavedModal()" class="settings-saved-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>
    </div>
</div>

@endsection
