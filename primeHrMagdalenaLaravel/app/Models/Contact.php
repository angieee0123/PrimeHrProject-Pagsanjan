<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Contact extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    protected $fillable = [
        'employee_id', 'type', 'number', 'contact_person'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
