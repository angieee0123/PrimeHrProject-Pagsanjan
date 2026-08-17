<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EmploymentDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    /**
     * The employment types an employee may hold. Same vocabulary the
     * registration and designation forms validate against.
     */
    public const EMPLOYMENT_TYPES = [
        'Permanent',
        'Temporary',
        'Coterminous',
        'Casual',
        'Contractual',
        'Job Order',
    ];

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
