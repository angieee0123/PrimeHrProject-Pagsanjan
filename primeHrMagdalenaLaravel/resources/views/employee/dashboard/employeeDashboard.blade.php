@extends('layouts.employee')

@section('title', 'Dashboard · PRIME HRIS')

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Dashboard'
    ])

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    <main class="main-content permanent-dashboard glass-shell">

        @include('employee.notification.employeeNotification')
        @include('employee.topbar.employeeTopbar')

        <div class="perm-dash">

            {{-- Header --}}

            {{-- Stats Grid --}}
            <div class="stats-grid stats-grid-4 perm-stats">

                <div class="stat-card perm-stat-hover" onclick="window.location.href='{{ route('employee.payslip') }}'">
                    <div class="stat-top">
                        <p class="stat-label">Basic Pay</p>
                        <div class="stat-icon-wrap">
                            <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        </div>
                    </div>
                    <p class="stat-value">₱{{ number_format($basicPay, 2) }}</p>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#0b044d"></span>
                        <p class="stat-sub">{{ $startDate->format('M d') }}–{{ $endDate->format('d, Y') }}</p>
                    </div>
                </div>

                <div class="stat-card perm-stat-hover" onclick="window.location.href='{{ route('employee.payslip') }}'">
                    <div class="stat-top">
                        <p class="stat-label">Net Pay</p>
                        <div class="stat-icon-wrap">
                            <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                    </div>
                    <p class="stat-value">₱{{ number_format($netPay, 2) }}</p>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#22c55e"></span>
                        <p class="stat-sub">₱{{ number_format($totalDeductions, 2) }} deducted</p>
                    </div>
                </div>

                @if($isPermanent ?? false)
                <div class="stat-card perm-stat-hover" onclick="window.location.href='{{ route('employee.leave') }}'">
                    <div class="stat-top">
                        <p class="stat-label" style="display:flex;align-items:center;gap:6px">
                            Leave Credits
                            @if($pendingLeaveCount > 0)
                                <span class="stat-pill-alert">{{ $pendingLeaveCount }}</span>
                            @endif
                        </p>
                        <div class="stat-icon-wrap">
                            <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                    </div>
                    <p class="stat-value">{{ number_format($leaveBalances->sum('available_credits'), 1) }}<span style="font-size:14px;font-weight:500"> days</span></p>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#d9bb00"></span>
                        <p class="stat-sub">
                            @if($pendingLeaveCount > 0)
                                {{ $pendingLeaveCount }} request{{ $pendingLeaveCount != 1 ? 's' : '' }} pending
                            @else
                                {{ $approvedLeaveCount }} approved this year
                            @endif
                        </p>
                    </div>
                </div>
                @endif

                <div class="stat-card perm-stat-hover" onclick="window.location.href='{{ route('employee.attendance') }}'">
                    <div class="stat-top">
                        <p class="stat-label">Attendance Rate</p>
                        <div class="stat-icon-wrap">
                            <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                    </div>
                    <p class="stat-value">{{ $attendanceRate }}<span style="font-size:14px;font-weight:500">%</span></p>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:{{ $lateDays > 0 ? '#b7791f' : '#15803d' }}"></span>
                        <p class="stat-sub">
                            {{ $presentDays }} of {{ $totalDays }} days
                            @if($lateDays > 0) · {{ $lateDays }} late @endif
                        </p>
                    </div>
                </div>

            </div>

            {{-- Quick Actions Bar --}}
            <div class="perm-action-bar">
                <div style="display:flex;align-items:center;gap:10px" class="perm-action-spacer">
                    <div style="width:34px;height:34px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;color:#0b044d;flex-shrink:0">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:800;color:#111827;margin:0">Quick Actions</p>
                        <p style="font-size:11px;color:#667085;margin:0">Frequently used HR workflows</p>
                    </div>
                </div>
                <button class="modal-btn-primary perm-quick-btn" onclick="window.location.href='{{ route('employee.payslip') }}'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    View Payslip
                </button>
                @if($isPermanent ?? false)
                <button class="btn-export perm-quick-btn" onclick="window.location.href='{{ route('employee.leave') }}'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    File Leave
                </button>
                @endif
                <button class="btn-export perm-quick-btn" onclick="window.location.href='{{ route('employee.attendance') }}'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                    Attendance
                </button>
                <button class="btn-export perm-quick-btn" onclick="window.location.href='{{ route('employee.passslip') }}'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Pass Slip
                </button>
                <button class="btn-export perm-quick-btn" onclick="window.location.href='{{ route('employee.profile') }}'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    My Profile
                </button>
            </div>

            {{-- Overview: wide attendance chart + my requests --}}
            <div class="perm-overview-grid">

                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <p class="chart-title">Attendance Trends</p>
                            <p class="chart-sub">Your attendance, late arrivals and absences</p>
                        </div>
                        <div class="chart-tabs">
                            <button class="chart-tab" onclick="switchAttendanceChart('week')">Week</button>
                            <button class="chart-tab active" onclick="switchAttendanceChart('month')">Month</button>
                            <button class="chart-tab" onclick="switchAttendanceChart('year')">Year</button>
                        </div>
                    </div>
                    <canvas id="attendanceChart" style="max-height:260px;padding:0 16px 16px"></canvas>
                </div>

                <div class="table-section perm-section" style="margin:0">
                    <div class="table-header">
                        <div>
                            <p class="table-title" style="display:flex;align-items:center;gap:8px">
                                My Requests
                                @if($pendingLeaveCount > 0)
                                    <span class="stat-pill-alert">{{ $pendingLeaveCount }}</span>
                                @endif
                            </p>
                            <p class="table-sub">Latest leave applications</p>
                        </div>
                        <button class="btn-export" style="font-size:11px;padding:6px 12px" onclick="window.location.href='{{ route('employee.leave') }}'">View all</button>
                    </div>
                    <div class="perm-card-body">
                        @forelse($leaveRequests as $req)
                            @php
                                $statusStyles = [
                                    'approved'  => ['bg' => '#e8f9ef', 'fg' => '#15803d', 'dot' => '#15803d'],
                                    'pending'   => ['bg' => '#fefce8', 'fg' => '#a16207', 'dot' => '#c9a227'],
                                    'rejected'  => ['bg' => '#fdedec', 'fg' => '#8e1e18', 'dot' => '#8e1e18'],
                                    'cancelled' => ['bg' => '#f2f4f7', 'fg' => '#667085', 'dot' => '#98a2b3'],
                                ];
                                $s = $statusStyles[$req->status] ?? $statusStyles['cancelled'];
                            @endphp
                            <div class="perm-req-row">
                                <span class="perm-req-dot" style="background:{{ $s['dot'] }}"></span>
                                <div class="perm-req-info">
                                    <p class="perm-req-title">{{ $req->leaveType->leave_name ?? $req->leave_code }}</p>
                                    <p class="perm-req-sub">
                                        {{ \Carbon\Carbon::parse($req->start_date)->format('M d') }}–{{ \Carbon\Carbon::parse($req->end_date)->format('M d, Y') }}
                                        · {{ number_format($req->number_of_days, 1) }} day{{ $req->number_of_days != 1 ? 's' : '' }}
                                    </p>
                                </div>
                                <span class="badge-status" style="background:{{ $s['bg'] }};color:{{ $s['fg'] }};text-transform:none">{{ ucfirst($req->status) }}</span>
                            </div>
                        @empty
                            <div class="perm-empty">
                                <div class="perm-empty-icon">
                                    <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                </div>
                                <p class="perm-empty-title">No Requests Yet</p>
                                <p class="perm-empty-sub">Your leave applications will appear here</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Salary + Leave balance --}}
            <div class="perm-secondary-grid" @if(!($isPermanent ?? false)) style="grid-template-columns:1fr" @endif>

                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <p class="chart-title">Salary Overview</p>
                            <p class="chart-sub">Your earnings over time</p>
                        </div>
                        <div class="chart-tabs">
                            <button class="chart-tab" onclick="switchSalaryChart('week')">Week</button>
                            <button class="chart-tab active" onclick="switchSalaryChart('month')">Month</button>
                            <button class="chart-tab" onclick="switchSalaryChart('year')">Year</button>
                        </div>
                    </div>
                    <canvas id="salaryChart" style="max-height:260px;padding:0 16px 16px"></canvas>
                </div>

                @if($isPermanent ?? false)
                <div class="table-section perm-section" style="margin:0">
                    <div class="table-header">
                        <div>
                            <p class="table-title">Leave Balance</p>
                            <p class="table-sub">Available credits by type</p>
                        </div>
                    </div>
                    <div class="perm-card-body">
                        @forelse($leaveBalances as $balance)
                        @php
                            $percent = $balance->total_credits > 0 ? ($balance->available_credits / $balance->total_credits) * 100 : 0;
                            $colors = ['#0b044d','#8e1e18','#a16207','#15803d','#0e7490'];
                            $color  = $colors[$loop->index % count($colors)];
                        @endphp
                        <div class="perm-balance-row">
                            <div class="perm-balance-top">
                                <div style="display:flex;align-items:center;gap:8px;min-width:0">
                                    <span class="perm-req-dot" style="background:{{ $color }}"></span>
                                    <span class="perm-balance-name">{{ $balance->leaveType->leave_name ?? 'Unknown' }}</span>
                                </div>
                                <span class="perm-balance-val" style="color:{{ $color }}">{{ number_format($balance->available_credits, 1) }} <em>/ {{ number_format($balance->total_credits, 1) }} days</em></span>
                            </div>
                            <div class="perm-balance-track">
                                <div class="perm-balance-fill" style="width:{{ $percent }}%;background:{{ $color }}"></div>
                            </div>
                        </div>
                        @empty
                        <div class="perm-empty">
                            <div class="perm-empty-icon">
                                <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <p class="perm-empty-title">No Leave Balances</p>
                            <p class="perm-empty-sub">Leave balances will appear here</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endif

            </div>

            {{-- Recent Payslips --}}
            <div class="table-section perm-section">
                <div class="table-header">
                    <div>
                        <p class="table-title">Recent Payslips</p>
                        <p class="table-sub">Your last {{ $payslips->count() }} pay periods</p>
                    </div>
                    <div class="table-actions">
                        <button class="modal-btn-primary" onclick="window.location.href='{{ route('employee.payslip') }}'">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            View All Payslips
                        </button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="payroll-table payslip-history-table">
                        <thead>
                            <tr>
                                <th>Pay Period</th>
                                <th>Basic Pay</th>
                                <th>Deductions</th>
                                <th>Net Pay</th>
                                <th>Pay Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payslips as $p)
                            <tr>
                                <td class="table-cell-period"><strong>{{ $p->period_label }}</strong></td>
                                <td class="table-cell-basic">₱{{ number_format($p->basic_pay, 2) }}</td>
                                <td class="table-cell-deduct">
                                    @if($p->deductions > 0)
                                        −₱{{ number_format($p->deductions, 2) }}
                                        <br><span style="font-size:11px;color:#9999bb">late &amp; undertime</span>
                                    @else
                                        <span style="color:#9999bb">₱0.00</span>
                                    @endif
                                </td>
                                <td style="font-weight:700;color:#15803d">₱{{ number_format($p->net_pay, 2) }}</td>
                                <td class="table-cell-date">{{ \Carbon\Carbon::parse($p->pay_date)->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="perm-empty">
                                        <div class="perm-empty-icon">
                                            <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                        </div>
                                        <p class="perm-empty-title">No Payslips Yet</p>
                                        <p class="perm-empty-sub">Your pay periods will appear here once payroll is processed</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

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
                            @forelse($deductions as $d)
                            <tr onclick="showDeductionModal({{ $d->id }})" style="cursor:pointer" data-deduction-id="{{ $d->id }}">
                                <td class="table-cell-period">
                                    <strong>{{ $d->deductionType->name ?? 'N/A' }}</strong>
                                    @if($d->deductionType->code)
                                        <br><span style="font-size:11px;color:#9999bb">{{ $d->deductionType->code }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $categoryColors = [
                                            'mandatory' => 'background:#e8f9ef;color:#15803d',
                                            'loan'      => 'background:#fefce8;color:#a16207',
                                            'voluntary' => 'background:#f0effe;color:#0b044d',
                                        ];
                                        $cStyle = $categoryColors[$d->deductionType->category] ?? 'background:#f7f6ff;color:#6b6a8a';
                                    @endphp
                                    <span class="badge-status" style="{{ $cStyle }}">{{ ucfirst($d->deductionType->category ?? 'Other') }}</span>
                                </td>
                                <td class="table-cell-basic deduction-amount-cell" data-per-cutoff="{{ $d->calculated_amount ?? ($d->installment_amount ?? $d->amount ?? ($d->deductionType && strtoupper($d->deductionType->computation_type) === 'FIXED' ? $d->deductionType->percentage_rate / 2 : 0)) }}">
                                    @php
                                        $perCutoff = $d->calculated_amount ?? ($d->installment_amount ?? $d->amount ?? 0);
                                        if ($perCutoff == 0 && $d->deductionType && strtoupper($d->deductionType->computation_type) === 'FIXED') {
                                            $perCutoff = ($d->deductionType->percentage_rate ?? 0) / 2;
                                        }
                                        $monthly = $perCutoff * 2;
                                    @endphp
                                    @if($monthly > 0)
                                        <span class="deduction-amount">₱{{ number_format($monthly, 2) }}</span>
                                        <br><span style="font-size:11px;color:#9999bb" class="deduction-period">per month</span>
                                    @elseif($d->deductionType && strtoupper($d->deductionType->computation_type) === 'PERCENTAGE' && $d->deductionType->percentage_rate > 0)
                                        <span style="color:#a16207;font-size:12px">{{ $d->deductionType->percentage_rate }}% of salary</span>
                                        <br><span style="font-size:11px;color:#9999bb">Pending computation</span>
                                    @else
                                        <span style="color:#9999bb;font-size:12px">To be computed</span>
                                    @endif
                                </td>
                                <td class="table-cell-deduct">
                                    @if($d->remaining_balance !== null)
                                        ₱{{ number_format($d->remaining_balance, 2) }}
                                        @if($d->total_amount)
                                            <br><span style="font-size:11px;color:#9999bb">of ₱{{ number_format($d->total_amount, 2) }}</span>
                                        @endif
                                    @else
                                        <span style="color:#9999bb">N/A</span>
                                    @endif
                                </td>
                                <td class="table-cell-date deduction-date-cell" data-start-date="{{ $d->start_date ? $d->start_date->format('Y-m-d') : '' }}">
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $monthStart = $now->copy()->startOfMonth();
                                        $monthEnd = $now->copy()->endOfMonth();
                                    @endphp
                                    @if($d->start_date && $d->start_date <= $now)
                                        {{ $monthStart->format('M d') }} – {{ $monthEnd->format('d, Y') }}
                                    @elseif($d->start_date && $d->start_date > $now)
                                        Not yet started
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($d->status === 'active')
                                        <span class="badge-status" style="background:#e8f9ef;color:#15803d;border:1px solid #bbf7d0;text-transform:none">Active</span>
                                    @elseif($d->status === 'pending')
                                        <span class="badge-status pending">Pending</span>
                                    @else
                                        <span class="badge-status on-hold">{{ ucfirst($d->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div style="text-align:center;padding:40px 24px">
                                        <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                                            <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                        </div>
                                        <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Active Deductions</p>
                                        <p style="font-size:12px;color:#94a3b8;margin:0">Your deductions and loans will appear here</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="mobile-card-list dashboard-deductions-list">
                    @forelse($deductions as $d)
                        @php
                            $categoryColors = [
                                'mandatory' => 'background:#e8f9ef;color:#15803d',
                                'loan'      => 'background:#fefce8;color:#a16207',
                                'voluntary' => 'background:#f0effe;color:#0b044d',
                            ];
                            $cStyle = $categoryColors[$d->deductionType->category] ?? 'background:#f7f6ff;color:#6b6a8a';
                            $perCutoff = $d->calculated_amount ?? ($d->installment_amount ?? $d->amount ?? 0);
                            if ($perCutoff == 0 && $d->deductionType && strtoupper($d->deductionType->computation_type) === 'FIXED') {
                                $perCutoff = ($d->deductionType->percentage_rate ?? 0) / 2;
                            }
                            $monthly = $perCutoff * 2;
                            $now = \Carbon\Carbon::now();
                            $monthStart = $now->copy()->startOfMonth();
                            $monthEnd = $now->copy()->endOfMonth();
                        @endphp
                        <button type="button" class="mobile-data-card" onclick="showDeductionModal({{ $d->id }})" data-deduction-id="{{ $d->id }}">
                            <span class="mobile-card-kicker">
                                <span class="badge-status" style="{{ $cStyle }}">{{ ucfirst($d->deductionType->category ?? 'Other') }}</span>
                                @if($d->status === 'active')
                                    <span class="badge-status" style="background:#e8f9ef;color:#15803d;border:1px solid #bbf7d0;text-transform:none">Active</span>
                                @elseif($d->status === 'pending')
                                    <span class="badge-status pending">Pending</span>
                                @else
                                    <span class="badge-status on-hold">{{ ucfirst($d->status) }}</span>
                                @endif
                            </span>
                            <span class="mobile-card-title">{{ $d->deductionType->name ?? 'N/A' }}</span>
                            @if($d->deductionType->code)
                                <span class="mobile-card-sub">{{ $d->deductionType->code }}</span>
                            @endif
                            <span class="mobile-card-metrics">
                                <span>
                                    <small class="mobile-deduction-amount-label">Monthly Amount</small>
                                    <strong class="deduction-amount-cell" data-per-cutoff="{{ $perCutoff }}">
                                        @if($monthly > 0)
                                            <span class="deduction-amount">&#8369;{{ number_format($monthly, 2) }}</span>
                                            <em class="deduction-period">per month</em>
                                        @elseif($d->deductionType && strtoupper($d->deductionType->computation_type) === 'PERCENTAGE' && $d->deductionType->percentage_rate > 0)
                                            <span>{{ $d->deductionType->percentage_rate }}% of salary</span>
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
                                <strong class="deduction-date-cell" data-start-date="{{ $d->start_date ? $d->start_date->format('Y-m-d') : '' }}">
                                    @if($d->start_date && $d->start_date <= $now)
                                        {{ $monthStart->format('M d') }} – {{ $monthEnd->format('d, Y') }}
                                    @elseif($d->start_date && $d->start_date > $now)
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

        </div>
    </main>

</div>

{{-- Deduction Details Modal --}}
<div class="modal-overlay" id="deductionModal" style="display:none" onclick="closeModal('deductionModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="deductionCategory">DEDUCTION DETAILS</span>
                <h3 class="modal-title" id="deductionName">Deduction Name</h3>
                <p class="modal-sub" id="deductionCode">Code</p>
            </div>
            <button class="modal-close" onclick="closeModal('deductionModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}</div>
                <div>
                    <p class="modal-emp-id">{{ $employee->employee_id }}</p>
                    <span class="badge-status" id="deductionStatusBadge">Active</span>
                </div>
            </div>
            <div class="modal-section-label">DEDUCTION INFORMATION</div>
            <div class="modal-row"><span>Total Amount</span><strong id="deductionTotalAmount">₱0.00</strong></div>
            <div class="modal-row"><span>Monthly Deduction</span><strong id="deductionMonthly">₱0.00</strong></div>
            <div class="modal-row"><span>Per Cutoff</span><strong id="deductionInstallment">₱0.00</strong></div>
            <div class="modal-row"><span>Remaining Balance</span><span class="modal-deduct" id="deductionRemaining">₱0.00</span></div>
            <div class="modal-section-label modal-section-deductions">SCHEDULE</div>
            <div class="modal-row"><span>Start Date</span><span id="deductionStartDate">N/A</span></div>
            <div class="modal-row"><span>End Date</span><span id="deductionEndDate">N/A</span></div>
            <div class="modal-row" id="deductionRemarksRow" style="display:none"><span>Remarks</span><span id="deductionRemarks" style="font-size:12px;color:#6b6a8a">N/A</span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('deductionModal')">Close</button>
        </div>
    </div>
</div>

@include('employee.chatbot.employeeChatbot')

<style>
/* ══════════ EMPLOYEE DASHBOARD ══════════
   Glass card system mirroring the admin dashboard (adminDashboard.blade.php). */
.perm-dash {
    --eh-blue: #0b044d;
    --eh-blue-2: #1b1464;
    --eh-card: #ffffff;
    --eh-ink: #111827;
    --eh-muted: #667085;
    --eh-line: #e5e7eb;
    --eh-glass: rgba(255, 255, 255, .58);
    --eh-glass-strong: rgba(255, 255, 255, .78);
    --eh-glass-border: rgba(255, 255, 255, .65);
    --eh-radius: 18px;
    padding-bottom: 28px;
    color: var(--eh-ink);
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", 'Poppins', sans-serif;
    position: relative;
    isolation: isolate;
}

.perm-dash * { letter-spacing: 0; }

/* ── Glass surfaces ── */
.perm-dash .stat-card,
.perm-dash .table-section,
.perm-dash .chart-card,
.perm-action-bar {
    border: 1px solid var(--eh-glass-border) !important;
    border-radius: var(--eh-radius) !important;
    background: linear-gradient(180deg, var(--eh-glass-strong), var(--eh-glass)) !important;
    backdrop-filter: blur(24px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .8),
        0 1px 3px rgba(15, 23, 42, .04),
        0 10px 30px rgba(15, 23, 42, .07) !important;
    position: relative;
    isolation: isolate;
}

.perm-dash .stat-card:hover,
.perm-dash .table-section:hover,
.perm-dash .chart-card:hover {
    border-color: rgba(255, 255, 255, .9) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .9),
        0 2px 4px rgba(15, 23, 42, .05),
        0 16px 40px rgba(11, 4, 77, .12) !important;
}

@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
    .perm-dash .stat-card,
    .perm-dash .table-section,
    .perm-dash .chart-card,
    .perm-action-bar {
        background: rgba(255, 255, 255, .96) !important;
    }
}

/* ── Stats ── */
.perm-stats {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}

.perm-stat-hover {
    cursor: pointer;
    min-height: 132px;
    padding: 20px !important;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}

.perm-stat-hover:hover { transform: translateY(-4px); }

.perm-dash .stat-top { margin-bottom: 16px; }

.perm-dash .stat-label {
    color: var(--eh-muted) !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}

.perm-dash .stat-value {
    color: var(--eh-ink) !important;
    font-size: 26px !important;
    line-height: 1.05;
}

.perm-dash .stat-value span { color: var(--eh-muted) !important; }

.perm-dash .stat-icon-wrap {
    width: 40px !important;
    height: 40px !important;
    border: 1px solid rgba(255, 255, 255, .7);
    border-radius: 12px !important;
    background: rgba(255, 255, 255, .55) !important;
    backdrop-filter: blur(10px) saturate(160%);
    -webkit-backdrop-filter: blur(10px) saturate(160%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
}

.perm-dash .stat-sub,
.perm-dash .table-sub,
.perm-dash .chart-sub { color: var(--eh-muted) !important; font-size: 12px !important; }

.stat-pill-alert {
    padding: 2px 7px;
    border-radius: 999px;
    background: #8e1e18;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.5;
}

/* ── Action bar ── */
.perm-action-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    padding: 14px 16px;
}

.perm-action-spacer { margin-right: auto; }

.perm-quick-btn {
    font-size: 11px !important;
    padding: 0 14px !important;
    height: 34px !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-weight: 700 !important;
}

/* ── Controls: pills, gradient primary ── */
.perm-dash .chart-tab,
.perm-dash .btn-export,
.perm-dash .modal-btn-primary,
.perm-dash .filter-select {
    border-radius: 999px !important;
    font-weight: 700 !important;
    transition: all .22s cubic-bezier(.4, 0, .2, 1) !important;
}

.perm-dash .chart-tab.active,
.perm-dash .modal-btn-primary {
    border-color: transparent !important;
    background: linear-gradient(135deg, var(--eh-blue-2), var(--eh-blue)) !important;
    color: #fff !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .25),
        0 8px 20px rgba(11, 4, 77, .35) !important;
}

.perm-dash .chart-tab.active:hover,
.perm-dash .modal-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .3),
        0 10px 26px rgba(11, 4, 77, .42) !important;
}

.perm-dash .btn-export,
.perm-dash .filter-select {
    border-color: rgba(15, 23, 42, .08) !important;
    background: rgba(255, 255, 255, .55) !important;
    backdrop-filter: blur(12px) saturate(160%) !important;
    -webkit-backdrop-filter: blur(12px) saturate(160%) !important;
    color: #56547a !important;
}

.perm-dash .btn-export:hover {
    background: rgba(255, 255, 255, .85) !important;
    border-color: rgba(15, 23, 42, .12) !important;
    transform: translateY(-1px);
}

.perm-dash .chart-tab:not(.active):hover { background: rgba(11, 4, 77, .06) !important; }

/* ── Grids ── */
.perm-overview-grid,
.perm-secondary-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 18px;
    align-items: stretch;
    margin-bottom: 18px;
}

.perm-overview-grid > *,
.perm-secondary-grid > * { min-width: 0; }

/* ── Section chrome ── */
.perm-dash .table-section { margin-bottom: 18px; }

.perm-dash .table-header,
.perm-dash .chart-header {
    align-items: center;
    padding: 18px 20px !important;
    border-bottom: 1px solid rgba(15, 23, 42, .06) !important;
    background: transparent !important;
    border-radius: var(--eh-radius) var(--eh-radius) 0 0 !important;
}

.perm-dash .table-title,
.perm-dash .chart-title {
    color: var(--eh-ink) !important;
    font-size: 15px !important;
    font-weight: 800 !important;
}

.perm-dash .chart-card { padding: 0 !important; }

.perm-dash .payroll-table {
    border-collapse: separate;
    border-spacing: 0;
}

.perm-dash .table-wrapper {
    max-width: 100%;
    overflow: auto;
}

.perm-dash .payroll-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: rgba(248, 250, 252, .75);
    backdrop-filter: blur(10px) saturate(160%);
    -webkit-backdrop-filter: blur(10px) saturate(160%);
    color: #667085;
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
}

