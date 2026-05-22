<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TravelOrder extends Model
{
    use HasFactory;

    protected $table = 'travel_orders';

    protected $fillable = [
        'employee_id',
        'destination',
        'purpose',
        'travel_date',
        'return_date',
        'duration',
        'transportation_mode',
        'estimated_budget',
        'attachment_path',
        'status',
        'approved_by',
        'approved_at',
        'disapproved_by',
        'disapproved_at',
        'disapproval_reason',
        'filed_by',
        'order_number',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'return_date' => 'date',
        'approved_at' => 'datetime',
        'disapproved_at' => 'datetime',
        'estimated_budget' => 'decimal:2',
        'duration' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($travelOrder) {
            if (!$travelOrder->order_number) {
                $travelOrder->order_number = self::generateOrderNumber();
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

    public function disapprover()
    {
        return $this->belongsTo(User::class, 'disapproved_by');
    }

    public function filer()
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    /**
     * Generate unique travel order number
     */
    public static function generateOrderNumber()
    {
        $year = now()->year;
        $month = now()->format('m');
        
        // Get the last order number for this month
        $lastOrder = self::where('order_number', 'like', "TO-{$year}{$month}-%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "TO-{$year}{$month}-{$newNumber}";
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'pending';
            case 'approved':
                return 'processed';
            case 'disapproved':
                return 'on-hold';
            default:
                return 'cancelled';
        }
    }

    /**
     * Get formatted travel dates
     */
    public function getFormattedDatesAttribute()
    {
        $start = Carbon::parse($this->travel_date)->format('M d, Y');
        $end = Carbon::parse($this->return_date)->format('M d, Y');
        
        if ($start === $end) {
            return $start;
        }
        
        return "{$start} - {$end}";
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by employee
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope for current year
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('travel_date', now()->year);
    }
}