<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'destination',
        'purpose',
        'travel_date',
        'return_date',
        'duration',
        'transportation_mode',
        'estimated_budget',
        'attachment',
        'status',
        'remarks',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'return_date' => 'date',
        'approved_at' => 'datetime',
        'estimated_budget' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
