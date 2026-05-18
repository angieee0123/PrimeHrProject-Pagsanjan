{{-- Permanent Training Topbar --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <div>
            <h2>Learning &amp; Development</h2>
            <p><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; CSC PDS Section IV · Fiscal Year {{ date('Y') }}</p>
        </div>
    </div>
    <div class="banner-right">
        @php
            $totalHrs = $stats['total_hours'] ?? 0;
            $goalPct = min(100, round(($totalHrs / 40) * 100));
        @endphp
        <span class="banner-badge" id="bannerGoalBadge">
            <span class="banner-badge-dot banner-badge-dot-amber"></span>
            <span id="bannerGoalPct">{{ $goalPct }}%</span> of 40-hr goal
        </span>
        <span class="banner-badge" id="bannerVerifiedCount">
            <span class="banner-badge-dot banner-badge-dot-success"></span>
            {{ $stats['verified'] ?? 0 }} Verified
        </span>
        <span class="banner-badge outline" id="bannerPendingCount">{{ $stats['pending'] ?? 0 }} Pending</span>
        <div class="training-topbar-search">
            <svg class="training-topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="permanentTrainingSearch" class="training-topbar-search-input" placeholder="Search title or status..." oninput="filterPermanentTraining()">
        </div>
    </div>
</div>
