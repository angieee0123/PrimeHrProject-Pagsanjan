<?php

namespace App\Services\Recruitment;

use Illuminate\Support\Carbon;

/**
 * Date-range → years parser for resume experience.
 *
 * Ported from `services/date_range_parser.py`. The behaviours that matter are
 * the reference's own decisions, kept deliberately:
 *
 *  - **Month-level precision.** A day typed in a resume ("Aug 17, 2017") is
 *    ignored and read as the first of the month; an end date is expanded to the
 *    end of its month before inclusive day counting, because "Jan 2020 – Apr
 *    2020" means all of April.
 *  - **Overlaps merge first.** Two jobs held at once are one stretch of
 *    calendar time, not two; otherwise a candidate could claim double years.
 *  - **Present resolves to today** in the business calendar, so the answer is
 *    stable for a whole day rather than moving hour by hour.
 *
 * Supported shapes: `Aug 17, 2017 – Present`, `Jan 2025 - Apr 2025`,
 * `08/2019 - 02/2020`, `YYYY-MM / MM/YYYY`, `YYYY - YYYY`,
 * `YYYY – Kasalukuyan`, with hyphen, en dash or em dash.
 */
class ExperienceDateParser
{
    private const MONTH_RE = '(?:jan\.?|january|feb\.?|february|mar\.?|march|apr\.?|april|may|jun\.?|june|jul\.?|july|aug\.?|august|sep\.?|sept\.?|september|oct\.?|october|nov\.?|november|dec\.?|december)';

    private const PRESENT_RE = '(?:present|current|now|ongoing|kasalukuyan)';

    private const DATE_CORE = '(?:(?:'.self::MONTH_RE.')\s+\d{1,2},?\s*\d{4}|(?:'.self::MONTH_RE.')\s+\d{4}|\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}|\d{1,2}[\/\-]\d{4}|\d{4}[\/\-]\d{1,2}|\d{4})';

    private const RANGE_RE = '~(?P<start>'.self::DATE_CORE.')\s*[\-\x{2013}\x{2014}]\s*(?P<end>'.self::DATE_CORE.'|'.self::PRESENT_RE.')~iu';

    private const YEAR_RANGE_RE = '~(?P<start_year>\d{4})\s*[\-\x{2013}\x{2014}]\s*(?P<end>\d{4}|'.self::PRESENT_RE.')~iu';

    private const MONTHS = [
        'jan' => 1, 'january' => 1,
        'feb' => 2, 'february' => 2,
        'mar' => 3, 'march' => 3,
        'apr' => 4, 'april' => 4,
        'may' => 5,
        'jun' => 6, 'june' => 6,
        'jul' => 7, 'july' => 7,
        'aug' => 8, 'august' => 8,
        'sep' => 9, 'sept' => 9, 'september' => 9,
        'oct' => 10, 'october' => 10,
        'nov' => 11, 'november' => 11,
        'dec' => 12, 'december' => 12,
    ];

    /**
     * Every merged experience range in a document.
     *
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    public function parseRanges(string $text, ?Carbon $present = null): array
    {
        $present = ($present ?? BusinessClock::today())->copy()->startOfDay();

        $ranges = [];
        $seen = [];

        if (preg_match_all(self::RANGE_RE, $text, $matches, PREG_SET_ORDER) !== false) {
            foreach ($matches as $match) {
                $startRaw = $match['start'];
                $endRaw = $match['end'];

                $start = $this->parseSingleDate($startRaw, $present);
                $end = $this->parseSingleDate($endRaw, $present);

                if ($start === null || $end === null) {
                    continue;
                }

                $endIsPresent = $this->isPresentToken($endRaw);

                // Expand a month-precision end to the end of that month. A bare
                // year is left at Jan 1, matching the reference: "2020 - 2021"
                // has never meant "through December 2021".
                if (! $endIsPresent
                    && (preg_match('~'.self::MONTH_RE.'~iu', $endRaw) === 1
                        || preg_match('~\d{1,2}[\/\-]\d{4}~u', $endRaw) === 1)) {
                    $end = $end->copy()->endOfMonth()->startOfDay();
                }

                if ($end->lt($start)) {
                    continue;
                }

                $key = $start->toDateString().'|'.$end->toDateString();

                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $ranges[] = ['start' => $start, 'end' => $end];
            }
        }

        // Secondary pass: a year-only range such as "2020 - 2021", which the
        // verbose primary pattern can lose to a surrounding token.
        if (preg_match_all(self::YEAR_RANGE_RE, $text, $yearMatches, PREG_SET_ORDER) !== false) {
            foreach ($yearMatches as $match) {
                $startYear = (int) $match['start_year'];
                $endRaw = $match['end'];

                if ($startYear < 1900 || $startYear > 2100) {
                    continue;
                }

                if ($this->isPresentToken($endRaw)) {
                    $end = $present->copy();
                } else {
                    $endYear = (int) $endRaw;

                    if ($endYear < 1900 || $endYear > 2100) {
                        continue;
                    }

                    $end = Carbon::create($endYear, 1, 1, 0, 0, 0, BusinessClock::timezone())->startOfDay();
                }

                $start = Carbon::create($startYear, 1, 1, 0, 0, 0, BusinessClock::timezone())->startOfDay();

                if ($end->lt($start)) {
                    continue;
                }

                $key = $start->toDateString().'|'.$end->toDateString();

                if (isset($seen[$key])) {
                    continue;
                }

                // A richer range already starting in this year wins; otherwise
                // "Jan 2020 - Present" and "2020 - Present" would both count.
                $dominated = false;

                foreach ($ranges as $existing) {
                    if ((int) $existing['start']->year === $startYear
                        && ! $existing['start']->isSameDay($start)) {
                        $dominated = true;
                        break;
                    }
                }

                if ($dominated) {
                    continue;
                }

                $seen[$key] = true;
                $ranges[] = ['start' => $start, 'end' => $end];
            }
        }

        return $this->mergeOverlaps($ranges);
    }

    /** Total experience years from date ranges, merged, to one decimal. */
    public function totalYearsFromText(string $text, ?Carbon $present = null): float
    {
        $ranges = $this->parseRanges($text, $present);

        if ($ranges === []) {
            return 0.0;
        }

        $days = 0;

        foreach ($ranges as $range) {
            $days += $this->inclusiveDays($range['start'], $range['end']);
        }

        return round($days / 365.25, 1);
    }

