<?php

namespace App\Services\Recruitment;

use App\Models\ApplicantProfile;
use App\Models\ScreeningResult;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Screening pipeline orchestrator: extract → anonymize → parse → score →
 * explain → persist → rerank.
 *
 * The reference ran this in a FastAPI `BackgroundTask`. Here it is a queued
 * Laravel job (`App\Jobs\ScreenApplicantProfile`), which is the same contract:
 * the upload request returns immediately and one applicant's failure cannot
 * take down the rest of the batch.
 *
 * The order is load-bearing. Anonymization happens **before** parsing reaches
 * the scorer, so demographic fields cannot influence a score; the candidate
 * name is extracted from the raw text because it is display data, and it never
 * enters the parsed profile the scorer reads.
 */
class ScreeningPipeline
{
    public function __construct(
        private readonly ResumeTextExtractor $extractor = new ResumeTextExtractor(),
        private readonly ResumeAnonymizer $anonymizer = new ResumeAnonymizer(),
        private readonly ResumeProfileParser $parser = new ResumeProfileParser(),
        private readonly FitScoreCalculator $scorer = new FitScoreCalculator(),
        private readonly ScreeningExplainer $explainer = new ScreeningExplainer(),
        private readonly CandidateResolver $candidates = new CandidateResolver(),
        private readonly ResumeIntakeService $intake = new ResumeIntakeService(),
    ) {
    }

    /**
     * Screen one application.
     *
     * A failure marks the profile FAILED and rethrows, so the queue can retry
     * once and the failure is visible in the log. The reference swallowed the
     * exception in a background task; a queue that never hears about a failure
     * cannot retry it.
     */
    public function run(string $profileId): void
    {
        $profile = ApplicantProfile::find($profileId);

        if ($profile === null) {
            return;
        }

        $vacancy = Vacancy::find($profile->vacancy_id);

        if ($vacancy === null) {
            $profile->parse_status = ApplicantProfile::PARSE_FAILED;
            $profile->save();

            return;
        }

        $profile->parse_status = ApplicantProfile::PARSE_PARSING;
        $profile->save();

        try {
            $rawText = $this->extractor->extract(
                $this->intake->absolutePath($profile),
                pathinfo($profile->source_file_path, PATHINFO_EXTENSION),
            );

            $profile->name = $this->parser->extractName($rawText);
            $this->candidates->adoptName($profile);

            [$redacted, $report] = $this->anonymizer->anonymize($rawText);

            $parsed = $this->parser->parse($redacted, (array) ($vacancy->required_skills ?? []));
            $score = $this->scorer->compute($parsed, $vacancy);

            // The parser detects the applicant's skills against the vacancy's
            // list; the scorer returns which of them actually cleared the floor,
            // and that is what the audit trail records as matched.
            $parsed['skills'] = $score['matched'];
            $parsed['unmatched'] = array_values(array_filter(
                (array) ($vacancy->required_skills ?? []),
                static fn (string $skill) => ! in_array($skill, $score['matched'], true),
            ));

            $explanation = $this->explainer->build($parsed, $score['subscores'], $score['matched'], $vacancy);

            DB::transaction(function () use ($profile, $vacancy, $parsed, $report, $score, $explanation): void {
                $profile->parsed = $parsed;
                $profile->redaction_report = $report;
                $profile->parse_status = ApplicantProfile::PARSE_DONE;
                $profile->save();

                // Written as one row per application rather than one per run.
                // A queued retry must not leave two competing scores behind;
                // the reference's "never recompute in place" rule is about a
                // *re-rank* writing a fresh position, not about a retried job.
                ScreeningResult::updateOrCreate(
                    ['applicant_id' => $profile->id],
                    [
                        'vacancy_id' => $vacancy->id,
                        'fit_score' => $score['fit'],
                        'subscores' => $score['subscores'],
                        'explanation' => mb_substr($explanation, 0, 2000),
                        'model_version' => (string) config('hiring.model_version', 'php-fallback-regex-1.0'),
                    ],
                );

                $this->rerank($vacancy);
            });
        } catch (Throwable $e) {
            $profile->parse_status = ApplicantProfile::PARSE_FAILED;
            $profile->save();

            throw $e;
        }
    }

    /**
     * Recompute `rank_position` by descending Fit Score.
     *
     * A portal applicant holds a rank only once HR has ACCEPTED their
     * submittal; while PENDING (or once DISAPPROVED) they stay unranked.
     * HR-uploaded resumes (user_id is NULL) were vetted by the staff member who
     * uploaded them and are always eligible. Ineligible candidates keep their
     * Fit Score — HR reads it to make the accept/disapprove call — but carry
     * `rank_position = NULL`, and eligible ranks stay contiguous 1..N rather
     * than leaving a gap where a pending applicant was skipped.
     */
    public function rerank(Vacancy $vacancy): void
    {
        $rows = DB::table('screening_results')
            ->join('applicant_profiles', 'applicant_profiles.id', '=', 'screening_results.applicant_id')
            ->where('screening_results.vacancy_id', $vacancy->id)
            ->orderByDesc('screening_results.fit_score')
            ->orderBy('screening_results.created_at')
            ->orderBy('screening_results.id')
            ->select([
                'screening_results.id as result_id',
                'applicant_profiles.user_id',
                'applicant_profiles.review_status',
            ])
            ->get();

        $position = 0;

        foreach ($rows as $row) {
            $eligible = $row->user_id === null || $row->review_status === ApplicantProfile::REVIEW_ACCEPTED;

            $rank = null;

            if ($eligible) {
                $position++;
                $rank = $position;
            }

            DB::table('screening_results')
                ->where('id', $row->result_id)
                ->update(['rank_position' => $rank]);
        }

        // The concurrency token rehire checks against. Bumped here rather than
        // at each call site so no future re-ranking path can forget to move it.
        Vacancy::whereKey($vacancy->id)->increment('ranking_version');
    }

    /**
     * Whether this application may hold a rank.
     *
     * The in-PHP twin of the rerank query's predicate.
     */
    public function isRankEligible(ApplicantProfile $profile): bool
    {
        return $profile->user_id === null
            || $profile->review_status === ApplicantProfile::REVIEW_ACCEPTED;
    }
}
