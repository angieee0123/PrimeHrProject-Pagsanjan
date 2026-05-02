<!-- Export Success Modal -->
<div id="export-success-modal" class="fb-overlay">
    <div class="fb-box" onclick="event.stopPropagation()">
        <div class="fb-icon-wrap fb-icon-success">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </div>
        <span class="fb-eyebrow fb-eyebrow-success">EXPORT COMPLETE</span>
        <h3 class="fb-title">File Downloaded!</h3>
        <p class="fb-desc"><span id="export-success-type">Data</span> has been successfully exported as a CSV file.</p>
        <button class="fb-btn fb-btn-success" onclick="closeExportSuccessModal()">Done</button>
    </div>
</div>

<!-- Export Error Modal -->
<div id="export-error-modal" class="fb-overlay">
    <div class="fb-box" onclick="event.stopPropagation()">
        <div class="fb-icon-wrap fb-icon-failed">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <span class="fb-eyebrow fb-eyebrow-failed">EXPORT FAILED</span>
        <h3 class="fb-title">Export Error</h3>
        <p class="fb-desc" id="export-error-msg">Something went wrong during the export. Please try again.</p>
        <button class="fb-btn fb-btn-failed" onclick="closeExportErrorModal()">Close</button>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="fb-overlay">
    <div class="fb-box" onclick="event.stopPropagation()">
        <div class="fb-icon-wrap fb-icon-success">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <span class="fb-eyebrow fb-eyebrow-success">SUCCESS</span>
        <h3 class="fb-title">Department Registered!</h3>
        <p class="fb-desc">The department has been successfully added to the system.</p>
        <button class="fb-btn fb-btn-success" onclick="closeSuccessModal()">Done</button>
    </div>
</div>

<!-- Failed Modal -->
<div id="failed-modal" class="fb-overlay">
    <div class="fb-box" onclick="event.stopPropagation()">
        <div class="fb-icon-wrap fb-icon-failed">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <span class="fb-eyebrow fb-eyebrow-failed">FAILED</span>
        <h3 class="fb-title">Registration Failed</h3>
        <p class="fb-desc" id="failed-msg">Something went wrong. Please check the form and try again.</p>
        <button class="fb-btn fb-btn-failed" onclick="closeFailedModal()">Try Again</button>
    </div>
</div>

<!-- Import Summary Modal -->
<div id="import-summary-modal" class="fb-overlay">
    <div class="fb-box" style="max-width:480px;" onclick="event.stopPropagation()">
        <div class="fb-icon-wrap fb-icon-success">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
        <span class="fb-eyebrow fb-eyebrow-success">IMPORT COMPLETE</span>
        <h3 class="fb-title" id="import-summary-title">Import Summary</h3>

        <div style="display:flex;gap:12px;justify-content:center;margin:12px 0;">
            <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 20px;text-align:center;">
                <p style="font-size:22px;font-weight:800;color:#15803d;margin:0;" id="import-count">0</p>
                <p style="font-size:11px;color:#6b6a8a;margin:2px 0 0;">Imported</p>
            </div>
            <div style="background:#fff5f5;border:1.5px solid #fecaca;border-radius:10px;padding:12px 20px;text-align:center;">
                <p style="font-size:22px;font-weight:800;color:#8e1e18;margin:0;" id="skipped-count">0</p>
                <p style="font-size:11px;color:#6b6a8a;margin:2px 0 0;">Skipped</p>
            </div>
        </div>

        <div id="skipped-list-wrap" style="display:none;width:100%;">
            <p style="font-size:11px;font-weight:700;color:#8e1e18;margin-bottom:6px;letter-spacing:.4px;text-align:left;">SKIPPED RECORDS</p>
            <div id="skipped-list" style="background:#fff5f5;border:1.5px solid #fecaca;border-radius:8px;padding:10px 12px;max-height:160px;overflow-y:auto;text-align:left;"></div>
        </div>

        <button class="fb-btn fb-btn-success" style="margin-top:16px;" onclick="closeImportSummaryModal()">Done</button>
    </div>
</div>
