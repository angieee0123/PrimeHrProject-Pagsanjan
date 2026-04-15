<!-- Employee Registration Wizard Modal - Database Synchronized -->
<div id="employeeWizardModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; overflow-y:auto;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:700px; margin:20px auto; padding:0; position:relative; box-shadow:0 8px 32px rgba(11,4,77,0.15);">

        <!-- Header with Progress -->
        <div style="background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); color:#fff; padding:24px; border-radius:12px 12px 0 0; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                <div>
                    <h3 style="margin:0; font-size:18px; font-weight:700;">Employee Registration Wizard</h3>
                    <p style="margin:4px 0 0; font-size:13px; opacity:0.9;">Complete all steps to register a new employee</p>
                </div>
                <button onclick="closeEmployeeWizard()"
                    style="background:none; border:none; cursor:pointer; color:#fff; font-size:24px; line-height:1; padding:0; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">&times;</button>
            </div>

            <!-- Progress Indicator - 10 Steps -->
            <div style="display:flex; gap:6px; align-items:center; overflow-x:auto; padding:0 0 8px;">
                <div class="wizard-step active" data-step="1">
                    <div class="wizard-circle">1</div>
                    <span class="wizard-label">Personal</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="2">
                    <div class="wizard-circle">2</div>
                    <span class="wizard-label">Employment</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="3">
                    <div class="wizard-circle">3</div>
                    <span class="wizard-label">Contact</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="4">
                    <div class="wizard-circle">4</div>
                    <span class="wizard-label">Gov IDs</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="5">
                    <div class="wizard-circle">5</div>
                    <span class="wizard-label">Education</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="6">
                    <div class="wizard-circle">6</div>
                    <span class="wizard-label">Work Exp</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="7">
                    <div class="wizard-circle">7</div>
                    <span class="wizard-label">Training</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="8">
                    <div class="wizard-circle">8</div>
                    <span class="wizard-label">Family</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="9">
                    <div class="wizard-circle">9</div>
                    <span class="wizard-label">Documents</span>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="10">
                    <div class="wizard-circle">10</div>
                    <span class="wizard-label">Review</span>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <form id="employeeWizardForm" style="padding:32px;">

            <!-- STEP 1: Personal Information (employees table) -->
            <div class="wizard-content active" data-step="1" style="display:block;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">Personal Information</h4>

                <!-- Employee ID (UNIQUE) -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Employee ID * <span style="color:#0b044d; font-size:11px;">(UNIQUE)</span></label>
                    <input type="text" name="employee_id" placeholder="e.g. PGS-0001" maxlength="255" required
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                </div>

                <!-- Full Name Row (DB: first_name, middle_name, last_name) -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">First Name *</label>
                        <input type="text" name="first_name" placeholder="e.g. Maria" maxlength="255" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Optional" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Last Name *</label>
                        <input type="text" name="last_name" placeholder="e.g. Santos" maxlength="255" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Suffix (DB: suffix) -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Suffix</label>
                    <select name="suffix"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                        <option value="">None</option>
                        <option>Jr.</option>
                        <option>Sr.</option>
                        <option>II</option>
                        <option>III</option>
                        <option>IV</option>
                    </select>
                </div>

                <!-- Birth Date & Sex (DB: birth_date, sex) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Date of Birth *</label>
                        <input type="date" name="birth_date" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Sex *</label>
                        <select name="sex" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <!-- Civil Status & Place of Birth (DB: civil_status, place_of_birth) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Civil Status *</label>
                        <select name="civil_status" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                    <div>
                        <label class="wizard-label-text">Place of Birth</label>
                        <input type="text" name="place_of_birth" placeholder="e.g. Pagsanjan, Laguna" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Physical Description (DB: height, weight, blood_type) -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Height (cm)</label>
                        <input type="number" name="height" step="0.01" placeholder="e.g. 165.5"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Weight (kg)</label>
                        <input type="number" name="weight" step="0.01" placeholder="e.g. 65.5"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Blood Type</label>
                        <select name="blood_type"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
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

                <!-- Citizenship & Email (DB: citizenship, email) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Citizenship</label>
                        <input type="text" name="citizenship" placeholder="e.g. Filipino" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Email</label>
                        <input type="email" name="email" placeholder="e.g. maria@example.com" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
            </div>

            <!-- STEP 2: Employment Details (employment_details table) -->
            <div class="wizard-content" data-step="2" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">Employment Details</h4>

                <!-- Position & Department (DB: position, department) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Position *</label>
                        <input type="text" name="position" placeholder="e.g. Administrative Officer IV" maxlength="255" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Department / Office *</label>
                        <select name="department" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Department</option>
                            <option>Office of the Mayor</option>
                            <option>Office of the Mun. Engineer</option>
                            <option>Municipal Health Office</option>
                            <option>MSWD – Pagsanjan</option>
                            <option>Office of the Mun. Treasurer</option>
                            <option>Municipal Civil Registrar</option>
                            <option>Office of the Mun. Budget</option>
                            <option>Office of the Mun. Agriculturist</option>
                        </select>
                    </div>
                </div>

                <!-- Employment Status (DB: employment_status) -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Employment Type / Status *</label>
                    <select name="employment_status" required
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                        <option value="">Select Type</option>
                        <option value="Permanent">Permanent</option>
                        <option value="Casual">Casual</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Job Order">Job Order</option>
                    </select>
                </div>

                <!-- Appointment Date (DB: appointment_date) -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">Appointment Date *</label>
                    <input type="date" name="appointment_date" required
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                </div>

                <!-- Salary Grade & Step Increment (DB: salary_grade, step_increment) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Salary Grade</label>
                        <input type="text" name="salary_grade" placeholder="e.g. SG-11" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Step Increment</label>
                        <input type="text" name="step_increment" placeholder="e.g. Step 3" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Employment Status Radio (for users table role assignment) -->
                <div style="background:#f8f8fb; padding:12px; border-radius:8px;">
                    <label class="wizard-label-text" style="margin-bottom:10px;">Account Status</label>
                    <div style="display:flex; gap:20px;">
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="radio" name="account_status" value="Active" checked
                                style="cursor:pointer;">
                            Active
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="radio" name="account_status" value="Inactive"
                                style="cursor:pointer;">
                            Inactive
                        </label>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Contact, Address & Government IDs -->
            <div class="wizard-content" data-step="3" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">Contact & Government Information</h4>

                <!-- Contact Section (contacts table) -->
                <div style="background:#f8f8fb; padding:12px; border-radius:8px; margin-bottom:16px;">
                    <h5 style="margin:0; font-size:12px; font-weight:600; color:#0b044d;">📞 Contact Details</h5>
                </div>

                <!-- Mobile & Landline (DB: contacts.type + contacts.number) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Mobile Number</label>
                        <input type="tel" name="mobile_number" placeholder="e.g. +63 9xx xxxx xxx" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Landline Number</label>
                        <input type="tel" name="landline_number" placeholder="e.g. (02) xxxx xxxx" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Emergency Contact (DB: contacts.type='emergency' + contact_person) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
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

                <!-- Address Section (addresses table - residential) -->
                <div style="background:#f8f8fb; padding:12px; border-radius:8px; margin:24px 0 16px;">
                    <h5 style="margin:0; font-size:12px; font-weight:600; color:#0b044d;">📍 Residential Address</h5>
                </div>

                <!-- House No & Street (DB: house_no, street) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">House No.</label>
                        <input type="text" name="house_no" placeholder="e.g. 123" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Street</label>
                        <input type="text" name="street" placeholder="e.g. Main Street" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Barangay & City (DB: barangay, city) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Barangay</label>
                        <input type="text" name="barangay" placeholder="e.g. Maligaya" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">City/Municipality</label>
                        <input type="text" name="city" placeholder="e.g. Pagsanjan" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Province & Zip Code (DB: province, zip_code) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Province</label>
                        <input type="text" name="province" placeholder="e.g. Laguna" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Zip Code</label>
                        <input type="text" name="zip_code" placeholder="e.g. 4010" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Government IDs Section (government_ids table) -->
                <div style="background:#f8f8fb; padding:12px; border-radius:8px; margin:24px 0 16px;">
                    <h5 style="margin:0; font-size:12px; font-weight:600; color:#0b044d;">🪪 Government IDs</h5>
                </div>

                <!-- GSIS & PhilHealth (DB: gsis_no, philhealth_no) -->
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

                <!-- PAG-IBIG & TIN (DB: pagibig_no, tin_no) -->
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

                <!-- License Number (DB: license_no) -->
                <div style="margin-bottom:16px;">
                    <label class="wizard-label-text">License Number</label>
                    <input type="text" name="license_no" placeholder="Professional License" maxlength="255"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                </div>
            </div>

            <!-- STEP 4: Review & Confirmation -->
            <div class="wizard-content" data-step="4" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">Review Information</h4>

                <div id="wizardReviewContent" style="background:#f8f8fb; padding:20px; border-radius:8px; max-height:400px; overflow-y:auto;">
                    <p style="color:#6b6a8a; text-align:center; padding:40px 20px;">Loading review data...</p>
                </div>

                <div style="background:#e8f9ef; border:1px solid #bbf7d0; border-radius:8px; padding:12px; margin-top:16px; display:flex; gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="flex-shrink:0;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <p style="margin:0; font-size:13px; font-weight:600; color:#15803d;">Ready to submit</p>
                        <p style="margin:4px 0 0; font-size:12px; color:#047857;">Please verify all information is correct before submitting.</p>
                    </div>
                </div>
            </div>

        </form>

        <!-- Footer Navigation -->
        <div style="border-top:1px solid #e5e5e8; padding:20px 32px; display:flex; justify-content:space-between; align-items:center; background:#f9f9fb; border-radius:0 0 12px 12px;">
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
                    Submit
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
    flex: 1;
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
    width: 100%;
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
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Input Focus States */
input:focus, select:focus, textarea:focus {
    border-color: #0b044d;
    outline: none;
    box-shadow: 0 0 0 3px rgba(11, 4, 77, 0.1);
}

