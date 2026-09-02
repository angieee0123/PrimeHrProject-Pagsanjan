<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\TravelOrder;
use Carbon\Carbon;

class AdminLeaveCalendarController extends Controller
{
    /**
     * Read-only availability calendar: shows who is on leave or on a travel
     * order each day so the admin can judge coverage before approving new
     * requests. Shows approved AND pending records; pending is styled distinctly
     * in the view. Full-page reload paging via ?month=YYYY-MM.
     *
     * Filters (?type, ?status, ?department, ?leave_code) narrow the calendar to
     * what the viewer actually wants to judge. They are applied in the query,
     * not hidden in CSS, so the stat strip and the "+X more" counts describe the
     * filtered month rather than the unfiltered one.
     */
    /**
     * The route this calendar's own links point at, and the view that draws it.
     *
     * The mayor gets the same read-only availability monitor over the same
     * query — {@see MayorLeaveCalendarController}, which overrides only these
     * two. Everything below is one computation for both surfaces, so the
     * mayor's calendar cannot drift from the admin's on what counts as
     * "out" or on how a month is paged.
     */
    protected function routeName(): string
    {
        return 'admin.leaveCalendar';
    }

    protected function viewName(): string
    {
        return 'admin.leaveCalendar.leaveCalendar';
    }

