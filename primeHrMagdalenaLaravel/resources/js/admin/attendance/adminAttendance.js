// Admin Attendance Dashboard — tabs, search, pagination, action menus

document.addEventListener('DOMContentLoaded', function() {
    const dashboard = document.querySelector('.attendance-dashboard');
    if (dashboard) {
        window.periodDisplay = dashboard.dataset.periodDisplay;
        window.periodDisplayFile = dashboard.dataset.periodDisplayFile;
    }
});

// Tab switching functionality.
//
// The inline handlers pass only the tab name, so the button is normally found
// through the click event; `data-tab` is the fallback for switching without a
// click (the topbar search brings the Summary tab forward). `window.event` is a
// legacy global and is undefined outside a real dispatch, hence the guards.
function switchTab(tabName, trigger) {
    const btn = (trigger && trigger.closest ? trigger.closest('.tab-btn') : null)
        || (window.event && window.event.target && window.event.target.closest ? window.event.target.closest('.tab-btn') : null)
        || document.querySelector('.tab-btn[data-tab="' + tabName + '"]');

    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');

    if (btn) btn.classList.add('active');
    const tab = document.getElementById(tabName + '-tab');
    if (tab) tab.style.display = 'block';
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
 * rendered table, because the table is paged in the browser -- the endpoint
 * recomputes every matching employee, not the page currently on screen.
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
    const activeTab = new URLSearchParams(window.location.search).get('tab');

    if (activeTab === 'detailed' || activeTab === 'settings') {
        switchTab(activeTab);
    }
});

/**
 * Topbar search over the Attendance Summary table.
 *
 * The three fields it matches -- name, employee ID, department -- are the same
 * three the Summary export searches, so a download and the table it came from
 * agree on who the search left.
 *
 * It no longer rebuilds the table body. Doing that with cloned rows detached
 * every row the pagination below holds a reference to, so typing anything and
 * then changing page or page size emptied the table; the term is recorded
 * instead and the pagination shows and hides the rows in place.
 */
function searchAttendance(query) {
    window._attendanceSearchTerm = query;
    filterAttendanceSummary();

    // Searched while another tab is open, the matching rows would be off
    // screen -- the box filters the Summary table, so bring it forward.
    const summaryBtn = document.querySelector('.tab-btn[data-tab="summary"]');
    if (String(query).trim() !== '' && summaryBtn && !summaryBtn.classList.contains('active')) {
        switchTab('summary');
    }
}
window.searchAttendance = searchAttendance;

// Attendance Summary Pagination
//
// The table is sent whole (AttendanceController::index builds the roll-up for
// every employee the filters matched) and paged here, so the page count, the
// footer figures and the "per page" select all describe the full roster rather
// than the dozen rows the controller used to send.
window._attendanceCurrentPage = 1;
window._attendanceRowsPerPage = 10;
window._attendanceSearchTerm = '';

function attendanceSummaryMatches(row, term) {
    if (!term) return true;

    return ['.emp-name', '.emp-id', '.dept-tag'].some(selector => {
        const cell = row.querySelector(selector);
        return (cell ? cell.textContent : '').toLowerCase().includes(term);
    });
}

window.filterAttendanceSummary = function () {
    const term = (window._attendanceSearchTerm || '').trim().toLowerCase();

    window._attendanceFilteredRows = Array.from(
        document.querySelectorAll('#attendanceSummaryBody tr[data-id]')
    ).filter(row => attendanceSummaryMatches(row, term));

    window._attendanceCurrentPage = 1;
    updateAttendancePagination();
};

/**
 * The "nothing matched" row. The partial renders it only when the period itself
 * holds nobody; a search or filter that matches nothing gets it created here,
 * once -- rows are never rebuilt with innerHTML, because the paging state and
 * the row action menus point at these nodes.
 */
function renderAttendanceNoResults(show) {
    const body = document.getElementById('attendanceSummaryBody');
    if (!body) return;

    const existing = document.getElementById('attendanceSummaryNoResults');

    if (show) {
        if (existing) return;

        const emptyRow = document.createElement('tr');
        emptyRow.id = 'attendanceSummaryNoResults';
        emptyRow.className = 'attendance-empty-row';
        emptyRow.innerHTML = '<td colspan="11">No employee matches the search and filters currently applied.</td>';
        body.appendChild(emptyRow);
    } else if (existing) {
        existing.remove();
    }
}

window.updateAttendancePagination = function () {
    const rows = window._attendanceFilteredRows || [];
    const total = rows.length;
    const perPage = window._attendanceRowsPerPage || 10;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    const page = Math.min(Math.max(1, window._attendanceCurrentPage), totalPages);
    window._attendanceCurrentPage = page;

    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, total);

    // Hide every row the table holds -- including ones a larger page size
    // revealed -- then show this page's slice of the filtered list.
    document.querySelectorAll('#attendanceSummaryBody tr[data-id]').forEach(row => row.style.display = 'none');
    rows.forEach((row, i) => { if (i >= start && i < end) row.style.display = ''; });

    renderAttendanceNoResults(total === 0);

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
    const select = document.getElementById('attendanceRowsPerPage');
    window._attendanceRowsPerPage = parseInt(select ? select.value : '', 10) || 10;
    window._attendanceCurrentPage = 1;
    updateAttendancePagination();
};

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    // The select is the only place the page size is stated, so read it rather
    // than keeping a second copy of "10" here.
    const perPageSelect = document.getElementById('attendanceRowsPerPage');
    if (perPageSelect) {
        window._attendanceRowsPerPage = parseInt(perPageSelect.value, 10) || 10;
    }

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
