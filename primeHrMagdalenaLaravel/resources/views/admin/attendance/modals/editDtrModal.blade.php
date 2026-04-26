<!-- Edit DTR Modal -->
<div id="editModal" class="modal-overlay" style="display: none;" onclick="closeEditModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">EDIT DTR RECORD</span>
                <h3 class="modal-title" id="editName"></h3>
                <p class="modal-sub" id="editEmpId"></p>
            </div>
            <button class="modal-close" onclick="closeEditModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
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
            <div class="modal-net-row" style="margin-top: 16px;">
                <span>ATTENDANCE RATE PREVIEW</span>
                <strong id="editRatePreview">0%</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeEditModal()">Cancel</button>
            <button class="modal-btn-primary" onclick="saveEdit()">Save Changes</button>
        </div>
    </div>
</div>
