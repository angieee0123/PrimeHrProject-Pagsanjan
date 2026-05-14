<section class="table-section" id="transactions-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Leave Transaction History</h3>
            <p class="table-sub">Complete audit trail of all leave credit adjustments · {{ $leaveTransactions->total() ?? 0 }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterTransactionEmployee" onchange="applyTransactionFilters()">
                <option value="">All Employees</option>
                @foreach($transactionEmployees ?? [] as $emp)
                    <option value="{{ $emp->id }}" {{ request('filter_employee') == $emp->id ? 'selected' : '' }}>{{ $emp->employee_id }} - {{ $emp->first_name }} {{ $emp->last_name }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="filterTransactionType" onchange="applyTransactionFilters()">
                <option value="">All Types</option>
                <option value="credit" {{ request('filter_type') == 'credit' ? 'selected' : '' }}>Credit (Added)</option>
                <option value="debit" {{ request('filter_type') == 'debit' ? 'selected' : '' }}>Debit (Used)</option>
                <option value="pending" {{ request('filter_type') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="reversal" {{ request('filter_type') == 'reversal' ? 'selected' : '' }}>Reversal</option>
                <option value="adjustment" {{ request('filter_type') == 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
            </select>
            <select class="filter-select" id="filterTransactionLeaveType" onchange="applyTransactionFilters()">
                <option value="">All Leave Types</option>
                @foreach($leaveTypes ?? [] as $type)
                    <option value="{{ $type->leave_code }}" {{ request('filter_leave_code') == $type->leave_code ? 'selected' : '' }}>{{ $type->leave_code }} - {{ $type->leave_name }}</option>
                @endforeach
            </select>
            <input type="date" class="filter-select" id="filterTransactionDate" onchange="applyTransactionFilters()" value="{{ request('filter_date') }}" placeholder="Filter by date" style="width: 150px;">
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th style="text-align: left; cursor: pointer;" onclick="sortTransactionTable('employee_id')">
                        Employee <span class="sort-icon">{{ request('sort_by') == 'employee_id' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortTransactionTable('leave_code')">
                        Leave Type <span class="sort-icon">{{ request('sort_by') == 'leave_code' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortTransactionTable('transaction_type')">
                        Transaction Type <span class="sort-icon">{{ request('sort_by') == 'transaction_type' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortTransactionTable('amount')">
                        Amount <span class="sort-icon">{{ request('sort_by') == 'amount' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortTransactionTable('balance_before')">
                        Balance Before <span class="sort-icon">{{ request('sort_by') == 'balance_before' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortTransactionTable('balance_after')">
                        Balance After <span class="sort-icon">{{ request('sort_by') == 'balance_after' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortTransactionTable('transaction_date')">
                        Date <span class="sort-icon">{{ request('sort_by') == 'transaction_date' || !request('sort_by') ? (request('sort_order', 'desc') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center;">Reference</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="transactionsTableBody">
                @forelse($leaveTransactions ?? [] as $transaction)
                <tr class="transaction-row">
                    <td data-label="Employee" style="text-align: left;">
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[($transaction->employee_id ?? 0) % count($avatarColors)] }};">
                                {{ strtoupper(substr($transaction->employee->first_name ?? 'N', 0, 1) . substr($transaction->employee->last_name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $transaction->employee->first_name ?? 'N/A' }} {{ $transaction->employee->last_name ?? '' }}</p>
                                <p class="emp-id">{{ $transaction->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td data-label="Leave Type" style="text-align: center;">
                        <span class="dept-tag">{{ $transaction->leave_code }}</span>
                    </td>
                    <td data-label="Type" style="text-align: center;">
                        @if($transaction->transaction_type === 'credit')
                            <span class="badge-status processed">Credit</span>
                        @elseif($transaction->transaction_type === 'debit')
                            <span class="badge-status on-hold">Debit</span>
                        @elseif($transaction->transaction_type === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($transaction->transaction_type === 'reversal')
                            <span class="badge-status cancelled">Reversal</span>
                        @elseif($transaction->transaction_type === 'adjustment')
                            <span class="badge-status pending">Adjustment</span>
                        @else
                            <span class="badge-status cancelled">{{ ucfirst($transaction->transaction_type) }}</span>
                        @endif
                    </td>
                    <td data-label="Amount" style="text-align: center; font-weight: 600; color: {{ $transaction->amount >= 0 ? '#15803d' : '#dc2626' }};">
                        {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 6) }} days
                    </td>
                    <td data-label="Before" style="text-align: center; color: #6b6a8a;">
                        {{ number_format($transaction->balance_before, 6) }}
                    </td>
                    <td data-label="After" style="text-align: center; font-weight: 600; color: #0b044d;">
                        {{ number_format($transaction->balance_after, 6) }}
                    </td>
                    <td data-label="Date" style="text-align: center; color: #6b6a8a; font-size: 12px;">
                        {{ $transaction->transaction_date ? $transaction->transaction_date->format('M d, Y') : 'N/A' }}
                    </td>
                    <td data-label="Reference" style="text-align: center; font-size: 12px;">
                        @if($transaction->reference_type === 'leave_application')
                            <span style="color: #0b044d; font-weight: 500;">Leave App</span>
                        @elseif($transaction->reference_type === 'manual_adjustment')
                            <span style="color: #8e1e18; font-weight: 500;">Manual</span>
                        @elseif($transaction->reference_type === 'accrual')
                            <span style="color: #15803d; font-weight: 500;">Accrual</span>
                        @elseif($transaction->reference_type === 'initialization')
                            <span style="color: #6b3fa0; font-weight: 500;">Initialization</span>
                        @else
                            <span style="color: #6b6a8a;">{{ ucfirst(str_replace('_', ' ', $transaction->reference_type ?? 'N/A')) }}</span>
                        @endif
                    </td>
                    <td data-label="Actions" style="text-align: center;">
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewTransactionDetails(
                                '{{ addslashes($transaction->employee->first_name ?? 'N/A') }} {{ addslashes($transaction->employee->last_name ?? '') }}',
                                '{{ $transaction->employee->employee_id ?? 'N/A' }}',
                                '{{ $transaction->leave_code }}',
                                '{{ ucfirst($transaction->transaction_type) }}',
                                {{ $transaction->amount }},
                                {{ $transaction->balance_before }},
                                {{ $transaction->balance_after }},
                                '{{ $transaction->transaction_date ? $transaction->transaction_date->format('M d, Y') : 'N/A' }}',
                                '{{ ucfirst(str_replace('_', ' ', $transaction->reference_type ?? 'N/A')) }}',
                                '{{ addslashes($transaction->remarks ?? 'N/A') }}',
                                '{{ optional(optional($transaction->processedBy)->employee)->first_name ?? 'System' }} {{ optional(optional($transaction->processedBy)->employee)->last_name ?? '' }}'
                            )">View</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 60px 20px;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No transactions found</p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af;">Leave transactions will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="margin: 0;" id="transactionFooter">
                Showing <strong>{{ $leaveTransactions->firstItem() ?? 0 }}</strong> to 
                <strong>{{ $leaveTransactions->lastItem() ?? 0 }}</strong> of 
                <strong>{{ $leaveTransactions->total() ?? 0 }}</strong> transactions
            </p>
        </div>
        <div class="pagination">
            @if(isset($leaveTransactions) && $leaveTransactions->hasPages())
                @if ($leaveTransactions->onFirstPage())
                    <button class="page-btn" disabled>‹</button>
                @else
                    <button class="page-btn" onclick="navigateToTransactionPage('{{ $leaveTransactions->previousPageUrl() }}')">‹</button>
                @endif

                @foreach ($leaveTransactions->getUrlRange(1, $leaveTransactions->lastPage()) as $page => $url)
                    @if ($page == $leaveTransactions->currentPage())
                        <button class="page-btn active">{{ $page }}</button>
                    @else
                        <button class="page-btn" onclick="navigateToTransactionPage('{{ $url }}')">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($leaveTransactions->hasMorePages())
                    <button class="page-btn" onclick="navigateToTransactionPage('{{ $leaveTransactions->nextPageUrl() }}')">›</button>
                @else
                    <button class="page-btn" disabled>›</button>
                @endif
            @else
                <button class="page-btn active">1</button>
            @endif
        </div>
    </div>
</section>

{{-- Transaction Detail Modal --}}
<div class="modal-overlay" id="transactionDetailModal" onclick="closeTransactionDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">TRANSACTION DETAILS</span>
                <h3 class="modal-title" id="transactionEmployeeName">Employee Name</h3>
                <p class="modal-sub" id="transactionEmployeeId">EMP-001</p>
            </div>
            <button class="modal-close" onclick="closeTransactionDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <span class="modal-section-label">TRANSACTION INFORMATION</span>
            <div class="modal-row"><span>Leave Type</span><strong id="transactionLeaveType">VL</strong></div>
            <div class="modal-row"><span>Transaction Type</span><span class="badge-status pending" id="transactionType">Credit</span></div>
            <div class="modal-row"><span>Amount</span><strong id="transactionAmount" style="color: #15803d;">+5.00 days</strong></div>
            <div class="modal-row"><span>Balance Before</span><strong id="transactionBalanceBefore">10.00 days</strong></div>
            <div class="modal-row"><span>Balance After</span><strong id="transactionBalanceAfter">15.00 days</strong></div>
            <div class="modal-row"><span>Transaction Date</span><strong id="transactionDate">Jan 15, 2026</strong></div>
            
            <span class="modal-section-label modal-section-deductions">REFERENCE & AUDIT</span>
            <div class="modal-row"><span>Reference Type</span><strong id="transactionReference">Manual Adjustment</strong></div>
            <div class="modal-row"><span>Processed By</span><strong id="transactionProcessedBy">Admin User</strong></div>
            
            <span class="modal-section-label modal-section-deductions">REMARKS</span>
            <div class="modal-row"><span id="transactionRemarks" style="color: #6b7280; font-style: italic;">No remarks provided</span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeTransactionDetailModal()">Close</button>
        </div>
    </div>
</div>

<script>
function sortTransactionTable(column) {
    const currentSort = new URLSearchParams(window.location.search).get('sort_by');
    const currentOrder = new URLSearchParams(window.location.search).get('sort_order') || 'desc';
    const newOrder = (currentSort === column && currentOrder === 'asc') ? 'desc' : 'asc';
    
    const url = new URL(window.location.href);
    url.searchParams.set('sort_by', column);
    url.searchParams.set('sort_order', newOrder);
    url.searchParams.set('tab', 'transactions');
    window.location.href = url.toString();
}

function applyTransactionFilters() {
    const employeeId = document.getElementById('filterTransactionEmployee').value;
    const type = document.getElementById('filterTransactionType').value;
    const leaveCode = document.getElementById('filterTransactionLeaveType').value;
    const date = document.getElementById('filterTransactionDate').value;
    
    const url = new URL(window.location.href);
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    
    if (employeeId) {
        url.searchParams.set('filter_employee', employeeId);
    } else {
        url.searchParams.delete('filter_employee');
    }
    
    if (type) {
        url.searchParams.set('filter_type', type);
    } else {
        url.searchParams.delete('filter_type');
    }
    
    if (leaveCode) {
        url.searchParams.set('filter_leave_code', leaveCode);
    } else {
        url.searchParams.delete('filter_leave_code');
    }
    
    if (date) {
        url.searchParams.set('filter_date', date);
    } else {
        url.searchParams.delete('filter_date');
    }
    
    window.location.href = url.toString();
}

function navigateToTransactionPage(url) {
    const urlObj = new URL(url, window.location.origin);
    urlObj.searchParams.set('tab', 'transactions');
    window.location.href = urlObj.toString();
}

function viewTransactionDetails(employeeName, employeeId, leaveType, type, amount, balanceBefore, balanceAfter, date, reference, remarks, processedBy) {
    document.getElementById('transactionEmployeeName').textContent = employeeName;
    document.getElementById('transactionEmployeeId').textContent = employeeId;
    document.getElementById('transactionLeaveType').textContent = leaveType;
    
    const typeBadge = document.getElementById('transactionType');
    typeBadge.textContent = type;
    typeBadge.className = 'badge-status ' + 
        (type === 'Credit' ? 'processed' : 
         type === 'Debit' ? 'on-hold' : 
         type === 'Pending' ? 'pending' :
         type === 'Reversal' ? 'cancelled' :
         type === 'Adjustment' ? 'pending' : 'cancelled');
    
    const amountEl = document.getElementById('transactionAmount');
    amountEl.textContent = (amount >= 0 ? '+' : '') + parseFloat(amount).toFixed(6) + ' days';
    amountEl.style.color = amount >= 0 ? '#15803d' : '#dc2626';
    
    document.getElementById('transactionBalanceBefore').textContent = parseFloat(balanceBefore).toFixed(6) + ' days';
    document.getElementById('transactionBalanceAfter').textContent = parseFloat(balanceAfter).toFixed(6) + ' days';
    document.getElementById('transactionDate').textContent = date;
    document.getElementById('transactionReference').textContent = reference;
    document.getElementById('transactionProcessedBy').textContent = processedBy || 'System';
    document.getElementById('transactionRemarks').textContent = remarks || 'No remarks provided';
    
    document.getElementById('transactionDetailModal').style.display = 'flex';
}

function closeTransactionDetailModal() {
    document.getElementById('transactionDetailModal').style.display = 'none';
}
</script>
