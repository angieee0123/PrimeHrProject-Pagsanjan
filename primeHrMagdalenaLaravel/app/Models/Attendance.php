<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public $timestamps = false;

    protected $table = 'attendance';

    protected $fillable = [
        'employee_id', 'date', 'am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out', 'accredited_hours'
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
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
