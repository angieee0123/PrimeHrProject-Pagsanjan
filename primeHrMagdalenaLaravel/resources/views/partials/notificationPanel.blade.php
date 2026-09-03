{{-- The notification bell and its dropdown — the *only* definition of either.

     There used to be two: admin/notification/adminNotification.blade.php and
     employee/notification/employeeNotification.blade.php were the same 90 lines
     of markup and CSS pasted twice, differing in one word (the audience). One
     of them had already drifted — the admin panel's clear button had a tooltip
     the employee's did not, and their keyframes had different names — and the
     mayor, having no copy at all, had no bell whatsoever. Those three files are
     now three lines each, all of them pointing here.

     Expects $audience: 'admin', 'employee' or 'mayor'. --}}
@auth
@php
    $notifAudience = in_array($audience ?? null, ['admin', 'employee', 'mayor'], true)
        ? $audience
        : 'employee';

    // The same count the sidebar row shows, from the same method — the two sit
    // on one page, and a rail saying 3 beside a bell saying 5 is worse than
    // neither showing one.
    $notifUnread = \App\Models\Notification::unreadCountFor(Auth::id(), $notifAudience);

    $notifications = \App\Models\Notification::where('user_id', Auth::id())
        ->forAudience($notifAudience)
        ->recent((int) config('notifications.panel_limit', 8))
        ->get();

    // The history page for this bell's own area. Each area has its own URL so
    // the role gate on the prefix stays the thing that decides access.
    $notifHistoryUrl = \App\Services\NotificationService::link($notifAudience . '.notifications');
@endphp

<div class="notif-wrap">
    <button class="notif-btn" id="notifBtn" type="button" onclick="toggleNotif()"
            aria-haspopup="true" aria-expanded="false" aria-controls="notifPanel"
            aria-label="Notifications{{ $notifUnread > 0 ? ', ' . $notifUnread . ' unread' : '' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="notif-badge {{ $notifUnread > 0 ? 'active' : '' }}" id="notifDot">{{ $notifUnread > 99 ? '99+' : $notifUnread }}</span>
    </button>

    <div class="notif-panel" id="notifPanel" role="dialog" aria-label="Notifications">
        <div class="notif-head">
            <div class="notif-head-text">
                <h3>Notifications</h3>
                {{-- Written as a sentence that stays true at every count. The
                     old one read "You have 0 unread message". --}}
                <p id="notifSummary" data-zero="You're all caught up"
                   data-one="1 unread notification" data-many=":count unread notifications">
                    @if($notifUnread === 0)
                        You're all caught up
                    @elseif($notifUnread === 1)
                        1 unread notification
                    @else
                        {{ $notifUnread }} unread notifications
                    @endif
                </p>
            </div>
            <button class="notif-clear" type="button" onclick="markAllAsRead()"
                    title="Mark all as read" {{ $notifUnread === 0 ? 'disabled' : '' }} id="notifClearBtn">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Mark all read
            </button>
        </div>

        <div class="notif-body" id="notifBody">
            @include('partials.notificationItems', ['notifications' => $notifications])
        </div>

        @if($notifHistoryUrl)
        <a class="notif-foot" href="{{ $notifHistoryUrl }}">
            View all notifications
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        @endif
    </div>
</div>

@once
@include('partials.notificationStyles')
@endonce

@include('partials.notificationPanelScript', ['audience' => $notifAudience])
@endauth
