<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'am_in',
        'am_out',
        'pm_in',
        'pm_out',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
