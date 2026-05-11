<div class="modal" id="addAccrualRateModal" onclick="closeAccrualRateModal(event)">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">Add Accrual Rate</h2>
                <p class="modal-subtitle">Configure leave credit earning rate</p>
            </div>
            <button class="modal-close" onclick="closeAccrualRateModal()">&times;</button>
        </div>

        <form id="addAccrualRateForm" method="POST" action="/admin/leave/accrual-rates">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <!-- Leave Type Selection -->
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">
                            Leave Type <span style="color: #8e1e18;">*</span>
                        </label>
                        <select name="leave_code" class="form-input" required>
                            <option value="">Select Leave Type</option>
                            <option value="VL">VL - Vacation Leave</option>
                            <option value="SL">SL - Sick Leave</option>
                            <!-- Add more accrued leave types as needed -->
                        </select>
                        <small class="form-hint">Only accrued leave types are shown</small>
                    </div>

                    <!-- Accrual Frequency -->
                    <div class="form-group">
                        <label class="form-label">
                            Accrual Frequency <span style="color: #8e1e18;">*</span>
                        </label>
                        <select name="accrual_frequency" class="form-input" required onchange="updateAccrualHint()">
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        <small class="form-hint">How often credits are earned</small>
                    </div>

                    <!-- Days of Service Required -->
                    <div class="form-group">
                        <label class="form-label">
                            Days of Service Required <span style="color: #8e1e18;">*</span>
                        </label>
                        <input type="number" name="days_of_service_required" class="form-input" step="0.01" min="0.01" value="1.00" required>
                        <small class="form-hint" id="serviceHint">Service period to earn credits</small>
                    </div>

                    <!-- Credits Earned Per Period -->
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">
                            Credits Earned Per Period <span style="color: #8e1e18;">*</span>
                        </label>
                        <input type="number" name="credits_earned_per_period" class="form-input" step="0.0001" min="0.0001" value="0.0417" required>
                        <small class="form-hint" id="creditsHint">
                            Example: 0.0417 credits per day (1.25 days ÷ 30 days)
                        </small>
                    </div>

                    <!-- Effective Date -->
                    <div class="form-group">
                        <label class="form-label">
                            Effective Date <span style="color: #8e1e18;">*</span>
                        </label>
                        <input type="date" name="effective_date" class="form-input" required>
                        <small class="form-hint">When this rate becomes active</small>
                    </div>

                    <!-- End Date -->
                    <div class="form-group">
                        <label class="form-label">
                            End Date
                        </label>
                        <input type="date" name="end_date" class="form-input">
                        <small class="form-hint">Leave empty for current rate</small>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label class="form-label">
                            Status <span style="color: #8e1e18;">*</span>
                        </label>
                        <select name="is_active" class="form-input" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <small class="form-hint">Only active rates are used</small>
                    </div>

                    <!-- Quick Calculator -->
                    <div class="form-group">
                        <label class="form-label">Quick Calculator</label>
                        <button type="button" class="btn-secondary" onclick="openCalculator()" style="width: 100%; padding: 8px; font-size: 13px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                <rect x="4" y="2" width="16" height="20" rx="2"/>
                                <line x1="8" y1="6" x2="16" y2="6"/>
                                <line x1="8" y1="10" x2="16" y2="10"/>
                                <line x1="8" y1="14" x2="16" y2="14"/>
                                <line x1="8" y1="18" x2="16" y2="18"/>
                            </svg>
                            Calculate Rate
                        </button>
                        <small class="form-hint">Helper to calculate daily rate</small>
                    </div>

                    <!-- Notes -->
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">
                            Notes / CSC Reference
                        </label>
                        <textarea name="notes" class="form-input" rows="3" placeholder="e.g., CSC MC No. 41, s. 1998 - Standard leave credits for government employees"></textarea>
                        <small class="form-hint">Reference to CSC memo or policy</small>
                    </div>
                </div>

                <!-- Calculation Example Box -->
                <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 12px; margin-top: 16px;">
                    <div style="display: flex; gap: 10px; align-items: start;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <div>
                            <p style="margin: 0 0 6px 0; color: #0369a1; font-size: 13px; font-weight: 600;">Calculation Example</p>
                            <p style="margin: 0; color: #075985; font-size: 12px; line-height: 1.5;" id="calculationExample">
                                If an employee works <strong>30 days</strong>, they earn: 30 × 0.0417 = <strong>1.25 credits</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAccrualRateModal()">Cancel</button>
                <button type="submit" class="btn-submit">Add Accrual Rate</button>
            </div>
        </form>
    </div>
</div>

<!-- Calculator Modal -->
<div class="modal" id="calculatorModal" onclick="closeCalculatorModal(event)">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">Accrual Rate Calculator</h2>
                <p class="modal-subtitle">Calculate daily or monthly accrual rate</p>
            </div>
            <button class="modal-close" onclick="closeCalculatorModal()">&times;</button>
        </div>

        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Annual Leave Days</label>
                <input type="number" id="calcAnnualDays" class="form-input" value="15" step="0.01" min="0">
                <small class="form-hint">Total days per year (e.g., 15 for VL/SL)</small>
            </div>

            <div class="form-group">
                <label class="form-label">Calculation Method</label>
                <select id="calcMethod" class="form-input" onchange="calculateRate()">
                    <option value="daily">Daily (÷ 360 working days)</option>
                    <option value="monthly">Monthly (÷ 12 months)</option>
                </select>
            </div>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 16px; margin-top: 16px;">
                <p style="margin: 0 0 8px 0; color: #15803d; font-size: 13px; font-weight: 600;">Calculated Rate:</p>
                <p style="margin: 0; color: #166534; font-size: 24px; font-weight: 700;" id="calculatedRate">0.0417</p>
                <p style="margin: 8px 0 0 0; color: #166534; font-size: 12px;" id="calculationFormula">15 ÷ 360 = 0.0417 credits per day</p>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeCalculatorModal()">Close</button>
            <button type="button" class="btn-submit" onclick="applyCalculatedRate()">Apply Rate</button>
        </div>
    </div>
</div>

<script>
function updateAccrualHint() {
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

function openCalculator() {
    document.getElementById('calculatorModal').classList.add('active');
    calculateRate();
}

function closeCalculatorModal(event) {
    if (!event || event.target.id === 'calculatorModal') {
        document.getElementById('calculatorModal').classList.remove('active');
    }
}

function calculateRate() {
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

function applyCalculatedRate() {
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

// Update calculation example when inputs change
document.addEventListener('DOMContentLoaded', function() {
    const creditsInput = document.querySelector('input[name="credits_earned_per_period"]');
    const daysInput = document.querySelector('input[name="days_of_service_required"]');
    
    if (creditsInput && daysInput) {
        creditsInput.addEventListener('input', updateCalculationExample);
        daysInput.addEventListener('input', updateCalculationExample);
    }
});

function updateCalculationExample() {
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
</script>
