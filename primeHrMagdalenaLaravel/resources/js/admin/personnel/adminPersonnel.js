// Admin Personnel Page Scripts

// Fill in (or hide) the "Verification email sent" panel in the success modal.
//
// Shared by the wizard's flash and the bulk import's JSON response so the two
// cannot describe the same pair of emails differently — one of them going
// quiet is how an admin ends up not knowing a link was sent at all.
//
// textContent throughout, for the same reason as the error list below: the
// address is a value the admin typed into the wizard one screen ago, and
// building this with innerHTML would make the registration form an XSS vector
// against whoever submits it.
function renderEmailNotice(notice) {
    const box = document.getElementById('successEmailNotice');
    if (!box) return;

    if (!notice) {
        box.hidden = true;
        return;
    }

    const failed = notice.status === 'failed';
    const address = document.getElementById('successEmailNoticeAddress');

    box.classList.toggle('is-failed', failed);

    document.getElementById('successEmailNoticeTitle').textContent = notice.title || (failed
        ? 'Email could not be sent'
        : 'Verification email sent');

    // Bulk import has no single address to read back, so the line is dropped
    // rather than filled with a stand-in.
    address.textContent = notice.email || '';
    address.hidden = !notice.email;

    document.getElementById('successEmailNoticeText').textContent = notice.text || (failed
        ? 'The account was created, but neither the verification link nor the credentials '
          + 'reached this address. '
          + (notice.reason || 'Check the mail settings, then have them use "Forgot password" to get in.')
        // Both messages are named because two arriving together is what the
        // employee will ask about, and the order matters: every area sits
        // behind EnsureEmailIsVerifiedForArea, so the credentials do not work
        // until the link is opened.
        : 'A second email carries their username and password. They must open the '
          + 'verification link before they can sign in.');

    box.hidden = false;
}

// Session flash handling — window.personnelFlash is set by an inline script
// in adminPersonnel.blade.php
// (@json(session('success' | 'warning' | 'error' | 'active_tab'))).
document.addEventListener('DOMContentLoaded', function() {
    const flash = window.personnelFlash || {};

    // `warning` is the employee-was-created-but-the-email-failed case, so it
    // takes the success path: the record exists, and only the wording differs.
    // Routing it to the error modal would suggest nothing had been saved.
    const created = flash.success || flash.warning;

    if (created) {
        document.getElementById('successMessage').textContent = created;
        document.getElementById('successModal').style.display = 'flex';
        if (document.getElementById('employeeWizardModal')) {
            document.getElementById('employeeWizardModal').style.display = 'none';
        }
        // A successful add/update means there's no draft left to resume.
        if (window.clearWizardDraft) window.clearWizardDraft();
    }

    // What was emailed to the new account. Only registration flashes this, so
    // the panel stays hidden for the other actions that open this modal —
    // schedule assignment sends nothing and must not claim to.
    renderEmailNotice(flash.emailNotice);

    if (flash.error) {
        document.getElementById('errorMessage').textContent = flash.error;

        // Every rejected field, grouped under the wizard step that owns it.
        // textContent throughout: these strings carry values the admin typed,
        // so building this with innerHTML would make the registration form an
        // XSS vector against whoever submits it.
        const list = document.getElementById('errorDetails');
        if (list) {
            list.textContent = '';
            const details = flash.errorDetails || [];

            details.forEach(function (detail) {
                const item = document.createElement('li');

                if (detail.step) {
                    const badge = document.createElement('span');
                    badge.className = 'personnel-modal-error-step';
                    badge.textContent = 'Step ' + detail.step + ' \u00b7 ' + detail.step_name;
                    item.appendChild(badge);
                }

                const text = document.createElement('span');
                text.textContent = detail.message;
                item.appendChild(text);

                list.appendChild(item);
            });

            list.hidden = details.length === 0;
        }

        document.getElementById('errorModal').style.display = 'flex';
    }

    if (flash.activeTab === 'schedules') {
        showPersonnelTab('schedules');
    }
});

/**
 * Show one tab of the Personnel page.
 *
 * The filter toolbar is hidden on Work Schedules: its fields filter employee
 * records, and its Export button downloads the masterlist. Left on screen it
 * sat next to the Work Schedules Export button -- two buttons, two different
 * files, nothing on either saying which.
 */
function showPersonnelTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    const btn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    const content = document.getElementById(tab);
    if (!btn || !content) return;

    btn.classList.add('active');
    content.classList.add('active');

    const toolbar = document.getElementById('personnelFilterToolbar');
    if (toolbar) toolbar.style.display = tab === 'employees' ? '' : 'none';
}

// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            showPersonnelTab(this.dataset.tab);
        });
    });
});

// Pagination and Sorting
let currentPage = 1;
let rowsPerPage = 10;
let sortColumn = -1;
let sortAscending = true;
let allRows = [];

// Column index -> the sort key PersonnelExportController understands, so the
// CSV comes back in the order the table is being read in. Indexes match the
// <th> order in employee-records-tab.blade.php; the Actions column is not
// sortable and has no key.
const SORT_KEYS = ['name', 'position', 'department', 'type', 'appointed', 'status'];

document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('personnelTableBody');
    if (tbody) {
        allRows = Array.from(tbody.querySelectorAll('tr'));
        window.allRows = allRows; // Make it globally accessible for search
        updatePagination();
        displayPage(currentPage);
    }
});

