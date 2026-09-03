<!-- Bulk Assign Schedule Modal -->
<div id="bulkScheduleModal" class="bulk-overlay">
    <div class="bulk-modal">

        {{-- Header --}}
        <div class="bulk-header">
            <div class="bulk-header-left">
                <div class="bulk-header-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <p class="bulk-eyebrow">BULK ASSIGNMENT</p>
                    <h3 class="bulk-title">Assign Schedule to Multiple Employees</h3>
                </div>
            </div>
            <button type="button" onclick="closeBulkScheduleModal()" class="bulk-close-btn">&times;</button>
        </div>

        <form action="{{ route('admin.schedules.bulk-assign') }}" method="POST" class="bulk-form">
            @csrf

            <div class="bulk-body">

                {{-- LEFT: Employee Selection --}}
                <div class="bulk-left">

                    <div class="bulk-filters">
                        <p class="bulk-section-label">QUICK FILTERS</p>
                        <select id="bulkFilterDepartment" onchange="filterBulkEmployees()" class="bulk-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <select id="bulkFilterDesignation" onchange="filterBulkEmployees()" class="bulk-select">
                            <option value="">All Designations</option>
                        </select>
                        <div class="bulk-filter-btns">
                            <button type="button" onclick="selectFilteredEmployees()" class="bulk-btn-primary">Select Filtered</button>
                            <button type="button" onclick="clearBulkFilters()" class="bulk-btn-ghost">Clear</button>
                        </div>
                    </div>

                    <div class="bulk-select-all-row">
                        <label class="bulk-select-all-label">
                            <input type="checkbox" id="selectAllEmployees" onclick="toggleAllEmployees(this)" class="bulk-checkbox">
                            Select All
                        </label>
                    </div>

                    <div class="bulk-emp-list" id="employeeCheckboxList">
                        @foreach($employees as $employee)
                        @php
                            $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name . ($employee->suffix ? ' ' . $employee->suffix : ''));
                            $department   = $employee->employmentDetail && $employee->employmentDetail->departmentRelation  ? $employee->employmentDetail->departmentRelation->name  : 'N/A';
                            $departmentId = $employee->employmentDetail && $employee->employmentDetail->departmentRelation  ? $employee->employmentDetail->departmentRelation->id    : '';
                            $designationId= $employee->employmentDetail && $employee->employmentDetail->designationRelation ? $employee->employmentDetail->designationRelation->id   : '';
                        @endphp
                        <label class="bulk-emp-item" data-department-id="{{ $departmentId }}" data-designation-id="{{ $designationId }}">
                            <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="employee-checkbox bulk-checkbox">
                            {{-- Photo where the employee has one, initials otherwise. Picking
                                 people out of a list of twelve similar names is much easier by
                                 face; `employees.photo` already holds a public URL. --}}
                            @if($employee->photo)
                                <img src="{{ $employee->photo }}" alt="" class="bulk-emp-avatar" loading="lazy">
                            @else
                                <span class="bulk-emp-avatar bulk-emp-avatar-initials"
                                      style="background: {{ $avatarColors[$loop->index % count($avatarColors)] }}"
                                      aria-hidden="true">{{ getInitials($fullName) }}</span>
                            @endif
                            <div class="bulk-emp-info">
                                <p class="bulk-emp-name">{{ $fullName }}</p>
                                <p class="bulk-emp-meta">{{ $employee->employee_id }} · {{ $department }}</p>
                            </div>
                            <span class="bulk-emp-tick" aria-hidden="true">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                        </label>
                        @endforeach
                    </div>

                    <div class="bulk-count-bar">
                        <p class="bulk-count-text"><span id="selectedCount" class="bulk-count-num">0</span> employee(s) selected</p>
                    </div>
                </div>

                {{-- RIGHT: Schedule Config --}}
                <div class="bulk-right">

                    <p class="bulk-section-label">DATE RANGE</p>
                    <div class="bulk-grid-2">
                        <div>
                            <label class="bulk-label">Start Date <span class="bulk-req">*</span></label>
                            <input type="date" name="start_date" required class="bulk-input">
                        </div>
                        <div>
                            <label class="bulk-label">End Date <span class="bulk-req">*</span></label>
                            <input type="date" name="end_date" required class="bulk-input">
                        </div>
                    </div>

                    <div class="bulk-shift-block">
                        <p class="bulk-section-label">MORNING SHIFT</p>
                        <div class="bulk-grid-2">
                            <div>
                                <label class="bulk-label">Time In <span class="bulk-req">*</span></label>
                                <input type="time" name="am_in" required class="bulk-input">
                            </div>
                            <div>
                                <label class="bulk-label">Time Out <span class="bulk-req">*</span></label>
                                <input type="time" name="am_out" required class="bulk-input">
                            </div>
                        </div>
                    </div>

                    <div class="bulk-shift-block">
                        <p class="bulk-section-label">AFTERNOON SHIFT</p>
                        <div class="bulk-grid-2">
                            <div>
                                <label class="bulk-label">Time In <span class="bulk-req">*</span></label>
                                <input type="time" name="pm_in" required class="bulk-input">
                            </div>
                            <div>
                                <label class="bulk-label">Time Out <span class="bulk-req">*</span></label>
                                <input type="time" name="pm_out" required class="bulk-input">
                            </div>
                        </div>
                    </div>

                    <div class="bulk-info-note">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="bulk-info-icon"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <p>Schedule will be applied to all selected employees for the specified date range.</p>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="bulk-footer">
                <button type="button" onclick="closeBulkScheduleModal()" class="bulk-btn-ghost bulk-footer-cancel">Cancel</button>
                <button type="submit" class="bulk-btn-submit">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Assign to Selected
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ── Overlay ── */
.bulk-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(11,4,77,0.55);
    backdrop-filter: blur(4px);
    z-index: var(--personnel-z-modal);
    align-items: center;
    justify-content: center;
    padding: 16px;
}

