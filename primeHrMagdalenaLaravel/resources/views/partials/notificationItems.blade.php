{{-- The notification panel's card list. Rendered on first paint by the admin /
     employee notification partials, and re-rendered by NotificationController::feed()
     when either panel polls — so the polled markup cannot drift from the
     server-rendered one. Expects $notifications. --}}
@forelse($notifications as $notif)
<div class="notif-card {{ !$notif->is_read ? 'new' : '' }}"
     data-notif-id="{{ $notif->id }}"
     data-notif-link="{{ $notif->link ?? '' }}">
    <div class="notif-left">
        <div class="notif-avatar" style="background:{{
            $notif->type === 'leave_request' ? 'linear-gradient(135deg,#15803d,#22c55e)' :
            ($notif->type === 'payroll' ? 'linear-gradient(135deg,#0369a1,#0ea5e9)' :
            ($notif->type === 'attendance' ? 'linear-gradient(135deg,#b91c1c,#ef4444)' :
            ($notif->type === 'training' ? 'linear-gradient(135deg,#7c3aed,#a78bfa)' :
            ($notif->type === 'pass_slip' ? 'linear-gradient(135deg,#0f766e,#14b8a6)' :
            ($notif->type === 'travel_order' ? 'linear-gradient(135deg,#a16207,#eab308)' :
            'linear-gradient(135deg,#ea580c,#fb923c)'))))) }}">
            @if($notif->type === 'leave_request')
                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            @elseif($notif->type === 'payroll')
                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            @elseif($notif->type === 'attendance')
                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            @elseif($notif->type === 'training')
                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            @elseif($notif->type === 'pass_slip')
                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/><path d="M8 4v4"/><path d="M16 4v4"/></svg>
            @elseif($notif->type === 'travel_order')
                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17.8 19.2 16 11l3.5-3.5a2.1 2.1 0 0 0-3-3L13 8 4.8 6.2a1 1 0 0 0-.9 1.7l5.1 3.3-2.3 2.3-2.2-.4a1 1 0 0 0-.9 1.6l2.3 2.3 2.3 2.3a1 1 0 0 0 1.6-.9l-.4-2.2 2.3-2.3 3.3 5.1a1 1 0 0 0 1.7-.9z"/></svg>
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
