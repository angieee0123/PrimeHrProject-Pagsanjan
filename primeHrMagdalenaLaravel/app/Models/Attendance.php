<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Attendance extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    protected $table = 'attendance';

    // `attendance_type` and `remarks` were missing here while both
    // LeaveApplicationObserver and TravelOrderObserver passed them to
    // Attendance::create(), so mass assignment silently discarded them and
    // every approved leave or travel day was stored as REGULAR. That is the
    // column the dashboard, reports, and the AI Assistant all count leave and
    // absence from, and it is what the attendance scanner checks before
    // punching over a day another workflow owns.
    protected $fillable = [
        'employee_id', 'date', 'am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out', 'accredited_hours', 'total_hours',
        'attendance_type', 'remarks'
    ];

    protected $casts = [
        'date' => 'date',
        'am_in' => 'string',
        'am_out' => 'string',
        'pm_in' => 'string',
        'pm_out' => 'string',
        'ot_in' => 'string',
        'ot_out' => 'string',
        'accredited_hours' => 'integer',
        'total_hours' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function accreditedHoursLogs()
    {
        return $this->hasMany(AccreditedHoursLog::class);
    }
}
