<div id="viewLeaveTypeModal" class="modal-overlay" onclick="closeViewLeaveTypeModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">LEAVE TYPE · <span id="viewLeaveCode">-</span></span>
                <h3 class="modal-title" id="viewLeaveName">-</h3>
                <p class="modal-sub">Leave Type Configuration</p>
            </div>
            <button class="modal-close" onclick="closeViewLeaveTypeModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-section-label">BASIC INFORMATION</div>
            <div class="modal-row"><span>Annual Limit</span><strong id="viewAnnualLimit">-</strong></div>
            <div class="modal-row"><span>Type</span><strong id="viewLeaveTypeAccrual">-</strong></div>
            <div class="modal-row"><span>Status</span><span id="viewLeaveStatus" class="badge-status">-</span></div>

            <div class="modal-section-label" style="margin-top: 16px;">CONFIGURATION</div>
            <div id="viewLeaveConfig" style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                <!-- Configuration badges will be inserted here -->
            </div>

            <div id="viewAttachmentInfoGroup" style="display: none; margin-top: 16px;">
                <div class="modal-section-label">ATTACHMENT INSTRUCTIONS</div>
                <p id="viewAttachmentInfo" style="font-size: 13px; color: #6b6a8a; line-height: 1.6; margin: 8px 0;">-</p>
            </div>

            <div id="viewDocumentGroup" style="display: none; margin-top: 16px;">
                <div class="modal-section-label">SUPPORTING DOCUMENT</div>
                <a href="#" id="viewDocumentLink" class="document-link" target="_blank" style="margin-top: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    View Document
                </a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeViewLeaveTypeModal()">Close</button>
            <button class="modal-btn-primary" onclick="editLeaveTypeFromView()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Leave Type
            </button>
        </div>
    </div>
</div>