    /**
     * Audit-friendly ranges with their durations, for the parsed JSON.
     *
     * @return array<int, array{start: string, end: string, days: int, years: float}>
     */
    public function rangesForAudit(string $text, ?Carbon $present = null): array
    {
        $out = [];

        foreach ($this->parseRanges($text, $present) as $range) {
            $days = $this->inclusiveDays($range['start'], $range['end']);
            $out[] = [
                'start' => $range['start']->toDateString(),
                'end' => $range['end']->toDateString(),
                'days' => $days,
                'years' => round($days / 365.25, 1),
            ];
        }

        return $out;
    }

    /**
     * Merge overlapping or contiguous ranges. Inclusive ends.
     *
     * @param  array<int, array{start: Carbon, end: Carbon}>  $ranges
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    public function mergeOverlaps(array $ranges): array
    {
        if ($ranges === []) {
            return [];
        }

        usort($ranges, static fn (array $a, array $b) => $a['start']->getTimestamp() <=> $b['start']->getTimestamp());

        $merged = [$ranges[0]];

        foreach (array_slice($ranges, 1) as $range) {
            $lastIndex = count($merged) - 1;
            $last = $merged[$lastIndex];

            if ($range['start']->lte($last['end']->copy()->addDay())) {
                if ($range['end']->gt($last['end'])) {
                    $merged[$lastIndex]['end'] = $range['end'];
                }
            } else {
                $merged[] = $range;
            }
        }

        return $merged;
    }

    private function parseSingleDate(string $raw, Carbon $present): ?Carbon
    {
        $value = trim($raw);

        if ($this->isPresentToken($value)) {
            return $present->copy();
        }

        // A value with no four-digit year cannot be trusted: the reference's
        // fuzzy parser would otherwise fill in its default year (2000).
        if (preg_match('/\d{4}/', $value) !== 1) {
            return null;
        }

        $year = null;
        $month = null;

        if (preg_match('~^('.self::MONTH_RE.')\s+\d{1,2},?\s*(\d{4})$~iu', $value, $m) === 1) {
            $month = $this->monthNumber($m[1]);
            $year = (int) $m[2];
        } elseif (preg_match('~^('.self::MONTH_RE.')\s+(\d{4})$~iu', $value, $m) === 1) {
            $month = $this->monthNumber($m[1]);
            $year = (int) $m[2];
        } elseif (preg_match('~^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$~u', $value, $m) === 1) {
            $month = (int) $m[1];
            $year = (int) $m[3];
        } elseif (preg_match('~^(\d{1,2})[\/\-](\d{4})$~u', $value, $m) === 1) {
            $month = (int) $m[1];
            $year = (int) $m[2];
        } elseif (preg_match('~^(\d{4})[\/\-](\d{1,2})$~u', $value, $m) === 1) {
            $month = (int) $m[2];
            $year = (int) $m[1];
        } elseif (preg_match('~^(\d{4})$~u', $value, $m) === 1) {
            $month = 1;
            $year = (int) $m[1];
        }

        if ($year === null || $month === null
            || $year < 1900 || $year > 2100
            || $month < 1 || $month > 12) {
            return null;
        }

        // Month precision: the day is discarded, deliberately.
        return Carbon::create($year, $month, 1, 0, 0, 0, BusinessClock::timezone())->startOfDay();
    }

    private function isPresentToken(string $value): bool
    {
        return preg_match('~^('.self::PRESENT_RE.')$~iu', trim($value)) === 1;
    }

    private function monthNumber(string $raw): ?int
    {
        $key = mb_strtolower(rtrim(trim($raw), '.'));

        return self::MONTHS[$key] ?? null;
    }

    private function inclusiveDays(Carbon $start, Carbon $end): int
    {
        return (int) round($start->diffInDays($end)) + 1;
    }
}
