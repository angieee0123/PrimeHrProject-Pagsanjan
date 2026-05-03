@extends('layouts.app')

@section('title', 'Settings · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { background: #f8f7fc; min-height: 100vh; }
.app-layout { display: flex; min-height: 100vh; }
.main-content { flex: 1; margin-left: 260px; padding: 24px 28px; transition: margin-left 0.3s; }
.sidebar.collapsed + .main-content, .sidebar.collapsed ~ .main-content { margin-left: 70px; }
.settings-container { display: flex; gap: 24px; }
.settings-sidebar { width: 280px; flex-shrink: 0; }
.settings-profile-card { background: linear-gradient(135deg, #8e1e18 0%, #b02a1f 100%); border-radius: 16px; padding: 24px; margin-bottom: 16px; }
.settings-profile-avatar { width: 56px; height: 56px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; color: #8e1e18; font-size: 18px; font-weight: 700; margin-bottom: 12px; }
.settings-profile-name { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.settings-profile-role { font-size: 12px; color: rgba(255,255,255,0.7); margin-bottom: 16px; }
.settings-profile-info-item { margin-bottom: 12px; }
.settings-profile-info-item p:first-child { font-size: 10px; color: rgba(255,255,255,0.5); font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px; }
.settings-profile-info-item p:last-child { font-size: 12px; color: #fff; font-weight: 600; }
.settings-nav { background: #fff; border-radius: 14px; border: 1.5px solid #e5e4f0; overflow: hidden; }
.settings-nav-item { display: flex; align-items: center; gap: 12px; width: 100%; padding: 14px 18px; border: none; background: none; font-size: 13px; font-weight: 500; color: #6b6a8a; cursor: pointer; text-align: left; transition: all 0.15s; position: relative; font-family: 'Poppins', sans-serif; }
.settings-nav-item:hover { background: #f8f7fc; }
.settings-nav-item.active { background: #8e1e18; color: #fff; }
.settings-nav-item.active svg { stroke: #fff; }
.settings-tip { background: #fefce8; border-radius: 14px; border: 1px solid #fde68a; padding: 16px; margin-top: 16px; }
.settings-tip-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.settings-tip-title { font-size: 10px; font-weight: 700; color: #a16207; letter-spacing: 0.5px; }
.settings-tip-text { font-size: 12px; color: #92400e; line-height: 1.5; }
.settings-content { flex: 1; }
.settings-section { background: #fff; border-radius: 14px; border: 1.5px solid #e5e4f0; margin-bottom: 20px; }
.settings-section-title { font-size: 14px; font-weight: 700; color: #0b044d; padding: 18px 20px; border-bottom: 1px solid #e5e4f0; }
.settings-section-content { padding: 20px; }
.settings-form-wrapper { margin-bottom: 20px; }
.settings-avatar-row { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f0effe; }
.settings-avatar { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; font-weight: 700; }
.settings-avatar-name { font-size: 15px; font-weight: 700; color: #0b044d; margin-bottom: 2px; }
.settings-avatar-role { font-size: 12px; color: #6b6a8a; }
.settings-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.settings-form-field label { display: block; font-size: 11px; font-weight: 700; color: #9999bb; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.settings-form-field input, .settings-form-field select { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e5e4f0; font-size: 13px; font-family: 'Poppins', sans-serif; }
.settings-form-field input:focus, .settings-form-field select:focus { outline: none; border-color: #0b044d; }
.settings-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-bottom: 1px solid #f4f3ff; }
.settings-row:last-child { border-bottom: none; }
.settings-row-label p:first-child { font-size: 13px; font-weight: 600; color: #0b044d; margin-bottom: 2px; }
.settings-row-label p:last-child { font-size: 11px; color: #9999bb; }
.settings-toggle { width: 44px; height: 24px; border-radius: 12px; background: #e5e4f0; border: none; cursor: pointer; position: relative; transition: background 0.2s; }
.settings-toggle.active { background: #15803d; }
.settings-toggle-thumb { position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.settings-toggle.active .settings-toggle-thumb { transform: translateX(20px); }
.settings-select { padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e5e4f0; font-size: 13px; font-family: 'Poppins', sans-serif; background: #fff; }
.settings-message { font-size: 13px; padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; }
.settings-message.success { background: #f0fdf4; color: #15803d; }
.settings-message.error { background: #fef2f2; color: #dc2626; }
.settings-save-bar { display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid #f0effe; margin-top: 16px; }
.settings-btn-reset { padding: 10px 20px; border-radius: 9px; border: 1.5px solid #e5e4f0; background: #fff; font-size: 13px; font-weight: 600; color: #6b6a8a; cursor: pointer; font-family: 'Poppins', sans-serif; }
.settings-btn-save { padding: 10px 20px; border-radius: 9px; border: none; background: #8e1e18; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 8px; font-family: 'Poppins', sans-serif; }
.settings-btn-save.saved { background: #15803d; }
.settings-btn-primary { padding: 10px 20px; border-radius: 9px; border: none; background: #8e1e18; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 8px; font-family: 'Poppins', sans-serif; }
.notif-readonly { background: #f7f6ff; padding: 5px 12px; border-radius: 7px; font-size: 13px; font-weight: 600; color: #5a5888; }
.hidden { display: none; }
.mobile-menu-btn { display: none; position: fixed; top: 20px; left: 20px; z-index: 101; width: 44px; height: 44px; border-radius: 12px; background: #0b044d; border: none; cursor: pointer; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(11,4,77,0.25); color: #fff; }
.mobile-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(11,4,77,0.4); backdrop-filter: blur(2px); z-index: 99; }
.mobile-overlay.active { display: block; }
@media (max-width: 768px) { .mobile-menu-btn { display: flex; } .sidebar { transform: translateX(-100%); } .sidebar.mobile-open { transform: translateX(0); } .main-content { margin-left: 0 !important; } .settings-container { flex-direction: column; } .settings-sidebar { width: 100%; } }
</style>
@endpush

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

    @include('permanent.permanent-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('permanent.permanent-notification')

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
                                    <div class="settings-avatar" style="background:#8e1e18;">AR</div>
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
                                <div class="settings-form-field" style="margin-bottom:12px;">
                                    <label>Current Password</label>
                                    <input type="password" id="currentPw" placeholder="••••••••">
                                </div>
                                <div class="settings-form-field" style="margin-bottom:12px;">
                                    <label>New Password</label>
                                    <input type="password" id="newPw" placeholder="••••••••">
                                </div>
                                <div class="settings-form-field" style="margin-bottom:16px;">
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

@include('permanent.permanent-chatbot')

{{-- Settings Save Success Modal --}}
<div class="modal-overlay" id="settingsSavedModal" onclick="closeSavedModal()" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,4,77,0.55);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;visibility:hidden;transition:all 0.2s;padding:20px;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:400px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);transform:translateY(16px);transition:transform 0.2s;" onclick="event.stopPropagation()" id="settingsSavedBox">
        <div style="text-align:center;padding:32px 24px 20px;">
            <div id="savedIcon" style="width:56px;height:56px;border-radius:50%;background:#e8f9ef;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 id="savedTitle" style="font-size:18px;font-weight:700;color:#0b044d;margin-bottom:6px;">Settings Saved!</h3>
            <p id="savedMsg" style="font-size:13px;color:#6b6a8a;margin-bottom:20px;">Your changes have been saved successfully.</p>
            <div style="text-align:left;background:#f7f6ff;border-radius:12px;padding:14px 16px;">
                <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0effe;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Section</span><strong id="savedSection" style="color:#0b044d;">Profile</strong></div>
                <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Saved at</span><strong id="savedTime" style="color:#0b044d;">—</strong></div>
            </div>
        </div>
        <div style="display:flex;justify-content:center;padding:0 24px 24px;">
            <button onclick="closeSavedModal()" style="width:100%;padding:10px 20px;border-radius:9px;border:none;background:#8e1e18;font-size:13px;font-weight:600;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;font-family:'Poppins',sans-serif;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>
    </div>
</div>

@endsection
