{{-- Payslip History Table --}}
<div class="table-section">
    <div class="table-header">
        <div>
            <p class="table-title">Payslip History</p>
            <p class="table-sub">Recent payroll records</p>
        </div>
        <div class="table-actions">
            <button class="btn-export">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <button class="modal-btn-primary" onclick="openModal()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                View Latest
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table payslip-history-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Basic Pay</th>
                    <th>Deductions</th>
                    <th>Net Pay</th>
                    <th>Pay Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payslips as $payslip)
                <tr>
                    <td class="table-cell-period">{{ $payslip->period_start->format('M d') }}-{{ $payslip->period_end->format('d, Y') }}</td>
                    <td class="table-cell-basic">₱{{ number_format($payslip->basic_pay, 2) }}</td>
                    <td class="table-cell-deduct">₱{{ number_format($payslip->late_deduction + $payslip->undertime_deduction + $payslip->other_deductions, 2) }}</td>
                    <td class="net-pay">₱{{ number_format($payslip->net_pay, 2) }}</td>
                    <td class="table-cell-date">{{ $payslip->period_end->format('M d, Y') }}</td>
                    <td>
                        @if($payslip->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @else
                            <span class="badge-status processed">Processed</span>
                        @endif
                    </td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-action btn-view" onclick="viewPayslipDetail({{ $payslip->id }})" title="View Details">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="btn-action btn-print" onclick="printPayslipDirect({{ $payslip->id }})" title="Print">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="eh-empty-cell">No payslip records found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>Showing <strong>{{ $payslips->firstItem() ?? 0 }}–{{ $payslips->lastItem() ?? 0 }}</strong> of <strong>{{ $payslips->total() }}</strong> payslips</span>
        <div class="pagination">
            @if($payslips->onFirstPage())
                <button class="page-btn" disabled>‹</button>
            @else
                <a href="{{ $payslips->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach($payslips->getUrlRange(1, $payslips->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page == $payslips->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if($payslips->hasMorePages())
                <a href="{{ $payslips->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <button class="page-btn" disabled>›</button>
            @endif
        </div>
    </div>
</div>
