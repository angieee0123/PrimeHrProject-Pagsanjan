<x-modal-container id="editAccrualRateModal" close="closeEditAccrualRateModal" overlay-class="modal-overlay" container-class="modal-container" max-width="700px"
                     title="Edit Accrual Rate" subtitle="Update leave credit earning rate configuration">
    <x-slot:closeIcon>&times;</x-slot:closeIcon>

    <form id="editAccrualRateForm">
        @csrf
        @method('PUT')
        <input type="hidden" id="editAccrualRateId">

        <div class="form-grid">
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Leave Type <span style="color:var(--theme-danger);">*</span></label>
                <select name="leave_type_id" id="editAccrualLeaveTypeId" class="form-input" required>
                    <option value="">Select Leave Type</option>
                    @if(isset($accruedLeaveTypes) && $accruedLeaveTypes->count() > 0)
                        @foreach($accruedLeaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}">{{ $leaveType->leave_code }} - {{ $leaveType->leave_name }}</option>
                        @endforeach
                    @endif
                </select>
                <small class="form-hint">Only accrued leave types are shown</small>
            </div>

            <div class="form-group">
                <label class="form-label">Accrual Frequency <span style="color:var(--theme-danger);">*</span></label>
                <select name="accrual_frequency" id="editAccrualFrequency" class="form-input" required>
                    <option value="daily">Daily</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Days of Service Required <span style="color:var(--theme-danger);">*</span></label>
                <input type="number" name="days_of_service_required" id="editAccrualDaysService" class="form-input" step="0.01" min="0.01" required>
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Credits Earned Per Period <span style="color:var(--theme-danger);">*</span></label>
                <input type="number" name="credits_earned_per_period" id="editAccrualCredits" class="form-input" step="0.0001" min="0.0001" required>
                <small class="form-hint">Example: 0.0417 credits per day (1.25 days ÷ 30 days)</small>
            </div>

            <div class="form-group">
                <label class="form-label">Effective Date <span style="color:var(--theme-danger);">*</span></label>
                <input type="date" name="effective_date" id="editAccrualEffectiveDate" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" id="editAccrualEndDate" class="form-input">
                <small class="form-hint">Leave empty for current rate</small>
            </div>

            <div class="form-group">
                <label class="form-label">Status <span style="color:var(--theme-danger);">*</span></label>
                <select name="is_active" id="editAccrualIsActive" class="form-input" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Notes / CSC Reference</label>
                <textarea name="notes" id="editAccrualNotes" class="form-input" rows="3" placeholder="e.g., CSC MC No. 41, s. 1998"></textarea>
            </div>
        </div>

        <div id="editAccrualError" style="display:none; background:var(--theme-danger-subtle); border:1px solid #fca5a5; border-radius:6px; padding:10px 14px; margin-top:12px; color:#991b1b; font-size:13px;"></div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeEditAccrualRateModal()"
                    style="flex:none; padding:9px 22px;">Cancel</button>
            <button type="submit" class="btn-submit" id="editAccrualSubmitBtn"
                    style="flex:none; padding:9px 22px;">Save Changes</button>
        </div>
    </form>
</x-modal-container>
