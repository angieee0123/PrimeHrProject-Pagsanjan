@extends('layouts.app')

{{--
    Ranked candidates across every vacancy.

    The backend orders this endpoint by Fit Score, not by per-vacancy rank, and
    the rank position is shown alongside — the two are different facts (a strong
    candidate in a crowded vacancy can rank third with the same score that ranks
    first elsewhere) and re-sorting here would disagree with whichever board the
    reader came from.

    Only HR-uploaded or accepted portal applications hold a rank at all; the rest
    of the screened pool carries a Fit Score and is visible from the vacancy's
    own page.
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
            'title' => 'The shortlist could not be loaded',
        ])
    @else

        <form method="GET" action="{{ route('admin.recruitment.rankings') }}" class="filter-card">
            <div class="filter-card-fields">
                <div class="fld">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                    <select name="vacancy" class="fc-select" aria-label="Filter by vacancy">
                        <option value="">All vacancies ({{ $total }} ranked)</option>
                        @foreach($vacancies as $id => $title)
                            <option value="{{ $id }}" @selected($vacancyId === (string) $id)>{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-card-actions">
                <button type="submit" class="btn-solid">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Apply
                </button>
                @if($vacancyId !== '')
                    <a href="{{ route('admin.recruitment.rankings') }}" class="btn-ghost">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Ranked candidates</h3>
                    <p class="table-sub">
                        Ordered by Fit Score.
                        <strong>{{ count($rankings) }}</strong>
                        @if($vacancyId !== '')
                            of <strong>{{ $total }}</strong> ranked candidates for this vacancy
                        @else
                            {{ \Illuminate\Support\Str::plural('candidate', count($rankings)) }} holding a rank
                        @endif
                    </p>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th scope="col" class="hire-center">Rank</th>
                            <th scope="col">Candidate</th>
                            <th scope="col">Vacancy</th>
                            <th scope="col" class="hire-center">Fit score</th>
                            <th scope="col">Subscores</th>
                            <th scope="col">Standing</th>
                            <th scope="col">Why</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rankings as $row)
                            <tr>
                                <td class="hire-center hire-num">
                                    <strong>{{ $row['rank_position'] ?? '—' }}</strong>
                                </td>
                                <td>
                                    <div class="hire-title-cell">
                                        <strong>{{ $row['name'] ?: 'Name withheld' }}</strong>
                                        <span>{{ $row['anon_code'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="hire-title-cell">
                                        @if(!empty($row['vacancy_id']))
                                            <a href="{{ route('admin.recruitment.show', $row['vacancy_id']) }}">
                                                <strong>{{ $row['position_title'] ?? '—' }}</strong>
                                            </a>
                                        @else
                                            <strong>{{ $row['position_title'] ?? '—' }}</strong>
                                        @endif
                                        <span>{{ $row['place_of_assignment'] ?? '' }}</span>
                                    </div>
                                </td>
                                <td class="hire-center">
                                    @include('admin.recruitment.partials.fit-score', ['score' => $row['fit_score'] ?? null])
                                </td>
                                <td>
                                    <div class="hire-chips">
                                        @foreach($row['subscores'] ?? [] as $criterion => $value)
                                            <span class="hire-chip is-muted">
                                                {{ \App\Services\HiringPresenter::WEIGHTS[$criterion] ?? \Illuminate\Support\Str::headline($criterion) }}
                                                <b>{{ \App\Services\HiringPresenter::percent($value) }}</b>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <x-hiring-status kind="selection" :value="$row['selection_status'] ?? null" />
                                    @if(!empty($row['selection_reason']))
                                        <div class="hire-hint">{{ $row['selection_reason'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($row['explanation']))
                                        <details class="hire-why">
                                            <summary>Read the reasoning</summary>
                                            <p>{{ $row['explanation'] }}</p>
                                        </details>
                                    @else
                                        <span class="hire-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <p class="empty-title">No ranked candidates</p>
                                        <p class="empty-sub">
                                            A candidate joins a shortlist once a resume is uploaded against a
                                            vacancy, or once a portal application is accepted under Approvals.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p>Showing <strong>{{ count($rankings) }}</strong> of <strong>{{ $total }}</strong> ranked candidates</p>
            </div>
        </div>

    @endif
</div>
@endsection
