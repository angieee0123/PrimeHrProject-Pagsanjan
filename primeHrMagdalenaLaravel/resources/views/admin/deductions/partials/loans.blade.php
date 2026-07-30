<div id="loans-tab" class="ded-hidden">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Loans</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Manage GSIS and Pag-IBIG loans with automatic balance tracking</p>
        </div>
        <div class="table-actions">
            <button class="btn-export adm-btn-primary-solid" onclick="openAddLoanModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Loan
            </button>
        </div>
    </div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Loan Type</th>
                <th>Provider</th>
                <th>Total Amount</th>
                <th>Remaining Balance</th>
                <th>Monthly Installment</th>
                <th>Per Cutoff</th>
                <th>Progress</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="loansTableBody">
            @forelse($loans as $loan)
                @php
                    $progress = $loan->total_amount > 0 ? (($loan->total_amount - $loan->remaining_balance) / $loan->total_amount) * 100 : 0;
                    
                    // Get the deduction schedule - prioritize custom schedule over default
                    $cutoffSchedule = 'BOTH_SPLIT'; // Default fallback
                    
                    if ($loan->custom_cutoff_schedule) {
                        // Use employee's custom schedule
                        $cutoffSchedule = $loan->custom_cutoff_schedule;
                    } else {
                        // Use deduction type's default schedule
                        $schedule = $loan->deductionType->schedules->first();
                        $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                    }
                    
                    // Calculate per-cutoff based on schedule
                    $monthlyInstallment = $loan->installment_amount ?? 0;
                    if ($cutoffSchedule === '1ST_ONLY') {
                        $perCutoff1st = $monthlyInstallment;
                        $perCutoff2nd = 0;
                        $perCutoffDisplay = '₱' . number_format($perCutoff1st, 2) . ' (1st only)';
                    } elseif ($cutoffSchedule === '2ND_ONLY') {
                        $perCutoff1st = 0;
                        $perCutoff2nd = $monthlyInstallment;
                        $perCutoffDisplay = '₱' . number_format($perCutoff2nd, 2) . ' (2nd only)';
                    } elseif ($cutoffSchedule === 'BOTH_FULL') {
                        $perCutoff1st = $monthlyInstallment;
                        $perCutoff2nd = $monthlyInstallment;
                        $perCutoffDisplay = '₱' . number_format($monthlyInstallment, 2) . ' (each cutoff)';
                    } else { // BOTH_SPLIT (default)
                        $perCutoff1st = $monthlyInstallment / 2;
                        $perCutoff2nd = $monthlyInstallment / 2;
                        $perCutoffDisplay = '₱' . number_format($perCutoff1st, 2) . ' (split)';
                    }
                    
                    // Add indicator if using custom schedule
                    $scheduleLabel = $cutoffSchedule;
                    if ($loan->custom_cutoff_schedule) {
                        $scheduleLabel .= ' (Custom)';
                    }
                    
                    // Determine provider from loan type code
                    $provider = 'Other';
                    if (str_contains($loan->deductionType->code, 'GSIS')) {
                        $provider = 'GSIS';
                    } elseif (str_contains($loan->deductionType->code, 'PAGIBIG')) {
                        $provider = 'Pag-IBIG';
                    }
                @endphp
                <tr data-employee="{{ strtolower($loan->employee->first_name . ' ' . $loan->employee->last_name) }}" 
                    data-loan-type="{{ $loan->deduction_type_id }}" 
                    data-status="{{ $loan->status }}">
                    <td>
                        <div class="ded-row-flex">
                            @if($loan->employee->photo)
                                <img src="{{ $loan->employee->photo }}" class="ded-avatar-img">
                            @else
                                <div class="avatar ded-avatar-img" style="background: {{ $avatarColors[($loan->employee_id ?? 0) % count($avatarColors)] }}; display:flex; align-items:center; justify-content:center; color:white; font-weight:600; font-size:13px;">
                                    {{ getInitials($loan->employee->first_name . ' ' . $loan->employee->last_name) }}
                                </div>
                            @endif
                            <div>
                                <p class="ded-cell-title">
                                    {{ $loan->employee->first_name }} {{ $loan->employee->last_name }}
                                </p>
                                <p class="ded-cell-sub">ID: {{ $loan->employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">
                            {{ $loan->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p class="ded-cell-title">{{ $loan->deductionType->name }}</p>
                            <p class="ded-cell-sub">{{ $loan->deductionType->code }}</p>
                        </div>
                    </td>
                    <td>
                        @php
                            $providerColors = [
                                'GSIS' => ['bg' => '#0b044d18', 'text' => '#0b044d'],
                                'Pag-IBIG' => ['bg' => '#15803d18', 'text' => '#15803d'],
                                'Other' => ['bg' => '#56547a18', 'text' => '#56547a'],
                            ];
                            $providerColor = $providerColors[$provider] ?? $providerColors['Other'];
                        @endphp
                        <span class="badge" style="background: {{ $providerColor['bg'] }}; color: {{ $providerColor['text'] }};">
                            {{ $provider }}
                        </span>
                    </td>
                    <td>
                        <span class="ded-cell-title" style="font-size:13px;">
                            ₱{{ number_format($loan->total_amount ?? 0, 2) }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p class="ded-cell-title" style="color: {{ $loan->remaining_balance > 0 ? '#c9a227' : '#15803d' }};">
                                ₱{{ number_format($loan->remaining_balance ?? 0, 2) }}
                            </p>
                            @if($loan->remaining_balance > 0)
                                <p class="ded-cell-sub">
                                    {{ number_format($progress, 1) }}% paid
                                </p>
                            @else
                                <p class="ded-cell-sub" style="color:#15803d;">Fully paid</p>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="ded-text-sm">
                            ₱{{ number_format($loan->installment_amount ?? 0, 2) }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p class="ded-cell-title">
                                {!! $perCutoffDisplay !!}
                            </p>
                            <p class="ded-cell-sub">{{ $scheduleLabel }}</p>
                        </div>
                    </td>
                    <td class="ded-min-w-120">
                        <div class="ded-progress-track">
                            <div class="ded-progress-bar">
                                <div class="ded-progress-fill" style="width: {{ $progress }}%; background: {{ $progress >= 100 ? '#15803d' : '#0b044d' }};"></div>
                            </div>
                            <span class="ded-progress-pct">{{ number_format($progress, 1) }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">
                            {{ \Carbon\Carbon::parse($loan->start_date)->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">
                            {{ $loan->end_date ? \Carbon\Carbon::parse($loan->end_date)->format('M d, Y') : 'Ongoing' }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'ACTIVE' => ['bg' => '#15803d18', 'text' => '#15803d'],
                                'SUSPENDED' => ['bg' => '#c9a22718', 'text' => '#c9a227'],
                                'COMPLETED' => ['bg' => '#56547a18', 'text' => '#56547a'],
                            ];
                            $statusColor = $statusColors[$loan->status] ?? $statusColors['ACTIVE'];
                        @endphp
                        <span class="badge" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }};">
                            {{ $loan->status }}
                        </span>
                    </td>
                    <td>
                        <div class="ded-actions">
                            <button class="action-btn" onclick="viewLoanDetails({{ $loan->id }})" title="View Details">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button class="action-btn" onclick="editEmployeeDeduction({{ $loan->id }})" title="Edit">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <button class="action-btn ded-danger-btn" onclick="deleteEmployeeDeduction({{ $loan->id }}, '{{ $loan->employee->first_name }} {{ $loan->employee->last_name }}', '{{ $loan->deductionType->name }}')" title="Delete">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noLoansRow">
                    <td colspan="13" class="ded-empty-cell">
                        No loans found. Click "Add Loan" to create a new loan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="table-footer">
        <div class="ded-footer-flex">
            <p id="loansFooter">Showing <strong id="loanRowStart">1</strong>-<strong id="loanRowEnd">{{ min(10, $loans->count()) }}</strong> of <strong id="loanRowTotal">{{ $loans->count() }}</strong> loans</p>
            <select id="loanRowsPerPage" class="filter-select ded-rows-select" onchange="changeLoanRowsPerPage()">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="loanPaginationControls"></div>
    </div>
</section>

@push('scripts')
    @vite('resources/js/admin/deductions/loans.js')
@endpush

</div>
