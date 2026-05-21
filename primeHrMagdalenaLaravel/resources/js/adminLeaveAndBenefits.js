// JavaScript for adminLeaveAndBenefits.blade.php

window.switchTab = function(tab) {
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    buttons.forEach(btn => {
        if ((tab === 'leave' && btn.textContent.includes('Leave Requests')) ||
            (tab === 'transactions' && btn.textContent.includes('Transaction History')) ||
            (tab === 'benefits' && btn.textContent.includes('Benefits Summary')) ||
            (tab === 'types' && btn.textContent.includes('Leave Types')) ||
            (tab === 'accrual' && btn.textContent.includes('CSC Daily Accrual'))) {
            btn.classList.add('active');
        }
    });

    document.getElementById('leave-tab').style.display = 'none';
    document.getElementById('transactions-tab').style.display = 'none';
    document.getElementById('benefits-tab').style.display = 'none';
    document.getElementById('types-tab').style.display = 'none';
    document.getElementById('accrual-tab').style.display = 'none';

    if (tab === 'leave') {
        document.getElementById('leave-tab').style.display = 'block';
    } else if (tab === 'transactions') {
        document.getElementById('transactions-tab').style.display = 'block';
    } else if (tab === 'benefits') {
        document.getElementById('benefits-tab').style.display = 'block';
    } else if (tab === 'types') {
        document.getElementById('types-tab').style.display = 'block';
    } else if (tab === 'accrual') {
        document.getElementById('accrual-tab').style.display = 'block';
    }
}

window.openAddLeaveTypeModal = function() {
    const form = document.getElementById('addLeaveTypeForm');
    form.reset();
    form.action = '/admin/leave/types';
    
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();
    
    document.querySelector('#addLeaveTypeModal .modal-title').textContent = 'Add New Leave Type';
    document.querySelector('#addLeaveTypeModal .modal-subtitle').textContent = 'Create a new leave type for LGU Pagsanjan';
    
    form.querySelector('input[name="leave_code"]').readOnly = false;
    form.querySelector('.btn-submit').textContent = 'Add Leave Type';
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
