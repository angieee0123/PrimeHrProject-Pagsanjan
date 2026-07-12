{{-- Permanent Payslip Topbar --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div>
            <h2>My Payslips</h2>
            <p><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; {{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }} · {{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }} · {{ $employee->employee_id ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="banner-right">
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="employeePayslipSearch" class="topbar-search-input" placeholder="Search period or status..." oninput="filterPermanentPayslip(this.value)">
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    
    
}
</style>
