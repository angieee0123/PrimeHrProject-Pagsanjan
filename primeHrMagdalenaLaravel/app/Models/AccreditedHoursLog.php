<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\CscTimeConversionService;

class AccreditedHoursLog extends Model
{
    protected $table = 'accredited_hours_log';

    protected $fillable = [
        'attendance_id',
        'employee_id',
        'schedule_id',
        'am_accredited_minutes',
        'pm_accredited_minutes',
        'ot_minutes',
        'late_minutes',
        'undertime_minutes',
        'total_accredited_minutes',
        'total_actual_minutes',
        'am_grace_applied',
        'pm_grace_applied',
        'computation_notes',
        'late_deducted_from_leave',
        'late_deduction_leave_type',
        'lwop_minutes',
        'requires_salary_deduction',
    ];

    protected $casts = [
        'am_grace_applied' => 'boolean',
        'pm_grace_applied' => 'boolean',
        'late_deducted_from_leave' => 'boolean',
        'requires_salary_deduction' => 'boolean',
    ];
    
    // Eager load relationships to prevent N+1 queries
    protected $with = ['employee.employmentDetail.designationRelation', 'attendance'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get total accredited hours (CSC standard: minutes / 60)
     * 
     * @return float Hours with 4 decimal precision
     */
    public function getTotalAccreditedHoursAttribute(): float
    {
        return CscTimeConversionService::convertMinutesToHours($this->total_accredited_minutes);
    }

    /**
     * Get total accredited days (CSC standard: minutes / 480)
     * 
     * @return float Days with 6 decimal precision
     */
    public function getTotalAccreditedDaysAttribute(): float
    {
        return CscTimeConversionService::convertMinutesToDays($this->total_accredited_minutes);
    }

    /**
     * Get late hours (CSC standard: minutes / 60)
     * 
     * @return float Hours with 4 decimal precision
     */
    public function getLateHoursAttribute(): float
    {
        return CscTimeConversionService::convertMinutesToHours($this->late_minutes);
    }

    /**
     * Get late days (CSC standard: minutes / 480)
     * 
     * @return float Days with 6 decimal precision
     */
    public function getLateDaysAttribute(): float
    {
        return CscTimeConversionService::convertMinutesToDays($this->late_minutes);
    }

    /**
     * Get undertime hours (CSC standard: minutes / 60)
     * 
     * @return float Hours with 4 decimal precision
     */
    public function getUndertimeHoursAttribute(): float
    {
        return CscTimeConversionService::convertMinutesToHours($this->undertime_minutes);
    }

    /**
     * Get undertime days (CSC standard: minutes / 480)
     * 
     * @return float Days with 6 decimal precision
     */
    public function getUndertimeDaysAttribute(): float
    {
        return CscTimeConversionService::convertMinutesToDays($this->undertime_minutes);
    }

    /**
     * Get leave deduction for late/undertime (CSC standard)
     * 
     * @return array Deduction breakdown
     */
    public function getLeaveDeductionAttribute(): array
    {
        return CscTimeConversionService::calculateLeaveDeduction(
            $this->late_minutes,
            $this->undertime_minutes
        );
    }

    /**
     * Get LWOP hours (Leave Without Pay - for salary deduction)
     * 
     * @return float Hours with 4 decimal precision
     */
    public function getLwopHoursAttribute(): float
    {
        return CscTimeConversionService::convertMinutesToHours($this->lwop_minutes ?? 0);
    }

    /**
     * Get LWOP days (Leave Without Pay - for salary deduction)
     * 
     * @return float Days with 6 decimal precision
     */
    public function getLwopDaysAttribute(): float
    {
        return CscTimeConversionService::convertMinutesToDays($this->lwop_minutes ?? 0);
    }
}
