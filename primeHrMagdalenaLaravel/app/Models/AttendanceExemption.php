<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceExemption extends Model
{
    protected $fillable = [
        'exemption_type',
        'reference_id',
        'reference_name',
        'exempt_from_abandoned',
        'exempt_from_incomplete',
        'reason',
        'start_date',
        'end_date',
        'am_in_not_required',
        'am_out_not_required',
        'pm_in_not_required',
        'pm_out_not_required',
        'auto_fill_am_out',
        'auto_fill_pm_in',
        'created_by',
    ];

    protected $casts = [
        'exempt_from_abandoned' => 'boolean',
        'exempt_from_incomplete' => 'boolean',
        'am_in_not_required' => 'boolean',
        'am_out_not_required' => 'boolean',
        'pm_in_not_required' => 'boolean',
        'pm_out_not_required' => 'boolean',
        'auto_fill_am_out' => 'boolean',
        'auto_fill_pm_in' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who created this exemption
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if an employee is exempt from abandoned flag on a given date
     */
    public static function isExemptFromAbandoned(int $employeeId, ?int $departmentId = null, ?int $designationId = null, $date = null): bool
    {
        $exemption = self::getActiveExemption($employeeId, $departmentId, $designationId, $date);

        return $exemption ? (bool) $exemption->exempt_from_abandoned : false;
    }

    /**
     * Check if an employee is exempt from incomplete flag on a given date
     */
    public static function isExemptFromIncomplete(int $employeeId, ?int $departmentId = null, ?int $designationId = null, $date = null): bool
    {
        $exemption = self::getActiveExemption($employeeId, $departmentId, $designationId, $date);

        return $exemption ? (bool) $exemption->exempt_from_incomplete : false;
    }

    /**
     * Get schedule default times for auto-fill (falls back to system defaults)
     */
    public static function getScheduleDefaults(?Schedule $schedule): array
    {
        return [
            'am_in' => $schedule ? \Carbon\Carbon::parse($schedule->am_in)->format('H:i') : '08:00',
            'am_out' => $schedule ? \Carbon\Carbon::parse($schedule->am_out)->format('H:i') : '12:00',
            'pm_in' => $schedule ? \Carbon\Carbon::parse($schedule->pm_in)->format('H:i') : '13:00',
            'pm_out' => $schedule ? \Carbon\Carbon::parse($schedule->pm_out)->format('H:i') : '17:00',
        ];
    }

    /**
     * Apply exemption auto-fill to punch times using the employee's schedule.
     * Actual scans in the database are unchanged; this returns effective times
     * used for DTR display, status flags, and accredited hours.
     */
    public function applyAutoFillToPunches(
        ?string $amIn,
        ?string $amOut,
        ?string $pmIn,
        ?string $pmOut,
        ?Schedule $schedule
    ): array {
        $defaults = self::getScheduleDefaults($schedule);
        $hasAnyPunch = $amIn || $amOut || $pmIn || $pmOut;
        $before = [
            'am_in' => $amIn,
            'am_out' => $amOut,
            'pm_in' => $pmIn,
            'pm_out' => $pmOut,
        ];

        if ($this->am_in_not_required && !$amIn && $hasAnyPunch) {
            $amIn = $defaults['am_in'];
        }

        if ($this->am_out_not_required && $this->auto_fill_am_out && !$amOut && ($amIn || $pmIn || $pmOut)) {
            $amOut = $defaults['am_out'];
        }

        if ($this->pm_in_not_required && $this->auto_fill_pm_in && !$pmIn && ($amIn || $amOut || $pmOut)) {
            $pmIn = $defaults['pm_in'];
        }

        if ($this->pm_out_not_required && !$pmOut && ($amIn || $amOut || $pmIn)) {
            $pmOut = $defaults['pm_out'];
        }

        return [
            'am_in' => $amIn,
            'am_out' => $amOut,
            'pm_in' => $pmIn,
            'pm_out' => $pmOut,
            'auto_filled' => [
                'am_in' => !$before['am_in'] && (bool) $amIn,
                'am_out' => !$before['am_out'] && (bool) $amOut,
                'pm_in' => !$before['pm_in'] && (bool) $pmIn,
                'pm_out' => !$before['pm_out'] && (bool) $pmOut,
            ],
        ];
    }

    /**
     * Resolve effective punch times for an employee on a date, applying active exemption auto-fill.
     */
    public static function resolveEffectivePunches(
        int $employeeId,
        ?int $departmentId,
        ?int $designationId,
        $date,
        ?string $amIn,
        ?string $amOut,
        ?string $pmIn,
        ?string $pmOut,
        ?Schedule $schedule
    ): array {
        $exemption = self::getActiveExemption($employeeId, $departmentId, $designationId, $date);

        if (!$exemption) {
            return [
                'am_in' => $amIn,
                'am_out' => $amOut,
                'pm_in' => $pmIn,
                'pm_out' => $pmOut,
                'exemption' => null,
                'auto_filled' => [],
            ];
        }

        $result = $exemption->applyAutoFillToPunches($amIn, $amOut, $pmIn, $pmOut, $schedule);
        $result['exemption'] = $exemption;

        return $result;
    }

    /**
     * Check whether a punch is satisfied (present or marked not required).
     */
    public function isPunchSatisfied(string $punch, ?string $value): bool
    {
        if ($value) {
            return true;
        }

        return match ($punch) {
            'am_in' => (bool) $this->am_in_not_required,
            'am_out' => (bool) $this->am_out_not_required,
            'pm_in' => (bool) $this->pm_in_not_required,
            'pm_out' => (bool) $this->pm_out_not_required,
            default => false,
        };
    }

    /**
     * Get active exemption for an employee on a specific date
     * Returns the exemption configuration if one exists and is active
     */
    public static function getActiveExemption(int $employeeId, ?int $departmentId = null, ?int $designationId = null, $date = null): ?self
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        
        // Check employee-specific exemption first (highest priority)
        $exemption = self::where('exemption_type', 'employee')
            ->where('reference_id', $employeeId)
            ->where(function($query) use ($date) {
                $query->where(function($q) use ($date) {
                    // No date restrictions
                    $q->whereNull('start_date')->whereNull('end_date');
                })->orWhere(function($q) use ($date) {
                    // Only start date
                    $q->whereNotNull('start_date')
                      ->whereNull('end_date')
                      ->whereDate('start_date', '<=', $date);
                })->orWhere(function($q) use ($date) {
                    // Only end date
                    $q->whereNull('start_date')
                      ->whereNotNull('end_date')
                      ->whereDate('end_date', '>=', $date);
                })->orWhere(function($q) use ($date) {
                    // Both dates
                    $q->whereNotNull('start_date')
                      ->whereNotNull('end_date')
                      ->whereDate('start_date', '<=', $date)
                      ->whereDate('end_date', '>=', $date);
                });
            })
            ->first();

        if ($exemption) {
            return $exemption;
        }

        // Check department exemption
        if ($departmentId) {
            $exemption = self::where('exemption_type', 'department')
                ->where('reference_id', $departmentId)
                ->where(function($query) use ($date) {
                    $query->where(function($q) use ($date) {
                        $q->whereNull('start_date')->whereNull('end_date');
                    })->orWhere(function($q) use ($date) {
                        $q->whereNotNull('start_date')
                          ->whereNull('end_date')
                          ->whereDate('start_date', '<=', $date);
                    })->orWhere(function($q) use ($date) {
                        $q->whereNull('start_date')
                          ->whereNotNull('end_date')
                          ->whereDate('end_date', '>=', $date);
                    })->orWhere(function($q) use ($date) {
                        $q->whereNotNull('start_date')
                          ->whereNotNull('end_date')
                          ->whereDate('start_date', '<=', $date)
                          ->whereDate('end_date', '>=', $date);
                    });
                })
                ->first();

            if ($exemption) {
                return $exemption;
            }
        }

        // Check designation exemption
        if ($designationId) {
            $exemption = self::where('exemption_type', 'designation')
                ->where('reference_id', $designationId)
                ->where(function($query) use ($date) {
                    $query->where(function($q) use ($date) {
                        $q->whereNull('start_date')->whereNull('end_date');
                    })->orWhere(function($q) use ($date) {
                        $q->whereNotNull('start_date')
                          ->whereNull('end_date')
                          ->whereDate('start_date', '<=', $date);
                    })->orWhere(function($q) use ($date) {
                        $q->whereNull('start_date')
                          ->whereNotNull('end_date')
                          ->whereDate('end_date', '>=', $date);
                    })->orWhere(function($q) use ($date) {
                        $q->whereNotNull('start_date')
                          ->whereNotNull('end_date')
                          ->whereDate('start_date', '<=', $date)
                          ->whereDate('end_date', '>=', $date);
                    });
                })
                ->first();

            if ($exemption) {
                return $exemption;
            }
        }

        return null;
    }

    /**
     * Check if a specific time entry is not required for an employee on a date
     */
    public static function isTimeEntryNotRequired(int $employeeId, ?int $departmentId, ?int $designationId, $date, string $timeEntry): bool
    {
        $exemption = self::getActiveExemption($employeeId, $departmentId, $designationId, $date);
        
        if (!$exemption) {
            return false;
        }

        switch ($timeEntry) {
            case 'am_in':
                return $exemption->am_in_not_required;
            case 'am_out':
                return $exemption->am_out_not_required;
            case 'pm_in':
                return $exemption->pm_in_not_required;
            case 'pm_out':
                return $exemption->pm_out_not_required;
            default:
                return false;
        }
    }
}
