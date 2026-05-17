<div id="editDeductionTypeModal" class="modal-overlay" onclick="closeEditDeductionTypeModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Deduction Type</h3>
                <p class="modal-subtitle">Update deduction type information</p>
            </div>
            <button class="modal-close" onclick="closeEditDeductionTypeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="editDeductionTypeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Code <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="code" id="edit_code" class="form-input" maxlength="50" required readonly style="background: #f7f6ff;">
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Name <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-input" maxlength="100" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Category <span style="color: #8e1e18;">*</span></label>
                        <select name="category" id="edit_category" class="form-input" required>
                            <option value="MANDATORY">Mandatory</option>
                            <option value="LOAN">Loan</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Computation Type <span style="color: #8e1e18;">*</span></label>
                        <select name="computation_type" id="edit_computation_type" class="form-input" required>
                            <option value="PERCENTAGE">Percentage</option>
                            <option value="FIXED">Fixed Amount</option>
                            <option value="CUSTOM">Custom</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" id="edit_rateLabel">Rate (%)</label>
                        <input type="number" name="rate" id="edit_rate" class="form-input" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="flex: 1;">
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
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Max Amount</label>
                        <input type="number" name="max_amount" id="edit_max_amount" class="form-input" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Status <span style="color: #8e1e18;">*</span></label>
                        <select name="is_active" id="edit_is_active" class="form-input" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deduction Type <span style="color: #8e1e18;">*</span></label>
                    <select name="deducted_from_employee" id="edit_deducted_from_employee" class="form-input" required>
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
                    <textarea name="description" id="edit_description" class="form-input" rows="2"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditDeductionTypeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Update Deduction Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditDeductionTypeModal() {
    document.getElementById('editDeductionTypeModal').classList.add('active');
}

function closeEditDeductionTypeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('editDeductionTypeModal').classList.remove('active');
}

function editDeductionType(code) {
    // Fetch deduction type data
    fetch(`/admin/deductions/types/${code}`)
        .then(response => response.json())
        .then(data => {
            // Populate form fields
            document.getElementById('edit_code').value = data.code;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_category').value = data.category;
            document.getElementById('edit_computation_type').value = data.computation_type;
            document.getElementById('edit_rate').value = data.percentage_rate || '';
            document.getElementById('edit_base_salary').value = data.base_salary_type || '';
            document.getElementById('edit_max_amount').value = data.max_amount || '';
            document.getElementById('edit_is_active').value = data.is_active ? '1' : '0';
            document.getElementById('edit_deducted_from_employee').value = data.deducted_from_employee ? '1' : '0';
            document.getElementById('edit_description').value = data.description || '';
            
            // Set form action
            document.getElementById('editDeductionTypeForm').action = `/admin/deductions/types/${code}`;
            
            // Update label based on computation type
            const rateLabel = document.getElementById('edit_rateLabel');
            if (data.computation_type === 'PERCENTAGE') {
                rateLabel.textContent = 'Rate (%)';
            } else if (data.computation_type === 'FIXED') {
                rateLabel.textContent = 'Amount';
            } else {
                rateLabel.textContent = 'Rate/Amount';
            }
            
            // Open modal
            openEditDeductionTypeModal();
        })
        .catch(error => {
            console.error('Error fetching deduction type:', error);
            alert('Failed to load deduction type data. Please try again.');
        });
}

document.getElementById('edit_computation_type')?.addEventListener('change', function() {
    const rateLabel = document.getElementById('edit_rateLabel');
    const rateInput = document.getElementById('edit_rate');
    
    if (this.value === 'PERCENTAGE') {
        rateLabel.textContent = 'Rate (%)';
        rateInput.placeholder = 'e.g., 9.00';
    } else if (this.value === 'FIXED') {
        rateLabel.textContent = 'Amount';
        rateInput.placeholder = 'e.g., 500.00';
    } else {
        rateLabel.textContent = 'Rate/Amount';
        rateInput.placeholder = 'N/A';
    }
});
</script>
