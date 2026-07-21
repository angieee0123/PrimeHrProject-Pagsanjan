<!-- Assign Deduction Schedule Modal -->
<x-schedule-modal id="assignDeductionScheduleModal" close="closeAssignDeductionScheduleModal" max-width="650px"
                   box-style="max-height:90vh; display:flex; flex-direction:column;"
                   eyebrow="DEDUCTION SCHEDULE" title="Employee Name" title-id="deductionScheduleEmployeeName">
    <x-slot:icon>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
    </x-slot:icon>

    <form id="assignDeductionScheduleForm" onsubmit="handleDeductionScheduleSubmit(event)">
        <input type="hidden" name="employee_id" id="deductionScheduleEmployeeId">

        <div class="ded-sched-panel">
            <div class="ded-sched-block">
                <label class="ded-sched-label">
                    Effective Period <span class="ded-required">*</span>
                </label>
                <div class="ded-sched-grid">
                    <div>
                        <label class="ded-sched-sublabel">From Month</label>
                        <input type="month" name="start_month" id="startMonth" required class="ded-sched-input">
                    </div>
                    <div>
                        <label class="ded-sched-sublabel">To Month</label>
                        <input type="month" name="end_month" id="endMonth" required class="ded-sched-input">
                    </div>
                </div>
            </div>

            <div id="existingSchedulesSection" class="ded-sched-history-section">
                <div class="ded-sched-history-head">
                    <label class="ded-sched-history-label">Existing Schedules</label>
                    <button type="button" onclick="toggleScheduleHistory()" class="ded-sched-toggle-btn">
                        <span id="toggleScheduleText">Show History</span>
                        <svg id="toggleScheduleIcon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                </div>
                <div id="scheduleHistoryList" class="ded-sched-history-list">
                    <!-- Schedule history will be loaded here -->
                </div>
            </div>

            <div class="ded-sched-deductions-box">
                <p class="ded-sched-deductions-label">EMPLOYEE DEDUCTIONS & LOANS</p>

                <div id="deductionsList" class="ded-sched-deductions-list">
                    <!-- Deductions will be loaded here dynamically -->
                    <p class="ded-sched-loading">
                        Loading deductions...
                    </p>
                </div>
            </div>

            <div class="ded-sched-note-box is-green">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2" class="ded-sched-note-icon">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <p class="ded-sched-note-text is-green">
                    <strong>Non-Destructive Scheduling:</strong> Creating a new schedule will not delete previous schedules. All historical schedules are preserved for audit purposes.
                </p>
            </div>

            <div class="ded-sched-note-box is-gold">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c9a227" stroke-width="2" class="ded-sched-note-icon">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p class="ded-sched-note-text is-gold">
                    Set the period for this deduction schedule. The selected cutoff configuration will apply to all months within the specified range. You can create different schedules for different periods.
                </p>
            </div>
        </div>

        <div class="ded-sched-footer">
            <button type="button" onclick="closeAssignDeductionScheduleModal()" class="ded-sched-btn-cancel">
                Cancel
            </button>
            <button type="submit" class="ded-sched-btn-submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Save Schedule
            </button>
        </div>
    </form>
</x-schedule-modal>

@push('scripts')
    @vite('resources/js/admin/deductions/assignDeductionScheduleModal.js')
@endpush
