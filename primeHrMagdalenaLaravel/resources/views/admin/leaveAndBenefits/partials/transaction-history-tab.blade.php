<section class="table-section" id="transactions-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Leave Transaction History</h3>
            <p class="table-sub">Complete audit trail of all leave credit adjustments · {{ $leaveTransactions->total() ?? 0 }} records</p>
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
                            @if($transaction->employee->photo)
                                <img src="{{ $transaction->employee->photo }}" alt="{{ $transaction->employee->first_name }}" class="emp-avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #e8e7f5;">
                            @else
                                <div class="emp-avatar" style="background: {{ $avatarColors[($transaction->employee_id ?? 0) % count($avatarColors)] }}; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:12px; border:2px solid #e8e7f5;">
                                    {{ strtoupper(substr($transaction->employee->first_name ?? 'N', 0, 1) . substr($transaction->employee->last_name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $transaction->employee->first_name ?? 'N/A' }} {{ $transaction->employee->last_name ?? '' }}</p>
                                <p class="emp-id">{{ $transaction->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td data-label="Leave Type" style="text-align: center;">
                        <span class="dept-tag">{{ $transaction->leave_code }}</span>
                    </td>
                    <td data-label="Transaction Type" style="text-align: center;">
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
                    <td data-label="Amount" style="text-align: center; font-weight: 600; color: {{ $transaction->transaction_type === 'credit' || $transaction->transaction_type === 'adjustment' ? '#23875a' : '#d5433c' }};">
                        {{ $transaction->transaction_type === 'debit' ? '-' : '+' }}{{ number_format(abs($transaction->amount), 2) }} days
                    </td>
                    <td data-label="Balance Before" style="text-align: center; color: #6b6a8a;">
                        {{ number_format($transaction->balance_before, 2) }}
                    </td>
                    <td data-label="Balance After" style="text-align: center; font-weight: 600; color: #0b044d;">
                        {{ number_format($transaction->balance_after, 2) }}
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
                            <span style="color: #23875a; font-weight: 500;">Accrual</span>
                        @elseif($transaction->reference_type === 'initialization')
                            <span style="color: #6b3fa0; font-weight: 500;">Initialization</span>
                        @elseif($transaction->reference_type === 'leave_import')
                            <span style="color: #0284c7; font-weight: 500;">Import</span>
                        @else
                            <span style="color: #6b6a8a;">{{ ucfirst(str_replace('_', ' ', $transaction->reference_type ?? 'N/A')) }}</span>
                        @endif
                    </td>
                    <td data-label="Actions" style="text-align: center;">
                        <div style="position: relative; display: flex; justify-content: center;">
                            <button class="th-ellipsis-btn" onclick="toggleTransactionActionMenu(event, this)" title="Actions">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                            </button>
                            <div class="th-action-menu" style="display: none;">
                                <button onclick="viewTransactionDetails(
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
                                )">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View
                                </button>
                                @if($transaction->reference_type === 'manual_adjustment')
                                <button onclick="openEditTransactionModal(
                                    {{ $transaction->id }},
                                    {{ abs($transaction->amount) }},
                                    '{{ $transaction->amount < 0 ? 'deduct' : 'add' }}',
                                    '{{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : '' }}',
                                    '{{ addslashes(preg_replace('/^\[(DEDUCTION|ADDITION)\]\s*/', '', $transaction->remarks ?? '')) }}'
                                )">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                @endif
                            </div>
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
        <div style="display:flex;align-items:center;gap:12px;">
            <p style="margin: 0;" id="transactionFooter">
                Showing <strong id="transactionRowStart">{{ $leaveTransactions->firstItem() ?? 0 }}</strong>-<strong id="transactionRowEnd">{{ $leaveTransactions->lastItem() ?? 0 }}</strong> of <strong id="transactionRowTotal">{{ $leaveTransactions->total() ?? 0 }}</strong> records
            </p>
            <select id="transactionRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeTransactionRowsPerPage()">
                <option value="10" {{ request('transaction_per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('transaction_per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('transaction_per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('transaction_per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
                <option value="all" {{ request('transaction_per_page') == 'all' ? 'selected' : '' }}>Show All</option>
            </select>
        </div>
        <div class="pagination" id="transactionPaginationControls">
            @php
                $currentPage = $leaveTransactions->currentPage();
                $lastPage = $leaveTransactions->lastPage();
                $window = 1;
                $pageItems = [];
                for ($i = 1; $i <= $lastPage; $i++) {
                    if ($i === 1 || $i === $lastPage || ($i >= $currentPage - $window && $i <= $currentPage + $window)) {
                        $pageItems[] = $i;
                    } elseif (end($pageItems) !== '...') {
                        $pageItems[] = '...';
                    }
                }
            @endphp

            @if($currentPage > 1)
                <button class="page-btn" onclick="navigateToTransactionPage({{ $currentPage - 1 }})">‹</button>
            @else
                <button class="page-btn" disabled>‹</button>
            @endif

            @foreach ($pageItems as $page)
                @if($page === '...')
                    <span class="page-ellipsis">…</span>
                @elseif($page == $currentPage)
                    <button class="page-btn active">{{ $page }}</button>
                @else
                    <button class="page-btn" onclick="navigateToTransactionPage({{ $page }})">{{ $page }}</button>
                @endif
            @endforeach

            @if($currentPage < $lastPage)
                <button class="page-btn" onclick="navigateToTransactionPage({{ $currentPage + 1 }})">›</button>
            @else
                <button class="page-btn" disabled>›</button>
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
            <div class="modal-row"><span>Amount</span><strong id="transactionAmount" style="color: #23875a;">+5.00 days</strong></div>
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

{{-- Edit Transaction Modal --}}
<div class="modal-overlay" id="editTransactionModal" onclick="closeEditTransactionModal(event)" style="display: none;">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Transaction</h3>
                <p class="modal-subtitle">Update this manual leave credit adjustment</p>
            </div>
            <button class="modal-close" onclick="closeEditTransactionModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="editTransactionForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="transaction_type" id="editTransactionType" value="add">

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Amount (Days) <span style="color: #8e1e18;">*</span></label>
                        <input type="number" name="amount" id="editTransactionAmount" class="form-input" step="0.000001" min="0.000001" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Transaction Date <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="transaction_date" id="editTransactionDate" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Reason / Remarks <span style="color: #8e1e18;">*</span></label>
                    <textarea name="remarks" id="editTransactionRemarks" class="form-input" rows="3" maxlength="500" required></textarea>
                    <p style="font-size: 11px; color: #6b6a8a; margin: 4px 0 0 0;">This is a [ADDITION]/[DEDUCTION] adjustment based on the original transaction type; the amount sign cannot be flipped here.</p>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditTransactionModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function changeTransactionRowsPerPage() {
    const perPage = document.getElementById('transactionRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('transaction_per_page', perPage);
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

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

function applyTransactionDateRangeFilter() {
    const dateFrom = document.getElementById('filterTransactionDateFrom').value;
    const dateTo = document.getElementById('filterTransactionDateTo').value;
    const url = new URL(window.location.href);
    
    if (!dateFrom || !dateTo) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(dateFrom) > new Date(dateTo)) {
        alert('Start date must be before or equal to end date');
        return;
    }
    
    url.searchParams.set('filter_transaction_date_from', dateFrom);
    url.searchParams.set('filter_transaction_date_to', dateTo);
    url.searchParams.delete('filter_transaction_year');
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    
    window.location.href = url.toString();
}

function clearTransactionDateRangeFilter() {
    const url = new URL(window.location.href);
    url.searchParams.delete('filter_transaction_date_from');
    url.searchParams.delete('filter_transaction_date_to');
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function clearAllTransactionFilters() {
    const url = new URL(window.location.href);
    url.searchParams.delete('filter_transaction_date_from');
    url.searchParams.delete('filter_transaction_date_to');
    url.searchParams.delete('filter_transaction_year');
    url.searchParams.delete('filter_employee');
    url.searchParams.delete('filter_type');
    url.searchParams.delete('filter_leave_code');
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function applyTransactionYearFilter() {
    const year = document.getElementById('filterTransactionYear').value;
    const url = new URL(window.location.href);
    
    url.searchParams.delete('filter_transaction_date_from');
    url.searchParams.delete('filter_transaction_date_to');
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    
    if (year) {
        url.searchParams.set('filter_transaction_year', year);
    } else {
        url.searchParams.delete('filter_transaction_year');
    }
    
    window.location.href = url.toString();
}

function applyTransactionFilters() {
    const employeeId = document.getElementById('filterTransactionEmployee').value;
    const type = document.getElementById('filterTransactionType').value;
    const leaveCode = document.getElementById('filterTransactionLeaveType').value;

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

    window.location.href = url.toString();
}

function navigateToTransactionPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    url.searchParams.set('tab', 'transactions');
    window.location.href = url.toString();
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
    const sign = (type === 'Debit') ? '-' : '+';
    amountEl.textContent = sign + parseFloat(Math.abs(amount)).toFixed(2) + ' days';
    amountEl.style.color = (type === 'Debit') ? '#d5433c' : '#23875a';

    document.getElementById('transactionBalanceBefore').textContent = parseFloat(balanceBefore).toFixed(2) + ' days';
    document.getElementById('transactionBalanceAfter').textContent = parseFloat(balanceAfter).toFixed(2) + ' days';
    document.getElementById('transactionDate').textContent = date;
    document.getElementById('transactionReference').textContent = reference;
    document.getElementById('transactionProcessedBy').textContent = processedBy || 'System';
    document.getElementById('transactionRemarks').textContent = remarks || 'No remarks provided';

    document.getElementById('transactionDetailModal').style.display = 'flex';
}

function closeTransactionDetailModal() {
    document.getElementById('transactionDetailModal').style.display = 'none';
}

function toggleTransactionActionMenu(event, btn) {
    event.stopPropagation();
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.th-action-menu').forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', () => {
    document.querySelectorAll('.th-action-menu').forEach(m => m.style.display = 'none');
});

function openEditTransactionModal(id, amount, type, date, remarks) {
    document.getElementById('editTransactionForm').action = `/admin/leave/transactions/${id}`;
    document.getElementById('editTransactionType').value = type;
    document.getElementById('editTransactionAmount').value = amount;
    document.getElementById('editTransactionDate').value = date;
    document.getElementById('editTransactionRemarks').value = remarks;
    document.getElementById('editTransactionModal').style.display = 'flex';
}

function closeEditTransactionModal(event) {
    if (!event || event.target.id === 'editTransactionModal') {
        document.getElementById('editTransactionModal').style.display = 'none';
    }
}
</script>

<style>
.th-ellipsis-btn {
    background: none;
    border: none;
    color: #9999bb;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.th-ellipsis-btn:hover {
    background: #f1f5f9;
    color: #0b044d;
}
.th-action-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.15);
    z-index: 100;
    min-width: 140px;
    margin-top: 6px;
    overflow: hidden;
    animation: thSlideDown 0.2s ease-out;
}
.th-action-menu button {
    width: 100%;
    padding: 11px 14px;
    border: none;
    background: none;
    text-align: left;
    font-size: 12px;
    color: #0b044d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.th-action-menu button:hover {
    background: #f0effe;
}
@keyframes thSlideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
