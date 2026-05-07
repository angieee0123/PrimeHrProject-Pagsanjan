@extends('layouts.app')

@section('content')
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

function peso($amount) {
    return '₱' . number_format($amount, 2);
}

$startDateDisplay = request('start_date', now()->startOfMonth()->format('Y-m-d'));
$endDateDisplay = request('end_date', now()->endOfMonth()->format('Y-m-d'));
$periodDisplay = date('M d, Y', strtotime($startDateDisplay)) . ' — ' . date('M d, Y', strtotime($endDateDisplay));

$payrollRecords = $payrollRecords ?? [];
$viewMode = $viewMode ?? 'daily';

$grossPayroll = $payrollRecords->sum('basic');
$totalOtPay = $payrollRecords->sum('ot_pay');
$totalDeductions = 0;
$totalNet = 0;
foreach ($payrollRecords as $record) {
    $deductions = $record['late_deduction'] + $record['undertime_deduction'];
    $totalDeductions += $deductions;
    $totalNet += ($record['basic'] + $record['ot_pay'] - $deductions);
}
$processedCount = $payrollRecords->where('status', 'Processed')->count();
$pendingCount = $payrollRecords->where('status', 'Pending')->count();
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Gross Payroll</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
            </div>
        </div>
        <h2 class="stat-value" style="font-size: 18px;">{{ peso($grossPayroll) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">{{ $periodDisplay }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Net Pay</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <h2 class="stat-value" style="font-size: 18px;">{{ peso($totalNet) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">After deductions</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Deductions</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
        </div>
        <h2 class="stat-value" style="font-size: 18px;">{{ peso($totalDeductions) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Late & Undertime</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Records</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingCount }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">{{ $processedCount }} processed</p>
        </div>
    </div>
</div>

<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Payroll Register — {{ $periodDisplay }}</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Pay Date: {{ date('M d, Y', strtotime($endDateDisplay)) }} · {{ $payrollRecords->count() }} records</p>
        </div>
        <div class="table-actions">
            <form method="GET" action="{{ route('admin.payroll') }}" id="filterForm" style="display: contents;">
                <input type="date" class="filter-select" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                <span style="font-size: 12px; color: #9999bb;">to</span>
                <input type="date" class="filter-select" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
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
            <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Run Payroll
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
                    <th>Late Deduction</th>
                    <th>Undertime Deduction</th>
                    <th>Net Pay</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrollRecords as $index => $record)
                @php
                    $deductions = $record['late_deduction'] + $record['undertime_deduction'];
                    $net = $record['basic'] + $record['ot_pay'] - $deductions;
                @endphp
                <tr>
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
                    <td class="pay-cell">{{ peso($record['basic']) }}</td>
                    <td class="ot-pay">{{ peso($record['ot_pay']) }}</td>
                    <td class="deduction">{{ peso($record['late_deduction']) }}</td>
                    <td class="deduction">{{ peso($record['undertime_deduction']) }}</td>
                    <td class="net-pay">{{ peso($net) }}</td>
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
        <p>Showing <strong>{{ $payrollRecords->count() }}</strong> of <strong>{{ $payrollRecords->count() }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

<style>
.badge-emptype {
    font-size: 11px; color: #0b044d; background: #f0effe;
    padding: 3px 10px; border-radius: 20px; font-weight: 600;
    border: 1px solid #dddcf0;
}
.btn-edit {
    padding: 6px 16px; background: #f7f6ff; color: #0b044d;
    border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-edit:hover { background: #e8e7f5; }
.row-actions { display: flex; gap: 6px; }
.table-footer {
    padding: 16px 24px; border-top: 1px solid #f0effe;
    display: flex; justify-content: space-between; align-items: center;
}
.table-footer p { font-size: 13px; color: #6b6a8a; }
.pagination { display: flex; gap: 6px; }
.page-btn {
    width: 32px; height: 32px; border: 1px solid #e8e7f5;
    border-radius: 6px; background: #fff; color: #6b6a8a;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.page-btn.active { background: #0b044d; color: #fff; border-color: #0b044d; }
.page-btn:hover { background: #f7f6ff; }
.payroll-summary-bar {
    display: flex; align-items: center; gap: 20px;
    padding: 14px 24px; background: #fafafe;
    border: 1px solid #f0effe; border-radius: 8px;
}
.psummary-item { display: flex; flex-direction: column; gap: 2px; }
.psummary-item span { font-size: 11px; color: #9999bb; font-weight: 500; }
.psummary-item strong { font-size: 13px; color: #0b044d; font-weight: 600; }
.psummary-divider { width: 1px; height: 28px; background: #e8e7f5; }
.pay-cell {
    font-size: 13px; color: #0b044d; font-weight: 600;
}
.ot-pay {
    font-size: 13px; color: #15803d; font-weight: 600;
}
.deduction {
    font-size: 13px; color: #8e1e18; font-weight: 600;
}
.net-pay {
    font-size: 13px; color: #15803d; font-weight: 700;
}
.daily-rate {
    font-size: 13px; color: #5a0f0b; font-weight: 600;
}
.work-date {
    font-size: 12.5px; color: #6b6a8a; font-weight: 500;
}
.days-count {
    font-size: 12.5px; color: #0b044d; font-weight: 600;
    background: #f0effe; padding: 4px 10px; border-radius: 4px;
}
.btn-filter-main {
    padding: 7px 16px; background: #0b044d; color: #fff;
    border: none; border-radius: 6px; font-size: 12.5px;
    font-weight: 600; cursor: pointer; display: flex;
    align-items: center; gap: 6px; font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}
.btn-filter-main:hover { background: #1a0f6e; }
</style>
@endsection
