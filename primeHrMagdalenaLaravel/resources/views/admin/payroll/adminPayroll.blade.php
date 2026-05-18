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

@if(session('success'))
<div style="margin-bottom: 20px; padding: 12px 16px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; color: #155724; font-size: 13px;">
    <strong>✓</strong> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="margin-bottom: 20px; padding: 12px 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; color: #721c24; font-size: 13px;">
    <strong>✗</strong> {{ session('error') }}
</div>
@endif

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
            <p class="stat-sub">All deductions included</p>
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

<div class="payroll-tabs">
    <a href="{{ route('admin.payroll', ['tab' => 'register'] + request()->except('tab')) }}" 
       class="tab-link {{ $activeTab === 'register' ? 'active' : '' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Payroll Register
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
    @elseif($activeTab === 'generate')
        @include('admin.payroll.partials.generate-payroll')
    @endif
</section>

<style>
.payroll-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    border-bottom: 2px solid #f0effe;
}

.tab-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    font-size: 13px;
    font-weight: 600;
    color: #6b6a8a;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}

.tab-link:hover {
    color: #0b044d;
    background: #f7f6ff;
}

.tab-link.active {
    color: #0b044d;
    border-bottom-color: #0b044d;
}

.tab-link svg {
    width: 16px;
    height: 16px;
}

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
