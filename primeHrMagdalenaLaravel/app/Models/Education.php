<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Education extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'employee_id', 'level', 'school_name',
        'degree', 'year_graduated', 'honors'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
