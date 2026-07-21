<?php

namespace App\Services;

use App\Models\PassSlip;
use App\Models\Schedule;
use Carbon\Carbon;

/**
 * Applies CSC MC No. 21, s. 1991 (government working hours), CSC MC No. 01, s. 1994
 * (habitual tardiness/undertime, amending Rule XVIII Sec. 1 of the Omnibus Rules
 * Implementing Book V of E.O. 292), and CSC MC No. 05, s. 1997 (attendance and
 * punctuality) to how an approved Pass Slip affects daily attendance.
 */
class PassSlipComplianceService
{
    private const HABITUAL_THRESHOLD_PER_MONTH = 10;

    /**
     * Minutes of an approved pass slip's time-out/time-in window that fall inside
     * paid office hours (AM and PM sessions only — the unpaid lunch break in between
     * is never counted).
     */
    public function computeGapMinutes(PassSlip $slip, ?Schedule $schedule): int
    {
        [$amStart, $amEnd, $pmStart, $pmEnd] = $this->officeWindows($schedule);

        $out = $this->toMinutes($slip->time_out);
        $in = $slip->time_in ? $this->toMinutes($slip->time_in) : $pmEnd;

        if ($out === null || $in === null || $in <= $out) {
            return 0;
        }

        $amOverlap = max(0, min($in, $amEnd) - max($out, $amStart));
        $pmOverlap = max(0, min($in, $pmEnd) - max($out, $pmStart));

        return $amOverlap + $pmOverlap;
    }

    public function isExcused(PassSlip $slip): bool
    {
        return $slip->status === 'approved' && $slip->type === 'official_activity';
    }

    /**
     * Minutes of an approved pass slip's window that fall inside a single named
     * session ('am' or 'pm') only — used to decide whether a missing punch pair
     * for that specific session is explained by the slip (as opposed to
     * computeGapMinutes(), which sums both sessions together).
     */
    public function sessionOverlapMinutes(PassSlip $slip, ?Schedule $schedule, string $session): int
    {
        [$amStart, $amEnd, $pmStart, $pmEnd] = $this->officeWindows($schedule);
        [$sessionStart, $sessionEnd] = $session === 'am' ? [$amStart, $amEnd] : [$pmStart, $pmEnd];

        $out = $this->toMinutes($slip->time_out);
        $in = $slip->time_in ? $this->toMinutes($slip->time_in) : $pmEnd;

        if ($out === null || $in === null || $in <= $out) {
            return 0;
        }

        return max(0, min($in, $sessionEnd) - max($out, $sessionStart));
    }

    /**
     * True when an approved Official Activity pass slip's window fully covers a
     * session, i.e. it explains why that session's punch pair is missing —
     * the employee was already out on official business for the entire
     * session, not merely absent without excuse.
     */
    public function excusesSession(PassSlip $slip, ?Schedule $schedule, string $session): bool
    {
        if (!$this->isExcused($slip)) {
            return false;
        }

        [$amStart, $amEnd, $pmStart, $pmEnd] = $this->officeWindows($schedule);
        $sessionDuration = $session === 'am' ? ($amEnd - $amStart) : ($pmEnd - $pmStart);

        return $this->sessionOverlapMinutes($slip, $schedule, $session) >= $sessionDuration;
    }

    public function isChargeable(PassSlip $slip): bool
    {
        return $slip->status === 'approved' && $slip->type === 'personal_reason';
    }

    /**
     * Adjust an already-computed undertime figure (minutes) for a day's approved
     * pass slips. Official Activity excuses the gap (CSC MC 21 s.1991 treats time on
     * official business as time rendered); Personal Reason charges it as undertime,
     * since ordinary AM/PM punches alone would not otherwise capture a mid-session
     * personal errand.
     */
    public function adjustUndertimeMinutes(int $undertimeMinutes, iterable $passSlipsForDate, ?Schedule $schedule): int
    {
        foreach ($passSlipsForDate as $slip) {
            $gap = $this->computeGapMinutes($slip, $schedule);

            if ($this->isExcused($slip)) {
                $undertimeMinutes = max(0, $undertimeMinutes - $gap);
            } elseif ($this->isChargeable($slip)) {
                $undertimeMinutes += $gap;
            }
        }

        return $undertimeMinutes;
    }

    /**
     * Habitual tardiness/undertime check (CSC MC 01 s.1994): an employee is habitual
     * when a month has 10+ days with undertime, and either (a) 2+ such months fall
     * within the same CSC semester (Jan-Jun or Jul-Dec of a given year), or
     * (b) any 2 calendar-consecutive months both qualify (this clause applies even
     * across a semester boundary, e.g. June + July).
     *
     * @param array<string, int> $undertimeDayCountsByMonth Keys 'Y-m' => count of days with undertime > 0 that month.
     */
    public function isHabitual(array $undertimeDayCountsByMonth): bool
    {
        $qualifyingMonths = array_keys(array_filter(
            $undertimeDayCountsByMonth,
            fn ($count) => $count >= self::HABITUAL_THRESHOLD_PER_MONTH
        ));

        if (count($qualifyingMonths) < 2) {
            return false;
        }

        sort($qualifyingMonths);

        $bySemester = [];
        foreach ($qualifyingMonths as $month) {
            $year = (int) substr($month, 0, 4);
            $half = ((int) substr($month, 5, 2)) <= 6 ? 1 : 2;
            $bySemester["{$year}-{$half}"][] = $month;
        }

        foreach ($bySemester as $monthsInSemester) {
            if (count($monthsInSemester) >= 2) {
                return true;
            }
        }

        foreach ($qualifyingMonths as $i => $month) {
            if (!isset($qualifyingMonths[$i + 1])) {
                continue;
            }

            if (Carbon::createFromFormat('Y-m', $month)->addMonthNoOverflow()->format('Y-m') === $qualifyingMonths[$i + 1]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Minutes of an approved Official Activity pass slip's window that fall
     * within an arbitrary explicit range (in minutes since midnight). Used to
     * credit the portion of a session an employee was on official business
     * for, even when real punches also exist for that session — e.g. he
     * returned early, before the slip's stated end time, so the pre-arrival
     * gap isn't captured by the punch pair alone.
     */
    public function excusedRangeOverlapMinutes(PassSlip $slip, int $rangeStart, int $rangeEnd): int
    {
        if (!$this->isExcused($slip) || $rangeEnd <= $rangeStart) {
            return 0;
        }

        $out = $this->toMinutes($slip->time_out);
        $in = $slip->time_in ? $this->toMinutes($slip->time_in) : $rangeEnd;

        if ($out === null || $in === null || $in <= $out) {
            return 0;
        }

        return max(0, min($in, $rangeEnd) - max($out, $rangeStart));
    }

    private function officeWindows(?Schedule $schedule): array
    {
        $amStart = $schedule ? $this->toMinutes($schedule->am_in) : 480;  // 08:00
        $amEnd = $schedule ? $this->toMinutes($schedule->am_out) : 720;   // 12:00
        $pmStart = $schedule ? $this->toMinutes($schedule->pm_in) : 780;  // 13:00
        $pmEnd = $schedule ? $this->toMinutes($schedule->pm_out) : 1020;  // 17:00

        return [$amStart, $amEnd, $pmStart, $pmEnd];
    }

    private function toMinutes(?string $time): ?int
    {
        if (!$time) {
            return null;
        }

        $parts = explode(':', $time);

        return ((int) $parts[0]) * 60 + (int) ($parts[1] ?? 0);
    }
}
