{{-- Payslip Modal --}}
@if($latestPayslip)
<div class="modal-overlay" id="payslipModal" onclick="closeModal('payslipModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">PAYSLIP · {{ strtoupper($latestPayslip->period_start->format('M d') . '-' . $latestPayslip->period_end->format('d, Y')) }}</span>
                <h3 class="modal-title">{{ Auth::user()->employee->first_name ?? '' }} {{ Auth::user()->employee->last_name ?? '' }}</h3>
                <p class="modal-sub">{{ Auth::user()->employee->employmentDetail->designationRelation->title ?? 'N/A' }} · {{ Auth::user()->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</p>
            </div>
            <button class="modal-close" onclick="closeModal('payslipModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr(Auth::user()->employee->first_name ?? 'U', 0, 1) . substr(Auth::user()->employee->last_name ?? 'N', 0, 1)) }}</div>
                <div>
                    <p class="modal-emp-id">{{ Auth::user()->employee->employee_id ?? 'N/A' }}</p>
                    <span class="badge-status {{ $latestPayslip->status === 'pending' ? 'pending' : 'processed' }}">{{ ucfirst($latestPayslip->status) }}</span>
                </div>
            </div>
            <div class="modal-section-label">EARNINGS</div>
            <div class="modal-row"><span>Basic Semi-Monthly Pay</span><strong>₱{{ number_format($latestPayslip->basic_pay, 2) }}</strong></div>
            @if($latestPayslip->ot_pay > 0)
            <div class="modal-row"><span>Overtime Pay</span><strong>₱{{ number_format($latestPayslip->ot_pay, 2) }}</strong></div>
            @endif
            <div class="modal-section-label modal-section-deductions">DEDUCTIONS</div>
            @if($latestPayslip->late_deduction > 0)
            <div class="modal-row"><span>Late Deduction</span><span class="modal-deduct">₱{{ number_format($latestPayslip->late_deduction, 2) }}</span></div>
            @endif
            @if($latestPayslip->undertime_deduction > 0)
            <div class="modal-row"><span>Undertime Deduction</span><span class="modal-deduct">₱{{ number_format($latestPayslip->undertime_deduction, 2) }}</span></div>
            @endif
            @if($latestPayslip->other_deductions > 0)
            <div class="modal-row"><span>Other Deductions</span><span class="modal-deduct">₱{{ number_format($latestPayslip->other_deductions, 2) }}</span></div>
            @endif
            <div class="modal-row total"><span>Total Deductions</span><span class="modal-deduct">₱{{ number_format($latestPayslip->late_deduction + $latestPayslip->undertime_deduction + $latestPayslip->other_deductions, 2) }}</span></div>
            <div class="modal-net-row">
                <span>NET PAY</span>
                <strong>₱{{ number_format($latestPayslip->net_pay, 2) }}</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('payslipModal')">Close</button>
            <button class="modal-btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Payslip
            </button>
        </div>
    </div>
</div>
@endif
