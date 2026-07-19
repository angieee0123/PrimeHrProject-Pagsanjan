{{-- Recent Payslips --}}
<div class="table-section perm-section">
    <div class="table-header">
        <div>
            <p class="table-title">Recent Payslips</p>
            <p class="table-sub">Your last {{ $payslips->count() }} pay periods</p>
        </div>
        <div class="table-actions">
            <button class="modal-btn-primary" onclick="window.location.href='{{ route('employee.payslip') }}'">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                View All Payslips
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table payslip-history-table">
            <thead>
                <tr>
                    <th>Pay Period</th>
                    <th>Basic Pay</th>
                    <th>Deductions</th>
                    <th>Net Pay</th>
                    <th>Pay Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payslips as $p)
                <tr>
                    <td class="table-cell-period"><strong>{{ $p->period_label }}</strong></td>
                    <td class="table-cell-basic">₱{{ number_format($p->basic_pay, 2) }}</td>
                    <td class="table-cell-deduct">
                        @if($p->deductions > 0)
                            −₱{{ number_format($p->deductions, 2) }}
                            <br><span class="eh-subtext">late &amp; undertime</span>
                        @else
                            <span class="eh-na-note">₱0.00</span>
                        @endif
                    </td>
                    <td class="eh-net-pay-cell">₱{{ number_format($p->net_pay, 2) }}</td>
                    <td class="table-cell-date">{{ \Carbon\Carbon::parse($p->pay_date)->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="perm-empty">
                            <div class="perm-empty-icon">
                                <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            </div>
                            <p class="perm-empty-title">No Payslips Yet</p>
                            <p class="perm-empty-sub">Your pay periods will appear here once payroll is processed</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
