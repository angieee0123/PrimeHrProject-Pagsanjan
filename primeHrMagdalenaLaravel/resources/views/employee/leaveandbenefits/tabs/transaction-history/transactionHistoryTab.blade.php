<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Leave Transaction History</h3>
            <p class="table-sub">Complete record of all your leave credit changes · {{ $employeeTransactions->total() ?? 0 }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterTransactionLeaveType" onchange="applyEmployeeTransactionFilters()">
                <option value="">All Leave Types</option>
                @foreach($leaveTypes ?? [] as $type)
                    <option value="{{ $type->leave_code }}" {{ request('filter_leave_code') == $type->leave_code ? 'selected' : '' }}>{{ $type->leave_code }} - {{ $type->leave_name }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="filterTransactionType" onchange="applyEmployeeTransactionFilters()">
                <option value="">All Types</option>
                <option value="credit" {{ request('filter_type') == 'credit' ? 'selected' : '' }}>Credit (Added)</option>
                <option value="debit" {{ request('filter_type') == 'debit' ? 'selected' : '' }}>Debit (Used)</option>
                <option value="pending" {{ request('filter_type') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="adjustment" {{ request('filter_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
            </select>
            <input type="date" class="filter-select lb-w-150" id="filterTransactionDate" onchange="applyEmployeeTransactionFilters()" value="{{ request('filter_date') }}" placeholder="Filter by date">
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="lb-th-sort" onclick="sortEmployeeTransactionTable('leave_code')">
                        Leave Type <span class="sort-icon">{{ request('sort_by') == 'leave_code' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th class="lb-th-sort" onclick="sortEmployeeTransactionTable('transaction_type')">
                        Transaction Type <span class="sort-icon">{{ request('sort_by') == 'transaction_type' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th class="lb-th-sort" onclick="sortEmployeeTransactionTable('amount')">
                        Amount <span class="sort-icon">{{ request('sort_by') == 'amount' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th class="lb-th-sort" onclick="sortEmployeeTransactionTable('balance_before')">
                        Balance Before <span class="sort-icon">{{ request('sort_by') == 'balance_before' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th class="lb-th-sort" onclick="sortEmployeeTransactionTable('balance_after')">
                        Balance After <span class="sort-icon">{{ request('sort_by') == 'balance_after' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th class="lb-th-sort" onclick="sortEmployeeTransactionTable('transaction_date')">
                        Date <span class="sort-icon">{{ request('sort_by') == 'transaction_date' || !request('sort_by') ? (request('sort_order', 'desc') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th class="lb-ta-center">Source/Reason</th>
                    <th class="lb-ta-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employeeTransactions ?? [] as $transaction)
                <tr class="transaction-row">
                    <td data-label="Leave Type" class="lb-ta-center">
                        <span class="dept-tag">{{ $transaction->leave_code }}</span>
                    </td>
                    <td data-label="Type" class="lb-ta-center">
                        @if($transaction->transaction_type === 'credit')
                            <span class="badge-status processed">Credit</span>
                        @elseif($transaction->transaction_type === 'debit')
                            <span class="badge-status on-hold">Debit</span>
                        @elseif($transaction->transaction_type === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($transaction->transaction_type === 'adjustment')
                            <span class="badge-status pending">Adjustment</span>
                        @else
                            <span class="badge-status cancelled">{{ ucfirst($transaction->transaction_type) }}</span>
                        @endif
                    </td>
                    <td data-label="Amount" class="lb-ta-center lb-fw600" style="color: {{ $transaction->amount >= 0 ? 'var(--theme-success)' : 'var(--theme-danger)' }};">
                        {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} days
                    </td>
                    <td data-label="Before" class="lb-ta-center lb-c-muted">
                        {{ number_format($transaction->balance_before, 2) }}
                    </td>
                    <td data-label="After" class="lb-ta-center lb-fw600 lb-c-primary">
                        {{ number_format($transaction->balance_after, 2) }}
                    </td>
                    <td data-label="Date" class="lb-ta-center lb-c-muted lb-fs-12">
                        {{ $transaction->transaction_date ? $transaction->transaction_date->format('M d, Y') : 'N/A' }}
                    </td>
                    <td data-label="Source" class="lb-source-cell">
                        @php
                            $remarks = $transaction->remarks ?? '';
                            $isLateDeduction = str_contains($remarks, 'Late deduction');
                            $isUndertimeDeduction = str_contains($remarks, 'Undertime deduction');
                            $isLeaveApp = $transaction->reference_type === 'leave_application';
                            $isManual = $transaction->reference_type === 'manual_adjustment';
                            $isAccrual = $transaction->reference_type === 'accrual';
                            $isAttendanceReversal = $transaction->reference_type === 'attendance_correction_reversal';
                        @endphp

                        @if($isAttendanceReversal)
                            <div class="lb-flex-gap-6">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2">
                                    <polyline points="1 4 1 10 7 10"/>
                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                                </svg>
                                <span class="lb-fw600" style="color: #0891b2;">Attendance Correction Reversal</span>
                            </div>
                            <small class="lb-source-remark">{{ $remarks }}</small>
                        @elseif($isLateDeduction)
                            <div class="lb-flex-gap-6">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <span class="lb-fw600" style="color: var(--theme-warning);">Late Deduction</span>
                            </div>
                            <small class="lb-source-remark">{{ $remarks }}</small>
                        @elseif($isUndertimeDeduction)
                            <div class="lb-flex-gap-6">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 8 10"/>
                                </svg>
                                <span class="lb-fw600" style="color: var(--theme-danger);">Undertime Deduction</span>
                            </div>
                            <small class="lb-source-remark">{{ $remarks }}</small>
                        @elseif($isLeaveApp)
                            <div class="lb-flex-gap-6">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span class="lb-fw600 lb-c-primary">Leave Application</span>
                            </div>
                            <small class="lb-source-remark">{{ $remarks }}</small>
                        @elseif($isManual)
                            <div class="lb-flex-gap-6">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b3fa0" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                <span class="lb-fw600" style="color: #6b3fa0;">Manual Adjustment</span>
                            </div>
                            <small class="lb-source-remark">{{ $remarks }}</small>
                        @elseif($isAccrual)
                            <div class="lb-flex-gap-6">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                                <span class="lb-fw600 lb-c-green">Monthly Accrual</span>
                            </div>
                            <small class="lb-source-remark">{{ $remarks }}</small>
                        @else
                            <div class="lb-flex-gap-6">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b6a8a" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="16" x2="12" y2="12"/>
                                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                                </svg>
                                <span class="lb-fw500-muted">{{ ucfirst(str_replace('_', ' ', $transaction->reference_type ?? 'Other')) }}</span>
                            </div>
                            @if($remarks)
                                <small class="lb-source-remark">{{ $remarks }}</small>
                            @endif
                        @endif
                    </td>
                    <td data-label="Actions" class="lb-ta-center">
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewEmployeeTransactionDetails(
                                '{{ $transaction->leave_code }}',
                                '{{ ucfirst($transaction->transaction_type) }}',
                                {{ $transaction->amount }},
                                {{ $transaction->balance_before }},
                                {{ $transaction->balance_after }},
                                '{{ $transaction->transaction_date ? $transaction->transaction_date->format('M d, Y') : 'N/A' }}',
                                '{{ ucfirst(str_replace('_', ' ', $transaction->reference_type ?? 'N/A')) }}',
                                '{{ addslashes($transaction->remarks ?? 'N/A') }}'
                            )">View</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="lb-empty-cell">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" class="lb-empty-icon">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="lb-empty-title">No transactions found</p>
                        <p class="lb-empty-sub">Your leave transactions will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="lb-flex-gap-12">
            <p class="lb-m0">
                Showing <strong>{{ $employeeTransactions->firstItem() ?? 0 }}</strong> to
                <strong>{{ $employeeTransactions->lastItem() ?? 0 }}</strong> of
                <strong>{{ $employeeTransactions->total() ?? 0 }}</strong> transactions
            </p>
            <select id="employeeTransactionRowsPerPage" class="filter-select lb-select-inline" onchange="changeEmployeeTransactionRowsPerPage()">
                <option value="10" {{ request('employee_transaction_per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('employee_transaction_per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('employee_transaction_per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('employee_transaction_per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination">
            @if(isset($employeeTransactions) && $employeeTransactions->hasPages())
                @php
                    $current = $employeeTransactions->currentPage();
                    $last = $employeeTransactions->lastPage();
                    // Sliding window of 5 pages around the current one, clamped to the range
                    $window = 2;
                    $start = max(1, min($current - $window, $last - ($window * 2)));
                    $end = min($last, max($current + $window, 1 + ($window * 2)));
                @endphp

                @if ($employeeTransactions->onFirstPage())
                    <button class="page-btn" disabled>‹</button>
                @else
                    <button class="page-btn" onclick="navigateToEmployeeTransactionPage('{{ $employeeTransactions->previousPageUrl() }}')">‹</button>
                @endif

                @if ($start > 1)
                    <button class="page-btn" onclick="navigateToEmployeeTransactionPage('{{ $employeeTransactions->url(1) }}')">1</button>
                    @if ($start > 2)
                        <span class="page-ellipsis">…</span>
                    @endif
                @endif

                @foreach ($employeeTransactions->getUrlRange($start, $end) as $page => $url)
                    @if ($page == $current)
                        <button class="page-btn active">{{ $page }}</button>
                    @else
                        <button class="page-btn" onclick="navigateToEmployeeTransactionPage('{{ $url }}')">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <span class="page-ellipsis">…</span>
                    @endif
                    <button class="page-btn" onclick="navigateToEmployeeTransactionPage('{{ $employeeTransactions->url($last) }}')">{{ $last }}</button>
                @endif

                @if ($employeeTransactions->hasMorePages())
                    <button class="page-btn" onclick="navigateToEmployeeTransactionPage('{{ $employeeTransactions->nextPageUrl() }}')">›</button>
                @else
                    <button class="page-btn" disabled>›</button>
                @endif
            @else
                <button class="page-btn active">1</button>
            @endif
        </div>
    </div>
</section>

{{-- Employee Transaction Detail Modal --}}
<div class="modal-overlay lb-hidden" id="employeeTransactionDetailModal" onclick="closeEmployeeTransactionDetailModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">TRANSACTION DETAILS</span>
                <h3 class="modal-title">Leave Credit Transaction</h3>
            </div>
            <button class="modal-close" onclick="closeEmployeeTransactionDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <span class="modal-section-label">TRANSACTION INFORMATION</span>
            <div class="modal-row"><span>Leave Type</span><strong id="empTransactionLeaveType">VL</strong></div>
            <div class="modal-row"><span>Transaction Type</span><span class="badge-status pending" id="empTransactionType">Credit</span></div>
            <div class="modal-row"><span>Amount</span><strong id="empTransactionAmount" class="lb-c-green">+5.00 days</strong></div>
            <div class="modal-row"><span>Balance Before</span><strong id="empTransactionBalanceBefore">10.00 days</strong></div>
            <div class="modal-row"><span>Balance After</span><strong id="empTransactionBalanceAfter">15.00 days</strong></div>
            <div class="modal-row"><span>Transaction Date</span><strong id="empTransactionDate">Jan 15, 2026</strong></div>

            <span class="modal-section-label modal-section-deductions">SOURCE/REASON</span>
            <div class="modal-row">
                <div class="lb-w-full">
                    <div id="empTransactionSourceLabel" class="lb-source-header">
                        <svg id="empTransactionSourceIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b6a8a" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <strong id="empTransactionReference" class="lb-c-primary">Manual Adjustment</strong>
                    </div>
                    <div class="lb-source-box">
                        <span id="empTransactionRemarks" class="lb-source-remarks-text">No remarks provided</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeEmployeeTransactionDetailModal()">Close</button>
        </div>
    </div>
</div>