    public function index()
    {
        // Which month are we viewing? Fall back to the current month on a bad param.
        $monthParam = request('month');
        try {
            $cursor = $monthParam
                ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
                : Carbon::today()->startOfMonth();
        } catch (\Exception $e) {
            $cursor = Carbon::today()->startOfMonth();
        }

        // ---- View mode ---------------------------------------------------
        // Month, week or day, the way a calendar app offers them. Leave and
        // travel are whole-day records, so week and day show *more of each
        // record* rather than an hour grid — an hour axis would imply a
        // precision these dates do not carry.
        $view = in_array(request('view'), ['month', 'week', 'day'], true) ? request('view') : 'month';

        // Week and day are anchored by ?date=YYYY-MM-DD; month keeps ?month=
        // so existing links and the jump-to picker still work.
        try {
            $anchor = request('date')
                ? Carbon::createFromFormat('Y-m-d', request('date'))->startOfDay()
                : null;
        } catch (\Exception $e) {
            $anchor = null;
        }

        if (!$anchor) {
            // Switching month → week with no date lands on today when today is
            // in the month being viewed, and on the 1st otherwise. Jumping to
            // "this week" of a month you are not looking at would be a surprise.
            $anchor = Carbon::today()->between($cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth())
                ? Carbon::today()
                : $cursor->copy()->startOfMonth();
        }

        // A day the anchor belongs to is what the header and the summary
        // describe; the grid may be wider (month view pads to whole weeks).
        [$periodStart, $periodEnd] = match ($view) {
            'day'   => [$anchor->copy()->startOfDay(), $anchor->copy()->endOfDay()],
            'week'  => [$anchor->copy()->startOfWeek(Carbon::SUNDAY), $anchor->copy()->endOfWeek(Carbon::SATURDAY)],
            default => [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()],
        };

        // Month spans whole weeks (Sun–Sat) so leading/trailing days of adjacent
        // months fill the 7-column rows. Week and day show exactly their period.
        $gridStart = $view === 'month' ? $periodStart->copy()->startOfWeek(Carbon::SUNDAY) : $periodStart->copy();
        $gridEnd   = $view === 'month' ? $periodEnd->copy()->endOfWeek(Carbon::SATURDAY)   : $periodEnd->copy();

        // ---- Filters ----------------------------------------------------
        // Unknown values fall back to "no filter" rather than an empty calendar,
        // so a hand-edited URL cannot make the page look like nobody is out.
        $filterType   = in_array(request('type'), ['leave', 'travel'], true) ? request('type') : '';
        $filterStatus = in_array(request('status'), ['approved', 'pending'], true) ? request('status') : '';
        $filterDept   = ctype_digit((string) request('department')) ? (int) request('department') : null;
        $filterLeave  = trim((string) request('leave_code'));

        $departments = Department::whereHas('employmentDetails')->orderBy('name')->get(['id', 'name']);
        $leaveTypes  = LeaveType::orderBy('leave_name')->get(['leave_code', 'leave_name']);

        if ($filterLeave !== '' && !$leaveTypes->contains('leave_code', $filterLeave)) {
            $filterLeave = '';
        }
        // Travel orders carry no leave type, so "travel only" plus a leave code
        // describes nothing at all. The explicit type choice wins and the code
        // is dropped — otherwise a stale select silently empties the calendar.
        if ($filterType === 'travel') {
            $filterLeave = '';
        }
        if ($filterDept !== null && !$departments->contains('id', $filterDept)) {
            $filterDept = null;
        }

        $showLeaves = $filterType !== 'travel';
        $showTravel = $filterType !== 'leave' && $filterLeave === '';

        $filters = array_filter([
            'type'       => $filterType,
            'status'     => $filterStatus,
            'department' => $filterDept,
            'leave_code' => $filterLeave,
        ], fn($v) => $v !== '' && $v !== null);

        $eventsByDate = [];

        // ---- Leaves overlapping the visible grid (approved + pending) ----
        $leaves = collect();
        if ($showLeaves) {
            $leaves = LeaveApplication::with(['employee', 'leaveType'])
                ->whereIn('status', $filterStatus !== '' ? [$filterStatus] : ['approved', 'pending'])
                ->when($filterLeave !== '', fn($q) => $q->where('leave_code', $filterLeave))
                ->when($filterDept !== null, fn($q) => $q->whereHas(
                    'employee.employmentDetail',
                    fn($d) => $d->where('department_id', $filterDept)
                ))
                ->whereDate('start_date', '<=', $gridEnd)
                ->whereDate('end_date', '>=', $gridStart)
                ->get();
        }

        foreach ($leaves as $leave) {
            $emp = $leave->employee;
            if (!$emp) continue;

            $start = Carbon::parse($leave->start_date);
            $end   = Carbon::parse($leave->end_date);

            $event = [
                'type'        => 'leave',
                'status'      => $leave->status,
                'status_label'=> ucfirst($leave->status),
                'type_label'  => 'Leave',
                'sub'         => $leave->leaveType->leave_name ?? 'Leave',
                'range_label' => $this->rangeLabel($start, $end),
                'name'        => $emp->first_name . ' ' . $emp->last_name,
                'initials'    => $this->initials($emp),
                'color'       => $this->color($emp->id),
                'photo'       => $emp->photo,
                // Payload consumed on click → openAdminLeaveDetailModal(...)
                'payload'     => [
                    'kind'               => 'leave',
                    'id'                 => $leave->id,
                    'name'               => $emp->first_name . ' ' . $emp->last_name,
                    'employee_code'      => $emp->employee_id ?? 'N/A',
                    'leave_type'         => $leave->leaveType->leave_name ?? 'Leave',
                    'detail_start'       => $start->format('M d, Y'),
                    'detail_end'         => $end->format('M d, Y'),
                    'days'               => (float) $leave->number_of_days,
                    'reason'             => $leave->reason ?? '',
                    'status_label'       => ucfirst($leave->status),
                    'application_number' => $leave->application_number,
                    'attachment_url'     => $leave->attachment_path ? asset('storage/' . $leave->attachment_path) : '',
                    'approver_remarks'   => $leave->approver_remarks ?? '',
                ],
            ];

            $this->spread($eventsByDate, $event, $start, $end, $gridStart, $gridEnd);
        }

        // ---- Travel orders overlapping the visible grid (approved + pending) ----
        // 'awaiting_companions' is a pre-approval state, so it counts as pending.
        $travelStatuses = match ($filterStatus) {
            'approved' => ['approved'],
            'pending'  => ['pending', 'awaiting_companions'],
            default    => ['approved', 'pending', 'awaiting_companions'],
        };

        $travelOrders = collect();
        if ($showTravel) {
            $travelOrders = TravelOrder::with('employee')
                ->whereIn('status', $travelStatuses)
                ->when($filterDept !== null, fn($q) => $q->whereHas(
                    'employee.employmentDetail',
                    fn($d) => $d->where('department_id', $filterDept)
                ))
                ->whereDate('travel_date', '<=', $gridEnd)
                ->whereDate('return_date', '>=', $gridStart)
                ->get();
        }

        foreach ($travelOrders as $order) {
            $emp = $order->employee;
            if (!$emp) continue;

            $start  = Carbon::parse($order->travel_date);
            $end    = Carbon::parse($order->return_date);
            $status = $order->status === 'approved' ? 'approved' : 'pending';

            $event = [
                'type'        => 'travel',
                'status'      => $status,
                'status_label'=> $status === 'approved' ? 'Approved' : 'Pending',
                'type_label'  => 'Travel Order',
                'sub'         => $order->destination ?: 'Travel',
                'range_label' => $this->rangeLabel($start, $end),
                'name'        => $emp->first_name . ' ' . $emp->last_name,
                'initials'    => $this->initials($emp),
                'color'       => $this->color($emp->id),
                'photo'       => $emp->photo,
                // Payload consumed on click → viewOrder(id)
                'payload'     => [
                    'kind' => 'travel',
                    'id'   => $order->id,
                ],
            ];

            $this->spread($eventsByDate, $event, $start, $end, $gridStart, $gridEnd);
        }

        // Sort each day's events: approved before pending, then leaves before travel.
        foreach ($eventsByDate as $date => $events) {
            usort($events, function ($a, $b) {
                $sa = $a['status'] === 'approved' ? 0 : 1;
                $sb = $b['status'] === 'approved' ? 0 : 1;
                if ($sa !== $sb) return $sa <=> $sb;
                return strcmp($a['type'], $b['type']);
            });
            $eventsByDate[$date] = $events;
        }

        // Build the flat list of day cells for the grid.
        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $key = $d->toDateString();
            $days[] = [
                'date'       => $d->copy(),
                'key'        => $key,
                // Only month view pads with adjacent-month days to dim.
                'in_month'   => $view !== 'month' || $d->month === $cursor->month,
                'is_today'   => $d->isToday(),
                'is_weekend' => in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]),
                'events'     => $eventsByDate[$key] ?? [],
            ];
        }

        // Header + navigation. When opened inside the floating-button modal the
        // page loads with ?embed=1 (bare layout, no sidebar/FAB); the month
        // links must carry that flag so paging stays inside the modal iframe.
        // The active filters ride along too — stepping to the next month is
        // still the same question, so it should not silently widen the answer.
        $embed = request()->boolean('embed');
        $q = array_merge($embed ? ['embed' => 1] : [], $filters);

        // Every cell links to its own day view. Clicking a date is how you get
        // from "something is happening here" to what it actually is, so the
        // link is built for all days, not only the ones carrying events — an
        // empty day answers "is anyone out?" with the same click.
        //
        // The filters ride along for the same reason paging carries them:
        // the day is being opened out of a filtered month and should not
        // silently widen the answer on arrival.
        foreach ($days as $i => $cell) {
            $days[$i]['day_url'] = route($this->routeName(), array_merge($q, [
                'view' => 'day',
                'date' => $cell['key'],
            ]));
        }

        // Each view names its own period and steps by its own unit — a week
        // view whose arrows jumped a month would be a calendar you cannot walk.
        $monthLabel = match ($view) {
            'day'  => $periodStart->format('l, F j, Y'),
            'week' => $periodStart->isSameMonth($periodEnd)
                        ? $periodStart->format('M j') . ' – ' . $periodEnd->format('j, Y')
                        : $periodStart->format('M j') . ' – ' . $periodEnd->format('M j, Y'),
            default => $cursor->format('F Y'),
        };

        $currentMonth = $cursor->format('Y-m');           // <input type="month"> in month view
        $currentDate  = $periodStart->format('Y-m-d');    // <input type="date"> in week/day view

        // Keep the view on every link, or paging would silently drop you back
        // into month view.
        $qv = array_merge($q, $view !== 'month' ? ['view' => $view] : []);

        $step = fn (Carbon $d) => route($this->routeName(), $view === 'month'
            ? array_merge($qv, ['month' => $d->format('Y-m')])
            : array_merge($qv, ['date' => $d->format('Y-m-d')]));

        [$prevAnchor, $nextAnchor] = match ($view) {
            'day'   => [$periodStart->copy()->subDay(),   $periodStart->copy()->addDay()],
            'week'  => [$periodStart->copy()->subWeek(),  $periodStart->copy()->addWeek()],
            default => [$cursor->copy()->subMonth(),      $cursor->copy()->addMonth()],
        };

        $prevUrl  = $step($prevAnchor);
        $nextUrl  = $step($nextAnchor);
        $todayUrl = $view === 'month'
            ? route($this->routeName(), $qv)
            : route($this->routeName(), array_merge($qv, ['date' => Carbon::today()->format('Y-m-d')]));

        // The Month / Week / Day switcher. Each keeps the day you are looking
        // at, so switching re-frames the same date rather than jumping to now.
        // Week and day carry the *anchor*, not the period start. From August's
        // month view the period starts on the 1st, so anchoring the switch
        // there dropped you into the week of Jul 26 while today was Aug 11 —
        // the anchor is already "today when today is in view", which is the
        // day somebody switching views means.
        $viewUrls = [
            'month' => route($this->routeName(), array_merge($q, ['month' => $periodStart->format('Y-m')])),
            'week'  => route($this->routeName(), array_merge($q, ['view' => 'week', 'date' => $anchor->format('Y-m-d')])),
            'day'   => route($this->routeName(), array_merge($q, ['view' => 'day',  'date' => $anchor->format('Y-m-d')])),
        ];

        // Month-level summary for the stat strip. Counts records that actually
        // touch the displayed month (not the adjacent-month spillover days), each
        // record once regardless of how many days it spans.
        // Counts describe the period on screen — the week or the day when one of
        // those is showing, not the month it happens to sit in.
        $touchesPeriod = fn($s, $e) => Carbon::parse($s)->lte($periodEnd) && Carbon::parse($e)->gte($periodStart);

        $leaveInMonth  = $leaves->filter(fn($l) => $touchesPeriod($l->start_date, $l->end_date));
        $travelInMonth = $travelOrders->filter(fn($t) => $touchesPeriod($t->travel_date, $t->return_date));

        $summary = [
            'people'  => $leaveInMonth->pluck('employee_id')->merge($travelInMonth->pluck('employee_id'))->unique()->count(),
            'leave'   => $leaveInMonth->count(),
            'travel'  => $travelInMonth->count(),
            'pending' => $leaveInMonth->where('status', 'pending')->count()
                       + $travelInMonth->filter(fn($t) => $t->status !== 'approved')->count(),
        ];
        $peopleOut = $summary['people'];

        // Drives the "no records match" hint: an empty month reads very
        // differently when it is the filter doing the emptying.
        $hasFilters = count($filters) > 0;

        // Base for the <input type="month"> jump-to picker, which appends
        // &month=YYYY-MM client-side; carries embed + filters like the arrows do.
        $monthNavBase = route($this->routeName(), $qv);

        // The filter form posts month as a hidden field, so its action carries
        // only the embed flag — the selects themselves supply the rest.
        $filterAction = route($this->routeName(), $embed ? ['embed' => 1] : []);
        $clearUrl     = route($this->routeName(), array_merge(
            $embed ? ['embed' => 1] : [],
            $view === 'month'
                ? ['month' => $cursor->format('Y-m')]
                : ['view' => $view, 'date' => $periodStart->format('Y-m-d')]
        ));

        // Note: the per-day avatar cap (5) lives in the view/CSS — markers past
        // the 5th are hidden via .cal-markers .cal-marker:nth-child(n+6) and
        // surfaced through the "+X more" day popover.

        return view($this->viewName(), compact(
            'days', 'monthLabel', 'currentMonth', 'prevUrl', 'nextUrl', 'todayUrl', 'peopleOut', 'summary', 'embed',
            'departments', 'leaveTypes', 'filterType', 'filterStatus', 'filterDept', 'filterLeave',
            'hasFilters', 'filterAction', 'clearUrl', 'monthNavBase',
            'view', 'viewUrls', 'currentDate'
        ));
    }

    /**
     * Add an event to every day it covers within the visible grid.
     */
    private function spread(array &$bucket, array $event, Carbon $start, Carbon $end, Carbon $gridStart, Carbon $gridEnd): void
    {
        $from = $start->greaterThan($gridStart) ? $start->copy() : $gridStart->copy();
        $to   = $end->lessThan($gridEnd) ? $end->copy() : $gridEnd->copy();

        for ($d = $from->startOfDay(); $d->lte($to); $d->addDay()) {
            $bucket[$d->toDateString()][] = $event;
        }
    }

    private function initials($emp): string
    {
        return strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
    }

    private function color(int $id): string
    {
        $colors = ['#0b044d', '#8e1e18', '#15803d', '#a16207', '#7c3aed'];
        return $colors[$id % count($colors)];
    }

    private function rangeLabel(Carbon $start, Carbon $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->format('M d, Y');
        }
        return $start->format('M d') . ' – ' . $end->format('M d, Y');
    }
}
