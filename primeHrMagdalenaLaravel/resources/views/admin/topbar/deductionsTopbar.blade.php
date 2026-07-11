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
        <span class="banner-badge">
            <span class="banner-badge-dot" style="background:#c9a227"></span>
            {{ $stats['active_loans'] }} Active Loans
        </span>
        <span class="banner-badge outline">₱{{ number_format($stats['total_outstanding'], 2) }} Outstanding</span>
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="deductionsSearchInput" class="topbar-search-input" placeholder="Search employee, type, or status..." oninput="searchDeductions(this.value)">
        </div>
    </div>
</div>

<style>
.topbar-search-wrap { position: relative; display: flex; align-items: center; }
.topbar-search-icon { position: absolute; left: 12px; color: #8f8daf; pointer-events: none; }
.topbar-search-input {
    background: #fff; border: 1.5px solid transparent; border-radius: 9px;
    padding: 9px 12px 9px 34px; font-size: 12.5px; color: #0b044d; outline: none; width: 260px;
    font-family: inherit; box-shadow: 0 1px 3px rgba(11,4,77,.12);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.topbar-search-input::placeholder { color: #aaa8cc; }
.topbar-search-input:focus { border-color: #0b044d; box-shadow: 0 0 0 3px rgba(11,4,77,.12); }

@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    .topbar-search-wrap { width: 100%; }
    .topbar-search-input { width: 100%; }
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
