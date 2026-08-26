import { exportWithFilters } from './exportWithFilters.js';
import { confirmDeleteLoan } from './deleteDeduction.js';

window._loanCurrentPage = 1;
window._loanRowsPerPage = 10;

/* ── Row actions (⋮) ────────────────────────────────────────────────────────
   The menu cannot stay inside its <td>: .table-section sets overflow:hidden, so
   an absolutely positioned menu is clipped at the card edge. position:fixed does
   not escape either, because the glass styling puts backdrop-filter on
   .table-section, which makes it the containing block for its fixed descendants.
   So the open menu is moved to <body>, positioned from the button's viewport
   rect, and put back on close. */
let _loanOpenMenu = null;
let _loanMenuHome = null;

window.closeLoanMenu = function () {
    document.querySelectorAll('.lt-menu-btn.is-open').forEach(btn => {
        btn.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
    });

    if (!_loanOpenMenu) return;
    _loanOpenMenu.classList.remove('is-open');
    _loanOpenMenu.style.top = '';
    _loanOpenMenu.style.left = '';
    // insertBefore with a null reference appends, which is what we want when the
    // menu was the last child of its cell.
    _loanMenuHome.parent.insertBefore(_loanOpenMenu, _loanMenuHome.next);
    _loanOpenMenu = null;
    _loanMenuHome = null;
};

window.toggleLoanMenu = function (e) {
    e.stopPropagation();
    const btn = e.currentTarget;
    const menu = document.getElementById(btn.dataset.menu);
    if (!menu) return;

    const wasOpen = menu === _loanOpenMenu;
    closeLoanMenu();
    if (wasOpen) return;

    _loanMenuHome = { parent: menu.parentNode, next: menu.nextSibling };
    document.body.appendChild(menu);
    menu.classList.add('is-open');

    const rect = btn.getBoundingClientRect();
    let top = rect.bottom + 6;
    if (top + menu.offsetHeight > window.innerHeight - 8) {
        top = Math.max(8, rect.top - menu.offsetHeight - 6);
    }
    menu.style.top = top + 'px';
    menu.style.left = Math.max(8, rect.right - menu.offsetWidth) + 'px';

    btn.classList.add('is-open');
    btn.setAttribute('aria-expanded', 'true');
    _loanOpenMenu = menu;
};

document.addEventListener('click', () => closeLoanMenu());
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLoanMenu(); });
// Capture phase: the table wrapper's own scroll does not bubble to window.
window.addEventListener('scroll', () => closeLoanMenu(), true);
window.addEventListener('resize', () => closeLoanMenu());

function filterLoans() {
    closeLoanMenu();
    const searchTerm = document.getElementById('searchLoan').value.toLowerCase();
    const loanTypeFilter = document.getElementById('filterLoanType').value;
    const statusFilter = document.getElementById('filterLoanStatus').value;
    const rows = document.querySelectorAll('#loansTableBody tr:not(#noLoansRow)');

    const filtered = [];

    rows.forEach(row => {
        const employeeName = row.dataset.employee || '';
        const loanType = row.dataset.loanType || '';
        const status = row.dataset.status || '';

        const matchesSearch = employeeName.includes(searchTerm);
        const matchesType = !loanTypeFilter || loanType === loanTypeFilter;
        const matchesStatus = !statusFilter || status === statusFilter;

        if (matchesSearch && matchesType && matchesStatus) {
            filtered.push(row);
        }
    });

    window._loanFilteredRows = filtered;
    window._loanCurrentPage = 1;
    updateLoanPagination();
}

window.updateLoanPagination = function () {
    // A menu parked in <body> would survive its own row being paged out.
    closeLoanMenu();

    const rows = window._loanFilteredRows || [];
    const total = rows.length;
    const perPage = window._loanRowsPerPage;
    const totalPages = Math.ceil(total / perPage) || 1;
    const page = Math.min(window._loanCurrentPage, totalPages);
    window._loanCurrentPage = page;

    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, total);

    document.querySelectorAll('#loansTableBody tr:not(#noLoansRow)').forEach(row => row.style.display = 'none');
    rows.forEach((row, i) => { if (i >= start && i < end) row.style.display = ''; });

    document.getElementById('loanRowStart').textContent = total ? start + 1 : 0;
    document.getElementById('loanRowEnd').textContent = end;
    document.getElementById('loanRowTotal').textContent = total;

    // Show/hide no data row
    const noLoansRow = document.getElementById('noLoansRow');
    if (noLoansRow) {
        noLoansRow.style.display = total === 0 ? '' : 'none';
    }

    const controls = document.getElementById('loanPaginationControls');
    if (totalPages <= 1) { controls.innerHTML = ''; return; }

    let html = '';
    const maxVisible = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    if (page > 1) html += '<button class="page-btn" onclick="goToLoanPage(' + (page - 1) + ')">‹</button>';
    if (startPage > 1) {
        html += '<button class="page-btn" onclick="goToLoanPage(1)">1</button>';
        if (startPage > 2) html += '<span class="ded-ellipsis">...</span>';
    }
    for (let i = startPage; i <= endPage; i++) {
        html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToLoanPage(' + i + ')">' + i + '</button>';
    }
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span class="ded-ellipsis">...</span>';
        html += '<button class="page-btn" onclick="goToLoanPage(' + totalPages + ')">' + totalPages + '</button>';
    }
    if (page < totalPages) html += '<button class="page-btn" onclick="goToLoanPage(' + (page + 1) + ')">›</button>';

    controls.innerHTML = html;
};

