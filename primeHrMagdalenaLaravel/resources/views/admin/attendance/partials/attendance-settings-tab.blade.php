<div id="settings-tab" style="display: none;">
    <section class="table-section">
        <div class="table-header">
            <div>
                <h3 class="table-title">Attendance Exemption Configuration</h3>
                <p class="table-sub">Configure which time punches are not required, with optional effectivity dates</p>
            </div>
            <div class="table-actions">
                <button class="btn-primary" onclick="openAddExemptionModal()" style="display: flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Exemption
                </button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th style="text-align: center;">Type</th>
                        <th style="text-align: left;">Name</th>
                        <th style="text-align: center;">Effectivity</th>
                        <th style="text-align: center;">Not Required</th>
                        <th style="text-align: center;">Legacy Flags</th>
                        <th style="text-align: left;">Reason</th>
                        <th style="text-align: center;">Created By</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="exemptionsTableBody">
                    @forelse($exemptions ?? [] as $exemption)
                    <tr>
                        <td data-label="Type" style="text-align: center;">
                            @if($exemption->exemption_type === 'employee')
                                <span class="badge-status processed">Employee</span>
                            @elseif($exemption->exemption_type === 'department')
                                <span class="badge-status pending">Department</span>
                            @else
                                <span class="badge-status on-hold">Designation</span>
                            @endif
                        </td>
                        <td data-label="Name" style="text-align: left;">
                            <div style="display:flex;align-items:center;gap:10px">
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
                                        <img src="{{ $photo }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid #ecebf6;flex-shrink:0">
                                    @else
                                        <div style="width:34px;height:34px;border-radius:50%;background:{{ $color }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:11px;flex-shrink:0;border:2px solid #ecebf6">{{ $initials }}</div>
                                    @endif
                                @elseif($exemption->exemption_type === 'department')
                                    <div style="width:34px;height:34px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                                    </div>
                                @else
                                    <div style="width:34px;height:34px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    </div>
                                @endif
                                <strong style="font-size:13px">{{ $exemption->reference_name }}</strong>
                            </div>
                        </td>
                        <td data-label="Effectivity" style="text-align: center; font-size: 12px; white-space: nowrap;">
                            @if($exemption->start_date || $exemption->end_date)
                                <span style="color: #56547a;">
                                    {{ $exemption->start_date ? $exemption->start_date->format('M d, Y') : 'No start' }}
                                    &rarr;
                                    {{ $exemption->end_date ? $exemption->end_date->format('M d, Y') : 'No end' }}
                                </span>
                            @else
                                <span style="color: #9ca3af;">Permanent</span>
                            @endif
                        </td>
                        <td data-label="Not Required" style="text-align: center; font-size: 11px;">
                            <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: nowrap;">
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
                                    <span style="color: #9ca3af;">None</span>
                                @endif
                            </div>
                        </td>
                        <td data-label="Legacy" style="text-align: center; font-size: 11px; white-space: nowrap;">
                            @if($exemption->exempt_from_abandoned || $exemption->exempt_from_incomplete)
                                <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: nowrap;">
                                    @if($exemption->exempt_from_abandoned)
                                        <span style="color: #56547a;">Abandoned</span>
                                    @endif
                                    @if($exemption->exempt_from_abandoned && $exemption->exempt_from_incomplete)
                                        <span style="color: #d1d5db;">·</span>
                                    @endif
                                    @if($exemption->exempt_from_incomplete)
                                        <span style="color: #56547a;">Incomplete</span>
                                    @endif
                                </div>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td data-label="Reason" style="text-align: left; font-size: 13px; color: #56547a;">
                            {{ $exemption->reason ?? 'N/A' }}
                        </td>
                        <td data-label="Created By" style="text-align: center; font-size: 13px;">
                            {{ $exemption->creator->username ?? 'System' }}
                        </td>
                        <td data-label="Actions" style="text-align: center;">
                            <div class="row-actions">
                                <button class="btn-edit" onclick="editExemption({{ $exemption->id }})" title="Edit">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <button class="btn-delete" onclick="deleteExemption({{ $exemption->id }})" title="Delete">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 60px 20px;">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                                <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/>
                                <path d="M12 6v6l4 2"/>
                            </svg>
                            <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No exemptions configured</p>
                            <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af;">Add exemptions for employees with flexible work arrangements</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- Add/Edit Exemption Modal --}}
