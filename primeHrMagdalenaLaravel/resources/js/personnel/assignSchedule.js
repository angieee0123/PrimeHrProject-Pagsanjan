// Assign Schedule Modal

function closeAssignScheduleModal() {
    document.getElementById('assignScheduleModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('scheduleOverlapWarning').style.display = 'none';
}

function validateScheduleDates() {
    const employeeId = document.getElementById('scheduleEmployeeId').value;
    const scheduleId = document.getElementById('scheduleId').value;
    const startDate = document.getElementById('scheduleStartDate').value;
    const endDate = document.getElementById('scheduleEndDate').value;

    if (!startDate || !endDate) {
        return true;
    }

    // Check for overlaps via AJAX
    const formData = new FormData();
    formData.append('employee_id', employeeId);
    formData.append('schedule_id', scheduleId);
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);

    fetch('/admin/schedules/check-overlap', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.has_overlap) {
            document.getElementById('scheduleOverlapWarning').style.display = 'block';
            document.getElementById('overlapDetails').textContent = data.overlap_details;
        } else {
            document.getElementById('scheduleOverlapWarning').style.display = 'none';
        }
    });

    return true;
}

window.closeAssignScheduleModal = closeAssignScheduleModal;
window.validateScheduleDates = validateScheduleDates;
