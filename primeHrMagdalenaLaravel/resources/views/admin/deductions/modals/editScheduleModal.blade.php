<x-modal-container id="editScheduleModal" close="closeEditScheduleModal"
                     title="Edit Deduction Schedule" subtitle="Configure when this deduction is applied during payroll">
    <form id="editScheduleForm" onsubmit="handleScheduleUpdate(event)">
        <input type="hidden" name="deduction_type_id" id="scheduleDeductionTypeId">

        <div class="form-group">
            <label class="form-label">Deduction Type</label>
            <input type="text" id="scheduleDeductionName" class="form-input ded-readonly-input" readonly>
        </div>

        <div class="form-group">
            <label class="form-label">Cutoff Schedule <span class="ded-required">*</span></label>
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
            <label class="form-label">Priority Order <span class="ded-required">*</span></label>
            <input type="number" name="priority" id="schedulePriority" class="form-input" placeholder="e.g., 1" min="1" required>
            <p class="ded-field-note">Lower numbers are deducted first (1 = highest priority)</p>
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
</x-modal-container>

@push('scripts')
    @vite('resources/js/admin/deductions/editScheduleModal.js')
@endpush
