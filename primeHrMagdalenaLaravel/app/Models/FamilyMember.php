<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FamilyMember extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'employee_id', 'name', 'relationship',
        'birthdate', 'occupation'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
