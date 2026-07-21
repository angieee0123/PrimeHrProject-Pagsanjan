<div class="notif-wrap">
    <button class="notif-btn" id="notifBtn" onclick="toggleNotif()">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        @php
            $unreadCount = \App\Models\Notification::where('user_id', Auth::id())->unread()->count();
        @endphp
        <span class="notif-dot {{ $unreadCount > 0 ? 'active' : '' }}" id="notifDot"></span>
    </button>
    <div class="notif-panel" id="notifPanel">
        <div class="notif-head">
            <div>
                <h3>Notifications</h3>
                <p>You have <span id="unreadCount">{{ $unreadCount }}</span> unread message</p>
            </div>
            <button class="notif-clear" onclick="markAllAsRead()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
        </div>
        <div class="notif-body" id="notifBody">
            @php
                $notifications = \App\Models\Notification::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            @endphp
            @forelse($notifications as $notif)
            <div class="notif-card {{ !$notif->is_read ? 'new' : '' }}" onclick="markAsReadAndRedirect({{ $notif->id }}, '{{ $notif->link ?? '#' }}')">
                <div class="notif-left">
                    <div class="notif-avatar" style="background:{{ 
                        $notif->type === 'leave_request' ? 'linear-gradient(135deg,#15803d,#22c55e)' : 
                        ($notif->type === 'payroll' ? 'linear-gradient(135deg,#0369a1,#0ea5e9)' : 
                        ($notif->type === 'attendance' ? 'linear-gradient(135deg,#b91c1c,#ef4444)' : 
                        ($notif->type === 'training' ? 'linear-gradient(135deg,#7c3aed,#a78bfa)' : 
                        'linear-gradient(135deg,#ea580c,#fb923c)'))) }}">
                        @if($notif->type === 'leave_request')
                            <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        @elseif($notif->type === 'payroll')
                            <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        @elseif($notif->type === 'attendance')
                            <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @elseif($notif->type === 'training')
                            <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        @else
                            <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        @endif
                    </div>
                </div>
                <div class="notif-right">
                    <h4>{{ $notif->title }}</h4>
                    <p class="notif-msg">{{ $notif->message }}</p>
                    <span class="notif-time">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ $notif->time_ago }}
                    </span>
                </div>
            </div>
            @empty
            <div class="notif-empty">
                <svg width="40" height="40" fill="none" stroke="#d9d9ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <p>No notifications</p>
            </div>
            @endforelse
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
.notif-head { padding: 12px 16px; border-bottom: 1px solid #f0effe; display: flex; justify-content: space-between; align-items: flex-start; }
.notif-head h3 { font-size: 13px; font-weight: 700; color: #0b044d; margin: 0 0 2px; }
.notif-head p { font-size: 11px; color: #7c7c99; margin: 0; }
.notif-head p span { font-weight: 600; color: #0b044d; }
.notif-clear { width: 28px; height: 28px; border-radius: 8px; background: none; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; color: #7c7c99; }
.notif-clear:hover { background: #f7f6ff; color: #ef4444; }
.notif-body { max-height: 420px; overflow-y: auto; padding: 8px; }
.notif-card { background: #fafafe; border: 1px solid #f0effe; border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 8px; transition: all 0.2s; cursor: pointer; }
.notif-card:last-child { margin-bottom: 0; }
.notif-card:hover { background: #f7f6ff; border-color: #e5e5f0; }
.notif-card.new { background: linear-gradient(135deg, #f7f6ff 0%, #fafafe 100%); border-color: #d9d9ee; }
.notif-left { flex-shrink: 0; }
.notif-avatar { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.notif-right { flex: 1; min-width: 0; }
.notif-right h4 { font-size: 12px; font-weight: 700; color: #0b044d; margin: 0 0 3px; }
.notif-msg { font-size: 11px; color: #5a5888; line-height: 1.4; margin: 0 0 6px; }
.notif-time { font-size: 10px; color: #9999bb; display: flex; align-items: center; gap: 4px; }
.notif-time svg { opacity: 0.7; }
.notif-empty { padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; }
.notif-empty svg { width: 40px; height: 40px; stroke: #d9d9ee; margin-bottom: 10px; display: block; }
.notif-empty p { font-size: 12px; color: #9999bb; margin: 0; }
@media (max-width: 768px) {
    .notif-wrap { top: 12px; right: 12px; }
    .notif-btn { width: 44px; height: 44px; border-radius: 10px; }
    .notif-panel {
        position: fixed;
        top: 64px;
        right: 12px;
        left: 12px;
        width: auto;
        max-height: calc(100vh - 84px);
        border-radius: 14px;
    }
    .notif-body { max-height: calc(100vh - 190px); }
}
@media (max-width: 480px) {
    .notif-wrap { top: 12px; right: 12px; }
    .notif-panel { position: fixed; top: 68px; right: 12px; left: 12px; width: auto; border-radius: 14px; max-height: calc(100vh - 90px); }
    .notif-body { max-height: calc(100vh - 200px); }
    .notif-head { padding: 16px; }
    .notif-card { padding: 12px; gap: 10px; }
    .notif-avatar { width: 38px; height: 38px; }
    .notif-right h4 { font-size: 13px; }
    .notif-msg { font-size: 12px; margin-bottom: 8px; }
}
</style>

<script>
function toggleNotif() {
    const panel = document.getElementById('notifPanel');
    panel.classList.toggle('open');
}

function markAllAsRead() {
    fetch('/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notif-card.new').forEach(card => {
                card.classList.remove('new');
            });
            document.getElementById('unreadCount').textContent = '0';
            document.getElementById('notifDot').classList.remove('active');
        }
    });
}

function markAsReadAndRedirect(notificationId, link) {
    fetch(`/api/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(() => {
        if (link && link !== '#') {
            window.location.href = link;
        }
    });
}

document.addEventListener('click', (e) => {
    const wrap = document.querySelector('.notif-wrap');
    const panel = document.getElementById('notifPanel');
    if (!wrap.contains(e.target)) {
        panel.classList.remove('open');
    }
});
</script>
