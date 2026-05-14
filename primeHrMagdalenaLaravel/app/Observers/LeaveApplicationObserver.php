<?php

namespace App\Observers;

use App\Models\LeaveApplication;
use App\Models\Attendance;
use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;
use App\Services\CscTimeConversionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveApplicationObserver
{
    /**
     * Handle the LeaveApplication "updated" event.
     * Automatically create attendance records when leave is approved.
     */
    public function updated(LeaveApplication $leaveApplication)
    {
        // Check if status changed to 'approved'
        if ($leaveApplication->isDirty('status') && $leaveApplication->status === 'approved') {
            $this->createAttendanceRecordsForLeave($leaveApplication);
        }
    }

    /**
     * Create attendance records for approved leave
     */
    private function createAttendanceRecordsForLeave(LeaveApplication $leaveApplication)
    {
        try {
            DB::beginTransaction();

            $employee = $leaveApplication->employee;
            $startDate = Carbon::parse($leaveApplication->start_date);
            $endDate = Carbon::parse($leaveApplication->end_date);
            $current = $startDate->copy();

            $recordsCreated = 0;

            while ($current->lte($endDate)) {
                // Skip weekends using CSC service
                if (!CscTimeConversionService::isWeekend($current)) {
                    $dateKey = $current->format('Y-m-d');

                    // Check if attendance record already exists
                    $existingAttendance = Attendance::where('employee_id', $employee->id)
                        ->where('date', $dateKey)
                        ->first();

                    if (!$existingAttendance) {
                        // Get employee schedule for this date
                        $schedule = $employee->getScheduleForDate($dateKey);

                        // Create attendance record with CSC standard 8-hour work day
                        $attendance = Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $dateKey,
                            'am_in' => 'ON_LEAVE',
                            'am_out' => 'ON_LEAVE',
                            'pm_in' => 'ON_LEAVE',
                            'pm_out' => 'ON_LEAVE',
                            'ot_in' => null,
                            'ot_out' => null,
                            'accredited_hours' => CscTimeConversionService::MINUTES_PER_WORK_DAY, // 480 minutes = 8 hours
                            'total_hours' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                        ]);

                        // Create accredited hours log with CSC standard values
                        $accreditedLog = AccreditedHoursLog::create([
                            'attendance_id' => $attendance->id,
                            'employee_id' => $employee->id,
                            'schedule_id' => $schedule ? $schedule->id : null,
                            'am_accredited_minutes' => CscTimeConversionService::MINUTES_PER_HALF_DAY, // 240 minutes = 4 hours
                            'pm_accredited_minutes' => CscTimeConversionService::MINUTES_PER_HALF_DAY, // 240 minutes = 4 hours
                            'ot_minutes' => 0,
                            'late_minutes' => 0,
                            'undertime_minutes' => 0,
                            'total_accredited_minutes' => CscTimeConversionService::MINUTES_PER_WORK_DAY, // 480 minutes = 8 hours
                            'total_actual_minutes' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                            'am_grace_applied' => false,
                            'pm_grace_applied' => false,
                            'computation_notes' => sprintf(
                                'On approved leave: %s - %s (%s)',
                                $leaveApplication->leaveType->leave_name ?? 'Leave',
                                $leaveApplication->application_number,
                                $leaveApplication->leaveType->leave_code ?? 'N/A'
                            ),
                        ]);

                        // Create daily salary computation
                        DailySalaryComputation::computeFromAccreditedLog($accreditedLog);

                        $recordsCreated++;
                    }
                }

                $current->addDay();
            }

            DB::commit();

            Log::info("Created {$recordsCreated} attendance records for approved leave", [
                'leave_application_id' => $leaveApplication->id,
                'application_number' => $leaveApplication->application_number,
                'employee_id' => $employee->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create attendance records for approved leave', [
                'leave_application_id' => $leaveApplication->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle the LeaveApplication "deleted" event.
     * Clean up attendance records if leave is deleted.
     */
    public function deleted(LeaveApplication $leaveApplication)
    {
        // Only clean up if leave was approved
        if ($leaveApplication->status === 'approved') {
            $this->removeAttendanceRecordsForLeave($leaveApplication);
        }
    }

    /**
     * Remove attendance records created for leave
     */
    private function removeAttendanceRecordsForLeave(LeaveApplication $leaveApplication)
    {
        try {
            DB::beginTransaction();

            $employee = $leaveApplication->employee;
            $startDate = Carbon::parse($leaveApplication->start_date);
            $endDate = Carbon::parse($leaveApplication->end_date);

            // Find and delete attendance records marked as 'ON_LEAVE'
            $deletedCount = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('am_in', 'ON_LEAVE')
                ->where('am_out', 'ON_LEAVE')
                ->where('pm_in', 'ON_LEAVE')
                ->where('pm_out', 'ON_LEAVE')
                ->delete();

            // Note: Cascading deletes will handle accredited_hours_log and daily_salary_computations

            DB::commit();

            Log::info("Removed {$deletedCount} attendance records for deleted leave", [
                'leave_application_id' => $leaveApplication->id,
                'application_number' => $leaveApplication->application_number,
                'employee_id' => $employee->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to remove attendance records for deleted leave', [
                'leave_application_id' => $leaveApplication->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
