// Transaction History Tab

window.changeTransactionRowsPerPage = function() {
    const perPage = document.getElementById('transactionRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('transaction_per_page', perPage);
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

window.sortTransactionTable = function(column) {
    const currentSort = new URLSearchParams(window.location.search).get('sort_by');
    const currentOrder = new URLSearchParams(window.location.search).get('sort_order') || 'desc';
    const newOrder = (currentSort === column && currentOrder === 'asc') ? 'desc' : 'asc';

    const url = new URL(window.location.href);
    url.searchParams.set('sort_by', column);
    url.searchParams.set('sort_order', newOrder);
    url.searchParams.set('tab', 'transactions');
    window.location.href = url.toString();
}

window.applyTransactionDateRangeFilter = function() {
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

window.clearTransactionDateRangeFilter = function() {
    const url = new URL(window.location.href);
    url.searchParams.delete('filter_transaction_date_from');
    url.searchParams.delete('filter_transaction_date_to');
    url.searchParams.set('tab', 'transactions');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

window.clearAllTransactionFilters = function() {
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

window.applyTransactionYearFilter = function() {
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

window.applyTransactionFilters = function() {
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

window.navigateToTransactionPage = function(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    url.searchParams.set('tab', 'transactions');
    window.location.href = url.toString();
}

window.viewTransactionDetails = function(employeeName, employeeId, leaveType, type, amount, balanceBefore, balanceAfter, date, reference, remarks, processedBy) {
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
    amountEl.style.color = (type === 'Debit') ? '#d5433c' : '#15803d';

    document.getElementById('transactionBalanceBefore').textContent = parseFloat(balanceBefore).toFixed(2) + ' days';
    document.getElementById('transactionBalanceAfter').textContent = parseFloat(balanceAfter).toFixed(2) + ' days';
    document.getElementById('transactionDate').textContent = date;
    document.getElementById('transactionReference').textContent = reference;
    document.getElementById('transactionProcessedBy').textContent = processedBy || 'System';
    document.getElementById('transactionRemarks').textContent = remarks || 'No remarks provided';

    document.getElementById('transactionDetailModal').style.display = 'flex';
}

window.closeTransactionDetailModal = function() {
    document.getElementById('transactionDetailModal').style.display = 'none';
}

window.toggleTransactionActionMenu = function(event, btn) {
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

window.openEditTransactionModal = function(id, amount, type, date, remarks) {
    document.getElementById('editTransactionForm').action = `/admin/leave/transactions/${id}`;
    document.getElementById('editTransactionType').value = type;
    document.getElementById('editTransactionAmount').value = amount;
    document.getElementById('editTransactionDate').value = date;
    document.getElementById('editTransactionRemarks').value = remarks;
    document.getElementById('editTransactionModal').style.display = 'flex';
}

window.closeEditTransactionModal = function(event) {
    if (!event || event.target.id === 'editTransactionModal') {
        document.getElementById('editTransactionModal').style.display = 'none';
    }
}
