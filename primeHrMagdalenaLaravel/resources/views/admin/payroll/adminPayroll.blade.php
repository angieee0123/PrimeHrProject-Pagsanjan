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

@if($activeTab === 'register')
{{-- Filter Toolbar --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.payroll') }}" id="filterForm" style="display: contents;">
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

<style>
.payroll-tabs {
    display: inline-flex;
    gap: 4px;
    padding: 5px;
    background: #f0effe;
    border: 1px solid #e3e1f7;
    border-radius: 14px;
    margin-bottom: 20px;
}

.tab-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #6b6a8a;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.2s;
}

.tab-link:hover:not(.active) {
    color: #0b044d;
}

.tab-link.active {
    color: #fff;
    background: linear-gradient(135deg, #1a0f6e, #0b044d);
    box-shadow: 0 4px 12px rgba(11,4,77,.25);
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

<script>
// Payroll Register Pagination
window._payrollCurrentPage = 1;
window._payrollRowsPerPage = 10;

window.filterPayrollRegister = function () {
    const allRows = document.querySelectorAll('#payrollRegisterBody tr[data-id]');
    const filtered = [];
    
    allRows.forEach(row => {
        filtered.push(row);
    });
    
    window._payrollFilteredRows = filtered;
    window._payrollCurrentPage = 1;
    updatePayrollPagination();
};

window.updatePayrollPagination = function () {
    const rows = window._payrollFilteredRows || [];
    const total = rows.length;
    const perPage = window._payrollRowsPerPage;
    const totalPages = Math.ceil(total / perPage) || 1;
    const page = Math.min(window._payrollCurrentPage, totalPages);
    window._payrollCurrentPage = page;
    
    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, total);
    
    document.querySelectorAll('#payrollRegisterBody tr[data-id]').forEach(row => row.style.display = 'none');
    rows.forEach((row, i) => { if (i >= start && i < end) row.style.display = ''; });
    
    document.getElementById('payrollRowStart').textContent = total ? start + 1 : 0;
    document.getElementById('payrollRowEnd').textContent = end;
    document.getElementById('payrollRowTotal').textContent = total;
    
    const controls = document.getElementById('payrollPaginationControls');
    if (totalPages <= 1) { controls.innerHTML = ''; return; }
    
    let html = '';
    const maxVisible = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);
    
    if (page > 1) html += '<button class="page-btn" onclick="goToPayrollPage(' + (page - 1) + ')">‹</button>';
    if (startPage > 1) {
        html += '<button class="page-btn" onclick="goToPayrollPage(1)">1</button>';
        if (startPage > 2) html += '<span style="padding:0 8px;color:#9999bb;">...</span>';
    }
    for (let i = startPage; i <= endPage; i++) {
        html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToPayrollPage(' + i + ')">' + i + '</button>';
    }
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span style="padding:0 8px;color:#9999bb;">...</span>';
        html += '<button class="page-btn" onclick="goToPayrollPage(' + totalPages + ')">' + totalPages + '</button>';
    }
    if (page < totalPages) html += '<button class="page-btn" onclick="goToPayrollPage(' + (page + 1) + ')">›</button>';
    
    controls.innerHTML = html;
};

window.goToPayrollPage = function (page) {
    window._payrollCurrentPage = page;
    updatePayrollPagination();
};

window.changePayrollRowsPerPage = function () {
    window._payrollRowsPerPage = parseInt(document.getElementById('payrollRowsPerPage').value) || 10;
    window._payrollCurrentPage = 1;
    updatePayrollPagination();
};

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('payrollRegisterBody')) {
        filterPayrollRegister();
    }
});
</script>
@endsection
