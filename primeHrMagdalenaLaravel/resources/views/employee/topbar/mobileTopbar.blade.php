{{-- Reusable mobile topbar for permanent employee pages --}}
<div class="permanent-mobile-topbar">
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>
    <div class="permanent-mobile-topbar-copy">
        <span>{{ $mobileTopbarEyebrow ?? 'PRIME HRIS' }}</span>
        <strong>{{ $mobileTopbarTitle ?? 'Dashboard' }}</strong>
    </div>
    <div class="permanent-mobile-avatar">{{ $authInitials ?? 'PE' }}</div>
</div>
