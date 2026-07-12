{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div>
            <h2>Payroll Management</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Semi-Monthly Payroll Processing</p>
        </div>
    </div>
    <div class="banner-right">
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="payrollSearchInput" class="topbar-search-input" placeholder="Search by ID, name, or department..." oninput="searchPayroll(this.value)">
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    
    
}
</style>

<script>
function searchPayroll(query) {
    const searchTerm = query.toLowerCase().trim();
    const tbody = document.querySelector('.payroll-table tbody');
    if (!tbody) return;

    if (!window.allPayrollRows || window.allPayrollRows.length === 0) {
        window.allPayrollRows = Array.from(tbody.querySelectorAll('tr'));
    }

    const filtered = window.allPayrollRows.filter(row => {
        const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
        const id   = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
        return searchTerm === '' || name.includes(searchTerm) || id.includes(searchTerm) || dept.includes(searchTerm);
    });

    tbody.innerHTML = '';
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:40px;color:#56547a;">No records found matching your search.</td></tr>';
    } else {
        filtered.forEach(row => tbody.appendChild(row.cloneNode(true)));
    }
}
</script>
