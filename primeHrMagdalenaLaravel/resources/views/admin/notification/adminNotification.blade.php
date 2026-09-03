{{-- The admin bell. Markup, styling and behaviour all live in
     partials/notificationPanel.blade.php — this file used to be a copy of the
     employee panel and the two had already drifted. Kept as a file of its own
     only because eighteen admin pages @include it by this name. --}}
@include('partials.notificationPanel', ['audience' => 'admin'])
