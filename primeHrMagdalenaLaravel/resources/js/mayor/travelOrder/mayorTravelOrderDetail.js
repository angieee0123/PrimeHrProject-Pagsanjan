// The mayor's read-only Travel Order detail modal.
//
// Markup: views/mayor/travelOrder/modals/viewTravelOrderModal.blade.php
//
// Split out of mayorTravelOrder.js because two pages open this modal: the
// Travel Orders table and the Leave & Travel Calendar. The calendar cannot
// load the whole travel-order page script — its DOMContentLoaded handler
// reaches for the tab panels, which do not exist there — and a second copy of
// the fetch-and-fill would be a second thing to keep true about one document.

window.closeMayorViewTravelOrderModal = function (event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('mayorViewTravelOrderModal').style.display = 'none';
    document.body.style.overflow = '';
};

window.viewMayorTravelOrder = function (id) {
    fetch(`/mayor/travelorder/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set basic info
            document.getElementById('mtoOrderId').textContent = data.id;
            document.getElementById('mtoEmployeeInfo').textContent =
                `${data.employee.first_name} ${data.employee.last_name} · ${data.employee.employee_id}`;

            // Set status badge
            const statusBadge = document.getElementById('mtoStatusBadge');
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
                statusBadge.className = 'badge-status to-badge-cancelled';
                statusBadge.textContent = 'Cancelled';
            }

            // Set travel info
            document.getElementById('mtoDestination').textContent = data.destination;
            document.getElementById('mtoDuration').textContent = data.duration + ' days';
            document.getElementById('mtoTravelDate').textContent = new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('mtoReturnDate').textContent = new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('mtoPurpose').textContent = data.purpose;
            document.getElementById('mtoTransportation').textContent = data.transportation_mode || 'Not specified';
            document.getElementById('mtoBudget').textContent = data.estimated_budget ? '₱' + parseFloat(data.estimated_budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Not specified';

            // Show/hide approval section
            const approvalSection = document.getElementById('mtoApprovalSection');
            if (data.status !== 'pending' && data.approved_by) {
                approvalSection.style.display = 'block';
                document.getElementById('mtoApprovedBy').textContent = data.approver ? `${data.approver.employee?.first_name || 'Admin'} ${data.approver.employee?.last_name || 'User'}` : 'Admin User';
                document.getElementById('mtoApprovedAt').textContent = data.approved_at ? new Date(data.approved_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';

                // Show remarks if rejected
                const remarksSection = document.getElementById('mtoRemarksSection');
                if (data.status === 'rejected' && data.remarks) {
                    remarksSection.style.display = 'block';
                    document.getElementById('mtoRemarks').textContent = data.remarks;
                } else {
                    remarksSection.style.display = 'none';
                }
            } else {
                approvalSection.style.display = 'none';
            }

            // Render companions and document history
            renderMayorTravelCompanions(data.companions || []);
            renderMayorTravelHistory(data.histories || []);

            // Show/hide attachment
            const attachmentSection = document.getElementById('mtoAttachmentSection');
            if (data.attachment) {
                attachmentSection.style.display = 'block';
                document.getElementById('mtoAttachmentLink').href = `/storage/${data.attachment}`;
            } else {
                attachmentSection.style.display = 'none';
            }

            // Show modal
            document.getElementById('mayorViewTravelOrderModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load travel order details');
        });
};

function mayorCompanionAvatar(employee, size) {
    if (employee && employee.photo) {
        const src = /^(\/|https?:)/.test(employee.photo) ? employee.photo : `/storage/${employee.photo}`;
        return `<img src="${src}" alt="" class="to-companion-avatar-img" style="width: ${size}px; height: ${size}px;">`;
    }
    const initials = (((employee?.first_name || 'E')[0]) + ((employee?.last_name || 'E')[0])).toUpperCase();
    return `<span class="to-companion-avatar-fallback" style="width: ${size}px; height: ${size}px; font-size: ${Math.round(size * 0.38)}px;">${initials}</span>`;
}

function renderMayorTravelCompanions(companions) {
    const section = document.getElementById('mtoCompanionsSection');
    const list = document.getElementById('mtoCompanionsList');

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
        const note = companion.response_note ? `<p class="to-companion-note">“${companion.response_note}”</p>` : '';
        return `<div class="to-companion-row">
            ${mayorCompanionAvatar(emp, 32)}
            <div class="to-companion-info">
                <p class="to-companion-name">${name}</p>
                <p class="to-companion-id">${emp.employee_id || ''}</p>
                ${note}
            </div>
            ${badges[companion.status] || ''}
        </div>`;
    }).join('');

    section.style.display = 'block';
}

function renderMayorTravelHistory(histories) {
    const section = document.getElementById('mtoHistorySection');
    const list = document.getElementById('mtoHistoryList');

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
        companion_accepted: 'var(--theme-success)',
        companion_rejected: 'var(--theme-danger)',
        forwarded_to_hr: 'var(--theme-warning)',
        approved: 'var(--theme-success)',
        disapproved: 'var(--theme-danger)'
    };

    list.innerHTML = histories.map(entry => {
        const when = new Date(entry.created_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        const label = labels[entry.action] || entry.action;
        const remarks = entry.remarks ? `<p class="to-history-remarks">${entry.remarks}</p>` : '';
        return `<div class="to-history-row">
            <div class="to-history-rail">
                <span class="to-history-dot" style="background: ${dotColors[entry.action] || 'var(--theme-neutral-600)'};"></span>
                <span class="to-history-line"></span>
            </div>
            <div class="to-history-body">
                <p class="to-history-label">${label} <span class="to-history-when">· ${when}</span></p>
                ${remarks}
            </div>
        </div>`;
    }).join('');

    section.style.display = 'block';
}
