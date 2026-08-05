<div id="schedules-tab" class="ded-hidden">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Deduction Schedules</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Manage when deductions are applied per cutoff period for each employee</p>
        </div>
    </div>

<div class="ded-notice-banner-teal">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="16" x2="12" y2="12"/>
        <line x1="12" y1="8" x2="12.01" y2="8"/>
    </svg>
    <div class="ded-text-sm">
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
                <th class="th-center">Actions</th>
            </tr>
        </thead>
        <tbody id="schedulesTableBody">
            @forelse($employeesWithDeductions as $emp)
                <tr data-employee="{{ strtolower($emp['name']) }}" data-department="{{ $emp['department'] }}">
                    <td>
                        <div class="ded-row-flex">
                            @if($emp['photo'] ?? null)
                                <img src="{{ $emp['photo'] }}" class="ded-avatar-img">
                            @else
                                <div class="avatar ded-avatar-img" style="background: {{ $avatarColors[($emp['id'] ?? 0) % count($avatarColors)] }}; display:flex; align-items:center; justify-content:center; color:white; font-weight:600; font-size:13px;">
                                    {{ getInitials($emp['name']) }}
                                </div>
                            @endif
                            <div>
                                <p class="ded-cell-title">{{ $emp['name'] }}</p>
                                <p class="ded-cell-sub">ID: {{ $emp['employee_id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">{{ $emp['department'] }}</span>
                    </td>
                    <td>
                        <span class="ded-badge-count is-blue">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                            {{ $emp['deductions_count'] }} {{ $emp['deductions_count'] == 1 ? 'Deduction' : 'Deductions' }}
                        </span>
                    </td>
                    <td>
                        @if($emp['loans_count'] > 0)
                            <span class="ded-badge-count is-gold">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 6v6l4 2"/>
                                </svg>
                                {{ $emp['loans_count'] }} {{ $emp['loans_count'] == 1 ? 'Loan' : 'Loans' }}
                            </span>
                        @else
                            <span class="ded-text-muted-sm">No loans</span>
                        @endif
                    </td>
                    <td class="ded-text-muted-sm">
                        {{ $emp['updated_at'] ? \Carbon\Carbon::parse($emp['updated_at'])->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="td-center">
                        <div class="row-actions">
                            <button class="btn-view" onclick="openAssignDeductionScheduleModal({{ $emp['id'] }}, '{{ $emp['name'] }}')">Manage Schedule</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noSchedulesRow">
                    <td colspan="6" class="ded-empty-cell">
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

@push('scripts')
    @vite('resources/js/admin/deductions/schedules.js')
@endpush

</div>
