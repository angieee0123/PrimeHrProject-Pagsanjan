// Payroll Register Pagination
window._payrollCurrentPage = 1;
window._payrollRowsPerPage = 10;

window.filterPayrollRegister = function () {
    const allRows = document.querySelectorAll('#payrollRegisterBody tr[data-id]');
    const filtered = [];

    allRows.forEach(row => {
        filtered.push(row);
    });

    window._payrollFilteredRows = filtered;
    window._payrollCurrentPage = 1;
    updatePayrollPagination();
};

window.updatePayrollPagination = function () {
    const rows = window._payrollFilteredRows || [];
    const total = rows.length;
    const perPage = window._payrollRowsPerPage;
    const totalPages = Math.ceil(total / perPage) || 1;
    const page = Math.min(window._payrollCurrentPage, totalPages);
    window._payrollCurrentPage = page;

    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, total);

    /*
        Each record is two rows: the record itself and the breakdown that
        unfolds beneath it. They have to be paged together — hiding only
        `tr[data-id]` would leave an expanded breakdown stranded on a page
        whose record is no longer shown.
    */
    const detailOf = (row) => row.dataset.rowKey
        ? document.querySelector(`.pr-detail-row[data-detail-for="${row.dataset.rowKey}"]`)
        : null;

    document.querySelectorAll('#payrollRegisterBody tr[data-id]').forEach(row => {
        row.style.display = 'none';
        const detail = detailOf(row);
        if (detail) detail.style.display = 'none';
    });

    rows.forEach((row, i) => {
        if (i < start || i >= end) return;
        row.style.display = '';
        const detail = detailOf(row);
        // `hidden` stays the record of whether it is expanded; display only
        // decides whether this page shows it at all.
        if (detail) detail.style.display = '';
    });

    document.getElementById('payrollRowStart').textContent = total ? start + 1 : 0;
    document.getElementById('payrollRowEnd').textContent = end;
    document.getElementById('payrollRowTotal').textContent = total;

    const controls = document.getElementById('payrollPaginationControls');
    if (totalPages <= 1) { controls.innerHTML = ''; return; }

    let html = '';
    const maxVisible = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    if (page > 1) html += '<button class="page-btn" onclick="goToPayrollPage(' + (page - 1) + ')">‹</button>';
    if (startPage > 1) {
        html += '<button class="page-btn" onclick="goToPayrollPage(1)">1</button>';
        if (startPage > 2) html += '<span class="pr-ellipsis">...</span>';
    }
    for (let i = startPage; i <= endPage; i++) {
        html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToPayrollPage(' + i + ')">' + i + '</button>';
    }
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span class="pr-ellipsis">...</span>';
        html += '<button class="page-btn" onclick="goToPayrollPage(' + totalPages + ')">' + totalPages + '</button>';
    }
    if (page < totalPages) html += '<button class="page-btn" onclick="goToPayrollPage(' + (page + 1) + ')">›</button>';

    controls.innerHTML = html;
};

window.goToPayrollPage = function (page) {
    window._payrollCurrentPage = page;
    updatePayrollPagination();
};

window.changePayrollRowsPerPage = function () {
    window._payrollRowsPerPage = parseInt(document.getElementById('payrollRowsPerPage').value) || 10;
    window._payrollCurrentPage = 1;
    updatePayrollPagination();
};

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('payrollRegisterBody')) {
        filterPayrollRegister();
    }
});
