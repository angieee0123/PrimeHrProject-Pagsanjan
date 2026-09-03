// Monetization Requests Tab — mirrors leave-requests-tab.js: approve /
// disapprove over fetch, a status filter over the rendered rows, and a
// detail modal rendering the office's Monetization computation sheet.

function monetEsc(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

function monetMoney(value) {
    return '₱' + Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function monetDays(value) {
    const n = Number(value || 0);
    return n.toFixed(1) + ' ' + (n === 1 ? 'day' : 'days');
}

// The computation sheet from docs/excels/Monetization-2022 2.docx:
// Name / Position / Salary, VL + SL credits, and TLB = S × D × CF.
function adminMonetSheetHtml(r) {
    const total = Number(r.vl_balance || 0) + Number(r.sl_balance || 0);
    return `
        <div style="text-align: center; margin-bottom: 16px;">
            <p style="margin: 0; font-size: 13px; color: var(--theme-neutral-700);">Province of Laguna</p>
            <p style="margin: 0; font-size: 14px; font-weight: 800; color: var(--theme-neutral-950);">Municipality of Pagsanjan</p>
            <p style="margin: 4px 0 0; font-size: 15px; font-weight: 800; color: var(--gp-pri);">Monetization</p>
        </div>
        <div style="display: grid; gap: 8px; font-size: 13px; margin-bottom: 14px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Name</span><strong>${monetEsc(r.employee_name)}</strong></div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Position</span><strong>${monetEsc(r.position || 'N/A')}</strong></div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Salary</span><strong>${monetMoney(r.monthly_salary)}</strong></div>
        </div>
        <p style="font-size: 12px; font-weight: 800; color: var(--theme-neutral-950); margin: 0 0 8px;">NO. OF LEAVE CREDITS AS OF ${monetEsc((r.filed_at || '').toUpperCase())}</p>
        <div style="display: grid; gap: 8px; font-size: 13px; margin-bottom: 14px;">
            <div style="display: flex; justify-content: space-between;"><span>Vacation Leave</span><strong>${monetDays(r.vl_balance)}</strong></div>
            <div style="display: flex; justify-content: space-between;"><span>Sick Leave</span><strong>${monetDays(r.sl_balance)}</strong></div>
            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--theme-neutral-200); padding-top: 8px;"><span>Total Earned Leave Credits</span><strong>${monetDays(total)}</strong></div>
        </div>
        <p style="font-size: 12px; font-weight: 800; color: var(--theme-neutral-950); margin: 0 0 8px;">COMPUTATION: TOTAL LEAVE BENEFITS = S × D × CF</p>
        <div style="font-size: 13px; display: grid; gap: 4px; background: var(--theme-neutral-50); border: 1px solid var(--theme-neutral-200); border-radius: 8px; padding: 12px 14px;">
            <div>S (Monthly Salary) = ${monetMoney(r.monthly_salary)}</div>
            <div>D (Days Monetized: ${monetDays(r.vl_days)} VL + ${monetDays(r.sl_days)} SL) = ${monetDays(r.total_days)}</div>
            <div>CF (Constant Factor) = ${monetEsc(r.constant_factor)}</div>
            <div style="margin-top: 6px; font-weight: 700;">TLB = ${monetMoney(r.monthly_salary)} × ${monetEsc(r.total_days)} × ${monetEsc(r.constant_factor)} = <span style="color: var(--gp-pri); font-size: 15px;">${monetMoney(r.computed_amount)}</span></div>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-top: 14px;">
            <span style="color: var(--theme-neutral-700);">Reason: ${monetEsc(r.reason || '—')}</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-top: 8px;">
            <span style="color: var(--theme-neutral-700);">Approved by</span><strong>${monetEsc(r.decided_by || '—')}${r.decided_at ? ' · ' + monetEsc(r.decided_at) : ''}</strong>
        </div>`;
}

function adminMonetDetailsHtml(r) {
    const banner = r.status === 'disapproved'
        ? `<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; text-align: center;">DISAPPROVED${r.decided_at ? ' · ' + monetEsc(r.decided_at) : ''}</div>`
        : r.status === 'pending'
            ? `<div style="background: #fffbeb; border: 1px solid #fde68a; color: #b45309; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; text-align: center;">PENDING APPROVAL</div>`
            : `<div style="background: #f3f4f6; border: 1px solid var(--theme-neutral-300); color: var(--theme-neutral-700); font-weight: 700; font-size: 13px; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; text-align: center;">CANCELLED</div>`;
    return banner + `
        <div style="display: grid; gap: 8px; font-size: 13px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Employee</span><strong>${monetEsc(r.employee_name)} (${monetEsc(r.employee_id_no || 'N/A')})</strong></div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Position</span><strong>${monetEsc(r.position || 'N/A')}</strong></div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Department</span><strong>${monetEsc(r.department || 'N/A')}</strong></div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">VL Days</span><strong>${monetDays(r.vl_days)}</strong></div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">SL Days</span><strong>${monetDays(r.sl_days)}</strong></div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Estimated Amount</span><strong>${monetMoney(r.computed_amount)}</strong></div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Reason</span><strong style="text-align: right; max-width: 60%;">${monetEsc(r.reason || '—')}</strong></div>
            ${r.status === 'disapproved' ? `<div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--theme-neutral-200); padding-bottom: 6px;"><span style="color: var(--theme-danger); font-weight: 600;">Disapproval Reason</span><strong style="text-align: right; max-width: 60%;">${monetEsc(r.approver_remarks || '—')}</strong></div>` : ''}
            ${r.decided_by ? `<div style="display: flex; justify-content: space-between;"><span style="color: var(--theme-neutral-700); font-weight: 600;">Decided by</span><strong>${monetEsc(r.decided_by)}${r.decided_at ? ' · ' + monetEsc(r.decided_at) : ''}</strong></div>` : ''}
        </div>`;
}

window.openAdminMonetDetailModal = function(id) {
    fetch(`/admin/monetization/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Could not load the monetization request');
            return;
        }
        const r = data.request;
        document.getElementById('adminMonetRequestNumber').textContent = 'MONETIZATION REQUEST · ' + r.request_number;
        document.getElementById('adminMonetEmployeeName').textContent = r.employee_name;
        document.getElementById('adminMonetEmployeeId').textContent = r.employee_id_no || 'N/A';

        const statusBadge = document.getElementById('adminMonetStatus');
        const label = r.status.charAt(0).toUpperCase() + r.status.slice(1);
        statusBadge.textContent = label === 'Disapproved' ? 'Disapproved' : label;
        statusBadge.className = 'badge-status ' +
            (r.status === 'approved' ? 'processed' :
             r.status === 'pending' ? 'pending' :
             r.status === 'disapproved' ? 'rejected' : 'cancelled');

        document.getElementById('adminMonetDetailBody').innerHTML =
            r.status === 'approved' ? adminMonetSheetHtml(r) : adminMonetDetailsHtml(r);

        const decisionBtns = document.getElementById('adminMonetDecisionBtns');
        if (r.status === 'pending') {
            decisionBtns.style.display = 'flex';
            document.getElementById('adminMonetApproveBtn').onclick = () => approveMonetizationRequest(r.id, r.request_number);
            document.getElementById('adminMonetDisapproveBtn').onclick = () => {
                closeAdminMonetDetailModal();
                openMonetDisapproveModal(r.id, r.request_number);
            };
        } else {
            decisionBtns.style.display = 'none';
        }

        document.getElementById('adminMonetDetailModal').style.display = 'flex';
    })
    .catch(() => alert('Could not load the monetization request'));
};

