{{-- Admin Training Verification Topbar (matches permanent training topbar) --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <div>
            <h2>Training Verification</h2>
            <p><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; CSC PDS Section IV · Fiscal Year {{ date('Y') }}</p>
        </div>
    </div>
    <div class="banner-right">
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="adminTrainingSearch" class="topbar-search-input" placeholder="Search employee or training..." oninput="filterAdminTraining()">
        </div>
    </div>
</div>

<style>
input.topbar-search-input[type="text"] {
    background: rgba(255,255,255,.55) !important; border: 1.5px solid rgba(11,4,77,.12) !important; border-radius: 9px !important;
    padding: 9px 12px 9px 34px; font-size: 12.5px; color: #0b044d !important; outline: none; width: 260px;
    font-family: inherit; box-shadow: none;
    backdrop-filter: blur(8px) saturate(160%) !important; -webkit-backdrop-filter: blur(8px) saturate(160%) !important;
    transition: background 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}
input.topbar-search-input[type="text"]::placeholder { color: #aaa8cc; }
input.topbar-search-input[type="text"]:focus {
    background: #fff !important; border-color: #0b044d !important;
    backdrop-filter: none !important; -webkit-backdrop-filter: none !important;
    box-shadow: 0 0 0 3px rgba(11,4,77,.12);
}

@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    
    
}
</style>
