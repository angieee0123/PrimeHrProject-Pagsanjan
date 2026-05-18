<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'request_type',
        'title',
        'description',
        'status',
        'admin_response',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'pending',
            'processing' => 'warning',
            'completed' => 'processed',
            'rejected' => 'danger',
            default => 'pending'
        };
    }

    public function getRequestTypeNameAttribute()
    {
        return match($this->request_type) {
            'payslip' => 'Payslip Request',
            'deduction_inquiry' => 'Deduction Inquiry',
            'leave_balance' => 'Leave Balance Inquiry',
            'attendance_correction' => 'Attendance Correction',
            'certificate' => 'Certificate Request',
            'other' => 'Other Request',
            default => ucfirst(str_replace('_', ' ', $this->request_type))
        };
    }
}
