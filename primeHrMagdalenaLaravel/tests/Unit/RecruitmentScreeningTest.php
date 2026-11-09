<?php

namespace Tests\Unit;

use App\Models\Vacancy;
use App\Services\Recruitment\DegreeNormalizer;
use App\Services\Recruitment\ExperienceDateParser;
use App\Services\Recruitment\FitScoreCalculator;
use App\Services\Recruitment\ResumeAnonymizer;
use App\Services\Recruitment\ResumeProfileParser;
use App\Services\Recruitment\ScreeningExplainer;
use App\Services\Recruitment\SkillMatcher;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The pure screening rules: redaction, degree normalization, date ranges, skill
 * matching and the weighted Fit Score.
 *
 * No database and no files — these are the decisions that determine a rank, so
 * they are tested as rules rather than through the pipeline that calls them.
 */
class RecruitmentScreeningTest extends TestCase
{
    #[Test]
    public function the_anonymizer_removes_every_identifying_and_demographic_field(): void
    {
        $text = implode("\n", [
            'JUAN DELA CRUZ',
            'juan.delacruz@example.com',
            '0917 123 4567',
            'Date of Birth: January 1, 1990',
            'Age: 36',
            'Sex: Male',
            'Religion: Roman Catholic',
            'Civil Status: Single',
            'Bachelor of Science in Environmental Science',
        ]);

        [$redacted, $report] = (new ResumeAnonymizer())->anonymize($text);

        $this->assertStringNotContainsString('juan.delacruz@example.com', $redacted);
        $this->assertStringNotContainsString('0917 123 4567', $redacted);
        $this->assertStringNotContainsString('January 1, 1990', $redacted);
        $this->assertStringNotContainsString('Roman Catholic', $redacted);
        $this->assertStringNotContainsString('Single', $redacted);

        $this->assertContains('email address', $report['removed']);
        $this->assertContains('contact number', $report['removed']);
        $this->assertContains('date of birth', $report['removed']);
        $this->assertContains('age', $report['removed']);
        $this->assertContains('gender', $report['removed']);
        $this->assertContains('religion', $report['removed']);
        $this->assertContains('civil status', $report['removed']);

        // The education line is what the scorer is allowed to read, and it is
        // untouched by redaction.
        $this->assertStringContainsString('Bachelor of Science', $redacted);
    }

    #[Test]
    public function degree_aliases_normalize_but_unrelated_programs_are_preserved(): void
    {
        $normalizer = new DegreeNormalizer();

        $this->assertSame(
            ['bachelor of science in environmental science'],
            $normalizer->normalize(['BSES'])
        );
        $this->assertSame(
            ['bachelor of science in environmental science'],
            $normalizer->normalize(['B.S.E.S.'])
        );
        $this->assertSame(
            ["master's degree in business administration"],
            $normalizer->normalize(['MBA'])
        );
        $this->assertSame(
            ['doctoral degree'],
            $normalizer->normalize(['PhD'])
        );
        $this->assertSame(
            ['marine biology'],
            $normalizer->normalize(['Marine Biology']),
            'An unlisted degree is preserved rather than dropped.'
        );
    }

    #[Test]
    public function experience_ranges_merge_overlaps_and_respect_month_precision(): void
    {
        $parser = new ExperienceDateParser();
        $present = Carbon::parse('2026-11-09');

        $ranges = $parser->rangesForAudit('Jan 2020 - Apr 2020', $present);

        $this->assertCount(1, $ranges);
        $this->assertSame('2020-01-01', $ranges[0]['start']);
        $this->assertSame('2020-04-30', $ranges[0]['end'], 'April 2020 is counted through its last day.');
        $this->assertSame(121, $ranges[0]['days']);
        $this->assertSame(0.3, $ranges[0]['years']);

        $this->assertSame(0.3, $parser->totalYearsFromText('Jan 2020 - Apr 2020', $present));

        // Two overlapping jobs are one stretch of calendar time, not two.
        $merged = $parser->rangesForAudit(
            'Jan 2020 - Dec 2020 and Jun 2020 - Aug 2021',
            $present
        );

        $this->assertCount(1, $merged);
        $this->assertSame('2020-01-01', $merged[0]['start']);
        $this->assertSame('2021-08-31', $merged[0]['end']);

        // "Present" resolves to the supplied business day.
        $this->assertGreaterThan(
            9.0,
            $parser->totalYearsFromText('Aug 2017 - Present', $present)
        );

        // A bare year range stays January-to-January.
        $this->assertSame(1.0, $parser->totalYearsFromText('2020 - 2021', $present));
    }

