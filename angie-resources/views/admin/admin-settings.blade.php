@extends('layouts.app')

@section('title', 'Settings · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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

    @include('admin.admin-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('admin.admin-notification')

<style>
    :root { --primary: #0b044d; --accent: #15803d; }
    .settings-container { display: flex; gap: 20px; }
    .settings-sidebar { width: 260px; flex-shrink: 0; }
    .settings-content { flex: 1; }
    .settings-profile-card { background: #fff; border-radius: 14px; padding: 20px; border: 1.5px solid #e5e4f0; text-align: center; margin-bottom: 16px; }
    .settings-profile-avatar { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #0b044d, #2d1a8e); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; margin: 0 auto 12px; }
    .settings-profile-name { font-size: 16px; font-weight: 700; color: #0b044d; margin: 0 0 4px; }
    .settings-profile-role { font-size: 13px; color: #6b6a8a; margin: 0 0 16px; }
    .settings-profile-info { text-align: left; border-top: 1px solid #e5e4f0; padding-top: 12px; }
    .settings-profile-info-item { margin-bottom: 10px; }
    .settings-profile-info-label { font-size: 10px; font-weight: 600; color: #9999bb; margin: 0 0 2px; letter-spacing: 0.5px; }
    .settings-profile-info-value { font-size: 12px; font-weight: 600; color: #0b044d; margin: 0; }
    .settings-profile-info-item.pending .settings-profile-info-value { color: #d9bb00; }
    .settings-nav { background: #fff; border-radius: 14px; padding: 8px; border: 1.5px solid #e5e4f0; margin-bottom: 16px; }
    .settings-nav-item { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 12px; border: none; background: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; color: #6b6a8a; transition: all 0.15s; text-align: left; position: relative; }
    .settings-nav-item:hover { background: #f8f7fc; }
    .settings-nav-item.active { background: #0b044d; color: #fff; }
    .settings-nav-item.active .settings-nav-icon svg { stroke: #fff; }
    .settings-nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }
    .settings-nav-icon svg { width: 16px; height: 16px; }
    .settings-nav-badge { margin-left: auto; background: #d9bb00; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; }
    .settings-nav-arrow { position: absolute; right: 12px; }
    .settings-tip { background: #f7f6ff; border-radius: 12px; padding: 14px; border: 1.5px solid #e5e4f0; }
    .settings-tip-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .settings-tip-icon { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
    .settings-tip-title { font-size: 10px; font-weight: 700; color: #6b3fa0; margin: 0; letter-spacing: 0.5px; }
    .settings-tip-text { font-size: 12px; color: #6b6a8a; margin: 0; line-height: 1.5; }
    .settings-section { background: #fff; border-radius: 14px; padding: 20px; border: 1.5px solid #e5e4f0; margin-bottom: 16px; }
    .settings-section-title { font-size: 14px; font-weight: 700; color: #0b044d; margin: 0 0 16px; }
    .settings-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0eff8; }
    .settings-row:last-of-type { border-bottom: none; }
    .settings-row-label { flex: 1; }
    .settings-row-title { font-size: 13px; font-weight: 600; color: #0b044d; margin: 0 0 2px; }
    .settings-row-desc { font-size: 11.5px; color: #9999bb; margin: 0; }
    .settings-row-control { flex-shrink: 0; }
    .settings-toggle { width: 44px; height: 24px; border-radius: 12px; background: #e5e4f0; border: none; cursor: pointer; position: relative; transition: all 0.2s; }
    .settings-toggle.active { background: #15803d; }
    .settings-toggle-thumb { position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 50%; background: #fff; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .settings-toggle.active .settings-toggle-thumb { left: 22px; }
    .settings-select { padding: 8px 12px; border: 1.5px solid #e5e4f0; border-radius: 8px; font-size: 13px; background: #fff; min-width: 140px; }
    .settings-form-wrapper { background: #f8f7fc; border-radius: 10px; padding: 16px; margin-top: 16px; }
    .settings-avatar-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .settings-avatar { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #0b044d, #2d1a8e); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; }
    .settings-avatar-name { font-size: 14px; font-weight: 700; color: #0b044d; margin: 0 0 2px; }
    .settings-avatar-role { font-size: 12px; color: #6b6a8a; margin: 0; }
    .settings-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .settings-form-field { }
    .settings-form-field label { display: block; font-size: 11.5px; font-weight: 600; color: #9999bb; margin-bottom: 6px; }
    .settings-form-field input { width: 100%; padding: 10px 12px; border: 1.5px solid #e5e4f0; border-radius: 8px; font-size: 13px; box-sizing: border-box; }
    .settings-form-field-full { grid-column: span 2; }
    .settings-save-bar { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
    .settings-btn-reset { padding: 8px 16px; border: 1.5px solid #e5e4f0; border-radius: 8px; background: #fff; font-size: 13px; font-weight: 600; color: #6b6a8a; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .settings-btn-reset:hover { background: #f8f7fc; border-color: #d0cfe5; box-shadow: 0 2px 6px rgba(0,0,0,0.12); }
    .settings-btn-save { display: flex; align-items: center; gap: 6px; padding: 8px 16px; border: none; border-radius: 8px; background: #0b044d; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 6px rgba(11,4,77,0.25); }
    .settings-btn-save:hover { background: #1a0d7a; box-shadow: 0 4px 12px rgba(11,4,77,0.35); transform: translateY(-1px); }
    .settings-btn-save.saved { background: #15803d; box-shadow: 0 2px 6px rgba(21,128,61,0.25); }
    .settings-btn-primary { display: flex; align-items: center; gap: 6px; padding: 10px 20px; border: none; border-radius: 8px; background: #0b044d; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; margin-top: 12px; transition: all 0.2s; box-shadow: 0 2px 6px rgba(11,4,77,0.25); }
    .settings-btn-primary:hover { background: #1a0d7a; box-shadow: 0 4px 12px rgba(11,4,77,0.35); transform: translateY(-1px); }
    .settings-message { font-size: 12px; padding: 8px 12px; border-radius: 6px; margin: 8px 0; }
    .settings-message.success { background: #f0fdf4; color: #15803d; }
    .settings-message.error { background: #fef2f2; color: #8e1e18; }
    .requests-section { margin-bottom: 24px; }
    .requests-section-title { font-size: 14px; font-weight: 700; color: #0b044d; margin: 0 0 12px; display: flex; align-items: center; gap: 8px; }
    .requests-badge { background: #d9bb00; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
    .requests-list { display: flex; flex-direction: column; gap: 10px; }
    .request-card { display: flex; align-items: center; gap: 12px; background: #fff; border: 1.5px solid #e5e4f0; border-radius: 12px; padding: 14px; }
    .request-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 700; flex-shrink: 0; }
    .request-info { flex: 1; }
    .request-name { font-size: 13px; font-weight: 700; color: #0b044d; margin: 0 0 2px; }
    .request-details { font-size: 12px; color: #6b6a8a; margin: 0 0 6px; }
    .request-badges { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .request-badge { font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 6px; }
    .request-meta { font-size: 10px; color: #9999bb; }
    .request-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .request-btn-reject { padding: 6px 12px; border: 1.5px solid #e5e4f0; border-radius: 6px; background: #fff; font-size: 12px; font-weight: 600; color: #8e1e18; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .request-btn-reject:hover { background: #fef2f2; border-color: #8e1e18; box-shadow: 0 2px 6px rgba(142,30,24,0.15); }
    .request-btn-approve { padding: 6px 12px; border: none; border-radius: 6px; background: #15803d; font-size: 12px; font-weight: 600; color: #fff; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 6px rgba(21,128,61,0.25); }
    .request-btn-approve:hover { background: #1a6e3c; box-shadow: 0 4px 10px rgba(21,128,61,0.35); transform: translateY(-1px); }
    .request-status { font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px; flex-shrink: 0; }
    .request-status.approved { background: #f0fdf4; color: #15803d; }
    .request-status.rejected { background: #fef2f2; color: #8e1e18; }
    .requests-empty { text-align: center; padding: 30px; background: #f8f7fc; border-radius: 12px; }
    .requests-empty-icon { font-size: 32px; margin: 0 0 8px; }
    .requests-empty-title { font-size: 14px; font-weight: 700; color: #0b044d; margin: 0 0 4px; }
    .requests-empty-desc { font-size: 12px; color: #9999bb; margin: 0; }
    @media (max-width: 900px) {
        .settings-container { flex-direction: column; }
        .settings-sidebar { width: 100%; }
        .settings-nav { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
        .settings-nav-arrow { display: none; }
    }
    @media (max-width: 640px) {
        .settings-form-grid { grid-template-columns: 1fr; }
        .settings-form-field-full { grid-column: span 1; }
        .settings-avatar-row { flex-direction: column; text-align: center; }
        .request-card { flex-direction: column; align-items: flex-start; }
        .request-actions { width: 100%; justify-content: stretch; }
        .request-btn-reject, .request-btn-approve { flex: 1; }
        .settings-row { flex-direction: column; align-items: flex-start; gap: 10px; }
        .settings-row-control { width: 100%; display: flex; justify-content: flex-start; }
    }
    @media (max-width: 480px) {
        .settings-profile-card { padding: 16px; }
        .settings-section { padding: 16px; }
        .settings-form-wrapper { padding: 12px; }
        .settings-nav { grid-template-columns: 1fr; }
    }
</style>

<div class="settings-container">
    <div class="settings-sidebar">
        <div class="settings-profile-card">
            <div class="settings-profile-avatar">AD</div>
            <h3 class="settings-profile-name">Admin User</h3>
            <p class="settings-profile-role">HR Staff</p>
            <div class="settings-profile-info">
                <div class="settings-profile-info-item">
                    <p class="settings-profile-info-label">ROLE</p>
                    <p class="settings-profile-info-value">Administrator</p>
                </div>
                <div class="settings-profile-info-item">
                    <p class="settings-profile-info-label">DEPARTMENT</p>
                    <p class="settings-profile-info-value">Human Resource Mgmt</p>
                </div>
                <div class="settings-profile-info-item pending">
                    <p class="settings-profile-info-label">PENDING</p>
                    <p class="settings-profile-info-value">3 requests</p>
                </div>
            </div>
        </div>

        <div class="settings-nav">
            <button class="settings-nav-item active" onclick="setSettingsTab('profile')">
                <span class="settings-nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                <span class="settings-nav-label">Profile</span>
                <svg class="settings-nav-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <button class="settings-nav-item" onclick="setSettingsTab('system')">
                <span class="settings-nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
                <span class="settings-nav-label">System</span>
            </button>
            <button class="settings-nav-item" onclick="setSettingsTab('security')">
                <span class="settings-nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                <span class="settings-nav-label">Security</span>
            </button>
            <button class="settings-nav-item" onclick="setSettingsTab('notifications')">
                <span class="settings-nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>
                <span class="settings-nav-label">Notifications</span>
            </button>
            <button class="settings-nav-item" onclick="setSettingsTab('requests')">
                <span class="settings-nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg></span>
                <span class="settings-nav-label">Account Requests</span>
                <span class="settings-nav-badge">3</span>
            </button>
        </div>

        <div class="settings-tip">
            <div class="settings-tip-header">
                <span class="settings-tip-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b3fa0" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                <p class="settings-tip-title">SECURITY TIP</p>
            </div>
            <p class="settings-tip-text">Enable 2FA and use a strong password to protect your admin account.</p>
        </div>
    </div>

    <div class="settings-content">
        <!-- Profile Tab -->
        <div id="profileTab">
            <div class="settings-section">
                <h3 class="settings-section-title">Account Information</h3>
                <div class="settings-form-wrapper">
                    <div class="settings-avatar-row">
                        <div class="settings-avatar">AD</div>
                        <div>
                            <p class="settings-avatar-name">Admin User</p>
                            <p class="settings-avatar-role">HR Staff · Human Resource Management Office</p>
                        </div>
                    </div>
                    <div class="settings-form-grid">
                        <div class="settings-form-field">
                            <label>First Name</label>
                            <input type="text" value="Admin" />
                        </div>
                        <div class="settings-form-field">
                            <label>Last Name</label>
                            <input type="text" value="User" />
                        </div>
                        <div class="settings-form-field">
                            <label>Email Address</label>
                            <input type="email" value="admin@pagsanjan.gov.ph" />
                        </div>
                        <div class="settings-form-field">
                            <label>Contact No.</label>
                            <input type="text" value="09171234567" />
                        </div>
                        <div class="settings-form-field settings-form-field-full">
                            <label>Department / Office</label>
                            <input type="text" value="Human Resource Management Office" />
                        </div>
                    </div>
                    <div class="settings-save-bar">
                        <button class="settings-btn-reset" onclick="resetProfileForm()">Reset</button>
                        <button class="settings-btn-save" onclick="saveProfile()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Tab -->
        <div id="systemTab" style="display: none;">
            <div class="settings-section">
                <h3 class="settings-section-title">General Settings</h3>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Fiscal Year</p>
                        <p class="settings-row-desc">Current active fiscal year</p>
                    </div>
                    <div class="settings-row-control">
                        <select class="settings-select" id="fiscalYear">
                            <option>2024</option>
                            <option selected>2025</option>
                            <option>2026</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Pay Period</p>
                        <p class="settings-row-desc">Default payroll cycle</p>
                    </div>
                    <div class="settings-row-control">
                        <select class="settings-select" id="payPeriod">
                            <option>Monthly</option>
                            <option selected>Semi-Monthly</option>
                            <option>Weekly</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Currency</p>
                        <p class="settings-row-desc">Display currency for payroll</p>
                    </div>
                    <div class="settings-row-control">
                        <select class="settings-select" id="currency">
                            <option selected>PHP</option>
                            <option>USD</option>
                        </select>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Date Format</p>
                        <p class="settings-row-desc">How dates are displayed</p>
                    </div>
                    <div class="settings-row-control">
                        <select class="settings-select" id="dateFormat">
                            <option selected>MM/DD/YYYY</option>
                            <option>DD/MM/YYYY</option>
                            <option>YYYY-MM-DD</option>
                        </select>
                    </div>
                </div>
                <div class="settings-form-wrapper">
                    <div class="settings-save-bar">
                        <button class="settings-btn-reset" onclick="resetSystemForm()">Reset</button>
                        <button class="settings-btn-save" onclick="saveSystem()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Tab -->
        <div id="securityTab" style="display: none;">
            <div class="settings-section">
                <h3 class="settings-section-title">Change Password</h3>
                <div class="settings-form-wrapper">
                    <div class="settings-form-field settings-form-field-full">
                        <label>Current Password</label>
                        <input type="password" id="currentPw" placeholder="••••••••" />
                    </div>
                    <div class="settings-form-field settings-form-field-full">
                        <label>New Password</label>
                        <input type="password" id="newPw" placeholder="••••••••" />
                    </div>
                    <div class="settings-form-field settings-form-field-full">
                        <label>Confirm New Password</label>
                        <input type="password" id="confirmPw" placeholder="••••••••" />
                    </div>
                    <p class="settings-message" id="pwMessage" style="display: none;"></p>
                    <button class="settings-btn-primary" onclick="changePassword()">Change Password</button>
                </div>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title">Access Control</h3>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Two-Factor Authentication</p>
                        <p class="settings-row-desc">Require OTP on login</p>
                    </div>
                    <div class="settings-row-control">
                        <button class="settings-toggle" id="twoFAToggle" onclick="toggleTwoFA()">
                            <span class="settings-toggle-thumb"></span>
                        </button>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Session Timeout</p>
                        <p class="settings-row-desc">Auto-logout after inactivity</p>
                    </div>
                    <div class="settings-row-control">
                        <select class="settings-select" id="sessionTimeout">
                            <option value="15">15 minutes</option>
                            <option value="30" selected>30 minutes</option>
                            <option value="60">1 hour</option>
                            <option value="120">2 hours</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Tab -->
        <div id="notificationsTab" style="display: none;">
            <div class="settings-section">
                <h3 class="settings-section-title">In-App Notifications</h3>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Payroll Approval</p>
                        <p class="settings-row-desc">Notify when payroll records need approval</p>
                    </div>
                    <div class="settings-row-control">
                        <button class="settings-toggle active" onclick="toggleNotification(this)"><span class="settings-toggle-thumb"></span></button>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Leave Requests</p>
                        <p class="settings-row-desc">Notify when employees file leave requests</p>
                    </div>
                    <div class="settings-row-control">
                        <button class="settings-toggle active" onclick="toggleNotification(this)"><span class="settings-toggle-thumb"></span></button>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">New Employee</p>
                        <p class="settings-row-desc">Notify when a new employee account is created</p>
                    </div>
                    <div class="settings-row-control">
                        <button class="settings-toggle active" onclick="toggleNotification(this)"><span class="settings-toggle-thumb"></span></button>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">DTR Deadline</p>
                        <p class="settings-row-desc">Remind before DTR submission deadline</p>
                    </div>
                    <div class="settings-row-control">
                        <button class="settings-toggle active" onclick="toggleNotification(this)"><span class="settings-toggle-thumb"></span></button>
                    </div>
                </div>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">System Alerts</p>
                        <p class="settings-row-desc">Critical system and security alerts</p>
                    </div>
                    <div class="settings-row-control">
                        <button class="settings-toggle" onclick="toggleNotification(this)"><span class="settings-toggle-thumb"></span></button>
                    </div>
                </div>
            </div>
            <div class="settings-section">
                <h3 class="settings-section-title">Email Notifications</h3>
                <div class="settings-row">
                    <div class="settings-row-label">
                        <p class="settings-row-title">Email Digest</p>
                        <p class="settings-row-desc">Receive a daily summary via email</p>
                    </div>
                    <div class="settings-row-control">
                        <button class="settings-toggle active" onclick="toggleNotification(this)"><span class="settings-toggle-thumb"></span></button>
                    </div>
                </div>
                <div class="settings-form-wrapper">
                    <div class="settings-save-bar">
                        <button class="settings-btn-reset" onclick="resetNotificationsForm()">Reset</button>
                        <button class="settings-btn-save" onclick="saveSettings('notifications')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Tab -->
        <div id="requestsTab" style="display: none;">
            <div class="requests-section">
                <p class="requests-section-title">Pending Requests <span class="requests-badge">3</span></p>
                <div class="requests-list">
                    <div class="request-card">
                        <div class="request-avatar" style="background: #8e1e18;">MJ</div>
                        <div class="request-info">
                            <p class="request-name">Maria Jane R. Reyes</p>
                            <p class="request-details">Administrative Aide III · mjreyes@pagsanjan.gov.ph</p>
                            <div class="request-badges">
                                <span class="request-badge" style="background: #8e1e1815; color: #8e1e18;">Permanent</span>
                                <span class="request-meta">PGS-0421</span>
                                <span class="request-meta">Submitted Jun 20, 2025</span>
                            </div>
                        </div>
                        <div class="request-actions">
                            <button class="request-btn-reject" onclick="rejectRequest('1')">Reject</button>
                            <button class="request-btn-approve" onclick="approveRequest('1')">Approve</button>
                        </div>
                    </div>
                    <div class="request-card">
                        <div class="request-avatar" style="background: #1a6e3c;">JC</div>
                        <div class="request-info">
                            <p class="request-name">John Carlo S. Mendoza</p>
                            <p class="request-details">Job Order Driver · jcmendoza@pagsanjan.gov.ph</p>
                            <div class="request-badges">
                                <span class="request-badge" style="background: #1a6e3c15; color: #1a6e3c;">Job Order</span>
                                <span class="request-meta">PGS-JO-089</span>
                                <span class="request-meta">Submitted Jun 18, 2025</span>
                            </div>
                        </div>
                        <div class="request-actions">
                            <button class="request-btn-reject" onclick="rejectRequest('2')">Reject</button>
                            <button class="request-btn-approve" onclick="approveRequest('2')">Approve</button>
                        </div>
                    </div>
                    <div class="request-card">
                        <div class="request-avatar" style="background: #0b044d;">LA</div>
                        <div class="request-info">
                            <p class="request-name">Lisa Ann T. Garcia</p>
                            <p class="request-details">HR Admin · lagarcia@pagsanjan.gov.ph</p>
                            <div class="request-badges">
                                <span class="request-badge" style="background: #0b044d15; color: #0b044d;">Admin</span>
                                <span class="request-meta">PGS-ADM-012</span>
                                <span class="request-meta">Submitted Jun 15, 2025</span>
                            </div>
                        </div>
                        <div class="request-actions">
                            <button class="request-btn-reject" onclick="rejectRequest('3')">Reject</button>
                            <button class="request-btn-approve" onclick="approveRequest('3')">Approve</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setSettingsTab(tab) {
    document.querySelectorAll('.settings-nav-item').forEach(item => item.classList.remove('active'));
    event.target.closest('.settings-nav-item').classList.add('active');
    ['profile', 'system', 'security', 'notifications', 'requests'].forEach(t => {
        document.getElementById(t + 'Tab').style.display = t === tab ? 'block' : 'none';
    });
}

function toggleNotification(btn) { btn.classList.toggle('active'); }
function toggleTwoFA() { document.getElementById('twoFAToggle').classList.toggle('active'); }

function showSettingsModal(section, isSuccess, title, msg) {
    const colors = { success: { bg: '#e8f9ef', stroke: '#15803d', btn: '#15803d' }, error: { bg: '#fdf0ef', stroke: '#8e1e18', btn: '#8e1e18' } };
    const c = colors[isSuccess ? 'success' : 'error'];
    const now = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true }) + ', ' +
                new Date().toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('smIcon').style.background = c.bg;
    document.getElementById('smIcon').innerHTML = isSuccess
        ? '<svg width="28" height="28" fill="none" stroke="' + c.stroke + '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>'
        : '<svg width="28" height="28" fill="none" stroke="' + c.stroke + '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    document.getElementById('smTitle').textContent = title;
    document.getElementById('smMsg').textContent = msg;
    document.getElementById('smSection').textContent = section;
    document.getElementById('smTime').textContent = now;
    document.getElementById('smBtn').style.background = c.btn;
    const modal = document.getElementById('settingsModal');
    modal.style.display = 'flex';
}

function closeSettingsModal() {
    document.getElementById('settingsModal').style.display = 'none';
}

function saveSettings(section) {
    const labels = { profile: 'Account Information', system: 'System Settings', notifications: 'Notification Preferences' };
    showSettingsModal(labels[section] || section, true, 'Settings Saved!', 'Your ' + (labels[section] || section).toLowerCase() + ' have been saved successfully.');
}

function saveProfile() {
    saveSettings('profile');
}

function saveSystem() {
    saveSettings('system');
}

function resetProfileForm() {
    document.querySelectorAll('#profileTab input').forEach(input => { input.value = input.defaultValue; });
}

function resetSystemForm() {
    document.getElementById('fiscalYear').value = '2025';
    document.getElementById('payPeriod').value = 'Semi-Monthly';
    document.getElementById('currency').value = 'PHP';
    document.getElementById('dateFormat').value = 'MM/DD/YYYY';
}

function resetNotificationsForm() {
    document.querySelectorAll('#notificationsTab .settings-toggle').forEach((t, i) => {
        if (i < 4) t.classList.add('active'); else t.classList.remove('active');
    });
}

function changePassword() {
    const current = document.getElementById('currentPw').value;
    const newPw   = document.getElementById('newPw').value;
    const confirm = document.getElementById('confirmPw').value;
    const msg     = document.getElementById('pwMessage');

    if (!current || !newPw || !confirm) {
        msg.style.display = 'block';
        msg.className = 'settings-message error';
        msg.textContent = 'Please fill in all password fields.';
        return;
    }
    if (newPw.length < 8) {
        msg.style.display = 'block';
        msg.className = 'settings-message error';
        msg.textContent = 'New password must be at least 8 characters.';
        return;
    }
    if (newPw !== confirm) {
        msg.style.display = 'block';
        msg.className = 'settings-message error';
        msg.textContent = 'New password and confirmation do not match.';
        return;
    }
    msg.style.display = 'none';
    document.getElementById('currentPw').value = '';
    document.getElementById('newPw').value = '';
    document.getElementById('confirmPw').value = '';
    showSettingsModal('Password', true, 'Password Changed!', 'Your password has been updated successfully.');
}

function approveRequest(id) {
    const card = document.querySelector('[onclick="approveRequest(\'' + id + '\''  + ')"]').closest('.request-card');
    card.querySelector('.request-actions').innerHTML = '<span class="request-status approved">Approved</span>';
    showSettingsModal('Account Requests', true, 'Request Approved!', 'The account request has been approved successfully.');
}

function rejectRequest(id) {
    const card = document.querySelector('[onclick="rejectRequest(\'' + id + '\''  + ')"]').closest('.request-card');
    card.querySelector('.request-actions').innerHTML = '<span class="request-status rejected">Rejected</span>';
    showSettingsModal('Account Requests', false, 'Request Rejected', 'The account request has been rejected.');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSettingsModal(); });
</script>
    </main>

    @include('admin.admin-chatbot')

{{-- Settings Success Modal --}}
<div id="settingsModal" onclick="closeSettingsModal()" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,4,77,0.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:1000;padding:20px;">
    <div onclick="event.stopPropagation()" style="background:#fff;border-radius:16px;width:100%;max-width:400px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);animation:smUp 0.25s ease;">
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
            <button onclick="closeSettingsModal()" id="smBtn" style="width:100%;padding:10px 20px;border-radius:9px;border:none;font-size:13px;font-weight:600;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;font-family:'Poppins',sans-serif;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>
    </div>
</div>
<style>@keyframes smUp{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}</style>

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

    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        logoText.style.display  = collapsed ? 'none' : '';
        navLabel.style.display  = collapsed ? 'none' : '';
        userInfo.style.display  = collapsed ? 'none' : '';
        sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
    });

    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
</script>
@endsection