<!-- Correct Attendance Time Modal -->
<x-modal id="correctModal" close="closeCorrectModal" max-width="600px"
          eyebrow="CORRECT ATTENDANCE TIME" title-id="correctEmployeeName" subtitle-id="correctDate">
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

            <div class="modal-net-row gp-mt-16">
                <span>CALCULATED TOTAL HOURS</span>
                <strong id="calculatedTotalHours" class="text-primary-ink">0.0 hrs</strong>
            </div>

            <div id="correctPassSlipBanner"></div>

            <div class="form-field gp-mt-16">
                <label>Reason for Correction <span class="text-danger">*</span></label>
                <textarea id="correctReason" name="reason" class="form-input" rows="3" placeholder="Explain why this correction is needed..." required></textarea>
            </div>

            <div class="form-field gp-mt-16">
                <label>Supporting Documents (PDF, JPG, PNG) <span class="text-danger">*</span></label>
                <input type="file" id="correctAttachments" name="attachments[]" class="form-input" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                <p class="field-hint-sm">Required: Upload one or more documents (max 5MB each)</p>
                <div id="filePreview"></div>
            </div>
        </div>
        <div class="modal-footer">
            <x-modal-btn variant="ghost" onclick="closeCorrectModal()">Cancel</x-modal-btn>
            <x-modal-btn type="submit" id="correctSubmitBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Correction
            </x-modal-btn>
        </div>
    </form>
</x-modal>
