<?php

namespace App\Services\Recruitment;

use App\Exceptions\RecruitmentRuleViolation;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Position lifecycle — the single authority on vacancy states and the moves
 * between them.
 *
 * Six states, ported unchanged from the reference's `services/lifecycle.py`:
 *
 *     DRAFT    not yet published — JD being finalised or awaiting approval
 *     OPEN     approved and published, actively recruiting
 *     ON_HOLD  recruitment temporarily paused (budget freeze, reorg, manager ask)
 *     LAPSED   closing date passed with no decision — automatic, this service
 *     CLOSED   position filled — a decision was made
 *     CANCELLED  permanently withdrawn — a decision was made, reason required
 *
 *     DRAFT ──(publish)───────────────────────► OPEN
 *     OPEN ──(hold)───────────────────────────► ON_HOLD
 *     ON_HOLD ──(resume)──────────────────────► OPEN
 *     OPEN ──(closing_date passes, no action)─► LAPSED    automatic, + grace
 *     OPEN/ON_HOLD/LAPSED ──(close)───────────► CLOSED    deliberate, no grace
 *     DRAFT/OPEN/ON_HOLD/LAPSED ──(cancel)────► CANCELLED deliberate, needs reason
 *     LAPSED ──(reopen)───────────────────────► OPEN      deliberate
 *
 * **Only LAPSED reverses out of the terminal-ish group.** CLOSED and CANCELLED
 * record a decision somebody made, and undoing a decision by editing the record
 * erases the fact that it was made. A lapse is not a decision — it is an expiry
 * nobody acted on — so it stays recoverable, which also keeps its applicants
 * attached to the same requisition row rather than orphaning them behind a new
 * one.
 *
 * Everything here is idempotent: the sweep is driven by a scheduled command and
 * may fire against a row a previous run already moved. Transitions are gated on
 * current state, not on elapsed time alone.
 */
class VacancyLifecycleService
{
    /** Spec floor. Clamped in the config so lowering it cannot shorten a promise. */
    public const MIN_GRACE_PERIOD_DAYS = 7;

    /** Every status, in the order the admin tabs read. */
    public const ALL_STATUSES = [
        Vacancy::STATUS_DRAFT,
        Vacancy::STATUS_OPEN,
        Vacancy::STATUS_ON_HOLD,
        Vacancy::STATUS_LAPSED,
        Vacancy::STATUS_CLOSED,
        Vacancy::STATUS_CANCELLED,
    ];

    /** The one status an applicant may ever see in a public listing. */
    public const PUBLICLY_LISTED_STATUSES = [Vacancy::STATUS_OPEN];

    /** Statuses whose detail page still answers publicly, and which archive. */
    public const ARCHIVED_STATUSES = [
        Vacancy::STATUS_LAPSED,
        Vacancy::STATUS_CLOSED,
        Vacancy::STATUS_CANCELLED,
    ];

    /** Can still return to OPEN. */
    public const REVERSIBLE_STATUSES = [
        Vacancy::STATUS_DRAFT,
        Vacancy::STATUS_ON_HOLD,
        Vacancy::STATUS_LAPSED,
    ];

    /** Cannot return to OPEN by any route — a new requisition must be created. */
    public const TERMINAL_STATUSES = [
        Vacancy::STATUS_CLOSED,
        Vacancy::STATUS_CANCELLED,
    ];

    /**
     * Every legal move, as `from => [to, ...]`.
     *
     * The single source of truth: the controller asks this rather than
     * re-deriving the rules per endpoint, which is how two paths to the same
     * transition quietly diverge.
     */
    public const ALLOWED_TRANSITIONS = [
        Vacancy::STATUS_DRAFT => [Vacancy::STATUS_OPEN, Vacancy::STATUS_CANCELLED],
        Vacancy::STATUS_OPEN => [Vacancy::STATUS_ON_HOLD, Vacancy::STATUS_LAPSED, Vacancy::STATUS_CLOSED, Vacancy::STATUS_CANCELLED],
        Vacancy::STATUS_ON_HOLD => [Vacancy::STATUS_OPEN, Vacancy::STATUS_CLOSED, Vacancy::STATUS_CANCELLED],
        Vacancy::STATUS_LAPSED => [Vacancy::STATUS_OPEN, Vacancy::STATUS_CLOSED, Vacancy::STATUS_CANCELLED],
        Vacancy::STATUS_CLOSED => [],
        Vacancy::STATUS_CANCELLED => [],
    ];

