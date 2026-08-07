<?php

namespace App\Services;

use App\Models\DailySalaryComputation;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

/**
 * The HR rules the assistant is allowed to state, read from the places the
 * system actually computes them.
 *
 * Why this exists: the chatbot's knowledge used to be a ~165-line string
 * constant listing leave types, grace periods, conversion factors and the LWOP
 * formula. Every line of it was a second copy of a rule that lives somewhere
 * real — `leave_types_config`, `leave_accrual_rates`, CscTimeConversionService,
 * AttendanceComputationService — and nothing kept the copies in agreement.
 *
 * The copy had already drifted: it named 7 leave types where the database holds
 * 20 active ones, so an employee asking about Bereavement or Solo Parent leave
 * was answered from a list those benefits were missing from. It also stated
 * "VL and SL are accrued monthly" without the 1.25-days rate the accrual table
 * defines.
 *
 * That string is also injected into the text-to-SQL prompt, so a stale rule did
 * not merely produce a wrong sentence — it produced a wrong query whose rows
 * then looked authoritative.
 *
 * Everything here is therefore derived, never typed. If a fact cannot be read
 * from the system, it does not belong in this file.
 */
class HrPolicyFactsService
{
    /**
     * Reading the config tables costs two queries; a single chat turn asks for
     * the block up to three times (policy shortcut, SQL prompt, narration).
     */
    private ?array $cache = null;

    /**
     * Every fact, structured. Callers that need a specific number use this;
     * `knowledgeBlock()` renders the same data as prose for a prompt.
     *
     * @return array<string, mixed>
     */
    public function facts(): array
    {
        return $this->cache ??= [
            'conversion' => [
                'minutes_per_day' => CscTimeConversionService::MINUTES_PER_WORK_DAY,
                'hours_per_day' => CscTimeConversionService::HOURS_PER_WORK_DAY,
                'minutes_per_half_day' => CscTimeConversionService::MINUTES_PER_HALF_DAY,
            ],
            'attendance' => [
                'grace_minutes' => AttendanceComputationService::GRACE_MINUTES,
                'default_am_in' => $this->clockTime(AttendanceComputationService::DEFAULT_AM_START),
                'default_am_out' => $this->clockTime(AttendanceComputationService::DEFAULT_AM_END),
                'default_pm_in' => $this->clockTime(AttendanceComputationService::DEFAULT_PM_START),
                'default_pm_out' => $this->clockTime(AttendanceComputationService::DEFAULT_PM_END),
            ],
            'payroll' => [
                'working_days_per_month' => DailySalaryComputation::WORKING_DAYS_PER_MONTH,
                'deduction_order' => LateDeductionService::DEDUCTION_ORDER,
            ],
            'leave_types' => $this->leaveTypes(),
        ];
    }

    /**
     * Active leave types with their limits and the accrual rate that applies,
     * straight from the tables HR edits.
     *
     * @return array<int, array<string, mixed>>
     */
    private function leaveTypes(): array
    {
        try {
            $rates = DB::table('leave_accrual_rates')
                ->where('is_active', 1)
                ->orderByDesc('effective_date')
                ->get()
                ->keyBy('leave_type_id');

            $types = DB::table('leave_types_config')
                ->where('is_active', 1)
                ->orderBy('leave_code')
                ->get();
        } catch (\Throwable) {
            // No config tables on this connection (the test SQLite database
            // builds only what a test touches). Report nothing rather than
            // falling back to a hard-coded list — an empty list is honest,
            // a stale list is the bug this class exists to remove.
            return [];
        }

        return $types->map(function ($type) use ($rates) {
            $rate = $rates->get($type->id);

            return [
                'code' => $type->leave_code,
                'name' => $type->leave_name,
                'annual_limit' => (float) $type->annual_limit,
                'accrued' => (bool) $type->is_accrued,
                'cumulative' => (bool) $type->is_cumulative,
                'monetizable' => (bool) $type->is_monetizable,
                'requires_6_months' => (bool) $type->requires_6_months,
                'requires_attachment' => (bool) $type->requires_attachment,
                'attachment_info' => $type->attachment_info,
                'accrual_per_month' => $rate && $rate->accrual_frequency === 'monthly'
                    ? (float) $rate->credits_earned_per_period
                    : null,
            ];
        })->all();
    }

