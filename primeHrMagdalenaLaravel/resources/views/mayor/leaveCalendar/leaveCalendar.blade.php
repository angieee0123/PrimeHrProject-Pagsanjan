{{-- The mayor's Leave & Travel Calendar.

     The grid, the filters and the stat strip are the admin's, included from
     one partial and computed by one controller — see
     MayorLeaveCalendarController. What this file adds is the mayor's chrome
     and the mayor's detail modals: the admin's open under /admin, which
     EnsureRoleForArea closes to the mayor. --}}
@extends($embed ? 'layouts.calendarEmbed' : 'layouts.mayor')

@section('title', "Leave & Travel Calendar · PRIME HRIS")

@push('styles')
    @vite(['resources/css/admin/adminLeaveAndBenefits.css', 'resources/css/admin/adminLeaveCalendar.css', 'resources/css/travelOrder.css'])
@endpush

@section('content')

@unless($embed)
    @include('mayor.topbar.leaveCalendarTopbar')
@endunless

@include('partials.leaveCalendar.calendar')

{{-- Detail modals: leave (CS Form No. 6) and travel order, both read-only. --}}
@include('mayor.leaveCalendar.modals.mayorLeaveDetailModal')
@include('mayor.travelOrder.modals.viewTravelOrderModal')

@push('scripts')
    {{-- Which modals a marker click opens. shared/leaveCalendar.js defaults to
         the admin pair, so this surface names its own; both are pushed by the
         modal partials above. --}}
    <script>
        window.leaveCalendarOpeners = {
            leave: 'openMayorLeaveDetailModal',
            travel: 'viewMayorTravelOrder',
        };
    </script>
    @vite('resources/js/shared/leaveCalendar.js')
@endpush

@endsection
