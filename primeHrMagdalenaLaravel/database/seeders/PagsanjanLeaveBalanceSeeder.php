<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Current leave balances for the thirty Pagsanjan personnel.
 *
 * Every row is written under `year = 2026`, but nothing here reads a balance
 * back through `where('year', now()->year)` — `LeaveBalance::currentFor()` is
 * the only definition of "current" in this system (the latest row per
 * `leave_code`, whatever year it carries), and a seeder that filtered on the
 * calendar year would report these thirty people as having no credits at all
 * the moment the year rolled over.
 *
 * Job Orders are the case worth reading twice. They are paid by the day and do
 * not earn plantilla leave, so a full fifteen days of vacation leave on a job
 * order's file is a statement a screener would have to un-learn. They get the
 * same credit codes as everybody else, all at zero, so the shape of their
 * balance screen matches a permanent employee's while the figures tell the
 * truth. The five types the application seeder files against are always
 * created, including for them, so no application can reference a balance row
 * that does not exist.
 */
class PagsanjanLeaveBalanceSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** The year every seeded balance belongs to. */
    private const BALANCE_YEAR = 2026;

    /** VL and SL are earned monthly; a full year is 1.25 × 12 = 15 days. */
    private const MONTHLY_ACCRUAL = 1.25;

    /** The account whose name sits on every seeded credit movement. */
    private const ADMIN_FALLBACK_USER_ID = 1;

    /** Resolved once: the credits below are all written by the same administrator. */
    private ?int $adminUserId = null;

    /**
     * The non-accrued credit types the application seeder draws from.
     *
     * `SEL` is deliberately left at zero: `leave_types_config` carries its
     * `annual_limit` as 0.00, and inventing five days for a type whose own
     * configuration says it grants none would make the balance disagree with
     * the leave type it is a balance of. The row is still written so the
     * application seeder finds a balance rather than a missing row.
     */
    private const FIXED_TYPES = ['FL', 'SPL', 'WL', 'BL', 'SEL'];

    /**
     * Last year's unused remainder, drawn per employee and applied to both
     * accrued codes.
     *
     * The spread is whole days on purpose. A from-scratch computation gives all
     * thirty people exactly 15.000000, and a roster page where every row reads
     * the same figure looks generated; two days either side is what an office
     * that does not force its staff to consume every credit actually holds.
     */
    private const CARRY_OVER_OPTIONS = [0.0, 1.0, 2.0];

    public function run(): void
    {
        $this->seedRandomness();

        $employees = $this->rosterEmployees();
        $accrualByCode = $this->accrualRateByCode();

        if ($employees === []) {
            $this->command->warn('No Pagsanjan roster employees found — run PagsanjanEmployeeSeeder first.');

            return;
        }

        $timestamp = now();

        // Idempotence. The delete runs committed, ahead of the writes, for the
        // same reason PagsanjanEmployeeSeeder commits its own: MySQL checks
        // `leave_balances_employee_id_leave_code_year_unique` as each row is
        // written, so an uncommitted predecessor left over from the previous run
        // would reject its own replacement.
        //
        // Balances are not left for the application seeder to clean up because
        // that seeder moves credits *downward* from these figures — it can undo
        // a debit, but it cannot know what the row looked like before the debit.
        // Resetting here is what makes the pair re-runnable.
        DB::transaction(function () use ($employees) {
            $ids = array_keys($employees);

            LeaveBalance::whereIn('employee_id', $ids)->delete();
            LeaveTransaction::whereIn('employee_id', $ids)
                ->where('reference_type', 'initialization')
                ->delete();
        });

        $balances = 0;
        $zeroed = 0;

        DB::transaction(function () use ($employees, $accrualByCode, $timestamp, &$balances, &$zeroed) {
            foreach ($employees as $employee) {
                $isJobOrder = $this->employmentDetailFor($employee->id)?->employment_status === 'Job Order';
                $allocations = $this->allocationsFor($isJobOrder, $accrualByCode);

                if ($isJobOrder) {
                    $zeroed++;
                }

                foreach ($allocations as $leaveCode => $figures) {
                    $this->writeBalance($employee, $leaveCode, $figures, $isJobOrder, $timestamp);
                    $balances++;
                }
            }
        });

        $this->command->info(sprintf(
            'Pagsanjan leave balances: %d rows for %d employees (%d Job Order, all credits zero); %d initialization transactions.',
            $balances,
            count($employees),
            $zeroed,
            $balances
        ));
    }

    /**
     * The figure each credit code starts the seeded year at.
     *
     * `total_credits` is the whole entitlement the employee holds for the year —
     * fifteen accrued days plus whatever last year left unspent — and
     * `carried_over` is written as zero.
     *
     * Splitting the two was the bug. The leave pages, the AI assistant and the
     * ledger all read
     * `available = total_credits + carried_over - used_credits - pending_credits`,
     * so an earlier version that wrote `total = 15, carried = 2` stored the same
     * two days in two columns at once and reported a seventeen-day balance
     * against a fifteen-day entitlement. Folding the carry into `total` keeps
     * one figure authoritative: the entitlement is seventeen days, earned in
     * part last year and in part this year. `carried_over` would be the column
     * to populate if an accrual were ever posted here after January.
     *
     * Nothing has been spent yet — the application seeder is what moves credits
     * out of these rows — so `used_credits` and `pending_credits` start at zero
     * and `available_credits` is the full entitlement.
     *
     * @return array<string, array{total: float, carried: float}>
     */
    private function allocationsFor(bool $isJobOrder, array $accrualByCode): array
    {
        if ($isJobOrder) {
            return array_fill_keys($this->creditCodes($accrualByCode), ['total' => 0.0, 'carried' => 0.0]);
        }

        $accruedTotal = round(self::MONTHLY_ACCRUAL * 12, 6);
        $accruedCarried = self::CARRY_OVER_OPTIONS[$this->draw(0, count(self::CARRY_OVER_OPTIONS) - 1)];

        $allocations = [];

        foreach (array_keys($accrualByCode) as $leaveCode) {
            $allocations[$leaveCode] = [
                'total' => round($accruedTotal + $accruedCarried, 6),
                'carried' => 0.0,
            ];
        }

        // The rest are a whole-year allocation rather than an accrual. `SPL`
        // earns a month for every ten months of service, so its three days are
        // written as a three-day entitlement.
        $fixed = ['FL' => 5.0, 'SPL' => 3.0, 'WL' => 5.0, 'BL' => 3.0, 'SEL' => 0.0];

        foreach ($fixed as $leaveCode => $limit) {
            $allocations[$leaveCode] = ['total' => round($limit, 6), 'carried' => 0.0];
        }

        return $allocations;
    }

    /**
     * Insert one balance row and its matching `initialization` credit.
     *
     * The transaction is written so the credits a screen shows can be traced
     * back to where they came from: `balance_before = 0` because this is the
     * first movement the employee's ledger has, and `balance_after` is the
     * available figure on the row, so the pair always reconciles.
     */
    private function writeBalance(
        Employee $employee,
        string $leaveCode,
        array $figures,
        bool $isJobOrder,
        $timestamp
    ): void {
        $total = round($figures['total'], 6);
        $carried = round($figures['carried'], 6);

        // `total` already contains the carried days (see allocationsFor), and
        // nothing has been spent yet, so the available figure is the entitlement
        // itself. `carried_over` is provenance, not a second helping of days:
        // this row is the baseline the ledger, the leave pages and the AI
        // assistant all read `available = total + carried_over - used - pending`
        // from, and adding the carry-over twice would make the balance read two
        // days richer than the entitlement behind it.
        $available = $total;

        LeaveBalance::create([
            'employee_id' => $employee->id,
            'leave_code' => $leaveCode,
            'year' => self::BALANCE_YEAR,
            'total_credits' => $total,
            'used_credits' => 0,
            'pending_credits' => 0,
            'available_credits' => $available,
            'carried_over' => $carried,
        ]);

        LeaveTransaction::create([
            'employee_id' => $employee->id,
            'leave_code' => $leaveCode,
            'year' => self::BALANCE_YEAR,
            'transaction_type' => 'credit',
            'amount' => $available,
            'balance_before' => 0,
            'balance_after' => $available,
            'reference_type' => 'initialization',
            'reference_id' => null,
            'transaction_date' => $timestamp->toDateString(),
            'processed_by' => $this->adminUserId(),
            'remarks' => $isJobOrder
                ? 'Job Order personnel are paid by the day and earn no plantilla leave credits.'
                : 'Initial leave balance for ' . self::BALANCE_YEAR . '.',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    /**
     * Accrual rate per month, keyed by leave code, for the types that accrue.
     *
     * Read from `leave_accrual_rates` rather than hard-coded: the rate is a
     * configuration row, and VL and SL are only both 1.25 this year. The
     * monthly figure is what a balance is built from, and `× 12` is the year
     * that produces.
     *
     * Keyed in a fixed order on purpose. The caller draws its carry-over once
     * per employee and then walks these codes, so a code order that changed
     * with the table's physical row order would hand out a different carry-over
     * to the same person on the next run.
     *
     * @return array<string, float>
     */
    private function accrualRateByCode(): array
    {
        $rates = [];

        foreach (LeaveType::where('is_accrued', true)->where('is_active', true)->get() as $leaveType) {
            $perMonth = (float) DB::table('leave_accrual_rates')
                ->where('leave_type_id', $leaveType->id)
                ->where('is_active', 1)
                ->orderByDesc('effective_date')
                ->value('credits_earned_per_period');

            $rates[$leaveType->leave_code] = $perMonth > 0 ? $perMonth : self::MONTHLY_ACCRUAL;
        }

        ksort($rates);

        return $rates;
    }

    /** The administrator a seeded credit is attributed to, resolved once. */
    private function adminUserId(): int
    {
        return $this->adminUserId ??= (int) (User::whereJsonContains('roles', 'admin')->value('id')
            ?? self::ADMIN_FALLBACK_USER_ID);
    }

    /**
     * Every credit code this pair of seeders writes: the accrued ones the
     * accrual table names, plus the fixed-allocation types filed against.
     *
     * @return array<int, string>
     */
    private function creditCodes(array $accrualByCode): array
    {
        return array_values(array_unique(array_merge(array_keys($accrualByCode), self::FIXED_TYPES)));
    }
}
