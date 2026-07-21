{{-- Stats Grid --}}
<div class="stats-grid stats-grid-4" style="margin-bottom:20px;">
    <div class="stat-card personnel-stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Personnel</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['total'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">All records</p>
        </div>
    </div>

    <div class="stat-card personnel-stat-card">
        <div class="stat-top">
            <p class="stat-label">Active</p>
            <div class="stat-icon-wrap" style="background:#e9f9ef">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['active'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#15803d"></span>
            <p class="stat-sub">Currently active</p>
        </div>
    </div>

    <div class="stat-card personnel-stat-card">
        <div class="stat-top">
            <p class="stat-label">Inactive</p>
            <div class="stat-icon-wrap" style="background:#fdedec">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['inactive'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#8e1e18"></span>
            <p class="stat-sub">Deactivated accounts</p>
        </div>
    </div>

    <div class="stat-card personnel-stat-card">
        <div class="stat-top">
            <p class="stat-label">Permanent</p>
            <div class="stat-icon-wrap" style="background:#fbf6e3">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c9a227" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['permanent'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#c9a227"></span>
            <p class="stat-sub">Permanent employees</p>
        </div>
    </div>
</div>
