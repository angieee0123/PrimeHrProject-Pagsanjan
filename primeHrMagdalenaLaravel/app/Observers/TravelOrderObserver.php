<?php

namespace App\Observers;

use App\Models\TravelOrder;
use App\Models\Attendance;
use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;
use App\Services\CscTimeConversionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TravelOrderObserver
{
    /**
     * Handle the TravelOrder "updated" event.
     * Automatically create attendance records when travel order is approved.
     */
    public function updated(TravelOrder $travelOrder)
    {
        // Check if status changed to 'approved'
        if ($travelOrder->isDirty('status') && $travelOrder->status === 'approved') {
            $this->createAttendanceRecordsForTravelOrder($travelOrder);
        }
    }

    /**
     * Create attendance records for approved travel order
     */
    private function createAttendanceRecordsForTravelOrder(TravelOrder $travelOrder)
    {
        try {
            DB::beginTransaction();

            $employee = $travelOrder->employee;
            $startDate = Carbon::parse($travelOrder->travel_date);
            $endDate = Carbon::parse($travelOrder->return_date);
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

                        // Create attendance record with null time values and TRAVEL_ORDER type (same as leave system)
                        $attendance = Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $dateKey,
                            'am_in' => null,
                            'am_out' => null,
                            'pm_in' => null,
                            'pm_out' => null,
                            'ot_in' => null,
                            'ot_out' => null,
                            'accredited_hours' => CscTimeConversionService::MINUTES_PER_WORK_DAY, // 480 minutes = 8 hours
                            'total_hours' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                            'attendance_type' => 'TRAVEL_ORDER',
                            'remarks' => "Travel Order: {$travelOrder->destination} - {$travelOrder->order_number}",
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
                                'On approved travel order: %s - %s (%s)',
                                $travelOrder->destination,
                                $travelOrder->order_number,
                                $travelOrder->purpose
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

            Log::info("Created {$recordsCreated} attendance records for approved travel order", [
                'travel_order_id' => $travelOrder->id,
                'order_number' => $travelOrder->order_number,
                'employee_id' => $employee->id,
                'destination' => $travelOrder->destination,
                'travel_date' => $startDate->format('Y-m-d'),
                'return_date' => $endDate->format('Y-m-d'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create attendance records for approved travel order', [
                'travel_order_id' => $travelOrder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle the TravelOrder "deleted" event.
     * Clean up attendance records if travel order is deleted.
     */
    public function deleted(TravelOrder $travelOrder)
    {
        // Only clean up if travel order was approved
        if ($travelOrder->status === 'approved') {
            $this->removeAttendanceRecordsForTravelOrder($travelOrder);
        }
    }

    /**
     * Remove attendance records created for travel order
     */
    private function removeAttendanceRecordsForTravelOrder(TravelOrder $travelOrder)
    {
        try {
            DB::beginTransaction();

            $employee = $travelOrder->employee;
            $startDate = Carbon::parse($travelOrder->travel_date);
            $endDate = Carbon::parse($travelOrder->return_date);

            // Find and delete attendance records with TRAVEL_ORDER type and null time values
            $deletedCount = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('attendance_type', 'TRAVEL_ORDER')
                ->whereNull('am_in')
                ->whereNull('am_out')
                ->whereNull('pm_in')
                ->whereNull('pm_out')
                ->where('accredited_hours', CscTimeConversionService::MINUTES_PER_WORK_DAY)
                ->delete();

            // Note: Cascading deletes will handle accredited_hours_log and daily_salary_computations

            DB::commit();

            Log::info("Removed {$deletedCount} attendance records for deleted travel order", [
                'travel_order_id' => $travelOrder->id,
                'order_number' => $travelOrder->order_number,
                'employee_id' => $employee->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to remove attendance records for deleted travel order', [
                'travel_order_id' => $travelOrder->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}