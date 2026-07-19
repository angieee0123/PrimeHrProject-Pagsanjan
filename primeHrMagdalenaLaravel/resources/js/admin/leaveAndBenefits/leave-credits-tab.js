// Leave Credits Tab
let leaveCreditsCurrentPage = 1;
let leaveCreditsRowsPerPage = 10;

window.applyDateRangeFilter = function() {
    const dateFrom = document.getElementById('filterCreditsDateFrom').value;
    const dateTo = document.getElementById('filterCreditsDateTo').value;
    const url = new URL(window.location.href);

    if (!dateFrom || !dateTo) {
        alert('Please select both start and end dates');
        return;
    }

    if (new Date(dateFrom) > new Date(dateTo)) {
        alert('Start date must be before or equal to end date');
        return;
    }

    url.searchParams.set('filter_credits_date_from', dateFrom);
    url.searchParams.set('filter_credits_date_to', dateTo);
    url.searchParams.delete('filter_credits_year');
    url.searchParams.set('tab', 'leave-credits');

    window.location.href = url.toString();
}

window.clearDateRangeFilter = function() {
    const url = new URL(window.location.href);
    url.searchParams.delete('filter_credits_date_from');
    url.searchParams.delete('filter_credits_date_to');
    url.searchParams.set('tab', 'leave-credits');
    window.location.href = url.toString();
}

window.applyYearFilter = function() {
    const year = document.getElementById('filterCreditsYear').value;
    const url = new URL(window.location.href);

    url.searchParams.delete('filter_credits_date_from');
    url.searchParams.delete('filter_credits_date_to');
    url.searchParams.set('tab', 'leave-credits');

    if (year) {
        url.searchParams.set('filter_credits_year', year);
    } else {
        url.searchParams.delete('filter_credits_year');
    }

    window.location.href = url.toString();
}

window.changeLeaveCreditsRowsPerPage = function() {
    const value = document.getElementById('leaveCreditsRowsPerPage').value;
    leaveCreditsRowsPerPage = value === 'all' ? 999999 : parseInt(value);
    leaveCreditsCurrentPage = 1;
    renderLeaveCreditsPage();
}

window.applyCreditsFilters = function() {
    const employeeId = document.getElementById('filterCreditsEmployee').value;
    const leaveCode = document.getElementById('filterCreditsLeaveType').value;
    const type = document.getElementById('filterCreditsType').value;
    const rows = document.querySelectorAll('.leave-credits-row');

    rows.forEach(row => {
        const rowEmployeeId = row.getAttribute('data-employee-id');
        const rowLeaveCode = row.getAttribute('data-leave-code');
        const rowType = row.getAttribute('data-type');

        const matchEmployee = !employeeId || rowEmployeeId === employeeId;
        const matchLeaveType = !leaveCode || rowLeaveCode === leaveCode;
        const matchType = !type || rowType === type;

        if (matchEmployee && matchLeaveType && matchType) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    leaveCreditsCurrentPage = 1;
    renderLeaveCreditsPage();
}

window.renderLeaveCreditsPage = function() {
    const tbody = document.getElementById('leaveCreditsTableBody');
    const allRows = Array.from(tbody.querySelectorAll('.leave-credits-row'));
    const visibleRows = allRows.filter(row => row.style.display !== 'none');

    const totalRows = visibleRows.length;
    const totalPages = Math.ceil(totalRows / leaveCreditsRowsPerPage);
    const start = (leaveCreditsCurrentPage - 1) * leaveCreditsRowsPerPage;
    const end = start + leaveCreditsRowsPerPage;

    allRows.forEach(row => row.classList.add('hidden-by-pagination'));

    visibleRows.forEach((row, index) => {
        if (index >= start && index < end) {
            row.classList.remove('hidden-by-pagination');
        }
    });

    document.getElementById('leaveCreditsRowStart').textContent = totalRows > 0 ? start + 1 : 0;
    document.getElementById('leaveCreditsRowEnd').textContent = Math.min(end, totalRows);
    document.getElementById('leaveCreditsRowTotal').textContent = totalRows;

    const paginationContainer = document.getElementById('leaveCreditsPagination');
    if (!paginationContainer) return;

    let html = '';

    if (leaveCreditsCurrentPage > 1) {
        html += `<button class="page-btn" onclick="changeLeaveCreditsPage(${leaveCreditsCurrentPage - 1})">‹</button>`;
    } else {
        html += `<button class="page-btn" disabled>‹</button>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        if (i === leaveCreditsCurrentPage) {
            html += `<button class="page-btn active">${i}</button>`;
        } else {
            html += `<button class="page-btn" onclick="changeLeaveCreditsPage(${i})">${i}</button>`;
        }
    }

    if (leaveCreditsCurrentPage < totalPages) {
        html += `<button class="page-btn" onclick="changeLeaveCreditsPage(${leaveCreditsCurrentPage + 1})">›</button>`;
    } else {
        html += `<button class="page-btn" disabled>›</button>`;
    }

    paginationContainer.innerHTML = html;
}

window.changeLeaveCreditsPage = function(page) {
    leaveCreditsCurrentPage = page;
    renderLeaveCreditsPage();
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('leave-credits-tab')) {
        renderLeaveCreditsPage();
    }
});
