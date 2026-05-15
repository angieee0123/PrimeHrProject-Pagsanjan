<div id="assignDeductionModal" class="modal-overlay" onclick="closeAssignDeductionModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Assign Deductions</h3>
                <p class="modal-subtitle">Assign multiple deduction types to an employee</p>
            </div>
            <button class="modal-close" onclick="closeAssignDeductionModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="assignDeductionForm" action="{{ route('admin.deductions.employee.bulk-assign') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Employee <span style="color: #8e1e18;">*</span></label>
                    <select name="employee_id" id="assignEmployee" class="form-input" required onchange="checkExistingDeductions()">
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

                <div id="existingDeductionsWarning" class="warning-box" style="display: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="min-width: 16px;">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <strong>Existing Deductions:</strong>
                        <p id="existingDeductionsList" style="margin: 4px 0 0 0; font-size: 12px;"></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Deduction Types <span style="color: #8e1e18;">*</span>
                        <span id="selectedCount" style="color: #6b6a8a; font-weight: 400; margin-left: 8px;">(0 selected)</span>
                    </label>
                    <div class="checkbox-actions">
                        <button type="button" class="btn-link" onclick="selectAllDeductions()">Select All</button>
                        <button type="button" class="btn-link" onclick="deselectAllDeductions()">Deselect All</button>
                        <button type="button" class="btn-link" onclick="selectMandatoryOnly()">Mandatory Only</button>
                    </div>
                    <div class="checkbox-group">
                        @php
                            $deductionTypes = \App\Models\DeductionType::where('is_active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category');
                        @endphp
                        @foreach($deductionTypes as $category => $types)
                            <div class="checkbox-category">
                                <p class="category-label">{{ $category }}</p>
                                @foreach($types as $type)
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="deduction_types[]" value="{{ $type->id }}" 
                                               data-category="{{ $type->category }}" 
                                               data-computation="{{ $type->computation_type }}"
                                               data-code="{{ $type->code }}"
                                               onchange="handleCheckboxChange()">
                                        <span class="checkbox-text">
                                            {{ $type->name }} 
                                            <span style="color: #9999bb; font-size: 11px;">({{ $type->code }})</span>
                                            @if($type->computation_type === 'PERCENTAGE')
                                                <span style="color: #6b6a8a; font-size: 11px;"> - {{ $type->percentage_rate }}%</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Start Date <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-input" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-input">
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
                    <button type="button" class="btn-cancel" onclick="closeAssignDeductionModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Assign Deductions</button>
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

.checkbox-group {
    border: 1px solid #e5e3f8;
    border-radius: 6px;
    padding: 12px;
    max-height: 280px;
    overflow-y: auto;
}

.checkbox-category {
    margin-bottom: 16px;
}

.checkbox-category:last-child {
    margin-bottom: 0;
}

.category-label {
    font-size: 11px;
    font-weight: 700;
    color: #0b044d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 8px 0;
    padding-bottom: 6px;
    border-bottom: 1px solid #f0effe;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s;
    margin-bottom: 4px;
}

.checkbox-label:hover {
    background: #f7f6ff;
}

.checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #0b044d;
}

.checkbox-text {
    font-size: 13px;
    color: #0b044d;
    user-select: none;
}

.warning-box {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: #fff8e1;
    border: 1px solid #ffd54f;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 13px;
    color: #6b6a8a;
}

.warning-box svg {
    color: #f9a825;
}

.checkbox-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 8px;
}

.btn-link {
    background: none;
    border: none;
    color: #0b044d;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.btn-link:hover {
    background: #f7f6ff;
}
</style>

<script>
function openAssignDeductionModal() {
    document.getElementById('assignDeductionModal').classList.add('active');
    setTimeout(() => handleCheckboxChange(), 100);
}

function closeAssignDeductionModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('assignDeductionModal').classList.remove('active');
    document.getElementById('assignDeductionForm').reset();
    document.getElementById('existingDeductionsWarning').style.display = 'none';
    deselectAllDeductions();
}

function handleCheckboxChange() {
    const checkboxes = document.querySelectorAll('input[name="deduction_types[]"]:checked');
    const submitBtn = document.querySelector('#assignDeductionForm .btn-submit');
    const selectedCount = document.getElementById('selectedCount');
    
    // Update selected count
    selectedCount.textContent = `(${checkboxes.length} selected)`;
    
    // Enable/disable submit button based on selection
    if (checkboxes.length > 0) {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    } else {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    }
}

function selectAllDeductions() {
    const checkboxes = document.querySelectorAll('input[name="deduction_types[]"]:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = true);
    handleCheckboxChange();
}

function deselectAllDeductions() {
    const checkboxes = document.querySelectorAll('input[name="deduction_types[]"]');
    checkboxes.forEach(cb => cb.checked = false);
    handleCheckboxChange();
}

function selectMandatoryOnly() {
    deselectAllDeductions();
    const checkboxes = document.querySelectorAll('input[name="deduction_types[]"]');
    checkboxes.forEach(cb => {
        if (cb.dataset.category === 'MANDATORY') {
            cb.checked = true;
        }
    });
    handleCheckboxChange();
}

function checkExistingDeductions() {
    const employeeId = document.getElementById('assignEmployee').value;
    const warningBox = document.getElementById('existingDeductionsWarning');
    const warningList = document.getElementById('existingDeductionsList');
    
    if (!employeeId) {
        warningBox.style.display = 'none';
        // Enable all checkboxes
        document.querySelectorAll('input[name="deduction_types[]"]').forEach(cb => {
            cb.disabled = false;
            cb.parentElement.style.opacity = '1';
        });
        return;
    }
    
    // Fetch existing deductions via AJAX
    fetch(`/admin/deductions/employee/${employeeId}/active`)
        .then(response => response.json())
        .then(data => {
            if (data.deductions && data.deductions.length > 0) {
                // Show warning
                warningBox.style.display = 'flex';
                const deductionNames = data.deductions.map(d => d.name).join(', ');
                warningList.textContent = `This employee already has: ${deductionNames}`;
                
                // Disable checkboxes for existing deductions
                document.querySelectorAll('input[name="deduction_types[]"]').forEach(cb => {
                    const deductionTypeId = parseInt(cb.value);
                    const hasDeduction = data.deductions.some(d => d.id === deductionTypeId);
                    
                    if (hasDeduction) {
                        cb.disabled = true;
                        cb.checked = false;
                        cb.parentElement.style.opacity = '0.5';
                        cb.parentElement.title = 'Already assigned to this employee';
                    } else {
                        cb.disabled = false;
                        cb.parentElement.style.opacity = '1';
                        cb.parentElement.title = '';
                    }
                });
            } else {
                warningBox.style.display = 'none';
                // Enable all checkboxes
                document.querySelectorAll('input[name="deduction_types[]"]').forEach(cb => {
                    cb.disabled = false;
                    cb.parentElement.style.opacity = '1';
                    cb.parentElement.title = '';
                });
            }
            handleCheckboxChange();
        })
        .catch(error => {
            console.error('Error fetching existing deductions:', error);
            warningBox.style.display = 'none';
        });
}

// Initialize button state on modal open
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('assignDeductionModal');
    if (modal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class' && modal.classList.contains('active')) {
                    handleCheckboxChange();
                }
            });
        });
        observer.observe(modal, { attributes: true });
    }
});
</script>
