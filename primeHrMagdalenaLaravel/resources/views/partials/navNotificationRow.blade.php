{{-- The "Notifications" row in the sidebar rail, for all three areas.

     The bell is a floating button pinned to the top-right corner of the page;
     it opens a dropdown of the newest few. The rail row is the way to the full
     history — the same door the dropdown's footer link opens, in the place
     people look for a page rather than a popup.

     It sits ungrouped, beside Dashboard and AI Assistant, in every rail: the
     admin rail collapses its groups, and an unread badge hidden inside a shut
     section is a badge nobody sees. Being in the same position in all three
     rails is also what makes it findable when somebody holds two roles.

     Expects $area: 'admin', 'employee' or 'mayor'. --}}
@auth
@php
    $navNotifArea = in_array($area ?? null, ['admin', 'employee', 'mayor'], true) ? $area : 'employee';
    $navNotifRoute = \App\Services\NotificationService::link($navNotifArea . '.notifications');

    // Same count the bell shows, from the same method — a rail saying 3 beside
    // a bell saying 5 is worse than neither showing one.
    $navNotifUnread = \App\Models\Notification::unreadCountFor(Auth::id(), $navNotifArea);

    $navNotifActive = Route::currentRouteName() === $navNotifArea . '.notifications';
@endphp

@if($navNotifRoute)
<a href="{{ $navNotifRoute }}"
   class="nav-item {{ $navNotifActive ? 'active' : '' }}"
   title="Notifications{{ $navNotifUnread > 0 ? ' (' . $navNotifUnread . ' unread)' : '' }}">
    <span class="nav-icon">
        <x-nav-icon name="notifications" />
        {{-- A second copy of the badge, shown only while the rail is collapsed:
             the label it normally sits beside is display:none there, and a
             count floating in the empty half of the row reads as belonging to
             nothing. --}}
        @if($navNotifUnread > 0)
        <span class="nav-notif-dot" aria-hidden="true"></span>
        @endif
    </span>
    <span class="nav-label">Notifications</span>
    @if($navNotifUnread > 0)
    <span class="nav-notif-badge">{{ $navNotifUnread > 99 ? '99+' : $navNotifUnread }}</span>
    @endif
    @if($navNotifActive)
    <span class="nav-active-bar"></span>
    @endif
</a>
@endif

@once
<style>
/* The rail's own tokens, not the panel's: this badge sits on the sidebar
   surface, which is the brand colour, so it takes the sidebar foreground for
   its border rather than a page background that is not behind it. */
.nav-item .nav-notif-badge {
    margin-left: auto;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: var(--theme-danger);
    color: var(--theme-danger-fg, #fff);
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
    flex-shrink: 0;
}

.nav-item .nav-icon { position: relative; }

/* Hidden by default; the collapsed rail is the only place it earns its keep. */
.nav-notif-dot { display: none; }

.sidebar.collapsed .nav-item .nav-notif-badge { display: none; }
.sidebar.collapsed .nav-notif-dot {
    display: block;
    position: absolute;
    top: -3px;
    right: -3px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--theme-danger);
    box-shadow: 0 0 0 2px var(--theme-sidebar-bg);
}
</style>
@endonce
@endauth
