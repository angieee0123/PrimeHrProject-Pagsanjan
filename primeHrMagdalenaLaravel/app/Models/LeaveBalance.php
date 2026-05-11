<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_code',
        'year',
        'total_credits',
        'used_credits',
        'pending_credits',
        'available_credits',
        'carried_over',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_credits' => 'decimal:2',
        'used_credits' => 'decimal:2',
        'pending_credits' => 'decimal:2',
        'available_credits' => 'decimal:2',
        'carried_over' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_code', 'leave_code');
    }
}
