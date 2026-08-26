@extends('layouts.app')

@push('styles')
    @vite('resources/css/admin/adminLeaveAndBenefits.css')
@endpush

@section('content')
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

$totalApproved = $leaveApplications->where('status', 'approved')->count();
$totalPending = $leaveApplications->where('status', 'pending')->count();
$totalDays = $leaveApplications->where('status', 'approved')->sum('number_of_days');
@endphp

@include('admin.topbar.leaveandbenefitsTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

<div class="stats-grid stats-grid-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Requests</p>
            <div class="stat-icon-wrap" style="background: var(--gp-bg-tint-2);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $leaveApplications->count() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: var(--gp-pri);"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap" style="background: var(--theme-success-subtle);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalApproved }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: var(--theme-success);"></span>
            <p class="stat-sub">This period</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Approval</p>
            <div class="stat-icon-wrap" style="background: var(--theme-warning-subtle);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPending }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #c9a227;"></span>
            <p class="stat-sub">Needs action</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Days</p>
            <div class="stat-icon-wrap" style="background: #fdf0ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ number_format($totalDays, 0) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: var(--theme-danger);"></span>
            <p class="stat-sub">Across all employees</p>
        </div>
    </div>
</div>

{{-- Filter Toolbar (contents swap per active tab) --}}
<div class="filter-card" id="leaveFilterCard">
    <div class="filter-group" id="leave-filter-group" style="display: contents;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" id="filterLeaveDateFrom" title="Leave date from" onchange="applyAdminLeaveFilters()">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" id="filterLeaveDateTo" title="Leave date to" onchange="applyAdminLeaveFilters()">
            </div>
            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                <select class="fc-select" id="filterDepartment" onchange="applyAdminLeaveFilters()">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <select class="fc-select" id="filterLeaveType" onchange="applyAdminLeaveFilters()">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->leave_name }}">{{ $type->leave_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select class="fc-select" id="filterLeaveStatus" onchange="applyAdminLeaveFilters()">
                    <option value="">All Status</option>
                    <option value="Approved">Approved</option>
                    <option value="Pending">Pending</option>
                    {{-- The column stores "rejected"; only the label says
                         "Disapproved". Filtering on the label matched no row
                         at all, and the export would have inherited that. --}}
                    <option value="Rejected">Disapproved</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button type="button" class="btn-ghost"
                    onclick="exportLeaveTab('leave')"
                    data-export-tab="leave"
                    data-export-url="{{ route('admin.leave.export.requests') }}"
                    title="Download the leave requests as a CSV file">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="transactions-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" id="filterTransactionDateFrom" value="{{ request('filter_transaction_date_from') }}" title="From date">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" id="filterTransactionDateTo" value="{{ request('filter_transaction_date_to') }}" title="To date">
            </div>
            <button type="button" class="btn-ghost" onclick="applyTransactionDateRangeFilter()">Apply</button>
            @if(request('filter_transaction_date_from') || request('filter_transaction_date_to'))
                <button type="button" class="btn-ghost" onclick="clearTransactionDateRangeFilter()">Clear</button>
            @endif
            <div class="fc-divider"></div>
            <div class="fld">
                <select class="fc-select" id="filterTransactionYear" onchange="applyTransactionYearFilter()" {{ request('filter_transaction_date_from') || request('filter_transaction_date_to') ? 'disabled' : '' }}>
                    <option value="">Most Recent</option>
                    @foreach($transactionYears ?? [] as $year)
                        <option value="{{ $year }}" {{ request('filter_transaction_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <select class="fc-select" id="filterTransactionEmployee" onchange="applyTransactionFilters()">
                    <option value="">All Employees</option>
                    @foreach($transactionEmployees ?? [] as $emp)
                        <option value="{{ $emp->id }}" {{ request('filter_employee') == $emp->id ? 'selected' : '' }}>{{ $emp->employee_id }} - {{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <select class="fc-select" id="filterTransactionType" onchange="applyTransactionFilters()">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('filter_type') == 'credit' ? 'selected' : '' }}>Credit (Added)</option>
                    <option value="debit" {{ request('filter_type') == 'debit' ? 'selected' : '' }}>Debit (Used)</option>
                    <option value="pending" {{ request('filter_type') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reversal" {{ request('filter_type') == 'reversal' ? 'selected' : '' }}>Reversal</option>
                    <option value="adjustment" {{ request('filter_type') == 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                </select>
            </div>
            <div class="fld">
                <select class="fc-select" id="filterTransactionLeaveType" onchange="applyTransactionFilters()">
                    <option value="">All Leave Types</option>
                    @foreach($allLeaveTypes ?? [] as $type)
                        <option value="{{ $type->leave_code }}" {{ request('filter_leave_code') == $type->leave_code ? 'selected' : '' }}>{{ $type->leave_code }} - {{ $type->leave_name }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('filter_transaction_date_from') || request('filter_transaction_date_to') || request('filter_transaction_year') || request('filter_employee') || request('filter_type') || request('filter_leave_code'))
                <button type="button" class="btn-ghost" onclick="clearAllTransactionFilters()">Clear All</button>
            @endif
        </div>
        <div class="filter-card-actions">
            <button type="button" class="btn-ghost"
                    onclick="exportLeaveTab('transactions')"
                    data-export-tab="transactions"
                    data-export-url="{{ route('admin.leave.export.transactions') }}"
                    title="Download the transaction history as a CSV file">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="leave-credits-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" id="filterCreditsDateFrom" value="{{ request('filter_credits_date_from') }}" title="From date">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" id="filterCreditsDateTo" value="{{ request('filter_credits_date_to') }}" title="To date">
            </div>
            <button type="button" class="btn-ghost" onclick="applyDateRangeFilter()">Apply</button>
            @if(request('filter_credits_date_from') || request('filter_credits_date_to'))
                <button type="button" class="btn-ghost" onclick="clearDateRangeFilter()">Clear</button>
            @endif
            <div class="fc-divider"></div>
            <div class="fld">
                <select class="fc-select" id="filterCreditsYear" onchange="applyYearFilter()" {{ request('filter_credits_date_from') || request('filter_credits_date_to') ? 'disabled' : '' }}>
                    <option value="">Most Recent</option>
                    @foreach($availableYears ?? [] as $year)
                        <option value="{{ $year }}" {{ request('filter_credits_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <select class="fc-select" id="filterCreditsEmployee" onchange="applyCreditsFilters()">
                    <option value="">All Employees</option>
                    @foreach($employees ?? [] as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_id }} - {{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <select class="fc-select" id="filterCreditsLeaveType" onchange="applyCreditsFilters()">
                    <option value="">All Leave Types</option>
                    @foreach($allLeaveTypes ?? [] as $type)
                        <option value="{{ $type->leave_code }}">{{ $type->leave_code }} - {{ $type->leave_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <select class="fc-select" id="filterCreditsType" onchange="applyCreditsFilters()">
                    <option value="">All Types</option>
                    <option value="accrued">Accrued</option>
                    <option value="fixed">Fixed</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button type="button" class="btn-ghost"
                    onclick="exportLeaveTab('leave-credits')"
                    data-export-tab="leave-credits"
                    data-export-url="{{ route('admin.leave.export.credits') }}"
                    title="Download the leave credits as a CSV file">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="benefits-filter-group" style="display: none;">
        <div class="filter-card-fields"></div>
        <div class="filter-card-actions">
            <button type="button" class="btn-ghost"
                    onclick="exportLeaveTab('benefits')"
                    data-export-tab="benefits"
                    data-export-url="{{ route('admin.leave.export.benefits') }}"
                    title="Download the benefits summary as a CSV file">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="types-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select class="fc-select" id="filterLeaveTypeStatus" onchange="filterLeaveTypes()">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <select class="fc-select" id="filterLeaveTypeAccrual" onchange="filterLeaveTypes()">
                    <option value="all">All Types</option>
                    <option value="accrued">Accrued</option>
                    <option value="fixed">Fixed</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button type="button" class="btn-ghost"
                    onclick="exportLeaveTab('types')"
                    data-export-tab="types"
                    data-export-url="{{ route('admin.leave.export.types') }}"
                    title="Download the leave types as a CSV file">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="accrual-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select class="fc-select" id="filterAccrualStatus" onchange="filterAccrualRates()">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <select class="fc-select" id="filterAccrualFrequency" onchange="filterAccrualRates()">
                    <option value="all">All Frequencies</option>
                    <option value="daily">Daily</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button type="button" class="btn-ghost"
                    onclick="exportLeaveTab('accrual')"
                    data-export-tab="accrual"
                    data-export-url="{{ route('admin.leave.export.accrual') }}"
                    title="Download the CSC daily accrual rates as a CSV file">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="seg-tabs">
    <button class="tab-btn active" onclick="switchTab('leave')">Leave Requests</button>
    <button class="tab-btn" onclick="switchTab('transactions')">Transaction History</button>
    <button class="tab-btn" onclick="switchTab('leave-credits')">Leave Credits</button>
    <button class="tab-btn" onclick="switchTab('benefits')">Benefits Summary</button>
    <button class="tab-btn" onclick="switchTab('types')">Leave Types</button>
    <button class="tab-btn" onclick="switchTab('accrual')">CSC Daily Accrual</button>
    <button class="tab-btn" onclick="switchTab('import')">Import Records</button>
</div>

@include('admin.leaveAndBenefits.partials.leave-requests-tab')

@include('admin.leaveAndBenefits.partials.transaction-history-tab')

@include('admin.leaveAndBenefits.partials.leave-credits-tab')

@include('admin.leaveAndBenefits.partials.benefits-summary-tab')

@include('admin.leaveAndBenefits.partials.leave-types-tab')

@include('admin.leaveAndBenefits.partials.csc-daily-accrual-tab')

@include('admin.leaveAndBenefits.partials.migrate-leave-records-tab')

</div>

@include('admin.leaveAndBenefits.modals.add-leave-type-modal')

@include('admin.leaveAndBenefits.modals.view-leave-type-modal')

@include('admin.leaveAndBenefits.modals.add-accrual-rate-modal')

@include('admin.leaveAndBenefits.modals.add-manual-credit-modal')

@include('admin.leaveAndBenefits.modals.success-modal')

@include('admin.leaveAndBenefits.modals.error-modal')

@include('admin.leaveAndBenefits.modals.migrate-leave-records-modal')

@push('scripts')
    @vite('resources/js/admin/leaveAndBenefits/adminLeaveAndBenefits.js')
@endpush
@endsection
