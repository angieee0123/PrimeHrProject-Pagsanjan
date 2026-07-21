{{-- View Pass Slip Detail Modal --}}
<x-modal id="viewPassSlipDetailModal" close="closePassSlipDetailModal" title="-" title-id="detailReason" subtitle="-" subtitle-id="detailSlipDate">
    <x-slot:eyebrow>PASS SLIP · PS-<span id="detailSlipId">-</span></x-slot:eyebrow>
    <div class="modal-body">
        <div class="modal-emp-row">
            <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr(auth()->user()->employee->first_name ?? 'E', 0, 1) . substr(auth()->user()->employee->last_name ?? 'E', 0, 1)) }}</div>
            <div>
                <p class="modal-emp-id">{{ auth()->user()->employee->employee_id ?? 'N/A' }}</p>
                <span class="badge-status" id="detailPassSlipStatus">-</span>
            </div>
        </div>

        <span class="modal-section-label">PASS SLIP DETAILS</span>
        <div class="modal-row"><span>Reason</span><strong id="detailReasonFull">-</strong></div>
        <div class="modal-row"><span>Destination</span><strong id="detailDestination">-</strong></div>
        <div class="modal-row"><span>Date</span><strong id="detailDate">-</strong></div>
        <div class="modal-row"><span>Time Out</span><strong id="detailTimeOut">-</strong></div>
        <div class="modal-row"><span>Time In</span><strong id="detailTimeIn">-</strong></div>

        <div id="detailApprovalSection" class="ps-hidden">
            <span class="modal-section-label modal-section-deductions">APPROVAL INFORMATION</span>
            <div class="modal-row"><span>Processed By</span><strong id="detailApprovedBy">-</strong></div>
            <div class="modal-row"><span>Date Processed</span><strong id="detailApprovedAt">-</strong></div>
        </div>

        <div id="detailRemarksSection" class="ps-hidden">
            <span class="modal-section-label modal-section-deductions">REMARKS</span>
            <div class="modal-row"><span id="detailRemarks" class="ps-remarks-text">-</span></div>
        </div>

        <div id="detailAttachmentSection" class="ps-hidden ps-mt-16">
            <span class="modal-section-label">SUPPORTING DOCUMENT</span>
            <a id="detailAttachmentLink" href="#" target="_blank" class="document-link ps-mt-8">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                </svg>
                View Attachment
            </a>
        </div>
    </div>
    <div class="modal-footer">
        <button class="modal-btn-ghost" onclick="closePassSlipDetailModal()">Close</button>
        <button class="modal-btn-primary ps-hidden ps-btn-danger" id="detailPassSlipCancelBtn" onclick="cancelPassSlip()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            Cancel Pass Slip
        </button>
    </div>
</x-modal>