window.goToLoanPage = function (page) {
    window._loanCurrentPage = page;
    updateLoanPagination();
};

window.changeLoanRowsPerPage = function () {
    window._loanRowsPerPage = parseInt(document.getElementById('loanRowsPerPage').value) || 10;
    window._loanCurrentPage = 1;
    updateLoanPagination();
};

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('loansTableBody')) {
        filterLoans();
    }
});

function viewLoanDetails(id) {
    // Fetch loan data
    fetch(`/admin/deductions/employee/${id}`)
        .then(response => response.json())
        .then(data => {
            const employeeName = `${data.employee.first_name} ${data.employee.last_name}`;
            const loanType = data.deduction_type.name;
            const totalAmount = parseFloat(data.total_amount || 0);
            const remainingBalance = parseFloat(data.remaining_balance || 0);
            const installment = parseFloat(data.installment_amount || 0);
            const amountPaid = totalAmount - remainingBalance;
            const progress = totalAmount > 0 ? (amountPaid / totalAmount) * 100 : 0;
            const monthsRemaining = installment > 0 ? Math.ceil(remainingBalance / installment) : 0;

            // Get schedule - prioritize custom schedule over default
            let schedule = 'BOTH_SPLIT'; // Default
            let scheduleSource = 'Default';

            if (data.custom_cutoff_schedule) {
                schedule = data.custom_cutoff_schedule;
                scheduleSource = 'Custom';
            } else if (data.deduction_type.schedules && data.deduction_type.schedules.length > 0) {
                schedule = data.deduction_type.schedules[0].cutoff_schedule;
                scheduleSource = 'Type Default';
            }

            // Calculate per-cutoff based on schedule
            let perCutoff1st, perCutoff2nd, scheduleText;
            if (schedule === '1ST_ONLY') {
                perCutoff1st = installment;
                perCutoff2nd = 0;
                scheduleText = '1st Cutoff Only';
            } else if (schedule === '2ND_ONLY') {
                perCutoff1st = 0;
                perCutoff2nd = installment;
                scheduleText = '2nd Cutoff Only';
            } else if (schedule === 'BOTH_FULL') {
                perCutoff1st = installment;
                perCutoff2nd = installment;
                scheduleText = 'Both Cutoffs (Full Amount Each)';
            } else { // BOTH_SPLIT
                perCutoff1st = installment / 2;
                perCutoff2nd = installment / 2;
                scheduleText = 'Both Cutoffs (Split 50-50)';
            }

            const message = `
╔════════════════════════════════════════════╗
║          LOAN DETAILS                      ║
╠════════════════════════════════════════════╣
║ Employee: ${employeeName.padEnd(32)} ║
║ Loan Type: ${loanType.padEnd(31)} ║
╠════════════════════════════════════════════╣
║ Total Amount: ₱${totalAmount.toFixed(2).padStart(26)} ║
║ Amount Paid: ₱${amountPaid.toFixed(2).padStart(27)} ║
║ Remaining Balance: ₱${remainingBalance.toFixed(2).padStart(21)} ║
║ Progress: ${progress.toFixed(1)}%${' '.repeat(32 - progress.toFixed(1).length)} ║
╠════════════════════════════════════════════╣
║ Monthly Installment: ₱${installment.toFixed(2).padStart(19)} ║
║ Schedule: ${scheduleText.padEnd(31)} ║
║ Schedule Source: ${scheduleSource.padEnd(26)} ║
║ 1st Cutoff: ₱${perCutoff1st.toFixed(2).padStart(26)} ║
║ 2nd Cutoff: ₱${perCutoff2nd.toFixed(2).padStart(26)} ║
║ Months Remaining: ${monthsRemaining} months${' '.repeat(22 - monthsRemaining.toString().length)} ║
╠════════════════════════════════════════════╣
║ Start Date: ${new Date(data.start_date).toLocaleDateString().padEnd(28)} ║
║ End Date: ${(data.end_date ? new Date(data.end_date).toLocaleDateString() : 'Ongoing').padEnd(30)} ║
║ Status: ${data.status.padEnd(32)} ║
╠════════════════════════════════════════════╣
║ Remarks: ${(data.remarks || 'None').padEnd(31)} ║
╚════════════════════════════════════════════╝
            `.trim();

            alert(message);
        })
        .catch(error => {
            console.error('Error fetching loan details:', error);
            alert('Failed to load loan details.');
        });
}

function exportLoans(btn) {
    exportWithFilters(btn, {
        search:    'searchLoan',
        loan_type: 'filterLoanType',
        status:    'filterLoanStatus',
    });
}

// Ensure modal functions are in global scope
window.openAddLoanModal = function() {
    document.getElementById('addLoanModal').classList.add('active');
};

window.closeAddLoanModal = function(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('addLoanModal').classList.remove('active');
    document.getElementById('addLoanForm').reset();
    document.getElementById('providerName').value = '';
    document.getElementById('otherProviderFields').style.display = 'none';
    document.getElementById('otherProviderName').removeAttribute('required');
    document.getElementById('otherLoanType').removeAttribute('required');
};

window.filterLoans = filterLoans;
window.viewLoanDetails = viewLoanDetails;
// A loan deletion asks its own question — the row shares the deductions
// endpoint, not the deductions wording.
window.deleteEmployeeLoan = confirmDeleteLoan;
window.exportLoans = exportLoans;
