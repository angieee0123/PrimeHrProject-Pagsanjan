@extends('layouts.mayor')

@section('title', "Mayor's View · PRIME HRIS")

@push('styles')
    @vite('resources/css/mayor/mayorDashboard.css')
@endpush

@section('content')

@php
    // Figures the cards headline with, computed once here rather than inline.
    $attTotal   = $attendanceToday['on_time'] + $attendanceToday['late'] + $attendanceToday['absent'];
    $leaveTotal = array_sum($leaveBreakdown);
    $deptMax    = max(1, $departments->max('count') ?? 1);
    $pct        = fn ($part, $whole) => $whole > 0 ? round($part / $whole * 100) : 0;

    /* Scale the unit to the number. Dividing everything by a million turned a
       ₱16,536 month into "₱0.02M", which reads as nothing at all. Matches the
       peso() formatter the chart axis and tooltip use. */
    $money = function ($amount) {
        $amount = (float) $amount;
        if ($amount >= 1000000) return '₱' . number_format($amount / 1000000, 2) . 'M';
        if ($amount >= 1000)    return '₱' . number_format($amount / 1000) . 'K';
        return '₱' . number_format($amount);
    };

    // Both series the chart can show, keyed the way the JS switches them.
    // Built here rather than inline in @json(): Blade parses that directive's
    // argument by counting brackets, so a multi-line array literal inside it
    // compiles to mismatched delimiters.
    $chartSeries = [
        'payroll'     => $payrollTrend,
        'designation' => $payrollByDesignation,
        'employees'   => $employeeGrowth,
    ];
@endphp

{{-- Same shell as the admin dashboard: .enterprise-hr-dashboard supplies the
     card/glass treatment, .glass-shell the page background. --}}
<main class="enterprise-hr-dashboard glass-shell">

@include('mayor.topbar.dashboardTopbar')

