document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('generatePayrollForm');
    const startDateInput = form.querySelector('[name="start_date"]');
    const endDateInput = form.querySelector('[name="end_date"]');
    const deptFilter = form.querySelector('[name="department"]');
    const empStatusFilter = form.querySelector('[name="employment_status"]');

    function updatePreview() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const department = deptFilter.value;
        const employmentStatus = empStatusFilter.value;

        if (!startDate || !endDate) return;

        // Fetch preview data
        fetch(`${window.payrollRoutes.preview}?start_date=${startDate}&end_date=${endDate}&department=${department}&employment_status=${employmentStatus}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('previewEmployees').textContent = data.employee_count;
                document.getElementById('previewGross').textContent = '₱' + parseFloat(data.estimated_gross).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('previewDeductions').textContent = '₱' + parseFloat(data.estimated_deductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('previewNet').textContent = '₱' + parseFloat(data.estimated_net).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            })
            .catch(error => console.error('Error fetching preview:', error));
    }

    // Add event listeners
    startDateInput.addEventListener('change', updatePreview);
    endDateInput.addEventListener('change', updatePreview);
    deptFilter.addEventListener('change', updatePreview);
    empStatusFilter.addEventListener('change', updatePreview);

    // Initial load
    updatePreview();
});

function handleGeneratePayroll(event) {
    event.preventDefault();

    const form = document.getElementById('generatePayrollForm');
    const formData = new FormData(form);
    const generateBtn = document.getElementById('generateBtn');

    // Validate dates
    const startDate = form.querySelector('[name="start_date"]').value;
    const endDate = form.querySelector('[name="end_date"]').value;
    const payDate = form.querySelector('[name="pay_date"]').value;

    if (!startDate || !endDate || !payDate) {
        showFailedModal({
            message: 'Please fill in all required date fields.',
            errors: ['Start Date, End Date, and Pay Date are required.']
        });
        return false;
    }

    if (new Date(endDate) < new Date(startDate)) {
        showFailedModal({
            message: 'Invalid date range.',
            errors: ['End Date must be after or equal to Start Date.']
        });
        return false;
    }

    // Disable button and show loading
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg> Generating...';

    // Fetch payroll data for preview
    fetch(window.payrollRoutes.calculate, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (data.data.employees && data.data.employees.length > 0) {
                showPayrollModal(data.data);
            } else {
                showFailedModal({
                    message: 'No employees found for the selected criteria.',
                    errors: ['Please adjust your filters and ensure employees have attendance records for the selected period.']
                });
            }
        } else {
            showFailedModal({
                message: data.message || 'Failed to generate payroll',
                errors: data.errors || []
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFailedModal({
            message: error.message || 'Failed to generate payroll. Please try again.',
            errors: error.errors ? Object.values(error.errors).flat() : [error.error || 'An unexpected error occurred']
        });
    })
    .finally(() => {
        // Re-enable button
        generateBtn.disabled = false;
        generateBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Generate Payroll';
    });

    return false;
}

window.handleGeneratePayroll = handleGeneratePayroll;