.perm-dash .payroll-table td { border-bottom: 1px solid rgba(15, 23, 42, .05); }
.perm-dash .payroll-table tbody tr:hover { background: rgba(11, 4, 77, .035); }

.perm-dash .dept-tag,
.perm-dash .badge-status {
    border-radius: 999px !important;
    font-weight: 800 !important;
}

.perm-card-body { padding: 8px 20px 16px; }

/* ── My Requests rows ── */
.perm-req-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(15, 23, 42, .05);
}

.perm-req-row:last-child { border-bottom: 0; }

.perm-req-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
}

.perm-req-info { flex: 1; min-width: 0; }

.perm-req-title {
    margin: 0 0 3px;
    color: var(--eh-ink);
    font-size: 12.5px;
    font-weight: 800;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.perm-req-sub {
    margin: 0;
    color: var(--eh-muted);
    font-size: 11px;
}

/* ── Leave balance bars ── */
.perm-balance-row { margin-bottom: 16px; }
.perm-balance-row:last-child { margin-bottom: 4px; }

.perm-balance-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
}

.perm-balance-name {
    font-size: 12.5px;
    font-weight: 600;
    color: #1e293b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.perm-balance-val {
    font-size: 12.5px;
    font-weight: 800;
    white-space: nowrap;
    flex-shrink: 0;
}

