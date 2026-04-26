<!-- Correct Attendance Time Modal -->
<div id="correctModal" class="modal-overlay" style="display: none;" onclick="closeCorrectModal()">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">CORRECT ATTENDANCE TIME</span>
                <h3 class="modal-title" id="correctEmployeeName"></h3>
                <p class="modal-sub" id="correctDate"></p>
            </div>
            <button class="modal-close" onclick="closeCorrectModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="correctForm" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" id="correctAttendanceId" name="attendance_id">
                <input type="hidden" id="correctEmployeeId" name="employee_id">
                <input type="hidden" id="correctDateValue" name="date">

                <div class="form-grid">
                    <div class="form-field">
                        <label>AM In</label>
                        <input type="time" id="correctAmIn" name="am_in" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>AM Out</label>
                        <input type="time" id="correctAmOut" name="am_out" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>PM In</label>
                        <input type="time" id="correctPmIn" name="pm_in" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>PM Out</label>
                        <input type="time" id="correctPmOut" name="pm_out" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>OT In</label>
                        <input type="time" id="correctOtIn" name="ot_in" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>OT Out</label>
                        <input type="time" id="correctOtOut" name="ot_out" class="form-input" onchange="calculateTotalHours()">
                    </div>
                </div>

                <div class="modal-net-row" style="margin-top: 16px;">
                    <span>CALCULATED TOTAL HOURS</span>
                    <strong id="calculatedTotalHours" style="color: #0b044d;">0.0 hrs</strong>
                </div>

                <div class="form-field" style="margin-top: 16px;">
                    <label>Reason for Correction <span style="color: #8e1e18;">*</span></label>
                    <textarea id="correctReason" name="reason" class="form-input" rows="3" placeholder="Explain why this correction is needed..." required></textarea>
                </div>

                <div class="form-field" style="margin-top: 16px;">
                    <label>Supporting Documents (PDF, JPG, PNG) <span style="color: #8e1e18;">*</span></label>
                    <input type="file" id="correctAttachments" name="attachments[]" class="form-input" accept=".pdf,.jpg,.jpeg,.png" multiple required style="padding: 8px;">
                    <p style="font-size: 11px; color: #9999bb; margin-top: 4px;">Required: Upload one or more documents (max 5MB each)</p>
                    <div id="filePreview" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn-ghost" onclick="closeCorrectModal()">Cancel</button>
                <button type="submit" class="modal-btn-primary" id="correctSubmitBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Correction
                </button>
            </div>
        </form>
    </div>
</div>
