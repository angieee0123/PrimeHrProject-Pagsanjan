<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentDetail extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'employee_id', 'position', 'department', 'employment_status',
        'appointment_date', 'salary_grade', 'step_increment'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }
}
