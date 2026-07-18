<x-modal-container id="addManualCreditModal" close="closeManualCreditModal"
                     title="Add Manual Leave Credits" title-id="modalTitle"
                     subtitle="Manually adjust employee leave balance" subtitle-id="modalSubtitle">
    <form id="addManualCreditForm" action="{{ route('admin.leave.manual-credit.store') }}" method="POST">
        @csrf
        <input type="hidden" name="transaction_type" id="transactionType" value="add">

        <div class="form-row">
            <!-- Employee Selection -->
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Employee <span style="color: #8e1e18;">*</span></label>
                <select name="employee_id" class="form-input" required onchange="loadEmployeeLeaveTypes(this.value)">
                    <option value="">Select Employee</option>
                    @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}">
                            {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <!-- Leave Type Selection -->
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Leave Type <span style="color: #8e1e18;">*</span></label>
                <select name="leave_code" id="leaveTypeSelect" class="form-input" required onchange="showCurrentBalance(this.value)">
                    <option value="">Select Leave Type</option>
                </select>
            </div>
        </div>

        <!-- Current Balance Display -->
        <div id="currentBalanceDisplay" style="display: none; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 12px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #075985; font-size: 13px; font-weight: 600;">Current Balance:</span>
                <span id="currentBalanceValue" style="color: #0369a1; font-size: 16px; font-weight: 700;">0.00 days</span>
            </div>
        </div>

        <div class="form-row">
            <!-- Credit Amount -->
            <div class="form-group" style="flex: 1;">
                <label class="form-label" id="amountLabel">Credit Amount (Days) <span style="color: #8e1e18;">*</span></label>
                <input type="number" name="amount" class="form-input" step="0.000001" min="0.000001" placeholder="e.g., 5.125000 or 0.083333" required onchange="calculateNewBalance()">
                <p style="font-size: 11px; color: #56547a; margin: 4px 0 0 0;" id="amountHint">Number of days to add (up to 6 decimals, e.g., 0.125000 = 1 hour)</p>
            </div>

            <!-- Transaction Date -->
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Transaction Date <span style="color: #8e1e18;">*</span></label>
                <input type="date" name="transaction_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                <p style="font-size: 11px; color: #56547a; margin: 4px 0 0 0;">Date of adjustment</p>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Reason / Remarks <span style="color: #8e1e18;">*</span></label>
            <textarea name="remarks" class="form-input" rows="3" placeholder="e.g., Manual adjustment for service award, correction of previous error, etc." required></textarea>
            <p style="font-size: 11px; color: #56547a; margin: 4px 0 0 0;">Explain why this manual adjustment is being made</p>
        </div>

        <!-- Preview Box -->
        <div id="previewBox" style="display: none; border-radius: 6px; padding: 12px; margin-top: 16px;">
            <div style="display: flex; gap: 12px; align-items: start;">
                <svg id="previewIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <div style="flex: 1;">
                    <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: 600;" id="previewTitle">Preview</p>
                    <p style="margin: 0; font-size: 12px; line-height: 1.5;" id="previewText">
                        <span id="previewEmployee">Employee Name</span> will have
                        <strong id="previewAmount">0.00</strong> days <span id="previewAction">added to</span> their
                        <strong id="previewLeaveType">Leave Type</strong>.<br>
                        New balance: <strong id="previewNewBalance">0.00 days</strong>
                    </p>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="closeManualCreditModal()">Cancel</button>
            <button type="submit" class="btn-submit" id="submitBtn">Add Credits</button>
        </div>
    </form>
</x-modal-container>

@push('scripts')
    @vite('resources/js/leaveAndBenefits/add-manual-credit-modal.js')
@endpush
