@extends('layouts.app')

@section('content')
@include('admin.topbar.adminTopbar')
@include('admin.notification.adminNotification')

{{-- Stats Grid --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Employees</p>
            <div class="stat-icon-wrap" style="background:#f0effe">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $stats['total_employees'] }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#22c55e"></span>
            <p class="stat-sub">+{{ $stats['new_this_month'] }} new this month</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Present Today</p>
            <div class="stat-icon-wrap" style="background:#e8f9ef">
                <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $stats['present_today'] }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#22c55e"></span>
            <p class="stat-sub">{{ $stats['attendance_rate'] }}% attendance rate</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">On Leave</p>
            <div class="stat-icon-wrap" style="background:#fefce8">
                <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $stats['on_leave'] }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#f59e0b"></span>
            <p class="stat-sub">{{ $stats['pending_leave'] }} pending approval</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Monthly Payroll</p>
            <div class="stat-icon-wrap" style="background:#fdf0ef">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="#8e1e18" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
            </div>
        </div>
        <p class="stat-value" style="font-size:20px">₱{{ number_format($stats['monthly_payroll'] / 1000000, 2) }}M</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">{{ now()->format('F Y') }} payroll</p>
        </div>
    </div>
</div>

{{-- Main Content Grid: Charts + Sidebar --}}
<div style="display:grid; grid-template-columns:1fr 380px; gap:20px; margin-bottom:20px;">
    
    {{-- Left: Charts --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        
        {{-- Top 10 Early Birds / Late Birds --}}
        <div class="table-section" style="margin-bottom:0">
            <div class="table-header" style="padding:16px 20px">
                <div>
                    <p class="table-title" id="birdsTabTitle" style="font-size:14px">🌅 Top 10 Early Birds Today</p>
                    <p class="table-sub" style="font-size:11px;margin-top:2px">{{ now()->format('F d, Y') }}</p>
                </div>
                <div style="display:flex;gap:6px">
                    <button id="tabEarly" onclick="switchBirdsTab('early')" style="font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #0b044d;background:#0b044d;color:#fff;cursor:pointer;font-weight:600">🌅 Early Birds</button>
                    <button id="tabLate" onclick="switchBirdsTab('late')" style="font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #e5e4f0;background:#fff;color:#5a5888;cursor:pointer;font-weight:600">🐦 Late Birds</button>
                </div>
            </div>

            {{-- Early Birds Panel --}}
            <div id="panelEarly" style="padding:8px 20px 16px; display:grid; grid-template-columns:repeat(5, 1fr); gap:12px;">
                @forelse($earlyBirds as $bird)
                <div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:10px;background:#fafafe;border-radius:8px;border:1px solid #f0effe">
                    <div style="position:relative">
                        @if($bird['photo'])
                            <img src="{{ $bird['photo'] }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2.5px solid #e8e7f5">
                        @else
                            <div class="emp-avatar-dynamic" data-bg="{{ $bird['color'] }}" style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:14px;border:2.5px solid #e8e7f5">{{ $bird['initials'] }}</div>
                        @endif
                        <div style="position:absolute;top:-4px;right:-4px;width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,#fbbf24,#f59e0b);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.15)">
                            {{ $bird['rank'] }}
                        </div>
                    </div>
                    <div style="text-align:center;width:100%">
                        <p style="font-size:11.5px;font-weight:600;color:#0b044d;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $bird['name'] }}">{{ $bird['name'] }}</p>
                        <p style="font-size:10px;color:#9999bb;margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $bird['position'] }}">{{ $bird['position'] }}</p>
                        <p style="font-size:12px;font-weight:700;color:#15803d;margin:0;background:#e8f9ef;padding:3px 8px;border-radius:4px;display:inline-block">{{ $bird['time_in'] }}</p>
                    </div>
                </div>
                @empty
                <div style="grid-column:1/-1;text-align:center;padding:30px;color:#9999bb;font-size:12px">
                    No attendance records today
                </div>
                @endforelse
            </div>

            {{-- Late Birds Panel --}}
            <div id="panelLate" style="display:none; padding:8px 20px 16px; display:none; grid-template-columns:repeat(5, 1fr); gap:12px;">
                @forelse($lateBirds as $bird)
                <div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:10px;background:#fff8f8;border-radius:8px;border:1px solid #fde8e8">
                    <div style="position:relative">
                        @if($bird['photo'])
                            <img src="{{ $bird['photo'] }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2.5px solid #fde8e8">
                        @else
                            <div class="emp-avatar-dynamic" data-bg="{{ $bird['color'] }}" style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:14px;border:2.5px solid #fde8e8">{{ $bird['initials'] }}</div>
                        @endif
                        <div style="position:absolute;top:-4px;right:-4px;width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,#f87171,#dc2626);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.15)">
                            {{ $bird['rank'] }}
                        </div>
                    </div>
                    <div style="text-align:center;width:100%">
                        <p style="font-size:11.5px;font-weight:600;color:#0b044d;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $bird['name'] }}">{{ $bird['name'] }}</p>
                        <p style="font-size:10px;color:#9999bb;margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $bird['position'] }}">{{ $bird['position'] }}</p>
                        <p style="font-size:12px;font-weight:700;color:#dc2626;margin:0;background:#fde8e8;padding:3px 8px;border-radius:4px;display:inline-block">{{ $bird['time_in'] }}</p>
                        <p style="font-size:10px;color:#dc2626;margin:4px 0 0;font-weight:600">+{{ $bird['late_minutes'] }} min late</p>
                    </div>
                </div>
                @empty
                <div style="grid-column:1/-1;text-align:center;padding:30px;color:#9999bb;font-size:12px">
                    No late records today 🎉
                </div>
                @endforelse
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <p class="chart-title">Employee Growth</p>
                        <p class="chart-sub">Total employees over time</p>
                    </div>
                    <div class="chart-tabs">
                        <button class="chart-tab" onclick="switchEmployeeChart('week')">Week</button>
                        <button class="chart-tab active" onclick="switchEmployeeChart('month')">Month</button>
                        <button class="chart-tab" onclick="switchEmployeeChart('year')">Year</button>
                    </div>
                </div>
                <canvas id="employeeChart" style="max-height:280px"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <p class="chart-title">Attendance Trends</p>
                        <p class="chart-sub">Daily attendance rate</p>
                    </div>
                    <div class="chart-tabs">
                        <button class="chart-tab" onclick="switchAttendanceChart('week')">Week</button>
                        <button class="chart-tab active" onclick="switchAttendanceChart('month')">Month</button>
                        <button class="chart-tab" onclick="switchAttendanceChart('year')">Year</button>
                    </div>
                </div>
                <canvas id="attendanceChart" style="max-height:280px"></canvas>
            </div>
        </div>

    </div>

    {{-- Right Sidebar --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        
        {{-- Pending Leave Requests --}}
        <div class="table-section" style="margin-bottom:0">
            <div class="table-header">
                <div>
                    <p class="table-title">Pending Leave Requests</p>
                    <p class="table-sub">Requires approval</p>
                </div>
                <button class="btn-export" style="font-size:11px;padding:6px 12px">View All</button>
            </div>
            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr><th>Employee</th><th>Type</th><th>Days</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $l)
                        <tr>
                            <td>
                                <div class="emp-cell">
                                    @if($l['photo'])
                                        <img src="{{ $l['photo'] }}" style="width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid #e8e7f5;">
                                    @else
                                        <div class="emp-avatar emp-avatar-sm emp-avatar-dynamic" data-bg="{{ $l['color'] }}" style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:600; font-size:12px; border:2px solid #e8e7f5;">{{ $l['initials'] }}</div>
                                    @endif
                                    <p class="emp-name" style="margin:0">{{ $l['name'] }}</p>
                                </div>
                            </td>
                            <td><span class="dept-tag" style="font-size:11px">{{ $l['type'] }}</span></td>
                            <td style="font-size:12px;color:#5a5888">{{ $l['days'] }}</td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <form method="POST" action="{{ route('admin.leave.approve', $l['id']) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-activate" style="font-size:10px;padding:4px 8px">✓</button>
                                    </form>
                                    <button class="btn-deactivate" onclick="alert('Reject functionality')" style="font-size:10px;padding:4px 8px">✕</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:20px;color:#9999bb;font-size:12px">No pending requests</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Department Breakdown --}}
        <div class="table-section" style="margin-bottom:0">
            <div class="table-header" style="padding:16px 20px">
                <p class="table-title" style="font-size:13px">Department Breakdown</p>
            </div>
            @php
            $total = $stats['total_employees'];
            @endphp
            <div style="padding:4px 20px 16px">
                @foreach($departments as $d)
                <div style="margin-bottom:10px">
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                        <span style="font-weight:600;color:#0b044d">{{ $d['name'] }}</span>
                        <span style="color:#9999bb">{{ $d['count'] }}</span>
                    </div>
                    <div style="height:6px;background:#f0effe;border-radius:99px;overflow:hidden">
                        <div class="dept-fill" data-w="{{ $total > 0 ? round($d['count']/$total*100) : 0 }}%" data-bg="{{ $d['color'] }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Upcoming Events --}}
        <div class="stat-card" style="margin:0">
            <p class="stat-label" style="margin-bottom:12px">Upcoming Events</p>
            @php
            $events = [
                ['label'=>'Payroll Release','date'=>'Jun 15','color'=>'#0b044d'],
                ['label'=>'CSC Training','date'=>'Jun 18','color'=>'#8e1e18'],
                ['label'=>'Performance Review','date'=>'Jun 25','color'=>'#15803d'],
            ];
            @endphp
            @foreach($events as $ev)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f7f6ff">
                <div class="event-icon event-icon-dynamic" data-bg="{{ $ev['color'] }}">
                    <svg width="14" height="14" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div style="flex:1">
                    <p style="font-size:12.5px;font-weight:600;color:#0b044d;margin:0 0 2px">{{ $ev['label'] }}</p>
                    <p style="font-size:11px;color:#9999bb;margin:0">{{ $ev['date'] }}, {{ now()->year }}</p>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</div>

{{-- Attendance Leaderboard --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    {{-- Top Performers --}}
    <div class="table-section" style="margin:0">
        <div class="table-header" style="padding:16px 20px">
            <div>
                <p class="table-title" style="font-size:14px">🏆 Top 5 Performers</p>
                <p class="table-sub" style="font-size:11px;margin-top:2px">Excellent attendance records</p>
            </div>
            <div style="display:flex;gap:6px">
                <button id="perfTabMonth" onclick="switchPerfTab('month')" style="font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #0b044d;background:#0b044d;color:#fff;cursor:pointer;font-weight:600;transition:all .2s">This Month</button>
                <button id="perfTabWeek"  onclick="switchPerfTab('week')"  style="font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #e5e4f0;background:#fff;color:#5a5888;cursor:pointer;font-weight:600;transition:all .2s">This Week</button>
            </div>
        </div>

        @php
            $top5Month = $attendancePerformanceMonth->whereIn('tier', ['excellent','good'])->take(5)->values();
            $top5Week  = $attendancePerformanceWeek->whereIn('tier',  ['excellent','good'])->take(5)->values();
        @endphp

        <div style="padding:0 20px 20px">
            <div id="perfPanelMonth">
                @forelse($top5Month as $i => $emp)
                @php
                    $borderColor = $i === 0 ? '#fbbf24' : ($i === 1 ? '#94a3b8' : ($i === 2 ? '#fb923c' : '#e8f9ef'));
                    $gradients = [
                        'linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)',
                        'linear-gradient(135deg, #94a3b8 0%, #64748b 100%)',
                        'linear-gradient(135deg, #fb923c 0%, #ea580c 100%)'
                    ];
                @endphp
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:#fff;margin-bottom:6px;border:1.5px solid {{ $borderColor }};transition:all .15s;{{ $i < 3 ? 'box-shadow:0 2px 8px rgba(0,0,0,0.06);' : '' }}" onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='translateX(0)'">
                    <span style="font-size:18px;font-weight:800;color:{{ $i < 3 ? ($i === 0 ? '#fbbf24' : ($i === 1 ? '#94a3b8' : '#fb923c')) : '#9999bb' }};width:28px;text-align:center;flex-shrink:0">{{ $i < 3 ? ['🥇','🥈','🥉'][$i] : ($i + 1) }}</span>
                    @if($emp['photo'])
                        <img src="{{ $emp['photo'] }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid {{ $borderColor }};flex-shrink:0">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;border:2px solid {{ $borderColor }}">{{ $emp['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <p style="font-size:12.5px;font-weight:700;color:#0b044d;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $emp['name'] }}</p>
                        <p style="font-size:10px;color:#9999bb;margin:0">{{ $emp['position'] }}</p>
                    </div>
                    <div style="text-align:right;flex-shrink:0;min-width:70px">
                        <p style="font-size:18px;font-weight:900;color:#15803d;margin:0;line-height:1;{{ $i < 3 ? 'background:'.$gradients[$i].';-webkit-background-clip:text;-webkit-text-fill-color:transparent;' : '' }}">{{ $emp['rate'] }}%</p>
                        <p style="font-size:9px;color:#15803d;margin:2px 0 0;background:#e8f9ef;padding:2px 6px;border-radius:4px;display:inline-block">✓ {{ $emp['present_days'] }}d</p>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:40px 20px;color:#9999bb">
                    <div style="font-size:36px;margin-bottom:8px">📅</div>
                    <p style="font-size:12px;font-weight:600;color:#5a5888;margin:0">No top performers yet</p>
                </div>
                @endforelse
            </div>

            <div id="perfPanelWeek" style="display:none">
                @forelse($top5Week as $i => $emp)
                @php
                    $borderColor = $i === 0 ? '#fbbf24' : ($i === 1 ? '#94a3b8' : ($i === 2 ? '#fb923c' : '#e8f9ef'));
                    $gradients = [
                        'linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)',
                        'linear-gradient(135deg, #94a3b8 0%, #64748b 100%)',
                        'linear-gradient(135deg, #fb923c 0%, #ea580c 100%)'
                    ];
                @endphp
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:#fff;margin-bottom:6px;border:1.5px solid {{ $borderColor }};transition:all .15s;{{ $i < 3 ? 'box-shadow:0 2px 8px rgba(0,0,0,0.06);' : '' }}" onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='translateX(0)'">
                    <span style="font-size:18px;font-weight:800;color:{{ $i < 3 ? ($i === 0 ? '#fbbf24' : ($i === 1 ? '#94a3b8' : '#fb923c')) : '#9999bb' }};width:28px;text-align:center;flex-shrink:0">{{ $i < 3 ? ['🥇','🥈','🥉'][$i] : ($i + 1) }}</span>
                    @if($emp['photo'])
                        <img src="{{ $emp['photo'] }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid {{ $borderColor }};flex-shrink:0">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;border:2px solid {{ $borderColor }}">{{ $emp['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <p style="font-size:12.5px;font-weight:700;color:#0b044d;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $emp['name'] }}</p>
                        <p style="font-size:10px;color:#9999bb;margin:0">{{ $emp['position'] }}</p>
                    </div>
                    <div style="text-align:right;flex-shrink:0;min-width:70px">
                        <p style="font-size:18px;font-weight:900;color:#15803d;margin:0;line-height:1;{{ $i < 3 ? 'background:'.$gradients[$i].';-webkit-background-clip:text;-webkit-text-fill-color:transparent;' : '' }}">{{ $emp['rate'] }}%</p>
                        <p style="font-size:9px;color:#15803d;margin:2px 0 0;background:#e8f9ef;padding:2px 6px;border-radius:4px;display:inline-block">✓ {{ $emp['present_days'] }}d</p>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:40px 20px;color:#9999bb">
                    <div style="font-size:36px;margin-bottom:8px">📅</div>
                    <p style="font-size:12px;font-weight:600;color:#5a5888;margin:0">No top performers yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Needs Attention --}}
    <div class="table-section" style="margin:0">
        <div class="table-header" style="padding:16px 20px">
            <div>
                <p class="table-title" style="font-size:14px">⚠️ Needs Attention</p>
                <p class="table-sub" style="font-size:11px;margin-top:2px">Employees with attendance concerns</p>
            </div>
        </div>

        @php
            $bottom5Month = $attendancePerformanceMonth->whereIn('tier', ['needs_improvement','poor'])->sortBy('rate')->take(5)->values();
            $bottom5Week  = $attendancePerformanceWeek->whereIn('tier',  ['needs_improvement','poor'])->sortBy('rate')->take(5)->values();
        @endphp

        <div style="padding:0 20px 20px">
            <div class="perf-bottom-month">
                @forelse($bottom5Month as $i => $emp)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:#fff;margin-bottom:6px;border:1.5px solid {{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }};transition:all .15s" onmouseover="this.style.borderColor='{{ $emp['tier'] === 'poor' ? '#dc2626' : '#a16207' }}'" onmouseout="this.style.borderColor='{{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }}'">
                    <span style="font-size:16px;width:28px;text-align:center;flex-shrink:0">{{ $emp['tier'] === 'poor' ? '🔴' : '🟡' }}</span>
                    @if($emp['photo'])
                        <img src="{{ $emp['photo'] }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid {{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }};flex-shrink:0">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;border:2px solid {{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }}">{{ $emp['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <p style="font-size:12.5px;font-weight:700;color:#0b044d;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $emp['name'] }}</p>
                        <p style="font-size:10px;color:#9999bb;margin:0">{{ $emp['position'] }}</p>
                    </div>
                    <div style="text-align:right;flex-shrink:0;min-width:70px">
                        <p style="font-size:18px;font-weight:900;color:{{ $emp['tier'] === 'poor' ? '#dc2626' : '#a16207' }};margin:0;line-height:1">{{ $emp['rate'] }}%</p>
                        <p style="font-size:9px;color:{{ $emp['tier'] === 'poor' ? '#dc2626' : '#a16207' }};margin:2px 0 0;background:{{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }};padding:2px 6px;border-radius:4px;display:inline-block">✗ {{ $emp['absent_days'] }}d absent</p>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:40px 20px;color:#9999bb">
                    <div style="font-size:36px;margin-bottom:8px">✅</div>
                    <p style="font-size:12px;font-weight:600;color:#15803d;margin:0">All employees doing great!</p>
                </div>
                @endforelse
            </div>

            <div class="perf-bottom-week" style="display:none">
                @forelse($bottom5Week as $i => $emp)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:#fff;margin-bottom:6px;border:1.5px solid {{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }};transition:all .15s" onmouseover="this.style.borderColor='{{ $emp['tier'] === 'poor' ? '#dc2626' : '#a16207' }}'" onmouseout="this.style.borderColor='{{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }}'">
                    <span style="font-size:16px;width:28px;text-align:center;flex-shrink:0">{{ $emp['tier'] === 'poor' ? '🔴' : '🟡' }}</span>
                    @if($emp['photo'])
                        <img src="{{ $emp['photo'] }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid {{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }};flex-shrink:0">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;border:2px solid {{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }}">{{ $emp['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <p style="font-size:12.5px;font-weight:700;color:#0b044d;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $emp['name'] }}</p>
                        <p style="font-size:10px;color:#9999bb;margin:0">{{ $emp['position'] }}</p>
                    </div>
                    <div style="text-align:right;flex-shrink:0;min-width:70px">
                        <p style="font-size:18px;font-weight:900;color:{{ $emp['tier'] === 'poor' ? '#dc2626' : '#a16207' }};margin:0;line-height:1">{{ $emp['rate'] }}%</p>
                        <p style="font-size:9px;color:{{ $emp['tier'] === 'poor' ? '#dc2626' : '#a16207' }};margin:2px 0 0;background:{{ $emp['tier'] === 'poor' ? '#fde8e8' : '#fefce8' }};padding:2px 6px;border-radius:4px;display:inline-block">✗ {{ $emp['absent_days'] }}d absent</p>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:40px 20px;color:#9999bb">
                    <div style="font-size:36px;margin-bottom:8px">✅</div>
                    <p style="font-size:12px;font-weight:600;color:#15803d;margin:0">All employees doing great!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
{{-- Employee Directory Table - Full Width --}}
<div class="table-section">
    <div class="table-header">
        <div>
            <p class="table-title">Employee Directory</p>
            <p class="table-sub">All active government personnel</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterDept" onchange="applyFilters()">
                <option value="">All Departments</option>
                <option>Administration</option>
                <option>Engineering</option>
                <option>Health</option>
                <option>Finance</option>
                <option>HRMO</option>
            </select>
            <select class="filter-select" id="filterType" onchange="applyFilters()">
                <option value="">All Types</option>
                <option>Permanent</option>
                <option>Job Order</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <button class="modal-btn-primary" onclick="openAddEmployee()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Employee
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr data-dept="{{ $emp['dept'] }}" data-type="{{ $emp['type'] }}">
                    <td>
                        <div class="emp-cell">
                            @if($emp['photo'])
                                <img src="{{ $emp['photo'] }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #e8e7f5;">
                            @else
                                <div class="emp-avatar emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:600; font-size:13px; border:2px solid #e8e7f5;">{{ $emp['initials'] }}</div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $emp['name'] }}</p>
                                <p class="emp-id">{{ $emp['employee_id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="position-cell">{{ $emp['position'] }}</span></td>
                    <td><span class="dept-tag">{{ $emp['dept'] }}</span></td>
                    <td><span class="dept-tag emp-type-tag {{ $emp['type']==='Permanent' ? 'is-permanent' : 'is-joborder' }}">{{ $emp['type'] }}</span></td>
                    <td>
                        @if($emp['status']==='active')
                            <span class="badge-status processed">Active</span>
                        @else
                            <span class="badge-status pending">Inactive</span>
                        @endif
                    </td>
                    <td><button class="btn-view" onclick='viewEmployeeDashboard({{ $emp["id"] ?? 0 }})'>View</button></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:#9999bb;">No employees found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span id="filterCount">Showing <strong>{{ $employees->firstItem() ?? 0 }}–{{ $employees->lastItem() ?? 0 }}</strong> of <strong>{{ $employees->total() }}</strong> employees</span>
        <div class="pagination">
            @if($employees->onFirstPage())
                <button class="page-btn" disabled>‹</button>
            @else
                <a href="{{ $employees->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                @if($page == $employees->currentPage())
                    <button class="page-btn active">{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if($employees->hasMorePages())
                <a href="{{ $employees->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <button class="page-btn" disabled>›</button>
            @endif
        </div>
    </div>
</div>

{{-- View Employee Modal --}}
<div id="viewEmployeeDashboardModal" style="display:none; position:fixed; inset:0; background:rgba(11,4,77,0.6); backdrop-filter:blur(4px); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:900px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px rgba(0,0,0,0.25);">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:24px; border-bottom:1.5px solid #f0effe;">
            <div>
                <h3 style="margin:0 0 4px; font-size:18px; font-weight:700; color:#0b044d;">Employee Details</h3>
                <p id="viewEmployeeDashboardId" style="margin:0; font-size:13px; color:#6b6a8a;"></p>
            </div>
            <button onclick="closeViewDashboardModal()" style="background:none; border:none; font-size:28px; color:#6b6a8a; cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>
        <div id="viewEmployeeDashboardContent" style="padding:24px;">
            <p style="text-align:center; color:#6b6a8a;">Loading...</p>
        </div>
    </div>
</div>

{{-- Add Employee Modal --}}
<div class="modal-overlay" id="addEmployeeModal" onclick="closeAddEmployee()">
    <div class="modal-box" style="max-width:560px" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon" style="background:linear-gradient(135deg,#0b044d,#1a0f6e)">
                    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                </div>
                <div>
                    <span class="modal-eyebrow">EMPLOYEE MANAGEMENT</span>
                    <h3 class="modal-title">Add New Employee</h3>
                    <p class="modal-sub">Fill in the details to register a new employee</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeAddEmployee()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:20px 24px;max-height:65vh;overflow-y:auto">
            <form id="addEmployeeForm" onsubmit="submitAddEmployee(event)">
                <div class="form-section-label">PERSONAL INFORMATION</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="first_name" placeholder="e.g. Juan" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="last_name" placeholder="e.g. Dela Cruz" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-input" name="middle_name" placeholder="e.g. Santos">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth <span class="form-required">*</span></label>
                        <input type="date" class="form-input" name="dob" required>
                    </div>
                </div>

                <div class="form-section-label" style="margin-top:18px">EMPLOYMENT DETAILS</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Type <span class="form-required">*</span></label>
                        <select class="form-input" name="emp_type" required>
                            <option value="">Select type</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Department <span class="form-required">*</span></label>
                        <select class="form-input" name="department" required>
                            <option value="">Select department</option>
                            <option>Administration</option>
                            <option>Engineering</option>
                            <option>Health</option>
                            <option>Finance</option>
                            <option>HRMO</option>
                            <option>General Services</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Position <span class="form-required">*</span></label>
                        <input type="text" class="form-input" name="position" placeholder="e.g. Administrative Officer II" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Hired <span class="form-required">*</span></label>
                        <input type="date" class="form-input" name="date_hired" required>
                    </div>
                </div>

                <div class="form-section-label" style="margin-top:18px">CONTACT INFORMATION</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" name="email" placeholder="e.g. juan@lgu.gov.ph">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-input" name="contact" placeholder="e.g. 09XX-XXX-XXXX">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:16px 24px 24px;border-top:1px solid #e5e4f0">
            <button class="modal-btn-ghost" onclick="closeAddEmployee()">Cancel</button>
            <button class="modal-btn-primary" onclick="submitAddEmployee(event)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Employee
            </button>
        </div>
    </div>
</div>

<script>
function switchPerfTab(tab) {
    const isMonth = tab === 'month';
    document.getElementById('perfPanelMonth').style.display = isMonth ? 'block' : 'none';
    document.getElementById('perfPanelWeek').style.display  = isMonth ? 'none'  : 'block';
    document.querySelector('.perf-bottom-month').style.display = isMonth ? 'block' : 'none';
    document.querySelector('.perf-bottom-week').style.display  = isMonth ? 'none'  : 'block';
    document.getElementById('perfTabMonth').style.cssText = isMonth
        ? 'font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #0b044d;background:#0b044d;color:#fff;cursor:pointer;font-weight:600;transition:all .2s'
        : 'font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #e5e4f0;background:#fff;color:#5a5888;cursor:pointer;font-weight:600;transition:all .2s';
    document.getElementById('perfTabWeek').style.cssText = isMonth
        ? 'font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #e5e4f0;background:#fff;color:#5a5888;cursor:pointer;font-weight:600;transition:all .2s'
        : 'font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #0b044d;background:#0b044d;color:#fff;cursor:pointer;font-weight:600;transition:all .2s';
}


function switchBirdsTab(tab) {
    const isEarly = tab === 'early';
    document.getElementById('panelEarly').style.display = isEarly ? 'grid' : 'none';
    document.getElementById('panelLate').style.display = isEarly ? 'none' : 'grid';
    document.getElementById('birdsTabTitle').textContent = isEarly ? '🌅 Top 10 Early Birds Today' : '🐦 Top 10 Late Birds Today';
    document.getElementById('tabEarly').style.cssText = isEarly
        ? 'font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #0b044d;background:#0b044d;color:#fff;cursor:pointer;font-weight:600'
        : 'font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #e5e4f0;background:#fff;color:#5a5888;cursor:pointer;font-weight:600';
    document.getElementById('tabLate').style.cssText = isEarly
        ? 'font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #e5e4f0;background:#fff;color:#5a5888;cursor:pointer;font-weight:600'
        : 'font-size:11px;padding:5px 14px;border-radius:6px;border:1.5px solid #dc2626;background:#dc2626;color:#fff;cursor:pointer;font-weight:600';
}

const employeeData = @json($chartData['employees']);
const attendanceData = @json($chartData['attendance']);

function initCharts() {
    const ctx1 = document.getElementById('employeeChart').getContext('2d');
    const ctx2 = document.getElementById('attendanceChart').getContext('2d');

    employeeChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: employeeData.month.labels,
            datasets: [{
                label: 'Total Employees',
                data: employeeData.month.data,
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
                    displayColors: false
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f7f6ff', drawBorder: false },
                    ticks: { color: '#9999bb', font: { size: 11, family: 'Poppins' } }
                },
                x: { 
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#9999bb', font: { size: 11, family: 'Poppins' } }
                }
            }
        }
    });

    attendanceChart = new Chart(ctx2, {
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
                    displayColors: false
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    max: 120, 
                    grid: { color: '#f7f6ff', drawBorder: false },
                    ticks: { color: '#9999bb', font: { size: 11, family: 'Poppins' } }
                },
                x: { 
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#9999bb', font: { size: 11, family: 'Poppins' } }
                }
            }
        }
    });
}

