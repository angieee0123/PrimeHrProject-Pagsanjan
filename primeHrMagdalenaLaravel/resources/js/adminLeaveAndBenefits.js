// JavaScript for adminLeaveAndBenefits.blade.php

// Leave Request Functions
window.approveLeaveRequest = function(id, appNumber) {
    if (!confirm(`Are you sure you want to approve leave request ${appNumber}?`)) {
        return;
    }
    
    fetch(`/admin/leave/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Leave request approved successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to approve leave request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the leave request');
    });
};

window.openRejectModal = function(id, appNumber) {
    window.currentRejectId = id;
    window.currentRejectAppNumber = appNumber;
    document.getElementById('rejectModalTitle').textContent = `Reject ${appNumber}`;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectModal').style.display = 'flex';
};

window.closeRejectModal = function() {
    document.getElementById('rejectModal').style.display = 'none';
    window.currentRejectId = null;
    window.currentRejectAppNumber = null;
};

window.openAdminLeaveDetailModal = function(id, name, empId, type, from, to, days, reason, status, appNumber, attachmentUrl, remarks) {
    window.currentLeaveApplicationId = id;
    document.getElementById('adminLeaveAppNumber').textContent = 'CS FORM NO. 6 · ' + appNumber;
    document.getElementById('adminLeaveEmployeeName').textContent = name;
    document.getElementById('adminLeaveEmployeeId').textContent = empId;
    document.getElementById('adminLeaveType').textContent = type;

    const statusBadge = document.getElementById('adminLeaveStatus');
    statusBadge.textContent = status;
    statusBadge.className = 'badge-status ' +
        (status === 'Approved' ? 'processed' :
         status === 'Pending' ? 'pending' :
         status === 'Rejected' || status === 'Disapproved' ? 'on-hold' : 'cancelled');

    const formFrame = document.getElementById('adminLeaveFormFrame');
    if (formFrame) {
        formFrame.src = `/admin/leave/${id}/view-form?embed=1`;
    }

    const downloadBtn = document.getElementById('adminDownloadBtn');
    if (attachmentUrl && attachmentUrl.trim() !== '') {
        downloadBtn.style.display = 'flex';
        downloadBtn.onclick = () => window.open(attachmentUrl, '_blank');
    } else {
        downloadBtn.style.display = 'none';
    }

    document.getElementById('adminLeaveDetailModal').style.display = 'flex';
};

window.closeAdminLeaveDetailModal = function() {
    const formFrame = document.getElementById('adminLeaveFormFrame');
    if (formFrame) {
        formFrame.src = 'about:blank';
    }
    document.getElementById('adminLeaveDetailModal').style.display = 'none';
};

window.printLeaveForm = function() {
    const frame = document.getElementById('adminLeaveFormFrame');
    if (frame?.contentWindow) {
        frame.contentWindow.print();
        return;
    }
    if (!window.currentLeaveApplicationId) return;
    const printWindow = window.open(`/admin/leave/${window.currentLeaveApplicationId}/view-form`, '_blank');
    if (printWindow) {
        printWindow.addEventListener('load', () => printWindow.print());
    }
};

window.downloadLeaveForm = function() {
    if (!window.currentLeaveApplicationId) return;
    window.location.href = `/admin/leave/${window.currentLeaveApplicationId}/download-form`;
};

// Initialize reject button handler
document.addEventListener('DOMContentLoaded', function() {
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', function() {
            const reason = document.getElementById('rejectionReason').value.trim();
            
            if (!reason) {
                alert('Please provide a reason for rejection');
                return;
            }
            
            const btn = this;
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/></svg> Rejecting...';
            
            fetch(`/admin/leave/${window.currentRejectId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ remarks: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Rejected!';
                    btn.style.background = '#15803d';
                    setTimeout(() => {
                        closeRejectModal();
                        location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'Failed to reject leave request');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while rejecting the leave request');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
        });
    }
    
    // Initialize leave request pagination
    const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
    leaveRequestTotalRows = rows.length - (rows[0]?.querySelector('.emp-cell') ? 0 : 1);
    
    if (typeof renderLeaveRequestPagination === 'function') {
        renderLeaveRequestPagination();
        paginateLeaveRequestTable();
    }
});

