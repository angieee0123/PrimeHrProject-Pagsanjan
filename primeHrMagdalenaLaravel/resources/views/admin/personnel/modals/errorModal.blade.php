<!-- Error Modal -->
<div id="errorModal" class="personnel-modal">
    <div class="personnel-modal-box">
        <div class="personnel-modal-icon error">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" style="stroke: var(--theme-danger-emphasis)" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <h3 class="personnel-modal-title">Registration Failed</h3>
        <p id="errorMessage" class="personnel-modal-message"></p>
        {{-- Populated by adminPersonnel.js from session('error_details'); stays
             empty for failures that have no per-field breakdown. --}}
        <ul id="errorDetails" class="personnel-modal-errors" hidden></ul>
        <button onclick="closeErrorModal()" class="personnel-modal-btn error">Close</button>
    </div>
</div>
