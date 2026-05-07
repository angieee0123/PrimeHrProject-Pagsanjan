<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'am_grace_applied' => 'boolean',
        'pm_grace_applied' => 'boolean',
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
}