window.switchTab = function(tab) {
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    buttons.forEach(btn => {
        if ((tab === 'leave' && btn.textContent.includes('Leave Requests')) ||
            (tab === 'transactions' && btn.textContent.includes('Transaction History')) ||
            (tab === 'leave-credits' && btn.textContent.includes('Leave Credits')) ||
            (tab === 'benefits' && btn.textContent.includes('Benefits Summary')) ||
            (tab === 'types' && btn.textContent.includes('Leave Types')) ||
            (tab === 'accrual' && btn.textContent.includes('CSC Daily Accrual')) ||
            (tab === 'import' && btn.textContent.includes('Import Records'))) {
            btn.classList.add('active');
        }
    });

    const tabs = ['leave-tab', 'transactions-tab', 'leave-credits-tab', 'benefits-tab', 'types-tab', 'accrual-tab', 'migrate-tab'];
    tabs.forEach(tabId => {
        const element = document.getElementById(tabId);
        if (element) element.style.display = 'none';
    });

    if (tab === 'leave') {
        const el = document.getElementById('leave-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'transactions') {
        const el = document.getElementById('transactions-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'leave-credits') {
        const el = document.getElementById('leave-credits-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'benefits') {
        const el = document.getElementById('benefits-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'types') {
        const el = document.getElementById('types-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'accrual') {
        const el = document.getElementById('accrual-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'import') {
        const el = document.getElementById('migrate-tab');
        if (el) el.style.display = 'block';
    }
}

window.openAddLeaveTypeModal = function() {
    const modal = document.getElementById('addLeaveTypeModal');
    
    if (!modal) {
        console.error('Modal not found!');
        return;
    }
    
    const form = document.getElementById('addLeaveTypeForm');
    if (form) {
        form.reset();
        form.action = '/admin/leave/types';
        
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
        
        const codeInput = form.querySelector('input[name="leave_code"]');
        if (codeInput) codeInput.readOnly = false;
        
        const submitBtn = form.querySelector('.btn-submit');
        if (submitBtn) submitBtn.textContent = 'Add Leave Type';
        
        const fileDisplay = document.getElementById('fileNameDisplay');
        if (fileDisplay) fileDisplay.textContent = 'Choose PDF file or drag here';
    }
    
    const title = modal.querySelector('.modal-title');
    if (title) title.textContent = 'Add New Leave Type';
    
    const subtitle = modal.querySelector('.modal-subtitle');
    if (subtitle) subtitle.textContent = 'Create a new leave type for LGU Pagsanjan';
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
};

window.closeAddLeaveTypeModal = function() {
    const modal = document.getElementById('addLeaveTypeModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
};

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addLeaveTypeModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAddLeaveTypeModal();
            }
        });
    }
});

document.getElementById('addLeaveTypeForm')?.addEventListener('submit', function(e) {
    const fileInput = document.getElementById('leaveTypeDocument');

    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (file.size > 5 * 1024 * 1024) {
            e.preventDefault();
            alert('File size exceeds 5MB limit. Please choose a smaller file.');
            return false;
        }
    }
});

