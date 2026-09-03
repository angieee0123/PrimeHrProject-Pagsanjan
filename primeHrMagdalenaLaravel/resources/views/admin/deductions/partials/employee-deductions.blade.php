{{--
    Employee Deductions.

    Ten columns did not fit: the table measured 1200px inside a 1142px
    wrapper, so the Actions menu sat past the right edge and every employee
    name wrapped onto three lines. Two pairs are folded together instead —
    Category into the deduction it describes, and Start/End into one Period —
    which brings it to eight and lets each cell hold a single line.
--}}
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
    <table class="payroll-table ded-emp-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Deduction</th>
                <th class="ded-num-col">Amount / Balance</th>
                <th>Cutoff</th>
                <th>Period</th>
                <th>Status</th>
                <th class="row-menu-head">Actions</th>
            </tr>
        </thead>
        <tbody id="employeeDeductionsTableBody">
            @forelse($employeeDeductions as $deduction)
                @php
                    $emp        = $deduction->employee;
                    $type       = $deduction->deductionType;
                    $fullName   = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '';
                    $fullName   = $fullName !== '' ? $fullName : 'Unknown Employee';
                    $department = $emp?->employmentDetail?->departmentRelation?->name ?? 'N/A';

                    // Category drives a themed chip rather than a hand-mixed
                    // hex with an alpha suffix.
                    $categoryClass = match ($type?->category ?? 'OTHER') {
                        'MANDATORY' => 'is-mandatory',
                        'LOAN'      => 'is-loan',
                        default     => 'is-other',
                    };

                    // Human wording only. The raw enum (BOTH_SPLIT, 1ST_ONLY)
                    // used to be printed underneath, which is storage detail
                    // rather than something an HR officer needs to read.
                    $cutoff = $type?->schedules?->first()?->cutoff_schedule ?? 'BOTH_SPLIT';
                    $cutoffLabel = match ($cutoff) {
                        '1ST_ONLY'  => '1st cutoff',
                        '2ND_ONLY'  => '2nd cutoff',
                        'BOTH_FULL' => 'Both — full each',
                        default     => 'Both — split 50/50',
                    };

                    $statusClass = match ($deduction->status) {
                        'ACTIVE'    => 'processed',
                        'SUSPENDED' => 'pending',
                        'COMPLETED' => 'is-neutral',
                        default     => 'is-neutral',
                    };

                    $isLoan = ($type?->category ?? '') === 'LOAN';
                    $total  = (float) ($deduction->total_amount ?? 0);
                    $left   = (float) ($deduction->remaining_balance ?? 0);
                    $paidPc = $total > 0 ? max(0, min(100, round((($total - $left) / $total) * 100))) : 0;
                @endphp
                <tr data-employee="{{ strtolower($fullName) }}"
                    data-type="{{ $type?->category ?? '' }}"
                    data-status="{{ $deduction->status }}">

                    <td>
                        <div class="ded-row-flex">
                            @if($emp?->photo)
                                <img src="{{ $emp->photo }}" alt="" class="ded-avatar-img" loading="lazy">
                            @else
                                <span class="ded-avatar-img ded-avatar-initials"
                                       style="background: {{ $avatarColors[($deduction->employee_id ?? 0) % count($avatarColors)] }}"
                                       aria-hidden="true">{{ getInitials($fullName) }}</span>
                            @endif
                            <div class="ded-emp-text">
                                <p class="ded-cell-title" title="{{ $fullName }}">{{ $fullName }}</p>
                                <p class="ded-cell-sub">{{ $emp->employee_id ?? '—' }}</p>
                            </div>
                        </div>
                    </td>

                    <td><span class="dept-tag" title="{{ $department }}">{{ $department }}</span></td>

                    <td>
                        <p class="ded-cell-title" title="{{ $type->name ?? 'Unknown Deduction' }}">{{ $type->name ?? 'Unknown Deduction' }}</p>
                        <p class="ded-cell-sub">
                            <span class="ded-chip {{ $categoryClass }}">{{ ucfirst(strtolower($type?->category ?? 'other')) }}</span>
                            <span class="ded-code">{{ $type->code ?? '—' }}</span>
                        </p>
                    </td>

                    <td class="ded-num-col">
                        @if($isLoan)
                            <p class="ded-amount">₱{{ number_format($left, 2) }}</p>
                            <p class="ded-cell-sub">of ₱{{ number_format($total, 2) }}</p>
                            {{-- A balance means little without knowing how far
                                 through the loan the employee is. --}}
                            <span class="ded-progress" role="img"
                                   aria-label="{{ $paidPc }} percent repaid">
                                <span class="ded-progress-fill" style="width: {{ $paidPc }}%"></span>
                            </span>
                        @elseif(($type?->computation_type ?? '') === 'PERCENTAGE')
                            <p class="ded-amount">{{ $type->percentage_rate }}%</p>
                            @if($type?->max_amount)
                                <p class="ded-cell-sub">max ₱{{ number_format($type->max_amount, 2) }}</p>
                            @endif
                        @elseif($deduction->amount)
                            <p class="ded-amount">₱{{ number_format($deduction->amount, 2) }}</p>
                        @else
                            <p class="ded-cell-sub ded-auto">Auto-computed</p>
                        @endif
                    </td>

                    <td><span class="ded-text-muted-sm">{{ $cutoffLabel }}</span></td>

                    <td>
                        <p class="ded-text-muted-sm">{{ \Carbon\Carbon::parse($deduction->start_date)->format('M d, Y') }}</p>
                        <p class="ded-cell-sub">
                            @if($deduction->end_date)
                                to {{ \Carbon\Carbon::parse($deduction->end_date)->format('M d, Y') }}
                            @else
                                Ongoing
                            @endif
                        </p>
                    </td>

                    <td><span class="badge-status {{ $statusClass }}">{{ ucfirst(strtolower($deduction->status)) }}</span></td>

                    <td class="row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="empDeductionMenu{{ $deduction->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for {{ $fullName }}">
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
                            <button type="button" role="menuitem" class="row-menu-item is-danger" onclick="closeRowMenu(); deleteEmployeeDeduction({{ $deduction->id }}, @js($fullName), @js($type->name ?? 'Unknown Deduction'), {{ $isLoan ? $left : 'null' }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Delete deduction
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noDataRow">
                    <td colspan="8" class="ded-empty-cell">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="ded-empty-icon"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <p class="ded-empty-title">No employee deductions yet</p>
                        <p class="ded-empty-sub">Use <strong>Assign Deduction</strong> to add the first one.</p>
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
