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
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Designation</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.designation_relation?.title || 'N/A'}</span></div>
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
window.generateQRCode = generateQRCode;
window.closeQRModal = closeQRModal;
window.downloadQRCode = downloadQRCode;
window.printQRCode = printQRCode;

// QR Code Functions
let currentQRData = null;

function generateQRCode(employeeId, employeeName) {
    currentQRData = { employeeId, employeeName };
    
    document.getElementById('qrEmployeeName').textContent = employeeName;
    document.getElementById('qrEmployeeId').textContent = `Employee ID: ${employeeId}`;
    document.getElementById('qrCodeModal').style.display = 'flex';
    document.getElementById('qrCodeContainer').innerHTML = '<p style="color:#6b6a8a;">Generating QR Code...</p>';
    
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
            text: String(employeeId),
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
    const filters = document.querySelectorAll('.filter-select');
    
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
                clearBtn.style.cssText = 'position:absolute;right:30px;top:50%;transform:translateY(-50%);background:none;border:none;font-size:20px;color:#6b6a8a;cursor:pointer;padding:0;width:24px;height:24px;';
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
        dropZone.style.borderColor = '#0b044d';
        dropZone.style.background = '#f0effe';
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
    document.getElementById('dropZone').style.borderColor = '#15803d';
    document.getElementById('dropZone').style.background = '#e8f9ef';
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
