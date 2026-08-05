<x-modal-container id="viewAccrualRateModal" close="closeViewAccrualRateModal" overlay-class="modal-overlay" container-class="modal-container" max-width="620px"
                     title="Accrual Rate Details" subtitle="Read-only view of the accrual rate configuration">
    <x-slot:closeIcon>&times;</x-slot:closeIcon>

    <div class="form-grid">
        <div class="form-group" style="grid-column: span 2;">
            <label class="form-label">Leave Type</label>
            <div class="form-input" style="background:#f8f8fc; cursor:default; display:flex; align-items:center; gap:10px;">
                <span id="viewAccrualLeaveCode" class="badge-status processed" style="font-size:12px;"></span>
                <span id="viewAccrualLeaveName" style="font-weight:600; color:var(--gp-pri);"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Accrual Frequency</label>
            <div class="form-input" style="background:#f8f8fc; cursor:default;">
                <span id="viewAccrualFrequency" class="badge-status"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Status</label>
            <div class="form-input" style="background:#f8f8fc; cursor:default;">
                <span id="viewAccrualStatus" class="badge-status"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Days of Service Required</label>
            <div class="form-input" style="background:#f8f8fc; cursor:default; font-weight:600; color:var(--gp-pri);">
                <span id="viewAccrualDaysService"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Credits Earned Per Period</label>
            <div class="form-input" style="background:#f8f8fc; cursor:default; font-weight:600; color:#15803d;">
                <span id="viewAccrualCredits"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Effective Date</label>
            <div class="form-input" style="background:#f8f8fc; cursor:default;">
                <span id="viewAccrualEffectiveDate"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">End Date</label>
            <div class="form-input" style="background:#f8f8fc; cursor:default;">
                <span id="viewAccrualEndDate"></span>
            </div>
        </div>

        <div class="form-group" style="grid-column: span 2;" id="viewAccrualNotesGroup">
            <label class="form-label">Notes / CSC Reference</label>
            <div class="form-input" style="background:#f8f8fc; cursor:default; min-height:60px; white-space:pre-wrap;">
                <span id="viewAccrualNotes"></span>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeViewAccrualRateModal()"
                style="flex:none; padding:9px 22px;">Close</button>
        <button type="button" class="btn-submit" onclick="switchToEditFromView()"
                style="flex:none; padding:9px 22px; display:inline-flex; align-items:center; gap:7px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Edit
        </button>
    </div>
</x-modal-container>
