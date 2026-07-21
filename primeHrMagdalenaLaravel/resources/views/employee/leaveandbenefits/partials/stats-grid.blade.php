{{-- Stats Grid --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Filed</p>
            <div class="stat-icon-wrap stat-icon-wrap-primary"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        </div>
        <h2 class="stat-value">{{ $leaveApplications->count() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-primary"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Days Used</p>
            <div class="stat-icon-wrap stat-icon-wrap-danger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        </div>
        <h2 class="stat-value">{{ number_format($leaveApplications->where('status', 'approved')->sum('number_of_days'), 0) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-danger"></span>
            <p class="stat-sub">Across all types</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Requests</p>
            <div class="stat-icon-wrap stat-icon-wrap-warning"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        </div>
        <h2 class="stat-value">{{ $leaveApplications->where('status', 'pending')->count() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-amber"></span>
            <p class="stat-sub">Awaiting approval</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">VL + SL Balance</p>
            <div class="stat-icon-wrap stat-icon-wrap-success"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        </div>
        @php
            $vlBalance = $leaveTypes->firstWhere('leave_code', 'VL')?->leaveBalances->first()?->available_credits ?? 0;
            $slBalance = $leaveTypes->firstWhere('leave_code', 'SL')?->leaveBalances->first()?->available_credits ?? 0;
            $totalBalance = $vlBalance + $slBalance;
        @endphp
        <h2 class="stat-value">{{ number_format($totalBalance, 0) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-success"></span>
            <p class="stat-sub">{{ number_format($vlBalance, 0) }} VL · {{ number_format($slBalance, 0) }} SL</p>
        </div>
    </div>
</div>
