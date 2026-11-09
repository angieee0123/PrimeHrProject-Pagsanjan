<?php

namespace App\Services\Recruitment;

use App\Models\ApplicantProfile;
use App\Models\AuditLog;
use App\Models\ScreeningResult;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;

/**
 * The recruitment overview's figures.
 *
 * Every number is computed from the same rows the rest of the module reads, so
 * the dashboard cannot disagree with the ranking or the approval queue. The
 * reference did this in `GET /api/dashboard`; here it is one query set behind
 * one service.
 */
class HiringDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $openCount = Vacancy::query()->where('status', Vacancy::STATUS_OPEN)->count();
        $totalApplicants = ApplicantProfile::query()->count();
        $scored = ScreeningResult::query()->count();
        $averageFit = ScreeningResult::query()->avg('fit_score');

        $byPosition = Vacancy::query()
            ->withCount('applicantProfiles')
            ->orderBy('position_title')
            ->get()
            ->map(fn (Vacancy $vacancy) => [
                'name' => $vacancy->position_title,
                'applicants' => (int) $vacancy->applicant_profiles_count,
            ])
            ->all();

        $fitDistribution = [
            ['band' => 'Strong (75–100)', 'count' => ScreeningResult::query()->where('fit_score', '>=', 75)->count()],
            ['band' => 'Moderate (50–74)', 'count' => ScreeningResult::query()->whereBetween('fit_score', [50, 74.99])->count()],
            ['band' => 'Weak (0–49)', 'count' => ScreeningResult::query()->where('fit_score', '<', 50)->count()],
        ];

        $recent = AuditLog::query()->orderByDesc('at')->limit(6)->get()
            ->map(static fn (AuditLog $entry) => RecruitmentPresenter::audit($entry))
            ->all();

        return [
            'stats' => [
                'openVacancies' => $openCount,
                'totalApplicants' => $totalApplicants,
                'screened' => $scored,
                'avgFit' => $averageFit === null ? null : round((float) $averageFit),
            ],
            'byPosition' => $byPosition,
            'fitDistribution' => $fitDistribution,
            'recentActivity' => $recent,
        ];
    }

    /**
     * Open positions that are still accepting applications, soonest closing
     * first — the "scan this first" list on the overview.
     *
     * Not truncated: a card list that silently stops at twelve is a list an HR
     * officer cannot tell is incomplete.
     *
     * @return array<int, array<string, mixed>>
     */
    public function openVacancies(): array
    {
        return Vacancy::query()
            ->where('status', Vacancy::STATUS_OPEN)
            ->whereDate('closing_date', '>=', BusinessClock::today()->toDateString())
            ->orderBy('closing_date')
            ->get()
            ->map(static fn (Vacancy $vacancy) => RecruitmentPresenter::vacancy($vacancy))
            ->all();
    }

    /**
     * Portal applications awaiting a decision.
     *
     * Under the staff-only scope there are no portal accounts, so this is empty
     * by design — an HR-uploaded resume was vetted by the officer who uploaded
     * it and enters the ranking directly. The query is kept faithful to the
     * reference so a later applicant portal needs no change here.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pendingApprovals(): array
    {
        return ApplicantProfile::query()
            ->whereNotNull('user_id')
            ->where('review_status', ApplicantProfile::REVIEW_PENDING)
            ->with(['vacancy', 'screeningResult'])
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (ApplicantProfile $profile) => RecruitmentPresenter::approval($profile))
            ->all();
    }

    /** Count of applications still awaiting a decision. */
    public function pendingApprovalCount(): int
    {
        return ApplicantProfile::query()
            ->whereNotNull('user_id')
            ->where('review_status', ApplicantProfile::REVIEW_PENDING)
            ->count();
    }
}
