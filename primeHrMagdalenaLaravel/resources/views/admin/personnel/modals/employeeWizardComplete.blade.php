<!-- Employee Registration Wizard - 6 Steps -->
<div id="employeeWizardModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; overflow-y:auto;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:800px; margin:20px auto; padding:0; position:relative; box-shadow:0 8px 32px rgba(11,4,77,0.15); max-height:90vh; display:flex; flex-direction:column;">

        <!-- Header with Progress -->
        <div style="background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); color:#fff; padding:24px; border-radius:12px 12px 0 0; position:relative; flex-shrink:0;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                <div>
                    <h3 style="margin:0; font-size:18px; font-weight:700;">Employee Registration Wizard</h3>
                    <p style="margin:4px 0 0; font-size:13px; opacity:0.9;"><span id="stepIndicator">Step 1 of 6</span> - Complete all steps to register</p>
                </div>
                <button onclick="closeEmployeeWizard()"
                    style="background:none; border:none; cursor:pointer; color:#fff; font-size:24px; line-height:1; padding:0; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">&times;</button>
            </div>

            <!-- Progress Indicator - Scrollable with Users step -->
            <div style="display:flex; gap:6px; align-items:center; overflow-x:auto; padding:0 0 8px; scroll-behavior:smooth;" id="progressBar">
                <div class="wizard-step active" data-step="1" style="min-width:60px;">
                    <div class="wizard-circle">1</div>
                    <span class="wizard-label">Personal</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="2" style="min-width:60px;">
                    <div class="wizard-circle">2</div>
                    <span class="wizard-label">Account</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="3" style="min-width:60px;">
                    <div class="wizard-circle">3</div>
                    <span class="wizard-label">Employment</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="4" style="min-width:60px;">
                    <div class="wizard-circle">4</div>
                    <span class="wizard-label">Contact</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="5" style="min-width:60px;">
                    <div class="wizard-circle">5</div>
                    <span class="wizard-label">Gov IDs</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="6" style="min-width:60px;">
                    <div class="wizard-circle">6</div>
                    <span class="wizard-label">Review</span>
                </div>
            </div>
        </div>

        <!-- Form Content - Scrollable -->
        <form id="employeeWizardForm" action="{{ route('admin.personnel.store') }}" method="POST" enctype="multipart/form-data" style="padding:32px; overflow-y:auto; flex:1;">
            @csrf

            <!-- STEP 1: Personal Information -->
            <div class="wizard-content active" data-step="1" style="display:block;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">👤 Personal Information</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Employee ID * <span style="color:#0b044d; font-size:11px;">(UNIQUE)</span></label>
                        <input type="text" name="employee_id" placeholder="e.g. PGS-0001" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div></div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">First Name *</label>
                        <input type="text" name="first_name" placeholder="Maria" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Optional" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Last Name *</label>
                        <input type="text" name="last_name" placeholder="Santos" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Suffix</label>
                        <select name="suffix" style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
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
                        <input type="file" name="photo" accept="image/*"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                        <p style="margin:4px 0 0; font-size:11px; color:#6b6a8a;">Supported: JPG, PNG, GIF (Max 5MB)</p>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Date of Birth *</label>
                        <input type="date" name="birth_date"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Place of Birth</label>
                        <input type="text" name="place_of_birth" placeholder="Pagsanjan, Laguna" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Sex *</label>
                        <select name="sex" style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="wizard-label-text">Civil Status *</label>
                        <select name="civil_status" style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Height (cm)</label>
                        <input type="number" name="height" step="0.01" placeholder="165.5"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Weight (kg)</label>
                        <input type="number" name="weight" step="0.01" placeholder="65.5"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Blood Type</label>
                        <select name="blood_type" style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
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
                    <input type="text" name="citizenship" placeholder="Filipino" maxlength="255"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                </div>
            </div>

            <!-- STEP 2: Account Setup (users table) -->
            <div class="wizard-content" data-step="2" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">🔐 Account Setup</h4>

                <div style="background:#e8f5ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px; margin-bottom:16px;">
                    <p style="margin:0; font-size:12px; color:#0369a1;"><strong>ℹ️ Create Login Credentials</strong></p>
                    <p style="margin:4px 0 0; font-size:11px; color:#0369a1;">Set up username, email, and password for system access</p>
                </div>

                <!-- Username (UNIQUE) -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Username * <span style="color:#0b044d; font-size:11px;">(UNIQUE)</span></label>
                    <input type="text" name="username" placeholder="e.g. maria.santos" maxlength="255"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    <p style="margin:4px 0 0; font-size:11px; color:#6b6a8a;">No spaces. Use lowercase letters, numbers, dots, and underscores.</p>
                </div>

                <!-- Email (UNIQUE) -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Email * <span style="color:#0b044d; font-size:11px;">(UNIQUE)</span></label>
                    <input type="email" name="user_email" placeholder="maria.santos@example.com" maxlength="255"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    <p style="margin:4px 0 0; font-size:11px; color:#6b6a8a;">Must be a valid email address for account notifications.</p>
                </div>

                <!-- Password -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Password * <span style="color:#0b044d; font-size:11px;">(Min 8 characters)</span></label>
                    <input type="password" name="password" placeholder="••••••••" maxlength="255"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    <p style="margin:4px 0 0; font-size:11px; color:#6b6a8a;">Use uppercase, lowercase, numbers, and symbols for security.</p>
                </div>

                <!-- Confirm Password -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Confirm Password *</label>
                    <input type="password" name="password_confirm" placeholder="••••••••" maxlength="255"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                </div>

                <!-- Role Assignment -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Role / Access Level * </label>
                    <select name="role"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                        <option value="">Select Role</option>
                        <option value="employee">Employee - Limited access to own records</option>
                        <option value="hr">HR - Full access to all employee records</option>
                        <option value="admin">Admin - System administrator access</option>
                    </select>
                </div>

                <!-- Password Requirements Checklist -->
                <div style="background:#f8f8fb; padding:12px; border-radius:8px; border-left:4px solid #0b044d;">
                    <p style="margin:0 0 8px; font-size:12px; font-weight:600; color:#0b044d;">✓ Password Requirements:</p>
                    <ul style="margin:0; padding-left:20px; font-size:11px; color:#6b6a8a;">
                        <li>At least 8 characters long</li>
                        <li>Contains uppercase letter (A-Z)</li>
                        <li>Contains lowercase letter (a-z)</li>
                        <li>Contains number (0-9)</li>
                        <li>Contains special character (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>

            <!-- STEP 3: Employment Details -->
            <div class="wizard-content" data-step="3" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">💼 Employment Details</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Position *</label>
                        <input type="text" name="position" placeholder="Administrative Officer IV" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Department / Office *</label>
                        <select name="department" style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Employment Type / Status *</label>
                        <select name="employment_status" style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Type</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Casual">Casual</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>
                    <div>
                        <label class="wizard-label-text">Appointment Date *</label>
                        <input type="date" name="appointment_date"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Salary Grade</label>
                        <input type="text" name="salary_grade" placeholder="SG-11" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Step Increment</label>
                        <input type="text" name="step_increment" placeholder="Step 3" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
            </div>

            <!-- STEP 4: Contact Information -->
            <div class="wizard-content" data-step="4" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">📞 Contact Information</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Mobile Number</label>
                        <input type="tel" name="mobile_number" placeholder="+63 9xx xxxx xxx" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Landline Number</label>
                        <input type="tel" name="landline_number" placeholder="(02) xxxx xxxx" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                    <div>
                        <label class="wizard-label-text">Emergency Contact Person</label>
                        <input type="text" name="emergency_contact_person" placeholder="Full Name" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Emergency Contact Number</label>
                        <input type="tel" name="emergency_contact_number" placeholder="Phone Number" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="background:#f8f8fb; padding:12px; border-radius:8px; margin-bottom:16px;">
                    <h5 style="margin:0 0 12px; font-size:12px; font-weight:600; color:#0b044d;">📍 Residential Address</h5>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">House No.</label>
                        <input type="text" name="house_no" placeholder="123" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Street</label>
                        <input type="text" name="street" placeholder="Main Street" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Barangay</label>
                        <input type="text" name="barangay" placeholder="Maligaya" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">City/Municipality</label>
                        <input type="text" name="city" placeholder="Pagsanjan" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label class="wizard-label-text">Province</label>
                        <input type="text" name="province" placeholder="Laguna" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Zip Code</label>
                        <input type="text" name="zip_code" placeholder="4010" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
            </div>

            <!-- STEP 5: Government IDs -->
            <div class="wizard-content" data-step="5" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">🪪 Government IDs</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">GSIS Number</label>
                        <input type="text" name="gsis_no" placeholder="GSIS ID" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">PhilHealth Number</label>
                        <input type="text" name="philhealth_no" placeholder="PhilHealth ID" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">PAG-IBIG Number</label>
                        <input type="text" name="pagibig_no" placeholder="PAG-IBIG ID" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">TIN Number</label>
                        <input type="text" name="tin_no" placeholder="Tax ID" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
                <div>
                    <label class="wizard-label-text">License Number</label>
                    <input type="text" name="license_no" placeholder="Professional License" maxlength="255"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                </div>
            </div>

            <!-- STEP 6: Review -->
            <div class="wizard-content" data-step="6" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">✅ Review All Information</h4>
                <div id="wizardReviewContent" style="background:#f8f8fb; padding:20px; border-radius:8px; max-height:300px; overflow-y:auto;">
                    <p style="color:#6b6a8a; text-align:center; padding:40px 20px;">Loading review data...</p>
                </div>
                <div style="background:#e8f9ef; border:1px solid #bbf7d0; border-radius:8px; padding:12px; margin-top:16px; display:flex; gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="flex-shrink:0;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <p style="margin:0; font-size:13px; font-weight:600; color:#15803d;">✓ All steps completed!</p>
                        <p style="margin:4px 0 0; font-size:12px; color:#047857;">Click Submit to register employee.</p>
                    </div>
                </div>
            </div>

        </form>

        <!-- Footer Navigation -->
        <div style="border-top:1px solid #e5e5e8; padding:20px 32px; display:flex; justify-content:space-between; align-items:center; background:#f9f9fb; border-radius:0 0 12px 12px; flex-shrink:0;">
            <button type="button" onclick="previousStep()" id="prevBtn"
                style="padding:10px 20px; border:1px solid #e5e5e8; border-radius:8px; background:#fff; font-size:13px; font-weight:600; cursor:pointer; color:#0b044d; font-family:'Poppins',sans-serif; display:none;">
                ← Previous
            </button>

            <div style="display:flex; gap:10px; flex:1; justify-content:flex-end;">
                <button type="button" onclick="closeEmployeeWizard()"
                    style="padding:10px 20px; border:1px solid #e5e5e8; border-radius:8px; background:#fff; font-size:13px; font-weight:600; cursor:pointer; color:#6b6a8a; font-family:'Poppins',sans-serif;">
                    Cancel
                </button>

                <button type="button" onclick="nextStep()" id="nextBtn"
                    style="padding:10px 20px; border:none; border-radius:8px; background:#0b044d; color:#fff; font-size:13px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
                    Next →
                </button>

                <button type="submit" id="submitBtn"
                    style="padding:10px 20px; border:none; border-radius:8px; background:#15803d; color:#fff; font-size:13px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; display:none;">
                    ✓ Submit
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Wizard Styles */
.wizard-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    position: relative;
    flex-shrink: 0;
}

