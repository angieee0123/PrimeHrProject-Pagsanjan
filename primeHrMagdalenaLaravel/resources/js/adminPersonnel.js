// Admin Personnel Page Scripts

// Pagination and Sorting
let currentPage = 1;
let rowsPerPage = 10;
let sortColumn = -1;
let sortAscending = true;
let allRows = [];

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

    currentPage = 1;
    displayPage(currentPage);
}

function displayPage(page) {
    const tbody = document.getElementById('personnelTableBody');
    tbody.innerHTML = '';

    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageRows = allRows.slice(start, end);

    if (pageRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #6b6a8a;">No employees found.</td></tr>';
    } else {
        pageRows.forEach(row => tbody.appendChild(row));
    }

    document.getElementById('showingStart').textContent = start + 1;
    document.getElementById('showingEnd').textContent = Math.min(end, allRows.length);
    document.getElementById('totalRecords').textContent = allRows.length;

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

function applyFilters() {
    const departmentFilter = document.getElementById('departmentFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    allRows.forEach(row => {
        let showRow = true;

        if (departmentFilter) {
            const deptTag = row.querySelector('.dept-tag');
            const deptText = deptTag ? deptTag.textContent.trim() : '';
            if (!deptText.includes(departmentFilter)) {
                showRow = false;
            }
        }

        if (statusFilter) {
            const statusBadge = row.querySelector('.badge-status');
            const statusText = statusBadge ? statusBadge.textContent.trim() : '';
            if (statusText !== statusFilter) {
                showRow = false;
            }
        }

        row.style.display = showRow ? '' : 'none';
    });

    const visibleRows = allRows.filter(row => row.style.display !== 'none');
    currentPage = 1;
    displayFilteredPage(visibleRows);
}

function displayFilteredPage(visibleRows) {
    const tbody = document.getElementById('personnelTableBody');
    tbody.innerHTML = '';

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageRows = visibleRows.slice(start, end);

    if (pageRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #6b6a8a;">No employees found matching the filters.</td></tr>';
    } else {
        pageRows.forEach(row => tbody.appendChild(row));
    }

    document.getElementById('showingStart').textContent = visibleRows.length > 0 ? start + 1 : 0;
    document.getElementById('showingEnd').textContent = Math.min(end, visibleRows.length);
    document.getElementById('totalRecords').textContent = visibleRows.length;

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

function exportTableData() {
    try {
        if (allRows.length === 0) {
            document.getElementById('exportErrorMessage').textContent = 'No employee records available to export.';
            document.getElementById('exportErrorModal').style.display = 'flex';
            return;
        }

        const data = [];
        data.push(['Employee Name', 'Employee ID', 'Position', 'Department', 'Type', 'Date Hired', 'Status']);

        allRows.forEach(row => {
            const empName = row.querySelector('.emp-name')?.textContent.trim() || '';
            const empId = row.querySelector('.emp-id')?.textContent.trim() || '';
            const position = row.querySelector('.position-cell')?.textContent.trim() || '';
            const department = row.querySelector('.dept-tag')?.textContent.trim() || '';
            const type = row.querySelector('.badge-emptype')?.textContent.trim() || '';
            const dateHired = row.cells[4]?.textContent.trim() || '';
            const status = row.querySelector('.badge-status')?.textContent.trim() || '';

            data.push([empName, empId, position, department, type, dateHired, status]);
        });

        const csv = data.map(row =>
            row.map(cell => `"${cell}"`).join(',')
        ).join('\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        const timestamp = new Date().toISOString().slice(0, 10);
        const filename = `Employee_Records_${timestamp}.csv`;
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        document.getElementById('exportSuccessMessage').textContent = `Successfully exported ${allRows.length} employee records to ${filename}`;
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
        iconWrap.style.background = '#e8f9ef';
        icon.style.stroke = '#15803d';
        icon.innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
        title.textContent = 'Activate Employee Account';
        message.textContent = 'Are you sure you want to activate this employee account? The employee will be able to access the system.';
        submitBtn.style.background = '#15803d';
    } else {
        iconWrap.style.background = '#fee8e8';
        icon.style.stroke = '#8e1e18';
        icon.innerHTML = '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>';
        title.textContent = 'Deactivate Employee Account';
        message.textContent = 'Are you sure you want to deactivate this employee account? The employee will no longer be able to access the system.';
        submitBtn.style.background = '#8e1e18';
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
    
    if (input.value.trim() !== 'Yes I confirm') {
        error.style.display = 'block';
        input.style.borderColor = '#8e1e18';
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

// View Employee
function viewEmployee(employeeId) {
    document.getElementById('viewEmployeeModal').style.display = 'flex';
    document.getElementById('viewEmployeeContent').innerHTML = '<p style="text-align:center; color:#6b6a8a;">Loading...</p>';
    
    fetch(`/admin/personnel/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewEmployeeId').textContent = data.employee_id;
            document.getElementById('viewEmployeeContent').innerHTML = generateEmployeeView(data);
        })
        .catch(error => {
            document.getElementById('viewEmployeeContent').innerHTML = '<p style="text-align:center; color:#8e1e18;">Error loading employee details.</p>';
        });
}

function closeViewModal() {
    document.getElementById('viewEmployeeModal').style.display = 'none';
}

function generateEmployeeView(data) {
    return `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">👤 Personal Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Full Name</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.first_name} ${data.middle_name || ''} ${data.last_name} ${data.suffix || ''}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Date of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.birth_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Place of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.place_of_birth || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Sex</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.sex || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Civil Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.civil_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Citizenship</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.citizenship || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Blood Type</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.blood_type || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">💼 Employment Details</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Position</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.position || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Department</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.department || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Employment Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.employment_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Appointment Date</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.appointment_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Salary Grade</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.salary_grade || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Step Increment</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.step_increment || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">📞 Contact Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Email</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.email || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Mobile Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.mobile_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Landline</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.landline_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Emergency Contact</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_person || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Emergency Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_number || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">🪪 Government IDs</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">GSIS Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.gsis_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">PhilHealth Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.philhealth_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">PAG-IBIG Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.pagibig_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">TIN Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.tin_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">License Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.license_no || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div>
            <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">📍 Address</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">House No.</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.house_no || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Street</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.street || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Barangay</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.barangay || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">City/Municipality</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.city || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Province</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.province || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Zip Code</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.zip_code || 'N/A'}</span></div>
            </div>
        </div>
    `;
}

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
window.exportTableData = exportTableData;
window.closeExportSuccessModal = closeExportSuccessModal;
window.closeExportErrorModal = closeExportErrorModal;
window.closeSuccessModal = closeSuccessModal;
window.closeErrorModal = closeErrorModal;
window.confirmStatusChange = confirmStatusChange;
window.closeConfirmModal = closeConfirmModal;
window.submitConfirmation = submitConfirmation;
window.viewEmployee = viewEmployee;
window.closeViewModal = closeViewModal;
