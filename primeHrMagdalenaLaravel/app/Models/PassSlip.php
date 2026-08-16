<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class PassSlip extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'pass_slips';

    /**
     * Official wording from the Pass Slip form's purpose checkboxes (item 3).
     */
    public const PURPOSE_LABELS = [
        'coordinate_with' => 'to coordinate with',
        'meeting_conference' => 'to attend meeting/conference',
        'secure_documents' => 'to secure documents & others',
        'follow_up' => 'to follow up',
        'personal_matter' => 'to attend personal matter',
    ];

    protected $fillable = [
        'slip_number',
        'employee_id',
        'type',
        'purpose_category',
        'date',
        'time_out',
        'time_in',
        'destination',
        'recommended_by_name',
        'reason',
        'attachment',
        'status',
        'remarks',
        'approved_by',
        'approved_at',
        'filed_by',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($passSlip) {
            if (!$passSlip->slip_number) {
                $passSlip->slip_number = self::generateSlipNumber();
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function filer()
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public static function generateSlipNumber()
    {
        $year = now()->year;
        $month = now()->format('m');

        $lastSlip = self::where('slip_number', 'like', "PS-{$year}{$month}-%")
            ->orderBy('slip_number', 'desc')
            ->first();

        if ($lastSlip) {
            $lastNumber = (int) substr($lastSlip->slip_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PS-{$year}{$month}-{$newNumber}";
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function getPurposeLabelAttribute()
    {
        return self::PURPOSE_LABELS[$this->purpose_category] ?? '';
    }
}
