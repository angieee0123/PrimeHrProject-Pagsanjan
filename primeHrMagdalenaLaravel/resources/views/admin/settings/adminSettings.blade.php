@extends('layouts.app')

@push('styles')
    @vite('resources/css/admin/adminSettings.css')
@endpush

@section('content')

@include('admin.topbar.settingsTopbar')
@include('admin.notification.adminNotification')

@php
    $fullName = trim(($employee->first_name ?? null) . ' ' . ($employee->last_name ?? null)) ?: $user->name;
    $initials = strtoupper(substr($employee->first_name ?? $user->name, 0, 1) . substr($employee->last_name ?? '', 0, 1));
    $roleLabel = $user->hasRole('admin') ? 'Administrator' : ($user->hasRole('hr') ? 'HR Staff' : ucfirst($user->roles[0] ?? 'Staff'));
    $position = $employee->employmentDetail->designationRelation->title ?? null;
    $department = $employee->employmentDetail->departmentRelation->name ?? null;
@endphp

<div class="glass-shell">
<div class="settings-container">
    <div class="settings-sidebar">
        <div class="settings-profile-card">
            <div class="settings-profile-avatar" id="sidebarAvatar">
                @if($employee?->photo)
                    <img src="{{ $employee->photo }}" alt="" class="settings-avatar-img">
                @else
                    <span id="sidebarAvatarInitials">{{ $initials }}</span>
                @endif
            </div>
            <h3 class="settings-profile-name">{{ $fullName }}</h3>
            <p class="settings-profile-role">{{ $employee->employee_id ?? $user->username ?? $roleLabel }}</p>
            <div class="settings-profile-info">
                <div class="settings-profile-info-item">
                    <p>ROLE</p>
                    <p>{{ $roleLabel }}</p>
                </div>
                @if($position)
                <div class="settings-profile-info-item">
                    <p>POSITION</p>
                    <p>{{ $position }}</p>
                </div>
                @endif
                @if($department)
                <div class="settings-profile-info-item">
                    <p>DEPARTMENT</p>
                    <p>{{ $department }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="settings-nav">
            <button class="settings-nav-item active" onclick="switchSettingsTab('profile', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Profile</span>
            </button>
            <button class="settings-nav-item" onclick="switchSettingsTab('security', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Security</span>
            </button>
            <button class="settings-nav-item" onclick="switchSettingsTab('notifications', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span>Notifications</span>
            </button>
            <button class="settings-nav-item" onclick="switchSettingsTab('ai', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/><line x1="8" y1="16" x2="8" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/></svg>
                <span>AI / Chatbot</span>
            </button>
            @if($isSystemAdmin)
            <button class="settings-nav-item" onclick="switchSettingsTab('theme', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/><path d="M2 12h20"/><path d="M12 2c2.76 4 4 8 4 10s-1.24 6-4 10"/><path d="M12 2c-2.76 4-4 8-4 10s1.24 6 4 10"/></svg>
                <span>Theme</span>
            </button>
            @endif
        </div>

        <div class="settings-tip">
            <div class="settings-tip-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <p class="settings-tip-title">QUICK TIP</p>
            </div>
            <p class="settings-tip-text">Turning off a notification category stops those alerts for your account only — other admins and HR staff are unaffected.</p>
        </div>
    </div>

    <div class="settings-content">
        {{-- Profile --}}
        <div id="tab-profile">
            <div class="settings-section">
                <h3 class="settings-section-title">Personal Information</h3>
                <div class="settings-section-content">
                    <div class="settings-form-wrapper">
                        <div class="settings-avatar-row">
                            <div class="settings-avatar-upload-wrap">
                                <div class="settings-avatar" id="mainAvatar">
                                    @if($employee?->photo)
                                        <img src="{{ $employee->photo }}" alt="" class="settings-avatar-img">
                                    @else
                                        <span id="mainAvatarInitials">{{ $initials }}</span>
                                    @endif
                                </div>
                                @if($employee)
                                <button type="button" class="settings-avatar-edit-btn" onclick="document.getElementById('avatarPhotoInput').click()" title="Change photo">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                </button>
                                <input type="file" id="avatarPhotoInput" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="uploadAvatarPhoto(this)">
                                @endif
                            </div>
                            <div class="settings-avatar-info">
                                <p class="settings-avatar-name">{{ $fullName }}</p>
                                <p class="settings-avatar-role">{{ $position ? $position . ' · ' . $department : $roleLabel }}</p>
                                @if(!$employee)
                                <p class="settings-row-desc" style="margin-top:4px">No employee record linked — photo upload unavailable.</p>
                                @endif
                            </div>
                        </div>
                        <p class="settings-message error hidden" id="avatarMsg"></p>

                        <div class="settings-form-grid">
                            <div class="settings-form-field">
                                <label>Full Name</label>
                                <input type="text" value="{{ $fullName }}" disabled title="Managed by HR — not editable here">
                            </div>
                            <div class="settings-form-field">
                                <label>Email Address</label>
                                <input type="email" id="settingsEmail" value="{{ $user->email }}">
                            </div>
                            <div class="settings-form-field">
                                <label>Contact No.</label>
                                <input type="text" id="settingsContactNo" value="{{ $contactNumber }}" placeholder="09XXXXXXXXX">
                            </div>
                            <div class="settings-form-field">
                                <label>Username</label>
                                <input type="text" value="{{ $user->username ?? '—' }}" disabled>
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

            @if($employee)
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
                    @if($position)
                    <div class="settings-row">
                        <div class="settings-row-label">
                            <p class="settings-row-title">Position</p>
                            <p class="settings-row-desc">Assigned by HR — not editable</p>
                        </div>
                        <span class="notif-readonly">{{ $position }}</span>
                    </div>
                    @endif
                    @if($department)
                    <div class="settings-row">
                        <div class="settings-row-label">
                            <p class="settings-row-title">Department</p>
                            <p class="settings-row-desc">Assigned by HR — not editable</p>
                        </div>
                        <span class="notif-readonly">{{ $department }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Security --}}
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
        </div>

        {{-- Notifications --}}
        <div id="tab-notifications" class="hidden">
            <div class="settings-section">
                <h3 class="settings-section-title">Notification Categories</h3>
                <div class="settings-section-content">
                    <div class="settings-row">
                        <div class="settings-row-label">
                            <p class="settings-row-title">New Leave Requests</p>
                            <p class="settings-row-desc">Notify when an employee files a leave request</p>
                        </div>
                        <button class="settings-toggle {{ $prefs['leave_requests'] ? 'active' : '' }}" data-pref="leave_requests" onclick="toggleSetting(this)">
                            <span class="settings-toggle-thumb"></span>
                        </button>
                    </div>
                    <div class="settings-row">
                        <div class="settings-row-label">
                            <p class="settings-row-title">New Training Submissions</p>
                            <p class="settings-row-desc">Notify when an employee submits a training record</p>
                        </div>
                        <button class="settings-toggle {{ $prefs['training_submissions'] ? 'active' : '' }}" data-pref="training_submissions" onclick="toggleSetting(this)">
                            <span class="settings-toggle-thumb"></span>
                        </button>
                    </div>
                    <div class="settings-row">
                        <div class="settings-row-label">
                            <p class="settings-row-title">New Travel Order Requests</p>
                            <p class="settings-row-desc">Notify when a travel order is forwarded for approval</p>
                        </div>
                        <button class="settings-toggle {{ $prefs['travel_orders'] ? 'active' : '' }}" data-pref="travel_orders" onclick="toggleSetting(this)">
                            <span class="settings-toggle-thumb"></span>
                        </button>
                    </div>
                    <div class="settings-row">
                        <div class="settings-row-label">
                            <p class="settings-row-title">Employee Requests</p>
                            <p class="settings-row-desc">Notify on payslip requests, deduction inquiries, and other employee requests</p>
                        </div>
                        <button class="settings-toggle {{ $prefs['employee_requests'] ? 'active' : '' }}" data-pref="employee_requests" onclick="toggleSetting(this)">
                            <span class="settings-toggle-thumb"></span>
                        </button>
                    </div>
                    <p class="settings-message error hidden" id="notifMsg"></p>
                    <div class="settings-form-wrapper">
                        <div class="settings-save-bar">
                            <button class="settings-btn-save" id="notifSaveBtn" onclick="saveNotificationPrefs()">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- AI / Chatbot --}}
        <div id="tab-ai" class="hidden">
            @php
                $providerLabels = ['groq' => 'Groq (Llama)', 'openai' => 'OpenAI', 'anthropic' => 'Anthropic (Claude)'];
                $systemDefaultLabel = $systemAiProvider ? ($providerLabels[$systemAiProvider] ?? $systemAiProvider) : 'not configured';
            @endphp
            <div class="settings-section">
                <h3 class="settings-section-title">Chatbot AI Provider</h3>
                <div class="settings-section-content">
                    <div class="settings-form-wrapper">
                        <p class="settings-row-desc" style="margin-bottom:16px">
                            By default, the PRIME HRIS chatbot uses the system default below (currently {{ $systemDefaultLabel }}).
                            Bring your own API key here to use a different provider or model for your own chatbot
                            conversations only — other admins and HR staff are unaffected.
                        </p>

                        <div class="settings-form-grid">
                            <div class="settings-form-field">
                                <label>Provider</label>
                                <select id="aiProvider" onchange="onAiProviderChange()">
                                    <option value="" {{ !$aiProvider ? 'selected' : '' }}>Use System Default ({{ $systemDefaultLabel }})</option>
                                    <option value="groq" {{ $aiProvider === 'groq' ? 'selected' : '' }}>Groq (Llama)</option>
                                    <option value="openai" {{ $aiProvider === 'openai' ? 'selected' : '' }}>OpenAI</option>
                                    <option value="anthropic" {{ $aiProvider === 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                                </select>
                            </div>
                            <div class="settings-form-field">
                                <label>Model <span style="text-transform:none;font-weight:500">(optional)</span></label>
                                <input type="text" id="aiModel" value="{{ $aiModel }}" placeholder="{{ $aiDefaultModels[$aiProvider ?? 'groq'] }}">
                            </div>
                        </div>

                        <div class="settings-form-field settings-field-spacing-md">
                            <label>API Key</label>
                            <input type="password" id="aiApiKey" placeholder="{{ $aiMaskedKey ? $aiMaskedKey . ' — leave blank to keep' : 'sk-...' }}" autocomplete="off">
                        </div>

                        <p class="settings-message error hidden" id="aiMsg"></p>
                        <div class="settings-save-bar">
                            <button class="settings-btn-reset" id="aiRevertBtn" onclick="revertAiSettings()">Use System Default</button>
                            <button class="settings-btn-save" id="aiSaveBtn" onclick="saveAiSettings()">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>

            @if($isSystemAdmin)
            <div class="settings-section">
                <h3 class="settings-section-title">System Default (applies org-wide)</h3>
                <div class="settings-section-content">
                    <div class="settings-form-wrapper">
                        <p class="settings-row-desc" style="margin-bottom:16px">
                            This is the provider/key used for every admin, HR staff, and employee who hasn't set up
                            their own key above. Only administrators can change this. Managed here in Settings —
                            no server or .env access needed.
                        </p>

                        <div class="settings-form-grid">
                            <div class="settings-form-field">
                                <label>Provider</label>
                                <select id="systemAiProvider" onchange="onSystemAiProviderChange()">
                                    <option value="" {{ !$systemAiProvider ? 'selected' : '' }}>Not configured (falls back to .env)</option>
                                    <option value="groq" {{ $systemAiProvider === 'groq' ? 'selected' : '' }}>Groq (Llama)</option>
                                    <option value="openai" {{ $systemAiProvider === 'openai' ? 'selected' : '' }}>OpenAI</option>
                                    <option value="anthropic" {{ $systemAiProvider === 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                                </select>
                            </div>
                            <div class="settings-form-field">
                                <label>Model <span style="text-transform:none;font-weight:500">(optional)</span></label>
                                <input type="text" id="systemAiModel" value="{{ $systemAiModel }}" placeholder="{{ $aiDefaultModels[$systemAiProvider ?? 'groq'] }}">
                            </div>
                        </div>

                        <div class="settings-form-field settings-field-spacing-md">
                            <label>API Key</label>
                            <input type="password" id="systemAiApiKey" placeholder="{{ $systemAiMaskedKey ? $systemAiMaskedKey . ' — leave blank to keep' : 'sk-...' }}" autocomplete="off">
                        </div>

                        <p class="settings-message error hidden" id="systemAiMsg"></p>
                        <div class="settings-save-bar">
                            <button class="settings-btn-save" id="systemAiSaveBtn" onclick="saveSystemAiSettings()">Save System Default</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        {{-- Theme --}}
        @if($isSystemAdmin)
        <div id="tab-theme" class="hidden">
            <div class="settings-section">
                <h3 class="settings-section-title">System Color Theme</h3>
                <div class="settings-section-content">
                    <p class="settings-row-desc" style="margin-bottom:20px">Choose a color palette for the entire system. This applies to all users — admin, employees, and mayor.</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:24px">
                        @foreach($themePalettes as $palette)
                        @if($palette['key'] === 'custom')
                        <button type="button"
                            onclick="selectTheme('custom')"
                            id="theme-card-custom"
                            style="border:2px solid {{ $activeTheme === 'custom' ? ($customThemeColor ?? '#6366f1') : '#e5e7eb' }};border-radius:12px;padding:16px 14px;background:{{ $activeTheme === 'custom' ? '#fafafe' : '#fff' }};cursor:pointer;text-align:left;transition:all .18s ease;position:relative">
                            <div id="custom-color-preview" style="width:40px;height:40px;border-radius:10px;background:{{ $customThemeColor ?? '#6366f1' }};margin-bottom:10px;box-shadow:0 2px 8px rgba(0,0,0,0.15)"></div>
                            <p style="font-size:13px;font-weight:700;color:#111827;margin:0 0 4px">Custom Color</p>
                            <input type="color" id="customColorPicker"
                                value="{{ $customThemeColor ?? '#6366f1' }}"
                                onclick="event.stopPropagation()"
                                oninput="onCustomColorChange(this.value)"
                                style="width:100%;height:28px;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;padding:2px 4px;background:#fff">
                            @if($activeTheme === 'custom')
                            <span class="theme-check" style="position:absolute;top:10px;right:10px;width:20px;height:20px;border-radius:50%;background:{{ $customThemeColor ?? '#6366f1' }};display:flex;align-items:center;justify-content:center">
                                <svg width="11" height="11" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            @endif
                        </button>
                        @else
                        <button type="button"
                            onclick="selectTheme('{{ $palette['key'] }}')"
                            id="theme-card-{{ $palette['key'] }}"
                            style="border:2px solid {{ $activeTheme === $palette['key'] ? $palette['preview'] : '#e5e7eb' }};border-radius:12px;padding:16px 14px;background:{{ $activeTheme === $palette['key'] ? '#fafafe' : '#fff' }};cursor:pointer;text-align:left;transition:all .18s ease;position:relative">
                            <div style="width:40px;height:40px;border-radius:10px;background:{{ $palette['preview'] }};margin-bottom:10px;box-shadow:0 2px 8px rgba(0,0,0,0.15)"></div>
                            <p style="font-size:13px;font-weight:700;color:#111827;margin:0 0 2px">{{ $palette['label'] }}</p>
                            <p style="font-size:11px;color:#6b7280;margin:0">{{ $palette['key'] === 'default' ? 'System default' : $palette['preview'] }}</p>
                            @if($activeTheme === $palette['key'])
                            <span style="position:absolute;top:10px;right:10px;width:20px;height:20px;border-radius:50%;background:{{ $palette['preview'] }};display:flex;align-items:center;justify-content:center">
                                <svg width="11" height="11" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            @endif
                        </button>
                        @endif
                        @endforeach
                    </div>
                    <p class="settings-message error hidden" id="themeMsg"></p>

                    {{-- Color overrides panel --}}
                    <div id="theme-overrides-panel" style="margin-bottom:24px;padding:20px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px">
                        <p style="font-size:12px;font-weight:700;color:#374151;letter-spacing:0.6px;text-transform:uppercase;margin:0 0 4px">Customize Colors</p>
                        <p style="font-size:11.5px;color:#6b7280;margin:0 0 16px;line-height:1.5">Fine-tune the selected palette. Leave as-is to use the palette defaults.</p>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px">
                            <div>
                                <label style="display:block;font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.7px;margin-bottom:6px">Secondary</label>
                                <p style="font-size:10.5px;color:#9ca3af;margin:0 0 6px;line-height:1.4">Button gradients, active tabs &amp; pagination</p>
                                <input type="color" id="override-secondary" style="width:100%;height:36px;border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;padding:2px 4px">
                            </div>
                            <div>
                                <label style="display:block;font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.7px;margin-bottom:6px">Accent</label>
                                <p style="font-size:10.5px;color:#9ca3af;margin:0 0 6px;line-height:1.4">Links, focus rings, chart tabs, form labels</p>
                                <input type="color" id="override-accent" style="width:100%;height:36px;border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;padding:2px 4px">
                            </div>
                            <div>
                                <label style="display:block;font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.7px;margin-bottom:6px">Muted</label>
                                <p style="font-size:10.5px;color:#9ca3af;margin:0 0 6px;line-height:1.4">Stat labels, table subtitles, sidebar nav text</p>
                                <input type="color" id="override-muted" style="width:100%;height:36px;border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;padding:2px 4px">
                            </div>
                        </div>
                        <button type="button" onclick="resetThemeOverrides()" style="margin-top:14px;font-size:11.5px;font-weight:600;color:#6b7280;background:none;border:1px solid #e5e7eb;border-radius:8px;padding:6px 14px;cursor:pointer">↺ Reset to palette defaults</button>
                    </div>

                    <div class="settings-save-bar">
                        <button class="settings-btn-save" id="themeSaveBtn" onclick="saveTheme()">Apply Theme</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
</div>

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

@push('scripts')
<script>
    window.adminSettingsData = {
        email: @json($user->email),
        contactNumber: @json($contactNumber),
        aiDefaultModels: @json($aiDefaultModels),
        activeTheme: @json($activeTheme),
        customThemeColor: @json($customThemeColor),
        themeSecondary: @json($themeSecondary),
        themeAccent: @json($themeAccent),
        themeMuted: @json($themeMuted),
        themePalettes: @json($themePalettes),
        themeRoute: '{{ route('admin.settings.theme') }}',
        csrfToken: '{{ csrf_token() }}',
    };
</script>
    @vite('resources/js/admin/settings/adminSettings.js')
@endpush
@endsection
