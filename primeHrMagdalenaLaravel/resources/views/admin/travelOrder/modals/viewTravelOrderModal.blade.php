{{-- View Travel Order Modal --}}
<x-modal id="viewTravelOrderModal" close="closeViewTravelOrderModal" title="Travel Order Details" title-id="viewModalTitle" subtitle="-" subtitle-id="viewEmployeeInfo">
    <x-slot:eyebrow>TRAVEL ORDER · #<span id="viewOrderId">-</span></x-slot:eyebrow>
    <div class="modal-body">
        {{-- Status Badge --}}
        <div class="to-status-wrap">
            <span id="viewStatusBadge" class="badge-status">-</span>
        </div>

        <div class="modal-section-label">TRAVEL INFORMATION</div>
        <div class="modal-row"><span>Destination</span><strong id="viewDestination">-</strong></div>
        <div class="modal-row"><span>Departure Date</span><strong id="viewTravelDate">-</strong></div>
        <div class="modal-row"><span>Return Date</span><strong id="viewReturnDate">-</strong></div>
        <div class="modal-row"><span>Duration</span><strong id="viewDuration">-</strong></div>
        <div class="modal-row"><span>Transportation Mode</span><strong id="viewTransportation">-</strong></div>
        <div class="modal-row"><span>Estimated Budget</span><strong id="viewBudget">-</strong></div>

        <div class="modal-section-label to-section-label-mt">PURPOSE OF TRAVEL</div>
        <p id="viewPurpose" class="to-purpose-text">-</p>

        {{-- Travel Companions --}}
        <div id="viewCompanionsSection" style="display: none;">
            <div class="modal-section-label to-section-label-mt">TRAVEL COMPANIONS</div>
            <div id="viewCompanionsList" class="to-companions-list"></div>
        </div>

        {{-- Document History --}}
        <div id="viewHistorySection" style="display: none;">
            <div class="modal-section-label to-section-label-mt">DOCUMENT HISTORY</div>
            <div id="viewHistoryList" class="to-history-list-mt"></div>
        </div>

        {{-- Approval Information --}}
        <div id="viewApprovalSection" style="display: none;">
            <div class="modal-section-label to-section-label-mt">APPROVAL INFORMATION</div>
            <div class="modal-row"><span>Processed By</span><strong id="viewApprovedBy">-</strong></div>
            <div class="modal-row"><span>Date Processed</span><strong id="viewApprovedAt">-</strong></div>

            <div id="viewRemarksSection" class="to-remarks-section-mt" style="display: none;">
                <div class="modal-section-label">REMARKS</div>
                <p id="viewRemarks" class="to-remarks-text">-</p>
            </div>
        </div>

        {{-- Attachment --}}
        <div id="viewAttachmentSection" class="to-attachment-section-mt" style="display: none;">
            <div class="modal-section-label">SUPPORTING DOCUMENT</div>
            <a id="viewAttachmentLink" href="#" target="_blank" class="document-link to-attachment-link-mt">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                </svg>
                View Attachment
            </a>
        </div>
    </div>
    <div class="modal-footer">
        <button class="modal-btn-ghost" onclick="closeViewTravelOrderModal()">Close</button>
    </div>
</x-modal>

@push('scripts')
    @vite('resources/js/travelOrder/viewTravelOrderModal.js')
@endpush