.perm-balance-val em {
    font-style: normal;
    font-size: 10.5px;
    font-weight: 600;
    color: #94a3b8;
}

.perm-balance-track {
    height: 6px;
    border-radius: 999px;
    background: #eef2f6;
    overflow: hidden;
}

.perm-balance-fill {
    height: 100%;
    border-radius: inherit;
    transition: width .3s ease;
}

/* ── Empty states ── */
.perm-empty { text-align: center; padding: 32px 24px; }

.perm-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin: 0 auto 12px;
    border-radius: 50%;
    background: #f1f5f9;
}

.perm-empty-title { margin: 0 0 4px; font-size: 13px; font-weight: 700; color: #475569; }
.perm-empty-sub { margin: 0; font-size: 12px; color: #94a3b8; }

/* ── Responsive ── */
@media (max-width: 1200px) {
    .perm-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .perm-overview-grid,
    .perm-secondary-grid { grid-template-columns: 1fr; }
}

@media (max-width: 760px) {
    .perm-action-bar { flex-direction: column; align-items: stretch; }
    .perm-action-spacer { margin-right: 0; margin-bottom: 4px; }
    .perm-quick-btn { width: 100% !important; justify-content: center; }
    .perm-stats { grid-template-columns: 1fr !important; }

    .perm-dash .table-header,
    .perm-dash .table-actions,
    .perm-dash .chart-header {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .perm-dash .table-actions > * { width: 100% !important; }
}

@media (max-width: 540px) {
    .perm-dash .stat-value { font-size: 24px !important; }
    .perm-stat-hover { min-height: auto; }
}
</style>

<script>
let attendanceChart, salaryChart;
const deductionsData = @json($deductions);
let currentDeductionView = 'monthly';

const attendanceData = @json($chartData['attendance']);
const salaryData     = @json($chartData['salary']);

function initCharts() {
    const ctx1 = document.getElementById('attendanceChart').getContext('2d');
    const ctx2 = document.getElementById('salaryChart').getContext('2d');

    const gradientAtt = ctx1.createLinearGradient(0, 0, 0, 300);
    gradientAtt.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
    gradientAtt.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

    attendanceChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: attendanceData.month.labels,
            datasets: [
                {
                    label: 'Attendance Rate (%)',
                    data: attendanceData.month.data,
                    borderColor: '#3b82f6',
                    backgroundColor: gradientAtt,
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 3
                },
                {
                    label: 'Late Arrivals (%)',
                    data: attendanceData.month.lateData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 2
                },
                {
                    label: 'Absent (%)',
                    data: attendanceData.month.absentData,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 12,
                        font: { size: 11, family: 'Poppins', weight: '600' },
                        color: '#64748b',
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#fff',
                    titleColor: '#0b044d',
                    bodyColor: '#5a5888',
                    borderColor: '#eceaf8',
                    borderWidth: 1.5,
                    padding: 12,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 120,
                    grid: { color: '#f7f6ff', drawBorder: false },
                    ticks: { color: '#9999bb', font: { size: 11, family: 'Poppins' }, padding: 8 }
                },
                x: {
                    offset: false,
                    grid: { display: false, drawBorder: false, offset: false },
                    ticks: { color: '#9999bb', font: { size: 11, family: 'Poppins' }, padding: 2, autoSkip: true, maxRotation: 0, minRotation: 0 }
                }
            }
        }
    });

    salaryChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: salaryData.month.labels,
            datasets: [{
                label: 'Net Pay (₱)',
                data: salaryData.month.data,
                borderColor: '#0b044d',
                backgroundColor: 'rgba(11,4,77,0.07)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#0b044d',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#fff',
                    titleColor: '#0b044d',
                    bodyColor: '#5a5888',
                    borderColor: '#eceaf8',
                    borderWidth: 1.5,
                    padding: 12,
                    displayColors: false,
                    callbacks: { label: ctx => '₱' + ctx.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f7f6ff', drawBorder: false },
                    ticks: {
                        color: '#9999bb',
                        font: { size: 11, family: 'Poppins' },
                        callback: v => v >= 1000 ? '₱' + (v/1000).toFixed(1) + 'k' : '₱' + v
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#9999bb', font: { size: 11, family: 'Poppins' } }
                }
            }
        }
    });
}

