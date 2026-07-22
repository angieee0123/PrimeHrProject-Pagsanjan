{{-- Add Employee Modal --}}
<div class="modal-overlay" id="addEmployeeModal" onclick="closeAddEmployee()">
    <div class="modal-box" style="max-width:560px" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon" style="background:linear-gradient(135deg,#0b044d,#150c63)">
                    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                </div>
                <div>
                    <span class="modal-eyebrow">EMPLOYEE MANAGEMENT</span>
                    <h3 class="modal-title">Add New Employee</h3>
                    <p class="modal-sub">Fill in the details to register a new employee</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeAddEmployee()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:20px 24px;max-height:65vh;overflow-y:auto">
            <form id="addEmployeeForm" onsubmit="submitAddEmployee(event)">
                <div class="form-section-label">PERSONAL INFORMATION</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="first_name" placeholder="e.g. Juan" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="last_name" placeholder="e.g. Dela Cruz" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-input" name="middle_name" placeholder="e.g. Santos">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth <span class="form-required">*</span></label>
                        <input type="date" class="form-input" name="dob" required>
                    </div>
                </div>

                <div class="form-section-label" style="margin-top:18px">EMPLOYMENT DETAILS</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Type <span class="form-required">*</span></label>
                        <select class="form-input" name="emp_type" required>
                            <option value="">Select type</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Temporary">Temporary</option>
                            <option value="Coterminous">Coterminous</option>
                            <option value="Casual">Casual</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Department <span class="form-required">*</span></label>
                        <select class="form-input" name="department" required>
                            <option value="">Select department</option>
                            <option>Administration</option>
                            <option>Engineering</option>
                            <option>Health</option>
                            <option>Finance</option>
                            <option>HRMO</option>
                            <option>General Services</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Position <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="position" placeholder="e.g. Administrative Officer II" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Hired <span class="form-required">*</span></label>
                        <input type="date" class="form-input" name="date_hired" required>
                    </div>
                </div>

                <div class="form-section-label" style="margin-top:18px">CONTACT INFORMATION</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" name="email" placeholder="e.g. juan@lgu.gov.ph">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-input" name="contact" placeholder="e.g. 09XX-XXX-XXXX">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:16px 24px 24px;border-top:1px solid #e5e4f0">
            <x-modal-btn variant="ghost" onclick="closeAddEmployee()">Cancel</x-modal-btn>
            <x-modal-btn onclick="submitAddEmployee(event)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Employee
            </x-modal-btn>
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/dashboard/addEmployeeModal.js')
@endpush
