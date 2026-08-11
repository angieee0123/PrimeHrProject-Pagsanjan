@php
$avatarColors = ['#0b044d', '#8e1e18', '#150c63', '#a52820', '#150c63', '#56547a'];
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        if (preg_match('/^[A-Z]/', $part)) {
            $initials .= $part[0];
        }
    }
    return strtoupper(substr($initials, 0, 2));
}

// Get deduction type names
$deductionTypeNames = [];
if (isset($deductionTypes) && $deductionTypes->isNotEmpty()) {
    $deductionTypeModels = \App\Models\DeductionType::whereIn('code', $deductionTypes)->get();
    foreach ($deductionTypeModels as $dt) {
        $deductionTypeNames[$dt->code] = $dt->name;
    }
}

/*
    Column totals for the footer row.

    A payroll register without a totals line is not a register — the summary
    bar above carries gross / deductions / net, but nothing tied each column
    to a figure you could check a printout against. Computed here rather than
    inside the loop so the row cannot disagree with the cells above it.
*/
$colTotals = ['gross' => 0.0, 'deduction' => 0.0, 'net' => 0.0];

foreach ($payrollRecords as $r) {
    $rowDeductions = $r['late_deduction'] + $r['undertime_deduction'];
    foreach (($deductionTypes ?? []) as $code) {
        $rowDeductions += $r['deductions'][$code] ?? 0;
    }

    $gross = $r['basic'] + $r['ot_pay'];
    $colTotals['gross']     += $gross;
    $colTotals['deduction'] += $rowDeductions;
    $colTotals['net']       += $gross - $rowDeductions;
}
@endphp

<div class="table-header">
    <div>
        <h3 class="table-title">Payroll Register — {{ $periodDisplay }}</h3>
        <p class="table-sub">Municipal Government of Pagsanjan · Pay Date: {{ date('M d, Y', strtotime($endDateDisplay)) }} · {{ $payrollRecords->count() }} records</p>
    </div>
    @if($payrollRecords->count())
    <div class="table-actions">
        <button type="button" class="btn-export" data-role="expand-all" aria-expanded="false">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            <span data-label>Expand all</span>
        </button>
    </div>
    @endif
</div>

<div class="payroll-summary-bar pr-summary-bar-tight">
    <div class="psummary-item">
        <span>Gross Total</span>
        <strong>{{ peso($grossPayroll) }}</strong>
    </div>
    <div class="psummary-divider"></div>
    <div class="psummary-item">
        <span>Total Deductions</span>
        <strong class="deduction">{{ peso($totalDeductions) }}</strong>
    </div>
    <div class="psummary-divider"></div>
    <div class="psummary-item">
        <span>Total Net Pay</span>
        <strong class="net-pay">{{ peso($totalNet) }}</strong>
    </div>
    <div class="psummary-divider"></div>
    <div class="psummary-item">
        <span>Pay Date</span>
        <strong>{{ date('M d, Y', strtotime($endDateDisplay)) }}</strong>
    </div>
    <div class="psummary-divider"></div>
    <div class="psummary-item">
        <span>Records</span>
        <strong>{{ $payrollRecords->count() }}</strong>
    </div>
</div>

