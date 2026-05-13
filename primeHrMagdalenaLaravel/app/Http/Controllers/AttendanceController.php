<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $department = $request->get('department');
        $status = $request->get('status');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $employees = Employee::with(['employmentDetail.departmentRelation', 'schedule'])
            ->get()
            ->map(function ($employee) use ($startDate, $endDate) {
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                // Get approved leaves for this employee in the date range
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

                $present = 0;
                $absent = 0;
                $late = 0;
                $halfday = 0;
                $overtime = 0;
                $onLeave = 0;

                // Get employee's schedule or use defaults
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
                        // Only count working days (exclude weekends)
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

                        // Get schedule for this specific date
                        $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');
                        $scheduleForDate = $employee->getScheduleForDate($attendanceDate);
                        $expectedAmIn = $scheduleForDate ? Carbon::parse($scheduleForDate->am_in) : Carbon::parse('08:00:00');
                        $graceThreshold = $expectedAmIn->copy()->addMinutes($graceMinutes);

                        // Check if late using employee's schedule
                        if ($attendance->am_in) {
                            $amInTime = Carbon::parse($attendance->am_in);
                            if ($amInTime->gt($graceThreshold)) {
                                $late++;
                            }
                        }

                        // Check half day (only AM or only PM)
                        $hasAM = $attendance->am_in && $attendance->am_out;
                        $hasPM = $attendance->pm_in && $attendance->pm_out;
                        if (($hasAM && !$hasPM) || (!$hasAM && $hasPM)) {
                            $halfday++;
                        }

                        // Calculate overtime using employee's schedule
                        if ($attendance->ot_in && $attendance->ot_out) {
                            $otIn = Carbon::parse($attendance->ot_in);
                            $otOut = Carbon::parse($attendance->ot_out);
                            $expectedPmOut = $scheduleForDate ? Carbon::parse($scheduleForDate->pm_out) : Carbon::parse('17:00:00');
                            
                            if ($otIn->lt($expectedPmOut)) {
                                $otIn = $expectedPmOut;
                            }
                            
                            $overtime += $otIn->diffInHours($otOut, false);
                        }
                    }
                }

                // Calculate absences (working days without attendance and not on leave)
                foreach ($workingDays as $workingDay) {
                    $dayStr = $workingDay->format('Y-m-d');
                    if (!in_array($dayStr, $attendedDates)) {
                        // Check if this day is covered by approved leave
                        if (in_array($dayStr, $leaveDates)) {
                            $onLeave++;
                            $present++; // Count leave as present
                        } else {
                            $absent++;
                        }
                    }
                }

                $totalDays = $present + $absent + $halfday;
                $rate = $totalDays > 0 ? round(($present / $totalDays) * 100) : 0;
                $workingDaysCount = $totalDays;
                $status = ($absent === 0 && $late <= 2 && $workingDaysCount > 0) ? 'Complete' : 'Incomplete';

                $deptName = 'N/A';
                if ($employee->employmentDetail && $employee->employmentDetail->departmentRelation) {
                    $deptName = $employee->employmentDetail->departmentRelation->name;
                }

                return [
                    'id' => $employee->employee_id,
                    'employee_id' => $employee->id,
                    'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                    'position' => $employee->employmentDetail->position ?? 'N/A',
                    'dept' => $deptName,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'halfday' => $halfday,
                    'overtime' => round($overtime, 1),
                    'on_leave' => $onLeave,
                    'rate' => $rate,
                    'status' => $status,
                ];
            });

        // Apply filters
        if ($department && $department !== 'All Departments') {
            $employees = $employees->filter(fn($e) => $e['dept'] === $department);
        }

        if ($status && $status !== 'All Status') {
            $employees = $employees->filter(fn($e) => $e['status'] === $status);
        }

        $attendanceRecords = $employees->values()->all();

        // Calculate totals
        $totalPresent = array_sum(array_column($attendanceRecords, 'present'));
        $totalAbsent = array_sum(array_column($attendanceRecords, 'absent'));
        $totalLate = array_sum(array_column($attendanceRecords, 'late'));
        $totalOT = array_sum(array_column($attendanceRecords, 'overtime'));
        $totalOnLeave = array_sum(array_column($attendanceRecords, 'on_leave'));
        $completeCount = count(array_filter($attendanceRecords, fn($r) => $r['status'] === 'Complete'));
        $incompleteCount = count(array_filter($attendanceRecords, fn($r) => $r['status'] === 'Incomplete'));

        $departments = Employee::with('employmentDetail.departmentRelation')
            ->get()
            ->pluck('employmentDetail.departmentRelation.name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.attendance.adminAttendance', compact(
            'attendanceRecords',
            'totalPresent',
            'totalAbsent',
            'totalLate',
            'totalOT',
            'totalOnLeave',
            'completeCount',
            'incompleteCount',
            'departments'
        ));
    }

    private function getWorkingDays($startDate, $endDate)
    {
        $workingDays = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Exclude weekends (Saturday = 6, Sunday = 0)
            if (!in_array($current->dayOfWeek, [0, 6])) {
                $workingDays[] = $current->copy();
            }
            $current->addDay();
        }

        return $workingDays;
    }

    public function detailedDTR(Request $request, $employeeId)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Validate dates
        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Ensure start date is before end date
        if ($startDate->gt($endDate)) {
            return response()->json(['error' => 'Start date must be before end date'], 400);
        }

        $employee = Employee::with('schedule')->findOrFail($employeeId);

        // Fetch attendance records for the date range
        $attendances = Attendance::with(['accreditedHoursLogs.schedule'])
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function($a) {
                return Carbon::parse($a->date)->format('Y-m-d');
            });

        // Get approved leaves for this employee in the date range
        $approvedLeaves = \App\Models\LeaveApplication::where('employee_id', $employeeId)
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

        $records = $this->generateDetailedRecords($startDate, $endDate, $attendances, $employee, $approvedLeaves);

        return response()->json([
            'records' => $records,
            'employee' => [
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_id' => $employee->employee_id,
            ],
        ]);
    }

    public function exportDetailedDTR(Request $request, $employeeId)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Validate dates
        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $employee = Employee::with(['employmentDetail.departmentRelation', 'schedule'])->findOrFail($employeeId);

        // Fetch attendance records for the date range
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function($a) {
                return Carbon::parse($a->date)->format('Y-m-d');
            });

        $records = $this->generateDetailedRecords($startDate, $endDate, $attendances, $employee);

        $dateRange = $startDate->format('M_d_Y') . '_to_' . $endDate->format('M_d_Y');
        $fileName = "Detailed_DTR_{$employee->employee_id}_{$dateRange}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function() use ($records, $employee, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper Excel encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Add header info
            fputcsv($file, ['DETAILED DAILY TIME RECORD']);
            fputcsv($file, ['Municipal Government of Pagsanjan']);
            fputcsv($file, [$startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Employee:', $employee->first_name . ' ' . $employee->last_name]);
            fputcsv($file, ['Employee ID:', $employee->employee_id]);
            fputcsv($file, ['Position:', $employee->employmentDetail->position ?? 'N/A']);
            fputcsv($file, ['Department:', $employee->employmentDetail->departmentRelation->name ?? 'N/A']);
            fputcsv($file, []);

            // Add column headers
            fputcsv($file, ['Date', 'Day', 'AM In', 'AM Out', 'PM In', 'PM Out', 'OT In', 'OT Out', 'Undertime (min)', 'Late (min)', 'Total Hours']);

            // Add data rows
            foreach ($records as $record) {
                fputcsv($file, [
                    $record['date'],
                    $record['day'],
                    $record['am_in'] ?? 'Log Missing',
                    $record['am_out'] ?? 'Log Missing',
                    $record['pm_in'] ?? 'Log Missing',
                    $record['pm_out'] ?? 'Log Missing',
                    $record['ot_in'] ?? '-',
                    $record['ot_out'] ?? '-',
                    $record['undertime'] > 0 ? $record['undertime'] : '-',
                    $record['late_minutes'] > 0 ? $record['late_minutes'] : '-',
                    $record['total_hours'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function formatMinutes($minutes)
    {
        if ($minutes <= 0) {
            return '0 min';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0 && $mins > 0) {
            return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ' . round($mins) . ' min';
        } elseif ($hours > 0) {
            return $hours . ' hr' . ($hours > 1 ? 's' : '');
        } else {
            return round($mins) . ' min';
        }
    }

    private function generateDetailedRecords($startDate, $endDate, $attendances, $employee = null, $approvedLeaves = null)
    {
        $graceMinutes = 5;

        // Build leave dates map with leave details
        $leaveDatesMap = [];
        if ($approvedLeaves) {
            foreach ($approvedLeaves as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);
                $current = $leaveStart->copy();
                
                while ($current->lte($leaveEnd)) {
                    $dateKey = $current->format('Y-m-d');
                    $leaveDatesMap[$dateKey] = [
                        'leave_type' => $leave->leaveType->leave_name ?? 'Leave',
                        'leave_code' => $leave->leaveType->leave_code ?? 'N/A',
                        'application_number' => $leave->application_number,
                        'days' => $leave->number_of_days,
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
            $leaveInfo = $isOnLeave ? $leaveDatesMap[$dateKey] : null;

            // Get schedule for this specific date
            $schedule = $employee ? $employee->getScheduleForDate($dateKey) : null;
            $expectedAmIn = $schedule ? Carbon::parse($schedule->am_in) : Carbon::parse('08:00:00');
            $expectedAmOut = $schedule ? Carbon::parse($schedule->am_out) : Carbon::parse('12:00:00');
            $expectedPmIn = $schedule ? Carbon::parse($schedule->pm_in) : Carbon::parse('13:00:00');
            $expectedPmOut = $schedule ? Carbon::parse($schedule->pm_out) : Carbon::parse('17:00:00');
            
            $graceThresholdAm = $expectedAmIn->copy()->addMinutes($graceMinutes);
            $graceThresholdPm = $expectedPmIn->copy()->addMinutes($graceMinutes);

            // Parse time fields safely
            $amIn = null;
            $amOut = null;
            $pmIn = null;
            $pmOut = null;
            $otIn = null;
            $otOut = null;

            if ($attendance) {
                // Handle time fields - stored as TIME (HH:MM:SS) or DATETIME
                if ($attendance->am_in) {
                    try {
                        $amIn = Carbon::parse($attendance->am_in)->format('H:i');
                    } catch (\Exception $e) {
                        $amIn = null;
                    }
                }
                if ($attendance->am_out) {
                    try {
                        $amOut = Carbon::parse($attendance->am_out)->format('H:i');
                    } catch (\Exception $e) {
                        $amOut = null;
                    }
                }
                if ($attendance->pm_in) {
                    try {
                        $pmIn = Carbon::parse($attendance->pm_in)->format('H:i');
                    } catch (\Exception $e) {
                        $pmIn = null;
                    }
                }
                if ($attendance->pm_out) {
                    try {
                        $pmOut = Carbon::parse($attendance->pm_out)->format('H:i');
                    } catch (\Exception $e) {
                        $pmOut = null;
                    }
                }
                if ($attendance->ot_in) {
                    try {
                        $otIn = Carbon::parse($attendance->ot_in)->format('H:i');
                    } catch (\Exception $e) {
                        $otIn = null;
                    }
                }
                if ($attendance->ot_out) {
                    try {
                        $otOut = Carbon::parse($attendance->ot_out)->format('H:i');
                    } catch (\Exception $e) {
                        $otOut = null;
                    }
                }
            }

            // If on approved leave, mark as present with leave indicator
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
                    'accredited_minutes' => 480, // 8 hours
                    'am_accredited_minutes' => 240,
                    'pm_accredited_minutes' => 240,
                    'am_grace_applied' => false,
                    'pm_grace_applied' => false,
                    'schedule' => [
                        'am_in' => $expectedAmIn->format('H:i'),
                        'am_out' => $expectedAmOut->format('H:i'),
                        'pm_in' => $expectedPmIn->format('H:i'),
                        'pm_out' => $expectedPmOut->format('H:i'),
                    ],
                    'has_log' => false,
                    'needs_review' => false,
                    'is_incomplete' => false,
                    'attendance_id' => null,
                    'date_key' => $current->format('Y-m-d'),
                    'is_on_leave' => true,
                    'leave_info' => $leaveInfo,
                ];
                $current->addDay();
                continue;
            }

            // Calculate late minutes with grace period
            $lateMinutes = 0;
            if ($attendance && $attendance->am_in) {
                try {
                    $amInTime = Carbon::parse($attendance->am_in);
                    if ($amInTime->gt($graceThresholdAm)) {
                        $lateMinutes = $expectedAmIn->diffInMinutes($amInTime);
                    }
                } catch (\Exception $e) {
                    $lateMinutes = 0;
                }
            }

            // Check if employee only timed in AM without returning (no AM out and no PM in)
            // This means they left and never came back - mark as ABSENT
            $isAbandoned = false;
            if ($attendance && $attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !in_array($current->dayOfWeek, [0, 6])) {
                $isAbandoned = true;
            }

            // Check if truly absent (no time records at all)
            $isTrulyAbsent = !$attendance || (!$attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !$attendance->pm_out);

            // Determine if incomplete vs absent
            // INCOMPLETE: Has substantial attendance but missing some entries
            // ABSENT: No attendance, abandoned, or only single time-in without pair
            $isIncomplete = false;
            $isAbsent = false;
            
            if ($attendance && !in_array($current->dayOfWeek, [0, 6])) {
                $hasAmPair = $attendance->am_in && $attendance->am_out;
                $hasPmPair = $attendance->pm_in && $attendance->pm_out;
                $hasOnlyAmIn = $attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !$attendance->pm_out;
                $hasOnlyPmIn = !$attendance->am_in && !$attendance->am_out && $attendance->pm_in && !$attendance->pm_out;
                
                // ABSENT cases:
                // 1. Abandoned (AM in only, no AM out, no PM in)
                // 2. Only single time-in without any out (suspicious)
                if ($isAbandoned || $hasOnlyAmIn || $hasOnlyPmIn) {
                    $isAbsent = true;
                }
                // INCOMPLETE cases:
                // 1. Has AM pair but incomplete PM
                // 2. Has PM pair but incomplete AM  
                // 3. Has AM in, AM out, PM in but no PM out (worked but forgot to clock out)
                else if (($hasAmPair && !$hasPmPair) || (!$hasAmPair && $hasPmPair) || 
                         ($attendance->am_in && $attendance->am_out && $attendance->pm_in && !$attendance->pm_out)) {
                    $isIncomplete = true;
                }
            }

            // Calculate undertime (in minutes)
            $undertime = 0;
            if ($attendance && $attendance->pm_out && !in_array($current->dayOfWeek, [0, 6])) {
                try {
                    $pmOutTime = Carbon::parse($attendance->pm_out);
                    if ($pmOutTime->lt($expectedPmOut)) {
                        $undertime = $pmOutTime->diffInMinutes($expectedPmOut);
                    }
                } catch (\Exception $e) {
                    $undertime = 0;
                }
            }

            // If abandoned or only single time-in, treat as ABSENT
            if ($isAbandoned || $isAbsent) {
                $statusLabel = $isAbandoned ? 'ABANDONED' : 'ABSENT';
                $records[] = [
                    'date' => $current->format('M d, Y'),
                    'day' => $current->format('l'),
                    'am_in' => $amIn,
                    'am_out' => $statusLabel,
                    'pm_in' => $statusLabel,
                    'pm_out' => $statusLabel,
                    'ot_in' => null,
                    'ot_out' => null,
                    'late_minutes' => 0,
                    'late_display' => '-',
                    'undertime' => 480, // 8 hours undertime
                    'undertime_display' => '8 hrs',
                    'total_hours' => '0 hrs',
                    'accredited_minutes' => 0,
                    'am_accredited_minutes' => 0,
                    'pm_accredited_minutes' => 0,
                    'am_grace_applied' => false,
                    'pm_grace_applied' => false,
                    'schedule' => [
                        'am_in' => $expectedAmIn->format('H:i'),
                        'am_out' => $expectedAmOut->format('H:i'),
                        'pm_in' => $expectedPmIn->format('H:i'),
                        'pm_out' => $expectedPmOut->format('H:i'),
                    ],
                    'has_log' => false,
                    'needs_review' => true,
                    'is_incomplete' => false,
                    'is_absent' => true,
                    'is_abandoned' => $isAbandoned,
                    'attendance_id' => $attendance ? $attendance->id : null,
                    'date_key' => $current->format('Y-m-d'),
                    'is_on_leave' => false,
                    'leave_info' => null,
                ];
                $current->addDay();
                continue;
            }

            // Use stored total_hours from database (actual time worked in minutes)
            $totalHoursMinutes = $attendance ? $attendance->total_hours : 0;
            $totalHours = $totalHoursMinutes ? round($totalHoursMinutes / 60, 1) : 0;
            $needsReview = ($lateMinutes > 0 && $undertime > 0);

            // Get accredited hours from log if exists, otherwise calculate
            $accreditedMinutes = 0;
            $amAccreditedMins = 0;
            $pmAccreditedMins = 0;
            $amGraceApplied = false;
            $pmGraceApplied = false;
            $scheduleUsed = null;
            $hasLog = false;
            
            if ($attendance && $attendance->accreditedHoursLogs->isNotEmpty()) {
                // Use the latest log entry for this attendance
                $log = $attendance->accreditedHoursLogs->last();
                $accreditedMinutes = $log->total_accredited_minutes;
                $amAccreditedMins = $log->am_accredited_minutes;
                $pmAccreditedMins = $log->pm_accredited_minutes;
                $amGraceApplied = $log->am_grace_applied;
                $pmGraceApplied = $log->pm_grace_applied;
                
                // Get schedule from relationship
                if ($log->schedule) {
                    $scheduleUsed = [
                        'am_in' => substr($log->schedule->am_in, 0, 5),
                        'am_out' => substr($log->schedule->am_out, 0, 5),
                        'pm_in' => substr($log->schedule->pm_in, 0, 5),
                        'pm_out' => substr($log->schedule->pm_out, 0, 5),
                    ];
                }
                $hasLog = true;
            } elseif ($attendance && ($amIn && $amOut && $pmIn && $pmOut)) {
                // Fallback: Calculate if no log exists
                $toMin = fn($t) => $t ? (int)(explode(':', $t)[0]) * 60 + (int)(explode(':', $t)[1]) : null;
                
                $AM_START = $toMin($expectedAmIn->format('H:i'));
                $AM_END = $toMin($expectedAmOut->format('H:i'));
                $AM_GRACE = $AM_START + 5;
                $PM_START = $toMin($expectedPmIn->format('H:i'));
                $PM_END = $toMin($expectedPmOut->format('H:i'));
                $PM_GRACE = $PM_START + 5;
                
                // Calculate AM accredited
                $amInMin = $toMin($amIn);
                if ($amInMin <= $AM_GRACE) {
                    $amFrom = $AM_START;
                    $amGraceApplied = true;
                } else {
                    $amFrom = $amInMin;
                }
                $amTo = min($toMin($amOut), $AM_END);
                $amAccreditedMins = max(0, $amTo - $amFrom);
                
                // Calculate PM accredited
                $pmInMin = $toMin($pmIn);
                if ($pmInMin <= $PM_GRACE) {
                    $pmFrom = $PM_START;
                    $pmGraceApplied = true;
                } else {
                    $pmFrom = $pmInMin;
                }
                $pmTo = min($toMin($pmOut), $PM_END);
                $pmAccreditedMins = max(0, $pmTo - $pmFrom);
                
                $accreditedMinutes = $amAccreditedMins + $pmAccreditedMins;
                $scheduleUsed = [
                    'am_in' => $expectedAmIn->format('H:i'),
                    'am_out' => $expectedAmOut->format('H:i'),
                    'pm_in' => $expectedPmIn->format('H:i'),
                    'pm_out' => $expectedPmOut->format('H:i'),
                ];
            }

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
                'undertime' => $undertime,
                'undertime_display' => $this->formatMinutes($undertime),
                'total_hours' => $totalHours . ' hrs',
                'accredited_minutes' => $accreditedMinutes,
                'am_accredited_minutes' => $amAccreditedMins,
                'pm_accredited_minutes' => $pmAccreditedMins,
                'am_grace_applied' => $amGraceApplied,
                'pm_grace_applied' => $pmGraceApplied,
                'schedule' => $scheduleUsed ?: [
                    'am_in' => $expectedAmIn->format('H:i'),
                    'am_out' => $expectedAmOut->format('H:i'),
                    'pm_in' => $expectedPmIn->format('H:i'),
                    'pm_out' => $expectedPmOut->format('H:i'),
                ],
                'has_log' => $hasLog,
                'needs_review' => $needsReview,
                'is_incomplete' => $isIncomplete,
                'is_absent' => false,
                'is_abandoned' => false,
                'attendance_id' => $attendance ? $attendance->id : null,
                'date_key' => $current->format('Y-m-d'),
                'is_on_leave' => false,
                'leave_info' => null,
            ];

            $current->addDay();
        }

        return $records;
    }

    public function getAttendanceRecord($attendanceId)
    {
        // Handle both existing attendance ID and date-based lookup
        if (strpos($attendanceId, 'new_') === 0) {
            // New record format: new_employeeId_date
            $parts = explode('_', $attendanceId);
            $employeeId = $parts[1];
            $date = $parts[2];

            $employee = Employee::findOrFail($employeeId);

            return response()->json([
                'id' => null,
                'employee_id' => $employeeId,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'date' => $date,
                'am_in' => null,
                'am_out' => null,
                'pm_in' => null,
                'pm_out' => null,
                'ot_in' => null,
                'ot_out' => null,
                'is_new' => true,
            ]);
        }

        $attendance = Attendance::with('employee')->findOrFail($attendanceId);

        // Helper to format time to HH:MM
        $formatTime = function($time) {
            if (!$time) return null;
            try {
                return Carbon::parse($time)->format('H:i');
            } catch (\Exception $e) {
                return null;
            }
        };

        return response()->json([
            'id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'employee_name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
            'date' => Carbon::parse($attendance->date)->format('Y-m-d'),
            'am_in' => $formatTime($attendance->am_in),
            'am_out' => $formatTime($attendance->am_out),
            'pm_in' => $formatTime($attendance->pm_in),
            'pm_out' => $formatTime($attendance->pm_out),
            'ot_in' => $formatTime($attendance->ot_in),
            'ot_out' => $formatTime($attendance->ot_out),
            'is_new' => false,
        ]);
    }

    public function getAccreditedHoursLog($attendanceId)
    {
        $attendance = Attendance::with(['employee', 'accreditedHoursLogs.schedule'])->findOrFail($attendanceId);
        
        $logs = $attendance->accreditedHoursLogs->map(function($log) {
            return [
                'id' => $log->id,
                'date' => $log->attendance_date->format('M d, Y'),
                'schedule' => [
                    'am_in' => $log->scheduled_am_in,
                    'am_out' => $log->scheduled_am_out,
                    'pm_in' => $log->scheduled_pm_in,
                    'pm_out' => $log->scheduled_pm_out,
                ],
                'actual' => [
                    'am_in' => $log->actual_am_in,
                    'am_out' => $log->actual_am_out,
                    'pm_in' => $log->actual_pm_in,
                    'pm_out' => $log->actual_pm_out,
                    'ot_in' => $log->actual_ot_in,
                    'ot_out' => $log->actual_ot_out,
                ],
                'computation' => [
                    'am_minutes' => $log->am_accredited_minutes,
                    'pm_minutes' => $log->pm_accredited_minutes,
                    'ot_minutes' => $log->ot_minutes,
                    'late_minutes' => $log->late_minutes,
                    'undertime_minutes' => $log->undertime_minutes,
                    'total_accredited' => $log->total_accredited_minutes,
                    'total_actual' => $log->total_actual_minutes,
                ],
                'grace' => [
                    'am_applied' => $log->am_grace_applied,
                    'pm_applied' => $log->pm_grace_applied,
                ],
                'notes' => $log->computation_notes,
                'created_at' => $log->created_at->format('M d, Y h:i A'),
            ];
        });

        return response()->json([
            'employee' => [
                'name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
                'employee_id' => $attendance->employee->employee_id,
            ],
            'attendance_date' => Carbon::parse($attendance->date)->format('M d, Y'),
            'logs' => $logs,
        ]);
    }

    /**
     * Compute accredited hours and create detailed log.
     * Returns array with accredited minutes and log data.
     */
    private function computeAccreditedHours($employeeId, $date, ?string $amIn, ?string $amOut, ?string $pmIn, ?string $pmOut, ?string $otIn = null, ?string $otOut = null): array
    {
        if (!$amIn && !$amOut && !$pmIn && !$pmOut) {
            return ['accredited_minutes' => null, 'log_data' => null];
        }

        $employee = Employee::find($employeeId);
        $schedule = $employee ? $employee->getScheduleForDate($date) : null;

        $toMin = fn($t) => $t ? (int)(explode(':', $t)[0]) * 60 + (int)(explode(':', $t)[1]) : null;

        // Use employee's schedule or defaults
        $AM_START   = $schedule ? $toMin($schedule->am_in) : 480;  // Default 08:00
        $AM_END     = $schedule ? $toMin($schedule->am_out) : 720;  // Default 12:00
        $AM_GRACE   = $AM_START + 5;  // 5 minutes grace
        $PM_START   = $schedule ? $toMin($schedule->pm_in) : 780;  // Default 13:00
        $PM_END     = $schedule ? $toMin($schedule->pm_out) : 1020; // Default 17:00
        $PM_GRACE   = $PM_START + 5;  // 5 minutes grace

        // Check if employee abandoned (only AM in, no AM out, no PM in)
        // This means they left and never came back - treat as absent (0 accredited hours)
        if ($amIn && !$amOut && !$pmIn) {
            return [
                'accredited_minutes' => 0,
                'log_data' => [
                    'schedule_id' => $schedule ? $schedule->id : null,
                    'am_accredited_minutes' => 0,
                    'pm_accredited_minutes' => 0,
                    'ot_minutes' => 0,
                    'late_minutes' => 0,
                    'undertime_minutes' => 480, // 8 hours absent
                    'total_accredited_minutes' => 0,
                    'total_actual_minutes' => 0,
                    'am_grace_applied' => false,
                    'pm_grace_applied' => false,
                ]
            ];
        }

        $amMins = 0;
        $amGraceApplied = false;
        if ($amIn && $amOut) {
            $amInMin = $toMin($amIn);
            if ($amInMin <= $AM_GRACE) {
                $amFrom = $AM_START;
                $amGraceApplied = true;
            } else {
                $amFrom = $amInMin;
            }
            $amTo = min($toMin($amOut), $AM_END);
            $amMins = max(0, $amTo - $amFrom);
        }

        $pmMins = 0;
        $pmGraceApplied = false;
        if ($pmIn && $pmOut) {
            $pmInMin = $toMin($pmIn);
            if ($pmInMin <= $PM_GRACE) {
                $pmFrom = $PM_START;
                $pmGraceApplied = true;
            } else {
                $pmFrom = $pmInMin;
            }
            $pmTo = min($toMin($pmOut), $PM_END);
            $pmMins = max(0, $pmTo - $pmFrom);
        }

        // Calculate OT
        $otMins = 0;
        if ($otIn && $otOut) {
            $otMins = max(0, $toMin($otOut) - $toMin($otIn));
        }

        // Calculate late and undertime
        $lateMins = 0;
        if ($amIn) {
            $amInMin = $toMin($amIn);
            if ($amInMin > $AM_GRACE) {
                $lateMins = $amInMin - $AM_START;
            }
        }

        $undertimeMins = 0;
        if ($pmOut) {
            $pmOutMin = $toMin($pmOut);
            if ($pmOutMin < $PM_END) {
                $undertimeMins = $PM_END - $pmOutMin;
            }
        }

        $totalAccredited = $amMins + $pmMins;
        $totalActual = 0;
        if ($amIn && $amOut) $totalActual += $toMin($amOut) - $toMin($amIn);
        if ($pmIn && $pmOut) $totalActual += $toMin($pmOut) - $toMin($pmIn);
        if ($otIn && $otOut) $totalActual += $otMins;

        return [
            'accredited_minutes' => $totalAccredited,
            'log_data' => [
                'schedule_id' => $schedule ? $schedule->id : null,
                'am_accredited_minutes' => $amMins,
                'pm_accredited_minutes' => $pmMins,
                'ot_minutes' => $otMins,
                'late_minutes' => $lateMins,
                'undertime_minutes' => $undertimeMins,
                'total_accredited_minutes' => $totalAccredited,
                'total_actual_minutes' => $totalActual,
                'am_grace_applied' => $amGraceApplied,
                'pm_grace_applied' => $pmGraceApplied,
            ]
        ];
    }

    /**
     * Compute total hours worked in minutes (actual time logged).
     */
    private function computeTotalHours(?string $amIn, ?string $amOut, ?string $pmIn, ?string $pmOut, ?string $otIn, ?string $otOut): ?int
    {
        if (!$amIn && !$amOut && !$pmIn && !$pmOut && !$otIn && !$otOut) {
            return null;
        }

        $toMin = fn($t) => $t ? (int)(explode(':', $t)[0]) * 60 + (int)(explode(':', $t)[1]) : null;

        $totalMins = 0;

        // Calculate AM hours
        if ($amIn && $amOut) {
            $totalMins += max(0, $toMin($amOut) - $toMin($amIn));
        }

        // Calculate PM hours
        if ($pmIn && $pmOut) {
            $totalMins += max(0, $toMin($pmOut) - $toMin($pmIn));
        }

        // Calculate OT hours
        if ($otIn && $otOut) {
            $totalMins += max(0, $toMin($otOut) - $toMin($otIn));
        }

        return $totalMins;
    }

    /**
     * Recalculate attendance records for an employee within a date range.
     * Used when schedules are updated to ensure accredited hours reflect new schedule.
     */
    public function recalculateAttendanceForSchedule($employeeId, $startDate, $endDate)
    {
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $recalculatedCount = 0;

        foreach ($attendances as $attendance) {
            // Skip if no time records
            if (!$attendance->am_in && !$attendance->pm_in) {
                continue;
            }

            $computationResult = $this->computeAccreditedHours(
                $employeeId,
                Carbon::parse($attendance->date)->format('Y-m-d'),
                $attendance->am_in ? Carbon::parse($attendance->am_in)->format('H:i') : null,
                $attendance->am_out ? Carbon::parse($attendance->am_out)->format('H:i') : null,
                $attendance->pm_in ? Carbon::parse($attendance->pm_in)->format('H:i') : null,
                $attendance->pm_out ? Carbon::parse($attendance->pm_out)->format('H:i') : null,
                $attendance->ot_in ? Carbon::parse($attendance->ot_in)->format('H:i') : null,
                $attendance->ot_out ? Carbon::parse($attendance->ot_out)->format('H:i') : null
            );

            // Update attendance accredited hours
            $attendance->update([
                'accredited_hours' => $computationResult['accredited_minutes'],
            ]);

            // Update or create log
            if ($computationResult['log_data']) {
                $accreditedLog = AccreditedHoursLog::updateOrCreate(
                    ['attendance_id' => $attendance->id],
                    [
                        'employee_id' => $employeeId,
                        'schedule_id' => $computationResult['log_data']['schedule_id'],
                        'am_accredited_minutes' => $computationResult['log_data']['am_accredited_minutes'],
                        'pm_accredited_minutes' => $computationResult['log_data']['pm_accredited_minutes'],
                        'ot_minutes' => $computationResult['log_data']['ot_minutes'],
                        'late_minutes' => $computationResult['log_data']['late_minutes'],
                        'undertime_minutes' => $computationResult['log_data']['undertime_minutes'],
                        'total_accredited_minutes' => $computationResult['log_data']['total_accredited_minutes'],
                        'total_actual_minutes' => $computationResult['log_data']['total_actual_minutes'],
                        'am_grace_applied' => $computationResult['log_data']['am_grace_applied'],
                        'pm_grace_applied' => $computationResult['log_data']['pm_grace_applied'],
                        'computation_notes' => 'Recalculated due to schedule update at ' . now()->format('Y-m-d H:i:s'),
                    ]
                );
                
                // Trigger daily salary computation
                DailySalaryComputation::computeFromAccreditedLog($accreditedLog);
            }

            $recalculatedCount++;
        }

        return $recalculatedCount;
    }

    public function correctAttendance(Request $request)
    {
        $validated = $request->validate([
            'attendance_id' => 'nullable',
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'am_in' => 'nullable|date_format:H:i',
            'am_out' => 'nullable|date_format:H:i',
            'pm_in' => 'nullable|date_format:H:i',
            'pm_out' => 'nullable|date_format:H:i',
            'ot_in' => 'nullable|date_format:H:i',
            'ot_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
            'attachments.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Check if this is a new record or updating existing
        if ($validated['attendance_id']) {
            $attendance = Attendance::findOrFail($validated['attendance_id']);
        } else {
            // Create new attendance record
            $attendance = Attendance::firstOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'date' => $validated['date'],
                ],
                [
                    'am_in' => null,
                    'am_out' => null,
                    'pm_in' => null,
                    'pm_out' => null,
                    'ot_in' => null,
                    'ot_out' => null,
                ]
            );
        }

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attendance_corrections', 'public');
                $attachmentPaths[] = $path;
            }
        }

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'date' => $validated['date'],
            'old_am_in' => $attendance->am_in,
            'old_am_out' => $attendance->am_out,
            'old_pm_in' => $attendance->pm_in,
            'old_pm_out' => $attendance->pm_out,
            'old_ot_in' => $attendance->ot_in,
            'old_ot_out' => $attendance->ot_out,
            'new_am_in' => $validated['am_in'],
            'new_am_out' => $validated['am_out'],
            'new_pm_in' => $validated['pm_in'],
            'new_pm_out' => $validated['pm_out'],
            'new_ot_in' => $validated['ot_in'],
            'new_ot_out' => $validated['ot_out'],
            'reason' => $validated['reason'],
            'attachments' => $attachmentPaths,
            'corrected_by' => Auth::id(),
        ]);

        $computationResult = $this->computeAccreditedHours(
            $validated['employee_id'],
            $validated['date'],
            $validated['am_in'],
            $validated['am_out'],
            $validated['pm_in'],
            $validated['pm_out'],
            $validated['ot_in'],
            $validated['ot_out']
        );

        $attendance->update([
            'am_in'  => $validated['am_in'],
            'am_out' => $validated['am_out'],
            'pm_in'  => $validated['pm_in'],
            'pm_out' => $validated['pm_out'],
            'ot_in'  => $validated['ot_in'],
            'ot_out' => $validated['ot_out'],
            'accredited_hours' => $computationResult['accredited_minutes'],
            'total_hours' => $this->computeTotalHours(
                $validated['am_in'],
                $validated['am_out'],
                $validated['pm_in'],
                $validated['pm_out'],
                $validated['ot_in'],
                $validated['ot_out']
            ),
        ]);

        // Update or create accredited hours log (one log per attendance)
        if ($computationResult['log_data']) {
            $accreditedLog = AccreditedHoursLog::updateOrCreate(
                [
                    'attendance_id' => $attendance->id,
                ],
                [
                    'employee_id' => $validated['employee_id'],
                    'schedule_id' => $computationResult['log_data']['schedule_id'],
                    'am_accredited_minutes' => $computationResult['log_data']['am_accredited_minutes'],
                    'pm_accredited_minutes' => $computationResult['log_data']['pm_accredited_minutes'],
                    'ot_minutes' => $computationResult['log_data']['ot_minutes'],
                    'late_minutes' => $computationResult['log_data']['late_minutes'],
                    'undertime_minutes' => $computationResult['log_data']['undertime_minutes'],
                    'total_accredited_minutes' => $computationResult['log_data']['total_accredited_minutes'],
                    'total_actual_minutes' => $computationResult['log_data']['total_actual_minutes'],
                    'am_grace_applied' => $computationResult['log_data']['am_grace_applied'],
                    'pm_grace_applied' => $computationResult['log_data']['pm_grace_applied'],
                    'computation_notes' => 'Attendance correction by ' . Auth::user()->name . ' at ' . now()->format('Y-m-d H:i:s'),
                ]
            );
            
            // Trigger daily salary computation
            \App\Models\DailySalaryComputation::computeFromAccreditedLog($accreditedLog);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance record corrected successfully',
        ]);
    }
}
