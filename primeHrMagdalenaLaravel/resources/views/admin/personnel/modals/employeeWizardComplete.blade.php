<!-- Draft Continue/Start Over Prompt (shown before the wizard when a previous,
     unsubmitted "Add Employee" session was interrupted) -->
<div id="wizardDraftPrompt" class="wizard-draft-prompt-overlay" style="display:none;">
    <div class="wizard-draft-prompt-card" role="dialog" aria-modal="true" aria-labelledby="wizardDraftPromptTitle">
        <div class="wizard-draft-prompt-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"/>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
            </svg>
        </div>
        <h3 class="wizard-draft-prompt-title" id="wizardDraftPromptTitle">Unfinished Registration Found</h3>
        <p class="wizard-draft-prompt-text">You have an unsaved employee registration in progress. Would you like to continue where you left off, or start a new one?</p>
        <div class="wizard-draft-prompt-actions">
            <button type="button" class="wizard-draft-prompt-btn wizard-draft-prompt-startover" onclick="startOverEmployeeWizard()">Start Over</button>
            <button type="button" class="wizard-draft-prompt-btn wizard-draft-prompt-continue" onclick="continueEmployeeWizardDraft()">Continue Draft</button>
        </div>
    </div>
</div>

<!-- Employee Registration Wizard - 6 Steps -->
<div id="employeeWizardModal">
    <div class="wizard-modal-container">
        <!-- Header with Progress -->
        <div class="wizard-header">
            <div class="wizard-header-top">
                <div>
                    <h3 class="wizard-title" id="wizardTitle">Employee Registration Wizard</h3>
                    <p class="wizard-subtitle"><span id="stepIndicator">Step 1 of 7</span> - <span id="wizardSubtitle">Complete all steps to register</span></p>
                </div>
                <button onclick="closeEmployeeWizard()" class="wizard-close-btn" aria-label="Close wizard">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <!-- Progress Indicator (steps are clickable to jump between pages) -->
            <div id="progressBar">
                <div class="wizard-step active" data-step="1" role="button" tabindex="0" title="Go to Personal" onclick="goToWizardStep(1)">
                    <div class="wizard-circle">1</div>
                    <span class="wizard-label">Personal</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="2" role="button" tabindex="0" title="Go to Account" onclick="goToWizardStep(2)">
                    <div class="wizard-circle">2</div>
                    <span class="wizard-label">Account</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="3" role="button" tabindex="0" title="Go to Employment" onclick="goToWizardStep(3)">
                    <div class="wizard-circle">3</div>
                    <span class="wizard-label">Employment</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="4" role="button" tabindex="0" title="Go to Contact" onclick="goToWizardStep(4)">
                    <div class="wizard-circle">4</div>
                    <span class="wizard-label">Contact</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="5" role="button" tabindex="0" title="Go to Gov IDs" onclick="goToWizardStep(5)">
                    <div class="wizard-circle">5</div>
                    <span class="wizard-label">Gov IDs</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="6" role="button" tabindex="0" title="Go to Supporting Docs" onclick="goToWizardStep(6)">
                    <div class="wizard-circle">6</div>
                    <span class="wizard-label">Docs</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="7" role="button" tabindex="0" title="Go to Review" onclick="goToWizardStep(7)">
                    <div class="wizard-circle">7</div>
                    <span class="wizard-label">Review</span>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <form id="employeeWizardForm" action="{{ route('admin.personnel.store') }}" data-store-action="{{ route('admin.personnel.store') }}" method="POST" enctype="multipart/form-data" class="wizard-form">
            @csrf
            <input type="hidden" id="wizardEditId" name="_edit_id" value="">

            <!-- STEP 1: Personal Information -->
            <div class="wizard-content active" data-step="1" style="display:block;">
                <h4 class="wizard-section-title"><svg class="wizard-section-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Personal Information</span></h4>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Employee ID * <span style="color:var(--gp-pri); font-size:11px;">(UNIQUE)</span></label>
                        <input type="text" name="employee_id" placeholder="e.g. PGS-0001" maxlength="255" class="wizard-input">
                    </div>
                    <div></div>
                </div>
                <div class="wizard-grid-3">
                    <div>
                        <label class="wizard-label-text">First Name *</label>
                        <input type="text" name="first_name" placeholder="Maria" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Optional" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Last Name *</label>
                        <input type="text" name="last_name" placeholder="Santos" maxlength="255" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Suffix</label>
                        <select name="suffix" class="wizard-select">
                            <option value="">None</option>
                            <option>Jr.</option>
                            <option>Sr.</option>
                            <option>II</option>
                            <option>III</option>
                            <option>IV</option>
                        </select>
                    </div>
                    <div>
                        <label class="wizard-label-text">Photo (Upload)</label>
                        <input type="file" name="photo" accept="image/*" class="wizard-input">
                        <p class="wizard-hint">Supported: JPG, PNG, GIF (Max 5MB)</p>
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Date of Birth *</label>
                        <input type="date" name="birth_date" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Place of Birth</label>
                        <input type="text" name="place_of_birth" placeholder="Pagsanjan, Laguna" maxlength="255" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Sex *</label>
                        <select name="sex" class="wizard-select">
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="wizard-label-text">Civil Status *</label>
                        <select name="civil_status" class="wizard-select">
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                </div>
                <div class="wizard-grid-3">
                    <div>
                        <label class="wizard-label-text">Height (cm)</label>
                        <input type="number" name="height" step="0.01" placeholder="165.5" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Weight (kg)</label>
                        <input type="number" name="weight" step="0.01" placeholder="65.5" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Blood Type</label>
                        <select name="blood_type" class="wizard-select">
                            <option value="">Select</option>
                            <option>A+</option>
                            <option>A-</option>
                            <option>B+</option>
                            <option>B-</option>
                            <option>AB+</option>
                            <option>AB-</option>
                            <option>O+</option>
                            <option>O-</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="wizard-label-text">Citizenship</label>
                    <input type="text" name="citizenship" placeholder="Filipino" maxlength="255" class="wizard-input">
                </div>
            </div>

            <!-- STEP 2: Account Setup -->
            <div class="wizard-content" data-step="2" style="display:none;">
                <div id="step2-register">
                    <h4 class="wizard-section-title"><svg class="wizard-section-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span>Account Setup</span></h4>
                    <div class="wizard-info-box">
                        <p class="wizard-info-title"><svg class="wizard-info-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg><strong>Create Login Credentials</strong></p>
                        <p class="wizard-info-text">Set up username, email, and password for system access</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Username * <span style="color:var(--gp-pri); font-size:11px;">(UNIQUE)</span></label>
                        <input type="text" name="username" placeholder="e.g. santosjuan" maxlength="255" class="wizard-input">
                        <p class="wizard-hint">Auto-filled from Last Name + First Name — edit if you'd like a different one.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Email * <span style="color:var(--gp-pri); font-size:11px;">(UNIQUE)</span></label>
                        <input type="email" name="user_email" placeholder="maria.santos@example.com" maxlength="255" class="wizard-input">
                        <p class="wizard-hint">Must be a valid email address for account notifications.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Password * <span style="color:var(--gp-pri); font-size:11px;">(Min 8 characters)</span></label>
                        <div class="wizard-pw-wrap">
                            <input type="password" name="password" placeholder="••••••••" maxlength="255" class="wizard-input">
                            <button type="button" class="wizard-pw-toggle" onclick="toggleWizardPassword(this)" aria-label="Show password">
                                <svg class="wizard-eye-show" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                                <svg class="wizard-eye-hide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        <p class="wizard-hint">Use uppercase, lowercase, numbers, and symbols for security.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Confirm Password *</label>
                        <div class="wizard-pw-wrap">
                            <input type="password" name="password_confirm" placeholder="••••••••" maxlength="255" class="wizard-input">
                            <button type="button" class="wizard-pw-toggle" onclick="toggleWizardPassword(this)" aria-label="Show password">
                                <svg class="wizard-eye-show" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                                <svg class="wizard-eye-hide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Role / Access Level * <span style="font-weight:400;color:var(--gp-text-mid);">(select one or more)</span></label>
                        <div class="wizard-role-checkboxes" id="register-roles">
                            <label class="wizard-role-checkbox">
                                <input type="checkbox" name="roles[]" value="employee" checked>
                                <span><strong>Employee</strong> — Limited access to own records</span>
                            </label>
                            <label class="wizard-role-checkbox">
                                <input type="checkbox" name="roles[]" value="hr">
                                <span><strong>HR</strong> — Full access to all employee records</span>
                            </label>
                            <label class="wizard-role-checkbox">
                                <input type="checkbox" name="roles[]" value="admin">
                                <span><strong>Admin</strong> — System administrator access</span>
                            </label>
                            <label class="wizard-role-checkbox">
                                <input type="checkbox" name="roles[]" value="mayor">
                                <span><strong>Mayor</strong> — Read-only oversight dashboard</span>
                            </label>
                        </div>
                        <p class="wizard-hint">An employee can hold multiple roles at once, e.g. HR + Mayor.</p>
                    </div>
                </div>
                <div id="step2-edit" style="display:none;">
                    <h4 class="wizard-section-title"><svg class="wizard-section-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span>Account Info</span></h4>
                    <div class="wizard-info-box" style="background:var(--gp-bg-tint-2);border-color:var(--gp-pri)22;">
                        <p class="wizard-info-title"><svg class="wizard-info-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg><strong>Account credentials cannot be changed here.</strong></p>
                        <p class="wizard-info-text">Username, email, and password are managed separately.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Role / Access Level * <span style="font-weight:400;color:var(--gp-text-mid);">(select one or more)</span></label>
                        <div class="wizard-role-checkboxes" id="edit-roles">
                            <label class="wizard-role-checkbox">
                                <input type="checkbox" name="roles[]" value="employee">
                                <span><strong>Employee</strong> — Limited access to own records</span>
                            </label>
                            <label class="wizard-role-checkbox">
                                <input type="checkbox" name="roles[]" value="hr">
                                <span><strong>HR</strong> — Full access to all employee records</span>
                            </label>
                            <label class="wizard-role-checkbox">
                                <input type="checkbox" name="roles[]" value="admin">
                                <span><strong>Admin</strong> — System administrator access</span>
                            </label>
                            <label class="wizard-role-checkbox">
                                <input type="checkbox" name="roles[]" value="mayor">
                                <span><strong>Mayor</strong> — Read-only oversight dashboard</span>
                            </label>
                        </div>
                        <p class="wizard-hint">An employee can hold multiple roles at once, e.g. HR + Mayor.</p>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Employment Details -->
            <div class="wizard-content" data-step="3" style="display:none;">
                <h4 class="wizard-section-title"><svg class="wizard-section-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg><span>Employment Details</span></h4>
                <div class="wizard-field">
                    <label class="wizard-label-text">Department / Office *</label>
                    <select name="department" id="wizard-department" class="wizard-select" onchange="loadDesignations(this.value)">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="wizard-field">
                    <label class="wizard-label-text">Position / Designation *</label>
                    <select name="designation_id" id="wizard-position" class="wizard-select" onchange="fillFromDesignation(this)" disabled>
                        <option value="">— Select a department first —</option>
                    </select>
                    <p class="wizard-hint" id="wizard-position-hint">Select a department to load available designations.</p>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Employment Type / Status *</label>
                        <input type="text" name="employment_status" id="wizard-employment-status" class="wizard-input" placeholder="Auto-filled from designation" readonly style="background:var(--gp-bg-tint);color:var(--gp-text-mid);cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Appointment Date *</label>
                        <input type="date" name="appointment_date" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Salary Grade</label>
                        <input type="text" name="salary_grade" id="wizard-salary-grade" class="wizard-input" placeholder="Auto-filled from designation" readonly style="background:var(--gp-bg-tint);color:var(--gp-text-mid);cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Step Increment</label>
                        <input type="text" name="step_increment" placeholder="Step 3" maxlength="255" class="wizard-input">
                    </div>
                </div>
            </div>

            <!-- STEP 4: Contact Information -->
            <div class="wizard-content" data-step="4" style="display:none;">
                <h4 class="wizard-section-title"><svg class="wizard-section-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg><span>Contact Information</span></h4>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Mobile Number</label>
                        <input type="tel" name="mobile_number" placeholder="+63 9xx xxxx xxx" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Landline Number</label>
                        <input type="tel" name="landline_number" placeholder="(02) xxxx xxxx" maxlength="255" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2" style="margin-bottom:20px;">
                    <div>
                        <label class="wizard-label-text">Emergency Contact Person</label>
                        <input type="text" name="emergency_contact_person" placeholder="Full Name" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Emergency Contact Number</label>
                        <input type="tel" name="emergency_contact_number" placeholder="Phone Number" maxlength="255" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-address-header">
                    <h5 class="wizard-address-title"><svg class="wizard-address-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><span>Residential Address</span></h5>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">House No.</label>
                        <input type="text" name="house_no" placeholder="123" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Street</label>
                        <input type="text" name="street" placeholder="Main Street" maxlength="255" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Barangay</label>
                        <input type="text" name="barangay" placeholder="Maligaya" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">City/Municipality</label>
                        <input type="text" name="city" placeholder="Pagsanjan" maxlength="255" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Province</label>
                        <input type="text" name="province" placeholder="Laguna" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">Zip Code</label>
                        <input type="text" name="zip_code" placeholder="4010" maxlength="255" class="wizard-input">
                    </div>
                </div>
            </div>

            <!-- STEP 5: Government IDs -->
            <div class="wizard-content" data-step="5" style="display:none;"
                 id="wizardGovIdStep"
                 data-attachment-cards
                 data-extensions="{{ implode(',', \App\Models\GovernmentId::EXTENSIONS) }}"
                 data-max-kb="{{ \App\Models\GovernmentId::maxKb() }}"
                 data-max-label="{{ \App\Models\GovernmentId::maxSizeLabel() }}">
                <h4 class="wizard-section-title"><svg class="wizard-section-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2.2"/><line x1="14" y1="10" x2="19" y2="10"/><line x1="14" y1="14" x2="18" y2="14"/></svg><span>Government IDs</span></h4>

                <div class="doc-intro">
                    <div class="doc-intro-text">
                        <p class="doc-intro-lead">Upload a scanned copy or clear photo of each ID. <strong>The number is read from the scan automatically</strong> — verify what comes back, or type it in by hand if the auto-read finds nothing.</p>
                        <p class="doc-intro-formats">
                            <span class="doc-intro-formats-label">Accepted</span>
                            @foreach (\App\Models\GovernmentId::EXTENSIONS as $ext)
                                @continue($ext === 'jpeg')
                                <span class="doc-format-chip">{{ strtoupper($ext) }}</span>
                            @endforeach
                            <span class="doc-intro-size">up to {{ \App\Models\GovernmentId::maxSizeLabel() }} each</span>
                        </p>
                    </div>
                    <div class="doc-progress" role="status" aria-live="polite">
                        <div class="doc-progress-count"><span data-attach-count>0</span><span class="doc-progress-total">/{{ count(\App\Models\GovernmentId::IDS) }}</span></div>
                        <div class="doc-progress-label">scanned</div>
                        <div class="doc-progress-track"><div class="doc-progress-fill" data-attach-fill style="width:0%;"></div></div>
                    </div>
                </div>

                <p class="doc-payload-warning" data-payload-warning role="alert" hidden></p>

                <div class="doc-grid">
                    @foreach (\App\Models\GovernmentId::IDS as $gov)
                        <div class="doc-card govid-card" data-doc-card="{{ $gov['key'] }}">
                            <div class="doc-face">
                                <input type="file"
                                       id="govid-input-{{ $gov['key'] }}"
                                       name="{{ $gov['key'] }}_file"
                                       accept="{{ \App\Models\GovernmentId::accept() }}"
                                       class="doc-input govid-file-input"
                                       data-id-type="{{ $gov['key'] }}"
                                       data-target="{{ $gov['key'] }}_no"
                                       aria-label="Upload {{ $gov['label'] }} scanned ID">
                                <span class="doc-thumb" data-doc-thumb>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><polyline points="7 9 12 4 17 9"/><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                                </span>
                                <span class="doc-text">
                                    <span class="doc-name">{{ $gov['label'] }}</span>
                                    <span class="doc-note">{{ $gov['note'] }}</span>
                                    <span class="doc-meta" data-doc-meta>Click to browse, or drop a file here</span>
                                </span>
                                <span class="doc-cta" data-doc-cta>Browse</span>
                            </div>
                            <div class="doc-picked" data-doc-picked hidden>
                                <svg class="doc-picked-tick" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                <span class="doc-picked-name" data-doc-filename></span>
                                <span class="doc-picked-size" data-doc-filesize></span>
                                <button type="button" class="doc-remove" data-doc-remove aria-label="Remove the scan chosen for {{ $gov['label'] }}">Remove</button>
                            </div>
                            <p class="doc-error" data-doc-error role="alert" hidden></p>
                            <div class="wizard-govid-current" data-current-for="{{ $gov['key'] }}" style="display:none;"></div>
                            <div class="govid-number">
                                <label class="govid-number-label" for="govid-no-{{ $gov['key'] }}">{{ $gov['label'] }} Number</label>
                                <input type="text" id="govid-no-{{ $gov['key'] }}" name="{{ $gov['key'] }}_no" placeholder="{{ $gov['placeholder'] }}" maxlength="255" class="govid-number-input">
                                <p class="govid-ocr-status" data-status-for="{{ $gov['key'] }}"></p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- STEP 6: Supporting Documents (the 201 file) -->
            <div class="wizard-content" data-step="6" style="display:none;"
                 id="wizardDocsStep"
                 data-attachment-cards
                 data-extensions="{{ implode(',', \App\Models\EmployeeSupportingDocument::EXTENSIONS) }}"
                 data-max-kb="{{ \App\Models\EmployeeSupportingDocument::maxKb() }}"
                 data-max-label="{{ \App\Models\EmployeeSupportingDocument::maxSizeLabel() }}"
                 data-post-max-kb="{{ \App\Models\EmployeeSupportingDocument::postMaxKb() }}"
                 data-post-max-label="{{ \App\Models\EmployeeSupportingDocument::sizeLabel(\App\Models\EmployeeSupportingDocument::postMaxKb()) }}">
                <h4 class="wizard-section-title"><svg class="wizard-section-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span>Supporting Documents (201 File)</span></h4>

                <div class="doc-intro">
                    <div class="doc-intro-text">
                        <p class="doc-intro-lead">Attach the 201-file documents you have on hand. <strong>Every field is optional</strong> — leave a document blank if it does not apply or is not yet available, and add it later from Edit Employee.</p>
                        <p class="doc-intro-formats">
                            <span class="doc-intro-formats-label">Accepted</span>
                            @foreach (\App\Models\EmployeeSupportingDocument::EXTENSIONS as $ext)
                                @continue($ext === 'jpeg')
                                <span class="doc-format-chip">{{ strtoupper($ext) }}</span>
                            @endforeach
                            <span class="doc-intro-size">up to {{ \App\Models\EmployeeSupportingDocument::maxSizeLabel() }} each</span>
                        </p>
                    </div>
                    <div class="doc-progress" role="status" aria-live="polite">
                        <div class="doc-progress-count"><span data-attach-count>0</span><span class="doc-progress-total">/{{ count(\App\Models\EmployeeSupportingDocument::documents()) }}</span></div>
                        <div class="doc-progress-label">attached</div>
                        <div class="doc-progress-track"><div class="doc-progress-fill" data-attach-fill style="width:0%;"></div></div>
                    </div>
                </div>

                {{-- Exceeding post_max_size makes PHP discard the entire body,
                     CSRF token included, and the admin gets 419 Page Expired
                     with seven steps of typing gone. The running total is
                     checked before the wizard is allowed to submit. --}}
                <p class="doc-payload-warning" data-payload-warning role="alert" hidden></p>

                @foreach (\App\Models\EmployeeSupportingDocument::GROUPS as $group)
                    <div class="doc-group">
                        <div class="doc-group-head">
                            <h5 class="doc-group-title">
                                @if ($group['icon'] === 'form')
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                @elseif ($group['icon'] === 'shield')
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                                @else
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M8.5 12.5 7 22l5-3 5 3-1.5-9.5"/></svg>
                                @endif
                                <span>{{ $group['title'] }}</span>
                            </h5>
                            <span class="doc-group-hint">{{ $group['hint'] }}</span>
                        </div>

                        <div class="doc-grid">
                            @foreach ($group['items'] as $doc)
                                <div class="doc-card" data-doc-card="{{ $doc['key'] }}">
                                    <div class="doc-face">
                                        <input type="file"
                                               id="doc-input-{{ $doc['key'] }}"
                                               name="{{ $doc['input'] }}"
                                               accept="{{ \App\Models\EmployeeSupportingDocument::accept() }}"
                                               class="doc-input"
                                               data-doc-key="{{ $doc['key'] }}"
                                               aria-label="Upload {{ $doc['label'] }}">
                                        <span class="doc-thumb" data-doc-thumb>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><polyline points="7 9 12 4 17 9"/><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                                        </span>
                                        <span class="doc-text">
                                            <span class="doc-name">{{ $doc['label'] }}</span>
                                            <span class="doc-note">{{ $doc['note'] }}</span>
                                            <span class="doc-meta" data-doc-meta>Click to browse, or drop a file here</span>
                                        </span>
                                        <span class="doc-cta" data-doc-cta>Browse</span>
                                    </div>
                                    <div class="doc-picked" data-doc-picked hidden>
                                        <svg class="doc-picked-tick" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span class="doc-picked-name" data-doc-filename></span>
                                        <span class="doc-picked-size" data-doc-filesize></span>
                                        <button type="button" class="doc-remove" data-doc-remove aria-label="Remove the file chosen for {{ $doc['label'] }}">Remove</button>
                                    </div>
                                    <p class="doc-error" data-doc-error role="alert" hidden></p>
                                    <div class="wizard-supporting-current" data-current-for="{{ $doc['key'] }}" style="display:none;"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- STEP 7: Review -->
            <div class="wizard-content" data-step="7" style="display:none;">
                <h4 class="wizard-section-title"><svg class="wizard-section-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span>Review All Information</span></h4>
                <div id="wizardReviewContent" class="wizard-review-content">
                    <p style="color:var(--gp-text-mid); text-align:center; padding:40px 20px;">Loading review data...</p>
                </div>
                <div class="wizard-success-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--theme-success)" stroke-width="2" class="wizard-success-icon">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <p class="wizard-success-title">All steps completed!</p>
                        <p class="wizard-success-text">Click Submit to register employee.</p>
                    </div>
                </div>
            </div>
        </form>

        <!-- Footer Navigation -->
        <div class="wizard-footer">
            <button type="button" onclick="previousStep()" id="prevBtn" class="wizard-btn wizard-btn-prev" style="display:none;">
                ← Previous
            </button>
            <div class="wizard-footer-actions">
                <button type="button" onclick="closeEmployeeWizard()" class="wizard-btn wizard-btn-cancel">
                    Cancel
                </button>
                <button type="button" onclick="nextStep()" id="nextBtn" class="wizard-btn wizard-btn-next">
                    Next →
                </button>
                <button type="submit" id="submitBtn" class="wizard-btn wizard-btn-submit" style="display:none;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Submit</span>
                </button>
                <button type="button" id="updateBtn" class="wizard-btn wizard-btn-submit" style="display:none;background:var(--gp-pri-2);" onclick="submitWizardUpdate()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Save Changes</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/admin/personnel/employeeWizard.js', 'resources/js/admin/personnel/employeeWizardComplete.js', 'resources/js/admin/personnel/employeeWizardValidation.js', 'resources/js/admin/personnel/employeeWizardDocuments.js'])
@endpush

