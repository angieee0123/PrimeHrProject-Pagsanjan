<div style="background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding: 24px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #fff;">Attendance Management</h3>
        <p style="margin: 0; font-size: 13px; color: rgba(255,255,255,0.7);">{{ now()->format('l, F j, Y') }} · Municipal Government of Pagsanjan</p>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        <!-- Search Bar -->
        <div style="position: relative;">
            <input type="text" id="attendanceSearchInput" placeholder="Search by ID, name, or department..." style="width: 320px; padding: 10px 40px 10px 16px; border: none; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; background: #fff;" oninput="searchAttendance(this.value)" />
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </div>

        <!-- Notification Bell -->
        <button style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; position: relative;" title="Notifications" onclick="toggleNotifications()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span style="position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #0b044d;"></span>
        </button>
    </div>
</div>

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
        const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
        return searchTerm === '' || name.includes(searchTerm) || id.includes(searchTerm) || dept.includes(searchTerm);
    });

    tbody.innerHTML = '';
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 40px; color: #6b6a8a;">No records found matching your search.</td></tr>';
    } else {
        filtered.forEach(row => tbody.appendChild(row.cloneNode(true)));
    }
}
</script>
