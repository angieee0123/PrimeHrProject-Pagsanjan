<x-modal-container id="addDeductionTypeModal" close="closeAddDeductionTypeModal"
                     title="Add Deduction Type" subtitle="Create a new deduction type for payroll processing">
    <form id="addDeductionTypeForm" action="{{ route('admin.deductions.types.store') }}" method="POST">
        @csrf
        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Code <span class="ded-required">*</span></label>
                <input type="text" name="code" class="form-input" placeholder="e.g., GSIS" maxlength="50" required>
            </div>
            <div class="form-group ded-col-2">
                <label class="form-label">Name <span class="ded-required">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="e.g., GSIS Contribution" maxlength="100" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Category <span class="ded-required">*</span></label>
                <select name="category" class="form-input" required>
                    <option value="">Select Category</option>
                    <option value="MANDATORY">Mandatory</option>
                    <option value="LOAN">Loan</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Computation Type <span class="ded-required">*</span></label>
                <select name="computation_type" class="form-input" id="computationType" required>
                    <option value="">Select Type</option>
                    <option value="PERCENTAGE">Percentage</option>
                    <option value="FIXED">Fixed Amount</option>
                    <option value="CUSTOM">Custom</option>
                </select>
            </div>
        </div>

        <div class="form-row" id="rateAmountRow">
            <div class="form-group ded-col">
                <label class="form-label" id="rateLabel">Rate (%)</label>
                <input type="number" name="rate" class="form-input" id="rateInput" placeholder="e.g., 9.00" step="0.01" min="0">
                <small class="field-hint ded-hidden" id="rateHint"></small>
            </div>
            <div class="form-group ded-col" id="baseSalaryGroup">
                <label class="form-label">Base Salary</label>
                <select name="base_salary" class="form-input" id="baseSalarySelect">
                    <option value="">None</option>
                    <option value="BASIC">Basic Salary</option>
                    <option value="GROSS">Gross Salary</option>
                    <option value="MONTHLY">Monthly Salary</option>
                    <option value="CUSTOM">Custom</option>
                </select>
                <small class="field-hint ded-hidden" id="baseSalaryHint"></small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Max Amount</label>
                <input type="number" name="max_amount" class="form-input" id="maxAmountInput" placeholder="e.g., 100.00" step="0.01" min="0">
                <small class="field-hint ded-hidden" id="maxAmountHint"></small>
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Status <span class="ded-required">*</span></label>
                <select name="is_active" class="form-input" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Deduction Type <span class="ded-required">*</span></label>
            <select name="deducted_from_employee" class="form-input" required>
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
            <textarea name="description" class="form-input" rows="2" placeholder="Brief description of this deduction type..."></textarea>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="closeAddDeductionTypeModal()">Cancel</button>
            <button type="submit" class="btn-submit">Add Deduction Type</button>
        </div>
    </form>
</x-modal-container>

@push('scripts')
    @vite('resources/js/admin/deductions/addDeductionTypeModal.js')
@endpush
