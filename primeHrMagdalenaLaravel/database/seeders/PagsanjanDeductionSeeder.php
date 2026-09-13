<?php

namespace Database\Seeders;

use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use Carbon\CarbonImmutable;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Payroll deduction assignments for five of the Pagsanjan personnel.
 *
 * The subjects are the first five *permanent* employees in roster order. Job
 * Orders are skipped on purpose: they are paid out of a different fund by the
 * day and carry no plantilla loan or government share, so an MPL instalment on a
 * job order's file would be a withholding the payroll office never makes. The
 * skip is written as a rule rather than as an "index 0–4" constant so that a
 * roster edit which moves somebody into the top five cannot quietly put a Job
 * Order on the plantilla half of the deduction screen.
 *
 * Nothing here writes `deduction_schedules`. The type's own default schedule is
 * already in that table (`DeductionTypesSeeder` owns it) and duplicating it per
 * employee would make the Schedules tab show two answers to one question. Where
 * an office withholds differently for one member, that goes on the assignment's
 * `custom_cutoff_schedule`, which `PayrollRegisterService::cutoffScheduleFor()`
 * documents as the employee's own override winning over the type default.
 *
 * Every figure is either read from `deduction_types` or derived from the
 * employee's own `designations.monthly_rate`; the only literal amounts are the
 * loan principals built from a drawn instalment, and they are stated as such.
 */
class PagsanjanDeductionSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** How many employees carry assignments in this dataset. */
    private const SUBJECT_COUNT = 5;

    /**
     * The payroll cut-off the loan balances below are stated as of.
     *
     * A `remaining_balance` is only meaningful next to a date — it is the
     * principal less everything withheld so far — and the rest of this dataset
     * is stated as of the same day. Fixing it keeps a re-run from moving every
     * balance a little further down the amortisation schedule.
     */
    private const AS_OF = '2026-09-14';

    /** Payroll runs semi-monthly, so a month of a loan term is two cut-offs. */
    private const CUTOFFS_PER_MONTH = 2;

    /** Days between two cut-off dates, used to count instalments already taken. */
    private const CUTOFF_DAYS = 15;

    /**
     * The month the one suspended loan stopped being withheld.
     *
     * A paused loan's balance is only honest if it is counted to the month the
     * pause began. Counting it to `AS_OF` like a running loan would show the
     * balance a loan that had kept paying would have, which is a figure that
     * contradicts the reason recorded beside it — the member's payroll was
     * stopped, so no instalment was taken after that month.
     */
    private const SUSPENDED_FROM = '2026-06-01';

    /**
     * The latest month any loan in this dataset starts in.
     *
     * A loan that starts later than June and runs the shortest term would still
     * be open into the following year, which is fine, but it would also have too
     * few instalments behind it for a balance to read as a loan in progress
     * rather than one just granted.
     */
    private const LATEST_START_MONTH = 6;

    /**
     * The amount a FIXED mandatory type falls back to when its row states none.
     *
     * `max_amount` is where a fixed share keeps its figure (GSIS-SI is ₱100.00),
     * but the column is nullable and `PayrollRegisterService` reads a FIXED
     * mandatory from `percentage_rate` first and the assignment's own `amount`
     * second. Writing ₱0.00 for a missing figure would silently exempt the
     * member, so the seeder states the figure instead and this constant names it.
     */
    private const FIXED_FALLBACK_AMOUNT = 100.00;

    /**
     * The rate base when `designations.monthly_rate` cannot be read.
     *
     * `monthlyRateFor()` already falls back to the Job Order monthly equivalent,
     * so this only covers a designation row that has gone missing entirely. A
     * percentage of zero is not a smaller deduction, it is no deduction at all,
     * and a mandatory share that reads ₱0.00 on a payslip looks like a bug in
     * the payroll run rather than in the seeder.
     */
    private const RATE_FALLBACK = 14308.00;

    /**
     * Assignments per subject, in roster order.
     *
     * `GSIS PS` is not listed: every plantilla member pays it and the seeder
     * adds it to all five, which is what guarantees the mandatory side of the
     * mixture. The rest is a fixed plan rather than a draw because the three
     * states the UI has to be able to show — `ACTIVE`, `COMPLETED` and
     * `SUSPENDED` — have to land somewhere specific, and a draw that happened to
     * place the one completed loan on a different person each run would be a
     * different dataset, not a differently seeded one.
     *
     * @var array<int, array{mandatory: array<int, string>, loans: array<int, array{code: string, status: string, cutoff: string}>}>
     */
    private const PLAN = [
        // Executive Asst. II, Office of the Mayor — one small loan, fully repaid.
        [
            'mandatory' => ['PhilHeath PS'],
            'loans' => [['code' => 'LOAN_gsis EL', 'status' => 'COMPLETED', 'cutoff' => 'BOTH_SPLIT']],
        ],
        // Private Sec. II, Office of the Mayor — mandatory shares only.
        [
            'mandatory' => ['PAG-IBIG PS'],
            'loans' => [],
        ],
        // Admin. Aide IV, Office of the Mayor — the assignment the office has stopped taking.
        [
            'mandatory' => ['PhilHeath PS', 'GSIS-SI'],
            'loans' => [['code' => 'LOAN_MPL', 'status' => 'SUSPENDED', 'cutoff' => '1ST_ONLY']],
        ],
        // Admin. Aide IV, Human Resources Management Office — mandatory shares only.
        [
            'mandatory' => ['PAG-IBIG PS', 'GSIS-SI'],
            'loans' => [],
        ],
        // Admin. Aide IV, Human Resources Management Office — two live loans, withheld differently.
        [
            'mandatory' => ['PhilHeath PS'],
            'loans' => [
                ['code' => 'LOAN_gsis EL', 'status' => 'ACTIVE', 'cutoff' => 'BOTH_SPLIT'],
                ['code' => 'LOAN_MPL', 'status' => 'ACTIVE', 'cutoff' => '2ND_ONLY'],
            ],
        ],
    ];

    public function run(): void
    {
        $this->seedRandomness();

        $roster = $this->rosterEmployees();

        if ($roster === []) {
            $this->command->warn('No Pagsanjan roster employees found — run PagsanjanEmployeeSeeder first.');

            return;
        }

        $subjects = $this->subjects();
        $types = $this->typesByCode();

        // Idempotence, committed ahead of the writes for the same reason
        // PagsanjanEmployeeSeeder commits its own: the rows replaced here are the
        // rows a re-run has to be able to see gone before it inserts their
        // successors. It prunes the whole roster, not just this run's five
        // subjects, because a roster edit that moved somebody out of the top five
        // would otherwise leave their old assignments behind as rows no seeder
        // owns.
        DB::transaction(function () use ($roster) {
            EmployeeDeduction::whereIn('employee_id', array_keys($roster))->delete();
        });

        $assignments = 0;
        $employees = 0;
        $byStatus = ['ACTIVE' => 0, 'COMPLETED' => 0, 'SUSPENDED' => 0];
        $byCategory = ['MANDATORY' => 0, 'LOAN' => 0];

        DB::transaction(function () use ($subjects, $types, &$assignments, &$employees, &$byStatus, &$byCategory) {
            // `$subjects` is keyed by employee id, so the plan — which is written
            // in roster order — is indexed by a counter rather than by that key.
            $position = 0;

            foreach ($subjects as $employee) {
                $plan = self::PLAN[$position];
                $detail = $this->employmentDetailFor($employee->id);
                $monthlyRate = $this->monthlyRate($detail?->designation_id);

                $rows = [
                    ['type' => $this->requireType($types, 'GSIS PS'), 'kind' => 'mandatory'],
                ];

                foreach ($plan['mandatory'] as $code) {
                    $rows[] = ['type' => $this->requireType($types, $code), 'kind' => 'mandatory'];
                }

                foreach ($plan['loans'] as $loan) {
                    $rows[] = ['type' => $this->requireType($types, $loan['code']), 'kind' => 'loan'] + $loan;
                }

                foreach ($rows as $row) {
                    $written = $row['kind'] === 'mandatory'
                        ? $this->writeMandatory($employee, $row['type'], $monthlyRate)
                        : $this->writeLoan($employee, $row['type'], $monthlyRate, $row['status'], $row['cutoff']);

                    $byStatus[$written->status]++;
                    $byCategory[$row['type']->category]++;
                    $assignments++;
                }

                $employees++;
                $position++;
            }
        });

        $this->command->info(sprintf(
            'Pagsanjan deductions: %d assignments for %d employees (%d mandatory, %d loan; %d active, %d completed, %d suspended).',
            $assignments,
            $employees,
            $byCategory['MANDATORY'],
            $byCategory['LOAN'],
            $byStatus['ACTIVE'],
            $byStatus['COMPLETED'],
            $byStatus['SUSPENDED']
        ));

        if ($employees < self::SUBJECT_COUNT) {
            $this->command->warn(sprintf(
                'Only %d permanent employee(s) with a designation were available — expected %d.',
                $employees,
                self::SUBJECT_COUNT
            ));
        }
    }

    /**
     * The employees the assignments are written for: the first five in roster
     * order who are not Job Orders and whose employment detail names a
     * designation.
     *
     * A designation is required because every mandatory amount is a percentage
     * of that row's `monthly_rate`; an employee without one cannot be given a
     * figure that is not invented, so they are passed over rather than given the
     * fallback rate.
     *
     * @return array<int, Employee> keyed by employees.id
     */
    private function subjects(): array
    {
        $subjects = [];

        foreach ($this->rosterEmployees() as $employee) {
            $detail = $this->employmentDetailFor($employee->id);

            if ($detail?->employment_status === 'Job Order' || $detail?->designation_id === null) {
                continue;
            }

            $subjects[$employee->id] = $employee;

            if (count($subjects) === self::SUBJECT_COUNT) {
                break;
            }
        }

        return $subjects;
    }

    /**
     * The active types this seeder may assign, keyed by code.
     *
     * Read from the table rather than hard-coded: `percentage_rate` and
     * `max_amount` are the vocabulary of the deduction screen, and a seeder that
     * carried its own copy of them would keep withholding last year's rate after
     * somebody edited the real one. `deducted_from_employee` is the filter the
     * payroll register applies, so the government shares are not assignable here.
     *
     * @return array<string, DeductionType>
     */
    private function typesByCode(): array
    {
        return DeductionType::where('is_active', true)
            ->where('deducted_from_employee', true)
            ->get()
            ->keyBy('code')
            ->all();
    }

    /** A type the plan names, or a failure that says which seeder to run. */
    private function requireType(array $types, string $code): DeductionType
    {
        if (! isset($types[$code])) {
            throw new \RuntimeException(
                "No active deduction type [{$code}] borne by the employee — run DeductionTypesSeeder " .
                'and UpdateDeductionTypesSeeder first.'
            );
        }

        return $types[$code];
    }

    /** The monthly rate a mandatory share is a percentage of. */
    private function monthlyRate(?int $designationId): float
    {
        if ($designationId === null) {
            return self::RATE_FALLBACK;
        }

        $rate = $this->monthlyRateFor($designationId);

        return $rate > 0 ? $rate : self::RATE_FALLBACK;
    }

    /**
     * A government share or fixed contribution.
     *
     * A percentage type is a share of the member's own monthly rate. The types on
     * this table all carry `base_salary_type = NULL`, which names no base at all,
     * and the monthly rate is the base the payroll register falls back to for a
     * `MONTHLY` type — so the stored amount is the whole monthly share, not half
     * of one. Which cut-off actually withholds it is the type's schedule row
     * (`GSIS PS` and `PhilHeath PS` on the first, `PAG-IBIG PS` on the second),
     * and the assignment deliberately leaves `custom_cutoff_schedule` null so
     * that default keeps applying.
     *
     * `total_amount` and `remaining_balance` stay null: a share is not a loan and
     * has no principal to run down. `installment_amount` repeats `amount`
     * because the screens read the instalment column to describe what a cut-off
     * withholds, and a null there would read as nothing being withheld.
     */
    private function writeMandatory(Employee $employee, DeductionType $type, float $monthlyRate): EmployeeDeduction
    {
        $amount = match ($type->computation_type) {
            'PERCENTAGE' => $this->percentageShare($type, $monthlyRate),
            default => $this->fixedShare($type),
        };

        return EmployeeDeduction::create([
            'employee_id' => $employee->id,
            'deduction_type_id' => $type->id,
            'amount' => $amount,
            'start_date' => '2026-01-01',
            'end_date' => null,
            'remaining_balance' => null,
            'total_amount' => null,
            'installment_amount' => $amount,
            'status' => 'ACTIVE',
            'custom_cutoff_schedule' => null,
            'remarks' => null,
        ]);
    }

    /**
     * A percentage of the member's monthly rate, capped when the type states a cap.
     *
     * The cap is applied to the amount rather than to a rate so that a type whose
     * `max_amount` is set (PhilHealth's ₱5,000 ceiling behaves this way in the
     * real schedule) withholds the ceiling and not a figure above it.
     */
    private function percentageShare(DeductionType $type, float $monthlyRate): float
    {
        $rate = (float) ($type->percentage_rate ?? 0);

        if ($rate <= 0) {
            return self::FIXED_FALLBACK_AMOUNT;
        }

        $amount = round($monthlyRate * $rate / 100, 2);

        if ($type->max_amount !== null) {
            $amount = min($amount, round((float) $type->max_amount, 2));
        }

        return $amount;
    }

    /** A fixed contribution, taken from the figure the type's own row states. */
    private function fixedShare(DeductionType $type): float
    {
        $stated = (float) ($type->max_amount ?? 0);

        if ($stated <= 0) {
            // Read second because `PayrollRegisterService` reads a FIXED
            // mandatory from `percentage_rate` — the column is misnamed for that
            // computation type, not the value.
            $stated = (float) ($type->percentage_rate ?? 0);
        }

        return $stated > 0 ? round($stated, 2) : self::FIXED_FALLBACK_AMOUNT;
    }

    /**
     * A loan assignment: principal, per-cut-off instalment and what is still owed.
     *
     * The instalment is drawn as a percentage of the member's own monthly rate
     * rather than as a round figure, because that is how a loan is actually
     * granted — the agency nets the amortisation against the pay it is taken from
     * and refuses a loan whose instalment would leave too little. Three to six
     * per cent of the monthly rate per cut-off keeps four loans and three
     * mandatory shares inside one month's salary, which is what the payroll
     * register has to be able to pay.
     *
     * A completed loan is the exception and is built the other way round. It
     * cannot still be running, so its term is fitted inside the first half of
     * 2026 — twelve cut-offs from January to June, all of them behind the date
     * the rest of this dataset is stated as of — and its balance is zero.
     *
     * A suspended loan stops counting at the month the pause began, not at
     * `AS_OF`: its balance has to agree with the reason written beside it.
     */
    private function writeLoan(
        Employee $employee,
        DeductionType $type,
        float $monthlyRate,
        string $status,
        string $cutoff
    ): EmployeeDeduction {
        $installment = $this->amortisation($monthlyRate);

        if ($status === 'COMPLETED') {
            $cutoffs = 6 * self::CUTOFFS_PER_MONTH;
            $total = round($installment * $cutoffs, 2);

            return EmployeeDeduction::create([
                'employee_id' => $employee->id,
                'deduction_type_id' => $type->id,
                // Nothing is currently withheld, so the amount is the instalment
                // the loan was paid off at — the figure the last payslip it
                // appeared on carried.
                'amount' => $installment,
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'remaining_balance' => 0.00,
                'total_amount' => $total,
                'installment_amount' => $installment,
                'status' => 'COMPLETED',
                'custom_cutoff_schedule' => $cutoff,
                'remarks' => sprintf('%s fully paid in June 2026 over a six-month term.', $type->name),
            ]);
        }

        $suspended = $status === 'SUSPENDED';
        $start = $this->loanStartDate($suspended ? self::SUSPENDED_FROM : self::AS_OF);
        $termMonths = $this->draw(12, 24);
        $cutoffs = $termMonths * self::CUTOFFS_PER_MONTH;
        $total = round($installment * $cutoffs, 2);

        // What has been withheld since the loan started — up to the pause for a
        // suspended loan, up to the cut-off the dataset is stated as of for a
        // running one. Counting cut-offs that have begun, not instalments that
        // have cleared, is what keeps the balance a figure the loan officer
        // would recognise.
        $countedTo = $suspended ? self::SUSPENDED_FROM : self::AS_OF;
        $elapsed = (int) floor($start->diffInDays(CarbonImmutable::parse($countedTo)) / self::CUTOFF_DAYS);
        $remaining = round($total - min($cutoffs, max(0, $elapsed)) * $installment, 2);
        $remaining = max(0.0, min($total, $remaining));

        return EmployeeDeduction::create([
            'employee_id' => $employee->id,
            'deduction_type_id' => $type->id,
            'amount' => $installment,
            'start_date' => $start->toDateString(),
            // The month the last instalment falls in, less the day the term was
            // counted from inside it.
            'end_date' => $start->addMonths($termMonths)->subDay()->toDateString(),
            'remaining_balance' => $remaining,
            'total_amount' => $total,
            'installment_amount' => $installment,
            'status' => $status,
            'custom_cutoff_schedule' => $cutoff,
            'remarks' => $suspended
                ? sprintf(
                    '%s suspended as of %s after the member went on leave without pay for a month; the instalment resumes with the payroll after their return.',
                    $type->name,
                    CarbonImmutable::parse(self::SUSPENDED_FROM)->format('F Y')
                )
                : sprintf(
                    '%s, %d cut-offs at %s per cut-off from %s.',
                    $type->name,
                    $cutoffs,
                    number_format($installment, 2),
                    $start->format('F Y')
                ),
        ]);
    }

    /**
     * The per-cut-off instalment: three to six per cent of the monthly rate.
     *
     * Drawn rather than fixed so five members do not all carry the same
     * amortisation, and rounded to centavos because that is the unit the column
     * stores. A member whose rate cannot be read at all falls back to the stated
     * figure the same way a mandatory share does.
     */
    private function amortisation(float $monthlyRate): float
    {
        $share = $this->draw(3, 6);

        return round($monthlyRate > 0 ? $monthlyRate * $share / 100 : self::FIXED_FALLBACK_AMOUNT, 2);
    }

    /**
     * A loan that is still running starts on a payday in the first half of 2026.
     *
     * The first or the sixteenth is a cut-off date, so an instalment that started
     * there lines up with the payroll that would have taken it — a loan dated the
     * third of the month would show an amortisation schedule no payroll run
     * matches.
     *
     * The month is drawn from those that leave at least two cut-offs between the
     * start and the date the balance is counted from, so a suspended loan has an
     * instalment or two behind it and a running one is never a loan that has
     * taken nothing while claiming a reduced balance.
     */
    private function loanStartDate(string $countedTo): CarbonImmutable
    {
        $latest = min(self::LATEST_START_MONTH, max(1, (int) CarbonImmutable::parse($countedTo)->format('n') - 2));

        return CarbonImmutable::create(2026, $this->draw(1, $latest), $this->draw(1, 2) === 1 ? 1 : 16);
    }
}
