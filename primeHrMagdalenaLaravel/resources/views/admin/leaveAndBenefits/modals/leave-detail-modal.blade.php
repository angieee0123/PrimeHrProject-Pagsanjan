{{-- Admin Leave Detail Modal — CS Form No. 6 preview.
     Shared: the Leave & Benefits requests tab and the dashboard's "On Leave
     Today" card both open this, so it lives on its own rather than being
     embedded in either page. @once keeps the asset pushes single even if two
     includers land on the same request. --}}
@once
    @push('scripts')
        @vite('resources/js/admin/leaveAndBenefits/leaveDetailModal.js')
    @endpush
@endonce

<div class="modal-overlay" id="adminLeaveDetailModal" onclick="closeAdminLeaveDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 920px; width: 95vw;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="adminLeaveAppNumber">CS FORM NO. 6 · LV-2025-001</span>
                <h3 class="modal-title" id="adminLeaveEmployeeName">Employee Name</h3>
                <p class="modal-sub">
                    <span id="adminLeaveEmployeeId">PGS-0115</span>
                    · <span id="adminLeaveType">Vacation Leave</span>
                    · <span id="adminLeaveStatus" class="badge-status pending" style="font-size: 11px;">Pending</span>
                </p>
            </div>
            <button class="modal-close" onclick="closeAdminLeaveDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 0; background: #e8e8f0;">
            <iframe id="adminLeaveFormFrame"
                title="CS Form No. 6 — Application for Leave"
                style="width: 100%; height: 65vh; border: none; display: block; background: #fff;"
                src="about:blank"></iframe>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeAdminLeaveDetailModal()">Close</button>
            <button class="modal-btn-primary" id="adminDownloadBtn" style="display: none; background: #6b7280;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Attachment
            </button>
            <div style="display: flex; gap: 8px; margin-left: auto;">
                <button class="modal-btn-primary" id="adminPrintFormBtn" onclick="printLeaveForm()" style="background: #6366f1;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Form
                </button>
                <button class="modal-btn-primary" id="adminDownloadFormBtn" onclick="downloadLeaveForm()" style="background: #10b981;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
