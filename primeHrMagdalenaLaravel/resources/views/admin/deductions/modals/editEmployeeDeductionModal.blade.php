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
                <input type="hidden" id="editDeductionId" name="deduction_id">
                
                <div class="info-box" style="background: #f7f6ff; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-size: 11px; color: #6b6a8a; font-weight: 600;">EMPLOYEE</span>
                        <span id="editEmployeeName" style="font-size: 13px; color: #0b044d; font-weight: 600;"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 11px; color: #6b6a8a; font-weight: 600;">DEDUCTION TYPE</span>
                        <span id="editDeductionType" style="font-size: 13px; color: #0b044d; font-weight: 600;"></span>
                    </div>
                </div>

                <div class="form-row" id="editLoanFields" style="display: none;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Total Amount</label>
                        <input type="number" id="editTotalAmount" class="form-input" step="0.01" min="0" readonly style="background: #f7f6ff; cursor: not-allowed;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Remaining Balance</label>
                        <input type="number" name="remaining_balance" id="editRemainingBalance" class="form-input" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-group" id="editInstallmentField" style="display: none;">
                    <label class="form-label">Monthly Installment <span style="color: #8e1e18;">*</span></label>
                    <input type="number" name="installment_amount" id="editInstallmentAmount" class="form-input" step="0.01" min="0">
                </div>

                <div class="form-group" id="editFixedAmountField" style="display: none;">
                    <label class="form-label">Deduction Amount <span style="color: #8e1e18;">*</span></label>
                    <input type="number" name="amount" id="editAmount" class="form-input" step="0.01" min="0">
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Start Date <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="start_date" id="editStartDate" class="form-input" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="editEndDate" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span style="color: #8e1e18;">*</span></label>
                    <select name="status" id="editStatus" class="form-input" required>
                        <option value="ACTIVE">Active</option>
                        <option value="SUSPENDED">Suspended</option>
                        <option value="COMPLETED">Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" id="editRemarks" class="form-input" rows="2"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditEmployeeDeductionModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Update Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.info-box {
    background: #f7f6ff;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 16px;
}
</style>

<script>
function openEditEmployeeDeductionModal() {
    document.getElementById('editEmployeeDeductionModal').classList.add('active');
}

function closeEditEmployeeDeductionModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('editEmployeeDeductionModal').classList.remove('active');
    document.getElementById('editEmployeeDeductionForm').reset();
}
</script>
