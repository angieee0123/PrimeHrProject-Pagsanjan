<?php

use App\Models\Employee;
use App\Models\DailySalaryComputation;
use App\Models\SalaryComputation;
use Illuminate\Support\Facades\DB;

// Test payroll generation for April 1-16, 2026
$startDate = '2026-04-01';
$endDate = '2026-04-16';
$payDate = '2026-05-18';
$payrollType = 'regular';

echo "Testing Payroll Generation\n";
echo "Period: $startDate to $endDate\n";
echo "Pay Date: $payDate\n";
echo "Payroll Type: $payrollType\n\n";

// Get employees with daily computations
$employeeIds = DailySalaryComputation::whereBetween('work_date', [$startDate, $endDate])
    ->distinct()
    ->pluck('employee_id');

echo "Employees with computations: " . $employeeIds->count() . "\n";
echo "Employee IDs: " . $employeeIds->implode(', ') . "\n\n";

foreach ($employeeIds as $employeeId) {
    $employee = Employee::with([
        'employmentDetail.departmentRelation',
        'employmentDetail.designationRelation',
        'deductions' => function($q) use ($endDate) {
            $q->where('status', 'ACTIVE')
              ->where('start_date', '<=', $endDate)
              ->where(function($query) use ($endDate) {
                  $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $endDate);
              })
              ->with('deductionType.schedules');
        }
    ])->find($employeeId);
    
    if (!$employee) {
        echo "Employee $employeeId not found\n";
        continue;
    }
    
    echo "Processing: {$employee->first_name} {$employee->last_name} (ID: $employeeId)\n";
    
    // Get computations
    $computations = DailySalaryComputation::where('employee_id', $employeeId)
        ->whereBetween('work_date', [$startDate, $endDate])
        ->get();
    
    echo "  Daily computations: {$computations->count()}\n";
    
    $monthlyRate = $employee->employmentDetail?->designationRelation?->monthly_rate ?? 0;
    $dailyRate = $monthlyRate > 0 ? $monthlyRate / 22 : 0;
    $basicPay = $computations->sum('daily_basic_pay');
    $otPay = $computations->sum('ot_pay');
    $lateDeduction = $computations->sum('late_deduction');
    $undertimeDeduction = $computations->sum('undertime_deduction');
    
    echo "  Monthly Rate: ₱" . number_format($monthlyRate, 2) . "\n";
    echo "  Daily Rate: ₱" . number_format($dailyRate, 2) . "\n";
    echo "  Basic Pay: ₱" . number_format($basicPay, 2) . "\n";
    echo "  OT Pay: ₱" . number_format($otPay, 2) . "\n";
    
    // Calculate deductions
    $deductionBreakdown = [];
    $totalOtherDeductions = 0;
    $startDay = (int) date('d', strtotime($startDate));
    $isCutoff1st = $startDay <= 15;
    
    foreach ($employee->deductions as $deduction) {
        $deductionType = $deduction->deductionType;
        
        if (!$deductionType->deducted_from_employee) {
            continue;
        }
        
        $cutoffSchedule = $deduction->custom_cutoff_schedule 
            ?? ($deductionType->schedules->first()->cutoff_schedule ?? 'BOTH_SPLIT');
        
        $deductionAmount = 0;
        if ($deductionType->category === 'MANDATORY') {
            if ($deductionType->computation_type === 'PERCENTAGE') {
                $baseAmount = $deductionType->base_salary_type === 'BASIC' ? $basicPay 
                    : ($deductionType->base_salary_type === 'GROSS' ? $basicPay + $otPay 
                    : ($deductionType->base_salary_type === 'MONTHLY' ? $monthlyRate 
                    : $basicPay));
                $deductionAmount = $baseAmount * ($deductionType->percentage_rate / 100);
            } elseif ($deductionType->computation_type === 'FIXED') {
                $deductionAmount = $deductionType->percentage_rate ?? $deduction->amount ?? 0;
            } else {
                $deductionAmount = $deduction->amount ?? 0;
            }
        } elseif ($deductionType->category === 'LOAN') {
            $deductionAmount = $deduction->installment_amount ?? 0;
        }
        
        // Apply cutoff schedule
        if ($cutoffSchedule === '1ST_ONLY') {
            $deductionAmount = $isCutoff1st ? $deductionAmount : 0;
        } elseif ($cutoffSchedule === '2ND_ONLY') {
            $deductionAmount = $isCutoff1st ? 0 : $deductionAmount;
        } elseif ($cutoffSchedule === 'BOTH_FULL') {
            // Keep full amount
        } else { // BOTH_SPLIT
            $deductionAmount = $deductionAmount / 2;
        }
        
        if ($deductionAmount > 0) {
            $deductionBreakdown[$deductionType->code] = [
                'name' => $deductionType->name,
                'amount' => round($deductionAmount, 2),
                'category' => $deductionType->category,
            ];
            echo "  {$deductionType->name}: ₱" . number_format($deductionAmount, 2) . "\n";
        }
        
        $totalOtherDeductions += $deductionAmount;
    }
    
    $grossPay = $basicPay + $otPay;
    $netPay = $grossPay - $lateDeduction - $undertimeDeduction - $totalOtherDeductions;
    
    echo "  Total Deductions: ₱" . number_format($totalOtherDeductions, 2) . "\n";
    echo "  Gross Pay: ₱" . number_format($grossPay, 2) . "\n";
    echo "  Net Pay: ₱" . number_format($netPay, 2) . "\n";
    echo "  Deduction Breakdown: " . json_encode($deductionBreakdown) . "\n\n";
    
    // Try to save
    try {
        $periodComputation = SalaryComputation::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'period_start' => $startDate,
                'period_end' => $endDate,
            ],
            [
                'pay_date' => $payDate,
                'payroll_type' => $payrollType,
                'monthly_rate' => $monthlyRate,
                'daily_rate' => $dailyRate,
                'hourly_rate' => $dailyRate / 8,
                'total_days_present' => $computations->count(),
                'total_days_absent' => 0,
                'total_hours_worked' => $computations->count() * 8,
                'total_accredited_hours' => $computations->count() * 8,
                'total_late_minutes' => 0,
                'total_undertime_minutes' => 0,
                'total_ot_minutes' => 0,
                'basic_pay' => $basicPay,
                'ot_pay' => $otPay,
                'late_deduction' => $lateDeduction,
                'undertime_deduction' => $undertimeDeduction,
                'other_deductions' => $totalOtherDeductions,
                'deduction_breakdown' => json_encode($deductionBreakdown),
                'gross_pay' => $grossPay,
                'net_pay' => $netPay,
                'status' => 'approved',
                'computed_by' => 1,
            ]
        );
        
        echo "  ✅ SAVED! Record ID: {$periodComputation->id}\n";
        echo "  Was recently created: " . ($periodComputation->wasRecentlyCreated ? 'YES' : 'NO (updated existing)') . "\n\n";
        
    } catch (\Exception $e) {
        echo "  ❌ ERROR: " . $e->getMessage() . "\n\n";
    }
}

// Check final count
$totalRecords = SalaryComputation::whereBetween('period_start', [$startDate, $endDate])->count();
echo "\nTotal salary computation records in database: $totalRecords\n";
