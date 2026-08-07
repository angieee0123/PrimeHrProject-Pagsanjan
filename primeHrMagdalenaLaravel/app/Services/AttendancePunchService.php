<?php

namespace App\Services;

use App\Models\AccreditedHoursLog;
use App\Models\Attendance;
use App\Models\AttendancePunch;
use App\Models\DailySalaryComputation;
use App\Models\Employee;
use App\Models\PassSlip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Records one punch and re-accredits the day around it.
 *
 * This is the seam the biometric reader will plug into. Nothing here knows how
 * the employee was identified — a QR badge today, a fingerprint later — so
 * swapping the capture device means calling `punch()` with a different
 * `$source` and changing nothing about schedules, grace periods, pass slips,
 * or payroll.
 *
 * A punch is not simply a write. It lands in `attendance`, then the day is
 * re-accredited through the same AttendanceComputationService the correction
 * form uses, and the resulting log drives the daily salary figure and (once
 * the day is actually finished) the late/undertime leave deductions.
 */
class AttendancePunchService
{
    /**
     * Re-scanning the same slot within this window is the camera or the
     * employee firing twice, not a corrected time. Treated as a duplicate so a
     * jittery kiosk cannot rewrite an arrival time a minute later.
     */
    private const DUPLICATE_WINDOW_SECONDS = 90;

    /** Day types owned by another workflow, which a punch must not overwrite. */
    private const PROTECTED_TYPES = ['LEAVE', 'TRAVEL_ORDER', 'HOLIDAY'];

    public function __construct(
        private readonly AttendanceComputationService $computation,
    ) {
    }

