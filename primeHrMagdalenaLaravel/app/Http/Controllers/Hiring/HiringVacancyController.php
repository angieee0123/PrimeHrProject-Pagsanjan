<?php

namespace App\Http\Controllers\Hiring;

use App\Exceptions\RecruitmentRuleViolation;
use App\Jobs\ScreenApplicantProfile;
use App\Models\ApplicantProfile;
use App\Models\ScreeningResult;
use App\Models\Vacancy;
use App\Services\HiringPresenter;
use App\Services\Recruitment\BusinessClock;
use App\Services\Recruitment\RecruitmentPresenter;
use App\Services\Recruitment\ResumeIntakeService;
use App\Services\Recruitment\VacancyLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Vacancies: the list, one position's detail and screening, and the lifecycle
 * moves on it.
 *
 * ## All of it is local now
 *
 * The rows come from this application's `vacancies` table, the state machine
 * from `VacancyLifecycleService`, and the screening job from the queue. There is
 * no second service to reach, so the "not connected" state this page used to
 * carry is gone: a failure here is an ordinary 404 or a rule refusal, not an
 * outage.
 *
 * ## Filtering happens in the controller
 *
 * The status tabs at the top of the page show how many positions are in *each*
 * state, so the whole list is fetched once and narrowed in memory. A municipal
 * recruitment pool is dozens of positions, not thousands, and the alternative
 * is a second query per page load just to count the tabs.
 */
class HiringVacancyController extends HiringController
{
    /** The six lifecycle states, in the order the tabs read. */
    public const STATUSES = ['OPEN', 'DRAFT', 'ON_HOLD', 'LAPSED', 'CLOSED', 'CANCELLED'];

    public function __construct(
        private readonly VacancyLifecycleService $lifecycle,
        private readonly ResumeIntakeService $intake,
    ) {
    }

    public function index(Request $request): View
    {
        $all = Vacancy::query()
            ->withCount(['applicantProfiles', 'screeningResults'])
            ->orderByDesc('created_at')
            ->get();

        $counts = collect(self::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => $all->where('status', $status)->count()])
            ->all();

        $rows = $all->map(static fn (Vacancy $vacancy) => RecruitmentPresenter::vacancy(
            $vacancy,
            (int) $vacancy->applicant_profiles_count,
            (int) $vacancy->screening_results_count,
        ));

        // An unrecognised `?status=` shows everything rather than an empty
        // table, which is what a hand-typed URL deserves.
        $status = strtoupper((string) $request->query('status', ''));
        $status = in_array($status, self::STATUSES, true) ? $status : null;

