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
                    <p class="wizard-subtitle"><span id="stepIndicator">Step 1 of 6</span> - <span id="wizardSubtitle">Complete all steps to register</span></p>
                </div>
                <button onclick="closeEmployeeWizard()" class="wizard-close-btn">&times;</button>
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
                <div class="wizard-step" data-step="6" role="button" tabindex="0" title="Go to Review" onclick="goToWizardStep(6)">
                    <div class="wizard-circle">6</div>
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
                <h4 class="wizard-section-title">👤 Personal Information</h4>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Employee ID * <span style="color:#0b044d; font-size:11px;">(UNIQUE)</span></label>
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
                    <h4 class="wizard-section-title">🔐 Account Setup</h4>
                    <div class="wizard-info-box">
                        <p class="wizard-info-title"><strong>ℹ️ Create Login Credentials</strong></p>
                        <p class="wizard-info-text">Set up username, email, and password for system access</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Username * <span style="color:#0b044d; font-size:11px;">(UNIQUE)</span></label>
                        <input type="text" name="username" placeholder="e.g. santosjuan" maxlength="255" class="wizard-input">
                        <p class="wizard-hint">Auto-filled from Last Name + First Name — edit if you'd like a different one.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Email * <span style="color:#0b044d; font-size:11px;">(UNIQUE)</span></label>
                        <input type="email" name="user_email" placeholder="maria.santos@example.com" maxlength="255" class="wizard-input">
                        <p class="wizard-hint">Must be a valid email address for account notifications.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Password * <span style="color:#0b044d; font-size:11px;">(Min 8 characters)</span></label>
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
                        <label class="wizard-label-text">Role / Access Level * <span style="font-weight:400;color:#56547a;">(select one or more)</span></label>
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
                    <h4 class="wizard-section-title">🔐 Account Info</h4>
                    <div class="wizard-info-box" style="background:#f2f1fb;border-color:#0b044d22;">
                        <p class="wizard-info-title"><strong>ℹ️ Account credentials cannot be changed here.</strong></p>
                        <p class="wizard-info-text">Username, email, and password are managed separately.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Role / Access Level * <span style="font-weight:400;color:#56547a;">(select one or more)</span></label>
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
                <h4 class="wizard-section-title">💼 Employment Details</h4>
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
                        <input type="text" name="employment_status" id="wizard-employment-status" class="wizard-input" placeholder="Auto-filled from designation" readonly style="background:#f7f6fc;color:#56547a;cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Appointment Date *</label>
                        <input type="date" name="appointment_date" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Salary Grade</label>
                        <input type="text" name="salary_grade" id="wizard-salary-grade" class="wizard-input" placeholder="Auto-filled from designation" readonly style="background:#f7f6fc;color:#56547a;cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Step Increment</label>
                        <input type="text" name="step_increment" placeholder="Step 3" maxlength="255" class="wizard-input">
                    </div>
                </div>
            </div>

            <!-- STEP 4: Contact Information -->
            <div class="wizard-content" data-step="4" style="display:none;">
                <h4 class="wizard-section-title">📞 Contact Information</h4>
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
                    <h5 class="wizard-address-title">📍 Residential Address</h5>
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
            <div class="wizard-content" data-step="5" style="display:none;">
                <h4 class="wizard-section-title">🪪 Government IDs</h4>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">GSIS Number</label>
                        <input type="text" name="gsis_no" placeholder="GSIS ID" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">PhilHealth Number</label>
                        <input type="text" name="philhealth_no" placeholder="PhilHealth ID" maxlength="255" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">PAG-IBIG Number</label>
                        <input type="text" name="pagibig_no" placeholder="PAG-IBIG ID" maxlength="255" class="wizard-input">
                    </div>
                    <div>
                        <label class="wizard-label-text">TIN Number</label>
                        <input type="text" name="tin_no" placeholder="Tax ID" maxlength="255" class="wizard-input">
                    </div>
                </div>
                <div>
                    <label class="wizard-label-text">License Number</label>
                    <input type="text" name="license_no" placeholder="Professional License" maxlength="255" class="wizard-input">
                </div>
            </div>

            <!-- STEP 6: Review -->
            <div class="wizard-content" data-step="6" style="display:none;">
                <h4 class="wizard-section-title">✅ Review All Information</h4>
                <div id="wizardReviewContent" class="wizard-review-content">
                    <p style="color:#56547a; text-align:center; padding:40px 20px;">Loading review data...</p>
                </div>
                <div class="wizard-success-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" class="wizard-success-icon">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <p class="wizard-success-title">✓ All steps completed!</p>
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
                    ✓ Submit
                </button>
                <button type="button" id="updateBtn" class="wizard-btn wizard-btn-submit" style="display:none;background:#150c63;" onclick="submitWizardUpdate()">
                    ✓ Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/admin/personnel/employeeWizard.js', 'resources/js/admin/personnel/employeeWizardComplete.js', 'resources/js/admin/personnel/employeeWizardValidation.js'])
@endpush

