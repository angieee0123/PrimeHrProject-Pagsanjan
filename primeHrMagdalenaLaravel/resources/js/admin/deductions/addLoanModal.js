import { initBusyDateRange } from '../../shared/busyDatesCalendar.js';

// Busy-date calendar on the loan period. INFORMATIONAL only: a repayment
// period legitimately spans the employee's leave and travel days, so nothing
// is blocked. minDate null because loans can start retroactively.
let loanBusyCal = null;
document.addEventListener('DOMContentLoaded', function () {
    loanBusyCal = initBusyDateRange({
        fromId: 'loanStartDate',
        toId: 'loanEndDate',
        scope: 'admin',
        minDate: null,
        onChange: () => calculateLoanInstallment(),
    });

    // Repaint the marks whenever a different employee is picked.
    document.getElementById('loanEmployee')?.addEventListener('change', function () {
        if (loanBusyCal) loanBusyCal.setEmployee(this.value);
    });
});

function openAddLoanModal() {
    document.getElementById('addLoanModal').classList.add('active');
}

function closeAddLoanModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('addLoanModal').classList.remove('active');
    document.getElementById('addLoanForm').reset();
    document.getElementById('providerName').value = '';
    document.getElementById('otherProviderFields').style.display = 'none';
    document.getElementById('otherProviderName').removeAttribute('required');
    document.getElementById('otherLoanType').removeAttribute('required');
}

function handleLoanTypeChange() {
    const loanSelect = document.getElementById('loanProvider');
    const selectedOption = loanSelect.options[loanSelect.selectedIndex];
    const providerId = loanSelect.value;
    const providerType = selectedOption.getAttribute('data-provider');
    const providerNameInput = document.getElementById('providerName');
    const otherProviderFields = document.getElementById('otherProviderFields');
    const otherProviderName = document.getElementById('otherProviderName');
    const otherLoanType = document.getElementById('otherLoanType');
    const maxAmountHint = document.getElementById('maxAmountHint');
    const totalAmountInput = document.getElementById('loanTotalAmount');

    // Reset other provider fields
    otherProviderFields.style.display = 'none';
    otherProviderName.removeAttribute('required');
    otherLoanType.removeAttribute('required');
    maxAmountHint.style.display = 'none';
    totalAmountInput.removeAttribute('max');

    if (!providerId) {
        providerNameInput.value = '';
        return;
    }

    // Handle "Other" (external provider)
    if (providerId === 'OTHER') {
        providerNameInput.value = 'External Provider';
        otherProviderFields.style.display = 'flex';
        otherProviderName.setAttribute('required', 'required');
        otherLoanType.setAttribute('required', 'required');
        return;
    }

    // Set provider name based on data attribute
    if (providerType) {
        providerNameInput.value = providerType;
    }

    // Show loan constraints
    const maxAmount = selectedOption.getAttribute('data-max-amount');
    const interestRate = selectedOption.getAttribute('data-interest-rate');
    const maxTerms = selectedOption.getAttribute('data-max-terms');

    if (maxAmount && parseFloat(maxAmount) > 0) {
        maxAmountHint.textContent = `Max loanable: ₱${parseFloat(maxAmount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        maxAmountHint.style.display = 'block';
        totalAmountInput.setAttribute('max', maxAmount);
    }

    if (interestRate && parseFloat(interestRate) > 0) {
        maxAmountHint.textContent += ` | Interest: ${interestRate}%`;
    }

    if (maxTerms && parseInt(maxTerms) > 0) {
        maxAmountHint.textContent += ` | Max terms: ${maxTerms} months`;
    }
}

function calculateLoanInstallment() {
    const totalAmount = parseFloat(document.getElementById('loanTotalAmount').value) || 0;
    const startDate = document.getElementById('loanStartDate').value;
    const endDate = document.getElementById('loanEndDate').value;

    if (totalAmount > 0 && startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const months = Math.round((end - start) / (1000 * 60 * 60 * 24 * 30));

        if (months > 0) {
            const installment = (totalAmount / months).toFixed(2);
            document.getElementById('loanInstallment').value = installment;
        }
    }
}

window.openAddLoanModal = openAddLoanModal;
window.closeAddLoanModal = closeAddLoanModal;
window.handleLoanTypeChange = handleLoanTypeChange;
window.calculateLoanInstallment = calculateLoanInstallment;
