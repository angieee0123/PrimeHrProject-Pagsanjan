window.changePendingRowsPerPage = function() {
    const perPage = document.getElementById('pendingRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', 'pending');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

window.filterPendingOrders = function() {
    const dept = document.getElementById('travelOrderFilterDept').value;
    const mode = document.getElementById('travelOrderFilterMode').value;
    const dateFrom = document.getElementById('travelOrderFilterDateFrom').value;
    const dateTo = document.getElementById('travelOrderFilterDateTo').value;
    document.querySelectorAll('.pending-order-row').forEach(row => {
        const matchDept = dept === 'all' || row.dataset.department === dept;
        const matchMode = mode === 'all' || row.dataset.mode === mode;
        const matchDateFrom = !dateFrom || row.dataset.travelDate >= dateFrom;
        const matchDateTo = !dateTo || row.dataset.travelDate <= dateTo;
        row.style.display = matchDept && matchMode && matchDateFrom && matchDateTo ? '' : 'none';
    });
}

window.toggleTravelActionMenu = function(event, btn) {
    event.stopPropagation();
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.travel-action-menu').forEach(m => { if (m !== menu) m.style.display = 'none'; });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// NOTE: disapproveOrder() lived here and asked for the reason with prompt() —
// no label, no 500-character ceiling, and nothing naming the order being
// refused. Both decisions now open #travelDecisionModal instead; see
// travelDecisionModal.js.

document.addEventListener('click', () => {
    document.querySelectorAll('.travel-action-menu').forEach(m => m.style.display = 'none');
});
