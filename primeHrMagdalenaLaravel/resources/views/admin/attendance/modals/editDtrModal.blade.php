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
        <x-modal-btn variant="ghost" onclick="closeEditModal()">Cancel</x-modal-btn>
        <x-modal-btn onclick="saveEdit()">Save Changes</x-modal-btn>
    </div>
</x-modal>

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
.eps-badge.official { background: #e9f9ef; color: #15803d; }
.eps-badge.personal { background: #fbf6e3; color: #c9a227; }
</style>
