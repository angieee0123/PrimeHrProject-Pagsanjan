// View Employee Schedules Modal
let currentViewEmployeeId = null;
let currentViewEmployeeName = null;

function viewEmployeeSchedules(employeeId, employeeName) {
    currentViewEmployeeId = employeeId;
    currentViewEmployeeName = employeeName;

    document.getElementById('viewSchedulesEmployeeName').textContent = employeeName;
    document.getElementById('viewSchedulesModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Fetch schedules
    fetch(`/admin/schedules/employee/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            displaySchedules(data.schedules);
        })
        .catch(error => {
            console.error('Error fetching schedules:', error);
            document.getElementById('schedulesListContainer').innerHTML = `
                <div style="text-align:center; padding:40px; color:var(--theme-danger);">
                    <p>Failed to load schedules. Please try again.</p>
                </div>
            `;
        });
}

function displaySchedules(schedules) {
    const container = document.getElementById('schedulesListContainer');

    if (schedules.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:40px; color:var(--gp-text-mid);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 16px; opacity:0.3;">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <p style="margin:0; font-size:14px; font-weight:600;">No schedules assigned yet</p>
                <p style="margin:8px 0 0; font-size:12px;">Click "Add New Schedule" to create one.</p>
            </div>
        `;
        return;
    }

    const today = new Date().toISOString().split('T')[0];

    let html = '';
    schedules.forEach((schedule, index) => {
        const isActive = schedule.start_date <= today && schedule.end_date >= today;
        const isPast = schedule.end_date < today;
        const isFuture = schedule.start_date > today;

        let statusBadge = '';
        let statusColor = '';

        if (isActive) {
            statusBadge = 'Active';
            statusColor = 'var(--theme-success)';
        } else if (isFuture) {
            statusBadge = 'Upcoming';
            statusColor = 'var(--theme-warning)';
        } else {
            statusBadge = 'Expired';
            statusColor = 'var(--gp-text-mid)';
        }

        html += `
            <div style="background:${isActive ? '#f0fdf4' : 'var(--gp-bg-tint)'}; border:1.5px solid ${isActive ? '#bbf7d0' : 'var(--gp-border)'}; border-radius:10px; padding:16px; margin-bottom:12px;">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <span style="display:inline-block; padding:4px 10px; background:${statusColor}15; color:${statusColor}; border-radius:6px; font-size:11px; font-weight:700; letter-spacing:0.5px;">
                                ${statusBadge}
                            </span>
                        </div>
                        <p style="margin:0; font-size:13px; color:var(--gp-text-mid);">
                            <strong style="color:var(--gp-pri);">${formatDate(schedule.start_date)}</strong> to <strong style="color:var(--gp-pri);">${formatDate(schedule.end_date)}</strong>
                        </p>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button onclick="editSchedule(${schedule.id})" style="padding:6px 12px; background:#fff; border:1.5px solid var(--gp-border); border-radius:6px; font-size:12px; font-weight:600; color:var(--gp-pri); cursor:pointer; font-family:'Poppins',sans-serif;">
                            Edit
                        </button>
                        <button onclick="confirmDeleteSchedule(${schedule.id}, '${formatDate(schedule.start_date)}', '${formatDate(schedule.end_date)}')" style="padding:6px 12px; background:#fee8e8; border:1.5px solid #f5d0ce; border-radius:6px; font-size:12px; font-weight:600; color:var(--theme-danger); cursor:pointer; font-family:'Poppins',sans-serif;">
                            Delete
                        </button>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:12px;">
                    <div style="background:#fff; border-radius:8px; padding:12px;">
                        <p style="margin:0 0 8px; font-size:10px; font-weight:700; letter-spacing:1px; color:var(--gp-text-soft);">MORNING SHIFT</p>
                        <div style="display:flex; gap:12px;">
                            <div style="flex:1;">
                                <p style="margin:0 0 4px; font-size:11px; color:var(--gp-text-mid);">Time In</p>
                                <p style="margin:0; font-size:15px; font-weight:700; color:var(--gp-pri);">${formatTime12h(schedule.am_in)}</p>
                            </div>
                            <div style="flex:1;">
                                <p style="margin:0 0 4px; font-size:11px; color:var(--gp-text-mid);">Time Out</p>
                                <p style="margin:0; font-size:15px; font-weight:700; color:var(--gp-pri);">${formatTime12h(schedule.am_out)}</p>
                            </div>
                        </div>
                    </div>

                    <div style="background:#fff; border-radius:8px; padding:12px;">
                        <p style="margin:0 0 8px; font-size:10px; font-weight:700; letter-spacing:1px; color:var(--gp-text-soft);">AFTERNOON SHIFT</p>
                        <div style="display:flex; gap:12px;">
                            <div style="flex:1;">
                                <p style="margin:0 0 4px; font-size:11px; color:var(--gp-text-mid);">Time In</p>
                                <p style="margin:0; font-size:15px; font-weight:700; color:var(--gp-pri);">${formatTime12h(schedule.pm_in)}</p>
                            </div>
                            <div style="flex:1;">
                                <p style="margin:0 0 4px; font-size:11px; color:var(--gp-text-mid);">Time Out</p>
                                <p style="margin:0; font-size:15px; font-weight:700; color:var(--gp-pri);">${formatTime12h(schedule.pm_out)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatTime12h(timeStr) {
    if (!timeStr) return '--';
    const [h, m] = timeStr.split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}

function closeViewSchedulesModal() {
    document.getElementById('viewSchedulesModal').style.display = 'none';
    document.body.style.overflow = '';
}

function openAddScheduleFromView() {
    closeViewSchedulesModal();
    openAssignScheduleModal(currentViewEmployeeId, currentViewEmployeeName, null);
}

function editSchedule(scheduleId) {
    fetch(`/admin/schedules/${scheduleId}`)
        .then(response => response.json())
        .then(schedule => {
            closeViewSchedulesModal();
            openAssignScheduleModal(schedule.employee_id, currentViewEmployeeName, schedule);
        })
        .catch(error => {
            console.error('Error fetching schedule:', error);
            alert('Failed to load schedule details.');
        });
}

function confirmDeleteSchedule(scheduleId, startDate, endDate) {
    if (confirm(`Are you sure you want to delete the schedule from ${startDate} to ${endDate}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/schedules/${scheduleId}/delete`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

window.viewEmployeeSchedules = viewEmployeeSchedules;
window.closeViewSchedulesModal = closeViewSchedulesModal;
window.openAddScheduleFromView = openAddScheduleFromView;
window.editSchedule = editSchedule;
window.confirmDeleteSchedule = confirmDeleteSchedule;
