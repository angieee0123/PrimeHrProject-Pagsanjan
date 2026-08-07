<?php

namespace App\Services;

use App\Models\Employee;

/**
 * Turns a day's raw punch times into accredited minutes.
 *
 * This lived inside AttendanceController while the correction form was the
 * only thing that wrote punches. A punch now also arrives from the attendance
 * scanner (and, later, from a biometric device), and all three must accredit
 * a day identically — a scanned 08:03 arrival has to earn the same grace as a
 * manually corrected one, or the demo device and the payroll it feeds disagree.
 *
 * The controller keeps its private wrappers and delegates here, so the
 * correction path is byte-for-byte the behaviour it had before.
 */
class AttendanceComputationService
{
    /**
     * Compute accredited hours and create detailed log.
     * Returns array with accredited minutes and log data.
     */
    public function computeAccreditedHours($employeeId, $date, ?string $amIn, ?string $amOut, ?string $pmIn, ?string $pmOut, ?string $otIn = null, ?string $otOut = null, ?iterable $passSlipsForDate = null): array
    {
        if (!$amIn && !$amOut && !$pmIn && !$pmOut) {
            return ['accredited_minutes' => null, 'log_data' => null];
        }

        $employee = Employee::find($employeeId);
        $schedule = $employee ? $employee->getScheduleForDate($date) : null;

        $departmentId = $employee?->employmentDetail?->department_id;
        $designationId = $employee?->employmentDetail?->designation_id;

        $effective = \App\Models\AttendanceExemption::resolveEffectivePunches(
            $employeeId,
            $departmentId,
            $designationId,
            $date,
            $amIn,
            $amOut,
            $pmIn,
            $pmOut,
            $schedule
        );

        $amIn = $effective['am_in'];
        $amOut = $effective['am_out'];
        $pmIn = $effective['pm_in'];
        $pmOut = $effective['pm_out'];
        $exemption = $effective['exemption'];

        $toMin = fn($t) => $t ? (int)(explode(':', $t)[0]) * 60 + (int)(explode(':', $t)[1]) : null;

        // Use employee's schedule or defaults
        $AM_START   = $schedule ? $toMin($schedule->am_in) : 480;  // Default 08:00
        $AM_END     = $schedule ? $toMin($schedule->am_out) : 720;  // Default 12:00
        $AM_GRACE   = $AM_START + 5;  // 5 minutes grace
        $PM_START   = $schedule ? $toMin($schedule->pm_in) : 780;  // Default 13:00
        $PM_END     = $schedule ? $toMin($schedule->pm_out) : 1020; // Default 17:00
        $PM_GRACE   = $PM_START + 5;  // 5 minutes grace

        // Abandoned: AM in only with no AM out and no PM in (after exemption auto-fill)
        if ($amIn && !$amOut && !$pmIn && !($exemption && $exemption->exempt_from_abandoned)) {
            return [
                'accredited_minutes' => 0,
                'log_data' => [
                    'schedule_id' => $schedule ? $schedule->id : null,
                    'am_accredited_minutes' => 0,
                    'pm_accredited_minutes' => 0,
                    'ot_minutes' => 0,
                    'late_minutes' => 0,
                    'undertime_minutes' => 480, // 8 hours absent
                    'total_accredited_minutes' => 0,
                    'total_actual_minutes' => 0,
                    'am_grace_applied' => false,
                    'pm_grace_applied' => false,
                ]
            ];
        }

        $passSlipCompliance = new PassSlipComplianceService();

        $amMins = 0;
        $amGraceApplied = false;
        if ($amIn && $amOut) {
            $amInMin = $toMin($amIn);
            if ($amInMin <= $AM_GRACE) {
                $amFrom = $AM_START;
                $amGraceApplied = true;
            } else {
                $amFrom = $amInMin;
            }
            $amTo = min($toMin($amOut), $AM_END);
            $amMins = max(0, $amTo - $amFrom);
            if ($passSlipsForDate) {
                $amMins = min($AM_END - $AM_START, $amMins + $this->creditPassSlipGapMinutes($AM_START, $AM_END, $amFrom, $amTo, $passSlipsForDate, $passSlipCompliance));
            }
        } elseif ($passSlipsForDate) {
            // No AM punches — credit the session if an approved Official
            // Activity pass slip's window explains the absence.
            foreach ($passSlipsForDate as $slip) {
                if ($passSlipCompliance->excusesSession($slip, $schedule, 'am')) {
                    $amMins = max($amMins, min($AM_END - $AM_START, $passSlipCompliance->sessionOverlapMinutes($slip, $schedule, 'am')));
                }
            }
        }

        $pmMins = 0;
        $pmGraceApplied = false;
        if ($pmIn && $pmOut) {
            $pmInMin = $toMin($pmIn);
            if ($pmInMin <= $PM_GRACE) {
                $pmFrom = $PM_START;
                $pmGraceApplied = true;
            } else {
                $pmFrom = $pmInMin;
            }
            $pmTo = min($toMin($pmOut), $PM_END);
            $pmMins = max(0, $pmTo - $pmFrom);
            if ($passSlipsForDate) {
                $pmMins = min($PM_END - $PM_START, $pmMins + $this->creditPassSlipGapMinutes($PM_START, $PM_END, $pmFrom, $pmTo, $passSlipsForDate, $passSlipCompliance));
            }
        } elseif ($passSlipsForDate) {
            foreach ($passSlipsForDate as $slip) {
                if ($passSlipCompliance->excusesSession($slip, $schedule, 'pm')) {
                    $pmMins = max($pmMins, min($PM_END - $PM_START, $passSlipCompliance->sessionOverlapMinutes($slip, $schedule, 'pm')));
                }
            }
        }

        // Calculate OT
        $otMins = 0;
        if ($otIn && $otOut) {
            $otMins = max(0, $toMin($otOut) - $toMin($otIn));
        }

        // Calculate late and undertime
        $lateMins = 0;
        $undertimeMins = 0;

        // AM In late
        if ($amIn) {
            $amInMin = $toMin($amIn);
            if ($amInMin > $AM_GRACE) {
                $lateMins += $amInMin - $AM_START;
            }
        }

        // AM Out undertime (left early before lunch)
        if ($amOut) {
            $amOutMin = $toMin($amOut);
            if ($amOutMin < $AM_END) {
                $undertimeMins += $AM_END - $amOutMin;
            }
        }

        // PM In late (returned late from lunch)
        if ($pmIn) {
            $pmInMin = $toMin($pmIn);
            if ($pmInMin > $PM_GRACE) {
                $lateMins += $pmInMin - $PM_START;
            }
        }

        // PM Out undertime (left early at end of day)
        if ($pmOut) {
            $pmOutMin = $toMin($pmOut);
            if ($pmOutMin < $PM_END) {
                $undertimeMins += $PM_END - $pmOutMin;
            }
        }

        $totalAccredited = $amMins + $pmMins;
        $totalActual = 0;
        if ($amIn && $amOut) $totalActual += $toMin($amOut) - $toMin($amIn);
        if ($pmIn && $pmOut) $totalActual += $toMin($pmOut) - $toMin($pmIn);
        if ($otIn && $otOut) $totalActual += $otMins;

        // Apply CSC-based Pass Slip adjustment (see PassSlipComplianceService):
        // Official Activity excuses the gap, Personal Reason charges it.
        if ($passSlipsForDate) {
            $undertimeMins = $passSlipCompliance->adjustUndertimeMinutes($undertimeMins, $passSlipsForDate, $schedule);
        }

        return [
            'accredited_minutes' => $totalAccredited,
            'log_data' => [
                'schedule_id' => $schedule ? $schedule->id : null,
                'am_accredited_minutes' => $amMins,
                'pm_accredited_minutes' => $pmMins,
                'ot_minutes' => $otMins,
                'late_minutes' => $lateMins,
                'undertime_minutes' => $undertimeMins,
                'total_accredited_minutes' => $totalAccredited,
                'total_actual_minutes' => $totalActual,
                'am_grace_applied' => $amGraceApplied,
                'pm_grace_applied' => $pmGraceApplied,
            ]
        ];
    }

