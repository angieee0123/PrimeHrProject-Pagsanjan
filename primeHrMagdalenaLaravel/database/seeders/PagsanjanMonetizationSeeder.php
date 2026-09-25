<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\MonetizationRequest;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;

/**
 * Two monetization requests — one approved, one pending — for the first two
 * *permanent* personnel of the Pagsanjan roster.
 *
 * Permanent employees only, because a Job Order has no leave credits to
 * monetize: the five Job Orders in this dataset hold no plantilla leave balance
 * and `employment_status` is where that fact lives.
 *
 * **Only VL is monetized.** `leave_types_config.MLC` states the office's rule —
 * "Maximum 10 days of VL credits can be monetized annually" — so the ten-day cap
 * is read from the leave type rather than invented, and `sl_days` stays 0
 * because sick leave credits are not converted except at separation, which is
 * not what either of these requests is.
 *
 * The balance rows are written by the leave seeders that run before this one in
 * `DatabaseSeeder`; if a row is missing the employee is reported and skipped
 * rather than being given a ten-day request against a zero balance.
 *
 * The amount is `MonetizationRequest::computeAmount()` on a draft model, so the
 * constant factor lives in exactly one place. Re-running the seeder restores
 * the days its own previous run debited (read back from the ledger it wrote)
 * before re-deriving the balance, which is what keeps `vl_balance` and
 * `computed_amount` identical on the second run instead of shrinking.
 */
class PagsanjanMonetizationSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** The admin account that approves monetization. */
    private const DECIDING_USER_ID = 1;

    /** How many employees file a request. */
    private const SUBJECTS = 2;

    /**
     * The annual cap on monetizable vacation leave, from the office's own rule
     * recorded on the `MLC` leave type: "Maximum 10 days of VL credits can be
     * monetized annually."
     */
    private const MAX_VL_DAYS = 10;

    /** Sick leave is not convertible outside separation. */
    private const SL_DAYS = 0;

    /** When the approved request was signed — before the seeded travel dates. */
    private const DECISION_DATE = '2026-09-01 09:15:00';

    /**
     * One plan per subject, in roster order.
     *
     * The days are fixed rather than drawn: they are read against a real leave
     * balance and capped by the `MLC` rule, and a random figure would make the
     * pesos on the screen move between two runs of the same seeder.
     */
    private const REQUESTS = [
        [
            'days' => 8,
            'status' => 'approved',
            'reason' => 'Monetizing eight days of my vacation leave credits to cover the tuition expenses of my children this semester.',
            'approver_remarks' => 'Approved. Eight days are within the ten-day annual limit and are supported by the certified leave balances on file.',
        ],
        [
            'days' => 10,
            'status' => 'pending',
            'reason' => 'Monetizing ten days of my vacation leave credits to settle the repair of our house damaged by the last typhoon.',
        ],
    ];

    public function run(): void
    {
        // The roster's deterministic base. Nothing below is drawn — the figures
        // come from the leave balances and the `MLC` cap — but the seeder keeps
        // the same starting point as its siblings.
        $this->seedRandomness();

        $roster = $this->rosterEmployees();
        $rosterIds = array_keys($roster);
        $subjects = [];

        foreach ($roster as $employee) {
            if ($this->employmentDetailFor($employee->id)?->employment_status === 'Permanent') {
                $subjects[] = $employee;

                if (count($subjects) === self::SUBJECTS) {
                    break;
                }
            }
        }

        if (count($subjects) < self::SUBJECTS) {
            $this->command->warn('Fewer than two permanent Pagsanjan employees found — run PagsanjanEmployeeSeeder first.');

            return;
        }

        // Idempotence. The previous run moved the VL balance down by the days it
        // approved, so those days go back first; the figures below are then read
        // from the same balances the first run saw.
        $this->restoreDebitedBalances($rosterIds);

        MonetizationRequest::whereIn('employee_id', $rosterIds)->delete();
        LeaveTransaction::whereIn('employee_id', $rosterIds)
            ->where('reference_type', 'monetization')
            ->delete();

        $summary = [];

        foreach ($subjects as $index => $employee) {
            $plan = self::REQUESTS[$index];
            $detail = $this->employmentDetailFor($employee->id);
            $vlBalance = LeaveBalance::currentForCode($employee->id, 'VL');
            $slBalance = LeaveBalance::currentForCode($employee->id, 'SL');

            if ($detail === null || $vlBalance === null) {
                $this->command->warn(
                    "No employment detail or VL balance row for {$employee->first_name} {$employee->last_name} — monetization skipped."
                );

                continue;
            }

            $vlAvailable = round((float) $vlBalance->available_credits, 3);
            $vlDays = (float) min($plan['days'], self::MAX_VL_DAYS, $vlAvailable);

            if ($vlDays < $plan['days']) {
                $this->command->warn(sprintf(
                    'VL balance of %s allows only %s day(s) — request reduced from %d.',
                    $employee->first_name . ' ' . $employee->last_name,
                    $vlDays,
                    $plan['days']
                ));
            }

            if ($vlDays <= 0) {
                $this->command->warn("No monetizable VL credits for {$employee->first_name} {$employee->last_name} — request skipped.");

                continue;
            }

            $salary = $this->monthlyRateFor($detail->designation_id);

            $draft = new MonetizationRequest([
                'monthly_salary' => $salary,
                'vl_days' => $vlDays,
                'sl_days' => self::SL_DAYS,
            ]);
            $amount = $draft->computeAmount();

            $approved = $plan['status'] === 'approved';
            $approvedAt = $approved ? Carbon::parse(self::DECISION_DATE) : null;

            $request = MonetizationRequest::create([
                // Minted here rather than left to the model's `creating` hook:
                // `DatabaseSeeder` runs with `WithoutModelEvents`, so under the
                // documented seed order the hook never fires and the insert dies
                // on `request_number` having no default.
                'request_number' => MonetizationRequest::generateRequestNumber(),
                'employee_id' => $employee->id,
                'vl_days' => $vlDays,
                'sl_days' => self::SL_DAYS,
                'monthly_salary' => $salary,
                'vl_balance' => $vlAvailable,
                'sl_balance' => $slBalance ? round((float) $slBalance->available_credits, 3) : null,
                'computed_amount' => $amount,
                'reason' => $plan['reason'],
                'filed_by' => User::where('employee_id', $employee->id)->value('id'),
                'status' => $plan['status'],
                'approved_by' => $approved ? self::DECIDING_USER_ID : null,
                'approved_at' => $approvedAt,
                'approver_remarks' => $approved ? $plan['approver_remarks'] : null,
            ]);

            if ($approved) {
                $this->recordDebit($request, $vlBalance, $vlDays, $approvedAt);
            }

            $summary[] = sprintf(
                '%s for %s %s = PHP %s',
                $request->request_number,
                $employee->first_name,
                $employee->last_name,
                number_format($amount, 2)
            );
        }

        $this->command->info('Pagsanjan monetization: ' . count($summary) . ' request(s) — ' . implode('; ', $summary) . '.');
    }

    /**
     * Put back the days an earlier run of this seeder debited.
     *
     * The ledger is the record of what was taken, so the restore is derived from
     * it rather than from a guess: `amount` is negative on a debit, and
     * subtracting it returns the days to the row `currentForCode` picked — the
     * same rule the debit used to choose its row.
     */
    private function restoreDebitedBalances(array $employeeIds): void
    {
        $debits = LeaveTransaction::whereIn('employee_id', $employeeIds)
            ->where('reference_type', 'monetization')
            ->get();

        foreach ($debits as $debit) {
            $balance = LeaveBalance::currentForCode($debit->employee_id, $debit->leave_code);

            if ($balance === null) {
                continue;
            }

            $balance->update([
                'available_credits' => round((float) $balance->available_credits - (float) $debit->amount, 6),
            ]);
        }
    }

    /**
     * The approved request's effect: the VL balance moves down and the matching
     * `leave_transactions` debit is written, naming the request it belongs to.
     *
     * Only `available_credits` moves. A monetized credit is commuted to cash,
     * not taken as leave, so it must not be added to `used_credits`: that column
     * is what `available = total_credits + carried_over - used_credits -
     * pending_credits` measures leave taken against, and counting a
     * monetization there charges the employee twice — once in the balance and
     * once in the arithmetic that derives it. The ledger row is what records
     * the encashment.
     */
    private function recordDebit(
        MonetizationRequest $request,
        LeaveBalance $balance,
        float $vlDays,
        Carbon $approvedAt
    ): void {
        $before = round((float) $balance->available_credits, 3);
        $after = round($before - $vlDays, 3);

        $balance->update(['available_credits' => $after]);

        LeaveTransaction::create([
            'employee_id' => $request->employee_id,
            'leave_code' => 'VL',
            // The balance row's own year, not the calendar year: balances are
            // not rewritten each January (LeaveBalance::currentFor's rule), so a
            // hard-coded year would misdate a debit taken against a balance
            // sitting under an older one.
            'year' => (int) $balance->year,
            'transaction_type' => 'debit',
            'amount' => -1 * $vlDays,
            'balance_before' => $before,
            'balance_after' => $after,
            'reference_type' => 'monetization',
            'reference_id' => $request->id,
            'transaction_date' => $approvedAt->toDateString(),
            'processed_by' => self::DECIDING_USER_ID,
            'remarks' => sprintf(
                'Monetization of %s day(s) of VL credits — request %s.',
                $vlDays,
                $request->request_number
            ),
        ]);
    }
}
