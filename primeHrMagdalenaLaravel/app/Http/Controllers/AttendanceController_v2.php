<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;
use App\Services\LateDeductionService;
use App\Services\UndertimeDeductionService;
use App\Services\CscTimeConversionService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $department = $request->get('department');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $tab = $request->get('tab', 'summary');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Get all employees
        $allEmployees = Employee::with(['employmentDetail.departmentRelation', 'schedule'])
            ->orderBy('first_name')
            ->get();

        // Filter by department if needed
        if ($department && $department !== 'All Departments') {
            $allEmployees = $allEmployees->filter(function($emp) use ($department) {
                return $emp->employmentDetail?->departmentRelation?->name === $department;
            });
        }

        // For summary tab, use pagination
        if ($tab === 'summary') {
            $employees = $allEmployees->slice(($page - 1) * $perPage, $perPage);
        } else {
            $employees = $allEmployees;
        }
        
        $attendanceRecords = $employees->map(function ($employee) use ($startDate, $endDate) {
            return $this->calculateEmployeeAttendance($employee, $startDate, $endDate);
        })->toArray();

        // Apply filters
        if ($status && $status !== 'All Status') {
            $attendanceRecords = array_filter($attendanceRecords, fn($e) => $e['status'] === $status);
            $attendanceRecords = array_values($attendanceRecords);
        }

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

        // Generate detailed records only when detailed tab is active
        $detailedRecords = [];
        $detailedPagination = [
            'current_page' => 1,
            'per_page' => 10,
            'total' => 0,
            'last_page' => 0,
            'from' => 0,
            'to' => 0,
        ];

        if ($tab === 'detailed') {
            $detailedPerPage = (int)$perPage;
            $detailedPage = (int)$page;
            $employeeName = $request->get('employee_name');
            
            $detailedRecordsData = [];
            foreach ($allEmployees as $employee) {
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc')
                    ->get()
                    ->keyBy(function($a) {
                        return Carbon::parse($a->date)->format('Y-m-d');
                    });
                
                $leaves = \App\Models\LeaveApplication::where('employee_id', $employee->id)
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
                
                $records = $this->generateDetailedRecords($startDate, $endDate, $attendances, $employee, $leaves);
                $detailedRecordsData = array_merge($detailedRecordsData, $records);
            }
            
            // Apply employee name filter if provided
            if ($employeeName) {
                $detailedRecordsData = array_filter($detailedRecordsData, fn($r) => $r['employee_name'] === $employeeName);
                $detailedRecordsData = array_values($detailedRecordsData);
            }
            
            // Paginate detailed records
            $totalRecords = count($detailedRecordsData);
            $lastPage = max(1, ceil($totalRecords / $detailedPerPage));
            $from = (($detailedPage - 1) * $detailedPerPage) + 1;
            $to = min($detailedPage * $detailedPerPage, $totalRecords);
            
            $detailedRecords = array_slice($detailedRecordsData, ($detailedPage - 1) * $detailedPerPage, $detailedPerPage);
            
            $detailedPagination = [
                'current_page' => $detailedPage,
                'per_page' => $detailedPerPage,
                'total' => $totalRecords,
                'last_page' => $lastPage,
                'from' => $totalRecords > 0 ? $from : 0,
                'to' => $totalRecords > 0 ? $to : 0,
            ];
        }

        $exemptions = \App\Models\AttendanceExemption::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.attendance.adminAttendance', compact(
            'attendanceRecords',
            'employees',
            'totalPresent',
            'totalAbsent',
            'totalLate',
            'totalOT',
            'totalOnLeave',
            'completeCount',
            'incompleteCount',
            'departments',
            'detailedRecords',
            'detailedPagination',
            'exemptions'
        ));
    }

    private function calculateEmployeeAttendance($employee, $startDate, $endDate)
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'date', 'am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out')
            ->get();

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

        $workingDays = $this->getWorkingDays($startDate, $endDate);
        $attendedDates = $attendances->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->unique()->toArray();
        
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

                $attendanceDate = $attendance->date->format('Y-m-d');
                $scheduleForDate = $employee->getScheduleForDate($attendanceDate);
                $expectedAmIn = $scheduleForDate ? Carbon::parse($scheduleForDate->am_in) : Carbon::parse('08:00:00');
                $graceThreshold = $expectedAmIn->copy()->addMinutes(5);

                if ($attendance->am_in) {
                    $amInTime = Carbon::parse($attendance->am_in);
                    if ($amInTime->gt($graceThreshold)) {
                        $late++;
                    }
                }

                $hasAM = $attendance->am_in && $attendance->am_out;
                $hasPM = $attendance->pm_in && $attendance->pm_out;
                if (($hasAM && !$hasPM) || (!$hasAM && $hasPM)) {
                    $halfday++;
                }

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

        $totalDays = $present + $absent + $halfday;
        $rate = $totalDays > 0 ? number_format(($present / $totalDays) * 100, 2, '.', '') : 0;
        $status = ($absent === 0 && $late <= 2 && $totalDays > 0) ? 'Complete' : 'Incomplete';

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
            'overtime' => $overtime,
            'on_leave' => $onLeave,
            'rate' => $rate,
            'status' => $status,
        ];
    }

    private function getWorkingDays($startDate, $endDate)
    {
        return CscTimeConversionService::getWorkingDates($startDate, $endDate);
    }

    private function formatMinutes($minutes)
    {
        return CscTimeConversionService::formatMinutes($minutes);
    }

    private function generateDetailedRecords($startDate, $endDate, $attendances, $employee = null, $approvedLeaves = null, $approvedTravelOrders = null)
    {
        $graceMinutes = 5;
        $today = Carbon::now()->startOfDay();
        
        if ($employee && $employee->employmentDetail && $employee->employmentDetail->appointment_date) {
            $appointmentDate = Carbon::parse($employee->employmentDetail->appointment_date)->startOfDay();
            if ($appointmentDate->gt($startDate)) {
                $startDate = $appointmentDate;
            }
        }
        
        if ($startDate->gt($today)) {
            if ((!$approvedLeaves || $approvedLeaves->isEmpty()) && (!$approvedTravelOrders || $approvedTravelOrders->isEmpty())) {
                return [];
            }
        }

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
                        'application_number' => $leave->application_number,
                        'days' => $leave->number_of_days,
                    ];
                    $current->addDay();
                }
            }
        }

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
        $employeeName = $employee ? trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name) : 'Unknown';
        $employeeId = $employee ? $employee->employee_id : 'N/A';

        while ($current->lte($endDate)) {
            if ($employee && $employee->employmentDetail && $employee->employmentDetail->appointment_date) {
                $appointmentDate = Carbon::parse($employee->employmentDetail->appointment_date)->startOfDay();
                if ($current->lt($appointmentDate)) {
                    $current->addDay();
                    continue;
                }
            }
            
            $dateKey = $current->format('Y-m-d');
            $attendance = $attendances->get($dateKey);
            $isOnLeave = isset($leaveDatesMap[$dateKey]);
            $isOnTravelOrder = isset($travelOrderDatesMap[$dateKey]);
            $leaveInfo = $isOnLeave ? $leaveDatesMap[$dateKey] : null;
            $travelOrderInfo = $isOnTravelOrder ? $travelOrderDatesMap[$dateKey] : null;

            $schedule = $employee ? $employee->getScheduleForDate($dateKey) : null;
            $expectedAmIn = $schedule ? Carbon::parse($schedule->am_in) : Carbon::parse('08:00:00');
            $expectedAmOut = $schedule ? Carbon::parse($schedule->am_out) : Carbon::parse('12:00:00');
            $expectedPmIn = $schedule ? Carbon::parse($schedule->pm_in) : Carbon::parse('13:00:00');
            $expectedPmOut = $schedule ? Carbon::parse($schedule->pm_out) : Carbon::parse('17:00:00');
            
            $graceThresholdAm = $expectedAmIn->copy()->addMinutes($graceMinutes);
            $graceThresholdPm = $expectedPmIn->copy()->addMinutes($graceMinutes);

            $amIn = null;
            $amOut = null;
            $pmIn = null;
            $pmOut = null;
            $otIn = null;
            $otOut = null;

            if ($attendance) {
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

            if ($employee && !in_array($current->dayOfWeek, [0, 6])) {
                $departmentId = null;
                $designationId = null;
                if ($employee->employmentDetail) {
                    $departmentId = $employee->employmentDetail->department_id;
                    $designationId = $employee->employmentDetail->designation_id;
                }

                $effective = \App\Models\AttendanceExemption::resolveEffectivePunches(
                    $employee->id,
                    $departmentId,
                    $designationId,
                    $dateKey,
                    $amIn,
                    $amOut,
                    $pmIn,
                    $pmOut,
                    $schedule
                );

                $amIn = $effective['am_in'];
                $amOut = $effective['am_out'];
                $pmIn = $effective['pm_in'];
                $pmOut = $effective['pm_out'];
            }

            if ($isOnTravelOrder && !in_array($current->dayOfWeek, [0, 6])) {
                $records[] = [
                    'date' => $current->format('M d, Y'),
                    'day' => $current->format('l'),
                    'employee_name' => $employeeName,
                    'employee_id' => $employeeId,
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
                    'is_on_leave' => false,
                    'is_on_travel_order' => true,
                    'travel_order_info' => $travelOrderInfo,
                ];
                $current->addDay();
                continue;
            }

            if ($isOnLeave && !in_array($current->dayOfWeek, [0, 6])) {
                $records[] = [
                    'date' => $current->format('M d, Y'),
                    'day' => $current->format('l'),
                    'employee_name' => $employeeName,
                    'employee_id' => $employeeId,
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
                    'is_on_travel_order' => false,
                    'leave_info' => $leaveInfo,
                ];
                $current->addDay();
                continue;
            }

            $lateMinutes = 0;
            $undertimeMinutes = 0;
            
            if ($attendance && $attendance->accreditedHoursLogs->isNotEmpty()) {
                $log = $attendance->accreditedHoursLogs->last();
                $lateMinutes = $log->late_minutes;
                $undertimeMinutes = $log->undertime_minutes;
            } else {
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
                
                if ($attendance && $attendance->pm_out) {
                    try {
                        $pmOutTime = Carbon::parse($attendance->pm_out);
                        if ($pmOutTime->lt($expectedPmOut)) {
                            $undertimeMinutes = $pmOutTime->diffInMinutes($expectedPmOut);
                        }
                    } catch (\Exception $e) {
                        $undertimeMinutes = 0;
                    }
                }
            }

            $isAbandoned = false;
            if ($attendance && $amIn && !$amOut && !$pmIn && !in_array($current->dayOfWeek, [0, 6])) {
                $isAbandoned = true;
            }

            $isTrulyAbsent = !$attendance || (!$attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !$attendance->pm_out);

            if ($isTrulyAbsent && $current->gt($today) && !in_array($current->dayOfWeek, [0, 6])) {
                $current->addDay();
                continue;
            }

            $departmentId = null;
            $designationId = null;
            if ($employee && $employee->employmentDetail) {
                $departmentId = $employee->employmentDetail->department_id;
                $designationId = $employee->employmentDetail->designation_id;
            }

            $isExemptFromAbandoned = $employee ? \App\Models\AttendanceExemption::isExemptFromAbandoned(
                $employee->id,
                $departmentId,
                $designationId,
                $dateKey
            ) : false;

            $isExemptFromIncomplete = $employee ? \App\Models\AttendanceExemption::isExemptFromIncomplete(
                $employee->id,
                $departmentId,
                $designationId,
                $dateKey
            ) : false;

            $isIncomplete = false;
            $isAbsent = false;

            if ($attendance && !in_array($current->dayOfWeek, [0, 6])) {
                $hasAmPair = $amIn && $amOut;
                $hasPmPair = $pmIn && $pmOut;
                $hasOnlyAmIn = $attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !$attendance->pm_out;
                $hasOnlyPmIn = !$attendance->am_in && !$attendance->am_out && $attendance->pm_in && !$attendance->pm_out;

                if ($isAbandoned && !$isExemptFromAbandoned) {
                    $isAbsent = true;
                } else if (($hasOnlyAmIn || $hasOnlyPmIn) && !$isExemptFromAbandoned) {
                    $isAbsent = true;
                } else if (!$isExemptFromIncomplete && (
                    ($hasAmPair && !$hasPmPair) ||
                    (!$hasAmPair && $hasPmPair) ||
                    ($amIn && $amOut && $pmIn && !$pmOut)
                )) {
                    $isIncomplete = true;
                }
            }

            if (($isAbandoned || $isAbsent) && !$isExemptFromAbandoned) {
                $statusLabel = $isAbandoned ? 'ABANDONED' : 'ABSENT';
                $totalHoursMinutes = $attendance ? $attendance->total_hours : 0;
                $totalHours = '0 hrs';
                if ($totalHoursMinutes) {
                    $hours = (int)($totalHoursMinutes / 60);
                    $mins = $totalHoursMinutes % 60;
                    if ($mins > 0) {
                        $totalHours = $hours . 'h ' . (int)$mins . 'm';
                    } else {
                        $totalHours = $hours . ' hrs';
                    }
                }
                
                $records[] = [
                    'date' => $current->format('M d, Y'),
                    'day' => $current->format('l'),
                    'employee_name' => $employeeName,
                    'employee_id' => $employeeId,
                    'am_in' => $amIn,
                    'am_out' => $statusLabel,
                    'pm_in' => $statusLabel,
                    'pm_out' => $statusLabel,
                    'ot_in' => null,
                    'ot_out' => null,
                    'late_minutes' => 0,
                    'late_display' => '-',
                    'undertime' => 480,
                    'undertime_display' => '8 hrs',
                    'total_hours' => $totalHours,
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

            $totalHoursMinutes = $attendance ? $attendance->total_hours : 0;
            if ($totalHoursMinutes) {
                $hours = (int)($totalHoursMinutes / 60);
                $mins = $totalHoursMinutes % 60;
                if ($mins > 0) {
                    $totalHours = $hours . 'h ' . (int)$mins . 'm';
                } else {
                    $totalHours = $hours . ' hrs';
                }
            } else {
                $totalHours = '0 hrs';
            }
            $needsReview = ($lateMinutes > 0 && $undertimeMinutes > 0);

            $accreditedMinutes = 0;
            $amAccreditedMins = 0;
            $pmAccreditedMins = 0;
            $amGraceApplied = false;
            $pmGraceApplied = false;
            $scheduleUsed = null;
            $hasLog = false;
            
            if ($attendance && $attendance->accreditedHoursLogs->isNotEmpty()) {
                $log = $attendance->accreditedHoursLogs->last();
                $accreditedMinutes = $log->total_accredited_minutes;
                $amAccreditedMins = $log->am_accredited_minutes;
                $pmAccreditedMins = $log->pm_accredited_minutes;
                $amGraceApplied = $log->am_grace_applied;
                $pmGraceApplied = $log->pm_grace_applied;
                
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
                $toMin = fn($t) => $t ? (int)(explode(':', $t)[0]) * 60 + (int)(explode(':', $t)[1]) : null;
                
                $AM_START = $toMin($expectedAmIn->format('H:i'));
                $AM_END = $toMin($expectedAmOut->format('H:i'));
                $AM_GRACE = $AM_START + 5;
                $PM_START = $toMin($expectedPmIn->format('H:i'));
                $PM_END = $toMin($expectedPmOut->format('H:i'));
                $PM_GRACE = $PM_START + 5;
                
                $amInMin = $toMin($amIn);
                if ($amInMin <= $AM_GRACE) {
                    $amFrom = $AM_START;
                    $amGraceApplied = true;
                } else {
                    $amFrom = $amInMin;
                }
                $amTo = min($toMin($amOut), $AM_END);
                $amAccreditedMins = max(0, $amTo - $amFrom);
                
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
                'employee_name' => $employeeName,
                'employee_id' => $employeeId,
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
                'total_hours' => $totalHours,
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
        if (strpos($attendanceId, 'new_') === 0) {
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

    // Rest of the methods remain the same as in the original file
    // (All other methods like detailedDTR, exportDetailedDTR, etc.)
}
