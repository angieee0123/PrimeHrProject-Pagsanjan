<div style="background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding: 24px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #fff;">Personnel Management</h3>
        <p style="margin: 0; font-size: 13px; color: rgba(255,255,255,0.7);">{{ now()->format('l, F j, Y') }} · Municipal Government of Pagsanjan</p>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        <!-- Search Bar -->
        <div style="position: relative;">
            <input type="text" id="personnelSearchInput" placeholder="Search by ID, name, or position..." style="width: 320px; padding: 10px 40px 10px 16px; border: none; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; background: #fff;" oninput="searchPersonnel(this.value)" />
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </div>

        <!-- Notification System -->
        <div class="personnel-notif-wrap" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
            <button class="personnel-notif-btn" id="personnelNotifBtn" onclick="togglePersonnelNotif()" style="background: rgba(11,4,77,0.9); border: none; color: #fff; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; position: relative; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" title="Notifications">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="personnel-notif-dot" id="personnelNotifDot" style="position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #0b044d; display: none;"></span>
            </button>
            <div class="personnel-notif-panel" id="personnelNotifPanel" style="position: absolute; top: 48px; right: 0; width: 420px; background: #fff; border-radius: 16px; box-shadow: 0 12px 32px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05); display: none; flex-direction: column; overflow: hidden; z-index: 1001;">
                <div class="personnel-notif-head" style="padding: 12px 16px; border-bottom: 1px solid #f0effe; display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 style="font-size: 13px; font-weight: 700; color: #0b044d; margin: 0 0 2px;">Notifications</h3>
                        <p style="font-size: 11px; color: #7c7c99; margin: 0;">You have <span id="personnelUnreadCount" style="font-weight: 600; color: #0b044d;">3</span> unread messages</p>
                    </div>
                    <button class="personnel-notif-clear" onclick="clearPersonnelNotifs()" style="width: 28px; height: 28px; border-radius: 8px; background: none; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; color: #7c7c99;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
                <div class="personnel-notif-body" id="personnelNotifBody" style="max-height: 420px; overflow-y: auto; padding: 8px;">
                    <div class="personnel-notif-card new" style="background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border: 1px solid #d9d9ee; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 8px; transition: all 0.2s; cursor: pointer;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #d9bb00, #f59e0b);">
                                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px;">New Employee Onboarded</h4>
                            <p style="font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px;">Maria Santos has been successfully added to the system</p>
                            <span style="font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                1 hour ago
                            </span>
                        </div>
                    </div>
                    <div class="personnel-notif-card new" style="background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border: 1px solid #d9d9ee; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 8px; transition: all 0.2s; cursor: pointer;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #15803d, #22c55e);">
                                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px;">Payroll Processing Complete</h4>
                            <p style="font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px;">June 2025 payroll has been processed for 245 employees</p>
                            <span style="font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                3 hours ago
                            </span>
                        </div>
                    </div>
                    <div class="personnel-notif-card new" style="background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border: 1px solid #d9d9ee; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 8px; transition: all 0.2s; cursor: pointer;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #0369a1, #0ea5e9);">
                                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px;">Contract Expiring Soon</h4>
                            <p style="font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px;">5 job order contracts will expire within 30 days</p>
                            <span style="font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                1 day ago
                            </span>
                        </div>
                    </div>
                    <div class="personnel-notif-card" style="background: #fafafe; border: 1px solid #f0effe; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 0; transition: all 0.2s; cursor: pointer;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #6b3fa0, #7c4fc0);">
                                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px;">Training Completed</h4>
                            <p style="font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px;">15 employees completed Safety Training Program</p>
                            <span style="font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                2 days ago
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.personnel-notif-btn:hover { background: rgba(11,4,77,1) !important; transform: scale(1.05); }
.personnel-notif-clear:hover { background: #f7f6ff !important; color: #ef4444 !important; }
.personnel-notif-card:hover { background: #f7f6ff !important; border-color: #e5e5f0 !important; }
.personnel-notif-dot.active { display: block !important; animation: notifPulse 2s infinite; }
@keyframes notifPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.personnel-notif-panel.open { display: flex !important; animation: notifFadeIn 0.25s ease; }
@keyframes notifFadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 480px) {
    .personnel-notif-panel { position: fixed !important; top: 68px !important; right: 12px !important; left: 12px !important; width: auto !important; max-height: calc(100vh - 90px) !important; }
}
</style>

<script>
function togglePersonnelNotif() {
    document.getElementById('personnelNotifPanel').classList.toggle('open');
}

function clearPersonnelNotifs() {
    document.getElementById('personnelNotifBody').innerHTML = '<div style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center;"><svg width="40" height="40" fill="none" stroke="#d9d9ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="margin-bottom: 10px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><p style="font-size: 12px; color: #9999bb; margin: 0;">No notifications</p></div>';
    updatePersonnelNotifCount();
}

function updatePersonnelNotifCount() {
    const newCount = document.querySelectorAll('.personnel-notif-card.new').length;
    const dot = document.getElementById('personnelNotifDot');
    const countSpan = document.getElementById('personnelUnreadCount');
    if (countSpan) countSpan.textContent = newCount;
    if (dot) dot.classList.toggle('active', newCount > 0);
}

document.addEventListener('click', (e) => {
    const wrap = document.querySelector('.personnel-notif-wrap');
    const panel = document.getElementById('personnelNotifPanel');
    if (wrap && panel && !wrap.contains(e.target)) {
        panel.classList.remove('open');
    }
});

window.addEventListener('load', updatePersonnelNotifCount);

function searchPersonnel(query) {
    const searchTerm = query.toLowerCase().trim();

    if (!window.allRows || window.allRows.length === 0) {
        const tbody = document.getElementById('personnelTableBody');
        if (tbody) {
            window.allRows = Array.from(tbody.querySelectorAll('tr'));
        }
    }

    if (!window.allRows) return;

    const filteredRows = window.allRows.filter(row => {
        const empName = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
        const empId = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const position = row.querySelector('.position-cell')?.textContent.toLowerCase() || '';

        return searchTerm === '' ||
               empName.includes(searchTerm) ||
               empId.includes(searchTerm) ||
               position.includes(searchTerm);
    });

    const tbody = document.getElementById('personnelTableBody');
    if (tbody) {
        tbody.innerHTML = '';

        if (filteredRows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #6b6a8a;">No employees found matching your search.</td></tr>';
        } else {
            filteredRows.forEach(row => tbody.appendChild(row.cloneNode(true)));
        }
    }

    const showingStart = document.getElementById('showingStart');
    const showingEnd = document.getElementById('showingEnd');
    const totalRecords = document.getElementById('totalRecords');

    if (showingStart && showingEnd && totalRecords) {
        showingStart.textContent = filteredRows.length > 0 ? '1' : '0';
        showingEnd.textContent = filteredRows.length;
        totalRecords.textContent = filteredRows.length;
    }

    if (typeof window.currentPage !== 'undefined') {
        window.currentPage = 1;
    }

    const paginationControls = document.getElementById('paginationControls');
    if (paginationControls) {
        paginationControls.style.display = searchTerm === '' ? '' : 'none';
    }
}
</script>
