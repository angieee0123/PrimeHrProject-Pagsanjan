<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $fillable = [
        'employee_id', 'level', 'school_name',
        'degree', 'year_graduated', 'honors'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
