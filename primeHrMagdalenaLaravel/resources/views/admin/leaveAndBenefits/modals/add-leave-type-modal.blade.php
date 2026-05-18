<div id="addLeaveTypeModal" class="modal-overlay" onclick="closeAddLeaveTypeModal(event)" style="display: none;">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Add New Leave Type</h3>
                <p class="modal-subtitle">Create a new leave type for LGU Pagsanjan</p>
            </div>
            <button type="button" class="modal-close" onclick="closeAddLeaveTypeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="addLeaveTypeForm" action="{{ route('admin.leave.types.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Leave Code <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="leave_code" class="form-input" placeholder="e.g., SL" maxlength="10" required>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Leave Name <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="leave_name" class="form-input" placeholder="e.g., Study Leave" maxlength="100" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Annual Limit (Days) <span style="color: #8e1e18;">*</span></label>
                        <input type="number" name="annual_limit" class="form-input" placeholder="e.g., 15.00" step="0.01" min="0" required>
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
                    <label class="form-label">Leave Type Configuration</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="hidden" name="is_accrued" value="0">
                            <input type="checkbox" name="is_accrued" value="1" class="form-checkbox">
                            <span>Accrued (Earned monthly, e.g., 1.25 days/month)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="hidden" name="is_cumulative" value="0">
                            <input type="checkbox" name="is_cumulative" value="1" class="form-checkbox">
                            <span>Cumulative (Unused days carry over to next year)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="hidden" name="requires_6_months" value="0">
                            <input type="checkbox" name="requires_6_months" value="1" class="form-checkbox">
                            <span>Requires 6 Months Service (CSC requirement)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="hidden" name="is_monetizable" value="0">
                            <input type="checkbox" name="is_monetizable" value="1" class="form-checkbox">
                            <span>Monetizable (Can be converted to cash)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="hidden" name="requires_attachment" value="0">
                            <input type="checkbox" name="requires_attachment" value="1" class="form-checkbox">
                            <span>Requires Attachment (Force upload before submission)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Attachment Instructions</label>
                    <textarea name="attachment_info" class="form-input" rows="2" placeholder="e.g., Medical certificate required if more than 2 consecutive days"></textarea>
                    <p style="font-size: 11px; color: #6b6a8a; margin: 4px 0 0 0;">Instructions shown to employees when filing this leave type</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Attach Supporting Document (Optional)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="document" id="leaveTypeDocument" class="file-input" accept=".pdf" onchange="updateFileName(this)">
                        <label for="leaveTypeDocument" class="file-upload-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span id="fileNameDisplay">Choose PDF file or drag here</span>
                        </label>
                    </div>
                    <p style="font-size: 11px; color: #6b6a8a; margin: 4px 0 0 0;">Upload policy document, memo, or reference file (PDF only, max 5MB)</p>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddLeaveTypeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Add Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