window.viewLeaveType = function(code) {
    fetch(`/admin/leave/types/${code}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewLeaveCode').textContent = data.leave_code;
            document.getElementById('viewLeaveName').textContent = data.leave_name;
            document.getElementById('viewAnnualLimit').textContent = data.annual_limit > 0 ? `${data.annual_limit} days` : 'As needed';
            document.getElementById('viewLeaveTypeAccrual').textContent = data.is_accrued ? 'Accrued' : 'Fixed';
            
            const statusBadge = document.getElementById('viewLeaveStatus');
            statusBadge.textContent = data.is_active ? 'Active' : 'Inactive';
            statusBadge.className = data.is_active ? 'badge-status processed' : 'badge-status on-hold';

            const configContainer = document.getElementById('viewLeaveConfig');
            configContainer.innerHTML = '';
            const configs = [];
            if (data.is_accrued) configs.push('Accrued');
            if (data.is_cumulative) configs.push('Cumulative');
            if (data.requires_6_months) configs.push('Requires 6 Months');
            if (data.is_monetizable) configs.push('Monetizable');
            if (data.requires_attachment) configs.push('Requires Attachment');
            
            if (configs.length > 0) {
                configs.forEach(config => {
                    const badge = document.createElement('span');
                    badge.className = 'config-badge';
                    badge.textContent = config;
                    configContainer.appendChild(badge);
                });
            } else {
                configContainer.innerHTML = '<span style="color: #9ca3af; font-size: 13px;">No special configuration</span>';
            }

            const attachmentGroup = document.getElementById('viewAttachmentInfoGroup');
            if (data.attachment_info) {
                document.getElementById('viewAttachmentInfo').textContent = data.attachment_info;
                attachmentGroup.style.display = 'block';
            } else {
                attachmentGroup.style.display = 'none';
            }

            const documentGroup = document.getElementById('viewDocumentGroup');
            if (data.document_path) {
                document.getElementById('viewDocumentLink').href = `/storage/${data.document_path}`;
                documentGroup.style.display = 'block';
            } else {
                documentGroup.style.display = 'none';
            }

            document.getElementById('viewLeaveTypeModal').setAttribute('data-leave-code', code);
            document.getElementById('viewLeaveTypeModal').classList.add('active');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load leave type details');
        });
}

window.closeViewLeaveTypeModal = function(event) {
    if (!event || event.target.id === 'viewLeaveTypeModal') {
        document.getElementById('viewLeaveTypeModal').classList.remove('active');
    }
}

window.editLeaveTypeFromView = function() {
    const code = document.getElementById('viewLeaveTypeModal').getAttribute('data-leave-code');
    closeViewLeaveTypeModal();
    editLeaveType(code);
}

window.editLeaveType = function(code) {
    console.log('Editing leave type:', code);
    
    fetch(`/admin/leave/types/${code}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch leave type');
            }
            return response.json();
        })
        .then(data => {
            console.log('Fetched data:', data);
            
            // Update modal title and subtitle
            document.querySelector('#addLeaveTypeModal .modal-title').textContent = 'Edit Leave Type';
            document.querySelector('#addLeaveTypeModal .modal-subtitle').textContent = 'Update leave type configuration';
            
            const form = document.getElementById('addLeaveTypeForm');
            
            // Set form action for update
            form.action = `/admin/leave/types/${code}`;
            
            // Add or update _method input for PUT request
            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'PUT';
            
            // Populate form fields
            form.querySelector('input[name="leave_code"]').value = data.leave_code || '';
            form.querySelector('input[name="leave_code"]').readOnly = true;
            form.querySelector('input[name="leave_name"]').value = data.leave_name || '';
            form.querySelector('input[name="annual_limit"]').value = data.annual_limit || 0;
            form.querySelector('select[name="is_active"]').value = data.is_active ? '1' : '0';
            
            // Set checkboxes - properly handle boolean values
            const checkboxFields = [
                'is_accrued',
                'is_cumulative',
                'requires_6_months',
                'is_monetizable',
                'requires_attachment'
            ];
            
            checkboxFields.forEach(fieldName => {
                const checkbox = form.querySelector(`input[name="${fieldName}"][type="checkbox"]`);
                if (checkbox) {
                    // Ensure we're checking the actual boolean value
                    checkbox.checked = data[fieldName] === true || data[fieldName] === 1 || data[fieldName] === '1';
                    console.log(`Set ${fieldName} to ${checkbox.checked} (value was: ${data[fieldName]})`);
                }
            });
            
            // Set textarea
            const attachmentInfo = form.querySelector('textarea[name="attachment_info"]');
            if (attachmentInfo) {
                attachmentInfo.value = data.attachment_info || '';
            }
            
            // Update submit button text
            const submitBtn = form.querySelector('.btn-submit');
            if (submitBtn) {
                submitBtn.textContent = 'Update Leave Type';
            }
            
            // Update file display if document exists
            const fileDisplay = document.getElementById('fileNameDisplay');
            if (fileDisplay) {
                if (data.document_path) {
                    const fileName = data.document_path.split('/').pop();
                    fileDisplay.textContent = 'Current: ' + fileName;
                } else {
                    fileDisplay.textContent = 'Choose PDF file or drag here';
                }
            }
            
            // Open the modal
            const modal = document.getElementById('addLeaveTypeModal');
            if (modal) {
                modal.classList.add('active');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        })
        .catch(error => {
            console.error('Error loading leave type:', error);
            alert('Failed to load leave type for editing. Please try again.');
        });
}

