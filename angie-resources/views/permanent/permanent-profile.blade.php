@extends('layouts.app')

@section('title', 'Profile · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<style>
        .profile-header { background: linear-gradient(135deg, #0b044d 0%, #2d1a8e 100%); border-radius: 16px; padding: 24px 28px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
        .profile-header-left { display: flex; align-items: center; gap: 20px; flex: 1; min-width: 280px; }
        .profile-avatar { width: 72px; height: 72px; border-radius: 50%; background: #8e1e18; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 26px; flex-shrink: 0; }
        .profile-info h2 { font-size: 20px; font-weight: 800; color: #ffffff; margin: 0 0 6px; }
        .profile-info p { font-size: 13px; color: rgba(255,255,255,0.65); margin-bottom: 10px; }
        .btn-edit-profile { padding: 9px 20px; border-radius: 9px; border: none; background: #fff; color: #0b044d; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; white-space: nowrap; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 14px; padding: 18px; border: 1.5px solid #e5e4f0; }
        .stat-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .stat-label { font-size: 12px; color: #9999bb; font-weight: 600; }
        .stat-icon-wrap { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .stat-value { font-size: 22px; font-weight: 800; color: #0b044d; margin: 0 0 6px; }
        .stat-footer { display: flex; align-items: center; gap: 6px; }
        .stat-dot { width: 6px; height: 6px; border-radius: 50%; }
        .stat-sub { font-size: 11px; color: #9999bb; margin: 0; }
        
        .table-section { background: #fff; border-radius: 14px; border: 1.5px solid #e5e4f0; margin-bottom: 20px; overflow: hidden; }
        .table-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #e5e4f0; }
        .table-title { font-size: 14px; font-weight: 700; color: #0b044d; margin: 0 0 2px; }
        .table-sub { font-size: 12px; color: #9999bb; margin: 0; }
        
        .pmodal-tabs { display: flex; border-bottom: 1px solid #f0effe; padding: 0 24px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .pmodal-tabs::-webkit-scrollbar { height: 2px; }
        .pmodal-tabs::-webkit-scrollbar-thumb { background: #e5e4f0; border-radius: 2px; }
        .pmodal-tab { background: none; border: none; padding: 14px 18px; font-size: 12px; font-weight: 600; color: #9999bb; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; white-space: nowrap; }
        .pmodal-tab.active { color: #0b044d; border-bottom-color: #0b044d; }
        
        .pmodal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .pmodal-field { padding: 14px 0; border-bottom: 1px solid #f4f3ff; }
        .pmodal-field span { display: block; font-size: 11px; color: #9999bb; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .pmodal-field strong { display: block; font-size: 14px; color: #0b044d; font-weight: 600; word-break: break-word; }
        .pmodal-field.pmodal-full { grid-column: 1 / -1; }
        
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(11, 4, 77, 0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.2s; padding: 20px; }
        .modal-overlay.show { opacity: 1; visibility: visible; }
        .modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow: hidden; transform: scale(0.95); transition: transform 0.2s; }
        .modal-overlay.show .modal-box { transform: scale(1); }
        .modal-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 24px; border-bottom: 1px solid #e5e4f0; }
        .modal-eyebrow { font-size: 10px; font-weight: 700; color: #9999bb; letter-spacing: 1px; text-transform: uppercase; }
        .modal-title { font-size: 18px; font-weight: 700; color: #0b044d; margin: 4px 0; }
        .modal-close { background: none; border: none; cursor: pointer; color: #9999bb; padding: 4px; }
        .modal-body { padding: 0 24px 20px; max-height: 60vh; overflow-y: auto; }
        .form-section-label { font-size: 10.5px; font-weight: 700; color: #aaa8cc; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: block; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-field { }
        .form-field label { display: block; font-size: 11px; font-weight: 700; color: #9999bb; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .form-field input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1.5px solid #e5e4f0; font-size: 13px; font-family: 'Poppins', sans-serif; }
        .form-field input:focus { outline: none; border-color: #0b044d; }
        .form-field.form-full { grid-column: 1 / -1; }
        .modal-footer { display: flex; justify-content: space-between; padding: 16px 24px; border-top: 1px solid #e5e4f0; gap: 10px; flex-wrap: wrap; }
        .modal-btn-ghost { padding: 10px 20px; border-radius: 9px; border: 1.5px solid #e5e4f0; background: #fff; font-size: 13px; font-weight: 600; color: #6b6a8a; cursor: pointer; }
        .modal-btn-primary { padding: 10px 20px; border-radius: 9px; border: none; background: #0b044d; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        
        .hidden { display: none; }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            
            .profile-header { padding: 20px; }
            .profile-header-left { flex-direction: column; align-items: flex-start; gap: 16px; min-width: 100%; }
            .profile-avatar { width: 60px; height: 60px; font-size: 22px; }
            .profile-info h2 { font-size: 18px; }
            .profile-info p { font-size: 12px; }
            .btn-edit-profile { width: 100%; justify-content: center; }
            
            .pmodal-grid { grid-template-columns: 1fr; gap: 0; }
            .pmodal-field.pmodal-full { grid-column: 1; }
            
            .pmodal-tabs { padding: 0 16px; }
            .pmodal-tab { padding: 12px 14px; font-size: 11px; }
            
            .table-section > div:last-child { padding: 20px 16px !important; }
            
            .form-grid { grid-template-columns: 1fr; gap: 12px; }
            .form-field.form-full { grid-column: 1; }
            
            .modal-header { padding: 16px 20px; }
            .modal-body { padding: 0 20px 16px; }
            .modal-footer { padding: 12px 20px; }
            .modal-title { font-size: 16px; }
            
            .modal-btn-ghost, .modal-btn-primary { flex: 1; justify-content: center; }
        }
        
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; gap: 12px; }
            
            .stat-card { padding: 16px; }
            .stat-value { font-size: 20px; }
            
            .profile-header { padding: 16px; }
            .profile-avatar { width: 52px; height: 52px; font-size: 20px; }
            .profile-info h2 { font-size: 16px; }
            
            .table-title { font-size: 13px; }
            .table-sub { font-size: 11px; }
            
            .pmodal-tab { padding: 10px 12px; font-size: 10px; }
            .pmodal-field strong { font-size: 13px; }
            
            .modal-box { max-width: 100%; border-radius: 12px; }
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

    @include('permanent.permanent-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('permanent.permanent-notification')
            <div class="profile-header">
                <div class="profile-header-left">
                    <div class="profile-avatar">AR</div>
                    <div class="profile-info">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
                            <h2>Ana R. Reyes</h2>
                            <span class="banner-badge"><span class="banner-badge-dot"></span>Active</span>
                        </div>
                        <p>Nurse II · Municipal Health Office</p>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <span class="banner-badge outline">Permanent</span>
                            <span class="banner-badge outline">PGS-0115</span>
                            <span class="banner-badge outline">Hired: Jan 15, 2018</span>
                        </div>
                    </div>
                </div>
                <button class="btn-edit-profile" onclick="openEditModal()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profile
                </button>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Years of Service</p>
                        <div class="stat-icon-wrap" style="background:#0b044d15"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                    </div>
                    <h2 class="stat-value">7.5</h2>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#0b044d"></span>
                        <p class="stat-sub">Since Jan 2018</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Performance Rating</p>
                        <div class="stat-icon-wrap" style="background:#15803d15"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                    </div>
                    <h2 class="stat-value">4.9</h2>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#15803d"></span>
                        <p class="stat-sub">Latest evaluation</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Leave Balance</p>
                        <div class="stat-icon-wrap" style="background:#d9bb0015"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><path d="M12 2v6l3 3"/><circle cx="12" cy="12" r="10"/></svg></div>
                    </div>
                    <h2 class="stat-value">12</h2>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#d9bb00"></span>
                        <p class="stat-sub">Days remaining</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Trainings Completed</p>
                        <div class="stat-icon-wrap" style="background:#8e1e1815"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                    </div>
                    <h2 class="stat-value">8</h2>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#8e1e18"></span>
                        <p class="stat-sub">Total programs</p>
                    </div>
                </div>
            </div>
            
            <section class="table-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Profile Information</h3>
                        <p class="table-sub">View and manage your personal details</p>
                    </div>
                </div>
                
                <div class="pmodal-tabs">
                    <button class="pmodal-tab active" onclick="switchTab('personal', this)">Personal Info</button>
                    <button class="pmodal-tab" onclick="switchTab('employment', this)">Employment</button>
                    <button class="pmodal-tab" onclick="switchTab('government', this)">Government IDs</button>
                    <button class="pmodal-tab" onclick="switchTab('emergency', this)">Emergency Contact</button>
                </div>
                
                <div style="padding:28px 32px;">
                    <div id="tab-personal" class="tab-content">
                        <div class="pmodal-grid">
                            <div class="pmodal-field"><span>Full Name</span><strong>Ana R. Reyes</strong></div>
                            <div class="pmodal-field"><span>Gender</span><strong>Female</strong></div>
                            <div class="pmodal-field"><span>Date of Birth</span><strong>Jul 22, 1990</strong></div>
                            <div class="pmodal-field"><span>Contact No.</span><strong>09201122334</strong></div>
                            <div class="pmodal-field pmodal-full"><span>Email Address</span><strong>ana.reyes@pagsanjan.gov.ph</strong></div>
                            <div class="pmodal-field pmodal-full"><span>Address</span><strong>123 Rizal Street, Barangay Poblacion, Pagsanjan, Laguna</strong></div>
                        </div>
                    </div>
                    <div id="tab-employment" class="tab-content hidden">
                        <div class="pmodal-grid">
                            <div class="pmodal-field"><span>Employee ID</span><strong>PGS-0115</strong></div>
                            <div class="pmodal-field"><span>Employment Type</span><strong>Permanent</strong></div>
                            <div class="pmodal-field"><span>Date Hired</span><strong>Jan 15, 2018</strong></div>
                            <div class="pmodal-field"><span>Status</span><strong>Active</strong></div>
                            <div class="pmodal-field pmodal-full"><span>Position / Designation</span><strong>Nurse II</strong></div>
                            <div class="pmodal-field pmodal-full"><span>Department / Office</span><strong>Municipal Health Office</strong></div>
                        </div>
                    </div>
                    <div id="tab-government" class="tab-content hidden">
                        <div class="pmodal-grid">
                            <div class="pmodal-field"><span>GSIS No.</span><strong>3456789012</strong></div>
                            <div class="pmodal-field"><span>PhilHealth No.</span><strong>34-567890123-4</strong></div>
                            <div class="pmodal-field"><span>Pag-IBIG No.</span><strong>3456-7890-1234</strong></div>
                            <div class="pmodal-field"><span>TIN</span><strong>345-678-901</strong></div>
                        </div>
                    </div>
                    <div id="tab-emergency" class="tab-content hidden">
                        <div class="pmodal-grid">
                            <div class="pmodal-field"><span>Contact Person</span><strong>Roberto Reyes</strong></div>
                            <div class="pmodal-field"><span>Relationship</span><strong>Spouse</strong></div>
                            <div class="pmodal-field"><span>Phone Number</span><strong>09171234567</strong></div>
                        </div>
                    </div>
                </div>
            </section>
    </main>

</div>

@include('permanent.permanent-chatbot')
    
    <div class="modal-overlay" id="editModal">
        <div class="modal-box">
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
                <p class="form-section-label">CONTACT INFORMATION</p>
                <div class="form-grid">
                    <div class="form-field">
                        <label>Contact No.</label>
                        <input type="text" value="09201122334" id="contact">
                    </div>
                    <div class="form-field">
                        <label>Email Address</label>
                        <input type="email" value="ana.reyes@pagsanjan.gov.ph" id="email">
                    </div>
                    <div class="form-field form-full">
                        <label>Address</label>
                        <input type="text" value="123 Rizal Street, Barangay Poblacion, Pagsanjan, Laguna" id="address">
                    </div>
                </div>
                
                <p class="form-section-label" style="margin-top:20px;">EMERGENCY CONTACT</p>
                <div class="form-grid">
                    <div class="form-field">
                        <label>Contact Person</label>
                        <input type="text" value="Roberto Reyes" id="emergencyContact">
                    </div>
                    <div class="form-field">
                        <label>Relationship</label>
                        <input type="text" value="Spouse" id="emergencyRelation">
                    </div>
                    <div class="form-field">
                        <label>Phone Number</label>
                        <input type="text" value="09171234567" id="emergencyPhone">
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
{{-- Save Success Modal --}}
<div class="modal-overlay" id="saveSuccessModal" onclick="closeSaveSuccess()">
    <div class="modal-box" style="max-width:400px" onclick="event.stopPropagation()">
        <div class="modal-body" style="text-align:center;padding:32px 24px 20px;">
            <div style="width:56px;height:56px;border-radius:50%;background:#e8f9ef;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="modal-title" style="margin-bottom:6px;">Profile Updated!</h3>
            <p style="font-size:13px;color:#6b6a8a;margin-bottom:20px;">Your profile information has been saved successfully.</p>
            <div style="text-align:left;background:#f7f6ff;border-radius:12px;padding:14px 16px;">
                <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0effe;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Updated by</span><strong style="color:#0b044d;">Ana R. Reyes</strong></div>
                <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0effe;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Section</span><strong style="color:#0b044d;">Contact &amp; Emergency</strong></div>
                <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;"><span style="color:#6b6a8a;font-weight:600;">Date &amp; Time</span><strong id="saveTimestamp" style="color:#0b044d;">—</strong></div>
            </div>
        </div>
        <div class="modal-footer" style="justify-content:center;padding-top:0;">
            <button class="modal-btn-primary" style="width:100%;justify-content:center;" onclick="closeSaveSuccess()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>
    </div>
</div>
<script>
    const sidebar      = document.getElementById('sidebar');
    const toggleBtn    = document.getElementById('toggle-btn');
    const logoText     = document.getElementById('logo-text');
    const navLabel     = document.getElementById('nav-label');
    const userInfo     = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn    = document.getElementById('mobile-menu-btn');
    const overlay      = document.getElementById('mobile-overlay');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');
            toggleBtn.textContent = collapsed ? '›' : '‹';
            if (logoText) logoText.style.display  = collapsed ? 'none' : '';
            if (navLabel) navLabel.style.display  = collapsed ? 'none' : '';
            if (userInfo) userInfo.style.display  = collapsed ? 'none' : '';
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
            document.querySelectorAll('.pmodal-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
    }
    
    function openEditModal() {
            document.getElementById('editModal').classList.add('show');
    }
    
    function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
    }
    
    function saveProfile() {
        const now = new Date().toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });

        // Update displayed values in the profile view
        document.querySelector('#tab-personal .pmodal-field:nth-child(4) strong').textContent = document.getElementById('contact').value;
        document.querySelector('#tab-personal .pmodal-field:nth-child(5) strong').textContent = document.getElementById('email').value;
        document.querySelector('#tab-personal .pmodal-field:nth-child(6) strong').textContent = document.getElementById('address').value;
        document.querySelector('#tab-emergency .pmodal-field:nth-child(1) strong').textContent = document.getElementById('emergencyContact').value;
        document.querySelector('#tab-emergency .pmodal-field:nth-child(2) strong').textContent = document.getElementById('emergencyRelation').value;
        document.querySelector('#tab-emergency .pmodal-field:nth-child(3) strong').textContent = document.getElementById('emergencyPhone').value;

        document.getElementById('saveTimestamp').textContent = now;
        closeEditModal();
        document.getElementById('saveSuccessModal').classList.add('show');
    }

    function closeSaveSuccess() {
        document.getElementById('saveSuccessModal').classList.remove('show');
    }
    
    document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
    });
</script>
@endsection