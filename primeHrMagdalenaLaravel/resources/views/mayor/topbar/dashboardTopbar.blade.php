{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </div>
        @php
            $mayorEmployee = Auth::check() ? Auth::user()->employee : null;
            $mayorName = $mayorEmployee ? trim($mayorEmployee->first_name . ' ' . $mayorEmployee->last_name) : 'Mayor';
        @endphp
        <div>
            <h2>Welcome, {{ $mayorName }}</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; PRIME HRIS Oversight Dashboard</p>
        </div>
    </div>
    <div class="banner-right">
        <span class="banner-badge">
            <span class="banner-badge-dot mayor-live-dot"></span>
            Live · Updated {{ now()->format('h:i A') }}
        </span>
        <span class="banner-badge outline">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:-2px"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="7" r="4"/></svg>
            View Only Access
        </span>
    </div>
</div>

<style>
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
.welcome-banner {
    position: relative;
    overflow: hidden;
    border-radius: 20px !important;
    border: 1px solid rgba(255, 255, 255, .18) !important;
    background: #0b044d !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .16), 0 16px 40px rgba(11, 4, 77, .28) !important;
}
.banner-icon {
    border: 1px solid rgba(255, 255, 255, .22) !important;
    background: rgba(255, 255, 255, .12) !important;
    backdrop-filter: blur(10px) saturate(180%);
    -webkit-backdrop-filter: blur(10px) saturate(180%);
}
.banner-badge {
    border: 1px solid rgba(255, 255, 255, .16);
    background: rgba(255, 255, 255, .12) !important;
    backdrop-filter: blur(10px) saturate(180%);
    -webkit-backdrop-filter: blur(10px) saturate(180%);
}
.mayor-live-dot { animation: pulse 2s ease-in-out infinite; }
</style>
