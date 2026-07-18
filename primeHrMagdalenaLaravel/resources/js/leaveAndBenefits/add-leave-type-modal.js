// Add/Edit Leave Type Modal

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

window.editLeaveType = function(code) {
    fetch(`/admin/leave/types/${code}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch leave type');
            }
            return response.json();
        })
        .then(data => {
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

// Handle form submission with AJAX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addLeaveTypeForm');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = form.querySelector('.btn-submit');
            const originalText = submitBtn.textContent;

            // Disable submit button and show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';

            // Create FormData object
            const formData = new FormData(form);

            // Send AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
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
                // Close add modal
                closeAddLeaveTypeModal();

                // Show success modal
                const redirectUrl = document.getElementById('successModal')?.dataset.defaultRedirect;
                openSuccessModal(data.message || 'Leave type registered successfully!', redirectUrl);
            })
            .catch(error => {
                console.error('Error:', error);

                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;

                // Close add modal
                closeAddLeaveTypeModal();

                // Show error modal
                let errorMessage = 'Failed to register leave type. Please try again.';

                if (error.errors) {
                    // Laravel validation errors
                    const firstError = Object.values(error.errors)[0];
                    errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                } else if (error.message) {
                    errorMessage = error.message;
                }

                openErrorModal(errorMessage);
            });
        });
    }
});
