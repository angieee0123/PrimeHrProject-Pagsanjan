<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryComputation extends Model
{
    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'pay_date',
        'payroll_type',
        'monthly_rate',
        'daily_rate',
        'hourly_rate',
        'total_days_present',
        'total_days_absent',
        'total_hours_worked',
        'total_accredited_hours',
        'total_late_minutes',
        'total_undertime_minutes',
        'total_ot_minutes',
        'basic_pay',
        'ot_pay',
        'late_deduction',
        'undertime_deduction',
        'other_deductions',
        'deduction_breakdown',
        'gross_pay',
        'net_pay',
        'status',
        'computed_by',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
        'monthly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_hours_worked' => 'decimal:2',
        'total_accredited_hours' => 'decimal:2',
        'basic_pay' => 'decimal:2',
        'ot_pay' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'undertime_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'deduction_breakdown' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function computedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'computed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Compute period salary from daily computations
     */
    public static function computePeriod(
        int $employeeId,
        string $periodStart,
        string $periodEnd,
        string $payrollType = 'monthly',
        ?int $computedBy = null
    ): self {
        $employee = Employee::findOrFail($employeeId);
        
        // Get monthly rate
        $monthlyRate = $employee->employmentDetail->designationRelation->monthly_rate ?? 0;
        $dailyRate = $monthlyRate / 22;
        $hourlyRate = $dailyRate / 8;
        
        // Aggregate daily computations with their accredited_hours_log data
        $dailyComputations = DailySalaryComputation::with('accreditedHoursLog')
            ->where('employee_id', $employeeId)
            ->whereBetween('work_date', [$periodStart, $periodEnd])
            ->get();
        
        $totalDaysPresent = $dailyComputations->filter(fn($d) => $d->is_present)->count();
        $totalDaysAbsent = $dailyComputations->filter(fn($d) => $d->is_absent)->count();
        $totalAccreditedHours = $dailyComputations->sum('accredited_minutes') / 60;
        $totalLateMinutes = $dailyComputations->sum('late_minutes');
        $totalUndertimeMinutes = $dailyComputations->sum('undertime_minutes');
        $totalOtMinutes = $dailyComputations->sum('ot_minutes');
        $basicPay = $dailyComputations->sum('daily_basic_pay');
        $otPay = $dailyComputations->sum('ot_pay');
        $lateDeduction = $dailyComputations->sum('late_deduction');
        $undertimeDeduction = $dailyComputations->sum('undertime_deduction');
        $grossPay = $dailyComputations->sum('daily_gross_pay');
        $netPay = $grossPay; // Can add other deductions here
        
        return self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            [
                'payroll_type' => $payrollType,
                'monthly_rate' => $monthlyRate,
                'daily_rate' => $dailyRate,
                'hourly_rate' => $hourlyRate,
                'total_days_present' => $totalDaysPresent,
                'total_days_absent' => $totalDaysAbsent,
                'total_accredited_hours' => $totalAccreditedHours,
                'total_late_minutes' => $totalLateMinutes,
                'total_undertime_minutes' => $totalUndertimeMinutes,
                'total_ot_minutes' => $totalOtMinutes,
                'basic_pay' => $basicPay,
                'ot_pay' => $otPay,
                'late_deduction' => $lateDeduction,
                'undertime_deduction' => $undertimeDeduction,
                'gross_pay' => $grossPay,
                'net_pay' => $netPay,
                'status' => 'pending',
                'computed_by' => $computedBy,
            ]
        );
    }
}
