{{-- Attendance Page Header --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/>
            </svg>
        </div>
        <div>
            <h2>Attendance Management</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; PRIME HRIS Admin Panel</p>
        </div>
    </div>
    <div class="banner-right">
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="attendanceSearchInput" class="topbar-search-input" placeholder="Search employee, ID or department..." oninput="searchAttendance(this.value)">
        </div>
    </div>
</div>

<style>
/* ══ Liquid Glass welcome banner — scoped to this page's own <style> tag ══ */
.welcome-banner {
    position: relative;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", 'Poppins', sans-serif !important;
    border-radius: 20px !important;
    border: 1px solid rgba(255, 255, 255, .18) !important;
    background:
        radial-gradient(340px 200px at 100% -20%, rgba(129, 140, 248, .35), transparent 70%),
        linear-gradient(135deg, #0b044d 0%, #150c63 100%) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .16),
        0 16px 40px rgba(11, 4, 77, .28) !important;
}
.welcome-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255, 255, 255, .08), transparent 45%);
    pointer-events: none;
}
.welcome-banner .banner-icon {
    border: 1px solid rgba(255, 255, 255, .22) !important;
    background: rgba(255, 255, 255, .12) !important;
    backdrop-filter: blur(10px) saturate(180%);
    -webkit-backdrop-filter: blur(10px) saturate(180%);
}

.topbar-search-wrap { position: relative; display: flex; align-items: center; }
.topbar-search-icon { position: absolute; left: 12px; color: rgba(255,255,255,.6); pointer-events: none; }
.topbar-search-input {
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); border-radius: 999px;
    backdrop-filter: blur(10px) saturate(180%); -webkit-backdrop-filter: blur(10px) saturate(180%);
    padding: 9px 14px 9px 34px; font-size: 12.5px; color: #fff; outline: none; width: 260px;
    font-family: inherit; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.topbar-search-input::placeholder { color: rgba(255,255,255,.55); }
.topbar-search-input:focus { border-color: rgba(255,255,255,.4); background: rgba(255,255,255,.18); box-shadow: 0 0 0 4px rgba(255,255,255,.1); }
@media (max-width: 768px) {
    .banner-right { flex-wrap: wrap; }
    .topbar-search-wrap { width: 100%; }
    .topbar-search-input { width: 100%; }
}
</style>

<script>
function searchAttendance(query) {
    const searchTerm = query.toLowerCase().trim();
    const tbody = document.querySelector('.payroll-table tbody');
    if (!tbody) return;

    if (!window.allAttendanceRows || window.allAttendanceRows.length === 0) {
        window.allAttendanceRows = Array.from(tbody.querySelectorAll('tr'));
    }

    const filtered = window.allAttendanceRows.filter(row => {
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