function sortTable(columnIndex) {
    if (sortColumn === columnIndex) {
        sortAscending = !sortAscending;
    } else {
        sortColumn = columnIndex;
        sortAscending = true;
    }

    allRows.sort((a, b) => {
        let aValue, bValue;

        if (columnIndex === 0) {
            aValue = a.querySelector('.emp-name').textContent.trim();
            bValue = b.querySelector('.emp-name').textContent.trim();
        } else if (columnIndex === 1) {
            aValue = a.querySelector('.position-cell').textContent.trim();
            bValue = b.querySelector('.position-cell').textContent.trim();
        } else if (columnIndex === 2) {
            aValue = a.querySelector('.dept-tag').textContent.trim();
            bValue = b.querySelector('.dept-tag').textContent.trim();
        } else if (columnIndex === 3) {
            aValue = a.querySelector('.badge-emptype').textContent.trim();
            bValue = b.querySelector('.badge-emptype').textContent.trim();
        } else if (columnIndex === 4) {
            aValue = a.cells[4].textContent.trim();
            bValue = b.cells[4].textContent.trim();
        } else if (columnIndex === 5) {
            aValue = a.querySelector('.badge-status').textContent.trim();
            bValue = b.querySelector('.badge-status').textContent.trim();
        }

        if (aValue < bValue) return sortAscending ? -1 : 1;
        if (aValue > bValue) return sortAscending ? 1 : -1;
        return 0;
    });

    const headers = document.querySelectorAll('#personnelTable th');
    headers.forEach((header, index) => {
        const svg = header.querySelector('svg');
        if (svg) {
            if (index === columnIndex) {
                svg.style.transform = sortAscending ? 'rotate(0deg)' : 'rotate(180deg)';
                svg.style.opacity = '1';
            } else {
                svg.style.transform = 'rotate(0deg)';
                svg.style.opacity = '0.3';
            }
        }
    });

    // Re-applies the filters rather than paging the whole roster: sorting used
    // to fall through to displayPage(), which slices allRows and so put the
    // hidden rows back into the page's slice -- a filtered table came back
    // showing four of its ten rows and a footer counting all fourteen.
    refreshPersonnelRows();
}

// The empty state uses the same shell the Blade partial renders when there are
// no employees at all, so a filter that matches nobody does not drop to a bare
// line of text in the middle of an otherwise styled table.
function emptyRow(title, text) {
    return '<tr class="prs-empty-row"><td colspan="7"><div class="prs-empty">'
        + '<p class="prs-empty-title">' + title + '</p>'
        + '<p class="prs-empty-text">' + text + '</p>'
        + '</div></td></tr>';
}

// "Showing 1-10 of 14" in the footer and "N of 14 records" in the heading are
// the same figure; the heading used to print the untouched total, so a search
// left it claiming the whole roster was still on screen.
function setShownCount(count) {
    const shown = document.getElementById('recordsShown');
    if (shown) shown.textContent = count;
}

function displayPage(page) {
    const tbody = document.getElementById('personnelTableBody');
    tbody.innerHTML = '';

    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageRows = allRows.slice(start, end);

    if (pageRows.length === 0) {
        tbody.innerHTML = emptyRow('No employees on record', 'Use “Add Employee” to register new personnel, or Bulk Import to bring in an existing roster.');
    } else {
        pageRows.forEach(row => tbody.appendChild(row));
    }

    document.getElementById('showingStart').textContent = start + 1;
    document.getElementById('showingEnd').textContent = Math.min(end, allRows.length);
    document.getElementById('totalRecords').textContent = allRows.length;
    setShownCount(allRows.length);

    updatePaginationButtons();
}

function updatePagination() {
    updatePaginationButtons();
}

function updatePaginationButtons() {
    const totalPages = Math.ceil(allRows.length / rowsPerPage);
    const paginationControls = document.getElementById('paginationControls');
    paginationControls.innerHTML = '';

    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.textContent = '‹';
        prevBtn.onclick = () => changePage(currentPage - 1);
        paginationControls.appendChild(prevBtn);
    }

    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);

    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.className = 'page-btn' + (i === currentPage ? ' active' : '');
        pageBtn.textContent = i;
        pageBtn.onclick = () => changePage(i);
        paginationControls.appendChild(pageBtn);
    }

    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.textContent = '›';
        nextBtn.onclick = () => changePage(currentPage + 1);
        paginationControls.appendChild(nextBtn);
    }
}

function changePage(page) {
    currentPage = page;
    displayPage(currentPage);
}

function changeRowsPerPage(value) {
    if (value === 'all') {
        rowsPerPage = allRows.length;
    } else {
        rowsPerPage = parseInt(value);
    }
    currentPage = 1;
    displayPage(currentPage);
}

/**
 * Everything the Personnel page is currently filtered by, read off the
 * controls themselves.
 *
 * One reader for one set of criteria: the toolbar, the topbar search box and
 * the Export button all describe the same view, and this is what keeps the
 * downloaded file from being built from a different set of them than the
 * table on screen.
 */
function personnelFilterState() {
    return {
        department: document.getElementById('departmentFilter')?.value || '',
        status: document.getElementById('statusFilter')?.value || '',
        hiredFrom: document.getElementById('hiredDateFrom')?.value || '',
        hiredTo: document.getElementById('hiredDateTo')?.value || '',
        search: (document.getElementById('personnelSearchInput')?.value || '').trim(),
    };
}

/**
 * The one predicate that decides whether a row is on screen.
 *
 * The toolbar filters and the search box used to each own a pass over the
 * rows, and each pass wrote `row.style.display` from scratch -- so whichever
 * ran last silently undid the other, and typing in the search box brought back
 * rows the department filter had just hidden. The export then sent both sets
 * of criteria to the server and got a narrower file than the screen showed.
 * They compose here instead, which is both what the toolbar reads as and what
 * PersonnelExportController applies.
 *
 * Department is compared exactly, not as a substring: the select's value is
 * the department's name verbatim, and `includes()` let "Accounting" match
 * "Accounting and Budget Office".
 */
