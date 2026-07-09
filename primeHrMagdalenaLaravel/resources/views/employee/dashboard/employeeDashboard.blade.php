@extends('layouts.permanent')

@section('title', 'Dashboard · PRIME HRIS')

@section('content')
<div class="app-layout">

    @include('permanent.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Dashboard'
    ])

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('permanent.sidebar.permanentSidebar')

    <main class="main-content permanent-dashboard glass-shell">

        @include('permanent.notification.permanentNotification')
        @include('permanent.topbar.permanentTopbar')

        <div class="perm-dash">

            {{-- Header --}}

            {{-- Stats Grid --}}
            <div class="stats-grid stats-grid-4 perm-stats">

                <div class="stat-card perm-stat-hover">
                    <div class="stat-top">
                        <p class="stat-label">Basic Pay</p>
                        <div class="stat-icon-wrap" style="background:#f0effe;border:1px solid #dbe0ea;border-radius:10px;width:40px;height:40px">
                            <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        </div>
                    </div>
                    <p class="stat-value" style="font-size:26px">₱{{ number_format($basicPay, 2) }}</p>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#0b044d"></span>
                        <p class="stat-sub">{{ $startDate->format('M d') }}–{{ $endDate->format('d, Y') }}</p>
                    </div>
                </div>

                <div class="stat-card perm-stat-hover">
                    <div class="stat-top">
                        <p class="stat-label">Net Pay</p>
                        <div class="stat-icon-wrap" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;width:40px;height:40px">
                            <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                    </div>
                    <p class="stat-value" style="font-size:26px">₱{{ number_format($netPay, 2) }}</p>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#22c55e"></span>
                        <p class="stat-sub">After deductions</p>
                    </div>
                </div>

                <div class="stat-card perm-stat-hover">
                    <div class="stat-top">
                        <p class="stat-label">Leave Credits</p>
                        <div class="stat-icon-wrap" style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;width:40px;height:40px">
                            <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                    </div>
                    <p class="stat-value" style="font-size:26px">{{ number_format($leaveBalances->sum('available_credits'), 1) }}<span style="font-size:14px;color:#9999bb;font-weight:500"> days</span></p>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#d9bb00"></span>
                        <p class="stat-sub">{{ $leaveBalances->count() }} leave type(s)</p>
                    </div>
                </div>

                <div class="stat-card perm-stat-hover">
                    <div class="stat-top">
                        <p class="stat-label">Attendance Rate</p>
                        <div class="stat-icon-wrap" style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;width:40px;height:40px">
                            <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                    </div>
                    <p class="stat-value" style="font-size:26px">{{ $attendanceRate }}<span style="font-size:14px;color:#9999bb;font-weight:500">%</span></p>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#8e1e18"></span>
                        <p class="stat-sub">{{ $presentDays }} days present</p>
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
                <button class="modal-btn-primary perm-quick-btn" onclick="showPayslip()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    View Payslip
                </button>
                <button class="btn-export perm-quick-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    File Leave
                </button>
                <button class="btn-export perm-quick-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                    Attendance
                </button>
                <button class="btn-export perm-quick-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    My Profile
                </button>
            </div>

            {{-- Charts Row --}}
            <div class="charts-grid perm-charts">

                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <p class="chart-title">Attendance Trends</p>
                            <p class="chart-sub">Track your attendance patterns</p>
                        </div>
                        <div class="chart-tabs">
                            <button class="chart-tab" onclick="switchAttendanceChart('week')">Week</button>
                            <button class="chart-tab active" onclick="switchAttendanceChart('month')">Month</button>
                            <button class="chart-tab" onclick="switchAttendanceChart('year')">Year</button>
                        </div>
                    </div>
                    <canvas id="attendanceChart" style="max-height:260px;padding:0 16px 16px"></canvas>
                </div>

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

            </div>

            {{-- Deductions Table --}}
            <div class="table-section perm-section">
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

            {{-- Bottom Row: Notifications + Leave Balance --}}
            <div class="perm-bottom-grid">

                {{-- Notifications --}}
                <div class="table-section perm-section" style="margin:0">
                    <div class="table-header">
                        <div>
                            <p class="table-title">Notifications</p>
                            <p class="table-sub">Recent HR updates</p>
                        </div>
                        <button class="btn-export" style="font-size:11px;padding:6px 12px">Mark all read</button>
                    </div>
                    <div style="padding:8px 0">
                        @php
                        $notifs = [
                            ['title'=>'Leave Request Reminder','desc'=>'Your vacation leave request is pending approval.','time'=>'2 hours ago','status'=>'unread'],
                            ['title'=>'Payroll Updated','desc'=>'June 16–30, 2025 payroll is now available.','time'=>'Yesterday','status'=>'unread'],
                            ['title'=>'Training Schedule','desc'=>'CSC training scheduled for June 18, 2025.','time'=>'2 days ago','status'=>'read'],
                        ];
                        @endphp
                        @foreach($notifs as $n)
                        <div class="perm-notif-row">
                            <div class="perm-notif-dot {{ $n['status'] === 'unread' ? 'perm-notif-unread' : 'perm-notif-read' }}"></div>
                            <div style="flex:1;min-width:0">
                                <p class="perm-notif-title">{{ $n['title'] }}</p>
                                <p class="perm-notif-desc">{{ $n['desc'] }}</p>
                            </div>
                            <div style="text-align:right;flex-shrink:0">
                                <p class="perm-notif-time">{{ $n['time'] }}</p>
                                @if($n['status'] === 'unread')
                                    <span class="badge-status pending" style="font-size:10px;padding:2px 8px">Unread</span>
                                @else
                                    <span class="badge-status processed" style="font-size:10px;padding:2px 8px">Read</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Leave Balance --}}
                <div class="table-section perm-section" style="margin:0">
                    <div class="table-header">
                        <div>
                            <p class="table-title">Leave Balance</p>
                            <p class="table-sub">Available credits by type</p>
                        </div>
                    </div>
                    <div style="padding:16px 20px 20px">
                        @forelse($leaveBalances as $balance)
                        @php
                            $percent = $balance->total_credits > 0 ? ($balance->available_credits / $balance->total_credits) * 100 : 0;
                            $colors = ['#0b044d','#8e1e18','#a16207','#15803d','#0e7490'];
                            $color  = $colors[$loop->index % count($colors)];
                            $bgs    = ['#f0effe','#fff1f2','#fffbeb','#f0fdf4','#ecfeff'];
                            $bg     = $bgs[$loop->index % count($bgs)];
                        @endphp
                        <div style="margin-bottom:18px">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0"></div>
                                    <span style="font-size:13px;font-weight:600;color:#111827">{{ $balance->leaveType->leave_name ?? 'Unknown' }}</span>
                                </div>
                                <span style="font-size:13px;font-weight:700;color:{{ $color }}">{{ number_format($balance->available_credits, 1) }} <span style="font-size:11px;color:#94a3b8;font-weight:500">/ {{ number_format($balance->total_credits, 1) }} days</span></span>
                            </div>
                            <div style="height:8px;background:#f1f5f9;border-radius:999px;overflow:hidden">
                                <div style="width:{{ $percent }}%;height:100%;background:{{ $color }};border-radius:999px;transition:width 0.3s ease"></div>
                            </div>
                        </div>
                        @empty
                        <div style="text-align:center;padding:32px 24px">
                            <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                                <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Leave Balances</p>
                            <p style="font-size:12px;color:#94a3b8;margin:0">Leave balances will appear here</p>
                        </div>
                        @endforelse
                    </div>
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

