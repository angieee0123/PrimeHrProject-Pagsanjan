<?php

namespace App\Http\Controllers\Hiring;

use App\Models\ApplicantProfile;
use App\Models\ScreeningResult;
use App\Services\Recruitment\RecruitmentPresenter;
use App\Services\Recruitment\ScreeningPipeline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Applicants: the ranked shortlist, the approval gate, and the resume preview.
 *
 * Rankings and Approvals are the same candidate pool seen at two moments. An
 * approval decides whether a portal application joins the ranking at all; a
 * ranked row is the ordered shortlist that results. Both funnel into the same
 * `ScreeningPipeline::rerank()`, which is why a decision is visible in the
 * ranking immediately rather than after the next upload.
 *
 * Resumes are streamed through this controller, never linked to a public path.
 * A resume is somebody else's personal document and the guard is the reviewer's
 * role, not a URL.
 */
class HiringApplicantController extends HiringController
{
    /** `review_status` values, in tab order. */
    public const REVIEW_STATUSES = ['PENDING', 'ACCEPTED', 'DISAPPROVED'];

    public function __construct(private readonly ScreeningPipeline $pipeline)
    {
    }

    /**
     * Ranked candidates across every vacancy, with an optional vacancy filter.
     *
     * Ordered by Fit Score for this overview; the per-vacancy board on the
     * detail page orders by `rank_position` instead. Both are shown as stored
     * rather than re-sorted here — the score and the rank are separate facts,
     * and a client-side re-sort would silently disagree with whichever one the
     * reader came from.
     */
    public function rankings(Request $request): View
    {
        $results = ScreeningResult::query()
            ->join('applicant_profiles', 'applicant_profiles.id', '=', 'screening_results.applicant_id')
            ->where(function ($query): void {
                $query->whereNull('applicant_profiles.user_id')
                    ->orWhere('applicant_profiles.review_status', ApplicantProfile::REVIEW_ACCEPTED);
            })
            ->orderByDesc('screening_results.fit_score')
            ->orderBy('screening_results.created_at')
            ->select(['screening_results.*'])
            ->with(['applicant', 'vacancy'])
            ->get();

        $rows = $results->map(function (ScreeningResult $result): array {
            return RecruitmentPresenter::ranked($result, $result->applicant, $result->vacancy);
        });

        // Built from the unfiltered set, so the picker keeps listing every
        // vacancy while one of them is selected. Keyed by id, so two positions
        // sharing a title both stay selectable.
        $vacancies = $rows
            ->filter(static fn (array $row) => ($row['vacancy_id'] ?? null) !== null)
            ->mapWithKeys(static fn (array $row) => [
                $row['vacancy_id'] => $row['position_title'] ?? 'Untitled position',
            ])
            ->sort()
            ->all();

        $vacancyId = trim((string) $request->query('vacancy', ''));

        if ($vacancyId !== '') {
            $rows = $rows->where('vacancy_id', $vacancyId);
        }

        return view('admin.recruitment.rankings', [
            'rankings' => $rows->values()->all(),
            'vacancies' => $vacancies,
            'vacancyId' => $vacancyId,
            'total' => $results->count(),
            'failure' => null,
        ]);
    }

    /**
     * Applications submitted through the applicant portal that need a decision.
     *
     * Under the staff-only scope there is no portal account, so this list is
     * empty by design: a resume HR uploaded on a candidate's behalf was already
     * vetted by the officer who uploaded it. The query is kept faithful to the
     * reference so a later portal needs no change here.
     */
    public function approvals(Request $request): View
    {
        $all = ApplicantProfile::query()
            ->whereNotNull('user_id')
            ->with(['vacancy', 'screeningResult'])
            ->orderByDesc('created_at')
            ->get();

        $counts = collect(self::REVIEW_STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => $all->where('review_status', $status)->count()])
            ->all();

        $reviewStatus = strtoupper((string) $request->query('review_status', ''));
        $reviewStatus = array_key_exists($reviewStatus, $counts) ? $reviewStatus : null;

        $rows = $all;

        if ($reviewStatus !== null) {
            $rows = $rows->where('review_status', $reviewStatus);
        }

        return view('admin.recruitment.approvals', [
            'approvals' => $rows->values()->map(
                static fn (ApplicantProfile $profile) => RecruitmentPresenter::approval($profile)
            )->all(),
            'counts' => $counts,
            'reviewStatus' => $reviewStatus,
            'total' => $all->count(),
            'failure' => null,
        ]);
    }

    /** Accepting admits the candidate to the vacancy's ranking immediately. */
    public function accept(Request $request, string $id): RedirectResponse
    {
        return $this->decide($request, $id, ApplicantProfile::REVIEW_ACCEPTED, 'accept_application',
            'Application accepted and added to the ranking.');
    }

    /** Disapproving drops them out of the ranking and closes the gap below. */
    public function disapprove(Request $request, string $id): RedirectResponse
    {
        return $this->decide($request, $id, ApplicantProfile::REVIEW_DISAPPROVED, 'disapprove_application',
            'Application disapproved. The ranking has been updated.');
    }

    /**
     * Stream one candidate's submitted resume.
     *
     * PDF and plain text are shown inline, DOCX downloads — no browser can
     * render a DOCX, and a half-blank viewer tab reads as a broken file.
     * `nosniff` and a private, no-store cache are this application's standard
     * headers for a personal file: it must not be sniffed into something
     * executable, and it must not linger in a shared machine's cache.
     *
     * The read is recorded against the officer, because it is access to somebody
     * else's personal document.
     */
    public function resume(Request $request, string $id): HttpResponse
    {
        $profile = ApplicantProfile::find($id);

        if ($profile === null) {
            abort(404, 'That application does not exist.');
        }

        $disk = Storage::disk((string) config('hiring.disk', 'local'));

        if (! $disk->exists($profile->source_file_path)) {
            abort(404, 'The stored resume could not be found.');
        }

        $this->audit($request, 'VIEW_RESUME', 'applicant_profile', $profile->id);

        $filename = $this->originalFilename($profile->source_file_path);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $disposition = in_array($extension, ['pdf', 'txt'], true) ? 'inline' : 'attachment';

        return response($disk->get($profile->source_file_path), 200, [
            'Content-Type' => $disk->mimeType($profile->source_file_path) ?: 'application/octet-stream',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    private function decide(
        Request $request,
        string $id,
        string $status,
        string $action,
        string $success,
    ): RedirectResponse {
        $profile = ApplicantProfile::query()
            ->whereNotNull('user_id')
            ->find($id);

        if ($profile === null) {
            abort(404, 'That application does not exist.');
        }

        $profile->review_status = $status;
        $profile->save();

        // Acceptance is what admits this candidate to the ranking; disapproval
        // drops them out and closes the gap below. Both take effect immediately.
        $vacancy = $profile->vacancy;

        if ($vacancy !== null) {
            $this->pipeline->rerank($vacancy);
        }

        $this->audit($request, $action, 'applicant_profile', $profile->id);

        return redirect()->route('admin.recruitment.approvals')->with('success', $success);
    }

    /** The uploaded filename, recovered from the stored suffixed path. */
    private function originalFilename(string $storedPath): string
    {
        $name = basename(str_replace('\\', '/', $storedPath));
        $name = preg_replace('/^[a-z0-9]{8}_/', '', $name) ?? $name;
        $name = str_replace(['"', "\r", "\n"], '', $name);

        return $name !== '' ? $name : 'resume.pdf';
    }
}
