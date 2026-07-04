<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\DailySalaryComputation;
use App\Models\LeaveBalance;
use App\Models\Attendance;
use App\Models\EmployeeDeduction;
use App\Support\SqlCompat;
use Carbon\Carbon;

class MobileDashboardController extends Controller
{
    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 10;

    /**
     * Get main dashboard data (OPTIMIZED)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            // Cache key unique to this employee
            $cacheKey = "dashboard_data_{$employee->id}";

            // Try to get from cache, otherwise compute and cache
            $data = Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($employee) {
                return $this->computeDashboardData($employee);
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'cached_at' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Compute dashboard data (optimized with single queries)
     */
    private function computeDashboardData($employee)
    {
        // Eager load relationships in one query
        $employee->load([
            'employmentDetail.designationRelation:id,title',
            'employmentDetail.departmentRelation:id,name'
        ]);

        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->subDays(15);
        $endDate = $currentDate;

        // Single optimized query for salary data
        $salaryData = DailySalaryComputation::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->selectRaw('
                SUM(daily_basic_pay) as basic_pay,
                SUM(late_deduction + undertime_deduction) as total_deductions
            ')
            ->first();

        $basicPay = $salaryData->basic_pay ?? 0;
        $totalDeductions = $salaryData->total_deductions ?? 0;
        $netPay = $basicPay - $totalDeductions;

        // Single optimized query for leave balances
        $currentYear = $currentDate->year;
        $leaveData = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $currentYear)
            ->selectRaw('
                SUM(available_credits) as total_available,
                COUNT(*) as leave_types_count
            ')
            ->first();

        // Single optimized query for attendance
        $monthStart = $currentDate->copy()->startOfMonth();
        $monthEnd = $currentDate->copy()->endOfMonth();
        
        $attendanceData = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->selectRaw('
                COUNT(*) as total_days,
                SUM(CASE WHEN am_in IS NOT NULL OR pm_in IS NOT NULL THEN 1 ELSE 0 END) as present_days
            ')
            ->first();

        $totalDays = (int) ($attendanceData->total_days ?? 0);
        $presentDays = (int) ($attendanceData->present_days ?? 0);
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

        return [
            'employee' => [
                'id' => $employee->employee_id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'full_name' => $employee->first_name . ' ' . $employee->last_name,
                'initials' => strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)),
                'position' => $employee->employmentDetail->designationRelation->title ?? 'N/A',
                'department' => $employee->employmentDetail->departmentRelation->name ?? 'N/A',
                'employment_type' => $employee->employmentDetail->employment_type ?? 'Permanent',
                'status' => $employee->status ?? 'active',
            ],
            'salary' => [
                'basic_pay' => round($basicPay, 2),
                'net_pay' => round($netPay, 2),
                'total_deductions' => round($totalDeductions, 2),
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
                'period_label' => $startDate->format('M d') . '-' . $endDate->format('d, Y'),
            ],
            'leave' => [
                'total_available' => round((float) ($leaveData->total_available ?? 0), 1),
                'leave_types_count' => (int) ($leaveData->leave_types_count ?? 0),
            ],
            'attendance' => [
                'rate' => $attendanceRate,
                'present_days' => $presentDays,
                'total_days' => $totalDays,
            ],
        ];
    }