window.closeAdminMonetDetailModal = function() {
    document.getElementById('adminMonetDetailModal').style.display = 'none';
};

window.approveMonetizationRequest = function(id, reqNumber) {
    if (!confirm(`Are you sure you want to approve monetization request ${reqNumber}?\n\nThe monetized days will be deducted from the employee's VL/SL balances.`)) {
        return;
    }

    fetch(`/admin/monetization/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Monetization request approved successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to approve monetization request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the monetization request');
    });
};

window.openMonetDisapproveModal = function(id, reqNumber) {
    window.currentMonetDisapproveId = id;
    document.getElementById('monetRejectModalTitle').textContent = `Disapprove ${reqNumber}`;
    document.getElementById('monetRejectionReason').value = '';
    document.getElementById('monetRejectModal').style.display = 'flex';
};

window.closeMonetDisapproveModal = function() {
    document.getElementById('monetRejectModal').style.display = 'none';
    window.currentMonetDisapproveId = null;
};

window.toggleMonetActionMenu = function(event, btn) {
    event.stopPropagation();
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.monet-action-menu').forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
};

document.addEventListener('click', () => {
    document.querySelectorAll('.monet-action-menu').forEach(m => m.style.display = 'none');
});

window.applyMonetizationAdminFilters = function() {
    const status = document.getElementById('filterMonetStatus')?.value || '';
    const rows = document.querySelectorAll('#monetRequestsTableBody tr');
    let visible = 0;
    let total = 0;

    rows.forEach(row => {
        if (row.querySelector('.emp-cell')) {
            total++;
            const show = !status || row.dataset.status === status;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        }
    });

    const totalEl = document.getElementById('monetRequestRowTotal');
    if (totalEl) totalEl.textContent = visible;
    const countEl = document.getElementById('monetRequestCount');
    if (countEl) countEl.textContent = total;
};

document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('monetConfirmRejectBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const reason = document.getElementById('monetRejectionReason').value.trim();

            if (!reason) {
                alert('Please provide a reason for disapproval');
                return;
            }

            const btn = this;
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Disapproving...';

            fetch(`/admin/monetization/${window.currentMonetDisapproveId}/disapprove`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ remarks: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = 'Disapproved!';
                    setTimeout(() => {
                        closeMonetDisapproveModal();
                        location.reload();
                    }, 800);
                } else {
                    alert(data.message || 'Failed to disapprove monetization request');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while disapproving the monetization request');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
        });
    }
});
