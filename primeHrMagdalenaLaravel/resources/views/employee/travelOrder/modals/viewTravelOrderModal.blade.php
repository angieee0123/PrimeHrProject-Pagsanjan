{{-- View Travel Order Detail Modal --}}
<x-modal id="viewTravelDetailModal" close="closeTravelDetailModal" title="-" title-id="detailDestination" subtitle="-" subtitle-id="detailTravelDates">
    <x-slot:eyebrow>TRAVEL ORDER · TO-<span id="detailOrderId">-</span></x-slot:eyebrow>
    <div class="modal-body">
        <div class="modal-emp-row">
            <div class="emp-avatar modal-emp-avatar" id="detailFilerAvatar">{{ strtoupper(substr(auth()->user()->employee->first_name ?? 'E', 0, 1) . substr(auth()->user()->employee->last_name ?? 'E', 0, 1)) }}</div>
            <div>
                <p class="modal-emp-id" id="detailFilerInfo">{{ auth()->user()->employee->employee_id ?? 'N/A' }}</p>
                <span class="badge-status" id="detailTravelStatus">-</span>
            </div>
        </div>

        <span class="modal-section-label">TRAVEL DETAILS</span>
        <div class="modal-row"><span>Destination</span><strong id="detailDestinationFull">-</strong></div>
        <div class="modal-row"><span>Departure Date</span><strong id="detailDepartureDate">-</strong></div>
        <div class="modal-row"><span>Return Date</span><strong id="detailReturnDate">-</strong></div>
        <div class="modal-row"><span>Duration</span><strong id="detailDuration">-</strong></div>
        <div class="modal-row"><span>Transportation</span><strong id="detailTransportation">-</strong></div>
        <div class="modal-row"><span>Estimated Budget</span><strong id="detailBudget">-</strong></div>

        <span class="modal-section-label modal-section-deductions">PURPOSE OF TRAVEL</span>
        <div class="modal-row"><span id="detailPurpose" class="to-line-tall">-</span></div>

        <div id="detailCompanionsSection" class="to-hidden">
            <span class="modal-section-label modal-section-deductions">TRAVEL COMPANIONS</span>
            <div id="detailCompanionsList" class="to-companions-list"></div>
        </div>

        <div id="detailHistorySection" class="to-hidden">
            <span class="modal-section-label modal-section-deductions">DOCUMENT HISTORY</span>
            <div id="detailHistoryList" class="to-mt-8"></div>
        </div>

        <div id="detailApprovalSection" class="to-hidden">
            <span class="modal-section-label modal-section-deductions">APPROVAL INFORMATION</span>
            <div class="modal-row"><span>Processed By</span><strong id="detailApprovedBy">-</strong></div>
            <div class="modal-row"><span>Date Processed</span><strong id="detailApprovedAt">-</strong></div>
        </div>

        <div id="detailRemarksSection" class="to-hidden">
            <span class="modal-section-label modal-section-deductions">REMARKS</span>
            <div class="modal-row"><span id="detailRemarks" class="to-remarks-text">-</span></div>
        </div>

        <div id="detailAttachmentSection" class="to-hidden to-mt-16">
            <span class="modal-section-label">SUPPORTING DOCUMENT</span>
            <a id="detailAttachmentLink" href="#" target="_blank" class="document-link to-mt-8">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                </svg>
                View Attachment
            </a>
        </div>
    </div>
    <div class="modal-footer">
        <button class="modal-btn-ghost" onclick="closeTravelDetailModal()">Close</button>
        <button class="modal-btn-primary to-hidden to-btn-danger" id="detailCancelBtn" onclick="cancelTravelOrder()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            Cancel Travel Order
        </button>
    </div>
</x-modal>