<div class="modal-overlay" id="exemptionModal" onclick="closeExemptionModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 640px; max-height: 90vh; overflow-y: auto;">
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
                
                <div class="modal-row" style="display: block;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #0b044d;">Exemption Type</label>
                    <select id="exemptionType" name="exemption_type" class="filter-select" style="width: 100%;" onchange="loadExemptionOptions()" required>
                        <option value="">Select Type</option>
                        <option value="employee">Employee</option>
                        <option value="department">Department</option>
                        <option value="designation">Designation</option>
                    </select>
                </div>

                <div class="modal-row" style="display: block; margin-top: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #0b044d;">Select <span id="exemptionTypeLabel">Item</span></label>
                    <select id="exemptionReference" name="reference_id" class="filter-select" style="width: 100%;" required>
                        <option value="">Select an option</option>
                    </select>
                </div>

                <div class="modal-row" style="display: block; margin-top: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #0b044d;">Effectivity Period</label>
                    <p style="margin: 0 0 8px; font-size: 12px; color: #6b7280;">Leave blank for a permanent exemption with no date limits.</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-size: 12px; color: #6b7280;">Start Date</label>
                            <input type="date" id="exemptionStartDate" name="start_date" class="form-input">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-size: 12px; color: #6b7280;">End Date</label>
                            <input type="date" id="exemptionEndDate" name="end_date" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="modal-row" style="display: block; margin-top: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #0b044d;">Time Punches Not Required</label>
                    <p style="margin: 0 0 8px; font-size: 12px; color: #6b7280;">Checked punches are optional. Missing punches are auto-filled from the employee's schedule for DTR and accredited hours.</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="amInNotRequired" name="am_in_not_required" value="1" style="width: 16px; height: 16px; accent-color: #0b044d;">
                            <span>AM In</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="amOutNotRequired" name="am_out_not_required" value="1" style="width: 16px; height: 16px; accent-color: #0b044d;">
                            <span>AM Out</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="pmInNotRequired" name="pm_in_not_required" value="1" style="width: 16px; height: 16px; accent-color: #0b044d;">
                            <span>PM In</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="pmOutNotRequired" name="pm_out_not_required" value="1" style="width: 16px; height: 16px; accent-color: #0b044d;">
                            <span>PM Out</span>
                        </label>
                    </div>
                </div>

                <div class="modal-row" style="display: block; margin-top: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #0b044d;">Auto-fill from Schedule</label>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="autoFillAmOut" name="auto_fill_am_out" value="1" checked style="width: 16px; height: 16px; accent-color: #0b044d;">
                            <span>Auto-fill AM Out with schedule default when not required</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="autoFillPmIn" name="auto_fill_pm_in" value="1" checked style="width: 16px; height: 16px; accent-color: #0b044d;">
                            <span>Auto-fill PM In with schedule default when not required</span>
                        </label>
                    </div>
                </div>

                <div class="modal-row" style="display: block; margin-top: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #0b044d;">Legacy Flag Overrides <span style="font-weight: 400; color: #9ca3af;">(optional)</span></label>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="exemptAbandoned" name="exempt_from_abandoned" value="1" style="width: 16px; height: 16px; accent-color: #0b044d;">
                            <span>Exempt from "Abandoned" flag</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="exemptIncomplete" name="exempt_from_incomplete" value="1" style="width: 16px; height: 16px; accent-color: #0b044d;">
                            <span>Exempt from "Incomplete" flag</span>
                        </label>
                    </div>
                </div>

                <div class="modal-row" style="display: block; margin-top: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #0b044d;">Reason for Exemption</label>
                    <textarea id="exemptionReason" name="reason" rows="3" class="form-input" style="resize: vertical;" placeholder="e.g., Field worker with flexible schedule"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <x-modal-btn variant="ghost" onclick="closeExemptionModal()">Cancel</x-modal-btn>
                <x-modal-btn type="submit">Save Exemption</x-modal-btn>
            </div>
        </form>
    </div>
