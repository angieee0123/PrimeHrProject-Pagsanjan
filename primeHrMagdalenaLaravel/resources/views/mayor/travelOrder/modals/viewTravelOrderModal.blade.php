{{-- View Travel Order Modal (read-only).
     Shared: the mayor's Travel Orders table and the Leave & Travel Calendar
     both open this, so it ships its own script rather than relying on either
     page to load one. @once keeps the push single if both ever land on the
     same request. --}}
@once
    @push('scripts')
        @vite('resources/js/mayor/travelOrder/mayorTravelOrderDetail.js')
    @endpush
@endonce

<x-modal id="mayorViewTravelOrderModal" close="closeMayorViewTravelOrderModal" title="Travel Order Details" title-id="mtoModalTitle" subtitle="-" subtitle-id="mtoEmployeeInfo">
    <x-slot:eyebrow>TRAVEL ORDER · #<span id="mtoOrderId">-</span></x-slot:eyebrow>
    <div class="modal-body">
        {{-- Status Badge --}}
        <div class="to-status-wrap">
            <span id="mtoStatusBadge" class="badge-status">-</span>
        </div>

        <div class="modal-section-label">TRAVEL INFORMATION</div>
        <div class="modal-row"><span>Destination</span><strong id="mtoDestination">-</strong></div>
        <div class="modal-row"><span>Departure Date</span><strong id="mtoTravelDate">-</strong></div>
        <div class="modal-row"><span>Return Date</span><strong id="mtoReturnDate">-</strong></div>
        <div class="modal-row"><span>Duration</span><strong id="mtoDuration">-</strong></div>
        <div class="modal-row"><span>Transportation Mode</span><strong id="mtoTransportation">-</strong></div>
        <div class="modal-row"><span>Estimated Budget</span><strong id="mtoBudget">-</strong></div>

        <div class="modal-section-label to-section-label-mt">PURPOSE OF TRAVEL</div>
        <p id="mtoPurpose" class="to-purpose-text">-</p>

        {{-- Travel Companions --}}
        <div id="mtoCompanionsSection" style="display: none;">
            <div class="modal-section-label to-section-label-mt">TRAVEL COMPANIONS</div>
            <div id="mtoCompanionsList" class="to-companions-list"></div>
        </div>

        {{-- Document History --}}
        <div id="mtoHistorySection" style="display: none;">
            <div class="modal-section-label to-section-label-mt">DOCUMENT HISTORY</div>
            <div id="mtoHistoryList" class="to-history-list-mt"></div>
        </div>

        {{-- Approval Information --}}
        <div id="mtoApprovalSection" style="display: none;">
            <div class="modal-section-label to-section-label-mt">APPROVAL INFORMATION</div>
            <div class="modal-row"><span>Processed By</span><strong id="mtoApprovedBy">-</strong></div>
            <div class="modal-row"><span>Date Processed</span><strong id="mtoApprovedAt">-</strong></div>

            <div id="mtoRemarksSection" class="to-remarks-section-mt" style="display: none;">
                <div class="modal-section-label">REMARKS</div>
                <p id="mtoRemarks" class="to-remarks-text">-</p>
            </div>
        </div>

        {{-- Attachment --}}
        <div id="mtoAttachmentSection" class="to-attachment-section-mt" style="display: none;">
            <div class="modal-section-label">SUPPORTING DOCUMENT</div>
            <a id="mtoAttachmentLink" href="#" target="_blank" class="document-link to-attachment-link-mt">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                </svg>
                View Attachment
            </a>
        </div>
    </div>
    <div class="modal-footer">
        <button class="modal-btn-ghost" onclick="closeMayorViewTravelOrderModal()">Close</button>
    </div>
</x-modal>
