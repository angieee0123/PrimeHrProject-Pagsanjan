@extends('layouts.mayor')

@section('title', "Mayor's View · PRIME HRIS")

@push('styles')
    @vite('resources/css/mayor/mayorDashboard.css')
@endpush

@section('content')

<main class="enterprise-hr-dashboard">

@include('mayor.topbar.dashboardTopbar')

{{-- Stats Grid --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card mayor-stat-clickable" onclick="document.getElementById('mayor-dept-chart').scrollIntoView({behavior:'smooth'})">
        <div class="stat-top">
            <p class="stat-label">Total Employees</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['total_employees']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">+{{ $stats['new_this_month'] }} new this month</p>
        </div>
    </div>

    <div class="stat-card mayor-stat-clickable" onclick="document.getElementById('mayor-attendance-chart').scrollIntoView({behavior:'smooth'})">
        <div class="stat-top">
            <p class="stat-label">Present {{ $stats['attendance_is_live'] ? 'Today' : '(' . $stats['attendance_label'] . ')' }}</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['present_today']) }}<span style="font-size:14px;color:#8f8daf;font-weight:500"> / {{ number_format($stats['total_employees']) }}</span></p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#15803d"></span>
            <p class="stat-sub">{{ $stats['attendance_rate'] }}% attendance rate</p>
        </div>
    </div>

    <div class="stat-card mayor-stat-clickable" onclick="document.getElementById('mayor-leave-chart').scrollIntoView({behavior:'smooth'})">
        <div class="stat-top">
            <p class="stat-label" style="display:flex;align-items:center;gap:6px">
                On Leave Today
                @if($stats['pending_leave'] > 0)
                <span style="font-size:10px;font-weight:700;color:#fff;background:#8e1e18;padding:2px 7px;border-radius:4px;line-height:1.5">{{ $stats['pending_leave'] }}</span>
                @endif
            </p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['on_leave']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#c9a227"></span>
            <p class="stat-sub">{{ $stats['pending_leave'] }} pending approval</p>
        </div>
    </div>

    <div class="stat-card mayor-stat-clickable" onclick="document.getElementById('mayor-payroll-chart').scrollIntoView({behavior:'smooth'})">
        <div class="stat-top">
            <p class="stat-label">Monthly Payroll</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="#0b044d" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
            </div>
        </div>
        <p class="stat-value" style="font-size:20px">₱{{ number_format($stats['monthly_payroll'] / 1000000, 2) }}M</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">{{ $stats['payroll_label'] }} total</p>
        </div>
    </div>
</div>

