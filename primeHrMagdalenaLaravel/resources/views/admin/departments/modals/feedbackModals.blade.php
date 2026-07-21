<!-- Export Success Modal -->
<x-modal id="export-success-modal" overlay-class="fb-overlay" container-class="fb-box" :style-toggle="false">
    <div class="fb-icon-wrap fb-icon-success">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    </div>
    <span class="fb-eyebrow fb-eyebrow-success">EXPORT COMPLETE</span>
    <h3 class="fb-title">File Downloaded!</h3>
    <p class="fb-desc"><span id="export-success-type">Data</span> has been successfully exported as a CSV file.</p>
    <button class="fb-btn fb-btn-success" onclick="closeExportSuccessModal()">Done</button>
</x-modal>

<!-- Export Error Modal -->
<x-modal id="export-error-modal" overlay-class="fb-overlay" container-class="fb-box" :style-toggle="false">
    <div class="fb-icon-wrap fb-icon-failed">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <span class="fb-eyebrow fb-eyebrow-failed">EXPORT FAILED</span>
    <h3 class="fb-title">Export Error</h3>
    <p class="fb-desc" id="export-error-msg">Something went wrong during the export. Please try again.</p>
    <button class="fb-btn fb-btn-failed" onclick="closeExportErrorModal()">Close</button>
</x-modal>

<!-- Success Modal -->
<x-modal id="success-modal" overlay-class="fb-overlay" container-class="fb-box" :style-toggle="false">
    <div class="fb-icon-wrap fb-icon-success">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <span class="fb-eyebrow fb-eyebrow-success">SUCCESS</span>
    <h3 class="fb-title">Department Registered!</h3>
    <p class="fb-desc">The department has been successfully added to the system.</p>
    <button class="fb-btn fb-btn-success" onclick="closeSuccessModal()">Done</button>
</x-modal>

<!-- Failed Modal -->
<x-modal id="failed-modal" overlay-class="fb-overlay" container-class="fb-box" :style-toggle="false">
    <div class="fb-icon-wrap fb-icon-failed">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <span class="fb-eyebrow fb-eyebrow-failed">FAILED</span>
    <h3 class="fb-title">Registration Failed</h3>
    <p class="fb-desc" id="failed-msg">Something went wrong. Please check the form and try again.</p>
    <button class="fb-btn fb-btn-failed" onclick="closeFailedModal()">Try Again</button>
</x-modal>

<!-- Import Summary Modal -->
<x-modal id="import-summary-modal" overlay-class="fb-overlay" container-class="fb-box" :style-toggle="false" max-width="480px">
    <div class="fb-icon-wrap fb-icon-success">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    </div>
    <span class="fb-eyebrow fb-eyebrow-success">IMPORT COMPLETE</span>
    <h3 class="fb-title" id="import-summary-title">Import Summary</h3>

    <div class="ism-counts">
        <div class="ism-count-box ism-count-imported">
            <p class="ism-count-val ism-count-val-green" id="import-count">0</p>
            <p class="ism-count-caption">Imported</p>
        </div>
        <div class="ism-count-box ism-count-skipped">
            <p class="ism-count-val ism-count-val-red" id="skipped-count">0</p>
            <p class="ism-count-caption">Skipped</p>
        </div>
    </div>

    <div id="skipped-list-wrap" class="ism-skipped-wrap" style="display:none;">
        <p class="ism-skipped-label">SKIPPED RECORDS</p>
        <div id="skipped-list" class="ism-skipped-list"></div>
    </div>

    <button class="fb-btn fb-btn-success ism-btn-mt" onclick="closeImportSummaryModal()">Done</button>
</x-modal>