function switchAttendanceChart(period) {
    const chartCard = document.getElementById('attendanceChart').closest('.chart-card');
    const buttons = chartCard.querySelectorAll('.chart-tab');
    buttons.forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');

    attendanceChart.data.labels = attendanceData[period].labels;
    attendanceChart.data.datasets[0].data = attendanceData[period].data;
    attendanceChart.data.datasets[1].data = attendanceData[period].lateData;
    attendanceChart.data.datasets[2].data = attendanceData[period].absentData;
    attendanceChart.update();
}

function switchSalaryChart(period) {
    document.getElementById('salaryChart').closest('.chart-card').querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    salaryChart.data.labels = salaryData[period].labels;
    salaryChart.data.datasets[0].data = salaryData[period].data;
    salaryChart.update();
}

function switchDeductionView(view) {
    event.stopPropagation();
    currentDeductionView = view;
    document.querySelectorAll('#deductionsSection .chart-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');

    const amountHeader = document.getElementById('deductionAmountHeader');
    const dateHeader   = document.getElementById('deductionDateHeader');
    const mobileAmountLabels = document.querySelectorAll('.mobile-deduction-amount-label');
    const mobileDateLabels   = document.querySelectorAll('.mobile-deduction-date-label');
    const amountCells = document.querySelectorAll('.deduction-amount-cell');
    const dateCells   = document.querySelectorAll('.deduction-date-cell');
    const today = new Date();

    if (view === 'daily') {
        amountHeader.textContent = 'Daily Amount';
        dateHeader.textContent   = 'Today';
        mobileAmountLabels.forEach(l => l.textContent = 'Daily Amount');
        mobileDateLabels.forEach(l => l.textContent = 'Today');
        amountCells.forEach(cell => {
            const pc = parseFloat(cell.dataset.perCutoff || 0);
            if (pc > 0) {
                const daily = pc / 15;
                const s = cell.querySelector('.deduction-amount');
                const p = cell.querySelector('.deduction-period');
                if (s && p) { s.textContent = '₱' + daily.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2}); p.textContent = 'per day'; }
            }
        });
        dateCells.forEach(cell => {
            const sd = cell.dataset.startDate;
            if (sd && today >= new Date(sd)) {
                cell.textContent = today.toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'});
            } else if (sd) { cell.textContent = 'Not yet started'; }
        });

    } else if (view === 'weekly') {
        amountHeader.textContent = 'Weekly Amount';
        dateHeader.textContent   = 'Current Week';
        mobileAmountLabels.forEach(l => l.textContent = 'Weekly Amount');
        mobileDateLabels.forEach(l => l.textContent = 'Current Week');
        amountCells.forEach(cell => {
            const pc = parseFloat(cell.dataset.perCutoff || 0);
            if (pc > 0) {
                const weekly = pc / 2;
                const s = cell.querySelector('.deduction-amount');
                const p = cell.querySelector('.deduction-period');
                if (s && p) { s.textContent = '₱' + weekly.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2}); p.textContent = 'per week'; }
            }
        });
        dateCells.forEach(cell => {
            const sd = cell.dataset.startDate;
            if (sd && today >= new Date(sd)) {
                const ws = new Date(today); ws.setDate(today.getDate() - today.getDay());
                const we = new Date(ws); we.setDate(ws.getDate() + 6);
                cell.textContent = ws.toLocaleDateString('en-US',{month:'short',day:'numeric'}) + ' – ' + we.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
            } else if (sd) { cell.textContent = 'Not yet started'; }
        });

    } else {
        amountHeader.textContent = 'Monthly Amount';
        dateHeader.textContent   = 'Current Month';
        mobileAmountLabels.forEach(l => l.textContent = 'Monthly Amount');
        mobileDateLabels.forEach(l => l.textContent = 'Current Month');
        amountCells.forEach(cell => {
            const pc = parseFloat(cell.dataset.perCutoff || 0);
            if (pc > 0) {
                const monthly = pc * 2;
                const s = cell.querySelector('.deduction-amount');
                const p = cell.querySelector('.deduction-period');
                if (s && p) { s.textContent = '₱' + monthly.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2}); p.textContent = 'per month'; }
            }
        });
        dateCells.forEach(cell => {
            const sd = cell.dataset.startDate;
            if (sd && today >= new Date(sd)) {
                const ms = new Date(today.getFullYear(), today.getMonth(), 1);
                const me = new Date(today.getFullYear(), today.getMonth()+1, 0);
                cell.textContent = ms.toLocaleDateString('en-US',{month:'short',day:'numeric'}) + ' – ' + me.toLocaleDateString('en-US',{day:'numeric',year:'numeric'});
            } else if (sd) { cell.textContent = 'Not yet started'; }
            else { cell.textContent = 'N/A'; }
        });
    }
}

