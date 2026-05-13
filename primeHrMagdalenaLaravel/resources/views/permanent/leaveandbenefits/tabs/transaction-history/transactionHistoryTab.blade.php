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
            <input type="date" class="filter-select" id="filterTransactionDate" onchange="applyEmployeeTransactionFilters()" value="{{ request('filter_date') }}" placeholder="Filter by date" style="width: 150px;">
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th style="text-align: center; cursor: pointer;" onclick="sortEmployeeTransactionTable('leave_code')">
                        Leave Type <span class="sort-icon">{{ request('sort_by') == 'leave_code' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortEmployeeTransactionTable('transaction_type')">
                        Transaction Type <span class="sort-icon">{{ request('sort_by') == 'transaction_type' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortEmployeeTransactionTable('amount')">
                        Amount <span class="sort-icon">{{ request('sort_by') == 'amount' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortEmployeeTransactionTable('balance_before')">
                        Balance Before <span class="sort-icon">{{ request('sort_by') == 'balance_before' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortEmployeeTransactionTable('balance_after')">
                        Balance After <span class="sort-icon">{{ request('sort_by') == 'balance_after' ? (request('sort_order') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center; cursor: pointer;" onclick="sortEmployeeTransactionTable('transaction_date')">
                        Date <span class="sort-icon">{{ request('sort_by') == 'transaction_date' || !request('sort_by') ? (request('sort_order', 'desc') == 'asc' ? '↑' : '↓') : '⇅' }}</span>
                    </th>
                    <th style="text-align: center;">Reference</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employeeTransactions ?? [] as $transaction)
                <tr class="transaction-row">
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
                        @elseif($transaction->transaction_type === 'adjustment')
                            <span class="badge-status pending">Adjustment</span>
                        @else
                            <span class="badge-status cancelled">{{ ucfirst($transaction->transaction_type) }}</span>
                        @endif
                    </td>
                    <td data-label="Amount" style="text-align: center; font-weight: 600; color: {{ $transaction->amount >= 0 ? '#15803d' : '#dc2626' }};">
                        {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 4) }} days
                    </td>
                    <td data-label="Before" style="text-align: center; color: #6b6a8a;">
                        {{ number_format($transaction->balance_before, 4) }}
                    </td>
                    <td data-label="After" style="text-align: center; font-weight: 600; color: #0b044d;">
                        {{ number_format($transaction->balance_after, 4) }}
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
                    <td colspan="8" style="text-align: center; padding: 60px 20px;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No transactions found</p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af;">Your leave transactions will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="margin: 0;">
                Showing <strong>{{ $employeeTransactions->firstItem() ?? 0 }}</strong> to 
                <strong>{{ $employeeTransactions->lastItem() ?? 0 }}</strong> of 
                <strong>{{ $employeeTransactions->total() ?? 0 }}</strong> transactions
            </p>
        </div>
        <div class="pagination">
            @if(isset($employeeTransactions) && $employeeTransactions->hasPages())
                @if ($employeeTransactions->onFirstPage())
                    <button class="page-btn" disabled>‹</button>
                @else
                    <button class="page-btn" onclick="navigateToEmployeeTransactionPage('{{ $employeeTransactions->previousPageUrl() }}')">‹</button>
                @endif

                @foreach ($employeeTransactions->getUrlRange(1, $employeeTransactions->lastPage()) as $page => $url)
                    @if ($page == $employeeTransactions->currentPage())
                        <button class="page-btn active">{{ $page }}</button>
                    @else
                        <button class="page-btn" onclick="navigateToEmployeeTransactionPage('{{ $url }}')">{{ $page }}</button>
                    @endif
                @endforeach

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
<div class="modal-overlay" id="employeeTransactionDetailModal" onclick="closeEmployeeTransactionDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 600px;">
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
            <div class="modal-row"><span>Amount</span><strong id="empTransactionAmount" style="color: #15803d;">+5.00 days</strong></div>
            <div class="modal-row"><span>Balance Before</span><strong id="empTransactionBalanceBefore">10.00 days</strong></div>
            <div class="modal-row"><span>Balance After</span><strong id="empTransactionBalanceAfter">15.00 days</strong></div>
            <div class="modal-row"><span>Transaction Date</span><strong id="empTransactionDate">Jan 15, 2026</strong></div>
            
            <span class="modal-section-label modal-section-deductions">REFERENCE</span>
            <div class="modal-row"><span>Reference Type</span><strong id="empTransactionReference">Manual Adjustment</strong></div>
            
            <span class="modal-section-label modal-section-deductions">REMARKS</span>
            <div class="modal-row"><span id="empTransactionRemarks" style="color: #6b7280; font-style: italic;">No remarks provided</span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeEmployeeTransactionDetailModal()">Close</button>
        </div>
    </div>
</div>

<script>
function sortEmployeeTransactionTable(column) {
    const url = new URL(window.location.href);
    const currentSort = url.searchParams.get('sort_by');
    const currentOrder = url.searchParams.get('sort_order') || 'desc';
    const newOrder = (currentSort === column && currentOrder === 'asc') ? 'desc' : 'asc';
    
    url.searchParams.set('sort_by', column);
    url.searchParams.set('sort_order', newOrder);
    url.searchParams.set('tab', 'transactions');
    
    window.location.href = url.toString();
}

function applyEmployeeTransactionFilters() {
    const type = document.getElementById('filterTransactionType')?.value || '';
    const leaveCode = document.getElementById('filterTransactionLeaveType')?.value || '';
    const date = document.getElementById('filterTransactionDate')?.value || '';
    
    const url = new URL(window.location.href);
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    
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

function navigateToEmployeeTransactionPage(url) {
    const urlObj = new URL(url, window.location.origin);
    urlObj.searchParams.set('tab', 'transactions');
    window.location.href = urlObj.toString();
}

function viewEmployeeTransactionDetails(leaveType, type, amount, balanceBefore, balanceAfter, date, reference, remarks) {
    document.getElementById('empTransactionLeaveType').textContent = leaveType;
    
    const typeBadge = document.getElementById('empTransactionType');
    typeBadge.textContent = type;
    typeBadge.className = 'badge-status ' + 
        (type === 'Credit' ? 'processed' : 
         type === 'Debit' ? 'on-hold' : 
         type === 'Pending' ? 'pending' :
         type === 'Adjustment' ? 'pending' : 'cancelled');
    
    const amountEl = document.getElementById('empTransactionAmount');
    amountEl.textContent = (amount >= 0 ? '+' : '') + parseFloat(amount).toFixed(4) + ' days';
    amountEl.style.color = amount >= 0 ? '#15803d' : '#dc2626';
    
    document.getElementById('empTransactionBalanceBefore').textContent = parseFloat(balanceBefore).toFixed(4) + ' days';
    document.getElementById('empTransactionBalanceAfter').textContent = parseFloat(balanceAfter).toFixed(4) + ' days';
    document.getElementById('empTransactionDate').textContent = date;
    document.getElementById('empTransactionReference').textContent = reference;
    document.getElementById('empTransactionRemarks').textContent = remarks || 'No remarks provided';
    
    document.getElementById('employeeTransactionDetailModal').style.display = 'flex';
}

function closeEmployeeTransactionDetailModal() {
    document.getElementById('employeeTransactionDetailModal').style.display = 'none';
}
</script>
