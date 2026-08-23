<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Employee;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function assign(Request $request)
    {
        $data = $request->validate([
            'schedule_id' => 'nullable|exists:schedules,id',
            'employee_id' => 'required|exists:employees,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'am_in'       => 'required',
            'am_out'      => 'required',
            'pm_in'       => 'required',
            'pm_out'      => 'required',
        ]);

        // Check for overlapping schedules
        $overlapQuery = Schedule::where('employee_id', $data['employee_id'])
            ->where(function($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                      ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                      ->orWhere(function($q) use ($data) {
                          $q->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                      });
            });

        // Exclude current schedule if editing
        if ($data['schedule_id']) {
            $overlapQuery->where('id', '!=', $data['schedule_id']);
        }

        $overlappingSchedules = $overlapQuery->get();

        if ($overlappingSchedules->count() > 0) {
            $overlapDetails = $overlappingSchedules->map(function($s) {
                return Carbon::parse($s->start_date)->format('M d, Y') . ' - ' . Carbon::parse($s->end_date)->format('M d, Y');
            })->join(', ');

            return redirect()->route('admin.personnel')
                ->with('error', "Schedule overlaps with existing schedule(s): {$overlapDetails}. Please adjust the dates.")
                ->with('active_tab', 'schedules');
        }

        if ($data['schedule_id']) {
            // Update existing schedule
            $schedule = Schedule::findOrFail($data['schedule_id']);
            $schedule->update([
                'start_date'  => $data['start_date'],
                'end_date'    => $data['end_date'],
                'am_in'       => $data['am_in'],
                'am_out'      => $data['am_out'],
                'pm_in'       => $data['pm_in'],
                'pm_out'      => $data['pm_out'],
            ]);
            $message = 'Schedule updated successfully.';

            // Recalculate attendance for this schedule period
            $attendanceController = app(\App\Http\Controllers\AttendanceController::class);
            $recalculatedCount = $attendanceController->recalculateAttendanceForSchedule(
                $data['employee_id'],
                $data['start_date'],
                $data['end_date']
            );

            if ($recalculatedCount > 0) {
                $message .= " Recalculated {$recalculatedCount} attendance record(s).";
            }
        } else {
            // Create new schedule
            Schedule::create([
                'employee_id' => $data['employee_id'],
                'start_date'  => $data['start_date'],
                'end_date'    => $data['end_date'],
                'am_in'       => $data['am_in'],
                'am_out'      => $data['am_out'],
                'pm_in'       => $data['pm_in'],
                'pm_out'      => $data['pm_out'],
            ]);
            $message = 'Schedule assigned successfully.';

            // Recalculate attendance for this schedule period
            $attendanceController = app(\App\Http\Controllers\AttendanceController::class);
            $recalculatedCount = $attendanceController->recalculateAttendanceForSchedule(
                $data['employee_id'],
                $data['start_date'],
                $data['end_date']
            );

            if ($recalculatedCount > 0) {
                $message .= " Recalculated {$recalculatedCount} attendance record(s).";
            }
        }

        return redirect()->route('admin.personnel')->with('success', $message)->with('active_tab', 'schedules');
    }

    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'employee_ids'   => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'am_in'          => 'required',
            'am_out'         => 'required',
            'pm_in'          => 'required',
            'pm_out'         => 'required',
        ]);

        $successCount = 0;
        $skippedEmployees = [];

        foreach ($data['employee_ids'] as $employeeId) {
            // Check for overlapping schedules
            $hasOverlap = Schedule::where('employee_id', $employeeId)
                ->where(function($query) use ($data) {
                    $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                          ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                          ->orWhere(function($q) use ($data) {
                              $q->where('start_date', '<=', $data['start_date'])
                                ->where('end_date', '>=', $data['end_date']);
                          });
                })
                ->exists();

            if ($hasOverlap) {
                $employee = Employee::find($employeeId);
                $fullName = trim($employee->first_name . ' ' . $employee->last_name);
                $skippedEmployees[] = $fullName;
                continue;
            }

            Schedule::create([
                'employee_id' => $employeeId,
                'start_date'  => $data['start_date'],
                'end_date'    => $data['end_date'],
                'am_in'       => $data['am_in'],
                'am_out'      => $data['am_out'],
                'pm_in'       => $data['pm_in'],
                'pm_out'      => $data['pm_out'],
            ]);
            $successCount++;
        }

        if ($successCount > 0 && count($skippedEmployees) > 0) {
            $skippedList = implode(', ', $skippedEmployees);
            return redirect()->route('admin.personnel')
                ->with('success', "Schedule assigned to {$successCount} employee(s). Skipped {count($skippedEmployees)} due to overlaps: {$skippedList}")
                ->with('active_tab', 'schedules');
        } elseif ($successCount > 0) {
            return redirect()->route('admin.personnel')
                ->with('success', "Schedule assigned to {$successCount} employee(s) successfully.")
                ->with('active_tab', 'schedules');
        } else {
            $skippedList = implode(', ', $skippedEmployees);
            return redirect()->route('admin.personnel')
                ->with('error', "No schedules were assigned. All selected employees have overlapping schedules: {$skippedList}")
                ->with('active_tab', 'schedules');
        }
    }

    public function checkOverlap(Request $request)
    {
        $employeeId = $request->employee_id;
        $scheduleId = $request->schedule_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $overlapQuery = Schedule::where('employee_id', $employeeId)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            });

        if ($scheduleId) {
            $overlapQuery->where('id', '!=', $scheduleId);
        }

        $overlappingSchedules = $overlapQuery->get();

        if ($overlappingSchedules->count() > 0) {
            $overlapDetails = $overlappingSchedules->map(function($s) {
                return Carbon::parse($s->start_date)->format('M d, Y') . ' - ' . Carbon::parse($s->end_date)->format('M d, Y');
            })->join(', ');

            return response()->json([
                'has_overlap' => true,
                'overlap_details' => "This schedule overlaps with: {$overlapDetails}"
            ]);
        }

        return response()->json(['has_overlap' => false]);
    }

    public function forEmployee($employeeId)
    {
        $schedules = Schedule::where('employee_id', $employeeId)
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json(['schedules' => $schedules]);
    }

    public function show($id)
    {
        $schedule = Schedule::findOrFail($id);
        return response()->json($schedule);
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.personnel')->with('success', 'Schedule deleted successfully.')->with('active_tab', 'schedules');
    }

    public function remove($id)
    {
        $schedule = Schedule::where('employee_id', $id)->first();

        if ($schedule) {
            $schedule->delete();
            return redirect()->route('admin.personnel')->with('success', 'Schedule removed successfully.');
        }

        return redirect()->route('admin.personnel')->with('error', 'Schedule not found.');
    }

    /**
     * Work Schedules → CSV.
     *
     * The rows are built before streaming starts. They used to be assembled
     * inside the stream callback, which runs after the 200 and the CSV
     * headers have already gone out — so the catch below could never fire
     * and a failure reached the browser as a raw 500 mid-download instead
     * of the intended redirect.
     */
    public function export()
    {
        try {
            $employees = Employee::with(['schedule', 'employmentDetail.departmentRelation'])
                ->orderBy('last_name')
                ->get();

            $time = fn (?string $value) => $value ? Carbon::parse($value)->format('g:i A') : '--:--';

            $rows = $employees->map(function (Employee $emp) use ($time) {
                $schedule = $emp->currentSchedule();
                $status = $emp->scheduleStatus();

                $middle = $emp->middle_name ? substr($emp->middle_name, 0, 1) . '. ' : '';
                $suffix = $emp->suffix ? ' ' . $emp->suffix : '';

                return [
                    $emp->employee_id,
                    trim($emp->first_name . ' ' . $middle . $emp->last_name . $suffix),
                    $emp->employmentDetail?->departmentRelation?->name ?? 'N/A',
                    $time($schedule?->am_in),
                    $time($schedule?->am_out),
                    $time($schedule?->pm_in),
                    $time($schedule?->pm_out),
                    // The same states the table shows, resolved by the same
                    // method, so the CSV and the screen cannot disagree about
                    // who is covered.
                    $status['label'],
                    // What the screen puts under the badge: the date the
                    // state turns over. A CSV of statuses with no dates
                    // cannot answer "whose schedule lapses this month",
                    // which is the question the column exists for.
                    $status['date']
                        ? $status['note'] . ' ' . Carbon::parse($status['date'])->format('m/d/Y')
                        : '',
                ];
            })->all();

            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=schedules_' . now()->format('Y-m-d') . '.csv',
            ];

            return response()->stream(function () use ($rows) {
                $file = fopen('php://output', 'w');
                // BOM so Excel reads the UTF-8 names correctly.
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, ['Employee ID', 'Employee Name', 'Department', 'AM In', 'AM Out', 'PM In', 'PM Out', 'Status', 'Effective']);

                foreach ($rows as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);
            }, 200, $headers);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.personnel')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}
