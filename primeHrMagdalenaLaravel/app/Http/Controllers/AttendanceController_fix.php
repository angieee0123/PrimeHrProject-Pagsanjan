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
    private function generateDetailedRecords($startDate, $endDate, $attendances, $employee = null, $approvedLeaves = null, $approvedTravelOrders = null)
    {
        $graceMinutes = 5;
        $today = Carbon::now()->startOfDay();

        // Adjust start date to appointment date if employee hasn't been hired yet
        if ($employee && $employee->employmentDetail && $employee->employmentDetail->appointment_date) {
            $appointmentDate = Carbon::parse($employee->employmentDetail->appointment_date)->startOfDay();
            if ($appointmentDate->gt($startDate)) {
                $startDate = $appointmentDate;
            }
        }

        // Check if entire range is future and no approved leaves/travel orders, return empty
        if ($startDate->gt($today)) {
            if ((!$approvedLeaves || $approvedLeaves->isEmpty()) && (!$approvedTravelOrders || $approvedTravelOrders->isEmpty())) {
                return [];
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

        $records = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Skip dates before appointment date
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

            $current->addDay();
        }

        return $records;
    }
}
