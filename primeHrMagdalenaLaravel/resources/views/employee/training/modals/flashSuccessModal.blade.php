{{-- Flash success modal (after redirect) --}}
@if(session('success'))
<div class="modal-overlay training-modal-overlay" id="trainingFlashSuccessModal" onclick="closeModal('trainingFlashSuccessModal')">
    <div class="modal-box training-modal-sm" onclick="event.stopPropagation()">
        <div class="modal-body training-modal-center">
            <div class="training-modal-icon training-modal-icon-success">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="modal-title training-modal-title-gap">Success</h3>
            <p class="training-modal-text">{{ session('success') }}</p>
        </div>
        <div class="modal-footer training-modal-footer-center">
            <button type="button" class="modal-btn-primary training-modal-btn-full" onclick="closeModal('trainingFlashSuccessModal')">Done</button>
        </div>
    </div>
</div>
@endif
