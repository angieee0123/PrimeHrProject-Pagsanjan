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
        'attendance_date',
        'scheduled_am_in',
        'scheduled_am_out',
        'scheduled_pm_in',
        'scheduled_pm_out',
        'actual_am_in',
        'actual_am_out',
        'actual_pm_in',
        'actual_pm_out',
        'actual_ot_in',
        'actual_ot_out',
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
        'attendance_date' => 'date',
        'am_grace_applied' => 'boolean',
        'pm_grace_applied' => 'boolean',
    ];

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
