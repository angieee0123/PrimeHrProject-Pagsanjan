<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_types_config';

    protected $fillable = [
        'leave_code',
        'leave_name',
        'is_accrued',
        'annual_limit',
        'is_cumulative',
        'requires_6_months',
        'is_monetizable',
        'requires_attachment',
        'attachment_info',
        'document_path',
        'is_active',
    ];

    protected $casts = [
        'is_accrued' => 'boolean',
        'is_cumulative' => 'boolean',
        'requires_6_months' => 'boolean',
        'is_monetizable' => 'boolean',
        'requires_attachment' => 'boolean',
        'is_active' => 'boolean',
        'annual_limit' => 'decimal:2',
    ];

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'leave_code', 'leave_code');
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class, 'leave_code', 'leave_code');
    }

    public function leaveTransactions()
    {
        return $this->hasMany(LeaveTransaction::class, 'leave_code', 'leave_code');
    }

    public function accrualRates()
    {
        return $this->hasMany(LeaveAccrualRate::class, 'leave_type_id', 'id');
    }
}
