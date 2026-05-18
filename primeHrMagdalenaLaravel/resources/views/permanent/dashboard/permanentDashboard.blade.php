@extends('layouts.permanent')

@section('title', 'Permanent Dashboard · PRIME HRIS')

@section('content')
<div class="app-layout">

    {{-- Mobile Menu Button --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('permanent.sidebar.permanentSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard">

        @include('permanent.notification.permanentNotification')

        @include('permanent.topbar.permanentTopbar')

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Basic Pay</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value stat-value-compact">₱{{ number_format($basicPay, 2) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub">{{ $startDate->format('M d') }}-{{ $endDate->format('d, Y') }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Net Pay</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value stat-value-compact">₱{{ number_format($netPay, 2) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">After deductions</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Leave Credits</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                </div>
                <p class="stat-value">{{ number_format($leaveBalances->sum('available_credits'), 1) }} days</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">{{ $leaveBalances->count() }} leave type(s)</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Attendance</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $attendanceRate }}%</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">{{ $presentDays }} days present</p>
                </div>
            </div>

        </div>

        {{-- Charts Section --}}
        <div class="charts-grid">
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
                <canvas id="attendanceChart" style="max-height:280px"></canvas>
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
                <canvas id="salaryChart" style="max-height:280px"></canvas>
            </div>
        </div>

        {{-- Deductions & Loans Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">My Deductions & Loans</p>
                    <p class="table-sub">Active deductions from your salary</p>
                </div>
                <div class="table-actions">
                    <div class="chart-tabs" style="margin-right: 12px;" onclick="event.stopPropagation();">
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
                        <tr onclick="showDeductionModal({{ $d->id }})" style="cursor: pointer;" data-deduction-id="{{ $d->id }}">
                            <td class="table-cell-period">
                                <strong>{{ $d->deductionType->name ?? 'N/A' }}</strong>
                                @if($d->deductionType->code)
                                    <br><span style="font-size: 11px; color: #9999bb;">{{ $d->deductionType->code }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $categoryColors = [
                                        'mandatory' => 'background: #e8f9ef; color: #15803d;',
                                        'loan' => 'background: #fefce8; color: #a16207;',
                                        'voluntary' => 'background: #f0effe; color: #0b044d;',
                                    ];
                                    $style = $categoryColors[$d->deductionType->category] ?? 'background: #f7f6ff; color: #6b6a8a;';
                                @endphp
                                <span class="badge-status" style="{{ $style }}">{{ ucfirst($d->deductionType->category ?? 'Other') }}</span>
                            </td>
                            <td class="table-cell-basic deduction-amount-cell" data-per-cutoff="{{ $d->calculated_amount ?? ($d->installment_amount ?? $d->amount ?? ($d->deductionType && strtoupper($d->deductionType->computation_type) === 'FIXED' ? $d->deductionType->percentage_rate / 2 : 0)) }}">
                                @php
                                    $perCutoff = $d->calculated_amount ?? ($d->installment_amount ?? $d->amount ?? 0);
                                    if ($perCutoff == 0 && $d->deductionType && strtoupper($d->deductionType->computation_type) === 'FIXED') {
                                        // For FIXED type, percentage_rate is the MONTHLY amount, divide by 2 for per-cutoff
                                        $perCutoff = ($d->deductionType->percentage_rate ?? 0) / 2;
                                    }
                                    $monthly = $perCutoff * 2;
                                @endphp
                                @if($monthly > 0)
                                    <span class="deduction-amount">₱{{ number_format($monthly, 2) }}</span>
                                    <br><span style="font-size: 11px; color: #9999bb;" class="deduction-period">per month</span>
                                @elseif($d->deductionType && strtoupper($d->deductionType->computation_type) === 'PERCENTAGE' && $d->deductionType->percentage_rate > 0)
                                    <span style="color: #a16207; font-size: 12px;">{{ $d->deductionType->percentage_rate }}% of salary</span>
                                    <br><span style="font-size: 11px; color: #9999bb;">Pending computation</span>
                                @else
                                    <span style="color: #9999bb; font-size: 12px;">To be computed</span>
                                @endif
                            </td>
                            <td class="table-cell-deduct">
                                @if($d->remaining_balance !== null)
                                    ₱{{ number_format($d->remaining_balance, 2) }}
                                    @if($d->total_amount)
                                        <br><span style="font-size: 11px; color: #9999bb;">of ₱{{ number_format($d->total_amount, 2) }}</span>
                                    @endif
                                @else
                                    <span style="color: #9999bb;">N/A</span>
                                @endif
                            </td>
                            <td class="table-cell-date deduction-date-cell" data-start-date="{{ $d->start_date ? $d->start_date->format('Y-m-d') : '' }}">
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $monthStart = $now->copy()->startOfMonth();
                                    $monthEnd = $now->copy()->endOfMonth();
                                @endphp
                                @if($d->start_date && $d->start_date <= $now)
                                    {{ $monthStart->format('M d') }} - {{ $monthEnd->format('d, Y') }}
                                @elseif($d->start_date && $d->start_date > $now)
                                    Not yet started
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($d->status === 'active')
                                    <span class="badge-status processed">Active</span>
                                @elseif($d->status === 'pending')
                                    <span class="badge-status pending">Pending</span>
                                @else
                                    <span class="badge-status on-hold">{{ ucfirst($d->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6b6a8a;">No active deductions or loans</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong>{{ $deductions->count() }}</strong> active deduction(s)</span>
            </div>
        </div>

        <div class="bottom-row">

            {{-- Notifications --}}
            <div class="table-section mb-0">
                <div class="table-header">
                    <div>
                        <p class="table-title">Notifications</p>
                        <p class="table-sub">2 unread messages</p>
                    </div>
                    <button class="btn-export">Mark all read</button>
                </div>
                <div class="table-wrapper">
                    <table class="payroll-table">
                        <thead>
                            <tr><th>Message</th><th>Date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @php
                            $notifs = [
                                ['title'=>'Leave Request Reminder','desc'=>'Your vacation leave request is pending approval.','time'=>'2 hours ago','status'=>'unread'],
                                ['title'=>'Payroll Updated','desc'=>'June 16-30, 2025 payroll is now available.','time'=>'Yesterday','status'=>'unread'],
                                ['title'=>'Training Schedule','desc'=>'CSC training scheduled for June 18, 2025.','time'=>'2 days ago','status'=>'read'],
                            ];
                            @endphp
                            @foreach($notifs as $n)
                            <tr>
                                <td>
                                    <div class="dashboard-notif-item">
                                        <div class="dashboard-notif-dot {{ $n['status']==='unread' ? 'dashboard-notif-dot-unread' : 'dashboard-notif-dot-read' }}"></div>
                                        <div>
                                            <p class="dashboard-notif-title">{{ $n['title'] }}</p>
                                            <p class="dashboard-notif-desc">{{ $n['desc'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="dashboard-notif-time">{{ $n['time'] }}</td>
                                <td>
                                    @if($n['status']==='unread')
                                        <span class="badge-status pending">Unread</span>
                                    @else
                                        <span class="badge-status processed">Read</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="side-col">

                <div class="table-section mb-0">
                    <div class="table-header table-header-compact">
                        <p class="table-title table-title-sm">Quick Actions</p>
                    </div>
                    <div class="quick-actions-body">
                        <button class="quick-action-btn quick-action-btn-block" onclick="showPayslip()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            View Payslip
                        </button>
                        <button class="quick-action-btn quick-action-btn-block">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            File Leave
                        </button>
                        <button class="quick-action-btn quick-action-btn-block">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                            View Attendance
                        </button>
                        <button class="quick-action-btn quick-action-btn-block quick-action-btn-last">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            My Profile
                        </button>
                    </div>
                </div>

                <div class="stat-card no-margin">
                    <p class="stat-label leave-balance-label">Leave Balance</p>
                    @forelse($leaveBalances->take(3) as $balance)
                    @php
                        $percent = $balance->total_credits > 0 ? ($balance->available_credits / $balance->total_credits) * 100 : 0;
                        $colors = ['#0b044d', '#8e1e18', '#a16207'];
                        $color = $colors[$loop->index % 3];
                    @endphp
                    <div class="leave-row">
                        <div class="leave-row-top">
                            <span class="leave-row-name">{{ $balance->leaveType->leave_name ?? 'Unknown' }}</span>
                            <span class="leave-row-balance">{{ number_format($balance->available_credits, 1) }} days</span>
                        </div>
                        <div class="leave-progress-track">
                            <div class="leave-progress {{ $color == '#0b044d' ? 'leave-color-primary' : ($color == '#8e1e18' ? 'leave-color-danger' : 'leave-color-warning') }}" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p style="text-align: center; color: #6b6a8a; padding: 20px 0;">No leave balances available</p>
                    @endforelse
                </div>

            </div>
        </div>

    </main>

</div>

{{-- Deduction Details Modal --}}
<div class="modal-overlay" id="deductionModal" style="display: none;" onclick="closeModal('deductionModal')">
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
            <div class="modal-row" id="deductionRemarksRow" style="display: none;"><span>Remarks</span><span id="deductionRemarks" style="font-size: 12px; color: #6b6a8a;">N/A</span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('deductionModal')">Close</button>
        </div>
    </div>
</div>

@include('permanent.chatbot.permanentChatbot')

<script>
let attendanceChart, salaryChart;
const deductionsData = @json($deductions);
let currentDeductionView = 'monthly';

// Dynamic data from controller
const attendanceData = @json($chartData['attendance']);
const salaryData = @json($chartData['salary']);

function initCharts() {
    const ctx1 = document.getElementById('attendanceChart').getContext('2d');
    const ctx2 = document.getElementById('salaryChart').getContext('2d');

    attendanceChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: attendanceData.month.labels,
            datasets: [{
                label: 'Attendance Rate (%)',
                data: attendanceData.month.data,
                borderColor: '#15803d',
                backgroundColor: 'rgba(21, 128, 61, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#15803d',
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
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    max: 100,
                    grid: { color: '#f7f6ff', drawBorder: false },
                    ticks: { 
                        color: '#9999bb', 
                        font: { size: 11, family: 'Poppins' },
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: { 
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#9999bb', font: { size: 11, family: 'Poppins' } }
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
                backgroundColor: 'rgba(11, 4, 77, 0.1)',
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
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: '#f7f6ff', drawBorder: false },
                    ticks: { 
                        color: '#9999bb', 
                        font: { size: 11, family: 'Poppins' },
                        callback: function(value) {
                            if (value >= 1000) {
                                return '₱' + (value/1000).toFixed(1) + 'k';
                            }
                            return '₱' + value;
                        }
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
    chartCard.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    attendanceChart.data.labels = attendanceData[period].labels;
    attendanceChart.data.datasets[0].data = attendanceData[period].data;
    attendanceChart.update();
}

function switchSalaryChart(period) {
    const chartCard = document.getElementById('salaryChart').closest('.chart-card');
    chartCard.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    salaryChart.data.labels = salaryData[period].labels;
    salaryChart.data.datasets[0].data = salaryData[period].data;
    salaryChart.update();
}

function switchDeductionView(view) {
    event.stopPropagation(); // Prevent row click event
    currentDeductionView = view;
    const tableSection = document.querySelector('.table-section');
    tableSection.querySelectorAll('.chart-tabs .chart-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    
    const amountHeader = document.getElementById('deductionAmountHeader');
    const dateHeader = document.getElementById('deductionDateHeader');
    const amountCells = document.querySelectorAll('.deduction-amount-cell');
    const dateCells = document.querySelectorAll('.deduction-date-cell');
    
    const today = new Date();
    
    if (view === 'daily') {
        amountHeader.textContent = 'Daily Amount';
        dateHeader.textContent = 'Today';
        
        // Update amounts
        amountCells.forEach(cell => {
            const perCutoff = parseFloat(cell.dataset.perCutoff || 0);
            if (perCutoff > 0) {
                const daily = perCutoff / 15;
                const amountSpan = cell.querySelector('.deduction-amount');
                const periodSpan = cell.querySelector('.deduction-period');
                if (amountSpan && periodSpan) {
                    amountSpan.textContent = '₱' + daily.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    periodSpan.textContent = 'per day';
                }
            }
        });
        
        // Update dates to show today's date
        dateCells.forEach(cell => {
            const startDate = cell.dataset.startDate;
            if (startDate) {
                const start = new Date(startDate);
                if (today >= start) {
                    cell.textContent = today.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                } else {
                    cell.textContent = 'Not yet started';
                }
            }
        });
        
    } else if (view === 'weekly') {
        amountHeader.textContent = 'Weekly Amount';
        dateHeader.textContent = 'Current Week';
        
        // Update amounts
        amountCells.forEach(cell => {
            const perCutoff = parseFloat(cell.dataset.perCutoff || 0);
            if (perCutoff > 0) {
                const weekly = perCutoff / 2;
                const amountSpan = cell.querySelector('.deduction-amount');
                const periodSpan = cell.querySelector('.deduction-period');
                if (amountSpan && periodSpan) {
                    amountSpan.textContent = '₱' + weekly.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    periodSpan.textContent = 'per week';
                }
            }
        });
        
        // Update dates to show current week range
        dateCells.forEach(cell => {
            const startDate = cell.dataset.startDate;
            if (startDate) {
                const start = new Date(startDate);
                if (today >= start) {
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay()); // Sunday
                    const weekEnd = new Date(weekStart);
                    weekEnd.setDate(weekStart.getDate() + 6); // Saturday
                    
                    const startStr = weekStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    const endStr = weekEnd.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    cell.textContent = startStr + ' - ' + endStr;
                } else {
                    cell.textContent = 'Not yet started';
                }
            }
        });
        
    } else {
        amountHeader.textContent = 'Monthly Amount';
        dateHeader.textContent = 'Current Month';
        
        // Update amounts
        amountCells.forEach(cell => {
            const perCutoff = parseFloat(cell.dataset.perCutoff || 0);
            if (perCutoff > 0) {
                const monthly = perCutoff * 2;
                const amountSpan = cell.querySelector('.deduction-amount');
                const periodSpan = cell.querySelector('.deduction-period');
                if (amountSpan && periodSpan) {
                    amountSpan.textContent = '₱' + monthly.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    periodSpan.textContent = 'per month';
                }
            }
        });
        
        // Show current month range
        dateCells.forEach(cell => {
            const startDate = cell.dataset.startDate;
            if (startDate) {
                const start = new Date(startDate);
                if (today >= start) {
                    const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                    const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    
                    const startStr = monthStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    const endStr = monthEnd.toLocaleDateString('en-US', { day: 'numeric', year: 'numeric' });
                    cell.textContent = startStr + ' - ' + endStr;
                } else {
                    cell.textContent = 'Not yet started';
                }
            } else {
                cell.textContent = 'N/A';
            }
        });
    }
}

window.addEventListener('load', initCharts);

const sidebar      = document.getElementById('sidebar');
const toggleBtn    = document.getElementById('toggle-btn');
const logoText     = document.getElementById('logo-text');
const navLabel     = document.getElementById('nav-label');
const userInfo     = document.getElementById('user-info');
const sidebarFooter = document.getElementById('sidebar-footer');
const mobileBtn    = document.getElementById('mobile-menu-btn');
const overlay      = document.getElementById('mobile-overlay');

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        if (logoText) logoText.style.display  = collapsed ? 'none' : '';
        if (navLabel) navLabel.style.display  = collapsed ? 'none' : '';
        if (userInfo) userInfo.style.display  = collapsed ? 'none' : '';
        if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
    });
}

if (mobileBtn) {
    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
}

function showDeductionSummary() {
    if (deductionsData.length > 0) {
        showDeductionModal(deductionsData[0].id);
    } else {
        alert('No deduction records available.');
    }
}

function showDeductionModal(deductionId) {
    const deduction = deductionsData.find(d => d.id == deductionId);
    
    if (!deduction) {
        alert('Deduction not found.');
        return;
    }
    
    // Update modal content
    document.getElementById('deductionCategory').textContent = (deduction.deduction_type?.category || 'DEDUCTION').toUpperCase() + ' DETAILS';
    document.getElementById('deductionName').textContent = deduction.deduction_type?.name || 'N/A';
    document.getElementById('deductionCode').textContent = deduction.deduction_type?.code || '';
    
    const totalAmount = deduction.total_amount ? parseFloat(deduction.total_amount) : (deduction.calculated_amount ? parseFloat(deduction.calculated_amount) : parseFloat(deduction.amount || 0));
    const installment = deduction.calculated_amount ? parseFloat(deduction.calculated_amount) : (deduction.installment_amount ? parseFloat(deduction.installment_amount) : parseFloat(deduction.amount || 0));
    const monthly = installment * 2;
    const remaining = deduction.remaining_balance ? parseFloat(deduction.remaining_balance) : 0;
    
    document.getElementById('deductionTotalAmount').textContent = '₱' + totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('deductionMonthly').textContent = '₱' + monthly.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('deductionInstallment').textContent = '₱' + installment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('deductionRemaining').textContent = '₱' + remaining.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    document.getElementById('deductionStartDate').textContent = deduction.start_date || 'N/A';
    document.getElementById('deductionEndDate').textContent = deduction.end_date || 'N/A';
    
    // Status badge
    const statusBadge = document.getElementById('deductionStatusBadge');
    statusBadge.textContent = deduction.status ? deduction.status.charAt(0).toUpperCase() + deduction.status.slice(1) : 'Active';
    statusBadge.className = 'badge-status ' + (deduction.status === 'active' ? 'processed' : deduction.status === 'pending' ? 'pending' : 'on-hold');
    
    // Remarks
    if (deduction.remarks) {
        document.getElementById('deductionRemarksRow').style.display = 'flex';
        document.getElementById('deductionRemarks').textContent = deduction.remarks;
    } else {
        document.getElementById('deductionRemarksRow').style.display = 'none';
    }
    
    document.getElementById('deductionModal').style.display = 'flex';
}

function exportDeductions() {
    if (deductionsData.length === 0) {
        alert('No deduction records to export.');
        return;
    }
    
    // Create CSV content
    let csv = 'Deduction Type,Category,Monthly Amount,Per Cutoff,Remaining Balance,Total Amount,Start Date,End Date,Status\n';
    
    deductionsData.forEach(d => {
        const installment = d.installment_amount || d.amount || 0;
        const monthly = installment * 2;
        csv += `"${d.deduction_type?.name || 'N/A'}",`;
        csv += `"${d.deduction_type?.category || 'N/A'}",`;
        csv += `"${monthly.toFixed(2)}",`;
        csv += `"${installment.toFixed(2)}",`;
        csv += `"${(d.remaining_balance || 0).toFixed(2)}",`;
        csv += `"${(d.total_amount || d.amount || 0).toFixed(2)}",`;
        csv += `"${d.start_date || 'N/A'}",`;
        csv += `"${d.end_date || 'N/A'}",`;
        csv += `"${d.status || 'N/A'}"\n`;
    });
    
    // Create download link
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'deductions_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
    }
});
</script>
@endsection
