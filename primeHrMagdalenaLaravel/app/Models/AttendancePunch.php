<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePunch extends Model
{
    protected $table = 'attendance_punches';

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'date',
        'slot',
        'punched_at',
        'source',
        'device_label',
        'recorded_by',
        'previous_value',
    ];

    protected $casts = [
        'date' => 'date',
        'punched_at' => 'datetime',
    ];

    /** Slots a punch may fill, in the order a working day fills them. */
    public const SLOTS = ['am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out'];

    /** Capture devices. `qr_scan` stands in for `biometric` until the reader arrives. */
    public const SOURCES = ['qr_scan', 'biometric', 'manual'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Human label for a slot, e.g. `am_in` → `AM In`. */
    public static function slotLabel(string $slot): string
    {
        return match ($slot) {
            'am_in'  => 'AM In',
            'am_out' => 'AM Out',
            'pm_in'  => 'PM In',
            'pm_out' => 'PM Out',
            'ot_in'  => 'OT In',
            'ot_out' => 'OT Out',
            default  => $slot,
        };
    }
}
