// Mayor Travel Order — read-only mirror of the admin travel order page.
//
// The detail modal lives in its own module (mayorTravelOrderDetail.js), shipped
// by the modal partial itself so the Leave & Travel Calendar can open the same
// modal without any of the table behaviour below.

// ── Tab switching ──
window.switchTab = function (tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.table-section').forEach(section => section.style.display = 'none');

    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').style.display = 'block';
};

window.navigateToPage = function (url) {
    window.location.href = url;
};

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');

    if (activeTab === 'approved') {
        switchTab('approved');
    } else if (activeTab === 'disapproved') {
        switchTab('disapproved');
    } else {
        document.getElementById('pending-tab').style.display = 'block';
    }
});

// ── Filtering (department / mode / travel-date range) ──
function filterOrders(rowSelector) {
    const dept = document.getElementById('travelOrderFilterDept').value;
    const mode = document.getElementById('travelOrderFilterMode').value;
    const dateFrom = document.getElementById('travelOrderFilterDateFrom').value;
    const dateTo = document.getElementById('travelOrderFilterDateTo').value;
    document.querySelectorAll(rowSelector).forEach(row => {
        const matchDept = dept === 'all' || row.dataset.department === dept;
        const matchMode = mode === 'all' || row.dataset.mode === mode;
        const matchDateFrom = !dateFrom || row.dataset.travelDate >= dateFrom;
        const matchDateTo = !dateTo || row.dataset.travelDate <= dateTo;
        row.style.display = matchDept && matchMode && matchDateFrom && matchDateTo ? '' : 'none';
    });
}

window.filterPendingOrders = function () { filterOrders('.pending-order-row'); };
window.filterApprovedOrders = function () { filterOrders('.approved-order-row'); };
window.filterDisapprovedOrders = function () { filterOrders('.disapproved-order-row'); };

// ── Rows-per-page ──
function changeRowsPerPage(selectId, tab) {
    const perPage = document.getElementById(selectId).value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', tab);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

window.changePendingRowsPerPage = function () { changeRowsPerPage('pendingRowsPerPage', 'pending'); };
window.changeApprovedRowsPerPage = function () { changeRowsPerPage('approvedRowsPerPage', 'approved'); };
window.changeDisapprovedRowsPerPage = function () { changeRowsPerPage('disapprovedRowsPerPage', 'disapproved'); };
