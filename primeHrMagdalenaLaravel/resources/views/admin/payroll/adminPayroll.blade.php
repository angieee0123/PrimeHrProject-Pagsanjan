@extends('layouts.app')

@section('content')
@php
function peso($amount) {
    return '₱' . number_format($amount, 2);
}

$startDateDisplay = request('start_date', now()->startOfMonth()->format('Y-m-d'));
$endDateDisplay = request('end_date', now()->endOfMonth()->format('Y-m-d'));
$periodDisplay = date('M d, Y', strtotime($startDateDisplay)) . ' — ' . date('M d, Y', strtotime($endDateDisplay));

$payrollRecords = $payrollRecords ?? [];
$viewMode = $viewMode ?? 'daily';
$activeTab = request('tab', 'register');

$totalBasicPay = $payrollRecords->sum('basic');
$totalOtPay = $payrollRecords->sum('ot_pay');
$totalLateDeduction = $payrollRecords->sum('late_deduction');
$totalUndertimeDeduction = $payrollRecords->sum('undertime_deduction');

// Calculate total deductions from all sources
$totalOtherDeductions = 0;
foreach ($payrollRecords as $record) {
    if (isset($record['deductions'])) {
        foreach ($record['deductions'] as $deductionAmount) {
            $totalOtherDeductions += $deductionAmount;
        }
    }
}

$totalDeductions = $totalLateDeduction + $totalUndertimeDeduction + $totalOtherDeductions;
$grossPayroll = $totalBasicPay + $totalOtPay;
$totalNet = $grossPayroll - $totalDeductions;
$processedCount = $payrollRecords->where('status', 'Processed')->count();
$pendingCount = $payrollRecords->where('status', 'Pending')->count();
@endphp

@include('admin.topbar.payrollTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

@if(session('success'))
<div class="pr-flash is-success">
    <strong>✓</strong> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="pr-flash is-error">
    <strong>✗</strong> {{ session('error') }}
</div>
@endif

<div class="stats-grid pr-mb-20">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Gross Payroll</p>
            <div class="stat-icon-wrap pr-icon-wrap-blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
            </div>
        </div>
        <h2 class="stat-value pr-stat-value-sm">{{ peso($grossPayroll) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot pr-dot-blue"></span>
            <p class="stat-sub">{{ $periodDisplay }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Net Pay</p>
            <div class="stat-icon-wrap pr-icon-wrap-green">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <h2 class="stat-value pr-stat-value-sm">{{ peso($totalNet) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot pr-dot-green"></span>
            <p class="stat-sub">After deductions</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Deductions</p>
            <div class="stat-icon-wrap pr-icon-wrap-red">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
        </div>
        <h2 class="stat-value pr-stat-value-sm">{{ peso($totalDeductions) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot pr-dot-red"></span>
            <p class="stat-sub">All deductions included</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Records</p>
            <div class="stat-icon-wrap pr-icon-wrap-gold">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingCount }}</h2>
        <div class="stat-footer">
            <span class="stat-dot pr-dot-gold"></span>
            <p class="stat-sub">{{ $processedCount }} processed</p>
        </div>
    </div>
</div>

@if($activeTab === 'register')
{{-- Filter Toolbar --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.payroll') }}" id="filterForm" class="pr-filter-contents">
        <input type="hidden" name="tab" value="register">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" title="Start date">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}" title="End date">
            </div>
            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <select class="fc-select" name="employee_name">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp }}" {{ request('employee_name') == $emp ? 'selected' : '' }}>{{ $emp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                <select class="fc-select" name="department">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <select class="fc-select" name="employment_status">
                    <option value="">All Employment Types</option>
                    @foreach($employmentStatuses as $empStatus)
                        <option value="{{ $empStatus }}" {{ request('employment_status') == $empStatus ? 'selected' : '' }}>{{ $empStatus }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select class="fc-select" name="status">
                    <option value="">All Status</option>
                    <option value="Processed" {{ request('status') == 'Processed' ? 'selected' : '' }}>Processed</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="On Hold" {{ request('status') == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <select class="fc-select" name="view_mode">
                    <option value="daily" {{ request('view_mode', 'daily') == 'daily' ? 'selected' : '' }}>Daily View</option>
                    <option value="employee" {{ request('view_mode') == 'employee' ? 'selected' : '' }}>By Employee</option>
                    <option value="monthly" {{ request('view_mode') == 'monthly' ? 'selected' : '' }}>Monthly Summary</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button type="submit" class="btn-solid">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
            <button type="button" class="btn-ghost">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </form>
</div>
@endif

<div class="payroll-tabs">
    <a href="{{ route('admin.payroll', ['tab' => 'register'] + request()->except('tab')) }}" 
       class="tab-link {{ $activeTab === 'register' ? 'active' : '' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Payroll Register
    </a>
    <a href="{{ route('admin.payroll', ['tab' => 'payslips'] + request()->except('tab')) }}" 
       class="tab-link {{ $activeTab === 'payslips' ? 'active' : '' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Payslip Management
    </a>
    <a href="{{ route('admin.payroll', ['tab' => 'generate'] + request()->except('tab')) }}" 
       class="tab-link {{ $activeTab === 'generate' ? 'active' : '' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Generate Payroll
    </a>
</div>

<section class="table-section">
    @if($activeTab === 'register')
        @include('admin.payroll.partials.payroll-register')
    @elseif($activeTab === 'payslips')
        @include('admin.payroll.partials.payslip-management')
    @elseif($activeTab === 'generate')
        @include('admin.payroll.partials.generate-payroll')
    @endif
</section>

</div>

@include('admin.payroll.modals.payroll-result-modal')
@include('admin.payroll.modals.payroll-status-modals')
@include('admin.payroll.modals.payslip-detail-modal')

@push('scripts')
<script>
    window.payrollRoutes = {
        preview: @json(route('admin.payroll.preview')),
        calculate: @json(route('admin.payroll.calculate')),
        export: @json(route('admin.payroll.export')),
        generate: @json(route('admin.payroll.generate')),
        payslipsTab: @json(route('admin.payroll', ['tab' => 'payslips'])),
        payslipDetails: @json(url('/admin/payroll/payslip')),
        payslipApprove: @json(url('/admin/payroll/payslip')),
        payslipsExport: @json(url('/admin/payroll/payslips/export')),
    };
</script>
    @vite('resources/js/admin/payroll/adminPayroll.js')
@endpush
@endsection
