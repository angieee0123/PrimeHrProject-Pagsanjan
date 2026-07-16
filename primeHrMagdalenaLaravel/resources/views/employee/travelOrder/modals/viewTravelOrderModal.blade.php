{{-- View Travel Order Detail Modal --}}
<x-modal id="viewTravelDetailModal" close="closeTravelDetailModal" title="-" title-id="detailDestination" subtitle="-" subtitle-id="detailTravelDates">
    <x-slot:eyebrow>TRAVEL ORDER · TO-<span id="detailOrderId">-</span></x-slot:eyebrow>
    <div class="modal-body">
        <div class="modal-emp-row">
            <div class="emp-avatar modal-emp-avatar" id="detailFilerAvatar">{{ strtoupper(substr(auth()->user()->employee->first_name ?? 'E', 0, 1) . substr(auth()->user()->employee->last_name ?? 'E', 0, 1)) }}</div>
            <div>
                <p class="modal-emp-id" id="detailFilerInfo">{{ auth()->user()->employee->employee_id ?? 'N/A' }}</p>
                <span class="badge-status" id="detailTravelStatus">-</span>
            </div>
        </div>

        <span class="modal-section-label">TRAVEL DETAILS</span>
        <div class="modal-row"><span>Destination</span><strong id="detailDestinationFull">-</strong></div>
        <div class="modal-row"><span>Departure Date</span><strong id="detailDepartureDate">-</strong></div>
        <div class="modal-row"><span>Return Date</span><strong id="detailReturnDate">-</strong></div>
        <div class="modal-row"><span>Duration</span><strong id="detailDuration">-</strong></div>
        <div class="modal-row"><span>Transportation</span><strong id="detailTransportation">-</strong></div>
        <div class="modal-row"><span>Estimated Budget</span><strong id="detailBudget">-</strong></div>

        <span class="modal-section-label modal-section-deductions">PURPOSE OF TRAVEL</span>
        <div class="modal-row"><span id="detailPurpose" style="line-height: 1.6;">-</span></div>

        <div id="detailCompanionsSection" style="display: none;">
            <span class="modal-section-label modal-section-deductions">TRAVEL COMPANIONS</span>
            <div id="detailCompanionsList" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;"></div>
        </div>

        <div id="detailHistorySection" style="display: none;">
            <span class="modal-section-label modal-section-deductions">DOCUMENT HISTORY</span>
            <div id="detailHistoryList" style="margin-top: 8px;"></div>
        </div>

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
        <button class="modal-btn-ghost" onclick="closeTravelDetailModal()">Close</button>
        <button class="modal-btn-primary" id="detailCancelBtn" style="display: none; background: #dc2626;" onclick="cancelTravelOrder()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            Cancel Travel Order
        </button>
    </div>
</x-modal>

<script>
let currentTravelOrderId = null;

function closeTravelDetailModal() {
    document.getElementById('viewTravelDetailModal').style.display = 'none';
    document.body.style.overflow = '';
    currentTravelOrderId = null;
}

