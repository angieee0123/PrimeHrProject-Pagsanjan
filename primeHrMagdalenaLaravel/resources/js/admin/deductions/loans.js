window._loanCurrentPage = 1;
window._loanRowsPerPage = 10;

function filterLoans() {
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

function exportLoans() {
    window.location.href = '/admin/deductions/loans/export';
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
window.exportLoans = exportLoans;
