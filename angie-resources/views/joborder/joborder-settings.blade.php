@extends('layouts.app')

@section('title', 'Settings · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<style>
.app-layout { display: flex; min-height: 100vh; }
.main-content { flex: 1; margin-left: 260px; padding: 24px 28px; transition: margin-left 0.3s; }
.sidebar.collapsed + .main-content, .sidebar.collapsed ~ .main-content { margin-left: 70px; }
.settings-container { display: flex; gap: 24px; }
.settings-sidebar { width: 280px; flex-shrink: 0; }
.settings-profile-card { background: linear-gradient(135deg, #15803d 0%, #166534 100%); border-radius: 16px; padding: 24px; margin-bottom: 16px; }
.settings-profile-avatar { width: 56px; height: 56px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; color: #15803d; font-size: 18px; font-weight: 700; margin-bottom: 12px; }
.settings-profile-name { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.settings-profile-role { font-size: 12px; color: rgba(255,255,255,0.7); margin-bottom: 16px; }
.settings-profile-info-item { margin-bottom: 12px; }
.settings-profile-info-item p:first-child { font-size: 10px; color: rgba(255,255,255,0.5); font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px; }
.settings-profile-info-item p:last-child { font-size: 12px; color: #fff; font-weight: 600; }
.settings-profile-info-item.contract-end { background: rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 10px; margin-top: 4px; }
.settings-nav { background: #fff; border-radius: 14px; border: 1.5px solid #e5e4f0; overflow: hidden; }
.settings-nav-item { display: flex; align-items: center; gap: 12px; width: 100%; padding: 14px 18px; border: none; background: none; font-size: 13px; font-weight: 500; color: #6b6a8a; cursor: pointer; text-align: left; transition: all 0.15s; position: relative; font-family: 'Poppins', sans-serif; }
.settings-nav-item:hover { background: #f8f7fc; }
.settings-nav-item.active { background: #15803d; color: #fff; }
.settings-nav-item.active svg { stroke: #fff; }
.settings-tip { background: #f0fdf4; border-radius: 14px; border: 1.5px solid #bbf7d0; padding: 16px; margin-top: 16px; }
.settings-tip-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.settings-tip-title { font-size: 10px; font-weight: 700; color: #15803d; letter-spacing: 0.5px; }
.settings-tip-text { font-size: 12px; color: #166534; line-height: 1.5; }
.settings-content { flex: 1; }
.settings-section { background: #fff; border-radius: 14px; border: 1.5px solid #e5e4f0; margin-bottom: 20px; }
.settings-section-title { font-size: 14px; font-weight: 700; color: #0b044d; padding: 18px 20px; border-bottom: 1px solid #e5e4f0; }
.settings-section-content { padding: 20px; }
.settings-form-wrapper { margin-bottom: 20px; }
.settings-avatar-row { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f0effe; }
.settings-avatar { width: 64px; height: 64px; border-radius: 50%; background: #15803d; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; font-weight: 700; }
.settings-avatar-name { font-size: 15px; font-weight: 700; color: #0b044d; margin-bottom: 2px; }
.settings-avatar-role { font-size: 12px; color: #6b6a8a; }
.settings-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.settings-form-field label { display: block; font-size: 11px; font-weight: 700; color: #9999bb; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.settings-form-field input, .settings-form-field select { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e5e4f0; font-size: 13px; font-family: 'Poppins', sans-serif; }
.settings-form-field input:focus, .settings-form-field select:focus { outline: none; border-color: #15803d; }
.settings-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-bottom: 1px solid #f4f3ff; }
.settings-row:last-child { border-bottom: none; }
.settings-row-label p:first-child { font-size: 13px; font-weight: 600; color: #0b044d; margin-bottom: 2px; }
.settings-row-label p:last-child { font-size: 11px; color: #9999bb; }
.settings-toggle { width: 44px; height: 24px; border-radius: 12px; background: #e5e4f0; border: none; cursor: pointer; position: relative; transition: background 0.2s; }
.settings-toggle.active { background: #15803d; }
.settings-toggle-thumb { position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 50%; background: #fff; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.settings-toggle.active .settings-toggle-thumb { transform: translateX(20px); }
.settings-select { padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e5e4f0; font-size: 13px; font-family: 'Poppins', sans-serif; background: #fff; }
.settings-message { font-size: 13px; padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; }
.settings-message.success { background: #f0fdf4; color: #15803d; }
.settings-message.error { background: #fef2f2; color: #dc2626; }
.settings-save-bar { display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid #f0effe; margin-top: 16px; }
.settings-btn-reset { padding: 10px 20px; border-radius: 9px; border: 1.5px solid #e5e4f0; background: #fff; font-size: 13px; font-weight: 600; color: #6b6a8a; cursor: pointer; font-family: 'Poppins', sans-serif; }
.settings-btn-save { padding: 10px 20px; border-radius: 9px; border: none; background: #15803d; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 8px; font-family: 'Poppins', sans-serif; }
.settings-btn-save.saved { background: #166534; }
.settings-btn-primary { padding: 10px 20px; border-radius: 9px; border: none; background: #15803d; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; font-family: 'Poppins', sans-serif; }
.notif-readonly { background: #f7f6ff; padding: 5px 12px; border-radius: 7px; font-size: 13px; font-weight: 600; color: #5a5888; }
.hidden { display: none; }
.mobile-menu-btn { display: none; position: fixed; top: 20px; left: 20px; z-index: 101; width: 44px; height: 44px; border-radius: 12px; background: #0b044d; border: none; cursor: pointer; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(11,4,77,0.25); color: #fff; }
.mobile-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(11,4,77,0.4); backdrop-filter: blur(2px); z-index: 99; }
.mobile-overlay.active { display: block; }
@media (max-width: 768px) {
    .mobile-menu-btn { display: flex; }
    .sidebar { transform: translateX(-100%); }
    .sidebar.mobile-open { transform: translateX(0); }
    .main-content { margin-left: 0 !important; }
    .settings-container { flex-direction: column; }
    .settings-sidebar { width: 100%; }
    .settings-form-grid { grid-template-columns: 1fr; }
}
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

    @include('joborder.joborder-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('joborder.joborder-notification')

        <div class="settings-container">

            {{-- Settings Sidebar --}}
            <div class="settings-sidebar">
                <div class="settings-profile-card">
                    <div class="settings-profile-avatar">JD</div>
                    <h3 class="settings-profile-name">Juan D. Cruz</h3>
                    <p class="settings-profile-role">JO-0042</p>
                    <div class="settings-profile-info">
                        <div class="settings-profile-info-item">
                            <p>POSITION</p>
                            <p>Utility Worker I</p>
                        </div>
                        <div class="settings-profile-info-item">
                            <p>DEPARTMENT</p>
                            <p>General Services Office</p>
                        </div>
                        <div class="settings-profile-info-item">
                            <p>TYPE</p>
                            <p>Job Order</p>
                        </div>
                        <div class="settings-profile-info-item contract-end">
                            <p>CONTRACT ENDS</p>
                            <p>Dec 31, 2025</p>
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
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <p class="settings-tip-title">CONTRACT REMINDER</p>
                    </div>
                    <p class="settings-tip-text">Your contract ends Dec 31, 2025. Contact HR for renewal inquiries.</p>
                </div>
            </div>

            {{-- Settings Content --}}
            <div class="settings-content">

                {{-- Profile Tab --}}
                <div id="tab-profile">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Personal Information</h3>
                        <div class="settings-section-content">
                            <div class="settings-form-wrapper">
                                <div class="settings-avatar-row">
                                    <div class="settings-avatar">JD</div>
                                    <div>
                                        <p class="settings-avatar-name">Juan D. Cruz</p>
                                        <p class="settings-avatar-role">Utility Worker I · General Services Office</p>
                                    </div>
                                </div>
                                <div class="settings-form-grid">
                                    <div class="settings-form-field">
                                        <label>First Name</label>
                                        <input type="text" id="firstName" value="Juan">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Last Name</label>
                                        <input type="text" id="lastName" value="D. Cruz">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Email Address</label>
                                        <input type="email" id="email" value="juan.cruz@pagsanjan.gov.ph">
                                    </div>
                                    <div class="settings-form-field">
                                        <label>Contact No.</label>
                                        <input type="text" id="contact" value="09171234567">
                                    </div>
                                </div>
                                <div class="settings-save-bar">
                                    <button class="settings-btn-reset" onclick="resetProfile()">Reset</button>
                                    <button class="settings-btn-save" onclick="saveProfile()">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h3 class="settings-section-title">Employment Details</h3>
                        <div class="settings-section-content">
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Employee ID</p>
                                    <p>Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">JO-0042</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Position</p>
                                    <p>Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">Utility Worker I</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Department</p>
                                    <p>Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">General Services Office</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Employment Type</p>
                                    <p>Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">Job Order</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Contract Start</p>
                                    <p>Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">Jan 1, 2025</span>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Contract End</p>
                                    <p>Assigned by HR — not editable</p>
                                </div>
                                <span class="notif-readonly">Dec 31, 2025</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Security Tab --}}
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
                                <p class="settings-message hidden" id="pwMsg"></p>
                                <button class="settings-btn-primary" onclick="changePassword()">Change Password</button>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h3 class="settings-section-title">Login Security</h3>
                        <div class="settings-section-content">
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Two-Factor Authentication</p>
                                    <p>Require OTP on every login</p>
                                </div>
                                <button class="settings-toggle" id="twoFA" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Session Timeout</p>
                                    <p>Auto-logout after inactivity</p>
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

                {{-- Notifications Tab --}}
                <div id="tab-notifications" class="hidden">
                    <div class="settings-section">
                        <h3 class="settings-section-title">In-App Notifications</h3>
                        <div class="settings-section-content">
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Payslip Available</p>
                                    <p>Notify when your payslip is ready for the pay period</p>
                                </div>
                                <button class="settings-toggle active" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Contract Renewal Alert</p>
                                    <p>Notify 30 days before your contract expires</p>
                                </div>
                                <button class="settings-toggle active" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>DTR Deadline Reminder</p>
                                    <p>Remind before DTR submission deadline</p>
                                </div>
                                <button class="settings-toggle active" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-row">
                                <div class="settings-row-label">
                                    <p>Attendance Alert</p>
                                    <p>Notify when a late or absent entry is recorded</p>
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
                                    <p>Email Digest</p>
                                    <p>Receive a daily summary of updates via email</p>
                                </div>
                                <button class="settings-toggle active" onclick="toggleSetting(this)">
                                    <span class="settings-toggle-thumb"></span>
                                </button>
                            </div>
                            <div class="settings-form-wrapper">
                                <div class="settings-save-bar">
                                    <button class="settings-btn-reset" onclick="resetNotifs()">Reset</button>
                                    <button class="settings-btn-save" onclick="saveNotifs()">Save Changes</button>
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
    const sidebar       = document.getElementById('sidebar');
    const toggleBtn     = document.getElementById('toggle-btn');
    const logoText      = document.getElementById('logo-text');
    const navLabel      = document.getElementById('nav-label');
    const userInfo      = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn     = document.getElementById('mobile-menu-btn');
    const overlay       = document.getElementById('mobile-overlay');

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

    function resetProfile() {
        document.getElementById('firstName').value = 'Juan';
        document.getElementById('lastName').value = 'D. Cruz';
        document.getElementById('email').value = 'juan.cruz@pagsanjan.gov.ph';
        document.getElementById('contact').value = '09171234567';
    }

    function resetNotifs() {
        document.querySelectorAll('#tab-notifications .settings-toggle').forEach((t, i) => {
            t.classList.toggle('active', i !== 3);
        });
    }

    function showSaveModal(section, isSuccess, title, msg) {
        const color = isSuccess ? '#15803d' : '#8e1e18';
        const bg    = isSuccess ? '#e8f9ef'  : '#fdf0ef';
        const icon  = isSuccess
            ? '<svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>'
            : '<svg width="28" height="28" fill="none" stroke="#8e1e18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        const now = new Date().toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', hour12:true }) +
                    ', ' + new Date().toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
        document.getElementById('smIcon').style.background = bg;
        document.getElementById('smIcon').innerHTML = icon;
        document.getElementById('smTitle').textContent   = title;
        document.getElementById('smMsg').textContent     = msg;
        document.getElementById('smSection').textContent = section;
        document.getElementById('smTime').textContent    = now;
        document.getElementById('smBtn').style.background = color;
        const modal = document.getElementById('saveModal');
        modal.style.display = 'flex';
    }

    function closeSaveModal() {
        document.getElementById('saveModal').style.display = 'none';
    }

    function saveProfile() {
        showSaveModal('Personal Information', true, 'Settings Saved!', 'Your personal information has been saved successfully.');
    }

    function changePassword() {
        const current = document.getElementById('currentPw').value;
        const newPw   = document.getElementById('newPw').value;
        const confirm = document.getElementById('confirmPw').value;
        const msg     = document.getElementById('pwMsg');

        if (!current || !newPw || !confirm) {
            msg.textContent = 'Please fill in all password fields.';
            msg.className = 'settings-message error';
            msg.classList.remove('hidden');
            return;
        }
        if (newPw.length < 8) {
            msg.textContent = 'Password must be at least 8 characters.';
            msg.className = 'settings-message error';
            msg.classList.remove('hidden');
            return;
        }
        if (newPw !== confirm) {
            msg.textContent = 'New passwords do not match.';
            msg.className = 'settings-message error';
            msg.classList.remove('hidden');
            return;
        }
        msg.classList.add('hidden');
        document.getElementById('currentPw').value = '';
        document.getElementById('newPw').value = '';
        document.getElementById('confirmPw').value = '';
        showSaveModal('Password', true, 'Password Changed!', 'Your password has been updated successfully.');
    }

    function saveNotifs() {
        showSaveModal('Notification Preferences', true, 'Settings Saved!', 'Your notification preferences have been saved successfully.');
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSaveModal(); });
</script>

@include('joborder.joborder-chatbot')

{{-- Save Success Modal --}}
<div id="saveModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,4,77,0.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:1000;padding:20px;" onclick="closeSaveModal()">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:400px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);animation:smUp 0.25s ease;" onclick="event.stopPropagation()">
        <div style="text-align:center;padding:32px 24px 20px;">
            <div id="smIcon" style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"></div>
            <h3 id="smTitle" style="font-size:18px;font-weight:700;color:#0b044d;margin-bottom:6px;"></h3>
            <p id="smMsg" style="font-size:13px;color:#6b6a8a;margin-bottom:20px;"></p>
            <div style="text-align:left;background:#f7f6ff;border-radius:12px;padding:14px 16px;">
                <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0effe;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Section</span><strong id="smSection" style="color:#0b044d;"></strong></div>
                <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Saved at</span><strong id="smTime" style="color:#0b044d;"></strong></div>
            </div>
        </div>
        <div style="padding:0 24px 24px;">
            <button id="smBtn" onclick="closeSaveModal()" style="width:100%;padding:10px 20px;border-radius:9px;border:none;font-size:13px;font-weight:600;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;font-family:'Poppins',sans-serif;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>
    </div>
</div>
<style>@keyframes smUp{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}</style>

@endsection