function switchEmployeeChart(period) {
    const chartCard = document.getElementById('employeeChart').closest('.chart-card');
    chartCard.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    employeeChart.data.labels = employeeData[period].labels;
    employeeChart.data.datasets[0].data = employeeData[period].data;
    employeeChart.update();
}

function switchAttendanceChart(period) {
    const chartCard = document.getElementById('attendanceChart').closest('.chart-card');
    chartCard.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    attendanceChart.data.labels = attendanceData[period].labels;
    attendanceChart.data.datasets[0].data = attendanceData[period].data;
    attendanceChart.update();
}

window.addEventListener('load', initCharts);

function viewEmployeeDashboard(employeeId) {
    document.getElementById('viewEmployeeDashboardModal').style.display = 'flex';
    document.getElementById('viewEmployeeDashboardContent').innerHTML = '<p style="text-align:center; color:#6b6a8a;">Loading...</p>';
    
    fetch(`/admin/personnel/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewEmployeeDashboardId').textContent = data.employee_id;
            document.getElementById('viewEmployeeDashboardContent').innerHTML = generateEmployeeViewDashboard(data);
        })
        .catch(error => {
            document.getElementById('viewEmployeeDashboardContent').innerHTML = '<p style="text-align:center; color:#8e1e18;">Error loading employee details.</p>';
        });
}

function closeViewDashboardModal() {
    document.getElementById('viewEmployeeDashboardModal').style.display = 'none';
}

function generateEmployeeViewDashboard(data) {
    return `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">👤 Personal Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Full Name</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.first_name} ${data.middle_name || ''} ${data.last_name} ${data.suffix || ''}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Date of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.birth_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Place of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.place_of_birth || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Sex</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.sex || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Civil Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.civil_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Citizenship</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.citizenship || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Blood Type</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.blood_type || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">💼 Employment Details</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Designation</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.designation_relation?.title || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Department</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.department_relation?.name || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Employment Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.employment_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Appointment Date</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.appointment_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Salary Grade</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.salary_grade || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Step Increment</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.step_increment || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">📞 Contact Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Email</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.email || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Mobile Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.mobile_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Landline</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.landline_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Emergency Contact</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_person || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Emergency Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_number || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">🪪 Government IDs</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">GSIS Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.gsis_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">PhilHealth Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.philhealth_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">PAG-IBIG Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.pagibig_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">TIN Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.tin_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">License Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.license_no || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div>
            <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">📍 Address</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">House No.</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.house_no || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Street</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.street || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Barangay</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.barangay || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">City/Municipality</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.city || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Province</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.province || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Zip Code</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.zip_code || 'N/A'}</span></div>
            </div>
        </div>
    `;
}

function applyFilters() {
    const dept = document.getElementById('filterDept').value;
    const type = document.getElementById('filterType').value;
    const rows = document.querySelectorAll('.payroll-table tbody tr[data-dept]');
    let visible = 0;
    rows.forEach(row => {
        const matchDept = !dept || row.dataset.dept === dept;
        const matchType = !type || row.dataset.type === type;
        const show = matchDept && matchType;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const total = rows.length;
    document.getElementById('filterCount').innerHTML =
        visible === total
            ? 'Showing <strong>1–' + total + '</strong> of <strong>' + total + '</strong> employees'
            : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> employees';
}

function openAddEmployee() {
    document.getElementById('addEmployeeModal').classList.add('show');
}

function closeAddEmployee() {
    document.getElementById('addEmployeeModal').classList.remove('show');
    document.getElementById('addEmployeeForm').reset();
}

function submitAddEmployee(e) {
    e.preventDefault();
    const form = document.getElementById('addEmployeeForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const data = Object.fromEntries(new FormData(form));
    alert('Employee added successfully!\\n\\n' + data.first_name + ' ' + data.last_name + ' (' + data.emp_type + ')\\n' + data.position + ' · ' + data.department);
    closeAddEmployee();
}

function viewEmployee(emp) {
    document.getElementById('viewEmpName').textContent = emp.name;
    document.getElementById('viewEmpId').textContent = emp.id;
    document.getElementById('viewPosition').textContent = emp.position;
    document.getElementById('viewDept').textContent = emp.dept;
    document.getElementById('viewType').textContent = emp.type;
    document.getElementById('viewStatus').textContent = emp.status.charAt(0).toUpperCase() + emp.status.slice(1);
    document.getElementById('viewEmployeeModal').classList.add('show');
}

function closeViewEmployee() {
    document.getElementById('viewEmployeeModal').classList.remove('show');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeAddEmployee();
        closeViewEmployee();
    }
});

// Apply dynamic colors (avoid Blade-in-style lint issues)
document.querySelectorAll('.emp-avatar-dynamic, .event-icon-dynamic, .dept-fill').forEach(el => {
    const bg = el.dataset.bg;
    if (bg) el.style.backgroundColor = bg;
    const w = el.dataset.w;
    if (w) el.style.width = w;
});
</script>
@endsection