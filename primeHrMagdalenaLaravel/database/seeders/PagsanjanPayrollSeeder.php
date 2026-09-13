<?php

namespace Database\Seeders;

use App\Models\DailySalaryComputation;
use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\SalaryComputation;
use App\Models\User;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The semi-monthly payslips for the first fifteen personnel on the roster.
 *
 * Every peso here is aggregated from `daily_salary_computations` - the rows
 * `PagsanjanAttendanceSeeder` writes - rather than re-derived from the monthly
 * rate, so a payslip and the DTR behind it cannot disagree about how many days
 * were worked.
 *
 * The sixteen cut-offs run from 2026-01-01 to 2026-08-31 and the register
 * covers 2026-01-01 to 2026-09-11, so all sixteen aggregate real daily figures.
 * The "no register for this period" fallback is kept as a safety net for a
 * window the attendance seed does not reach - a zero basic pay would read as
 * "this person earned nothing" instead of "this period was not encoded" - but
 * the seeded window does not trigger it any more, and `run()` reports the
 * number of periods it had to fall back on so a future window change that
 * starts triggering it is visible rather than silent.
 *
 * `PagsanjanDeductionSeeder` is the authority on *which* deduction amounts an
 * employee actually carries. This seeder only needs a payroll that adds up, so
 * it applies the organisation's standard mandatory shares as the
 * `deduction_types` table defines them.
 */
class PagsanjanPayrollSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** Payslips are for the first fifteen of the roster, in roster order. */
    private const PAYSLIP_EMPLOYEES = 15;

    /**
     * The employee-borne share types, memoised for the run by
     * `employeeDeductionTypes()`.
     *
     * @var Collection<int, DeductionType>|null
     */
    private ?Collection $deductionTypes = null;

    /**
     * The cut-offs, as sentinel rather than strings so a malformed range fails
     * `Carbon::parse()` here rather than halfway through a write.
     *
     * Sixteen semi-monthly cut-offs, 2026-01-01 to 2026-08-31, oldest first:
     * every whole month the register covers, and nothing past the month before
     * the dataset's "today" (2026-09-14), because a cut-off is payroll only once
     * the period it closes has ended. Each month's second cut-off carries that
     * month's real last day - February stops at 28 because 2026 is not a leap
     * year, and April and June at 30 - rather than a typed 31 that would claim a
     * day the month does not have.
     *
     * `status` follows one rule: a cut-off that is closed and paid is `paid`,
     * and the two August cut-offs are `approved`, because the most recent
     * payroll is the one still in flight.
     *
     * @var array<int, array{start: string, end: string, status: string}>
     */
    private const PERIODS = [
        ['start' => '2026-01-01', 'end' => '2026-01-15', 'status' => 'paid'],
        ['start' => '2026-01-16', 'end' => '2026-01-31', 'status' => 'paid'],
        ['start' => '2026-02-01', 'end' => '2026-02-15', 'status' => 'paid'],
        ['start' => '2026-02-16', 'end' => '2026-02-28', 'status' => 'paid'],
        ['start' => '2026-03-01', 'end' => '2026-03-15', 'status' => 'paid'],
        ['start' => '2026-03-16', 'end' => '2026-03-31', 'status' => 'paid'],
        ['start' => '2026-04-01', 'end' => '2026-04-15', 'status' => 'paid'],
        ['start' => '2026-04-16', 'end' => '2026-04-30', 'status' => 'paid'],
        ['start' => '2026-05-01', 'end' => '2026-05-15', 'status' => 'paid'],
        ['start' => '2026-05-16', 'end' => '2026-05-31', 'status' => 'paid'],
        ['start' => '2026-06-01', 'end' => '2026-06-15', 'status' => 'paid'],
        ['start' => '2026-06-16', 'end' => '2026-06-30', 'status' => 'paid'],
        ['start' => '2026-07-01', 'end' => '2026-07-15', 'status' => 'paid'],
        ['start' => '2026-07-16', 'end' => '2026-07-31', 'status' => 'paid'],
        ['start' => '2026-08-01', 'end' => '2026-08-15', 'status' => 'approved'],
        ['start' => '2026-08-16', 'end' => '2026-08-31', 'status' => 'approved'],
    ];

    /**
     * Days between a cut-off's end and payday.
     *
     * The municipal practice is the 20th and the 5th; a fixed offset keeps the
     * payslip's own dates consistent without a holiday calendar this dataset
     * does not have.
     */
    private const PAY_DATE_OFFSET_DAYS = 5;

    public function run(): void
    {
        $this->seedRandomness();

        $employees = array_slice($this->rosterEmployees(), 0, self::PAYSLIP_EMPLOYEES, true);

        if ($employees === []) {
            $this->command->warn('No seeded Pagsanjan employees found - run PagsanjanEmployeeSeeder first.');

            return;
        }

        $employeeIds = array_keys($employees);

        // Resolved once for the whole run: every payslip in one seed should name
        // the same accountant, and `computed_by` is a foreign key into `users`.
        $adminId = User::whereJsonContains('roles', 'admin')->value('id');

        $clear = $this->clearPeriods($employeeIds);

        $written = 0;
        $fallbacks = 0;

        foreach ($employees as $employeeId => $employee) {
            // The rate is a property of the person, not of the cut-off, so it is
            // resolved once per employee rather than once per payslip.
            $rates = $this->periodRates($employee);

            // One read of the employee's whole January-August register, bucketed
            // by cut-off before the loop rather than fetched once per period.
            $dailyRows = $this->dailyRowsByPeriod($employeeId);

            foreach (self::PERIODS as $index => $period) {
                $start = Carbon::parse($period['start']);
                $end = Carbon::parse($period['end']);

                $totals = $this->aggregatePeriod($dailyRows[$index], $start, $end);

                if ($totals['from_register']) {
                    $aggregate = $totals;
                } else {
                    $fallbacks++;

                    $aggregate = $this->fullPeriodFigures($start, $end, $rates);
                }

                $breakdown = $this->deductionBreakdown($aggregate['basic_pay']);
                $otherDeductions = round(array_sum($breakdown), 2);
                $netPay = max(0, round($aggregate['gross_pay'] - $otherDeductions, 2));

                SalaryComputation::create([
                    'employee_id' => $employeeId,
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                    'pay_date' => $end->copy()->addDays(self::PAY_DATE_OFFSET_DAYS)->toDateString(),
                    'payroll_type' => 'semi-monthly',
                    'monthly_rate' => $rates['monthly'],
                    'daily_rate' => $rates['daily'],
                    'hourly_rate' => $rates['hourly'],
                    'total_days_present' => $aggregate['days_present'],
                    'total_days_absent' => $aggregate['days_absent'],
                    'total_hours_worked' => round($aggregate['accredited_minutes'] / 60, 2),
                    'total_accredited_hours' => round($aggregate['accredited_minutes'] / 60, 2),
                    'total_late_minutes' => $aggregate['late_minutes'],
                    'total_undertime_minutes' => 0,
                    'total_ot_minutes' => 0,
                    'basic_pay' => $aggregate['basic_pay'],
                    'ot_pay' => 0.0,
                    'late_deduction' => $aggregate['late_deduction'],
                    'undertime_deduction' => 0.0,
                    'other_deductions' => $otherDeductions,
                    'deduction_breakdown' => $breakdown,
                    'gross_pay' => $aggregate['gross_pay'],
                    'net_pay' => $netPay,
                    'status' => $period['status'],
                    'computed_by' => $adminId,
                    'approved_by' => $adminId,
                    'notes' => $aggregate['notes'],
                ]);

                $written++;
            }
        }

        $this->command->info(sprintf(
            'Pagsanjan payroll: %d payslip(s) written for %d employee(s) across %d cut-off(s); %d period(s) paid at the full daily rate because the register does not cover them; %d stale row(s) cleared.',
            $written,
            count($employeeIds),
            count(self::PERIODS),
            $fallbacks,
            $clear,
        ));
    }

    /**
     * Remove these employees' payslips for these sixteen cut-offs before rewriting.
     *
     * Scoped to the period pairs, not to the employees alone: another seeder may
     * legitimately hold a 13th-month or bonus row for the same person, and
     * clearing those would delete a record this one did not write.
     *
     * @param  array<int, int>  $employeeIds
     */
    private function clearPeriods(array $employeeIds): int
    {
        return SalaryComputation::whereIn('employee_id', $employeeIds)
            ->where(function ($query) {
                foreach (self::PERIODS as $period) {
                    $query->orWhere(function ($inner) use ($period) {
                        $inner->where('period_start', $period['start'])
                            ->where('period_end', $period['end']);
                    });
                }
            })
            ->delete();
    }

    /**
     * The employee's monthly, daily and hourly rate.
     *
     * The monthly rate comes from the designation through the roster trait, which
     * is also what `DailySalaryComputation::computeFromAccreditedLog()` resolves
     * for the daily rows - two different answers would make a payslip's basic
     * pay and its own component rows disagree.
     *
     * @return array{monthly: float, daily: float, hourly: float}
     */
    private function periodRates(Employee $employee): array
    {
        $detail = $this->employmentDetailFor($employee->id);
        $monthly = $detail ? $this->monthlyRateFor((int) $detail->designation_id) : 0.0;

        // A rate of zero has no daily equivalent; dividing by 22 anyway would
        // manufacture a peso figure for somebody the payroll cannot pay.
        $daily = $monthly > 0 ? $monthly / DailySalaryComputation::WORKING_DAYS_PER_MONTH : 0.0;
        $hourly = $daily > 0 ? $daily / 8 : 0.0;

        return [
            'monthly' => round($monthly, 2),
            'daily' => round($daily, 2),
            'hourly' => round($hourly, 2),
        ];
    }

    /**
     * The employee's daily rows for the whole cut-off window, bucketed by cut-off.
     *
     * One query per employee, not one per (employee, cut-off). The daily table
     * holds one row per working day, so an employee's whole January-August
     * register is about 173 rows: cheaper to read once and split in PHP than to
     * issue the sixteen range queries per employee the payslips used to need, and
     * the split uses the same `PERIODS` boundaries the payslips are written with,
     * so a bucketed row cannot land in a period it does not belong to.
     *
     * The minutes ride along on the same read. Summing them row by row is the
     * same total the per-period `SUM(accredited_hours_log.total_accredited_minutes)`
     * produced, because `SUM()` skips NULL: a daily row whose log row is missing
     * contributes no minutes while still counting as a day present or absent,
     * which is what the old pair of queries did as well.
     *
     * @return array<int, array<int, object>> keyed by PERIODS index
     */
    private function dailyRowsByPeriod(int $employeeId): array
    {
        [$windowStart, $windowEnd] = $this->periodWindow();

        $rows = DB::table('daily_salary_computations as d')
            ->leftJoin('accredited_hours_log as l', 'l.id', '=', 'd.accredited_hours_log_id')
            ->where('d.employee_id', $employeeId)
            ->whereBetween('d.work_date', [$windowStart, $windowEnd])
            ->orderBy('d.work_date')
            ->get([
                'd.work_date',
                'd.daily_basic_pay',
                'd.late_deduction',
                'd.daily_gross_pay',
                'l.total_accredited_minutes as accredited_minutes',
                'l.late_minutes as late_minutes',
            ]);

        $buckets = array_fill(0, count(self::PERIODS), []);

        foreach ($rows as $row) {
            foreach (self::PERIODS as $index => $period) {
                if ($row->work_date >= $period['start'] && $row->work_date <= $period['end']) {
                    $buckets[$index][] = $row;

                    break;
                }
            }
        }

        return $buckets;
    }

    /**
     * The span every cut-off sits inside, as a [first start, last end] pair.
     *
     * `PERIODS` is chronological and contiguous, so those two boundaries are all
     * the batched register read needs; deriving them keeps a widened cut-off list
     * from silently leaving its last period outside the window it reads.
     *
     * @return array{0: string, 1: string}
     */
    private function periodWindow(): array
    {
        return [self::PERIODS[0]['start'], self::PERIODS[array_key_last(self::PERIODS)]['end']];
    }

    /**
     * The employee's real figures for a cut-off, from that cut-off's daily rows.
     *
     * The minutes come from the *same* daily rows the pesos come from, not from
     * `attendance` by date: a period's "days present" and its "accredited hours"
     * then describe the same set of rows and cannot disagree about which days
     * were counted.
     *
     * @param  array<int, object>  $rows  the cut-off's slice of `dailyRowsByPeriod()`
     * @return array<string, mixed>
     */
    private function aggregatePeriod(array $rows, Carbon $start, Carbon $end): array
    {
        if ($rows === []) {
            return ['from_register' => false];
        }

        $daysPresent = 0;
        $daysAbsent = 0;
        $basicPay = 0.0;
        $lateDeduction = 0.0;
        $grossPay = 0.0;
        $accreditedMinutes = 0;
        $lateMinutes = 0;

        foreach ($rows as $row) {
            if ((float) $row->daily_basic_pay > 0) {
                $daysPresent++;
            } else {
                $daysAbsent++;
            }

            $basicPay += (float) $row->daily_basic_pay;
            $lateDeduction += (float) $row->late_deduction;
            $grossPay += (float) $row->daily_gross_pay;
            $accreditedMinutes += (int) $row->accredited_minutes;
            $lateMinutes += (int) $row->late_minutes;
        }

        return [
            'from_register' => true,
            'days_present' => $daysPresent,
            'days_absent' => $daysAbsent,
            // `total_late_minutes` is a smallint unsigned; a sum past 65535 is
            // not a cut-off's worth of lateness, it is a corrupt row.
            'accredited_minutes' => $accreditedMinutes,
            'late_minutes' => min($lateMinutes, 65535),
            'basic_pay' => round($basicPay, 2),
            'late_deduction' => round($lateDeduction, 2),
            'gross_pay' => round($grossPay, 2),
            'notes' => sprintf(
                'Aggregated from %d daily salary computation(s) covering %s to %s.',
                count($rows),
                $start->toDateString(),
                $end->toDateString(),
            ),
        ];
    }

    /**
     * A cut-off with no daily rows behind it, paid at the full daily rate.
     *
     * A safety net the seeded window does not reach: the attendance seeder writes
     * 2026-01-01 to 2026-09-11 and these cut-offs stop at 2026-08-31, so every
     * payslip aggregates a real register. It stays because the alternative for a
     * period nobody encoded is a payslip of zeros, which understates the month's
     * payroll and reads as an absence that never happened; the period's own
     * working days are used instead and the `notes` column says the figure is a
     * rate rather than a register.
     *
     * @param  array{monthly: float, daily: float, hourly: float}  $rates
     * @return array<string, mixed>
     */
    private function fullPeriodFigures(Carbon $start, Carbon $end, array $rates): array
    {
        $workingDays = $this->workingDaysBetween($start, $end);
        $basicPay = round($workingDays * $rates['daily'], 2);

        return [
            'from_register' => false,
            'days_present' => $workingDays,
            'days_absent' => 0,
            'accredited_minutes' => $workingDays * 480,
            'late_minutes' => 0,
            'basic_pay' => $basicPay,
            'late_deduction' => 0.0,
            'gross_pay' => $basicPay,
            'notes' => sprintf(
                'No attendance register for %s to %s: paid at the full daily rate of PHP %s for %d working day(s).',
                $start->toDateString(),
                $end->toDateString(),
                number_format($rates['daily'], 2),
                $workingDays,
            ),
        ];
    }

    /** Mondays to Fridays between two dates, both ends inclusive. */
    private function workingDaysBetween(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $date = $start->copy()->startOfDay();
        $last = $end->copy()->startOfDay();

        while ($date->lte($last)) {
            if (! $date->isWeekend()) {
                $count++;
            }

            $date->addDay();
        }

        return $count;
    }

    /**
     * The employee-borne deductions for a cut-off, as `code => amount`.
     *
     * The rates are read from `deduction_types` rather than written here: the
     * table is the payroll's configuration and already states PhilHealth's
     * 2.5%, GSIS's 9%, Pag-IBIG's 2% and the GSIS state insurance amount. A
     * percentage typed into this file would be a second copy of a rate that
     * Settings can already change, and the two would drift.
     *
     * A loan type has no percentage and no amount in that table (the amount is
     * per employee, in `employee_deductions`), so it contributes nothing until
     * `PagsanjanDeductionSeeder` writes the real amounts.
     *
     * @return array<string, float>
     */
    private function deductionBreakdown(float $basicPay): array
    {
        $breakdown = [];

        foreach ($this->employeeDeductionTypes() as $type) {
            $amount = match ($type->computation_type) {
                'PERCENTAGE' => $basicPay * ((float) $type->percentage_rate / 100),
                'FIXED' => (float) ($type->max_amount ?? 0),
                default => 0.0,
            };

            // `max_amount` is the ceiling as well as the fixed amount, depending
            // on the type; applying it as a cap is only meaningful for a
            // percentage share.
            if ($type->computation_type === 'PERCENTAGE' && $type->max_amount !== null) {
                $amount = min($amount, (float) $type->max_amount);
            }

            $amount = round($amount, 2);

            if ($amount > 0) {
                $breakdown[$type->code] = $amount;
            }
        }

        return $breakdown;
    }

    /**
     * The employee-borne share types, read once and reused for every payslip.
     *
     * `deduction_types` is configuration, not payroll data: it does not change
     * while the seed runs, so re-reading it for each of the 240 payslips would be
     * 240 identical round trips that return the same rows. The memo lives on the
     * seeder instance, so it lasts exactly as long as one run; the next run gets
     * a fresh read.
     *
     * @return Collection<int, DeductionType>
     */
    private function employeeDeductionTypes(): Collection
    {
        return $this->deductionTypes ??= DeductionType::where('deducted_from_employee', true)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }
}
