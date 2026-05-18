<!-- Success Modal -->
<div id="payrollSuccessModal" class="status-modal-overlay" style="display: none;">
    <div class="status-modal-container success">
        <div class="status-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="9 12 11 14 15 10"/>
            </svg>
        </div>
        <h3 class="status-title">Payroll Generated Successfully!</h3>
        <p class="status-message" id="successMessage">
            Payroll has been successfully generated and saved to the database.
        </p>
        <div class="status-details" id="successDetails"></div>
        <div class="status-actions">
            <button type="button" class="btn-primary" onclick="closeSuccessModal()">
                Close
            </button>
            <button type="button" class="btn-secondary" onclick="viewPayrollRecords()">
                View Records
            </button>
        </div>
    </div>
</div>

<!-- Failed Modal -->
<div id="payrollFailedModal" class="status-modal-overlay" style="display: none;">
    <div class="status-modal-container failed">
        <div class="status-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <h3 class="status-title">Payroll Generation Failed</h3>
        <p class="status-message" id="failedMessage">
            An error occurred while generating the payroll.
        </p>
        <div class="error-details" id="errorDetails"></div>
        <div class="status-actions">
            <button type="button" class="btn-primary" onclick="closeFailedModal()">
                Close
            </button>
            <button type="button" class="btn-secondary" onclick="retryPayroll()">
                Try Again
            </button>
        </div>
    </div>
</div>

<style>
.status-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.status-modal-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    padding: 40px;
    max-width: 500px;
    width: 90%;
    text-align: center;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.status-icon {
    margin: 0 auto 24px;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-modal-container.success .status-icon {
    background: #dcfce7;
    color: #15803d;
}

.status-modal-container.failed .status-icon {
    background: #fee2e2;
    color: #dc2626;
}

.status-title {
    font-size: 24px;
    font-weight: 700;
    color: #0b044d;
    margin: 0 0 12px 0;
}

.status-message {
    font-size: 14px;
    color: #6b6a8a;
    margin: 0 0 24px 0;
    line-height: 1.6;
}

.status-details {
    background: #f7f6ff;
    border: 1px solid #e8e7f5;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
    text-align: left;
}

.status-details .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e8e7f5;
}

.status-details .detail-row:last-child {
    border-bottom: none;
}

.status-details .detail-label {
    font-size: 13px;
    color: #6b6a8a;
    font-weight: 500;
}

.status-details .detail-value {
    font-size: 13px;
    color: #0b044d;
    font-weight: 600;
}

.error-details {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
    text-align: left;
}

.error-details .error-item {
    font-size: 13px;
    color: #991b1b;
    padding: 6px 0;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.error-details .error-item::before {
    content: "•";
    font-weight: bold;
}

.status-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.status-actions .btn-primary,
.status-actions .btn-secondary {
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    border: none;
    transition: all 0.2s;
}

.status-actions .btn-primary {
    background: #0b044d;
    color: #fff;
}

.status-actions .btn-primary:hover {
    background: #1a0f6e;
}

.status-actions .btn-secondary {
    background: #f7f6ff;
    color: #0b044d;
    border: 1px solid #e8e7f5;
}

.status-actions .btn-secondary:hover {
    background: #e8e7f5;
}
</style>

<script>
function showSuccessModal(data) {
    const modal = document.getElementById('payrollSuccessModal');
    const message = document.getElementById('successMessage');
    const details = document.getElementById('successDetails');
    
    // Set message
    if (data.message) {
        message.textContent = data.message;
    }
    
    // Set details
    if (data.details) {
        let detailsHtml = '';
        if (data.details.employees_processed) {
            detailsHtml += `
                <div class="detail-row">
                    <span class="detail-label">Employees Processed:</span>
                    <span class="detail-value">${data.details.employees_processed}</span>
                </div>
            `;
        }
        if (data.details.total_gross) {
            detailsHtml += `
                <div class="detail-row">
                    <span class="detail-label">Total Gross Pay:</span>
                    <span class="detail-value">₱${parseFloat(data.details.total_gross).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                </div>
            `;
        }
        if (data.details.total_deductions) {
            detailsHtml += `
                <div class="detail-row">
                    <span class="detail-label">Total Deductions:</span>
                    <span class="detail-value">₱${parseFloat(data.details.total_deductions).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                </div>
            `;
        }
        if (data.details.total_net) {
            detailsHtml += `
                <div class="detail-row">
                    <span class="detail-label">Total Net Pay:</span>
                    <span class="detail-value">₱${parseFloat(data.details.total_net).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                </div>
            `;
        }
        details.innerHTML = detailsHtml;
    }
    
    modal.style.display = 'flex';
}

function showFailedModal(error) {
    const modal = document.getElementById('payrollFailedModal');
    const message = document.getElementById('failedMessage');
    const errorDetails = document.getElementById('errorDetails');
    
    // Set message
    if (error.message) {
        message.textContent = error.message;
    }
    
    // Set error details
    if (error.errors && Array.isArray(error.errors)) {
        let errorsHtml = '';
        error.errors.forEach(err => {
            errorsHtml += `<div class="error-item">${err}</div>`;
        });
        errorDetails.innerHTML = errorsHtml;
    } else if (error.error) {
        errorDetails.innerHTML = `<div class="error-item">${error.error}</div>`;
    }
    
    modal.style.display = 'flex';
}

function closeSuccessModal() {
    document.getElementById('payrollSuccessModal').style.display = 'none';
    // Redirect to payslips tab to view the generated records
    window.location.href = '{{ route("admin.payroll", ["tab" => "payslips"]) }}';
}

function closeFailedModal() {
    document.getElementById('payrollFailedModal').style.display = 'none';
}

function viewPayrollRecords() {
    window.location.href = '{{ route("admin.payroll", ["tab" => "payslips"]) }}';
}

function retryPayroll() {
    closeFailedModal();
    // Reset the form if needed
}
</script>
