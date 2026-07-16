{{-- View Travel Order Modal --}}
<x-modal id="viewTravelOrderModal" close="closeViewTravelOrderModal" title="Travel Order Details" title-id="viewModalTitle" subtitle="-" subtitle-id="viewEmployeeInfo">
    <x-slot:eyebrow>TRAVEL ORDER · #<span id="viewOrderId">-</span></x-slot:eyebrow>
    <div class="modal-body">
        {{-- Status Badge --}}
        <div style="text-align: center; margin-bottom: 20px;">
            <span id="viewStatusBadge" class="badge-status">-</span>
        </div>

        <div class="modal-section-label">TRAVEL INFORMATION</div>
        <div class="modal-row"><span>Destination</span><strong id="viewDestination">-</strong></div>
        <div class="modal-row"><span>Departure Date</span><strong id="viewTravelDate">-</strong></div>
        <div class="modal-row"><span>Return Date</span><strong id="viewReturnDate">-</strong></div>
        <div class="modal-row"><span>Duration</span><strong id="viewDuration">-</strong></div>
        <div class="modal-row"><span>Transportation Mode</span><strong id="viewTransportation">-</strong></div>
        <div class="modal-row"><span>Estimated Budget</span><strong id="viewBudget">-</strong></div>

        <div class="modal-section-label" style="margin-top: 16px;">PURPOSE OF TRAVEL</div>
        <p id="viewPurpose" style="font-size: 13px; color: #374151; line-height: 1.6; margin: 8px 0; padding: 12px; background: #f9fafb; border-radius: 6px;">-</p>

        {{-- Travel Companions --}}
        <div id="viewCompanionsSection" style="display: none;">
            <div class="modal-section-label" style="margin-top: 16px;">TRAVEL COMPANIONS</div>
            <div id="viewCompanionsList" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;"></div>
        </div>

        {{-- Document History --}}
        <div id="viewHistorySection" style="display: none;">
            <div class="modal-section-label" style="margin-top: 16px;">DOCUMENT HISTORY</div>
            <div id="viewHistoryList" style="margin-top: 8px;"></div>
        </div>

        {{-- Approval Information --}}
        <div id="viewApprovalSection" style="display: none;">
            <div class="modal-section-label" style="margin-top: 16px;">APPROVAL INFORMATION</div>
            <div class="modal-row"><span>Processed By</span><strong id="viewApprovedBy">-</strong></div>
            <div class="modal-row"><span>Date Processed</span><strong id="viewApprovedAt">-</strong></div>

            <div id="viewRemarksSection" style="display: none; margin-top: 12px;">
                <div class="modal-section-label">REMARKS</div>
                <p id="viewRemarks" style="font-size: 13px; color: #991b1b; line-height: 1.6; margin: 8px 0; padding: 12px; background: #fee2e2; border-radius: 6px; border-left: 3px solid #dc2626;">-</p>
            </div>
        </div>

        {{-- Attachment --}}
        <div id="viewAttachmentSection" style="display: none; margin-top: 16px;">
            <div class="modal-section-label">SUPPORTING DOCUMENT</div>
            <a id="viewAttachmentLink" href="#" target="_blank" class="document-link" style="margin-top: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                </svg>
                View Attachment
            </a>
        </div>
    </div>
    <div class="modal-footer">
        <button class="modal-btn-ghost" onclick="closeViewTravelOrderModal()">Close</button>
    </div>
</x-modal>

<script>
function closeViewTravelOrderModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('viewTravelOrderModal').style.display = 'none';
    document.body.style.overflow = '';
}