function matchesPersonnelFilters(row, state) {
    if (state.department) {
        const deptText = row.querySelector('.dept-tag')?.textContent.trim() || '';
        if (deptText !== state.department) return false;
    }

    if (state.status) {
        const statusText = row.querySelector('.badge-status')?.textContent.trim() || '';
        if (statusText !== state.status) return false;
    }

    // A row with no appointment date on file cannot satisfy a date window.
    if (state.hiredFrom && (!row.dataset.hired || row.dataset.hired < state.hiredFrom)) return false;
    if (state.hiredTo && (!row.dataset.hired || row.dataset.hired > state.hiredTo)) return false;

    if (state.search) {
        const term = state.search.toLowerCase();
        const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
        const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const position = row.querySelector('.position-cell')?.textContent.toLowerCase() || '';
        if (!name.includes(term) && !id.includes(term) && !position.includes(term)) return false;
    }

    return true;
}

/** Re-run the predicate over every row and repaint the first page. */
function refreshPersonnelRows() {
    const state = personnelFilterState();
    const rows = window.allRows || allRows;

    rows.forEach(row => {
        row.style.display = matchesPersonnelFilters(row, state) ? '' : 'none';
    });

    currentPage = 1;
    displayFilteredPage(rows.filter(row => row.style.display !== 'none'));
}

function applyFilters() {
    refreshPersonnelRows();
}

function displayFilteredPage(visibleRows) {
    const tbody = document.getElementById('personnelTableBody');
    tbody.innerHTML = '';

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageRows = visibleRows.slice(start, end);

    if (pageRows.length === 0) {
        tbody.innerHTML = emptyRow('No matching employees', 'No record matches the current search and filters. Clear them to see the full roster.');
    } else {
        pageRows.forEach(row => tbody.appendChild(row));
    }

    document.getElementById('showingStart').textContent = visibleRows.length > 0 ? start + 1 : 0;
    document.getElementById('showingEnd').textContent = Math.min(end, visibleRows.length);
    document.getElementById('totalRecords').textContent = visibleRows.length;
    setShownCount(visibleRows.length);

    updateFilteredPaginationButtons(visibleRows);
}

function updateFilteredPaginationButtons(visibleRows) {
    const totalPages = Math.ceil(visibleRows.length / rowsPerPage);
    const paginationControls = document.getElementById('paginationControls');
    paginationControls.innerHTML = '';

    if (totalPages <= 1) return;

    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.textContent = '‹';
        prevBtn.onclick = () => { currentPage--; displayFilteredPage(visibleRows); };
        paginationControls.appendChild(prevBtn);
    }

    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);

    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.className = 'page-btn' + (i === currentPage ? ' active' : '');
        pageBtn.textContent = i;
        const pageNum = i;
        pageBtn.onclick = () => { currentPage = pageNum; displayFilteredPage(visibleRows); };
        paginationControls.appendChild(pageBtn);
    }

    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.textContent = '›';
        nextBtn.onclick = () => { currentPage++; displayFilteredPage(visibleRows); };
        paginationControls.appendChild(nextBtn);
    }
}

// The search box is one of the criteria in personnelFilterState(), which is
// read fresh on every pass -- so searching narrows what the toolbar left on
// screen rather than replacing it.
function searchPersonnel() {
    refreshPersonnelRows();
}

/**
 * Export the roster as CSV.
 *
 * This used to build the file in the browser by reading the rendered table,
 * which capped it at the seven columns the table shows and produced a bare
 * grid with no title, no municipality and no record of which filters made it.
 * The file is now built by PersonnelExportController, which reads the records
 * themselves; this function's job is only to hand it the toolbar's filters.
 */
function exportTableData(btn) {
    try {
        // The button is passed in rather than looked up: the Work Schedules tab
        // carries its own [data-export-url], and a bare querySelector would
        // send the roster's filters to whichever of the two the DOM happens to
        // list first.
        const source = btn || document.querySelector('#personnelFilterToolbar [data-export-url]');
        const exportUrl = source?.dataset.exportUrl;

        if (!exportUrl) {
            throw new Error('Export endpoint is unavailable');
        }

        const visibleRows = (window.allRows || allRows).filter(row => row.style.display !== 'none');

        if (visibleRows.length === 0) {
            document.getElementById('exportErrorMessage').textContent =
                'No employee records match the current filters, so there is nothing to export.';
            document.getElementById('exportErrorModal').style.display = 'flex';
            return;
        }

        // The same state the rows on screen were filtered by, so the file the
        // server builds is the view that is being looked at. Only a criterion
        // that is actually set is sent, so the CSV's parameter block reads
        // "All Departments" rather than an empty cell.
        const state = personnelFilterState();
        const params = new URLSearchParams();

        if (state.department) params.set('department', state.department);
        if (state.status) params.set('status', state.status);
        if (state.hiredFrom) params.set('hired_from', state.hiredFrom);
        if (state.hiredTo) params.set('hired_to', state.hiredTo);
        if (state.search) params.set('search', state.search);

        // The column the table is sorted on, so the file lists the records in
        // the order they are being read in.
        const sortKey = SORT_KEYS[sortColumn];
        if (sortKey) {
            params.set('sort', sortKey);
            params.set('dir', sortAscending ? 'asc' : 'desc');
        }

        const query = params.toString();

        // The endpoint answers with a Content-Disposition attachment, so this
        // downloads the file without the page navigating away.
        window.location.href = query ? `${exportUrl}?${query}` : exportUrl;

        document.getElementById('exportSuccessMessage').textContent =
            `Exporting ${visibleRows.length} employee record${visibleRows.length === 1 ? '' : 's'}. The CSV will download shortly.`;
        document.getElementById('exportSuccessModal').style.display = 'flex';

    } catch (error) {
        console.error('Export error:', error);
        document.getElementById('exportErrorMessage').textContent = `An error occurred while exporting: ${error.message || 'Unknown error'}. Please try again.`;
        document.getElementById('exportErrorModal').style.display = 'flex';
    }
}

