{{-- Dashboard tabs · switchDashboardTab() in employeeDashboard.js does the toggling.
     Both panels stay in the DOM (hidden with CSS) so the deduction view switcher
     and the export button still find their elements while tab 2 is off-screen. --}}
<div class="perm-tabs" role="tablist" aria-label="Dashboard sections">
    <button type="button"
            class="perm-tab active"
            role="tab"
            id="tab-btn-overview"
            aria-controls="tab-panel-overview"
            aria-selected="true"
            data-tab="overview"
            onclick="switchDashboardTab('overview')">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Overview
        @if($pendingLeaveCount > 0)
            <span class="stat-pill-alert">{{ $pendingLeaveCount }}</span>
        @endif
    </button>

    <button type="button"
            class="perm-tab"
            role="tab"
            id="tab-btn-payroll"
            aria-controls="tab-panel-payroll"
            aria-selected="false"
            data-tab="payroll"
            onclick="switchDashboardTab('payroll')">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Payroll &amp; Deductions
        @if($deductions->count() > 0)
            <span class="perm-tab-count">{{ $deductions->count() }}</span>
        @endif
    </button>
</div>
