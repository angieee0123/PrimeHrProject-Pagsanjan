<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveApplication extends Model
{
    protected $fillable = [
        'application_number',
        'employee_id',
        'leave_code',
        'start_date',
        'end_date',
        'number_of_days',
        'reason',
        'status',
        'attachment_path',
        'filed_by',
        'approved_by',
        'approved_at',
        'approver_remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'number_of_days' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_code', 'leave_code');
    }

    public function filedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LeaveTransaction::class, 'reference_id')->where('reference_type', 'leave_application');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($leaveApplication) {
            if (empty($leaveApplication->application_number)) {
                $leaveApplication->application_number = self::generateApplicationNumber();
            }
        });
    }

    public static function generateApplicationNumber(): string
    {
        $year = date('Y');
        $lastApplication = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastApplication ? intval(substr($lastApplication->application_number, -4)) + 1 : 1;

        return 'LA-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
