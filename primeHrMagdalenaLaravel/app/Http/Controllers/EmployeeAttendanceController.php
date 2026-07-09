<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Services\CscTimeConversionService;
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
