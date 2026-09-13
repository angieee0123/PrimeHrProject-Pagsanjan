<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmploymentDetail;
use App\Models\PassSlip;
use App\Models\Schedule;
use App\Models\User;
use App\Services\CscTimeConversionService;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;

/**
 * Seven pass slips for the first three personnel of the Pagsanjan roster — the
 * Office of the Municipal Mayor — with approved, pending and rejected rows.
 *
 * `type`, `purpose_category`, `destination` and `reason` are written as one
 * statement, because the printed form fuses them: `PassSlip::PURPOSE_LABELS`
 * renders the category as the form's own words ("to coordinate with", "to
 * attend meeting/conference", "to secure documents & others", "to follow up",
 * "to attend personal matter"), and a slip whose category says *secure
 * documents* while its reason says *attend a meeting* prints a form that
 * contradicts itself. An `official_activity` slip therefore pairs
 * `coordinate_with` / `meeting_conference` / `secure_documents` with a
 * government destination, and a `personal_reason` slip pairs `personal_matter`
 * with a destination that is plainly the employee's own business.
 *
 * Times are read from each employee's real `schedules` row rather than assumed:
 * the GSO / GSO-SL / MDRRM offices open at 07:00 and close at 16:00, and a slip
 * timed 08:30 for someone on that shift would be an hour into their day.
 */
class PagsanjanPassSlipSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** The admin account that approves pass slips. */
    private const DECIDING_USER_ID = 1;

    /** The first three roster entries are the Mayor's Office staff who file. */
    private const FILERS = 3;

    /** A slip is not filed the minute the flag ceremony ends. */
    private const MINIMUM_LEAD_MINUTES = 30;

    /**
     * The last minutes of the day a slip may start.
     *
     * A slip that departs in the closing hour of the office cannot reach a
     * government counter and come back before it closes, so departures in the
     * final ninety minutes of the scheduled day are not offered at all.
     */
    private const CLOSING_BUFFER_MINUTES = 90;

    /**
     * The name the form carries when no office head is on the roster.
     *
     * None of the thirty personnel holds a "Mun." or "MGDH" designation — the
     * Municipal Mayor and the Municipal Administrator are not part of the
     * workbook this dataset came from — so the recommended-by line names the
     * Municipal Administrator, the officer who signs for the Mayor's Office in
     * practice.
     */
    private const DEFAULT_RECOMMENDER = 'FLORANTE C. SALVADOR JR.';

    /**
     * One slip list per filer, in roster order.
     *
     * `window` is the week the slip date is drawn from. The windows of one
     * employee never overlap, which is what keeps a person's slips on different
     * dates without a uniqueness check that could silently drop one.
     */
    private const SLIPS = [
        // Executive Assistant II.
        [
            [
                'type' => 'official_activity',
                'purpose_category' => 'meeting_conference',
                'destination' => "Provincial Assessor's Office, Santa Cruz",
                'reason' => "Attend the Provincial Assessor's Office coordination meeting on the 2026 general revision of the Schedule of Market Values of the Municipality of Pagsanjan.",
                'status' => 'approved',
                'remarks' => 'Approved. Coordinate with the Office of the Mayor before departure.',
                'window' => ['2026-08-03', '2026-08-07'],
            ],
            [
                'type' => 'official_activity',
                'purpose_category' => 'secure_documents',
                'destination' => 'GSIS Branch, Santa Cruz',
                'reason' => "Secure the certified service records of the Mayor's Office personnel from the GSIS Branch in Santa Cruz for the updating of their 201 files.",
                'status' => 'pending',
                'window' => ['2026-08-17', '2026-08-21'],
            ],
            [
                'type' => 'personal_reason',
                'purpose_category' => 'personal_matter',
                'destination' => 'Laguna State Polytechnic University, Santa Cruz',
                'reason' => 'Attend to the enrollment of my daughter at the Laguna State Polytechnic University, Santa Cruz.',
                'status' => 'rejected',
                'remarks' => 'Disapproved — the enrollment can be done on a Saturday, and the office needs its administrative aide during the budget hearing week.',
                'window' => ['2026-08-31', '2026-09-04'],
            ],
        ],

        // Private Secretary II.
        [
            [
                'type' => 'official_activity',
                'purpose_category' => 'coordinate_with',
                'destination' => 'Municipal Hall, Magdalena',
                'reason' => 'Coordinate with the Office of the Municipal Administrator of Magdalena on the joint inter-LGU clean-up drive along the Balanac River.',
                'status' => 'approved',
                'remarks' => 'Approved. Submit the minutes of the coordination meeting on return.',
                'window' => ['2026-08-10', '2026-08-14'],
            ],
            [
                'type' => 'official_activity',
                'purpose_category' => 'secure_documents',
                'destination' => 'BIR Revenue District Office, Calamba',
                'reason' => 'Secure the Certificate of Registration of the newly acquired service vehicle of the Office of the Municipal Mayor from the BIR Revenue District Office in Calamba.',
                'status' => 'pending',
                'window' => ['2026-08-24', '2026-08-28'],
            ],
        ],

        // Admin Aide IV.
        [
            [
                'type' => 'official_activity',
                'purpose_category' => 'coordinate_with',
                'destination' => 'DBM Regional Office IV-A',
                'reason' => "Coordinate with the DBM Regional Office IV-A on the release and liquidation of the Notice of Cash Allocation of the Mayor's Office.",
                'status' => 'approved',
                'remarks' => "Approved. Bring the Office's NCA file and the latest liquidation report.",
                'window' => ['2026-08-03', '2026-08-07'],
            ],
            [
                'type' => 'personal_reason',
                'purpose_category' => 'personal_matter',
                'destination' => 'Pag-IBIG Fund Branch, Santa Cruz',
                'reason' => 'Attend to the updating of my Pag-IBIG membership records for my housing loan application.',
                'status' => 'pending',
                'window' => ['2026-09-07', '2026-09-11'],
            ],
        ],
    ];

    public function run(): void
    {
        $this->seedRandomness();

        $roster = $this->rosterEmployees();
        $filers = array_slice($roster, 0, self::FILERS, true);

        if (count($filers) < self::FILERS) {
            $this->command->warn('Pagsanjan roster not found — run PagsanjanEmployeeSeeder first.');

            return;
        }

        PassSlip::whereIn('employee_id', array_keys($roster))->delete();

        $breakdown = [];
        $filed = 0;

        foreach (array_values($filers) as $index => $employee) {
            $filerId = User::where('employee_id', $employee->id)->value('id');
            $departmentId = $this->employmentDetailFor($employee->id)?->department_id;
            $recommender = $this->recommenderFor($employee->id, $departmentId);

            foreach (self::SLIPS[$index] as $plan) {
                $date = $this->pickWeekday($plan['window'][0], $plan['window'][1]);
                [$timeOut, $timeIn] = $this->passSlipTimes($employee->id);

                $decision = $plan['status'];

                PassSlip::create([
                    // Minted here rather than left to the model's `creating`
                    // hook: `DatabaseSeeder` runs with `WithoutModelEvents`, so
                    // under the documented seed order the hook never fires and
                    // the insert dies on `slip_number` having no default.
                    'slip_number' => PassSlip::generateSlipNumber(),
                    'employee_id' => $employee->id,
                    'type' => $plan['type'],
                    'purpose_category' => $plan['purpose_category'],
                    'date' => $date->toDateString(),
                    'time_out' => $timeOut,
                    'time_in' => $timeIn,
                    'destination' => $plan['destination'],
                    'recommended_by_name' => $recommender,
                    'reason' => $plan['reason'],
                    'attachment' => null,
                    'status' => $decision,
                    'remarks' => $plan['remarks'] ?? null,
                    'approved_by' => $decision === 'approved' ? self::DECIDING_USER_ID : null,
                    // A pass slip is approved before the employee leaves, so the
                    // timestamp sits at the start of the day the slip is for.
                    'approved_at' => $decision === 'approved' ? $date->copy()->setTime(7, 45) : null,
                    'filed_by' => $filerId,
                ]);

                $breakdown[$decision] = ($breakdown[$decision] ?? 0) + 1;
                $filed++;
            }
        }

        $this->command->info(sprintf(
            'Pagsanjan pass slips: %d filed for %d employees — %s.',
            $filed,
            count($filers),
            $this->statusBreakdown($breakdown)
        ));
    }

    /**
     * A weekday drawn from [$from, $to], by the same weekend rule the rest of
     * the system uses.
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
            throw new \RuntimeException("No weekday between {$from} and {$to} to file a pass slip on.");
        }

        return $candidates[$this->draw(0, count($candidates) - 1)];
    }

    /**
     * An out/in pair inside the employee's own working day.
     *
     * Candidate departure times are generated from the employee's `schedules`
     * row, so an early-shift office gets 07:30 and not 08:30. The return is one
     * to three hours later — the printed form's own range — clamped to the
     * scheduled end of the day, and never earlier than the departure, which is
     * the one thing the form cannot express.
     *
     * @return array{0: string, 1: string} [time_out, time_in] as H:i:s
     */
    private function passSlipTimes(int $employeeId): array
    {
        [$amIn, $amOut, $pmIn, $pmOut] = $this->workingMinutes($employeeId);

        $candidates = [];

        for ($minute = $amIn + self::MINIMUM_LEAD_MINUTES; $minute <= $amOut - 60; $minute += 30) {
            $candidates[] = $minute;
        }

        for ($minute = $pmIn + self::MINIMUM_LEAD_MINUTES; $minute <= $pmOut - self::CLOSING_BUFFER_MINUTES; $minute += 30) {
            $candidates[] = $minute;
        }

        if ($candidates === []) {
            // A schedule too short to hold an hour's errand still gets a slip;
            // the standard municipal day is the only defensible fallback.
            return ['08:30:00', '10:30:00'];
        }

        $out = $candidates[$this->draw(0, count($candidates) - 1)];
        $hours = $this->draw(1, 3);

        if ($out + $hours * 60 > $pmOut) {
            $hours = (int) floor(($pmOut - $out) / 60);
        }

        return [$this->clock($out), $this->clock($out + $hours * 60)];
    }

    /**
     * The employee's scheduled day, in minutes past midnight.
     *
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function workingMinutes(int $employeeId): array
    {
        $schedule = Schedule::where('employee_id', $employeeId)->first();

        if (! $schedule) {
            // Every seeded employee carries a schedule; a missing row must not
            // stop the slips from being written.
            return [8 * 60, 12 * 60, 13 * 60, 17 * 60];
        }

        return [
            $this->minutesOf($schedule->am_in),
            $this->minutesOf($schedule->am_out),
            $this->minutesOf($schedule->pm_in),
            $this->minutesOf($schedule->pm_out),
        ];
    }

    private function minutesOf(?string $time): int
    {
        [$hours, $minutes] = array_pad(explode(':', (string) $time), 2, '0');

        return ((int) $hours) * 60 + (int) $minutes;
    }

    private function clock(int $minutes): string
    {
        return sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * Who recommends the slip: the employee's own office head when one of the
     * thirty holds a "Mun." or "MGDH" designation in the same department,
     * otherwise the Municipal Administrator's name. Both branches read the
     * `designations` table — the recommended-by line never invents a title.
     */
    private function recommenderFor(int $employeeId, ?int $departmentId): string
    {
        if ($departmentId !== null) {
            $headDesignations = Designation::where('department_id', $departmentId)
                ->where(function ($query) {
                    $query->where('title', 'like', 'Mun.%')
                        ->orWhere('title', 'like', 'MGDH%');
                })
                ->pluck('id');

            if ($headDesignations->isNotEmpty()) {
                $head = EmploymentDetail::where('department_id', $departmentId)
                    ->whereIn('designation_id', $headDesignations)
                    ->where('employee_id', '!=', $employeeId)
                    ->first();

                $name = $head ? Employee::where('id', $head->employee_id)->first() : null;

                if ($name) {
                    // The form prints the name in full caps, as the office types it.
                    return strtoupper(trim($name->first_name . ' ' . $name->last_name));
                }
            }
        }

        return self::DEFAULT_RECOMMENDER;
    }

    /** "approved 3, pending 3, rejected 1" — in a stable order. */
    private function statusBreakdown(array $counts): string
    {
        $parts = [];

        foreach (['approved', 'pending', 'rejected'] as $status) {
            if (isset($counts[$status])) {
                $parts[] = "{$status} {$counts[$status]}";
            }
        }

        return implode(', ', $parts);
    }
}
