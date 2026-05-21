<div id="successModal" class="modal-overlay" onclick="closeSuccessModal(event)" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 500px;">
        <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
            <div style="width: 100%; text-align: center;">
                <div style="width: 80px; height: 80px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <h3 class="modal-title" style="color: #15803d; margin-bottom: 8px;">Success!</h3>
                <p class="modal-sub" id="successMessage" style="color: #6b6a8a;">Leave type registered successfully!</p>
            </div>
            <button type="button" class="modal-close" onclick="closeSuccessModal()" style="position: absolute; top: 20px; right: 20px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-footer" style="justify-content: center; border-top: none; padding-top: 0;">
            <button type="button" class="modal-btn-primary" onclick="closeSuccessModal()" style="min-width: 120px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                OK
            </button>
        </div>
    </div>
</div>
