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

        $gridStart = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

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
                'in_month'     => $d->month === $cursor->month,
                'is_today'     => $d->isToday(),
                'is_weekend'   => in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]),
                'events'       => $events,
                'primary_kind' => $events[0]['kind'] ?? null,   // drives the cell tint
            ];
        }

        // Navigation.
        $embed = request()->boolean('embed');
        $q = $embed ? ['embed' => 1] : [];
        $monthLabel   = $cursor->format('F Y');
        $currentMonth = $cursor->format('Y-m');
        $prevUrl  = route('employee.leaveCalendar', array_merge($q, ['month' => $cursor->copy()->subMonth()->format('Y-m')]));
        $nextUrl  = route('employee.leaveCalendar', array_merge($q, ['month' => $cursor->copy()->addMonth()->format('Y-m')]));
        $todayUrl = route('employee.leaveCalendar', $q);

        // Month summary.
        $monthStart = $cursor->copy()->startOfMonth();
        $monthEnd   = $cursor->copy()->endOfMonth();
        $touchesMonth = fn ($s, $e) => Carbon::parse($s)->lte($monthEnd) && Carbon::parse($e)->gte($monthStart);

        $leaveInMonth  = $leaves->filter(fn ($l) => $touchesMonth($l->start_date, $l->end_date));
        $travelInMonth = $travelOrders->filter(fn ($t) => $touchesMonth($t->travel_date, $t->return_date));

        // Distinct in-month days that carry any event.
        $daysOff = collect($eventsByDate)->keys()
            ->filter(fn ($d) => Carbon::parse($d)->between($monthStart, $monthEnd))
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
            'summary', 'embed', 'employee'
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