    /**
     * Get deductions list (OPTIMIZED)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deductions()
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            $cacheKey = "deductions_{$employee->id}";

            $deductions = Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($employee) {
                // Get latest salary computation once
                $latestComputation = DailySalaryComputation::where('employee_id', $employee->id)
                    ->select('monthly_rate', 'daily_rate')
                    ->orderBy('work_date', 'desc')
                    ->first();

                // Eager load deduction types
                return EmployeeDeduction::where('employee_id', $employee->id)
                    ->with('deductionType:id,name,code,category,computation_type,percentage_rate,base_salary_type,max_amount')
                    ->where('status', 'ACTIVE')
                    ->select('id', 'employee_id', 'deduction_type_id', 'installment_amount', 'amount', 'remaining_balance', 'total_amount', 'start_date', 'end_date', 'status')
                    ->orderBy('start_date', 'desc')
                    ->get()
                    ->map(function($deduction) use ($latestComputation) {
                        $deductionType = $deduction->deductionType;
                        $calculatedAmount = $this->calculateDeductionAmount($deduction, $deductionType, $latestComputation);
                        
                        return [
                            'id' => $deduction->id,
                            'deduction_type' => $deductionType->name ?? 'N/A',
                            'code' => $deductionType->code ?? null,
                            'category' => $deductionType->category ?? 'other',
                            'monthly_amount' => round($calculatedAmount * 2, 2),
                            'per_cutoff' => round($calculatedAmount, 2),
                            'remaining_balance' => round($deduction->remaining_balance ?? 0, 2),
                            'total_amount' => round($deduction->total_amount ?? 0, 2),
                            'start_date' => $deduction->start_date ? $deduction->start_date->format('Y-m-d') : null,
                            'end_date' => $deduction->end_date ? $deduction->end_date->format('Y-m-d') : null,
                            'status' => $deduction->status,
                        ];
                    })
                    ->values()
                    ->all();
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'deductions' => $deductions
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Deductions API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching deductions',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate deduction amount (extracted for reusability)
     */
    private function calculateDeductionAmount($deduction, $deductionType, $latestComputation)
    {
        if ($deduction->installment_amount > 0) {
            return $deduction->installment_amount;
        }
        
        if ($deduction->amount > 0) {
            return $deduction->amount;
        }
        
        if (!$deductionType) {
            return 0;
        }
        
        if (strtoupper($deductionType->computation_type) === 'FIXED' && $deductionType->percentage_rate > 0) {
            return $deductionType->percentage_rate / 2;
        }
        
        if (strtoupper($deductionType->computation_type) === 'PERCENTAGE' && $deductionType->percentage_rate > 0 && $latestComputation) {
            $monthlyRate = $latestComputation->monthly_rate;
            $dailyRate = $latestComputation->daily_rate;
            
            $calculatedAmount = 0;
            if (strtoupper($deductionType->base_salary_type) === 'MONTHLY') {
                $calculatedAmount = ($monthlyRate * $deductionType->percentage_rate) / 100 / 2;
            } else {
                $calculatedAmount = ($dailyRate * $deductionType->percentage_rate) / 100;
            }
            
            if ($deductionType->max_amount > 0 && $calculatedAmount > $deductionType->max_amount) {
                return $deductionType->max_amount / 2;
            }
            
            return $calculatedAmount;
        }
        
        return 0;
    }

    /**
     * Get leave balances (OPTIMIZED)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaveBalances()
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            $cacheKey = "leave_balances_{$employee->id}";

            $leaveBalances = Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($employee) {
                $currentYear = Carbon::now()->year;
                return LeaveBalance::where('employee_id', $employee->id)
                    ->where('year', $currentYear)
                    ->with('leaveType:leave_code,leave_name')
                    ->select('id', 'employee_id', 'leave_code', 'available_credits', 'used_credits', 'total_credits', 'year')
                    ->get()
                    ->map(function($balance) {
                        return [
                            'id' => $balance->id,
                            'leave_type' => $balance->leaveType->leave_name ?? 'Unknown',
                            'available' => round($balance->available_credits, 1),
                            'used' => round($balance->used_credits, 1),
                            'earned' => round($balance->total_credits, 1),
                            'year' => $balance->year,
                        ];
                    })
                    ->values()
                    ->all();
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'leave_balances' => $leaveBalances
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Leave Balances API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching leave balances',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get chart data for attendance and salary (OPTIMIZED)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function charts()
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            $cacheKey = "charts_{$employee->id}";

            $chartData = Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($employee) {
                return $this->prepareChartData($employee->id);
            });

            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);
        } catch (\Exception $e) {
            \Log::error('Charts API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching chart data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Clear cache for specific employee (useful after data updates)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            Cache::forget("dashboard_data_{$employee->id}");
            Cache::forget("deductions_{$employee->id}");
            Cache::forget("leave_balances_{$employee->id}");
            Cache::forget("charts_{$employee->id}");

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache'
            ], 500);
        }
    }

    /**
     * Prepare chart data (same logic as web dashboard, optimized)
     */
    private function prepareChartData($employeeId)
    {
        $now = Carbon::now();

        // Attendance data
        $attendanceWeek = $this->getAttendanceWeek($employeeId, $now);
        $attendanceMonth = $this->getAttendanceMonth($employeeId, $now);
        $attendanceYear = $this->getAttendanceYear($employeeId, $now);

        // Salary data
        $salaryWeek = $this->getSalaryWeek($employeeId, $now);
        $salaryMonth = $this->getSalaryMonth($employeeId, $now);
        $salaryYear = $this->getSalaryYear($employeeId, $now);

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

    // Optimized chart data methods (using raw queries for better performance)
    
    private function getAttendanceWeek($employeeId, $now)
    {
        $labels = [];
        $data = [];
        $dates = [];
        
        // Collect last 7 working days
        $daysAdded = 0;
        $dayOffset = 0;
        while ($daysAdded < 7) {
            $date = $now->copy()->subDays($dayOffset);
            $dayOffset++;
            
            if ($date->isWeekend()) continue;
            
            $dates[] = $date->format('Y-m-d');
            $labels[] = $date->format('D');
            $daysAdded++;
        }
        
        // Single query to get all attendance records
        $attendanceRecords = Attendance::where('employee_id', $employeeId)
            ->whereIn('date', $dates)
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();
        
        // Map presence
        foreach ($dates as $date) {
            $data[] = in_array($date, $attendanceRecords) ? 100 : 0;
        }
        
        return [
            'labels' => array_reverse($labels),
            'data' => array_reverse($data)
        ];
    }

    private function getAttendanceMonth($employeeId, $now)
    {
        $labels = [];
        $data = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $weekEnd = $now->copy()->subWeeks($i)->endOfWeek();
            $weekStart = $weekEnd->copy()->startOfWeek();
            
            $result = DB::table('attendance')
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->whereRaw(SqlCompat::isNotWeekend('date'))
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN am_in IS NOT NULL OR pm_in IS NOT NULL THEN 1 ELSE 0 END) as present
                ')
                ->first();
            
            $labels[] = 'Week ' . (4 - $i);
            $data[] = $result->total > 0 ? round(($result->present / $result->total) * 100) : 0;
        }
        
