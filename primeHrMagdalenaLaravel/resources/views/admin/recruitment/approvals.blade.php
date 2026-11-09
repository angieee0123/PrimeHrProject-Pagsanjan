@extends('layouts.app')

{{--
    The approval gate on portal applications.

    Only applications submitted through the applicant portal appear here: a
    resume HR uploaded on a candidate's behalf was already vetted by whoever
    uploaded it, which is also why those enter the ranking directly.

    Accepting admits the candidate to the vacancy's ranking immediately — the
    backend re-ranks on the decision rather than on the next upload — and
    disapproving drops them out and closes the gap below. Both are reversible by
    the opposite decision, so the button shown is always the one that would
    change something.
--}}

@section('content')
@include('admin.topbar.recruitmentTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

    @include('admin.recruitment.partials.flash')
    @include('admin.recruitment.partials.tabs')

    @if($failure)
        @include('admin.recruitment.partials.unavailable', [
            'failure' => $failure,
            'title' => 'The approval queue could not be loaded',
        ])
    @else

        <form method="GET" action="{{ route('admin.recruitment.approvals') }}" class="filter-card">
            <div class="filter-card-fields">
                <div class="fld">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <select name="review_status" class="fc-select" aria-label="Filter by decision">
                        <option value="">Every application ({{ $total }})</option>
                        @foreach(\App\Services\HiringPresenter::REVIEW_STATUSES as $code => $meta)
                            <option value="{{ $code }}" @selected($reviewStatus === $code)>
                                {{ $meta['label'] }} ({{ $counts[$code] ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-card-actions">
                <button type="submit" class="btn-solid">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Apply
                </button>
                @if($reviewStatus)
                    <a href="{{ route('admin.recruitment.approvals') }}" class="btn-ghost">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Portal applications</h3>
                    <p class="table-sub">
                        <strong>{{ count($approvals) }}</strong>
                        @if($reviewStatus)
                            of <strong>{{ $total }}</strong> applications with this decision
                        @else
                            {{ \Illuminate\Support\Str::plural('application', count($approvals)) }} from the applicant portal
                        @endif
                    </p>
                </div>
                <div class="table-actions">
                    <span class="hire-chip"><b>{{ $counts['PENDING'] ?? 0 }}</b> awaiting a decision</span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th scope="col">Applicant</th>
                            <th scope="col">Position applied for</th>
                            <th scope="col">Screening</th>
                            <th scope="col" class="hire-center">Fit score</th>
                            <th scope="col">Decision</th>
                            <th scope="col" class="hire-center">Resume</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvals as $approval)
                            @php $reviewStatusValue = $approval['review_status'] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="hire-title-cell">
                                        <strong>{{ $approval['name'] ?: 'Name withheld' }}</strong>
                                        <span>{{ $approval['anon_code'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="hire-title-cell">
                                        <a href="{{ route('admin.recruitment.show', $approval['vacancy_id']) }}">
                                            <strong>{{ $approval['position_title'] }}</strong>
                                        </a>
                                        <span>Filed {{ \App\Services\HiringPresenter::date($approval['created_at'] ?? null) ?? '—' }}</span>
                                    </div>
                                </td>
                                <td><x-hiring-status kind="parse" :value="$approval['parse_status'] ?? null" /></td>
                                <td class="hire-center">
                                    @include('admin.recruitment.partials.fit-score', ['score' => $approval['fit_score'] ?? null])
                                </td>
                                <td><x-hiring-status kind="review" :value="$reviewStatusValue" /></td>
                                <td class="hire-center">
                                    <a href="{{ route('admin.recruitment.resume-file', $approval['id']) }}"
                                       class="btn-export" target="_blank" rel="noopener">Open</a>
                                </td>
                                <td>
                                    <div class="hire-row-actions">
                                        @if($reviewStatusValue !== 'ACCEPTED')
                                            <form method="POST" action="{{ route('admin.recruitment.accept', $approval['id']) }}">
                                                @csrf
                                                <button type="submit" class="btn-solid">Accept</button>
                                            </form>
                                        @endif

                                        @if($reviewStatusValue !== 'DISAPPROVED')
                                            <form method="POST" action="{{ route('admin.recruitment.disapprove', $approval['id']) }}"
                                                  data-confirm="Disapproving removes this candidate from the vacancy's ranking and closes the gap below them."
                                                  data-confirm-title="Disapprove this application?"
                                                  data-confirm-action="Disapprove">
                                                @csrf
                                                <button type="submit" class="btn-ghost">Disapprove</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <p class="empty-title">Nothing here</p>
                                        <p class="empty-sub">
                                            Applications filed through the applicant portal appear here for a decision.
                                            Resumes uploaded directly on a vacancy skip this gate.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p>Showing <strong>{{ count($approvals) }}</strong> of <strong>{{ $total }}</strong> portal applications</p>
            </div>
        </div>

    @endif
</div>
@endsection