    /**
     * Reasons an admin may give for cancelling.
     *
     * A controlled list rather than free text so cancellations are countable in
     * reports — "budget cut" typed five different ways aggregates to nothing.
     */
    public const CANCEL_REASONS = [
        'BUDGET_CUT' => 'Budget cut',
        'ROLE_RESTRUCTURED' => 'Role restructured',
        'NO_LONGER_NEEDED' => 'No longer needed',
        'OTHER' => 'Other',
    ];

    public const LABELS = [
        Vacancy::STATUS_DRAFT => 'Draft',
        Vacancy::STATUS_OPEN => 'Open',
        Vacancy::STATUS_ON_HOLD => 'On hold',
        Vacancy::STATUS_LAPSED => 'Lapsed',
        Vacancy::STATUS_CLOSED => 'Closed',
        Vacancy::STATUS_CANCELLED => 'Cancelled',
    ];

    // ─────────────────────────────────────────────────────────────────────
    // The state machine
    // ─────────────────────────────────────────────────────────────────────

    public function canTransition(?string $current, string $target): bool
    {
        return in_array($target, self::ALLOWED_TRANSITIONS[$current] ?? [], true);
    }

    /**
     * Guard every deliberate transition. Throws 409 if the move is illegal.
     *
     * Terminal statuses get their own message: "already closed" and "closed
     * positions cannot be reopened" are different facts, and an admin who tried
     * to revive a filled position needs to be told to create a new requisition,
     * not that they clicked the wrong button.
     */
    public function assertTransition(Vacancy $vacancy, string $target): void
    {
        $current = $vacancy->status;

        if ($current === $target) {
            throw RecruitmentRuleViolation::conflict(
                'This position is already '.$this->label($target).'.'
            );
        }

        if (in_array($current, self::TERMINAL_STATUSES, true) && $target === Vacancy::STATUS_OPEN) {
            throw RecruitmentRuleViolation::conflict(
                $this->label($current).' is final — this position cannot be reopened. Create a new vacancy instead.'
            );
        }

        if (! $this->canTransition($current, $target)) {
            throw RecruitmentRuleViolation::conflict(
                'Cannot move a '.mb_strtolower($this->label($current)).' position to '
                .mb_strtolower($this->label($target)).'.'
            );
        }
    }

