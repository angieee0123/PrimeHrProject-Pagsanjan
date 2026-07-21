<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = ['title', 'department_id', 'salary_grade', 'monthly_rate', 'employment_type', 'description'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
