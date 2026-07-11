@extends('layouts.app')

@section('content')
@include('admin.topbar.deductionsTopbar')
@include('admin.notification.adminNotification')

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
@endphp

<div class="glass-shell">

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Deduction Types</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['total_types'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">{{ $stats['mandatory_count'] }} mandatory, {{ $stats['loan_count'] }} loans</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active Loans</p>
            <div class="stat-icon-wrap" style="background: #c9a22718; color: #c9a227;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['active_loans'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #c9a227;"></span>
            <p class="stat-sub">{{ $stats['active_loans'] > 0 ? 'Ongoing loans' : 'No active loans' }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Outstanding</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text>
                </svg>
            </div>
        </div>
        <h2 class="stat-value" style="font-size: 18px;">₱{{ number_format($stats['total_outstanding'], 2) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Loan balances</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Transactions</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['transactions_this_month'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">This month</p>
        </div>
    </div>
</div>

{{-- Filter Toolbar (contents swap per active tab) --}}
<div class="filter-card">
    <div class="filter-group" id="deduction-types-filter-group" style="display: contents;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <select class="fc-select" id="filterDeductionTypeCategory">
                    <option value="">All Categories</option>
                    <option value="MANDATORY">Mandatory</option>
                    <option value="LOAN">Loan</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select class="fc-select" id="filterDeductionTypeStatus">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button class="btn-ghost">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="employee-deductions-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchEmployee" class="fc-input" placeholder="Search employee..." onkeyup="filterEmployeeDeductions()">
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <select id="filterType" class="fc-select" onchange="filterEmployeeDeductions()">
                    <option value="">All Types</option>
                    <option value="MANDATORY">Mandatory</option>
                    <option value="LOAN">Loans</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select id="filterStatus" class="fc-select" onchange="filterEmployeeDeductions()">
                    <option value="">All Status</option>
                    <option value="ACTIVE">Active</option>
                    <option value="COMPLETED">Completed</option>
                    <option value="SUSPENDED">Suspended</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button class="btn-ghost" onclick="exportEmployeeDeductions()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="loans-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchLoan" class="fc-input" placeholder="Search employee..." onkeyup="filterLoans()">
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <select id="filterLoanType" class="fc-select" onchange="filterLoans()">
                    <option value="">All Loan Types</option>
                    @foreach(\App\Models\DeductionType::where('category', 'LOAN')->where('is_active', true)->orderBy('name')->get() as $loanType)
                        <option value="{{ $loanType->id }}">{{ $loanType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select id="filterLoanStatus" class="fc-select" onchange="filterLoans()">
                    <option value="">All Status</option>
                    <option value="ACTIVE">Active</option>
                    <option value="COMPLETED">Completed</option>
                    <option value="SUSPENDED">Suspended</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button class="btn-ghost" onclick="exportLoans()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="schedules-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchSchedule" class="fc-input" placeholder="Search employee..." onkeyup="filterSchedules()">
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                <select id="filterDepartment" class="fc-select" onchange="filterSchedules()">
                    <option value="">All Departments</option>
                    @foreach(\App\Models\Department::where('status', 'Active')->orderBy('name')->get() as $dept)
                        <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button class="btn-ghost" onclick="exportSchedules()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="filter-group" id="loan-types-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchLoanType" class="fc-input" placeholder="Search loan type..." onkeyup="filterLoanTypes()">
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <select id="filterLoanTypeProvider" class="fc-select" onchange="filterLoanTypes()">
                    <option value="">All Providers</option>
                    <option value="GSIS">GSIS</option>
                    <option value="PAG-IBIG">Pag-IBIG</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select id="filterLoanTypeStatus" class="fc-select" onchange="filterLoanTypes()">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions"></div>
    </div>

    <div class="filter-group" id="transactions-filter-group" style="display: none;">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" placeholder="From">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" placeholder="To">
            </div>
            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="fc-input" placeholder="Search employee...">
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <select class="fc-select">
                    <option value="">All Types</option>
                    <option value="GSIS">GSIS</option>
                    <option value="PHILHEALTH">PhilHealth</option>
                    <option value="PAGIBIG">Pag-IBIG</option>
                    <option value="WTAX">Withholding Tax</option>
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <select class="fc-select">
                    <option value="">All Cutoffs</option>
                    <option value="1ST">1st Cutoff</option>
                    <option value="2ND">2nd Cutoff</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button class="btn-ghost">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="seg-tabs">
    <button class="tab-btn active" onclick="switchTab('deduction-types')">Deduction Types</button>
    <button class="tab-btn" onclick="switchTab('employee-deductions')">Employee Deductions</button>
    <button class="tab-btn" onclick="switchTab('loans')">Loans</button>
    <button class="tab-btn" onclick="switchTab('schedules')">Schedules</button>
    <button class="tab-btn" onclick="switchTab('loan-types')">Loan Types</button>
    <button class="tab-btn" onclick="switchTab('transactions')">Transactions</button>
</div>

@include('admin.deductions.partials.deduction-types')

@include('admin.deductions.partials.employee-deductions')

@include('admin.deductions.partials.loans')

@include('admin.deductions.partials.schedules')

@include('admin.deductions.partials.loan-types')

@include('admin.deductions.partials.transactions')

</div>

@include('admin.deductions.modals.addDeductionTypeModal')
@include('admin.deductions.modals.editDeductionTypeModal')
@include('admin.deductions.modals.addLoanTypeModal')
@include('admin.deductions.modals.editLoanTypeModal')
@include('admin.deductions.modals.addLoanModal')
@include('admin.deductions.modals.assignDeductionScheduleModal')
@include('admin.deductions.modals.assignDeductionModal')
@include('admin.deductions.modals.editEmployeeDeductionModal')

<style>
.modal-overlay.active { display: flex !important; align-items: center; justify-content: center; }
</style>

@push('scripts')
<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');
        document.querySelectorAll('.filter-group').forEach(g => g.style.display = 'none');

        event.target.classList.add('active');
        document.getElementById(tabName + '-tab').style.display = 'block';
        const group = document.getElementById(tabName + '-filter-group');
        if (group) group.style.display = 'contents';
    }
</script>
@endpush
@endsection
