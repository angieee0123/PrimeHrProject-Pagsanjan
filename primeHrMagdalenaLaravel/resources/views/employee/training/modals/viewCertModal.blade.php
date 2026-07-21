{{-- View Certificate Modal --}}
<x-modal id="viewCertModal" class="training-modal-overlay" box-class="training-view-cert-modal" close="closeViewCertModal">
    <div class="modal-header">
        <div class="pmodal-hero">
            <div class="pmodal-hero-icon training-hero-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
                <span class="modal-eyebrow">VERIFIED TRAINING RECORD</span>
                <h3 class="modal-title" id="vcTitle">—</h3>
                <p class="modal-sub" id="vcSubtitle">—</p>
                <div class="pmodal-badges" id="vcBadges"></div>
            </div>
        </div>
        <button type="button" class="modal-close" onclick="closeViewCertModal()" aria-label="Close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="modal-body">
        <p class="modal-section-label">TRAINING DETAILS</p>
        <div class="training-modal-meta" id="vcDetails"></div>
        <p class="modal-section-label modal-section-deductions">CERTIFICATE FILE</p>
        <div class="training-cert-preview" id="vcPreview">
            <div class="training-cert-preview-icon" id="vcFileIcon"></div>
            <p class="training-cert-preview-name" id="vcFile">—</p>
            <p class="training-cert-preview-note">Certificate on file — verified by HRMO</p>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="modal-btn-ghost" onclick="closeViewCertModal()">Close</button>
        <button type="button" class="modal-btn-primary" id="vcDownloadBtn">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Certificate
        </button>
    </div>
</x-modal>
