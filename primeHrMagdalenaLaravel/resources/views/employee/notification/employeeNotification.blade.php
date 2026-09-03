{{-- The employee bell. See partials/notificationPanel.blade.php — same panel
     as the admin and mayor bells, pointed at this area's audience. --}}
@include('partials.notificationPanel', ['audience' => 'employee'])
