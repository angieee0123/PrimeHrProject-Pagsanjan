<?php

namespace Database\Seeders;

use App\Models\TravelOrder;
use App\Models\User;
use App\Services\CscTimeConversionService;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;

/**
 * Five travel orders for the first three personnel of the Pagsanjan roster —
 * the Office of the Municipal Mayor — carrying approved, pending and
 * disapproved decisions so every tab of the travel-order screen has a row.
 *
 * Destinations are Laguna / Calabarzon / Metro Manila, not the Bicol list in the
 * older `TravelOrderSeeder`: this HRIS serves Pagsanjan, Laguna, and a travel
 * order to Legazpi would be a trip this office never files.
 *
 * **Every order is created already carrying its final status.** The observer
 * (`app/Observers/TravelOrderObserver.php`) reacts only to a status that
 * *changes* to `approved` on update, and when it does it writes `attendance`
 * and `daily_salary_computations` rows. The attendance seeder owns that register
 * and reads approved orders itself to mark those dates `TRAVEL_ORDER`, so firing
 * the observer here would duplicate its work and write to tables this seeder
 * does not own. Creating with the status set is therefore a correctness
 * requirement, not a shortcut.
 */
class PagsanjanTravelOrderSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** The admin account that signs travel decisions. */
    private const DECIDING_USER_ID = 1;

    /** The first three roster entries are the Mayor's Office staff who travel. */
    private const TRAVELLERS = 3;

    /**
     * One plan list per traveller, in roster order.
     *
     * `window` is the span the travel date is drawn from rather than typed: the
     * itinerary stands in for the request the employee would have filed, and
     * drawing the weekday from a week keeps the roster from looking
     * hand-placed while staying reproducible. Windows of the same employee are
     * more than a week apart and no span exceeds three working days, so two
     * orders of one employee cannot overlap.
     *
     * `per_diem` and `transport_cost` compose `estimated_budget` the way the
     * office estimates a trip (working days x per diem + transport), which is
     * what keeps a one-day Santa Cruz run at a few hundred pesos and a
     * three-day Manila conference at a few thousand.
     *
     * `purpose` names the actual business of the filer's own office: the
     * Executive Assistant follows up appointments and records, the Private
     * Secretary carries the Mayor's correspondence, the Admin Aide handles
     * records and supply matters.
     */
    private const PLANS = [
        // Executive Assistant II.
        [
            [
                'status' => 'approved',
                'destination' => 'CSC Regional Office IV-A, Lipa City',
                'purpose' => "Attend the CSC Regional Office IV-A orientation on the updated Omnibus Rules on Appointments and Other Human Resource Actions, and secure the accredited service records of the Mayor's Office plantilla.",
                'transportation_mode' => 'Van',
                'per_diem' => 700.00,
                'transport_cost' => 500.00,
                'working_days' => 2,
                'window' => ['2026-08-03', '2026-08-07'],
                'remarks' => 'Approved. Proceed as requested and submit a report of the activity within three days of return.',
            ],
            [
                'status' => 'pending',
                'destination' => 'Santa Cruz, Laguna',
                'purpose' => "Submit the Mayor's Office quarterly report on delegated authority and the updated inventory of executive issuances to the Office of the Provincial Administrator, Santa Cruz, Laguna.",
                'transportation_mode' => 'Municipal Vehicle',
                'per_diem' => 300.00,
                'transport_cost' => 120.00,
                'working_days' => 1,
                'window' => ['2026-08-17', '2026-08-21'],
            ],
        ],

        // Private Secretary II.
        [
            [
                'status' => 'approved',
                'destination' => 'Manila, Metro Manila',
                'purpose' => 'Attend the League of Municipalities of the Philippines national convention on local governance and accompany the Municipal Mayor to the plenary and committee sessions.',
                'transportation_mode' => 'Bus',
                'per_diem' => 1200.00,
                'transport_cost' => 800.00,
                'working_days' => 3,
                'window' => ['2026-08-03', '2026-08-05'],
                'remarks' => 'Approved. Submit the convention kit and the Mayor\'s official report on return.',
            ],
            [
                'status' => 'disapproved',
                'destination' => 'Calamba City, Laguna',
                'purpose' => 'Attend the DENR Calabarzon consultative meeting on the updated solid waste management compliance requirements for local government units.',
                'transportation_mode' => 'Van',
                'per_diem' => 600.00,
                'transport_cost' => 250.00,
                'working_days' => 2,
                'window' => ['2026-08-24', '2026-08-28'],
                'disapproval_reason' => 'Disapproved — the trip falls on the same dates as the Office\'s budget hearing and the Private Secretary is required to be at the Mayor\'s Office.',
            ],
        ],

        // Admin Aide IV.
        [
            [
                'status' => 'pending',
                'destination' => 'Los Baños, Laguna',
                'purpose' => "Attend the records management seminar for local government units at the University of the Philippines Los Baños and apply the updated filing system to the Mayor's Office records.",
                'transportation_mode' => 'Van',
                'per_diem' => 500.00,
                'transport_cost' => 200.00,
                'working_days' => 1,
                'window' => ['2026-08-10', '2026-08-14'],
            ],
        ],
    ];

    public function run(): void
    {
        $this->seedRandomness();

        $roster = $this->rosterEmployees();
        $travellers = array_slice($roster, 0, self::TRAVELLERS, true);

        if (count($travellers) < self::TRAVELLERS) {
            $this->command->warn('Pagsanjan roster not found — run PagsanjanEmployeeSeeder first.');

            return;
        }

        // Idempotence. A mass delete issues no model events, so the observer's
        // `deleted` hook — which would reach into the attendance register — is
        // not triggered and the other seeders' tables stay untouched.
        TravelOrder::whereIn('employee_id', array_keys($roster))->delete();

        $breakdown = [];
        $filed = 0;

        foreach (array_values($travellers) as $index => $employee) {
            $filerId = User::where('employee_id', $employee->id)->value('id');

            foreach (self::PLANS[$index] as $plan) {
                $travelDate = $this->pickWeekday($plan['window'][0], $plan['window'][1]);
                // addWeekdays() walks working days only, so a Friday departure
                // with a two-day span returns on Monday and `duration` — counted
                // over the same weekday rule — is 2, not 4.
                $returnDate = $travelDate->copy()->addWeekdays($plan['working_days'] - 1);
                $duration = $this->workingDays($travelDate, $returnDate);

                $decision = $plan['status'];
                $disapprovalReason = $plan['disapproval_reason'] ?? null;

                TravelOrder::create([
                    // Minted here rather than left to the model's `creating`
                    // hook. `DatabaseSeeder` runs with `WithoutModelEvents`, so
                    // the hook never fires under the documented seed order and
                    // the insert dies on `order_number` having no default —
                    // exactly the kind of failure a standalone run of this
                    // seeder cannot show.
                    'order_number' => TravelOrder::generateOrderNumber(),
                    'employee_id' => $employee->id,
                    'destination' => $plan['destination'],
                    'purpose' => $plan['purpose'],
                    'travel_date' => $travelDate->toDateString(),
                    'return_date' => $returnDate->toDateString(),
                    'duration' => $duration,
                    'transportation_mode' => $plan['transportation_mode'],
                    'estimated_budget' => round($plan['per_diem'] * $duration + $plan['transport_cost'], 2),
                    'attachment' => null,
                    'status' => $decision,
                    // `TravelOrder::getStatusBadgeAttribute()` and the
                    // Disapproved tab read `remarks`, while
                    // TravelOrderController::disapprove() writes
                    // `disapproval_reason`. Both columns are filled with the
                    // same sentence so the row reads identically whichever one
                    // the screen asks for.
                    'remarks' => $disapprovalReason ?? $plan['remarks'] ?? null,
                    'approved_by' => $decision === 'approved' ? self::DECIDING_USER_ID : null,
                    'approved_at' => $decision === 'approved' ? $this->decisionAt($travelDate, 4) : null,
                    'disapproved_by' => $decision === 'disapproved' ? self::DECIDING_USER_ID : null,
                    'disapproved_at' => $decision === 'disapproved' ? $this->decisionAt($travelDate, 2) : null,
                    'disapproval_reason' => $disapprovalReason,
                    'filed_by' => $filerId,
                ]);

                $breakdown[$decision] = ($breakdown[$decision] ?? 0) + 1;
                $filed++;
            }
        }

        $this->command->info(sprintf(
            'Pagsanjan travel orders: %d filed for %d employees — %s.',
            $filed,
            count($travellers),
            $this->statusBreakdown($breakdown)
        ));
    }

    /**
     * A weekday drawn from [$from, $to].
     *
     * The weekday test is `CscTimeConversionService::isWeekend()`, the same one
     * the observer and the attendance seeder use to decide what counts as a
     * working day — a second weekend rule here is how a seeded travel order ends
     * up claiming a Saturday.
     */
    private function pickWeekday(string $from, string $to): Carbon
    {
        $candidates = [];
        $date = Carbon::parse($from);
        $last = Carbon::parse($to);

        while ($date->lte($last)) {
            if (! CscTimeConversionService::isWeekend($date)) {
                $candidates[] = $date->copy();
            }

            $date->addDay();
        }

        if ($candidates === []) {
            throw new \RuntimeException("No weekday between {$from} and {$to} to file a travel order on.");
        }

        return $candidates[$this->draw(0, count($candidates) - 1)];
    }

    /** How many working days the range covers, weekends excluded. */
    private function workingDays(Carbon $from, Carbon $to): int
    {
        $days = 0;

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            if (! CscTimeConversionService::isWeekend($date)) {
                $days++;
            }
        }

        return $days;
    }

    /**
     * When the decision was signed: a few days before the trip, which is when
     * an approval is actually given. The time of day is the start of the office
     * hours so the timestamp reads like a signed paper and not a seeder run.
     */
    private function decisionAt(Carbon $travelDate, int $daysBefore): Carbon
    {
        return $travelDate->copy()->subDays($daysBefore)->setTime(9, 0);
    }

    /** "approved 2, pending 2, disapproved 1" — in a stable order. */
    private function statusBreakdown(array $counts): string
    {
        $parts = [];

        foreach (['approved', 'pending', 'disapproved'] as $status) {
            if (isset($counts[$status])) {
                $parts[] = "{$status} {$counts[$status]}";
            }
        }

        return implode(', ', $parts);
    }
}
