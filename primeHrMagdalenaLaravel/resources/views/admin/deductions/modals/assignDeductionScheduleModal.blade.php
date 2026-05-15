<!-- Assign Deduction Schedule Modal -->
<div id="assignDeductionScheduleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:650px; max-height:90vh; box-shadow:0 8px 32px rgba(11,4,77,0.2); overflow:hidden; display:flex; flex-direction:column;">
        <div style="background:linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
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
                    <p style="margin:0; font-size:10px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">DEDUCTION SCHEDULE</p>
                    <h3 style="margin:0; font-size:16px; font-weight:700; color:#fff;" id="deductionScheduleEmployeeName">Employee Name</h3>
                </div>
            </div>
            <button onclick="closeAssignDeductionScheduleModal()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px;">&times;</button>
        </div>

        <form id="assignDeductionScheduleForm" onsubmit="handleDeductionScheduleSubmit(event)">
            <input type="hidden" name="employee_id" id="deductionScheduleEmployeeId">
            
            <div style="padding:24px; overflow-y:auto; flex:1;">
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:8px;">
                        Effective Period <span style="color:#8e1e18;">*</span>
                    </label>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                        <div>
                            <label style="display:block; font-size:11px; font-weight:600; color:#6b6a8a; margin-bottom:6px;">From Month</label>
                            <input type="month" name="start_month" id="startMonth" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; font-weight:600; color:#6b6a8a; margin-bottom:6px;">To Month</label>
                            <input type="month" name="end_month" id="endMonth" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                    </div>
                </div>

                <div id="existingSchedulesSection" style="display:none; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <label style="font-size:12px; font-weight:600; color:#0b044d;">Existing Schedules</label>
                        <button type="button" onclick="toggleScheduleHistory()" style="background:transparent; border:none; color:#0b044d; font-size:11px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:4px;">
                            <span id="toggleScheduleText">Show History</span>
                            <svg id="toggleScheduleIcon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                    </div>
                    <div id="scheduleHistoryList" style="display:none; background:#f7f6ff; border:1.5px solid #e8e7f5; border-radius:8px; padding:12px; max-height:200px; overflow-y:auto;">
                        <!-- Schedule history will be loaded here -->
                    </div>
                </div>

                <div style="background:#f7f6ff; border:1.5px solid #e8e7f5; border-radius:10px; padding:16px; margin-bottom:20px;">
                    <p style="margin:0 0 12px; font-size:11px; font-weight:700; letter-spacing:1px; color:#9999bb;">EMPLOYEE DEDUCTIONS & LOANS</p>
                    
                    <div id="deductionsList" style="display:flex; flex-direction:column; gap:10px;">
                        <!-- Deductions will be loaded here dynamically -->
                        <p style="margin:0; font-size:13px; color:#9999bb; text-align:center; padding:20px;">
                            Loading deductions...
                        </p>
                    </div>
                </div>

                <div style="background:#e8f5e9; border:1.5px solid #a5d6a7; border-radius:10px; padding:12px; display:flex; align-items:start; gap:10px; margin-bottom:16px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <p style="margin:0; font-size:12px; color:#1b5e20; line-height:1.5;">
                        <strong>Non-Destructive Scheduling:</strong> Creating a new schedule will not delete previous schedules. All historical schedules are preserved for audit purposes.
                    </p>
                </div>

                <div style="background:#fff9e6; border:1.5px solid #ffe9a3; border-radius:10px; padding:12px; display:flex; align-items:start; gap:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p style="margin:0; font-size:12px; color:#8b7500; line-height:1.5;">
                        Set the period for this deduction schedule. The selected cutoff configuration will apply to all months within the specified range. You can create different schedules for different periods.
                    </p>
                </div>
            </div>

            <div style="padding:16px 24px; border-top:1px solid #f0effe; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeAssignDeductionScheduleModal()" style="padding:10px 24px; background:#fff; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-weight:600; color:#6b6a8a; cursor:pointer; font-family:'Poppins',sans-serif;">
                    Cancel
                </button>
                <button type="submit" style="padding:10px 24px; background:#0b044d; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#fff; cursor:pointer; font-family:'Poppins',sans-serif; display:flex; align-items:center; gap:8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Save Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.deduction-schedule-item {
    background: #fff;
    border: 1.5px solid #e8e7f5;
    border-radius: 8px;
    padding: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}

