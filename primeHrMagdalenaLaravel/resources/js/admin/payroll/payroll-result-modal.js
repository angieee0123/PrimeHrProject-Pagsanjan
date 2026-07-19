let currentPayrollData = null;

function closePayrollModal() {
    document.getElementById('payrollResultModal').style.display = 'none';
}

function showPayrollModal(data) {
    currentPayrollData = data;

    // Populate modal info
    document.getElementById('modalPeriod').textContent = data.period;
    document.getElementById('modalPayDate').textContent = data.pay_date;
    document.getElementById('modalPayrollType').textContent = data.payroll_type;
    document.getElementById('modalEmployeeCount').textContent = data.employees.length;

    // Build dynamic table header
    const thead = document.querySelector('.payroll-summary-table thead');
    const deductionTypes = data.deduction_types || [];
    const deductionNames = data.deduction_names || {};

    thead.innerHTML = `
        <tr>
            <th rowspan="2">No.</th>
            <th rowspan="2">Employee Name</th>
            <th rowspan="2">Position</th>
            <th rowspan="2">Department</th>
            <th rowspan="2">Days Worked</th>
            <th rowspan="2">Daily Rate</th>
            <th colspan="2">Earnings</th>
            <th colspan="${2 + deductionTypes.length}">Deductions</th>
            <th rowspan="2">Total Deductions</th>
            <th rowspan="2">Net Pay</th>
        </tr>
        <tr>
            <th>Basic Pay</th>
            <th>OT Pay</th>
            <th>Late</th>
            <th>Undertime</th>
            ${deductionTypes.map(code => `<th>${deductionNames[code] || code}</th>`).join('')}
        </tr>
    `;

    // Populate table body
    const tbody = document.getElementById('payrollTableBody');
    tbody.innerHTML = '';

    let totals = {
        basicPay: 0,
        otPay: 0,
        late: 0,
        undertime: 0,
        deductions: {},
        totalDeductions: 0,
        netPay: 0
    };

    // Initialize deduction totals
    deductionTypes.forEach(code => {
        totals.deductions[code] = 0;
    });

    data.employees.forEach((emp, index) => {
        const row = document.createElement('tr');

        // Calculate total deductions - ensure all values are numbers
        const late = parseFloat(emp.late) || 0;
        const undertime = parseFloat(emp.undertime) || 0;
        let deductionSum = 0;

        // Sum all deduction amounts
        if (emp.deductions && typeof emp.deductions === 'object') {
            Object.values(emp.deductions).forEach(amount => {
                const deductAmount = parseFloat(amount) || 0;
                deductionSum += deductAmount;
            });
        }

        const totalDeductions = late + undertime + deductionSum;
        const basicPay = parseFloat(emp.basic_pay) || 0;
        const otPay = parseFloat(emp.ot_pay) || 0;
        const netPay = basicPay + otPay - totalDeductions;

        // Update totals
        totals.basicPay += basicPay;
        totals.otPay += otPay;
        totals.late += late;
        totals.undertime += undertime;
        totals.totalDeductions += totalDeductions;
        totals.netPay += netPay;

        // Update deduction totals
        deductionTypes.forEach(code => {
            const amount = parseFloat(emp.deductions[code]) || 0;
            totals.deductions[code] = (totals.deductions[code] || 0) + amount;
        });

        // Build deduction columns
        const deductionCells = deductionTypes.map(code => {
            const amount = parseFloat(emp.deductions[code]) || 0;
            return `<td class="text-right">₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>`;
        }).join('');

        row.innerHTML = `
            <td class="text-center">${index + 1}</td>
            <td>${emp.name}</td>
            <td>${emp.position}</td>
            <td>${emp.department}</td>
            <td class="text-center">${emp.days_worked}</td>
            <td class="text-right">₱${parseFloat(emp.daily_rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${basicPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${otPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${late.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${undertime.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            ${deductionCells}
            <td class="text-right">₱${totalDeductions.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${netPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
        `;
        tbody.appendChild(row);
    });

    // Build dynamic footer
    const tfoot = document.querySelector('.payroll-summary-table tfoot');
    const deductionTotalCells = deductionTypes.map(code => {
        const total = totals.deductions[code] || 0;
        return `<td id="total_${code}">₱${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>`;
    }).join('');

    tfoot.innerHTML = `
        <tr class="total-row">
            <td colspan="6" class="pr-total-label">TOTAL:</td>
            <td id="totalBasicPay">₱${totals.basicPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td id="totalOtPay">₱${totals.otPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td id="totalLate">₱${totals.late.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td id="totalUndertime">₱${totals.undertime.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            ${deductionTotalCells}
            <td id="totalDeductions">₱${totals.totalDeductions.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td id="totalNetPay">₱${totals.netPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
        </tr>
    `;

    // Show modal
    document.getElementById('payrollResultModal').style.display = 'flex';
}

function exportToExcel() {
    // Get form data
    const form = document.getElementById('generatePayrollForm');
    const formData = new FormData(form);

    // Create URL with parameters
    const params = new URLSearchParams(formData);
    window.location.href = window.payrollRoutes.export + '?' + params.toString();
}

function confirmPayroll() {
    // Show custom confirmation modal instead of built-in confirm
    showConfirmPayrollModal();
}

function showConfirmPayrollModal() {
    const modal = document.getElementById('confirmPayrollModal');
    modal.classList.add('active');
}

function closeConfirmPayrollModal() {
    const modal = document.getElementById('confirmPayrollModal');
    modal.classList.remove('active');
}

function proceedSavePayroll() {
    closeConfirmPayrollModal();

    const confirmBtn = document.querySelector('#payrollResultModal .btn-primary');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg> Saving...';

    const form = document.getElementById('generatePayrollForm');
    const formData = new FormData(form);

    // Submit to save endpoint
    fetch(window.payrollRoutes.generate, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        // Close the preview modal
        closePayrollModal();

        if (data.success) {
            showSuccessModal({
                message: data.message || 'Payroll has been successfully generated and saved.',
                details: {
                    employees_processed: data.employees_processed || currentPayrollData.employees.length,
                    total_gross: data.total_gross,
                    total_deductions: data.total_deductions,
                    total_net: data.total_net
                }
            });
        } else {
            showFailedModal({
                message: data.message || 'Failed to save payroll',
                errors: data.errors || []
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        closePayrollModal();
        showFailedModal({
            message: 'Failed to save payroll. Please try again.',
            error: error.message || 'An unexpected error occurred',
            errors: error.errors ? Object.values(error.errors).flat() : []
        });
    })
    .finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Confirm & Save';
    });
}

window.closePayrollModal = closePayrollModal;
window.showPayrollModal = showPayrollModal;
window.exportToExcel = exportToExcel;
window.confirmPayroll = confirmPayroll;
window.showConfirmPayrollModal = showConfirmPayrollModal;
window.closeConfirmPayrollModal = closeConfirmPayrollModal;
window.proceedSavePayroll = proceedSavePayroll;
