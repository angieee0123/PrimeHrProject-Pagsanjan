<?php

namespace Tests\Unit;

use App\Exceptions\RecruitmentRuleViolation;
use App\Models\ApplicantProfile;
use App\Models\Candidate;
use App\Models\ScreeningResult;
use App\Models\Vacancy;
use App\Services\Recruitment\ResumeIntakeService;
use App\Services\Recruitment\ScreeningPipeline;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsRecruitmentSchema;
use Tests\TestCase;

/**
 * The screening pipeline end to end: intake → extract → anonymize → parse →
 * score → explain → persist → rank.
 *
 * The vacancy, the candidate, the application and the score are real rows in
 * the in-memory database; only the file is fake. That is the same path an HR
 * upload takes, so a regression in any step fails here rather than in a page.
 */
class RecruitmentPipelineTest extends TestCase
{
    use BuildsRecruitmentSchema;

    private ResumeIntakeService $intake;

    private ScreeningPipeline $pipeline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildRecruitmentSchema([
            'vacancies',
            'candidates',
            'applicant_profiles',
            'screening_results',
            'audit_log',
        ]);

        Storage::fake((string) config('hiring.disk', 'local'));

        $this->intake = new ResumeIntakeService();
        $this->pipeline = new ScreeningPipeline();
    }

    private function vacancy(): Vacancy
    {
        return Vacancy::create([
            'position_title' => 'Administrative Officer I',
            'place_of_assignment' => "Mayor's Office",
            'qs_education' => "Bachelor's degree relevant to the job",
            'qs_training_hours' => 40,
            'qs_experience_years' => 5,
            'qs_eligibility' => 'Career Service Professional',
            'required_skills' => ['records management'],
            'weights' => [
                'education' => 0.2,
                'training' => 0.2,
                'experience' => 0.3,
                'eligibility' => 0.1,
                'skills' => 0.2,
            ],
            'posting_date' => '2026-11-01',
            'closing_date' => '2026-12-31',
        ]);
    }

    private function resume(string $name, string $body = ''): string
    {
        return implode("\n", [
            $name,
            'juan.delacruz@example.com',
            '0917 123 4567',
            'Date of Birth: January 1, 1990',
            'Sex: Male',
            'Bachelor of Science in Environmental Science',
            'Civil Service Professional',
            '40 hours of training',
            '5 years of experience',
            'Skills: records management, public speaking',
            $body,
        ]);
    }

    #[Test]
    public function a_resume_is_screened_scored_explained_and_ranked(): void
    {
        $vacancy = $this->vacancy();

        $profile = $this->intake->intake($vacancy, 'juan-dela-cruz.txt', $this->resume('JUAN DELA CRUZ'));

        $this->assertNotNull($profile);
        $this->assertSame(ApplicantProfile::PARSE_QUEUED, $profile->parse_status);
        $this->assertSame('A-0001', $profile->anon_code);

        $this->pipeline->run($profile->id);

        $profile = $profile->fresh();

        $this->assertSame(ApplicantProfile::PARSE_DONE, $profile->parse_status);
        $this->assertNotNull($profile->parsed);
        $this->assertSame('JUAN DELA CRUZ', $profile->name);
        $this->assertContains('email address', $profile->redaction_report['removed']);

        $candidate = Candidate::find($profile->candidate_id);
        $this->assertSame('JUAN DELA CRUZ', $candidate->full_name, 'The display name is adopted from the resume.');

        $result = ScreeningResult::where('applicant_id', $profile->id)->first();

        $this->assertNotNull($result);
        $this->assertSame(1, $result->rank_position);
        $this->assertSame(config('hiring.model_version'), $result->model_version);
        $this->assertGreaterThan(0, (float) $result->fit_score);
        $this->assertStringContainsString('Education:', $result->explanation);
        $this->assertContains('records management', $profile->parsed['skills']);
    }

    #[Test]
    public function a_lower_scoring_applicant_ranks_below_and_ranks_stay_contiguous(): void
    {
        $vacancy = $this->vacancy();

        $strong = $this->intake->intake($vacancy, 'strong.txt', $this->resume('JUAN DELA CRUZ'));

        $weakText = implode("\n", [
            'PEDRO SANTO',
            'pedro@example.com',
            'High school diploma',
            '2 hours of training',
            '2 years of experience',
            'Skills: public speaking',
        ]);

        $weak = $this->intake->intake($vacancy, 'weak.txt', $weakText);

        $this->pipeline->run($strong->id);
        $this->pipeline->run($weak->id);

        $this->assertSame(
            1,
            ScreeningResult::where('applicant_id', $strong->id)->value('rank_position')
        );
        $this->assertSame(
            2,
            ScreeningResult::where('applicant_id', $weak->id)->value('rank_position')
        );

        $strongScore = (float) ScreeningResult::where('applicant_id', $strong->id)->value('fit_score');
        $weakScore = (float) ScreeningResult::where('applicant_id', $weak->id)->value('fit_score');

        $this->assertGreaterThan($weakScore, $strongScore);

        // The rerank bumped the vacancy's concurrency token.
        $this->assertSame(2, $vacancy->fresh()->ranking_version);
    }

    #[Test]
    public function the_same_resume_is_not_accepted_twice_while_it_is_in_flight_or_done(): void
    {
        $vacancy = $this->vacancy();
        $contents = $this->resume('JUAN DELA CRUZ');

        $first = $this->intake->intake($vacancy, 'juan.txt', $contents);
        $this->assertNotNull($first);

        $this->assertNull(
            $this->intake->intake($vacancy, 'juan-again.txt', $contents),
            'A byte-identical resume is a duplicate while the first copy is pending.'
        );

        $this->pipeline->run($first->id);

        $this->assertNull(
            $this->intake->intake($vacancy, 'juan-third.txt', $contents),
            'A finished copy still blocks a re-upload.'
        );
    }

    #[Test]
    public function a_failed_copy_is_cleared_so_the_retry_is_clean(): void
    {
        $vacancy = $this->vacancy();
        $contents = $this->resume('JUAN DELA CRUZ');

        $first = $this->intake->intake($vacancy, 'juan.txt', $contents);
        $this->assertNotNull($first);

        $first->parse_status = ApplicantProfile::PARSE_FAILED;
        $first->save();

        $retry = $this->intake->intake($vacancy, 'juan.txt', $contents);

        $this->assertNotNull($retry);
        $this->assertNotSame($first->id, $retry->id);
        $this->assertNull(ApplicantProfile::find($first->id), 'The failed copy is removed, not left behind.');
    }

    #[Test]
    public function intake_refuses_an_unsupported_format_and_an_oversized_file(): void
    {
        $vacancy = $this->vacancy();

        try {
            $this->intake->intake($vacancy, 'photo.png', 'not a resume');
            $this->fail('A PNG is not an accepted resume format.');
        } catch (RecruitmentRuleViolation $e) {
            $this->assertSame(400, $e->status);
        }

        config(['hiring.max_resume_bytes' => 10]);

        $this->expectException(RecruitmentRuleViolation::class);
        $this->intake->intake($vacancy, 'long.txt', str_repeat('a', 100));
    }
}
