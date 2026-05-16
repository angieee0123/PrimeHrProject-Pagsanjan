<div class="table-header">
    <div>
        <h3 class="table-title">Generate Payroll</h3>
        <p class="table-sub">Configure and process payroll for selected period and employees</p>
    </div>
</div>

<div class="generate-payroll-container">
    <div class="generate-form-card">
        <form method="POST" action="{{ route('admin.payroll.generate') }}" id="generatePayrollForm" onsubmit="return handleGeneratePayroll(event);">
            @csrf
            
            <div class="form-section">
                <h4 class="section-title">Payroll Period</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-input" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Pay Date</label>
                        <input type="date" name="pay_date" class="form-input" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Payroll Type</label>
                        <select name="payroll_type" class="form-input" required>
                            <option value="regular">Regular Payroll</option>
                            <option value="13th_month">13th Month Pay</option>
                            <option value="bonus">Bonus</option>
                            <option value="special">Special Payroll</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="section-title">Employee Selection</h4>
                <div class="form-group">
                    <label>Department</label>
                    <select name="department" class="form-input" id="deptFilter">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Employment Status</label>
                    <select name="employment_status" class="form-input">
                        <option value="">All Status</option>
                        <option value="Permanent">Permanent</option>
                        <option value="Job Order">Job Order</option>
                        <option value="Casual">Casual</option>
                        <option value="Contractual">Contractual</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h4 class="section-title">Payroll Options</h4>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="include_deductions" checked>
                        <span>Include Deductions (SSS, PhilHealth, Pag-IBIG, Tax)</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="include_loans" checked>
                        <span>Include Loan Deductions</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="include_overtime">
                        <span>Include Overtime Pay</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="auto_approve">
                        <span>Auto-approve after generation</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('generatePayrollForm').reset(); updatePreview();">
                    Reset
                </button>
                <button type="submit" class="btn-primary" id="generateBtn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Generate Payroll
                </button>
            </div>
        </form>
    </div>

    <div class="preview-card">
        <h4 class="preview-title">Preview Summary</h4>
        <div class="preview-stats">
            <div class="preview-stat">
                <span class="preview-label">Employees</span>
                <strong class="preview-value" id="previewEmployees">0</strong>
            </div>
            <div class="preview-stat">
                <span class="preview-label">Estimated Gross</span>
                <strong class="preview-value" id="previewGross">{{ peso(0) }}</strong>
            </div>
            <div class="preview-stat">
                <span class="preview-label">Estimated Deductions</span>
                <strong class="preview-value deduction" id="previewDeductions">{{ peso(0) }}</strong>
            </div>
            <div class="preview-stat">
                <span class="preview-label">Estimated Net Pay</span>
                <strong class="preview-value net-pay" id="previewNet">{{ peso(0) }}</strong>
            </div>
        </div>
        <p class="preview-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Preview will update based on your selections
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('generatePayrollForm');
    const startDateInput = form.querySelector('[name="start_date"]');
    const endDateInput = form.querySelector('[name="end_date"]');
    const deptFilter = form.querySelector('[name="department"]');
    const empStatusFilter = form.querySelector('[name="employment_status"]');

    function updatePreview() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const department = deptFilter.value;
        const employmentStatus = empStatusFilter.value;

        if (!startDate || !endDate) return;

        // Fetch preview data
        fetch(`{{ route('admin.payroll.preview') }}?start_date=${startDate}&end_date=${endDate}&department=${department}&employment_status=${employmentStatus}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('previewEmployees').textContent = data.employee_count;
                document.getElementById('previewGross').textContent = '₱' + parseFloat(data.estimated_gross).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('previewDeductions').textContent = '₱' + parseFloat(data.estimated_deductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('previewNet').textContent = '₱' + parseFloat(data.estimated_net).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            })
            .catch(error => console.error('Error fetching preview:', error));
    }

    // Add event listeners
    startDateInput.addEventListener('change', updatePreview);
    endDateInput.addEventListener('change', updatePreview);
    deptFilter.addEventListener('change', updatePreview);
    empStatusFilter.addEventListener('change', updatePreview);

    // Initial load
    updatePreview();
});

function handleGeneratePayroll(event) {
    event.preventDefault();
    
    const form = document.getElementById('generatePayrollForm');
    const formData = new FormData(form);
    const generateBtn = document.getElementById('generateBtn');
    
    // Disable button and show loading
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg> Generating...';
    
    // Fetch payroll data
    fetch('{{ route("admin.payroll.calculate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPayrollModal(data.data);
        } else {
            alert('Error: ' + (data.message || 'Failed to generate payroll'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate payroll. Please try again.');
    })
    .finally(() => {
        // Re-enable button
        generateBtn.disabled = false;
        generateBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Generate Payroll';
    });
    
    return false;
}
</script>

@include('admin.payroll.modals.payroll-result-modal')

<style>
.generate-payroll-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
    margin-top: 20px;
}

.generate-form-card, .preview-card {
    background: #fff;
    border: 1px solid #e8e7f5;
    border-radius: 8px;
    padding: 24px;
}

.form-section {
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #f0effe;
}

.form-section:last-of-type {
    border-bottom: none;
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #0b044d;
    margin-bottom: 16px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #6b6a8a;
    margin-bottom: 6px;
}

.form-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
}

.form-input:focus {
    outline: none;
    border-color: #0b044d;
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b6a8a;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.btn-secondary {
    padding: 10px 20px;
    background: #f7f6ff;
    color: #0b044d;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
}

.btn-primary {
    padding: 10px 20px;
    background: #0b044d;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-primary:hover {
    background: #1a0f6e;
}

.preview-card {
    height: fit-content;
    position: sticky;
    top: 20px;
}

.preview-title {
    font-size: 14px;
    font-weight: 600;
    color: #0b044d;
    margin-bottom: 16px;
}

.preview-stats {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 16px;
}

.preview-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #fafafe;
    border-radius: 6px;
}

.preview-label {
    font-size: 12px;
    color: #9999bb;
    font-weight: 500;
}

.preview-value {
    font-size: 14px;
    color: #0b044d;
    font-weight: 600;
}

.preview-note {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #9999bb;
    padding: 12px;
    background: #f7f6ff;
    border-radius: 6px;
}

.preview-note svg {
    flex-shrink: 0;
}
</style>
