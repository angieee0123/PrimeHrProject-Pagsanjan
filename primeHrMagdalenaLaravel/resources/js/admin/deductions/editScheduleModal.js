// Sample data - replace with actual data from backend
const deductionSchedules = {
    'GSIS': {
        id: 1,
        name: 'GSIS Contribution',
        schedule: '1ST_ONLY',
        priority: 1,
        notes: ''
    },
    'PHILHEALTH': {
        id: 2,
        name: 'PhilHealth Contribution',
        schedule: '1ST_ONLY',
        priority: 2,
        notes: ''
    },
    'PAGIBIG': {
        id: 3,
        name: 'Pag-IBIG Contribution',
        schedule: '2ND_ONLY',
        priority: 3,
        notes: ''
    },
    'WTAX': {
        id: 4,
        name: 'Withholding Tax',
        schedule: 'BOTH_SPLIT',
        priority: 4,
        notes: ''
    }
};

function editSchedule(code) {
    const schedule = deductionSchedules[code];
    if (!schedule) return;

    // Populate form
    document.getElementById('scheduleDeductionTypeId').value = schedule.id;
    document.getElementById('scheduleDeductionName').value = schedule.name;
    document.getElementById('schedulePriority').value = schedule.priority;
    document.getElementById('scheduleNotes').value = schedule.notes;

    // Set radio button
    const radioButton = document.querySelector(`input[name="cutoff_schedule"][value="${schedule.schedule}"]`);
    if (radioButton) {
        radioButton.checked = true;
    }

    // Open modal
    document.getElementById('editScheduleModal').classList.add('active');
}

function closeEditScheduleModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('editScheduleModal').classList.remove('active');
    document.getElementById('editScheduleForm').reset();
}

function handleScheduleUpdate(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = {
        deduction_type_id: formData.get('deduction_type_id'),
        cutoff_schedule: formData.get('cutoff_schedule'),
        priority: formData.get('priority'),
        notes: formData.get('notes')
    };

    // TODO: Send to backend when route is created
    console.log('Schedule update data:', data);

    // For now, just show success message and close modal
    alert('Schedule updated successfully! (Backend integration pending)');
    closeEditScheduleModal();

    // Reload page to show changes (when backend is ready)
    // window.location.reload();
}

window.editSchedule = editSchedule;
window.closeEditScheduleModal = closeEditScheduleModal;
window.handleScheduleUpdate = handleScheduleUpdate;
