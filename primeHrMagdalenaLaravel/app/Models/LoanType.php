<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'deduction_type_id',
        'max_loanable_amount',
        'interest_rate',
        'max_terms_months',
        'is_active',
    ];

    protected $casts = [
        'max_loanable_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionType::class);
    }
}
