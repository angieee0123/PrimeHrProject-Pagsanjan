<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalRequirement extends Model
{
    protected $fillable = [
        'employee_id', 'saln_submitted', 'oath_of_office', 'assumption_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