function viewOrder(id) {
    fetch(`/admin/travelorder/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set basic info
            document.getElementById('viewOrderId').textContent = data.id;
            document.getElementById('viewEmployeeInfo').textContent =
                `${data.employee.first_name} ${data.employee.last_name} · ${data.employee.employee_id}`;

            // Set status badge
            const statusBadge = document.getElementById('viewStatusBadge');
            if (data.status === 'pending') {
                statusBadge.className = 'badge-status pending';
                statusBadge.textContent = 'Pending Approval';
            } else if (data.status === 'approved') {
                statusBadge.className = 'badge-status processed';
                statusBadge.textContent = 'Approved';
            } else if (data.status === 'rejected') {
                statusBadge.className = 'badge-status on-hold';
                statusBadge.textContent = 'Disapproved';
            } else {
                statusBadge.className = 'badge-status';
                statusBadge.style.background = '#f3f4f6';
                statusBadge.style.color = '#6b7280';
                statusBadge.textContent = 'Cancelled';
            }

            // Set travel info
            document.getElementById('viewDestination').textContent = data.destination;
            document.getElementById('viewDuration').textContent = data.duration + ' days';
            document.getElementById('viewTravelDate').textContent = new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('viewReturnDate').textContent = new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('viewPurpose').textContent = data.purpose;
            document.getElementById('viewTransportation').textContent = data.transportation_mode || 'Not specified';
            document.getElementById('viewBudget').textContent = data.estimated_budget ? '₱' + parseFloat(data.estimated_budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Not specified';

            // Show/hide approval section
            const approvalSection = document.getElementById('viewApprovalSection');
            if (data.status !== 'pending' && data.approved_by) {
                approvalSection.style.display = 'block';
                document.getElementById('viewApprovedBy').textContent = data.approver ? `${data.approver.employee?.first_name || 'Admin'} ${data.approver.employee?.last_name || 'User'}` : 'Admin User';
                document.getElementById('viewApprovedAt').textContent = data.approved_at ? new Date(data.approved_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';

                // Show remarks if rejected
                const remarksSection = document.getElementById('viewRemarksSection');
                if (data.status === 'rejected' && data.remarks) {
                    remarksSection.style.display = 'block';
                    document.getElementById('viewRemarks').textContent = data.remarks;
                } else {
                    remarksSection.style.display = 'none';
                }
            } else {
                approvalSection.style.display = 'none';
            }

            // Render companions and document history
            renderAdminTravelCompanions(data.companions || []);
            renderAdminTravelHistory(data.histories || []);

            // Show/hide attachment
            const attachmentSection = document.getElementById('viewAttachmentSection');
            if (data.attachment) {
                attachmentSection.style.display = 'block';
                document.getElementById('viewAttachmentLink').href = `/storage/${data.attachment}`;
            } else {
                attachmentSection.style.display = 'none';
            }

            // Show modal
            document.getElementById('viewTravelOrderModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load travel order details');
        });
}

function adminCompanionAvatar(employee, size) {
    if (employee && employee.photo) {
        const src = /^(\/|https?:)/.test(employee.photo) ? employee.photo : `/storage/${employee.photo}`;
        return `<img src="${src}" alt="" style="width: ${size}px; height: ${size}px; border-radius: 50%; object-fit: cover; border: 1px solid #e5e7eb; flex-shrink: 0;">`;
    }
    const initials = (((employee?.first_name || 'E')[0]) + ((employee?.last_name || 'E')[0])).toUpperCase();
    return `<span style="width: ${size}px; height: ${size}px; border-radius: 50%; background: linear-gradient(135deg, #0b044d, #4338ca); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: ${Math.round(size * 0.38)}px; font-weight: 700; flex-shrink: 0;">${initials}</span>`;
}

function renderAdminTravelCompanions(companions) {
    const section = document.getElementById('viewCompanionsSection');
    const list = document.getElementById('viewCompanionsList');

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
            ${adminCompanionAvatar(emp, 32)}
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

function renderAdminTravelHistory(histories) {
    const section = document.getElementById('viewHistorySection');
    const list = document.getElementById('viewHistoryList');

    if (!histories.length) {
        section.style.display = 'none';
        list.innerHTML = '';
        return;
    }

    const labels = {
        filed: 'Filed',
        companion_invited: 'Companion Invited',
        companion_accepted: 'Companion Accepted',
        companion_rejected: 'Companion Rejected',
        forwarded_to_hr: 'Forwarded to HR',
        approved: 'Approved',
        disapproved: 'Disapproved'
    };
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
        const label = labels[entry.action] || entry.action;
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
</script>
