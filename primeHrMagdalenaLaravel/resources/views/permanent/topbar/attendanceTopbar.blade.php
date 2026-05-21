{{-- Permanent Attendance Topbar --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
            <h2>My Attendance</h2>
            <p><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; {{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }} · {{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }} · {{ $employee->employee_id ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="banner-right">
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="permanentAttendanceSearch" class="topbar-search-input" placeholder="Search date or status..." oninput="filterPermanentAttendanceTable(this.value)">
        </div>
    </div>
</div>

<style>
.topbar-search-wrap { position:relative; display:flex; align-items:center; }
.topbar-search-icon { position:absolute; left:10px; color:#6b6a8a; pointer-events:none; }
.topbar-search-input { background:#fff; border:1.5px solid #e5e4f0; border-radius:9px; padding:8px 12px 8px 32px; font-size:12.5px; color:#0b044d; outline:none; width:300px; font-family:'Poppins',sans-serif; transition:border-color 0.2s, box-shadow 0.2s; box-shadow:0 1px 3px rgba(11,4,77,0.06); }
.topbar-search-input::placeholder { color:#aaa8cc; }
.topbar-search-input:focus { border-color:#0b044d; box-shadow:0 0 0 3px rgba(11,4,77,0.1); }
@media (max-width: 768px) {
    .welcome-banner { flex-direction:column; align-items:flex-start; gap:12px; }
    .banner-right { width:100%; }
    .topbar-search-wrap { width:100%; }
    .topbar-search-input { width:100%; }
}
</style>
