{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
        </div>
        <div>
            <h2>Travel Order Management</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Official Travel Requests & Approvals</p>
        </div>
    </div>
    <div class="banner-right">
              <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="travelOrderSearchInput" class="topbar-search-input" placeholder="Search employee or destination..." oninput="searchTravelOrders(this.value)">
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    
    
}
</style>

<script>
function searchTravelOrders(query) {
    const searchTerm = query.toLowerCase().trim();
    const activeBtn = document.querySelector('.tab-btn.active');
    if (!activeBtn) return;

    const tabMap = { 'Pending': 'pending', 'Approved': 'approved', 'Disapproved': 'disapproved' };
    const tabId = tabMap[activeBtn.textContent.trim()];
    if (!tabId) return;

    document.querySelectorAll('#' + tabId + '-tab tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (!searchTerm || text.includes(searchTerm)) ? '' : 'none';
    });
}
</script>
