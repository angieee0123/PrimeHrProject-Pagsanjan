{{-- Admin Monetization Detail Modal.

     One layout for every status — a status banner over Employee /
     Monetization / Request panels. It used to render two different bodies,
     the office's computation sheet for an approved request and a flat list
     of label-value rows for every other one, so a request changed shape the
     moment it was decided and a *pending* one never showed the admin the
     arithmetic they were being asked to approve.

     Pending requests additionally carry the Approve / Disapprove decision in
     the footer; an approved one carries Print Sheet / Download PDF, which are
     the office's actual form. Filled by openAdminMonetDetailModal(). --}}
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
        {{-- Filled by openAdminMonetDetailModal(): a status banner, then
             Employee / Monetization / Request panels around the office's own
             TLB = S x D x CF working. Grouped rather than one long list of
             label-value rows, because an admin reading this is deciding
             whether to release money and needs the person, the arithmetic
             and the paperwork separable at a glance. --}}
        <div class="modal-body" id="adminMonetDetailBody" style="max-height: 66vh; overflow-y: auto; background: var(--theme-neutral-50);">
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeAdminMonetDetailModal()">Close</button>
            {{-- The office's Monetization form, same document the row menu and
                 the employee's own modal produce. Shown only for an approved
                 request, which is the only status that sheet can honestly
                 carry. --}}
            <div id="adminMonetPrintBtns" style="display: none; gap: 8px; margin-left: auto;">
                <button class="modal-btn-ghost" id="adminMonetDownloadBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
                <button class="modal-btn-primary" id="adminMonetPrintBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Sheet
                </button>
            </div>
            <div id="adminMonetDecisionBtns" style="display: none; gap: 8px; margin-left: auto;">
                {{-- The canonical success variant rather than a hex fill:
                     approve and disapprove have to stay one glance apart
                     under every palette, and the semantic ramp is what
                     guarantees that. --}}
                <button class="modal-btn-success" id="adminMonetApproveBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Approve
                </button>
                <button class="modal-btn-danger" id="adminMonetDisapproveBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Disapprove
                </button>
            </div>
        </div>
    </div>
</div>
