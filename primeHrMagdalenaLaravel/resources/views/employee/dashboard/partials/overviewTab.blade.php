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
        <div class="perm-chart-canvas-wrap">
            <canvas id="attendanceChart"></canvas>
        </div>
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
        <div class="perm-chart-canvas-wrap">
            <canvas id="salaryChart"></canvas>
        </div>
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
