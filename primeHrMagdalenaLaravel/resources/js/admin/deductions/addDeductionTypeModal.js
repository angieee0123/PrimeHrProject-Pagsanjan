function openAddDeductionTypeModal() {
    document.getElementById('addDeductionTypeModal').classList.add('active');
}

function closeAddDeductionTypeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('addDeductionTypeModal').classList.remove('active');
}

document.getElementById('computationType')?.addEventListener('change', function() {
    const rateLabel = document.getElementById('rateLabel');
    const rateInput = document.getElementById('rateInput');
    const rateHint = document.getElementById('rateHint');
    const baseSalaryGroup = document.getElementById('baseSalaryGroup');
    const baseSalarySelect = document.getElementById('baseSalarySelect');
    const baseSalaryHint = document.getElementById('baseSalaryHint');
    const maxAmountGroup = document.getElementById('maxAmountGroup');
    const maxAmountInput = document.getElementById('maxAmountInput');
    const maxAmountHint = document.getElementById('maxAmountHint');

    // Reset hints
    rateHint.style.display = 'none';
    baseSalaryHint.style.display = 'none';
    maxAmountHint.style.display = 'none';

    if (this.value === 'PERCENTAGE') {
        // Percentage: Show all fields
        rateLabel.textContent = 'Rate (%)';
        rateInput.placeholder = 'e.g., 9.00';
        rateHint.textContent = 'Percentage to deduct from base salary';
        rateHint.style.display = 'block';

        baseSalaryGroup.style.display = 'flex';
        baseSalarySelect.value = '';
        baseSalaryHint.textContent = 'Select what salary component to calculate from';
        baseSalaryHint.style.display = 'block';

        maxAmountGroup.style.display = 'flex';
        maxAmountInput.value = '';
        maxAmountHint.textContent = 'Optional: Cap the deduction amount (e.g., Pag-IBIG max ₱100)';
        maxAmountHint.style.display = 'block';

    } else if (this.value === 'FIXED') {
        // Fixed: Hide base salary, show amount and optional max
        rateLabel.textContent = 'Fixed Amount';
        rateInput.placeholder = 'e.g., 500.00';
        rateHint.textContent = 'Fixed amount to deduct (e.g., union dues, uniform)';
        rateHint.style.display = 'block';

        baseSalaryGroup.style.display = 'none';
        baseSalarySelect.value = '';

        maxAmountGroup.style.display = 'flex';
        maxAmountInput.value = '';
        maxAmountHint.textContent = 'Optional: Usually not needed for fixed amounts';
        maxAmountHint.style.display = 'block';

    } else if (this.value === 'CUSTOM') {
        // Custom: Show all but with different hints
        rateLabel.textContent = 'Rate/Amount';
        rateInput.placeholder = 'N/A';
        rateHint.textContent = 'Custom logic will be used (e.g., withholding tax)';
        rateHint.style.display = 'block';

        baseSalaryGroup.style.display = 'flex';
        baseSalarySelect.value = 'CUSTOM';
        baseSalaryHint.textContent = 'Set to Custom for custom calculation logic';
        baseSalaryHint.style.display = 'block';

        maxAmountGroup.style.display = 'flex';
        maxAmountInput.value = '';
        maxAmountHint.textContent = 'Depends on custom logic implementation';
        maxAmountHint.style.display = 'block';

    } else {
        // Default: Show all fields
        rateLabel.textContent = 'Rate/Amount';
        rateInput.placeholder = 'Enter value';
        baseSalaryGroup.style.display = 'flex';
        maxAmountGroup.style.display = 'flex';
    }
});

window.openAddDeductionTypeModal = openAddDeductionTypeModal;
window.closeAddDeductionTypeModal = closeAddDeductionTypeModal;
