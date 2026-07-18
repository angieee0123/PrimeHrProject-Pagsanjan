{{-- Stats --}}
<div class="stats-grid stats-grid-4" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Departments</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $departments->count() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d;"></span>
            <p class="stat-sub">All offices</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Personnel</p>
            <div class="stat-icon-wrap" style="background:#e8f9ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPersonnel }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#15803d;"></span>
            <p class="stat-sub">Across all offices</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active Offices</p>
            <div class="stat-icon-wrap" style="background:#fbf6e3;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $activeDepts }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#c9a227;"></span>
            <p class="stat-sub">Operational units</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Largest Office</p>
            <div class="stat-icon-wrap" style="background:#fdf0ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $largestDept ? $largestDept->personnel_count : 0 }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#8e1e18;"></span>
            <p class="stat-sub">{{ $largestDept ? $largestDept->code : 'N/A' }}</p>
        </div>
    </div>
</div>
