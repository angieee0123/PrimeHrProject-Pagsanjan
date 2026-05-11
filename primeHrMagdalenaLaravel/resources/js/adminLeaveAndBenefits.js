// JavaScript for adminLeaveAndBenefits.blade.php

window.switchTab = function(tab) {
    // Update tab buttons
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Find and activate the clicked button
    buttons.forEach(btn => {
        if ((tab === 'leave' && btn.textContent.includes('Leave Requests')) ||
            (tab === 'benefits' && btn.textContent.includes('Benefits Summary')) ||
            (tab === 'types' && btn.textContent.includes('Leave Types'))) {
            btn.classList.add('active');
        }
    });

    // Show/hide tab content
    document.getElementById('leave-tab').style.display = 'none';
    document.getElementById('benefits-tab').style.display = 'none';
    document.getElementById('types-tab').style.display = 'none';

    if (tab === 'leave') {
        document.getElementById('leave-tab').style.display = 'block';
    } else if (tab === 'benefits') {
        document.getElementById('benefits-tab').style.display = 'block';
    } else if (tab === 'types') {
        document.getElementById('types-tab').style.display = 'block';
    }
}

window.openLeaveTypesModal = function() {
    document.getElementById('leaveTypesModal').classList.add('active');
}

window.closeLeaveTypesModal = function(event) {
    if (!event || event.target.id === 'leaveTypesModal') {
        document.getElementById('leaveTypesModal').classList.remove('active');
    }
}

window.openAddLeaveTypeForm = function() {
    alert('Add Leave Type form will be implemented next!');
}

window.openAddLeaveTypeModal = function() {
    // Reset form for adding new leave type
    const form = document.getElementById('addLeaveTypeForm');
    form.reset();
    form.action = '/admin/leave/types';
    
    // Remove method spoofing if exists
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();
    
    // Reset modal title
    document.querySelector('#addLeaveTypeModal .modal-title').textContent = 'Add New Leave Type';
    document.querySelector('#addLeaveTypeModal .modal-subtitle').textContent = 'Create a new leave type for LGU Pagsanjan';
    
    // Reset leave code readonly
    form.querySelector('input[name="leave_code"]').readOnly = false;
    
    // Reset submit button
    form.querySelector('.btn-submit').textContent = 'Add Leave Type';
    
    // Reset file display
    document.getElementById('fileNameDisplay').textContent = 'Choose PDF file or drag here';
    
    document.getElementById('addLeaveTypeModal').classList.add('active');
}

window.closeAddLeaveTypeModal = function(event) {
    if (!event || event.target.id === 'addLeaveTypeModal') {
        document.getElementById('addLeaveTypeModal').classList.remove('active');
    }
}

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

    // Form will submit normally to the server
});

window.viewLeaveType = function(code) {
    fetch(`/admin/leave/types/${code}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewLeaveCode').textContent = data.leave_code;
            document.getElementById('viewLeaveName').textContent = data.leave_name;
            document.getElementById('viewAnnualLimit').textContent = data.annual_limit > 0 ? `${data.annual_limit} days` : 'As needed';
            document.getElementById('viewLeaveTypeAccrual').textContent = data.is_accrued ? 'Accrued' : 'Fixed';
            
            // Status badge
            const statusBadge = document.getElementById('viewLeaveStatus');
            statusBadge.textContent = data.is_active ? 'Active' : 'Inactive';
            statusBadge.className = data.is_active ? 'badge-status processed' : 'badge-status on-hold';

            // Configuration badges
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

            // Attachment info
            const attachmentGroup = document.getElementById('viewAttachmentInfoGroup');
            if (data.attachment_info) {
                document.getElementById('viewAttachmentInfo').textContent = data.attachment_info;
                attachmentGroup.style.display = 'block';
            } else {
                attachmentGroup.style.display = 'none';
            }

            // Document
            const documentGroup = document.getElementById('viewDocumentGroup');
            if (data.document_path) {
                document.getElementById('viewDocumentLink').href = `/storage/${data.document_path}`;
                documentGroup.style.display = 'block';
            } else {
                documentGroup.style.display = 'none';
            }

            // Store code for edit
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
    fetch(`/admin/leave/types/${code}`)
        .then(response => response.json())
        .then(data => {
            // Update modal title
            document.querySelector('#addLeaveTypeModal .modal-title').textContent = 'Edit Leave Type';
            document.querySelector('#addLeaveTypeModal .modal-subtitle').textContent = 'Update leave type configuration';
            
            // Update form action
            const form = document.getElementById('addLeaveTypeForm');
            form.action = `/admin/leave/types/${code}`;
            
            // Add method spoofing for PUT request
            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'PUT';
            
            // Populate form fields
            form.querySelector('input[name="leave_code"]').value = data.leave_code;
            form.querySelector('input[name="leave_code"]').readOnly = true;
            form.querySelector('input[name="leave_name"]').value = data.leave_name;
            form.querySelector('input[name="annual_limit"]').value = data.annual_limit;
            form.querySelector('select[name="is_active"]').value = data.is_active ? '1' : '0';
            
            // Checkboxes
            form.querySelector('input[name="is_accrued"]').checked = data.is_accrued;
            form.querySelector('input[name="is_cumulative"]').checked = data.is_cumulative;
            form.querySelector('input[name="requires_6_months"]').checked = data.requires_6_months;
            form.querySelector('input[name="is_monetizable"]').checked = data.is_monetizable;
            form.querySelector('input[name="requires_attachment"]').checked = data.requires_attachment;
            
            // Textarea
            form.querySelector('textarea[name="attachment_info"]').value = data.attachment_info || '';
            
            // Update submit button
            form.querySelector('.btn-submit').textContent = 'Update Leave Type';
            
            // Show document info if exists
            if (data.document_path) {
                document.getElementById('fileNameDisplay').textContent = 'Current: ' + data.document_path.split('/').pop();
            }
            
            // Open modal
            document.getElementById('addLeaveTypeModal').classList.add('active');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load leave type for editing');
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
    const statusFilter = document.getElementById('filterLeaveStatus').value;
    const accrualFilter = document.getElementById('filterLeaveAccrual').value;
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
    urlParams.set('tab', 'types'); // Preserve tab
    
    window.location.search = urlParams.toString();
}

window.changePerPage = function(perPage) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('per_page', perPage);
    urlParams.delete('page'); // Reset to first page
    urlParams.set('tab', 'types'); // Preserve tab
    
    window.location.search = urlParams.toString();
}

window.navigateToPage = function(url) {
    // Add tab parameter to URL
    const urlObj = new URL(url, window.location.origin);
    urlObj.searchParams.set('tab', 'types');
    window.location.href = urlObj.toString();
}
