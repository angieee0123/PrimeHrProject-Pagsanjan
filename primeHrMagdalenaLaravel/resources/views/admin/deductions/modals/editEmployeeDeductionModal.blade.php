<div id="editEmployeeDeductionModal" class="modal-overlay" onclick="closeEditEmployeeDeductionModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Employee Deduction</h3>
                <p class="modal-subtitle">Update deduction details</p>
            </div>
            <button class="modal-close" onclick="closeEditEmployeeDeductionModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="editEmployeeDeductionForm" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="form-label">Employee</label>
                    <input type="text" id="edit_employee_name" class="form-input" readonly style="background: #f7f6ff;">
                </div>

                <div class="form-group">
                    <label class="form-label">Deduction Type</label>
                    <input type="text" id="edit_deduction_name" class="form-input" readonly style="background: #f7f6ff;">
                </div>

                <div class="form-row" id="edit_loanFields" style="display: none;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Total Amount</label>
                        <input type="number" name="total_amount" id="edit_total_amount" class="form-input" step="0.01" min="0" readonly style="background: #f7f6ff;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Remaining Balance</label>
                        <input type="number" name="remaining_balance" id="edit_remaining_balance" class="form-input" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-row" id="edit_loanInstallment" style="display: none;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Installment Amount <span style="color: #8e1e18;">*</span></label>
                        <input type="number" name="installment_amount" id="edit_installment_amount" class="form-input" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-group" id="edit_fixedAmountField" style="display: none;">
                    <label class="form-label">Deduction Amount <span style="color: #8e1e18;">*</span></label>
                    <input type="number" name="amount" id="edit_amount" class="form-input" step="0.01" min="0">
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Start Date <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="start_date" id="edit_start_date" class="form-input" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="edit_end_date" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span style="color: #8e1e18;">*</span></label>
                    <select name="status" id="edit_status" class="form-input" required>
                        <option value="ACTIVE">Active</option>
                        <option value="SUSPENDED">Suspended</option>
                        <option value="COMPLETED">Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" id="edit_remarks" class="form-input" rows="2"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditEmployeeDeductionModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Update Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditEmployeeDeductionModal() {
    document.getElementById('editEmployeeDeductionModal').classList.add('active');
}

function closeEditEmployeeDeductionModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('editEmployeeDeductionModal').classList.remove('active');
}

function editEmployeeDeduction(id) {
    // This would typically fetch data via AJAX
    // For now, showing the modal
    openEditEmployeeDeductionModal();
    
    // Example: Fetch and populate data
    // fetch(`/admin/deductions/employee/${id}`)
    //     .then(response => response.json())
    //     .then(data => {
    //         document.getElementById('edit_employee_name').value = data.employee_name;
    //         document.getElementById('edit_deduction_name').value = data.deduction_name;
    //         // ... populate other fields
    //         document.getElementById('editEmployeeDeductionForm').action = `/admin/deductions/employee/${id}`;
    //     });
}
</script>
