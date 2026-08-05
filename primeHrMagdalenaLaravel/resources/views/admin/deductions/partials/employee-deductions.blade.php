<div id="employee-deductions-tab" class="ded-hidden">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Deductions</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Assign and manage deductions for employees</p>
        </div>
        <div class="table-actions">
            <button class="btn-export adm-btn-primary-solid" onclick="openAssignDeductionModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Assign Deduction
            </button>
        </div>
    </div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Deduction Type</th>
                <th>Category</th>
                <th>Amount/Balance</th>
                <th>Cutoff Schedule</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th class="row-menu-head">Actions</th>
            </tr>
        </thead>
        <tbody id="employeeDeductionsTableBody">
            @forelse($employeeDeductions as $deduction)
                <tr data-employee="{{ strtolower($deduction->employee->first_name . ' ' . $deduction->employee->last_name) }}" 
                    data-type="{{ $deduction->deductionType->category }}" 
                    data-status="{{ $deduction->status }}">
                    <td>
                        <div class="ded-row-flex">
                            @if($deduction->employee->photo)
                                <img src="{{ $deduction->employee->photo }}" class="ded-avatar-img">
                            @else
                                <div class="avatar ded-avatar-img" style="background: {{ $avatarColors[($deduction->employee_id ?? 0) % count($avatarColors)] }}; display:flex; align-items:center; justify-content:center; color:white; font-weight:600; font-size:13px;">
                                    {{ getInitials($deduction->employee->first_name . ' ' . $deduction->employee->last_name) }}
                                </div>
                            @endif
                            <div>
                                <p class="ded-cell-title">
                                    {{ $deduction->employee->first_name }} {{ $deduction->employee->last_name }}
                                </p>
                                <p class="ded-cell-sub">ID: {{ $deduction->employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">
                            {{ $deduction->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p class="ded-cell-title">{{ $deduction->deductionType->name }}</p>
                            <p class="ded-cell-sub">{{ $deduction->deductionType->code }}</p>
                        </div>
                    </td>
                    <td>
                        @php
                            $categoryColors = [
                                'MANDATORY' => ['bg' => '#0b044d18', 'text' => '#0b044d'],
                                'LOAN' => ['bg' => '#c9a22718', 'text' => '#c9a227'],
                                'OTHER' => ['bg' => '#56547a18', 'text' => '#56547a'],
                            ];
                            $colors = $categoryColors[$deduction->deductionType->category] ?? $categoryColors['OTHER'];
                        @endphp
                        <span class="badge" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};">
                            {{ $deduction->deductionType->category }}
                        </span>
                    </td>
                    <td>
                        @if($deduction->deductionType->category === 'LOAN')
                            <div>
                                <p class="ded-cell-title">
                                    ₱{{ number_format($deduction->remaining_balance ?? 0, 2) }}
                                </p>
                                <p class="ded-cell-sub">
                                    of ₱{{ number_format($deduction->total_amount ?? 0, 2) }}
                                </p>
                            </div>
                        @elseif($deduction->deductionType->computation_type === 'PERCENTAGE')
                            <span class="ded-text-sm">
                                {{ $deduction->deductionType->percentage_rate }}% 
                                @if($deduction->deductionType->max_amount)
                                    (max ₱{{ number_format($deduction->deductionType->max_amount, 2) }})
                                @endif
                            </span>
                        @elseif($deduction->amount)
                            <span class="ded-text-sm">₱{{ number_format($deduction->amount, 2) }}</span>
                        @else
                            <span class="ded-text-faint-sm">Auto-computed</span>
                        @endif
                    </td>
                    <td>
                        @php
                            // Get the deduction schedule
                            $schedule = $deduction->deductionType->schedules->first();
                            $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                            
                            // Display cutoff schedule
                            if ($cutoffSchedule === '1ST_ONLY') {
                                $scheduleDisplay = '1st Cutoff Only';
                                $scheduleColor = '#0b044d';
                            } elseif ($cutoffSchedule === '2ND_ONLY') {
                                $scheduleDisplay = '2nd Cutoff Only';
                                $scheduleColor = '#15803d';
                            } elseif ($cutoffSchedule === 'BOTH_FULL') {
                                $scheduleDisplay = 'Both (Full Each)';
                                $scheduleColor = '#c9a227';
                            } else { // BOTH_SPLIT
                                $scheduleDisplay = 'Both (Split 50-50)';
                                $scheduleColor = '#56547a';
                            }
                        @endphp
                        <div>
                            <p style="color: {{ $scheduleColor }};" class="ded-cell-title">
                                {{ $scheduleDisplay }}
                            </p>
                            <p class="ded-cell-sub">{{ $cutoffSchedule }}</p>
                        </div>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">
                            {{ \Carbon\Carbon::parse($deduction->start_date)->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">
                            {{ $deduction->end_date ? \Carbon\Carbon::parse($deduction->end_date)->format('M d, Y') : 'Ongoing' }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'ACTIVE' => ['bg' => '#15803d18', 'text' => '#15803d'],
                                'SUSPENDED' => ['bg' => '#c9a22718', 'text' => '#c9a227'],
                                'COMPLETED' => ['bg' => '#56547a18', 'text' => '#56547a'],
                            ];
                            $statusColor = $statusColors[$deduction->status] ?? $statusColors['ACTIVE'];
                        @endphp
                        <span class="badge" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }};">
                            {{ $deduction->status }}
                        </span>
                    </td>
                    <td class="row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="empDeductionMenu{{ $deduction->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for {{ $deduction->employee->first_name }} {{ $deduction->employee->last_name }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="empDeductionMenu{{ $deduction->id }}" role="menu" aria-label="Deduction actions">
                            <button type="button" role="menuitem" class="row-menu-item" onclick="closeRowMenu(); editEmployeeDeduction({{ $deduction->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit deduction
                            </button>
                            <div class="row-menu-sep"></div>
                            <button type="button" role="menuitem" class="row-menu-item is-danger" onclick="closeRowMenu(); deleteEmployeeDeduction({{ $deduction->id }}, '{{ $deduction->employee->first_name }} {{ $deduction->employee->last_name }}', '{{ $deduction->deductionType->name }}')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Delete deduction
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noDataRow">
                    <td colspan="10" class="ded-empty-cell">
                        No employee deductions found. Click "Assign Deduction" to add.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="table-footer">
        <div class="ded-footer-flex">
            <p id="employeeDeductionsFooter">Showing <strong id="deductionRowStart">1</strong>-<strong id="deductionRowEnd">{{ min(10, $employeeDeductions->count()) }}</strong> of <strong id="deductionRowTotal">{{ $employeeDeductions->count() }}</strong> records</p>
            <select id="deductionRowsPerPage" class="filter-select ded-rows-select" onchange="changeDeductionRowsPerPage()">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="deductionPaginationControls"></div>
    </div>
</section>

@push('scripts')
    @vite('resources/js/admin/deductions/employee-deductions.js')
@endpush

</div>
