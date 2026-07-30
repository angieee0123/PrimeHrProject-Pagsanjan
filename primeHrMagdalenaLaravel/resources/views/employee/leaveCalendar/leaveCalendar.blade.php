@extends($embed ? 'layouts.calendarEmbed' : 'layouts.employee')

@push('styles')
    @vite('resources/css/employee/employeeLeaveCalendar.css')
@endpush

@section('content')

@php $weekCount = intdiv(count($days), 7); @endphp
<main class="ec-calendar glass-shell ec-weeks-{{ $weekCount }}">

    {{-- Control bar --}}
    <div class="filter-card ec-toolbar">
        <div class="filter-card-fields ec-nav">
            <span class="ec-toolbar-label">Month</span>
            @php
                $monthNavBase = route('employee.leaveCalendar', $embed ? ['embed' => 1] : []);
                $monthNavSep  = str_contains($monthNavBase, '?') ? '&' : '?';
            @endphp
            <a href="{{ $prevUrl }}" class="btn-ghost ec-nav-btn" aria-label="Previous month">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <input type="month" class="ec-month-input" value="{{ $currentMonth }}"
                   aria-label="Jump to month and year"
                   onchange="if(this.value){window.location.href='{{ $monthNavBase }}{{ $monthNavSep }}month=' + this.value;}">
            <a href="{{ $nextUrl }}" class="btn-ghost ec-nav-btn" aria-label="Next month">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="{{ $todayUrl }}" class="btn-solid ec-today-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Today
            </a>
        </div>
        <div class="filter-card-actions ec-legend">
            <span class="ec-legend-item"><span class="ec-legend-dot is-leave-approved"></span>Approved leave</span>
            <span class="ec-legend-item"><span class="ec-legend-dot is-leave-pending"></span>Pending leave</span>
            <span class="ec-legend-item"><span class="ec-legend-dot is-travel"></span>Travel order</span>
        </div>
    </div>

    {{-- Summary strip --}}
    <div class="ec-stats">
        <div class="ec-stat">
            <span class="ec-stat-ico is-days">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </span>
            <div class="ec-stat-body"><p class="ec-stat-num">{{ $summary['days_off'] }}</p><p class="ec-stat-lbl">Days off this month</p></div>
        </div>
        <div class="ec-stat">
            <span class="ec-stat-ico is-leave">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </span>
            <div class="ec-stat-body"><p class="ec-stat-num">{{ $summary['leave'] }}</p><p class="ec-stat-lbl">My leaves</p></div>
        </div>
        <div class="ec-stat">
            <span class="ec-stat-ico is-travel">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            <div class="ec-stat-body"><p class="ec-stat-num">{{ $summary['travel'] }}</p><p class="ec-stat-lbl">My travel orders</p></div>
        </div>
        <div class="ec-stat">
            <span class="ec-stat-ico is-pending">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div class="ec-stat-body"><p class="ec-stat-num">{{ $summary['pending'] }}</p><p class="ec-stat-lbl">Awaiting approval</p></div>
        </div>
    </div>

    <div class="ec-card">
        <div class="ec-grid ec-weekdays">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $wd)
                <div class="ec-weekday {{ in_array($i, [0, 6]) ? 'is-weekend' : '' }}">{{ $wd }}</div>
            @endforeach
        </div>

        <div class="ec-grid ec-days">
            @foreach($days as $day)
                @php $count = count($day['events']); @endphp
                <div class="ec-day {{ $day['in_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_weekend'] ? 'is-weekend' : '' }} {{ $day['primary_kind'] ? 'kind-' . $day['primary_kind'] : '' }}">
                    <div class="ec-day-head">
                        <span class="ec-day-num">{{ $day['date']->format('j') }}</span>
                    </div>

                    @if($count > 0)
                        <div class="ec-events" data-day-label="{{ $day['date']->format('l, F j, Y') }}">
                            @foreach($day['events'] as $ev)
                                @php
                                    $summaryData = [
                                        'label'        => $ev['label'],
                                        'type_label'   => $ev['type'] === 'leave' ? 'Leave' : 'Travel Order',
                                        'range_label'  => $ev['range_label'],
                                        'status_label' => $ev['status_label'],
                                        'kind'         => $ev['kind'],
                                    ];
                                @endphp
                                <button type="button" class="ec-pill kind-{{ $ev['kind'] }}"
                                        data-summary='@json($summaryData)'
                                        aria-label="{{ $ev['label'] }} — {{ $ev['status_label'] }}">
                                    <span class="ec-pill-dot"></span>
                                    <span class="ec-pill-text">{{ $ev['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</main>

{{-- Day-detail popover (opened by clicking a day with items) --}}
<div id="ecDayModal" class="ec-day-modal" style="display:none" onclick="closeEcDayModal(event)">
    <div class="ec-day-modal-panel" onclick="event.stopPropagation()">
        <div class="ec-day-modal-head">
            <h3 id="ecDayModalTitle">Day</h3>
            <button type="button" class="ec-day-modal-close" onclick="closeEcDayModal()" aria-label="Close">✕</button>
        </div>
        <div id="ecDayModalList" class="ec-day-modal-list"></div>
    </div>
</div>

<div id="ecTooltip" class="ec-tooltip" style="display:none"></div>

@push('scripts')
    @vite('resources/js/employee/leaveCalendar/leaveCalendar.js')
@endpush

@endsection