    /**
     * The caller's own working schedule, or the system default when they have
     * no `schedules` row covering today.
     *
     * The old knowledge block asserted "Standard schedule: AM 8:00-12:00, PM
     * 13:00-17:00" for everyone, but `schedules` is per-employee and
     * date-bounded — so the answer to "what time do I start?" is a lookup, not
     * a constant.
     *
     * @return array{am_in: string, am_out: string, pm_in: string, pm_out: string, source: string}
     */
    public function scheduleFor(?Employee $employee): array
    {
        $facts = $this->facts()['attendance'];

        $default = [
            'am_in' => $facts['default_am_in'],
            'am_out' => $facts['default_am_out'],
            'pm_in' => $facts['default_pm_in'],
            'pm_out' => $facts['default_pm_out'],
            'source' => 'system default',
        ];

        if ($employee === null) {
            return $default;
        }

        try {
            $schedule = $employee->getScheduleForDate(now()->toDateString());
        } catch (\Throwable) {
            return $default;
        }

        return $schedule === null ? $default : [
            'am_in' => substr((string) $schedule->am_in, 0, 5),
            'am_out' => substr((string) $schedule->am_out, 0, 5),
            'pm_in' => substr((string) $schedule->pm_in, 0, 5),
            'pm_out' => substr((string) $schedule->pm_out, 0, 5),
            'source' => 'their own assigned schedule',
        ];
    }

    /**
     * The facts rendered for a model prompt.
     *
     * @param Employee|null $employee When given, the schedule section describes
     *                                this person's own hours rather than the default.
     */
    public function knowledgeBlock(?Employee $employee = null): string
    {
        $f = $this->facts();
        $c = $f['conversion'];
        $a = $f['attendance'];
        $p = $f['payroll'];
        $schedule = $this->scheduleFor($employee);

        $order = implode(' first, then ', $p['deduction_order']);
        $lines = [];

        $lines[] = '=== PRIME HRIS RULES (read from the live system — treat as authoritative) ===';
        $lines[] = '';
        $lines[] = 'TIME CONVERSION (CSC standard):';
        $lines[] = "- {$c['minutes_per_day']} minutes = 1 work day = {$c['hours_per_day']} hours.";
        $lines[] = "- Half a work day is {$c['minutes_per_half_day']} minutes.";
        $lines[] = '';
        $lines[] = 'ATTENDANCE:';
        $lines[] = "- Grace period is {$a['grace_minutes']} minute(s) after AM In and after PM In. "
            . 'Arriving within it is not late.';
        $lines[] = "- Working hours for this user: AM {$schedule['am_in']}–{$schedule['am_out']}, "
            . "PM {$schedule['pm_in']}–{$schedule['pm_out']} ({$schedule['source']}).";
        $lines[] = '- Schedules are per-employee and date-bounded. Never state one employee\'s hours as if '
            . 'they applied to everyone.';
        $lines[] = '- An absence is recorded as attendance_type = \'ABSENT\'. Never infer absence from '
            . 'accredited_hours, which is NULL until payroll computes the day.';
        $lines[] = '';
        $lines[] = 'LATE / UNDERTIME DEDUCTION:';
        $lines[] = "- Late and undertime minutes are converted to days (minutes ÷ {$c['minutes_per_day']}) "
            . "and deducted from {$order}.";
        $lines[] = '- Whatever leave credits cannot cover becomes LWOP (Leave Without Pay).';
        $lines[] = "- LWOP salary deduction = (monthly rate ÷ {$p['working_days_per_month']}) × LWOP days.";
        $lines[] = '- Do NOT compute these figures yourself for a named employee. The system computes them; '
            . 'quote only numbers you were given.';
        $lines[] = '';
        $lines[] = $this->leaveTypeLines();

        return implode("\n", $lines);
    }

    private function leaveTypeLines(): string
    {
        $types = $this->facts()['leave_types'];

        if (empty($types)) {
            return "LEAVE TYPES:\n- Not available from this connection. Do not list leave types from memory.";
        }

        $count = count($types);
        $lines = ["LEAVE TYPES ({$count} active, from leave_types_config):"];

        foreach ($types as $t) {
            $parts = [];

            if ($t['annual_limit'] > 0) {
                $parts[] = rtrim(rtrim(number_format($t['annual_limit'], 2), '0'), '.') . ' day(s)/year';
            }

            if ($t['accrual_per_month'] !== null) {
                $parts[] = 'accrues ' . rtrim(rtrim(number_format($t['accrual_per_month'], 4), '0'), '.') . '/month';
            }

            if ($t['cumulative']) {
                $parts[] = 'carries over';
            }

            if ($t['monetizable']) {
                $parts[] = 'monetizable';
            }

            if ($t['requires_6_months']) {
                $parts[] = 'needs 6 months service';
            }

            if ($t['requires_attachment']) {
                $parts[] = 'attachment required';
            }

            $lines[] = "- {$t['code']} ({$t['name']})" . ($parts ? ': ' . implode(', ', $parts) : '');
        }

        $lines[] = 'That list is complete. If a leave type is not on it, say it is not configured in this '
            . 'system rather than describing it from general knowledge.';

        return implode("\n", $lines);
    }

    /** Minutes past midnight → "HH:MM". */
    private function clockTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
