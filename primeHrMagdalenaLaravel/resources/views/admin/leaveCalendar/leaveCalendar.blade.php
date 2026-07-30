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

@php $weekCount = intdiv(count($days), 7); @endphp
<main class="lc-calendar glass-shell lc-weeks-{{ $weekCount }}">

    {{-- Control bar — same filter-card language as the Attendance page --}}
    <div class="filter-card lc-toolbar">
        <div class="filter-card-fields lc-nav">
            <span class="lc-toolbar-label">Month</span>
            @php
                $monthNavBase = route('admin.leaveCalendar', $embed ? ['embed' => 1] : []);
                $monthNavSep  = str_contains($monthNavBase, '?') ? '&' : '?';
            @endphp
            <a href="{{ $prevUrl }}" class="btn-ghost lc-nav-btn" aria-label="Previous month">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            {{-- Type or pick an exact month & year to jump straight to it. --}}
            <input type="month" class="lc-month-input" value="{{ $currentMonth }}"
                   aria-label="Jump to month and year"
                   onchange="if(this.value){window.location.href='{{ $monthNavBase }}{{ $monthNavSep }}month=' + this.value;}">
            <a href="{{ $nextUrl }}" class="btn-ghost lc-nav-btn" aria-label="Next month">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="{{ $todayUrl }}" class="btn-solid lc-today-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Today
            </a>
        </div>
        <div class="filter-card-actions lc-legend">
            <span class="lc-legend-item"><span class="lc-legend-dot is-leave-approved"></span>Approved leave</span>
            <span class="lc-legend-item"><span class="lc-legend-dot is-leave-pending"></span>Pending leave</span>
            <span class="lc-legend-item"><span class="lc-legend-dot is-travel"></span>Travel order</span>
            <span class="lc-legend-item"><span class="lc-legend-dot is-pending-swatch"></span>Pending (dashed)</span>
        </div>
    </div>

    {{-- Summary stat strip --}}
    <div class="lc-stats">
        <div class="lc-stat">
            <span class="lc-stat-ico is-people">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div class="lc-stat-body"><p class="lc-stat-num">{{ $peopleOut }}</p><p class="lc-stat-lbl">People out</p></div>
        </div>
        <div class="lc-stat">
            <span class="lc-stat-ico is-leave">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </span>
            <div class="lc-stat-body"><p class="lc-stat-num">{{ $summary['leave'] }}</p><p class="lc-stat-lbl">On leave</p></div>
        </div>
        <div class="lc-stat">
            <span class="lc-stat-ico is-travel">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            <div class="lc-stat-body"><p class="lc-stat-num">{{ $summary['travel'] }}</p><p class="lc-stat-lbl">Travel orders</p></div>
        </div>
        <div class="lc-stat">
            <span class="lc-stat-ico is-pending">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div class="lc-stat-body"><p class="lc-stat-num">{{ $summary['pending'] }}</p><p class="lc-stat-lbl">Pending approval</p></div>
        </div>
    </div>

    <div class="lc-card">
        {{-- Weekday header --}}
        <div class="lc-grid lc-weekdays">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $wd)
                <div class="lc-weekday {{ in_array($i, [0, 6]) ? 'is-weekend' : '' }}">{{ $wd }}</div>
            @endforeach
        </div>

        {{-- Day cells --}}
        <div class="lc-grid lc-days">
            @foreach($days as $day)
                @php $count = count($day['events']); @endphp
                <div class="lc-day {{ $day['in_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_weekend'] ? 'is-weekend' : '' }}">
                    <div class="lc-day-head">
                        <span class="lc-day-num">{{ $day['date']->format('j') }}</span>
                        @if($count > 0)
                            <span class="lc-day-count">{{ $count }}</span>
                        @endif
                    </div>

                    @if($count > 0)
                        <div class="cal-stack">
                        <div class="cal-markers" data-day-label="{{ $day['date']->format('l, F j, Y') }}">
                            @foreach($day['events'] as $ev)
                                @php
                                    $summary = [
                                        'name'         => $ev['name'],
                                        'type_label'   => $ev['type_label'],
                                        'sub'          => $ev['sub'],
                                        'range_label'  => $ev['range_label'],
                                        'status_label' => $ev['status_label'],
                                        'type'         => $ev['type'],
                                    ];
                                @endphp
                                <button type="button"
                                        class="cal-marker type-{{ $ev['type'] }} status-{{ $ev['status'] }}"
                                        data-summary='@json($summary)'
                                        data-payload='@json($ev['payload'])'
                                        aria-label="{{ $ev['name'] }} — {{ $ev['type_label'] }} ({{ $ev['status_label'] }})">
                                    @if($ev['photo'])
                                        <img src="{{ $ev['photo'] }}" alt="">
                                    @else
                                        <span class="cal-marker-initials" style="background:{{ $ev['color'] }}">{{ $ev['initials'] }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        @if($count > 4)
                            <button type="button" class="cal-more" data-day-label="{{ $day['date']->format('l, F j, Y') }}">+{{ $count - 4 }}</button>
                        @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</main>

{{-- Day-detail popover: lists everyone out on a clicked day (opened by "+X more"). --}}
<div id="calDayModal" class="cal-day-modal" style="display:none" onclick="closeCalDayModal(event)">
    <div class="cal-day-modal-panel" onclick="event.stopPropagation()">
        <div class="cal-day-modal-head">
            <h3 id="calDayModalTitle">Day</h3>
            <button type="button" class="cal-day-modal-close" onclick="closeCalDayModal()" aria-label="Close">✕</button>
        </div>
        <div id="calDayModalList" class="cal-day-modal-list"></div>
    </div>
</div>

{{-- Hover summary tooltip --}}
<div id="calTooltip" class="cal-tooltip" style="display:none"></div>

{{-- Reused detail modals: leave (CS Form No. 6) and travel order --}}
@include('admin.leaveAndBenefits.modals.leave-detail-modal')
@include('admin.travelOrder.modals.viewTravelOrderModal')

@push('scripts')
    {{-- leaveDetailModal.js and viewTravelOrderModal.js are pushed by their own
         modal partials above; here we only add the calendar's own script. --}}
    @vite('resources/js/admin/leaveCalendar/leaveCalendar.js')
@endpush

@endsection
