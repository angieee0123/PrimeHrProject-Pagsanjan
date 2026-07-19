// CSC Daily Accrual Tab

window.changeAccrualRowsPerPage = function() {
    const perPage = document.getElementById('accrualRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('accrual_per_page', perPage);
    url.searchParams.set('tab', 'accrual');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

window.filterAccrualRates = function() {
    const statusFilter = document.getElementById('filterAccrualStatus').value;
    const frequencyFilter = document.getElementById('filterAccrualFrequency').value;
    const rows = document.querySelectorAll('.accrual-rate-row');

    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const frequency = row.getAttribute('data-frequency');

        const matchesStatus = statusFilter === 'all' || status === statusFilter;
        const matchesFrequency = frequencyFilter === 'all' || frequency === frequencyFilter;

        if (matchesStatus && matchesFrequency) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.navigateToAccrualPage = function(url) {
    const urlObj = new URL(url, window.location.origin);
    urlObj.searchParams.set('tab', 'accrual');
    window.location.href = urlObj.toString();
}

window.viewAccrualRate = function(id) {
    alert('View Accrual Rate #' + id + ' - To be implemented');
}

window.editAccrualRate = function(id) {
    alert('Edit Accrual Rate #' + id + ' - To be implemented');
}
