<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAccrualRate extends Model
{
    protected $fillable = [
        'leave_code',
        'days_of_service_required',
        'credits_earned_per_period',
        'accrual_frequency',
        'effective_date',
        'end_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'days_of_service_required' => 'decimal:2',
        'credits_earned_per_period' => 'decimal:4',
        'effective_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_code', 'leave_code');
    }
}
