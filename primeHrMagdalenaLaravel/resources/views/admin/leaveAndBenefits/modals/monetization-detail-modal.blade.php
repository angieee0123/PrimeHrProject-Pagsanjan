{{-- Admin Monetization Detail Modal. Same computation sheet the employee sees
     for an approved request; pending requests additionally carry the
     Approve / Disapprove decision in the footer, and a disapproved one
     carries the approver's remarks. Filled by openAdminMonetDetailModal(). --}}
<div class="modal-overlay" id="adminMonetDetailModal" onclick="closeAdminMonetDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 720px; width: 95vw;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="adminMonetRequestNumber">MONETIZATION REQUEST · MON-2026-0000</span>
                <h3 class="modal-title" id="adminMonetEmployeeName">Employee Name</h3>
                <p class="modal-sub">
                    <span id="adminMonetEmployeeId">PGS-0115</span>
                    · <span id="adminMonetStatus" class="badge-status pending" style="font-size: 11px;">Pending</span>
                </p>
            </div>
            <button class="modal-close" onclick="closeAdminMonetDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" id="adminMonetDetailBody" style="max-height: 65vh; overflow-y: auto;">
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeAdminMonetDetailModal()">Close</button>
            <div id="adminMonetDecisionBtns" style="display: none; gap: 8px; margin-left: auto;">
                <button class="modal-btn-primary" id="adminMonetApproveBtn" style="background: #16a34a;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Approve
                </button>
                <button class="modal-btn-primary" id="adminMonetDisapproveBtn" style="background: var(--theme-danger);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Disapprove
                </button>
            </div>
        </div>
    </div>
</div>