{{-- ── Stats grid — the admin dashboard's stat-card pattern ── --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card mayor-stat-clickable" tabindex="0"
         onclick="document.getElementById('deptDistribution').scrollIntoView({behavior:'smooth'})"
         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
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

    <div class="stat-card mayor-stat-clickable" tabindex="0"
         onclick="document.getElementById('attendanceCard').scrollIntoView({behavior:'smooth'})"
         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
        <div class="stat-top">
            {{-- The title stays stable; when the records lag, the date is
                 disclosed in the sub-line rather than bracketed into the label. --}}
            <p class="stat-label">{{ $stats['attendance_is_live'] ? 'Present Today' : 'Present' }}</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['present_today']) }}<span class="mayor-stat-of"> / {{ number_format($stats['expected_today']) }}</span></p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#15803d"></span>
            <p class="stat-sub">
                {{ $stats['attendance_rate'] }}% attendance rate
                @unless($stats['attendance_is_live']) · as of {{ $stats['attendance_label'] }} @endunless
            </p>
        </div>
    </div>

    <div class="stat-card mayor-stat-clickable" tabindex="0"
         onclick="document.getElementById('leaveCard').scrollIntoView({behavior:'smooth'})"
         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
        <div class="stat-top">
            {{-- Badge removed: it repeated the pending count that the sub-line
                 below already states. --}}
            <p class="stat-label">On Leave Today</p>
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

    <div class="stat-card mayor-stat-clickable" tabindex="0"
         onclick="document.getElementById('payrollCard').scrollIntoView({behavior:'smooth'})"
         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
        <div class="stat-top">
            <p class="stat-label">Monthly Payroll</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="#0b044d" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
            </div>
        </div>
        <p class="stat-value mayor-stat-value-sm">{{ $money($stats['monthly_payroll']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">{{ $stats['payroll_label'] }} total</p>
        </div>
    </div>
</div>

{{-- ── Overview row — the admin's chart + right-rail arrangement ── --}}
<div class="enterprise-overview-grid">

    {{-- Payroll trend.
         A trend over time for one measure is a line/area, not columns — the
         question is which way the money is moving, not how six discrete
         heights compare. The header carries the latest month and its
         change, so the panel answers itself before you read the marks. --}}
    @php
        // The chart opens on Year (12 months) — the widest read for oversight.
        // The headline mirrors whichever period is showing; the JS recomputes it
        // on switch from the same arrays, so there is one source of truth.
        $openPeriod = 'year';
        $trendData  = $payrollTrend[$openPeriod]['data'] ?? [];
        $trendLast  = count($trendData) ? (float) end($trendData) : 0.0;
        $trendPrev  = count($trendData) > 1 ? (float) $trendData[count($trendData) - 2] : null;
        $trendDelta = ($trendPrev !== null && $trendPrev > 0) ? ($trendLast - $trendPrev) / $trendPrev * 100 : null;
    @endphp
    <div class="chart-card" id="payrollCard">
        <div class="chart-header">
            <div>
                <p class="chart-title" id="payrollChartTitle">Payroll Trend</p>
                <p class="chart-sub" id="payrollChartSub">12 months through {{ $payrollAnchor->format('M Y') }}</p>
            </div>
            <div class="payroll-header-right">
                <div class="payroll-headline">
                    <p class="payroll-headline-value" id="payrollHeadlineValue">{{ $money($trendLast) }}</p>
                    <div class="payroll-headline-meta">
                        {{-- Direction is stated in the text as well as the colour, so
                             the change never rides on hue alone. --}}
                        <span class="payroll-delta {{ $trendDelta === null ? 'is-flat' : ($trendDelta >= 0 ? 'is-up' : 'is-down') }}"
                              id="payrollDelta" @if($trendDelta === null) hidden @endif>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline id="payrollDeltaArrow" points="{{ ($trendDelta ?? 0) >= 0 ? '6 15 12 9 18 15' : '6 9 12 15 18 9' }}"/>
                            </svg>
                            <span id="payrollDeltaText">{{ $trendDelta === null ? '' : (($trendDelta >= 0 ? 'Up ' : 'Down ') . number_format(abs($trendDelta), 1) . '%') }}</span>
                        </span>
                        <span class="payroll-headline-label" id="payrollDeltaLabel">vs {{ $payrollAnchor->copy()->subMonth()->format('M') }}</span>
                    </div>
                </div>
                {{-- Same two-level switch the admin dashboard uses: what to plot,
                     then over what range. --}}
                <div class="chart-tabs" id="mayorChartTabs">
                    <button type="button" class="chart-tab active" data-chart="payroll" onclick="switchMayorChart('payroll')">Payroll</button>
                    <button type="button" class="chart-tab" data-chart="designation" onclick="switchMayorChart('designation')">By Designation</button>
                    <button type="button" class="chart-tab" data-chart="employees" onclick="switchMayorChart('employees')">Employees</button>
                </div>
                <div class="chart-tabs payroll-period-tabs" id="payrollPeriodTabs">
                    <button type="button" class="chart-tab" onclick="switchChartPeriod('week')">Week</button>
                    <button type="button" class="chart-tab" onclick="switchChartPeriod('month')">Month</button>
                    <button type="button" class="chart-tab active" onclick="switchChartPeriod('year')">Year</button>
                </div>
            </div>
        </div>
        <div class="payroll-chart-wrap">
            {{-- The inner box is what Chart.js measures, and it is exactly the
                 canvas's box — see the note in mayorDashboard.css. --}}
            <div class="payroll-chart-inner">
                <canvas id="payrollTrendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Rail card: attendance split --}}
    <div class="table-section" style="margin:0" id="attendanceCard">
        <div class="table-header">
            <div>
                <p class="table-title">{{ $stats['attendance_is_live'] ? "Today's Attendance" : 'Latest Attendance' }}</p>
                {{-- States the denominator: the rate is against those expected in,
                     not raw headcount, so leave and travel don't read as absence. --}}
                <p class="table-sub">
                    {{ $attendanceAnchor->format('F d, Y') }} · {{ $attendanceToday['rate'] }}% of {{ $stats['expected_today'] }} expected
                    @if($stats['on_leave_anchor'] + $stats['on_travel_anchor'] > 0)
                        · {{ $stats['on_leave_anchor'] + $stats['on_travel_anchor'] }} on leave/travel
                    @endif
                </p>
            </div>
        </div>
        <div class="enterprise-card-body mayor-breakdown-body">
            @if($attTotal === 0)
            <div class="mayor-empty">
                <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                <p>No attendance recorded</p>
            </div>
            @else
            {{-- Headline first, then the split. --}}
            <div class="mayor-figure">
                <p class="mayor-figure-value">{{ $attendanceToday['rate'] }}<span class="mayor-figure-unit">%</span></p>
                <p class="mayor-figure-label">{{ $attendanceToday['on_time'] + $attendanceToday['late'] }} of {{ $attTotal }} clocked in</p>
            </div>

            {{-- One track per status rather than a single stacked bar: in a rail
                 this narrow a 1-of-16 segment collapses to an unreadable sliver,
                 whereas its own full-width track still shows the proportion.
                 Reuses the admin dashboard's ranked-row component. --}}
            @foreach([
                ['label' => 'On Time', 'count' => $attendanceToday['on_time'], 'tone' => 'is-good'],
                ['label' => 'Late',    'count' => $attendanceToday['late'],    'tone' => 'is-warn'],
                ['label' => 'Absent',  'count' => $attendanceToday['absent'],  'tone' => 'is-bad'],
            ] as $row)
            <div class="dept-dist-row">
                <span class="dept-dist-swatch {{ $row['tone'] }}"></span>
                <div class="dept-dist-info">
                    <div class="dept-dist-top">
                        <span class="dept-dist-name">{{ $row['label'] }}</span>
                        <span class="dept-dist-count">{{ $row['count'] }}<em>{{ $pct($row['count'], $attTotal) }}%</em></span>
                    </div>
                    <div class="dept-dist-track">
                        <div class="dept-dist-fill {{ $row['tone'] }}" style="width:{{ $pct($row['count'], $attTotal) }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- Rail card: leave split --}}
    <div class="table-section" style="margin:0" id="leaveCard">
        <div class="table-header">
            <div>
                <p class="table-title">Leave Requests</p>
                <p class="table-sub">{{ now()->format('F Y') }} · {{ $leaveTotal }} filed</p>
            </div>
        </div>
        <div class="enterprise-card-body mayor-breakdown-body">
            @if($leaveTotal === 0)
            <div class="mayor-empty">
                <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p>No leave requests filed this month</p>
            </div>
            @else
            {{-- Pending leads the headline: it is the number that needs an action,
                 which is what an oversight view is watching for. --}}
            <div class="mayor-figure">
                <p class="mayor-figure-value">{{ $leaveBreakdown['pending'] }}<span class="mayor-figure-unit">of {{ $leaveTotal }}</span></p>
                <p class="mayor-figure-label">awaiting approval</p>
            </div>

            @foreach([
                ['label' => 'Approved', 'count' => $leaveBreakdown['approved'], 'tone' => 'is-good'],
                ['label' => 'Pending',  'count' => $leaveBreakdown['pending'],  'tone' => 'is-warn'],
                ['label' => 'Rejected', 'count' => $leaveBreakdown['rejected'], 'tone' => 'is-bad'],
            ] as $row)
            <div class="dept-dist-row">
                <span class="dept-dist-swatch {{ $row['tone'] }}"></span>
                <div class="dept-dist-info">
                    <div class="dept-dist-top">
                        <span class="dept-dist-name">{{ $row['label'] }}</span>
                        <span class="dept-dist-count">{{ $row['count'] }}<em>{{ $pct($row['count'], $leaveTotal) }}%</em></span>
                    </div>
                    <div class="dept-dist-track">
                        <div class="dept-dist-fill {{ $row['tone'] }}" style="width:{{ $pct($row['count'], $leaveTotal) }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>
</div>

{{-- ── Department distribution + personnel highlights ── --}}
<div class="mayor-split-grid">

    {{-- Reuses the admin dashboard's Department Distribution rows verbatim. --}}
    <div class="table-section" style="margin:0;display:flex;flex-direction:column" id="deptDistribution">
        <div class="table-header">
            <div>
                <p class="table-title">Department Distribution</p>
                <p class="table-sub">
                    {{ $departments->count() }} staffed {{ Str::plural('department', $departments->count()) }} · {{ number_format($stats['total_employees']) }} employees
                    @if($departments->count() > 6) · scroll for more @endif
                </p>
            </div>
        </div>
        {{-- Six rows visible, the rest reachable by scrolling. flex:1 + min-height:0
             still lets the card shrink to match its neighbour in the grid row. --}}
        <div class="enterprise-card-body mayor-dept-scroll">
            @forelse($departments as $d)
            <div class="dept-dist-row">
                <span class="dept-dist-swatch" style="background:{{ $d['color'] }}"></span>
                <div class="dept-dist-info">
                    <div class="dept-dist-top">
                        <span class="dept-dist-name">{{ $d['name'] }}</span>
                        <span class="dept-dist-count">{{ $d['count'] }}<em>{{ $d['percentage'] }}%</em></span>
                    </div>
                    <div class="dept-dist-track">
                        <div class="dept-dist-fill" style="width:{{ round($d['count'] / $deptMax * 100) }}%;background:{{ $d['color'] }}"></div>
                    </div>
                </div>
            </div>
            @empty
            <div class="mayor-empty">
                <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <p>No department data yet</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Personnel Highlights — one tabbed panel instead of several list cards --}}
    <div class="table-section" style="margin:0">
        <div class="table-header">
            <div>
                <p class="table-title" id="highlightsTitle">Top Attendance Performers</p>
                <p class="table-sub" id="highlightsSub">{{ $perfPeriodMonth }}</p>
            </div>
            <div class="mayor-highlights-tabs">
                <button id="tabHlPerformers" onclick="switchHighlights('performers')" class="chart-tab active">Top Performers</button>
                <button id="tabHlEarners" onclick="switchHighlights('earners')" class="chart-tab">Top Earners</button>
                <button id="tabHlLeave" onclick="switchHighlights('leave')" class="chart-tab">Recent Leave</button>
            </div>
        </div>

        {{-- Top Performers panel --}}
        <div id="panelHlPerformers" class="mayor-panel mayor-panel-scroll">
            <table class="mayor-perf-table">
                <thead>
                    <tr>
                        <th class="mayor-perf-col-rank">#</th>
                        <th>Employee</th>
                        <th>Position</th>
                        <th class="mayor-perf-col-rate">Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPerformers as $i => $emp)
                    <tr>
                        <td>
                            <span class="mayor-perf-rank-td {{ $i < 3 ? 'is-top' : '' }}">{{ $i + 1 }}</span>
                        </td>
                        <td>
                            <div class="mayor-perf-employee">
                                @if($emp['photo'])
                                    <img src="{{ $emp['photo'] }}" alt="" class="mayor-avatar mayor-avatar-sm mayor-avatar-img">
                                @else
                                    <div class="mayor-avatar mayor-avatar-sm" style="background:{{ $emp['color'] }}">{{ $emp['initials'] }}</div>
                                @endif
                                <span class="mayor-perf-name">{{ $emp['name'] }}</span>
                            </div>
                        </td>
                        <td class="mayor-perf-position">{{ $emp['position'] }}</td>
                        <td>
                            <div class="mayor-perf-rate-cell">
                                <div class="mayor-perf-bar-track">
                                    <div class="mayor-perf-bar-fill" style="width:{{ $emp['rate'] }}%"></div>
                                </div>
                                <span class="mayor-perf-rate">{{ $emp['rate'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="mayor-perf-empty">No attendance data yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top Earners panel --}}
        <div id="panelHlEarners" class="mayor-panel-list" style="display:none">
            <div class="mayor-highlight-list">
                @forelse($topEarners as $earner)
                <div class="mayor-highlight-row">
                    <div class="mayor-rank {{ $earner['rank'] <= 3 ? 'is-top' : '' }}">{{ $earner['rank'] }}</div>
                    @if($earner['photo'])
                        <img src="{{ $earner['photo'] }}" alt="" class="mayor-avatar mayor-avatar-img">
                    @else
                        <div class="mayor-avatar" style="background:{{ $earner['color'] }}">{{ $earner['initials'] }}</div>
                    @endif
                    <div class="mayor-highlight-body">
                        <p class="mayor-highlight-name">{{ $earner['name'] }}</p>
                        <p class="mayor-highlight-meta">{{ $earner['designation'] }}</p>
                    </div>
                    <div class="mayor-highlight-amount">
                        <p class="mayor-highlight-amount-value">₱{{ number_format($earner['avg_earnings'], 2) }}</p>
                        <p class="mayor-highlight-amount-label">avg/day</p>
                    </div>
                </div>
                @empty
                <div class="mayor-empty">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <p>No salary data available</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Leave panel --}}
        <div id="panelHlLeave" class="mayor-panel-list" style="display:none">
            <div class="mayor-highlight-list">
                @forelse($recentLeaveFilers as $filer)
                <div class="mayor-highlight-row">
                    @if($filer['photo'])
                        <img src="{{ $filer['photo'] }}" alt="" class="mayor-avatar mayor-avatar-img">
                    @else
                        <div class="mayor-avatar" style="background:{{ $filer['color'] }}">{{ $filer['initials'] }}</div>
                    @endif
                    <div class="mayor-highlight-body">
                        <p class="mayor-highlight-name">{{ $filer['name'] }}</p>
                        <p class="mayor-highlight-meta">{{ $filer['leave_type'] }} · {{ $filer['days'] }} day{{ $filer['days'] > 1 ? 's' : '' }}</p>
                    </div>
                    <div class="mayor-status-pill" style="background:{{ $filer['status_bg'] }};color:{{ $filer['status_color'] }}">{{ $filer['status'] }}</div>
                </div>
                @empty
                <div class="mayor-empty">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <p>No recent leave applications</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

</main>

@push('scripts')
<script>
    window.mayorDashboardData = {
        perfPeriodMonth: @json($perfPeriodMonth),
        payrollAnchorLabel: @json($payrollAnchor->format('F Y')),
        chartSeries: @json($chartSeries),
    };
</script>
    @vite('resources/js/mayor/dashboard/mayorDashboard.js')
@endpush
@endsection
