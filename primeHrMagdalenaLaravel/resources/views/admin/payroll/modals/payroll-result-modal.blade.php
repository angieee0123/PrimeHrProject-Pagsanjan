<div id="payrollResultModal" class="modal-overlay" style="display: none;">
    <div class="modal-container" style="max-width: 95%; width: 1400px;">
        <div class="modal-header">
            <h3 class="modal-title">Generated Payroll Summary</h3>
            <button type="button" class="modal-close" onclick="closePayrollModal()">&times;</button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div class="payroll-info-bar">
                <div class="info-item">
                    <span class="info-label">Period:</span>
                    <strong id="modalPeriod">-</strong>
                </div>
                <div class="info-item">
                    <span class="info-label">Pay Date:</span>
                    <strong id="modalPayDate">-</strong>
                </div>
                <div class="info-item">
                    <span class="info-label">Payroll Type:</span>
                    <strong id="modalPayrollType">-</strong>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Employees:</span>
                    <strong id="modalEmployeeCount">0</strong>
                </div>
            </div>

            <div class="table-wrapper" style="margin-top: 20px;">
                <table class="payroll-summary-table">
                    <thead>
                        <tr>
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Employee Name</th>
                            <th rowspan="2">Position</th>
                            <th rowspan="2">Department</th>
                            <th rowspan="2">Days Worked</th>
                            <th rowspan="2">Daily Rate</th>
                            <th colspan="2">Earnings</th>
                            <th colspan="4">Deductions</th>
                            <th rowspan="2">Total Deductions</th>
                            <th rowspan="2">Net Pay</th>
                        </tr>
                        <tr>
                            <th>Basic Pay</th>
                            <th>OT Pay</th>
                            <th>Late</th>
                            <th>Undertime</th>
                            <th>SSS/GSIS</th>
                            <th>Loans</th>
                        </tr>
                    </thead>
                    <tbody id="payrollTableBody">
                        <tr>
                            <td colspan="14" style="text-align: center; padding: 40px; color: #9999bb;">
                                Loading payroll data...
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="6" style="text-align: right; font-weight: 700;">TOTAL:</td>
                            <td id="totalBasicPay">₱0.00</td>
                            <td id="totalOtPay">₱0.00</td>
                            <td id="totalLate">₱0.00</td>
                            <td id="totalUndertime">₱0.00</td>
                            <td id="totalMandatory">₱0.00</td>
                            <td id="totalLoans">₱0.00</td>
                            <td id="totalDeductions">₱0.00</td>
                            <td id="totalNetPay">₱0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePayrollModal()">Close</button>
            <button type="button" class="btn-export-excel" onclick="exportToExcel()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export to Excel
            </button>
            <button type="button" class="btn-primary" onclick="confirmPayroll()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Confirm & Save
            </button>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.modal-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e8e7f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 18px;
    font-weight: 700;
    color: #0b044d;
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: #f7f6ff;
    color: #0b044d;
    font-size: 24px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.modal-close:hover {
    background: #e8e7f5;
}

.modal-body {
    padding: 24px;
    overflow-y: auto;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e8e7f5;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.payroll-info-bar {
    display: flex;
    gap: 24px;
    padding: 16px 20px;
    background: #f7f6ff;
    border-radius: 8px;
    border: 1px solid #e8e7f5;
}

.info-item {
    display: flex;
    gap: 8px;
    align-items: center;
}

.info-label {
    font-size: 12px;
    color: #6b6a8a;
    font-weight: 500;
}

.info-item strong {
    font-size: 13px;
    color: #0b044d;
    font-weight: 600;
}

.payroll-summary-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.payroll-summary-table thead {
    background: #0b044d;
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 10;
}

.payroll-summary-table th {
    padding: 10px 8px;
    text-align: center;
    font-weight: 600;
    border: 1px solid #1a0f6e;
    font-size: 11px;
}

.payroll-summary-table td {
    padding: 8px;
    border: 1px solid #e8e7f5;
    font-size: 12px;
}

.payroll-summary-table tbody tr:hover {
    background: #f7f6ff;
}

.payroll-summary-table .total-row {
    background: #f7f6ff;
    font-weight: 700;
}

.payroll-summary-table .total-row td {
    border-top: 2px solid #0b044d;
    padding: 12px 8px;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.btn-export-excel {
    padding: 10px 20px;
    background: #15803d;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-export-excel:hover {
    background: #166534;
}
</style>

<script>
function closePayrollModal() {
    document.getElementById('payrollResultModal').style.display = 'none';
}

function showPayrollModal(data) {
    // Populate modal info
    document.getElementById('modalPeriod').textContent = data.period;
    document.getElementById('modalPayDate').textContent = data.pay_date;
    document.getElementById('modalPayrollType').textContent = data.payroll_type;
    document.getElementById('modalEmployeeCount').textContent = data.employees.length;

    // Populate table
    const tbody = document.getElementById('payrollTableBody');
    tbody.innerHTML = '';

    let totals = {
        basicPay: 0,
        otPay: 0,
        late: 0,
        undertime: 0,
        mandatory: 0,
        loans: 0,
        deductions: 0,
        netPay: 0
    };

    data.employees.forEach((emp, index) => {
        const row = document.createElement('tr');
        
        const totalDeductions = emp.late + emp.undertime + emp.mandatory_deductions + emp.loan_deductions;
        const netPay = emp.basic_pay + emp.ot_pay - totalDeductions;

        totals.basicPay += emp.basic_pay;
        totals.otPay += emp.ot_pay;
        totals.late += emp.late;
        totals.undertime += emp.undertime;
        totals.mandatory += emp.mandatory_deductions;
        totals.loans += emp.loan_deductions;
        totals.deductions += totalDeductions;
        totals.netPay += netPay;

        row.innerHTML = `
            <td class="text-center">${index + 1}</td>
            <td>${emp.name}</td>
            <td>${emp.position}</td>
            <td>${emp.department}</td>
            <td class="text-center">${emp.days_worked}</td>
            <td class="text-right">₱${parseFloat(emp.daily_rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${parseFloat(emp.basic_pay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${parseFloat(emp.ot_pay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${parseFloat(emp.late).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${parseFloat(emp.undertime).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${parseFloat(emp.mandatory_deductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${parseFloat(emp.loan_deductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${parseFloat(totalDeductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="text-right">₱${parseFloat(netPay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
        `;
        tbody.appendChild(row);
    });

    // Update totals
    document.getElementById('totalBasicPay').textContent = '₱' + totals.basicPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalOtPay').textContent = '₱' + totals.otPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalLate').textContent = '₱' + totals.late.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalUndertime').textContent = '₱' + totals.undertime.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalMandatory').textContent = '₱' + totals.mandatory.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalLoans').textContent = '₱' + totals.loans.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalDeductions').textContent = '₱' + totals.deductions.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalNetPay').textContent = '₱' + totals.netPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    // Show modal
    document.getElementById('payrollResultModal').style.display = 'flex';
}

function exportToExcel() {
    // Get form data
    const form = document.getElementById('generatePayrollForm');
    const formData = new FormData(form);
    
    // Create URL with parameters
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("admin.payroll.export") }}?' + params.toString();
}

function confirmPayroll() {
    if (confirm('Are you sure you want to save this payroll? This action cannot be undone.')) {
        document.getElementById('generatePayrollForm').submit();
    }
}
</script>
