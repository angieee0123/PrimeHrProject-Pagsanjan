<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;
use App\Models\PassSlip;
use App\Services\LateDeductionService;
use App\Services\UndertimeDeductionService;
use App\Services\CscTimeConversionService;
use App\Services\PassSlipComplianceService;
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

        // Build the flat, day-by-day log for the "Detailed Time Record" tab
        $detailedEmployeesQuery = Employee::with(['employmentDetail.departmentRelation', 'schedule'])
            ->orderBy('first_name');

        if ($department && $department !== 'All Departments') {
            $detailedEmployeesQuery->whereHas('employmentDetail.departmentRelation', function ($q) use ($department) {
                $q->where('name', $department);
            });
        }

        $detailedEmployees = $detailedEmployeesQuery->get();

        $employeeNameFilter = $request->get('employee_name');
        if ($employeeNameFilter) {
            $detailedEmployees = $detailedEmployees->filter(function ($employee) use ($employeeNameFilter) {
                $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ? $employee->middle_name . ' ' : '') . $employee->last_name);
                return $fullName === $employeeNameFilter;
            })->values();
        }

        $detailedResult = $this->buildDetailedRecords($detailedEmployees, $startDate, $endDate, (int) $page, (int) $perPage);
        $detailedRecords = $detailedResult['records'];
        $detailedPagination = $detailedResult['pagination'];

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
            ->select('id', 'date', 'am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out', 'attendance_type')
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

        $approvedPassSlips = PassSlip::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $passSlipCompliance = new PassSlipComplianceService();
        $passSlips = $approvedPassSlips->map(function ($slip) use ($employee, $passSlipCompliance) {
            $schedule = $employee->getScheduleForDate(Carbon::parse($slip->date)->format('Y-m-d'));

            return [
                'slip_number' => $slip->slip_number,
                'date' => Carbon::parse($slip->date)->format('M d, Y'),
                'type' => $slip->type,
                'purpose_label' => $slip->purpose_label,
                'destination' => $slip->destination,
                'time_out' => $slip->time_out,
                'time_in' => $slip->time_in,
                'gap_minutes' => $passSlipCompliance->computeGapMinutes($slip, $schedule),
                'excused' => $passSlipCompliance->isExcused($slip),
            ];
        })->values()->toArray();

        $present = 0;
        $absent = 0;
        $late = 0;
        $halfday = 0;
        $overtime = 0;
        $onLeave = 0;

        $workingDays = $this->getWorkingDays($startDate, $endDate);
        // Only dates with actual time punches count as "attended" — blank placeholder
        // rows (no am_in/pm_in) must not block the leave-day fallback check below.
        $attendedDates = $attendances
            ->filter(fn($a) => $a->am_in || $a->pm_in)
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->unique()
            ->toArray();
        
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

        // Track dates already counted as leave from attendance_type = 'LEAVE' rows
        $leaveAttendanceDates = $attendances
            ->where('attendance_type', 'LEAVE')
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();

        foreach ($attendances as $attendance) {
            $attendanceDate = $attendance->date->format('Y-m-d');

            // Count LEAVE-type attendance rows as on_leave + present
            if ($attendance->attendance_type === 'LEAVE') {
                $onLeave++;
                $present++;
                continue;
            }

            $hasAttendance = $attendance->am_in || $attendance->pm_in;

            if ($hasAttendance) {
                $present++;

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
            // Skip days already counted as leave via attendance_type = 'LEAVE'
            if (in_array($dayStr, $leaveAttendanceDates)) {
                continue;
            }
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
        $deptCode = 'N/A';
        if ($employee->employmentDetail && $employee->employmentDetail->departmentRelation) {
            $deptName = $employee->employmentDetail->departmentRelation->name;
            $deptCode = $employee->employmentDetail->departmentRelation->code ?? $deptName;
        }

        return [
            'id' => $employee->employee_id,
            'employee_id' => $employee->id,
            'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
            'position' => $employee->employmentDetail->position ?? 'N/A',
            'dept' => $deptName,
            'dept_code' => $deptCode,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'halfday' => $halfday,
            'overtime' => $overtime,
            'on_leave' => $onLeave,
            'rate' => $rate,
            'status' => $status,
            'photo' => $employee->photo,
            'pass_slips' => $passSlips,
        ];
    }

    private function getWorkingDays($startDate, $endDate)
    {
        // Use CSC service to get working days (excludes weekends automatically)
        return CscTimeConversionService::getWorkingDates($startDate, $endDate);
    }

    /**
     * Build the flat, day-by-day attendance log used by the "Detailed Time Record" tab
     * (one row per employee per calendar day), paginated at the row level.
     */
    private function buildDetailedRecords($employees, Carbon $startDate, Carbon $endDate, int $page, int $perPage)
    {
        $rows = [];
        $today = Carbon::now()->startOfDay();

        foreach ($employees as $employee) {
            $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ? $employee->middle_name . ' ' : '') . $employee->last_name);

            $attendances = Attendance::with('accreditedHoursLogs')
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get()
                ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

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

            // Map every weekday covered by an approved leave to its leave type name
            $leaveDatesMap = [];
            foreach ($approvedLeaves as $leave) {
                $cursor = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);
                while ($cursor->lte($leaveEnd)) {
                    if (!in_array($cursor->dayOfWeek, [0, 6])) {
                        $leaveDatesMap[$cursor->format('Y-m-d')] = $leave->leaveType->leave_name ?? 'Leave';
                    }
                    $cursor->addDay();
                }
            }

            // Approved pass slips for this employee in the period, keyed by date
            // (a day can have more than one), so this timeline reflects the same
            // real Pass Slip exceptions as the Summary tab and per-employee
            // Detailed DTR modal instead of flagging those days Absent/Needs Review.
            $approvedPassSlips = PassSlip::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            $passSlipDatesMap = [];
            foreach ($approvedPassSlips as $slip) {
                $passSlipDatesMap[Carbon::parse($slip->date)->format('Y-m-d')][] = $slip;
            }

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $dateKey = $current->format('Y-m-d');
                $attendance = $attendances->get($dateKey);
                $isWeekend = in_array($current->dayOfWeek, [0, 6]);
                $hasPunch = $attendance && ($attendance->am_in || $attendance->pm_in);
                $passSlipsToday = $passSlipDatesMap[$dateKey] ?? [];
                $isOnPassSlip = !empty($passSlipsToday);

                // A blank placeholder attendance row must not shadow an approved leave day
                // (same fix applied to the summary tab's on_leave calculation).
                $isOnLeave = !$isWeekend && !$hasPunch && isset($leaveDatesMap[$dateKey]);

                // A future weekday with nothing to show yet (no punch, no leave) isn't
                // "absent" — the day just hasn't happened. Mirrors the per-employee
                // Detailed DTR modal (generateDetailedRecords), which skips these
                // entirely instead of marking them absent.
                if (!$isWeekend && $current->gt($today) && !$hasPunch && !$isOnLeave) {
                    $current->addDay();
                    continue;
                }

                $isAbsent = !$isWeekend && !$isOnLeave && !$hasPunch && !$isOnPassSlip;

                $log = ($attendance && $attendance->accreditedHoursLogs->isNotEmpty())
                    ? $attendance->accreditedHoursLogs->last()
                    : null;

                $rows[] = [
                    'date_key' => $dateKey,
                    'date' => $current->format('M d, Y'),
                    'day' => $current->format('l'),
                    'employee_name' => $fullName,
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_id,
                    'photo' => $employee->photo,
                    'attendance_id' => $attendance->id ?? null,
                    'am_in' => $attendance && $attendance->am_in ? Carbon::parse($attendance->am_in)->format('H:i') : null,
                    'am_out' => $attendance && $attendance->am_out ? Carbon::parse($attendance->am_out)->format('H:i') : null,
                    'pm_in' => $attendance && $attendance->pm_in ? Carbon::parse($attendance->pm_in)->format('H:i') : null,
                    'pm_out' => $attendance && $attendance->pm_out ? Carbon::parse($attendance->pm_out)->format('H:i') : null,
                    'ot_in' => $attendance && $attendance->ot_in ? Carbon::parse($attendance->ot_in)->format('H:i') : null,
                    'ot_out' => $attendance && $attendance->ot_out ? Carbon::parse($attendance->ot_out)->format('H:i') : null,
                    'late_minutes' => $log->late_minutes ?? 0,
                    'undertime_minutes' => $log->undertime_minutes ?? 0,
                    'total_hours' => $attendance && $attendance->total_hours ? round($attendance->total_hours / 60, 2) : 0,
                    // Blade compares this against 480 (minutes in an 8-hour day), so it holds minutes despite the key name.
                    'accredited_hours' => $log->total_accredited_minutes ?? 0,
                    'is_absent' => $isAbsent,
                    'is_on_leave' => $isOnLeave,
                    'leave_info' => $isOnLeave ? ['leave_type' => $leaveDatesMap[$dateKey]] : null,
                    'is_on_pass_slip' => $isOnPassSlip,
                    'pass_slip_info' => $isOnPassSlip ? array_map(fn($slip) => [
                        'type' => $slip->type,
                        'purpose_label' => $slip->purpose_label,
                        'destination' => $slip->destination,
                    ], $passSlipsToday) : null,
                ];

                $current->addDay();
            }
        }

        usort($rows, fn($a, $b) => [$a['date_key'], $a['employee_name']] <=> [$b['date_key'], $b['employee_name']]);

        // Paginate by distinct date, not by flat employee-day row, so each
        // page holds N whole days (with every employee's record for those
        // days) instead of cutting a day's avatar cluster in half.
        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row['date_key']][] = $row;
        }
        $dateKeys = array_keys($byDate);

        $totalRows = count($rows);
        $totalDays = count($dateKeys);
        $lastPage = $perPage > 0 ? (int) ceil($totalDays / $perPage) : 0;
        $page = max(1, min($page, max(1, $lastPage)));

        $pageDateKeys = array_slice($dateKeys, ($page - 1) * $perPage, $perPage);

        $sliced = [];
        foreach ($pageDateKeys as $dateKey) {
            foreach ($byDate[$dateKey] as $row) {
                $sliced[] = $row;
            }
        }

        return [
            'records' => $sliced,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalRows,
                'total_days' => $totalDays,
                'last_page' => $lastPage,
                'from' => $totalDays > 0 ? ($page - 1) * $perPage + 1 : 0,
                'to' => $totalDays > 0 ? min($page * $perPage, $totalDays) : 0,
            ],
        ];
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

        $employee = Employee::with(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation', 'schedule'])->findOrFail($employeeId);

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

        // Get approved travel orders for this employee in the date range
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

        // Get approved pass slips for this employee in the date range
        $approvedPassSlips = PassSlip::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $records = $this->generateDetailedRecords($startDate, $endDate, $attendances, $employee, $approvedLeaves, $approvedTravelOrders, $approvedPassSlips);

        return response()->json([
            'records' => $records,
            'employee' => [
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_id' => $employee->employee_id,
                'department' => $employee->employmentDetail->departmentRelation->name ?? null,
                'position' => $employee->employmentDetail->designationRelation->title
                    ?? ($employee->employmentDetail->position ?? null),
                'employment_status' => $employee->employmentDetail->employment_status ?? null,
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
        // Use CSC service for consistent formatting
        return CscTimeConversionService::formatMinutes($minutes);
    }

    /**
     * Shared by every branch of generateDetailedRecords() (weekday, weekend,
     * etc.) so accredited hours are computed consistently regardless of which
     * branch a day falls into. Prefers an existing AccreditedHoursLog; falls
     * back to computing from actual punches, crediting a session from an
     * approved Official Activity pass slip's overlap when that session has
     * no punches because the employee was out on official business.
     */
    /**
     * Extra minutes to credit within a session when an approved Official
     * Activity pass slip covers the gap before the employee's actual
     * arrival and/or after their actual departure — e.g. he returned early
     * from official business partway through the session, so the punch
     * pair alone would under-credit the pre-arrival hour.
     */
    private function creditPassSlipGapMinutes(int $sessionStart, int $sessionEnd, int $realFrom, int $realTo, iterable $passSlipsForDate, PassSlipComplianceService $passSlipCompliance): int
    {
        $extra = 0;

        $preGap = max(0, $realFrom - $sessionStart);
        if ($preGap > 0) {
            $covered = 0;
            foreach ($passSlipsForDate as $slip) {
                $covered += $passSlipCompliance->excusedRangeOverlapMinutes($slip, $sessionStart, $realFrom);
            }
            $extra += min($preGap, $covered);
        }

        $postGap = max(0, $sessionEnd - $realTo);
        if ($postGap > 0) {
            $covered = 0;
            foreach ($passSlipsForDate as $slip) {
                $covered += $passSlipCompliance->excusedRangeOverlapMinutes($slip, $realTo, $sessionEnd);
            }
            $extra += min($postGap, $covered);
        }

        return $extra;
    }

    private function computeDayAccreditedMinutes(
        $attendance,
        ?string $amIn,
        ?string $amOut,
        ?string $pmIn,
        ?string $pmOut,
        bool $amExcusedByPassSlip,
        bool $pmExcusedByPassSlip,
        array $passSlipsToday,
        PassSlipComplianceService $passSlipCompliance,
        $schedule,
        Carbon $expectedAmIn,
        Carbon $expectedAmOut,
        Carbon $expectedPmIn,
        Carbon $expectedPmOut
    ): array {
        $accreditedMinutes = 0;
        $amAccreditedMins = 0;
        $pmAccreditedMins = 0;
        $amGraceApplied = false;
        $pmGraceApplied = false;
        $scheduleUsed = null;
        $hasLog = false;
        $log = null;

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
        } elseif ($attendance && (($amIn && $amOut) || $amExcusedByPassSlip) && (($pmIn && $pmOut) || $pmExcusedByPassSlip)) {
            $toMin = fn($t) => $t ? (int)(explode(':', $t)[0]) * 60 + (int)(explode(':', $t)[1]) : null;

            $AM_START = $toMin($expectedAmIn->format('H:i'));
            $AM_END = $toMin($expectedAmOut->format('H:i'));
            $AM_GRACE = $AM_START + 5;
            $PM_START = $toMin($expectedPmIn->format('H:i'));
            $PM_END = $toMin($expectedPmOut->format('H:i'));
            $PM_GRACE = $PM_START + 5;

            if ($amIn && $amOut) {
                $amInMin = $toMin($amIn);
                if ($amInMin <= $AM_GRACE) {
                    $amFrom = $AM_START;
                    $amGraceApplied = true;
                } else {
                    $amFrom = $amInMin;
                }
                $amTo = min($toMin($amOut), $AM_END);
                $amAccreditedMins = max(0, $amTo - $amFrom);
                if (!empty($passSlipsToday)) {
                    $amAccreditedMins = min($AM_END - $AM_START, $amAccreditedMins + $this->creditPassSlipGapMinutes($AM_START, $AM_END, $amFrom, $amTo, $passSlipsToday, $passSlipCompliance));
                }
            } elseif ($amExcusedByPassSlip) {
                $amAccreditedMins = min($AM_END - $AM_START, array_reduce($passSlipsToday, fn($carry, $slip) => max($carry, $passSlipCompliance->sessionOverlapMinutes($slip, $schedule, 'am')), 0));
            }

            if ($pmIn && $pmOut) {
                $pmInMin = $toMin($pmIn);
                if ($pmInMin <= $PM_GRACE) {
                    $pmFrom = $PM_START;
                    $pmGraceApplied = true;
                } else {
                    $pmFrom = $pmInMin;
                }
                $pmTo = min($toMin($pmOut), $PM_END);
                $pmAccreditedMins = max(0, $pmTo - $pmFrom);
                if (!empty($passSlipsToday)) {
                    $pmAccreditedMins = min($PM_END - $PM_START, $pmAccreditedMins + $this->creditPassSlipGapMinutes($PM_START, $PM_END, $pmFrom, $pmTo, $passSlipsToday, $passSlipCompliance));
                }
            } elseif ($pmExcusedByPassSlip) {
                $pmAccreditedMins = min($PM_END - $PM_START, array_reduce($passSlipsToday, fn($carry, $slip) => max($carry, $passSlipCompliance->sessionOverlapMinutes($slip, $schedule, 'pm')), 0));
            }

            $accreditedMinutes = $amAccreditedMins + $pmAccreditedMins;
            $scheduleUsed = [
                'am_in' => $expectedAmIn->format('H:i'),
                'am_out' => $expectedAmOut->format('H:i'),
                'pm_in' => $expectedPmIn->format('H:i'),
                'pm_out' => $expectedPmOut->format('H:i'),
            ];
        }

        return compact('accreditedMinutes', 'amAccreditedMins', 'pmAccreditedMins', 'amGraceApplied', 'pmGraceApplied', 'scheduleUsed', 'hasLog', 'log');
    }

    private function generateDetailedRecords($startDate, $endDate, $attendances, $employee = null, $approvedLeaves = null, $approvedTravelOrders = null, $approvedPassSlips = null)
    {
        $passSlipCompliance = new PassSlipComplianceService();

        $graceMinutes = 5;
        $today = Carbon::now()->startOfDay();
        
        $effectiveStart = $startDate->copy();
        if ($employee && $employee->employmentDetail && $employee->employmentDetail->appointment_date) {
            $appointmentDate = Carbon::parse($employee->employmentDetail->appointment_date)->startOfDay();
            // Only clamp if appointment date falls within the requested range
            if ($appointmentDate->gt($effectiveStart) && $appointmentDate->lte($endDate)) {
                $effectiveStart = $appointmentDate;
            }
        }
        $startDate = $effectiveStart;
        
        if ($startDate->gt($today)) {
            if ((!$approvedLeaves || $approvedLeaves->isEmpty()) && (!$approvedTravelOrders || $approvedTravelOrders->isEmpty())) {
                // Only return empty if ALL dates are in future and NO leaves/travel orders
                // But still check if there's any attendance data
                $hasAnyAttendance = $attendances->count() > 0;
                if (!$hasAnyAttendance) {
                    return [];
                }
            }
        }

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

        // Build pass slip dates map (a day can have more than one approved slip)
        $passSlipDatesMap = [];
        if ($approvedPassSlips) {
            foreach ($approvedPassSlips as $passSlip) {
                $dateKey = Carbon::parse($passSlip->date)->format('Y-m-d');
                $passSlipDatesMap[$dateKey][] = $passSlip;
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
            $passSlipsToday = $passSlipDatesMap[$dateKey] ?? [];
            $isOnPassSlip = !empty($passSlipsToday);

            // Get schedule for this specific date
            $schedule = $employee ? $employee->getScheduleForDate($dateKey) : null;
            $expectedAmIn = $schedule ? Carbon::parse($schedule->am_in) : Carbon::parse('08:00:00');
            $expectedAmOut = $schedule ? Carbon::parse($schedule->am_out) : Carbon::parse('12:00:00');
            $expectedPmIn = $schedule ? Carbon::parse($schedule->pm_in) : Carbon::parse('13:00:00');
            $expectedPmOut = $schedule ? Carbon::parse($schedule->pm_out) : Carbon::parse('17:00:00');
            
            $graceThresholdAm = $expectedAmIn->copy()->addMinutes($graceMinutes);
            $graceThresholdPm = $expectedPmIn->copy()->addMinutes($graceMinutes);

            // Built once per day so every branch below (travel order / leave /
            // weekend / absent / normal) can attach the same approved Pass Slip
            // annotation regardless of which branch the day falls into.
            $passSlipInfo = $isOnPassSlip ? array_map(fn($slip) => [
                'slip_number' => $slip->slip_number,
                'type' => $slip->type,
                'purpose_label' => $slip->purpose_label,
                'destination' => $slip->destination,
                'time_out' => $slip->time_out,
                'time_in' => $slip->time_in,
                'gap_minutes' => $passSlipCompliance->computeGapMinutes($slip, $schedule ?? null),
                'excused' => $passSlipCompliance->isExcused($slip),
            ], $passSlipsToday) : null;

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

            // Apply exemption auto-fill for display and status evaluation
            $activeExemption = null;
            $autoFilled = [];
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
                $activeExemption = $effective['exemption'];
                $autoFilled = $effective['auto_filled'] ?? [];
            }

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
                    'is_on_leave' => false,
                    'is_on_travel_order' => true,
                    'travel_order_info' => $travelOrderInfo,
                    'is_on_pass_slip' => $isOnPassSlip,
                    'pass_slip_info' => $passSlipInfo,
                ];
                $current->addDay();
                continue;
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
                    'is_on_travel_order' => false,
                    'leave_info' => $leaveInfo,
                    'is_on_pass_slip' => $isOnPassSlip,
                    'pass_slip_info' => $passSlipInfo,
                ];
                $current->addDay();
                continue;
            }

            // Calculate late minutes with grace period
            $lateMinutes = 0;
            $undertimeMinutes = 0;
            
            // If we have a log, use the values from the log (already calculated correctly)
            if ($attendance && $attendance->accreditedHoursLogs->isNotEmpty()) {
                $log = $attendance->accreditedHoursLogs->last();
                $lateMinutes = $log->late_minutes;
                $undertimeMinutes = $log->undertime_minutes;
            } else {
                // Fallback: Calculate if no log exists
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

            // Apply CSC-based Pass Slip adjustment: Official Activity excuses the gap,
            // Personal Reason charges it (biometric AM/PM punches alone wouldn't
            // otherwise reflect a mid-session personal errand).
            if ($isOnPassSlip) {
                $undertimeMinutes = $passSlipCompliance->adjustUndertimeMinutes($undertimeMinutes, $passSlipsToday, $schedule ?? null);
            }

            // Check if employee only timed in AM without returning (uses effective punches after auto-fill)
            $isAbandoned = false;
            if ($attendance && $amIn && !$amOut && !$pmIn && !in_array($current->dayOfWeek, [0, 6])) {
                $isAbandoned = true;
            }

            // If an approved Official Activity pass slip's window fully covers a
            // session, that session's missing punch pair is explained — the
            // employee was on official business, not incomplete/absent. Computed
            // here (before the weekend branch) so both weekday and weekend days
            // can credit accredited hours for it consistently.
            $amExcusedByPassSlip = false;
            $pmExcusedByPassSlip = false;
            if ($isOnPassSlip) {
                foreach ($passSlipsToday as $slip) {
                    if ($passSlipCompliance->excusesSession($slip, $schedule ?? null, 'am')) {
                        $amExcusedByPassSlip = true;
                    }
                    if ($passSlipCompliance->excusesSession($slip, $schedule ?? null, 'pm')) {
                        $pmExcusedByPassSlip = true;
                    }
                }
            }

            // Always include weekends in the table
            if (in_array($current->dayOfWeek, [0, 6])) {
                $weekendAccredited = $this->computeDayAccreditedMinutes(
                    $attendance, $amIn, $amOut, $pmIn, $pmOut,
                    $amExcusedByPassSlip, $pmExcusedByPassSlip, $passSlipsToday, $passSlipCompliance,
                    $schedule ?? null, $expectedAmIn, $expectedAmOut, $expectedPmIn, $expectedPmOut
                );

                $records[] = [
                    'date'                => $current->format('M d, Y'),
                    'day'                 => $current->format('l'),
                    'am_in'               => $amIn,
                    'am_out'              => $amOut,
                    'pm_in'               => $pmIn,
                    'pm_out'              => $pmOut,
                    'ot_in'               => $otIn,
                    'ot_out'              => $otOut,
                    'late_minutes'        => 0,
                    'late_display'        => null,
                    'undertime'           => 0,
                    'undertime_display'   => null,
                    'total_hours'         => $attendance ? (function() use ($attendance) {
                        $m = $attendance->total_hours ?? 0;
                        $h = (int)($m / 60); $min = $m % 60;
                        return $min > 0 ? "{$h}h {$min}m" : "{$h} hrs";
                    })() : '0 hrs',
                    'accredited_minutes'  => $weekendAccredited['accreditedMinutes'],
                    'am_accredited_minutes' => $weekendAccredited['amAccreditedMins'],
                    'pm_accredited_minutes' => $weekendAccredited['pmAccreditedMins'],
                    'am_grace_applied'    => $weekendAccredited['amGraceApplied'],
                    'pm_grace_applied'    => $weekendAccredited['pmGraceApplied'],
                    'schedule'            => $weekendAccredited['scheduleUsed'] ?? [
                        'am_in'  => $expectedAmIn->format('H:i'),
                        'am_out' => $expectedAmOut->format('H:i'),
                        'pm_in'  => $expectedPmIn->format('H:i'),
                        'pm_out' => $expectedPmOut->format('H:i'),
                    ],
                    'has_log'             => $weekendAccredited['hasLog'],
                    'needs_review'        => false,
                    'is_incomplete'       => false,
                    'is_absent'           => false,
                    'is_abandoned'        => false,
                    'attendance_id'       => $attendance ? $attendance->id : null,
                    'date_key'            => $current->format('Y-m-d'),
                    'is_on_leave'         => false,
                    'leave_info'          => null,
                    'is_on_pass_slip'     => $isOnPassSlip,
                    'pass_slip_info'      => $passSlipInfo,
                ];
                $current->addDay();
                continue;
            }

            // Check if truly absent (no time records at all)
            $isTrulyAbsent = !$attendance || (!$attendance->am_in && !$attendance->am_out && !$attendance->pm_in && !$attendance->pm_out);

            // Skip only future weekdays with no records, no leave, and no travel order
            if ($isTrulyAbsent && $current->gt($today) && !$isOnLeave && !$isOnTravelOrder) {
                $current->addDay();
                continue;
            }

            // Get employee's department and designation for exemption checking
            $departmentId = null;
            $designationId = null;
            if ($employee && $employee->employmentDetail) {
                $departmentId = $employee->employmentDetail->department_id;
                $designationId = $employee->employmentDetail->designation_id;
            }

            // Legacy flag exemptions (date-aware)
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

            // Determine if incomplete vs absent (uses effective punches after exemption auto-fill)
            $isIncomplete = false;
            $isAbsent = false;

            if ($attendance && !in_array($current->dayOfWeek, [0, 6])) {
                $hasAmPair = ($amIn && $amOut) || $amExcusedByPassSlip;
                $hasPmPair = ($pmIn && $pmOut) || $pmExcusedByPassSlip;
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

            // If abandoned or only single time-in, treat as ABSENT (unless exempt)
            if (($isAbandoned || $isAbsent) && !$isExemptFromAbandoned) {
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
                    'is_on_pass_slip' => $isOnPassSlip,
                    'pass_slip_info' => $passSlipInfo,
                ];
                $current->addDay();
                continue;
            }

            // Use stored total_hours from database (actual time worked in minutes)
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
            } // Exact hours with 4 decimals
            $needsReview = ($lateMinutes > 0 && $undertimeMinutes > 0);

            // Get accredited hours from log if exists, otherwise calculate
            // (shared with the weekend branch above via computeDayAccreditedMinutes)
            $dayAccredited = $this->computeDayAccreditedMinutes(
                $attendance, $amIn, $amOut, $pmIn, $pmOut,
                $amExcusedByPassSlip, $pmExcusedByPassSlip, $passSlipsToday, $passSlipCompliance,
                $schedule ?? null, $expectedAmIn, $expectedAmOut, $expectedPmIn, $expectedPmOut
            );
            $accreditedMinutes = $dayAccredited['accreditedMinutes'];
            $amAccreditedMins = $dayAccredited['amAccreditedMins'];
            $pmAccreditedMins = $dayAccredited['pmAccreditedMins'];
            $amGraceApplied = $dayAccredited['amGraceApplied'];
            $pmGraceApplied = $dayAccredited['pmGraceApplied'];
            $scheduleUsed = $dayAccredited['scheduleUsed'];
            $hasLog = $dayAccredited['hasLog'];
            $log = $dayAccredited['log'];

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
                'total_hours' => $totalHours,
                'accredited_minutes' => $accreditedMinutes,
                'am_accredited_minutes' => $amAccreditedMins,
                'pm_accredited_minutes' => $pmAccreditedMins,
                'am_grace_applied' => $amGraceApplied,
                'pm_grace_applied' => $pmGraceApplied,
                'late_deducted_from_leave' => $hasLog && $log->late_deducted_from_leave,
                'late_deduction_leave_type' => $hasLog ? $log->late_deduction_leave_type : null,
                'undertime_deducted_from_leave' => $hasLog && $log->undertime_deducted_from_leave,
                'undertime_deduction_leave_type' => $hasLog ? $log->undertime_deduction_leave_type : null,
                'lwop_minutes' => $hasLog ? $log->lwop_minutes : 0,
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
                'is_on_pass_slip' => $isOnPassSlip,
                'pass_slip_info' => $passSlipInfo,
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

            $approvedPassSlips = PassSlip::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where('date', $date)
                ->get()
                ->map(fn($slip) => [
                    'slip_number' => $slip->slip_number,
                    'type'        => $slip->type,
                    'time_out'    => $slip->time_out ? substr($slip->time_out, 0, 5) : null,
                    'time_in'     => $slip->time_in  ? substr($slip->time_in,  0, 5) : null,
                ])->values()->toArray();

            return response()->json([
                'id'            => null,
                'employee_id'   => $employeeId,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'date'          => $date,
                'am_in'         => null,
                'am_out'        => null,
                'pm_in'         => null,
                'pm_out'        => null,
                'ot_in'         => null,
                'ot_out'        => null,
                'is_new'        => true,
                'pass_slips'    => $approvedPassSlips,
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

        $dateStr = Carbon::parse($attendance->date)->format('Y-m-d');
        $approvedPassSlips = PassSlip::where('employee_id', $attendance->employee_id)
            ->where('status', 'approved')
            ->where('date', $dateStr)
            ->get()
            ->map(fn($slip) => [
                'slip_number' => $slip->slip_number,
                'type'        => $slip->type,
                'time_out'    => $formatTime($slip->time_out),
                'time_in'     => $formatTime($slip->time_in),
            ])->values()->toArray();

        return response()->json([
            'id'            => $attendance->id,
            'employee_id'   => $attendance->employee_id,
            'employee_name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
            'date'          => $dateStr,
            'am_in'         => $formatTime($attendance->am_in),
            'am_out'        => $formatTime($attendance->am_out),
            'pm_in'         => $formatTime($attendance->pm_in),
            'pm_out'        => $formatTime($attendance->pm_out),
            'ot_in'         => $formatTime($attendance->ot_in),
            'ot_out'        => $formatTime($attendance->ot_out),
            'is_new'        => false,
            'pass_slips'    => $approvedPassSlips,
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
    private function computeAccreditedHours($employeeId, $date, ?string $amIn, ?string $amOut, ?string $pmIn, ?string $pmOut, ?string $otIn = null, ?string $otOut = null, ?iterable $passSlipsForDate = null): array
    {
        if (!$amIn && !$amOut && !$pmIn && !$pmOut) {
            return ['accredited_minutes' => null, 'log_data' => null];
        }

        $employee = Employee::find($employeeId);
        $schedule = $employee ? $employee->getScheduleForDate($date) : null;

        $departmentId = $employee?->employmentDetail?->department_id;
        $designationId = $employee?->employmentDetail?->designation_id;

        $effective = \App\Models\AttendanceExemption::resolveEffectivePunches(
            $employeeId,
            $departmentId,
            $designationId,
            $date,
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
        $exemption = $effective['exemption'];

        $toMin = fn($t) => $t ? (int)(explode(':', $t)[0]) * 60 + (int)(explode(':', $t)[1]) : null;

        // Use employee's schedule or defaults
        $AM_START   = $schedule ? $toMin($schedule->am_in) : 480;  // Default 08:00
        $AM_END     = $schedule ? $toMin($schedule->am_out) : 720;  // Default 12:00
        $AM_GRACE   = $AM_START + 5;  // 5 minutes grace
        $PM_START   = $schedule ? $toMin($schedule->pm_in) : 780;  // Default 13:00
        $PM_END     = $schedule ? $toMin($schedule->pm_out) : 1020; // Default 17:00
        $PM_GRACE   = $PM_START + 5;  // 5 minutes grace

        // Abandoned: AM in only with no AM out and no PM in (after exemption auto-fill)
        if ($amIn && !$amOut && !$pmIn && !($exemption && $exemption->exempt_from_abandoned)) {
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

        $passSlipCompliance = new PassSlipComplianceService();

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
            if ($passSlipsForDate) {
                $amMins = min($AM_END - $AM_START, $amMins + $this->creditPassSlipGapMinutes($AM_START, $AM_END, $amFrom, $amTo, $passSlipsForDate, $passSlipCompliance));
            }
        } elseif ($passSlipsForDate) {
            // No AM punches — credit the session if an approved Official
            // Activity pass slip's window explains the absence.
            foreach ($passSlipsForDate as $slip) {
                if ($passSlipCompliance->excusesSession($slip, $schedule, 'am')) {
                    $amMins = max($amMins, min($AM_END - $AM_START, $passSlipCompliance->sessionOverlapMinutes($slip, $schedule, 'am')));
                }
            }
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
            if ($passSlipsForDate) {
                $pmMins = min($PM_END - $PM_START, $pmMins + $this->creditPassSlipGapMinutes($PM_START, $PM_END, $pmFrom, $pmTo, $passSlipsForDate, $passSlipCompliance));
            }
        } elseif ($passSlipsForDate) {
            foreach ($passSlipsForDate as $slip) {
                if ($passSlipCompliance->excusesSession($slip, $schedule, 'pm')) {
                    $pmMins = max($pmMins, min($PM_END - $PM_START, $passSlipCompliance->sessionOverlapMinutes($slip, $schedule, 'pm')));
                }
            }
        }

        // Calculate OT
        $otMins = 0;
        if ($otIn && $otOut) {
            $otMins = max(0, $toMin($otOut) - $toMin($otIn));
        }

        // Calculate late and undertime
        $lateMins = 0;
        $undertimeMins = 0;
        
        // AM In late
        if ($amIn) {
            $amInMin = $toMin($amIn);
            if ($amInMin > $AM_GRACE) {
                $lateMins += $amInMin - $AM_START;
            }
        }
        
        // AM Out undertime (left early before lunch)
        if ($amOut) {
            $amOutMin = $toMin($amOut);
            if ($amOutMin < $AM_END) {
                $undertimeMins += $AM_END - $amOutMin;
            }
        }
        
        // PM In late (returned late from lunch)
        if ($pmIn) {
            $pmInMin = $toMin($pmIn);
            if ($pmInMin > $PM_GRACE) {
                $lateMins += $pmInMin - $PM_START;
            }
        }
        
        // PM Out undertime (left early at end of day)
        if ($pmOut) {
            $pmOutMin = $toMin($pmOut);
            if ($pmOutMin < $PM_END) {
                $undertimeMins += $PM_END - $pmOutMin;
            }
        }

        $totalAccredited = $amMins + $pmMins;
        $totalActual = 0;
        if ($amIn && $amOut) $totalActual += $toMin($amOut) - $toMin($amIn);
        if ($pmIn && $pmOut) $totalActual += $toMin($pmOut) - $toMin($pmIn);
        if ($otIn && $otOut) $totalActual += $otMins;

        // Apply CSC-based Pass Slip adjustment (see PassSlipComplianceService):
        // Official Activity excuses the gap, Personal Reason charges it.
        if ($passSlipsForDate) {
            $undertimeMins = $passSlipCompliance->adjustUndertimeMinutes($undertimeMins, $passSlipsForDate, $schedule);
        }

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

        $passSlipsByDate = PassSlip::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($slip) => Carbon::parse($slip->date)->format('Y-m-d'));

        $recalculatedCount = 0;

        foreach ($attendances as $attendance) {
            // Skip if no time records
            if (!$attendance->am_in && !$attendance->pm_in) {
                continue;
            }

            $dateKey = Carbon::parse($attendance->date)->format('Y-m-d');

            $computationResult = $this->computeAccreditedHours(
                $employeeId,
                $dateKey,
                $attendance->am_in ? Carbon::parse($attendance->am_in)->format('H:i') : null,
                $attendance->am_out ? Carbon::parse($attendance->am_out)->format('H:i') : null,
                $attendance->pm_in ? Carbon::parse($attendance->pm_in)->format('H:i') : null,
                $attendance->pm_out ? Carbon::parse($attendance->pm_out)->format('H:i') : null,
                $attendance->ot_in ? Carbon::parse($attendance->ot_in)->format('H:i') : null,
                $attendance->ot_out ? Carbon::parse($attendance->ot_out)->format('H:i') : null,
                $passSlipsByDate->get($dateKey)
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
            
            // Get the old accredited hours log before making changes
            $oldLog = $attendance->accreditedHoursLogs()->latest()->first();
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
            $oldLog = null;
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

        $passSlipsForDate = PassSlip::where('employee_id', $validated['employee_id'])
            ->where('status', 'approved')
            ->where('date', $validated['date'])
            ->get();

        $computationResult = $this->computeAccreditedHours(
            $validated['employee_id'],
            $validated['date'],
            $validated['am_in'],
            $validated['am_out'],
            $validated['pm_in'],
            $validated['pm_out'],
            $validated['ot_in'],
            $validated['ot_out'],
            $passSlipsForDate
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
            // Snapshot deduction state BEFORE updateOrCreate overwrites the same row
            $hadPreviousDeductions = $oldLog && ($oldLog->late_deducted_from_leave || $oldLog->undertime_deducted_from_leave);

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
            
            // Handle leave balance recalculation
            $recalculationSummary = null;
            if ($hadPreviousDeductions) {
                // Correction of an existing record that already had leave deductions — reverse + reapply
                $recalculationService = new \App\Services\AttendanceCorrectionLeaveRecalculationService();
                $recalculationSummary = $recalculationService->recalculateLeaveDeductions($oldLog, $accreditedLog);
                $summaryMessage = $recalculationService->getSummaryMessage($recalculationSummary);

                Log::info('Leave balance recalculation completed', [
                    'attendance_id' => $attendance->id,
                    'employee_id' => $validated['employee_id'],
                    'date' => $validated['date'],
                    'summary' => $summaryMessage,
                ]);
            } else {
                // New record or first-time correction — process deductions fresh
                $lateDeductionService = new LateDeductionService();
                $lateDeductionService->processLateDeduction($accreditedLog);

                $undertimeDeductionService = new UndertimeDeductionService();
                $undertimeDeductionService->processUndertimeDeduction($accreditedLog);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance record corrected successfully',
            'recalculation_summary' => $recalculationSummary ?? null,
        ]);
    }

    /**
     * Get options for exemption dropdown based on type
     */
    public function getExemptionOptions(Request $request)
    {
        $type = $request->get('type');
        $options = [];

        switch ($type) {
            case 'employee':
                $options = Employee::select('id', DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, ' (', employee_id, ')') as name"))
                    ->orderBy('first_name')
                    ->get();
                break;

            case 'department':
                $options = \App\Models\Department::select('id', 'name')
                    ->orderBy('name')
                    ->get();
                break;

            case 'designation':
                $options = \App\Models\Designation::select('id', 'title as name')
                    ->orderBy('title')
                    ->get();
                break;
        }

        return response()->json($options);
    }

    /**
     * Get a single exemption
     */
    public function getExemption($id)
    {
        $exemption = \App\Models\AttendanceExemption::findOrFail($id);
        return response()->json($exemption);
    }

    /**
     * Store a new exemption
     */
    public function storeExemption(Request $request)
    {
        $validated = $request->validate([
            'exemption_type' => 'required|in:employee,department,designation',
            'reference_id' => 'required',
            'exempt_from_abandoned' => 'boolean',
            'exempt_from_incomplete' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'am_in_not_required' => 'boolean',
            'am_out_not_required' => 'boolean',
            'pm_in_not_required' => 'boolean',
            'pm_out_not_required' => 'boolean',
            'auto_fill_am_out' => 'boolean',
            'auto_fill_pm_in' => 'boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        // Get reference name based on type
        $referenceName = $this->getReferenceName($validated['exemption_type'], $validated['reference_id']);

        // Check if exemption already exists for the same type and reference
        $existing = \App\Models\AttendanceExemption::where('exemption_type', $validated['exemption_type'])
            ->where('reference_id', $validated['reference_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'An exemption for this ' . $validated['exemption_type'] . ' already exists.'
            ], 422);
        }

        $exemption = \App\Models\AttendanceExemption::create([
            'exemption_type' => $validated['exemption_type'],
            'reference_id' => $validated['reference_id'],
            'reference_name' => $referenceName,
            'exempt_from_abandoned' => $validated['exempt_from_abandoned'] ?? false,
            'exempt_from_incomplete' => $validated['exempt_from_incomplete'] ?? false,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'am_in_not_required' => $validated['am_in_not_required'] ?? false,
            'am_out_not_required' => $validated['am_out_not_required'] ?? false,
            'pm_in_not_required' => $validated['pm_in_not_required'] ?? false,
            'pm_out_not_required' => $validated['pm_out_not_required'] ?? false,
            'auto_fill_am_out' => $validated['auto_fill_am_out'] ?? true,
            'auto_fill_pm_in' => $validated['auto_fill_pm_in'] ?? true,
            'reason' => $validated['reason'],
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Exemption created successfully',
            'exemption' => $exemption
        ]);
    }

    /**
     * Update an existing exemption
     */
    public function updateExemption(Request $request, $id)
    {
        $exemption = \App\Models\AttendanceExemption::findOrFail($id);

        $validated = $request->validate([
            'exemption_type' => 'required|in:employee,department,designation',
            'reference_id' => 'required',
            'exempt_from_abandoned' => 'boolean',
            'exempt_from_incomplete' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'am_in_not_required' => 'boolean',
            'am_out_not_required' => 'boolean',
            'pm_in_not_required' => 'boolean',
            'pm_out_not_required' => 'boolean',
            'auto_fill_am_out' => 'boolean',
            'auto_fill_pm_in' => 'boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        // Get reference name based on type
        $referenceName = $this->getReferenceName($validated['exemption_type'], $validated['reference_id']);

        // Check if another exemption with same type and reference exists
        $existing = \App\Models\AttendanceExemption::where('exemption_type', $validated['exemption_type'])
            ->where('reference_id', $validated['reference_id'])
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'An exemption for this ' . $validated['exemption_type'] . ' already exists.'
            ], 422);
        }

        $exemption->update([
            'exemption_type' => $validated['exemption_type'],
            'reference_id' => $validated['reference_id'],
            'reference_name' => $referenceName,
            'exempt_from_abandoned' => $validated['exempt_from_abandoned'] ?? false,
            'exempt_from_incomplete' => $validated['exempt_from_incomplete'] ?? false,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'am_in_not_required' => $validated['am_in_not_required'] ?? false,
            'am_out_not_required' => $validated['am_out_not_required'] ?? false,
            'pm_in_not_required' => $validated['pm_in_not_required'] ?? false,
            'pm_out_not_required' => $validated['pm_out_not_required'] ?? false,
            'auto_fill_am_out' => $validated['auto_fill_am_out'] ?? true,
            'auto_fill_pm_in' => $validated['auto_fill_pm_in'] ?? true,
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Exemption updated successfully',
            'exemption' => $exemption
        ]);
    }

    /**
     * Delete an exemption
     */
    public function destroyExemption($id)
    {
        $exemption = \App\Models\AttendanceExemption::findOrFail($id);
        $exemption->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exemption deleted successfully'
        ]);
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

        $employee = Employee::with(['employmentDetail', 'schedule'])->findOrFail($employeeId);
        $appointmentDate = $employee->employmentDetail && $employee->employmentDetail->appointment_date 
            ? Carbon::parse($employee->employmentDetail->appointment_date)->startOfDay()
            : null;

        // Adjust start date to appointment date if it's after start date
        if ($appointmentDate && $appointmentDate->gt($startDate)) {
            $startDate = $appointmentDate;
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

    /**
     * Helper method to get reference name
     */
    private function getReferenceName($type, $referenceId)
    {
        switch ($type) {
            case 'employee':
                $employee = Employee::find($referenceId);
                return $employee ? trim($employee->first_name . ' ' . ($employee->middle_name ? $employee->middle_name . ' ' : '') . $employee->last_name) : 'Unknown';

            case 'department':
                $department = \App\Models\Department::find($referenceId);
                return $department ? $department->name : 'Unknown';

            case 'designation':
                $designation = \App\Models\Designation::find($referenceId);
                return $designation ? $designation->title : 'Unknown';

            default:
                return 'Unknown';
        }
    }
}
