@php
    // The desktop table and the mobile card list render the same rows, so the
    // per-cutoff math and category styling are resolved once here for both.
    $categoryColors = [
        'mandatory' => 'background:#e8f9ef;color:#15803d',
        'loan'      => 'background:#fefce8;color:#a16207',
        'voluntary' => 'background:#f0effe;color:#0b044d',
    ];

    $now        = \Carbon\Carbon::now();
    $monthStart = $now->copy()->startOfMonth();
    $monthEnd   = $now->copy()->endOfMonth();

    $deductionRows = $deductions->map(function ($d) use ($categoryColors, $now) {
        $type = $d->deductionType;
        $isFixed = $type && strtoupper($type->computation_type) === 'FIXED';

        // Amounts are stored per cutoff — twice a month.
        $perCutoff = $d->calculated_amount ?? ($d->installment_amount ?? $d->amount ?? 0);
        if ($perCutoff == 0 && $isFixed) {
            $perCutoff = ($type->percentage_rate ?? 0) / 2;
        }

        return (object) [
            'model'       => $d,
            'type'        => $type,
            'perCutoff'   => $perCutoff,
            'monthly'     => $perCutoff * 2,
            'isPercentagePending' => $perCutoff * 2 <= 0
                && $type
                && strtoupper($type->computation_type) === 'PERCENTAGE'
                && $type->percentage_rate > 0,
            'categoryStyle' => $categoryColors[$type->category ?? null] ?? 'background:#f7f6ff;color:#6b6a8a',
            'hasStarted'  => $d->start_date && $d->start_date <= $now,
            'notStarted'  => $d->start_date && $d->start_date > $now,
            'startDate'   => $d->start_date ? $d->start_date->format('Y-m-d') : '',
        ];
    });
@endphp

{{-- Deductions Table --}}
<div class="table-section perm-section" id="deductionsSection">
    <div class="table-header">
        <div>
            <p class="table-title">My Deductions &amp; Loans</p>
            <p class="table-sub">Active deductions from your salary</p>
        </div>
        <div class="table-actions">
            <div class="chart-tabs" onclick="event.stopPropagation();">
                <button class="chart-tab" onclick="switchDeductionView('daily')">Daily</button>
                <button class="chart-tab" onclick="switchDeductionView('weekly')">Weekly</button>
                <button class="chart-tab active" onclick="switchDeductionView('monthly')">Monthly</button>
            </div>
            <button class="btn-export" onclick="exportDeductions()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <button class="modal-btn-primary" onclick="showDeductionSummary()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                View Summary
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table payslip-history-table">
            <thead>
                <tr>
                    <th>Deduction Type</th>
                    <th>Category</th>
                    <th><span id="deductionAmountHeader">Monthly Amount</span></th>
                    <th>Remaining Balance</th>
                    <th><span id="deductionDateHeader">Current Month</span></th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="deductionsTableBody">
                @forelse($deductionRows as $row)
                @php $d = $row->model; @endphp
                <tr onclick="showDeductionModal({{ $d->id }})" class="eh-cursor-pointer" data-deduction-id="{{ $d->id }}">
                    <td class="table-cell-period">
                        <strong>{{ $row->type->name ?? 'N/A' }}</strong>
                        @if($row->type->code)
                            <br><span class="eh-subtext">{{ $row->type->code }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-status" style="{{ $row->categoryStyle }}">{{ ucfirst($row->type->category ?? 'Other') }}</span>
                    </td>
                    <td class="table-cell-basic deduction-amount-cell" data-per-cutoff="{{ $row->perCutoff }}">
                        @if($row->monthly > 0)
                            <span class="deduction-amount">₱{{ number_format($row->monthly, 2) }}</span>
                            <br><span class="eh-subtext deduction-period">per month</span>
                        @elseif($row->isPercentagePending)
                            <span class="eh-pending-note">{{ $row->type->percentage_rate }}% of salary</span>
                            <br><span class="eh-subtext">Pending computation</span>
                        @else
                            <span class="eh-tbd-note">To be computed</span>
                        @endif
                    </td>
                    <td class="table-cell-deduct">
                        @if($d->remaining_balance !== null)
                            ₱{{ number_format($d->remaining_balance, 2) }}
                            @if($d->total_amount)
                                <br><span class="eh-subtext">of ₱{{ number_format($d->total_amount, 2) }}</span>
                            @endif
                        @else
                            <span class="eh-na-note">N/A</span>
                        @endif
                    </td>
                    <td class="table-cell-date deduction-date-cell" data-start-date="{{ $row->startDate }}">
                        @if($row->hasStarted)
                            {{ $monthStart->format('M d') }} – {{ $monthEnd->format('d, Y') }}
                        @elseif($row->notStarted)
                            Not yet started
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @include('employee.dashboard.partials.deductionStatusBadge', ['status' => $d->status])
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="perm-empty">
                            <div class="perm-empty-icon">
                                <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            </div>
                            <p class="perm-empty-title">No Active Deductions</p>
                            <p class="perm-empty-sub">Your deductions and loans will appear here</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="mobile-card-list dashboard-deductions-list">
        @forelse($deductionRows as $row)
            @php $d = $row->model; @endphp
            <button type="button" class="mobile-data-card" onclick="showDeductionModal({{ $d->id }})" data-deduction-id="{{ $d->id }}">
                <span class="mobile-card-kicker">
                    <span class="badge-status" style="{{ $row->categoryStyle }}">{{ ucfirst($row->type->category ?? 'Other') }}</span>
                    @include('employee.dashboard.partials.deductionStatusBadge', ['status' => $d->status])
                </span>
                <span class="mobile-card-title">{{ $row->type->name ?? 'N/A' }}</span>
                @if($row->type->code)
                    <span class="mobile-card-sub">{{ $row->type->code }}</span>
                @endif
                <span class="mobile-card-metrics">
                    <span>
                        <small class="mobile-deduction-amount-label">Monthly Amount</small>
                        <strong class="deduction-amount-cell" data-per-cutoff="{{ $row->perCutoff }}">
                            @if($row->monthly > 0)
                                <span class="deduction-amount">&#8369;{{ number_format($row->monthly, 2) }}</span>
                                <em class="deduction-period">per month</em>
                            @elseif($row->isPercentagePending)
                                <span>{{ $row->type->percentage_rate }}% of salary</span>
                                <em>Pending computation</em>
                            @else
                                <span>To be computed</span>
                            @endif
                        </strong>
                    </span>
                    <span>
                        <small>Balance</small>
                        <strong>
                            @if($d->remaining_balance !== null)
                                &#8369;{{ number_format($d->remaining_balance, 2) }}
                            @else
                                N/A
                            @endif
                        </strong>
                    </span>
                </span>
                <span class="mobile-card-foot">
                    <span class="mobile-deduction-date-label">Current Month</span>
                    <strong class="deduction-date-cell" data-start-date="{{ $row->startDate }}">
                        @if($row->hasStarted)
                            {{ $monthStart->format('M d') }} – {{ $monthEnd->format('d, Y') }}
                        @elseif($row->notStarted)
                            Not yet started
                        @else
                            N/A
                        @endif
                    </strong>
                </span>
            </button>
        @empty
            <div class="mobile-empty-card">No active deductions or loans</div>
        @endforelse
    </div>

    <div class="table-footer">
        <span>Showing <strong>{{ $deductions->count() }}</strong> active deduction(s)</span>
    </div>
</div>
