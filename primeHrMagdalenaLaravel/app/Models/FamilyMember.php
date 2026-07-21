<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    protected $fillable = [
        'employee_id', 'name', 'relationship',
        'birthdate', 'occupation'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
