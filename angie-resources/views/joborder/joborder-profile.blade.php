@extends('layouts.app')

@section('title', 'Profile · PRIME HRIS')

@section('content')
<div class="app-layout">

    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('joborder.joborder-sidebarnav')

    <main class="main-content">

        @include('joborder.joborder-notification')

        {{-- Profile Banner --}}
        <div class="welcome-banner profile-banner">
            <div class="banner-left profile-banner-left">
                <div class="profile-avatar-lg">JD</div>
                <div class="profile-banner-info">
                    <div class="profile-banner-name-row">
                        <h2 class="profile-banner-name">Juan D. Cruz</h2>
                        <span class="banner-badge"><span class="banner-badge-dot"></span>Active</span>
                    </div>
                    <p class="profile-banner-sub">Utility Worker I · General Services Office</p>
                    <div class="profile-banner-badges">
                        <span class="banner-badge outline">Job Order</span>
                        <span class="banner-badge outline">JO-0042</span>
                        <span class="banner-badge outline">Contract: Jan 1 – Dec 31, 2025</span>
                    </div>
                </div>
            </div>
            <div class="banner-right">
                <button class="btn-edit-profile" onclick="openEditModal()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profile
                </button>
            </div>
        </div>

        {{-- Stats --}}
        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Months Served</p>
                    <div class="stat-icon-wrap" style="background:#f0effe">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value">4</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">Since Jan 2025</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Contract Days Left</p>
                    <div class="stat-icon-wrap" style="background:#fefce8">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <p class="stat-value">272</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#f59e0b"></span>
                    <p class="stat-sub">Until Dec 31, 2025</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Attendance Rate</p>
                    <div class="stat-icon-wrap" style="background:#e8f9ef">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">90%</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">19 days present</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Trainings Completed</p>
                    <div class="stat-icon-wrap" style="background:#fdf0ef">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                </div>
                <p class="stat-value">2</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">Total programs</p>
                </div>
            </div>
        </div>

        {{-- Contract Alert --}}
        <div class="contract-alert">
            <div class="contract-alert-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <div class="contract-alert-content">
                <h4>Job Order Contract Status</h4>
                <p>Your contract is valid from Jan 1, 2025 to Dec 31, 2025. 272 days remaining.</p>
            </div>
            <button class="contract-alert-btn">View Contract</button>
        </div>

        {{-- Profile Info Section --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Profile Information</p>
                    <p class="table-sub">View and manage your personal details</p>
                </div>
            </div>

            <div class="profile-tabs">
                <button class="profile-tab active" onclick="switchTab('personal', this)">Personal Info</button>
                <button class="profile-tab" onclick="switchTab('employment', this)">Employment</button>
                <button class="profile-tab" onclick="switchTab('government', this)">Government IDs</button>
                <button class="profile-tab" onclick="switchTab('emergency', this)">Emergency Contact</button>
            </div>

            <div style="padding:24px 28px;">
                <div id="tab-personal" class="tab-content">
                    <div class="profile-grid">
                        <div class="profile-field"><span>Full Name</span><strong>Juan D. Cruz</strong></div>
                        <div class="profile-field"><span>Gender</span><strong>Male</strong></div>
                        <div class="profile-field"><span>Date of Birth</span><strong>Aug 12, 1992</strong></div>
                        <div class="profile-field"><span>Contact No.</span><strong>09171234567</strong></div>
                        <div class="profile-field profile-full"><span>Email Address</span><strong>juan.cruz@pagsanjan.gov.ph</strong></div>
                        <div class="profile-field profile-full"><span>Address</span><strong>789 Rizal Street, Barangay Sampaloc, Pagsanjan, Laguna</strong></div>
                    </div>
                </div>
                <div id="tab-employment" class="tab-content hidden">
                    <div class="profile-grid">
                        <div class="profile-field"><span>Employee ID</span><strong>JO-0042</strong></div>
                        <div class="profile-field"><span>Employment Type</span><strong>Job Order</strong></div>
                        <div class="profile-field"><span>Contract Start</span><strong>Jan 1, 2025</strong></div>
                        <div class="profile-field"><span>Contract End</span><strong>Dec 31, 2025</strong></div>
                        <div class="profile-field"><span>Status</span><strong>Active</strong></div>
                        <div class="profile-field"><span>Days Remaining</span><strong>272 days</strong></div>
                        <div class="profile-field profile-full"><span>Position / Designation</span><strong>Utility Worker I</strong></div>
                        <div class="profile-field profile-full"><span>Department / Office</span><strong>General Services Office</strong></div>
                    </div>
                </div>
                <div id="tab-government" class="tab-content hidden">
                    <div class="profile-grid">
                        <div class="profile-field"><span>SSS No.</span><strong>34-5678901-2</strong></div>
                        <div class="profile-field"><span>PhilHealth No.</span><strong>12-345678901-2</strong></div>
                        <div class="profile-field"><span>Pag-IBIG No.</span><strong>1234-5678-9012</strong></div>
                        <div class="profile-field"><span>TIN</span><strong>123-456-789</strong></div>
                        <div class="profile-field profile-full">
                            <div class="note-box">
                                <div class="note-box-inner">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                    <div>
                                        <span style="font-size:11px;font-weight:700;color:#a16207;display:block;margin-bottom:3px;">NOTE FOR JOB ORDER EMPLOYEES</span>
                                        <span style="font-size:12px;color:#6b6a8a;">Job Order employees use SSS instead of GSIS. Make sure your SSS contributions are up to date.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="tab-emergency" class="tab-content hidden">
                    <div class="profile-grid">
                        <div class="profile-field"><span>Contact Person</span><strong>Maria Cruz</strong></div>
                        <div class="profile-field"><span>Relationship</span><strong>Spouse</strong></div>
                        <div class="profile-field"><span>Phone Number</span><strong>09181234567</strong></div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

{{-- Edit Profile Modal --}}
<div class="modal-overlay" id="editModal" style="display:none" onclick="closeEditModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">EDIT PROFILE</span>
                <h3 class="modal-title">Update Personal Information</h3>
            </div>
            <button class="modal-close" onclick="closeEditModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <span class="modal-section-label">CONTACT INFORMATION</span>
            <div class="form-grid">
                <div class="form-field">
                    <label>Contact No.</label>
                    <input type="text" value="09171234567" id="contact">
                </div>
                <div class="form-field">
                    <label>Email Address</label>
                    <input type="email" value="juan.cruz@pagsanjan.gov.ph" id="email">
                </div>
                <div class="form-field form-full">
                    <label>Address</label>
                    <input type="text" value="789 Rizal Street, Barangay Sampaloc, Pagsanjan, Laguna" id="address">
                </div>
            </div>
            <span class="modal-section-label" style="margin-top:20px;display:block;">EMERGENCY CONTACT</span>
            <div class="form-grid">
                <div class="form-field">
                    <label>Contact Person</label>
                    <input type="text" value="Maria Cruz" id="emergencyContact">
                </div>
                <div class="form-field">
                    <label>Relationship</label>
                    <input type="text" value="Spouse" id="emergencyRelation">
                </div>
                <div class="form-field">
                    <label>Phone Number</label>
                    <input type="text" value="09181234567" id="emergencyPhone">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeEditModal()">Cancel</button>
            <button class="modal-btn-primary" onclick="saveProfile()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Save Changes
            </button>
        </div>
    </div>
</div>

<style>
.profile-avatar-lg { width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 20px; flex-shrink: 0; border: 2px solid rgba(255,255,255,0.3); }
.btn-edit-profile { padding: 9px 18px; border-radius: 9px; border: none; background: #fff; color: #15803d; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
.btn-edit-profile:hover { background: #f0fdf4; }

.profile-banner { align-items: center; }
.profile-banner-left { flex: 1; }
.profile-banner-info { display: flex; flex-direction: column; gap: 4px; }
.profile-banner-name-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.profile-banner-name { font-size: 18px; font-weight: 800; color: #fff; margin: 0; }
.profile-banner-sub { font-size: 12px; color: rgba(255,255,255,0.6); margin: 0; }
.profile-banner-badges { display: flex; gap: 8px; flex-wrap: wrap; }

.contract-alert { background: linear-gradient(135deg, #1a6e3c 0%, #15803d 100%); border-radius: 14px; padding: 18px 22px; display: flex; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.contract-alert-icon { width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.contract-alert-content { flex: 1; min-width: 180px; }
.contract-alert-content h4 { font-size: 13px; font-weight: 700; color: #fff; margin: 0 0 3px; }
.contract-alert-content p { font-size: 12px; color: rgba(255,255,255,0.8); margin: 0; }
.contract-alert-btn { padding: 8px 16px; font-size: 12px; background: #fff; color: #1a6e3c; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; }

.profile-tabs { display: flex; border-bottom: 1px solid #f0effe; padding: 0 20px; }
.profile-tab { background: none; border: none; padding: 13px 16px; font-size: 12px; font-weight: 600; color: #9999bb; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; font-family: 'Poppins', sans-serif; }
.profile-tab.active { color: #0b044d; border-bottom-color: #0b044d; }
.profile-tab:hover:not(.active) { color: #6b6a8a; }

.profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 24px; }
.profile-field { padding: 13px 0; border-bottom: 1px solid #f4f3ff; }
.profile-field span { display: block; font-size: 10.5px; color: #9999bb; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.profile-field strong { display: block; font-size: 13.5px; color: #0b044d; font-weight: 600; }
.profile-full { grid-column: 1 / -1; }

.note-box { background: #fefce8; padding: 12px 14px; border-radius: 8px; border: 1px solid #fde68a; }
.note-box-inner { display: flex; align-items: flex-start; gap: 10px; }
.note-box-inner svg { margin-top: 2px; flex-shrink: 0; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-field label { display: block; font-size: 11px; font-weight: 700; color: #9999bb; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.form-field input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1.5px solid #e5e4f0; font-size: 13px; font-family: 'Poppins', sans-serif; outline: none; }
.form-field input:focus { border-color: #15803d; }
.form-full { grid-column: 1 / -1; }

.hidden { display: none; }

@media (max-width: 640px) {
    .profile-banner { flex-direction: column; align-items: flex-start; gap: 14px; }
    .profile-banner-left { flex-direction: column; align-items: flex-start; gap: 12px; }
    .profile-avatar-lg { width: 48px; height: 48px; font-size: 17px; }
    .profile-banner-name { font-size: 16px; }
    .profile-banner-sub { font-size: 11px; }
    .btn-edit-profile { width: 100%; justify-content: center; padding: 9px 18px; }
    .contract-alert { padding: 14px 16px; gap: 12px; }
    .contract-alert-icon { width: 36px; height: 36px; }
    .profile-tabs { padding: 0 12px; overflow-x: auto; flex-wrap: nowrap; }
    .profile-tab { white-space: nowrap; padding: 11px 12px; font-size: 11px; }
    .profile-grid { grid-template-columns: 1fr; gap: 0; }
    .profile-full { grid-column: 1; }
    .form-grid { grid-template-columns: 1fr; }
    .form-full { grid-column: 1; }
}

@media (max-width: 480px) {
    .profile-banner-badges .banner-badge.outline:last-child { display: none; }
    .contract-alert-content h4 { font-size: 12px; }
    .contract-alert-content p { font-size: 11px; }
    .contract-alert-btn { width: 100%; text-align: center; }
    .profile-field strong { font-size: 13px; }
    .note-box { padding: 10px 12px; }
}
</style>

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
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        document.querySelectorAll('.profile-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function openEditModal() {
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function saveProfile() {
        const now = new Date().toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', hour12:true }) +
                    ', ' + new Date().toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
        document.querySelector('#tab-personal .profile-field:nth-child(4) strong').textContent = document.getElementById('contact').value;
        document.querySelector('#tab-personal .profile-field:nth-child(5) strong').textContent = document.getElementById('email').value;
        document.querySelector('#tab-personal .profile-field:nth-child(6) strong').textContent = document.getElementById('address').value;
        document.querySelector('#tab-emergency .profile-field:nth-child(1) strong').textContent = document.getElementById('emergencyContact').value;
        document.querySelector('#tab-emergency .profile-field:nth-child(2) strong').textContent = document.getElementById('emergencyRelation').value;
        document.querySelector('#tab-emergency .profile-field:nth-child(3) strong').textContent = document.getElementById('emergencyPhone').value;
        document.getElementById('profileSaveTime').textContent = now;
        closeEditModal();
        document.getElementById('profileSaveModal').style.display = 'flex';
    }

    function closeProfileSaveModal() {
        document.getElementById('profileSaveModal').style.display = 'none';
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeEditModal(); closeProfileSaveModal(); }
    });
</script>

@include('joborder.joborder-chatbot')

{{-- Save Success Modal --}}
<div id="profileSaveModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,4,77,0.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:1000;padding:20px;" onclick="closeProfileSaveModal()">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:400px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);animation:psUp 0.25s ease;" onclick="event.stopPropagation()">
        <div style="text-align:center;padding:32px 24px 20px;">
            <div style="width:56px;height:56px;border-radius:50%;background:#e8f9ef;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#0b044d;margin-bottom:6px;">Profile Updated!</h3>
            <p style="font-size:13px;color:#6b6a8a;margin-bottom:20px;">Your profile information has been saved successfully.</p>
            <div style="text-align:left;background:#f7f6ff;border-radius:12px;padding:14px 16px;">
                <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0effe;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Updated by</span><strong style="color:#0b044d;">Juan D. Cruz</strong></div>
                <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0effe;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Section</span><strong style="color:#0b044d;">Contact &amp; Emergency</strong></div>
                <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Saved at</span><strong id="profileSaveTime" style="color:#0b044d;"></strong></div>
            </div>
        </div>
        <div style="padding:0 24px 24px;">
            <button onclick="closeProfileSaveModal()" style="width:100%;padding:10px 20px;border-radius:9px;border:none;background:#15803d;font-size:13px;font-weight:600;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;font-family:'Poppins',sans-serif;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>
    </div>
</div>
<style>@keyframes psUp{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}</style>

@endsection
