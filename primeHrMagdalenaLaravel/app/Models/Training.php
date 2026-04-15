<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'employee_id', 'title', 'conducted_by',
        'date_from', 'date_to', 'hours'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
