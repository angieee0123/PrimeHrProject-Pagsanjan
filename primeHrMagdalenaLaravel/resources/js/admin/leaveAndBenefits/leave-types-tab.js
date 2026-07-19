// Leave Types Tab

window.searchLeaveTypes = function() {
    const searchValue = document.getElementById('searchLeaveTypes').value.toLowerCase();
    const rows = document.querySelectorAll('.leave-type-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchValue)) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

window.filterLeaveTypes = function() {
    const statusFilter = document.getElementById('filterLeaveTypeStatus').value;
    const accrualFilter = document.getElementById('filterLeaveTypeAccrual').value;
    const rows = document.querySelectorAll('.leave-type-row');

    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const accrual = row.getAttribute('data-accrual');

        let showRow = true;

        if (statusFilter !== 'all' && status !== statusFilter) {
            showRow = false;
        }

        if (accrualFilter !== 'all' && accrual !== accrualFilter) {
            showRow = false;
        }

        if (showRow) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

window.sortLeaveTypes = function(column) {
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort_by');
    const currentOrder = urlParams.get('sort_order') || 'asc';

    let newOrder = 'asc';
    if (currentSort === column && currentOrder === 'asc') {
        newOrder = 'desc';
    }

    urlParams.set('sort_by', column);
    urlParams.set('sort_order', newOrder);
    urlParams.set('tab', 'types');

    window.location.search = urlParams.toString();
}

window.changePerPage = function(perPage) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('per_page', perPage);
    urlParams.delete('page');
    urlParams.set('tab', 'types');

    window.location.search = urlParams.toString();
}

window.navigateToPage = function(url) {
    const urlObj = new URL(url, window.location.origin);
    urlObj.searchParams.set('tab', 'types');
    window.location.href = urlObj.toString();
}

window.changeLeaveTypesRowsPerPage = function() {
    const perPage = document.getElementById('leaveTypesRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', 'types');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

window.toggleLeaveTypeActionMenu = function(event, btn) {
    event.stopPropagation();
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.lt-action-menu').forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', () => {
    document.querySelectorAll('.lt-action-menu').forEach(m => m.style.display = 'none');
});
