<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Services\CscTimeConversionService;
use App\Services\CsvReportWriter;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return view('employee.attendance.employeeAttendance')->with('error', 'Employee record not found.');
        }

        $payload = $this->buildAttendancePayload($employee, $request);

        return view('employee.attendance.employeeAttendance', array_merge($payload, [
            'employee' => $employee,
        ]));
    }

    /**
     * Attendance summary + DTR records for API consumers (mobile app).
     */
    public function buildAttendancePayload(Employee $employee, Request $request): array
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Get attendance records
        $attendances = Attendance::with('accreditedHoursLogs')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        // Get approved leaves
        $approvedLeaves = \App\Models\LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->with('leaveType')
            ->get();

        // Calculate statistics
        $present = 0;
        $absent = 0;
        $late = 0;
        $halfday = 0;
        $overtime = 0;
        $onLeave = 0;
        $totalLateMinutes = 0;
        $totalUndertimeMinutes = 0;

        $graceMinutes = 5;
        $workingDays = $this->getWorkingDays($startDate, $endDate);
        $attendedDates = $attendances->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

        // Get all leave dates
        $leaveDates = [];
        foreach ($approvedLeaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = Carbon::parse($leave->end_date);
            $current = $leaveStart->copy();
            
            while ($current->lte($leaveEnd)) {
                if (!in_array($current->dayOfWeek, [0, 6])) {
                    $leaveDates[] = $current->format('Y-m-d');
                }
                $current->addDay();
            }
        }
        $leaveDates = array_unique($leaveDates);

        foreach ($attendances as $attendance) {
            $hasAttendance = $attendance->am_in || $attendance->pm_in;

            if ($hasAttendance) {
                $present++;

                $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');
                $scheduleForDate = $employee->getScheduleForDate($attendanceDate);
                $expectedAmIn = $scheduleForDate ? Carbon::parse($scheduleForDate->am_in) : Carbon::parse('08:00:00');
                $graceThreshold = $expectedAmIn->copy()->addMinutes($graceMinutes);

                // Check if late
                if ($attendance->am_in) {
                    $amInTime = Carbon::parse($attendance->am_in);
                    if ($amInTime->gt($graceThreshold)) {
                        $late++;
                        $totalLateMinutes += $expectedAmIn->diffInMinutes($amInTime);
                    }
                }

                // Check half day
                $hasAM = $attendance->am_in && $attendance->am_out;
                $hasPM = $attendance->pm_in && $attendance->pm_out;
                if (($hasAM && !$hasPM) || (!$hasAM && $hasPM)) {
                    $halfday++;
                }

                // Calculate overtime
                if ($attendance->ot_in && $attendance->ot_out) {
                    $otIn = Carbon::parse($attendance->ot_in);
                    $otOut = Carbon::parse($attendance->ot_out);
                    $overtime += $otIn->diffInHours($otOut, false);
                }

                // Get undertime from accredited hours log
                if ($attendance->accreditedHoursLogs->isNotEmpty()) {
                    $log = $attendance->accreditedHoursLogs->last();
                    $totalUndertimeMinutes += $log->undertime_minutes;
                }
            }
        }

        // Calculate absences
        foreach ($workingDays as $workingDay) {
            $dayStr = $workingDay->format('Y-m-d');
            if (!in_array($dayStr, $attendedDates)) {
                if (in_array($dayStr, $leaveDates)) {
                    $onLeave++;
                    $present++;
                } else {
                    $absent++;
                }
            }
        }

        $totalDays = $present + $absent;
        $rate = $totalDays > 0 ? number_format(($present / $totalDays) * 100, 0) : 0;
        $workingDaysCount = count($workingDays);

        // Format attendance records for display
        $records = $attendances->map(function($attendance) use ($employee) {
            $date = Carbon::parse($attendance->date);
            
            return [
                'date' => $date->format('M d'),
                'day' => $date->format('D'),
                'in' => $attendance->am_in ? Carbon::parse($attendance->am_in)->format('g:i A') : '—',
                'out' => $attendance->pm_out ? Carbon::parse($attendance->pm_out)->format('g:i A') : '—',
                'ot' => ($attendance->ot_in && $attendance->ot_out) 
                    ? '+' . Carbon::parse($attendance->ot_in)->diffInHours(Carbon::parse($attendance->ot_out)) . 'h'
                    : '—',
                'status' => $this->getAttendanceStatus($attendance, $employee),
            ];
        });

        $periodDisplay = $startDate->format('F Y');

        $detailedRecords = $this->fetchDetailedRecords(
            $employee,
            $startDate->copy(),
            $endDate->copy()
        );

        return [
            'records' => $records,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'halfday' => $halfday,
            'overtime' => $overtime,
            'onLeave' => $onLeave,
            'rate' => $rate,
            'workingDaysCount' => $workingDaysCount,
            'periodDisplay' => $periodDisplay,
            'totalLateMinutes' => $totalLateMinutes,
            'totalUndertimeMinutes' => $totalUndertimeMinutes,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'dtr_records' => $detailedRecords,
        ];
    }

    public function detailedDTR(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'Employee record not found'], 404);
        }

        try {
            $payload = $this->buildDetailedPayload($employee, $request);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json($payload);
    }

    /**
     * @return array{records: array, employee: array, period_start: string, period_end: string}
     */
    public function buildDetailedPayload(Employee $employee, Request $request): array
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$startDate || !$endDate) {
            throw new \InvalidArgumentException('Start date and end date are required');
        }

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if ($startDate->gt($endDate)) {
            throw new \InvalidArgumentException('Start date must be before end date');
        }

        $records = $this->fetchDetailedRecords($employee, $startDate, $endDate);

        return [
            'records' => $records,
            'employee' => [
                'name' => trim($employee->first_name . ' ' . $employee->last_name),
                'employee_id' => $employee->employee_id,
            ],
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    private function fetchDetailedRecords(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $attendances = Attendance::with(['accreditedHoursLogs.schedule'])
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function ($a) {
                return Carbon::parse($a->date)->format('Y-m-d');
            });

        $approvedLeaves = \App\Models\LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->with('leaveType')
            ->get();

        $approvedTravelOrders = \App\Models\TravelOrder::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('travel_date', [$startDate, $endDate])
                    ->orWhereBetween('return_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('travel_date', '<=', $startDate)
                            ->where('return_date', '>=', $endDate);
                    });
            })
            ->get();

        return $this->generateDetailedRecords(
            $startDate,
            $endDate,
            $attendances,
            $employee,
            $approvedLeaves,
            $approvedTravelOrders
        );
    }

    /**
     * "Export" on the Detailed Time Record toolbar.
     *
     * The button used to build the file in the browser out of `detailedRecords`
     * — the array as it was *fetched*, before the toolbar touched it. So the
     * View dropdown and the topbar search narrowed the table on screen and the
     * download ignored both: an employee who had filtered down to their six
     * late days got every day of the month back, with no letterhead, no
     * municipality and nothing saying what it covered.
     *
     * It is now a server-side endpoint like every other export here, and it
     * re-runs `fetchDetailedRecords()` — the *same* method the page renders
     * from, so the file and the screen cannot disagree about what a day was.
     * The toolbar's three filters reach it as query params.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('employee.attendance')
                ->with('error', 'No employee record found.');
        }

        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $startDate = $request->get('start_date') ?: now()->startOfMonth()->format('Y-m-d');
        $endDate   = $request->get('end_date') ?: now()->endOfMonth()->format('Y-m-d');

        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate   = Carbon::parse($endDate)->endOfDay();
        } catch (\Throwable $e) {
            return redirect()->route('employee.attendance')
                ->with('error', 'Export failed: the date range could not be read.');
        }

        if ($startDate->gt($endDate)) {
            return redirect()->route('employee.attendance')
                ->with('error', 'Export failed: the start date is after the end date.');
        }

        // "all" is the dropdown's own word for no filter; anything
        // unrecognised is treated the same way rather than silently handing
        // back an empty file.
        $view = strtolower(trim((string) $request->get('view')));
        if (!array_key_exists($view, self::DTR_VIEWS)) {
            $view = 'all';
        }

        $search = trim((string) $request->get('search'));

        $records = collect($this->fetchDetailedRecords($employee, $startDate->copy(), $endDate->copy()))
            ->map(fn (array $record) => $record + ['state' => $this->recordState($record)])
            ->filter(fn (array $record) => $this->matchesView($record, $view))
            ->filter(fn (array $record) => $this->matchesSearch($record, $search))
            ->values();

        $dateRange = $startDate->format('M_d_Y') . '_to_' . $endDate->format('M_d_Y');
        $fileName  = "Daily_Time_Record_{$employee->employee_id}_{$dateRange}.csv";

        return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
            $records, $employee, $startDate, $endDate, $view, $search
        ) {
            $csv->letterhead(
                'Daily Time Record',
                'Human Resource Management Office · PRIME HRIS',
                CsvReportWriter::longDate($startDate) . ' to ' . CsvReportWriter::longDate($endDate)
            );

            $csv->parameters([
                'Employee:'            => trim($employee->first_name . ' ' . $employee->last_name),
                'Employee ID:'         => $employee->employee_id,
                'Position:'            => $employee->employmentDetail->designationRelation->title ?? 'Unassigned',
                'Department / Office:' => $employee->employmentDetail->departmentRelation->name ?? 'Unassigned',
                'Date From:'           => CsvReportWriter::longDate($startDate),
                'Date To:'             => CsvReportWriter::longDate($endDate),
                'View:'                => self::DTR_VIEWS[$view],
                'Search Term:'         => $search !== '' ? $search : 'None',
            ], $records->count());

            $csv->columns([
                'No.', 'Date', 'Day',
                'AM In', 'AM Out', 'PM In', 'PM Out',
                'Overtime In', 'Overtime Out', 'Overtime (min)',
                'Late (min)', 'Undertime (min)',
                'Accredited Hours', 'Status', 'Leave / Travel Reference',
            ]);

            foreach ($records as $index => $record) {
                $csv->row([
                    $index + 1,
                    CsvReportWriter::date(Carbon::parse($record['date_key'])),
                    $record['day'],
                    $this->timeCell($record, 'am_in'),
                    $this->timeCell($record, 'am_out'),
                    $this->timeCell($record, 'pm_in'),
                    $this->timeCell($record, 'pm_out'),
                    $this->timeCell($record, 'ot_in'),
                    $this->timeCell($record, 'ot_out'),
                    $this->overtimeMinutes($record),
                    (int) ($record['late_minutes'] ?? 0),
                    (int) ($record['undertime'] ?? 0),
                    $this->hours(((int) ($record['accredited_minutes'] ?? 0)) / 60),
                    self::DTR_STATUS_LABELS[$record['state']],
                    $this->dayReference($record),
                ]);
            }

            if ($records->isEmpty()) {
                $csv->emptyNotice('No time records matched the filters above.');
            }

            // Totalled over the *exported* rows rather than the whole period:
            // the file has to add up to its own table, which is the first
            // thing anyone checks a register against.
            $lateMinutes      = $records->sum(fn (array $r) => (int) ($r['late_minutes'] ?? 0));
            $undertimeMinutes = $records->sum(fn (array $r) => (int) ($r['undertime'] ?? 0));
            $overtimeMinutes  = $records->sum(fn (array $r) => $this->overtimeMinutes($r));
            $accreditedMins   = $records->sum(fn (array $r) => (int) ($r['accredited_minutes'] ?? 0));
            $byState          = $records->countBy('state');

            $csv->summary('Summary', [
                'Days Covered:'           => $records->count(),
                // Any day with a punch on it, which is what the Present KPI
                // card above the table counts — a Saturday somebody came in on
                // is a day they were present, however the row is badged.
                'Days Present:'           => $records->filter(fn (array $r) => $this->hasAnyPunch($r))->count(),
                'Days Absent:'            => $byState['absent'] ?? 0,
                'Days on Leave:'          => $records->where('is_on_leave', true)->count(),
                'Days on Travel Order:'   => $records->where('is_on_travel_order', true)->count(),
                'Incomplete Logs:'        => $byState['incomplete'] ?? 0,
                'Rest Days / Weekends:'   => $byState['weekend'] ?? 0,
                'Days Late:'              => $byState['late'] ?? 0,
                'Total Late:'             => CscTimeConversionService::formatMinutes($lateMinutes),
                'Total Undertime:'        => CscTimeConversionService::formatMinutes($undertimeMinutes),
                'Total Overtime:'         => CscTimeConversionService::formatMinutes($overtimeMinutes),
                'Total Accredited Hours:' => $this->hours($accreditedMins / 60),
            ]);

            $csv->notes([
                'Accredited Hours is the figure payroll credits for the day — the schedule met, less late and undertime. An approved leave or travel order accredits a full eight hours.',
                'Times are 24-hour (HH:MM). "Log Missing" is a working day with no punch on that slot; a rest day or an approved absence with no punch reads as a dash.',
                'A day marked Absent has no punch and no approved leave or travel order covering it.',
            ]);
        });
    }

    /**
     * The View dropdown's options, keyed by the `data-chip` the toolbar sends.
     *
     * The labels are the dropdown's own words, printed back into the file's
     * parameter block — a reader has to be able to tell "this covers the whole
     * month" from "this covers the Mondays".
     */
    private const DTR_VIEWS = [
        'all'        => 'All Records',
        'mon'        => 'Mondays only',
        'tue'        => 'Tuesdays only',
        'wed'        => 'Wednesdays only',
        'thu'        => 'Thursdays only',
        'fri'        => 'Fridays only',
        'weekdays'   => 'Weekdays (Mon-Fri)',
        'weekend'    => 'Weekends only',
        'present'    => 'Present',
        'absent'     => 'Absent',
        'late'       => 'Late',
        'leave'      => 'On Leave',
        'incomplete' => 'Incomplete',
    ];

    /** How each row state is spelled in the file's Status column. */
    private const DTR_STATUS_LABELS = [
        'present'    => 'Present',
        'late'       => 'Late',
        'incomplete' => 'Incomplete',
        'absent'     => 'Absent',
        'leave'      => 'On Leave / Travel Order',
        'weekend'    => 'Rest Day',
    ];

    /** The weekday names the dropdown's day options resolve to. */
    private const DTR_VIEW_DAYS = [
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
    ];

    /**
     * The one classification behind the timeline dot, the status badge and the
     * View dropdown — resolved here so the file and the screen cannot disagree
     * about what a day was.
     *
     * Mirrors `renderDetailedDTR()` in `employeeAttendance.js`, including the
     * precedence: an approved leave or travel order outranks the weekend, and
     * a day with some punches but not all four is Incomplete before it is Late.
     */
    private function recordState(array $record): string
    {
        $isWeekend = in_array($record['day'] ?? '', ['Saturday', 'Sunday'], true);
        $isCovered = !empty($record['is_on_leave']) || !empty($record['is_on_travel_order']);

        $slots = array_map(
            fn (string $slot) => $this->isTime($record[$slot] ?? null),
            ['am_in', 'am_out', 'pm_in', 'pm_out']
        );

        $hasAnyLog  = in_array(true, $slots, true);
        $isComplete = !in_array(false, $slots, true);

        return match (true) {
            $isCovered                               => 'leave',
            !$isWeekend && !$hasAnyLog               => 'absent',
            $isWeekend                               => 'weekend',
            !$isComplete                             => 'incomplete',
            (int) ($record['late_minutes'] ?? 0) > 0 => 'late',
            default                                  => 'present',
        };
    }

    /** The same branch table as `applyDtrChip()` in `employeeAttendance.js`. */
    private function matchesView(array $record, string $view): bool
    {
        $day   = $record['day'] ?? '';
        $state = $record['state'];

        if (isset(self::DTR_VIEW_DAYS[$view])) {
            return $day === self::DTR_VIEW_DAYS[$view];
        }

        return match ($view) {
            'all'      => true,
            'weekdays' => in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], true),
            'weekend'  => in_array($day, ['Saturday', 'Sunday'], true),
            default    => $state === $view,
        };
    }

    /**
     * The topbar search box narrows the table by matching each row's rendered
     * text. Matched here against the fields that row is *built* from — the
     * date in both spellings it shows, the weekday, the status word and the
     * leave or travel reference — so a narrowed table and its export agree.
     */
    private function matchesSearch(array $record, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $haystack = mb_strtolower(implode(' ', array_filter([
            $record['date'] ?? '',
            $record['date_key'] ?? '',
            $record['day'] ?? '',
            self::DTR_STATUS_LABELS[$record['state']],
            $this->dayReference($record),
        ])));

        return str_contains($haystack, mb_strtolower($search));
    }

    /**
     * A punch, or what its absence means.
     *
     * Leave and travel rows carry the sentinel strings the table prints in
     * place of times, so they are never written into a time column as though
     * somebody had logged one.
     */
    private function timeCell(array $record, string $slot): string
    {
        $value = $record[$slot] ?? null;

        if ($this->isTime($value)) {
            return $value;
        }

        if ($record['state'] === 'leave' || $record['state'] === 'weekend') {
            return '—';
        }

        // Overtime is not scheduled, so an empty OT slot is not a missing log.
        return in_array($slot, ['ot_in', 'ot_out'], true) ? '—' : 'Log Missing';
    }

    /** What covered the day, when something did — the table's last column. */
    private function dayReference(array $record): string
    {
        if (!empty($record['is_on_travel_order']) && !empty($record['travel_order_info'])) {
            $t = $record['travel_order_info'];

            return trim(($t['order_number'] ?? 'Travel Order') . ' - ' . ($t['destination'] ?? ''), ' -');
        }

        if (!empty($record['is_on_leave']) && !empty($record['leave_info'])) {
            $l = $record['leave_info'];

            return trim(($l['leave_code'] ?? '') . ' - ' . ($l['leave_type'] ?? ''), ' -');
        }

        $deduction = $record['leave_deduction'] ?? '-';

        return $deduction !== '-' && $deduction !== '' ? $deduction . ' (late deduction)' : '';
    }

    /** Whether anybody logged a time on this day at all. */
    private function hasAnyPunch(array $record): bool
    {
        foreach (['am_in', 'am_out', 'pm_in', 'pm_out'] as $slot) {
            if ($this->isTime($record[$slot] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /** Overtime as the KPI card counts it: the gap between the two OT punches. */
    private function overtimeMinutes(array $record): int
    {
        if (!$this->isTime($record['ot_in'] ?? null) || !$this->isTime($record['ot_out'] ?? null)) {
            return 0;
        }

        $toMinutes = function (string $time): int {
            [$h, $m] = array_map('intval', explode(':', $time));

            return $h * 60 + $m;
        };

        return max(0, $toMinutes($record['ot_out']) - $toMinutes($record['ot_in']));
    }

    /**
     * A real clock time, as opposed to null or one of the "ON LEAVE" /
     * "ON TRAVEL" sentinels the record carries in place of one.
     */
    private function isTime($value): bool
    {
        return is_string($value) && preg_match('/^\d{1,2}:\d{2}/', $value) === 1;
    }

    /** Trailing zeros dropped: "8" rather than "8.00", "7.5" kept. */
    private function hours($total): string
    {
        return rtrim(rtrim(number_format((float) $total, 2, '.', ''), '0'), '.') ?: '0';
    }

    private function getWorkingDays($startDate, $endDate)
    {
        return CscTimeConversionService::getWorkingDates($startDate, $endDate);
    }

    private function getAttendanceStatus($attendance, $employee)
    {
        if (!$attendance->am_in && !$attendance->pm_in) {
            return 'absent';
        }

        $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');
        $scheduleForDate = $employee->getScheduleForDate($attendanceDate);
        $expectedAmIn = $scheduleForDate ? Carbon::parse($scheduleForDate->am_in) : Carbon::parse('08:00:00');
        $graceThreshold = $expectedAmIn->copy()->addMinutes(5);

        if ($attendance->am_in) {
            $amInTime = Carbon::parse($attendance->am_in);
            if ($amInTime->gt($graceThreshold)) {
                return 'late';
            }
        }

        return 'present';
    }

    private function formatMinutes($minutes)
    {
        return CscTimeConversionService::formatMinutes($minutes);
    }

    private function generateDetailedRecords($startDate, $endDate, $attendances, $employee, $approvedLeaves, $approvedTravelOrders = null)
    {
        $graceMinutes = 5;

        // Build leave dates map
        $leaveDatesMap = [];
        if ($approvedLeaves) {
            foreach ($approvedLeaves as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);
                $current = $leaveStart->copy();
                
                while ($current->lte($leaveEnd)) {
                    $dateKey = $current->format('Y-m-d');
                    $leaveDatesMap[$dateKey] = [
                        'type' => 'leave',
                        'leave_type' => $leave->leaveType->leave_name ?? 'Leave',
                        'leave_code' => $leave->leaveType->leave_code ?? 'N/A',
                        'days' => $leave->number_of_days ?? 1,
                    ];
                    $current->addDay();
                }
            }
        }

        // Build travel order dates map
        $travelOrderDatesMap = [];
        if ($approvedTravelOrders) {
            foreach ($approvedTravelOrders as $travelOrder) {
                $travelStart = Carbon::parse($travelOrder->travel_date);
                $travelEnd = Carbon::parse($travelOrder->return_date);
                $current = $travelStart->copy();
                
                while ($current->lte($travelEnd)) {
                    $dateKey = $current->format('Y-m-d');
                    $travelOrderDatesMap[$dateKey] = [
                        'type' => 'travel_order',
                        'destination' => $travelOrder->destination,
                        'purpose' => $travelOrder->purpose,
                        'order_number' => $travelOrder->order_number,
                        'duration' => $travelOrder->duration,
                    ];
                    $current->addDay();
                }
            }
        }

        $records = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateKey = $current->format('Y-m-d');
            $attendance = $attendances->get($dateKey);
            $isOnLeave = isset($leaveDatesMap[$dateKey]);
            $isOnTravelOrder = isset($travelOrderDatesMap[$dateKey]);
            $leaveInfo = $isOnLeave ? $leaveDatesMap[$dateKey] : null;
            $travelOrderInfo = $isOnTravelOrder ? $travelOrderDatesMap[$dateKey] : null;

            // Get schedule
            $schedule = $employee->getScheduleForDate($dateKey);
            $expectedAmIn = $schedule ? Carbon::parse($schedule->am_in) : Carbon::parse('08:00:00');
            $expectedAmOut = $schedule ? Carbon::parse($schedule->am_out) : Carbon::parse('12:00:00');
            $expectedPmIn = $schedule ? Carbon::parse($schedule->pm_in) : Carbon::parse('13:00:00');
            $expectedPmOut = $schedule ? Carbon::parse($schedule->pm_out) : Carbon::parse('17:00:00');

            // Parse time fields
            $amIn = $attendance && $attendance->am_in ? Carbon::parse($attendance->am_in)->format('H:i') : null;
            $amOut = $attendance && $attendance->am_out ? Carbon::parse($attendance->am_out)->format('H:i') : null;
            $pmIn = $attendance && $attendance->pm_in ? Carbon::parse($attendance->pm_in)->format('H:i') : null;
            $pmOut = $attendance && $attendance->pm_out ? Carbon::parse($attendance->pm_out)->format('H:i') : null;
            $otIn = $attendance && $attendance->ot_in ? Carbon::parse($attendance->ot_in)->format('H:i') : null;
            $otOut = $attendance && $attendance->ot_out ? Carbon::parse($attendance->ot_out)->format('H:i') : null;

            // If on travel order (takes priority over leave)
            if ($isOnTravelOrder && !in_array($current->dayOfWeek, [0, 6])) {
                $records[] = [
                    'date' => $current->format('M d, Y'),
                    'date_key' => $dateKey,
                    'day' => $current->format('l'),
                    'am_in' => 'ON TRAVEL',
                    'am_out' => 'ON TRAVEL',
                    'pm_in' => 'ON TRAVEL',
                    'pm_out' => 'ON TRAVEL',
                    'ot_in' => null,
                    'ot_out' => null,
                    'late_minutes' => 0,
                    'late_display' => '-',
                    'undertime' => 0,
                    'undertime_display' => '-',
                    'total_hours' => '8.0 hrs',
                    'accredited_minutes' => 480,
                    'leave_deduction' => '-',
                    'is_on_leave' => false,
                    'is_on_travel_order' => true,
                    'travel_order_info' => $travelOrderInfo,
                ];
                $current->addDay();
                continue;
            }

            // If on leave
            if ($isOnLeave && !in_array($current->dayOfWeek, [0, 6])) {
                $records[] = [
                    'date' => $current->format('M d, Y'),
                    'date_key' => $dateKey,
                    'day' => $current->format('l'),
                    'am_in' => 'ON LEAVE',
                    'am_out' => 'ON LEAVE',
                    'pm_in' => 'ON LEAVE',
                    'pm_out' => 'ON LEAVE',
                    'ot_in' => null,
                    'ot_out' => null,
                    'late_minutes' => 0,
                    'late_display' => '-',
                    'undertime' => 0,
                    'undertime_display' => '-',
                    'total_hours' => '8.0 hrs',
                    'accredited_minutes' => 480,
                    'leave_deduction' => '-',
                    'is_on_leave' => true,
                    'is_on_travel_order' => false,
                    'leave_info' => $leaveInfo,
                ];
                $current->addDay();
                continue;
            }

            // Get late and undertime from log
            $lateMinutes = 0;
            $undertimeMinutes = 0;
            $accreditedMinutes = 0;
            $leaveDeduction = '-';

            if ($attendance && $attendance->accreditedHoursLogs->isNotEmpty()) {
                $log = $attendance->accreditedHoursLogs->last();
                $lateMinutes = $log->late_minutes;
                $undertimeMinutes = $log->undertime_minutes;
                $accreditedMinutes = $log->total_accredited_minutes;
                
                if ($log->late_deducted_from_leave) {
                    $leaveDeduction = $log->late_deduction_leave_type ?? 'Leave';
                }
            }

            // Calculate total hours from accredited minutes
            $totalHours = $accreditedMinutes > 0 ? number_format($accreditedMinutes / 60, 1) : '0.0';

            $records[] = [
                'date' => $current->format('M d, Y'),
                'date_key' => $dateKey,
                'day' => $current->format('l'),
                'am_in' => $amIn,
                'am_out' => $amOut,
                'pm_in' => $pmIn,
                'pm_out' => $pmOut,
                'ot_in' => $otIn,
                'ot_out' => $otOut,
                'late_minutes' => $lateMinutes,
                'late_display' => $this->formatMinutes($lateMinutes),
                'undertime' => $undertimeMinutes,
                'undertime_display' => $this->formatMinutes($undertimeMinutes),
                'total_hours' => $totalHours . ' hrs',
                'accredited_minutes' => $accreditedMinutes,
                'leave_deduction' => $leaveDeduction,
                'is_on_leave' => false,
                'is_on_travel_order' => false,
            ];

            $current->addDay();
        }

        return $records;
    }
}
