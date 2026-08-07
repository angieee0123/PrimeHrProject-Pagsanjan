<?php

namespace Tests\Unit;

use App\Services\AiAccessPolicy;
use App\Services\ChartDataService;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * How a charting request is read.
 *
 * "generate a graph ng sahod ni jeremy pogi weekly sa buwan ng january-august
 * 2026" returned the "I can chart:" list — a refusal — because detectSubject()
 * was the one English-only link in an otherwise bilingual assistant, and
 * because nothing understood weekly buckets or a month span. These pin all
 * three so the question cannot silently regress into that list again.
 *
 * Only the pure parsing is exercised here; the queries behind it need the
 * payroll tables, which the SQLite test connection does not build.
 */
class ChartRequestParsingTest extends TestCase
{
    private ChartDataService $charts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->charts = new ChartDataService(new AiAccessPolicy());
    }

    private function parse(string $method, mixed ...$args): mixed
    {
        $ref = new ReflectionMethod(ChartDataService::class, $method);
        $ref->setAccessible(true);

        return $ref->invoke($this->charts, ...$args);
    }

    #[Test]
    public function the_question_that_was_refused_now_resolves_to_a_payroll_chart(): void
    {
        $this->assertSame(
            'payroll',
            $this->parse('detectSubject', 'generate a graph ng sahod ni jeremy pogi weekly sa buwan ng january- august 2026')
        );
    }

    #[Test]
    public function tagalog_pay_words_are_payroll(): void
    {
        foreach (['sahod', 'suweldo', 'sweldo'] as $word) {
            $this->assertSame('payroll', $this->parse('detectSubject', "chart ng {$word} 2026"), $word);
        }
    }

    /**
     * Bare "pay" named no other keyword, so "the monthly pay of Jeremy" fell
     * through to the capability list.
     */
    #[Test]
    public function bare_pay_is_enough_to_mean_payroll(): void
    {
        $this->assertSame('payroll', $this->parse('detectSubject', 'chart the monthly pay of Jeremy Pogi in 2026'));
    }

    #[Test]
    public function english_subjects_still_route_as_before(): void
    {
        $this->assertSame('payroll', $this->parse('detectSubject', 'graph of payroll expenses 2026'));
        $this->assertSame('attendance_trend', $this->parse('detectSubject', 'chart monthly attendance'));
        $this->assertSame('headcount', $this->parse('detectSubject', 'graph headcount by department'));
        $this->assertSame('gender', $this->parse('detectSubject', 'chart gender distribution'));
        $this->assertNull($this->parse('detectSubject', 'draw me something nice'));
    }

    /**
     * The ordering bug in miniature: the request says "weekly" and also says
     * "buwan" (month), because the months name the range rather than the
     * bucket. The finer unit has to win.
     */
    #[Test]
    public function weekly_beats_the_month_words_naming_the_range(): void
    {
        $this->assertSame(
            'weekly',
            $this->parse('detectGranularity', 'graph ng sahod weekly sa buwan ng january- august 2026')
        );
    }

    #[Test]
    public function granularity_reads_both_languages(): void
    {
        $this->assertSame('weekly', $this->parse('detectGranularity', 'kada linggo'));
        $this->assertSame('weekly', $this->parse('detectGranularity', 'per week please'));
        $this->assertSame('daily', $this->parse('detectGranularity', 'araw-araw'));
        $this->assertSame('daily', $this->parse('detectGranularity', 'show it per day'));
        $this->assertSame('monthly', $this->parse('detectGranularity', 'payroll expenses for 2026'));
    }

    #[Test]
    public function a_span_of_two_months_becomes_that_range(): void
    {
        $range = $this->parse('detectMonthRange', 'sahod weekly sa buwan ng january- august 2026');

        $this->assertSame('2026-01-01', $range['start']->toDateString());
        $this->assertSame('2026-08-31', $range['end']->toDateString());
        $this->assertSame('Jan–Aug 2026', $range['label']);
    }

    #[Test]
    public function a_single_month_becomes_that_month(): void
    {
        $range = $this->parse('detectMonthRange', 'payroll for march 2026');

        $this->assertSame('2026-03-01', $range['start']->toDateString());
        $this->assertSame('2026-03-31', $range['end']->toDateString());
    }

    #[Test]
    public function no_month_named_means_the_whole_year(): void
    {
        $range = $this->parse('detectMonthRange', 'payroll expenses 2026');

        $this->assertSame('2026-01-01', $range['start']->toDateString());
        $this->assertSame('2026-12-31', $range['end']->toDateString());
        $this->assertSame('2026', $range['label']);
    }

    /**
     * "May" is an ordinary English word and the Tagalog for "has", so it only
     * counts as a month once the question is already talking about dates.
     */
    #[Test]
    public function may_is_not_a_month_without_date_context(): void
    {
        $range = $this->parse('detectMonthRange', 'chart employees who may have absences');

        $this->assertSame('01-01', $range['start']->format('m-d'));
        $this->assertSame('12-31', $range['end']->format('m-d'));
    }

    #[Test]
    public function may_is_a_month_when_a_year_is_present(): void
    {
        $range = $this->parse('detectMonthRange', 'payroll for may 2026');

        $this->assertSame('2026-05-01', $range['start']->toDateString());
        $this->assertSame('2026-05-31', $range['end']->toDateString());
    }

    /**
     * A partial edge week is summed from its in-range days only, so labelling
     * it with the ISO week start would print a date outside the window asked
     * for — "Wk Dec 29" at the head of a Jan–Aug chart.
     */
    #[Test]
    public function a_week_starting_before_the_range_is_labelled_from_the_range(): void
    {
        $range = $this->parse('detectMonthRange', 'sahod weekly january to august 2026');

        $this->assertSame('Wk Jan 01', $this->parse('bucketLabel', '2025-12-29', 'weekly', $range));
        $this->assertSame('Wk Jan 05', $this->parse('bucketLabel', '2026-01-05', 'weekly', $range));
    }
}
