<!-- Payslip Detail Modal -->
<div id="payslipDetailModal" class="modal-overlay" style="display: none;">
    <div class="modal-container" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title">Payslip Details</h3>
            <button type="button" class="modal-close" onclick="closePayslipModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Employee Info -->
            <div class="payslip-header">
                <div class="payslip-logo">
                    <img src="{{ asset('municipal-of-pagsanjan-logo.jpg') }}" alt="Pagsanjan Logo" class="logo-image">
                    <h2>MUNICIPAL GOVERNMENT OF PAGSANJAN</h2>
                    <p>Province of Laguna</p>
                    <h3 class="payslip-title">PAYSLIP</h3>
                </div>
            </div>

            <div class="payslip-info-grid">
                <div class="info-group">
                    <label>Employee Name:</label>
                    <strong id="modalEmployeeName">-</strong>
                </div>
                <div class="info-group">
                    <label>Employee ID:</label>
                    <strong id="modalEmployeeId">-</strong>
                </div>
                <div class="info-group">
                    <label>Department:</label>
                    <strong id="modalDepartment">-</strong>
                </div>
                <div class="info-group">
                    <label>Position:</label>
                    <strong id="modalPosition">-</strong>
                </div>
                <div class="info-group">
                    <label>Period:</label>
                    <strong id="modalPeriod">-</strong>
                </div>
                <div class="info-group">
                    <label>Pay Date:</label>
                    <strong id="modalPayDate">-</strong>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Earnings Section -->
            <div class="payslip-section">
                <h4 class="section-title">Earnings</h4>
                <div class="payslip-table">
                    <div class="table-row">
                        <span>Monthly Rate:</span>
                        <strong id="modalMonthlyRate">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Daily Rate:</span>
                        <strong id="modalDailyRate">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Days Worked:</span>
                        <strong id="modalDaysWorked">0</strong>
                    </div>
                    <div class="table-row highlight">
                        <span>Basic Pay:</span>
                        <strong id="modalBasicPay">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Overtime Pay:</span>
                        <strong id="modalOtPay">₱0.00</strong>
                    </div>
                    <div class="table-row total">
                        <span>Gross Pay:</span>
                        <strong id="modalGrossPay">₱0.00</strong>
                    </div>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Deductions Section -->
            <div class="payslip-section">
                <h4 class="section-title">Deductions</h4>
                <div class="payslip-table">
                    <div class="table-row">
                        <span>Late Deduction:</span>
                        <strong class="deduction-amount" id="modalLateDeduction">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Undertime Deduction:</span>
                        <strong class="deduction-amount" id="modalUndertimeDeduction">₱0.00</strong>
                    </div>
                    <div id="modalDeductionBreakdown">
                        <!-- Dynamic deduction breakdown will be inserted here -->
                    </div>
                    <div class="table-row total">
                        <span>Total Deductions:</span>
                        <strong class="deduction-amount" id="modalTotalDeductions">₱0.00</strong>
                    </div>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Net Pay Section -->
            <div class="payslip-section">
                <div class="net-pay-box">
                    <span>NET PAY</span>
                    <strong id="modalNetPay">₱0.00</strong>
                </div>
            </div>

            <!-- Status and Notes -->
            <div class="payslip-footer">
                <div class="status-info">
                    <label>Status:</label>
                    <span id="modalStatus" class="badge-status">-</span>
                </div>
                <div class="notes-info" id="modalNotesSection" style="display: none;">
                    <label>Notes:</label>
                    <p id="modalNotes">-</p>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-row">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p class="signature-label">Employee Signature</p>
                        <p class="signature-date">Date: <span id="employeeSignDate">__________</span></p>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p class="signature-label">Prepared By</p>
                        <p class="signature-name" id="preparedByName">{{ Auth::user()->employee->first_name ?? 'Admin' }} {{ Auth::user()->employee->last_name ?? '' }}</p>
                        <p class="signature-date">Date Released: <span id="releaseDate">__________</span></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePayslipModal()">Close</button>
            <button type="button" class="btn-primary" onclick="printPayslip()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print Payslip
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
    overflow: hidden;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e8e7f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
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
    background: #fff;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e8e7f5;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    background: #fff;
}

.btn-secondary {
    padding: 10px 20px;
    background: #f7f6ff;
    color: #0b044d;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
}

.btn-secondary:hover {
    background: #e8e7f5;
}

