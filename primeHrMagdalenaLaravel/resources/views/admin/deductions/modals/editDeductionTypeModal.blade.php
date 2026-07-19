<x-modal-container id="editDeductionTypeModal" close="closeEditDeductionTypeModal"
                     title="Edit Deduction Type" subtitle="Update deduction type information">
    <form id="editDeductionTypeForm" method="POST">
        @csrf
        @method('PUT')
        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Code <span class="ded-required">*</span></label>
                <input type="text" name="code" id="edit_code" class="form-input ded-readonly-input" maxlength="50" required readonly>
            </div>
            <div class="form-group ded-col-2">
                <label class="form-label">Name <span class="ded-required">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-input" maxlength="100" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Category <span class="ded-required">*</span></label>
                <select name="category" id="edit_category" class="form-input" required>
                    <option value="MANDATORY">Mandatory</option>
                    <option value="LOAN">Loan</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Computation Type <span class="ded-required">*</span></label>
                <select name="computation_type" id="edit_computation_type" class="form-input" required>
                    <option value="PERCENTAGE">Percentage</option>
                    <option value="FIXED">Fixed Amount</option>
                    <option value="CUSTOM">Custom</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label" id="edit_rateLabel">Rate (%)</label>
                <input type="number" name="rate" id="edit_rate" class="form-input" step="0.01" min="0">
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Base Salary</label>
                <select name="base_salary" id="edit_base_salary" class="form-input">
                    <option value="">None</option>
                    <option value="BASIC">Basic Salary</option>
                    <option value="GROSS">Gross Salary</option>
                    <option value="MONTHLY">Monthly Salary</option>
                    <option value="CUSTOM">Custom</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Max Amount</label>
                <input type="number" name="max_amount" id="edit_max_amount" class="form-input" step="0.01" min="0">
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Status <span class="ded-required">*</span></label>
                <select name="is_active" id="edit_is_active" class="form-input" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Deduction Type <span class="ded-required">*</span></label>
            <select name="deducted_from_employee" id="edit_deducted_from_employee" class="form-input" required>
                <option value="1">Employee Share (Deducted from salary)</option>
                <option value="0">Employer/Government Share (Record-keeping only)</option>
            </select>
            <small class="field-hint ded-inline-note">
                Select "Employee Share" if this will be deducted from employee's salary.<br>
                Select "Employer/Government Share" if this is paid by the government/employer (e.g., government's GSIS contribution).
            </small>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" id="edit_description" class="form-input" rows="2"></textarea>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="closeEditDeductionTypeModal()">Cancel</button>
            <button type="submit" class="btn-submit">Update Deduction Type</button>
        </div>
    </form>
</x-modal-container>

@push('scripts')
    @vite('resources/js/admin/deductions/editDeductionTypeModal.js')
@endpush
