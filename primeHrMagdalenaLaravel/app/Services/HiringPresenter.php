<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * The vocabulary shared by every Recruitment page.
 *
 * The recruitment tables store codes (`OPEN`, `NOT_QUALIFIED`, …) and ISO
 * timestamps. Every Blade file that renders one needs the same label, the same
 * badge colour and the same date format — and six views each carrying their own
 * `match` statement is how the archive tab ends up calling a lapsed position
 * "Lapsed" while the detail page calls it "Expired".
 *
 * So the maps live here, once. They mirror the lifecycle service's constants
 * and the profile status columns. A code this class has not seen is still
 * rendered — headline-cased, in the neutral badge — rather than dropped, so a
 * state added later shows up as an unfamiliar word instead of a blank cell.
 */
class HiringPresenter
{
    /**
     * Position lifecycle states (`lifecycle.ALL_STATUSES`).
     *
     * Colour is never the only cue — the label is always beside it — but the
     * mapping does carry meaning: green is live, amber is paused, red is
     * something somebody must decide about or that was withdrawn, grey is done.
     */
    public const VACANCY_STATUSES = [
        'DRAFT'     => ['label' => 'Draft',     'badge' => 'is-info'],
        'OPEN'      => ['label' => 'Open',      'badge' => 'is-success'],
        'ON_HOLD'   => ['label' => 'On hold',   'badge' => 'is-warning'],
        'LAPSED'    => ['label' => 'Lapsed',    'badge' => 'is-danger'],
        'CLOSED'    => ['label' => 'Closed',    'badge' => 'is-neutral'],
        'CANCELLED' => ['label' => 'Cancelled', 'badge' => 'is-danger'],
    ];

    /** The approval gate on a portal application. */
    public const REVIEW_STATUSES = [
        'PENDING'     => ['label' => 'Pending',     'badge' => 'is-warning'],
        'ACCEPTED'    => ['label' => 'Accepted',    'badge' => 'is-success'],
        'DISAPPROVED' => ['label' => 'Disapproved', 'badge' => 'is-danger'],
    ];

    /** Resume extraction: PENDING → PARSING → DONE | FAILED. */
    public const PARSE_STATUSES = [
        'PENDING' => ['label' => 'Queued',   'badge' => 'is-neutral'],
        'PARSING' => ['label' => 'Screening', 'badge' => 'is-info'],
        'DONE'    => ['label' => 'Screened', 'badge' => 'is-success'],
        'FAILED'  => ['label' => 'Failed',   'badge' => 'is-danger'],
    ];

    /** Where a candidate stands after ranking. */
    public const SELECTION_STATUSES = [
        'PENDING'             => ['label' => 'Awaiting review', 'badge' => 'is-neutral'],
        'QUALIFIED_INTERVIEW' => ['label' => 'For interview',   'badge' => 'is-info'],
        'HIRED'               => ['label' => 'Appointed',       'badge' => 'is-success'],
        'NOT_QUALIFIED'       => ['label' => 'Not qualified',   'badge' => 'is-danger'],
    ];

    /**
     * Controlled cancellation reasons (`lifecycle.CANCEL_REASONS`).
     *
     * A controlled list rather than free text so cancellations are countable
     * by cause; `OTHER` requires detail, which the backend also enforces.
     */
    public const CANCEL_REASONS = [
        'BUDGET_CUT'          => 'Budget cut',
        'ROLE_RESTRUCTURED'   => 'Role restructured',
        'NO_LONGER_NEEDED'    => 'No longer needed',
        'OTHER'               => 'Other',
    ];

    /**
     * The five CSC criteria a Fit Score is weighted across, in the order the
     * score reads. The keys are the backend's (`scorer.compute_fit_score`).
     */
    public const WEIGHTS = [
        'education'   => 'Education',
        'training'    => 'Training',
        'experience'  => 'Experience',
        'eligibility' => 'Eligibility',
        'skills'      => 'Skills',
    ];

    /**
     * Label + badge modifier for one status code.
     *
     * @param  'vacancy'|'review'|'parse'|'selection'  $kind
     * @return array{label: string, badge: string}
     */
    public static function status(string $kind, ?string $value): array
    {
        $map = match ($kind) {
            'review' => self::REVIEW_STATUSES,
            'parse' => self::PARSE_STATUSES,
            'selection' => self::SELECTION_STATUSES,
            default => self::VACANCY_STATUSES,
        };

        $code = is_string($value) ? strtoupper(trim($value)) : '';

        return $map[$code] ?? [
            'label' => $code === '' ? 'Unknown' : Str::headline($code),
            'badge' => 'is-neutral',
        ];
    }

    /**
     * A Fit Score read as a band. Drives the score chip's colour.
     *
     * @return array{label: string, badge: string}
     */
    public static function fitBand(mixed $score): array
    {
        if (! is_numeric($score)) {
            return ['label' => 'Not scored', 'badge' => 'is-neutral'];
        }

        $score = (float) $score;

        return match (true) {
            $score >= 75 => ['label' => 'Strong', 'badge' => 'is-success'],
            $score >= 50 => ['label' => 'Moderate', 'badge' => 'is-warning'],
            default => ['label' => 'Weak', 'badge' => 'is-danger'],
        };
    }

    /**
     * A date, in the municipality's own calendar.
     *
     * `RecruitmentPresenter` hands the views ISO strings (`2026-11-30` or a
     * timestamp). Parsing them through Carbon and formatting here — rather than
     * `substr`-ing the string — is what stops a closing date reading as the day
     * before.
     */
    public static function date(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y');
        } catch (\Throwable) {
            return null;
        }
    }

    /** A timestamp, with the time, for anything that records an action. */
    public static function dateTime(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y · g:i A');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Days left before a closing date, negative once it has passed.
     *
     * Compared as calendar dates, deliberately: a position closing today is
     * still open all of today, so the answer on the closing date is 0 ("closes
     * today"), not -1.
     */
    public static function daysUntil(?string $date): ?int
    {
        if (! is_string($date) || trim($date) === '') {
            return null;
        }

        try {
            return (int) now()->startOfDay()->diffInDays(Carbon::parse($date)->startOfDay(), false);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Money, in pesos. */
    public static function peso(mixed $amount): string
    {
        return '₱'.number_format((float) $amount, 2);
    }

    /** `0.25` → `25%`, without a trailing `.0`. */
    public static function percent(mixed $value): string
    {
        return rtrim(rtrim(number_format(((float) $value) * 100, 1), '0'), '.').'%';
    }
}