function viewTravelOrder(id) {
    currentTravelOrderId = id;

    fetch(`/employee/travelorder/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set header info
            document.getElementById('detailOrderId').textContent = data.id;
            document.getElementById('detailDestination').textContent = data.destination;
            document.getElementById('detailTravelDates').textContent =
                new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) +
                ' — ' +
                new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

            // Set status badge
            const statusBadge = document.getElementById('detailTravelStatus');
            if (data.status === 'pending') {
                statusBadge.className = 'badge-status pending';
                statusBadge.textContent = 'Pending';
            } else if (data.status === 'approved') {
                statusBadge.className = 'badge-status processed';
                statusBadge.textContent = 'Approved';
            } else if (data.status === 'rejected') {
                statusBadge.className = 'badge-status on-hold';
                statusBadge.textContent = 'Rejected';
            } else if (data.status === 'awaiting_companions') {
                statusBadge.className = 'badge-status pending';
                statusBadge.style.background = '#ede9fe';
                statusBadge.style.color = '#6d28d9';
                statusBadge.textContent = 'Awaiting Companions';
            } else {
                statusBadge.className = 'badge-status';
                statusBadge.style.background = '#f3f4f6';
                statusBadge.style.color = '#6b7280';
                statusBadge.textContent = 'Cancelled';
            }

            // Set travel details
            document.getElementById('detailDestinationFull').textContent = data.destination;
            document.getElementById('detailDepartureDate').textContent = new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('detailReturnDate').textContent = new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('detailDuration').textContent = data.duration + ' days';
            document.getElementById('detailTransportation').textContent = data.transportation_mode || 'Not specified';
            document.getElementById('detailBudget').textContent = data.estimated_budget ? '₱' + parseFloat(data.estimated_budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Not specified';
            document.getElementById('detailPurpose').textContent = data.purpose;

            // Show/hide approval section
            const approvalSection = document.getElementById('detailApprovalSection');
            if (data.status !== 'pending' && data.approved_by) {
                approvalSection.style.display = 'block';
                document.getElementById('detailApprovedBy').textContent = data.approver ?
                    `${data.approver.employee?.first_name || 'Admin'} ${data.approver.employee?.last_name || 'User'}` :
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

            // Show who filed the order (a companion may be viewing someone else's order)
            if (data.employee) {
                document.getElementById('detailFilerAvatar').textContent =
                    ((data.employee.first_name || 'E')[0] + (data.employee.last_name || 'E')[0]).toUpperCase();
                document.getElementById('detailFilerInfo').textContent =
                    `${data.employee.first_name || ''} ${data.employee.last_name || ''} · ${data.employee.employee_id || 'N/A'}`;
            }

            // Render companions and document history
            renderTravelCompanions(data.companions || []);
            renderTravelHistory(data.histories || []);

            // Show/hide cancel button (only while not yet processed by HR, and only for the filer)
            const cancelBtn = document.getElementById('detailCancelBtn');
            const isFiler = data.employee && data.employee.employee_id === '{{ auth()->user()->employee->employee_id ?? '' }}';
            if (isFiler && (data.status === 'pending' || data.status === 'awaiting_companions')) {
                cancelBtn.style.display = 'inline-flex';
            } else {
                cancelBtn.style.display = 'none';
            }

            // Show modal
            document.getElementById('viewTravelDetailModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load travel order details');
        });
}

function travelCompanionAvatar(employee, size) {
    if (employee && employee.photo) {
        const src = /^(\/|https?:)/.test(employee.photo) ? employee.photo : `/storage/${employee.photo}`;
        return `<img src="${src}" alt="" style="width: ${size}px; height: ${size}px; border-radius: 50%; object-fit: cover; border: 1px solid #e5e7eb; flex-shrink: 0;">`;
    }
    const initials = (((employee?.first_name || 'E')[0]) + ((employee?.last_name || 'E')[0])).toUpperCase();
    return `<span style="width: ${size}px; height: ${size}px; border-radius: 50%; background: linear-gradient(135deg, #0b044d, #4338ca); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: ${Math.round(size * 0.38)}px; font-weight: 700; flex-shrink: 0;">${initials}</span>`;
}

function renderTravelCompanions(companions) {
    const section = document.getElementById('detailCompanionsSection');
    const list = document.getElementById('detailCompanionsList');

    if (!companions.length) {
        section.style.display = 'none';
        list.innerHTML = '';
        return;
    }

    const badges = {
        pending:  '<span class="badge-status pending">Pending</span>',
        accepted: '<span class="badge-status processed">Accepted</span>',
        rejected: '<span class="badge-status on-hold">Rejected</span>'
    };

    list.innerHTML = companions.map(companion => {
        const emp = companion.employee || {};
        const name = `${emp.first_name || ''} ${emp.last_name || ''}`.trim() || 'Unknown employee';
        const note = companion.response_note ? `<p style="margin: 2px 0 0; font-size: 11px; color: #9ca3af; font-style: italic;">“${companion.response_note}”</p>` : '';
        return `<div style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #f9fafb; border-radius: 8px;">
            ${travelCompanionAvatar(emp, 32)}
            <div style="flex: 1; min-width: 0;">
                <p style="margin: 0; font-size: 13px; font-weight: 600; color: #0b044d;">${name}</p>
                <p style="margin: 0; font-size: 11px; color: #9ca3af;">${emp.employee_id || ''}</p>
                ${note}
            </div>
            ${badges[companion.status] || ''}
        </div>`;
    }).join('');

    section.style.display = 'block';
}

const travelHistoryLabels = {
    filed: 'Filed',
    companion_invited: 'Companion Invited',
    companion_accepted: 'Companion Accepted',
    companion_rejected: 'Companion Rejected',
    forwarded_to_hr: 'Forwarded to HR',
    approved: 'Approved',
    disapproved: 'Disapproved'
};

function renderTravelHistory(histories) {
    const section = document.getElementById('detailHistorySection');
    const list = document.getElementById('detailHistoryList');

    if (!histories.length) {
        section.style.display = 'none';
        list.innerHTML = '';
        return;
    }

    const dotColors = {
        filed: '#0369a1',
        companion_invited: '#6d28d9',
        companion_accepted: '#15803d',
        companion_rejected: '#dc2626',
        forwarded_to_hr: '#a16207',
        approved: '#15803d',
        disapproved: '#dc2626'
    };

    list.innerHTML = histories.map(entry => {
        const when = new Date(entry.created_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        const label = travelHistoryLabels[entry.action] || entry.action;
        const remarks = entry.remarks ? `<p style="margin: 2px 0 0; font-size: 12px; color: #6b7280; line-height: 1.5;">${entry.remarks}</p>` : '';
        return `<div style="display: flex; gap: 10px; padding: 6px 0;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: ${dotColors[entry.action] || '#9ca3af'}; margin-top: 4px; flex-shrink: 0;"></span>
                <span style="flex: 1; width: 2px; background: #e5e7eb;"></span>
            </div>
            <div style="flex: 1; padding-bottom: 6px;">
                <p style="margin: 0; font-size: 12px; font-weight: 700; color: #0b044d;">${label} <span style="font-weight: 400; color: #9ca3af; font-size: 11px;">· ${when}</span></p>
                ${remarks}
            </div>
        </div>`;
    }).join('');

    section.style.display = 'block';
}

function cancelTravelOrder() {
    if (!currentTravelOrderId) return;

    if (confirm('Are you sure you want to cancel this travel order?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employee/travelorder/${currentTravelOrderId}`;

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
