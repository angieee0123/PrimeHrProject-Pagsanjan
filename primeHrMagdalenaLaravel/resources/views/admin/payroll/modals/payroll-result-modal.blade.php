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
            <td colspan="6" style="text-align: right; font-weight: 700;">TOTAL:</td>
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
    window.location.href = '{{ route("admin.payroll.export") }}?' + params.toString();
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
    fetch('{{ route("admin.payroll.generate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
        confirmBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Confirm & Save';
    });
}
</script>

<!-- Confirm Payroll Modal -->
<div id="confirmPayrollModal" class="adm-overlay" onclick="closeConfirmPayrollModal()">
    <div class="adm-box" style="max-width:480px;" onclick="event.stopPropagation()">
        <div class="adm-header" style="background: linear-gradient(135deg, #d9bb00, #fbbf24); border-bottom: none;">
            <div class="adm-header-left">
                <div class="vdm-avatar" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div>
                    <span class="adm-eyebrow" style="color: rgba(255,255,255,0.9) !important;">CONFIRMATION REQUIRED</span>
                    <h3 class="adm-title" style="color: #fff !important;">Save Payroll</h3>
                </div>
            </div>
            <button class="adm-close" style="color: rgba(255,255,255,0.8);" onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.color='#fff'" onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.8)'" onclick="closeConfirmPayrollModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="vdm-body" style="padding: 32px 24px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <h4 style="font-size: 17px; font-weight: 600; color: #0b044d; margin: 0 0 12px 0;">Are you sure you want to save this payroll?</h4>
                <p style="font-size: 13.5px; color: #6b6a8a; line-height: 1.6; margin: 0;">This will create salary computation records for all employees in the selected period. This action cannot be undone.</p>
            </div>
            <div style="background: #fef9f3; border: 1px solid #fed7aa; border-radius: 10px; padding: 16px;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <div>
                        <p style="font-size: 12px; font-weight: 600; color: #92400e; margin: 0 0 6px 0;">What happens next:</p>
                        <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #78350f; line-height: 1.7;">
                            <li>Payroll records will be created for all employees</li>
                            <li>Payslips will be generated and visible to employees</li>
                            <li>You can still edit individual records if needed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="adm-footer" style="background: #fafafe; padding: 20px 24px;">
            <button class="adm-btn-ghost" onclick="closeConfirmPayrollModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Cancel
            </button>
            <button class="adm-btn-primary" onclick="proceedSavePayroll()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Yes, Save Payroll
            </button>
        </div>
    </div>
</div>

<style>
.adm-overlay {
    display: none;
    position: fixed;
    z-index: 99999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(11, 4, 77, 0.4);
    backdrop-filter: blur(4px);
}

.adm-overlay.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.adm-box {
    background: white;
    border-radius: 16px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(11, 4, 77, 0.3);
    animation: modalSlideIn 0.3s ease;
    overflow: hidden;
}

@keyframes modalSlideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.adm-header {
    padding: 24px;
    border-bottom: 1px solid #e8e6f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.adm-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.vdm-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.adm-eyebrow {
    font-size: 10px;
    font-weight: 700;
    color: #ffffff !important;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 2px;
}

.adm-title {
    margin: 0;
    color: #ffffff !important;
    font-size: 19px;
    font-weight: 600;
    line-height: 1.2;
}

.adm-close {
    background: none;
    border: none;
    color: #9999bb;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.adm-close:hover {
    background: #f0eeff;
    color: #0b044d;
}

.vdm-body {
    padding: 20px 24px;
}

.adm-footer {
    padding: 20px 24px;
    border-top: 1px solid #e8e6f5;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.adm-btn-ghost {
    padding: 10px 20px;
    background: transparent;
    color: #6b6a8a;
    border: 1px solid #e8e7f5;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.adm-btn-ghost:hover {
    background: #f7f6ff;
    color: #0b044d;
    border-color: #d0c9ff;
}

.adm-btn-primary {
    padding: 10px 24px;
    background: linear-gradient(135deg, #0b044d, #2d1a8e);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(11, 4, 77, 0.2);
}

.adm-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(11, 4, 77, 0.3);
}

.adm-btn-primary:active {
    transform: translateY(0);
}
</style>
