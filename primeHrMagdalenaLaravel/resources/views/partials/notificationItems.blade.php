{{-- The notification panel's card list.

     Rendered on first paint by partials/notificationPanel.blade.php and
     re-rendered by NotificationController::feed() when a panel polls, so the
     polled markup cannot drift from the server-rendered one. The history page
     renders the same card through partials/notificationCard.blade.php, which is
     what this loops over — one card definition for all three surfaces.

     Expects $notifications. --}}
@forelse($notifications as $notif)
    @include('partials.notificationCard', ['notif' => $notif])
@empty
<div class="notif-empty">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    <p>You're all caught up</p>
    <span>Approvals, decisions and account changes will appear here.</span>
</div>
@endforelse