/* ── Modal box ── */
.bulk-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 780px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(11,4,77,0.25);
    overflow: hidden;
}

/* ── Header ── */
.bulk-header {
    background: linear-gradient(135deg, var(--gp-pri-2), var(--gp-pri-2));
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}
.bulk-header-left { display: flex; align-items: center; gap: 12px; }
.bulk-header-icon {
    width: 36px; height: 36px;
    background: rgba(255,255,255,0.12);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.bulk-eyebrow { margin: 0; font-size: 9.5px; font-weight: 700; letter-spacing: 1.5px; color: rgba(255,255,255,0.5); }
.bulk-title   { margin: 0; font-size: 14px; font-weight: 700; color: #fff; }
.bulk-close-btn {
    background: rgba(255,255,255,0.1); border: none; color: #fff;
    width: 28px; height: 28px; border-radius: 50%;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0; transition: background 0.2s;
}
.bulk-close-btn:hover { background: rgba(255,255,255,0.2); }

/* ── Form ── */
.bulk-form { display: flex; flex-direction: column; flex: 1; overflow: hidden; min-height: 0; }

/* ── Two-column body ── */
.bulk-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    flex: 1;
    overflow: hidden;
    min-height: 0;
}

/* ── LEFT column ── */
.bulk-left {
    display: flex;
    flex-direction: column;
    border-right: 1.5px solid var(--gp-bg-tint-2);
    overflow: hidden;
    min-height: 0;
}
.bulk-filters {
    padding: 14px 16px;
    border-bottom: 1px solid var(--gp-bg-tint-2);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 7px;
}
.bulk-section-label { margin: 0 0 8px; font-size: 10px; font-weight: 700; letter-spacing: 1px; color: var(--gp-text-soft); }
.bulk-select {
    width: 100%; padding: 7px 10px;
    border: 1.5px solid var(--gp-border); border-radius: 8px;
    font-size: 12px; font-family: 'Poppins', sans-serif;
    color: var(--gp-pri); background: #fff; cursor: pointer;
}
.bulk-filter-btns { display: flex; gap: 6px; }
.bulk-btn-primary {
    flex: 1; padding: 7px 10px;
    background: var(--gp-pri); color: #fff;
    border: none; border-radius: 6px;
    font-size: 11.5px; font-weight: 600;
    cursor: pointer; font-family: 'Poppins', sans-serif;
    transition: background 0.2s;
}
.bulk-btn-primary:hover { background: var(--gp-pri-2); }
.bulk-btn-ghost {
    flex: 1; padding: 7px 10px;
    background: #fff; color: var(--gp-text-mid);
    border: 1.5px solid var(--gp-border); border-radius: 6px;
    font-size: 11.5px; font-weight: 600;
    cursor: pointer; font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}
.bulk-btn-ghost:hover { border-color: var(--gp-pri); color: var(--gp-pri); }
.bulk-select-all-row {
    padding: 10px 16px;
    border-bottom: 1px solid var(--gp-bg-tint-2);
    flex-shrink: 0;
}
.bulk-select-all-label {
    display: flex; align-items: center; gap: 8px;
    cursor: pointer; font-size: 12px; font-weight: 600; color: var(--gp-pri);
}
.bulk-checkbox { width: 14px; height: 14px; cursor: pointer; accent-color: var(--gp-pri); flex-shrink: 0; }
.bulk-emp-list { flex: 1; overflow-y: auto; padding: 6px 10px; }
.bulk-emp-item {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 9px; border-radius: 9px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
    margin-bottom: 2px;
}
.bulk-emp-item:hover { background: var(--gp-bg-tint-2); }

/* Selected rows are the whole point of this panel, so they read as chosen
   rather than merely hovered. :has() lets the row respond to its own
   checkbox without any JS bookkeeping. */
.bulk-emp-item:has(.employee-checkbox:checked) {
    background: var(--theme-primary-soft);
    border-color: var(--theme-primary-border);
}
.bulk-emp-item:has(.employee-checkbox:focus-visible) {
    border-color: var(--theme-accent);
}

.bulk-emp-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    flex-shrink: 0;
    object-fit: cover;
    border: 1.5px solid var(--gp-border);
}
.bulk-emp-avatar-initials {
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 10.5px; font-weight: 700; letter-spacing: 0.2px;
    border-color: transparent;
}

