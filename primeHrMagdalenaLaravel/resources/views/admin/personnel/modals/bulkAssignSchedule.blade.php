<!-- Bulk Assign Schedule Modal -->
<div id="bulkScheduleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center; overflow-y:auto;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:650px; margin:20px; box-shadow:0 8px 32px rgba(11,4,77,0.2); overflow:hidden;">
        <div style="background:linear-gradient(135deg, #1a0f6e 0%, #2d1a8e 100%); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
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
                    <p style="margin:0; font-size:10px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">BULK ASSIGNMENT</p>
                    <h3 style="margin:0; font-size:16px; font-weight:700; color:#fff;">Assign Schedule to Multiple Employees</h3>
                </div>
            </div>
            <button onclick="closeBulkScheduleModal()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px;">&times;</button>
        </div>

        <form action="{{ route('admin.schedules.bulk-assign') }}" method="POST">
            @csrf
            
            <div style="padding:24px; display:flex; flex-direction:column; gap:20px;">
                <!-- Filter Options -->
                <div style="background:#f7f6ff; border:1.5px solid #e8e7f5; border-radius:10px; padding:16px;">
                    <p style="margin:0 0 12px; font-size:11px; font-weight:700; letter-spacing:1px; color:#9999bb;">QUICK FILTERS</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Filter by Department
                            </label>
                            <select id="bulkFilterDepartment" onchange="filterBulkEmployees()" style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box; cursor:pointer;">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Filter by Designation
                            </label>
                            <select id="bulkFilterDesignation" onchange="filterBulkEmployees()" style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box; cursor:pointer;">
                                <option value="">All Designations</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; margin-top:12px;">
                        <button type="button" onclick="selectFilteredEmployees()" style="flex:1; padding:8px 16px; background:#0b044d; color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
                            Select Filtered
                        </button>
                        <button type="button" onclick="clearBulkFilters()" style="flex:1; padding:8px 16px; background:#fff; color:#6b6a8a; border:1.5px solid #e8e7f5; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Employee Selection -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:8px;">
                        Select Employees <span style="color:#8e1e18;">*</span>
                    </label>
                    <div style="background:#f7f6ff; border:1.5px solid #e8e7f5; border-radius:10px; padding:16px; max-height:200px; overflow-y:auto;">
                        <div style="margin-bottom:12px;">
                            <label style="display:flex; align-items:center; gap:8px; padding:8px; background:#fff; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600; color:#0b044d;">
                                <input type="checkbox" id="selectAllEmployees" onclick="toggleAllEmployees(this)" style="width:16px; height:16px; cursor:pointer;">
                                Select All Employees
                            </label>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:6px;" id="employeeCheckboxList">
                            @foreach($employees as $employee)
                            @php
                                $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name . ($employee->suffix ? ' ' . $employee->suffix : ''));
                                $department = $employee->employmentDetail && $employee->employmentDetail->departmentRelation
                                    ? $employee->employmentDetail->departmentRelation->name
                                    : 'N/A';
                                $departmentId = $employee->employmentDetail && $employee->employmentDetail->departmentRelation
                                    ? $employee->employmentDetail->departmentRelation->id
                                    : '';
                                $designationId = $employee->employmentDetail && $employee->employmentDetail->designationRelation
                                    ? $employee->employmentDetail->designationRelation->id
                                    : '';
                                $designation = $employee->employmentDetail && $employee->employmentDetail->designationRelation
                                    ? $employee->employmentDetail->designationRelation->title
                                    : 'N/A';
                            @endphp
                            <label class="employee-item" data-department-id="{{ $departmentId }}" data-designation-id="{{ $designationId }}" style="display:flex; align-items:center; gap:8px; padding:8px; background:#fff; border-radius:6px; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='#f0effe'" onmouseout="this.style.background='#fff'">
                                <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="employee-checkbox" style="width:16px; height:16px; cursor:pointer;">
                                <div style="flex:1;">
                                    <p style="margin:0; font-size:13px; font-weight:600; color:#0b044d;">{{ $fullName }}</p>
                                    <p style="margin:0; font-size:11px; color:#9999bb;">{{ $employee->employee_id }} · {{ $department }} · {{ $designation }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <p style="margin:8px 0 0; font-size:11px; color:#6b6a8a;">
                        <span id="selectedCount">0</span> employee(s) selected
                    </p>
                </div>

                <!-- Date Range -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                            Start Date <span style="color:#8e1e18;">*</span>
                        </label>
                        <input type="date" name="start_date" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                            End Date <span style="color:#8e1e18;">*</span>
                        </label>
                        <input type="date" name="end_date" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                    </div>
                </div>

                <!-- Schedule Times -->
                <div style="background:#f7f6ff; border:1.5px solid #e8e7f5; border-radius:10px; padding:16px;">
                    <p style="margin:0 0 12px; font-size:11px; font-weight:700; letter-spacing:1px; color:#9999bb;">MORNING SHIFT</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Time In <span style="color:#8e1e18;">*</span>
                            </label>
                            <input type="time" name="am_in" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Time Out <span style="color:#8e1e18;">*</span>
                            </label>
                            <input type="time" name="am_out" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                    </div>

                    <p style="margin:0 0 12px; font-size:11px; font-weight:700; letter-spacing:1px; color:#9999bb;">AFTERNOON SHIFT</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Time In <span style="color:#8e1e18;">*</span>
                            </label>
                            <input type="time" name="pm_in" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Time Out <span style="color:#8e1e18;">*</span>
                            </label>
                            <input type="time" name="pm_out" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                    </div>
                </div>

                <div style="background:#e8f9ef; border:1.5px solid #bbf7d0; border-radius:10px; padding:12px; display:flex; align-items:start; gap:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <p style="margin:0; font-size:12px; color:#15803d; line-height:1.5;">
                        This schedule will be applied to all selected employees for the specified date range. You can assign different schedules for different periods.
                    </p>
                </div>
            </div>

            <div style="padding:16px 24px; border-top:1px solid #f0effe; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeBulkScheduleModal()" style="padding:10px 24px; background:#fff; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-weight:600; color:#6b6a8a; cursor:pointer; font-family:'Poppins',sans-serif;">
                    Cancel
                </button>
                <button type="submit" style="padding:10px 24px; background:#1a0f6e; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#fff; cursor:pointer; font-family:'Poppins',sans-serif; display:flex; align-items:center; gap:8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Assign to Selected
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function closeBulkScheduleModal() {
    document.getElementById('bulkScheduleModal').style.display = 'none';
    document.body.style.overflow = '';
    clearBulkFilters();
}

function toggleAllEmployees(checkbox) {
    const checkboxes = document.querySelectorAll('.employee-checkbox:not([style*="display: none"])');
    checkboxes.forEach(cb => {
        if (cb.closest('.employee-item').style.display !== 'none') {
            cb.checked = checkbox.checked;
        }
    });
    updateSelectedCount();
}

function filterBulkEmployees() {
    const departmentFilter = document.getElementById('bulkFilterDepartment').value;
    const designationFilter = document.getElementById('bulkFilterDesignation').value;
    const items = document.querySelectorAll('.employee-item');
    
    items.forEach(item => {
        const itemDept = item.dataset.departmentId;
        const itemDesig = item.dataset.designationId;
        
        const deptMatch = !departmentFilter || itemDept === departmentFilter;
        const desigMatch = !designationFilter || itemDesig === designationFilter;
        
        if (deptMatch && desigMatch) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
            item.querySelector('.employee-checkbox').checked = false;
        }
    });
    
    updateSelectedCount();
    updateSelectAllCheckbox();
}

