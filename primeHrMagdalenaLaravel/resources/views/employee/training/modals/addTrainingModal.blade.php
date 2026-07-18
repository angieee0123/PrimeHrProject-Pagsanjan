{{-- Add New Training Modal --}}
<div class="modal-overlay training-modal-overlay" id="addTrainingModal" onclick="closeModal('addTrainingModal')">
    <div class="modal-box training-add-modal" onclick="event.stopPropagation()">
        <form id="addTrainingForm" class="training-modal-form" method="POST" action="{{ route('employee.training.store') }}" enctype="multipart/form-data" onsubmit="submitTraining(event)">
        @csrf
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon training-hero-icon">
                    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                            <div>
                    <span class="modal-eyebrow">LEARNING &amp; DEVELOPMENT</span>
                    <h3 class="modal-title">Add New Training</h3>
                    <p class="modal-sub" id="modalStepSub">Step 1 of 2 — Upload your certificate to auto-fill details.</p>
                            </div>
                        </div>
            <button type="button" class="modal-close" onclick="closeModal('addTrainingModal')" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
                            </div>

            {{-- Step indicator --}}
            <div class="cert-steps" id="certSteps">
                <div class="cert-step active" id="step1Indicator">
                    <span class="cert-step-num">1</span>
                    <span class="cert-step-label">Upload Certificate</span>
                            </div>
                <div class="cert-step-line"></div>
                <div class="cert-step" id="step2Indicator">
                    <span class="cert-step-num">2</span>
                    <span class="cert-step-label">Review &amp; Submit</span>
                        </div>
                            </div>

            <div class="modal-body training-modal-body-scroll">

                {{-- STEP 1: Upload --}}
                <div id="certStep1">
                    <div class="training-form-grid">
                        <div class="training-form-field training-form-full">
                            <div class="training-dropzone" id="trainingDropZone">
                                <input type="file" id="trainingCertificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png" hidden onchange="handleTrainingFile(this)">
                                <button type="button" class="training-dropzone-label" id="dropzoneBtn" onclick="document.getElementById('trainingCertificate').click()">
                                    <svg width="40" height="40" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <p class="training-dropzone-title">Click to upload or drag and drop</p>
                                    <p class="training-dropzone-sub">PDF, JPEG, or PNG · Max 5 MB</p>
                                    <p class="training-dropzone-sub" style="margin-top:6px; color:#0b044d; font-weight:600;">Certificate details will be auto-extracted</p>
                                </button>
                                <p class="training-file-name" id="trainingFileName" hidden></p>
                            </div>
                        </div>
                    </div>

                    {{-- OCR scanning state --}}
                    <div id="certScanState" style="display:none; text-align:center; padding:24px 0;">
                        <div class="cert-scan-spinner"></div>
                        <p class="cert-scan-label" id="certScanLabel">Reading certificate...</p>
                        <p style="font-size:11px; color:#9999bb; margin-top:4px;">This may take a few seconds</p>
                            </div>

                    <div class="training-enroll-note" style="margin-top:16px;">
                        <svg width="16" height="16" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p class="training-enroll-note-text">Upload any Certificate of Completion (PDF or image). The system reads different certificate layouts—government, private, or agency-issued—and auto-fills the form. Review and correct any field before submitting.</p>
                        </div>
                            </div>

                {{-- STEP 2: Review auto-filled form --}}
                <div id="certStep2" style="display:none;">

                    <div class="cert-autofill-banner" id="certAutofillBanner">
                        <svg width="15" height="15" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span id="certAutofillMsg">Details extracted from your certificate. Please review and correct if needed.</span>
                            </div>

                    <p class="modal-section-label">PROGRAM DETAILS</p>
                    <div class="training-form-grid">
                        <div class="training-form-field training-form-full">
                            <label for="trainingTitle">Title of Seminar / Conference / Training Program <span class="req">*</span></label>
                            <input type="text" id="trainingTitle" name="title" required placeholder="e.g. Leadership and Governance Seminar">
                        </div>
                        <div class="training-form-field">
                            <label for="trainingPositionType">Type of Position <span class="req">*</span></label>
                            <select id="trainingPositionType" name="position_type" required>
                                <option value="">Select type</option>
                                <option value="Managerial">Managerial</option>
                                <option value="Supervisory">Supervisory</option>
                                <option value="Technical">Technical</option>
                                <option value="Clerical">Clerical</option>
                            </select>
                            </div>
                        <div class="training-form-field training-form-full">
                            <label for="trainingConductedBy">Conducted / Sponsored By <span class="req">*</span></label>
                            <input type="text" id="trainingConductedBy" name="conducted_by" required placeholder="Training agency or institution">
                            </div>
                        <div class="training-form-field training-form-full">
                            <label for="trainingVenue">Venue / Location</label>
                            <input type="text" id="trainingVenue" name="venue" placeholder="e.g. Laguna, Philippines">
                        </div>
                    </div>

                    <p class="modal-section-label modal-section-deductions">DATES &amp; HOURS</p>
                    <div class="training-form-grid">
                        <div class="training-form-field">
                            <label for="trainingDateFrom">Inclusive Date (From) <span class="req">*</span></label>
                            <input type="date" id="trainingDateFrom" name="date_from" required>
                        </div>
                        <div class="training-form-field">
                            <label for="trainingDateTo">Inclusive Date (To) <span class="req">*</span></label>
                            <input type="date" id="trainingDateTo" name="date_to" required>
                        </div>
                        <div class="training-form-field">
                            <label for="trainingHours">Number of Hours <span class="req">*</span></label>
                            <input type="number" id="trainingHours" name="hours" min="1" max="999" required placeholder="e.g. 8">
            </div>
        </div>

                    <p class="modal-section-label modal-section-deductions">DOCUMENTATION</p>
                    <div class="training-form-grid">
                        <div class="training-form-field">
                            <label for="trainingRefDoc">Reference Document Number <span class="req">*</span></label>
                            <input type="text" id="trainingRefDoc" name="ref_doc_no" required placeholder="Office Order No. or Travel Order No.">
                            <p class="training-field-hint">Enter the Office Order or Travel Order that authorized your attendance.</p>
                        </div>
                        <div class="training-form-field">
                            <label for="trainingCertNo">Certificate Number</label>
                            <input type="text" id="trainingCertNo" name="cert_no" placeholder="e.g. CERT-2025-001">
                        </div>
                        <div class="training-form-field training-form-full">
                            <label>Uploaded Certificate</label>
                            <div class="cert-file-preview" id="certFilePreview">
                                <div id="certFileIcon2"></div>
                                <div>
                                    <p id="certFileName2" style="font-size:13px; font-weight:600; color:#0b044d; margin:0;"></p>
                                    <p style="font-size:11px; color:#9999bb; margin:0;">Ready for submission</p>
                                </div>
                                <button type="button" onclick="resetToStep1()" style="margin-left:auto; background:none; border:none; color:#8e1e18; cursor:pointer; font-size:12px; font-weight:600;">Change</button>
                            </div>
                        </div>
</div>

                    <div class="training-enroll-note">
                        <svg width="16" height="16" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" class="training-enroll-note-icon"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p class="training-enroll-note-text">Submitted entries are marked <strong>Pending</strong> until HR verifies your certificate and reference document. Only verified hours count toward your annual L&amp;D total.</p>
            </div>
            </div>
        </div>

            <div class="modal-footer training-modal-footer-sticky">
                <button type="button" class="modal-btn-ghost" onclick="closeModal('addTrainingModal')">Cancel</button>
                <button type="submit" class="modal-btn-primary" id="certSubmitBtn" style="display:none;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Submit for Verification
                </button>
        </div>
        </form>
    </div>
</div>