.deduction-schedule-item:hover {
    border-color: #0b044d;
    box-shadow: 0 2px 8px rgba(11, 4, 77, 0.1);
}

.deduction-info {
    flex: 1;
}

.deduction-name {
    font-size: 13px;
    font-weight: 600;
    color: #0b044d;
    margin: 0 0 4px 0;
}

.deduction-details {
    font-size: 11px;
    color: #9999bb;
    margin: 0;
}

.cutoff-selector {
    display: flex;
    gap: 8px;
}

.cutoff-radio {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: 1.5px solid #e8e7f5;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
    font-weight: 600;
    color: #6b6a8a;
}

.cutoff-radio:hover {
    border-color: #0b044d;
    background: #f7f6ff;
}

.cutoff-radio input[type="radio"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #0b044d;
}

.cutoff-radio input[type="radio"]:checked + span {
    color: #0b044d;
}

.cutoff-radio:has(input[type="radio"]:checked) {
    border-color: #0b044d;
    background: #f7f6ff;
    color: #0b044d;
}

.schedule-history-item {
    background: #fff;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 8px;
    font-size: 12px;
}

.schedule-history-item:last-child {
    margin-bottom: 0;
}

.schedule-period {
    font-weight: 600;
    color: #0b044d;
    margin-bottom: 4px;
}

.schedule-details {
    color: #6b6a8a;
    font-size: 11px;
}

.schedule-status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    margin-left: 8px;
}

.schedule-status.active {
    background: #e8f5e9;
    color: #2e7d32;
}

.schedule-status.past {
    background: #f5f5f5;
    color: #757575;
}

.schedule-status.future {
    background: #e3f2fd;
    color: #1565c0;
}
</style>

<script>
function openAssignDeductionScheduleModal(employeeId, employeeName) {
    document.getElementById('deductionScheduleEmployeeId').value = employeeId;
    document.getElementById('deductionScheduleEmployeeName').textContent = employeeName;
    document.getElementById('assignDeductionScheduleModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Set default to current month for both start and end
    const now = new Date();
    const currentMonth = now.toISOString().slice(0, 7);
    document.getElementById('startMonth').value = currentMonth;
    document.getElementById('endMonth').value = currentMonth;
    
    // Load employee deductions
    loadEmployeeDeductions(employeeId);
    
    // Load existing schedules
    loadExistingSchedules(employeeId);
}

function closeAssignDeductionScheduleModal() {
    document.getElementById('assignDeductionScheduleModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('assignDeductionScheduleForm').reset();
}

function loadEmployeeDeductions(employeeId) {
    const deductionsList = document.getElementById('deductionsList');
    deductionsList.innerHTML = '<p style="margin:0; font-size:13px; color:#9999bb; text-align:center; padding:20px;">Loading deductions...</p>';
    
    // Fetch employee deductions from API
    fetch(`/admin/deductions/employee/${employeeId}/deductions`)
        .then(response => response.json())
        .then(data => {
            if (!data.deductions || data.deductions.length === 0) {
                deductionsList.innerHTML = '<p style="margin:0; font-size:13px; color:#9999bb; text-align:center; padding:20px;">No active deductions found for this employee.</p>';
                return;
            }
            
            deductionsList.innerHTML = data.deductions.map(deduction => {
                // Determine which radio should be checked based on current schedule
                let checked1st = '';
                let checked2nd = '';
                let checkedBoth = '';
                
                if (deduction.current_schedule === '1ST_ONLY') {
                    checked1st = 'checked';
                } else if (deduction.current_schedule === '2ND_ONLY') {
                    checked2nd = 'checked';
                } else if (deduction.current_schedule === 'BOTH_SPLIT' || deduction.current_schedule === 'BOTH_FULL') {
                    checkedBoth = 'checked';
                } else {
                    checked1st = 'checked'; // Default to 1st cutoff
                }
                
                return `
                    <div class="deduction-schedule-item">
                        <div class="deduction-info">
                            <p class="deduction-name">${deduction.name}</p>
                            <p class="deduction-details">
                                <span style="display:inline-block; padding:2px 8px; background:#f0effe; border-radius:4px; font-size:10px; font-weight:600; color:#0b044d; margin-right:6px;">${deduction.category}</span>
                                ${deduction.amount}
                            </p>
                        </div>
                        <div class="cutoff-selector">
                            <label class="cutoff-radio">
                                <input type="radio" name="deduction_${deduction.id}_cutoff" value="1ST" ${checked1st} required>
                                <span>1st Cutoff</span>
                            </label>
                            <label class="cutoff-radio">
                                <input type="radio" name="deduction_${deduction.id}_cutoff" value="2ND" ${checked2nd} required>
                                <span>2nd Cutoff</span>
                            </label>
                            <label class="cutoff-radio">
                                <input type="radio" name="deduction_${deduction.id}_cutoff" value="BOTH" ${checkedBoth} required>
                                <span>Both</span>
                            </label>
                        </div>
                    </div>
                `;
            }).join('');
        })
        .catch(error => {
            console.error('Error loading deductions:', error);
            deductionsList.innerHTML = '<p style="margin:0; font-size:13px; color:#8e1e18; text-align:center; padding:20px;">Failed to load deductions. Please try again.</p>';
        });
}

function handleDeductionScheduleSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const employeeId = formData.get('employee_id');
    const startMonth = formData.get('start_month');
    const endMonth = formData.get('end_month');
    
    // Validate date range
    if (startMonth > endMonth) {
        alert('End month must be equal to or after start month.');
        return;
    }
    
    // Collect all deduction schedules
    const schedules = [];
    const form = event.target;
    const radioGroups = form.querySelectorAll('input[type="radio"]:checked');
    
    radioGroups.forEach(radio => {
        const deductionId = radio.name.match(/deduction_(\d+)_cutoff/)[1];
        schedules.push({
            deduction_id: deductionId,
            cutoff: radio.value
        });
    });
    
    const data = {
        employee_id: employeeId,
        start_month: startMonth,
        end_month: endMonth,
        schedules: schedules
    };
    
    // TODO: Send to backend when route is created
    console.log('Deduction schedule data:', data);
    
    // Calculate number of months
    const start = new Date(startMonth);
    const end = new Date(endMonth);
    const monthsDiff = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth()) + 1;
    
    // For now, just show success message and close modal
    alert(`Deduction schedule saved successfully for ${monthsDiff} month(s)! (Backend integration pending)`);
    closeAssignDeductionScheduleModal();
    
    // Reload page to show changes (when backend is ready)
    // window.location.reload();
}

