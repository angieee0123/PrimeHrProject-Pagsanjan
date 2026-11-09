<?php

namespace Tests\Unit;

use App\Exceptions\RecruitmentRuleViolation;
use App\Models\Vacancy;
use App\Services\Recruitment\BusinessClock;
use App\Services\Recruitment\VacancyLifecycleService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsRecruitmentSchema;
use Tests\TestCase;

/**
 * The six-state position lifecycle.
 *
 * These are the guarantees the reference's own test suite encoded, re-expressed
 * against the native service: DRAFT is not public, LAPSED is automatic and
 * reversible, CLOSED and CANCELLED are terminal, and a closing date means the
 * whole of that day.
 */
class RecruitmentLifecycleTest extends TestCase
{
    use BuildsRecruitmentSchema;

    private VacancyLifecycleService $lifecycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildRecruitmentSchema(['vacancies', 'audit_log']);

        $this->lifecycle = new VacancyLifecycleService();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function vacancy(array $overrides = []): Vacancy
    {
        return Vacancy::create(array_merge([
            'position_title' => 'Administrative Officer I',
            'place_of_assignment' => "Mayor's Office",
            'qs_education' => "Bachelor's degree relevant to the job",
            'qs_eligibility' => 'Career Service Professional',
            'required_skills' => ['records management'],
            'weights' => [
                'education' => 0.3,
                'training' => 0.1,
                'experience' => 0.3,
                'eligibility' => 0.1,
                'skills' => 0.2,
            ],
            'posting_date' => '2026-11-01',
            'closing_date' => '2026-12-31',
            'status' => Vacancy::STATUS_OPEN,
        ], $overrides));
    }

    #[Test]
    public function publishing_a_draft_makes_it_public_and_stamps_the_dates(): void
    {
        $vacancy = $this->vacancy([
            'status' => Vacancy::STATUS_DRAFT,
            'posting_date' => '2026-10-01',
            'requisition_open_date' => null,
        ]);

        $this->assertFalse($this->lifecycle->isAcceptingApplications($vacancy));

        $published = $this->lifecycle->publish($vacancy);

        $this->assertSame(Vacancy::STATUS_OPEN, $published->status);
        $this->assertSame(BusinessClock::today()->toDateString(), $published->posting_date->toDateString());
        $this->assertNotNull($published->published_at);
        $this->assertSame(
            $published->posting_date->toDateString(),
            $published->requisition_open_date->toDateString(),
            'A publish backfills the requisition date so time-to-fill starts at publication.'
        );
        $this->assertSame('2027-01-08', $published->grace_period_expires_at->toDateString());
    }

    #[Test]
    public function a_hold_resumes_without_counting_as_a_reopen(): void
    {
        $vacancy = $this->vacancy();

        $this->lifecycle->hold($vacancy, 'Budget freeze');

        $this->assertSame(Vacancy::STATUS_ON_HOLD, $vacancy->status);
        $this->assertNotNull($vacancy->on_hold_at);
        $this->assertSame('Budget freeze', $vacancy->hold_note);

        $resumed = $this->lifecycle->resume($vacancy, Carbon::parse('2027-01-31'));

        $this->assertSame(Vacancy::STATUS_OPEN, $resumed->status);
        $this->assertNull($resumed->on_hold_at);
        $this->assertNull($resumed->hold_note);
        $this->assertSame(0, $resumed->reopen_count, 'A pause is not a reopen.');
        $this->assertSame('2027-01-31', $resumed->closing_date->toDateString());
    }

    #[Test]
    public function closing_is_terminal_and_retires_the_detail_page_at_once(): void
    {
        $vacancy = $this->vacancy();

        $closed = $this->lifecycle->close($vacancy, 'Position filled');

        $this->assertSame(Vacancy::STATUS_CLOSED, $closed->status);
        $this->assertNotNull($closed->closed_at);
        $this->assertTrue($closed->detail_retired);
        $this->assertSame('Position filled', $closed->close_reason);

        $this->expectException(RecruitmentRuleViolation::class);
        $this->expectExceptionCode(0);
        $this->lifecycle->assertTransition($closed, Vacancy::STATUS_OPEN);
    }

    #[Test]
    public function cancellation_requires_a_controlled_reason_and_detail_for_other(): void
    {
        $vacancy = $this->vacancy();

        try {
            $this->lifecycle->cancel($vacancy, 'OTHER');
            $this->fail('A cancellation reason of OTHER without detail should be refused.');
        } catch (RecruitmentRuleViolation $e) {
            $this->assertSame(400, $e->status);
        }

        $cancelled = $this->lifecycle->cancel($vacancy, 'OTHER', 'Reorganized into a new office');

        $this->assertSame(Vacancy::STATUS_CANCELLED, $cancelled->status);
        $this->assertSame('OTHER', $cancelled->cancel_reason);
        $this->assertSame('Reorganized into a new office', $cancelled->cancel_reason_detail);
        $this->assertTrue($cancelled->detail_retired);
    }

    #[Test]
    public function an_unknown_cancellation_reason_is_refused(): void
    {
        $this->expectException(RecruitmentRuleViolation::class);

        $this->lifecycle->cancel($this->vacancy(), 'BECAUSE_I_SAID_SO');
    }

