@extends('layouts.app')

{{--
    Every position in every state, not just the open ones.

    The archive keeps lapsed, closed and cancelled positions visible to
    HR on purpose — time-to-fill and pipeline reporting need them — and this
    list mirrors that decision. The archive tab is the narrower view of the same
    data, badged by why the position left service.

    The status counts come from the unfiltered list so the tabs describe the
    whole board rather than only the tab being viewed; that is also why the
    controller fetches once and narrows in memory.
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
            'title' => 'The vacancy list could not be loaded',
        ])
    @else

        <form method="GET" action="{{ route('admin.recruitment.vacancies') }}" class="filter-card">
            <div class="filter-card-fields">
                <div class="fld">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="q" value="{{ $search }}" class="fc-input"
                           placeholder="Position or office" aria-label="Search positions">
                </div>

                <div class="fld">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <select name="status" class="fc-select" aria-label="Filter by status">
                        <option value="">All statuses ({{ $total }})</option>
                        @foreach(\App\Services\HiringPresenter::VACANCY_STATUSES as $code => $meta)
                            <option value="{{ $code }}" @selected($status === $code)>
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
                @if($status || $search !== '')
                    <a href="{{ route('admin.recruitment.vacancies') }}" class="btn-ghost">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Vacancies</h3>
                    <p class="table-sub">
                        <strong>{{ count($vacancies) }}</strong>
                        @if($status || $search !== '')
                            of <strong>{{ $total }}</strong> positions matching this view
                        @else
                            {{ \Illuminate\Support\Str::plural('position', count($vacancies)) }} on record
                        @endif
                    </p>
                </div>
                <div class="table-actions">
                    <a href="{{ route('admin.recruitment.archive') }}" class="btn-export">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                        Archive
                    </a>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th scope="col">Position</th>
                            <th scope="col">Plantilla</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="hire-center">Slots</th>
                            <th scope="col" class="hire-center">Applicants</th>
                            <th scope="col">Closing</th>
                            <th scope="col" class="hire-center">Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vacancies as $vacancy)
                            @php
                                $daysLeft = \App\Services\HiringPresenter::daysUntil($vacancy['closing_date'] ?? null);
                            @endphp
                            <tr>
                                <td>
                                    <div class="hire-title-cell">
                                        <strong>{{ $vacancy['position_title'] }}</strong>
                                        <span>{{ $vacancy['place_of_assignment'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="hire-title-cell">
                                        <strong>{{ ($vacancy['plantilla_item_no'] ?? null) ?: '—' }}</strong>
                                        <span>
                                            @if(!empty($vacancy['salary_grade']))
                                                SG {{ $vacancy['salary_grade'] }}
                                            @endif
                                            {{ isset($vacancy['monthly_salary']) ? \App\Services\HiringPresenter::peso($vacancy['monthly_salary']) : '' }}
                                        </span>
                                    </div>
                                </td>
                                <td><x-hiring-status :value="$vacancy['status'] ?? null" /></td>
                                <td class="hire-center hire-num">{{ (int) ($vacancy['slots'] ?? 1) }}</td>
                                <td class="hire-center hire-num">{{ (int) ($vacancy['applicant_count'] ?? 0) }}</td>
                                <td class="hire-nowrap">
                                    {{ \App\Services\HiringPresenter::date($vacancy['closing_date'] ?? null) ?? '—' }}
                                    @if($daysLeft !== null && $vacancy['status'] === 'OPEN')
                                        <div class="hire-hint">
                                            @if($daysLeft < 0)
                                                passed
                                            @elseif($daysLeft === 0)
                                                closes today
                                            @else
                                                {{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }} left
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="hire-center">
                                    <a href="{{ route('admin.recruitment.show', $vacancy['id']) }}" class="btn-export">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        @if($status || $search !== '')
                                            <p class="empty-title">No positions match this view</p>
                                            <p class="empty-sub">Try a different status, or clear the search.</p>
                                            <a href="{{ route('admin.recruitment.vacancies') }}" class="btn-ghost">Clear filters</a>
                                        @else
                                            <p class="empty-title">No vacancies yet</p>
                                            <p class="empty-sub">A position created here is published immediately.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p>Showing <strong>{{ count($vacancies) }}</strong> of <strong>{{ $total }}</strong> positions</p>
            </div>
        </div>

    @endif
</div>
@endsection
