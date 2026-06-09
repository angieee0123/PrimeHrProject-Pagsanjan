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

        if ($status && $status !== 'All Status') {
            $attendanceRecords = array_filter($attendanceRecords, fn($e) => $e['status'] === $status);
            $attendanceRecords = array_values($attendanceRecords);
        }

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

        $tab = $request->get('tab', 'summary');
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
            $detailedPerPage = (int)$perPage ?: 10;
            $detailedPage = (int)$page ?: 1;
            $employeeName = $request->get('employee_name');
            
            $allEmps = Employee::with(['employmentDetail.departmentRelation', 'schedule'])->orderBy('first_name')->get();
            if ($department && $department !== 'All Departments') {
                $allEmps = $allEmps->filter(fn($e) => $e->employmentDetail?->departmentRelation?->name === $department);
            }
            
            $detailedRecordsData = [];
            foreach ($allEmps as $emp) {
                $att = Attendance::where('employee_id', $emp->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->with('accreditedHoursLogs.schedule')
                    ->orderBy('date', 'asc')
                    ->get()
                    ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));
                
                $leaves = \App\Models\LeaveApplication::where('employee_id', $emp->id)
                    ->where('status', 'approved')
                    ->where(function($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function($q2) use ($startDate, $endDate) {
                              $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
                          });
                    })
                    ->with('leaveType')->get();
                
                $recs = $this->generateDetailedRecords($startDate, $endDate, $att, $emp, $leaves);
                $detailedRecordsData = array_merge($detailedRecordsData, $recs);
            }
            
            if ($employeeName) {
                $detailedRecordsData = array_filter($detailedRecordsData, fn($r) => $r['employee_name'] === $employeeName);
                $detailedRecordsData = array_values($detailedRecordsData);
            }
            
            $total = count($detailedRecordsData);
            $lastPage = max(1, ceil($total / $detailedPerPage));
            $from = (($detailedPage - 1) * $detailedPerPage) + 1;
            $to = min($detailedPage * $detailedPerPage, $total);
            
            $detailedRecords = array_slice($detailedRecordsData, ($detailedPage - 1) * $detailedPerPage, $detailedPerPage);
            
            $detailedPagination = [
                'current_page' => $detailedPage,
                'per_page' => $detailedPerPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $total > 0 ? $from : 0,
                'to' => $total > 0 ? $to : 0,
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
}
