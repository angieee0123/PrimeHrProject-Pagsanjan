{{-- Deduction Details Modal · populated by showDeductionModal() in employeeDashboard.js.
     Headless modal-component usage: the eyebrow/title/subtitle need ids (deductionCategory/
     deductionName/deductionCode) for that JS to fill in, but the component's built-in
     header only exposes titleId/subtitleId (no eyebrowId), so the header is written out
     by hand here instead of using the title/eyebrow props. --}}
<x-modal id="deductionModal" close="closeDeductionModal">
    <div class="modal-header">
        <div>
            <span class="modal-eyebrow" id="deductionCategory">DEDUCTION DETAILS</span>
            <h3 class="modal-title" id="deductionName">Deduction Name</h3>
            <p class="modal-sub" id="deductionCode">Code</p>
        </div>
        <button class="modal-close" onclick="closeDeductionModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="modal-body">
        <div class="modal-emp-row">
            <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}</div>
            <div>
                <p class="modal-emp-id">{{ $employee->employee_id }}</p>
                <span class="badge-status" id="deductionStatusBadge">Active</span>
            </div>
        </div>
        <div class="modal-section-label">DEDUCTION INFORMATION</div>
        <div class="modal-row"><span>Total Amount</span><strong id="deductionTotalAmount">₱0.00</strong></div>
        <div class="modal-row"><span>Monthly Deduction</span><strong id="deductionMonthly">₱0.00</strong></div>
        <div class="modal-row"><span>Per Cutoff</span><strong id="deductionInstallment">₱0.00</strong></div>
        <div class="modal-row"><span>Remaining Balance</span><span class="modal-deduct" id="deductionRemaining">₱0.00</span></div>
        <div class="modal-section-label modal-section-deductions">SCHEDULE</div>
        <div class="modal-row"><span>Start Date</span><span id="deductionStartDate">N/A</span></div>
        <div class="modal-row"><span>End Date</span><span id="deductionEndDate">N/A</span></div>
        <div class="modal-row" id="deductionRemarksRow" style="display:none"><span>Remarks</span><span id="deductionRemarks" style="font-size:12px;color:#6b6a8a">N/A</span></div>
    </div>
    <div class="modal-footer">
        <button class="modal-btn-ghost" onclick="closeDeductionModal()">Close</button>
    </div>
</x-modal>
