<div class="modal" id="addManualCreditModal" onclick="closeManualCreditModal(event)">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="modalTitle">Add Manual Leave Credits</h2>
                <p class="modal-subtitle" id="modalSubtitle">Manually adjust employee leave balance</p>
            </div>
            <button class="modal-close" onclick="closeManualCreditModal()">&times;</button>
        </div>

        <form id="addManualCreditForm" method="POST" action="{{ route('admin.leave.manual-credit.store') }}">
            @csrf
            <input type="hidden" name="transaction_type" id="transactionType" value="add">
            <div class="modal-body">
                <div class="form-grid">
                    <!-- Employee Selection -->
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">
                            Employee <span style="color: #8e1e18;">*</span>
                        </label>
                        <select name="employee_id" class="form-input" required onchange="loadEmployeeLeaveTypes(this.value)">
                            <option value="">Select Employee</option>
                            @foreach($employees ?? [] as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-hint">Select the employee to adjust credits for</small>
                    </div>

                    <!-- Leave Type Selection -->
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">
                            Leave Type <span style="color: #8e1e18;">*</span>
                        </label>
                        <select name="leave_code" id="leaveTypeSelect" class="form-input" required onchange="showCurrentBalance(this.value)">
                            <option value="">Select Leave Type</option>
                        </select>
                        <small class="form-hint">Choose which leave type to adjust</small>
                    </div>

                    <!-- Current Balance Display -->
                    <div id="currentBalanceDisplay" style="grid-column: span 2; display: none; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 12px; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #075985; font-size: 13px; font-weight: 600;">Current Balance:</span>
                            <span id="currentBalanceValue" style="color: #0369a1; font-size: 16px; font-weight: 700;">0.00 days</span>
                        </div>
                    </div>

                    <!-- Credit Amount -->
                    <div class="form-group">
                        <label class="form-label" id="amountLabel">
                            Credit Amount (Days) <span style="color: #8e1e18;">*</span>
                        </label>
                        <input type="number" name="amount" class="form-input" step="0.01" min="0.01" placeholder="e.g., 5.00" required onchange="calculateNewBalance()">
                        <small class="form-hint" id="amountHint">Number of days to add</small>
                    </div>

                    <!-- Transaction Date -->
                    <div class="form-group">
                        <label class="form-label">
                            Transaction Date <span style="color: #8e1e18;">*</span>
                        </label>
                        <input type="date" name="transaction_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                        <small class="form-hint">Date of adjustment</small>
                    </div>

                    <!-- Reason/Remarks -->
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">
                            Reason / Remarks <span style="color: #8e1e18;">*</span>
                        </label>
                        <textarea name="remarks" class="form-input" rows="3" placeholder="e.g., Manual adjustment for service award, correction of previous error, etc." required></textarea>
                        <small class="form-hint">Explain why this manual adjustment is being made</small>
                    </div>
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
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeManualCreditModal()">Cancel</button>
                <button type="submit" class="btn-submit" id="submitBtn">Add Credits</button>
            </div>
        </form>
    </div>
</div>

<script>
let employeeLeaveBalances = {};
let currentTransactionType = 'add';

function loadEmployeeLeaveTypes(employeeId) {
    if (!employeeId) {
        document.getElementById('leaveTypeSelect').innerHTML = '<option value="">Select Leave Type</option>';
        document.getElementById('currentBalanceDisplay').style.display = 'none';
        document.getElementById('previewBox').style.display = 'none';
        return;
    }

    // Fetch employee's leave balances
    fetch(`/admin/leave/employee/${employeeId}/balances`)
        .then(response => response.json())
        .then(data => {
            employeeLeaveBalances = data.balances;
            const select = document.getElementById('leaveTypeSelect');
            select.innerHTML = '<option value="">Select Leave Type</option>';
            
            data.leaveTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.leave_code;
                option.textContent = `${type.leave_code} - ${type.leave_name}`;
                select.appendChild(option);
            });

            // Update preview employee name
            const employeeSelect = document.querySelector('select[name="employee_id"]');
            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            document.getElementById('previewEmployee').textContent = selectedOption.text.split(' - ')[1];
        })
        .catch(error => {
            console.error('Error loading leave types:', error);
            alert('Failed to load leave types for this employee');
        });
}

function showCurrentBalance(leaveCode) {
    if (!leaveCode || !employeeLeaveBalances[leaveCode]) {
        document.getElementById('currentBalanceDisplay').style.display = 'none';
        return;
    }

    const balance = employeeLeaveBalances[leaveCode];
    document.getElementById('currentBalanceValue').textContent = `${parseFloat(balance).toFixed(2)} days`;
    document.getElementById('currentBalanceDisplay').style.display = 'block';
    
    calculateNewBalance();
}

