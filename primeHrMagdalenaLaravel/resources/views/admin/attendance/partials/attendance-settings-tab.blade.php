<div id="settings-tab">
    <section class="table-section">
        <div class="table-header">
            <div>
                <h3 class="table-title">Attendance Exemption Configuration</h3>
                <p class="table-sub">Configure which time punches are not required, with optional effectivity dates</p>
            </div>
            <div class="table-actions">
                <button class="btn-primary" onclick="openAddExemptionModal()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Exemption
                </button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="payroll-table exemptions-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Effectivity</th>
                        <th class="th-center">Not Required</th>
                        <th class="th-center">Legacy Flags</th>
                        <th>Reason</th>
                        <th>Created By</th>
                        <th class="row-menu-head">Actions</th>
                    </tr>
                </thead>
                <tbody id="exemptionsTableBody">
                    @forelse($exemptions ?? [] as $exemption)
                    <tr>
                        <td data-label="Type">
                            @if($exemption->exemption_type === 'employee')
                                <span class="badge-status processed">Employee</span>
                            @elseif($exemption->exemption_type === 'department')
                                <span class="badge-status pending">Department</span>
                            @else
                                <span class="badge-status is-info is-label">Designation</span>
                            @endif
                        </td>
                        <td data-label="Name">
                            <div class="exemption-ref-cell">
                                @if($exemption->exemption_type === 'employee')
                                    @php
                                        $emp = $exemption->employee;
                                        $photo = $emp->photo ?? null;
                                        $initials = collect(explode(' ', trim($exemption->reference_name)))
                                            ->filter()->map(fn($p) => strtoupper(substr($p,0,1)))->take(2)->join('');
                                        $colors = ['#0b044d','#8e1e18','#150c63','#15803d','#a52820','#56547a'];
                                        $color = $colors[$exemption->id % count($colors)];
                                    @endphp
                                    @if($photo)
                                        <img src="{{ $photo }}" class="exemption-avatar">
                                    @else
                                        <div class="exemption-avatar-fallback" style="background:{{ $color }}">{{ $initials }}</div>
                                    @endif
                                @elseif($exemption->exemption_type === 'department')
                                    <div class="exemption-icon-wrap department">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                                    </div>
                                @else
                                    <div class="exemption-icon-wrap designation">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    </div>
                                @endif
                                <strong class="exemption-ref-name">{{ $exemption->reference_name }}</strong>
                            </div>
                        </td>
                        <td data-label="Effectivity">
                            @if($exemption->start_date || $exemption->end_date)
                                <span class="text-muted-sm">
                                    {{ $exemption->start_date ? $exemption->start_date->format('M d, Y') : 'No start' }}
                                    &rarr;
                                    {{ $exemption->end_date ? $exemption->end_date->format('M d, Y') : 'No end' }}
                                </span>
                            @else
                                <span class="text-faint">Permanent</span>
                            @endif
                        </td>
                        <td data-label="Not Required">
                            <div class="pill-row">
                                @if($exemption->am_in_not_required)
                                    <span class="exempt-pill">AM IN</span>
                                @endif
                                @if($exemption->am_out_not_required)
                                    <span class="exempt-pill">AM OUT</span>
                                @endif
                                @if($exemption->pm_in_not_required)
                                    <span class="exempt-pill">PM IN</span>
                                @endif
                                @if($exemption->pm_out_not_required)
                                    <span class="exempt-pill">PM OUT</span>
                                @endif
                                @if(!$exemption->am_in_not_required && !$exemption->am_out_not_required && !$exemption->pm_in_not_required && !$exemption->pm_out_not_required)
                                    <span class="text-faint">None</span>
                                @endif
                            </div>
                        </td>
                        <td data-label="Legacy">
                            @if($exemption->exempt_from_abandoned || $exemption->exempt_from_incomplete)
                                <div class="pill-row">
                                    @if($exemption->exempt_from_abandoned)
                                        <span class="text-muted-sm">Abandoned</span>
                                    @endif
                                    @if($exemption->exempt_from_abandoned && $exemption->exempt_from_incomplete)
                                        <span class="text-divider-dot">·</span>
                                    @endif
                                    @if($exemption->exempt_from_incomplete)
                                        <span class="text-muted-sm">Incomplete</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-faint">-</span>
                            @endif
                        </td>
                        <td data-label="Reason">
                            {{ $exemption->reason ?? 'N/A' }}
                        </td>
                        <td data-label="Created By">
                            {{ $exemption->creator->username ?? 'System' }}
                        </td>
                        <td data-label="Actions" class="row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="exemptionMenu{{ $exemption->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for this exemption">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="exemptionMenu{{ $exemption->id }}" role="menu" aria-label="Exemption actions">
                            <button type="button" role="menuitem" class="row-menu-item" onclick="closeRowMenu(); editExemption({{ $exemption->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit exemption
                            </button>
                            <div class="row-menu-sep"></div>
                            <button type="button" role="menuitem" class="row-menu-item is-danger" onclick="closeRowMenu(); deleteExemption({{ $exemption->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Delete exemption
                            </button>
                        </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="empty-state-cell">
                            <svg class="empty-state-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5">
                                <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/>
                                <path d="M12 6v6l4 2"/>
                            </svg>
                            <p class="empty-state-title">No exemptions configured</p>
                            <p class="empty-state-sub">Add exemptions for employees with flexible work arrangements</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- Add/Edit Exemption Modal --}}
