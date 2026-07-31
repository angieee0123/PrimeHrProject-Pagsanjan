<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentId extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'employee_id', 'gsis_no', 'gsis_file_path',
        'philhealth_no', 'philhealth_file_path',
        'pagibig_no', 'pagibig_file_path',
        'tin_no', 'tin_file_path',
        'license_no', 'license_file_path',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
