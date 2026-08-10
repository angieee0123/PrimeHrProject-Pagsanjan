{{-- Payslip Detail Modal — no close prop passed to the modal component: the original
     overlay has no click-outside-to-close, only the explicit header/footer close
     buttons, and the component's close prop would add both together. --}}
<x-modal id="payslipDetailModal" container-class="modal-container" max-width="800px">
        <div class="modal-header">
            <h3 class="modal-title">Payslip Details</h3>
            <button type="button" class="modal-close" onclick="closePayslipDetailModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Employee Info -->
            <div class="payslip-header">
                <div class="payslip-logo">
                    <img src="{{ \App\Services\SiteContentService::logoUrl() }}" alt="Pagsanjan Logo" class="logo-image">
                    <h2>MUNICIPAL GOVERNMENT OF PAGSANJAN</h2>
                    <p>Province of Laguna</p>
                    <h3 class="payslip-title">PAYSLIP</h3>
                </div>
            </div>

            <div class="payslip-info-grid">
                <div class="info-group">
                    <label>Employee Name:</label>
                    <strong id="modalEmployeeName">-</strong>
                </div>
                <div class="info-group">
                    <label>Employee ID:</label>
                    <strong id="modalEmployeeId">-</strong>
                </div>
                <div class="info-group">
                    <label>Department:</label>
                    <strong id="modalDepartment">-</strong>
                </div>
                <div class="info-group">
                    <label>Position:</label>
                    <strong id="modalPosition">-</strong>
                </div>
                <div class="info-group">
                    <label>Period:</label>
                    <strong id="modalPeriod">-</strong>
                </div>
                <div class="info-group">
                    <label>Pay Date:</label>
                    <strong id="modalPayDate">-</strong>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Earnings Section -->
            <div class="payslip-section">
                <h4 class="section-title">Earnings</h4>
                <div class="payslip-table">
                    <div class="table-row">
                        <span>Monthly Rate:</span>
                        <strong id="modalMonthlyRate">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Daily Rate:</span>
                        <strong id="modalDailyRate">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Days Worked:</span>
                        <strong id="modalDaysWorked">0</strong>
                    </div>
                    <div class="table-row highlight">
                        <span>Basic Pay:</span>
                        <strong id="modalBasicPay">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Overtime Pay:</span>
                        <strong id="modalOtPay">₱0.00</strong>
                    </div>
                    <div class="table-row total">
                        <span>Gross Pay:</span>
                        <strong id="modalGrossPay">₱0.00</strong>
                    </div>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Deductions Section -->
            <div class="payslip-section">
                <h4 class="section-title">Deductions</h4>
                <div class="payslip-table">
                    <div class="table-row">
                        <span>Late Deduction:</span>
                        <strong class="deduction-amount" id="modalLateDeduction">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Undertime Deduction:</span>
                        <strong class="deduction-amount" id="modalUndertimeDeduction">₱0.00</strong>
                    </div>
                    <div id="modalDeductionBreakdown">
                        <!-- Dynamic deduction breakdown will be inserted here -->
                    </div>
                    <div class="table-row total">
                        <span>Total Deductions:</span>
                        <strong class="deduction-amount" id="modalTotalDeductions">₱0.00</strong>
                    </div>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Net Pay Section -->
            <div class="payslip-section">
                <div class="net-pay-box">
                    <span>NET PAY</span>
                    <strong id="modalNetPay">₱0.00</strong>
                </div>
            </div>

            <!-- Status and Notes -->
            <div class="payslip-footer">
                <div class="status-info">
                    <label>Status:</label>
                    <span id="modalStatus" class="badge-status">-</span>
                </div>
                <div class="notes-info eh-hidden" id="modalNotesSection">
                    <label>Notes:</label>
                    <p id="modalNotes">-</p>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-row">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p class="signature-label">Employee Signature</p>
                        <p class="signature-date">Date: <span id="employeeSignDate">__________</span></p>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p class="signature-label">Prepared By</p>
                        <p class="signature-name" id="preparedByName">HR Department</p>
                        <p class="signature-date">Date Released: <span id="releaseDate">{{ date('M d, Y') }}</span></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePayslipDetailModal()">Close</button>
            <button type="button" class="btn-primary" onclick="printPayslip()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print Payslip
            </button>
        </div>
</x-modal>

@push('styles')
    @vite('resources/css/employee/employeePayslip.css')
@endpush
