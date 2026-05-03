<div class="notif-wrap">
    <button class="notif-btn" id="notifBtn" onclick="toggleNotif()">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="notif-dot" id="notifDot"></span>
    </button>
    <div class="notif-panel" id="notifPanel">
        <div class="notif-head">
            <div>
                <h3>Notifications</h3>
                <p>You have <span id="unreadCount">3</span> unread message</p>
            </div>
            <button class="notif-clear" onclick="clearAll()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
        </div>
        <div class="notif-body" id="notifBody">
            <div class="notif-card new" onclick="goToPage('/admin/leave-requests')">
                <div class="notif-left">
                    <div class="notif-avatar" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                        <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                </div>
                <div class="notif-right">
                    <h4>New Leave Request</h4>
                    <p class="notif-msg">Juan Dela Cruz submitted a vacation leave request for 3 days</p>
                    <span class="notif-time">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        5 minutes ago
                    </span>
                </div>
            </div>
            <div class="notif-card new" onclick="goToPage('/admin/payroll')">
                <div class="notif-left">
                    <div class="notif-avatar" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                        <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                </div>
                <div class="notif-right">
                    <h4>Payroll Processing</h4>
                    <p class="notif-msg">Monthly payroll for December 2024 is ready for review and approval</p>
                    <span class="notif-time">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        15 minutes ago
                    </span>
                </div>
            </div>
            <div class="notif-card new" onclick="goToPage('/admin/attendance')">
                <div class="notif-left">
                    <div class="notif-avatar" style="background:linear-gradient(135deg,#b91c1c,#ef4444)">
                        <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                </div>
                <div class="notif-right">
                    <h4>Late Attendance Alert</h4>
                    <p class="notif-msg">Maria Santos logged in 30 minutes late today without prior notice</p>
                    <span class="notif-time">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        1 hour ago
                    </span>
                </div>
            </div>
            <div class="notif-card" onclick="goToPage('/admin/employees')">
                <div class="notif-left">
                    <div class="notif-avatar" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                        <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                </div>
                <div class="notif-right">
                    <h4>New Employee Onboarding</h4>
                    <p class="notif-msg">Pedro Reyes has been added to the system. Complete onboarding checklist</p>
                    <span class="notif-time">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        2 hours ago
                    </span>
                </div>
            </div>
            <div class="notif-card" onclick="goToPage('/admin/settings')">
                <div class="notif-left">
                    <div class="notif-avatar" style="background:linear-gradient(135deg,#ea580c,#fb923c)">
                        <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                </div>
                <div class="notif-right">
                    <h4>System Update</h4>
                    <p class="notif-msg">PRIME HRIS will undergo maintenance on Dec 25, 2024 from 2:00 AM - 4:00 AM</p>
                    <span class="notif-time">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        3 hours ago
                    </span>
                </div>
            </div>
            <div class="notif-card" onclick="goToPage('/admin/leave-requests')">
                <div class="notif-left">
                    <div class="notif-avatar" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                        <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>
                <div class="notif-right">
                    <h4>Leave Request Approved</h4>
                    <p class="notif-msg">Anna Garcia's sick leave request for Dec 20-21 has been approved</p>
                    <span class="notif-time">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Yesterday
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notif-wrap { position: fixed; top: 20px; right: 20px; z-index: 1000; }
.notif-btn { width: 48px; height: 48px; border-radius: 12px; background: #fff; border: 1px solid #e5e5f0; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; position: relative; }
.notif-btn:hover { border-color: #0b044d; box-shadow: 0 4px 12px rgba(11,4,77,0.15); transform: translateY(-1px); }
.notif-btn svg { color: #0b044d; }
.notif-dot { position: absolute; top: 10px; right: 10px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #fff; display: none; }
.notif-dot.active { display: block; animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.notif-panel { position: absolute; top: 56px; right: 0; width: 420px; background: #fff; border-radius: 16px; box-shadow: 0 12px 32px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05); display: none; flex-direction: column; overflow: hidden; }
.notif-panel.open { display: flex; animation: fadeIn 0.25s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
.notif-head { padding: 20px 24px; border-bottom: 1px solid #f0effe; display: flex; justify-content: space-between; align-items: flex-start; }
.notif-head h3 { font-size: 16px; font-weight: 700; color: #0b044d; margin: 0 0 4px; }
.notif-head p { font-size: 12px; color: #7c7c99; margin: 0; }
.notif-head p span { font-weight: 600; color: #0b044d; }
.notif-clear { width: 32px; height: 32px; border-radius: 8px; background: none; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; color: #7c7c99; }
.notif-clear:hover { background: #f7f6ff; color: #ef4444; }
.notif-body { max-height: 480px; overflow-y: auto; padding: 12px; }
.notif-card { background: #fafafe; border: 1px solid #f0effe; border-radius: 12px; padding: 16px; display: flex; gap: 14px; margin-bottom: 12px; transition: all 0.2s; }
.notif-card:last-child { margin-bottom: 0; }
.notif-card:hover { background: #f7f6ff; border-color: #e5e5f0; }
.notif-card.new { background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border-color: #d9d9ee; }
.notif-left { flex-shrink: 0; }
.notif-avatar { width: 44px; height: 44px; border-radius: 11px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.notif-right { flex: 1; min-width: 0; }
.notif-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
.notif-top h4 { font-size: 13.5px; font-weight: 700; color: #0b044d; margin: 0; }
.notif-close { background: none; border: none; color: #9999bb; font-size: 22px; cursor: pointer; width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; line-height: 1; padding: 0; }
.notif-close:hover { background: #f0effe; color: #0b044d; }
.notif-msg { font-size: 12.5px; color: #5a5888; line-height: 1.5; margin: 0 0 12px; }
.notif-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
.notif-time { font-size: 11px; color: #9999bb; display: flex; align-items: center; gap: 4px; }
.notif-time svg { opacity: 0.7; }
.notif-actions { display: flex; gap: 6px; }
.notif-action-btn { padding: 6px 12px; border-radius: 7px; border: 1px solid #e5e5f0; background: #fff; color: #5a5888; font-size: 11.5px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.notif-action-btn:hover { border-color: #0b044d; color: #0b044d; background: #f7f6ff; }
.notif-action-btn.primary { background: #0b044d; color: #fff; border-color: #0b044d; }
.notif-action-btn.primary:hover { background: #1a0f6e; }
.notif-empty { padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; }
.notif-empty svg { width: 40px; height: 40px; stroke: #d9d9ee; margin-bottom: 10px; display: block; }
.notif-empty p { font-size: 12px; color: #9999bb; margin: 0; }
@media (max-width: 480px) {
    .notif-wrap { top: 12px; right: 12px; }
    .notif-panel { position: fixed; top: 68px; right: 12px; left: 12px; width: auto; border-radius: 14px; max-height: calc(100vh - 90px); }
    .notif-body { max-height: calc(100vh - 200px); }
    .notif-head { padding: 16px; }
    .notif-card { padding: 12px; gap: 10px; }
    .notif-avatar { width: 38px; height: 38px; }
    .notif-top h4 { font-size: 13px; }
    .notif-msg { font-size: 12px; margin-bottom: 8px; }
    .notif-action-btn { padding: 5px 10px; font-size: 11px; }
}
</style>

<script>
function toggleNotif() {
    const panel = document.getElementById('notifPanel');
    panel.classList.toggle('open');
}

function goToPage(url) {
    window.location.href = url;
}

function clearAll() {
    const body = document.getElementById('notifBody');
    body.innerHTML = '<div class="notif-empty"><svg width="40" height="40" fill="none" stroke="#d9d9ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><p>No notifications</p></div>';
    updateCount();
}

function updateCount() {
    const newCount = document.querySelectorAll('.notif-card.new').length;
    const dot = document.getElementById('notifDot');
    const countSpan = document.getElementById('unreadCount');
    countSpan.textContent = newCount;
    if (newCount > 0) {
        dot.classList.add('active');
    } else {
        dot.classList.remove('active');
    }
}

document.addEventListener('click', (e) => {
    const wrap = document.querySelector('.notif-wrap');
    const panel = document.getElementById('notifPanel');
    if (!wrap.contains(e.target)) {
        panel.classList.remove('open');
    }
});

window.addEventListener('load', updateCount);
</script>

<style>
@keyframes fadeOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.95); } }
</style>
