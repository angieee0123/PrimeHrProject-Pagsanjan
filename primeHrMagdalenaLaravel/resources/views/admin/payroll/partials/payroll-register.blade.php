@php
$avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
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
    <div class="table-actions">
        <form method="GET" action="{{ route('admin.payroll') }}" id="filterForm" style="display: contents;">
            <input type="hidden" name="tab" value="register">
            <input type="date" class="filter-select" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
            <span style="font-size: 12px; color: #9999bb;">to</span>
            <input type="date" class="filter-select" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
            <select class="filter-select" name="employee_name">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp }}" {{ request('employee_name') == $emp ? 'selected' : '' }}>{{ $emp }}</option>
                @endforeach
            </select>
            <select class="filter-select" name="department">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <select class="filter-select" name="status">
                <option value="">All Status</option>
                <option value="Processed" {{ request('status') == 'Processed' ? 'selected' : '' }}>Processed</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="On Hold" {{ request('status') == 'On Hold' ? 'selected' : '' }}>On Hold</option>
            </select>
            <select class="filter-select" name="view_mode" style="background: #f7f6ff; border-color: #0b044d; color: #0b044d; font-weight: 600;">
                <option value="daily" {{ request('view_mode', 'daily') == 'daily' ? 'selected' : '' }}>Daily View</option>
                <option value="employee" {{ request('view_mode') == 'employee' ? 'selected' : '' }}>By Employee</option>
                <option value="monthly" {{ request('view_mode') == 'monthly' ? 'selected' : '' }}>Monthly Summary</option>
            </select>
            <button type="submit" class="btn-filter-main">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
        </form>
        <button class="btn-export">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </button>
    </div>
</div>

<div class="payroll-summary-bar" style="margin-top: 0; margin-bottom: 16px;">
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
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                @if($viewMode === 'daily')
                    <th>Work Date</th>
                    <th>Daily Rate</th>
                @else
                    <th>Days Worked</th>
                    <th>Daily Rate</th>
                @endif
                <th>Basic Pay</th>
                <th>OT Pay</th>
                <th>Late</th>
                <th>Undertime</th>
                @if(isset($deductionTypes) && $deductionTypes->isNotEmpty())
                    @foreach($deductionTypes as $code)
                        <th>{{ $deductionTypeNames[$code] ?? $code }}</th>
                    @endforeach
                @endif
                <th>Total Deductions</th>
                <th>Net Pay</th>
                <th>Status</th>
                <th>Actions</th>
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
                        <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                            {{ getInitials($record['name']) }}
                        </div>
                        <div>
                            <p class="emp-name">{{ $record['name'] }}</p>
                            <p class="emp-id">{{ $record['id'] }}</p>
                        </div>
                    </div>
                </td>
                <td><span class="dept-tag">{{ $record['dept'] }}</span></td>
                @if($viewMode === 'daily')
                    <td class="work-date">{{ date('M d, Y', strtotime($record['work_date'])) }}</td>
                    <td class="daily-rate">{{ peso($record['daily_rate']) }}</td>
                @else
                    <td class="days-count">{{ $record['days_count'] }} days</td>
                    <td class="daily-rate">{{ peso($record['daily_rate']) }}</td>
                @endif
                <td class="pay-cell">{{ peso($basicPay) }}</td>
                <td class="ot-pay">{{ peso($otPay) }}</td>
                <td class="deduction">{{ peso($lateDeduction) }}</td>
                <td class="deduction">{{ peso($undertimeDeduction) }}</td>
                @if(isset($deductionTypes) && $deductionTypes->isNotEmpty())
                    @foreach($deductionTypes as $code)
                        <td class="deduction">{{ peso($record['deductions'][$code] ?? 0) }}</td>
                    @endforeach
                @endif
                <td class="deduction" style="font-weight: 700;">{{ peso($totalDeductionsRow) }}</td>
                <td class="net-pay">{{ peso($netPay) }}</td>
                <td><span class="badge-status {{ $record['status'] === 'Processed' ? 'processed' : ($record['status'] === 'Pending' ? 'pending' : 'on-hold') }}">{{ $record['status'] }}</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view">Payslip</button>
                        <button class="btn-edit">Edit</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="table-footer">
    <div style="display:flex;align-items:center;gap:12px;">
        <p id="payrollRegisterFooter">Showing <strong id="payrollRowStart">1</strong>-<strong id="payrollRowEnd">{{ min(10, $payrollRecords->count()) }}</strong> of <strong id="payrollRowTotal">{{ $payrollRecords->count() }}</strong> records</p>
        <select id="payrollRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changePayrollRowsPerPage()">
            <option value="10">10 rows</option>
            <option value="25">25 rows</option>
            <option value="50">50 rows</option>
            <option value="100">100 rows</option>
        </select>
    </div>
    <div class="pagination" id="payrollPaginationControls"></div>
</div>
