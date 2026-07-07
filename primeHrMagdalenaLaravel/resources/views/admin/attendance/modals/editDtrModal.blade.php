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

            <div class="edit-passslip-section" style="margin-top: 16px;">
                <label class="edit-passslip-label">Approved Pass Slips This Period</label>
                <div id="editPassSlipList" class="edit-passslip-list"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeEditModal()">Cancel</button>
            <button class="modal-btn-primary" onclick="saveEdit()">Save Changes</button>
        </div>
    </div>
</div>

<style>
/* This page's `.modal-overlay` z-index is decided by whichever admin
   CSS file loads last (currently adminPayroll.css, z-index: 1000) —
   see detailedDtrModal.blade.php for the full explanation. Detailed DTR
   modal forces itself to z-index: 3000 via #detailedDTRModal.ddtr-overlay,
   so this modal needs an equally-specific override higher than that to
   render in front of it instead of behind it. */
#editModal.modal-overlay {
    z-index: 4000 !important;
    position: fixed !important;
}

/* ── Approved Pass Slip note ── */
.edit-passslip-label {
    display: block; font-size: 11px; font-weight: 600; color: #7C839D;
    text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px;
}
.edit-passslip-list { display: flex; flex-direction: column; gap: 8px; }
.edit-passslip-empty {
    font-size: 12.5px; color: #9aa1b5; font-style: italic;
    padding: 10px 12px; background: #f7f8fc; border-radius: 10px;
}
.edit-passslip-item {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 10px 12px; border-radius: 10px; border: 1px solid #eceaf8;
    background: #fafbff; font-size: 12.5px; color: #1E2247;
}
.edit-passslip-item .eps-date { font-weight: 600; white-space: nowrap; }
.edit-passslip-item .eps-meta { color: #7C839D; font-size: 11.5px; margin-top: 2px; }
.eps-slip-num { font-size: 10px; font-weight: 500; color: #9aa1b5; margin-left: 4px; }
.eps-badge {
    font-size: 9.5px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase;
    padding: 3px 9px; border-radius: 999px; white-space: nowrap; flex-shrink: 0;
}
.eps-badge.official { background: #e9f9ef; color: #23875a; }
.eps-badge.personal { background: #fdf3e3; color: #a6720c; }
</style>
