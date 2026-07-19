let currentPayslipId = null;

function closePayslipModal() {
    document.getElementById('payslipDetailModal').style.display = 'none';
    currentPayslipId = null;
}

function viewPayslipDetail(id) {
    currentPayslipId = id;

    // Fetch payslip details from server
    fetch(`${window.payrollRoutes.payslipDetails}/${id}/details`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populatePayslipModal(data.payslip);
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
