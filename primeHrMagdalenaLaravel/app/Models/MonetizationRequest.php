<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

#[Table('monetization_requests')]
class MonetizationRequest extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * Constant Factor in the monetization computation
     * (Total Leave Benefits = Monthly Salary × Days × CF), matching the
     * office's Monetization sheet in docs/excels.
     */
    public const CONSTANT_FACTOR = 0.0481927;

    protected $fillable = [
        'id',
        'request_number',
        'employee_id',
        'vl_days',
        'sl_days',
        'monthly_salary',
        'vl_balance',
        'sl_balance',
        'computed_amount',
        'reason',
        'approver_remarks',
        'filed_by',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'vl_days' => 'decimal:3',
        'sl_days' => 'decimal:3',
        'monthly_salary' => 'decimal:2',
        'vl_balance' => 'decimal:3',
        'sl_balance' => 'decimal:3',
        'computed_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function filedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Days being monetized: VL days + SL days. */
    public function totalDays(): float
    {
        return (float) $this->vl_days + (float) $this->sl_days;
    }

    /**
     * Total Leave Benefits = Monthly Salary × Days × Constant Factor,
     * rounded to centavos — the same formula printed on the office sheet.
     */
    public function computeAmount(): float
    {
        return round((float) $this->monthly_salary * $this->totalDays() * self::CONSTANT_FACTOR, 2);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $request->request_number = self::generateRequestNumber();
            }
        });
    }

    public static function generateRequestNumber(): string
    {
        $year = date('Y');
        $last = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $last ? intval(substr($last->request_number, -4)) + 1 : 1;

        return 'MON-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
