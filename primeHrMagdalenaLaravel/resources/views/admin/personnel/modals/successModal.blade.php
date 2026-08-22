<!-- Success Modal -->
<div id="successModal" class="personnel-modal">
    <div class="personnel-modal-box">
        <div class="personnel-modal-icon success">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3 class="personnel-modal-title">Registration Successful!</h3>
        <p id="successMessage" class="personnel-modal-message"></p>

        {{-- What was emailed, and what the employee has to do with it. Hidden
             unless the flash carries an `email_notice`, so the modal stays as
             it was for the other things that open it (schedule assignment,
             edits) — those send nothing and must not claim to. --}}
        <div id="successEmailNotice" class="personnel-modal-mail" hidden>
            <svg class="personnel-modal-mail-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <div class="personnel-modal-mail-body">
                <p id="successEmailNoticeTitle" class="personnel-modal-mail-title"></p>
                <p id="successEmailNoticeAddress" class="personnel-modal-mail-address"></p>
                <p id="successEmailNoticeText" class="personnel-modal-mail-text"></p>
            </div>
        </div>

        <button onclick="closeSuccessModal()" class="personnel-modal-btn success">Done</button>
    </div>
</div>
