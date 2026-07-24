// Add/Deduct Manual Leave Credits Modal
import { initBusySingleDate } from '../../shared/busyDatesCalendar.js';

let employeeLeaveBalances = {};
let currentTransactionType = 'add';

// Busy-date calendar on the transaction date. INFORMATIONAL only: this is the
// ledger date the adjustment posts on, which is valid whether or not the
// employee is on leave that day — so nothing is blocked, and backdating stays
// allowed (minDate null) since corrections are routinely posted retroactively.
let manualCreditCal = null;
document.addEventListener('DOMContentLoaded', function () {
    manualCreditCal = initBusySingleDate({
        inputId: 'manualCreditDate',
        scope: 'admin',
        minDate: null,
    });
});

window.loadEmployeeLeaveTypes = function(employeeId) {
    // Repaint the date calendar for whoever was just picked.
    if (manualCreditCal) manualCreditCal.setEmployee(employeeId);

    if (!employeeId) {
        document.getElementById('leaveTypeSelect').innerHTML = '<option value="">Select Leave Type</option>';
        document.getElementById('currentBalanceDisplay').style.display = 'none';
        document.getElementById('previewBox').style.display = 'none';
        return;
    }

    // Fetch employee's leave balances
    fetch(`/admin/leave/employee/${employeeId}/balances`)
        .then(response => response.json())
        .then(data => {
            employeeLeaveBalances = data.balances;
            const select = document.getElementById('leaveTypeSelect');
            select.innerHTML = '<option value="">Select Leave Type</option>';

            data.leaveTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.leave_code;
                option.textContent = `${type.leave_code} - ${type.leave_name}`;
                select.appendChild(option);
            });

            // Update preview employee name
            const employeeSelect = document.querySelector('select[name="employee_id"]');
            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            document.getElementById('previewEmployee').textContent = selectedOption.text.split(' - ')[1];
        })
        .catch(error => {
            console.error('Error loading leave types:', error);
            alert('Failed to load leave types for this employee');
        });
}

window.showCurrentBalance = function(leaveCode) {
    if (!leaveCode || !employeeLeaveBalances[leaveCode]) {
        document.getElementById('currentBalanceDisplay').style.display = 'none';
        return;
    }

    const balance = employeeLeaveBalances[leaveCode];
    document.getElementById('currentBalanceValue').textContent = `${parseFloat(balance).toFixed(6)} days`;
    document.getElementById('currentBalanceDisplay').style.display = 'block';

    calculateNewBalance();
}

window.calculateNewBalance = function() {
    const leaveCode = document.querySelector('select[name="leave_code"]').value;
    const amount = parseFloat(document.querySelector('input[name="amount"]').value) || 0;

    if (!leaveCode || amount <= 0) {
        document.getElementById('previewBox').style.display = 'none';
        return;
    }

    const currentBalance = parseFloat(employeeLeaveBalances[leaveCode] || 0);
    const newBalance = currentTransactionType === 'add'
        ? currentBalance + amount
        : currentBalance - amount;

    // Check if deduction would result in negative balance
    if (currentTransactionType === 'deduct' && newBalance < 0) {
        document.getElementById('previewBox').style.background = '#fef2f2';
        document.getElementById('previewBox').style.border = '1px solid #fecaca';
        document.getElementById('previewIcon').setAttribute('stroke', '#dc2626');
        document.getElementById('previewTitle').style.color = '#dc2626';
        document.getElementById('previewText').style.color = '#991b1b';
        document.getElementById('previewText').innerHTML = `
            <strong>⚠️ Warning:</strong> This deduction will result in a negative balance of <strong>${newBalance.toFixed(6)} days</strong>.
            Current balance is only <strong>${currentBalance.toFixed(6)} days</strong>.
        `;
        document.getElementById('previewBox').style.display = 'block';
        return;
    }

    const leaveTypeSelect = document.querySelector('select[name="leave_code"]');
    const leaveTypeName = leaveTypeSelect.options[leaveTypeSelect.selectedIndex].text;

    // Set colors based on transaction type
    if (currentTransactionType === 'add') {
        document.getElementById('previewBox').style.background = '#f0fdf4';
        document.getElementById('previewBox').style.border = '1px solid #bbf7d0';
        document.getElementById('previewIcon').setAttribute('stroke', '#15803d');
        document.getElementById('previewTitle').style.color = '#15803d';
        document.getElementById('previewText').style.color = '#166534';
    } else {
        document.getElementById('previewBox').style.background = '#fef3c7';
        document.getElementById('previewBox').style.border = '1px solid #fde68a';
        document.getElementById('previewIcon').setAttribute('stroke', '#d97706');
        document.getElementById('previewTitle').style.color = '#d97706';
        document.getElementById('previewText').style.color = '#92400e';
    }

    document.getElementById('previewAmount').textContent = amount.toFixed(6);
    document.getElementById('previewAction').textContent = currentTransactionType === 'add' ? 'added to' : 'deducted from';
    document.getElementById('previewLeaveType').textContent = leaveTypeName;
    document.getElementById('previewNewBalance').textContent = newBalance.toFixed(6);
    document.getElementById('previewBox').style.display = 'block';
}

window.openManualCreditModal = function(type = 'add') {
    currentTransactionType = type;
    document.getElementById('transactionType').value = type;

    const form = document.getElementById('addManualCreditForm');
    form.reset();
    document.getElementById('currentBalanceDisplay').style.display = 'none';
    document.getElementById('previewBox').style.display = 'none';
    document.getElementById('leaveTypeSelect').innerHTML = '<option value="">Select Leave Type</option>';
    employeeLeaveBalances = {};

    // Update modal based on type
    if (type === 'add') {
        document.getElementById('modalTitle').textContent = 'Add Manual Leave Credits';
        document.getElementById('modalSubtitle').textContent = 'Manually add credits to employee leave balance';
        document.getElementById('amountLabel').innerHTML = 'Credit Amount (Days) <span style="color: #8e1e18;">*</span>';
        document.getElementById('amountHint').textContent = 'Number of days to add (up to 6 decimals, e.g., 0.125000 = 1 hour)';
        document.getElementById('submitBtn').textContent = 'Add Credits';
        document.getElementById('submitBtn').style.background = '#0b044d';
        document.getElementById('previewTitle').textContent = 'Preview - Adding Credits';
    } else {
        document.getElementById('modalTitle').textContent = 'Deduct Leave Credits';
        document.getElementById('modalSubtitle').textContent = 'Manually deduct credits from employee leave balance';
        document.getElementById('amountLabel').innerHTML = 'Deduction Amount (Days) <span style="color: #8e1e18;">*</span>';
        document.getElementById('amountHint').textContent = 'Number of days to deduct (up to 6 decimals, e.g., 0.125000 = 1 hour)';
        document.getElementById('submitBtn').textContent = 'Deduct Credits';
        document.getElementById('submitBtn').style.background = '#8e1e18';
        document.getElementById('previewTitle').textContent = 'Preview - Deducting Credits';
    }

    document.getElementById('addManualCreditModal').style.display = 'flex';
}

window.closeManualCreditModal = function(event) {
    if (!event || event.target.id === 'addManualCreditModal') {
        document.getElementById('addManualCreditModal').style.display = 'none';
    }
}

// Update preview when amount changes
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput) {
        amountInput.addEventListener('input', calculateNewBalance);
    }
});