{{-- Compact summary charts — 2x2 grid, distinct from admin's full-width trend charts --}}
<div class="mayor-charts-grid">

    {{-- Workforce by Department --}}
    <div class="chart-card mayor-chart-card" id="mayor-dept-chart" style="scroll-margin-top:20px">
        <div class="mayor-chart-card-head">
            <div class="mayor-table-title-row">
                <div class="mayor-section-icon">
                    <svg width="14" height="14" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div>
                    <p class="mayor-chart-card-title">Workforce by Department</p>
                    <p class="mayor-chart-card-sub">{{ number_format($stats['total_employees']) }} employees total</p>
                </div>
            </div>
        </div>
        @if($departments->isEmpty())
        <div style="text-align:center;padding:20px 0">
            <p style="font-size:12px;color:#8f8daf;margin:0">No department data yet</p>
        </div>
        @else
        <div class="mayor-donut-row">
            <div class="mayor-donut-wrap">
                <canvas id="deptDonut"></canvas>
                <div class="mayor-donut-center">
                    <span class="mayor-donut-center-value">{{ $departments->count() }}</span>
                    <span class="mayor-donut-center-label">Depts</span>
                </div>
            </div>
            <div class="mayor-legend-list">
                @foreach($departments->take(5) as $d)
                <div class="mayor-legend-row">
                    <span class="mayor-legend-dot" style="background:{{ $d['color'] }}"></span>
                    <span class="mayor-legend-label">{{ $d['name'] }}</span>
                    <span class="mayor-legend-value">{{ $d['count'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Today's Attendance --}}
    <div class="chart-card mayor-chart-card" id="mayor-attendance-chart" style="scroll-margin-top:20px">
        <div class="mayor-chart-card-head">
            <div class="mayor-table-title-row">
                <div class="mayor-section-icon">
                    <svg width="14" height="14" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="mayor-chart-card-title">{{ $stats['attendance_is_live'] ? "Today's Attendance" : 'Latest Attendance' }}</p>
                    <p class="mayor-chart-card-sub">{{ $attendanceAnchor->format('F d, Y') }}</p>
                </div>
            </div>
        </div>
        <div class="mayor-donut-row">
            <div class="mayor-donut-wrap">
                <canvas id="attendanceDonut"></canvas>
                <div class="mayor-donut-center">
                    <span class="mayor-donut-center-value">{{ $attendanceToday['rate'] }}%</span>
                    <span class="mayor-donut-center-label">Present</span>
                </div>
            </div>
            <div class="mayor-legend-list">
                <div class="mayor-legend-row">
                    <span class="mayor-legend-dot" style="background:#15803d"></span>
                    <span class="mayor-legend-label">On Time</span>
                    <span class="mayor-legend-value">{{ $attendanceToday['on_time'] }}</span>
                </div>
                <div class="mayor-legend-row">
                    <span class="mayor-legend-dot" style="background:#c9a227"></span>
                    <span class="mayor-legend-label">Late</span>
                    <span class="mayor-legend-value">{{ $attendanceToday['late'] }}</span>
                </div>
                <div class="mayor-legend-row">
                    <span class="mayor-legend-dot" style="background:#8e1e18"></span>
                    <span class="mayor-legend-label">Absent</span>
                    <span class="mayor-legend-value">{{ $attendanceToday['absent'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Payroll Trend --}}
    <div class="chart-card mayor-chart-card" id="mayor-payroll-chart" style="scroll-margin-top:20px">
        <div class="mayor-chart-card-head">
            <div class="mayor-table-title-row">
                <div class="mayor-section-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#0b044d" stroke="none"><text x="4" y="17" font-size="14" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
                </div>
                <div>
                    <p class="mayor-chart-card-title">Payroll Trend</p>
                    <p class="mayor-chart-card-sub">6 months through {{ $payrollAnchor->format('M Y') }}</p>
                </div>
            </div>
        </div>
        <div style="height:150px">
            <canvas id="payrollBar"></canvas>
        </div>
    </div>

    {{-- Leave Requests This Month --}}
    <div class="chart-card mayor-chart-card" id="mayor-leave-chart" style="scroll-margin-top:20px">
        <div class="mayor-chart-card-head">
            <div class="mayor-table-title-row">
                <div class="mayor-section-icon">
                    <svg width="14" height="14" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <p class="mayor-chart-card-title">Leave Requests</p>
                    <p class="mayor-chart-card-sub">{{ now()->format('F Y') }}</p>
                </div>
            </div>
        </div>
        @php $leaveTotal = array_sum($leaveBreakdown); @endphp
        @if($leaveTotal === 0)
        <div style="text-align:center;padding:20px 0">
            <p style="font-size:12px;color:#8f8daf;margin:0">No leave requests filed this month</p>
        </div>
        @else
        <div class="mayor-donut-row">
            <div class="mayor-donut-wrap">
                <canvas id="leaveDonut"></canvas>
                <div class="mayor-donut-center">
                    <span class="mayor-donut-center-value">{{ $leaveTotal }}</span>
                    <span class="mayor-donut-center-label">Total</span>
                </div>
            </div>
            <div class="mayor-legend-list">
                <div class="mayor-legend-row">
                    <span class="mayor-legend-dot" style="background:#15803d"></span>
                    <span class="mayor-legend-label">Approved</span>
                    <span class="mayor-legend-value">{{ $leaveBreakdown['approved'] }}</span>
                </div>
                <div class="mayor-legend-row">
                    <span class="mayor-legend-dot" style="background:#c9a227"></span>
                    <span class="mayor-legend-label">Pending</span>
                    <span class="mayor-legend-value">{{ $leaveBreakdown['pending'] }}</span>
                </div>
                <div class="mayor-legend-row">
                    <span class="mayor-legend-dot" style="background:#8e1e18"></span>
                    <span class="mayor-legend-label">Rejected</span>
                    <span class="mayor-legend-value">{{ $leaveBreakdown['rejected'] }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Personnel Highlights — one tabbed panel instead of several separate list panels --}}
<div class="table-section" style="margin:0">
    <div class="table-header" style="background:linear-gradient(135deg,#f2f1fb 0%,#fff 100%)">
        <div class="mayor-table-title-row">
            <div class="mayor-section-icon">
                <svg width="15" height="15" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v5a5 5 0 0 1-10 0z"/><path d="M17 6h2a2 2 0 0 1-2 4"/><path d="M7 6H5a2 2 0 0 0 2 4"/></svg>
            </div>
            <div>
                <p class="table-title" id="highlightsTitle">Top Attendance Performers</p>
                <p class="table-sub" id="highlightsSub">{{ $perfPeriodMonth }}</p>
            </div>
        </div>
        <div class="mayor-highlights-tabs">
            <button id="tabHlPerformers" onclick="switchHighlights('performers')" class="chart-tab active">Top Performers</button>
            <button id="tabHlEarners" onclick="switchHighlights('earners')" class="chart-tab">Top Earners</button>
            <button id="tabHlLeave" onclick="switchHighlights('leave')" class="chart-tab">Recent Leave</button>
        </div>
    </div>

    {{-- Top Performers panel — real data table with column headers --}}
    <div id="panelHlPerformers" style="padding:2px 20px 14px;overflow-x:auto">
        <table class="mayor-perf-table">
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Employee</th>
                    <th>Position</th>
                    <th style="text-align:right">Attendance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topPerformers as $i => $emp)
                <tr>
                    <td>
                        <span class="mayor-perf-rank-td" style="background:{{ $i < 3 ? '#fbf6e3' : '#ecebf6' }};color:{{ $i < 3 ? '#c9a227' : '#8f8daf' }}">{{ $i + 1 }}</span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px">
                            @if($emp['photo'])
                                <img src="{{ $emp['photo'] }}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;border:2px solid #e2e1f0;flex-shrink:0">
                            @else
                                <div style="width:26px;height:26px;border-radius:50%;background:{{ $emp['color'] }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:10px;border:2px solid #e2e1f0;flex-shrink:0">{{ $emp['initials'] }}</div>
                            @endif
                            <span style="font-weight:600;white-space:nowrap">{{ $emp['name'] }}</span>
                        </div>
                    </td>
                    <td style="color:#8f8daf;white-space:nowrap">{{ $emp['position'] }}</td>
                    <td>
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                            <div class="mayor-perf-bar-track">
                                <div class="mayor-perf-bar-fill" style="width:{{ $emp['rate'] }}%"></div>
                            </div>
                            <span style="font-weight:700;min-width:32px;text-align:right">{{ $emp['rate'] }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:20px 0;font-size:12px;color:#8f8daf;border-bottom:none">No attendance data yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Top Earners panel --}}
    <div id="panelHlEarners" style="display:none;padding:6px 20px 18px">
        <div style="display:flex;flex-direction:column">
            @forelse($topEarners as $earner)
            <div class="mayor-highlight-row" style="border-bottom:{{ $earner['rank'] < 5 ? '1px solid #ecebf6' : 'none' }}">
                <div style="min-width:24px;height:24px;border-radius:8px;text-align:center;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;background:{{ $earner['rank'] <= 3 ? '#fbf6e3' : '#ecebf6' }};color:{{ $earner['rank'] <= 3 ? '#c9a227' : '#8f8daf' }}">{{ $earner['rank'] }}</div>
                @if($earner['photo'])
                    <img src="{{ $earner['photo'] }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid #e2e1f0;flex-shrink:0">
                @else
                    <div style="width:32px;height:32px;border-radius:50%;background:{{ $earner['color'] }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;border:2px solid #e2e1f0;flex-shrink:0">{{ $earner['initials'] }}</div>
                @endif
                <div style="flex:1;min-width:0">
                    <p style="font-size:12.5px;font-weight:600;color:#0b044d;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $earner['name'] }}</p>
                    <p style="font-size:11px;color:#56547a;margin:0;font-weight:500">{{ $earner['designation'] }}</p>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <p style="font-size:13px;font-weight:700;color:#0b044d;margin:0">₱{{ number_format($earner['avg_earnings'], 2) }}</p>
                    <p style="font-size:10px;color:#8f8daf;margin:2px 0 0;font-weight:500">avg/day</p>
                </div>
            </div>
            @empty
            <p style="text-align:center;padding:20px 0;margin:0;font-size:12px;color:#8f8daf">No salary data available</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Leave panel --}}
    <div id="panelHlLeave" style="display:none;padding:6px 20px 18px">
        <div style="display:flex;flex-direction:column">
            @forelse($recentLeaveFilers as $i => $filer)
            <div class="mayor-highlight-row" style="border-bottom:{{ $i < count($recentLeaveFilers) - 1 ? '1px solid #ecebf6' : 'none' }}">
                @if($filer['photo'])
                    <img src="{{ $filer['photo'] }}" style="width:32px;height:32px;border-radius:9px;object-fit:cover;border:2px solid #e2e1f0;flex-shrink:0">
                @else
                    <div style="width:32px;height:32px;border-radius:9px;background:{{ $filer['color'] }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;border:2px solid #e2e1f0;flex-shrink:0">{{ $filer['initials'] }}</div>
                @endif
                <div style="flex:1;min-width:0">
                    <p style="font-size:12.5px;font-weight:600;color:#0b044d;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $filer['name'] }}</p>
                    <p style="font-size:11px;color:#56547a;margin:0;font-weight:500">{{ $filer['leave_type'] }} · {{ $filer['days'] }} day{{ $filer['days'] > 1 ? 's' : '' }}</p>
                </div>
                <div style="font-size:10px;padding:4px 8px;border-radius:6px;font-weight:700;background:{{ $filer['status_bg'] }};color:{{ $filer['status_color'] }};flex-shrink:0">{{ $filer['status'] }}</div>
            </div>
            @empty
            <p style="text-align:center;padding:20px 0;margin:0;font-size:12px;color:#8f8daf">No recent leave applications</p>
            @endforelse
        </div>
    </div>
</div>

</main>

@push('scripts')
<script>
    window.mayorDashboardData = {
        perfPeriodMonth: @json($perfPeriodMonth),
        payrollAnchorLabel: @json($payrollAnchor->format('F Y')),
        departments: @json($departments->take(5)->values()),
        attendanceToday: @json($attendanceToday),
        payrollTrend: @json($payrollTrend),
        leaveBreakdown: @json($leaveBreakdown),
    };
</script>
    @vite('resources/js/mayor/dashboard/mayorDashboard.js')
@endpush
@endsection
