<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employee_id', 'type', 'number', 'contact_person'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
