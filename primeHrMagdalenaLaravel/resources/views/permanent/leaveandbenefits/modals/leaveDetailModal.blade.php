{{-- Leave Detail Modal --}}
<div class="modal-overlay" id="detailModal" onclick="closeModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">LEAVE REQUEST · LV-2025-002</span>
                <h3 class="modal-title" id="detailType">Sick Leave</h3>
                <p class="modal-sub" id="detailDates">Jun 15, 2025 — Jun 16, 2025</p>
            </div>
            <button class="modal-close" onclick="closeModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr(auth()->user()->employee->first_name ?? 'E', 0, 1) . substr(auth()->user()->employee->last_name ?? 'E', 0, 1)) }}</div>
                <div>
                    <p class="modal-emp-id">{{ auth()->user()->employee->employee_id ?? 'N/A' }}</p>
                    <span class="badge-status processed" id="detailStatus">Approved</span>
                </div>
            </div>
            <span class="modal-section-label">LEAVE DETAILS</span>
            <div class="modal-row"><span>Leave Type</span><strong id="detailType2">Sick Leave</strong></div>
            <div class="modal-row"><span>Date From</span><strong id="detailFrom">Jun 15, 2025</strong></div>
            <div class="modal-row"><span>Date To</span><strong id="detailTo">Jun 16, 2025</strong></div>
            <div class="modal-row"><span>No. of Days</span><strong id="detailDays">2 days</strong></div>
            <span class="modal-section-label modal-section-deductions">REASON</span>
            <div class="modal-row"><span id="detailReason">Medical consultation</span></div>
            <div id="remarksSection" style="display: none;">
                <span class="modal-section-label modal-section-deductions">APPROVER REMARKS</span>
                <div class="modal-row"><span id="remarksText" style="color: #6b7280; font-style: italic;"></span></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal()">Close</button>
            <button class="modal-btn-primary" id="cancelBtn" style="display: none; background: #dc2626;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                Cancel Request
            </button>
            <button class="modal-btn-primary" id="downloadBtn" style="display: none;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Attachment
            </button>
        </div>
    </div>
</div>
