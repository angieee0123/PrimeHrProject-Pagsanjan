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
    deductionsList.innerHTML = '<p class="ded-sched-loading">Loading deductions...</p>';

    // Fetch employee deductions from API
    fetch(`/admin/deductions/employee/${employeeId}/deductions`)
        .then(response => response.json())
        .then(data => {
            if (!data.deductions || data.deductions.length === 0) {
                deductionsList.innerHTML = '<p class="ded-sched-loading">No active deductions found for this employee.</p>';
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
                                <span class="ded-deduction-badge-inline">${deduction.category}</span>
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
            deductionsList.innerHTML = '<p class="ded-sched-loading" style="color:var(--theme-danger);">Failed to load deductions. Please try again.</p>';
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
        const match = radio.name.match(/deduction_(\d+)_cutoff/);
        if (match) {
            const deductionId = match[1];
            schedules.push({
                deduction_id: deductionId,
                cutoff: radio.value
            });
        }
    });

    if (schedules.length === 0) {
        alert('Please select a cutoff schedule for at least one deduction.');
        return;
    }

    // Create form data for submission
    const submitData = new FormData();
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        submitData.append('_token', csrfToken.content);
    }
    submitData.append('employee_id', employeeId);
    submitData.append('start_month', startMonth);
    submitData.append('end_month', endMonth);

    schedules.forEach((schedule, index) => {
        submitData.append(`schedules[${index}][deduction_id]`, schedule.deduction_id);
        submitData.append(`schedules[${index}][cutoff]`, schedule.cutoff);
    });

    // Disable submit button to prevent double submission
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/></svg> Saving...';

    // Submit to backend
    fetch('/admin/deductions/schedules/update', {
        method: 'POST',
        body: submitData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            closeAssignDeductionScheduleModal();
            window.location.reload();
        } else if (data && data.error) {
            alert('Error: ' + data.error);
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    })
    .catch(error => {
        console.error('Error saving schedule:', error);
        alert('Failed to save schedule. Please check the console for details and try again.');
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    });
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

window.openAssignDeductionScheduleModal = openAssignDeductionScheduleModal;
window.closeAssignDeductionScheduleModal = closeAssignDeductionScheduleModal;
window.handleDeductionScheduleSubmit = handleDeductionScheduleSubmit;
window.toggleScheduleHistory = toggleScheduleHistory;
