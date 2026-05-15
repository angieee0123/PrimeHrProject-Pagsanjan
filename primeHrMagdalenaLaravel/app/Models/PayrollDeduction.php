<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDeduction extends Model
{
    protected $table = 'deduction_transactions';

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'employee_deduction_id',
        'deduction_type_id',
        'cutoff_period',
        'amount_deducted',
        'computation_details',
        'deduction_date',
    ];

    protected $casts = [
        'amount_deducted' => 'decimal:2',
        'computation_details' => 'array',
        'deduction_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeDeduction(): BelongsTo
    {
        return $this->belongsTo(EmployeeDeduction::class);
    }

    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionType::class);
    }
}
