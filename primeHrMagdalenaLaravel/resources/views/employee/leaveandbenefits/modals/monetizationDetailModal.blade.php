{{-- Monetization Detail Modal. The body is filled by openMonetizationDetail():
     an approved request renders the office's Monetization computation sheet
     (the same figures as docs/excels/Monetization-2022 2.docx); any other
     status renders the request details with its status banner, and a
     disapproved one additionally carries the approver's remarks. --}}
<x-modal id="monetizationDetailModal" close="closeMonetizationDetail" max-width="460px" class="permanent-leavebenefits" eyebrow="MONETIZATION REQUEST · MON-2026-0000" title="Monetization" title-id="monetDetailTitle" subtitle="—" subtitle-id="monetDetailSubtitle">
    <div class="modal-body">
        <div class="modal-emp-row">
            <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr(auth()->user()->employee->first_name ?? 'E', 0, 1) . substr(auth()->user()->employee->last_name ?? 'E', 0, 1)) }}</div>
            <div>
                <p class="modal-emp-id">{{ auth()->user()->employee->employee_id ?? 'N/A' }}</p>
                <span class="badge-status pending" id="monetDetailStatus">Pending</span>
            </div>
        </div>
        <div id="monetDetailBody"></div>
    </div>
    <div class="modal-footer">
        <button class="modal-btn-ghost" onclick="closeMonetizationDetail()">Close</button>
        <button class="modal-btn-primary lb-btn-danger lb-hidden" id="monetCancelBtn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            Cancel Request
        </button>
        <button class="modal-btn-primary lb-hidden" id="monetPrintBtn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print Sheet
        </button>
    </div>
</x-modal>
