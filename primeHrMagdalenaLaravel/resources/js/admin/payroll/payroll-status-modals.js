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
    window.location.href = window.payrollRoutes.payslipsTab;
}

function closeFailedModal() {
    document.getElementById('payrollFailedModal').style.display = 'none';
}

function viewPayrollRecords() {
    window.location.href = window.payrollRoutes.payslipsTab;
}

function retryPayroll() {
    closeFailedModal();
    // Reset the form if needed
}

window.showSuccessModal = showSuccessModal;
window.showFailedModal = showFailedModal;
window.closeSuccessModal = closeSuccessModal;
window.closeFailedModal = closeFailedModal;
window.viewPayrollRecords = viewPayrollRecords;
window.retryPayroll = retryPayroll;
