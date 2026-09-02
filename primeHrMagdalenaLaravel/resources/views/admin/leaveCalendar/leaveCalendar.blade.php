@extends($embed ? 'layouts.calendarEmbed' : 'layouts.app')

@push('styles')
    @vite(['resources/css/admin/adminLeaveAndBenefits.css', 'resources/css/admin/adminLeaveCalendar.css'])
@endpush

@section('content')

@unless($embed)
    <x-topbar title="Leave & Travel Calendar">
        <x-slot:icon><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></x-slot:icon>
        <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Who is out on leave or travel</x-slot:subtitle>
    </x-topbar>
    @include('admin.notification.adminNotification')
@endunless

@include('partials.leaveCalendar.calendar')


{{-- Reused detail modals: leave (CS Form No. 6) and travel order --}}
@include('admin.leaveAndBenefits.modals.leave-detail-modal')
@include('admin.travelOrder.modals.viewTravelOrderModal')

@push('scripts')
    {{-- leaveDetailModal.js and viewTravelOrderModal.js are pushed by their own
         modal partials above; here we only add the calendar's own script. --}}
    @vite('resources/js/shared/leaveCalendar.js')
@endpush

@endsection
