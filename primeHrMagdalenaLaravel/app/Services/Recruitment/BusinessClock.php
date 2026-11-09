<?php

namespace App\Services\Recruitment;

use App\Models\Vacancy;
use Illuminate\Support\Carbon;

/**
 * The one clock, and the one *calendar*, for recruitment.
 *
 * The reference backend had two answers to "what day is it today" — one in UTC
 * and one in the host's local zone — and they disagreed for eight hours out of
 * every twenty-four, which silently kept expired positions OPEN and unarchived
 * overnight. This class is the native port's only conversion point.
 *
 * The rule:
 *
 *   - **A calendar day** (a closing date, a decision's date, "is this in the
 *     future") is a day in the business timezone.
 *   - **An instant** (when a vacancy was published, when an application was
 *     rejected) is stored as Laravel stores every timestamp — in the
 *     application's configured timezone.
 *
 * Laravel's `now()` already follows `config('app.timezone')`, which is
 * Asia/Manila for this installation, so the two agree by construction. That is
 * deliberately simpler than the reference's UTC-storage/Manila-calendar split:
 * there is no second convention here for a caller to get wrong.
 */
final class BusinessClock
{
    public static function timezone(): string
    {
        return (string) config('hiring.business_timezone', config('app.timezone', 'Asia/Manila'));
    }

    /** The current instant, in the business timezone. */
    public static function now(): Carbon
    {
        return Carbon::now(self::timezone());
    }

    /** Today's date, as the office would write it. */
    public static function today(): Carbon
    {
        return Carbon::today(self::timezone());
    }

    /** Parse a stored date (or a date string) as a business-calendar day. */
    public static function day(Carbon|string|null $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value, self::timezone())->startOfDay();
    }

    /**
     * The instant a business day ends — midnight opening the *next* day.
     *
     * Half-open on purpose: `<= 23:59:59` silently drops the last microsecond
     * of the day and invites the off-by-one where one caller compares with
     * `<=` and another with `<` start-of-next.
     */
    public static function endOfDay(Carbon|string $day): Carbon
    {
        return self::day($day)->addDay()->startOfDay();
    }

    /**
     * When a position's candidate-facing grace window expires.
     *
     * Anchored to the END of the closing day, not its start: a position closing
     * on the 30th is open all of the 30th, so a seven-day window runs from the
     * 31st. Anchoring to 00:00 on the closing date would quietly hand
     * candidates a six-day window.
     */
    public static function graceExpiry(Carbon|string $closingDate): Carbon
    {
        return self::endOfDay($closingDate)->addDays(self::gracePeriodDays());
    }

    public static function gracePeriodDays(): int
    {
        return max(7, (int) config('hiring.grace_period_days', 7));
    }

    /**
     * True once the closing day is fully over.
     *
     * A position closes at the END of its closing_date, so this is `<` against
     * today, never `<=`: on the closing date itself the position is still
     * accepting applications.
     */
    public static function isPastClosing(Vacancy $vacancy, ?Carbon $asOf = null): bool
    {
        $closing = self::day($vacancy->closing_date);

        if ($closing === null) {
            return false;
        }

        return $closing->lt($asOf?->copy()->startOfDay() ?? self::today());
    }

    /**
     * Days left before a closing date, negative once it has passed.
     *
     * Compared as calendar dates: a position closing today answers 0 ("closes
     * today"), not -1.
     */
    public static function daysUntil(Carbon|string|null $date): ?int
    {
        $day = self::day($date);

        return $day === null ? null : (int) self::today()->diffInDays($day, false);
    }
}
