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

window.disapproveOrder = function(id) {
    const reason = prompt('Reason for disapproval:');
    if (!reason) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/travelorder/${id}/disapprove`;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    form.innerHTML = `<input type="hidden" name="_token" value="${token}"><input type="hidden" name="reason" value="${reason}">`;
    document.body.appendChild(form);
    form.submit();
}

document.addEventListener('click', () => {
    document.querySelectorAll('.travel-action-menu').forEach(m => m.style.display = 'none');
});
