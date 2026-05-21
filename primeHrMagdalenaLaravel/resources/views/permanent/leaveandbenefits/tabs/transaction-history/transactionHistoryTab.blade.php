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
                    <th style="text-align: center;">Source/Reason</th>
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
                    <td data-label="Source" style="text-align: left; font-size: 12px; padding-left: 16px;">
                        @php
                            $remarks = $transaction->remarks ?? '';
                            $isLateDeduction = str_contains($remarks, 'Late deduction');
                            $isUndertimeDeduction = str_contains($remarks, 'Undertime deduction');
                            $isLeaveApp = $transaction->reference_type === 'leave_application';
                            $isManual = $transaction->reference_type === 'manual_adjustment';
                            $isAccrual = $transaction->reference_type === 'accrual';
                        @endphp
                        
                        @if($isLateDeduction)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <span style="color: #a16207; font-weight: 600;">Late Deduction</span>
                            </div>
                            <small style="color: #6b6a8a; display: block; margin-top: 2px; padding-left: 20px;">{{ $remarks }}</small>
                        @elseif($isUndertimeDeduction)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 8 10"/>
                                </svg>
                                <span style="color: #8e1e18; font-weight: 600;">Undertime Deduction</span>
                            </div>
                            <small style="color: #6b6a8a; display: block; margin-top: 2px; padding-left: 20px;">{{ $remarks }}</small>
                        @elseif($isLeaveApp)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span style="color: #0b044d; font-weight: 600;">Leave Application</span>
                            </div>
                            <small style="color: #6b6a8a; display: block; margin-top: 2px; padding-left: 20px;">{{ $remarks }}</small>
                        @elseif($isManual)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b3fa0" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                <span style="color: #6b3fa0; font-weight: 600;">Manual Adjustment</span>
                            </div>
                            <small style="color: #6b6a8a; display: block; margin-top: 2px; padding-left: 20px;">{{ $remarks }}</small>
                        @elseif($isAccrual)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                                <span style="color: #15803d; font-weight: 600;">Monthly Accrual</span>
                            </div>
                            <small style="color: #6b6a8a; display: block; margin-top: 2px; padding-left: 20px;">{{ $remarks }}</small>
                        @else
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b6a8a" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="16" x2="12" y2="12"/>
                                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                                </svg>
                                <span style="color: #6b6a8a; font-weight: 500;">{{ ucfirst(str_replace('_', ' ', $transaction->reference_type ?? 'Other')) }}</span>
                            </div>
                            @if($remarks)
                                <small style="color: #6b6a8a; display: block; margin-top: 2px; padding-left: 20px;">{{ $remarks }}</small>
                            @endif
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
            
            <span class="modal-section-label modal-section-deductions">SOURCE/REASON</span>
            <div class="modal-row">
                <div style="width: 100%;">
                    <div id="empTransactionSourceLabel" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <svg id="empTransactionSourceIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b6a8a" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <strong id="empTransactionReference" style="color: #0b044d;">Manual Adjustment</strong>
                    </div>
                    <div style="background: #f7f6ff; padding: 12px; border-radius: 8px; border-left: 3px solid #0b044d;">
                        <span id="empTransactionRemarks" style="color: #6b7280; font-size: 13px; line-height: 1.5;">No remarks provided</span>
                    </div>
                </div>
            </div>
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
    amountEl.textContent = (amount >= 0 ? '+' : '') + parseFloat(amount).toFixed(6) + ' days';
    amountEl.style.color = amount >= 0 ? '#15803d' : '#dc2626';
    
    document.getElementById('empTransactionBalanceBefore').textContent = parseFloat(balanceBefore).toFixed(6) + ' days';
    document.getElementById('empTransactionBalanceAfter').textContent = parseFloat(balanceAfter).toFixed(6) + ' days';
    document.getElementById('empTransactionDate').textContent = date;
    
    // Determine source type and update icon/label
    const sourceIcon = document.getElementById('empTransactionSourceIcon');
    const sourceLabel = document.getElementById('empTransactionReference');
    const remarksEl = document.getElementById('empTransactionRemarks');
    
    const isLateDeduction = remarks.includes('Late deduction');
    const isUndertimeDeduction = remarks.includes('Undertime deduction');
    const isLeaveApp = reference.toLowerCase().includes('leave app');
    const isAccrual = reference.toLowerCase().includes('accrual');
    const isManual = reference.toLowerCase().includes('manual');
    
    if (isLateDeduction) {
        sourceIcon.innerHTML = '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>';
        sourceIcon.setAttribute('stroke', '#a16207');
        sourceLabel.textContent = 'Late Deduction';
        sourceLabel.style.color = '#a16207';
    } else if (isUndertimeDeduction) {
        sourceIcon.innerHTML = '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 8 10"/>';
        sourceIcon.setAttribute('stroke', '#8e1e18');
        sourceLabel.textContent = 'Undertime Deduction';
        sourceLabel.style.color = '#8e1e18';
    } else if (isLeaveApp) {
        sourceIcon.innerHTML = '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>';
        sourceIcon.setAttribute('stroke', '#0b044d');
        sourceLabel.textContent = 'Leave Application';
        sourceLabel.style.color = '#0b044d';
    } else if (isAccrual) {
        sourceIcon.innerHTML = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>';
        sourceIcon.setAttribute('stroke', '#15803d');
        sourceLabel.textContent = 'Monthly Accrual';
        sourceLabel.style.color = '#15803d';
    } else if (isManual) {
        sourceIcon.innerHTML = '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>';
        sourceIcon.setAttribute('stroke', '#6b3fa0');
        sourceLabel.textContent = 'Manual Adjustment';
        sourceLabel.style.color = '#6b3fa0';
    } else {
        sourceIcon.innerHTML = '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>';
        sourceIcon.setAttribute('stroke', '#6b6a8a');
        sourceLabel.textContent = reference;
        sourceLabel.style.color = '#6b6a8a';
    }
    
    remarksEl.textContent = remarks || 'No remarks provided';
    
    document.getElementById('employeeTransactionDetailModal').style.display = 'flex';
}

function closeEmployeeTransactionDetailModal() {
    document.getElementById('employeeTransactionDetailModal').style.display = 'none';
}
</script>
