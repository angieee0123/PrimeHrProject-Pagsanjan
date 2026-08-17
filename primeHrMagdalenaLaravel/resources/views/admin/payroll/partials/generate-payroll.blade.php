<div class="table-header">
    <div>
        <h3 class="table-title">Generate Payroll</h3>
        <p class="table-sub">Configure and process payroll for selected period and employees</p>
    </div>
</div>

<div class="info-banner">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <div>
        <strong>How it works:</strong> This will process attendance records and create payslip computations for the selected period. 
        Once generated, payslips will be visible to employees in their accounts.
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
                    <label>Employment Type</label>
                    <select name="employment_status" class="form-input">
                        <option value="">All Employment Types</option>
                        @foreach($employmentStatuses as $empStatus)
                            <option value="{{ $empStatus }}">{{ $empStatus }}</option>
                        @endforeach
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

            {{-- The same pill pair the table headers use ("Add Deduction Type",
                 "File Travel Order"): .btn-export supplies the full-round shape,
                 .adm-btn-primary-solid takes over the colours, so the two differ
                 by weight rather than by silhouette. Reusing those classes rather
                 than restyling .btn-primary/.btn-secondary, which the payroll
                 modals share. --}}
            <div class="form-actions">
                <button type="button" class="btn-export" onclick="document.getElementById('generatePayrollForm').reset(); updatePreview();">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L3 8"/></svg>
                    Reset
                </button>
                <button type="submit" class="btn-export adm-btn-primary-solid" id="generateBtn">
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

@push('scripts')
    @vite('resources/js/admin/payroll/generate-payroll.js')
@endpush
