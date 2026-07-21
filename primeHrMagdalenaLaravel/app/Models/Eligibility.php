<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eligibility extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'rating', 'exam_date',
        'exam_place', 'license_no', 'validity_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
