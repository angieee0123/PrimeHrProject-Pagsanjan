<div id="addLoanTypeModal" class="modal-overlay" onclick="closeAddLoanTypeModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Add Loan Type</h3>
                <p class="modal-subtitle">Create a new loan type that will appear in deduction options</p>
            </div>
            <button class="modal-close" onclick="closeAddLoanTypeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="addLoanTypeForm" action="{{ route('admin.deductions.loan-types.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Loan Provider <span style="color: #8e1e18;">*</span></label>
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
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Loan Type Code <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="code" id="loanTypeCode" class="form-input" placeholder="e.g., GSIS_HOUSING" maxlength="50" required style="text-transform: uppercase;">
                        <p style="font-size: 11px; color: #6b6a8a; margin: 4px 0 0 0;">Unique identifier (auto-generated, can be edited)</p>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Loan Type Name <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="name" id="loanTypeName" class="form-input" placeholder="e.g., Housing Loan" maxlength="100" required onchange="updateLoanCode()">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Max Loanable Amount</label>
                        <input type="number" name="max_loanable_amount" class="form-input" placeholder="e.g., 500000.00" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Interest Rate (%)</label>
                        <input type="number" name="interest_rate" class="form-input" placeholder="e.g., 6.00" step="0.01" min="0" max="100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Max Terms (Months)</label>
                        <input type="number" name="max_terms_months" class="form-input" placeholder="e.g., 60" min="1">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Status <span style="color: #8e1e18;">*</span></label>
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

                <div style="background: #e3f2fd; border: 1px solid #90caf9; border-radius: 8px; padding: 12px; display: flex; align-items: start; gap: 10px; margin-top: 16px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <p style="margin: 0; font-size: 12px; color: #0d47a1; line-height: 1.5;">
                        This loan type will be stored in the <strong>loan_types</strong> table and linked to <strong>deduction_types</strong> via foreign key. It will automatically appear in the "Add Employee Loan" dropdown and can be assigned to multiple employees.
                    </p>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddLoanTypeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Register Loan Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(11, 4, 77, 0.4);
    backdrop-filter: blur(4px);
    z-index: 9999;
    animation: fadeIn 0.2s ease;
}

.modal-overlay.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-container {
    background: #fff;
    border-radius: 12px;
    width: 90%;
    max-width: 650px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(11, 4, 77, 0.3);
    animation: slideUp 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px;
    border-bottom: 1px solid #f0effe;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #0b044d;
    margin: 0 0 4px 0;
}

.modal-subtitle {
    font-size: 12px;
    color: #6b6a8a;
    margin: 0;
}

.modal-close {
    background: transparent;
    border: none;
    color: #6b6a8a;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f7f6ff;
    color: #0b044d;
}

.modal-body {
    padding: 24px;
    max-height: calc(90vh - 140px);
    overflow-y: auto;
}

.form-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: #0b044d;
    margin-bottom: 6px;
}

.form-input {
    padding: 10px 12px;
    border: 1px solid #e5e3f8;
    border-radius: 6px;
    font-size: 13px;
    color: #0b044d;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #0b044d;
    box-shadow: 0 0 0 3px rgba(11, 4, 77, 0.1);
}

.form-input::placeholder {
    color: #b3b1c8;
}

textarea.form-input {
    resize: vertical;
    min-height: 60px;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #f0effe;
}

.btn-cancel {
    padding: 10px 20px;
    border: 1px solid #e5e3f8;
    background: #fff;
    color: #6b6a8a;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.btn-cancel:hover {
    background: #f7f6ff;
    border-color: #0b044d;
    color: #0b044d;
}

.btn-submit {
    padding: 10px 20px;
    border: none;
    background: #0b044d;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.btn-submit:hover {
    background: #1a0f6e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(11, 4, 77, 0.3);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
}
</style>
