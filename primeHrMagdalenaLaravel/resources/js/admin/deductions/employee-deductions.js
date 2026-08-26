import { exportWithFilters } from './exportWithFilters.js';
import { confirmDeleteDeduction } from './deleteDeduction.js';

window._deductionCurrentPage = 1;
window._deductionRowsPerPage = 10;

function filterEmployeeDeductions() {
    const searchTerm = document.getElementById('searchEmployee').value.toLowerCase();
    const typeFilter = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('#employeeDeductionsTableBody tr:not(#noDataRow)');

    const filtered = [];

    rows.forEach(row => {
        const employeeName = row.dataset.employee || '';
        const type = row.dataset.type || '';
        const status = row.dataset.status || '';

        const matchesSearch = employeeName.includes(searchTerm);
        const matchesType = !typeFilter || type === typeFilter;
        const matchesStatus = !statusFilter || status === statusFilter;

        if (matchesSearch && matchesType && matchesStatus) {
            filtered.push(row);
        }
    });

    window._deductionFilteredRows = filtered;
    window._deductionCurrentPage = 1;
    updateDeductionPagination();
}

window.updateDeductionPagination = function () {
    const rows = window._deductionFilteredRows || [];
    const total = rows.length;
    const perPage = window._deductionRowsPerPage;
    const totalPages = Math.ceil(total / perPage) || 1;
    const page = Math.min(window._deductionCurrentPage, totalPages);
    window._deductionCurrentPage = page;

    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, total);

    document.querySelectorAll('#employeeDeductionsTableBody tr:not(#noDataRow)').forEach(row => row.style.display = 'none');
    rows.forEach((row, i) => { if (i >= start && i < end) row.style.display = ''; });

    document.getElementById('deductionRowStart').textContent = total ? start + 1 : 0;
    document.getElementById('deductionRowEnd').textContent = end;
    document.getElementById('deductionRowTotal').textContent = total;

    // Show/hide no data row
    const noDataRow = document.getElementById('noDataRow');
    if (noDataRow) {
        noDataRow.style.display = total === 0 ? '' : 'none';
    }

    const controls = document.getElementById('deductionPaginationControls');
    if (totalPages <= 1) { controls.innerHTML = ''; return; }

    let html = '';
    const maxVisible = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    if (page > 1) html += '<button class="page-btn" onclick="goToDeductionPage(' + (page - 1) + ')">‹</button>';
    if (startPage > 1) {
        html += '<button class="page-btn" onclick="goToDeductionPage(1)">1</button>';
        if (startPage > 2) html += '<span class="ded-ellipsis">...</span>';
    }
    for (let i = startPage; i <= endPage; i++) {
        html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToDeductionPage(' + i + ')">' + i + '</button>';
    }
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span class="ded-ellipsis">...</span>';
        html += '<button class="page-btn" onclick="goToDeductionPage(' + totalPages + ')">' + totalPages + '</button>';
    }
    if (page < totalPages) html += '<button class="page-btn" onclick="goToDeductionPage(' + (page + 1) + ')">›</button>';

    controls.innerHTML = html;
};

window.goToDeductionPage = function (page) {
    window._deductionCurrentPage = page;
    updateDeductionPagination();
};

window.changeDeductionRowsPerPage = function () {
    window._deductionRowsPerPage = parseInt(document.getElementById('deductionRowsPerPage').value) || 10;
    window._deductionCurrentPage = 1;
    updateDeductionPagination();
};

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('employeeDeductionsTableBody')) {
        filterEmployeeDeductions();
    }
});

function editEmployeeDeduction(id) {
    // Fetch deduction data
    fetch(`/admin/deductions/employee/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set form action
            document.getElementById('editEmployeeDeductionForm').action = `/admin/deductions/employee/${id}`;

            // Populate basic fields
            document.getElementById('editDeductionId').value = data.id;
            document.getElementById('editStartDate').value = data.start_date;
            document.getElementById('editEndDate').value = data.end_date || '';
            document.getElementById('editStatus').value = data.status;
            document.getElementById('editRemarks').value = data.remarks || '';

            // Show employee and deduction type info
            document.getElementById('editEmployeeName').textContent = `${data.employee.first_name} ${data.employee.last_name}`;
            document.getElementById('editDeductionType').textContent = data.deduction_type.name;

            // Hide all conditional fields first
            document.getElementById('editLoanFields').style.display = 'none';
            document.getElementById('editInstallmentField').style.display = 'none';
            document.getElementById('editFixedAmountField').style.display = 'none';

            // Show relevant fields based on deduction type
            if (data.deduction_type.category === 'LOAN') {
                // Show loan fields
                document.getElementById('editLoanFields').style.display = 'flex';
                document.getElementById('editInstallmentField').style.display = 'block';
                document.getElementById('editTotalAmount').value = data.total_amount || '';
                document.getElementById('editRemainingBalance').value = data.remaining_balance || '';
                document.getElementById('editInstallmentAmount').value = data.installment_amount || '';
            } else if (data.deduction_type.computation_type === 'FIXED' && data.amount) {
                // Show fixed amount field for non-loan fixed deductions
                document.getElementById('editFixedAmountField').style.display = 'block';
                document.getElementById('editAmount').value = data.amount || '';
            }

            // Open modal
            openEditEmployeeDeductionModal();
        })
        .catch(error => {
            console.error('Error fetching deduction:', error);
            alert('Failed to load deduction data.');
        });
}

function exportEmployeeDeductions(btn) {
    exportWithFilters(btn, {
        search: 'searchEmployee',
        type:   'filterType',
        status: 'filterStatus',
    });
}

window.filterEmployeeDeductions = filterEmployeeDeductions;
window.editEmployeeDeduction = editEmployeeDeduction;
window.deleteEmployeeDeduction = confirmDeleteDeduction;
window.exportEmployeeDeductions = exportEmployeeDeductions;