window.addEventListener('load', initCharts);

// Sidebar
const sidebar       = document.getElementById('sidebar');
const toggleBtn     = document.getElementById('toggle-btn');
const logoText      = document.getElementById('logo-text');
const navLabel      = document.getElementById('nav-label');
const userInfo      = document.getElementById('user-info');
const sidebarFooter = document.getElementById('sidebar-footer');
const mobileBtn     = document.getElementById('mobile-menu-btn');
const overlay       = document.getElementById('mobile-overlay');

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        if (logoText) logoText.style.display  = collapsed ? 'none' : '';
        if (navLabel) navLabel.style.display  = collapsed ? 'none' : '';
        if (userInfo) userInfo.style.display  = collapsed ? 'none' : '';
        if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label,.nav-active-bar').forEach(el => el.style.display = collapsed ? 'none' : '');
    });
}
if (mobileBtn) mobileBtn.addEventListener('click', () => { sidebar.classList.toggle('mobile-open'); overlay.classList.toggle('active'); });
if (overlay)   overlay.addEventListener('click', () => { sidebar.classList.remove('mobile-open'); overlay.classList.remove('active'); });

function showDeductionSummary() {
    if (deductionsData.length > 0) showDeductionModal(deductionsData[0].id);
    else alert('No deduction records available.');
}