window.searchLeaveTypes = function() {
    const searchValue = document.getElementById('searchLeaveTypes').value.toLowerCase();
    const rows = document.querySelectorAll('.leave-type-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchValue)) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

window.filterLeaveTypes = function() {
    const statusFilter = document.getElementById('filterLeaveTypeStatus').value;
    const accrualFilter = document.getElementById('filterLeaveTypeAccrual').value;
    const rows = document.querySelectorAll('.leave-type-row');

    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const accrual = row.getAttribute('data-accrual');

        let showRow = true;

        if (statusFilter !== 'all' && status !== statusFilter) {
            showRow = false;
        }

        if (accrualFilter !== 'all' && accrual !== accrualFilter) {
            showRow = false;
        }

        if (showRow) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

window.updateFileName = function(input) {
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSize = (file.size / 1024 / 1024).toFixed(2);

        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit. Please choose a smaller file.');
            input.value = '';
            fileNameDisplay.textContent = 'Choose PDF file or drag here';
            return;
        }

        fileNameDisplay.textContent = file.name + ' (' + fileSize + ' MB)';
    } else {
        fileNameDisplay.textContent = 'Choose PDF file or drag here';
    }
}

window.sortLeaveTypes = function(column) {
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort_by');
    const currentOrder = urlParams.get('sort_order') || 'asc';
    
    let newOrder = 'asc';
    if (currentSort === column && currentOrder === 'asc') {
        newOrder = 'desc';
    }
    
    urlParams.set('sort_by', column);
    urlParams.set('sort_order', newOrder);
    urlParams.set('tab', 'types');
    
    window.location.search = urlParams.toString();
}

window.changePerPage = function(perPage) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('per_page', perPage);
    urlParams.delete('page');
    urlParams.set('tab', 'types');
    
    window.location.search = urlParams.toString();
}

window.navigateToPage = function(url) {
    const urlObj = new URL(url, window.location.origin);
    urlObj.searchParams.set('tab', 'types');
    window.location.href = urlObj.toString();
}

// Leave Request Pagination Functions
let leaveRequestCurrentPage = 1;
let leaveRequestRowsPerPage = 10;
let leaveRequestTotalRows = 0;

window.changeLeaveRequestRowsPerPage = function() {
    leaveRequestRowsPerPage = parseInt(document.getElementById('leaveRequestRowsPerPage').value);
    leaveRequestCurrentPage = 1;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
};

window.renderLeaveRequestPagination = function() {
    const totalPages = Math.ceil(leaveRequestTotalRows / leaveRequestRowsPerPage);
    const paginationControls = document.getElementById('leaveRequestPaginationControls');
    if (!paginationControls) return;
    
    let html = '';
    
    html += `<button class="page-btn" ${leaveRequestCurrentPage === 1 ? 'disabled' : ''} onclick="changeLeaveRequestPage(${leaveRequestCurrentPage - 1})">‹</button>`;
    
    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === leaveRequestCurrentPage ? 'active' : ''}" onclick="changeLeaveRequestPage(${i})">${i}</button>`;
    }
    
    html += `<button class="page-btn" ${leaveRequestCurrentPage === totalPages ? 'disabled' : ''} onclick="changeLeaveRequestPage(${leaveRequestCurrentPage + 1})">›</button>`;
    
    paginationControls.innerHTML = html;
};

window.changeLeaveRequestPage = function(page) {
    const totalPages = Math.ceil(leaveRequestTotalRows / leaveRequestRowsPerPage);
    if (page < 1 || page > totalPages) return;
    leaveRequestCurrentPage = page;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
};

