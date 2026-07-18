{{-- Export PDS Success Modal --}}
<x-modal id="pdsExportModal" class="training-modal-overlay" box-class="training-modal-sm" close="closePdsExportModal">
    <div class="modal-body training-modal-center">
        <div class="training-modal-icon training-modal-icon-success">
            <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </div>
        <h3 class="modal-title training-modal-title-gap">PDS Export Ready</h3>
        <p class="training-modal-text">Your verified training history has been prepared in the official CSC PDS Excel format (Section IV).</p>
        <div class="training-modal-meta">
            <div class="modal-row"><span>Format</span><strong>CSC PDS Excel</strong></div>
            <div class="modal-row training-modal-row-last"><span>Records</span><strong id="pdsRecordCount">—</strong></div>
        </div>
    </div>
    <div class="modal-footer training-modal-footer-center">
        <button type="button" class="modal-btn-ghost training-modal-btn-half" onclick="closePdsExportModal()">Close</button>
        <button type="button" class="modal-btn-primary training-modal-btn-half" onclick="closePdsExportModal()">Download File</button>
    </div>
</x-modal>
