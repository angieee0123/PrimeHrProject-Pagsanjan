<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelOrderHistory extends Model
{
    protected $table = 'travel_order_histories';

    protected $fillable = [
        'travel_order_id',
        'action',
        'remarks',
        'performed_by',
    ];

    public function travelOrder()
    {
        return $this->belongsTo(TravelOrder::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
