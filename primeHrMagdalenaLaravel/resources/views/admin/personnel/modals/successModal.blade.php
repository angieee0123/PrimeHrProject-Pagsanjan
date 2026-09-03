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

        {{-- Rows the import refused because the employee already exists.

             This used to be one sentence per duplicate appended to
             #successMessage ("Row 2: Employee ID 00123 already exists Row 3: …"),
             which on a real CSV ran to a paragraph that repeated the same six
             words a dozen times and pushed the Done button past the bottom of
             the screen. The wording that every line shared is said once, in the
             heading; the list carries only what differs — who, and which ID.

             Hidden unless the response carries duplicates, so the modal stays
             exactly as it was for everything else that opens it (the wizard,
             schedule assignment, edits). --}}
        <div id="successDuplicateNotice" class="personnel-modal-dupes" hidden>
            <p class="personnel-modal-dupes-title">Duplicate Records Found</p>
            <p class="personnel-modal-dupes-lede">The following records already exist and were skipped:</p>
            <ul id="successDuplicateList" class="personnel-modal-dupes-list"></ul>
            <p id="successDuplicateCount" class="personnel-modal-dupes-count"></p>
        </div>

        <button onclick="closeSuccessModal()" class="personnel-modal-btn success">Done</button>
    </div>
</div>
