<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Eligibility extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'employee_id', 'type', 'rating', 'exam_date',
        'exam_place', 'license_no', 'validity_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