        return ['labels' => $labels, 'data' => $data];
    }

    private function getAttendanceYear($employeeId, $now)
    {
        $labels = [];
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $result = DB::table('attendance')
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->whereRaw(SqlCompat::isNotWeekend('date'))
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN am_in IS NOT NULL OR pm_in IS NOT NULL THEN 1 ELSE 0 END) as present
                ')
                ->first();
            
            $labels[] = $month->format('M');
            $data[] = $result->total > 0 ? round(($result->present / $result->total) * 100) : 0;
        }
        
        return ['labels' => $labels, 'data' => $data];
    }

    private function getSalaryWeek($employeeId, $now)
    {
        $labels = [];
        $data = [];
        $dates = [];
        
        $daysAdded = 0;
        $dayOffset = 0;
        while ($daysAdded < 7) {
            $date = $now->copy()->subDays($dayOffset);
            $dayOffset++;
            
            if ($date->isWeekend()) continue;
            
            $dates[] = $date->format('Y-m-d');
            $labels[] = $date->format('D');
            $daysAdded++;
        }
        
        $salaries = DailySalaryComputation::where('employee_id', $employeeId)
            ->whereIn('work_date', $dates)
            ->selectRaw('work_date, (daily_basic_pay - late_deduction - undertime_deduction) as net_pay')
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });
        
        foreach ($dates as $date) {
            $data[] = isset($salaries[$date]) ? round($salaries[$date]->net_pay, 2) : 0;
        }
        
        return [
            'labels' => array_reverse($labels),
            'data' => array_reverse($data)
        ];
    }

    private function getSalaryMonth($employeeId, $now)
    {
        $labels = [];
        $data = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $weekEnd = $now->copy()->subWeeks($i)->endOfWeek();
            $weekStart = $weekEnd->copy()->startOfWeek();
            
            $weekNetPay = DB::table('daily_salary_computations')
                ->where('employee_id', $employeeId)
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->whereRaw(SqlCompat::isNotWeekend('work_date'))
                ->selectRaw('SUM(daily_basic_pay - late_deduction - undertime_deduction) as net_pay')
                ->value('net_pay');
            
            $labels[] = 'Week ' . (4 - $i);
            $data[] = round($weekNetPay ?? 0, 2);
        }
        
        return ['labels' => $labels, 'data' => $data];
    }

    private function getSalaryYear($employeeId, $now)
    {
        $labels = [];
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthNetPay = DB::table('daily_salary_computations')
                ->where('employee_id', $employeeId)
                ->whereBetween('work_date', [$monthStart, $monthEnd])
                ->whereRaw(SqlCompat::isNotWeekend('work_date'))
                ->selectRaw('SUM(daily_basic_pay - late_deduction - undertime_deduction) as net_pay')
                ->value('net_pay');
            
            $labels[] = $month->format('M');
            $data[] = round($monthNetPay ?? 0, 2);
        }
        
        return ['labels' => $labels, 'data' => $data];
    }
}
