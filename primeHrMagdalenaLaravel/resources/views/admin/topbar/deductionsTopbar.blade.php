{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
        </div>
        <div>
            <h2>Deductions Management</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Payroll Deductions & Loan Tracking</p>
        </div>
    </div>
    <div class="banner-right">
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="deductionsSearchInput" class="topbar-search-input" placeholder="Search employee, type, or status..." oninput="searchDeductions(this.value)">
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    
    
}
</style>

<script>
function searchDeductions(query) {
    const searchTerm = query.toLowerCase().trim();
    const activeBtn = document.querySelector('.tab-btn.active');
    if (!activeBtn) return;

    const tabMap = {
        'Deduction Types': 'deduction-types',
        'Employee Deductions': 'employee-deductions',
        'Loans': 'loans',
        'Schedules': 'schedules',
        'Loan Types': 'loan-types',
        'Transactions': 'transactions'
    };
    const tabId = tabMap[activeBtn.textContent.trim()];
    if (!tabId) return;

    document.querySelectorAll('#' + tabId + '-tab tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (!searchTerm || text.includes(searchTerm)) ? '' : 'none';
    });
}
</script>
