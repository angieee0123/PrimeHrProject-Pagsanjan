<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\DailySalaryComputation;
use App\Models\LeaveBalance;
use App\Models\Attendance;
use Carbon\Carbon;

class PermanentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return view('permanent.dashboard.permanentDashboard')->with('error', 'Employee record not found.');
        }

        // Get current period (last 15 days)
        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->subDays(15);
        $endDate = $currentDate;

        // Get latest salary computation for current period
        $latestSalary = DailySalaryComputation::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date', 'desc')
            ->first();

        // Calculate period totals
        $periodComputations = DailySalaryComputation::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        $basicPay = $periodComputations->sum('daily_basic_pay');
        $totalDeductions = $periodComputations->sum('late_deduction') + $periodComputations->sum('undertime_deduction');
        $netPay = $basicPay - $totalDeductions;

        // Get leave balances for current year
        $currentYear = $currentDate->year;
        $leaveBalances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $currentYear)
            ->with('leaveType')
            ->get();

        // Get attendance for current month
        $monthStart = $currentDate->copy()->startOfMonth();
        $monthEnd = $currentDate->copy()->endOfMonth();
        
        $attendanceRecords = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $totalDays = $attendanceRecords->count();
        $presentDays = $attendanceRecords->where('status', 'present')->count();
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

        // Get payslip history (group by semi-monthly periods)
        $payslips = DailySalaryComputation::where('employee_id', $employee->id)
            ->selectRaw('
                YEAR(work_date) as year,
                MONTH(work_date) as month,
                CASE 
                    WHEN DAY(work_date) <= 15 THEN 1 
                    ELSE 2 
                END as cutoff,
                MIN(work_date) as period_start,
                MAX(work_date) as period_end,
                SUM(daily_basic_pay) as basic_pay,
                SUM(late_deduction + undertime_deduction) as deductions,
                SUM(daily_basic_pay - late_deduction - undertime_deduction) as net_pay,
                MAX(work_date) as pay_date
            ')
            ->groupByRaw('YEAR(work_date), MONTH(work_date), CASE WHEN DAY(work_date) <= 15 THEN 1 ELSE 2 END')
            ->orderByRaw('YEAR(work_date) DESC, MONTH(work_date) DESC, CASE WHEN DAY(work_date) <= 15 THEN 1 ELSE 2 END DESC')
            ->limit(5)
            ->get()
            ->map(function($payslip) {
                $payslip->period_label = Carbon::parse($payslip->period_start)->format('M d') . '-' . Carbon::parse($payslip->period_end)->format('d, Y');
                return $payslip;
            });

        return view('permanent.dashboard.permanentDashboard', compact(
            'employee',
            'basicPay',
            'netPay',
            'totalDeductions',
            'leaveBalances',
            'attendanceRate',
            'presentDays',
            'payslips',
            'startDate',
            'endDate'
        ));
    }
}