        if ($status !== null) {
            $rows = $rows->where('status', $status);
        }

        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(static fn (array $vacancy) => str_contains(
                mb_strtolower(($vacancy['position_title'] ?? '').' '.($vacancy['place_of_assignment'] ?? '')),
                $needle
            ));
        }

        return view('admin.recruitment.vacancies', [
            'vacancies' => $rows->values()->all(),
            'counts' => $counts,
            'total' => $all->count(),
            'status' => $status,
            'search' => $search,
            'failure' => null,
        ]);
    }

    /**
     * The archive tab: lapsed, closed and cancelled positions.
     *
     * Deliberately not folded into the main list. A position nobody decided on
     * (lapsed), one that was filled (closed) and one that was withdrawn
     * (cancelled) call for different actions, and the status itself is served as
     * `archive_reason` so this page never infers it from nullable timestamps.
     */
    public function archive(Request $request): View
    {
        $all = Vacancy::query()
            ->whereIn('status', VacancyLifecycleService::ARCHIVED_STATUSES)
            ->withCount(['applicantProfiles', 'screeningResults'])
            ->orderByDesc('closing_date')
            ->get();

        $counts = collect(VacancyLifecycleService::ARCHIVED_STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => $all->where('status', $status)->count()])
            ->all();

        $rows = $all->map(static fn (Vacancy $vacancy) => RecruitmentPresenter::vacancy(
            $vacancy,
            (int) $vacancy->applicant_profiles_count,
            (int) $vacancy->screening_results_count,
            $vacancy->status,
        ));

        $reason = strtoupper((string) $request->query('reason', ''));
        $reason = array_key_exists($reason, $counts) ? $reason : null;

        if ($reason !== null) {
            $rows = $rows->where('archive_reason', $reason);
        }

        return view('admin.recruitment.archive', [
            'vacancies' => $rows->values()->all(),
            'counts' => $counts,
            'reason' => $reason,
            'total' => $all->count(),
            'failure' => null,
        ]);
    }

    public function show(string $id): View
    {
        $vacancy = Vacancy::query()
            ->withCount(['applicantProfiles', 'screeningResults'])
            ->find($id);

        if ($vacancy === null) {
            abort(404, 'That vacancy does not exist.');
        }

        $profiles = ApplicantProfile::query()
            ->where('vacancy_id', $vacancy->id)
            ->with('screeningResult')
            ->orderBy('created_at')
            ->get()
            ->map(static fn (ApplicantProfile $profile) => RecruitmentPresenter::profile($profile))
            ->all();

        $ranked = ScreeningResult::query()
            ->join('applicant_profiles', 'applicant_profiles.id', '=', 'screening_results.applicant_id')
            ->where('screening_results.vacancy_id', $vacancy->id)
            ->where(function ($query): void {
                $query->whereNull('applicant_profiles.user_id')
                    ->orWhere('applicant_profiles.review_status', ApplicantProfile::REVIEW_ACCEPTED);
            })
            ->orderBy('screening_results.rank_position')
            ->orderByDesc('screening_results.fit_score')
            ->select(['screening_results.*'])
            ->with(['applicant', 'vacancy'])
            ->get()
            ->map(static fn (ScreeningResult $result) => RecruitmentPresenter::ranked(
                $result,
                $result->applicant,
                $result->vacancy,
            ))
            ->all();

        $vacancyArray = RecruitmentPresenter::vacancy(
            $vacancy,
            (int) $vacancy->applicant_profiles_count,
            (int) $vacancy->screening_results_count,
        );

        return view('admin.recruitment.vacancy', [
            'vacancy' => $vacancyArray,
            'profiles' => $profiles,
            'ranking' => [
                'vacancy' => $vacancyArray,
                'results' => $ranked,
                'model_version' => (string) config('hiring.model_version'),
            ],
            'results' => $ranked,
            'failure' => null,
            'profilesFailure' => null,
            'rankingFailure' => null,
        ]);
    }

    public function create(Request $request): View
    {
        $this->requireManager($request);

        return view('admin.recruitment.create', [
            'weights' => HiringPresenter::WEIGHTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireManager($request);

        $data = $request->validate($this->rules());

        $weights = [];

        foreach (array_keys(HiringPresenter::WEIGHTS) as $criterion) {
            $weights[$criterion] = round((float) $data['weight_'.$criterion], 3);
        }

        // The same check the reference applied in its request schema, done here
        // so the message sits beside the fields rather than arriving second-hand.
        if (round(array_sum($weights), 3) !== 1.0) {
            return back()
                ->withInput()
                ->withErrors([
                    'weights' => 'The five weights must add up to 100% — they currently add up to '
                        .HiringPresenter::percent(array_sum($weights)).'.',
                ]);
        }

        $vacancy = $this->lifecycle->create([
            'position_title' => $data['position_title'],
            'place_of_assignment' => $data['place_of_assignment'],
            'plantilla_item_no' => self::blankToNull($data['plantilla_item_no'] ?? null),
            'salary_grade' => isset($data['salary_grade']) ? (int) $data['salary_grade'] : null,
            'monthly_salary' => (float) $data['monthly_salary'],
            'qs_education' => $data['qs_education'],
            'qs_training_hours' => (int) $data['qs_training_hours'],
            'qs_experience_years' => (float) $data['qs_experience_years'],
            'qs_eligibility' => $data['qs_eligibility'],
            'competency' => self::blankToNull($data['competency'] ?? null),
            'required_skills' => self::skills($data['required_skills']),
            'weights' => $weights,
            'instructions' => self::blankToNull($data['instructions'] ?? null),
            'slots' => (int) $data['slots'],
            'posting_date' => $data['posting_date'],
            'closing_date' => $data['closing_date'],
            'status' => $data['status'],
        ], $request->user());

        $this->audit(
            $request,
            $vacancy->status === Vacancy::STATUS_DRAFT ? 'CREATE_DRAFT_VACANCY' : 'CREATE_VACANCY',
            'vacancy',
            $vacancy->id,
        );

        return redirect()
            ->route('admin.recruitment.show', $vacancy->id)
            ->with('success', 'Vacancy created: '.$vacancy->position_title.'.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Lifecycle
    // ─────────────────────────────────────────────────────────────────────

    public function publish(Request $request, string $id): RedirectResponse
    {
        $this->requireManager($request);

        return $this->apply($request, $id, 'PUBLISH_VACANCY', 'The vacancy is now open and publicly listed.',
            function (Vacancy $vacancy): void {
                $this->lifecycle->assertTransition($vacancy, Vacancy::STATUS_OPEN);

                if (BusinessClock::isPastClosing($vacancy)) {
                    throw RecruitmentRuleViolation::badRequest(
                        "This draft's closing date has already passed. Set a future closing date before publishing, or it will lapse on the next sweep."
                    );
                }

                $this->lifecycle->publish($vacancy);
            });
    }

    public function hold(Request $request, string $id): RedirectResponse
    {
        $this->requireManager($request);

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->apply($request, $id, 'HOLD_VACANCY',
            'The vacancy is on hold. Applicants keep their place in the pipeline and are not notified.',
            function (Vacancy $vacancy) use ($data): void {
                $this->lifecycle->assertTransition($vacancy, Vacancy::STATUS_ON_HOLD);
                $this->lifecycle->hold($vacancy, self::blankToNull($data['note'] ?? null));
            });
    }

    public function resume(Request $request, string $id): RedirectResponse
    {
        $this->requireManager($request);

        $data = $request->validate([
            'closing_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        return $this->apply($request, $id, 'RESUME_VACANCY', 'The vacancy is open again.',
            function (Vacancy $vacancy) use ($data): void {
                $this->lifecycle->assertTransition($vacancy, Vacancy::STATUS_OPEN);

                $closing = isset($data['closing_date']) && $data['closing_date'] !== null
                    ? Carbon::parse($data['closing_date'], BusinessClock::timezone())->startOfDay()
                    : BusinessClock::day($vacancy->closing_date);

                if ($closing === null || $closing->lt(BusinessClock::today())) {
                    throw RecruitmentRuleViolation::badRequest(
                        'The closing date passed while this position was on hold. Supply a future closing date to resume it.'
                    );
                }

                $this->lifecycle->resume($vacancy, $closing);
            });
    }

    public function cancel(Request $request, string $id): RedirectResponse
    {
        $this->requireManager($request);

        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(HiringPresenter::CANCEL_REASONS))],
            'reason_detail' => ['nullable', 'string', 'max:255', 'required_if:reason,OTHER'],
        ]);

        return $this->apply($request, $id, 'CANCEL_VACANCY:'.$data['reason'],
            'The vacancy has been cancelled. This is final.',
            function (Vacancy $vacancy) use ($data): void {
                $this->lifecycle->assertTransition($vacancy, Vacancy::STATUS_CANCELLED);
                $this->lifecycle->cancel($vacancy, $data['reason'], self::blankToNull($data['reason_detail'] ?? null));
            });
    }

    public function close(Request $request, string $id): RedirectResponse
    {
        $this->requireManager($request);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->apply($request, $id, 'CLOSE_VACANCY', 'The vacancy is closed. This is final.',
            function (Vacancy $vacancy) use ($data): void {
                $this->lifecycle->assertTransition($vacancy, Vacancy::STATUS_CLOSED);
                $this->lifecycle->close($vacancy, self::blankToNull($data['reason'] ?? null));
            });
    }

    public function reopen(Request $request, string $id): RedirectResponse
    {
        $this->requireManager($request);

        $data = $request->validate([
            'closing_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        return $this->apply($request, $id, 'REOPEN_VACANCY',
            'The vacancy is open again on the same requisition, so its previous applicants are still attached.',
            function (Vacancy $vacancy) use ($data): void {
                $this->lifecycle->assertTransition($vacancy, Vacancy::STATUS_OPEN);

                // Anything still here can legally reach OPEN, but not via
                // *reopen*. Route it to the move that actually fits rather than
                // silently doing the wrong one: a resumed hold must not
                // increment reopen_count, and a draft has never been open.
                if ($vacancy->status !== Vacancy::STATUS_LAPSED) {
                    $correct = $vacancy->status === Vacancy::STATUS_ON_HOLD ? 'resume' : 'publish';

                    throw RecruitmentRuleViolation::conflict(
                        'Only a lapsed position can be reopened. This one is '
                        .mb_strtolower($this->lifecycle->label($vacancy->status)).' — use '.$correct.' instead.'
                    );
                }

                $closing = Carbon::parse($data['closing_date'], BusinessClock::timezone())->startOfDay();

                if ($closing->lt(BusinessClock::today())) {
                    throw RecruitmentRuleViolation::badRequest(
                        'The new closing date must be in the future — a position reopened with a past date would lapse again on the next sweep.'
                    );
                }

                $this->lifecycle->reopen($vacancy, $closing);
            });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Resume intake
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Upload one or more resumes against this vacancy.
     *
     * Allowed for HR staff as well as admins, because screening is their job.
     * Each accepted file becomes a PENDING application and is queued for
     * screening; the request returns without waiting for extraction.
     *
     * Filenames are preserved, not generated: a reviewer downloading the file
     * gets "juan-dela-cruz.pdf" rather than a hash, and it is also what the
     * duplicate report names.
     */
    public function uploadResumes(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'file', 'max:10240', 'mimes:pdf,docx,txt'],
        ]);

        $vacancy = Vacancy::find($id);

        if ($vacancy === null) {
            abort(404, 'That vacancy does not exist.');
        }

        $created = [];
        $duplicates = [];

        foreach ($request->file('files') as $file) {
            try {
                $profile = $this->intake->intake(
                    $vacancy,
                    (string) $file->getClientOriginalName(),
                    (string) $file->get(),
                );
            } catch (RecruitmentRuleViolation $e) {
                return redirect()
                    ->route('admin.recruitment.show', $vacancy->id)
                    ->with('error', $e->getMessage());
            }

            if ($profile === null) {
                $duplicates[] = $file->getClientOriginalName();

                continue;
            }

            $created[] = $profile;
        }

        foreach ($created as $profile) {
            ScreenApplicantProfile::dispatch($profile->id);
        }

        $this->audit($request, 'UPLOAD_RESUME', 'vacancy', $vacancy->id);

        $count = count($created);
        $message = $count === 1
            ? '1 resume uploaded and queued for screening.'
            : $count.' resumes uploaded and queued for screening.';

        if ($duplicates !== []) {
            // Not an error: the file is already on file for this vacancy, and
            // saying which is more useful than a bare count.
            $message .= ' Skipped as already on file: '.implode(', ', $duplicates).'.';
        }

        return redirect()->route('admin.recruitment.show', $vacancy->id)->with('success', $message);
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * Run one lifecycle move and turn its refusal into a flash.
     *
     * One place decides between a success flash and the rule's own sentence, so
     * every move reports its outcome the same way.
     */
    private function apply(Request $request, string $id, string $action, string $success, callable $move): RedirectResponse
    {
        $vacancy = Vacancy::find($id);

        if ($vacancy === null) {
            abort(404, 'That vacancy does not exist.');
        }

        try {
            $move($vacancy);
        } catch (RecruitmentRuleViolation $e) {
            return redirect()
                ->route('admin.recruitment.show', $vacancy->id)
                ->with('error', $e->getMessage());
        }

        $this->audit($request, $action, 'vacancy', $vacancy->id);

        return redirect()->route('admin.recruitment.show', $vacancy->id)->with('success', $success);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'position_title' => ['required', 'string', 'min:2', 'max:255'],
            'place_of_assignment' => ['required', 'string', 'min:2', 'max:255'],
            'plantilla_item_no' => ['nullable', 'string', 'max:100'],
            'salary_grade' => ['nullable', 'integer', 'min:1', 'max:33'],
            'monthly_salary' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'qs_education' => ['required', 'string', 'min:2', 'max:255'],
            'qs_training_hours' => ['required', 'integer', 'min:0', 'max:100000'],
            'qs_experience_years' => ['required', 'numeric', 'min:0', 'max:60'],
            'qs_eligibility' => ['required', 'string', 'min:2', 'max:255'],
            'competency' => ['nullable', 'string', 'max:255'],
            'required_skills' => ['required', 'string', 'max:2000'],
            'weight_education' => ['required', 'numeric', 'min:0', 'max:1'],
            'weight_training' => ['required', 'numeric', 'min:0', 'max:1'],
            'weight_experience' => ['required', 'numeric', 'min:0', 'max:1'],
            'weight_eligibility' => ['required', 'numeric', 'min:0', 'max:1'],
            'weight_skills' => ['required', 'numeric', 'min:0', 'max:1'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'slots' => ['required', 'integer', 'min:1', 'max:100'],
            'posting_date' => ['required', 'date'],
            'closing_date' => ['required', 'date', 'after_or_equal:posting_date'],
            'status' => ['required', Rule::in(['DRAFT', 'OPEN'])],
        ];
    }

    /**
     * Split a free-text skills field into the list the scorer reads.
     *
     * Comma or newline separated, because that is how a QS list is typed. Empty
     * entries are dropped rather than stored as "" — a blank skill would match
     * nothing and quietly dilute the skills subscore.
     *
     * @return array<int, string>
     */
    private static function skills(string $value): array
    {
        $parts = preg_split('/[,;\r\n]+/', $value) ?: [];

        return array_values(array_filter(
            array_map(static fn (string $skill) => trim($skill), $parts),
            static fn (string $skill) => $skill !== ''
        ));
    }

    private static function blankToNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