    #[Test]
    public function the_sweep_lapses_only_open_positions_past_their_closing_date(): void
    {
        $overdue = $this->vacancy([
            'position_title' => 'Overdue',
            'closing_date' => Carbon::today()->subDays(2)->toDateString(),
        ]);
        $draft = $this->vacancy([
            'position_title' => 'Draft',
            'status' => Vacancy::STATUS_DRAFT,
            'closing_date' => Carbon::today()->subDays(2)->toDateString(),
        ]);
        $held = $this->vacancy([
            'position_title' => 'Held',
            'status' => Vacancy::STATUS_ON_HOLD,
            'closing_date' => Carbon::today()->subDays(2)->toDateString(),
        ]);

        $lapsed = $this->lifecycle->lapseDue();

        $this->assertCount(1, $lapsed);
        $this->assertSame(Vacancy::STATUS_LAPSED, $overdue->fresh()->status);
        $this->assertNotNull($overdue->fresh()->lapsed_at);
        $this->assertFalse($overdue->fresh()->detail_retired);

        $this->assertSame(Vacancy::STATUS_DRAFT, $draft->fresh()->status, 'A draft never lapses.');
        $this->assertSame(Vacancy::STATUS_ON_HOLD, $held->fresh()->status, 'A hold pauses the clock.');
    }

    #[Test]
    public function a_position_is_still_open_on_its_closing_date(): void
    {
        $vacancy = $this->vacancy(['closing_date' => Carbon::today()->toDateString()]);

        $this->assertTrue($this->lifecycle->isAcceptingApplications($vacancy));

        $vacancy->closing_date = Carbon::today()->subDay();
        $vacancy->save();

        $this->assertFalse(
            $this->lifecycle->isAcceptingApplications($vacancy),
            'Removal from the listing is immediate, not deferred to the sweep.'
        );
        $this->assertSame(Vacancy::STATUS_OPEN, $vacancy->fresh()->status);
    }

    #[Test]
    public function the_sweep_retires_archived_pages_once_the_grace_window_ends(): void
    {
        $vacancy = $this->vacancy([
            'closing_date' => Carbon::today()->subDays(10)->toDateString(),
        ]);

        $this->lifecycle->lapseDue();

        $lapsed = $vacancy->fresh();
        $lapsed->grace_period_expires_at = Carbon::now()->subHour();
        $lapsed->save();

        $retired = $this->lifecycle->retireExpired();

        $this->assertCount(1, $retired);
        $this->assertTrue($lapsed->fresh()->detail_retired);
    }

    #[Test]
    public function a_lapsed_position_reopens_on_the_same_requisition(): void
    {
        $vacancy = $this->vacancy([
            'closing_date' => Carbon::today()->subDays(3)->toDateString(),
        ]);

        $this->lifecycle->lapseDue();
        $lapsed = $vacancy->fresh();
        $lapsedAt = $lapsed->lapsed_at;

        $reopened = $this->lifecycle->reopen($lapsed, Carbon::today()->addMonth());

        $this->assertSame(Vacancy::STATUS_OPEN, $reopened->status);
        $this->assertSame(1, $reopened->reopen_count);
        $this->assertSame(BusinessClock::today()->toDateString(), $reopened->posting_date->toDateString());
        $this->assertNotNull($reopened->lapsed_at, 'Reopening does not erase the record of the lapse.');
        $this->assertSame($lapsedAt?->toDateString(), $reopened->lapsed_at?->toDateString());
        $this->assertFalse($reopened->detail_retired);
    }

    #[Test]
    public function the_grace_window_is_never_shorter_than_the_spec_floor(): void
    {
        config(['hiring.grace_period_days' => 1]);

        $expiry = BusinessClock::graceExpiry('2026-12-31');

        $this->assertSame('2027-01-08', $expiry->toDateString());
    }

    #[Test]
    public function the_scheduled_sweep_command_lapses_what_is_due(): void
    {
        $vacancy = $this->vacancy([
            'closing_date' => Carbon::today()->subDay()->toDateString(),
        ]);

        $this->artisan('recruitment:lifecycle-sweep')->assertExitCode(0);

        $this->assertSame(Vacancy::STATUS_LAPSED, $vacancy->fresh()->status);
    }

    #[Test]
    public function illegal_moves_carry_the_reason_a_reader_needs(): void
    {
        $draft = $this->vacancy(['status' => Vacancy::STATUS_DRAFT]);

        try {
            $this->lifecycle->assertTransition($draft, Vacancy::STATUS_ON_HOLD);
            $this->fail('A draft cannot be put on hold.');
        } catch (RecruitmentRuleViolation $e) {
            $this->assertSame(409, $e->status);
            $this->assertStringContainsString('Cannot move a draft position to on hold', $e->getMessage());
        }

        $closed = $this->vacancy(['status' => Vacancy::STATUS_CLOSED]);

        try {
            $this->lifecycle->assertTransition($closed, Vacancy::STATUS_OPEN);
            $this->fail('A closed position cannot be reopened.');
        } catch (RecruitmentRuleViolation $e) {
            $this->assertStringContainsString('Create a new vacancy instead', $e->getMessage());
        }
    }
}