.btn-primary {
    padding: 10px 20px;
    background: #0b044d;
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

.btn-primary:hover {
    background: #1a0f6e;
}

.payslip-header {
    text-align: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #0b044d;
}

.payslip-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.logo-image {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 8px;
}

.payslip-logo h2 {
    font-size: 18px;
    font-weight: 700;
    color: #0b044d;
    margin: 0;
    text-transform: uppercase;
}

.payslip-logo p {
    font-size: 13px;
    color: #6b6a8a;
    margin: 0;
}

.payslip-title {
    font-size: 16px;
    font-weight: 700;
    color: #0b044d;
    margin: 8px 0 0 0;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.payslip-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
    border: 1px solid #e8e7f5;
    padding: 16px;
    border-radius: 6px;
    background: #fafafe;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-group label {
    font-size: 11px;
    color: #6b6a8a;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-group strong {
    font-size: 14px;
    color: #0b044d;
    font-weight: 600;
}

.payslip-divider {
    height: 1px;
    background: #e8e7f5;
    margin: 20px 0;
}

.payslip-section {
    margin-bottom: 20px;
}

.section-title {
    font-size: 14px;
    font-weight: 700;
    color: #0b044d;
    margin: 0 0 12px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.payslip-table {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.table-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #fafafe;
    border-radius: 6px;
}

.table-row span {
    font-size: 13px;
    color: #6b6a8a;
}

.table-row strong {
    font-size: 13px;
    color: #0b044d;
    font-weight: 600;
}

.table-row.highlight {
    background: #f7f6ff;
    border: 1px solid #e8e7f5;
}

.table-row.total {
    background: #0b044d;
    color: #fff;
    margin-top: 8px;
}

.table-row.total span,
.table-row.total strong {
    color: #fff;
    font-size: 14px;
}

.deduction-amount {
    color: #dc2626 !important;
}

.net-pay-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%);
    border-radius: 8px;
    color: #fff;
}

.net-pay-box span {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1px;
}

.net-pay-box strong {
    font-size: 24px;
    font-weight: 700;
}

.payslip-footer {
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e8e7f5;
}

.status-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.status-info label {
    font-size: 12px;
    color: #6b6a8a;
    font-weight: 600;
}

.notes-info {
    margin-top: 12px;
}

.notes-info label {
    font-size: 12px;
    color: #6b6a8a;
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
}

.notes-info p {
    font-size: 13px;
    color: #0b044d;
    background: #f7f6ff;
    padding: 12px;
    border-radius: 6px;
    margin: 0;
}

.notes-info p {
    font-size: 13px;
    color: #0b044d;
    background: #f7f6ff;
    padding: 12px;
    border-radius: 6px;
    margin: 0;
}

.signature-section {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e8e7f5;
}

.signature-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.signature-box {
    text-align: center;
}

.signature-line {
    width: 100%;
    height: 60px;
    border-bottom: 2px solid #0b044d;
    margin-bottom: 8px;
}

