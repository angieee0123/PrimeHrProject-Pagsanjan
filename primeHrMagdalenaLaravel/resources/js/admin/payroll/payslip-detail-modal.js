let currentPayslipId = null;

function closePayslipModal() {
    const modal = document.getElementById('payslipDetailModal');
    modal.style.display = 'none';
    // Never leave the staging class behind — the next plain "View" would open
    // a modal parked off-screen and look like nothing happened.
    modal.classList.remove('is-print-staging');
    currentPayslipId = null;
}

/**
 * Fetch one payslip and fill the modal.
 *
 * Returns a promise so callers can wait for the data. "Print payslip" used to
 * fire window.print() on a 500ms timer alongside this fetch — a race it lost
 * whenever the server was slow, printing an empty sheet or, worse, whichever
 * payslip had been opened before. On a payroll document that is not a cosmetic
 * bug.
 *
 * `show` is false when the modal is only being staged for printing: the print
 * stylesheet isolates #payslipDetailModal and hides everything else, so the
 * markup has to exist and be laid out — but the person asked to print, not to
 * open it.
 */
function loadPayslipDetail(id, show = true) {
    currentPayslipId = id;

    // Fetch payslip details from server
    return fetch(`${window.payrollRoutes.payslipDetails}/${id}/details`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error: ' + (data.message || 'Failed to load payslip details'));
            return false;
        }

        const modal = document.getElementById('payslipDetailModal');
        populatePayslipModal(data.payslip);

        // Laid out either way — the print stylesheet needs it in flow — but
        // parked off-screen when it is only being staged for a print.
        modal.classList.toggle('is-print-staging', !show);
        modal.style.display = 'flex';
        return true;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load payslip details');
        return false;
    });
}

function viewPayslipDetail(id) {
    return loadPayslipDetail(id, true);
}

/**
 * "Print payslip" from a row menu — print without opening the modal.
 *
 * The old version called viewPayslipDetail() and then printed on a timer, so
 * the modal opened in front of the user and stayed open once the print dialog
 * closed. Nobody asked to read it; they asked for paper.
 */
function printPayslipDirect(id) {
    loadPayslipDetail(id, false).then((ok) => {
        if (!ok) {
            closePayslipModal();
            return;
        }

        // afterprint fires whether the dialog was confirmed or cancelled, which
        // is what we want: either way the staged modal has done its job. `once`
        // keeps repeated prints from stacking listeners.
        window.addEventListener('afterprint', () => closePayslipModal(), { once: true });
        window.print();
    });
}

function populatePayslipModal(payslip) {
    // Employee Info
    document.getElementById('modalEmployeeName').textContent = payslip.employee_name;
    document.getElementById('modalEmployeeId').textContent = payslip.employee_id;
    document.getElementById('modalDepartment').textContent = payslip.department;
    document.getElementById('modalPosition').textContent = payslip.position;
    document.getElementById('modalPeriod').textContent = payslip.period;
    document.getElementById('modalPayDate').textContent = payslip.pay_date || 'Not set';

    // Earnings
    document.getElementById('modalMonthlyRate').textContent = '₱' + parseFloat(payslip.monthly_rate).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalDailyRate').textContent = '₱' + parseFloat(payslip.daily_rate).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalDaysWorked').textContent = payslip.total_days_present;
    document.getElementById('modalBasicPay').textContent = '₱' + parseFloat(payslip.basic_pay).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalOtPay').textContent = '₱' + parseFloat(payslip.ot_pay).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalGrossPay').textContent = '₱' + parseFloat(payslip.gross_pay).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Deductions
    document.getElementById('modalLateDeduction').textContent = '₱' + parseFloat(payslip.late_deduction).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalUndertimeDeduction').textContent = '₱' + parseFloat(payslip.undertime_deduction).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Deduction Breakdown
    const breakdownContainer = document.getElementById('modalDeductionBreakdown');
    breakdownContainer.innerHTML = '';

    // Parse deduction_breakdown if it's a string
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
            // Validate deduction object
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

    document.getElementById('modalTotalDeductions').textContent = '₱' + parseFloat(payslip.total_deductions).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Net Pay
    document.getElementById('modalNetPay').textContent = '₱' + parseFloat(payslip.net_pay).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Status
    const statusBadge = document.getElementById('modalStatus');
    statusBadge.textContent = payslip.status.charAt(0).toUpperCase() + payslip.status.slice(1);
    statusBadge.className = 'badge-status ' + payslip.status;

    // Notes
    if (payslip.notes) {
        document.getElementById('modalNotesSection').style.display = 'block';
        document.getElementById('modalNotes').textContent = payslip.notes;
    } else {
        document.getElementById('modalNotesSection').style.display = 'none';
    }

    // Update release date to pay_date
    document.getElementById('releaseDate').textContent = payslip.pay_date || new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function printPayslip() {
    window.print();
}

window.closePayslipModal = closePayslipModal;
window.viewPayslipDetail = viewPayslipDetail;
window.printPayslip = printPayslip;
// Owned here rather than in payslip-management.js: this file owns the modal
// and the contract the print stylesheet depends on.
window.printPayslipDirect = printPayslipDirect;
