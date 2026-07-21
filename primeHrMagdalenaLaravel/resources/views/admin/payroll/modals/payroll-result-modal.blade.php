<x-modal id="payrollResultModal" container-class="modal-container" box-style="max-width: 95%; width: 1400px;" close="closePayrollModal" title="Generated Payroll Summary">
    <x-slot:closeIcon>&times;</x-slot:closeIcon>

    <div class="modal-body pr-modal-body-scroll">
        <div class="payroll-info-bar">
            <div class="info-item">
                <span class="info-label">Period:</span>
                <strong id="modalPeriod">-</strong>
            </div>
            <div class="info-item">
                <span class="info-label">Pay Date:</span>
                <strong id="modalPayDate">-</strong>
            </div>
            <div class="info-item">
                <span class="info-label">Payroll Type:</span>
                <strong id="modalPayrollType">-</strong>
            </div>
            <div class="info-item">
                <span class="info-label">Total Employees:</span>
                <strong id="modalEmployeeCount">0</strong>
            </div>
        </div>

        <div class="table-wrapper pr-table-mt20">
            <table class="payroll-summary-table">
                <thead>
                    <tr>
                        <th rowspan="2">No.</th>
                        <th rowspan="2">Employee Name</th>
                        <th rowspan="2">Position</th>
                        <th rowspan="2">Department</th>
                        <th rowspan="2">Days Worked</th>
                        <th rowspan="2">Daily Rate</th>
                        <th colspan="2">Earnings</th>
                        <th colspan="4">Deductions</th>
                        <th rowspan="2">Total Deductions</th>
                        <th rowspan="2">Net Pay</th>
                    </tr>
                    <tr>
                        <th>Basic Pay</th>
                        <th>OT Pay</th>
                        <th>Late</th>
                        <th>Undertime</th>
                        <th>SSS/GSIS</th>
                        <th>Loans</th>
                    </tr>
                </thead>
                <tbody id="payrollTableBody">
                    <tr>
                        <td colspan="14" class="pr-empty-cell">
                            Loading payroll data...
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6" class="pr-total-label">TOTAL:</td>
                        <td id="totalBasicPay">₱0.00</td>
                        <td id="totalOtPay">₱0.00</td>
                        <td id="totalLate">₱0.00</td>
                        <td id="totalUndertime">₱0.00</td>
                        <td id="totalMandatory">₱0.00</td>
                        <td id="totalLoans">₱0.00</td>
                        <td id="totalDeductions">₱0.00</td>
                        <td id="totalNetPay">₱0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closePayrollModal()">Close</button>
        <button type="button" class="btn-export-excel" onclick="exportToExcel()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export to Excel
        </button>
        <button type="button" class="btn-primary" onclick="confirmPayroll()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Confirm & Save
        </button>
    </div>
</x-modal>
<!-- Confirm Payroll Modal -->
<div id="confirmPayrollModal" class="adm-overlay" onclick="closeConfirmPayrollModal()">
    <div class="adm-box pr-modal-md" onclick="event.stopPropagation()">
        <div class="adm-header pr-header-gold">
            <div class="adm-header-left">
                <div class="vdm-avatar pr-header-icon-frost">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div>
                    <span class="adm-eyebrow pr-eyebrow-frost">CONFIRMATION REQUIRED</span>
                    <h3 class="adm-title pr-title-white">Save Payroll</h3>
                </div>
            </div>
            <button class="adm-close pr-close-frost" onclick="closeConfirmPayrollModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="vdm-body pr-body-pad32">
            <div class="pr-confirm-body">
                <div class="pr-confirm-icon-wrap">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <h4 class="pr-confirm-title">Are you sure you want to save this payroll?</h4>
                <p class="pr-confirm-text">This will create salary computation records for all employees in the selected period. This action cannot be undone.</p>
            </div>
            <div class="pr-confirm-note-box">
                <div class="pr-confirm-note-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" class="pr-confirm-note-icon">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <div>
                        <p class="pr-confirm-note-label">What happens next:</p>
                        <ul class="pr-confirm-note-list">
                            <li>Payroll records will be created for all employees</li>
                            <li>Payslips will be generated and visible to employees</li>
                            <li>You can still edit individual records if needed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="adm-footer pr-footer-pad">
            <button class="adm-btn-ghost" onclick="closeConfirmPayrollModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Cancel
            </button>
            <button class="adm-btn-primary" onclick="proceedSavePayroll()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Yes, Save Payroll
            </button>
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/payroll/payroll-result-modal.js')
@endpush