<div class="modal-overlay" id="exemptionModal" onclick="closeExemptionModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">ATTENDANCE CONFIGURATION</span>
                <h3 class="modal-title" id="exemptionModalTitle">Add Exemption</h3>
            </div>
            <button class="modal-close" onclick="closeExemptionModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="exemptionForm" onsubmit="saveExemption(event)">
            <input type="hidden" id="exemptionId" name="exemption_id">
            <div class="modal-body">
                <span class="modal-section-label">EXEMPTION DETAILS</span>

                <div class="modal-row modal-row-block">
                    <label class="modal-field-label">Exemption Type</label>
                    <select id="exemptionType" name="exemption_type" class="filter-select modal-select-full" onchange="loadExemptionOptions()" required>
                        <option value="">Select Type</option>
                        <option value="employee">Employee</option>
                        <option value="department">Department</option>
                        <option value="designation">Designation</option>
                    </select>
                </div>

                <div class="modal-row modal-row-block mt">
                    <label class="modal-field-label">Select <span id="exemptionTypeLabel">Item</span></label>
                    <select id="exemptionReference" name="reference_id" class="filter-select modal-select-full" required>
                        <option value="">Select an option</option>
                    </select>
                </div>

                <div class="modal-row modal-row-block mt">
                    <label class="modal-field-label">Effectivity Period</label>
                    <p class="modal-field-hint">Leave blank for a permanent exemption with no date limits.</p>
                    <div class="form-grid-2">
                        <div>
                            <label class="modal-field-label-sm">Start Date</label>
                            <input type="date" id="exemptionStartDate" name="start_date" class="form-input">
                        </div>
                        <div>
                            <label class="modal-field-label-sm">End Date</label>
                            <input type="date" id="exemptionEndDate" name="end_date" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="modal-row modal-row-block mt">
                    <label class="modal-field-label">Time Punches Not Required</label>
                    <p class="modal-field-hint">Checked punches are optional. Missing punches are auto-filled from the employee's schedule for DTR and accredited hours.</p>
                    <div class="checkbox-grid-2">
                        <label class="checkbox-label">
                            <input type="checkbox" id="amInNotRequired" name="am_in_not_required" value="1" class="checkbox-input">
                            <span>AM In</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="amOutNotRequired" name="am_out_not_required" value="1" class="checkbox-input">
                            <span>AM Out</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="pmInNotRequired" name="pm_in_not_required" value="1" class="checkbox-input">
                            <span>PM In</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="pmOutNotRequired" name="pm_out_not_required" value="1" class="checkbox-input">
                            <span>PM Out</span>
                        </label>
                    </div>
                </div>

                <div class="modal-row modal-row-block mt">
                    <label class="modal-field-label">Auto-fill from Schedule</label>
                    <div class="checkbox-col">
                        <label class="checkbox-label">
                            <input type="checkbox" id="autoFillAmOut" name="auto_fill_am_out" value="1" checked class="checkbox-input">
                            <span>Auto-fill AM Out with schedule default when not required</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="autoFillPmIn" name="auto_fill_pm_in" value="1" checked class="checkbox-input">
                            <span>Auto-fill PM In with schedule default when not required</span>
                        </label>
                    </div>
                </div>

                <div class="modal-row modal-row-block mt">
                    <label class="modal-field-label">Legacy Flag Overrides <span class="modal-field-optional">(optional)</span></label>
                    <div class="checkbox-col">
                        <label class="checkbox-label">
                            <input type="checkbox" id="exemptAbandoned" name="exempt_from_abandoned" value="1" class="checkbox-input">
                            <span>Exempt from "Abandoned" flag</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="exemptIncomplete" name="exempt_from_incomplete" value="1" class="checkbox-input">
                            <span>Exempt from "Incomplete" flag</span>
                        </label>
                    </div>
                </div>

                <div class="modal-row modal-row-block mt">
                    <label class="modal-field-label">Reason for Exemption</label>
                    <textarea id="exemptionReason" name="reason" rows="3" class="form-input" placeholder="e.g., Field worker with flexible schedule"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <x-modal-btn variant="ghost" onclick="closeExemptionModal()">Cancel</x-modal-btn>
                <x-modal-btn type="submit">Save Exemption</x-modal-btn>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/attendance/attendance-settings-tab.js')
@endpush
