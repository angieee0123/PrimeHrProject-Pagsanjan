<!-- Edit DTR Modal -->
<x-modal id="editModal" close="closeEditModal" eyebrow="EDIT DTR RECORD" title-id="editName" subtitle-id="editEmpId">
    <div class="modal-body">
        <div class="form-grid">
            <div class="form-field">
                <label>Days Present</label>
                <input type="number" min="0" id="editPresent" class="form-input">
            </div>
            <div class="form-field">
                <label>Days Absent</label>
                <input type="number" min="0" id="editAbsent" class="form-input">
            </div>
            <div class="form-field">
                <label>Late Arrivals</label>
                <input type="number" min="0" id="editLate" class="form-input">
            </div>
            <div class="form-field">
                <label>Half Days</label>
                <input type="number" min="0" id="editHalfday" class="form-input">
            </div>
            <div class="form-field">
                <label>Overtime (hrs)</label>
                <input type="number" min="0" step="0.5" id="editOT" class="form-input">
            </div>
            <div class="form-field">
                <label>Status</label>
                <select id="editStatus" class="form-input">
                    <option>Complete</option>
                    <option>Incomplete</option>
                </select>
            </div>
        </div>
        <div class="modal-net-row gp-mt-16">
            <span>ATTENDANCE RATE PREVIEW</span>
            <strong id="editRatePreview">0%</strong>
        </div>

        <div class="edit-passslip-section gp-mt-16">
            <label class="edit-passslip-label">Approved Pass Slips This Period</label>
            <div id="editPassSlipList" class="edit-passslip-list"></div>
        </div>
    </div>
    <div class="modal-footer">
        <x-modal-btn variant="ghost" onclick="closeEditModal()">Cancel</x-modal-btn>
        <x-modal-btn onclick="saveEdit()">Save Changes</x-modal-btn>
    </div>
</x-modal>
