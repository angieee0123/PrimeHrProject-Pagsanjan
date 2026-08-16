<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class WorkExperience extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'employee_id', 'company_name', 'position',
        'from_date', 'to_date', 'salary'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
