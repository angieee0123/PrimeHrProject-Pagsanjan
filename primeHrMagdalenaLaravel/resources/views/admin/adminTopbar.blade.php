<header class="topbar">
    <div class="topbar-left">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">{{ now()->format('l, F j, Y') }} · Fiscal Year {{ now()->year }}</p>
    </div>
    <div class="topbar-right">
        <!-- Notification Bell -->
        <div style="position: relative;">
            <button class="icon-btn" title="Notifications" onclick="toggleNotifications()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span class="notif-badge">3</span>
            </button>
        </div>

        <!-- Process Payroll Button -->
        <button class="btn-run-payroll">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Process Payroll
        </button>
    </div>
</header>

<script>
function toggleNotifications() {
    alert('Notifications feature coming soon!');
}
</script>