@include('permanent.chatbot.permanentChatbot')

<style>
.perm-dash {
    --eh-blue: #0b044d;
    --eh-ink: #111827;
    --eh-muted: #667085;
    --eh-line: #e5e7eb;
    --eh-card: #ffffff;
    padding-bottom: 28px;
    color: var(--eh-ink);
}

.perm-dash * { letter-spacing: 0; }

/* Header */
.perm-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
    padding: 22px 24px;
    border: 1px solid var(--eh-line);
    border-radius: 12px;
    background: var(--eh-card);
    box-shadow: 0 2px 8px rgba(15, 23, 42, .04), 0 1px 3px rgba(15, 23, 42, .03);
}

.perm-kicker {
    display: inline-flex;
    margin-bottom: 7px;
    color: var(--eh-blue);
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}

.perm-header h1 {
    margin: 0;
    color: var(--eh-ink);
    font-size: clamp(24px, 3vw, 34px);
    line-height: 1.1;
    font-weight: 800;
}

.perm-header > div:first-child p {
    margin: 8px 0 0;
    color: var(--eh-muted);
    font-size: 13px;
}

.perm-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.perm-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 188px;
    padding: 8px 12px 8px 8px;
    border: 1px solid var(--eh-line);
    border-radius: 10px;
    background: #fff;
}

.perm-profile strong, .perm-profile span { display: block; white-space: nowrap; }
.perm-profile strong { color: var(--eh-ink); font-size: 13px; font-weight: 750; }
.perm-profile span { color: var(--eh-muted); font-size: 11px; }