    /**
     * Record a punch for one employee into one slot.
     *
     * @param  string  $slot  one of AttendancePunch::SLOTS
     * @param  string  $source  one of AttendancePunch::SOURCES
     * @return array{status:string, message:string, employee:Employee, attendance:?Attendance, slot:string, time:?string, previous:?string}
     */
    public function punch(
        Employee $employee,
        string $slot,
        ?Carbon $at = null,
        string $source = 'qr_scan',
        ?int $recordedBy = null,
        ?string $deviceLabel = null,
    ): array {
        if (!in_array($slot, AttendancePunch::SLOTS, true)) {
            throw new \InvalidArgumentException("Unknown attendance slot [{$slot}].");
        }

        if (!in_array($source, AttendancePunch::SOURCES, true)) {
            throw new \InvalidArgumentException("Unknown punch source [{$source}].");
        }

        $at = $at ? $at->copy() : Carbon::now();
        $date = $at->toDateString();
        $time = $at->format('H:i');

        return DB::transaction(function () use ($employee, $slot, $at, $date, $time, $source, $recordedBy, $deviceLabel) {
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->lockForUpdate()
                ->first();

            // An approved leave or travel order already wrote this day via its
            // observer. Punching over it would erase the approval's trace from
            // the DTR, so refuse and let the operator resolve it deliberately
            // through the correction form.
            if ($attendance && in_array($attendance->attendance_type, self::PROTECTED_TYPES, true)) {
                return $this->result(
                    'blocked',
                    $this->firstName($employee) . ' is recorded as ' . $this->typeLabel($attendance->attendance_type)
                        . ' today. Use Attendance → Correct Record to change this day.',
                    $employee,
                    $attendance,
                    $slot,
                    null,
                    null,
                );
            }

            if (!$attendance) {
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                ]);
            }

            $previous = $attendance->{$slot};

            // Same slot, moments apart: the reader fired twice on one badge.
            if ($previous !== null && $this->isDuplicate($previous, $at, $employee->id, $date, $slot)) {
                return $this->result(
                    'duplicate',
                    AttendancePunch::slotLabel($slot) . ' already recorded at ' . $this->displayTime($previous) . '.',
                    $employee,
                    $attendance,
                    $slot,
                    $previous,
                    $previous,
                );
            }

            $attendance->{$slot} = $time;
            $attendance->save();

            AttendancePunch::create([
                'employee_id' => $employee->id,
                'attendance_id' => $attendance->id,
                'date' => $date,
                'slot' => $slot,
                'punched_at' => $at,
                'source' => $source,
                'device_label' => $deviceLabel,
                'recorded_by' => $recordedBy,
                'previous_value' => $previous,
            ]);

            $this->reaccredit($attendance);

            $message = $previous === null
                ? AttendancePunch::slotLabel($slot) . ' recorded at ' . $this->displayTime($time) . '.'
                : AttendancePunch::slotLabel($slot) . ' updated from ' . $this->displayTime($previous)
                    . ' to ' . $this->displayTime($time) . '.';

            return $this->result(
                $previous === null ? 'recorded' : 'updated',
                $message,
                $employee,
                $attendance->fresh(),
                $slot,
                $time,
                $previous,
            );
        });
    }

    /**
     * The slot a punch at this moment most likely belongs to, given the
     * employee's schedule and which slots the day already holds.
     *
     * The kiosk operator chooses the slot, so this only pre-selects a button —
     * it is a convenience, never the authority. When the biometric arrives it
     * becomes the authority, because a wall-mounted reader has no operator.
     */
    public function suggestSlot(Employee $employee, ?Carbon $at = null): string
    {
        $at = $at ? $at->copy() : Carbon::now();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $at->toDateString())
            ->first();

        $schedule = $employee->getScheduleForDate($at->toDateString());
        $toMin = fn($t) => $t ? (int) explode(':', $t)[0] * 60 + (int) explode(':', $t)[1] : null;

        $amEnd = $schedule ? $toMin($schedule->am_out) : 720;   // 12:00
        $pmStart = $schedule ? $toMin($schedule->pm_in) : 780;  // 13:00
        $pmEnd = $schedule ? $toMin($schedule->pm_out) : 1020;  // 17:00
        $now = $at->hour * 60 + $at->minute;

        // Midpoint of the lunch break: before it the morning is still closing,
        // after it the afternoon has effectively begun.
        $lunchMid = intdiv($amEnd + $pmStart, 2);

        if ($now < $lunchMid) {
            return ($attendance && $attendance->am_in) ? 'am_out' : 'am_in';
        }

        if ($now < $pmEnd) {
            return ($attendance && $attendance->pm_in) ? 'pm_out' : 'pm_in';
        }

        if (!$attendance || !$attendance->pm_out) {
            return 'pm_out';
        }

        return $attendance->ot_in ? 'ot_out' : 'ot_in';
    }

    /**
     * Re-run accreditation, daily salary, and leave deductions for the day.
     *
     * Leave deductions are held back until the day is actually complete. Run
     * after a lone morning punch, the afternoon reads as an undertime absence
     * and charges the employee's leave credits for hours they are still in the
     * building to work.
     */
    private function reaccredit(Attendance $attendance): void
    {
        $passSlips = PassSlip::where('employee_id', $attendance->employee_id)
            ->where('status', 'approved')
            ->whereDate('date', $attendance->date)
            ->get();

        $result = $this->computation->computeAccreditedHours(
            $attendance->employee_id,
            $attendance->date instanceof Carbon ? $attendance->date->toDateString() : $attendance->date,
            $attendance->am_in,
            $attendance->am_out,
            $attendance->pm_in,
            $attendance->pm_out,
            $attendance->ot_in,
            $attendance->ot_out,
            $passSlips,
        );

        $attendance->update([
            'accredited_hours' => $result['accredited_minutes'],
            'total_hours' => $this->computation->computeTotalHours(
                $attendance->am_in,
                $attendance->am_out,
                $attendance->pm_in,
                $attendance->pm_out,
                $attendance->ot_in,
                $attendance->ot_out,
            ),
        ]);

        if (!$result['log_data']) {
            return;
        }

        $oldLog = AccreditedHoursLog::where('attendance_id', $attendance->id)->latest()->first();
        $hadPreviousDeductions = $oldLog
            && ($oldLog->late_deducted_from_leave || $oldLog->undertime_deducted_from_leave);

        $log = AccreditedHoursLog::updateOrCreate(
            ['attendance_id' => $attendance->id],
            array_merge($result['log_data'], [
                'employee_id' => $attendance->employee_id,
                'computation_notes' => 'Attendance scanner punch at ' . Carbon::now()->format('Y-m-d H:i:s'),
            ]),
        );

        DailySalaryComputation::computeFromAccreditedLog($log);

        $dayComplete = $attendance->am_in && $attendance->am_out
            && $attendance->pm_in && $attendance->pm_out;

        if ($hadPreviousDeductions) {
            // A finished day is being amended by a later punch; reverse and
            // reapply so credits are not deducted twice.
            (new AttendanceCorrectionLeaveRecalculationService())
                ->recalculateLeaveDeductions($oldLog, $log);

            return;
        }

        if ($dayComplete) {
            (new LateDeductionService())->processLateDeduction($log);
            (new UndertimeDeductionService())->processUndertimeDeduction($log);
        }
    }

    /**
     * Whether this punch repeats one just made into the same slot.
     *
     * The stored slot value is only HH:MM, so a same-minute re-scan is caught
     * by string equality; the punch log carries the seconds needed to catch a
     * re-scan that crosses a minute boundary.
     */
    private function isDuplicate(string $previous, Carbon $at, int $employeeId, string $date, string $slot): bool
    {
        if ($previous === $at->format('H:i')) {
            return true;
        }

        $lastPunch = AttendancePunch::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('slot', $slot)
            ->latest('punched_at')
            ->first();

        return $lastPunch
            && abs($lastPunch->punched_at->diffInSeconds($at)) <= self::DUPLICATE_WINDOW_SECONDS;
    }

    private function result(
        string $status,
        string $message,
        Employee $employee,
        ?Attendance $attendance,
        string $slot,
        ?string $time,
        ?string $previous,
    ): array {
        return compact('status', 'message', 'employee', 'attendance', 'slot', 'time', 'previous');
    }

    /**
     * The slot columns are varchar and may hold markers like `TO` rather than
     * a time, so fall back to the raw value instead of failing to parse it.
     */
    private function displayTime(string $time): string
    {
        if (!preg_match('/^\d{1,2}:\d{2}/', $time)) {
            return $time;
        }

        return Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
    }

    private function firstName(Employee $employee): string
    {
        return $employee->first_name ?: 'This employee';
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'LEAVE' => 'on approved leave',
            'TRAVEL_ORDER' => 'on an approved travel order',
            'HOLIDAY' => 'a holiday',
            default => strtolower($type),
        };
    }
}
