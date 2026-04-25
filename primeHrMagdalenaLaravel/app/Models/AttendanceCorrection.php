<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    protected $fillable = [
        'attendance_id',
        'employee_id',
        'date',
        'old_am_in',
        'old_am_out',
        'old_pm_in',
        'old_pm_out',
        'old_ot_in',
        'old_ot_out',
        'new_am_in',
        'new_am_out',
        'new_pm_in',
        'new_pm_out',
        'new_ot_in',
        'new_ot_out',
        'reason',
        'attachments',
        'corrected_by',
    ];

    protected $casts = [
        'date' => 'date',
        'attachments' => 'array',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function correctedBy()
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