</div>

<script>
const exemptionCheckboxFields = [
    'exempt_from_abandoned',
    'exempt_from_incomplete',
    'am_in_not_required',
    'am_out_not_required',
    'pm_in_not_required',
    'pm_out_not_required',
    'auto_fill_am_out',
    'auto_fill_pm_in',
];

function loadExemptionOptions() {
    const type = document.getElementById('exemptionType').value;
    const select = document.getElementById('exemptionReference');
    const label = document.getElementById('exemptionTypeLabel');
    
    select.innerHTML = '<option value="">Loading...</option>';
    
    if (!type) {
        select.innerHTML = '<option value="">Select an option</option>';
        label.textContent = 'Item';
        return;
    }
    
    label.textContent = type.charAt(0).toUpperCase() + type.slice(1);
    
    fetch(`/admin/attendance/exemptions/options?type=${type}`)
        .then(response => response.json())
        .then(data => {
            select.innerHTML = '<option value="">Select ' + type + '</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading options:', error);
            select.innerHTML = '<option value="">Error loading options</option>';
        });
}

function openAddExemptionModal() {
    document.getElementById('exemptionModalTitle').textContent = 'Add Exemption';
    document.getElementById('exemptionForm').reset();
    document.getElementById('exemptionId').value = '';
    document.getElementById('autoFillAmOut').checked = true;
    document.getElementById('autoFillPmIn').checked = true;
    document.getElementById('exemptionModal').style.display = 'flex';
}

function closeExemptionModal() {
    document.getElementById('exemptionModal').style.display = 'none';
}

function saveExemption(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const exemptionId = document.getElementById('exemptionId').value;
    const url = exemptionId ? `/admin/attendance/exemptions/${exemptionId}` : '/admin/attendance/exemptions';
    const method = exemptionId ? 'PUT' : 'POST';
    
    const data = {};
    formData.forEach((value, key) => {
        if (key === 'exemption_id') {
            return;
        }
        if (exemptionCheckboxFields.includes(key)) {
            data[key] = true;
        } else {
            data[key] = value || null;
        }
    });
    
    exemptionCheckboxFields.forEach(field => {
        if (!formData.has(field)) {
            data[field] = false;
        }
    });
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeExemptionModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save exemption'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the exemption');
    });
}

function editExemption(id) {
    fetch(`/admin/attendance/exemptions/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('exemptionModalTitle').textContent = 'Edit Exemption';
            document.getElementById('exemptionId').value = data.id;
            document.getElementById('exemptionType').value = data.exemption_type;
            document.getElementById('exemptionStartDate').value = data.start_date ? data.start_date.substring(0, 10) : '';
            document.getElementById('exemptionEndDate').value = data.end_date ? data.end_date.substring(0, 10) : '';
            document.getElementById('amInNotRequired').checked = !!data.am_in_not_required;
            document.getElementById('amOutNotRequired').checked = !!data.am_out_not_required;
            document.getElementById('pmInNotRequired').checked = !!data.pm_in_not_required;
            document.getElementById('pmOutNotRequired').checked = !!data.pm_out_not_required;
            document.getElementById('autoFillAmOut').checked = data.auto_fill_am_out !== false;
            document.getElementById('autoFillPmIn').checked = data.auto_fill_pm_in !== false;
            document.getElementById('exemptAbandoned').checked = !!data.exempt_from_abandoned;
            document.getElementById('exemptIncomplete').checked = !!data.exempt_from_incomplete;
            document.getElementById('exemptionReason').value = data.reason || '';
            
            loadExemptionOptions();
            setTimeout(() => {
                document.getElementById('exemptionReference').value = data.reference_id;
            }, 500);
            
            document.getElementById('exemptionModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load exemption details');
        });
}

function deleteExemption(id) {
    if (!confirm('Are you sure you want to delete this exemption?')) {
        return;
    }
    
    fetch(`/admin/attendance/exemptions/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete exemption'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the exemption');
    });
}
</script>