.signature-label {
    font-size: 12px;
    font-weight: 600;
    color: #0b044d;
    margin: 0 0 4px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.signature-name {
    font-size: 13px;
    font-weight: 600;
    color: #0b044d;
    margin: 0 0 4px 0;
}

.signature-date {
    font-size: 11px;
    color: #6b6a8a;
    margin: 0;
}

@media print {
    body * {
        visibility: hidden;
    }
    
    #payslipDetailModal,
    #payslipDetailModal * {
        visibility: visible;
    }
    
    #payslipDetailModal {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: white;
        z-index: 9999;
    }
    
    .modal-overlay {
        position: static;
        background: none;
        padding: 0;
    }
    
    .modal-container {
        box-shadow: none;
        max-width: 100%;
        max-height: none;
        border-radius: 0;
    }
    
    .modal-header,
    .modal-footer {
        display: none !important;
    }
    
    .modal-body {
        padding: 15mm;
        overflow: visible;
        font-size: 11px;
    }
    
    .payslip-header {
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 2px solid #000;
        page-break-after: avoid;
    }
    
    .payslip-logo {
        flex-direction: row;
        justify-content: center;
        align-items: center;
        gap: 15px;
    }
    
    .logo-image {
        width: 100px;
        height: 100px;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .payslip-logo h2 {
        font-size: 20px;
        margin: 0;
    }
    
    .payslip-logo p {
        font-size: 13px;
        margin: 0;
    }
    
    .payslip-title {
        font-size: 18px;
        margin: 5px 0 0 0;
        letter-spacing: 3px;
    }
    
    .payslip-info-grid {
        display: table;
        width: 100%;
        margin-bottom: 10px;
        border: 1px solid #000;
    }
    
    .info-group {
        display: table-row;
    }
    
    .info-group label {
        display: table-cell;
        padding: 3px 8px;
        font-size: 9px;
        font-weight: 600;
        border-bottom: 1px solid #ddd;
        width: 30%;
        background: #f5f5f5;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .info-group strong {
        display: table-cell;
        padding: 3px 8px;
        font-size: 10px;
        border-bottom: 1px solid #ddd;
        border-left: 1px solid #ddd;
    }
    
    .payslip-divider {
        height: 0;
        margin: 8px 0;
        border: none;
    }
    
    .payslip-section {
        page-break-inside: avoid;
        margin-bottom: 8px;
    }
    
    .section-title {
        font-size: 11px;
        margin: 0 0 5px 0;
        padding: 3px 8px;
        background: #000;
        color: white;
        text-transform: uppercase;
        letter-spacing: 1px;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .payslip-table {
        display: table;
        width: 100%;
        border: 1px solid #000;
        border-collapse: collapse;
    }
    
    .table-row {
        display: table-row;
        padding: 0;
        margin: 0;
        background: white;
        border-radius: 0;
    }
    
    .table-row span {
        display: table-cell;
        padding: 3px 8px;
        font-size: 10px;
        border-bottom: 1px solid #ddd;
        width: 70%;
    }
    
    .table-row strong {
        display: table-cell;
        padding: 3px 8px;
        font-size: 10px;
        border-bottom: 1px solid #ddd;
        border-left: 1px solid #ddd;
        text-align: right;
        width: 30%;
    }
    
    .table-row.highlight {
        background: #f9f9f9;
        border: none;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .table-row.total {
        background: #000 !important;
        border-bottom: 2px solid #000;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .table-row.total span,
    .table-row.total strong {
        color: white !important;
        font-size: 11px;
        font-weight: 700;
        border-bottom: none;
        padding: 5px 8px;
    }
    
    .net-pay-box {
        display: table;
        width: 100%;
        padding: 8px 12px;
        background: #000 !important;
        border: 2px solid #000;
        border-radius: 0;
        margin: 8px 0;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .net-pay-box span {
        display: table-cell;
        font-size: 12px;
        font-weight: 700;
        color: white;
        width: 70%;
    }
    
    .net-pay-box strong {
        display: table-cell;
        font-size: 16px;
        font-weight: 700;
        color: white;
        text-align: right;
        width: 30%;
    }
    
    .payslip-footer {
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid #000;
        font-size: 9px;
    }
    
    .status-info {
        margin-bottom: 5px;
    }
    
    .status-info label {
        font-size: 9px;
    }
    
    .badge-status {
        padding: 2px 8px;
        font-size: 9px;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .notes-info {
        margin-top: 5px;
    }
    
    .notes-info label {
        font-size: 9px;
    }
    
    .notes-info p {
        font-size: 9px;
        padding: 5px;
        background: #f5f5f5;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .signature-section {
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #000;
        page-break-inside: avoid;
    }
    
    .signature-row {
        display: table;
        width: 100%;
    }
    
    .signature-box {
        display: table-cell;
        width: 50%;
        text-align: center;
        padding: 0 10px;
    }
    
    .signature-line {
        width: 100%;
        height: 40px;
        border-bottom: 1px solid #000;
        margin-bottom: 5px;
    }
    
    .signature-label {
        font-size: 9px;
        font-weight: 600;
        color: #000;
        margin: 0 0 3px 0;
        text-transform: uppercase;
    }
    
    .signature-name {
        font-size: 10px;
        font-weight: 600;
        color: #000;
        margin: 0 0 3px 0;
    }
    
    .signature-date {
        font-size: 8px;
        color: #000;
        margin: 0;
    }
    
    @page {
        margin: 15mm;
        size: A4 portrait;
    }
}
</style>

<script>
let currentPayslipId = null;

function closePayslipModal() {
    document.getElementById('payslipDetailModal').style.display = 'none';
    currentPayslipId = null;
}

function viewPayslipDetail(id) {
    currentPayslipId = id;
    
    // Fetch payslip details from server
    fetch(`/admin/payroll/payslip/${id}/details`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
</script>
