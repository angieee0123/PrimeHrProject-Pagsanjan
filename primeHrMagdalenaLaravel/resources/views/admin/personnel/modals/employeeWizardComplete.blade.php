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

            <!-- Progress Indicator -->
            <div id="progressBar">
                <div class="wizard-step active" data-step="1">
                    <div class="wizard-circle">1</div>
                    <span class="wizard-label">Personal</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="2">
                    <div class="wizard-circle">2</div>
                    <span class="wizard-label">Account</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="3">
                    <div class="wizard-circle">3</div>
                    <span class="wizard-label">Employment</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="4">
                    <div class="wizard-circle">4</div>
                    <span class="wizard-label">Contact</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="5">
                    <div class="wizard-circle">5</div>
                    <span class="wizard-label">Gov IDs</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="6">
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
                        <input type="text" name="username" placeholder="e.g. maria.santos" maxlength="255" class="wizard-input">
                        <p class="wizard-hint">No spaces. Use lowercase letters, numbers, dots, and underscores.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Email * <span style="color:#0b044d; font-size:11px;">(UNIQUE)</span></label>
                        <input type="email" name="user_email" placeholder="maria.santos@example.com" maxlength="255" class="wizard-input">
                        <p class="wizard-hint">Must be a valid email address for account notifications.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Password * <span style="color:#0b044d; font-size:11px;">(Min 8 characters)</span></label>
                        <input type="password" name="password" placeholder="••••••••" maxlength="255" class="wizard-input">
                        <p class="wizard-hint">Use uppercase, lowercase, numbers, and symbols for security.</p>
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Confirm Password *</label>
                        <input type="password" name="password_confirm" placeholder="••••••••" maxlength="255" class="wizard-input">
                    </div>
                    <div class="wizard-field">
                        <label class="wizard-label-text">Role / Access Level *</label>
                        <select name="role" class="wizard-select">
                            <option value="">Select Role</option>
                            <option value="employee">Employee - Limited access to own records</option>
                            <option value="hr">HR - Full access to all employee records</option>
                            <option value="admin">Admin - System administrator access</option>
                        </select>
                    </div>
                    <div class="wizard-requirements-box">
                        <p class="wizard-requirements-title">✓ Password Requirements:</p>
                        <ul class="wizard-requirements-list">
                            <li>At least 8 characters long</li>
                            <li>Contains uppercase letter (A-Z)</li>
                            <li>Contains lowercase letter (a-z)</li>
                            <li>Contains number (0-9)</li>
                            <li>Contains special character (!@#$%^&*)</li>
                        </ul>
                    </div>
                </div>
                <div id="step2-edit" style="display:none;">
                    <h4 class="wizard-section-title">🔐 Account Info</h4>
                    <div class="wizard-info-box" style="background:#f0effe;border-color:#0b044d22;">
                        <p class="wizard-info-title"><strong>ℹ️ Account credentials cannot be changed here.</strong></p>
                        <p class="wizard-info-text">Username, email, password and role are managed separately. Click Next to continue editing other details.</p>
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
                        <input type="text" name="employment_status" id="wizard-employment-status" class="wizard-input" placeholder="Auto-filled from designation" readonly style="background:#f7f6ff;color:#6b6a8a;cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Appointment Date *</label>
                        <input type="date" name="appointment_date" class="wizard-input">
                    </div>
                </div>
                <div class="wizard-grid-2">
                    <div>
                        <label class="wizard-label-text">Salary Grade</label>
                        <input type="text" name="salary_grade" id="wizard-salary-grade" class="wizard-input" placeholder="Auto-filled from designation" readonly style="background:#f7f6ff;color:#6b6a8a;cursor:not-allowed;">
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
                    <p style="color:#6b6a8a; text-align:center; padding:40px 20px;">Loading review data...</p>
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
                <button type="button" id="updateBtn" class="wizard-btn wizard-btn-submit" style="display:none;background:#1a0f6e;" onclick="submitWizardUpdate()">
                    ✓ Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let wizardIsEditMode = false;

function editEmployee(id) {
    wizardIsEditMode = true;
    const form = document.getElementById('employeeWizardForm');
    form.action = `/admin/personnel/${id}/update`;
    document.getElementById('wizardEditId').value    = id;
    document.getElementById('wizardTitle').textContent    = 'Edit Employee';
    document.getElementById('wizardSubtitle').textContent = 'Update employee information';
    document.getElementById('step2-register').style.display = 'none';
    document.getElementById('step2-edit').style.display     = 'block';
    document.getElementById('submitBtn').style.display = 'none';
    document.getElementById('updateBtn').style.display = 'none';

    // Reset to step 1 and open
    if (typeof goToWizardStep === 'function') goToWizardStep(1);
    document.getElementById('employeeWizardModal').style.display = 'flex';

    fetch(`/admin/personnel/${id}/edit`)
        .then(r => r.json())
        .then(d => {
            // Step 1 — Personal
            setVal('employee_id', d.employee_id);
            setVal('first_name',  d.first_name);
            setVal('middle_name', d.middle_name);
            setVal('last_name',   d.last_name);
            setVal('suffix',      d.suffix);
            setVal('birth_date',  d.birth_date);
            setVal('place_of_birth', d.place_of_birth);
            setVal('sex',         d.sex);
            setVal('civil_status',d.civil_status);
            setVal('height',      d.height);
            setVal('weight',      d.weight);
            setVal('blood_type',  d.blood_type);
            setVal('citizenship', d.citizenship);

            // Step 3 — Employment
            const emp = d.employment_detail || {};
            const deptSelect = document.getElementById('wizard-department');
            deptSelect.value = emp.department || '';
            // Load designations then set position
            if (emp.department) {
                fetch(`/admin/departments/${emp.department}/designations`)
                    .then(r => r.json())
                    .then(desigs => {
                        const pos = document.getElementById('wizard-position');
                        pos.innerHTML = '<option value="">Select Position</option>';
                        desigs.forEach(dg => {
                            const opt = document.createElement('option');
                            opt.value = dg.title;
                            opt.textContent = dg.title;
                            opt.dataset.employmentType = dg.employment_type || '';
                            opt.dataset.salaryGrade    = dg.salary_grade    || '';
                            pos.appendChild(opt);
                        });
                        pos.disabled = false;
                        pos.value = emp.designation_id || '';
                    });
            }
            const empStatusEl = document.getElementById('wizard-employment-status');
            const salaryEl    = document.getElementById('wizard-salary-grade');
            empStatusEl.removeAttribute('readonly'); empStatusEl.style.cssText = '';
            salaryEl.removeAttribute('readonly');    salaryEl.style.cssText = '';
            setVal('employment_status', emp.employment_status);
            setVal('appointment_date',  emp.appointment_date);
            setVal('salary_grade',      emp.salary_grade);
            setVal('step_increment',    emp.step_increment);

            // Step 4 — Contact
            const mobile    = (d.contacts || []).find(c => c.type === 'mobile');
            const landline  = (d.contacts || []).find(c => c.type === 'landline');
            const emergency = (d.contacts || []).find(c => c.type === 'emergency');
            setVal('mobile_number',             mobile?.number);
            setVal('landline_number',           landline?.number);
            setVal('emergency_contact_person',  emergency?.contact_person);
            setVal('emergency_contact_number',  emergency?.number);
            const addr = (d.addresses || [])[0] || {};
            setVal('house_no',  addr.house_no);
            setVal('street',    addr.street);
            setVal('barangay',  addr.barangay);
            setVal('city',      addr.city);
            setVal('province',  addr.province);
            setVal('zip_code',  addr.zip_code);

            // Step 5 — Gov IDs
            const gov = (d.government_ids || [])[0] || {};
            setVal('gsis_no',       gov.gsis_no);
            setVal('philhealth_no', gov.philhealth_no);
            setVal('pagibig_no',    gov.pagibig_no);
            setVal('tin_no',        gov.tin_no);
            setVal('license_no',    gov.license_no);
        });
}

function setVal(name, value) {
    const el = document.querySelector(`[name="${name}"]`);
    if (el) el.value = value || '';
}

function submitWizardUpdate() {
    document.getElementById('employeeWizardForm').submit();
}

function loadDesignations(deptId) {
    const posSelect  = document.getElementById('wizard-position');
    const statusInput = document.getElementById('wizard-employment-status');
    const gradeInput  = document.getElementById('wizard-salary-grade');
    const hint        = document.getElementById('wizard-position-hint');

    posSelect.innerHTML = '<option value="">Loading...</option>';
    posSelect.disabled  = true;
    statusInput.value   = '';
    gradeInput.value    = '';

    if (!deptId) {
        posSelect.innerHTML = '<option value="">— Select a department first —</option>';
        hint.textContent    = 'Select a department to load available designations.';
        return;
    }

    fetch(`/admin/departments/${deptId}/designations`)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                posSelect.innerHTML = '<option value="">No designations found for this department</option>';
                hint.textContent    = 'No designations are set up for this department yet.';
                return;
            }
            posSelect.innerHTML = '<option value="">Select Position</option>';
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value                   = d.id;
                opt.textContent             = d.title;
                opt.dataset.employmentType  = d.employment_type || '';
                opt.dataset.salaryGrade     = d.salary_grade    || '';
                posSelect.appendChild(opt);
            });
            posSelect.disabled   = false;
            hint.textContent     = `${data.length} designation(s) available. Select one to auto-fill employment details.`;
        })
        .catch(() => {
            posSelect.innerHTML = '<option value="">Error loading designations</option>';
            hint.textContent    = 'Could not load designations. Please try again.';
        });
}

function fillFromDesignation(select) {
    const opt = select.options[select.selectedIndex];
    document.getElementById('wizard-employment-status').value = opt.dataset.employmentType || '';
    document.getElementById('wizard-salary-grade').value      = opt.dataset.salaryGrade    || '';
}
</script>
