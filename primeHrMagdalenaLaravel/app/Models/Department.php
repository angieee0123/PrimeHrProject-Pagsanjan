<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Department extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    // No personnel_count: headcount is derived from employmentDetails, never
    // stored. DepartmentController::withHeadcount() aliases the count back to
    // that name for the views.
    protected $fillable = ['code', 'name', 'head', 'status', 'description'];

    public function employmentDetails()
    {
        return $this->hasMany(EmploymentDetail::class, 'department_id');
    }

    /** The positions registered under this office (the Designations tab). */
    public function designations()
    {
        return $this->hasMany(Designation::class, 'department_id');
    }

    public function employees()
    {
        return $this->hasManyThrough(Employee::class, EmploymentDetail::class, 'department_id', 'id', 'id', 'employee_id');
    }
}