function selectFilteredEmployees() {
    const items = document.querySelectorAll('.employee-item');
    items.forEach(item => {
        if (item.style.display !== 'none') {
            item.querySelector('.employee-checkbox').checked = true;
        }
    });
    updateSelectedCount();
    updateSelectAllCheckbox();
}

function clearBulkFilters() {
    document.getElementById('bulkFilterDepartment').value = '';
    document.getElementById('bulkFilterDesignation').value = '';
    
    const items = document.querySelectorAll('.employee-item');
    items.forEach(item => {
        item.style.display = 'flex';
    });
    
    updateSelectAllCheckbox();
}

function updateSelectAllCheckbox() {
    const visibleCheckboxes = Array.from(document.querySelectorAll('.employee-checkbox')).filter(cb => {
        return cb.closest('.employee-item').style.display !== 'none';
    });
    const checkedVisible = visibleCheckboxes.filter(cb => cb.checked);
    const selectAll = document.getElementById('selectAllEmployees');
    
    if (visibleCheckboxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } else if (checkedVisible.length === visibleCheckboxes.length) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else if (checkedVisible.length > 0) {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    } else {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelectedCount();
            updateSelectAllCheckbox();
        });
    });
    
    // Populate designations based on department filter
    document.getElementById('bulkFilterDepartment').addEventListener('change', function() {
        const deptId = this.value;
        const designationSelect = document.getElementById('bulkFilterDesignation');
        
        if (!deptId) {
            designationSelect.innerHTML = '<option value="">All Designations</option>';
            return;
        }
        
        fetch(`/admin/departments/${deptId}/designations`)
            .then(response => response.json())
            .then(designations => {
                let options = '<option value="">All Designations</option>';
                designations.forEach(d => {
                    options += `<option value="${d.id}">${d.title}</option>`;
                });
                designationSelect.innerHTML = options;
            });
    });
});

function updateSelectedCount() {
    const checked = document.querySelectorAll('.employee-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked;
}
</script>