function showDeductionModal(deductionId) {
    const d = deductionsData.find(x => x.id == deductionId);
    if (!d) { alert('Deduction not found.'); return; }

    document.getElementById('deductionCategory').textContent = (d.deduction_type?.category || 'DEDUCTION').toUpperCase() + ' DETAILS';
    document.getElementById('deductionName').textContent = d.deduction_type?.name || 'N/A';
    document.getElementById('deductionCode').textContent = d.deduction_type?.code || '';

    const totalAmount  = d.total_amount ? parseFloat(d.total_amount) : (d.calculated_amount ? parseFloat(d.calculated_amount) : parseFloat(d.amount || 0));
    const installment  = d.calculated_amount ? parseFloat(d.calculated_amount) : (d.installment_amount ? parseFloat(d.installment_amount) : parseFloat(d.amount || 0));
    const monthly      = installment * 2;
    const remaining    = d.remaining_balance ? parseFloat(d.remaining_balance) : 0;

    document.getElementById('deductionTotalAmount').textContent  = '₱' + totalAmount.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('deductionMonthly').textContent      = '₱' + monthly.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('deductionInstallment').textContent  = '₱' + installment.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('deductionRemaining').textContent    = '₱' + remaining.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('deductionStartDate').textContent    = d.start_date || 'N/A';
    document.getElementById('deductionEndDate').textContent      = d.end_date   || 'N/A';

    const sb = document.getElementById('deductionStatusBadge');
    sb.textContent = d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) : 'Active';
    sb.className   = 'badge-status';
    if (d.status === 'active')       sb.style.cssText = 'background:#e8f9ef;color:#15803d;border:1px solid #bbf7d0';
    else if (d.status === 'pending') { sb.className = 'badge-status pending'; sb.style.cssText = ''; }
    else                             { sb.className = 'badge-status on-hold'; sb.style.cssText = ''; }

    if (d.remarks) {
        document.getElementById('deductionRemarksRow').style.display = 'flex';
        document.getElementById('deductionRemarks').textContent = d.remarks;
    } else {
        document.getElementById('deductionRemarksRow').style.display = 'none';
    }

    document.getElementById('deductionModal').style.display = 'flex';
}

function exportDeductions() {
    if (deductionsData.length === 0) { alert('No deduction records to export.'); return; }
    let csv = 'Deduction Type,Category,Monthly Amount,Per Cutoff,Remaining Balance,Total Amount,Start Date,End Date,Status\n';
    deductionsData.forEach(d => {
        const i = d.installment_amount || d.amount || 0;
        const m = i * 2;
        csv += `"${d.deduction_type?.name||'N/A'}","${d.deduction_type?.category||'N/A'}","${m.toFixed(2)}","${i.toFixed(2)}","${(d.remaining_balance||0).toFixed(2)}","${(d.total_amount||d.amount||0).toFixed(2)}","${d.start_date||'N/A'}","${d.end_date||'N/A'}","${d.status||'N/A'}"\n`;
    });
    const blob = new Blob([csv], {type:'text/csv'});
    const url  = window.URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = 'deductions_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
});
</script>
@endsection
