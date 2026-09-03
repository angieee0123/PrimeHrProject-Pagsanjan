{{-- Bulk Import validation notice.

     This was a native alert('Please select a CSV file to upload.'). The browser
     dialog was the one thing on this page that did not wear the system's own
     design — it printed the page's URL above the sentence and dropped the admin
     into an OS chrome that looks nothing like the modal it was raised from.

     It carries no message of its own: the sentence is set by the caller in
     adminPersonnel.js, where the alert() used to be, so the wording stays with
     the check that raises it. Same split as successModal / errorModal.

     `.personnel-modal` puts it on --personnel-z-alert, one layer above the Bulk
     Import modal it is raised over, so it reads and clicks in front of it. --}}
<div id="bulkImportNoticeModal" class="personnel-modal" role="alertdialog"
     aria-labelledby="bulkImportNoticeTitle" aria-describedby="bulkImportNoticeMessage">
    <div class="personnel-modal-box">
        <div class="personnel-modal-icon warning">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" style="stroke: var(--theme-warning-emphasis)"
                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3 id="bulkImportNoticeTitle" class="personnel-modal-title">Check Your Upload</h3>
        <p id="bulkImportNoticeMessage" class="personnel-modal-message"></p>
        <button type="button" onclick="closeBulkImportNotice()" class="personnel-modal-btn warning">Got It</button>
    </div>
</div>
