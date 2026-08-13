@extends($embed ? 'layouts.calendarEmbed' : 'layouts.employee')

@push('styles')
    @vite('resources/css/employee/employeeLeaveCalendar.css')
@endpush

@section('content')

{{-- Only the month grid varies between 5 and 6 rows, and only that class has
     rules; week would report 1 and day 0, which name nothing. --}}
@php $weekCount = $view === 'month' ? intdiv(count($days), 7) : null; @endphp
<main class="ec-calendar glass-shell {{ $weekCount ? 'ec-weeks-' . $weekCount : '' }}">

    {{-- Control bar --}}
    <div class="filter-card ec-toolbar">
        <div class="filter-card-fields ec-nav">
            @php
                $unit = ['month' => 'month', 'week' => 'week', 'day' => 'day'][$view];
                $monthNavSep = str_contains($monthNavBase, '?') ? '&' : '?';
            @endphp
            <a href="{{ $prevUrl }}" class="btn-ghost ec-nav-btn" aria-label="Previous {{ $unit }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>

            {{-- The picker matches the unit being paged: a month input cannot
                 name the week or day the other two views are anchored to. --}}
            @if($view === 'month')
                <input type="month" class="ec-month-input" value="{{ $currentMonth }}"
                       aria-label="Jump to month and year"
                       onchange="if(this.value){window.location.href='{{ $monthNavBase }}{{ $monthNavSep }}month=' + this.value;}">
            @else
                <input type="date" class="ec-month-input" value="{{ $currentDate }}"
                       aria-label="Jump to a date"
                       onchange="if(this.value){window.location.href='{{ $monthNavBase }}{{ $monthNavSep }}date=' + this.value;}">
            @endif

            <a href="{{ $nextUrl }}" class="btn-ghost ec-nav-btn" aria-label="Next {{ $unit }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="{{ $todayUrl }}" class="btn-solid ec-today-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Today
            </a>

            {{-- Month view is named by its own picker ("August 2026"). A date
                 input is not a period name, so week and day say theirs. --}}
            @if($view !== 'month')
                <span class="ec-period-label">{{ $monthLabel }}</span>
            @endif

            {{-- Month / Week / Day. Each link keeps the date on screen, so
                 switching re-frames the same day instead of jumping to now. --}}
            <div class="ec-viewswitch" role="group" aria-label="Calendar view">
                @foreach(['month' => 'Month', 'week' => 'Week', 'day' => 'Day'] as $key => $label)
                    <a href="{{ $viewUrls[$key] }}"
                       class="ec-viewswitch-btn {{ $view === $key ? 'is-active' : '' }}"
                       @if($view === $key) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
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
            {{-- The count follows the period on screen, so the label has to as
                 well — "this month" over a week's total is a false statement. --}}
            <div class="ec-stat-body"><p class="ec-stat-num">{{ $summary['days_off'] }}</p><p class="ec-stat-lbl">Days off {{ ['month' => 'this month', 'week' => 'this week', 'day' => 'on this date'][$view] }}</p></div>
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

    @if($view === 'day')
    {{--
        Day view is a list, not a one-cell grid.

        Leave and travel are whole-day records — an application says "the 3rd
        to the 5th", never "09:00" — so an hour axis would imply a precision
        these dates do not carry. What the day is actually asked is what is
        booked, so it shows each record in full.
    --}}
    @php $dayCell = $days[0] ?? null; @endphp
    <div class="ec-card ec-dayview">
        @if($dayCell && count($dayCell['events']))
            <div class="ec-dayview-list">
                @foreach($dayCell['events'] as $ev)
                    <div class="ec-dayview-row kind-{{ $ev['kind'] }}">
                        <span class="ec-dayview-dot"></span>
                        <span class="ec-dayview-body">
                            <span class="ec-dayview-name">{{ $ev['label'] }}</span>
                            <span class="ec-dayview-meta">
                                {{ $ev['type'] === 'leave' ? 'Leave' : 'Travel Order' }}
                                <span class="ec-dayview-range">{{ $ev['range_label'] }}</span>
                            </span>
                        </span>
                        <span class="ec-dayview-status {{ $ev['status'] === 'approved' ? 'is-approved' : 'is-pending' }}">{{ $ev['status_label'] }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="ec-dayview-empty">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p class="ec-dayview-empty-title">Nothing booked on {{ $monthLabel }}</p>
                <p class="ec-dayview-empty-sub">You have no leave or travel recorded for this day.</p>
            </div>
        @endif
    </div>
    @else
    <div class="ec-card">
        {{-- Week view names the actual dates: in a seven-day view "Wed" alone
             does not say which Wednesday. --}}
        <div class="ec-grid ec-weekdays">
            @if($view === 'week')
                @foreach($days as $day)
                    <div class="ec-weekday {{ $day['is_weekend'] ? 'is-weekend' : '' }} {{ $day['is_today'] ? 'is-today' : '' }}">
                        {{ $day['date']->format('D') }} <span class="ec-weekday-num">{{ $day['date']->format('j') }}</span>
                    </div>
                @endforeach
            @else
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $wd)
                    <div class="ec-weekday {{ in_array($i, [0, 6]) ? 'is-weekend' : '' }}">{{ $wd }}</div>
                @endforeach
            @endif
        </div>

        <div class="ec-grid ec-days is-{{ $view }}">
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
    @endif

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