.perm-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--eh-blue);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
}

/* Stats */
.perm-stats {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}

.perm-stat-hover {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid var(--eh-line) !important;
    border-radius: 12px !important;
    background: var(--eh-card) !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .04), 0 1px 3px rgba(15, 23, 42, .03) !important;
    min-height: 132px;
    padding: 20px !important;
    overflow: hidden;
}

.perm-stat-hover:hover {
    border-color: #cfd4dc !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .08), 0 2px 4px rgba(15, 23, 42, .06) !important;
    transform: translateY(-4px);
}

/* Action bar */
.perm-action-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    padding: 14px 16px;
    border: 1px solid var(--eh-line) !important;
    border-radius: 12px !important;
    background: #fff !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .03);
}

.perm-action-spacer { margin-right: auto; }

.perm-quick-btn {
    font-size: 11px !important;
    padding: 0 14px !important;
    height: 34px !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    border-radius: 8px !important;
    font-weight: 700 !important;
    transition: all .18s ease !important;
}

/* Charts */
.perm-charts {
    gap: 18px;
    margin-bottom: 18px;
}

.perm-charts .chart-card {
    border: 1px solid var(--eh-line) !important;
    border-radius: 12px !important;
    background: var(--eh-card) !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .04), 0 1px 3px rgba(15, 23, 42, .03) !important;
    padding: 0 !important;
}

.perm-charts .chart-card:hover {
    border-color: #cfd4dc !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .08), 0 2px 4px rgba(15, 23, 42, .06) !important;
}

.perm-charts .chart-header {
    align-items: center;
    padding: 18px 20px !important;
    border-bottom: 1px solid var(--eh-line) !important;
    background: #fff !important;
}

/* Sections */
.perm-section {
    border: 1px solid var(--eh-line) !important;
    border-radius: 12px !important;
    background: var(--eh-card) !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .04), 0 1px 3px rgba(15, 23, 42, .03) !important;
}

.perm-section:hover {
    border-color: #cfd4dc !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .08), 0 2px 4px rgba(15, 23, 42, .06) !important;
}

.perm-section .table-header {
    align-items: center;
    padding: 18px 20px !important;
    border-bottom: 1px solid var(--eh-line) !important;
    background: #fff !important;
}

.perm-section .payroll-table {
    border-collapse: separate;
    border-spacing: 0;
}

.perm-section .table-wrapper {
    max-width: 100%;
    overflow: auto;
}

.perm-section .payroll-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8fafc;
    color: #667085;
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
}

.perm-section .payroll-table td { border-bottom: 1px solid #eef2f6; }
.perm-section .payroll-table tbody tr:hover { background: #f9fafb; }

/* Bottom grid */
.perm-bottom-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 18px;
    align-items: stretch;
    margin-top: 18px;
}

/* Notifications */
.perm-notif-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}

.perm-notif-row:last-child { border-bottom: none; }
.perm-notif-row:hover { background: #f9fafb; }

.perm-notif-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    margin-top: 5px;
    flex-shrink: 0;
}

.perm-notif-unread { background: #0b044d; }
.perm-notif-read { background: #d1d5db; }

.perm-notif-title { font-size: 13px; font-weight: 700; color: #111827; margin: 0 0 3px; }
.perm-notif-desc { font-size: 12px; color: #6b7280; margin: 0; }
.perm-notif-time { font-size: 11px; color: #9ca3af; margin: 0 0 5px; font-weight: 500; }

/* Responsive */
@media (max-width: 1200px) {
    .perm-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .perm-bottom-grid { grid-template-columns: 1fr; }
}

@media (max-width: 1024px) {
    .perm-charts { grid-template-columns: 1fr !important; }
}

@media (max-width: 900px) {
    .perm-stats { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
}

@media (max-width: 760px) {
    .perm-header,
    .perm-header-right {
        flex-direction: column;
        align-items: stretch;
    }
    .perm-header { padding: 18px; }
    .perm-profile { min-width: 0; width: 100%; box-sizing: border-box; }
    .perm-action-bar { flex-direction: column; align-items: stretch; }
    .perm-action-spacer { margin-right: 0; margin-bottom: 4px; }
    .perm-quick-btn { width: 100% !important; justify-content: center; }
    .perm-stats { grid-template-columns: 1fr !important; }
    .perm-charts { grid-template-columns: 1fr !important; }
    .perm-bottom-grid { grid-template-columns: 1fr; }
}

@media (max-width: 540px) {
    .perm-stats { grid-template-columns: 1fr !important; }
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
    document.querySelector('.perm-section .chart-tabs').querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
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
