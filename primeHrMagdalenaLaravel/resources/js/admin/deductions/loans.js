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

/* -- Loan details ----------------------------------------------------------
   Was a window.alert() drawing a box out of box-drawing characters, its
   columns lined up with padEnd(31)/padStart(26) - unthemeable, and it
   truncated any employee name or loan type longer than the pad while any peso
   figure past six digits pushed the box out of shape. It now fills
   viewLoanModal.

   The arithmetic below is unchanged, and deliberately mirrors what the table
   row already computes in Blade (partials/loans.blade.php) so the modal and
   the row it was opened from cannot state different figures for one loan. */

const PESO = value => '₱' + Number(value || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

// Parsed as a full instant and rendered in the browser's own zone, not off
// the string's YYYY-MM-DD prefix. `start_date` carries Laravel's `date` cast,
// so a stored 2020-01-01 serialises as "2019-12-31T16:00:00Z" under
// APP_TIMEZONE=Asia/Manila — reading the prefix would print the day before
// the one the table row beside it shows. Formatted `M d, Y` to match that row.
function loanDate(value) {
    if (!value) return null;
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) return null;
    return parsed.toLocaleDateString('en-US', {
        month: 'short', day: '2-digit', year: 'numeric',
    });
}

function loanInitials(name) {
    return (name.match(/\b[A-Z]/g) || []).slice(0, 2).join('') || '—';
}

window.closeViewLoanModal = function (event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('viewLoanModal').classList.remove('active');
};

function viewLoanDetails(id) {
    fetch(`/admin/deductions/employee/${encodeURIComponent(id)}`)
        .then(response => {
            if (!response.ok) throw new Error(`Request failed (${response.status})`);
            return response.json();
        })
        .then(data => {
            const el = elId => document.getElementById(elId);
            const set = (elId, value) => { el(elId).textContent = value; };

            const employeeName = `${data.employee.first_name} ${data.employee.last_name}`.trim();
            const loanType = data.deduction_type.name;
            const totalAmount = parseFloat(data.total_amount || 0);
            const remainingBalance = parseFloat(data.remaining_balance || 0);
            const installment = parseFloat(data.installment_amount || 0);
            const amountPaid = Math.max(0, totalAmount - remainingBalance);
            const progress = totalAmount > 0 ? (amountPaid / totalAmount) * 100 : 0;
            const monthsRemaining = installment > 0 ? Math.ceil(remainingBalance / installment) : 0;
            const isSettled = remainingBalance <= 0;

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

            /* -- Identity -- */
            set('viewLoanEmployeeName', employeeName || 'Unknown Employee');
            set('viewLoanAvatarInitials', loanInitials(employeeName));

            const photo = el('viewLoanAvatarPhoto');
            const initials = el('viewLoanAvatarInitials');
            if (data.employee.photo) {
                photo.src = data.employee.photo;
                photo.hidden = false;
                initials.hidden = true;
            } else {
                photo.removeAttribute('src');
                photo.hidden = true;
                initials.hidden = false;
            }

            // Only the parts this record actually carries - a placeholder
            // department reads as a fact about the employee.
            const detail = data.employee.employment_detail;
            const meta = [
                data.employee.employee_id,
                detail && detail.department_relation ? detail.department_relation.name : null,
                detail && detail.designation_relation ? detail.designation_relation.title : null,
            ].filter(Boolean);
            set('viewLoanEmployeeMeta', meta.length ? meta.join(' · ') : 'No employment details on file');

            /* -- Status. The app-wide themed badge vocabulary, mapped to the
                 same three meanings the table row's .lt-status shows. -- */
            const statusClass = { ACTIVE: 'active', SUSPENDED: 'pending', COMPLETED: 'is-neutral' };
            const status = el('viewLoanStatus');
            status.className = 'badge-status ' + (statusClass[data.status] || 'is-neutral');
            status.textContent = data.status
                ? data.status.charAt(0) + data.status.slice(1).toLowerCase()
                : '—';

            /* -- The three headline figures -- */
            set('viewLoanTotalAmount', PESO(totalAmount));
            set('viewLoanInstallment', PESO(installment) + ' / mo');
            set('viewLoanRemaining', PESO(remainingBalance));
            el('viewLoanRemaining').closest('.vld-figure')
                .classList.toggle('is-settled', isSettled);

            /* -- Repayment -- */
            set('viewLoanProgressPct', progress.toFixed(1) + '%');
            const note = el('viewLoanProgressNote');
            note.textContent = isSettled ? 'Fully paid' : PESO(amountPaid) + ' paid';
            note.classList.toggle('is-settled', isSettled);

            // The percentage is reported as computed; only the bar is clamped,
            // because a bar cannot be more than full.
            const fill = el('viewLoanProgressFill');
            fill.style.width = Math.max(0, Math.min(100, progress)) + '%';
            fill.className = 'vld-progress-fill'
                + (isSettled ? ' is-settled' : data.status === 'SUSPENDED' ? ' is-suspended' : '');
            el('viewLoanProgressBar')
                .setAttribute('aria-label', `${progress.toFixed(1)} percent repaid`);

            set('viewLoanAmountPaid', PESO(amountPaid));
            set('viewLoanMonthsRemaining', monthsRemaining === 0
                ? 'None remaining'
                : `${monthsRemaining} ${monthsRemaining === 1 ? 'month' : 'months'}`);

            /* -- Loan information -- */
            const code = data.deduction_type.code || '';
            const provider = code.includes('GSIS') ? 'GSIS'
                : (code.includes('PAGIBIG') || code.includes('PAG-IBIG')) ? 'Pag-IBIG'
                : 'Other';
            const providerClass = provider === 'GSIS' ? 'is-gsis'
                : provider === 'Pag-IBIG' ? 'is-pagibig'
                : 'is-other';

            set('viewLoanTypeName', loanType);
            const providerTag = el('viewLoanProvider');
            providerTag.className = 'lt-tag ' + providerClass;
            providerTag.textContent = provider;
            set('viewLoanTypeCodeValue', code || '—');

            const category = data.deduction_type.category || '';
            set('viewLoanCategory', category
                ? category.charAt(0) + category.slice(1).toLowerCase()
                : '—');

            set('viewLoanStartDate', loanDate(data.start_date) || 'Not set');
            // "Ongoing" rather than an em-dash: a loan with no end date runs
            // until the balance clears, which is a fact, not a missing value.
            set('viewLoanEndDate', loanDate(data.end_date) || 'Ongoing');

            /* -- Schedule -- */
            set('viewLoanScheduleText', scheduleText);
            const sourceTag = el('viewLoanScheduleSource');
            sourceTag.textContent = scheduleSource;
            // Worth flagging only when this loan overrides its type's schedule;
            // "Default" beside the schedule it describes says nothing.
            sourceTag.hidden = scheduleSource !== 'Custom';

            set('viewLoanCutoff1', PESO(perCutoff1st));
            set('viewLoanCutoff2', PESO(perCutoff2nd));

            /* -- Remarks -- */
            const remarksSection = el('viewLoanRemarksSection');
            if (data.remarks) {
                set('viewLoanRemarks', data.remarks);
                remarksSection.hidden = false;
            } else {
                remarksSection.hidden = true;
            }

            // Reading a loan is usually the step before correcting it - the
            // same pairing viewLoanTypeModal offers.
            el('viewLoanEditBtn').onclick = () => {
                closeViewLoanModal();
                editEmployeeDeduction(data.id);
            };

            el('viewLoanModal').classList.add('active');
        })
        .catch(error => {
            console.error('Error fetching loan details:', error);
            alert('Could not load that loan. Please refresh the page and try again.');
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
