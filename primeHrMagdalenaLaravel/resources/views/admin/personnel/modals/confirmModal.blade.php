<!-- Confirmation Modal -->
<div id="confirmModal">
    <div class="confirm-modal-box">
        <div class="confirm-modal-icon" id="confirmIconWrap">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke-width="2.5" id="confirmIcon">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3 class="confirm-modal-title" id="confirmTitle">Confirm Action</h3>
        <p class="confirm-modal-message" id="confirmMessage"></p>
        <div class="confirm-input-wrap">
            <label class="confirm-input-label">Type "Yes I confirm" to proceed:</label>
            <input type="text" id="confirmInput" placeholder="Yes I confirm" class="confirm-input" />
            <p class="confirm-error" id="confirmError">Please type "Yes I confirm" (capitalization doesn't matter)</p>
        </div>
        <div class="confirm-modal-footer">
            <button onclick="closeConfirmModal()" class="confirm-btn-cancel">Cancel</button>
            <button onclick="submitConfirmation()" class="confirm-btn-submit" id="confirmSubmitBtn">Confirm</button>
        </div>
    </div>
</div>
