{{-- Submit Success Modal --}}
<x-modal id="trainingSubmitModal" class="training-modal-overlay" box-class="training-modal-sm" close="closeTrainingSubmitModal">
    <div class="modal-body training-modal-center">
        <div class="training-modal-icon training-modal-icon-success">
            <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h3 class="modal-title training-modal-title-gap">Training Submitted</h3>
        <p class="training-modal-text">Your training record has been submitted and is <strong>Pending</strong> HR verification. You will be notified once it is approved or if corrections are needed.</p>
        <div class="training-modal-meta">
            <div class="modal-row"><span>Reference</span><strong id="tsRef">—</strong></div>
            <div class="modal-row training-modal-row-last"><span>Status</span><strong class="training-pending-strong">Pending</strong></div>
        </div>
    </div>
    <div class="modal-footer training-modal-footer-center">
        <button type="button" class="modal-btn-primary training-modal-btn-full" onclick="closeTrainingSubmitModal()">Done</button>
    </div>
</x-modal>
