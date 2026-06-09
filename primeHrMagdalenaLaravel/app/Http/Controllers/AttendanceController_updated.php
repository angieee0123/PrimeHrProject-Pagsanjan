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

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Optimized: Use pagination from the start
        $employeesQuery = Employee::with(['employmentDetail.departmentRelation', 'schedule'])
            ->orderBy('first_name');

        if ($department && $department !== 'All Departments') {
            $employeesQuery->whereHas('employmentDetail.departmentRelation', function($q) use ($department) {
                $q->where('name', $department);
            });
        }

        $employees = $employeesQuery->paginate($perPage, ['*'], 'page', $page);
        
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

        // Simplified detailed records pagination
        $detailedRecords = [];
        $detailedPagination = [
            'current_page' => 1,
            'per_page' => 10,
            'total' => 0,
            'last_page' => 0,
            'from' => 0,
            'to' => 0,
        ];

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
        // Use database queries instead of in-memory processing
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
        
        // Build leave dates efficiently
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
        // Use CSC service to get working days (excludes weekends automatically)
        return CscTimeConversionService::getWorkingDates($startDate, $endDate);
    }

    public function employeeAppointment($employeeId)
    {
        $employee = Employee::with('employmentDetail')->findOrFail($employeeId);
        $appointmentDate = $employee->employmentDetail->appointment_date ?? now()->format('Y-m-d');

        return response()->json([
            'appointment_date' => $appointmentDate,
        ]);
    }

    public function dtrSummary(Request $request, $employeeId)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();
        $today = Carbon::now()->startOfDay();

        $employee = Employee::with(['employmentDetail', 'schedule'])->findOrFail($employeeId);
        $appointmentDate = $employee->employmentDetail && $employee->employmentDetail->appointment_date 
            ? Carbon::parse($employee->employmentDetail->appointment_date)->startOfDay()
            : null;

        // Adjust start date to appointment date if it's after start date
        if ($appointmentDate && $appointmentDate->gt($startDate)) {
            $startDate = $appointmentDate;
        }

        // Check if entire range is in the future (no approved leaves/travel orders)
        if ($startDate->gt($today)) {
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
                ->get();

            $approvedTravelOrders = \App\Models\TravelOrder::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('travel_date', [$startDate, $endDate])
                          ->orWhereBetween('return_date', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('travel_date', '<=', $startDate)
                                ->where('return_date', '>=', $endDate);
                          });
                })
                ->get();

            if ($approvedLeaves->isEmpty() && $approvedTravelOrders->isEmpty()) {
                return response()->json([
                    'working_days' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'halfday' => 0,
                    'overtime' => 0,
                    'rate' => 0,
                    'message' => 'Record not yet available for future dates.',
                ]);
            }
        }

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

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
            ->get();

        $workingDays = $this->getWorkingDays($startDate, $endDate);
        $attendedDates = $attendances->pluck('date')->map(fn($d) => is_string($d) ? $d : $d->format('Y-m-d'))->toArray();
        
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

        $present = 0;
        $absent = 0;
        $late = 0;
        $halfday = 0;
        $overtime = 0;

        foreach ($attendances as $attendance) {
            $hasAttendance = $attendance->am_in || $attendance->pm_in;

            if ($hasAttendance) {
                $present++;

                $attendanceDate = is_string($attendance->date) ? $attendance->date : $attendance->date->format('Y-m-d');
                $scheduleForDate = $employee->getScheduleForDate($attendanceDate);
                $graceThreshold = $scheduleForDate 
                    ? Carbon::parse($scheduleForDate->am_in)->addMinutes(5)
                    : Carbon::parse('08:05:00');

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
                    $expectedPmOut = $scheduleForDate 
                        ? Carbon::parse($scheduleForDate->pm_out) 
                        : Carbon::parse('17:00:00');
                    
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
                    $present++;
                } else {
                    $absent++;
                }
            }
        }

        $totalDays = $present + $absent + $halfday;
        $rate = $totalDays > 0 ? round(($present / $totalDays) * 100) : 0;

        return response()->json([
            'working_days' => $totalDays,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'halfday' => $halfday,
            'overtime' => $overtime,
            'rate' => $rate,
        ]);
    }
}
