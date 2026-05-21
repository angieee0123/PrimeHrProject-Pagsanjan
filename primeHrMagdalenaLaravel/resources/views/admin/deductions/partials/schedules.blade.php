<div id="schedules-tab" style="display: none;">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Deduction Schedules</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Manage when deductions are applied per cutoff period for each employee</p>
        </div>
        <div class="table-actions">
            <input type="text" id="searchSchedule" class="filter-select" placeholder="Search employee..." style="width: 200px;" onkeyup="filterSchedules()">
            <select id="filterDepartment" class="filter-select" onchange="filterSchedules()">
                <option value="">All Departments</option>
                @foreach(\App\Models\Department::where('status', 'Active')->orderBy('name')->get() as $dept)
                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <button class="btn-export" onclick="exportSchedules()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
        </div>
    </div>

<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; color: #1e40af;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="16" x2="12" y2="12"/>
        <line x1="12" y1="8" x2="12.01" y2="8"/>
    </svg>
    <div style="font-size: 13px;">
        <strong>Deduction Scheduling:</strong> This table shows all employees with active deductions. Click "Manage Schedule" to configure which cutoff period (1st, 2nd, or Both) each deduction will be applied.
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Active Deductions</th>
                <th>Active Loans</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="schedulesTableBody">
            @forelse($employeesWithDeductions as $emp)
                <tr data-employee="{{ strtolower($emp['name']) }}" data-department="{{ $emp['department'] }}">
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar" style="background: {{ $avatarColors[($emp['id'] ?? 0) % count($avatarColors)] }};">
                                {{ getInitials($emp['name']) }}
                            </div>
                            <div>
                                <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">{{ $emp['name'] }}</p>
                                <p style="color: #9999bb; margin: 0; font-size: 11px;">ID: {{ $emp['employee_id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">{{ $emp['department'] }}</span>
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #f0effe; border-radius: 6px; font-size: 12px; font-weight: 600; color: #0b044d;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                            {{ $emp['deductions_count'] }} {{ $emp['deductions_count'] == 1 ? 'Deduction' : 'Deductions' }}
                        </span>
                    </td>
                    <td>
                        @if($emp['loans_count'] > 0)
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #fff9e6; border-radius: 6px; font-size: 12px; font-weight: 600; color: #8b7500;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 6v6l4 2"/>
                                </svg>
                                {{ $emp['loans_count'] }} {{ $emp['loans_count'] == 1 ? 'Loan' : 'Loans' }}
                            </span>
                        @else
                            <span style="color: #9999bb; font-size: 12px;">No loans</span>
                        @endif
                    </td>
                    <td style="color: #6b6a8a; font-size: 12px;">
                        {{ $emp['updated_at'] ? \Carbon\Carbon::parse($emp['updated_at'])->format('M d, Y') : 'N/A' }}
                    </td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view" onclick="openAssignDeductionScheduleModal({{ $emp['id'] }}, '{{ $emp['name'] }}')">Manage Schedule</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noSchedulesRow">
                    <td colspan="6" style="text-align: center; padding: 40px; color: #9999bb;">
                        No employees with active deductions found. Assign deductions to employees first.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="table-footer">
        <p>Showing <strong id="showingSchedulesCount">{{ count($employeesWithDeductions) }}</strong> of <strong id="totalSchedulesCount">{{ count($employeesWithDeductions) }}</strong> employees</p>
    </div>
</section>

<script>
function filterSchedules() {
    const searchTerm = document.getElementById('searchSchedule').value.toLowerCase();
    const departmentFilter = document.getElementById('filterDepartment').value;
    const rows = document.querySelectorAll('#schedulesTableBody tr:not(#noSchedulesRow)');
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        const employeeName = row.dataset.employee || '';
        const department = row.dataset.department || '';
        
        const matchesSearch = employeeName.includes(searchTerm);
        const matchesDepartment = !departmentFilter || department === departmentFilter;
        
        if (matchesSearch && matchesDepartment) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update showing count
    document.getElementById('showingSchedulesCount').textContent = visibleCount;
    
    // Show/hide no data row
    const noSchedulesRow = document.getElementById('noSchedulesRow');
    if (noSchedulesRow) {
        noSchedulesRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

function exportSchedules() {
    window.location.href = '/admin/deductions/schedules/export';
}
</script>

@include('admin.deductions.modals.assignDeductionScheduleModal')
</div>
