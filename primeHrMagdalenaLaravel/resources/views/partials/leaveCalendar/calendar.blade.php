{{--
    The Leave & Travel Calendar itself — the control card, the stat strip and
    the month / week / day grid.

    Shared by the admin's page and the mayor's, which are the same read-only
    availability monitor over the same query (AdminLeaveCalendarController and
    the MayorLeaveCalendarController that inherits it). Only what surrounds this
    differs: the layout, the topbar, and which detail modals a click opens. The
    grid was the half most likely to drift, so it lives here rather than in
    each surface's view.

    Every variable below comes from that controller; nothing is surface-specific
    except the links, which the controller already builds from its own route.
--}}
@php $weekCount = intdiv(count($days), 7); @endphp
<main class="lc-calendar glass-shell lc-weeks-{{ $weekCount }}">

    {{--
        One control card, two rows.

        The month navigator and the filter form used to be two separate
        `.filter-card`s stacked on top of each other, close enough that the
        second needed `margin-top: -26px` to claw back the first's bottom
        margin. Two bordered cards for one band of controls read as two
        unrelated toolbars; the rows keep their own layout, they just share
        a card now — and the negative margin is gone with the gap it was
        cancelling.
    --}}
    <div class="filter-card lc-controls">
    <div class="lc-toolbar">
        <div class="filter-card-fields lc-nav">
            @php
                $unit = ['month' => 'month', 'week' => 'week', 'day' => 'day'][$view];
                $monthNavSep = str_contains($monthNavBase, '?') ? '&' : '?';
            @endphp
            <a href="{{ $prevUrl }}" class="btn-ghost lc-nav-btn" aria-label="Previous {{ $unit }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>

            {{-- The picker matches the unit being paged: a month input cannot
                 name the week or day the other two views are anchored to. --}}
            @if($view === 'month')
                <input type="month" class="lc-month-input" value="{{ $currentMonth }}"
                       aria-label="Jump to month and year"
                       onchange="if(this.value){window.location.href='{{ $monthNavBase }}{{ $monthNavSep }}month=' + this.value;}">
            @else
                <input type="date" class="lc-month-input" value="{{ $currentDate }}"
                       aria-label="Jump to a date"
                       onchange="if(this.value){window.location.href='{{ $monthNavBase }}{{ $monthNavSep }}date=' + this.value;}">
            @endif

            <a href="{{ $nextUrl }}" class="btn-ghost lc-nav-btn" aria-label="Next {{ $unit }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="{{ $todayUrl }}" class="btn-solid lc-today-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Today
            </a>

            {{-- Month / Week / Day. Each link keeps the date on screen, so
                 switching re-frames the same day instead of jumping to now. --}}
            <div class="lc-viewswitch" role="group" aria-label="Calendar view">
                @foreach(['month' => 'Month', 'week' => 'Week', 'day' => 'Day'] as $key => $label)
                    <a href="{{ $viewUrls[$key] }}"
                       class="lc-viewswitch-btn {{ $view === $key ? 'is-active' : '' }}"
                       @if($view === $key) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </div>
        {{-- The legend draws the marks the grid draws, at the size the grid
             draws them: a green circle, an amber dashed circle, a blue
             squircle. The last item names the dash itself, because that cue
             marks a pending travel order as well as a pending leave and there
             is no fourth colour to look for. --}}
        <div class="filter-card-actions lc-legend" role="list" aria-label="Marker key">
            <span class="lc-legend-item" role="listitem"><span class="lc-legend-dot is-leave-approved"></span>Approved leave</span>
            <span class="lc-legend-item" role="listitem"><span class="lc-legend-dot is-leave-pending"></span>Pending leave</span>
            <span class="lc-legend-item" role="listitem"><span class="lc-legend-dot is-travel"></span>Travel order</span>
            <span class="lc-legend-item" role="listitem"><span class="lc-legend-dot is-pending-swatch"></span>Dashed = awaiting approval</span>
        </div>
    </div>

    {{-- Filter bar — narrows the calendar to what this viewer wants to judge.
         Filtering happens in the query, so the stat strip and the "+X more"
         counts below describe the filtered month, not the whole one. The month
         travels as a hidden field so filtering keeps you where you are. --}}
    <form method="GET" action="{{ $filterAction }}" class="lc-filterbar" id="lcFilterForm">
        <input type="hidden" name="month" value="{{ $currentMonth }}">
        @if($embed)<input type="hidden" name="embed" value="1">@endif

        <div class="filter-card-fields lc-filter-fields">
            <span class="lc-toolbar-label">Show</span>

            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>
                <select name="type" class="fc-select" id="lcType" title="Record type">
                    <option value="">Leave &amp; Travel</option>
                    <option value="leave"  {{ $filterType === 'leave'  ? 'selected' : '' }}>Leave only</option>
                    <option value="travel" {{ $filterType === 'travel' ? 'selected' : '' }}>Travel orders only</option>
                </select>
            </div>

            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select name="status" class="fc-select" title="Approval status">
                    <option value="">All statuses</option>
                    <option value="approved" {{ $filterStatus === 'approved' ? 'selected' : '' }}>Approved only</option>
                    <option value="pending"  {{ $filterStatus === 'pending'  ? 'selected' : '' }}>Pending only</option>
                </select>
            </div>

            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                <select name="department" class="fc-select" title="Department">
                    <option value="">All departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $filterDept === $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fc-divider"></div>
            {{-- A leave code is something only leave applications carry: picking
                 one hides travel orders, and "Travel orders only" makes this
                 select meaningless, so it disables rather than quietly emptying
                 the month. leaveCalendar.js mirrors that on change. --}}
            <div class="fld" data-leave-type-field>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <select name="leave_code" class="fc-select" id="lcLeaveCode"
                        title="Leave type — selecting one shows leave records only"
                        @disabled($filterType === 'travel')>
                    <option value="">All leave types</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->leave_code }}" {{ $filterLeave === $lt->leave_code ? 'selected' : '' }}>{{ $lt->leave_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="filter-card-actions lc-filter-actions">
            <button type="submit" class="btn-solid">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
            @if($hasFilters)
                <a href="{{ $clearUrl }}" class="btn-ghost lc-clear-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Clear
                </a>
            @endif
        </div>
    </form>
    </div>

    @if($hasFilters && $filterLeave !== '' && $filterType !== 'leave')
        <p class="lc-filter-note">
            Showing leave only — travel orders carry no leave type.
        </p>
    @endif

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

    {{-- An empty month reads very differently when it is the filter emptying it. --}}
    @if($hasFilters && $summary['leave'] + $summary['travel'] === 0)
        <div class="lc-empty-note">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>No leave or travel matches these filters in {{ $monthLabel }}. <a href="{{ $clearUrl }}">Clear filters</a> to see the whole month.</span>
        </div>
    @endif

    @if($view === 'day')
    {{--
        Day view is a list, not a one-cell grid.

        Leave and travel are whole-day records, so an hour axis would imply a
        precision these dates do not carry — a leave application says "the 3rd
        to the 5th", never "09:00". What a day is actually asked is *who is
        out and why*, so the day shows each person in full instead.
    --}}
    @php $dayCell = $days[0] ?? null; @endphp
    <div class="lc-card lc-dayview">
        @if($dayCell && count($dayCell['events']))
            <div class="lc-dayview-list">
                @foreach($dayCell['events'] as $ev)
                    <button type="button"
                            class="lc-dayview-row type-{{ $ev['type'] }} status-{{ $ev['status'] }}"
                            data-payload='@json($ev['payload'])'
                            aria-label="Open {{ $ev['name'] }} — {{ $ev['type_label'] }}">
                        <span class="lc-dayview-avatar">
                            @if($ev['photo'])
                                <img src="{{ $ev['photo'] }}" alt="">
                            @else
                                <span class="cal-marker-initials" style="background:{{ $ev['color'] }}">{{ $ev['initials'] }}</span>
                            @endif
                        </span>
                        <span class="lc-dayview-body">
                            <span class="lc-dayview-name">{{ $ev['name'] }}</span>
                            <span class="lc-dayview-meta">
                                <span class="cal-tip-tag type-{{ $ev['type'] }}">{{ $ev['type_label'] }}</span>
                                <span class="lc-dayview-sub">{{ $ev['sub'] }}</span>
                            </span>
                            <span class="lc-dayview-range">{{ $ev['range_label'] }}</span>
                        </span>
                        <span class="lc-dayview-status {{ $ev['status'] === 'approved' ? 'is-approved' : 'is-pending' }}">{{ $ev['status_label'] }}</span>
                    </button>
                @endforeach
            </div>
        @else
            <div class="lc-dayview-empty">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p class="lc-dayview-empty-title">Nobody is out on {{ $monthLabel }}</p>
                <p class="lc-dayview-empty-sub">No leave or travel {{ $hasFilters ? 'matches these filters' : 'is recorded' }} for this day.</p>
            </div>
        @endif
    </div>
    @else
    <div class="lc-card">
        {{-- Weekday header. Week view names the actual dates, because in a
             seven-day view "Wed" alone does not say which Wednesday. --}}
        @if($view !== 'day')
        <div class="lc-grid lc-weekdays">
            @if($view === 'week')
                @foreach($days as $day)
                    <div class="lc-weekday {{ $day['is_weekend'] ? 'is-weekend' : '' }} {{ $day['is_today'] ? 'is-today' : '' }}">
                        {{ $day['date']->format('D') }} <span class="lc-weekday-num">{{ $day['date']->format('j') }}</span>
                    </div>
                @endforeach
            @else
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $wd)
                    <div class="lc-weekday {{ in_array($i, [0, 6]) ? 'is-weekend' : '' }}">{{ $wd }}</div>
                @endforeach
            @endif
        </div>
        @endif

        {{-- Day cells. Month keeps the compact avatar row; week has the height
             to name people, so it does. Day is a list, handled below. --}}
        <div class="lc-grid lc-days is-{{ $view }}">
            @foreach($days as $day)
                @php
                    $count = count($day['events']);
                    $dayLabel = $day['date']->format('l, F j, Y');
                    // Counts for the day list's subtitle, so the header says what
                    // kind of day it is before you read the rows.
                    $dayLeave  = collect($day['events'])->where('type', 'leave')->count();
                    $dayTravel = $count - $dayLeave;
                @endphp
                {{-- Clicking a date opens that date in day view. The whole cell
                     is the target for a mouse; the date itself is a real <a> so
                     the same thing is reachable by keyboard and by middle-click,
                     since wrapping the cell in a link would nest it around the
                     marker buttons. --}}
                <div class="lc-day {{ $day['in_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_weekend'] ? 'is-weekend' : '' }} {{ $count > 0 ? 'has-events' : '' }}"
                     data-day-url="{{ $day['day_url'] }}">
                    <a href="{{ $day['day_url'] }}" class="lc-day-head lc-day-open"
                       aria-label="Open {{ $dayLabel }}{{ $count > 0 ? ' — ' . $count . ' out (' . $dayLeave . ' on leave, ' . $dayTravel . ' travelling)' : ' — nobody out' }}">
                        <span class="lc-day-num">{{ $day['date']->format('j') }}</span>
                        @if($count > 0)
                            <span class="lc-day-count">{{ $count }}</span>
                        @endif
                    </a>

                    @if($count > 0)
                        <div class="cal-stack">
                        <div class="cal-markers">
                            @foreach($day['events'] as $ev)
                                @php $evName = $ev['name']; @endphp
                                @php
                                    // Named apart from the month-level $summary above — this
                                    // loop runs after the stat strip, but reusing the name
                                    // would clobber it for anything added between them.
                                    $evSummary = [
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
                                        data-summary='@json($evSummary)'
                                        data-payload='@json($ev['payload'])'
                                        aria-label="{{ $ev['name'] }} — {{ $ev['type_label'] }} ({{ $ev['status_label'] }})">
                                    @if($ev['photo'])
                                        <img src="{{ $ev['photo'] }}" alt="">
                                    @else
                                        <span class="cal-marker-initials" style="background:{{ $ev['color'] }}">{{ $ev['initials'] }}</span>
                                    @endif
                                    {{-- Week view has the room for a name; month
                                         does not, and CSS hides this there. --}}
                                    <span class="cal-marker-name">
                                        <span class="cal-marker-name-main">{{ $evName }}</span>
                                        <span class="cal-marker-name-sub">{{ $ev['sub'] }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        {{-- Only month truncates, and only when the height it
                             has genuinely cannot hold everyone: on the full
                             page nothing is hidden at all, and inside the
                             modal shared/calendarFit.js measures what fits and
                             fills this chip in. So it ships `hidden` with no
                             number — the count is not knowable server-side,
                             because it depends on the window the reader opened
                             the calendar in. With JavaScript off it stays
                             hidden and every marker shows.

                             Week never truncates: it has the height to list
                             everyone, so a "+X" there would hide rows that
                             already fit. The chip opens the day, which is
                             where the hidden ones are. --}}
                        @if($view === 'month')
                            <a href="{{ $day['day_url'] }}" class="cal-more" hidden
                               data-cal-more-label="Open {{ $dayLabel }} to see all {{ $count }} — %d more not shown"
                               aria-label="Open {{ $dayLabel }} to see all {{ $count }}"><span data-cal-more-count>+0</span></a>
                        @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

</main>


{{-- Hover summary tooltip --}}
<div id="calTooltip" class="cal-tooltip" style="display:none"></div>
