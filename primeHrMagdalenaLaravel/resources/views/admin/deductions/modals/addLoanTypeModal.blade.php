<x-modal-container id="addLoanTypeModal" close="closeAddLoanTypeModal"
                     title="Add Loan Type" subtitle="Create a new loan type that will appear in deduction options">
    <form id="addLoanTypeForm" action="{{ route('admin.deductions.loan-types.store') }}" method="POST">
        @csrf
        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Loan Provider <span class="ded-required">*</span></label>
                <select name="provider" id="loanProvider" class="form-input" required onchange="updateLoanCode()">
                    <option value="">Select Provider</option>
                    <option value="GSIS">GSIS (Government Service Insurance System)</option>
                    <option value="PAGIBIG">Pag-IBIG (HDMF)</option>
                    <option value="SSS">SSS (Social Security System)</option>
                    <option value="BANK">Bank / Financial Institution</option>
                    <option value="COOP">Cooperative</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Loan Type Code <span class="ded-required">*</span></label>
                <input type="text" name="code" id="loanTypeCode" class="form-input ded-uppercase" placeholder="e.g., GSIS_HOUSING" maxlength="50" required>
                <p class="ded-field-note">Unique identifier (auto-generated, can be edited)</p>
            </div>
            <div class="form-group ded-col-2">
                <label class="form-label">Loan Type Name <span class="ded-required">*</span></label>
                <input type="text" name="name" id="loanTypeName" class="form-input" placeholder="e.g., Housing Loan" maxlength="100" required onchange="updateLoanCode()">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Max Loanable Amount</label>
                <input type="number" name="max_loanable_amount" class="form-input" placeholder="e.g., 500000.00" step="0.01" min="0">
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Interest Rate (%)</label>
                <input type="number" name="interest_rate" class="form-input" placeholder="e.g., 6.00" step="0.01" min="0" max="100">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Max Terms (Months)</label>
                <input type="number" name="max_terms_months" class="form-input" placeholder="e.g., 60" min="1">
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
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="2" placeholder="Brief description of this loan type..."></textarea>
        </div>

        <div class="ded-info-banner is-blue">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" class="ded-info-icon">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <p class="ded-info-text is-blue">
                This loan type will be stored in the <strong>loan_types</strong> table and linked to <strong>deduction_types</strong> via foreign key. It will automatically appear in the "Add Employee Loan" dropdown and can be assigned to multiple employees.
            </p>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="closeAddLoanTypeModal()">Cancel</button>
            <button type="submit" class="btn-submit">Register Loan Type</button>
        </div>
    </form>
</x-modal-container>
