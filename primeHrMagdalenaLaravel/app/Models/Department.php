<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['code', 'name', 'head', 'personnel_count', 'status', 'description'];

    public function employmentDetails()
    {
        return $this->hasMany(EmploymentDetail::class, 'department_id');
    }

    public function employees()
    {
        return $this->hasManyThrough(Employee::class, EmploymentDetail::class, 'department_id', 'id', 'id', 'employee_id');
    }
}
