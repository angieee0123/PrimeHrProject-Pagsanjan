<div id="errorModal" class="modal-overlay" onclick="closeErrorModal(event)" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 500px;">
        <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
            <div style="width: 100%; text-align: center;">
                <div style="width: 80px; height: 80px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
                <h3 class="modal-title" style="color: #8e1e18; margin-bottom: 8px;">Error!</h3>
                <p class="modal-sub" id="errorMessage" style="color: #6b6a8a;">Something went wrong. Please try again.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeErrorModal()" style="position: absolute; top: 20px; right: 20px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-footer" style="justify-content: center; border-top: none; padding-top: 0;">
            <button type="button" class="modal-btn-primary" onclick="closeErrorModal()" style="min-width: 120px; background: #8e1e18;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Close
            </button>
        </div>
    </div>
</div>
