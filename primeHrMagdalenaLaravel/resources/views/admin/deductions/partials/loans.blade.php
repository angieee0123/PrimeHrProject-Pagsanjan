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
    <table class="payroll-table lt-table">
        <thead>
            <tr>
                <th class="lt-c-employee">Employee</th>
                <th class="lt-c-loan">Loan</th>
                <th class="lt-c-balance lt-num">Balance</th>
                <th class="lt-c-progress">Repayment</th>
                <th class="lt-c-amort lt-num">Amortization</th>
                <th class="lt-c-term">Term</th>
                <th class="lt-c-status">Status</th>
                <th class="lt-c-actions row-menu-head">Actions</th>
            </tr>
        </thead>
        <tbody id="loansTableBody">
            @forelse($loans as $loan)
                @php
                    $totalAmount = $loan->total_amount ?? 0;
                    $remaining = $loan->remaining_balance ?? 0;
                    $paidAmount = max(0, $totalAmount - $remaining);
                    $progress = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;
                    $progressWidth = max(0, min(100, $progress));

                    // Get the deduction schedule - prioritize custom schedule over default
                    if ($loan->custom_cutoff_schedule) {
                        // Use employee's custom schedule
                        $cutoffSchedule = $loan->custom_cutoff_schedule;
                    } else {
                        // Use deduction type's default schedule
                        $schedule = $loan->deductionType?->schedules?->first();
                        $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                    }

                    // Calculate per-cutoff based on schedule
                    $monthlyInstallment = $loan->installment_amount ?? 0;
                    if ($cutoffSchedule === '1ST_ONLY') {
                        $perCutoffAmount = $monthlyInstallment;
                        $cutoffLabel = '1st cutoff only';
                    } elseif ($cutoffSchedule === '2ND_ONLY') {
                        $perCutoffAmount = $monthlyInstallment;
                        $cutoffLabel = '2nd cutoff only';
                    } elseif ($cutoffSchedule === 'BOTH_FULL') {
                        $perCutoffAmount = $monthlyInstallment;
                        $cutoffLabel = 'each cutoff';
                    } else { // BOTH_SPLIT (default)
                        $perCutoffAmount = $monthlyInstallment / 2;
                        $cutoffLabel = 'split per cutoff';
                    }

                    // Determine provider from loan type code
                    $provider = 'Other';
                    $providerClass = 'is-other';
                    $deductionCode = $loan->deductionType?->code ?? '';
                    if (str_contains($deductionCode, 'GSIS')) {
                        $provider = 'GSIS';
                        $providerClass = 'is-gsis';
                    } elseif (str_contains($deductionCode, 'PAGIBIG')) {
                        $provider = 'Pag-IBIG';
                        $providerClass = 'is-pagibig';
                    }

                    $statusClasses = [
                        'ACTIVE' => 'is-active',
                        'SUSPENDED' => 'is-suspended',
                        'COMPLETED' => 'is-completed',
                    ];
                    $statusClass = $statusClasses[$loan->status] ?? 'is-active';

                    $loanEmp = $loan->employee;
                    $loanType = $loan->deductionType;
                    $loanFullName = $loanEmp ? trim(($loanEmp->first_name ?? '') . ' ' . ($loanEmp->last_name ?? '')) : '';
                    $loanFullName = $loanFullName !== '' ? $loanFullName : 'Unknown Employee';
                    $isSettled = $remaining <= 0;
                    $barClass = $isSettled ? 'is-settled' : ($loan->status === 'SUSPENDED' ? 'is-suspended' : 'is-active');
                @endphp
                <tr data-employee="{{ strtolower($loanFullName) }}"
                    data-loan-type="{{ $loan->deduction_type_id }}"
                    data-status="{{ $loan->status }}">
                    <td class="lt-c-employee">
                        <div class="lt-employee">
                            @if($loanEmp?->photo)
                                <img src="{{ $loanEmp->photo }}" alt="" class="lt-avatar">
                            @else
                                <div class="lt-avatar lt-avatar-initials" style="background: {{ $avatarColors[($loan->employee_id ?? 0) % count($avatarColors)] }};">
                                    {{ getInitials($loanFullName) }}
                                </div>
                            @endif
                            <div class="lt-employee-text">
                                <p class="lt-primary">{{ $loanFullName }}</p>
                                <p class="lt-secondary">{{ $loanEmp?->employmentDetail?->departmentRelation?->name ?? 'No department' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="lt-c-loan">
                        <p class="lt-primary">{{ $loanType->name ?? 'Unknown Deduction' }}</p>
                        <div class="lt-loan-meta">
                            <span class="lt-tag {{ $providerClass }}">{{ $provider }}</span>
                            <span class="lt-code">{{ $loanType->code ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="lt-c-balance lt-num">
                        <p class="lt-amount {{ $isSettled ? 'is-settled' : 'is-due' }}">₱{{ number_format($remaining, 2) }}</p>
                        <p class="lt-secondary">of ₱{{ number_format($totalAmount, 2) }}</p>
                    </td>
                    <td class="lt-c-progress">
                        <div class="lt-progress">
                            <div class="lt-progress-head">
                                <span class="lt-progress-pct">{{ number_format($progress, 1) }}%</span>
                                <span class="lt-progress-note {{ $isSettled ? 'is-settled' : '' }}">
                                    {{ $isSettled ? 'Fully paid' : '₱' . number_format($paidAmount, 2) . ' paid' }}
                                </span>
                            </div>
                            <div class="lt-progress-bar" role="img" aria-label="{{ number_format($progress, 1) }} percent repaid">
                                <div class="lt-progress-fill {{ $barClass }}" style="width: {{ $progressWidth }}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="lt-c-amort lt-num">
                        <p class="lt-amount">₱{{ number_format($monthlyInstallment, 2) }}<span class="lt-unit">/mo</span></p>
                        <p class="lt-secondary">
                            ₱{{ number_format($perCutoffAmount, 2) }} {{ $cutoffLabel }}
                            @if($loan->custom_cutoff_schedule)
                                <span class="lt-tag is-custom">Custom</span>
                            @endif
                        </p>
                    </td>
                    <td class="lt-c-term">
                        <p class="lt-muted">{{ \Carbon\Carbon::parse($loan->start_date)->format('M d, Y') }}</p>
                        <p class="lt-secondary">
                            @if($loan->end_date)
                                to {{ \Carbon\Carbon::parse($loan->end_date)->format('M d, Y') }}
                            @else
                                Ongoing
                            @endif
                        </p>
                    </td>
                    <td class="lt-c-status">
                        <span class="lt-status {{ $statusClass }}">{{ ucfirst(strtolower($loan->status)) }}</span>
                    </td>
                    <td class="lt-c-actions">
                        <div class="lt-actions">
                            <button type="button" class="lt-action-btn lt-menu-btn" data-menu="loanMenu{{ $loan->id }}"
                                    onclick="toggleLoanMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                    title="Actions"
                                    aria-label="Actions for {{ $loanFullName }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                                </svg>
                            </button>
                        </div>
                        {{-- Opened by loans.js, which relocates it to <body> and positions it
                             fixed — .table-section clips its overflow, so a menu left in this
                             cell would be cut off at the card edge. --}}
                        <div class="lt-menu" id="loanMenu{{ $loan->id }}" role="menu" aria-label="Loan actions">
                            <button type="button" role="menuitem" class="lt-menu-item" onclick="closeLoanMenu(); viewLoanDetails({{ $loan->id }})">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                View details
                            </button>
                            <button type="button" role="menuitem" class="lt-menu-item" onclick="closeLoanMenu(); editEmployeeDeduction({{ $loan->id }})">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit loan
                            </button>
                            <div class="lt-menu-sep"></div>
                            <button type="button" role="menuitem" class="lt-menu-item is-danger" onclick="closeLoanMenu(); deleteEmployeeLoan({{ $loan->id }}, @js($loanFullName), @js($loanType->name ?? 'Unknown Deduction'), {{ $remaining }})">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                                Delete loan
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noLoansRow">
                    <td colspan="8" class="lt-empty">
                        <div class="lt-empty-body">
                            <div class="lt-empty-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                            </div>
                            <p class="lt-empty-title">No loans recorded yet</p>
                            <p class="lt-empty-sub">GSIS and Pag-IBIG loans you add will appear here with their balances tracked automatically.</p>
                            <button type="button" class="lt-empty-cta" onclick="openAddLoanModal()">Add Loan</button>
                        </div>
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