.wizard-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 600;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.wizard-step.active .wizard-circle {
    background: #fff;
    color: #0b044d;
    border-color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.wizard-label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
}

.wizard-step.active .wizard-label {
    color: #fff;
    font-weight: 600;
}

.wizard-connector {
    position: absolute;
    top: 18px;
    width: 50px;
    height: 2px;
    background: rgba(255, 255, 255, 0.2);
    z-index: -1;
}

.wizard-label-text {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.wizard-content {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

input:focus, select:focus, textarea:focus {
    border-color: #0b044d;
    outline: none;
    box-shadow: 0 0 0 3px rgba(11, 4, 77, 0.1);
}

.review-section {
    margin-bottom: 16px;
}

.review-section-title {
    font-size: 12px;
    font-weight: 600;
    color: #0b044d;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e5e8;
}

.review-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 8px;
}

.review-item {
    font-size: 12px;
}

.review-label {
    color: #6b6a8a;
    font-size: 11px;
    margin-bottom: 2px;
}

.review-value {
    color: #0b044d;
    font-weight: 600;
}
</style>

<script>
let currentStep = 1;
const totalSteps = 6;

function openEmployeeWizard() {
    document.getElementById('employeeWizardModal').style.display = 'flex';
    currentStep = 1;
    updateWizardUI();
}

function closeEmployeeWizard() {
    document.getElementById('employeeWizardModal').style.display = 'none';
    currentStep = 1;
    document.getElementById('employeeWizardForm').reset();
}

function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            currentStep++;
            updateWizardUI();
            if (currentStep === totalSteps) {
                generateReview();
            }
        }
    }
}

