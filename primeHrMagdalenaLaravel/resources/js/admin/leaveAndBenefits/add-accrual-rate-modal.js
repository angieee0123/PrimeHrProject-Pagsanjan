// Add Accrual Rate Modal + Rate Calculator

window.updateAccrualHint = function() {
    const frequency = document.querySelector('select[name="accrual_frequency"]').value;
    const serviceHint = document.getElementById('serviceHint');
    const creditsHint = document.getElementById('creditsHint');
    const example = document.getElementById('calculationExample');

    if (frequency === 'daily') {
        serviceHint.textContent = 'Usually 1 day for daily accrual';
        creditsHint.innerHTML = 'Example: 0.0417 credits per day (1.25 days ÷ 30 days)';
        example.innerHTML = 'If an employee works <strong>30 days</strong>, they earn: 30 × 0.0417 = <strong>1.25 credits</strong>';
        document.querySelector('input[name="days_of_service_required"]').value = '1.00';
        document.querySelector('input[name="credits_earned_per_period"]').value = '0.0417';
    } else if (frequency === 'monthly') {
        serviceHint.textContent = 'Usually 30 days for monthly accrual';
        creditsHint.innerHTML = 'Example: 1.25 credits per month';
        example.innerHTML = 'If an employee works <strong>1 month (30 days)</strong>, they earn: <strong>1.25 credits</strong>';
        document.querySelector('input[name="days_of_service_required"]').value = '30.00';
        document.querySelector('input[name="credits_earned_per_period"]').value = '1.2500';
    } else if (frequency === 'yearly') {
        serviceHint.textContent = 'Usually 365 days for yearly accrual';
        creditsHint.innerHTML = 'Example: 15 credits per year';
        example.innerHTML = 'If an employee works <strong>1 year (365 days)</strong>, they earn: <strong>15 credits</strong>';
        document.querySelector('input[name="days_of_service_required"]').value = '365.00';
        document.querySelector('input[name="credits_earned_per_period"]').value = '15.0000';
    }
}

window.openCalculator = function() {
    document.getElementById('calculatorModal').classList.add('active');
    calculateRate();
}

window.closeCalculatorModal = function(event) {
    if (!event || event.target.id === 'calculatorModal') {
        document.getElementById('calculatorModal').classList.remove('active');
    }
}

window.calculateRate = function() {
    const annualDays = parseFloat(document.getElementById('calcAnnualDays').value) || 15;
    const method = document.getElementById('calcMethod').value;

    let rate, formula;

    if (method === 'daily') {
        rate = (annualDays / 360).toFixed(4);
        formula = `${annualDays} ÷ 360 = ${rate} credits per day`;
    } else {
        rate = (annualDays / 12).toFixed(4);
        formula = `${annualDays} ÷ 12 = ${rate} credits per month`;
    }

    document.getElementById('calculatedRate').textContent = rate;
    document.getElementById('calculationFormula').textContent = formula;
}

window.applyCalculatedRate = function() {
    const rate = document.getElementById('calculatedRate').textContent;
    const method = document.getElementById('calcMethod').value;

    document.querySelector('input[name="credits_earned_per_period"]').value = rate;

    if (method === 'daily') {
        document.querySelector('select[name="accrual_frequency"]').value = 'daily';
        document.querySelector('input[name="days_of_service_required"]').value = '1.00';
    } else {
        document.querySelector('select[name="accrual_frequency"]').value = 'monthly';
        document.querySelector('input[name="days_of_service_required"]').value = '30.00';
    }

    updateAccrualHint();
    closeCalculatorModal();
}

window.openAddAccrualRateModal = function() {
    const form = document.getElementById('addAccrualRateForm');
    form.reset();
    form.action = '/admin/leave/accrual-rates';

    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();

    document.querySelector('#addAccrualRateModal .modal-title').textContent = 'Add Accrual Rate';
    document.querySelector('#addAccrualRateModal .modal-subtitle').textContent = 'Configure leave credit earning rate';

    form.querySelector('.btn-submit').textContent = 'Add Accrual Rate';

    // Set default values
    document.querySelector('select[name="accrual_frequency"]').value = 'daily';
    document.querySelector('input[name="days_of_service_required"]').value = '1.00';
    document.querySelector('input[name="credits_earned_per_period"]').value = '0.0417';
    document.querySelector('select[name="is_active"]').value = '1';

    // Set today as default effective date
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="effective_date"]').value = today;

    updateAccrualHint();
    document.getElementById('addAccrualRateModal').classList.add('active');
}

window.closeAccrualRateModal = function(event) {
    if (!event || event.target.id === 'addAccrualRateModal') {
        document.getElementById('addAccrualRateModal').classList.remove('active');
    }
}

window.updateCalculationExample = function() {
    const credits = parseFloat(document.querySelector('input[name="credits_earned_per_period"]').value) || 0;
    const days = parseFloat(document.querySelector('input[name="days_of_service_required"]').value) || 1;
    const frequency = document.querySelector('select[name="accrual_frequency"]').value;

    let example;
    if (frequency === 'daily') {
        const result = (30 * credits).toFixed(2);
        example = `If an employee works <strong>30 days</strong>, they earn: 30 × ${credits} = <strong>${result} credits</strong>`;
    } else if (frequency === 'monthly') {
        example = `If an employee works <strong>1 month (${days} days)</strong>, they earn: <strong>${credits} credits</strong>`;
    } else {
        example = `If an employee works <strong>1 year (${days} days)</strong>, they earn: <strong>${credits} credits</strong>`;
    }

    document.getElementById('calculationExample').innerHTML = example;
}

// Update calculation example when inputs change
document.addEventListener('DOMContentLoaded', function() {
    const creditsInput = document.querySelector('input[name="credits_earned_per_period"]');
    const daysInput = document.querySelector('input[name="days_of_service_required"]');

    if (creditsInput && daysInput) {
        creditsInput.addEventListener('input', updateCalculationExample);
        daysInput.addEventListener('input', updateCalculationExample);
    }
});
