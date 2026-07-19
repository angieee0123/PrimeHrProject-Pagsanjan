window.changeApprovedRowsPerPage = function() {
    const perPage = document.getElementById('approvedRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', 'approved');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

window.filterApprovedOrders = function() {
    const dept = document.getElementById('travelOrderFilterDept').value;
    const mode = document.getElementById('travelOrderFilterMode').value;
    const dateFrom = document.getElementById('travelOrderFilterDateFrom').value;
    const dateTo = document.getElementById('travelOrderFilterDateTo').value;
    document.querySelectorAll('.approved-order-row').forEach(row => {
        const matchDept = dept === 'all' || row.dataset.department === dept;
        const matchMode = mode === 'all' || row.dataset.mode === mode;
        const matchDateFrom = !dateFrom || row.dataset.travelDate >= dateFrom;
        const matchDateTo = !dateTo || row.dataset.travelDate <= dateTo;
        row.style.display = matchDept && matchMode && matchDateFrom && matchDateTo ? '' : 'none';
    });
}
