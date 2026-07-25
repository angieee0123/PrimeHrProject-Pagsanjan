<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    /** Categories shown on the admin Settings page. */
    public const ADMIN_KEYS = [
        'leave_requests',
        'training_submissions',
        'travel_orders',
        'employee_requests',
    ];

    /** Categories shown on the employee Settings page. */
    public const EMPLOYEE_KEYS = [
        'payslip_available',
        'leave_status',
        'dtr_reminder',
        'attendance_alert',
        'email_digest',
    ];

    protected $fillable = [
        'user_id',
        ...self::ADMIN_KEYS,
        ...self::EMPLOYEE_KEYS,
    ];

    protected function casts(): array
    {
        return array_fill_keys([...self::ADMIN_KEYS, ...self::EMPLOYEE_KEYS], 'boolean');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
