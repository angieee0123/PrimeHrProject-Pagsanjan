<div id="addDeductionTypeModal" class="modal-overlay" onclick="closeAddDeductionTypeModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Add Deduction Type</h3>
                <p class="modal-subtitle">Create a new deduction type for payroll processing</p>
            </div>
            <button class="modal-close" onclick="closeAddDeductionTypeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="addDeductionTypeForm" action="{{ route('admin.deductions.types.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Code <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="code" class="form-input" placeholder="e.g., GSIS" maxlength="50" required>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Name <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="e.g., GSIS Contribution" maxlength="100" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Category <span style="color: #8e1e18;">*</span></label>
                        <select name="category" class="form-input" required>
                            <option value="">Select Category</option>
                            <option value="MANDATORY">Mandatory</option>
                            <option value="LOAN">Loan</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Computation Type <span style="color: #8e1e18;">*</span></label>
                        <select name="computation_type" class="form-input" id="computationType" required>
                            <option value="">Select Type</option>
                            <option value="PERCENTAGE">Percentage</option>
                            <option value="FIXED">Fixed Amount</option>
                            <option value="CUSTOM">Custom</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" id="rateAmountRow">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" id="rateLabel">Rate (%)</label>
                        <input type="number" name="rate" class="form-input" id="rateInput" placeholder="e.g., 9.00" step="0.01" min="0">
                        <small class="field-hint" id="rateHint" style="display: none;"></small>
                    </div>
                    <div class="form-group" style="flex: 1;" id="baseSalaryGroup">
                        <label class="form-label">Base Salary</label>
                        <select name="base_salary" class="form-input" id="baseSalarySelect">
                            <option value="">None</option>
                            <option value="BASIC">Basic Salary</option>
                            <option value="GROSS">Gross Salary</option>
                            <option value="MONTHLY">Monthly Salary</option>
                            <option value="CUSTOM">Custom</option>
                        </select>
                        <small class="field-hint" id="baseSalaryHint" style="display: none;"></small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Max Amount</label>
                        <input type="number" name="max_amount" class="form-input" id="maxAmountInput" placeholder="e.g., 100.00" step="0.01" min="0">
                        <small class="field-hint" id="maxAmountHint" style="display: none;"></small>
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
                    <label class="form-label">Deduction Type <span style="color: #8e1e18;">*</span></label>
                    <select name="deducted_from_employee" class="form-input" required>
                        <option value="1">Employee Share (Deducted from salary)</option>
                        <option value="0">Employer/Government Share (Record-keeping only)</option>
                    </select>
                    <small class="field-hint" style="display: block; margin-top: 6px;">
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
    max-width: 600px;
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

.field-hint {
    font-size: 11px;
    color: #6b6a8a;
    margin-top: 4px;
    font-style: italic;
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

<script>
function openAddDeductionTypeModal() {
    document.getElementById('addDeductionTypeModal').classList.add('active');
}

function closeAddDeductionTypeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('addDeductionTypeModal').classList.remove('active');
}

document.getElementById('computationType')?.addEventListener('change', function() {
    const rateLabel = document.getElementById('rateLabel');
    const rateInput = document.getElementById('rateInput');
    const rateHint = document.getElementById('rateHint');
    const baseSalaryGroup = document.getElementById('baseSalaryGroup');
    const baseSalarySelect = document.getElementById('baseSalarySelect');
    const baseSalaryHint = document.getElementById('baseSalaryHint');
    const maxAmountGroup = document.getElementById('maxAmountGroup');
    const maxAmountInput = document.getElementById('maxAmountInput');
    const maxAmountHint = document.getElementById('maxAmountHint');
    
    // Reset hints
    rateHint.style.display = 'none';
    baseSalaryHint.style.display = 'none';
    maxAmountHint.style.display = 'none';
    
    if (this.value === 'PERCENTAGE') {
        // Percentage: Show all fields
        rateLabel.textContent = 'Rate (%)';
        rateInput.placeholder = 'e.g., 9.00';
        rateHint.textContent = 'Percentage to deduct from base salary';
        rateHint.style.display = 'block';
        
        baseSalaryGroup.style.display = 'flex';
        baseSalarySelect.value = '';
        baseSalaryHint.textContent = 'Select what salary component to calculate from';
        baseSalaryHint.style.display = 'block';
        
        maxAmountGroup.style.display = 'flex';
        maxAmountInput.value = '';
        maxAmountHint.textContent = 'Optional: Cap the deduction amount (e.g., Pag-IBIG max ₱100)';
        maxAmountHint.style.display = 'block';
        
    } else if (this.value === 'FIXED') {
        // Fixed: Hide base salary, show amount and optional max
        rateLabel.textContent = 'Fixed Amount';
        rateInput.placeholder = 'e.g., 500.00';
        rateHint.textContent = 'Fixed amount to deduct (e.g., union dues, uniform)';
        rateHint.style.display = 'block';
        
        baseSalaryGroup.style.display = 'none';
        baseSalarySelect.value = '';
        
        maxAmountGroup.style.display = 'flex';
        maxAmountInput.value = '';
        maxAmountHint.textContent = 'Optional: Usually not needed for fixed amounts';
        maxAmountHint.style.display = 'block';
        
    } else if (this.value === 'CUSTOM') {
        // Custom: Show all but with different hints
        rateLabel.textContent = 'Rate/Amount';
        rateInput.placeholder = 'N/A';
        rateHint.textContent = 'Custom logic will be used (e.g., withholding tax)';
        rateHint.style.display = 'block';
        
        baseSalaryGroup.style.display = 'flex';
        baseSalarySelect.value = 'CUSTOM';
        baseSalaryHint.textContent = 'Set to Custom for custom calculation logic';
        baseSalaryHint.style.display = 'block';
        
        maxAmountGroup.style.display = 'flex';
        maxAmountInput.value = '';
        maxAmountHint.textContent = 'Depends on custom logic implementation';
        maxAmountHint.style.display = 'block';
        
    } else {
        // Default: Show all fields
        rateLabel.textContent = 'Rate/Amount';
        rateInput.placeholder = 'Enter value';
        baseSalaryGroup.style.display = 'flex';
        maxAmountGroup.style.display = 'flex';
    }
});
</script>
