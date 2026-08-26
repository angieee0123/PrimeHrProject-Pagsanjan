// Admin Attendance Dashboard — tabs, search, pagination, action menus

document.addEventListener('DOMContentLoaded', function() {
    const dashboard = document.querySelector('.attendance-dashboard');
    if (dashboard) {
        window.periodDisplay = dashboard.dataset.periodDisplay;
        window.periodDisplayFile = dashboard.dataset.periodDisplayFile;
    }
});

// Tab switching functionality
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');

    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById(tabName + '-tab').style.display = 'block';
    syncAttendanceExportButton(tabName);
}
window.switchTab = switchTab;

/**
 * The toolbar's Export button belongs to the Attendance Summary tab.
 *
 * It sits above all three tabs, but it exports the summary roll-up: the
 * Detailed Time Record tab has its own "Export All" for the day-by-day log,
 * and Attendance Config holds settings, not records. Left visible on those
 * two it meant either a second button downloading a different file with
 * nothing to tell them apart, or a button that exports a tab you are not
 * looking at.
 */
function syncAttendanceExportButton(tabName) {
    const btn = document.getElementById('attendanceExportBtn');
    if (btn) btn.style.display = tabName === 'summary' ? '' : 'none';
}

/**
 * Export the Attendance Summary for the filters currently in force.
 *
 * The filters are read out of the controls themselves rather than off the
 * rendered table, because the table is paginated -- the endpoint recomputes
 * every matching employee, not the ten currently on screen.
 *
 * "Every filter" is the point, and the toolbar form is not all of them: the
 * search box lives in the topbar, outside this form, and narrows the table
 * by name, employee ID or department. Sending only the form's four fields
 * meant a searched-down table of one employee exported the whole department
 * -- a file that contradicts the screen it was downloaded from.
 */
function exportAttendanceSummary(btn) {
    const url = btn?.dataset.exportUrl;
    if (!url) return;

    const form = document.getElementById('attendanceFilterForm');
    const params = new URLSearchParams();

    ['start_date', 'end_date', 'department', 'status'].forEach(name => {
        const value = form?.elements[name]?.value || '';
        if (value) params.set(name, value);
    });

    const search = document.getElementById('attendanceSearchInput')?.value.trim() || '';
    if (search) params.set('search', search);

    const query = params.toString();
    window.location.href = query ? url + '?' + query : url;
}
window.exportAttendanceSummary = exportAttendanceSummary;

// Check URL parameter and switch to correct tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');

    if (activeTab === 'detailed') {
        // Switch to detailed tab
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');

        document.querySelectorAll('.tab-btn')[1].classList.add('active');
        document.getElementById('detailed-tab').style.display = 'block';
        syncAttendanceExportButton('detailed');
    } else if (activeTab === 'settings') {
        // Switch to settings tab
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');

        document.querySelectorAll('.tab-btn')[2].classList.add('active');
        document.getElementById('settings-tab').style.display = 'block';
        syncAttendanceExportButton('settings');
    }
});

// Search functionality
function searchAttendance(query) {
    const searchTerm = query.toLowerCase().trim();
    const tbody = document.querySelector('.payroll-table tbody');
    if (!tbody) return;

    if (!window.allAttendanceRows || window.allAttendanceRows.length === 0) {
        window.allAttendanceRows = Array.from(tbody.querySelectorAll('tr'));
    }

    const filtered = window.allAttendanceRows.filter(row => {
        const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
        const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
        return searchTerm === '' || name.includes(searchTerm) || id.includes(searchTerm) || dept.includes(searchTerm);
    });

    tbody.innerHTML = '';
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align: center; padding: 40px; color: var(--gp-text-mid);">No records found matching your search.</td></tr>';
    } else {
        filtered.forEach(row => tbody.appendChild(row.cloneNode(true)));
    }
}
window.searchAttendance = searchAttendance;

// Attendance Summary Pagination
window._attendanceCurrentPage = 1;
window._attendanceRowsPerPage = 10;

window.filterAttendanceSummary = function () {
    const allRows = document.querySelectorAll('#attendanceSummaryBody tr[data-id]');
    const filtered = [];

    allRows.forEach(row => {
        filtered.push(row);
    });

    window._attendanceFilteredRows = filtered;
    window._attendanceCurrentPage = 1;
    updateAttendancePagination();
};

window.updateAttendancePagination = function () {
    const rows = window._attendanceFilteredRows || [];
    const total = rows.length;
    const perPage = window._attendanceRowsPerPage;
    const totalPages = Math.ceil(total / perPage) || 1;
    const page = Math.min(window._attendanceCurrentPage, totalPages);
    window._attendanceCurrentPage = page;

    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, total);

    document.querySelectorAll('#attendanceSummaryBody tr[data-id]').forEach(row => row.style.display = 'none');
    rows.forEach((row, i) => { if (i >= start && i < end) row.style.display = ''; });

    document.getElementById('attendanceRowStart').textContent = total ? start + 1 : 0;
    document.getElementById('attendanceRowEnd').textContent = end;
    document.getElementById('attendanceRowTotal').textContent = total;

    const controls = document.getElementById('attendancePaginationControls');
    if (totalPages <= 1) { controls.innerHTML = ''; return; }

    let html = '';
    const maxVisible = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    if (page > 1) html += '<button class="page-btn" onclick="goToAttendancePage(' + (page - 1) + ')">‹</button>';
    if (startPage > 1) {
        html += '<button class="page-btn" onclick="goToAttendancePage(1)">1</button>';
        if (startPage > 2) html += '<span style="padding:0 8px;color:var(--gp-text-soft);">...</span>';
    }
    for (let i = startPage; i <= endPage; i++) {
        html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToAttendancePage(' + i + ')">' + i + '</button>';
    }
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span style="padding:0 8px;color:var(--gp-text-soft);">...</span>';
        html += '<button class="page-btn" onclick="goToAttendancePage(' + totalPages + ')">' + totalPages + '</button>';
    }
    if (page < totalPages) html += '<button class="page-btn" onclick="goToAttendancePage(' + (page + 1) + ')">›</button>';

    controls.innerHTML = html;
};

window.goToAttendancePage = function (page) {
    window._attendanceCurrentPage = page;
    updateAttendancePagination();
};

window.changeAttendanceRowsPerPage = function () {
    window._attendanceRowsPerPage = parseInt(document.getElementById('attendanceRowsPerPage').value) || 10;
    window._attendanceCurrentPage = 1;
    updateAttendancePagination();
};

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    filterAttendanceSummary();
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.row-actions')) closeAllActionMenus();
    });
});

function toggleActionMenu(e, id) {
    e.stopPropagation();
    const menu = document.getElementById(id);
    const isOpen = menu.style.display === 'block';
    closeAllActionMenus();
    if (!isOpen) menu.style.display = 'block';
}
window.toggleActionMenu = toggleActionMenu;

function closeAllActionMenus() {
    document.querySelectorAll('.action-dropdown').forEach(m => m.style.display = 'none');
}
window.closeAllActionMenus = closeAllActionMenus;

// Global escape-key handler — closes whichever attendance modal is open
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeDTRModal();
        closeEditModal();
        closeDetailedDTRModal();
        closeCorrectModal();
    }
});
