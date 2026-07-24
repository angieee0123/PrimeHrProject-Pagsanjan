// Assign Schedule Modal
import { initBusyDateRange } from '../../shared/busyDatesCalendar.js';

// Busy-date calendar for the schedule effectivity range. Marks are
// INFORMATIONAL only (no blockKind): a schedule period routinely spans an
// employee's leave and travel days — blocking them would make most realistic
// ranges unselectable, since flatpickr's range mode refuses to span a disabled
// day. minDate null because schedules can be backdated.
// openAssignScheduleModal() (adminPersonnel.js) drives this via setEmployee().
document.addEventListener('DOMContentLoaded', function () {
    window.scheduleBusyCal = initBusyDateRange({
        fromId: 'scheduleStartDate',
        toId: 'scheduleEndDate',
        scope: 'admin',
        minDate: null,
        onChange: () => validateScheduleDates(),
    });
});

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
