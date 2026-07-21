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
@endphp

<div class="table-header">
    <div>
        <h3 class="table-title">Payroll Register — {{ $periodDisplay }}</h3>
        <p class="table-sub">Municipal Government of Pagsanjan · Pay Date: {{ date('M d, Y', strtotime($endDateDisplay)) }} · {{ $payrollRecords->count() }} records</p>
    </div>
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
    <table class="payroll-table payroll-register-table">
        <thead>
            <tr>
                <th class="pr-w-20">Employee</th>
                <th class="pr-w-13">Department</th>
                @if($viewMode === 'daily')
                    <th class="pr-w-10 pr-th-center">Date</th>
                    <th class="pr-w-8 pr-th-right">Rate</th>
                @else
                    <th class="pr-w-8 pr-th-center">Days</th>
                    <th class="pr-w-8 pr-th-right">Rate</th>
                @endif
                <th class="pr-w-9 pr-th-right">Basic</th>
                <th class="pr-w-7 pr-th-right">OT</th>
                <th class="pr-w-7 pr-th-right">Late</th>
                <th class="pr-w-7 pr-th-right">UT</th>
                @if(isset($deductionTypes) && $deductionTypes->isNotEmpty())
                    @foreach($deductionTypes as $code)
                        <th class="deduction-col-hide pr-w-7 pr-th-right">{{ $deductionTypeNames[$code] ?? $code }}</th>
                    @endforeach
                @endif
                <th class="deduction-col-show pr-hidden pr-w-5 pr-th-center">Ded.</th>
                <th class="pr-w-9 pr-th-right">Total Ded.</th>
                <th class="pr-w-10 pr-th-right">Net Pay</th>
            </tr>
        </thead>
        <tbody id="payrollRegisterBody">
            @foreach($payrollRecords as $index => $record)
            @php
                $basicPay = $record['basic'];
                $otPay = $record['ot_pay'];
                $lateDeduction = $record['late_deduction'];
                $undertimeDeduction = $record['undertime_deduction'];
                $grossPay = $basicPay + $otPay;
                
                // Calculate total deductions from all sources
                $totalDeductionsRow = $lateDeduction + $undertimeDeduction;
                if (isset($record['deductions'])) {
                    foreach ($record['deductions'] as $deductionAmount) {
                        $totalDeductionsRow += $deductionAmount;
                    }
                }
                
                $netPay = $grossPay - $totalDeductionsRow;
            @endphp
            <tr data-name="{{ $record['name'] }}" data-id="{{ $record['id'] }}" data-dept="{{ $record['dept'] }}" data-status="{{ $record['status'] }}">
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
                    <td class="daily-rate pr-th-right">{{ peso($record['daily_rate']) }}</td>
                @else
                    <td class="pr-th-center"><span class="days-count">{{ $record['days_count'] }}</span></td>
                    <td class="daily-rate pr-th-right">{{ peso($record['daily_rate']) }}</td>
                @endif
                <td class="pay-cell pr-th-right">{{ peso($basicPay) }}</td>
                <td class="ot-pay pr-th-right">{{ peso($otPay) }}</td>
                <td class="deduction pr-th-right">{{ peso($lateDeduction) }}</td>
                <td class="deduction pr-th-right">{{ peso($undertimeDeduction) }}</td>
                @if(isset($deductionTypes) && $deductionTypes->isNotEmpty())
                    @foreach($deductionTypes as $code)
                        <td class="deduction deduction-col-hide pr-th-right">{{ peso($record['deductions'][$code] ?? 0) }}</td>
                    @endforeach
                @endif
                <td class="deduction-col-show pr-hidden pr-th-center">
                    <button class="btn-deductions-modal" onclick="showDeductionsModal({{ $index }})">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                    </button>
                    <div class="deductions-data pr-hidden" data-index="{{ $index }}">
                        @if(isset($deductionTypes) && $deductionTypes->isNotEmpty())
                            @foreach($deductionTypes as $code)
                                <span data-type="{{ $deductionTypeNames[$code] ?? $code }}" data-amount="{{ peso($record['deductions'][$code] ?? 0) }}"></span>
                            @endforeach
                        @endif
                    </div>
                </td>
                <td class="pr-th-right">
                    <span class="badge-deduction">{{ peso($totalDeductionsRow) }}</span>
                </td>
                <td class="pr-th-right">
                    <span class="badge-netpay">{{ peso($netPay) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
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

<!-- Deductions Modal -->
<div id="deductionsModal" class="adm-overlay" onclick="closeDeductionsModal()">
    <div class="adm-box pr-modal-md" onclick="event.stopPropagation()">
        <div class="adm-header pr-header-red">
            <div class="adm-header-left">
                <div class="vdm-avatar pr-header-icon-frost">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </div>
                <div>
                    <span class="adm-eyebrow">PAYROLL DEDUCTIONS</span>
                    <h3 class="adm-title">Deduction Breakdown</h3>
                </div>
            </div>
            <button class="adm-close pr-close-frost" onclick="closeDeductionsModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="vdm-body pr-body-pad24" id="deductionsModalBody"></div>
        <div class="adm-footer pr-footer-tint">
            <button class="adm-btn-primary" onclick="closeDeductionsModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Got it
            </button>
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/payroll/payroll-register.js')
@endpush
