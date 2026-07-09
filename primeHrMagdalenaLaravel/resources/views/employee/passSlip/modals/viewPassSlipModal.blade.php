{{-- View Pass Slip Detail Modal --}}
<div class="modal-overlay" id="viewPassSlipDetailModal" onclick="closePassSlipDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">PASS SLIP · PS-<span id="detailSlipId">-</span></span>
                <h3 class="modal-title" id="detailReason">-</h3>
                <p class="modal-sub" id="detailSlipDate">-</p>
            </div>
            <button class="modal-close" onclick="closePassSlipDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr(auth()->user()->employee->first_name ?? 'E', 0, 1) . substr(auth()->user()->employee->last_name ?? 'E', 0, 1)) }}</div>
                <div>
                    <p class="modal-emp-id">{{ auth()->user()->employee->employee_id ?? 'N/A' }}</p>
                    <span class="badge-status" id="detailPassSlipStatus">-</span>
                </div>
            </div>

            <span class="modal-section-label">PASS SLIP DETAILS</span>
            <div class="modal-row"><span>Reason</span><strong id="detailReasonFull">-</strong></div>
            <div class="modal-row"><span>Destination</span><strong id="detailDestination">-</strong></div>
            <div class="modal-row"><span>Date</span><strong id="detailDate">-</strong></div>
            <div class="modal-row"><span>Time Out</span><strong id="detailTimeOut">-</strong></div>
            <div class="modal-row"><span>Time In</span><strong id="detailTimeIn">-</strong></div>

            <div id="detailApprovalSection" style="display: none;">
                <span class="modal-section-label modal-section-deductions">APPROVAL INFORMATION</span>
                <div class="modal-row"><span>Processed By</span><strong id="detailApprovedBy">-</strong></div>
                <div class="modal-row"><span>Date Processed</span><strong id="detailApprovedAt">-</strong></div>
            </div>

            <div id="detailRemarksSection" style="display: none;">
                <span class="modal-section-label modal-section-deductions">REMARKS</span>
                <div class="modal-row"><span id="detailRemarks" style="color: #991b1b; font-style: italic; line-height: 1.6;">-</span></div>
            </div>

            <div id="detailAttachmentSection" style="display: none; margin-top: 16px;">
                <span class="modal-section-label">SUPPORTING DOCUMENT</span>
                <a id="detailAttachmentLink" href="#" target="_blank" class="document-link" style="margin-top: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                    View Attachment
                </a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closePassSlipDetailModal()">Close</button>
            <button class="modal-btn-primary" id="detailPassSlipCancelBtn" style="display: none; background: #dc2626;" onclick="cancelPassSlip()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                Cancel Pass Slip
            </button>
        </div>
    </div>
</div>

<script>
let currentPassSlipId = null;

function closePassSlipDetailModal() {
    document.getElementById('viewPassSlipDetailModal').style.display = 'none';
    document.body.style.overflow = '';
    currentPassSlipId = null;
}

function viewPassSlip(id) {
    currentPassSlipId = id;

    fetch(`/permanent/passslip/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set header info
            document.getElementById('detailSlipId').textContent = data.id;
            document.getElementById('detailReason').textContent = data.reason;
            document.getElementById('detailSlipDate').textContent =
                new Date(data.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

            // Set status badge
            const statusBadge = document.getElementById('detailPassSlipStatus');
            if (data.status === 'pending') {
                statusBadge.className = 'badge-status pending';
                statusBadge.textContent = 'Pending';
            } else if (data.status === 'approved') {
                statusBadge.className = 'badge-status processed';
                statusBadge.textContent = 'Approved';
            } else if (data.status === 'rejected') {
                statusBadge.className = 'badge-status on-hold';
                statusBadge.textContent = 'Rejected';
            } else {
                statusBadge.className = 'badge-status';
                statusBadge.style.background = '#f3f4f6';
                statusBadge.style.color = '#6b7280';
                statusBadge.textContent = 'Cancelled';
            }

            // Set pass slip details
            document.getElementById('detailReasonFull').textContent = data.reason;
            document.getElementById('detailDestination').textContent = data.destination || 'Not specified';
            document.getElementById('detailDate').textContent = new Date(data.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('detailTimeOut').textContent = data.time_out || 'Not specified';
            document.getElementById('detailTimeIn').textContent = data.time_in || 'Not specified';

            // Show/hide approval section
            const approvalSection = document.getElementById('detailApprovalSection');
            if (data.status !== 'pending' && data.approved_by) {
                approvalSection.style.display = 'block';
                document.getElementById('detailApprovedBy').textContent = data.approver ?
                    data.approver.name :
                    'Admin User';
                document.getElementById('detailApprovedAt').textContent = data.approved_at ?
                    new Date(data.approved_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) :
                    'N/A';
            } else {
                approvalSection.style.display = 'none';
            }

            // Show/hide remarks
            const remarksSection = document.getElementById('detailRemarksSection');
            if (data.status === 'rejected' && data.remarks) {
                remarksSection.style.display = 'block';
                document.getElementById('detailRemarks').textContent = data.remarks;
            } else {
                remarksSection.style.display = 'none';
            }

            // Show/hide attachment
            const attachmentSection = document.getElementById('detailAttachmentSection');
            if (data.attachment) {
                attachmentSection.style.display = 'block';
                document.getElementById('detailAttachmentLink').href = `/storage/${data.attachment}`;
            } else {
                attachmentSection.style.display = 'none';
            }

            // Show/hide cancel button (only for pending pass slips)
            const cancelBtn = document.getElementById('detailPassSlipCancelBtn');
            if (data.status === 'pending') {
                cancelBtn.style.display = 'inline-flex';
            } else {
                cancelBtn.style.display = 'none';
            }

            // Show modal
            document.getElementById('viewPassSlipDetailModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load pass slip details');
        });
}

function cancelPassSlip() {
    if (!currentPassSlipId) return;

    if (confirm('Are you sure you want to cancel this pass slip?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/permanent/passslip/${currentPassSlipId}`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
