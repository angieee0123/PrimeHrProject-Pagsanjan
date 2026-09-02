{{-- Mayor Leave Detail Modal — CS Form No. 6 preview, read-only.

     The admin's modal (admin/leaveAndBenefits/modals/leave-detail-modal) frames
     the same form, but every URL in it is under /admin, which EnsureRoleForArea
     closes to the mayor — the iframe would have loaded a 403 page. This is that
     modal pointed at the mayor's own three routes; the form inside is the one
     document, rendered by the same LeaveController methods.

     Print and Download stay: they hand out the filed application as it stands,
     which is reading it, not acting on it. Nothing here approves anything. --}}
@once
    @push('scripts')
        @vite('resources/js/mayor/leaveCalendar/mayorLeaveDetailModal.js')
    @endpush
@endonce

<div class="modal-overlay" id="mayorLeaveDetailModal" onclick="closeMayorLeaveDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 920px; width: 95vw;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="mayorLeaveAppNumber">CS FORM NO. 6 · LV-2025-001</span>
                <h3 class="modal-title" id="mayorLeaveEmployeeName">Employee Name</h3>
                <p class="modal-sub">
                    <span id="mayorLeaveEmployeeId">PGS-0115</span>
                    · <span id="mayorLeaveType">Vacation Leave</span>
                    · <span id="mayorLeaveStatus" class="badge-status pending" style="font-size: 11px;">Pending</span>
                </p>
            </div>
            <button class="modal-close" onclick="closeMayorLeaveDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 0; background: #e8e8f0;">
            <iframe id="mayorLeaveFormFrame"
                title="CS Form No. 6 — Application for Leave"
                style="width: 100%; height: 65vh; border: none; display: block; background: #fff;"
                src="about:blank"></iframe>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeMayorLeaveDetailModal()">Close</button>
            <button class="modal-btn-primary" id="mayorLeaveAttachmentBtn" style="display: none; background: var(--theme-neutral-700);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Attachment
            </button>
            <div style="display: flex; gap: 8px; margin-left: auto;">
                <button class="modal-btn-primary" onclick="printMayorLeaveForm()" style="background: #6366f1;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Form
                </button>
                <button class="modal-btn-primary" onclick="downloadMayorLeaveForm()" style="background: #10b981;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
