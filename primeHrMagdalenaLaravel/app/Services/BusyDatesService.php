<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\TravelOrder;
use Carbon\Carbon;

/**
 * The date ranges an employee is already committed to — leaves and travel
 * orders — as consumed by the busy-date calendars (busyDatesCalendar.js) in
 * the employee filing modals and the admin modals.
 *
 * Shared by /employee/busy-dates (own record) and /admin/employee-busy-dates
 * (any employee, admin/HR only) so both always report the same thing.
 */
class BusyDatesService
{
    /** Statuses that make a date "taken" — cancelled/rejected ones are free again. */
    public const BUSY_LEAVE_STATUSES = ['pending', 'approved'];
    public const BUSY_TRAVEL_STATUSES = ['pending', 'approved', 'awaiting_companions'];

    public static function forEmployee(?Employee $employee): array
    {
        if (!$employee) {
            return ['leaves' => [], 'travel_orders' => []];
        }

        return [
            'leaves' => LeaveApplication::where('employee_id', $employee->id)
                ->whereIn('status', self::BUSY_LEAVE_STATUSES)
                ->get(['start_date', 'end_date', 'status', 'leave_code'])
                ->map(fn ($l) => [
                    'start' => Carbon::parse($l->start_date)->toDateString(),
                    'end' => Carbon::parse($l->end_date)->toDateString(),
                    'status' => $l->status,
                    'label' => $l->leave_code . ' leave (' . $l->status . ')',
                ]),
            'travel_orders' => TravelOrder::where('employee_id', $employee->id)
                ->whereIn('status', self::BUSY_TRAVEL_STATUSES)
                ->get(['travel_date', 'return_date', 'status', 'destination'])
                ->map(fn ($t) => [
                    'start' => Carbon::parse($t->travel_date)->toDateString(),
                    'end' => Carbon::parse($t->return_date)->toDateString(),
                    'status' => $t->status,
                    'label' => 'Travel: ' . $t->destination . ' (' . str_replace('_', ' ', $t->status) . ')',
                ]),
        ];
    }
}
