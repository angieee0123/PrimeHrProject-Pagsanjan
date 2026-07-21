// Migrate Leave Records Modal

window.updateMigrateFileName = function(input) {
    const fileNameEl = document.getElementById('migrateFileName');
    const fileNameText = document.getElementById('migrateFileNameText');
    if (input.files && input.files[0]) {
        fileNameText.textContent = input.files[0].name;
        fileNameEl.style.display = 'block';
    } else {
        fileNameEl.style.display = 'none';
    }
}

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

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const migrateModal = document.getElementById('migrateLeaveRecordsModal');
    if (migrateModal) {
        migrateModal.addEventListener('click', function(event) {
            if (event.target === migrateModal) {
                closeMigrateLeaveRecordsModal(event);
            }
        });
    }
});

// Drag and drop support
document.addEventListener('DOMContentLoaded', function() {
    const migrateFileDropZone = document.getElementById('migrateFileDropZone');
    if (migrateFileDropZone) {
        migrateFileDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            migrateFileDropZone.style.background = '#e0f2fe';
            migrateFileDropZone.style.borderColor = '#0284c7';
        });

        migrateFileDropZone.addEventListener('dragleave', () => {
            migrateFileDropZone.style.background = '#f0f9ff';
            migrateFileDropZone.style.borderColor = '#bae6fd';
        });

        migrateFileDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            migrateFileDropZone.style.background = '#f0f9ff';
            migrateFileDropZone.style.borderColor = '#bae6fd';
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('migrateExcelFile').files = files;
                updateMigrateFileName(document.getElementById('migrateExcelFile'));
            }
        });
    }
});
