<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Designation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['title', 'department_id', 'salary_grade', 'monthly_rate', 'employment_type', 'description'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /** Who currently holds this position — counted by the CSV export. */
    public function employmentDetails()
    {
        return $this->hasMany(EmploymentDetail::class, 'designation_id');
    }
}
