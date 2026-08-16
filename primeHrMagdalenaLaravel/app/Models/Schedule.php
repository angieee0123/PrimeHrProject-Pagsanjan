<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Schedule extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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
