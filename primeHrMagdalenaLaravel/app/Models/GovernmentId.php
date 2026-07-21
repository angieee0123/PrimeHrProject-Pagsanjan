<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentId extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'employee_id', 'gsis_no', 'philhealth_no',
        'pagibig_no', 'tin_no', 'license_no'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