/* Review Summary Styles */
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
const totalSteps = 4;

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
        } else if (currentStep === totalSteps) {
            submitForm();
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
    // Hide all content
    document.querySelectorAll('.wizard-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));

    // Show current content
    document.querySelector(`.wizard-content[data-step="${currentStep}"]`).style.display = 'block';
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('active');

    // Update buttons
    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('nextBtn').style.display = currentStep < totalSteps ? 'block' : 'none';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';
}

function validateCurrentStep() {
    const form = document.getElementById('employeeWizardForm');
    const currentContent = document.querySelector(`.wizard-content[data-step="${currentStep}"]`);
    const requiredFields = currentContent.querySelectorAll('[required]');

    let isValid = true;
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            field.style.borderColor = '#e5e5e8';
        }
    });

    if (!isValid) {
        alert('Please fill in all required fields marked with *');
    }

    return isValid;
}

function generateReview() {
    const form = document.getElementById('employeeWizardForm');
    const formData = new FormData(form);

    let reviewHTML = `
        <div class="review-section">
            <div class="review-section-title">👤 Personal Information</div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Employee ID</div>
                    <div class="review-value">${formData.get('employee_id')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Full Name</div>
                    <div class="review-value">${formData.get('first_name')} ${formData.get('middle_name') ? formData.get('middle_name') + ' ' : ''}${formData.get('last_name')}${formData.get('suffix') ? ' ' + formData.get('suffix') : ''}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Date of Birth</div>
                    <div class="review-value">${formData.get('birth_date')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Sex / Civil Status</div>
                    <div class="review-value">${formData.get('sex')} / ${formData.get('civil_status')}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Email</div>
                    <div class="review-value">${formData.get('email') || 'Not provided'}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Citizenship</div>
                    <div class="review-value">${formData.get('citizenship') || 'Not provided'}</div>
                </div>
            </div>
        </div>

        <div class="review-section">
            <div class="review-section-title">💼 Employment Details</div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Position</div>
                    <div class="review-value">${formData.get('position')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Department</div>
                    <div class="review-value">${formData.get('department')}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Employment Type</div>
                    <div class="review-value">${formData.get('employment_status')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Appointment Date</div>
                    <div class="review-value">${formData.get('appointment_date')}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Salary Grade</div>
                    <div class="review-value">${formData.get('salary_grade') || 'Not provided'}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Status</div>
                    <div class="review-value">${formData.get('account_status')}</div>
                </div>
            </div>
        </div>

        <div class="review-section">
            <div class="review-section-title">📞 Contact Information</div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Mobile</div>
                    <div class="review-value">${formData.get('mobile_number') || 'Not provided'}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Landline</div>
                    <div class="review-value">${formData.get('landline_number') || 'Not provided'}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Emergency Contact</div>
                    <div class="review-value">${formData.get('emergency_contact_person') || 'Not provided'}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Emergency Number</div>
                    <div class="review-value">${formData.get('emergency_contact_number') || 'Not provided'}</div>
                </div>
            </div>
        </div>

        <div class="review-section">
            <div class="review-section-title">📍 Address</div>
            <div class="review-item">
                <div class="review-label">Residential Address</div>
                <div class="review-value">${[formData.get('house_no'), formData.get('street'), formData.get('barangay'), formData.get('city'), formData.get('province'), formData.get('zip_code')].filter(v => v).join(', ')}</div>
            </div>
        </div>

        <div class="review-section">
            <div class="review-section-title">🪪 Government IDs</div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">GSIS</div>
                    <div class="review-value">${formData.get('gsis_no') || 'Not provided'}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">PhilHealth</div>
                    <div class="review-value">${formData.get('philhealth_no') || 'Not provided'}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">PAG-IBIG</div>
                    <div class="review-value">${formData.get('pagibig_no') || 'Not provided'}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">TIN</div>
                    <div class="review-value">${formData.get('tin_no') || 'Not provided'}</div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('wizardReviewContent').innerHTML = reviewHTML;
}

function submitForm() {
    console.log('Form data ready for submission');
    const form = document.getElementById('employeeWizardForm');
    const formData = new FormData(form);
    
    // Log form data (for debugging)
    console.log('Form Fields:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    alert('✅ Employee data is ready for database submission!\n\nDatabase logic will be implemented in the backend handler.');
    closeEmployeeWizard();
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('employeeWizardForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (currentStep === totalSteps) {
                submitForm();
            }
        });
    }
});
</script>

        <!-- Form Content -->
        <form id="employeeWizardForm" style="padding:32px;">

            <!-- STEP 1: Personal Information -->
            <div class="wizard-content active" data-step="1" style="display:block;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">Personal Information</h4>

                <!-- Full Name Row (DB: first_name, middle_name, last_name) -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">First Name *</label>
                        <input type="text" name="first_name" placeholder="e.g. Maria" maxlength="255" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Optional" maxlength="255"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Last Name *</label>
                        <input type="text" name="last_name" placeholder="e.g. Santos" maxlength="255" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Suffix & Employee ID -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Suffix</label>
                        <select name="suffix"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">None</option>
                            <option>Jr.</option>
                            <option>Sr.</option>
                            <option>II</option>
                            <option>III</option>
                            <option>IV</option>
                        </select>
                    </div>
                    <div>
                        <label class="wizard-label-text">Employee ID *</label>
                        <input type="text" name="employee_id" placeholder="e.g. PGS-0001" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Birth Date & Sex -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Date of Birth *</label>
                        <input type="date" name="birth_date" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Sex *</label>
                        <select name="sex" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <!-- Civil Status & Place of Birth -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Civil Status *</label>
                        <select name="civil_status" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                    <div>
                        <label class="wizard-label-text">Place of Birth</label>
                        <input type="text" name="place_of_birth" placeholder="e.g. Pagsanjan, Laguna"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Physical Description -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Height (cm)</label>
                        <input type="number" name="height" step="0.01" placeholder="e.g. 165.5"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Weight (kg)</label>
                        <input type="number" name="weight" step="0.01" placeholder="e.g. 65.5"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Blood Type</label>
                        <select name="blood_type"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
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

                <!-- Additional Info -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Citizenship</label>
                        <input type="text" name="citizenship" placeholder="e.g. Filipino"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Email</label>
                        <input type="email" name="email" placeholder="e.g. maria@example.com"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
            </div>

            <!-- STEP 2: Employment Details -->
            <div class="wizard-content" data-step="2" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">Employment Details</h4>

                <!-- Position & Department -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Position *</label>
                        <input type="text" name="position" placeholder="e.g. Administrative Officer IV" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Department / Office *</label>
                        <select name="department" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Department</option>
                            <option>Office of the Mayor</option>
                            <option>Office of the Mun. Engineer</option>
                            <option>Municipal Health Office</option>
                            <option>MSWD – Pagsanjan</option>
                            <option>Office of the Mun. Treasurer</option>
                            <option>Municipal Civil Registrar</option>
                            <option>Office of the Mun. Budget</option>
                            <option>Office of the Mun. Agriculturist</option>
                        </select>
                    </div>
                </div>

                <!-- Employment Type & Status -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Employment Type *</label>
                        <select name="employment_status" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="">Select Type</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Casual">Casual</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>
                    <div>
                        <label class="wizard-label-text">Status *</label>
                        <select name="status" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif; background:#fff;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Appointment Date & Salary Grade -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Appointment Date *</label>
                        <input type="date" name="appointment_date" required
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Salary Grade</label>
                        <input type="text" name="salary_grade" placeholder="e.g. SG-11"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Step Increment -->
                <div>
                    <label class="wizard-label-text">Step Increment</label>
                    <input type="text" name="step_increment" placeholder="e.g. Step 3"
                        style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                </div>
            </div>

            <!-- STEP 3: Contact & Address Information -->
            <div class="wizard-content" data-step="3" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">Contact & Address Information</h4>

                <!-- Contact Section Header -->
                <div style="background:#f8f8fb; padding:12px; border-radius:8px; margin-bottom:16px;">
                    <h5 style="margin:0; font-size:12px; font-weight:600; color:#0b044d;">Contact Details</h5>
                </div>

                <!-- Primary Contact -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Mobile Number</label>
                        <input type="tel" name="mobile_number" placeholder="e.g. +63 9xx xxxx xxx"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Landline Number</label>
                        <input type="tel" name="landline_number" placeholder="e.g. (02) xxxx xxxx"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Emergency Contact Person</label>
                        <input type="text" name="emergency_contact_person" placeholder="Full Name"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Emergency Contact Number</label>
                        <input type="tel" name="emergency_contact_number" placeholder="Phone Number"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Address Section Header -->
                <div style="background:#f8f8fb; padding:12px; border-radius:8px; margin:24px 0 16px;">
                    <h5 style="margin:0; font-size:12px; font-weight:600; color:#0b044d;">Residential Address</h5>
                </div>

                <!-- House No & Street -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">House No.</label>
                        <input type="text" name="house_number" placeholder="e.g. 123"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Street</label>
                        <input type="text" name="street" placeholder="e.g. Main Street"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Barangay & City -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Barangay</label>
                        <input type="text" name="barangay" placeholder="e.g. Maligaya"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">City/Municipality</label>
                        <input type="text" name="city" placeholder="e.g. Pagsanjan"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>

                <!-- Province & Zip Code -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="wizard-label-text">Province</label>
                        <input type="text" name="province" placeholder="e.g. Laguna"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                    <div>
                        <label class="wizard-label-text">Zip Code</label>
                        <input type="text" name="zip_code" placeholder="e.g. 4010"
                            style="width:100%; padding:10px 12px; border:1px solid #e5e5e8; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:'Poppins',sans-serif;">
                    </div>
                </div>
            </div>

            <!-- STEP 4: Review & Confirmation -->
            <div class="wizard-content" data-step="4" style="display:none;">
                <h4 style="margin:0 0 20px; font-size:14px; font-weight:600; color:#0b044d;">Review Information</h4>

                <div id="wizardReviewContent" style="background:#f8f8fb; padding:20px; border-radius:8px; max-height:400px; overflow-y:auto;">
                    <p style="color:#6b6a8a; text-align:center; padding:40px 20px;">Loading review data...</p>
                </div>

                <div style="background:#e8f9ef; border:1px solid #bbf7d0; border-radius:8px; padding:12px; margin-top:16px; display:flex; gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="flex-shrink:0;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <p style="margin:0; font-size:13px; font-weight:600; color:#15803d;">Ready to submit</p>
                        <p style="margin:4px 0 0; font-size:12px; color:#047857;">Please verify all information is correct before submitting.</p>
                    </div>
                </div>
            </div>

        </form>

        <!-- Footer Navigation -->
        <div style="border-top:1px solid #e5e5e8; padding:20px 32px; display:flex; justify-content:space-between; align-items:center; background:#f9f9fb; border-radius:0 0 12px 12px;">
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
                    Submit
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
    flex: 1;
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
    width: 100%;
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
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Input Focus States */
input:focus, select:focus, textarea:focus {
    border-color: #0b044d;
    outline: none;
    box-shadow: 0 0 0 3px rgba(11, 4, 77, 0.1);
}

/* Review Summary Styles */
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
const totalSteps = 4;

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
        } else if (currentStep === totalSteps) {
            submitForm();
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
    // Hide all content
    document.querySelectorAll('.wizard-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));

    // Show current content
    document.querySelector(`.wizard-content[data-step="${currentStep}"]`).style.display = 'block';
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('active');

    // Update buttons
    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('nextBtn').style.display = currentStep < totalSteps ? 'block' : 'none';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';
}

function validateCurrentStep() {
    const form = document.getElementById('employeeWizardForm');
    const currentContent = document.querySelector(`.wizard-content[data-step="${currentStep}"]`);
    const requiredFields = currentContent.querySelectorAll('[required]');

    let isValid = true;
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            field.style.borderColor = '#e5e5e8';
        }
    });

    return isValid;
}

function generateReview() {
    const form = document.getElementById('employeeWizardForm');
    const formData = new FormData(form);

    let reviewHTML = `
        <div class="review-section">
            <div class="review-section-title">Personal Information</div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Full Name</div>
                    <div class="review-value">${formData.get('first_name')} ${formData.get('middle_name') ? formData.get('middle_name') + ' ' : ''}${formData.get('last_name')} ${formData.get('suffix') ? formData.get('suffix') : ''}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Employee ID</div>
                    <div class="review-value">${formData.get('employee_id')}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Date of Birth</div>
                    <div class="review-value">${formData.get('birth_date')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Sex</div>
                    <div class="review-value">${formData.get('sex')}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Civil Status</div>
                    <div class="review-value">${formData.get('civil_status')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Email</div>
                    <div class="review-value">${formData.get('email') || 'Not provided'}</div>
                </div>
            </div>
        </div>

        <div class="review-section">
            <div class="review-section-title">Employment Details</div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Position</div>
                    <div class="review-value">${formData.get('position')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Department</div>
                    <div class="review-value">${formData.get('department')}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Employment Type</div>
                    <div class="review-value">${formData.get('employment_status')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Status</div>
                    <div class="review-value">${formData.get('status')}</div>
                </div>
            </div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Appointment Date</div>
                    <div class="review-value">${formData.get('appointment_date')}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Salary Grade</div>
                    <div class="review-value">${formData.get('salary_grade') || 'Not provided'}</div>
                </div>
            </div>
        </div>

        <div class="review-section">
            <div class="review-section-title">Contact Information</div>
            <div class="review-row">
                <div class="review-item">
                    <div class="review-label">Mobile Number</div>
                    <div class="review-value">${formData.get('mobile_number') || 'Not provided'}</div>
                </div>
                <div class="review-item">
                    <div class="review-label">Emergency Contact</div>
                    <div class="review-value">${formData.get('emergency_contact_person') || 'Not provided'}</div>
                </div>
            </div>
        </div>

        <div class="review-section">
            <div class="review-section-title">Address</div>
            <div class="review-item">
                <div class="review-label">Full Address</div>
                <div class="review-value">${formData.get('house_number')} ${formData.get('street')}, ${formData.get('barangay')}, ${formData.get('city')}, ${formData.get('province')} ${formData.get('zip_code')}</div>
            </div>
        </div>
    `;

    document.getElementById('wizardReviewContent').innerHTML = reviewHTML;
}

function submitForm() {
    console.log('Form submitted - Database logic to be implemented');
    alert('Form submission will be processed when database logic is added.');
    closeEmployeeWizard();
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('employeeWizardForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (currentStep === totalSteps) {
                submitForm();
            }
        });
    }
});
</script>