window.paginateLeaveRequestTable = function() {
    const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
    const start = (leaveRequestCurrentPage - 1) * leaveRequestRowsPerPage;
    const end = start + leaveRequestRowsPerPage;
    let visibleCount = 0;
    
    rows.forEach((row, index) => {
        if (row.querySelector('.emp-cell')) {
            if (index >= start && index < end && row.style.display !== 'none') {
                row.style.display = '';
                visibleCount++;
            } else if (index < start || index >= end) {
                row.style.display = 'none';
            }
        }
    });
    
    const startEl = document.getElementById('leaveRequestRowStart');
    const endEl = document.getElementById('leaveRequestRowEnd');
    if (startEl) startEl.textContent = visibleCount > 0 ? start + 1 : 0;
    if (endEl) endEl.textContent = start + visibleCount;
};

window.applyAdminLeaveFilters = function() {
    const department = document.getElementById('filterDepartment')?.value || '';
    const type = document.getElementById('filterLeaveType')?.value || '';
    const status = document.getElementById('filterLeaveStatus')?.value || '';
    const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
    let visible = 0;
    
    rows.forEach(row => {
        if (row.querySelector('.emp-cell')) {
            const matchDept = !department || row.dataset.department === department;
            const matchType = !type || row.dataset.type === type;
            const matchStatus = !status || row.dataset.status === status;
            const show = matchDept && matchType && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        }
    });
    
    const total = rows.length - (rows[0]?.querySelector('.emp-cell') ? 0 : 1);
    
    leaveRequestTotalRows = visible;
    leaveRequestCurrentPage = 1;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
    
    const totalEl = document.getElementById('leaveRequestRowTotal');
    if (totalEl) totalEl.textContent = total;
};

