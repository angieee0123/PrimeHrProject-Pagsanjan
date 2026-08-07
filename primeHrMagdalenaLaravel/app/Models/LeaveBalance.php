<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class LeaveBalance extends Model
{
    /**
     * An employee's current balance for each leave code: the most recent row
     * per code, whatever year it was written under.
     *
     * Balances are NOT rewritten every January — a row is created when credits
     * are next computed, so an employee's live figures can sit under an older
     * `year` while their available_credits are entirely current. Employee 8's
     * standing VL/SL balances, for example, are stored under 2023.
     *
     * That is why "current balance" cannot be `where('year', now()->year)`:
     * that filter returns nothing for such an employee and reads as "you have
     * no leave credits". The leave pages have always ordered by year instead;
     * this method is where that rule now lives, so the AI Assistant answers the
     * same figures the employee sees on their own Leave & Benefits page rather
     * than a second interpretation of the same table.
     *
     * @return Collection<string, LeaveBalance> keyed by leave_code
     */
    public static function currentFor(int $employeeId): Collection
    {
        return static::query()
            ->where('employee_id', $employeeId)
            ->orderBy('year', 'desc')
            ->get()
            ->unique('leave_code')
            ->keyBy('leave_code');
    }

    /** The current balance for one leave code, or null if none was ever set. */
    public static function currentForCode(int $employeeId, string $leaveCode): ?self
    {
        return static::query()
            ->where('employee_id', $employeeId)
            ->where('leave_code', $leaveCode)
            ->orderBy('year', 'desc')
            ->first();
    }

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
        'total_credits' => 'decimal:6',
        'used_credits' => 'decimal:6',
        'pending_credits' => 'decimal:6',
        'available_credits' => 'decimal:6',
        'carried_over' => 'decimal:6',
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
