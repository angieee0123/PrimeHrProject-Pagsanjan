<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeductionType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'computation_type',
        'percentage_rate',
        'base_salary_type',
        'max_amount',
        'is_active',
    ];

    protected $casts = [
        'percentage_rate' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(DeductionSchedule::class);
    }

    public function employeeDeductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    public function loanTypes(): HasMany
    {
        return $this->hasMany(LoanType::class);
    }
}
