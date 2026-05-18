<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\DailySalaryComputation;
use App\Models\LeaveBalance;
use App\Models\Attendance;
use App\Models\EmployeeDeduction;
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
        // Consider present if they have at least AM In or PM In
        $presentDays = $attendanceRecords->filter(function($record) {
            return $record->am_in !== null || $record->pm_in !== null;
        })->count();
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
                SUM(late_deduction) as late_deduction,
                SUM(undertime_deduction) as undertime_deduction,
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

        // Get employee deductions with actual computed amounts
        $deductions = EmployeeDeduction::where('employee_id', $employee->id)
            ->with('deductionType')
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($deduction) use ($employee) {
                // Get actual deduction amount from the most recent salary computation
                $latestComputation = DailySalaryComputation::where('employee_id', $employee->id)
                    ->orderBy('work_date', 'desc')
                    ->first();
                
                $deductionType = $deduction->deductionType;
                $calculatedAmount = 0;
                
                if ($deduction->installment_amount > 0) {
                    // Use installment amount if set (for loans)
                    $calculatedAmount = $deduction->installment_amount;
                } elseif ($deduction->amount > 0) {
                    // Use fixed amount if set
                    $calculatedAmount = $deduction->amount;
                } elseif ($deductionType) {
                    // Calculate based on deduction type configuration
                    if (strtoupper($deductionType->computation_type) === 'FIXED' && $deductionType->percentage_rate > 0) {
                        // For FIXED type, percentage_rate field stores the MONTHLY fixed amount
                        // Divide by 2 to get per-cutoff amount (semi-monthly)
                        $calculatedAmount = $deductionType->percentage_rate / 2;
                    } elseif (strtoupper($deductionType->computation_type) === 'PERCENTAGE' && $deductionType->percentage_rate > 0 && $latestComputation) {
                        // Calculate percentage-based deduction
                        $monthlyRate = $latestComputation->monthly_rate;
                        $dailyRate = $latestComputation->daily_rate;
                        
                        if (strtoupper($deductionType->base_salary_type) === 'MONTHLY') {
                            $calculatedAmount = ($monthlyRate * $deductionType->percentage_rate) / 100 / 2; // Semi-monthly
                        } else {
                            $calculatedAmount = ($dailyRate * $deductionType->percentage_rate) / 100;
                        }
                        
                        // Apply max amount if set
                        if ($deductionType->max_amount > 0 && $calculatedAmount > $deductionType->max_amount) {
                            $calculatedAmount = $deductionType->max_amount / 2; // Semi-monthly
                        }
                    }
                }
                
                $deduction->calculated_amount = $calculatedAmount;
                return $deduction;
            });

        // Prepare chart data
        $chartData = $this->prepareChartData($employee->id);

        return view('permanent.dashboard.permanentDashboard', compact(
            'employee',
            'basicPay',
            'netPay',
            'totalDeductions',
            'leaveBalances',
            'attendanceRate',
            'presentDays',
            'payslips',
            'deductions',
            'startDate',
            'endDate',
            'chartData'
        ));
    }

    private function prepareChartData($employeeId)
    {
        $now = Carbon::now();

        // Attendance data
        $attendanceWeek = ['labels' => [], 'data' => []];
        $attendanceMonth = ['labels' => [], 'data' => []];
        $attendanceYear = ['labels' => [], 'data' => []];

        // Last 7 days (excluding weekends)
        $daysAdded = 0;
        $dayOffset = 0;
        while ($daysAdded < 7) {
            $date = $now->copy()->subDays($dayOffset);
            $dayOffset++;
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            $attendance = Attendance::where('employee_id', $employeeId)
                ->whereDate('date', $date)
                ->first();
            $attendanceWeek['labels'][] = $date->format('D');
            // Present if has AM In or PM In
            $isPresent = $attendance && ($attendance->am_in !== null || $attendance->pm_in !== null);
            $attendanceWeek['data'][] = $isPresent ? 100 : 0;
            $daysAdded++;
        }
        
        // Reverse to show chronological order
        $attendanceWeek['labels'] = array_reverse($attendanceWeek['labels']);
        $attendanceWeek['data'] = array_reverse($attendanceWeek['data']);

        // Last 4 weeks (grouped by week, excluding weekends)
        for ($i = 3; $i >= 0; $i--) {
            $weekEnd = $now->copy()->subWeeks($i)->endOfWeek();
            $weekStart = $weekEnd->copy()->startOfWeek();
            
            $total = Attendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->whereRaw('DAYOFWEEK(date) NOT IN (1, 7)') // Exclude Sunday (1) and Saturday (7)
                ->count();
            
            $present = Attendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->whereRaw('DAYOFWEEK(date) NOT IN (1, 7)')
                ->where(function($q) {
                    $q->whereNotNull('am_in')->orWhereNotNull('pm_in');
                })
                ->count();
            
            $attendanceMonth['labels'][] = 'Week ' . (4 - $i);
            $attendanceMonth['data'][] = $total > 0 ? round(($present / $total) * 100) : 0;
        }

        // Last 12 months (excluding weekends)
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $total = Attendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->whereRaw('DAYOFWEEK(date) NOT IN (1, 7)') // Exclude Sunday (1) and Saturday (7)
                ->count();
            
            $present = Attendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->whereRaw('DAYOFWEEK(date) NOT IN (1, 7)')
                ->where(function($q) {
                    $q->whereNotNull('am_in')->orWhereNotNull('pm_in');
                })
                ->count();
            
            $attendanceYear['labels'][] = $month->format('M');
            $attendanceYear['data'][] = $total > 0 ? round(($present / $total) * 100) : 0;
        }

        // Leave usage data
        $leaveWeek = ['labels' => [], 'data' => []];
        $leaveMonth = ['labels' => [], 'data' => []];
        $leaveYear = ['labels' => [], 'data' => []];

        // Last 7 days leave usage
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $leaveCount = \App\Models\LeaveApplication::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->sum('number_of_days');
            $leaveWeek['labels'][] = $date->format('D');
            $leaveWeek['data'][] = $leaveCount > 0 ? 1 : 0;
        }

        // Last 30 days cumulative leave
        $cumulative = 0;
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $leaveCount = \App\Models\LeaveApplication::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->exists();
            if ($leaveCount) $cumulative++;
            $leaveMonth['labels'][] = $date->format('M d');
            $leaveMonth['data'][] = $cumulative;
        }

        // Last 12 months leave usage
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $leaveDays = \App\Models\LeaveApplication::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where(function($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('start_date', [$monthStart, $monthEnd])
                      ->orWhereBetween('end_date', [$monthStart, $monthEnd]);
                })
                ->sum('number_of_days');
            
            $leaveYear['labels'][] = $month->format('M');
            $leaveYear['data'][] = $leaveDays;
        }

        // Salary data
        $salaryWeek = ['labels' => [], 'data' => []];
        $salaryMonth = ['labels' => [], 'data' => []];
        $salaryYear = ['labels' => [], 'data' => []];

        // Last 7 working days salary (excluding weekends)
        $daysAdded = 0;
        $dayOffset = 0;
        while ($daysAdded < 7) {
            $date = $now->copy()->subDays($dayOffset);
            $dayOffset++;
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            $dailySalary = DailySalaryComputation::where('employee_id', $employeeId)
                ->whereDate('work_date', $date)
                ->first();
            $salaryWeek['labels'][] = $date->format('D');
            $netPay = $dailySalary ? ($dailySalary->daily_basic_pay - $dailySalary->late_deduction - $dailySalary->undertime_deduction) : 0;
            $salaryWeek['data'][] = round($netPay, 2);
            $daysAdded++;
        }
        
        // Reverse to show chronological order
        $salaryWeek['labels'] = array_reverse($salaryWeek['labels']);
        $salaryWeek['data'] = array_reverse($salaryWeek['data']);

        // Last 4 weeks (grouped by week, excluding weekends)
        for ($i = 3; $i >= 0; $i--) {
            $weekEnd = $now->copy()->subWeeks($i)->endOfWeek();
            $weekStart = $weekEnd->copy()->startOfWeek();
            
            $weekSalary = DailySalaryComputation::where('employee_id', $employeeId)
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->whereRaw('DAYOFWEEK(work_date) NOT IN (1, 7)') // Exclude Sunday (1) and Saturday (7)
                ->get();
            
            $weekNetPay = $weekSalary->sum(function($s) {
                return $s->daily_basic_pay - $s->late_deduction - $s->undertime_deduction;
            });
            
            $salaryMonth['labels'][] = 'Week ' . (4 - $i);
            $salaryMonth['data'][] = round($weekNetPay, 2);
        }

        // Last 12 months salary (excluding weekends)
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthSalary = DailySalaryComputation::where('employee_id', $employeeId)
                ->whereBetween('work_date', [$monthStart, $monthEnd])
                ->whereRaw('DAYOFWEEK(work_date) NOT IN (1, 7)') // Exclude Sunday (1) and Saturday (7)
                ->get();
            
            $monthNetPay = $monthSalary->sum(function($s) {
                return $s->daily_basic_pay - $s->late_deduction - $s->undertime_deduction;
            });
            
            $salaryYear['labels'][] = $month->format('M');
            $salaryYear['data'][] = round($monthNetPay, 2);
        }

        return [
            'attendance' => [
                'week' => $attendanceWeek,
                'month' => $attendanceMonth,
                'year' => $attendanceYear,
            ],
            'salary' => [
                'week' => $salaryWeek,
                'month' => $salaryMonth,
                'year' => $salaryYear,
            ],
        ];
    }
}
