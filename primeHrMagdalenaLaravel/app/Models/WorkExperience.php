<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    protected $fillable = [
        'employee_id', 'company_name', 'position',
        'from_date', 'to_date', 'salary'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
