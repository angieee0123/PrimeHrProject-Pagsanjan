<x-modal-container id="addAccrualRateModal" close="closeAccrualRateModal" overlay-class="modal" container-class="modal-content" max-width="700px"
                     title="Add Accrual Rate" subtitle="Configure leave credit earning rate">
    <x-slot:closeIcon>&times;</x-slot:closeIcon>

    <form id="addAccrualRateForm" method="POST" action="/admin/leave/accrual-rates">
        @csrf
        <div class="form-grid">
            <!-- Leave Type Selection -->
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">
                    Leave Type <span style="color: #8e1e18;">*</span>
                </label>
                <select name="leave_type_id" class="form-input" required>
                    <option value="">Select Leave Type</option>
                    @if(isset($accruedLeaveTypes) && $accruedLeaveTypes->count() > 0)
                        @foreach($accruedLeaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}">{{ $leaveType->leave_code }} - {{ $leaveType->leave_name }}</option>
                        @endforeach
                    @else
                        <option value="" disabled>No accrued leave types available</option>
                    @endif
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

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeAccrualRateModal()">Cancel</button>
            <button type="submit" class="btn-submit">Add Accrual Rate</button>
        </div>
    </form>
</x-modal-container>

<!-- Calculator Modal -->
<x-modal-container id="calculatorModal" close="closeCalculatorModal" overlay-class="modal" container-class="modal-content" max-width="500px"
                     title="Accrual Rate Calculator" subtitle="Calculate daily or monthly accrual rate">
    <x-slot:closeIcon>&times;</x-slot:closeIcon>

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

    <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeCalculatorModal()">Close</button>
        <button type="button" class="btn-submit" onclick="applyCalculatedRate()">Apply Rate</button>
    </div>
</x-modal-container>

@push('scripts')
    @vite('resources/js/leaveAndBenefits/add-accrual-rate-modal.js')
@endpush
