<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelOrderCompanion extends Model
{
    protected $table = 'travel_order_companions';

    protected $fillable = [
        'travel_order_id',
        'employee_id',
        'status',
        'response_note',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function travelOrder()
    {
        return $this->belongsTo(TravelOrder::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
