<!-- Success Modal -->
<x-modal id="payrollSuccessModal" overlay-class="status-modal-overlay" container-class="status-modal-container" box-class="success">
    <div class="status-icon">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="9 12 11 14 15 10"/>
        </svg>
    </div>
    <h3 class="status-title">Payroll Generated Successfully!</h3>
    <p class="status-message" id="successMessage">
        Payroll has been successfully generated and saved to the database.
    </p>
    <div class="status-details" id="successDetails"></div>
    <div class="status-actions">
        <button type="button" class="btn-primary" onclick="closeSuccessModal()">
            Close
        </button>
        <button type="button" class="btn-secondary" onclick="viewPayrollRecords()">
            View Records
        </button>
    </div>
</x-modal>

<!-- Failed Modal -->
<x-modal id="payrollFailedModal" overlay-class="status-modal-overlay" container-class="status-modal-container" box-class="failed">
    <div class="status-icon">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="15" y1="9" x2="9" y2="15"/>
            <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
    </div>
    <h3 class="status-title">Payroll Generation Failed</h3>
    <p class="status-message" id="failedMessage">
        An error occurred while generating the payroll.
    </p>
    <div class="error-details" id="errorDetails"></div>
    <div class="status-actions">
        <button type="button" class="btn-primary" onclick="closeFailedModal()">
            Close
        </button>
        <button type="button" class="btn-secondary" onclick="retryPayroll()">
            Try Again
        </button>
    </div>
</x-modal>

@push('scripts')
    @vite('resources/js/admin/payroll/payroll-status-modals.js')
@endpush