function calculateNewBalance() {
    const leaveCode = document.querySelector('select[name="leave_code"]').value;
    const amount = parseFloat(document.querySelector('input[name="amount"]').value) || 0;
    
    if (!leaveCode || amount <= 0) {
        document.getElementById('previewBox').style.display = 'none';
        return;
    }

    const currentBalance = parseFloat(employeeLeaveBalances[leaveCode] || 0);
    const newBalance = currentTransactionType === 'add' 
        ? currentBalance + amount 
        : currentBalance - amount;

    // Check if deduction would result in negative balance
    if (currentTransactionType === 'deduct' && newBalance < 0) {
        document.getElementById('previewBox').style.background = '#fef2f2';
        document.getElementById('previewBox').style.border = '1px solid #fecaca';
        document.getElementById('previewIcon').setAttribute('stroke', '#dc2626');
        document.getElementById('previewTitle').style.color = '#dc2626';
        document.getElementById('previewText').style.color = '#991b1b';
        document.getElementById('previewText').innerHTML = `
            <strong>⚠️ Warning:</strong> This deduction will result in a negative balance of <strong>${newBalance.toFixed(2)} days</strong>. 
            Current balance is only <strong>${currentBalance.toFixed(2)} days</strong>.
        `;
        document.getElementById('previewBox').style.display = 'block';
        return;
    }

    const leaveTypeSelect = document.querySelector('select[name="leave_code"]');
    const leaveTypeName = leaveTypeSelect.options[leaveTypeSelect.selectedIndex].text;

    // Set colors based on transaction type
    if (currentTransactionType === 'add') {
        document.getElementById('previewBox').style.background = '#f0fdf4';
        document.getElementById('previewBox').style.border = '1px solid #bbf7d0';
        document.getElementById('previewIcon').setAttribute('stroke', '#15803d');
        document.getElementById('previewTitle').style.color = '#15803d';
        document.getElementById('previewText').style.color = '#166534';
    } else {
        document.getElementById('previewBox').style.background = '#fef3c7';
        document.getElementById('previewBox').style.border = '1px solid #fde68a';
        document.getElementById('previewIcon').setAttribute('stroke', '#d97706');
        document.getElementById('previewTitle').style.color = '#d97706';
        document.getElementById('previewText').style.color = '#92400e';
    }

    document.getElementById('previewAmount').textContent = amount.toFixed(2);
    document.getElementById('previewAction').textContent = currentTransactionType === 'add' ? 'added to' : 'deducted from';
    document.getElementById('previewLeaveType').textContent = leaveTypeName;
    document.getElementById('previewNewBalance').textContent = newBalance.toFixed(2);
    document.getElementById('previewBox').style.display = 'block';
}

window.openManualCreditModal = function(type = 'add') {
    currentTransactionType = type;
    document.getElementById('transactionType').value = type;
    
    const form = document.getElementById('addManualCreditForm');
    form.reset();
    document.getElementById('currentBalanceDisplay').style.display = 'none';
    document.getElementById('previewBox').style.display = 'none';
    document.getElementById('leaveTypeSelect').innerHTML = '<option value="">Select Leave Type</option>';
    employeeLeaveBalances = {};
    
    // Update modal based on type
    if (type === 'add') {
        document.getElementById('modalTitle').textContent = 'Add Manual Leave Credits';
        document.getElementById('modalSubtitle').textContent = 'Manually add credits to employee leave balance';
        document.getElementById('amountLabel').innerHTML = 'Credit Amount (Days) <span style="color: #8e1e18;">*</span>';
        document.getElementById('amountHint').textContent = 'Number of days to add';
        document.getElementById('submitBtn').textContent = 'Add Credits';
        document.getElementById('submitBtn').style.background = '#0b044d';
        document.getElementById('previewTitle').textContent = 'Preview - Adding Credits';
    } else {
        document.getElementById('modalTitle').textContent = 'Deduct Leave Credits';
        document.getElementById('modalSubtitle').textContent = 'Manually deduct credits from employee leave balance';
        document.getElementById('amountLabel').innerHTML = 'Deduction Amount (Days) <span style="color: #8e1e18;">*</span>';
        document.getElementById('amountHint').textContent = 'Number of days to deduct';
        document.getElementById('submitBtn').textContent = 'Deduct Credits';
        document.getElementById('submitBtn').style.background = '#8e1e18';
        document.getElementById('previewTitle').textContent = 'Preview - Deducting Credits';
    }
    
    document.getElementById('addManualCreditModal').classList.add('active');
}

window.closeManualCreditModal = function(event) {
    if (!event || event.target.id === 'addManualCreditModal') {
        document.getElementById('addManualCreditModal').classList.remove('active');
    }
}

// Update preview when amount changes
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput) {
        amountInput.addEventListener('input', calculateNewBalance);
    }
});
</script>
