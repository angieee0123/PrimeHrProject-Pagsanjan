// Bulk Import Attendance — mirrors resources/js/admin/personnel/adminPersonnel.js bulk import

function openBulkImportAttendanceModal() {
    const modal = document.getElementById('bulkImportAttendanceModal');
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeBulkImportAttendanceModal() {
    const modal = document.getElementById('bulkImportAttendanceModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('bulkImportAttendanceForm')?.reset();
    const fileInfo = document.getElementById('attendanceFileInfo');
    if (fileInfo) fileInfo.style.display = 'none';
    const dz = document.getElementById('attendanceDropZone');
    if (dz) { dz.style.borderColor = 'var(--theme-neutral-300)'; dz.style.background = 'var(--gp-bg-tint)'; }
}

function downloadAttendanceTemplate() {
    const headers = ['employee_id', 'date', 'am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out'];
    const sampleRows = [
        ['EMP-2024-001', '2026-05-01', '08:00', '12:00', '13:00', '17:00', '', ''],
        ['EMP-2024-001', '2026-05-02', '08:05', '12:00', '13:00', '17:30', '17:30', '18:30'],
        ['EMP-2024-002', '2026-05-01', '07:55', '12:00', '13:00', '17:00', '', ''],
    ];
    const csv = [headers.join(','), ...sampleRows.map(r => r.join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'Attendance_Import_Template.csv';
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function handleAttendanceFileSelect(event) {
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
    document.getElementById('attendanceFileName').textContent = file.name;
    document.getElementById('attendanceFileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';
    document.getElementById('attendanceFileInfo').style.display = 'block';
    const dz = document.getElementById('attendanceDropZone');
    dz.style.borderColor = 'var(--theme-success)';
    dz.style.background = 'var(--theme-success-subtle)';
}

function removeAttendanceFile() {
    const input = document.getElementById('attendanceCsvFile');
    if (input) input.value = '';
    document.getElementById('attendanceFileInfo').style.display = 'none';
    const dz = document.getElementById('attendanceDropZone');
    dz.style.borderColor = 'var(--theme-neutral-300)';
    dz.style.background = 'var(--gp-bg-tint)';
}

function submitBulkImportAttendance() {
    const fileInput = document.getElementById('attendanceCsvFile');
    if (!fileInput || !fileInput.files.length) {
        alert('Please select a CSV file to upload.');
        return;
    }

    const formData = new FormData();
    formData.append('csv_file', fileInput.files[0]);
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    if (token) formData.append('_token', token);

    const submitBtn = document.querySelector('#bulkImportAttendanceModal button[onclick="submitBulkImportAttendance()"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Importing...';
    }

    fetch('/admin/attendance/bulk-import', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Import failed');
            return data;
        })
        .then(data => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalText; }
            closeBulkImportAttendanceModal();

            // Use the attendance success modal if present, else fallback to the generic one / alert
            const successModal = document.getElementById('successModal');
            const successMessage = document.getElementById('successMessage');
            if (successModal && successMessage) {
                successMessage.textContent = data.message || 'Attendance imported successfully!';
                successModal.style.display = 'flex';
                setTimeout(() => location.reload(), 2000);
            } else {
                alert(data.message || 'Attendance imported successfully!');
                location.reload();
            }
        })
        .catch(error => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalText; }
            const errorModal = document.getElementById('errorModal');
            const errorMessage = document.getElementById('errorMessage');
            const msg = error.message || 'An error occurred during import. Please try again.';
            if (errorModal && errorMessage) {
                errorMessage.textContent = msg;
                errorModal.style.display = 'flex';
            } else {
                // attendance pages use successModal; reuse it for errors as well
                const successModal = document.getElementById('successModal');
                const successMessage = document.getElementById('successMessage');
                if (successModal && successMessage) {
                    successMessage.textContent = msg;
                    successModal.style.display = 'flex';
                } else {
                    alert(msg);
                }
            }
        });
}

// Drag & drop
const attendanceDropZone = document.getElementById('attendanceDropZone');
if (attendanceDropZone) {
    attendanceDropZone.addEventListener('click', () => {
        document.getElementById('attendanceCsvFile')?.click();
    });
    attendanceDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        attendanceDropZone.style.borderColor = 'var(--gp-pri)';
        attendanceDropZone.style.background = 'var(--theme-primary-light)';
    });
    attendanceDropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        attendanceDropZone.style.borderColor = 'var(--theme-neutral-300)';
        attendanceDropZone.style.background = 'var(--gp-bg-tint)';
    });
    attendanceDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        attendanceDropZone.style.borderColor = 'var(--theme-neutral-300)';
        attendanceDropZone.style.background = 'var(--gp-bg-tint)';
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                document.getElementById('attendanceCsvFile').files = files;
                handleAttendanceFileSelect({ target: { files } });
            } else {
                alert('Please upload a CSV file only.');
            }
        }
    });
}

// Close on backdrop click / Esc
document.getElementById('bulkImportAttendanceModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'bulkImportAttendanceModal') closeBulkImportAttendanceModal();
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const m = document.getElementById('bulkImportAttendanceModal');
        if (m && m.style.display === 'flex') closeBulkImportAttendanceModal();
    }
});

window.openBulkImportAttendanceModal = openBulkImportAttendanceModal;
window.closeBulkImportAttendanceModal = closeBulkImportAttendanceModal;
window.downloadAttendanceTemplate = downloadAttendanceTemplate;
window.handleAttendanceFileSelect = handleAttendanceFileSelect;
window.removeAttendanceFile = removeAttendanceFile;
window.submitBulkImportAttendance = submitBulkImportAttendance;
