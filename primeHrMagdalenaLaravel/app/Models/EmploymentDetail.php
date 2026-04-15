<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentDetail extends Model
{
    protected $fillable = [
        'employee_id', 'position', 'department_id', 'employment_status',
        'appointment_date', 'salary_grade', 'step_increment', 'account_status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
