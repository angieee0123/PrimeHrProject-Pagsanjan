<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_types_config';
    protected $primaryKey = 'leave_code';
    public $incrementing = false;
    protected $keyType = 'string';

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
}
