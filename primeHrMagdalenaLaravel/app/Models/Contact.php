<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'number', 'contact_person'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
