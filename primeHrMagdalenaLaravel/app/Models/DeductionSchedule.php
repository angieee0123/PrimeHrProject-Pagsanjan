<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeductionSchedule extends Model
{
    protected $fillable = [
        'deduction_type_id',
        'cutoff_schedule',
        'priority_order',
        'is_active',
        'effective_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];

    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionType::class);
    }
}