/* Confirmation mark, revealed only once the row is selected. */
.bulk-emp-tick {
    margin-left: auto;
    flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: var(--theme-success);
    color: var(--theme-success-fg, #fff);
    opacity: 0;
    transform: scale(0.6);
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.bulk-emp-item:has(.employee-checkbox:checked) .bulk-emp-tick {
    opacity: 1;
    transform: scale(1);
}

.bulk-emp-info { min-width: 0; flex: 1; }
.bulk-emp-name { margin: 0; font-size: 12px; font-weight: 600; color: var(--gp-pri); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bulk-emp-meta { margin: 0; font-size: 10.5px; color: var(--gp-text-soft); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bulk-count-bar { padding: 9px 16px; border-top: 1px solid var(--gp-bg-tint-2); flex-shrink: 0; background: var(--gp-bg-tint); }
.bulk-count-text { margin: 0; font-size: 11.5px; color: var(--gp-text-mid); }
.bulk-count-num { font-weight: 700; color: var(--gp-pri); }

/* ── RIGHT column ── */
.bulk-right {
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.bulk-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.bulk-label { display: block; font-size: 11.5px; font-weight: 600; color: var(--gp-pri); margin-bottom: 5px; }
.bulk-req { color: var(--theme-danger); }
.bulk-input {
    width: 100%; padding: 8px 10px;
    border: 1.5px solid var(--gp-border); border-radius: 8px;
    font-size: 12.5px; font-family: 'Poppins', sans-serif;
    color: var(--gp-pri); background: #fff; box-sizing: border-box;
    transition: border-color 0.2s;
}
.bulk-input:focus { outline: none; border-color: var(--gp-pri); }
.bulk-shift-block {
    background: var(--gp-bg-tint);
    border: 1.5px solid var(--gp-border);
    border-radius: 10px;
    padding: 12px;
}
/* Informational, not a success report: it describes what Assign will do,
   before anything has happened. Green here read as "done". */
.bulk-info-note {
    background: var(--theme-info-subtle);
    border: 1.5px solid var(--theme-info-border);
    color: var(--theme-info-emphasis);
    border-radius: 8px;
    padding: 10px 12px;
    display: flex; gap: 8px; align-items: flex-start;
}
.bulk-info-note p { margin: 0; font-size: 11.5px; color: inherit; line-height: 1.5; }
.bulk-info-icon { flex-shrink: 0; margin-top: 1px; }

/* ── Footer ── */
.bulk-footer {
    padding: 12px 20px;
    border-top: 1.5px solid var(--gp-bg-tint-2);
    display: flex; justify-content: flex-end; gap: 10px;
    flex-shrink: 0; background: #fff;
}
.bulk-footer-cancel { flex: none; padding: 9px 20px; }
.bulk-btn-submit {
    padding: 9px 20px;
    background: linear-gradient(135deg, var(--gp-pri-2), var(--gp-pri-2));
    border: none; border-radius: 8px;
    font-size: 13px; font-weight: 600; color: #fff;
    cursor: pointer; font-family: 'Poppins', sans-serif;
    display: flex; align-items: center; gap: 6px;
    transition: opacity 0.2s;
}
.bulk-btn-submit:hover { opacity: 0.9; }

/* ── Tablet: ≤768px — stack columns, bottom sheet style ── */
@media (max-width: 768px) {
    .bulk-overlay { padding: 0; align-items: flex-end; }
    .bulk-modal {
        max-width: 100%;
        max-height: 95vh;
        border-radius: 20px 20px 0 0;
    }
    .bulk-body {
        grid-template-columns: 1fr;
        overflow-y: auto;
        max-height: calc(95vh - 130px);
    }
    .bulk-left {
        border-right: none;
        border-bottom: 1.5px solid var(--gp-bg-tint-2);
        max-height: 280px;
        overflow: hidden;
    }
    .bulk-right { overflow-y: visible; }
    .bulk-title { font-size: 13px; }
}

/* ── Mobile: ≤480px ── */
@media (max-width: 480px) {
    .bulk-header { padding: 14px 16px; }
    .bulk-header-icon { display: none; }
    .bulk-title { font-size: 12.5px; }
    .bulk-eyebrow { display: none; }
    .bulk-filters { padding: 12px 14px; }
    .bulk-select-all-row { padding: 8px 14px; }
    .bulk-emp-list { padding: 4px 8px; }
    .bulk-count-bar { padding: 8px 14px; }
    .bulk-right { padding: 12px 14px; gap: 12px; }
    .bulk-grid-2 { grid-template-columns: 1fr 1fr; gap: 8px; }
    .bulk-footer { padding: 10px 14px; flex-direction: column; gap: 8px; }
    .bulk-footer-cancel,
    .bulk-btn-submit { width: 100%; justify-content: center; }
    .bulk-left { max-height: 240px; }
}
</style>

@push('scripts')
    @vite('resources/js/admin/personnel/bulkAssignSchedule.js')
@endpush