function closeExportSuccessModal() {
    document.getElementById('exportSuccessModal').style.display = 'none';
}

function closeExportErrorModal() {
    document.getElementById('exportErrorModal').style.display = 'none';
}

// Modal Functions
function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
    location.reload();
}

function closeErrorModal() {
    document.getElementById('errorModal').style.display = 'none';
}

// Status Change Confirmation
let pendingStatusChange = null;

function confirmStatusChange(employeeId, newStatus) {
    pendingStatusChange = { employeeId, newStatus };
    
    const isActivating = newStatus === 'Active';
    const modal = document.getElementById('confirmModal');
    const iconWrap = document.getElementById('confirmIconWrap');
    const icon = document.getElementById('confirmIcon');
    const title = document.getElementById('confirmTitle');
    const message = document.getElementById('confirmMessage');
    const submitBtn = document.getElementById('confirmSubmitBtn');
    const input = document.getElementById('confirmInput');
    const error = document.getElementById('confirmError');
    
    if (isActivating) {
        iconWrap.style.background = 'var(--theme-success-subtle)';
        icon.style.stroke = 'var(--theme-success)';
        icon.innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
        title.textContent = 'Activate Employee Account';
        message.textContent = 'Are you sure you want to activate this employee account? The employee will be able to access the system.';
        submitBtn.style.background = 'var(--theme-success)';
    } else {
        iconWrap.style.background = '#fee8e8';
        icon.style.stroke = 'var(--theme-danger)';
        icon.innerHTML = '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>';
        title.textContent = 'Deactivate Employee Account';
        message.textContent = 'Are you sure you want to deactivate this employee account? The employee will no longer be able to access the system.';
        submitBtn.style.background = 'var(--theme-danger)';
    }
    
    input.value = '';
    error.style.display = 'none';
    modal.style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    pendingStatusChange = null;
}

