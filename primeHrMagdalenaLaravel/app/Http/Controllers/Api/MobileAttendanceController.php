<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmployeeAttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileAttendanceController extends Controller
{
    public function __construct(
        private readonly EmployeeAttendanceController $attendanceController
    ) {
    }

    /**
     * Attendance stats, summary bar, and DTR records for the current period.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $payload = $this->attendanceController->buildAttendancePayload($employee, $request);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'present' => (int) $payload['present'],
                    'absent' => (int) $payload['absent'],
                    'late' => (int) $payload['late'],
                    'overtime_hours' => (float) $payload['overtime'],
                    'on_leave' => (int) $payload['onLeave'],
                    'attendance_rate' => (float) $payload['rate'],
                    'working_days' => (int) $payload['workingDaysCount'],
                    'halfday' => (int) $payload['halfday'],
                ],
                'summary' => [
                    'present' => (int) $payload['present'],
                    'absent' => (int) $payload['absent'],
                    'late' => (int) $payload['late'],
                    'overtime_hours' => (float) $payload['overtime'],
                    'on_leave' => (int) $payload['onLeave'],
                ],
                'period_display' => $payload['periodDisplay'],
                'period_start' => $payload['period_start'],
                'period_end' => $payload['period_end'],
                'dtr_records' => array_values($payload['dtr_records']),
            ],
        ]);
    }

    /**
     * Detailed time records for a custom date range.
     */
    public function detailed(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        try {
            $payload = $this->attendanceController->buildDetailedPayload($employee, $request);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'records' => array_values($payload['records']),
                'employee' => $payload['employee'],
                'period_start' => $payload['period_start'],
                'period_end' => $payload['period_end'],
            ],
        ]);
    }
}