    public function label(?string $status): string
    {
        return self::LABELS[$status] ?? ($status === null || $status === '' ? 'Unknown' : \Illuminate\Support\Str::headline($status));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Automatic transitions (the scheduled sweep)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Move every OPEN position whose closing date has passed to LAPSED.
     *
     * Gated on OPEN alone, deliberately. A DRAFT whose closing date slipped by
     * while it sat unpublished has never been advertised, and an ON_HOLD
     * position is paused precisely so the clock stops mattering — auto-lapsing
     * either would fabricate an expiry for something that was never running.
     *
     * @return array<int, Vacancy>
     */
    public function lapseDue(?Carbon $asOf = null): array
    {
        $today = ($asOf ?? BusinessClock::today())->copy()->startOfDay();

        $due = Vacancy::query()
            ->where('status', Vacancy::STATUS_OPEN)
            ->whereDate('closing_date', '<', $today->toDateString())
            ->get();

        $lapsed = [];

        foreach ($due as $vacancy) {
            $vacancy->status = Vacancy::STATUS_LAPSED;
            $vacancy->lapsed_at = BusinessClock::now();
            // Recomputed at lapse time rather than trusted from creation: the
            // closing date may have been edited since, and the grace window
            // must follow the date the position actually closed on.
            $vacancy->grace_period_expires_at = BusinessClock::graceExpiry($vacancy->closing_date);
            $vacancy->detail_retired = false;
            $vacancy->save();

            $lapsed[] = $vacancy;
        }

        return $lapsed;
    }

    /**
     * Flip `detail_retired` once the grace window has elapsed.
     *
     * Candidate-facing output does not change here — the same page and the same
     * copy serve both sides of this flag. What changes is that retirement
     * becomes an explicit, timestamped event the archive and reporting can see,
     * rather than an implicit date comparison recomputed on every read.
     *
     * @return array<int, Vacancy>
     */
    public function retireExpired(?Carbon $now = null): array
    {
        $moment = $now ?? BusinessClock::now();

        $due = Vacancy::query()
            ->whereIn('status', self::ARCHIVED_STATUSES)
            ->where('detail_retired', false)
            ->whereNotNull('grace_period_expires_at')
            ->where('grace_period_expires_at', '<=', $moment)
            ->get();

        foreach ($due as $vacancy) {
            $vacancy->detail_retired = true;
            $vacancy->save();
        }

        return $due->all();
    }

    /**
     * One full pass: lapse what is due, then retire what is expired.
     *
     * Ordered so a position that lapsed long ago and is already past its grace
     * window settles in a single run rather than needing two.
     *
     * @return array{lapsed: int, retired: int}
     */
    public function sweep(?Carbon $asOf = null): array
    {
        $lapsed = $this->lapseDue($asOf);
        $retired = $this->retireExpired();

        return ['lapsed' => count($lapsed), 'retired' => count($retired)];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Deliberate transitions (recruiter/admin actions)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * DRAFT → OPEN. The moment a position becomes public.
     *
     * `posting_date` resets to today rather than keeping whatever was typed
     * while drafting: the public "posted on" date should be when candidates
     * could first see it, not when someone started writing the JD.
     * `requisition_open_date` is backfilled here too if it was never set, so
     * time-to-fill measures from publication for a position drafted first.
     */
    public function publish(Vacancy $vacancy, ?User $actor = null): Vacancy
    {
        $today = BusinessClock::today();

        $vacancy->status = Vacancy::STATUS_OPEN;
        $vacancy->posting_date = $today;
        $vacancy->published_at = BusinessClock::now();
        $vacancy->requisition_open_date ??= $today;
        $vacancy->grace_period_expires_at = BusinessClock::graceExpiry($vacancy->closing_date);
        $vacancy->detail_retired = false;
        $vacancy->save();

        return $vacancy->refresh();
    }

    /**
     * OPEN → ON_HOLD. Recruitment paused; the requisition is expected back.
     *
     * Nothing about the applicant pool changes. The position simply stops being
     * listed and stops accepting new applications, both of which fall out of
     * `status != OPEN` without any extra rule.
     */
    public function hold(Vacancy $vacancy, ?string $note = null, ?User $actor = null): Vacancy
    {
        $vacancy->status = Vacancy::STATUS_ON_HOLD;
        $vacancy->on_hold_at = BusinessClock::now();
        $vacancy->hold_note = $note;
        $vacancy->save();

        return $vacancy->refresh();
    }

    /**
     * ON_HOLD → OPEN. Recruitment restarts.
     *
     * Not a reopen: `reopen_count` is untouched, because the requisition never
     * left service — it was paused, and counting a pause as a reopen would
     * corrupt the reporting that metric exists for.
     *
     * A new closing date may be supplied, and is required in practice if the
     * old one elapsed during the pause — otherwise the next sweep would lapse
     * the position immediately, which is a confusing way to come back.
     */
    public function resume(Vacancy $vacancy, ?Carbon $closingDate = null, ?User $actor = null): Vacancy
    {
        $vacancy->status = Vacancy::STATUS_OPEN;
        $vacancy->on_hold_at = null;
        $vacancy->hold_note = null;

        if ($closingDate !== null) {
            $vacancy->closing_date = $closingDate;
        }

        $vacancy->grace_period_expires_at = BusinessClock::graceExpiry($vacancy->closing_date);
        $vacancy->detail_retired = false;
        $vacancy->save();

        return $vacancy->refresh();
    }

    /**
     * → CANCELLED. Permanently withdrawn. Terminal.
     *
     * A reason is mandatory and drawn from CANCEL_REASONS, because the point of
     * recording it is to count cancellations by cause in reports — free text
     * cannot be aggregated. `cancel_reason_detail` carries the elaboration and
     * is required when the reason is OTHER, which is otherwise a hole straight
     * back to unstructured data.
     */
    public function cancel(Vacancy $vacancy, string $reason, ?string $reasonDetail = null, ?User $actor = null): Vacancy
    {
        if (! array_key_exists($reason, self::CANCEL_REASONS)) {
            throw RecruitmentRuleViolation::badRequest('Unknown cancellation reason.');
        }

        $reasonDetail = $reasonDetail !== null ? trim($reasonDetail) : null;

        if ($reason === 'OTHER' && ($reasonDetail === null || $reasonDetail === '')) {
            throw RecruitmentRuleViolation::badRequest(
                'A cancellation reason of "Other" needs a short explanation.'
            );
        }

        $vacancy->status = Vacancy::STATUS_CANCELLED;
        $vacancy->cancelled_at = BusinessClock::now();
        $vacancy->cancel_reason = $reason;
        $vacancy->cancel_reason_detail = $reasonDetail ?: null;
        // Same immediacy as a close: a deliberate decision is not an expiry, so
        // the detail page retires at once rather than lingering through a grace
        // window.
        $vacancy->detail_retired = true;
        $vacancy->grace_period_expires_at = $vacancy->cancelled_at;
        $vacancy->save();

        return $vacancy->refresh();
    }

    /**
     * → CLOSED. The position was filled. Terminal.
     *
     * No grace period, by design: this is a deliberate recruiter decision, not
     * an automatic expiry, so the detail page retires immediately alongside
     * removal from the public listing.
     */
    public function close(Vacancy $vacancy, ?string $reason = null, ?User $actor = null): Vacancy
    {
        $vacancy->status = Vacancy::STATUS_CLOSED;
        $vacancy->closed_at = BusinessClock::now();
        $vacancy->close_reason = $reason;
        $vacancy->detail_retired = true;
        $vacancy->grace_period_expires_at = $vacancy->closed_at;
        $vacancy->save();

        return $vacancy->refresh();
    }

    /**
     * Bring a LAPSED position back to OPEN on the SAME record.
     *
     * LAPSED only: reopening a CLOSED or CANCELLED position is not possible,
     * because both record a decision a human made and silently reversing it
     * would erase the fact that it happened.
     *
     * The requisition row is reused rather than cloned. That is the whole point:
     * every prior applicant stays attached and the recruiter still sees the full
     * historical pool. It is also why reopen matters more than "just make a new
     * one" — a new row orphans the pool.
     *
     * Two dates move independently:
     *   · posting_date        RESETS to today   — public freshness for sorting
     *   · requisition_open_date  NEVER moves    — true age, for time-to-fill
     */
    public function reopen(Vacancy $vacancy, Carbon|string $closingDate, ?User $actor = null): Vacancy
    {
        $vacancy->status = Vacancy::STATUS_OPEN;
        $vacancy->closing_date = $closingDate;
        $vacancy->posting_date = BusinessClock::today();
        $vacancy->reopened_at = BusinessClock::now();
        $vacancy->reopen_count = ((int) $vacancy->reopen_count) + 1;
        $vacancy->detail_retired = false;
        $vacancy->grace_period_expires_at = BusinessClock::graceExpiry($closingDate);
        // lapsed_at / closed_at are deliberately NOT cleared — they are the
        // audit trail of what happened to this requisition, and reopening does
        // not unmake a past lapse or closure.
        $vacancy->save();

        return $vacancy->refresh();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Read-side helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * The single gate an apply attempt must pass.
     *
     * An allow-list, not a blocklist: only OPEN qualifies, so DRAFT, ON_HOLD and
     * CANCELLED are excluded by construction and any status added later is
     * excluded by default. It checks the date as well as the status so a
     * position is closed to new applications the instant its closing date
     * passes, even if the sweep has not run yet.
     */
    public function isAcceptingApplications(Vacancy $vacancy): bool
    {
        return in_array($vacancy->status, self::PUBLICLY_LISTED_STATUSES, true)
            && ! BusinessClock::isPastClosing($vacancy);
    }

    /** Whether a position belongs in an applicant-facing listing. */
    public function isPubliclyListed(Vacancy $vacancy): bool
    {
        return $this->isAcceptingApplications($vacancy);
    }

    /**
     * Create a vacancy from validated form data.
     *
     * A draft is not public and never lapses — it sits until somebody publishes
     * it, which is the point: a JD still being written should not be advertised
     * and should not acquire an expiry it never earned.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): Vacancy
    {
        $initialStatus = $data['status'] ?? Vacancy::STATUS_OPEN;

        $vacancy = new Vacancy($data);
        $vacancy->status = $initialStatus;
        $vacancy->created_by = $actor->getAuthIdentifier();
        $vacancy->assigned_recruiter_id = $actor->getAuthIdentifier();
        // Both dates start together and diverge only on the first reopen. A
        // draft has not been published, so it has no requisition_open_date yet —
        // publish backfills it.
        $vacancy->requisition_open_date = $initialStatus === Vacancy::STATUS_OPEN
            ? $vacancy->posting_date
            : null;
        $vacancy->published_at = $initialStatus === Vacancy::STATUS_OPEN
            ? BusinessClock::now()
            : null;
        $vacancy->grace_period_expires_at = BusinessClock::graceExpiry($vacancy->closing_date);
        $vacancy->detail_retired = false;
        $vacancy->save();

        return $vacancy->refresh();
    }
}
