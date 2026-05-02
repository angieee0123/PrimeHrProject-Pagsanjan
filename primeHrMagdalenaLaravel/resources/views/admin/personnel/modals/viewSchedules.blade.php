<!-- View Employee Schedules Modal -->
<div id="viewSchedulesModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center; overflow-y:auto;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:900px; margin:20px; box-shadow:0 8px 32px rgba(11,4,77,0.2); max-height:90vh; display:flex; flex-direction:column;">
        <div style="background:linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding:20px 24px; border-radius:12px 12px 0 0; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; background:rgba(255,255,255,0.12); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <p style="margin:0; font-size:10px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">WORK SCHEDULES</p>
                    <h3 style="margin:0; font-size:16px; font-weight:700; color:#fff;" id="viewSchedulesEmployeeName">Employee Name</h3>
                </div>
            </div>
            <button onclick="closeViewSchedulesModal()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px;">&times;</button>
        </div>

        <div style="padding:24px; overflow-y:auto; flex:1;">
            <div id="schedulesListContainer">
                <!-- Schedules will be loaded here -->
            </div>
        </div>

        <div style="padding:16px 24px; border-top:1px solid #f0effe; display:flex; justify-content:flex-end; gap:10px;">
            <button onclick="closeViewSchedulesModal()" style="padding:10px 24px; background:#fff; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-weight:600; color:#6b6a8a; cursor:pointer; font-family:'Poppins',sans-serif;">
                Close
            </button>
            <button onclick="openAddScheduleFromView()" style="padding:10px 24px; background:#0b044d; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#fff; cursor:pointer; font-family:'Poppins',sans-serif; display:flex; align-items:center; gap:8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add New Schedule
            </button>
        </div>
    </div>
</div>

<script>
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
                <div style="text-align:center; padding:40px; color:#8e1e18;">
                    <p>Failed to load schedules. Please try again.</p>
                </div>
            `;
        });
}

function displaySchedules(schedules) {
    const container = document.getElementById('schedulesListContainer');
    
    if (schedules.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:40px; color:#6b6a8a;">
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
            statusColor = '#15803d';
        } else if (isFuture) {
            statusBadge = 'Upcoming';
            statusColor = '#d9bb00';
        } else {
            statusBadge = 'Expired';
            statusColor = '#6b6a8a';
        }
        
        html += `
            <div style="background:${isActive ? '#f0fdf4' : '#f7f6ff'}; border:1.5px solid ${isActive ? '#bbf7d0' : '#e8e7f5'}; border-radius:10px; padding:16px; margin-bottom:12px;">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <span style="display:inline-block; padding:4px 10px; background:${statusColor}15; color:${statusColor}; border-radius:6px; font-size:11px; font-weight:700; letter-spacing:0.5px;">
                                ${statusBadge}
                            </span>
                        </div>
                        <p style="margin:0; font-size:13px; color:#6b6a8a;">
                            <strong style="color:#0b044d;">${formatDate(schedule.start_date)}</strong> to <strong style="color:#0b044d;">${formatDate(schedule.end_date)}</strong>
                        </p>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button onclick="editSchedule(${schedule.id})" style="padding:6px 12px; background:#fff; border:1.5px solid #e8e7f5; border-radius:6px; font-size:12px; font-weight:600; color:#0b044d; cursor:pointer; font-family:'Poppins',sans-serif;">
                            Edit
                        </button>
                        <button onclick="confirmDeleteSchedule(${schedule.id}, '${formatDate(schedule.start_date)}', '${formatDate(schedule.end_date)}')" style="padding:6px 12px; background:#fee8e8; border:1.5px solid #f5d0ce; border-radius:6px; font-size:12px; font-weight:600; color:#8e1e18; cursor:pointer; font-family:'Poppins',sans-serif;">
                            Delete
                        </button>
                    </div>
                </div>
                
                <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:12px;">
                    <div style="background:#fff; border-radius:8px; padding:12px;">
                        <p style="margin:0 0 8px; font-size:10px; font-weight:700; letter-spacing:1px; color:#9999bb;">MORNING SHIFT</p>
                        <div style="display:flex; gap:12px;">
                            <div style="flex:1;">
                                <p style="margin:0 0 4px; font-size:11px; color:#6b6a8a;">Time In</p>
                                <p style="margin:0; font-size:15px; font-weight:700; color:#0b044d;">${schedule.am_in}</p>
                            </div>
                            <div style="flex:1;">
                                <p style="margin:0 0 4px; font-size:11px; color:#6b6a8a;">Time Out</p>
                                <p style="margin:0; font-size:15px; font-weight:700; color:#0b044d;">${schedule.am_out}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background:#fff; border-radius:8px; padding:12px;">
                        <p style="margin:0 0 8px; font-size:10px; font-weight:700; letter-spacing:1px; color:#9999bb;">AFTERNOON SHIFT</p>
                        <div style="display:flex; gap:12px;">
                            <div style="flex:1;">
                                <p style="margin:0 0 4px; font-size:11px; color:#6b6a8a;">Time In</p>
                                <p style="margin:0; font-size:15px; font-weight:700; color:#0b044d;">${schedule.pm_in}</p>
                            </div>
                            <div style="flex:1;">
                                <p style="margin:0 0 4px; font-size:11px; color:#6b6a8a;">Time Out</p>
                                <p style="margin:0; font-size:15px; font-weight:700; color:#0b044d;">${schedule.pm_out}</p>
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
        csrfToken.value = '{{ csrf_token() }}';
        
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
</script>
