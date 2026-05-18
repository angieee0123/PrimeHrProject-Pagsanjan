<div style="background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding: 24px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #fff;">Leave & Benefits Management</h3>
        <p style="margin: 0; font-size: 13px; color: rgba(255,255,255,0.7);">{{ now()->format('l, F j, Y') }} · Municipal Government of Pagsanjan</p>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        <!-- Search Bar -->
        <div style="position: relative;">
            <input type="text" id="leaveSearchInput" placeholder="Search by employee, leave type, or status..." style="width: 320px; padding: 10px 40px 10px 16px; border: none; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; background: #fff;" oninput="searchLeaveRecords(this.value)" />
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </div>

        <!-- Notification System -->
        <div class="leave-notif-wrap" style="position: relative;">
            <button class="leave-notif-btn" id="leaveNotifBtn" onclick="toggleLeaveNotif()" style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; position: relative; transition: all 0.2s;" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span class="leave-notif-dot" id="leaveNotifDot" style="position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #0b044d; display: none;"></span>
            </button>
            <div class="leave-notif-panel" id="leaveNotifPanel" style="position: absolute; top: 48px; right: 0; width: 420px; background: #fff; border-radius: 16px; box-shadow: 0 12px 32px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05); display: none; flex-direction: column; overflow: hidden; z-index: 1001;">
                <div class="leave-notif-head" style="padding: 12px 16px; border-bottom: 1px solid #f0effe; display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 style="font-size: 13px; font-weight: 700; color: #0b044d; margin: 0 0 2px;">Leave Notifications</h3>
                        <p style="font-size: 11px; color: #7c7c99; margin: 0;">You have <span id="leaveUnreadCount" style="font-weight: 600; color: #0b044d;">4</span> pending actions</p>
                    </div>
                    <button class="leave-notif-clear" onclick="clearLeaveNotifs()" style="width: 28px; height: 28px; border-radius: 8px; background: none; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; color: #7c7c99;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
                <div class="leave-notif-body" id="leaveNotifBody" style="max-height: 420px; overflow-y: auto; padding: 8px;">
                    <div class="leave-notif-card new" style="background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border: 1px solid #d9d9ee; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 8px; transition: all 0.2s; cursor: pointer;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #d9bb00, #f59e0b);">
                                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px;">Pending Leave Request</h4>
                            <p style="font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px;">Juan Dela Cruz filed Vacation Leave for 3 days</p>
                            <span style="font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                15 minutes ago
                            </span>
                        </div>
                    </div>
                    <div class="leave-notif-card new" style="background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border: 1px solid #d9d9ee; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 8px; transition: all 0.2s; cursor: pointer;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #d9bb00, #f59e0b);">
                                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px;">Pending Leave Request</h4>
                            <p style="font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px;">Maria Santos filed Sick Leave for 2 days</p>
                            <span style="font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                1 hour ago
                            </span>
                        </div>
                    </div>
                    <div class="leave-notif-card new" style="background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border: 1px solid #d9d9ee; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 8px; transition: all 0.2s; cursor: pointer;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #15803d, #22c55e);">
                                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px;">Leave Credits Accrued</h4>
                            <p style="font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px;">Monthly accrual processed for 245 employees</p>
                            <span style="font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                2 hours ago
                            </span>
                        </div>
                    </div>
                    <div class="leave-notif-card new" style="background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border: 1px solid #d9d9ee; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 0; transition: all 0.2s; cursor: pointer;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: linear-gradient(135deg, #0369a1, #0ea5e9);">
                                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px;">Leave Balance Low</h4>
                            <p style="font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px;">3 employees have less than 2 days VL remaining</p>
                            <span style="font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                1 day ago
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.leave-notif-btn:hover { background: rgba(255,255,255,0.2) !important; transform: scale(1.05); }
.leave-notif-clear:hover { background: #f7f6ff !important; color: #ef4444 !important; }
.leave-notif-card:hover { background: #f7f6ff !important; border-color: #e5e5f0 !important; }
.leave-notif-dot.active { display: block !important; animation: notifPulse 2s infinite; }
@keyframes notifPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.leave-notif-panel.open { display: flex !important; animation: notifFadeIn 0.25s ease; }
@keyframes notifFadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 480px) {
    .leave-notif-panel { position: fixed !important; top: 68px !important; right: 12px !important; left: 12px !important; width: auto !important; max-height: calc(100vh - 90px) !important; }
}
</style>

<script>
function searchLeaveRecords(query) {
    const searchTerm = query.toLowerCase().trim();
    const activeTab = document.querySelector('.tab-btn.active')?.textContent.trim();
    
    if (activeTab === 'Leave Requests') {
        const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
        let visible = 0;
        
        rows.forEach(row => {
            if (row.querySelector('.emp-cell')) {
                const empName = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
                const empId = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
                const leaveType = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                const status = row.querySelector('.badge-status')?.textContent.toLowerCase() || '';
                const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
                
                const matches = searchTerm === '' || 
                    empName.includes(searchTerm) || 
                    empId.includes(searchTerm) || 
                    leaveType.includes(searchTerm) || 
                    status.includes(searchTerm) ||
                    dept.includes(searchTerm);
                
                row.style.display = matches ? '' : 'none';
                if (matches) visible++;
            }
        });
        
        const total = rows.length - (rows[0]?.querySelector('.emp-cell') ? 0 : 1);
        if (document.getElementById('leaveRequestCount')) {
            document.getElementById('leaveRequestCount').textContent = visible;
        }
        if (document.getElementById('leaveRequestFooter')) {
            document.getElementById('leaveRequestFooter').innerHTML = `Showing <strong>${visible}</strong> of <strong>${total}</strong> records`;
        }
    } else if (activeTab === 'Transaction History') {
        const rows = document.querySelectorAll('.transaction-row');
        let visible = 0;
        
        rows.forEach(row => {
            const empName = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
            const empId = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
            const leaveCode = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
            const type = row.querySelector('.badge-status')?.textContent.toLowerCase() || '';
            
            const matches = searchTerm === '' || 
                empName.includes(searchTerm) || 
                empId.includes(searchTerm) || 
                leaveCode.includes(searchTerm) || 
                type.includes(searchTerm);
            
            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });
        
        const total = rows.length;
        if (document.getElementById('transactionFooter')) {
            document.getElementById('transactionFooter').innerHTML = `Showing <strong>${visible}</strong> of <strong>${total}</strong> transactions`;
        }
    } else if (activeTab === 'Leave Types') {
        const rows = document.querySelectorAll('.leave-type-row');
        let visible = 0;
        
        rows.forEach(row => {
            const code = row.querySelector('.emp-avatar')?.textContent.toLowerCase() || '';
            const name = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const status = row.querySelector('.badge-status')?.textContent.toLowerCase() || '';
            
            const matches = searchTerm === '' || 
                code.includes(searchTerm) || 
                name.includes(searchTerm) || 
                status.includes(searchTerm);
            
            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });
    } else if (activeTab === 'CSC Daily Accrual') {
        const rows = document.querySelectorAll('.accrual-rate-row');
        let visible = 0;
        
        rows.forEach(row => {
            const leaveType = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
            const frequency = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const status = row.querySelector('td:nth-child(7) .badge-status')?.textContent.toLowerCase() || '';
            
            const matches = searchTerm === '' || 
                leaveType.includes(searchTerm) || 
                frequency.includes(searchTerm) || 
                status.includes(searchTerm);
            
            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });
    }
}

function toggleLeaveNotif() {
    document.getElementById('leaveNotifPanel').classList.toggle('open');
}

function clearLeaveNotifs() {
    document.getElementById('leaveNotifBody').innerHTML = '<div style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center;"><svg width="40" height="40" fill="none" stroke="#d9d9ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="margin-bottom: 10px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><p style="font-size: 12px; color: #9999bb; margin: 0;">No notifications</p></div>';
    updateLeaveNotifCount();
}

function updateLeaveNotifCount() {
    const newCount = document.querySelectorAll('.leave-notif-card.new').length;
    const dot = document.getElementById('leaveNotifDot');
    const countSpan = document.getElementById('leaveUnreadCount');
    if (countSpan) countSpan.textContent = newCount;
    if (dot) dot.classList.toggle('active', newCount > 0);
}

document.addEventListener('click', (e) => {
    const wrap = document.querySelector('.leave-notif-wrap');
    const panel = document.getElementById('leaveNotifPanel');
    if (wrap && panel && !wrap.contains(e.target)) {
        panel.classList.remove('open');
    }
});

window.addEventListener('load', updateLeaveNotifCount);
</script>