function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateWizardUI();
    }
}

function updateWizardUI() {
    document.querySelectorAll('.wizard-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));

    document.querySelector(`.wizard-content[data-step="${currentStep}"]`).style.display = 'block';
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('active');

    document.getElementById('stepIndicator').textContent = `Step ${currentStep} of ${totalSteps}`;
    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('nextBtn').style.display = currentStep < totalSteps ? 'block' : 'none';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';
}

function validateCurrentStep() {
    // Since we removed 'required' attributes for testing, validation is skipped
    // All fields are optional, so always return true
    return true;
}

function generateReview() {
    const formData = new FormData(document.getElementById('employeeWizardForm'));
    let html = '<div class="review-section"><div class="review-section-title">👤 Personal Info</div>';
    html += `<div class="review-row"><div class="review-item"><div class="review-label">Employee ID</div><div class="review-value">${formData.get('employee_id')}</div></div>`;
    html += `<div class="review-item"><div class="review-label">Full Name</div><div class="review-value">${formData.get('first_name')} ${formData.get('last_name')}</div></div></div></div>`;

    html += '<div class="review-section"><div class="review-section-title">💼 Employment</div>';
    html += `<div class="review-row"><div class="review-item"><div class="review-label">Position</div><div class="review-value">${formData.get('position')}</div></div>`;
    html += `<div class="review-item"><div class="review-label">Department</div><div class="review-value">${formData.get('department')}</div></div></div></div>`;

    document.getElementById('wizardReviewContent').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('employeeWizardForm');

    if (form) {
        // Form submit event - only allow on final step
        form.addEventListener('submit', function(e) {
            if (currentStep !== totalSteps) {
                e.preventDefault();
                console.warn('Form submit blocked - not on final step');
                return false;
            }
            // Allow submission on final step
            console.log('Form submitting from step', currentStep);
            return true;
        });
    }

    // Submit button click handler
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Submit button clicked, current step:', currentStep, 'total steps:', totalSteps);

            if (currentStep === totalSteps) {
                // Submit the form
                console.log('Submitting form with data...');
                document.getElementById('employeeWizardForm').submit();
            } else {
                alert('Please complete all steps before submitting.');
            }
        });
    }
});
</script>



