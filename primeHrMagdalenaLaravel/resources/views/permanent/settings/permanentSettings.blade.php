@extends('layouts.permanent')

@section('title', 'Settings · PRIME HRIS')

@section('content')
<div class="app-layout">

    {{-- Mobile Menu Button --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('permanent.sidebar.permanentSidebar')

    {{-- Main Content --}}
    <main class="main-content">

        @include('permanent.notification.permanentNotification')

        <div class="settings-container">
            <div class="settings-sidebar">
                <div class="settings-profile-card">
                    <div class="settings-profile-avatar">AR</div>
                    <h3 class="settings-profile-name">Ana R. Reyes</h3>
                    <p class="settings-profile-role">PGS-0115</p>
                    <div class="settings-profile-info">
                        <div class="settings-profile-info-item">
                            <p>POSITION</p>
                            <p>Nurse II</p>
                        </div>
                        <div class="settings-profile-info-item">
                            <p>DEPARTMENT</p>
                            <p>Municipal Health Office</p>
                        </div>
                        <div class="settings-profile-info-item">
                            <p>TYPE</p>
                            <p>Permanent</p>
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
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
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
                                    <div class="settings-avatar">AR</div>
                                    <div class="settings-avatar-info">
                                        <p class="settings-avatar-name">Ana R. Reyes</p>
                                        <p class="settings-avatar-role">Nurse II · Municipal Health Office</p>
                                    </div>
                                </div>
                                
                                <div class="settings-form-grid">
                                    <div class="settings-form-field">
                                        <label>First Name</label>
                                        <input type="text" id="firstName" value="Ana">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Last Name</label>
                                        <input type="text" id="lastName" value="R. Reyes">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Email Address</label>
                                        <input type="email" id="emailAddr" value="ana.reyes@pagsanjan.gov.ph">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Contact No.</label>
                                        <input type="text" id="contactNo" value="09201122334">
                                    </div>
                                </div>
                                <div class="settings-save-bar">
                                    <button class="settings-btn-reset" onclick="resetProfile()">Reset</button>
                                    <button class="settings-btn-save" onclick="saveSettings('profile')">Save Changes</button>
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
                                <span class="notif-readonly">PGS-0115</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Position</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">Nurse II</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Department</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">Municipal Health Office</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Employment Type</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">Permanent</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Date Hired</p>
                                    <p class="settings-row-desc">Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">Jan 15, 2018</span>
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
                                <button class="settings-btn-primary" onclick="changePassword()">
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3 class="settings-section-title">Login Security</h3>
                        <div class="settings-section-content">
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Two-Factor Authentication</p>
                                    <p class="settings-row-desc">Require OTP on every login</p>
                                </div>
                                <button class="settings-toggle" id="twoFA" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Session Timeout</p>
                                    <p class="settings-row-desc">Auto-logout after inactivity</p>
                                </div>
                                <select class="settings-select">
                                    <option value="15">15 minutes</option>
                                    <option value="30" selected>30 minutes</option>
                                    <option value="60">1 hour</option>
                                    <option value="120">2 hours</option>
                                </select>
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
                                <button class="settings-toggle active" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Leave Status Update</p>
                                    <p class="settings-row-desc">Notify when your leave request is approved or rejected</p>
                                </div>
                                <button class="settings-toggle active" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">DTR Deadline Reminder</p>
                                    <p class="settings-row-desc">Remind before DTR submission deadline</p>
                                </div>
                                <button class="settings-toggle active" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p class="settings-row-title">Attendance Alert</p>
                                    <p class="settings-row-desc">Notify when a late or absent entry is recorded</p>
                                </div>
                                <button class="settings-toggle" onclick="toggleSetting(this)">
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
                                <button class="settings-toggle active" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-form-wrapper">
                                <div class="settings-save-bar">
                                    <button class="settings-btn-reset" onclick="resetNotifications()">Reset</button>
                                    <button class="settings-btn-save" onclick="saveSettings('notifications')">Save Changes</button>
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

    const profileDefaults = { firstName: 'Ana', lastName: 'R. Reyes', emailAddr: 'ana.reyes@pagsanjan.gov.ph', contactNo: '09201122334' };

    function saveSettings(section) {
        const labels = { profile: 'Personal Information', notifications: 'Notification Preferences', password: 'Password' };
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

    function resetNotifications() {
        document.querySelectorAll('#tab-notifications .settings-toggle').forEach((t, i) => {
            if (i < 3) t.classList.add('active'); else t.classList.remove('active');
        });
    }

    function changePassword() {
        const current = document.getElementById('currentPw').value;
        const newPw   = document.getElementById('newPw').value;
        const confirm = document.getElementById('confirmPw').value;
        const msg     = document.getElementById('pwMsg');

        if (!current || !newPw || !confirm) {
            msg.textContent = 'Please fill in all password fields.';
            msg.className = 'settings-message error';
            return;
        }
        if (newPw.length < 8) {
            msg.textContent = 'New password must be at least 8 characters.';
            msg.className = 'settings-message error';
            return;
        }
        if (newPw !== confirm) {
            msg.textContent = 'New password and confirmation do not match.';
            msg.className = 'settings-message error';
            return;
        }
        msg.classList.add('hidden');
        document.getElementById('currentPw').value = '';
        document.getElementById('newPw').value = '';
        document.getElementById('confirmPw').value = '';
        saveSettings('password');
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSavedModal(); });
</script>

@include('permanent.chatbot.permanentChatbot')

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
