<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySalaryComputation extends Model
{
    protected $fillable = [
        'employee_id',
        'accredited_hours_log_id',
        'work_date',
        'monthly_rate',
        'daily_rate',
        'hourly_rate',
        'daily_basic_pay',
        'ot_pay',
        'late_deduction',
        'undertime_deduction',
        'daily_gross_pay',
        'is_holiday',
        'is_rest_day',
        'holiday_type',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'monthly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'daily_basic_pay' => 'decimal:2',
        'ot_pay' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'undertime_deduction' => 'decimal:2',
        'daily_gross_pay' => 'decimal:2',
        'is_holiday' => 'boolean',
        'is_rest_day' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function accreditedHoursLog(): BelongsTo
    {
        return $this->belongsTo(AccreditedHoursLog::class);
    }
    
    // Accessor to get attendance data from accredited_hours_log
    public function getAttendanceAttribute()
    {
        return $this->accreditedHoursLog->attendance ?? null;
    }
    
    // Accessor methods to get time data from accredited_hours_log
    public function getAccreditedMinutesAttribute()
    {
        return $this->accreditedHoursLog->total_accredited_minutes ?? 0;
    }
    
    public function getActualMinutesAttribute()
    {
        return $this->accreditedHoursLog->total_actual_minutes ?? 0;
    }
    
    public function getLateMinutesAttribute()
    {
        return $this->accreditedHoursLog->late_minutes ?? 0;
    }
    
    public function getUndertimeMinutesAttribute()
    {
        return $this->accreditedHoursLog->undertime_minutes ?? 0;
    }
    
    public function getOtMinutesAttribute()
    {
        return $this->accreditedHoursLog->ot_minutes ?? 0;
    }
    
    public function getIsPresentAttribute()
    {
        return ($this->accreditedHoursLog->total_accredited_minutes ?? 0) > 0;
    }
    
    public function getIsAbsentAttribute()
    {
        return ($this->accreditedHoursLog->total_accredited_minutes ?? 0) == 0;
    }

    /**
     * Compute daily salary from accredited hours log
     */
    public static function computeFromAccreditedLog(AccreditedHoursLog $log): self
    {
        $employee = $log->employee;
        $attendance = $log->attendance;
        
        // Get monthly rate from designation with fallback to direct DB query
        $monthlyRate = 0;
        
        try {
            // Try relationship first (note: it's designationRelation, not designation)
            if ($employee->employmentDetail && $employee->employmentDetail->designationRelation) {
                $monthlyRate = $employee->employmentDetail->designationRelation->monthly_rate ?? 0;
            }
            
            // Fallback to direct DB query if relationship fails
            if ($monthlyRate == 0) {
                $monthlyRate = \DB::table('employees')
                    ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                    ->join('designations', 'employment_details.designation_id', '=', 'designations.id')
                    ->where('employees.id', $log->employee_id)
                    ->value('designations.monthly_rate') ?? 0;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get monthly rate for employee ' . $log->employee_id . ': ' . $e->getMessage());
            $monthlyRate = 0;
        }
        
        // Calculate rates (22 working days, 8 hours per day)
        $dailyRate = $monthlyRate > 0 ? $monthlyRate / 22 : 0;
        $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;
        
        // Calculate pay components using data from accredited_hours_log
        $dailyBasicPay = $dailyRate > 0 ? ($log->total_accredited_minutes / 480) * $dailyRate : 0;
        $otPay = $hourlyRate > 0 ? ($log->ot_minutes / 60) * $hourlyRate * 1.25 : 0; // 1.25x for OT
        $lateDeduction = $hourlyRate > 0 ? ($log->late_minutes / 60) * $hourlyRate : 0;
        $undertimeDeduction = $hourlyRate > 0 ? ($log->undertime_minutes / 60) * $hourlyRate : 0;
        $dailyGrossPay = $dailyBasicPay + $otPay - $lateDeduction - $undertimeDeduction;
        
        return self::updateOrCreate(
            [
                'accredited_hours_log_id' => $log->id,
            ],
            [
                'employee_id' => $log->employee_id,
                'work_date' => $attendance->date,
                'monthly_rate' => round($monthlyRate, 2),
                'daily_rate' => round($dailyRate, 2),
                'hourly_rate' => round($hourlyRate, 2),
                'daily_basic_pay' => round($dailyBasicPay, 2),
                'ot_pay' => round($otPay, 2),
                'late_deduction' => round($lateDeduction, 2),
                'undertime_deduction' => round($undertimeDeduction, 2),
                'daily_gross_pay' => round($dailyGrossPay, 2),
            ]
        );
    }
}