    /**
     * Extra minutes to credit within a session when an approved Official
     * Activity pass slip covers the gap before the employee's actual
     * arrival and/or after their actual departure — e.g. he returned early
     * from official business partway through the session, so the punch
     * pair alone would under-credit the pre-arrival hour.
     */
    public function creditPassSlipGapMinutes(int $sessionStart, int $sessionEnd, int $realFrom, int $realTo, iterable $passSlipsForDate, PassSlipComplianceService $passSlipCompliance): int
    {
        $extra = 0;

        $preGap = max(0, $realFrom - $sessionStart);
        if ($preGap > 0) {
            $covered = 0;
            foreach ($passSlipsForDate as $slip) {
                $covered += $passSlipCompliance->excusedRangeOverlapMinutes($slip, $sessionStart, $realFrom);
            }
            $extra += min($preGap, $covered);
        }

        $postGap = max(0, $sessionEnd - $realTo);
        if ($postGap > 0) {
            $covered = 0;
            foreach ($passSlipsForDate as $slip) {
                $covered += $passSlipCompliance->excusedRangeOverlapMinutes($slip, $realTo, $sessionEnd);
            }
            $extra += min($postGap, $covered);
        }

        return $extra;
    }

    /**
     * Compute total hours worked in minutes (actual time logged).
     */
    public function computeTotalHours(?string $amIn, ?string $amOut, ?string $pmIn, ?string $pmOut, ?string $otIn, ?string $otOut): ?int
    {
        if (!$amIn && !$amOut && !$pmIn && !$pmOut && !$otIn && !$otOut) {
            return null;
        }

        $toMin = fn($t) => $t ? (int)(explode(':', $t)[0]) * 60 + (int)(explode(':', $t)[1]) : null;

        $totalMins = 0;

        // Calculate AM hours
        if ($amIn && $amOut) {
            $totalMins += max(0, $toMin($amOut) - $toMin($amIn));
        }

        // Calculate PM hours
        if ($pmIn && $pmOut) {
            $totalMins += max(0, $toMin($pmOut) - $toMin($pmIn));
        }

        // Calculate OT hours
        if ($otIn && $otOut) {
            $totalMins += max(0, $toMin($otOut) - $toMin($otIn));
        }

        return $totalMins;
    }
}
