<div id="editLoanTypeModal" class="modal-overlay" onclick="closeEditLoanTypeModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Loan Type</h3>
                <p class="modal-subtitle">Update loan type information</p>
            </div>
            <button class="modal-close" onclick="closeEditLoanTypeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="editLoanTypeForm" method="POST">
                @csrf
                @method('PUT')
                
                <input type="hidden" id="editLoanTypeId" name="loan_type_id">
                
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Loan Provider <span style="color: #8e1e18;">*</span></label>
                        <select name="provider" id="editLoanProvider" class="form-input" required disabled>
                            <option value="">Select Provider</option>
                            <option value="GSIS">GSIS (Government Service Insurance System)</option>
                            <option value="PAGIBIG">Pag-IBIG (HDMF)</option>
                            <option value="SSS">SSS (Social Security System)</option>
                            <option value="BANK">Bank / Financial Institution</option>
                            <option value="COOP">Cooperative</option>
                            <option value="OTHER">Other</option>
                        </select>
                        <p style="font-size: 11px; color: #6b6a8a; margin: 4px 0 0 0;">Provider cannot be changed</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Loan Type Code <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="code" id="editLoanTypeCode" class="form-input" readonly style="background: #f7f6ff; cursor: not-allowed;">
                        <p style="font-size: 11px; color: #6b6a8a; margin: 4px 0 0 0;">Code cannot be changed</p>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Loan Type Name <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="name" id="editLoanTypeName" class="form-input" placeholder="e.g., Housing Loan" maxlength="100" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Max Loanable Amount</label>
                        <input type="number" name="max_loanable_amount" id="editMaxLoanable" class="form-input" placeholder="e.g., 500000.00" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Interest Rate (%)</label>
                        <input type="number" name="interest_rate" id="editInterestRate" class="form-input" placeholder="e.g., 6.00" step="0.01" min="0" max="100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Max Terms (Months)</label>
                        <input type="number" name="max_terms_months" id="editMaxTerms" class="form-input" placeholder="e.g., 60" min="1">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Status <span style="color: #8e1e18;">*</span></label>
                        <select name="is_active" id="editIsActive" class="form-input" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editDescription" class="form-input" rows="2" placeholder="Brief description of this loan type..."></textarea>
                </div>

                <div id="editEmployeesUsingWarning" style="display: none; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin-top: 16px;">
                    <div style="display: flex; align-items: start; gap: 10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <p style="margin: 0; font-size: 12px; color: #856404; line-height: 1.5;">
                            <strong>Warning:</strong> This loan type is currently assigned to <strong id="editEmployeesCount">0</strong> employee(s). Changes will affect their loan records.
                        </p>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditLoanTypeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Update Loan Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Reuse styles from addLoanTypeModal */
</style>
