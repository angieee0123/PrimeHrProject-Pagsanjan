<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\TravelOrder;
use Carbon\Carbon;

class AdminLeaveCalendarController extends Controller
{
    /**
     * Read-only availability calendar: shows who is on leave or on a travel
     * order each day so the admin can judge coverage before approving new
     * requests. Shows approved AND pending records; pending is styled distinctly
     * in the view. Full-page reload paging via ?month=YYYY-MM.
     */
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

        // The visible grid spans whole weeks (Sun–Sat) so leading/trailing days
        // of adjacent months fill the 7-column rows.
        $gridStart = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $eventsByDate = [];

        // ---- Leaves overlapping the visible grid (approved + pending) ----
        $leaves = LeaveApplication::with(['employee', 'leaveType'])
            ->whereIn('status', ['approved', 'pending'])
            ->whereDate('start_date', '<=', $gridEnd)
            ->whereDate('end_date', '>=', $gridStart)
            ->get();

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
        $travelOrders = TravelOrder::with('employee')
            ->whereIn('status', ['approved', 'pending', 'awaiting_companions'])
            ->whereDate('travel_date', '<=', $gridEnd)
            ->whereDate('return_date', '>=', $gridStart)
            ->get();

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
                'in_month'   => $d->month === $cursor->month,
                'is_today'   => $d->isToday(),
                'is_weekend' => in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]),
                'events'     => $eventsByDate[$key] ?? [],
            ];
        }

        // Header + navigation. When opened inside the floating-button modal the
        // page loads with ?embed=1 (bare layout, no sidebar/FAB); the month
        // links must carry that flag so paging stays inside the modal iframe.
        $embed = request()->boolean('embed');
        $q = $embed ? ['embed' => 1] : [];

        $monthLabel   = $cursor->format('F Y');
        $currentMonth = $cursor->format('Y-m');   // feeds the <input type="month"> jump-to picker
        $prevUrl  = route('admin.leaveCalendar', array_merge($q, ['month' => $cursor->copy()->subMonth()->format('Y-m')]));
        $nextUrl  = route('admin.leaveCalendar', array_merge($q, ['month' => $cursor->copy()->addMonth()->format('Y-m')]));
        $todayUrl = route('admin.leaveCalendar', $q);

        // Month-level summary for the stat strip. Counts records that actually
        // touch the displayed month (not the adjacent-month spillover days), each
        // record once regardless of how many days it spans.
        $monthStart = $cursor->copy()->startOfMonth();
        $monthEnd   = $cursor->copy()->endOfMonth();
        $touchesMonth = fn($s, $e) => Carbon::parse($s)->lte($monthEnd) && Carbon::parse($e)->gte($monthStart);

        $leaveInMonth  = $leaves->filter(fn($l) => $touchesMonth($l->start_date, $l->end_date));
        $travelInMonth = $travelOrders->filter(fn($t) => $touchesMonth($t->travel_date, $t->return_date));

        $summary = [
            'people'  => $leaveInMonth->pluck('employee_id')->merge($travelInMonth->pluck('employee_id'))->unique()->count(),
            'leave'   => $leaveInMonth->count(),
            'travel'  => $travelInMonth->count(),
            'pending' => $leaveInMonth->where('status', 'pending')->count()
                       + $travelInMonth->filter(fn($t) => $t->status !== 'approved')->count(),
        ];
        $peopleOut = $summary['people'];

        // Note: the per-day avatar cap (5) lives in the view/CSS — markers past
        // the 5th are hidden via .cal-markers .cal-marker:nth-child(n+6) and
        // surfaced through the "+X more" day popover.

        return view('admin.leaveCalendar.leaveCalendar', compact(
            'days', 'monthLabel', 'currentMonth', 'prevUrl', 'nextUrl', 'todayUrl', 'peopleOut', 'summary', 'embed'
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
