{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
        </div>
        <div>
            <h2>Reports & Analytics</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Municipal Government of Pagsanjan</p>
        </div>
    </div>
    <div class="banner-right">
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-input" class="topbar-search-input" placeholder="Search reports..." oninput="searchReports(this.value)">
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    
    
}
</style>

<script>
function searchReports(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.report-content tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}
</script>
