{{-- Printable Travel Order preview.

     The sheet itself is rendered by the server and shown in an iframe, exactly
     as the Pass Slip's form modal does it, so what the admin previews is byte
     for byte what Print and Download PDF produce. --}}
<div class="modal-overlay" id="travelOrderFormModal" onclick="closeTravelOrderFormModal()" style="display: none;">
    <div class="modal-box to-modal-box-wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="toFormOrderNumber">TRAVEL ORDER</span>
                <h3 class="modal-title" id="toFormEmployeeName">Employee Name</h3>
                <p class="modal-sub">
                    <span id="toFormEmployeeId">-</span>
                    · <span id="toFormDestination">-</span>
                </p>
            </div>
            <button class="modal-close" onclick="closeTravelOrderFormModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body to-modal-body-flush">
            <iframe id="toFormFrame"
                title="Travel Order"
                class="to-modal-iframe"
                src="about:blank"></iframe>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeTravelOrderFormModal()">Close</button>
            <div class="to-modal-footer-actions">
                <button class="modal-btn-primary to-btn-print" onclick="printTravelOrderForm()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Form
                </button>
                <button class="modal-btn-primary to-btn-download" onclick="downloadTravelOrderForm()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/travelOrder/travelOrderFormModal.js')
@endpush
