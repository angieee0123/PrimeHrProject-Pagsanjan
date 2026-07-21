<x-modal-container id="editEmployeeDeductionModal" close="closeEditEmployeeDeductionModal"
                     title="Edit Employee Deduction" subtitle="Update deduction details">
    <form id="editEmployeeDeductionForm" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" id="editDeductionId" name="deduction_id">

        <div class="info-box ded-info-box">
            <div class="ded-info-row">
                <span class="ded-info-label">EMPLOYEE</span>
                <span id="editEmployeeName" class="ded-info-value"></span>
            </div>
            <div class="ded-info-row">
                <span class="ded-info-label">DEDUCTION TYPE</span>
                <span id="editDeductionType" class="ded-info-value"></span>
            </div>
        </div>

        <div class="form-row ded-hidden" id="editLoanFields">
            <div class="form-group ded-col">
                <label class="form-label">Total Amount</label>
                <input type="number" id="editTotalAmount" class="form-input ded-readonly-input" step="0.01" min="0" readonly>
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Remaining Balance</label>
                <input type="number" name="remaining_balance" id="editRemainingBalance" class="form-input" step="0.01" min="0">
            </div>
        </div>

        <div class="form-group ded-hidden" id="editInstallmentField">
            <label class="form-label">Monthly Installment <span class="ded-required">*</span></label>
            <input type="number" name="installment_amount" id="editInstallmentAmount" class="form-input" step="0.01" min="0">
        </div>

        <div class="form-group ded-hidden" id="editFixedAmountField">
            <label class="form-label">Deduction Amount <span class="ded-required">*</span></label>
            <input type="number" name="amount" id="editAmount" class="form-input" step="0.01" min="0">
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Start Date <span class="ded-required">*</span></label>
                <input type="date" name="start_date" id="editStartDate" class="form-input" required>
            </div>
            <div class="form-group ded-col">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" id="editEndDate" class="form-input">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Status <span class="ded-required">*</span></label>
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
</x-modal-container>

@push('scripts')
    @vite('resources/js/admin/deductions/editEmployeeDeductionModal.js')
@endpush
