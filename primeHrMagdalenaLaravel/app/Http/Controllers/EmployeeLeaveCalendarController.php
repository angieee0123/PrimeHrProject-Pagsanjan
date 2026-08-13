<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\TravelOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveCalendarController extends Controller
{
    /**
     * The logged-in employee's OWN leave and travel schedule on a month grid —
     * a personal "my time off" calendar. Read-only, and strictly self-scoped:
     * it only ever queries Auth::user()->employee, never anyone else.
     *
     * Same month colour language as the shared busy-date pickers:
     *   green = approved leave · amber = pending leave · blue = travel order.
     */
    public function index()
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        // Which month?
        $monthParam = request('month');
        try {
            $cursor = $monthParam
                ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
                : Carbon::today()->startOfMonth();
        } catch (\Exception $e) {
            $cursor = Carbon::today()->startOfMonth();
        }

        // ---- View mode ---------------------------------------------------
        // Month, week or day, matching the admin calendar. Leave and travel are
        // whole-day records, so week and day show more of each record rather
        // than an hour grid — an hour axis would imply a precision these dates
        // do not carry.
        $view = in_array(request('view'), ['month', 'week', 'day'], true) ? request('view') : 'month';

        try {
            $anchor = request('date')
                ? Carbon::createFromFormat('Y-m-d', request('date'))->startOfDay()
                : null;
        } catch (\Exception $e) {
            $anchor = null;
        }

        if (!$anchor) {
            // Switching month → week with no date lands on today when today is
            // in the month being viewed, and on the 1st otherwise.
            $anchor = Carbon::today()->between($cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth())
                ? Carbon::today()
                : $cursor->copy()->startOfMonth();
        }

        [$periodStart, $periodEnd] = match ($view) {
            'day'   => [$anchor->copy()->startOfDay(), $anchor->copy()->endOfDay()],
            'week'  => [$anchor->copy()->startOfWeek(Carbon::SUNDAY), $anchor->copy()->endOfWeek(Carbon::SATURDAY)],
            default => [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()],
        };

        $gridStart = $view === 'month' ? $periodStart->copy()->startOfWeek(Carbon::SUNDAY) : $periodStart->copy();
        $gridEnd   = $view === 'month' ? $periodEnd->copy()->endOfWeek(Carbon::SATURDAY)   : $periodEnd->copy();

        $eventsByDate = [];
        $leaves = collect();
        $travelOrders = collect();

        if ($employee) {
            // ---- My leaves overlapping the visible grid (approved + pending) ----
            $leaves = LeaveApplication::with('leaveType')
                ->where('employee_id', $employee->id)
                ->whereIn('status', ['approved', 'pending'])
                ->whereDate('start_date', '<=', $gridEnd)
                ->whereDate('end_date', '>=', $gridStart)
                ->get();

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end   = Carbon::parse($leave->end_date);
                $event = [
                    'type'         => 'leave',
                    'status'       => $leave->status,
                    'kind'         => 'leave-' . $leave->status,          // leave-approved | leave-pending
                    'status_label' => ucfirst($leave->status),
                    'label'        => $leave->leaveType->leave_name ?? 'Leave',
                    'range_label'  => $this->rangeLabel($start, $end),
                    'priority'     => $leave->status === 'approved' ? 3 : 2,
                ];
                $this->spread($eventsByDate, $event, $start, $end, $gridStart, $gridEnd);
            }

            // ---- My travel orders overlapping the visible grid ----
            $travelOrders = TravelOrder::where('employee_id', $employee->id)
                ->whereIn('status', ['approved', 'pending', 'awaiting_companions'])
                ->whereDate('travel_date', '<=', $gridEnd)
                ->whereDate('return_date', '>=', $gridStart)
                ->get();

            foreach ($travelOrders as $order) {
                $start  = Carbon::parse($order->travel_date);
                $end    = Carbon::parse($order->return_date);
                $status = $order->status === 'approved' ? 'approved' : 'pending';
                $event = [
                    'type'         => 'travel',
                    'status'       => $status,
                    'kind'         => 'travel-' . $status,               // travel-approved | travel-pending
                    'status_label' => $status === 'approved' ? 'Approved' : 'Pending',
                    'label'        => $order->destination ? 'Travel · ' . $order->destination : 'Travel Order',
                    'range_label'  => $this->rangeLabel($start, $end),
                    'priority'     => 1,
                ];
                $this->spread($eventsByDate, $event, $start, $end, $gridStart, $gridEnd);
            }
        }

        // Sort each day's events by priority (approved leave first, travel last).
        foreach ($eventsByDate as $date => $events) {
            usort($events, fn ($a, $b) => $b['priority'] <=> $a['priority']);
            $eventsByDate[$date] = $events;
        }

        // Day cells.
        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $key = $d->toDateString();
            $events = $eventsByDate[$key] ?? [];
            $days[] = [
                'date'         => $d->copy(),
                'key'          => $key,
                // Only month view pads with adjacent-month days to dim.
                'in_month'     => $view !== 'month' || $d->month === $cursor->month,
                'is_today'     => $d->isToday(),
                'is_weekend'   => in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]),
                'events'       => $events,
                'primary_kind' => $events[0]['kind'] ?? null,   // drives the cell tint
            ];
        }

        // Navigation.
        $embed = request()->boolean('embed');
        $q = $embed ? ['embed' => 1] : [];

        // Every cell links to its own day view — clicking a date is how you get
        // from "something is happening here" to what it actually is. Built for
        // every day, not only the ones carrying events, so an empty date still
        // answers the question rather than doing nothing.
        foreach ($days as $i => $cell) {
            $days[$i]['day_url'] = route('employee.leaveCalendar', array_merge($q, [
                'view' => 'day',
                'date' => $cell['key'],
            ]));
        }
        // Each view names its own period and steps by its own unit.
        $monthLabel = match ($view) {
            'day'  => $periodStart->format('l, F j, Y'),
            'week' => $periodStart->isSameMonth($periodEnd)
                        ? $periodStart->format('M j') . ' – ' . $periodEnd->format('j, Y')
                        : $periodStart->format('M j') . ' – ' . $periodEnd->format('M j, Y'),
            default => $cursor->format('F Y'),
        };

        $currentMonth = $cursor->format('Y-m');
        $currentDate  = $periodStart->format('Y-m-d');

        // Keep the view on every link, or paging would drop back to month view.
        $qv = array_merge($q, $view !== 'month' ? ['view' => $view] : []);

        $step = fn (Carbon $d) => route('employee.leaveCalendar', $view === 'month'
            ? array_merge($qv, ['month' => $d->format('Y-m')])
            : array_merge($qv, ['date' => $d->format('Y-m-d')]));

        [$prevAnchor, $nextAnchor] = match ($view) {
            'day'   => [$periodStart->copy()->subDay(),  $periodStart->copy()->addDay()],
            'week'  => [$periodStart->copy()->subWeek(), $periodStart->copy()->addWeek()],
            default => [$cursor->copy()->subMonth(),     $cursor->copy()->addMonth()],
        };

        $prevUrl  = $step($prevAnchor);
        $nextUrl  = $step($nextAnchor);
        $todayUrl = $view === 'month'
            ? route('employee.leaveCalendar', $qv)
            : route('employee.leaveCalendar', array_merge($qv, ['date' => Carbon::today()->format('Y-m-d')]));

        // Week and day carry the anchor, not the period start: from August's
        // month view the period starts on the 1st, and switching there would
        // drop you into the week of Jul 26 while today is Aug 13.
        $viewUrls = [
            'month' => route('employee.leaveCalendar', array_merge($q, ['month' => $periodStart->format('Y-m')])),
            'week'  => route('employee.leaveCalendar', array_merge($q, ['view' => 'week', 'date' => $anchor->format('Y-m-d')])),
            'day'   => route('employee.leaveCalendar', array_merge($q, ['view' => 'day',  'date' => $anchor->format('Y-m-d')])),
        ];

        $monthNavBase = route('employee.leaveCalendar', $qv);

        // Month summary.
        // Counts describe the period on screen — the week or the day when one of
        // those is showing, not the month it happens to sit in.
        $touchesPeriod = fn ($s, $e) => Carbon::parse($s)->lte($periodEnd) && Carbon::parse($e)->gte($periodStart);

        $leaveInMonth  = $leaves->filter(fn ($l) => $touchesPeriod($l->start_date, $l->end_date));
        $travelInMonth = $travelOrders->filter(fn ($t) => $touchesPeriod($t->travel_date, $t->return_date));

        // Distinct days inside the period that carry any event.
        $daysOff = collect($eventsByDate)->keys()
            ->filter(fn ($d) => Carbon::parse($d)->between($periodStart, $periodEnd))
            ->count();

        $summary = [
            'days_off' => $daysOff,
            'leave'    => $leaveInMonth->count(),
            'travel'   => $travelInMonth->count(),
            'pending'  => $leaveInMonth->where('status', 'pending')->count()
                        + $travelInMonth->filter(fn ($t) => $t->status !== 'approved')->count(),
        ];

        return view('employee.leaveCalendar.leaveCalendar', compact(
            'days', 'monthLabel', 'currentMonth', 'prevUrl', 'nextUrl', 'todayUrl',
            'summary', 'embed', 'employee',
            'view', 'viewUrls', 'currentDate', 'monthNavBase'
        ));
    }

    private function spread(array &$bucket, array $event, Carbon $start, Carbon $end, Carbon $gridStart, Carbon $gridEnd): void
    {
        $from = $start->greaterThan($gridStart) ? $start->copy() : $gridStart->copy();
        $to   = $end->lessThan($gridEnd) ? $end->copy() : $gridEnd->copy();

        for ($d = $from->startOfDay(); $d->lte($to); $d->addDay()) {
            $bucket[$d->toDateString()][] = $event;
        }
    }

    private function rangeLabel(Carbon $start, Carbon $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->format('M d, Y');
        }
        return $start->format('M d') . ' – ' . $end->format('M d, Y');
    }
}
