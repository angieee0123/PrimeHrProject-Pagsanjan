<div id="editScheduleModal" class="modal-overlay" onclick="closeEditScheduleModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Deduction Schedule</h3>
                <p class="modal-subtitle">Configure when this deduction is applied during payroll</p>
            </div>
            <button class="modal-close" onclick="closeEditScheduleModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="editScheduleForm" onsubmit="handleScheduleUpdate(event)">
                <input type="hidden" name="deduction_type_id" id="scheduleDeductionTypeId">

                <div class="form-group">
                    <label class="form-label">Deduction Type</label>
                    <input type="text" id="scheduleDeductionName" class="form-input" readonly style="background: #f7f6ff; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label class="form-label">Cutoff Schedule <span style="color: #8e1e18;">*</span></label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="cutoff_schedule" value="1ST_ONLY" required>
                            <div class="radio-content">
                                <span class="radio-title">1st Cutoff Only</span>
                                <span class="radio-desc">Deduct only on 1st cutoff (Days 1-15)</span>
                            </div>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="cutoff_schedule" value="2ND_ONLY" required>
                            <div class="radio-content">
                                <span class="radio-title">2nd Cutoff Only</span>
                                <span class="radio-desc">Deduct only on 2nd cutoff (Days 16-31)</span>
                            </div>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="cutoff_schedule" value="BOTH_SPLIT" required>
                            <div class="radio-content">
                                <span class="radio-title">Both Cutoffs (Split 50-50)</span>
                                <span class="radio-desc">Split monthly amount equally across both cutoffs</span>
                            </div>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="cutoff_schedule" value="BOTH_FULL" required>
                            <div class="radio-content">
                                <span class="radio-title">Both Cutoffs (Full Amount)</span>
                                <span class="radio-desc">Deduct full amount on both cutoffs (rare)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Priority Order <span style="color: #8e1e18;">*</span></label>
                    <input type="number" name="priority" id="schedulePriority" class="form-input" placeholder="e.g., 1" min="1" required>
                    <p style="font-size: 11px; color: #6b6a8a; margin: 6px 0 0 0;">Lower numbers are deducted first (1 = highest priority)</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" id="scheduleNotes" class="form-input" rows="2" placeholder="Additional notes about this schedule..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditScheduleModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(11, 4, 77, 0.4);
    backdrop-filter: blur(4px);
    z-index: 9999;
    animation: fadeIn 0.2s ease;
}

.modal-overlay.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-container {
    background: #fff;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(11, 4, 77, 0.3);
    animation: slideUp 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px;
    border-bottom: 1px solid #f0effe;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #0b044d;
    margin: 0 0 4px 0;
}

.modal-subtitle {
    font-size: 12px;
    color: #6b6a8a;
    margin: 0;
}

.modal-close {
    background: transparent;
    border: none;
    color: #6b6a8a;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f7f6ff;
    color: #0b044d;
}

.modal-body {
    padding: 24px;
    max-height: calc(90vh - 140px);
    overflow-y: auto;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: #0b044d;
    margin-bottom: 6px;
}

.form-input {
    padding: 10px 12px;
    border: 1px solid #e5e3f8;
    border-radius: 6px;
    font-size: 13px;
    color: #0b044d;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #0b044d;
    box-shadow: 0 0 0 3px rgba(11, 4, 77, 0.1);
}

.form-input::placeholder {
    color: #b3b1c8;
}

textarea.form-input {
    resize: vertical;
    min-height: 60px;
}

.radio-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.radio-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border: 2px solid #e5e3f8;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.radio-label:hover {
    border-color: #0b044d;
    background: #f7f6ff;
}

.radio-label input[type="radio"] {
    margin-top: 2px;
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #0b044d;
    flex-shrink: 0;
}

.radio-label input[type="radio"]:checked + .radio-content {
    color: #0b044d;
}

.radio-label:has(input[type="radio"]:checked) {
    border-color: #0b044d;
    background: #f7f6ff;
}

.radio-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.radio-title {
    font-size: 13px;
    font-weight: 600;
    color: #0b044d;
}

.radio-desc {
    font-size: 12px;
    color: #6b6a8a;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #f0effe;
}

.btn-cancel {
    padding: 10px 20px;
    border: 1px solid #e5e3f8;
    background: #fff;
    color: #6b6a8a;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.btn-cancel:hover {
    background: #f7f6ff;
    border-color: #0b044d;
    color: #0b044d;
}

.btn-submit {
    padding: 10px 20px;
    border: none;
    background: #0b044d;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.btn-submit:hover {
    background: #1a0f6e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(11, 4, 77, 0.3);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .radio-label {
        padding: 12px;
    }
}
</style>

<script>
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
</script>
