<div id="addLoanModal" class="modal-overlay" onclick="closeAddLoanModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Add Employee Loan</h3>
                <p class="modal-subtitle">Create a new loan record with automatic balance tracking</p>
            </div>
            <button class="modal-close" onclick="closeAddLoanModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="addLoanForm" action="{{ route('admin.deductions.employee.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Employee <span style="color: #8e1e18;">*</span></label>
                    <select name="employee_id" class="form-input" required>
                        <option value="">Select Employee</option>
                        @foreach(\App\Models\Employee::with('employmentDetail.departmentRelation')->orderBy('last_name')->get() as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->last_name }}, {{ $emp->first_name }} 
                                @if($emp->employmentDetail && $emp->employmentDetail->departmentRelation)
                                    - {{ $emp->employmentDetail->departmentRelation->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Loan Type <span style="color: #8e1e18;">*</span></label>
                        <select name="deduction_type_id" id="loanProvider" class="form-input" required onchange="handleLoanTypeChange()">
                            <option value="">Select Loan Type</option>
                            <optgroup label="GSIS Loans">
                                @foreach(\App\Models\DeductionType::where('category', 'LOAN')->where('is_active', true)->where('code', 'LIKE', 'LOAN_GSIS%')->orderBy('name')->get() as $type)
                                    <option value="{{ $type->id }}" data-provider="GSIS">{{ str_replace('GSIS ', '', $type->name) }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Pag-IBIG Loans">
                                @foreach(\App\Models\DeductionType::where('category', 'LOAN')->where('is_active', true)->where('code', 'LIKE', 'LOAN_PAGIBIG%')->orderBy('name')->get() as $type)
                                    <option value="{{ $type->id }}" data-provider="PAG-IBIG">{{ str_replace('Pag-IBIG ', '', $type->name) }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Other Loans">
                                @foreach(\App\Models\DeductionType::where('category', 'LOAN')->where('is_active', true)->where('code', 'NOT LIKE', 'LOAN_GSIS%')->where('code', 'NOT LIKE', 'LOAN_PAGIBIG%')->orderBy('name')->get() as $type)
                                    <option value="{{ $type->id }}" data-provider="OTHER">{{ $type->name }}</option>
                                @endforeach
                            </optgroup>
                            <option value="OTHER" data-provider="CUSTOM">Other (External Provider)</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;" id="loanProviderDisplay">
                        <label class="form-label">Provider</label>
                        <input type="text" id="providerName" class="form-input" readonly placeholder="Select loan type first" style="background: #f7f6ff; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-row" id="otherProviderFields" style="display: none;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Provider Name <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="other_provider_name" id="otherProviderName" class="form-input" placeholder="e.g., SSS, Private Bank, Cooperative">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Loan Description <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="other_loan_type" id="otherLoanType" class="form-input" placeholder="e.g., Personal Loan, Emergency Loan">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Total Loan Amount <span style="color: #8e1e18;">*</span></label>
                        <input type="number" name="total_amount" id="loanTotalAmount" class="form-input" placeholder="e.g., 50000.00" step="0.01" min="0" required onchange="calculateLoanInstallment()">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Monthly Installment <span style="color: #8e1e18;">*</span></label>
                        <input type="number" name="installment_amount" id="loanInstallment" class="form-input" placeholder="e.g., 2500.00" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Start Date <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="start_date" id="loanStartDate" class="form-input" required value="{{ date('Y-m-d') }}" onchange="calculateLoanInstallment()">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">End Date <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="end_date" id="loanEndDate" class="form-input" required onchange="calculateLoanInstallment()">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span style="color: #8e1e18;">*</span></label>
                    <select name="status" class="form-input" required>
                        <option value="ACTIVE">Active</option>
                        <option value="SUSPENDED">Suspended</option>
                        <option value="COMPLETED">Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-input" rows="2" placeholder="Additional notes or remarks..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddLoanModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Add Loan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(11, 4, 77, 0.4);
    backdrop-filter: blur(4px);
    z-index: 9999;
    animation: fadeIn 0.2s ease;
}

.modal-overlay.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-container {
    background: #fff;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(11, 4, 77, 0.3);
    animation: slideUp 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px;
    border-bottom: 1px solid #f0effe;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #0b044d;
    margin: 0 0 4px 0;
}

.modal-subtitle {
    font-size: 12px;
    color: #6b6a8a;
    margin: 0;
}

.modal-close {
    background: transparent;
    border: none;
    color: #6b6a8a;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f7f6ff;
    color: #0b044d;
}

.modal-body {
    padding: 24px;
    max-height: calc(90vh - 140px);
    overflow-y: auto;
}

.form-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: #0b044d;
    margin-bottom: 6px;
}

.form-input {
    padding: 10px 12px;
    border: 1px solid #e5e3f8;
    border-radius: 6px;
    font-size: 13px;
    color: #0b044d;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #0b044d;
    box-shadow: 0 0 0 3px rgba(11, 4, 77, 0.1);
}

.form-input::placeholder {
    color: #b3b1c8;
}

textarea.form-input {
    resize: vertical;
    min-height: 60px;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #f0effe;
}

.btn-cancel {
    padding: 10px 20px;
    border: 1px solid #e5e3f8;
    background: #fff;
    color: #6b6a8a;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.btn-cancel:hover {
    background: #f7f6ff;
    border-color: #0b044d;
    color: #0b044d;
}

.btn-submit {
    padding: 10px 20px;
    border: none;
    background: #0b044d;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.btn-submit:hover {
    background: #1a0f6e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(11, 4, 77, 0.3);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
}
</style>

<script>
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
    
    // Reset other provider fields
    otherProviderFields.style.display = 'none';
    otherProviderName.removeAttribute('required');
    otherLoanType.removeAttribute('required');
    
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
</script>
