<div class="notif-wrap">
    <button class="notif-btn" id="notifBtn" onclick="toggleNotif()">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        @php
            $unreadCount = \App\Models\Notification::where('user_id', Auth::id())->forAudience('admin')->unread()->count();
        @endphp
        <span class="notif-badge {{ $unreadCount > 0 ? 'active' : '' }}" id="notifDot">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
    </button>
    <div class="notif-panel" id="notifPanel">
        <div class="notif-head">
            <div>
                <h3>Notifications</h3>
                <p>You have <span id="unreadCount">{{ $unreadCount }}</span> unread message</p>
            </div>
            <button class="notif-clear" onclick="markAllAsRead()" title="Mark all as read">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
        </div>
        <div class="notif-body" id="notifBody">
            @php
                $notifications = \App\Models\Notification::where('user_id', Auth::id())
                    ->forAudience('admin')
                    ->recent(10)
                    ->get();
            @endphp
            @include('partials.notificationItems', ['notifications' => $notifications])
        </div>
    </div>
</div>

{{-- Sizing mirrors employee/notification/employeeNotification.blade.php — keep
     the two panels visually identical (only the audience/query differs). --}}
<style>
.notif-wrap { position: fixed; top: 20px; right: 20px; z-index: 1000; }
/* adminDashboard.css also styles .notif-btn on admin pages with !important-
   free rules of the same values — kept in sync here so non-dashboard admin
   pages (which don't load adminDashboard.css) still match. */
.notif-btn { width: 46px; height: 46px; border-radius: 11px; background: #fff; border: 1px solid var(--theme-neutral-300); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; position: relative; }
.notif-btn:hover { border-color: var(--gp-pri); box-shadow: 0 4px 12px rgba(11,4,77,0.15); transform: translateY(-1px); }
.notif-btn svg { color: var(--gp-pri); width: 22px; height: 22px; }
.notif-badge { position: absolute; top: -6px; right: -6px; min-width: 19px; height: 19px; padding: 0 5px; background: var(--theme-danger); color: #fff; font-size: 10px; font-weight: 700; line-height: 1; border-radius: 999px; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(239,68,68,.4); display: none; align-items: center; justify-content: center; }
.notif-badge.active { display: inline-flex; animation: notifPulse 2s infinite; }
@keyframes notifPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.12); } }
.notif-panel { position: absolute; top: 56px; right: 0; width: 420px; background: #fff; border-radius: 16px; box-shadow: 0 12px 32px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05); display: none; flex-direction: column; overflow: hidden; }
.notif-panel.open { display: flex; animation: notifFadeIn 0.25s ease; }
@keyframes notifFadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
.notif-head { padding: 12px 16px; border-bottom: 1px solid var(--theme-primary-light); display: flex; justify-content: space-between; align-items: flex-start; }
.notif-head h3 { font-size: 13px; font-weight: 700; color: var(--gp-pri); margin: 0 0 2px; }
.notif-head p { font-size: 11px; color: var(--theme-neutral-700); margin: 0; }
.notif-head p span { font-weight: 600; color: var(--gp-pri); }
.notif-clear { width: 28px; height: 28px; border-radius: 8px; background: none; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; color: var(--theme-neutral-700); }
.notif-clear:hover { background: var(--theme-primary-soft); color: var(--theme-danger); }
.notif-body { max-height: 420px; overflow-y: auto; padding: 8px; }
.notif-card { background: var(--theme-neutral-50); border: 1px solid var(--theme-primary-light); border-radius: 10px; padding: 10px 12px; display: flex; gap: 10px; margin-bottom: 8px; transition: all 0.2s; cursor: pointer; }
.notif-card:last-child { margin-bottom: 0; }
.notif-card:hover { background: var(--theme-primary-soft); border-color: var(--theme-neutral-300); }
.notif-card.new { background: linear-gradient(135deg, var(--theme-primary-soft) 0%, var(--theme-neutral-50) 100%); border-color: var(--theme-neutral-400); }
.notif-left { flex-shrink: 0; }
.notif-avatar { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.notif-avatar svg { width: 16px; height: 16px; }
.notif-right { flex: 1; min-width: 0; }
.notif-right h4 { font-size: 12px; font-weight: 700; color: var(--gp-pri); margin: 0 0 3px; }
.notif-msg { font-size: 11px; color: var(--gp-text-mid); line-height: 1.4; margin: 0 0 6px; }
.notif-time { font-size: 10px; color: var(--gp-text-soft); display: flex; align-items: center; gap: 4px; }
.notif-time svg { opacity: 0.7; }
.notif-empty { padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; }
.notif-empty svg { width: 40px; height: 40px; stroke: var(--theme-neutral-400); margin-bottom: 10px; display: block; }
.notif-empty p { font-size: 12px; color: var(--gp-text-soft); margin: 0; }
@media (max-width: 768px) {
    .notif-wrap { top: 12px; right: 12px; }
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

@include('partials.notificationPanelScript', ['audience' => 'admin'])
