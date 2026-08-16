<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Address extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    protected $fillable = [
        'employee_id', 'type', 'house_no', 'street',
        'barangay', 'city', 'province', 'zip_code'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