function submitConfirmation() {
    const input = document.getElementById('confirmInput');
    const error = document.getElementById('confirmError');
    
    const typed = input.value.trim().replace(/\s+/g, ' ').toLowerCase();
    if (typed !== 'yes i confirm') {
        error.style.display = 'block';
        input.style.borderColor = 'var(--theme-danger)';
        return;
    }
    
    if (!pendingStatusChange) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/personnel/${pendingStatusChange.employeeId}/status`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = pendingStatusChange.newStatus;
    
    form.appendChild(csrfToken);
    form.appendChild(statusInput);
    document.body.appendChild(form);
    form.submit();
}

// View Employee modal logic lives in viewEmployeeModal.js, loaded by the
// viewEmployeeModal blade partial (shared with the admin dashboard).

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirmInput');
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            const confirmError = document.getElementById('confirmError');
            if (confirmError) {
                confirmError.style.display = 'none';
            }
            this.style.borderColor = '#e8e7f5';
        });
    }
});

// Make functions globally accessible
window.sortTable = sortTable;
window.displayPage = displayPage;
window.changePage = changePage;
window.changeRowsPerPage = changeRowsPerPage;
window.applyFilters = applyFilters;
window.searchPersonnel = searchPersonnel;
window.exportTableData = exportTableData;
window.closeExportSuccessModal = closeExportSuccessModal;
window.closeExportErrorModal = closeExportErrorModal;
window.closeSuccessModal = closeSuccessModal;
window.closeErrorModal = closeErrorModal;
window.confirmStatusChange = confirmStatusChange;
window.closeConfirmModal = closeConfirmModal;
window.submitConfirmation = submitConfirmation;
window.generateQRCode = generateQRCode;
window.closeQRModal = closeQRModal;
window.downloadQRCode = downloadQRCode;
window.printQRCode = printQRCode;

// QR Code Functions
let currentQRData = null;

/**
 * @param {string} payload  The signed badge string from Employee::$qr_payload.
 *   The badge used to encode a bare employee id, which meant a forged QR of
 *   "42" would punch in as employee 42 at the attendance scanner. The server
 *   signs it now and the scanner rejects anything unsigned, so a badge printed
 *   without this argument would scan as invalid.
 */
function generateQRCode(employeeId, employeeName, payload) {
    currentQRData = { employeeId, employeeName, payload };

    document.getElementById('qrEmployeeName').textContent = employeeName;
    document.getElementById('qrEmployeeId').textContent = `Employee ID: ${employeeId}`;
    document.getElementById('qrCodeModal').style.display = 'flex';
    document.getElementById('qrCodeContainer').innerHTML = '<p style="color:var(--theme-neutral-700);">Generating QR Code...</p>';

    if (!payload) {
        document.getElementById('qrCodeContainer').innerHTML =
            '<p style="color:#a52820;">This badge cannot be generated because it is missing its signature. Please reload the page.</p>';
        return;
    }

    // Generate QR code using QRCode.js library
    setTimeout(() => {
        const qrContainer = document.getElementById('qrCodeContainer');
        qrContainer.innerHTML = '';
        
        const qrWrapper = document.createElement('div');
        qrWrapper.style.background = 'white';
        qrWrapper.style.padding = '20px';
        qrWrapper.style.borderRadius = '8px';
        qrWrapper.style.display = 'inline-block';
        
        new QRCode(qrWrapper, {
            text: payload,
            width: 256,
            height: 256,
            colorDark: '#0b044d',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        
        qrContainer.appendChild(qrWrapper);
    }, 300);
}

function closeQRModal() {
    document.getElementById('qrCodeModal').style.display = 'none';
    currentQRData = null;
}

function downloadQRCode() {
    if (!currentQRData) return;
    
    const canvas = document.querySelector('#qrCodeContainer canvas');
    if (!canvas) return;
    
    // Create a new canvas with employee info
    const finalCanvas = document.createElement('canvas');
    const ctx = finalCanvas.getContext('2d');
    
    finalCanvas.width = 400;
    finalCanvas.height = 550;
    
    // White background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
    
    // Draw QR code
    ctx.drawImage(canvas, 50, 50, 300, 300);
    
    // Add text
    ctx.fillStyle = '#0b044d';
    ctx.font = 'bold 24px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(currentQRData.employeeName, 200, 380);
    
    ctx.fillStyle = '#6b6a8a';
    ctx.font = '18px Arial';
    ctx.fillText(`ID: ${currentQRData.employeeId}`, 200, 420);
    
    ctx.font = '16px Arial';
    ctx.fillText('Attendance QR Code', 200, 460);
    
    // Border
    ctx.strokeStyle = '#0b044d';
    ctx.lineWidth = 2;
    ctx.strokeRect(10, 10, 380, 530);
    
    // Download
    finalCanvas.toBlob(blob => {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `QR_${currentQRData.employeeId}_${currentQRData.employeeName.replace(/\s+/g, '_')}.png`;
        link.click();
        URL.revokeObjectURL(url);
    });
}

function printQRCode() {
    if (!currentQRData) return;
    
    const canvas = document.querySelector('#qrCodeContainer canvas');
    if (!canvas) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code - ${currentQRData.employeeName}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    background: #f5f5f5;
                }
                .qr-card {
                    background: white;
                    padding: 40px;
                    border-radius: 12px;
                    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
                    text-align: center;
                    border: 2px solid #0b044d;
                }
                .qr-card h2 {
                    margin: 20px 0 10px;
                    color: #0b044d;
                    font-size: 24px;
                }
                .qr-card p {
                    margin: 5px 0;
                    color: #6b6a8a;
                    font-size: 16px;
                }
                img {
                    border: 4px solid #f0effe;
                    border-radius: 8px;
                }
                @media print {
                    body { background: white; }
                    .qr-card { box-shadow: none; }
                }
            </style>
        </head>
        <body>
            <div class="qr-card">
                <img src="${canvas.toDataURL()}" width="300" height="300" />
                <h2>${currentQRData.employeeName}</h2>
                <p>Employee ID: ${currentQRData.employeeId}</p>
                <p style="font-size: 14px; margin-top: 20px;">Attendance QR Code</p>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
    }, 250);
}


// ══════════════════════════════════════════════════════
// RESPONSIVE ENHANCEMENTS FOR PERSONNEL PAGE
// ══════════════════════════════════════════════════════

// Mobile Table Scroll Indicator
document.addEventListener('DOMContentLoaded', function() {
    const tableWrappers = document.querySelectorAll('.table-wrapper');
    
    tableWrappers.forEach(wrapper => {
        // Check if table is wider than wrapper
        const table = wrapper.querySelector('table');
        if (!table) return;
        
        const checkScroll = () => {
            const hasScroll = table.offsetWidth > wrapper.clientWidth;
            
            if (hasScroll && window.innerWidth < 1024) {
                wrapper.classList.add('has-scroll');
                
                // Add scroll indicator if not exists
                if (!wrapper.querySelector('.scroll-indicator')) {
                    const indicator = document.createElement('div');
                    indicator.className = 'scroll-indicator';
                    indicator.innerHTML = '← Scroll to see more →';
                    indicator.style.cssText = `
                        position: absolute;
                        bottom: 10px;
                        left: 50%;
                        transform: translateX(-50%);
                        background: rgba(11,4,77,0.9);
                        color: #fff;
                        padding: 6px 16px;
                        border-radius: 20px;
                        font-size: 11px;
                        font-weight: 600;
                        pointer-events: none;
                        z-index: 10;
                        white-space: nowrap;
                        transition: opacity 0.3s ease;
                        box-shadow: 0 4px 12px rgba(11,4,77,0.3);
                    `;
                    wrapper.appendChild(indicator);
                    
                    // Hide indicator on scroll
                    wrapper.addEventListener('scroll', function() {
                        const scrollLeft = this.scrollLeft;
                        const maxScroll = this.scrollWidth - this.clientWidth;
                        
                        // Hide indicator when scrolled
                        if (scrollLeft > 50) {
                            indicator.style.opacity = '0';
                        } else {
                            indicator.style.opacity = '1';
                        }
                        
                        // Toggle fade effect
                        if (scrollLeft >= maxScroll - 10) {
                            wrapper.classList.add('scrolled-right');
                        } else {
                            wrapper.classList.remove('scrolled-right');
                        }
                    });
                    
                    // Auto-hide after 3 seconds
                    setTimeout(() => {
                        indicator.style.opacity = '0';
                    }, 3000);
                }
            } else {
                wrapper.classList.remove('has-scroll');
                const indicator = wrapper.querySelector('.scroll-indicator');
                if (indicator) indicator.remove();
            }
        };
        
        checkScroll();
        window.addEventListener('resize', debounce(checkScroll, 250));
    });
});

// Touch-friendly Modal Close
document.addEventListener('DOMContentLoaded', function() {
    const modals = [
        'assignScheduleModal',
        'bulkScheduleModal',
        'viewSchedulesModal',
        'viewEmployeeModal',
        'qrCodeModal',
        'confirmModal',
        'successModal',
        'errorModal',
        'exportSuccessModal',
        'exportErrorModal'
    ];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Close on backdrop click
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    const closeBtn = this.querySelector('[onclick*="close"]');
                    if (closeBtn) closeBtn.click();
                }
            });
            
            // Prevent body scroll when modal is open
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'style') {
                        const display = window.getComputedStyle(modal).display;
                        if (display === 'flex') {
                            document.body.style.overflow = 'hidden';
                        } else {
                            document.body.style.overflow = '';
                        }
                    }
                });
            });
            
            observer.observe(modal, { attributes: true });
        }
    });
});

// 3-Dot Action Menu Toggle
function toggleActionMenu(event, menuId) {
    event.stopPropagation();
    
    const menu = document.getElementById('actionMenu' + menuId);
    const allMenus = document.querySelectorAll('.action-menu-dropdown');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m !== menu) {
            m.classList.remove('active');
        }
    });
    
    // Toggle current menu
    menu.classList.toggle('active');
}

// Close menus when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.action-menu-wrapper')) {
        document.querySelectorAll('.action-menu-dropdown').forEach(menu => {
            menu.classList.remove('active');
        });
    }
});

// Close menus when clicking menu items
document.addEventListener('click', function(event) {
    if (event.target.closest('.action-menu-item')) {
        document.querySelectorAll('.action-menu-dropdown').forEach(menu => {
            menu.classList.remove('active');
        });
    }
});

// Close menus on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.action-menu-dropdown').forEach(menu => {
            menu.classList.remove('active');
        });
    }
});

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Mobile-friendly Filter Dropdowns
document.addEventListener('DOMContentLoaded', function() {
    const filters = document.querySelectorAll('.filter-select, .fc-select');
    
    filters.forEach(filter => {
        // Add touch-friendly styling
        filter.style.minHeight = '44px'; // iOS recommended touch target
        
        // Add clear button for mobile
        if (window.innerWidth < 768) {
            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'position:relative;display:inline-block;';
            filter.parentNode.insertBefore(wrapper, filter);
            wrapper.appendChild(filter);
            
            if (filter.value) {
                const clearBtn = document.createElement('button');
                clearBtn.innerHTML = '×';
                clearBtn.style.cssText = 'position:absolute;right:30px;top:50%;transform:translateY(-50%);background:none;border:none;font-size:20px;color:var(--theme-neutral-700);cursor:pointer;padding:0;width:24px;height:24px;';
                clearBtn.onclick = (e) => {
                    e.preventDefault();
                    filter.value = '';
                    filter.dispatchEvent(new Event('change'));
                    clearBtn.remove();
                };
                wrapper.appendChild(clearBtn);
            }
        }
    });
});

// Swipe to Close Modals (Mobile)
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('[id$="Modal"]');
    
    modals.forEach(modal => {
        let startY = 0;
        let currentY = 0;
        
        const modalContent = modal.querySelector('div:first-child');
        if (!modalContent) return;
        
        modalContent.addEventListener('touchstart', (e) => {
            startY = e.touches[0].clientY;
        }, { passive: true });
        
        modalContent.addEventListener('touchmove', (e) => {
            currentY = e.touches[0].clientY;
            const diff = currentY - startY;
            
            if (diff > 0) {
                modalContent.style.transform = `translateY(${diff}px)`;
                modalContent.style.transition = 'none';
            }
        }, { passive: true });
        
        modalContent.addEventListener('touchend', () => {
            const diff = currentY - startY;
            
            if (diff > 100) {
                // Close modal
                const closeBtn = modal.querySelector('[onclick*="close"]');
                if (closeBtn) closeBtn.click();
            }
            
            modalContent.style.transform = '';
            modalContent.style.transition = 'transform 0.3s ease';
        });
    });
});

// Responsive Pagination
function updateResponsivePagination() {
    const pagination = document.getElementById('paginationControls');
    if (!pagination) return;
    
    const isMobile = window.innerWidth < 640;
    const maxButtons = isMobile ? 3 : 5;
    
    // Re-render pagination with appropriate button count
    if (typeof updatePaginationButtons === 'function') {
        updatePaginationButtons();
    }
}

window.addEventListener('resize', debounce(updateResponsivePagination, 250));

// Export to global scope
window.toggleActionMenu = toggleActionMenu;
window.updateResponsivePagination = updateResponsivePagination;


// Schedule Tab Functions
/* ── Work Schedules: filtering and paging ────────────────────────────────
   The footer has always read "Showing 1-10 of N" and the rows-per-page select
   has always been on screen, but `changeScheduleRowsPerPage` was a
   `console.log` stub and nothing ever hid a row -- so the table rendered all
   N employees under a footer stating it was showing ten of them, and picking
   "50 per page" did nothing at all.

   Filtering and paging are one operation here: the page count has to be taken
   from the rows that survive the department filter, or filtering to a
   four-person department leaves the pager offering page 2 of an empty set. */

let schedRowsPerPage = 10;
let schedCurrentPage = 1;

/** The rows matching the current department filter, in table order. */
function schedVisibleRows() {
    const dept = document.getElementById('schedDepartmentFilter')?.value || '';
    return Array.from(document.querySelectorAll('#scheduleTableBody tr'))
        .filter(row => row.querySelector('.dept-tag'))
        .filter(row => !dept || row.querySelector('.dept-tag').textContent.trim() === dept);
}

function applyScheduleFilters() {
    schedCurrentPage = 1;
    renderScheduleTable();
}

function changeScheduleRowsPerPage(value) {
    schedRowsPerPage = value === 'all' ? Infinity : parseInt(value, 10) || 10;
    schedCurrentPage = 1;
    renderScheduleTable();
}

function renderScheduleTable() {
    const body = document.getElementById('scheduleTableBody');
    if (!body) return;

    const matching = schedVisibleRows();
    const total = matching.length;
    const totalPages = Math.max(1, Math.ceil(total / schedRowsPerPage));
    // A filter can shrink the set under the page you were on.
    schedCurrentPage = Math.min(schedCurrentPage, totalPages);

    const start = total === 0 ? 0 : (schedCurrentPage - 1) * schedRowsPerPage;
    const end = Math.min(start + schedRowsPerPage, total);

    body.querySelectorAll('tr').forEach(row => { row.style.display = 'none'; });
    matching.slice(start, end).forEach(row => { row.style.display = ''; });

    // The "no employees at all" row is server-rendered and has no .dept-tag,
    // so it never reaches the slice above; it is shown whenever the table
    // genuinely holds nothing.
    const emptyRow = body.querySelector('.sched-empty-row');
    if (emptyRow) emptyRow.style.display = '';

    let noMatch = document.getElementById('schedNoMatchRow');
    if (total === 0 && !emptyRow) {
        if (!noMatch) {
            noMatch = document.createElement('tr');
            noMatch.id = 'schedNoMatchRow';
            noMatch.className = 'sched-empty-row';
            noMatch.innerHTML =
                '<td colspan="8"><div class="sched-empty">' +
                '<span class="sched-empty-icon" aria-hidden="true">' +
                '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
                '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>' +
                '<p class="sched-empty-title">No employees in this department</p>' +
                '<p class="sched-empty-text">Clear the department filter to see every employee again.</p>' +
                '</div></td>';
            body.appendChild(noMatch);
        }
        noMatch.style.display = '';
    } else if (noMatch) {
        noMatch.remove();
    }

    const setText = (id, value) => {
        const node = document.getElementById(id);
        if (node) node.textContent = value;
    };
    setText('schedShowingStart', total === 0 ? 0 : start + 1);
    setText('schedShowingEnd', end);
    setText('schedTotalRecords', total);

    renderSchedulePagination(totalPages);
}

function renderSchedulePagination(totalPages) {
    const controls = document.getElementById('schedulePaginationControls');
    if (!controls) return;
    controls.innerHTML = '';
    if (totalPages <= 1) return;

    const button = (label, page, active) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'page-btn' + (active ? ' active' : '');
        btn.textContent = label;
        btn.onclick = () => { schedCurrentPage = page; renderScheduleTable(); };
        controls.appendChild(btn);
    };

    if (schedCurrentPage > 1) button('‹', schedCurrentPage - 1, false);

    const maxButtons = 5;
    let startPage = Math.max(1, schedCurrentPage - Math.floor(maxButtons / 2));
    const endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) startPage = Math.max(1, endPage - maxButtons + 1);

    for (let i = startPage; i <= endPage; i++) button(String(i), i, i === schedCurrentPage);

    if (schedCurrentPage < totalPages) button('›', schedCurrentPage + 1, false);
}

document.addEventListener('DOMContentLoaded', renderScheduleTable);

/**
 * Export the Work Schedules tab.
 *
 * A different file from the Employee Records export, not the same one with
 * columns hidden: it is built by PersonnelExportController::schedules() and
 * carries the shift slots, the schedule status and the date that status turns
 * over. This function's only job is to hand it the tab's department filter --
 * the toolbar above belongs to the Employee Records tab and does not apply
 * here.
 */
function exportSchedules(btn) {
    try {
        const exportUrl = btn?.dataset.exportUrl;

        if (!exportUrl) {
            throw new Error('Export endpoint is unavailable');
        }

        const rows = schedVisibleRows();

        if (rows.length === 0) {
            document.getElementById('exportErrorMessage').textContent =
                'No employees match the current department filter, so there is nothing to export.';
            document.getElementById('exportErrorModal').style.display = 'flex';
            return;
        }

        // Only a filter that is actually set is sent, so the CSV's parameter
        // block reads "All Departments" rather than an empty cell.
        const params = new URLSearchParams();
        const department = document.getElementById('schedDepartmentFilter')?.value || '';
        if (department) params.set('department', department);

        const query = params.toString();
        window.location.href = query ? `${exportUrl}?${query}` : exportUrl;

        document.getElementById('exportSuccessMessage').textContent =
            `Exporting ${rows.length} work schedule${rows.length === 1 ? '' : 's'}. The CSV will download shortly.`;
        document.getElementById('exportSuccessModal').style.display = 'flex';
    } catch (error) {
        console.error('Schedule export error:', error);
        document.getElementById('exportErrorMessage').textContent =
            `An error occurred while exporting: ${error.message || 'Unknown error'}. Please try again.`;
        document.getElementById('exportErrorModal').style.display = 'flex';
    }
}

function openBulkScheduleModal() {
    document.getElementById('bulkScheduleModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function openAssignScheduleModal(employeeId, employeeName, schedule) {
    document.getElementById('scheduleEmployeeId').value = employeeId;
    document.getElementById('scheduleEmployeeName').textContent = employeeName;

    if (schedule) {
        document.getElementById('scheduleId').value = schedule.id || '';
        document.getElementById('scheduleAmIn').value = schedule.am_in || '';
        document.getElementById('scheduleAmOut').value = schedule.am_out || '';
        document.getElementById('schedulePmIn').value = schedule.pm_in || '';
        document.getElementById('schedulePmOut').value = schedule.pm_out || '';
    } else {
        document.getElementById('scheduleId').value = '';
        document.getElementById('scheduleAmIn').value = '';
        document.getElementById('scheduleAmOut').value = '';
        document.getElementById('schedulePmIn').value = '';
        document.getElementById('schedulePmOut').value = '';
    }

    // The date fields are a flatpickr range (assignSchedule.js) — setting .value
    // directly would leave the picker's own state stale, so go through its API.
    // Repoint the busy marks at whoever's schedule we're editing.
    if (window.scheduleBusyCal) {
        window.scheduleBusyCal.setEmployee(employeeId);
        if (schedule && schedule.start_date) {
            window.scheduleBusyCal.flatpickr.setDate(
                [schedule.start_date, schedule.end_date || schedule.start_date], false);
        } else {
            window.scheduleBusyCal.clear();
        }
    }

    document.getElementById('assignScheduleModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function confirmRemoveSchedule(employeeId, employeeName) {
    if (confirm(`Are you sure you want to remove the schedule for ${employeeName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/schedules/${employeeId}/remove`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

window.applyScheduleFilters = applyScheduleFilters;
window.renderScheduleTable = renderScheduleTable;
window.changeScheduleRowsPerPage = changeScheduleRowsPerPage;
window.exportSchedules = exportSchedules;
window.openBulkScheduleModal = openBulkScheduleModal;
window.openAssignScheduleModal = openAssignScheduleModal;
window.confirmRemoveSchedule = confirmRemoveSchedule;

// Bulk Import Functions
function openBulkImportModal() {
    document.getElementById('bulkImportModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeBulkImportModal() {
    document.getElementById('bulkImportModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('bulkImportForm').reset();
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('dropZone').style.borderColor = '#dddcf0';
    document.getElementById('dropZone').style.background = '#fafafe';
}

function downloadTemplate() {
    const headers = [
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'place_of_birth',
        'sex',
        'civil_status',
        'citizenship',
        'blood_type',
        'email',
        'mobile_number',
        'landline_number',
        'house_no',
        'street',
        'barangay',
        'city',
        'province',
        'zip_code',
        'gsis_no',
        'philhealth_no',
        'pagibig_no',
        'tin_no',
        'license_no',
        'department',
        'designation',
        'employment_status',
        'appointment_date',
        'salary_grade',
        'step_increment'
    ];

    const sampleData = [
        'EMP-2024-001',
        'Juan',
        'Santos',
        'Dela Cruz',
        'Jr.',
        '1990-01-15',
        'Manila',
        'Male',
        'Single',
        'Filipino',
        'O+',
        'juan.delacruz@lgu.gov.ph',
        '09171234567',
        '(02) 1234-5678',
        '123',
        'Main Street',
        'Barangay 1',
        'Pagsanjan',
        'Laguna',
        '4008',
        '1234567890',
        '12-345678901-2',
        '1234-5678-9012',
        '123-456-789-000',
        'N12-34-567890',
        'Administration',
        'Administrative Officer II',
        'Permanent',
        '2020-01-01',
        '15',
        '1'
    ];

    const csv = [headers.join(','), sampleData.join(',')].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', 'Employee_Import_Template.csv');
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Drag and drop functionality
const dropZone = document.getElementById('dropZone');
if (dropZone) {
    dropZone.addEventListener('click', () => {
        document.getElementById('csvFile').click();
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--gp-pri)';
        dropZone.style.background = 'var(--theme-primary-light)';
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#dddcf0';
        dropZone.style.background = '#fafafe';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#dddcf0';
        dropZone.style.background = '#fafafe';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                document.getElementById('csvFile').files = files;
                handleFileSelect({ target: { files: files } });
            } else {
                alert('Please upload a CSV file only.');
            }
        }
    });
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.name.endsWith('.csv')) {
        alert('Please select a CSV file.');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('File size exceeds 5MB limit.');
        return;
    }

    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';
    document.getElementById('fileInfo').style.display = 'block';
    document.getElementById('dropZone').style.borderColor = 'var(--theme-success)';
    document.getElementById('dropZone').style.background = 'var(--theme-success-subtle)';
}

function removeFile() {
    document.getElementById('csvFile').value = '';
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('dropZone').style.borderColor = '#dddcf0';
    document.getElementById('dropZone').style.background = '#fafafe';
}

function submitBulkImport() {
    const fileInput = document.getElementById('csvFile');
    if (!fileInput.files.length) {
        alert('Please select a CSV file to upload.');
        return;
    }

    const formData = new FormData();
    formData.append('csv_file', fileInput.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    // Show loading state
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Importing...';

    fetch('/admin/personnel/bulk-import', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        if (data.success) {
            closeBulkImportModal();
            document.getElementById('successMessage').textContent = data.message || 'Employees imported successfully!';
            // Bulk import mails the same pair per row, so it says so too.
            renderEmailNotice(data.imported > 0 ? {
                status: 'sent',
                title: 'Verification emails sent',
                text: 'Each imported employee was emailed a verification link and, separately, '
                    + 'their username and password. They must open the link before they can sign in.',
            } : null);
            document.getElementById('successModal').style.display = 'flex';
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            document.getElementById('errorMessage').textContent = data.message || 'Failed to import employees. Please check your CSV file.';
            document.getElementById('errorModal').style.display = 'flex';
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        document.getElementById('errorMessage').textContent = 'An error occurred during import. Please try again.';
        document.getElementById('errorModal').style.display = 'flex';
    });
}

// Add spin animation for loading
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Export to global scope
window.openBulkImportModal = openBulkImportModal;
window.closeBulkImportModal = closeBulkImportModal;
window.downloadTemplate = downloadTemplate;
window.handleFileSelect = handleFileSelect;
window.removeFile = removeFile;
window.submitBulkImport = submitBulkImport;