<div class="table-wrapper">
    {{--
        Seven columns, not twelve.

        The register used to carry every earning and every contribution side by
        side, which is why it needed a `deduction-col-hide` / `-show` swap at
        1920px and a modal to reach the figures that swap took away. That whole
        mechanism existed only because the table was too wide to fit.

        The row now answers what a register is usually opened for — who, when,
        gross, deductions, net — and the working behind those figures unfolds
        under the row that owns it. Nothing depends on viewport width any more.
    --}}
    <table class="payroll-table payroll-register-table is-expandable">
        <thead>
            <tr>
                <th class="pr-w-toggle"><span class="pr-sr-only">Expand</span></th>
                <th class="pr-w-24">Employee</th>
                <th class="pr-w-16">Department</th>
                @if($viewMode === 'daily')
                    <th class="pr-w-12 pr-th-center">Date</th>
                @else
                    <th class="pr-w-12 pr-th-center">Days</th>
                @endif
                <th class="pr-w-13 pr-th-right">Gross</th>
                <th class="pr-w-13 pr-th-right">Deductions</th>
                <th class="pr-w-14 pr-th-right">Net Pay</th>
            </tr>
        </thead>
        <tbody id="payrollRegisterBody">
            @forelse($payrollRecords as $index => $record)
            @php
                $basicPay = $record['basic'];
                $otPay = $record['ot_pay'];
                $lateDeduction = $record['late_deduction'];
                $undertimeDeduction = $record['undertime_deduction'];
                $grossPay = $basicPay + $otPay;

                $totalDeductionsRow = $lateDeduction + $undertimeDeduction;
                if (isset($record['deductions'])) {
                    foreach ($record['deductions'] as $deductionAmount) {
                        $totalDeductionsRow += $deductionAmount;
                    }
                }

                $netPay = $grossPay - $totalDeductionsRow;
                $rowKey = 'prrow' . $index;
            @endphp
            <tr data-name="{{ $record['name'] }}" data-id="{{ $record['id'] }}" data-dept="{{ $record['dept'] }}"
                data-status="{{ $record['status'] }}" data-row-key="{{ $rowKey }}" class="pr-row">
                <td class="pr-toggle-cell">
                    <button type="button" class="pr-toggle" data-toggle-row="{{ $rowKey }}"
                            aria-expanded="false" aria-controls="{{ $rowKey }}-detail"
                            aria-label="Show the breakdown for {{ $record['name'] }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </td>
                <td>
                    <div class="emp-cell">
                        @if($record['photo'] ?? false)
                            <img src="{{ $record['photo'] }}" alt="{{ $record['name'] }}" class="pr-avatar-img">
                        @else
                            <div class="emp-avatar pr-avatar-img" style="background: {{ $avatarColors[$index % count($avatarColors)] }}; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:12px;">
                                {{ getInitials($record['name']) }}
                            </div>
                        @endif
                        <div>
                            <p class="emp-name">{{ $record['name'] }}</p>
                            <p class="emp-id">{{ $record['id'] }}</p>
                        </div>
                    </div>
                </td>
                <td><span class="dept-tag">{{ $record['dept'] }}</span></td>
                @if($viewMode === 'daily')
                    <td class="work-date pr-th-center">{{ date('M d, Y', strtotime($record['work_date'])) }}</td>
                @else
                    <td class="pr-th-center"><span class="days-count">{{ $record['days_count'] }}</span></td>
                @endif
                <td class="pay-cell pr-th-right">{{ peso($grossPay) }}</td>
                <td class="pr-th-right">
                    <span class="badge-deduction {{ $totalDeductionsRow > 0 ? '' : 'is-nil' }}">{{ peso($totalDeductionsRow) }}</span>
                </td>
                <td class="pr-th-right">
                    {{-- A net below zero does not wear the success colour, and
                         the sign goes outside the peso sign so "−₱145.45" does
                         not read as "₱-145". --}}
                    <span class="badge-netpay {{ $netPay < 0 ? 'is-negative' : '' }}">{{ ($netPay < 0 ? '−' : '') . peso(abs($netPay)) }}</span>
                </td>
            </tr>

            {{-- The working behind the row above. Hidden until asked for, and
                 kept in the same <tbody> so pagination can move the pair
                 together. --}}
            <tr class="pr-detail-row" id="{{ $rowKey }}-detail" data-detail-for="{{ $rowKey }}" hidden>
                <td colspan="7" class="pr-detail-cell">
                    <div class="pr-detail">
                        <div class="pr-detail-block">
                            <p class="pr-detail-title is-earnings">Earnings</p>
                            <dl class="pr-detail-list">
                                <div><dt>Daily rate</dt><dd>{{ peso($record['daily_rate']) }}</dd></div>
                                @if($viewMode !== 'daily')
                                    <div><dt>Days worked</dt><dd>{{ $record['days_count'] }}</dd></div>
                                @endif
                                <div><dt>Basic pay</dt><dd>{{ peso($basicPay) }}</dd></div>
                                <div><dt>Overtime</dt><dd class="{{ $otPay > 0 ? 'is-positive' : 'is-nil' }}">{{ peso($otPay) }}</dd></div>
                                <div class="is-sum"><dt>Gross</dt><dd>{{ peso($grossPay) }}</dd></div>
                            </dl>
                        </div>

                        <div class="pr-detail-block">
                            <p class="pr-detail-title is-deductions">Deductions</p>
                            <dl class="pr-detail-list">
                                <div><dt>Late</dt><dd class="{{ $lateDeduction > 0 ? 'is-charge' : 'is-nil' }}">{{ peso($lateDeduction) }}</dd></div>
                                <div><dt>Undertime</dt><dd class="{{ $undertimeDeduction > 0 ? 'is-charge' : 'is-nil' }}">{{ peso($undertimeDeduction) }}</dd></div>
                                @foreach(($deductionTypes ?? []) as $code)
                                    @php $amount = $record['deductions'][$code] ?? 0; @endphp
                                    <div><dt>{{ $deductionTypeNames[$code] ?? $code }}</dt><dd class="{{ $amount > 0 ? 'is-charge' : 'is-nil' }}">{{ peso($amount) }}</dd></div>
                                @endforeach
                                <div class="is-sum"><dt>Total</dt><dd class="is-charge">{{ peso($totalDeductionsRow) }}</dd></div>
                            </dl>
                        </div>

                        <div class="pr-detail-net">
                            <span class="pr-detail-net-label">Net pay</span>
                            <strong class="pr-detail-net-value {{ $netPay < 0 ? 'is-negative' : '' }}">{{ ($netPay < 0 ? '−' : '') . peso(abs($netPay)) }}</strong>
                            <span class="pr-detail-net-sub">{{ peso($grossPay) }} earned, less {{ peso($totalDeductionsRow) }} deducted</span>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            {{-- The register is filter-driven, so an empty result is a normal
                 outcome, not a fault. --}}
            <tr>
                <td colspan="7" class="pr-empty-cell">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="pr-empty-icon"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <p class="pr-empty-title">No payroll records for this selection</p>
                    <p class="pr-empty-sub">Widen the date range or clear a filter, or use <strong>Generate Payroll</strong> to process this period.</p>
                </td>
            </tr>
            @endforelse
        </tbody>

        @if($payrollRecords->count())
        <tfoot class="pr-totals">
            <tr>
                <td colspan="4" class="pr-totals-label">
                    Totals
                    <span class="pr-totals-count">{{ $payrollRecords->count() }} {{ Str::plural('record', $payrollRecords->count()) }}</span>
                </td>
                <td class="pr-th-right">{{ peso($colTotals['gross']) }}</td>
                <td class="pr-th-right pr-totals-strong">{{ peso($colTotals['deduction']) }}</td>
                <td class="pr-th-right pr-totals-strong is-net">{{ ($colTotals['net'] < 0 ? '−' : '') . peso(abs($colTotals['net'])) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<div class="table-footer">
    <div class="pr-footer-flex">
        <p id="payrollRegisterFooter">Showing <strong id="payrollRowStart">1</strong>-<strong id="payrollRowEnd">{{ min(10, $payrollRecords->count()) }}</strong> of <strong id="payrollRowTotal">{{ $payrollRecords->count() }}</strong> records</p>
        <select id="payrollRowsPerPage" class="filter-select pr-rows-select" onchange="changePayrollRowsPerPage()">
            <option value="10">10 rows</option>
            <option value="25">25 rows</option>
            <option value="50">50 rows</option>
            <option value="100">100 rows</option>
        </select>
    </div>
    <div class="pagination" id="payrollPaginationControls"></div>
</div>

@push('scripts')
    @vite('resources/js/admin/payroll/payroll-register.js')
@endpush
