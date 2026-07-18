{{-- Stats Grid --}}
<div class="stats-grid stats-grid-4">

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Latest Net Pay</p>
            <div class="stat-icon-wrap stat-icon-wrap-success">
                <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <p class="stat-value stat-value-compact">₱{{ number_format($stats['latest_net_pay'], 2) }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-success"></span>
            <p class="stat-sub">{{ $latestPayslip ? $latestPayslip->period_start->format('M d') . '-' . $latestPayslip->period_end->format('d, Y') : 'No data' }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Basic Pay</p>
            <div class="stat-icon-wrap stat-icon-wrap-primary">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <p class="stat-value stat-value-compact">₱{{ number_format($stats['basic_pay'], 2) }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-primary"></span>
            <p class="stat-sub">Semi-monthly rate</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Deductions</p>
            <div class="stat-icon-wrap stat-icon-wrap-danger">
                <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <p class="stat-value stat-value-compact">₱{{ number_format($stats['total_deductions'], 2) }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-danger"></span>
            <p class="stat-sub">Late, Undertime, Others</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Payslips</p>
            <div class="stat-icon-wrap stat-icon-wrap-warning">
                <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $stats['total_payslips'] }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-amber"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>

</div>
