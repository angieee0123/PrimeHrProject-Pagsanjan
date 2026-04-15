<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'house_no', 'street',
        'barangay', 'city', 'province', 'zip_code'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
