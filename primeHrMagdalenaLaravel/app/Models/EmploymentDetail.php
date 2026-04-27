<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentDetail extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'employee_id', 'designation_id', 'department_id', 'employment_status',
        'appointment_date', 'salary_grade', 'step_increment'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designationRelation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
}