    #[Test]
    public function the_parser_extracts_the_five_scored_criteria(): void
    {
        $text = implode("\n", [
            'JUAN DELA CRUZ',
            'juan@example.com',
            'Bachelor of Science in Environmental Science',
            'Civil Service Professional',
            '40 hours of training',
            '5 years of experience',
            'Skills: records management, public speaking',
        ]);

        [$redacted] = (new ResumeAnonymizer())->anonymize($text);

        $parser = new ResumeProfileParser();
        $parsed = $parser->parse($redacted, ['records management', 'communication']);

        $this->assertContains('bachelor of science in environmental science', $parsed['education']);
        $this->assertSame(40, $parsed['raw_training_hours']);
        $this->assertSame(5.0, $parsed['raw_experience_years']);
        $this->assertContains('civil service', $parsed['eligibility']);
        $this->assertContains('records management', $parsed['skills']);
        $this->assertNotContains('communication', $parsed['skills']);

        $this->assertSame('JUAN DELA CRUZ', $parser->extractName($text));
    }

    #[Test]
    public function the_name_heuristic_rejects_headings_and_contact_lines(): void
    {
        $parser = new ResumeProfileParser();

        $this->assertNull($parser->extractName("RESUME\n0917 123 4567"));
        $this->assertNull($parser->extractName("juan@example.com\nObjective: to serve"));
        $this->assertSame('Maria Santos Reyes', $parser->extractName("CURRICULUM VITAE\nMaria Santos Reyes\nAddress"));
    }

    #[Test]
    public function skill_matching_is_jaccard_overlap_with_the_reference_floor(): void
    {
        $matcher = new SkillMatcher();

        [$score, $matched] = $matcher->match(['records management'], ['records management']);

        $this->assertSame(1.0, $score);
        $this->assertSame(['records management'], $matched);

        // "communication" vs "communication skills" is 0.5, under the 0.60
        // floor — the documented cost of dropping Word2Vec.
        [$score, $matched] = $matcher->match(['communication'], ['communication skills']);

        $this->assertSame(0.0, $score);
        $this->assertSame([], $matched);

        $this->assertSame([1.0, []], $matcher->match([], ['anything']));
    }

    #[Test]
    public function a_fully_qualified_applicant_scores_one_hundred_and_the_explanation_says_why(): void
    {
        $vacancy = new Vacancy([
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
                'experience' => 0.2,
                'eligibility' => 0.2,
                'skills' => 0.2,
            ],
        ]);

        $parsed = [
            'education' => ['bachelor of science in environmental science'],
            'eligibility' => ['civil service professional'],
            'raw_training_hours' => 40,
            'raw_experience_years' => 5.0,
            'skills' => ['records management'],
        ];

        $score = (new FitScoreCalculator())->compute($parsed, $vacancy);

        $this->assertSame(100.0, $score['fit']);
        $this->assertSame(1.0, $score['subscores']['education']);
        $this->assertSame(['records management'], $score['matched']);

        $explanation = (new ScreeningExplainer())->build($parsed, $score['subscores'], $score['matched'], $vacancy);

        $this->assertStringContainsString('Education:', $explanation);
        $this->assertStringContainsString('Experience: 100%', $explanation);
        $this->assertStringContainsString('Training: 100%', $explanation);
        $this->assertStringContainsString('Eligibility: civil service professional', $explanation);
        $this->assertStringContainsString('Skills: matched 1 of 1', $explanation);
    }
}
