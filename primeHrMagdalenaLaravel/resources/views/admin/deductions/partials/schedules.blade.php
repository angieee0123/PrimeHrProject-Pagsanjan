<div class="table-header" style="margin-bottom: 16px;">
    <div>
        <h3 class="table-title" style="font-size: 16px; margin-bottom: 4px;">Deduction Schedules</h3>
        <p class="table-sub">Manage employee-specific deduction schedules per cutoff period</p>
    </div>
    <div class="table-actions">
        <input type="text" class="filter-select" placeholder="Search employee..." style="width: 200px;">
        <select class="filter-select">
            <option value="">All Departments</option>
            <option value="MHO">Municipal Health Office</option>
            <option value="MEO">Office of the Mun. Engineer</option>
        </select>
        <button class="btn-export">
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
        <strong>Per-Employee Scheduling:</strong> Select an employee to configure which cutoff period (1st or 2nd) their deductions will be applied for each month.
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Active Deductions</th>
                <th>Active Loans</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Sample data - replace with actual data from backend
                $employees = [
                    ['id' => 'EMP-001', 'name' => 'Juan Dela Cruz', 'dept' => 'Municipal Health Office', 'deductions' => 4, 'loans' => 1, 'updated' => '2024-01-15'],
                    ['id' => 'EMP-002', 'name' => 'Maria Santos', 'dept' => 'Office of the Mun. Engineer', 'deductions' => 3, 'loans' => 0, 'updated' => '2024-01-10'],
                    ['id' => 'EMP-003', 'name' => 'Pedro Reyes', 'dept' => 'Municipal Health Office', 'deductions' => 4, 'loans' => 2, 'updated' => '2024-01-12'],
                ];
            @endphp
            @forelse($employees as $emp)
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">{{ $emp['id'] }}</strong></td>
                <td>{{ $emp['name'] }}</td>
                <td>{{ $emp['dept'] }}</td>
                <td>
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #f0effe; border-radius: 6px; font-size: 12px; font-weight: 600; color: #0b044d;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                        </svg>
                        {{ $emp['deductions'] }} Deductions
                    </span>
                </td>
                <td>
                    @if($emp['loans'] > 0)
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #fff9e6; border-radius: 6px; font-size: 12px; font-weight: 600; color: #8b7500;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                        {{ $emp['loans'] }} Loans
                    </span>
                    @else
                    <span style="color: #9999bb; font-size: 12px;">No loans</span>
                    @endif
                </td>
                <td style="color: #6b6a8a; font-size: 12px;">{{ \Carbon\Carbon::parse($emp['updated'])->format('M d, Y') }}</td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="openAssignDeductionScheduleModal('{{ $emp['id'] }}', '{{ $emp['name'] }}')">Manage Schedule</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #9999bb;">
                    No employees found. Employees with active deductions will appear here.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-footer">
    <p>Showing <strong>{{ count($employees) }}</strong> of <strong>{{ count($employees) }}</strong> employees</p>
</div>

@include('admin.deductions.modals.assignDeductionScheduleModal')
