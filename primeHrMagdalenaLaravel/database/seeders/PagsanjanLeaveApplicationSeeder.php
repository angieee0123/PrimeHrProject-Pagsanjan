<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\CscTimeConversionService;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Leave applications for the Pagsanjan roster: sixty-odd filings dated inside
 * August and September 2026, most of them approved, about a third still waiting
 * on HR, and five refused with a stated reason.
 *
 * The rows are written at their final status in a single insert. Filing an
 * application as `pending` and then saving it as `approved` would fire
 * `LeaveApplicationObserver::updated()`, which writes `attendance` rows of type
 * LEAVE and a `daily_salary_computations` row for every working day of the
 * period — and the attendance table belongs to another seeder that reads this
 * one's approved applications and marks those days on the register itself. The
 * observer also cannot tell a seed from a real approval, so a status transition
 * here would launder generated data into the DTR as if a human had approved it.
 *
 * Bookkeeping mirrors `LeaveApplicationSeeder`: the credits a filing takes are
 * removed from the employee's current balance row and recorded as a
 * `leave_transactions` movement referencing the application. Nothing is filed
 * against a code the employee cannot pay for — a screener who opens an approved
 * vacation leave with no vacation credits behind it has been handed a data
 * error, not a demonstration.
 */
class PagsanjanLeaveApplicationSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** Every filing is dated inside this window, weekends excluded. */
    private const WINDOW_START = '2026-08-03';

    private const WINDOW_END = '2026-09-11';

    /** Applications are numbered from here; the model's generator cannot be used. */
    private const APPLICATION_NUMBER_FALLBACK_SEQ = 0;

    /**
     * Which applications each roster position files, and in what state.
     *
     * The plan is chosen by roster position, one entry per employee, so the
     * shape of the register is fixed rather than left to chance: the first
     * twenty-five entries — the plantilla staff, the only ones with credits to
     * file against — come to 36 approved, 21 pending and 5 rejected. Which
     * *leave* each application asks for, and on which days, is still drawn.
     *
     * Every plan opens with an approved application. The first entry is written
     * first, so an employee always has one approval on file before any later
     * filing can spend the credits it needs.
     *
     * @var array<int, array<int, string>>
     */
    private const STATUS_PLANS = [
        ['approved', 'pending', 'rejected'],
        ['approved', 'pending'],
        ['approved', 'approved', 'pending'],
        ['approved', 'pending'],
        ['approved', 'rejected', 'approved'],
        ['approved', 'approved'],
        ['approved', 'pending', 'pending'],
        ['approved', 'approved', 'pending'],
        ['approved', 'pending'],
        ['approved', 'pending', 'rejected'],
        ['approved', 'approved'],
        ['approved', 'pending'],
        ['approved', 'approved', 'pending'],
        ['approved', 'pending'],
        ['approved', 'pending', 'pending'],
        ['approved', 'rejected', 'pending'],
        ['approved', 'approved'],
        ['approved', 'pending'],
        ['approved', 'approved'],
        ['approved', 'pending', 'rejected'],
        ['approved', 'approved', 'pending'],
        ['approved', 'pending'],
        ['approved', 'approved'],
        ['approved', 'approved', 'pending'],
        ['approved', 'pending'],
        ['approved', 'rejected', 'approved'],
        ['approved', 'pending'],
        ['approved', 'approved', 'pending'],
        ['approved', 'pending'],
        ['approved', 'approved', 'pending'],
    ];

    /**
     * The codes a permanent employee may file, weighted toward VL and SL.
     *
     * A code appears once per unit of weight, which is what makes the draw pick
     * the ordinary vacation and sick filings most of the time without a second
     * weighted-choice helper. `MCL` is in the list because it is one of the
     * types this roster files; the sex check that actually restricts it is in
     * `fileableCodes()`.
     */
    private const PERMANENT_CODE_WEIGHTS = [
        'VL', 'VL', 'VL', 'VL', 'VL', 'VL',
        'SL', 'SL', 'SL', 'SL', 'SL',
        'WL', 'WL',
        'BL',
        'SEL',
        'SPL',
        'FL',
        'MCL',
    ];

    /** The service a `requires_6_months` leave type is measured against. */
    private const SERVICE_MONTHS_REQUIRED = 6;

    /**
     * The reason written per leave type, in the words the filing employee would
     * use. The record already carries `leave_code`; a generic "filing leave"
     * reason on all seventy rows tells a reviewer nothing about why the person
     * was away.
     */
    private const REASONS = [
        'VL' => 'Personal vacation and rest with family.',
        'SL' => 'Medical consultation and recovery.',
        'FL' => 'Mandatory annual forced leave.',
        'SPL' => 'Personal matters and family obligations.',
        'WL' => 'Wellness check-up and rest.',
        'BL' => 'Death of an immediate family member.',
        'SEL' => 'Assistance after a calamity in the barangay.',
        'MCL' => 'Medical consultation under the Magna Carta of Women.',
    ];

    /**
     * The refusals, with the remark that belongs to each.
     *
     * The remark repeats the reason on purpose. An approver's note is the only
     * place a refusal explains itself, and "Disapproved." beside an application
     * that asked for bereavement leave leaves the filer guessing; naming the
     * ground for refusal is what makes the row reviewable.
     *
     * @var array<int, array{ground: string, remarks: string}>
     */
    private const REJECTION_GROUNDS = [
        [
            'ground' => 'Filed on the day the leave was to begin, without the required prior notice.',
            'remarks' => 'Disapproved: filed on the day the leave was to begin. The application was not filed in advance of the requested period as required for this leave type.',
        ],
        [
            'ground' => 'The employee is needed in the office on these dates.',
            'remarks' => 'Disapproved: the requested dates fall on a period the office cannot spare the employee. The leave may be re-filed for other dates.',
        ],
        [
            'ground' => 'Insufficient leave credits for the period requested.',
            'remarks' => 'Disapproved for want of leave credits. The employee may re-file once further credits accrue.',
        ],
        [
            'ground' => 'The requested dates overlap a leave already approved for the same employee.',
            'remarks' => 'Disapproved: these dates overlap a leave already approved for this employee.',
        ],
        [
            'ground' => 'The service requirement for this leave type has not been met.',
            'remarks' => 'Disapproved: the six months of service this leave type requires had not been completed when the application was filed.',
        ],
    ];

    /** Draw attempts made before falling back to any code the employee can pay for. */
    private const CREDIT_DRAW_ATTEMPTS = 4;

    /** The next free sequence number in this year's LA-<year>-<0000> run. */
    private int $nextApplicationSequence = 0;

    /** Applications written per status, for the closing summary. */
    private array $filedByStatus = ['approved' => 0, 'pending' => 0, 'rejected' => 0];

    /** The administrator who signs the decisions, resolved once. */
    private ?int $adminUserId = null;

    /**
     * The active leave types, keyed by leave code.
     *
     * Read once and held: `fileableCodes()` consults it per employee, and thirty
     * employees times twenty types is a lot of identical queries.
     *
     * @var Collection<string, LeaveType>|null
     */
    private ?Collection $leaveTypes = null;

    public function run(): void
    {
        $this->seedRandomness();

        $employees = $this->rosterEmployees();

        if ($employees === []) {
            $this->command->warn('No Pagsanjan roster employees found — run PagsanjanEmployeeSeeder first.');

            return;
        }

        // Balances are rebuilt before anything is filed for two reasons. The
        // ledger moves *downward* from the figures the balance seeder writes, so
        // a second run cannot recompute what the first one spent — only reset
        // it. And the balance seeder is the owner of the pre-application
        // figures, so calling it is cheaper to keep correct than a second copy
        // of its allocation rules here.
        $this->call(PagsanjanLeaveBalanceSeeder::class);

        $this->purgePreviousRun($employees);

        $this->nextApplicationSequence = $this->startingSequenceNumber($employees);
        $leaveTypes = $this->leaveTypes();

        $filedTotal = 0;
        $jobOrders = 0;

        // One plan per roster position, so which employee files what is fixed
        // and the roster-wide totals above hold exactly. The counter is carried
        // separately from the loop key because `rosterEmployees()` is keyed by
        // `employees.id` — the ids are in the thousands, and using one as a
        // position would push the whole register past the end of the window.
        $position = 0;

        foreach ($employees as $employee) {
            $plan = self::STATUS_PLANS[$position % count(self::STATUS_PLANS)];

            if ($this->employmentDetailFor($employee->id)?->employment_status === 'Job Order') {
                $jobOrders++;
            }

            $filedTotal += $this->fileForEmployee($employee, $position, $plan, $leaveTypes);
            $position++;
        }

        $this->command->info(sprintf(
            'Pagsanjan leave applications: %d filed for %d employees — approved %d, pending %d, rejected %d; %d Job Order left with no credit to file against.',
            $filedTotal,
            count($employees) - $jobOrders,
            $this->filedByStatus['approved'],
            $this->filedByStatus['pending'],
            $this->filedByStatus['rejected'],
            $jobOrders
        ));
    }

    /**
     * Delete this seeder's own rows for the roster.
     *
     * Raw deletes, never `LeaveApplication::delete()`: the model's `deleted`
     * event sweeps `attendance` for the period of an approved leave, and this
     * seeder must not touch that table at all.
     *
     * The `attendance` rows carrying the LEAVE days are deliberately left
     * alone, and not only because this seeder never writes one. Another seeder
     * owns that table and derives those rows from the approved applications
     * here; deleting them would be one seeder undoing another's work on every
     * run, and whichever finished last would decide what the register said.
     * Whoever seeds attendance re-runs it after this one instead.
     *
     * The transactions are removed first, because the applications they
     * reference are about to go; the applications themselves are removed by raw
     * query so no model event can fire.
     */
    private function purgePreviousRun(array $employees): void
    {
        $employeeIds = array_keys($employees);

        DB::transaction(function () use ($employeeIds) {
            $applicationIds = DB::table('leave_applications')
                ->whereIn('employee_id', $employeeIds)
                ->pluck('id');

            if ($applicationIds->isNotEmpty()) {
                DB::table('leave_transactions')
                    ->where('reference_type', 'leave_application')
                    ->whereIn('reference_id', $applicationIds)
                    ->delete();
            }

            DB::table('leave_applications')->whereIn('employee_id', $employeeIds)->delete();
        });
    }

    /**
     * File one employee's applications and return how many were written.
     *
     * A Job Order files nothing, by design. `PagsanjanLeaveBalanceSeeder` gives
     * every Job Order a zero balance for every credit code — which is what the
     * roster actually holds, and what stops a screener being told a
     * daily-rate employee has fifteen days of vacation leave — and this seeder
     * will not file against credits that are not there. Their service dates
     * would refuse them `VL`, `FL` and `SPL` in any case: appointed 2026-01-05,
     * they clear the six-month requirement only in July, after most of this
     * window has passed.
     *
     * The employee is left on the roster with an empty leave file rather than
     * given an invented opening balance to spend. An approved application with
     * nothing behind it is the data error this whole check exists to prevent.
     *
     * @param array<int, string> $plan
     * @param Collection<string, LeaveType> $leaveTypes
     */
    private function fileForEmployee(Employee $employee, int $position, array $plan, Collection $leaveTypes): int
    {
        $user = User::where('employee_id', $employee->id)->first();

        if (! $user) {
            $this->command->warn("{$employee->first_name} {$employee->last_name} has no account — no leave filed.");

            return 0;
        }

        $isJobOrder = $this->employmentDetailFor($employee->id)?->employment_status === 'Job Order';

        if ($isJobOrder) {
            return 0;
        }

        // A leave type only the tenured may file is not offered to somebody who
        // has not served the six months it names. The permanent staff were
        // appointed 2021-03-01 and clear it by years; the check reads the date
        // rather than trusting that, so a later roster change cannot smuggle an
        // ineligible filing past it.
        $codes = $this->fileableCodes($employee);

        if ($codes === []) {
            return 0;
        }

        // The whole window, generated once and shared, so every application in
        // this run is dated by the same list of working days.
        $weekdays = $this->weekdaysInWindow();

        // Each employee starts filing at their own point in the window, which
        // spreads the register across the month instead of clustering every
        // employee's leave in the first week. There are thirty working days in
        // the window and thirty employees, so no two begin on the same day —
        // and `nextSlot()` wraps an employee who reaches the end back to the
        // earliest day they have not already taken.
        $cursor = $position % count($weekdays);

        $balances = LeaveBalance::currentFor($employee->id);
        $usedWindow = [];
        $filed = 0;

        foreach ($plan as $status) {
            $slot = $this->nextSlot($weekdays, $cursor, $usedWindow);

            if ($slot === null) {
                break;
            }

            [$startIndex, $days] = $slot;
            $leaveCode = $this->drawAffordableCode($codes, $balances, $days);

            if ($leaveCode === null) {
                // Every code this employee is allowed to file is exhausted.
                // Nothing is filed rather than an application with no credits
                // behind it, and the slot is released for the next one.
                $this->command->warn(
                    "{$employee->first_name} {$employee->last_name}: no code with credits left for a further filing."
                );

                continue;
            }

            $this->writeApplication(
                $employee,
                $user,
                $leaveCode,
                $leaveTypes[$leaveCode],
                $status,
                $weekdays[$startIndex],
                $weekdays[$startIndex + $days - 1],
                $days,
                $balances[$leaveCode]
            );

            // The balance row just moved, so the next filing this employee
            // makes must be checked against the new figure, not the one read
            // before this application was written.
            $balances = LeaveBalance::currentFor($employee->id);

            $usedWindow[] = [$startIndex, $startIndex + $days - 1];
            $cursor = $startIndex + $days;
            $filed++;
        }

        return $filed;
    }

    /**
     * Write one application, its credit movement and its ledger entry.
     *
     * Every random figure this needs is drawn up front, before any branch that
     * might or might not consume one. A draw that only happens on the rejected
     * path would shift every later draw in the run, so an edit to one
     * application's shape would silently rewrite every application after it.
     */
    private function writeApplication(
        Employee $employee,
        User $user,
        string $leaveCode,
        LeaveType $leaveType,
        string $status,
        Carbon $startDate,
        Carbon $endDate,
        int $days,
        LeaveBalance $balance
    ): void {
        $pending = $status === 'pending';

        // Filed a week or two before the leave begins, decided a day or two
        // after filing — the ordinary shape of an approved CS Form 6.
        $filingDate = $startDate->copy()->subDays($this->draw(4, 13));
        $approvedAt = $filingDate->copy()->addDays($this->draw(1, 2));
        $commuted = $this->draw(1, 4) === 1;
        $rejection = $status === 'rejected' ? $this->rejectionFor() : null;

        // A refusal spends nothing. Only an approved application uses the days
        // up, and only a pending one holds them; a rejected one leaves the
        // employee's credits exactly where they were. Debiting a refusal is how
        // a balance stops agreeing with its own ledger — the leave screen then
        // shows days missing that no approval ever consumed.
        $spends = $status !== 'rejected';

        $before = round((float) $balance->available_credits, 6);
        $after = $spends ? round($before - $days, 6) : $before;

        if ($after < 0) {
            // The caller only hands over a code it has already checked, so this
            // is a guard against a future edit rather than a live path: a credit
            // balance that goes negative is the one figure a leave screen must
            // never show.
            $this->command->warn("{$employee->last_name}: skipping {$leaveCode}, balance would go negative.");

            return;
        }

        DB::transaction(function () use (
            $employee, $user, $leaveCode, $leaveType, $status, $pending, $spends, $startDate, $endDate, $days,
            $filingDate, $approvedAt, $commuted, $rejection, $balance, $before, $after
        ) {
            $applicationNumber = $this->mintApplicationNumber();

            // One insert, carrying the final status. Updating a row into
            // `approved` afterwards is what would fire the observer and put
            // LEAVE days on the attendance register.
            $applicationId = DB::table('leave_applications')->insertGetId([
                'application_number' => $applicationNumber,
                'employee_id' => $employee->id,
                'leave_code' => $leaveCode,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'number_of_days' => $days,
                // A refusal's ground is written where the filer's own reason
                // would sit, so the form still reads as the application that was
                // actually filed.
                'reason' => $rejection['ground'] ?? (self::REASONS[$leaveCode] ?? 'Filing leave using available credits.'),
                'commutation_requested' => $commuted ? 1 : 0,
                // The CS Form 6 collects a destination for VL, and a
                // consultation type for SL; neither applies to the rest.
                'leave_location' => $leaveCode === 'VL' ? 'ph' : null,
                'leave_location_specify' => null,
                'sick_leave_type' => $leaveCode === 'SL' ? 'out_patient' : null,
                'illness_specify' => null,
                'study_leave_purpose' => null,
                // Approved and rejected decisions both happened; only a pending
                // application has no approver yet.
                'status' => $status,
                'attachment_path' => null,
                'filed_by' => $user->id,
                'approved_by' => $pending ? null : $this->adminUserId(),
                'approved_at' => $pending ? null : $approvedAt,
                'approver_remarks' => $rejection['remarks'] ?? ($pending
                    ? null
                    : 'Approved. The employee has sufficient leave credits for the period requested.'),
                'approved_days_with_pay' => $status === 'approved' ? $days : null,
                'approved_days_without_pay' => null,
                'approved_other_specify' => null,
                'created_at' => $filingDate,
                'updated_at' => $pending ? $filingDate : $approvedAt,
            ]);

            // The balance seeder writes used and pending at zero, so this is
            // the first movement either column has seen. A pending application
            // holds its days rather than spending them: the credits are still
            // the employee's until HR decides, which is why they leave
            // `available_credits` but not `used_credits`. A rejected one moves
            // nothing at all, so its update is skipped rather than written back
            // with the same figures.
            if ($spends) {
                LeaveBalance::where('id', $balance->id)->update([
                    'available_credits' => $after,
                    'used_credits' => round((float) $balance->used_credits + ($status === 'approved' ? $days : 0), 6),
                    'pending_credits' => round((float) $balance->pending_credits + ($pending ? $days : 0), 6),
                    'updated_at' => $filingDate,
                ]);
            }

            LeaveTransaction::create([
                'employee_id' => $employee->id,
                'leave_code' => $leaveCode,
                'year' => (int) $balance->year,
                // The ledger still records the refusal, with a zero movement:
                // the application exists and was decided, so hiding it would
                // leave the audit trail with a gap rather than an entry.
                'transaction_type' => $spends ? ($pending ? 'pending' : 'debit') : 'reversal',
                'amount' => $spends ? -$days : 0,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => 'leave_application',
                'reference_id' => $applicationId,
                'transaction_date' => $filingDate,
                'processed_by' => $user->id,
                'remarks' => sprintf(
                    '%s application %s (%s, %s to %s).',
                    ucfirst($status),
                    $applicationNumber,
                    $leaveType->leave_name,
                    $startDate->toDateString(),
                    $endDate->toDateString()
                ),
                'created_at' => $filingDate,
                'updated_at' => $filingDate,
            ]);
        });

        $this->filedByStatus[$status]++;
    }

    /**
     * The next filing slot for this employee, as [start index, working days].
     *
     * `$cursor` is where this employee's previous application ended looking
     * forward, so a day is never claimed twice inside one employee's own set of
     * filings. An employee who reaches the end of the window wraps back to the
     * earliest day they have not already taken — their own filings are the only
     * ones that overlap-restrict them, and each employee in this roster has room
     * for three short filings inside a thirty-day window.
     *
     * @param array<int, Carbon> $weekdays
     * @param array<int, array{0: int, 1: int}> $usedWindow
     * @return array{0: int, 1: int}|null
     */
    private function nextSlot(array $weekdays, int $cursor, array $usedWindow): ?array
    {
        $cursor = $this->firstFreeIndex($cursor, $usedWindow, count($weekdays));

        if ($cursor === null) {
            return null;
        }

        $remaining = count($weekdays) - $cursor;

        // Mostly a single day, with a few two- and three-day filings. The length
        // is capped by what is left of the window before the wrap.
        $days = match ($this->draw(1, 10)) {
            1, 2, 3, 4, 5, 6, 7, 8 => 1,
            9 => 2,
            default => 3,
        };

        return [$cursor, min($days, $remaining)];
    }

    /**
     * The first index at or after `$from` that this employee has not taken, or
     * null when the whole window is spoken for.
     *
     * @param array<int, array{0: int, 1: int}> $usedWindow
     */
    private function firstFreeIndex(int $from, array $usedWindow, int $windowLength): ?int
    {
        for ($index = $from; $index < $windowLength; $index++) {
            foreach ($usedWindow as [$takenStart, $takenEnd]) {
                if ($index >= $takenStart && $index <= $takenEnd) {
                    continue 2;
                }
            }

            return $index;
        }

        return null;
    }

    /**
     * A code this employee may file *and* can pay for.
     *
     * A drawn code with no available credits is dropped from the pool and the
     * draw repeated. Retrying against the same pool instead would re-draw the
     * same exhausted code — `SEL` carries no credits in this dataset and `BL`
     * only three days, so a Job Order drawing for a third filing would spin
     * until it gave up and filed nothing.
     *
     * @param array<int, string> $codes
     * @param Collection<string, LeaveBalance> $balances
     */
    private function drawAffordableCode(array $codes, Collection $balances, int $days): ?string
    {
        for ($attempt = 0; $attempt < self::CREDIT_DRAW_ATTEMPTS && $codes !== []; $attempt++) {
            $leaveCode = $codes[$this->draw(0, count($codes) - 1)];
            $balance = $balances[$leaveCode] ?? null;

            if ($balance !== null && round((float) $balance->available_credits, 6) >= $days) {
                return $leaveCode;
            }

            $codes = array_values(array_filter($codes, fn (string $code) => $code !== $leaveCode));
        }

        // The draws were unlucky rather than the employee being out of credits;
        // take any code they can still afford rather than filing nothing over a
        // random choice nobody sees.
        foreach ($codes as $leaveCode) {
            $balance = $balances[$leaveCode] ?? null;

            if ($balance !== null && round((float) $balance->available_credits, 6) >= $days) {
                return $leaveCode;
            }
        }

        return null;
    }

    /**
     * The codes an employee is entitled to file, with repeats carrying weight.
     *
     * The weighted list is a preference, not an entitlement: each candidate is
     * then checked against the leave type's own `requires_6_months` and the
     * employee's appointment date, and a code they could not be granted is
     * dropped rather than filed and left for an approver to refuse.
     *
     * @return array<int, string>
     */
    private function fileableCodes(Employee $employee): array
    {
        $appointed = $this->employmentDetailFor($employee->id)?->appointment_date;
        $tenured = $appointed !== null
            && Carbon::parse($appointed)->diffInMonths(Carbon::parse(self::WINDOW_END)) >= self::SERVICE_MONTHS_REQUIRED;

        $codes = [];

        foreach (self::PERMANENT_CODE_WEIGHTS as $leaveCode) {
            $leaveType = $this->leaveType($leaveCode);

            if ($leaveType !== null && $leaveType->requires_6_months && ! $tenured) {
                continue;
            }

            if ($leaveCode === 'MCL' && $employee->sex !== 'Female') {
                continue;
            }

            $codes[] = $leaveCode;
        }

        return $codes;
    }

    /**
     * Every weekday from 2026-08-03 to 2026-09-11 inclusive.
     *
     * Weekend arithmetic goes through `CscTimeConversionService::isWeekend()`
     * rather than a local `dayOfWeek` test, so a leave day the seeder dates is
     * a working day by the same rule the observers, the attendance register and
     * the payroll computations use.
     *
     * @return array<int, Carbon>
     */
    private function weekdaysInWindow(): array
    {
        $weekdays = [];
        $date = Carbon::parse(self::WINDOW_START);
        $end = Carbon::parse(self::WINDOW_END);

        while ($date->lte($end)) {
            if (! CscTimeConversionService::isWeekend($date)) {
                $weekdays[] = $date->copy();
            }

            $date->addDay();
        }

        return $weekdays;
    }

    /**
     * Where this year's numbering carries on from.
     *
     * `LeaveApplication::generateApplicationNumber()` cannot be used here. It
     * reads the newest application in the table on every call, and these rows
     * are inserted inside their own transactions — so every one of the seventy
     * would be handed the same number and the second would be rejected by
     * `leave_applications_application_number_unique`. The sequence is therefore
     * read once and incremented per filing.
     */
    private function startingSequenceNumber(array $employees): int
    {
        $last = DB::table('leave_applications')
            ->whereIn('employee_id', array_keys($employees))
            ->whereYear('created_at', date('Y'))
            ->orderByDesc('id')
            ->value('application_number');

        if (! $last) {
            return self::APPLICATION_NUMBER_FALLBACK_SEQ;
        }

        return (int) substr($last, -4);
    }

    /** The next LA-<year>-<0000> reference. */
    private function mintApplicationNumber(): string
    {
        $this->nextApplicationSequence++;

        return 'LA-' . date('Y') . '-' . str_pad((string) $this->nextApplicationSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * A refusal that names a real ground.
     *
     * The ground is drawn rather than keyed to the leave code, so a refusal is
     * not always the same sentence for the same type of leave. It is a draw
     * either way, but nothing here names the leave type, so a refusal can be
     * read against the application it belongs to without contradicting it.
     *
     * @return array{ground: string, remarks: string}
     */
    private function rejectionFor(): array
    {
        return self::REJECTION_GROUNDS[$this->draw(0, count(self::REJECTION_GROUNDS) - 1)];
    }

    /**
     * The active leave types these applications are filed against, keyed by
     * leave code — the code is what a balance, a transaction and an application
     * all join on.
     *
     * @return Collection<string, LeaveType>
     */
    private function leaveTypes(): Collection
    {
        return $this->leaveTypes ??= LeaveType::where('is_active', true)->get()->keyBy('leave_code');
    }

    /** One leave type by code, or null when the code is not configured. */
    private function leaveType(string $leaveCode): ?LeaveType
    {
        return $this->leaveTypes()->get($leaveCode);
    }

    /** The administrator a decision is attributed to, resolved once. */
    private function adminUserId(): int
    {
        return $this->adminUserId ??= (int) (User::whereJsonContains('roles', 'admin')->value('id') ?? 1);
    }
}
