<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'leave_requests',
        'training_submissions',
        'travel_orders',
        'employee_requests',
    ];

    protected function casts(): array
    {
        return [
            'leave_requests' => 'boolean',
            'training_submissions' => 'boolean',
            'travel_orders' => 'boolean',
            'employee_requests' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
