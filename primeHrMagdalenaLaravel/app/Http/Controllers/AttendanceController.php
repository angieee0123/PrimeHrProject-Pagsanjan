<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
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

        $employees = Employee::with(['employmentDetail.departmentRelation'])
            ->get()
            ->map(function ($employee) use ($startDate, $endDate) {
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                $present = 0;
                $absent = 0;
                $late = 0;
                $halfday = 0;
                $overtime = 0;

                $workingDays = $this->getWorkingDays($startDate, $endDate);
                $attendedDates = $attendances->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

                foreach ($attendances as $attendance) {
                    $hasAttendance = $attendance->am_in || $attendance->pm_in;

                    if ($hasAttendance) {
                        $present++;

                        // Check if late (AM in after 8:15 AM with grace period)
                        if ($attendance->am_in) {
                            $amInTime = Carbon::parse($attendance->am_in);
                            $graceThreshold = Carbon::parse('08:15:00');
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

                        // Calculate overtime hours (ensure it starts at 5:00 PM)
                        if ($attendance->ot_in && $attendance->ot_out) {
                            $otIn = Carbon::parse($attendance->ot_in);
                            $otOut = Carbon::parse($attendance->ot_out);
                            $expectedOtStart = Carbon::parse('17:00:00');
                            
                            if ($otIn->lt($expectedOtStart)) {
                                $otIn = $expectedOtStart;
                            }
                            
                            $overtime += $otIn->diffInHours($otOut, false);
                        }
                    }
                }

                // Calculate absences (working days without attendance)
                foreach ($workingDays as $workingDay) {
                    if (!in_array($workingDay->format('Y-m-d'), $attendedDates)) {
                        $absent++;
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

        $employee = Employee::findOrFail($employeeId);

        // Fetch attendance records for the date range
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function($a) {
                return Carbon::parse($a->date)->format('Y-m-d');
            });

        $records = $this->generateDetailedRecords($startDate, $endDate, $attendances);

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

        $employee = Employee::with('employmentDetail.departmentRelation')->findOrFail($employeeId);

        // Fetch attendance records for the date range
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function($a) {
                return Carbon::parse($a->date)->format('Y-m-d');
            });

        $records = $this->generateDetailedRecords($startDate, $endDate, $attendances);

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

    private function generateDetailedRecords($startDate, $endDate, $attendances)
    {
        $records = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateKey = $current->format('Y-m-d');
            $attendance = $attendances->get($dateKey);

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

            // Calculate late minutes with 15-min grace period
            $lateMinutes = 0;
            if ($attendance && $attendance->am_in) {
                try {
                    $amInTime = new \DateTime($attendance->am_in);
                    $graceThreshold = new \DateTime('08:15:00');
                    $expectedIn = new \DateTime('08:00:00');

                    if ($amInTime > $graceThreshold) {
                        $lateInterval = $expectedIn->diff($amInTime);
                        $lateMinutes = ($lateInterval->h * 60) + $lateInterval->i;
                    }
                } catch (\Exception $e) {
                    $lateMinutes = 0;
                }
            }

            // Calculate undertime (in minutes)
            // If within grace period (08:15), no undertime penalty
            $undertime = 0;
            if ($attendance && $attendance->pm_out && !in_array($current->dayOfWeek, [0, 6])) {
                try {
                    // Check if within grace period
                    $isWithinGrace = false;
                    if ($attendance->am_in) {
                        $amInTime = new \DateTime($attendance->am_in);
                        $graceThreshold = new \DateTime('08:15:00');
                        $isWithinGrace = ($amInTime <= $graceThreshold);
                    }

                    // Only calculate undertime if NOT within grace period
                    if (!$isWithinGrace) {
                        $workHours = 0;
                        if ($attendance->am_in) {
                            $amInTime = new \DateTime($attendance->am_in);
                            $pmOutTime = new \DateTime($attendance->pm_out);
                            $workInterval = $amInTime->diff($pmOutTime);
                            $workHours = ($workInterval->h + ($workInterval->i / 60)) - 1; // minus 1 hr break
                        }

                        // Undertime = max(0, 8 hours - WorkHours) in minutes
                        $undertime = max(0, (8 - $workHours) * 60);
                    }
                } catch (\Exception $e) {
                    $undertime = 0;
                }
            }

            // Calculate total hours
            $totalHours = 0;
            if ($attendance) {
                try {
                    $workHours = 0;
                    $otHours = 0;

                    // If we have AM In and PM Out
                    if ($attendance->am_in && $attendance->pm_out) {
                        $amInTime = new \DateTime($attendance->am_in);
                        $pmOutTime = new \DateTime($attendance->pm_out);

                        // WorkHours = (PM Out - AM In) - 1 hour
                        $workInterval = $amInTime->diff($pmOutTime);
                        $workHours = ($workInterval->h + ($workInterval->i / 60)) - 1;
                    }
                    // If only AM session
                    elseif ($attendance->am_in && $attendance->am_out && !$attendance->pm_in && !$attendance->pm_out) {
                        $amInTime = new \DateTime($attendance->am_in);
                        $amOutTime = new \DateTime($attendance->am_out);
                        $workInterval = $amInTime->diff($amOutTime);
                        $workHours = $workInterval->h + ($workInterval->i / 60);
                    }
                    // If only PM session
                    elseif ($attendance->pm_in && $attendance->pm_out && !$attendance->am_in && !$attendance->am_out) {
                        $pmInTime = new \DateTime($attendance->pm_in);
                        $pmOutTime = new \DateTime($attendance->pm_out);
                        $workInterval = $pmInTime->diff($pmOutTime);
                        $workHours = $workInterval->h + ($workInterval->i / 60);
                    }

                    // Calculate overtime
                    if ($attendance->ot_in && $attendance->ot_out) {
                        $otInTime = new \DateTime($attendance->ot_in);
                        $otOutTime = new \DateTime($attendance->ot_out);
                        $expectedOtStart = new \DateTime('17:00:00');

                        // Ensure OT starts at 5:00 PM
                        if ($otInTime < $expectedOtStart) {
                            $otInTime = clone $expectedOtStart;
                        }

                        $otInterval = $otInTime->diff($otOutTime);
                        $otHours = $otInterval->h + ($otInterval->i / 60);
                    }

                    // Convert late and undertime from minutes to hours
                    $lateHours = $lateMinutes / 60;
                    $undertimeHours = $undertime / 60;

                    // Store actual work hours for display
                    $actualWorkHours = $workHours + $otHours;
                    
                    // Total = WorkHours + OT - Late - Undertime
                    $totalHours = max(0, $workHours + $otHours - $lateHours - $undertimeHours);
                    
                    // Add grace period bonus (15 min = 0.25 hrs) if within grace period
                    if ($attendance->am_in) {
                        $amInTime = new \DateTime($attendance->am_in);
                        $graceThreshold = new \DateTime('08:15:00');
                        if ($amInTime <= $graceThreshold) {
                            $totalHours += 0.25; // Add 15 minutes bonus
                        }
                    }
                    
                    // Determine if needs review (has both late and undertime)
                    $needsReview = ($lateMinutes > 0 && $undertime > 0);
                } catch (\Exception $e) {
                    $totalHours = 0;
                    $actualWorkHours = 0;
                    $needsReview = false;
                }
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
                'total_hours' => round($totalHours, 1) . ' hrs',
                'actual_work_hours' => isset($actualWorkHours) ? round($actualWorkHours, 1) : 0,
                'needs_review' => isset($needsReview) ? $needsReview : false,
                'is_incomplete' => !$amOut || !$pmIn,
                'attendance_id' => $attendance ? $attendance->id : null,
                'date_key' => $current->format('Y-m-d'),
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

        $attendance->update([
            'am_in' => $validated['am_in'],
            'am_out' => $validated['am_out'],
            'pm_in' => $validated['pm_in'],
            'pm_out' => $validated['pm_out'],
            'ot_in' => $validated['ot_in'],
            'ot_out' => $validated['ot_out'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance record corrected successfully',
        ]);
    }
}
