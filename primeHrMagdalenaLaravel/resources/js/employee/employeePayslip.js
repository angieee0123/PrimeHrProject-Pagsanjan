const sidebar      = document.getElementById('sidebar');
const toggleBtn    = document.getElementById('toggle-btn');
const logoText     = document.getElementById('logo-text');
const navLabel     = document.getElementById('nav-label');
const userInfo     = document.getElementById('user-info');
const sidebarFooter = document.getElementById('sidebar-footer');
const mobileBtn    = document.getElementById('mobile-menu-btn');
const overlay      = document.getElementById('mobile-overlay');

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        if (logoText) logoText.style.display  = collapsed ? 'none' : '';
        if (navLabel) navLabel.style.display  = collapsed ? 'none' : '';
        if (userInfo) userInfo.style.display  = collapsed ? 'none' : '';
        if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
    });
}

if (mobileBtn) {
    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
}

function openModal() {
    document.getElementById('payslipModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// The shared modal component's "close" prop is always called with no
// arguments, so the generic closeModal(id) needs a zero-arg wrapper.
function closePayslipModal() { closeModal('payslipModal'); }

function filterPermanentPayslip(query) {
    const q = query.toLowerCase();
    document.querySelectorAll('.payroll-table tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

/*
    Export the payslip register the table is showing.

    The button had no handler at all — it rendered, it hovered, and clicking it
    did nothing. The period and status filters are already in the link the
    server rendered, because those are the ones that produced the table; the
    search box is the only filter that never reaches the URL, so it is appended
    here.
*/
function exportPayslips() {
    const btn = document.querySelector('[data-export-url]');
    if (!btn) return;

    const url = new URL(btn.dataset.exportUrl, window.location.origin);
    const search = document.getElementById('employeePayslipSearch')?.value.trim() || '';

    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }

    // A plain navigation: the response is a streamed attachment, so the
    // browser's own download handling is what should receive it.
    window.location.href = url.toString();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
    }
});

let currentPayslipData = null;

function viewPayslipDetail(id) {
    fetch(`/employee/payslip/${id}/details`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentPayslipData = data.payslip;
            populatePayslipDetailModal(data.payslip);
            document.getElementById('payslipDetailModal').style.display = 'flex';
        } else {
            alert('Error: ' + (data.message || 'Failed to load payslip details'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load payslip details');
    });
}

function populatePayslipDetailModal(payslip) {
    // Employee Info
    document.getElementById('modalEmployeeName').textContent = payslip.employee_name;
    document.getElementById('modalEmployeeId').textContent = payslip.employee_id;
    document.getElementById('modalDepartment').textContent = payslip.department;
    document.getElementById('modalPosition').textContent = payslip.position;
    document.getElementById('modalPeriod').textContent = payslip.period;
    document.getElementById('modalPayDate').textContent = payslip.pay_date || 'Not set';

    // Earnings
    document.getElementById('modalMonthlyRate').textContent = '₱' + parseFloat(payslip.monthly_rate || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalDailyRate').textContent = '₱' + parseFloat(payslip.daily_rate || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalDaysWorked').textContent = payslip.total_days_present || 0;
    document.getElementById('modalBasicPay').textContent = '₱' + parseFloat(payslip.basic_pay).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalOtPay').textContent = '₱' + parseFloat(payslip.ot_pay || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalGrossPay').textContent = '₱' + parseFloat(payslip.gross_pay || (payslip.basic_pay + (payslip.ot_pay || 0))).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Deductions
    document.getElementById('modalLateDeduction').textContent = '₱' + parseFloat(payslip.late_deduction || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalUndertimeDeduction').textContent = '₱' + parseFloat(payslip.undertime_deduction || 0).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Deduction Breakdown
    const breakdownContainer = document.getElementById('modalDeductionBreakdown');
    breakdownContainer.innerHTML = '';

    // Parse deduction_breakdown if it exists
    let deductionBreakdown = payslip.deduction_breakdown;
    if (typeof deductionBreakdown === 'string') {
        try {
            deductionBreakdown = JSON.parse(deductionBreakdown);
        } catch (e) {
            console.error('Error parsing deduction_breakdown:', e);
            deductionBreakdown = {};
        }
    }

    // Check if deductionBreakdown is valid and has entries
    if (deductionBreakdown && typeof deductionBreakdown === 'object' && Object.keys(deductionBreakdown).length > 0) {
        Object.entries(deductionBreakdown).forEach(([code, deduction]) => {
            if (deduction && deduction.name && deduction.amount !== undefined && !isNaN(deduction.amount)) {
                const row = document.createElement('div');
                row.className = 'table-row';
                row.innerHTML = `
                    <span>${deduction.name}:</span>
                    <strong class="deduction-amount">₱${parseFloat(deduction.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                `;
                breakdownContainer.appendChild(row);
            }
        });
    }

    document.getElementById('modalTotalDeductions').textContent = '₱' + parseFloat(payslip.total_deductions || 0).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Net Pay
    document.getElementById('modalNetPay').textContent = '₱' + parseFloat(payslip.net_pay).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Status
    const statusBadge = document.getElementById('modalStatus');
    statusBadge.textContent = payslip.status.charAt(0).toUpperCase() + payslip.status.slice(1);
    statusBadge.className = 'badge-status ' + (payslip.status === 'pending' ? 'pending' : 'processed');

    // Notes
    if (payslip.notes) {
        document.getElementById('modalNotesSection').style.display = 'block';
        document.getElementById('modalNotes').textContent = payslip.notes;
    } else {
        document.getElementById('modalNotesSection').style.display = 'none';
    }
}

function closePayslipDetailModal() {
    document.getElementById('payslipDetailModal').style.display = 'none';
    currentPayslipData = null;
}

function printPayslip() {
    window.print();
}

function printPayslipDirect(id) {
    fetch(`/employee/payslip/${id}/details`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentPayslipData = data.payslip;
            populatePayslipDetailModal(data.payslip);
            // Make modal visible to print engine but not to the user
            const modal = document.getElementById('payslipDetailModal');
            modal.style.visibility = 'hidden';
            modal.style.display = 'flex';
            window.print();
            modal.style.display = 'none';
            modal.style.visibility = '';
            currentPayslipData = null;
        } else {
            alert('Error: ' + (data.message || 'Failed to load payslip'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load payslip');
    });
}

window.openModal = openModal;
window.closeModal = closeModal;
window.closePayslipModal = closePayslipModal;
window.filterPermanentPayslip = filterPermanentPayslip;
window.exportPayslips = exportPayslips;
window.viewPayslipDetail = viewPayslipDetail;
window.closePayslipDetailModal = closePayslipDetailModal;
window.printPayslip = printPayslip;
window.printPayslipDirect = printPayslipDirect;