window.openImportLeaveRecordsModal = function() {
    const modal = document.getElementById('importLeaveRecordsModal');
    if (!modal) {
        return;
    }

    const form = document.getElementById('importLeaveRecordsForm');
    if (form) {
        form.reset();
    }

    modal.classList.add('active');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeImportLeaveRecordsModal = function(event) {
    if (event && event.target && event.target.id !== 'importLeaveRecordsModal' && event.type === 'click') {
        return;
    }

    const modal = document.getElementById('importLeaveRecordsModal');
    if (!modal) {
        return;
    }

    modal.classList.remove('active');
    modal.style.display = 'none';
    document.body.style.overflow = '';

    const form = document.getElementById('importLeaveRecordsForm');
    if (form) {
        form.reset();
    }

    const submitBtn = document.getElementById('importSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
    }
};

window.submitImportLeaveRecords = function() {
    const modal = document.getElementById('importLeaveRecordsModal');
    const employeeId = document.getElementById('importEmployeeId')?.value;
    const excelFile = document.getElementById('importExcelFile')?.files?.[0];
    const submitBtn = document.getElementById('importSubmitBtn');
    const csrfToken = document.querySelector('#importLeaveRecordsForm input[name="_token"]')?.value
        || document.querySelector('meta[name="csrf-token"]')?.content;

    if (!employeeId) {
        openErrorModal('Please select an employee.');
        return;
    }

    if (!excelFile) {
        openErrorModal('Please select an Excel file.');
        return;
    }

    if (excelFile.size > 5 * 1024 * 1024) {
        openErrorModal('File size exceeds 5MB limit.');
        return;
    }

    if (!modal?.dataset.importUrl) {
        openErrorModal('Import URL is not configured.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.querySelector('.btn-text').style.display = 'none';
    submitBtn.querySelector('.btn-loader').style.display = 'inline';

    const formData = new FormData();
    formData.append('employee_id', employeeId);
    formData.append('excel_file', excelFile);
    formData.append('_token', csrfToken);

    fetch(modal.dataset.importUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw data;
            });
        }
        return response.json();
    })
    .then(data => {
        closeImportLeaveRecordsModal();
        window.successModalRedirectUrl = modal.dataset.redirectUrl || null;
        openSuccessModal(data.message || 'Leave records imported successfully!');

        if (window.successModalRedirectUrl) {
            setTimeout(() => {
                window.location.href = window.successModalRedirectUrl;
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Import error:', error);

        submitBtn.disabled = false;
        submitBtn.querySelector('.btn-text').style.display = 'inline';
        submitBtn.querySelector('.btn-loader').style.display = 'none';

        let errorMessage = 'Failed to import leave records. Please try again.';
        if (error.message) {
            errorMessage = error.message;
        } else if (error.errors) {
            errorMessage = Object.values(error.errors).flat()[0];
        }

        openErrorModal(errorMessage);
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const importModal = document.getElementById('importLeaveRecordsModal');
    if (importModal) {
        importModal.addEventListener('click', function(event) {
            if (event.target === importModal) {
                closeImportLeaveRecordsModal(event);
            }
        });
    }
    
    const migrateModal = document.getElementById('migrateLeaveRecordsModal');
    if (migrateModal) {
        migrateModal.addEventListener('click', function(event) {
            if (event.target === migrateModal) {
                closeMigrateLeaveRecordsModal(event);
            }
        });
    }
});

window.openMigrateLeaveRecordsModal = function() {
    const modal = document.getElementById('migrateLeaveRecordsModal');
    if (!modal) {
        console.error('Migrate modal not found!');
        return;
    }

    const form = document.getElementById('migrateLeaveRecordsForm');
    if (form) {
        form.reset();
    }

    modal.classList.add('active');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeMigrateLeaveRecordsModal = function(event) {
    if (event && event.target && event.target.id !== 'migrateLeaveRecordsModal' && event.type === 'click') {
        return;
    }

    const modal = document.getElementById('migrateLeaveRecordsModal');
    if (!modal) {
        return;
    }

    modal.classList.remove('active');
    modal.style.display = 'none';
    document.body.style.overflow = '';

    const form = document.getElementById('migrateLeaveRecordsForm');
    if (form) {
        form.reset();
    }

    const submitBtn = document.getElementById('migrateSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
    }
};

window.submitMigrateLeaveRecords = function() {
    const modal = document.getElementById('migrateLeaveRecordsModal');
    const employeeId = document.getElementById('migrateEmployeeId')?.value;
    const excelFile = document.getElementById('migrateExcelFile')?.files?.[0];
    const submitBtn = document.getElementById('migrateSubmitBtn');
    const csrfToken = document.querySelector('#migrateLeaveRecordsForm input[name="_token"]')?.value
        || document.querySelector('meta[name="csrf-token"]')?.content;

    if (!employeeId) {
        openErrorModal('Please select an employee.');
        return;
    }

    if (!excelFile) {
        openErrorModal('Please select an Excel file.');
        return;
    }

    if (excelFile.size > 5 * 1024 * 1024) {
        openErrorModal('File size exceeds 5MB limit.');
        return;
    }

    if (!modal?.dataset.importUrl) {
        openErrorModal('Import URL is not configured.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.querySelector('.btn-text').style.display = 'none';
    submitBtn.querySelector('.btn-loader').style.display = 'inline';

    const formData = new FormData();
    formData.append('employee_id', employeeId);
    formData.append('excel_file', excelFile);
    formData.append('_token', csrfToken);

    fetch(modal.dataset.importUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw data;
            });
        }
        return response.json();
    })
    .then(data => {
        closeMigrateLeaveRecordsModal();
        window.successModalRedirectUrl = modal.dataset.redirectUrl || null;
        openSuccessModal(data.message || 'Leave records migrated successfully!');

        if (window.successModalRedirectUrl) {
            setTimeout(() => {
                window.location.href = window.successModalRedirectUrl;
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Migration error:', error);

        submitBtn.disabled = false;
        submitBtn.querySelector('.btn-text').style.display = 'inline';
        submitBtn.querySelector('.btn-loader').style.display = 'none';

        let errorMessage = 'Failed to migrate leave records. Please try again.';
        if (error.message) {
            errorMessage = error.message;
        } else if (error.errors) {
            errorMessage = Object.values(error.errors).flat()[0];
        }

        openErrorModal(errorMessage);
    });
};


window.downloadLeaveTemplate = function() {
    const link = document.createElement('a');
    link.href = '/admin/leave/download-template';
    link.click();
};
