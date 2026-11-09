@extends('layouts.app')

{{--
    One position: its terms, the moves available on it, the people screening it
    and the shortlist that results.

    The lifecycle buttons are rendered only for the transitions the current
    status actually allows — a draft can be published or cancelled, a lapsed
    position reopened, a closed one neither. That is a display decision, not the
    rule: the native state machine (`VacancyLifecycleService::assertTransition`)
    remains the authority, and it is what answers if a stale page posts a move
    that has since become illegal. Its refusal message is flashed verbatim.
--}}

@section('content')
@include('admin.topbar.recruitmentTopbar')
@include('admin.notification.adminNotification')

@php
    $isAdministrator = auth()->user()?->hasRole('admin');
@endphp

<div class="glass-shell">

    @include('admin.recruitment.partials.flash')

    @if($vacancy === null)
        @include('admin.recruitment.partials.unavailable', [
            'failure' => $failure,
            'title' => 'This vacancy could not be loaded',
        ])
    @else

        @php
            $status = $vacancy['status'] ?? null;
            $closingDate = \App\Services\HiringPresenter::date($vacancy['closing_date'] ?? null);
            $daysLeft = \App\Services\HiringPresenter::daysUntil($vacancy['closing_date'] ?? null);

            // The lifecycle moves this status allows (lifecycle.ALLOWED_TRANSITIONS).
            $canPublish = $status === 'DRAFT';
            $canHold = $status === 'OPEN';
            $canResume = $status === 'ON_HOLD';
            $canReopen = $status === 'LAPSED';
            $canClose = in_array($status, ['OPEN', 'ON_HOLD', 'LAPSED'], true);
            $canCancel = in_array($status, ['DRAFT', 'OPEN', 'ON_HOLD', 'LAPSED'], true);
            $isTerminal = in_array($status, ['CLOSED', 'CANCELLED'], true);

            $hasLifecycleActions = $isAdministrator
                && ($canPublish || $canHold || $canResume || $canReopen || $canClose || $canCancel);
        @endphp

        <div class="hire-toolbar">
            <a href="{{ route('admin.recruitment.vacancies') }}" class="btn-ghost">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                All vacancies
            </a>
            <div class="hire-toolbar-right">
                <x-hiring-status :value="$status" />
            </div>
        </div>

        {{-- ── The position itself ── --}}
        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">{{ $vacancy['position_title'] }}</h3>
                    <p class="table-sub">
                        {{ $vacancy['place_of_assignment'] }}
                        @if(!empty($vacancy['plantilla_item_no']))
                            &nbsp;·&nbsp; Plantilla item {{ $vacancy['plantilla_item_no'] }}
                        @endif
                    </p>
                </div>
                <div class="table-actions">
                    <span class="hire-chip"><b>{{ (int) ($vacancy['applicant_count'] ?? 0) }}</b> applicants</span>
                    <span class="hire-chip"><b>{{ (int) ($vacancy['screened_count'] ?? 0) }}</b> screened</span>
                </div>
            </div>

            <div class="hire-section-body">
                <div class="hire-facts">
                    <div class="hire-fact">
                        <p class="hire-fact-label">Status</p>
                        <p class="hire-fact-value">
                            <x-hiring-status :value="$status" />
                        </p>
                    </div>

                    <div class="hire-fact">
                        <p class="hire-fact-label">Salary</p>
                        <p class="hire-fact-value">
                            @if(!empty($vacancy['salary_grade']))
                                Grade {{ $vacancy['salary_grade'] }} ·
                            @endif
                            {{ \App\Services\HiringPresenter::peso($vacancy['monthly_salary'] ?? 0) }}
                        </p>
                    </div>

                    <div class="hire-fact">
                        <p class="hire-fact-label">Authorized slots</p>
                        <p class="hire-fact-value">{{ (int) ($vacancy['slots'] ?? 1) }}</p>
                    </div>

                    <div class="hire-fact">
                        <p class="hire-fact-label">Posted</p>
                        <p class="hire-fact-value">{{ \App\Services\HiringPresenter::date($vacancy['posting_date'] ?? null) ?? '—' }}</p>
                    </div>

                    <div class="hire-fact">
                        <p class="hire-fact-label">Closing date</p>
                        <p class="hire-fact-value">
                            {{ $closingDate ?? '—' }}
                            @if($daysLeft !== null && $status === 'OPEN')
                                <span class="hire-hint">
                                    @if($daysLeft < 0)
                                        (passed)
                                    @elseif($daysLeft === 0)
                                        (closes today)
                                    @else
                                        ({{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }} left)
                                    @endif
                                </span>
                            @endif
                        </p>
                    </div>

                    <div class="hire-fact">
                        <p class="hire-fact-label">Grace period ends</p>
                        <p class="hire-fact-value">
                            {{ \App\Services\HiringPresenter::dateTime($vacancy['grace_period_expires_at'] ?? null) ?? '—' }}
                        </p>
                    </div>

                    @if($status === 'CLOSED')
                        <div class="hire-fact">
                            <p class="hire-fact-label">Closed</p>
                            <p class="hire-fact-value">{{ \App\Services\HiringPresenter::dateTime($vacancy['closed_at'] ?? null) ?? '—' }}</p>
                        </div>
                    @endif

                    @if($status === 'CANCELLED')
                        <div class="hire-fact">
                            <p class="hire-fact-label">Cancellation reason</p>
                            <p class="hire-fact-value">
                                {{ $vacancy['cancel_reason_label'] ?? ($vacancy['cancel_reason'] ?? '—') }}
                                @if(!empty($vacancy['cancel_reason_detail']))
                                    <span class="hire-hint">— {{ $vacancy['cancel_reason_detail'] }}</span>
                                @endif
                            </p>
                        </div>
                    @endif

                    <div class="hire-fact is-wide">
                        <p class="hire-fact-label">Qualification standards</p>
                        <div class="hire-facts is-tight">
                            <div class="hire-fact">
                                <p class="hire-fact-label">Education</p>
                                <p class="hire-fact-value is-soft">{{ $vacancy['qs_education'] ?? '—' }}</p>
                            </div>
                            <div class="hire-fact">
                                <p class="hire-fact-label">Training</p>
                                <p class="hire-fact-value is-soft">{{ (int) ($vacancy['qs_training_hours'] ?? 0) }} hours</p>
                            </div>
                            <div class="hire-fact">
                                <p class="hire-fact-label">Experience</p>
                                <p class="hire-fact-value is-soft">{{ (float) ($vacancy['qs_experience_years'] ?? 0) }} years</p>
                            </div>
                            <div class="hire-fact">
                                <p class="hire-fact-label">Eligibility</p>
                                <p class="hire-fact-value is-soft">{{ $vacancy['qs_eligibility'] ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="hire-fact is-wide">
                        <p class="hire-fact-label">Required skills</p>
                        <div class="hire-chips">
                            @forelse($vacancy['required_skills'] ?? [] as $skill)
                                <span class="hire-chip">{{ $skill }}</span>
                            @empty
                                <span class="hire-hint">None listed.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="hire-fact is-wide">
                        <p class="hire-fact-label">Fit-score weights</p>
                        <div class="hire-chips">
                            @foreach(\App\Services\HiringPresenter::WEIGHTS as $key => $label)
                                <span class="hire-chip is-muted">
                                    {{ $label }}
                                    <b>{{ \App\Services\HiringPresenter::percent($vacancy['weights'][$key] ?? 0) }}</b>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    @if(!empty($vacancy['instructions']))
                        <div class="hire-fact is-wide">
                            <p class="hire-fact-label">Application instructions</p>
                            <p class="hire-fact-value is-soft" style="white-space: pre-line;">{{ $vacancy['instructions'] }}</p>
                        </div>
                    @endif

                    @if(!empty($vacancy['hold_note']))
                        <div class="hire-fact is-wide">
                            <p class="hire-fact-label">Internal hold note</p>
                            <p class="hire-fact-value is-soft">{{ $vacancy['hold_note'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ── Lifecycle ──
             <details> rather than a modal: each move needs at most a note or a
             date, and a disclosure cannot leave a half-filled dialog on screen
             after a validation redirect. --}}
        @if($hasLifecycleActions)
            <section class="table-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Manage this position</h3>
                        <p class="table-sub">
                            Only the moves this status allows are offered. The lifecycle service
                            enforces the same rules, and its refusal is shown verbatim.
                        </p>
                    </div>
                </div>

                <div class="hire-section-body">
                    <div class="hire-actions">

                        @if($canPublish)
                            <details class="hire-panel">
                                <summary>Publish</summary>
                                <form method="POST" action="{{ route('admin.recruitment.publish', $vacancy['id']) }}" class="hire-panel-body">
                                    @csrf
                                    <p>A draft is not public and never lapses. Publishing makes it visible and starts accepting applications, with the posting date set to today.</p>
                                    <button type="submit" class="btn-solid">Publish vacancy</button>
                                </form>
                            </details>
                        @endif

                        @if($canHold)
                            <details class="hire-panel">
                                <summary>Put on hold</summary>
                                <form method="POST" action="{{ route('admin.recruitment.hold', $vacancy['id']) }}" class="hire-panel-body">
                                    @csrf
                                    <div class="hire-field">
                                        <label class="hire-label" for="hold-note">Internal note</label>
                                        <input type="text" id="hold-note" name="note" class="hire-input" maxlength="255"
                                               placeholder="Budget freeze, awaiting approval…">
                                        <p class="hire-hint">Never shown to applicants, and they are not notified of a hold — it is temporary and reads as a rejection.</p>
                                    </div>
                                    <button type="submit" class="btn-solid">Put on hold</button>
                                </form>
                            </details>
                        @endif

                        @if($canResume)
                            <details class="hire-panel">
                                <summary>Resume recruitment</summary>
                                <form method="POST" action="{{ route('admin.recruitment.resume', $vacancy['id']) }}" class="hire-panel-body">
                                    @csrf
                                    <div class="hire-field">
                                        <label class="hire-label" for="resume-date">New closing date</label>
                                        <input type="date" id="resume-date" name="closing_date" class="hire-input"
                                               value="{{ old('closing_date', $vacancy['closing_date'] ?? '') }}">
                                        <p class="hire-hint">
                                            Required if the stored closing date passed during the pause —
                                            resuming into an immediate lapse is refused.
                                        </p>
                                    </div>
                                    <button type="submit" class="btn-solid">Resume</button>
                                </form>
                            </details>
                        @endif

                        @if($canReopen)
                            <details class="hire-panel">
                                <summary>Reopen</summary>
                                <form method="POST" action="{{ route('admin.recruitment.reopen', $vacancy['id']) }}" class="hire-panel-body">
                                    @csrf
                                    <div class="hire-field">
                                        <label class="hire-label" for="reopen-date">New closing date</label>
                                        <input type="date" id="reopen-date" name="closing_date" class="hire-input" required
                                               min="{{ now()->toDateString() }}" value="{{ old('closing_date') }}">
                                        <p class="hire-hint">
                                            Reopens this same requisition, so its previous applicants stay attached.
                                        </p>
                                    </div>
                                    <button type="submit" class="btn-solid">Reopen vacancy</button>
                                </form>
                            </details>
                        @endif

                        @if($canClose)
                            <details class="hire-panel">
                                <summary>Close as filled</summary>
                                <form method="POST" action="{{ route('admin.recruitment.close', $vacancy['id']) }}" class="hire-panel-body"
                                      data-confirm="Closing records that the position was filled. It is final — a closed position cannot be reopened."
                                      data-confirm-title="Close this vacancy?"
                                      data-confirm-action="Close vacancy">
                                    @csrf
                                    <div class="hire-field">
                                        <label class="hire-label" for="close-reason">Note <span class="hire-hint">(optional)</span></label>
                                        <input type="text" id="close-reason" name="reason" class="hire-input" maxlength="255"
                                               placeholder="Appointed — Juan Dela Cruz">
                                    </div>
                                    <button type="submit" class="btn-solid">Close vacancy</button>
                                </form>
                            </details>
                        @endif

                        @if($canCancel)
                            <details class="hire-panel is-danger">
                                <summary>Cancel</summary>
                                <form method="POST" action="{{ route('admin.recruitment.cancel', $vacancy['id']) }}" class="hire-panel-body"
                                      data-confirm="Cancelling withdraws the position permanently and notifies its applicants. It is final."
                                      data-confirm-title="Cancel this vacancy?"
                                      data-confirm-action="Cancel vacancy">
                                    @csrf
                                    <div class="hire-field">
                                        <label class="hire-label" for="cancel-reason">Reason</label>
                                        <select id="cancel-reason" name="reason" class="hire-select" required>
                                            <option value="">Choose a reason…</option>
                                            @foreach(\App\Services\HiringPresenter::CANCEL_REASONS as $code => $label)
                                                <option value="{{ $code }}" @selected(old('reason') === $code)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="hire-field">
                                        <label class="hire-label" for="cancel-detail">Detail <span class="hire-hint">(required for "Other")</span></label>
                                        <input type="text" id="cancel-detail" name="reason_detail" class="hire-input" maxlength="255"
                                               value="{{ old('reason_detail') }}">
                                    </div>
                                    <button type="submit" class="btn-solid">Cancel vacancy</button>
                                </form>
                            </details>
                        @endif
                    </div>
                </div>
            </section>
        @elseif($isTerminal)
            <div class="hire-notice">
                <strong>This position is {{ strtolower($status) }}.</strong>
                That decision is final — a new vacancy is needed to recruit for the role again.
            </div>
        @endif

        {{-- ── Resumes and applicants ── --}}
        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Applicants</h3>
                    <p class="table-sub">
                        Every resume filed against this vacancy. Screening runs in the background
                        after upload, so a row can read "Queued" for a moment.
                    </p>
                </div>
                <div class="table-actions">
                    <span class="hire-chip"><b>{{ count($profiles) }}</b> {{ \Illuminate\Support\Str::plural('profile', count($profiles)) }}</span>
                </div>
            </div>

            @if($profilesFailure)
                <div class="hire-degraded">
                    <strong>Applicants could not be loaded.</strong>
                    {{ $profilesFailure->message }}
                </div>
            @endif

            <div class="hire-section-body">
                <details class="hire-panel">
                    <summary>Upload resumes</summary>
                    <form method="POST" action="{{ route('admin.recruitment.resumes.upload', $vacancy['id']) }}"
                          enctype="multipart/form-data" class="hire-panel-body">
                        @csrf
                        <div class="hire-field">
                            <label class="hire-label" for="resume-files">Resume files</label>
                            <input type="file" id="resume-files" name="files[]" class="hire-input" multiple required
                                   accept=".pdf,.docx,.txt">
                            <p class="hire-hint">
                                PDF, DOCX or TXT, up to 10 MB each and 20 files at a time. A file
                                already on this vacancy is skipped rather than duplicated.
                            </p>
                        </div>
                        <button type="submit" class="btn-solid">Upload and screen</button>
                    </form>
                </details>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th scope="col">Applicant</th>
                            <th scope="col">Screening</th>
                            <th scope="col" class="hire-center">Fit score</th>
                            <th scope="col">Filed</th>
                            <th scope="col" class="hire-center">Resume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profiles as $profile)
                            <tr>
                                <td>
                                    <div class="hire-title-cell">
                                        <strong>{{ $profile['name'] ?: 'Name withheld' }}</strong>
                                        <span>{{ $profile['anon_code'] }}</span>
                                    </div>
                                </td>
                                <td><x-hiring-status kind="parse" :value="$profile['parse_status'] ?? null" /></td>
                                <td class="hire-center">
                                    @include('admin.recruitment.partials.fit-score', ['score' => $profile['fit_score'] ?? null])
                                </td>
                                <td class="hire-nowrap">{{ \App\Services\HiringPresenter::date($profile['created_at'] ?? null) ?? '—' }}</td>
                                <td class="hire-center">
                                    <a href="{{ route('admin.recruitment.resume-file', $profile['id']) }}"
                                       class="btn-export" target="_blank" rel="noopener">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <p class="empty-title">No applicants yet</p>
                                        <p class="empty-sub">Upload resumes above, or wait for applications from the applicant portal.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ── The shortlist ── --}}
        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Ranked shortlist</h3>
                    <p class="table-sub">
                        A rank is held only by candidates HR uploaded or the approval gate accepted.
                        Everyone else still carries a Fit Score, which is why this table can be
                        shorter than the applicants above.
                    </p>
                </div>
                @if($ranking && !empty($ranking['model_version']))
                    <div class="table-actions">
                        <span class="hire-chip is-muted">Scoring {{ $ranking['model_version'] }}</span>
                    </div>
                @endif
            </div>

            @if($rankingFailure)
                <div class="hire-degraded">
                    <strong>The shortlist could not be loaded.</strong>
                    {{ $rankingFailure->message }}
                </div>
            @endif

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th scope="col" class="hire-center">Rank</th>
                            <th scope="col">Candidate</th>
                            <th scope="col" class="hire-center">Fit score</th>
                            <th scope="col">Subscores</th>
                            <th scope="col">Standing</th>
                            <th scope="col">Why</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                            <tr>
                                <td class="hire-center hire-num"><strong>{{ $result['rank_position'] ?? '—' }}</strong></td>
                                <td>
                                    <div class="hire-title-cell">
                                        <strong>{{ $result['name'] ?: 'Name withheld' }}</strong>
                                        <span>{{ $result['anon_code'] }}</span>
                                    </div>
                                </td>
                                <td class="hire-center">
                                    @include('admin.recruitment.partials.fit-score', ['score' => $result['fit_score'] ?? null])
                                </td>
                                <td>
                                    <div class="hire-chips">
                                        @foreach($result['subscores'] ?? [] as $criterion => $value)
                                            <span class="hire-chip is-muted">
                                                {{ \App\Services\HiringPresenter::WEIGHTS[$criterion] ?? \Illuminate\Support\Str::headline($criterion) }}
                                                <b>{{ \App\Services\HiringPresenter::percent($value) }}</b>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <x-hiring-status kind="selection" :value="$result['selection_status'] ?? null" />
                                    @if(!empty($result['selection_reason']))
                                        <div class="hire-hint">{{ $result['selection_reason'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($result['explanation']))
                                        <details class="hire-why">
                                            <summary>Read the reasoning</summary>
                                            <p>{{ $result['explanation'] }}</p>
                                        </details>
                                    @else
                                        <span class="hire-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <p class="empty-title">No ranked candidates</p>
                                        <p class="empty-sub">
                                            A candidate joins the shortlist once their resume is uploaded here,
                                            or once a portal application is accepted under Approvals.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    @endif
</div>
@endsection