function loadExistingSchedules(employeeId) {
    // TODO: Replace with actual API call
    // For now, using sample data
    const sampleSchedules = [
        { period: 'Jan 2024 - Mar 2024', status: 'past', deductions: 4, created: '2024-01-05' },
        { period: 'Apr 2024 - Jun 2024', status: 'active', deductions: 4, created: '2024-04-01' },
        { period: 'Jul 2024 - Dec 2024', status: 'future', deductions: 5, created: '2024-06-15' },
    ];
    
    if (sampleSchedules.length === 0) {
        document.getElementById('existingSchedulesSection').style.display = 'none';
        return;
    }
    
    document.getElementById('existingSchedulesSection').style.display = 'block';
    
    const historyList = document.getElementById('scheduleHistoryList');
    historyList.innerHTML = sampleSchedules.map(schedule => {
        const statusLabels = {
            'past': 'Completed',
            'active': 'Active',
            'future': 'Scheduled'
        };
        
        return `
            <div class="schedule-history-item">
                <div class="schedule-period">
                    ${schedule.period}
                    <span class="schedule-status ${schedule.status}">${statusLabels[schedule.status]}</span>
                </div>
                <div class="schedule-details">
                    ${schedule.deductions} deductions configured • Created ${new Date(schedule.created).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                </div>
            </div>
        `;
    }).join('');
}

function toggleScheduleHistory() {
    const historyList = document.getElementById('scheduleHistoryList');
    const toggleText = document.getElementById('toggleScheduleText');
    const toggleIcon = document.getElementById('toggleScheduleIcon');
    
    if (historyList.style.display === 'none') {
        historyList.style.display = 'block';
        toggleText.textContent = 'Hide History';
        toggleIcon.style.transform = 'rotate(180deg)';
    } else {
        historyList.style.display = 'none';
        toggleText.textContent = 'Show History';
        toggleIcon.style.transform = 'rotate(0deg)';
    }
}
</script>
